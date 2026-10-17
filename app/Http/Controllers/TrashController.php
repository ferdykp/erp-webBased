<?php
namespace App\Http\Controllers;

use App\Models\{Admin,Booking,Customer,User,ProductTest,Pallet,ProductionLine,Porter,WarehousePic};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrashController extends Controller
{
    private const MODELS=[Admin::class,Booking::class,Customer::class,User::class,ProductTest::class,Pallet::class,ProductionLine::class,Porter::class,WarehousePic::class,\App\Models\DosimeterDetail::class,\App\Models\ProductTestDosimeter::class];
    public function index()
    {
        $entries=DB::table('trash_entries')->whereNull('restored_at')->where('model_type','!=',User::class)->latest('id')->paginate(20);
        return view('admin.trash.index',compact('entries'));
    }
    public function restore(Request $request, int $id)
    {
        $request->validate(['confirmed'=>'accepted']);
        DB::transaction(function () use ($id) {
            $entry=DB::table('trash_entries')->where('id',$id)->lockForUpdate()->firstOrFail();
            if ($entry->restored_at) return; // Safe repeat submission.
            abort_unless(in_array($entry->model_type,self::MODELS,true),404);
            $model=$entry->model_type::onlyTrashed()->whereKey($entry->record_id)->lockForUpdate()->firstOrFail();
            if ($model instanceof Booking && $model->customer_id && !Customer::find($model->customer_id)) {
                throw ValidationException::withMessages(['restore'=>'Restore the customer account before restoring this order.']);
            }
            if ($model instanceof User) abort(403); // Customer accounts restore through their profile as one unit.
            if ($model instanceof \App\Models\DosimeterDetail) {
                $record=\App\Models\DosimeterRecord::findOrFail($model->dosimeter_record_id);
                $booking=Booking::whereKey($record->booking_id)->lockForUpdate()->firstOrFail();
                $record=\App\Models\DosimeterRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                if (\App\Services\WorkflowState::handedOver($booking) || \App\Services\WorkflowState::dosimetryFinal($booking,$record->id)) throw ValidationException::withMessages(['restore'=>'This dosimetry stage is closed. The archived row remains preserved in Trash.']);
                $model->tablet_number=1+(int)$record->details()->max('tablet_number');
                $model->saveQuietly();
                $record->update(['tablet_quantity'=>$record->details()->count()+1]);
            }
            if ($model instanceof \App\Models\ProductTestDosimeter) {
                $test=\App\Models\ProductTest::whereKey($model->product_test_id)->lockForUpdate()->firstOrFail();
                if ($test->dosimeters()->exists() && !$test->dosimeters()->whereNull('dose_kgy')->exists()) throw ValidationException::withMessages(['restore'=>'This test measurement stage is closed.']);
                $model->sequence=1+(int)$test->dosimeters()->max('sequence');
                $model->saveQuietly();
            }
            $model->restore();
            DB::table('trash_entries')->where('id',$id)->update(['restored_at'=>now(),'restored_by'=>auth('admin')->id(),'updated_at'=>now()]);
            \App\Services\WarehouseInventory::audit('trash.restored',$model->getTable(),$model->id,['trash_entry_id'=>$id,'original_status'=>$model->status]);
        },3);
        return back()->with('success','Record restored. Its original workflow stage and history are unchanged.');
    }
}
