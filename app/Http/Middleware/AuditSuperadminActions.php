<?php
namespace App\Http\Middleware;

use App\Models\{Admin, Booking, BookingBatch, Customer, DosimeterRecord, Pallet, ProductTest, ProductionLine};
use App\Services\{AdminRecordHistory, SuperadminAccess, WarehouseInventory};
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditSuperadminActions
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->boolean('superadmin_override')) {
            abort_unless(SuperadminAccess::isSuperadmin(),403);
            throw \Illuminate\Validation\ValidationException::withMessages(['workflow'=>'Workflow override is disabled. Completed stages cannot be reopened.']);
        }
        if (!auth('admin')->check() || $request->isMethodSafe() || $request->routeIs('admin.logout')) return $next($request);
        $actor = auth('admin')->id();
        return DB::transaction(function () use ($request,$next,$actor) {
            $roots = $this->roots($request);
            $override = SuperadminAccess::overriding();
            \App\Services\WorkflowState::enforce($request,$roots);
            $request->attributes->set('superadmin.created', []);
            $history = app(AdminRecordHistory::class);
            $before = array_map(fn($model)=>$history->snapshot($model),$roots);
            $response = $next($request);
            if ($response->getStatusCode() < 400) {
                $subjects = array_merge($roots,$request->attributes->get('superadmin.created',[]));
                $subject = $subjects[0] ?? null;
                DB::table('operational_audit_events')->insert([
                    'admin_id'=>$actor,'action'=>$override ? 'superadmin.override' : 'admin.action',
                    'subject_type'=>$subject?->getTable() ?? 'admin_route','subject_id'=>$subject?->getKey() ?? 0,
                    'details'=>json_encode(['route'=>$request->route()->getName(),'method'=>$request->method(),
                        'reason'=>$request->input('override_reason'),'sensitive_fields_changed'=>$request->filled('password') ? ['password'] : [],'before'=>$before,
                        'after'=>array_map(fn($model)=>$history->snapshot($model),$subjects)],JSON_THROW_ON_ERROR),
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            return $response;
        },3);
    }

    private function roots(Request $request): array
    {
        $roots=[];
        $route=$request->route(); $name=$route->getName() ?? '';
        foreach ($route->parameters() as $key=>$value) {
            if ($key === 'session') continue; // Session history is captured under its bound batch.
            if ($value instanceof Model) { $roots[]=$value; continue; }
            if (!is_numeric($value)) continue;
            $class = match (true) {
                $key === 'booking_id' => Booking::class,
                str_contains($name,'templates.approve') => \App\Models\ProductionParameterTemplate::class,
                str_contains($name,'warehouse-pics') => \App\Models\WarehousePic::class,
                str_contains($name,'porter') => \App\Models\Porter::class,
                str_contains($name,'pallets') => Pallet::class,
                str_contains($name,'production-lines') => ProductionLine::class,
                str_contains($name,'customerList') => Customer::class,
                str_contains($name,'profile') => Admin::class,
                str_contains($name,'dosimeter') => DosimeterRecord::class,
                str_contains($name,'batches'),str_contains($name,'sessions') => BookingBatch::class,
                str_contains($name,'testing') => ProductTest::class,
                str_contains($name,'booking'),str_contains($name,'dispatch'),str_contains($name,'orders') => Booking::class,
                default => null,
            };
            if ($class && ($model=$class::find($value))) $roots[]=$model;
        }
        if (!$roots && $request->filled('booking_id') && ($booking=Booking::find($request->input('booking_id')))) $roots[]=$booking;
        if (!$roots && $request->filled('batch_id') && ($batch=BookingBatch::find($request->input('batch_id')))) $roots[]=$batch;
        foreach (['source_pallet_id','destination_pallet_id'] as $field) {
            if ($request->filled($field) && ($slot=Pallet::find($request->input($field)))) $roots[]=$slot;
        }
        foreach ($roots as $root) {
            if ($root instanceof Customer && $root->user_id && ($user=\App\Models\User::find($root->user_id))) $roots[]=$user;
        }
        if (!$roots && $request->routeIs('admin.profile.password')) $roots[] = auth('admin')->user();
        return $roots;
    }
}
