<?php

/** Non-destructive MySQL race checks. Retains synthetic fixtures in the isolated UX schema. */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'beamcustom_codex_ux_20261016') {
    throw new RuntimeException('Use DB_CONNECTION=mysql DB_DATABASE=beamcustom_codex_ux_20261016. Operational databases are forbidden.');
}
config(['session.driver'=>'array', 'cache.default'=>'array']);

use App\Models\{Admin, Booking, BookingBatch, DosimeterRecord, Pallet, ProductionLine, User};
use App\Services\WarehouseInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (($argv[1] ?? '') === 'worker') {
    $data = json_decode(base64_decode($argv[2]), true, flags: JSON_THROW_ON_ERROR);
    auth('admin')->login(Admin::findOrFail($data['admin']));
    while (microtime(true) < $data['at']) usleep(2000);
    try {
        $booking = Booking::findOrFail($data['booking']);
        if ($data['mode'] === 'receive') {
            app(WarehouseInventory::class)->receive($booking->id, [['product_id'=>$booking->products()->firstOrFail()->id,'pallet_id'=>$data['slot'],'quantity'=>(int)$booking->products()->sum('quantity')]], ['pic_warehouse'=>'Race PIC','porter_name'=>'Race Porter']);
        } elseif ($data['mode'] === 'dispatch') {
            app(App\Http\Controllers\OrderDispatchController::class)->confirm(Request::create('/', 'POST', ['reference'=>'RACE-'.$booking->id,'quantity_confirmed'=>1,'physical_handover'=>1]), $booking);
        } else {
            app(App\Http\Controllers\IrradiationSessionController::class)->store(Request::create('/', 'POST', ['started_at'=>'2025-06-01T08:00','ended_at'=>'2025-06-01T09:00']), BookingBatch::findOrFail($data['batch']));
        }
        echo json_encode(['result'=>'saved']);
    } catch (Illuminate\Validation\ValidationException $e) {
        echo json_encode(['result'=>'rejected','errors'=>$e->errors()]);
    } catch (Throwable $e) {
        echo json_encode(['result'=>'unexpected','type'=>get_class($e),'message'=>$e->getMessage()]);
        exit(1);
    }
    exit;
}

$tag='UX-RACE-'.bin2hex(random_bytes(4));
$admin=Admin::create(['name'=>$tag,'email'=>$tag.'@example.test','password'=>bcrypt(bin2hex(random_bytes(16))),'role'=>'superadmin']);
auth('admin')->login($admin);
$machine=ProductionLine::create(['name'=>$tag]);
$slot=Pallet::create(['line'=>$tag,'slot_section'=>1,'status'=>'empty']);
$makeOrder=function(int $qty) use($tag) {
    $suffix=bin2hex(random_bytes(4));
    $user=User::create(['username'=>$tag.'-'.$suffix,'email'=>$suffix.'@'.$tag.'.test','password'=>bin2hex(random_bytes(16)),'role'=>'customer']);
    $customer=$user->customer()->create(['company_name'=>$tag,'email'=>$user->email,'profile_completed'=>true]);
    $booking=Booking::create(['customer_id'=>$customer->id,'user_id'=>$user->id,'booking_code'=>$tag.'-'.$suffix,'booking_type'=>'regular','status'=>'pending','payment_status'=>'unpaid','qr_token'=>$suffix]);
    $booking->products()->create(['product_name'=>'Synthetic race goods','product_type'=>'Test','quantity'=>$qty,'unit'=>'box','dmin'=>1,'dmax'=>5,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
    return $booking;
};
$race=function(array $jobs) use($admin) {
    $at=microtime(true)+1;
    $processes=[];
    foreach($jobs as $job) {
        $payload=base64_encode(json_encode($job+['admin'=>$admin->id,'at'=>$at]));
        $process=proc_open([PHP_BINARY,__FILE__,'worker',$payload],[1=>['pipe','w'],2=>['pipe','w']],$pipes,base_path());
        if(!is_resource($process)) throw new RuntimeException('Could not start test worker.');
        $processes[]=[$process,$pipes];
    }
    $results=[];
    foreach($processes as [$process,$pipes]) {
        $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
        if($exit!==0) throw new RuntimeException('Worker failed: '.$out.' '.$err);
        $results[]=json_decode($out,true,flags:JSON_THROW_ON_ERROR);
    }
    return $results;
};
$assert=function(bool $condition,string $message) { if(!$condition) throw new RuntimeException($message); };
$a=$makeOrder(480);$b=$makeOrder(320);
$results=$race([['mode'=>'receive','booking'=>$a->id,'slot'=>$slot->id],['mode'=>'receive','booking'=>$a->id,'slot'=>$slot->id]]);
$assert(collect($results)->where('result','saved')->count()===1,'Duplicate receiving must have one winner.');
$assert((int)$slot->fresh()->filled_boxes===480,'Duplicate receiving changed inventory.');
echo "PASS simultaneous duplicate receiving: one receipt, 480 boxes\n";

$c=$makeOrder(100);
$results=$race([['mode'=>'receive','booking'=>$b->id,'slot'=>$slot->id],['mode'=>'receive','booking'=>$c->id,'slot'=>$slot->id]]);
$assert(collect($results)->every(fn($r)=>$r['result']==='saved'),'Different orders sharing a slot should both save.');
$assert((int)$slot->fresh()->filled_boxes===900,'Shared receiving lost an update.');
echo "PASS simultaneous different-order receiving: exact 900-box stock\n";

$batch=$a->batches()->create(['booking_product_id'=>$a->products()->first()->id,'batch_number'=>1,'quantity'=>480,'unit'=>'box','status'=>'done','production_line_id'=>$machine->id,'target_dose'=>2,'beam_speed'=>10,'loading_mode'=>'single_side']);
$batch->qa()->create(['actual_dose'=>2,'visual_check'=>'pass','indicator_check'=>'changed','is_damaged'=>false,'damaged_qty'=>0,'inspected_at'=>now()]);
$a->update(['status'=>'completed']);
$record=DosimeterRecord::create(['booking_id'=>$a->id,'tablet_quantity'=>1]);
$record->details()->create(['tablet_number'=>1,'dosimeter_number'=>'RACE-D1','absorbance'=>0.1,'dose_kgy'=>2]);
$results=$race([['mode'=>'dispatch','booking'=>$a->id],['mode'=>'dispatch','booking'=>$a->id]]);
$assert(collect($results)->where('result','saved')->count()===1,'Duplicate dispatch must have one winner.');
$assert((int)$slot->fresh()->filled_boxes===420,'Dispatch changed stock belonging to other orders.');
$assert(DB::table('order_dispatches')->where('booking_id',$a->id)->count()===1,'Dispatch duplicated.');
echo "PASS simultaneous duplicate dispatch: one dispatch; other orders retain 420 boxes\n";

$jobs=[];
foreach([$b,$c] as $booking) {
    $booking->update(['status'=>'processing']);
    $batch=$booking->batches()->create(['booking_product_id'=>$booking->products()->first()->id,'batch_number'=>1,'quantity'=>$booking->products()->sum('quantity'),'unit'=>'box','status'=>'processing','production_line_id'=>$machine->id,'target_dose'=>2,'beam_speed'=>10,'loading_mode'=>'single_side']);
    $jobs[]=['mode'=>'session','booking'=>$booking->id,'batch'=>$batch->id];
}
$results=$race($jobs);
$assert(collect($results)->where('result','saved')->count()===1,'Overlapping machine sessions must have one winner.');
$assert(DB::table('irradiation_sessions')->where('production_line_id',$machine->id)->count()===1,'Concurrent overlapping sessions saved.');
echo "PASS simultaneous overlapping machine runs: one saved; conflict identified\n";
echo "Synthetic fixtures retained under {$tag} in isolated schema.\n";
