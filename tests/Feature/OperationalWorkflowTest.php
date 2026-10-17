<?php

namespace Tests\Feature;

use App\Models\{Admin, Booking, BookingBatch, Customer, DosimeterRecord, Pallet, ProductionLine, User};
use App\Services\WarehouseInventory;
use Tests\Support\NonDestructiveDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationalWorkflowTest extends TestCase
{
    use NonDestructiveDatabase;
    use \Tests\Support\ChecksAlpineExpressions;

    private Admin $operator;
    private Pallet $slot;
    private ProductionLine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operator = Admin::create(['name'=>'Operator', 'email'=>'operator@test.example', 'password'=>bcrypt('test-password'), 'role'=>'superadmin']);
        $this->actingAs($this->operator, 'admin');
        $this->slot = Pallet::create(['line'=>'A','slot_section'=>1,'status'=>'empty']);
        $this->machine = ProductionLine::create(['name'=>'Test Machine']);
    }

    private function order(int $quantity = 100, string $unit = 'box'): Booking
    {
        $index = Booking::count()+1;
        $user = User::create(['username'=>'Customer '.$index,'email'=>'customer'.$index.'@test.example','password'=>'test-password','role'=>'customer']);
        $customer = Customer::create(['user_id'=>$user->id,'company_name'=>'Company '.$index,'email'=>$user->email,'profile_completed'=>true]);
        $booking = Booking::create(['booking_code'=>'ORDER-'.$index,'booking_type'=>'regular','customer_id'=>$customer->id,'user_id'=>$user->id,'status'=>'pending','payment_status'=>'unpaid','qr_token'=>'qr-'.$index]);
        $booking->products()->create(['product_name'=>'Product '.$index,'product_type'=>'Food','quantity'=>$quantity,'unit'=>$unit,'dmin'=>1,'dmax'=>5,'dimension_pack'=>'10x20x30','density_gross'=>0.001,'density_nett'=>0.001]);
        return $booking;
    }

    private function receive(Booking $booking, ?Pallet $slot = null)
    {
        return $this->post(route('admin.bookings.storePlacement', $booking->id), [
            'pic_warehouse'=>'PIC', 'porter_name'=>'Porter', 'physical_verified'=>'1',
            'allocations'=>[['product_id'=>$booking->products->first()->id,'pallet_id'=>($slot ?? $this->slot)->id,'quantity'=>$booking->products->sum('quantity')]],
        ]);
    }

    private function batch(Booking $booking, array $quantities = [100]): BookingBatch
    {
        $this->post(route('admin.production.process'), $this->parameters($booking)+['batch_quantities'=>$quantities])
            ->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('error');
        return $booking->batches()->latest('id')->firstOrFail();
    }

    private function parameters(Booking $booking): array
    {
        return ['booking_id'=>$booking->id,'production_line_id'=>$this->machine->id,'target_dose'=>2,'beam_speed'=>10,'loading_mode'=>'single_side'];
    }

    private function recordRun(BookingBatch $batch, string $start = '2026-01-01T08:00', string $end = '2026-01-01T09:00')
    {
        return $this->post(route('admin.production.sessions.store', $batch->id), ['started_at'=>$start,'ended_at'=>$end]);
    }

    private function checks(): array
    {
        return ['actual_dose'=>2,'visual_check'=>'pass','indicator_check'=>'changed','is_damaged'=>'no'];
    }

    private function completed(Booking $booking): BookingBatch
    {
        $this->receive($booking)->assertSessionHasNoErrors();
        $batch = $this->batch($booking, [(int)$booking->products->sum('quantity')]);
        $this->put(route('admin.production.batches.start', $batch->id))->assertSessionMissing('error');
        // Different orders use distinct physical machine time windows.
        $date = \Carbon\Carbon::parse('2025-01-01')->subDays($booking->id)->format('Y-m-d');
        $this->recordRun($batch, "{$date}T08:00", "{$date}T09:00")->assertSessionHasNoErrors();
        $this->put(route('admin.production.batches.finish', $batch->id), $this->checks())->assertSessionMissing('error');
        $record = DosimeterRecord::create(['booking_id'=>$booking->id,'tablet_quantity'=>1]);
        $record->details()->create(['tablet_number'=>1,'dosimeter_number'=>'D-'.$booking->id,'absorbance'=>1,'dose_kgy'=>2]);
        return $batch;
    }

    private function dispatch(Booking $booking)
    {
        return $this->post(route('admin.dispatch.confirm', $booking), ['reference'=>'DELIVERY-1','physical_handover'=>1,'quantity_confirmed'=>1]);
    }

    public function test_empty_and_occupied_slot_receiving_preserves_both_orders(): void
    {
        $a = $this->order(480); $b = $this->order(320);
        $this->receive($a)->assertSessionHasNoErrors()->assertRedirect();
        $this->receive($b)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(800, (int)$this->slot->fresh()->filled_boxes);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$a->id,'quantity'=>480]);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$b->id,'quantity'=>320]);
        $this->assertNull($this->slot->fresh()->current_booking_id);
    }

    public function test_duplicate_receiving_is_rejected_without_changing_stock(): void
    {
        $booking=$this->order(); $this->receive($booking);
        $this->receive($booking)->assertSessionHasErrors('receiving');
        $this->assertSame(100,(int)$this->slot->fresh()->filled_boxes);
        $this->assertDatabaseCount('pallet_contents',1);
    }

    public function test_multi_product_receiving_records_ownership_and_units(): void
    {
        $booking=$this->order();
        $other=$booking->products()->create(['product_name'=>'Second','product_type'=>'Food','quantity'=>5,'unit'=>'drum','dmin'=>1,'dmax'=>5,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
        $this->get(route('admin.smart.receive',$booking))->assertOk()->assertSee('Second');
        $this->post(route('admin.bookings.storePlacement',$booking->id),['pic_warehouse'=>'PIC','porter_name'=>'Porter','physical_verified'=>1,'allocations'=>[
            ['product_id'=>$booking->products->first()->id,'pallet_id'=>$this->slot->id,'quantity'=>100],
            ['product_id'=>$other->id,'pallet_id'=>$this->slot->id,'quantity'=>5],
        ]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pallet_contents',['booking_product_id'=>$other->id,'unit'=>'drum','quantity'=>5]);
        $this->assertDatabaseHas('placement_details',['booking_product_id'=>$other->id,'unit'=>'drum','quantity'=>5]);
    }

    public function test_receiving_rejects_wrong_product_or_quantity_atomically(): void
    {
        $a=$this->order(); $b=$this->order();
        $this->post(route('admin.bookings.storePlacement',$a->id),['pic_warehouse'=>'PIC','porter_name'=>'Porter','physical_verified'=>1,'allocations'=>[
            ['product_id'=>$b->products->first()->id,'pallet_id'=>$this->slot->id,'quantity'=>100],
        ]])->assertSessionHasErrors('allocations');
        $this->assertDatabaseCount('pallet_contents',0);
        $this->assertNull($a->fresh()->arrival_time);
    }

    public function test_received_pending_order_can_create_single_batch_without_porters_or_approval(): void
    {
        $booking=$this->order(); $this->receive($booking);
        $batch=$this->batch($booking);
        $this->assertSame('pending',$booking->fresh()->status);
        $this->assertSame(100,$batch->quantity);
        $this->assertSame('Porter',$batch->porter_name);
    }

    public function test_multi_batch_allocation_and_duplicate_allocation_protection(): void
    {
        $booking=$this->order(); $this->receive($booking); $this->batch($booking,[40,40,20]);
        $this->assertSame([40,40,20],$booking->batches()->orderBy('id')->pluck('quantity')->all());
        $this->post(route('admin.production.process'),$this->parameters($booking)+['batch_quantities'=>[100]])->assertSessionHas('error');
        $this->assertSame(100,(int)$booking->batches()->sum('quantity'));
    }

    public function test_unreceived_order_cannot_enter_production(): void
    {
        $booking=$this->order();
        $this->post(route('admin.production.process'),$this->parameters($booking)+['batch_quantities'=>[100]])->assertSessionHas('error');
        $this->assertDatabaseCount('booking_batches',0);
    }

    public function test_loading_mode_and_product_dose_are_validated(): void
    {
        $booking=$this->order(); $this->receive($booking);
        $data=$this->parameters($booking)+['batch_quantities'=>[100]];
        $data['loading_mode']='invalid';
        $this->post(route('admin.production.process'),$data)->assertSessionHasErrors('loading_mode');
        $data['loading_mode']='double_side'; $data['target_dose']=99;
        $this->post(route('admin.production.process'),$data)->assertSessionHasErrors('target_dose');
        $data['target_dose']=2;
        $this->post(route('admin.production.process'),$data)->assertSessionHasNoErrors()->assertSessionMissing('error');
        $this->assertDatabaseHas('booking_batches',['loading_mode'=>'double_side']);
    }

    public function test_start_records_no_inferred_actual_timestamps(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking);
        $this->put(route('admin.production.batches.start',$batch))->assertSessionMissing('error');
        $this->assertSame('processing',$batch->fresh()->status);
        $this->assertNull($batch->fresh()->offline_at);
        $this->assertNull($batch->fresh()->finished_at);
        $this->assertDatabaseCount('irradiation_sessions',0);
    }

    public function test_multiple_runs_across_days_sum_actual_duration(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking);
        $this->put(route('admin.production.batches.start',$batch));
        $this->recordRun($batch,'2026-01-01T23:00','2026-01-03T01:00')->assertSessionHasNoErrors();
        $this->recordRun($batch,'2026-01-03T01:00','2026-01-03T02:30')->assertSessionHasNoErrors();
        $this->assertDatabaseCount('irradiation_sessions',2);
        $this->assertSame(1650,(int)$batch->fresh()->total_duration);
        $this->assertSame('2026-01-01 23:00:00',$batch->fresh()->offline_at->format('Y-m-d H:i:s'));
    }

    public function test_machine_overlap_names_conflict_and_allows_exact_boundary(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking,[50,50]);
        $other=$booking->batches()->oldest('id')->first();
        $this->put(route('admin.production.batches.start',$batch));
        $this->put(route('admin.production.batches.start',$other));
        $this->recordRun($batch)->assertSessionHasNoErrors();
        $response=$this->recordRun($other,'2026-01-01T08:30','2026-01-01T09:30')->assertSessionHasErrors('started_at');
        $message=session('errors')->first('started_at');
        $this->assertStringContainsString($booking->booking_code,$message);
        $this->assertStringContainsString('2026-01-01 08:00:00',$message);
        $this->recordRun($other,'2026-01-01T09:00','2026-01-01T09:30')->assertSessionHasNoErrors();
        $this->assertDatabaseCount('irradiation_sessions',2);
    }

    public function test_voiding_incorrect_run_preserves_history_and_recomputes_duration(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking);
        $this->put(route('admin.production.batches.start',$batch)); $this->recordRun($batch);
        $run=DB::table('irradiation_sessions')->first();
        $this->post(route('admin.production.sessions.void',[$batch,$run->id]),['reason'=>'Wrong actual time entered','confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertNotNull(DB::table('irradiation_sessions')->value('voided_at'));
        $this->assertSame(0,(int)$batch->fresh()->total_duration);
        $this->recordRun($batch)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('irradiation_sessions',2);
        $this->assertDatabaseHas('operational_audit_events',['action'=>'irradiation.voided']);
    }

    public function test_finish_requires_actual_run_and_passed_checks(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking);
        $this->put(route('admin.production.batches.start',$batch));
        $this->put(route('admin.production.batches.finish',$batch),$this->checks())->assertSessionHas('error');
        $this->assertSame('processing',$batch->fresh()->status);
        $this->recordRun($batch);
        $this->put(route('admin.production.batches.finish',$batch),array_replace($this->checks(),['visual_check'=>'fail']))->assertSessionHas('error');
        $this->assertSame('processing',$batch->fresh()->status);
        $this->put(route('admin.production.batches.finish',$batch),$this->checks())->assertSessionMissing('error');
        $this->assertSame('done',$batch->fresh()->status);
        $this->assertSame('completed',$booking->fresh()->status);
        $this->assertDatabaseCount('order_dispatches',0);
        $this->assertSame(100,(int)$this->slot->fresh()->filled_boxes);
    }

    public function test_measured_dose_difference_does_not_block_batch_completion(): void
    {
        $booking=$this->order(); $this->receive($booking); $batch=$this->batch($booking);
        $this->put(route('admin.production.batches.start',$batch)); $this->recordRun($batch);
        $this->put(route('admin.production.batches.finish',$batch),array_replace($this->checks(),['actual_dose'=>99]))
            ->assertSessionMissing('error')->assertSessionHas('success',fn($message)=>str_contains($message,'Warning: measured dose differs'));
        $this->assertSame('done',$batch->fresh()->status);
        $this->assertSame('completed',$booking->fresh()->status);
        $this->assertEquals(99,$batch->fresh()->qa->actual_dose);
    }

    public function test_direct_dispatch_preserves_other_booking_in_shared_slot_and_nullable_approval(): void
    {
        $a=$this->order(480); $this->completed($a);
        $b=$this->order(320); $this->receive($b);
        $this->dispatch($a)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertDatabaseHas('order_dispatches',['booking_id'=>$a->id,'status'=>'dispatched','approved_by'=>null,'approved_at'=>null,'dispatched_by'=>$this->operator->id,'dispatched_quantity'=>480]);
        $this->assertDatabaseMissing('pallet_contents',['booking_id'=>$a->id]);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$b->id,'quantity'=>320]);
        $this->assertSame(320,(int)$this->slot->fresh()->filled_boxes);
        $this->assertSame('filled',$this->slot->fresh()->status);
        $this->assertDatabaseHas('operational_audit_events',['action'=>'dispatch.completed','subject_id'=>$a->id]);
        $this->dispatch($a)->assertSessionHasErrors('dispatch');
        $this->assertDatabaseCount('order_dispatches',1);
    }

    public function test_legacy_approved_dispatch_can_complete_without_reapproval(): void
    {
        $booking=$this->order(); $this->completed($booking);
        DB::table('order_dispatches')->insert(['booking_id'=>$booking->id,'status'=>'approved','approved_by'=>$this->operator->id,'approved_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
        $this->dispatch($booking)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('order_dispatches',1);
        $this->assertDatabaseHas('order_dispatches',['booking_id'=>$booking->id,'status'=>'dispatched','approved_by'=>$this->operator->id]);
    }

    public function test_dispatch_requires_complete_readings_but_dose_differences_and_quality_notes_only_warn(): void
    {
        $booking=$this->order(); $this->completed($booking);
        $detail=DosimeterRecord::first()->details()->first();
        $detail->update(['dose_kgy'=>null]);
        $this->dispatch($booking)->assertSessionHasErrors('dispatch');
        $this->assertDatabaseCount('order_dispatches',0);
        $this->assertSame(100,(int)$this->slot->fresh()->filled_boxes);
        $detail->update(['dose_kgy'=>99]);
        $booking->batches()->first()->qa()->update(['actual_dose'=>99]);
        DB::table('quality_reviews')->insert(['booking_id'=>$booking->id,'decision'=>'hold','notes'=>'Investigate','reviewed_by'=>$this->operator->id,'created_at'=>now(),'updated_at'=>now()]);
        $this->get(route('admin.dispatch.show',$booking))->assertOk()->assertSee('Verification Differences')->assertSee('Dispatch can continue without any approval.');
        $this->dispatch($booking)->assertSessionHasNoErrors();
        $dispatch=DB::table('order_dispatches')->where('booking_id',$booking->id)->first();
        $this->assertNull($dispatch->approved_by);
        $warnings=json_decode($dispatch->verification_warnings,true);
        $this->assertCount(3,$warnings);
        $this->assertStringContainsString('99',implode(' ', $warnings));
        $this->assertStringContainsString('Investigate',implode(' ', $warnings));
        $this->get(route('admin.dispatch.show',$booking))->assertOk()->assertSee('Verification Differences');
    }

    public function test_relocation_into_occupied_slot_preserves_ownership_and_placements(): void
    {
        $other=Pallet::create(['line'=>'B','slot_section'=>1,'status'=>'empty']);
        $a=$this->order(40);$b=$this->order(60);$this->receive($a);$this->receive($b,$other);
        $this->post(route('admin.enhancements.warehouse.relocate'),['source_pallet_id'=>$this->slot->id,'destination_pallet_id'=>$other->id,'reason'=>'Test relocation'])->assertSessionHasNoErrors();
        $this->assertSame(0,(int)$this->slot->fresh()->filled_boxes);
        $this->assertSame(100,(int)$other->fresh()->filled_boxes);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$a->id,'pallet_id'=>$other->id,'quantity'=>40]);
        $this->assertDatabaseHas('placement_details',['booking_id'=>$a->id,'line'=>'B','slot_section'=>1,'quantity'=>40]);
    }

    public function test_operational_order_and_occupied_slot_cannot_be_deleted(): void
    {
        $booking=$this->order();$this->receive($booking);
        $this->delete(route('admin.bookings.destroy',$booking->id), ['confirmed'=>1])->assertSessionHasErrors('booking');
        $this->delete(route('admin.pallets.destroy',$this->slot->id), ['confirmed'=>1])->assertSessionHas('error');
        $this->assertDatabaseHas('bookings',['id'=>$booking->id]);
        $this->assertDatabaseHas('pallets',['id'=>$this->slot->id]);
    }

    public function test_roles_cannot_mutate_other_operational_stages(): void
    {
        $booking=$this->order();
        $cargo=Admin::create(['name'=>'Cargo','email'=>'cargo@test.example','password'=>bcrypt('test-password'),'role'=>'cargo_admin']);
        $this->actingAs($cargo,'admin')->post(route('admin.production.process'),$this->parameters($booking)+['batch_quantities'=>[100]])->assertForbidden();
        $production=Admin::create(['name'=>'Production','email'=>'production@test.example','password'=>bcrypt('test-password'),'role'=>'production']);
        $this->actingAs($production,'admin');$this->receive($booking)->assertForbidden();$this->dispatch($booking)->assertForbidden();
    }
    public function test_admin_create_order_preserves_calculations_and_optional_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $existing = $this->order();
        $this->post(route('admin.bookings.store'), [
            'customer_id'=>$existing->customer_id, 'product_name'=>'Regression Sample', 'product_type'=>'Food',
            'quantity'=>10, 'unit'=>'box', 'dmin'=>1, 'dmax'=>5, 'dimension_pack'=>'10x20x30',
            'vol_per_pcs'=>6000, 'net_weight_pcs'=>1.5, 'gross_weight_per_pcs'=>2,
            'payment_status'=>'unpaid', 'total_price'=>125000,
            'product_photo'=>\Illuminate\Http\UploadedFile::fake()->image('product.png'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.bookings'));
        $booking=Booking::whereHas('products',fn($q)=>$q->where('product_name','Regression Sample'))->firstOrFail();
        $product=$booking->products()->firstOrFail();
        $this->assertSame(125000.0,$booking->total_price);
        $this->assertSame('10x20x30',$product->dimension_pack);
        $this->assertEquals(60000,$product->vol_total);
        $this->assertEquals(15,$product->total_net_weight);
        $this->assertEquals(20,$product->total_gross_weight);
        $this->assertEqualsWithDelta(20/60000,$product->density_gross,0.000001);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($product->photo_path);
        $this->get(route('admin.products.photo',$product->id))->assertOk();
    }

    public function test_product_testing_remains_separate_and_processed_history_is_protected(): void
    {
        $this->post(route('admin.testing.store'),['sample_name'=>'Lab Sample','quantity'=>2,'unit'=>'sample'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $test=\App\Models\ProductTest::firstOrFail();
        $this->assertDatabaseCount('bookings',0);
        $this->get(route('admin.testing.edit',$test))->assertOk();
        $this->put(route('admin.testing.parameters.update',$test),[
            'production_line_id'=>$this->machine->id,'target_dose'=>2,'beam_speed'=>10,
            'loading_mode'=>'single-side','freq'=>1,'scan_gear'=>1,
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.testing.dosimeters.update',$test),['readings'=>[
            ['dosimeter_number'=>'TEST-D1','position'=>'Top','absorbance'=>0.1],
        ]])->assertSessionHasNoErrors();
        $this->get(route('admin.testing.report',$test))->assertOk()->assertSee('TEST-D1');
        $this->delete(route('admin.testing.destroy',$test),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('product_tests',['id'=>$test->id]);
    }

    public function test_safe_deletion_requires_confirmation_and_authorization(): void
    {
        $porter=\App\Models\Porter::create(['name'=>'Unused Porter','is_active'=>true]);
        $this->delete(route('admin.porter.destroy',$porter))->assertSessionHasErrors('confirmed');
        $this->assertDatabaseHas('porters',['id'=>$porter->id]);
        $this->delete(route('admin.porter.destroy',$porter),['confirmed'=>1])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSoftDeleted('porters',['id'=>$porter->id]);
        $booking=$this->order();
        $manager=Admin::create(['name'=>'Manager','email'=>'manager@test.example','password'=>bcrypt('test-password'),'role'=>'manager']);
        $this->actingAs($manager,'admin')->delete(route('admin.bookings.destroy',$booking->id),['confirmed'=>1])->assertForbidden();
        $this->actingAs($this->operator,'admin')->delete(route('admin.bookings.destroy',$booking->id),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('bookings',['id'=>$booking->id]);
    }

    public function test_dispatch_inventory_reconciliation_failure_rolls_back_every_change(): void
    {
        $booking=$this->order();$this->completed($booking);
        DB::table('placement_details')->where('booking_id',$booking->id)->update(['quantity'=>99]);
        $this->dispatch($booking)->assertSessionHasErrors('dispatch');
        $this->assertDatabaseCount('order_dispatches',0);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$booking->id,'quantity'=>100]);
        $this->assertSame(100,(int)$this->slot->fresh()->filled_boxes);
    }

    public function test_completed_batch_parameters_and_dispatched_dosimetry_are_locked(): void
    {
        $booking=$this->order();$batch=$this->completed($booking);
        $this->put(route('admin.production.batches.parameter.update',$batch),$this->parameters($booking))->assertSessionHasErrors('parameters');
        $this->dispatch($booking)->assertSessionHasNoErrors();
        $this->postJson(route('admin.dosimeter.store-quantity'),['booking_id'=>$booking->id,'tablet_quantity'=>2])->assertUnprocessable()->assertJsonValidationErrors('dosimetry');
        $this->assertSame(1,(int)DosimeterRecord::where('booking_id',$booking->id)->value('tablet_quantity'));
    }

    public function test_legacy_receiving_form_still_accepts_uneven_allocations_into_shared_location(): void
    {
        $booking=$this->order(121);
        $this->post(route('admin.bookings.storePlacement',$booking->id),[
            'pic_warehouse'=>'PIC','porter_name'=>'Porter','total_qty'=>121,'per_pallet'=>31,
            'planning_mode'=>'manual','manual_pallet_count'=>4,
            'lines'=>['A','A','A','A'],'petaks'=>[1,1,1,1],'pallet_qty'=>[1,1,1,1],
        ])->assertSessionHasNoErrors();
        $this->assertSame([31,30,30,30],DB::table('pallet_contents')->orderBy('id')->pluck('quantity')->map(fn($n)=>(int)$n)->all());
        $this->assertSame(121,(int)$this->slot->fresh()->filled_boxes);
    }

    public function test_workflow_next_step_and_order_filters_use_receiving_records(): void
    {
        $booking=$this->order();$this->receive($booking);
        $this->get(route('admin.bookings',['view'=>'incoming']))->assertOk()->assertDontSee('Preview order '.$booking->booking_code);
        $this->get(route('admin.bookings',['view'=>'progress']))->assertOk()->assertSee('Preview order '.$booking->booking_code)->assertDontSee('Unarrived');
        $response=$this->get(route('admin.workspace.show',$booking))->assertOk();
        $response->assertSee('stage=preparation');
    }

    public function test_existing_document_downloads_render_after_production(): void
    {
        $booking=$this->order();$this->completed($booking);
        $this->get(route('admin.orders.report.pdf',$booking))->assertOk()->assertHeader('Content-Type','application/pdf');
        $this->get(route('admin.production.certificate',$booking->batches()->first()->id))->assertOk();
        $this->get(route('admin.report.export-excel',['id'=>$booking->id,'type'=>'nuc_processing_record']))->assertOk();
        $this->get(route('admin.report.export-excel',['id'=>$booking->id,'type'=>'jts_delivery_inbound']))->assertOk();
    }

    public function test_different_products_allocate_independently_and_do_not_mix_units(): void
    {
        $booking=$this->order();
        $first=$booking->products()->first();
        $second=$booking->products()->create(['product_name'=>'Drum Product','product_type'=>'Food','quantity'=>5,'unit'=>'drum','dmin'=>1,'dmax'=>5,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
        $this->post(route('admin.bookings.storePlacement',$booking->id),['pic_warehouse'=>'PIC','porter_name'=>'Porter','physical_verified'=>1,'allocations'=>[
            ['product_id'=>$first->id,'pallet_id'=>$this->slot->id,'quantity'=>100],['product_id'=>$second->id,'pallet_id'=>$this->slot->id,'quantity'=>5],
        ]])->assertSessionHasNoErrors();
        foreach([$first,$second] as $product) {
            $this->post(route('admin.production.process'),$this->parameters($booking)+['booking_product_id'=>$product->id,'batch_quantities'=>[$product->quantity]])->assertSessionHasNoErrors()->assertSessionMissing('error');
        }
        $this->assertDatabaseHas('booking_batches',['booking_product_id'=>$first->id,'quantity'=>100,'unit'=>'box']);
        $this->assertDatabaseHas('booking_batches',['booking_product_id'=>$second->id,'quantity'=>5,'unit'=>'drum']);
        $this->get(route('admin.pallets.index'))->assertOk()->assertSee('100 box')->assertSee('5 drum')->assertDontSee('105 ctn');
    }

    public function test_multi_product_dispatch_warns_about_missing_assignment_without_blocking(): void
    {
        $booking=$this->order();
        $first=$booking->products()->first();
        $second=$booking->products()->create(['product_name'=>'Second specification','product_type'=>'Food','quantity'=>5,'unit'=>'drum','dmin'=>10,'dmax'=>20,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
        $this->post(route('admin.bookings.storePlacement',$booking->id),['pic_warehouse'=>'PIC','porter_name'=>'Porter','physical_verified'=>1,'allocations'=>[
            ['product_id'=>$first->id,'pallet_id'=>$this->slot->id,'quantity'=>100],['product_id'=>$second->id,'pallet_id'=>$this->slot->id,'quantity'=>5],
        ]])->assertSessionHasNoErrors();
        $batches=[];
        foreach([$first,$second] as $index=>$product) {
            $batch=$booking->batches()->create(['booking_product_id'=>$product->id,'batch_number'=>$index+1,'quantity'=>$product->quantity,'unit'=>$product->unit,'status'=>'done','production_line_id'=>$this->machine->id]);
            $batch->qa()->create(['actual_dose'=>$index===0?2:15,'visual_check'=>'pass','indicator_check'=>'changed','is_damaged'=>false,'damaged_qty'=>0,'inspected_at'=>now()]);
            $batches[]=$batch;
        }
        $booking->update(['status'=>'completed']);
        $record=DosimeterRecord::create(['booking_id'=>$booking->id,'tablet_quantity'=>2]);
        foreach($batches as $index=>$batch) $record->details()->create(['tablet_number'=>$index+1,'dosimeter_number'=>'ASSIGNED-'.$index,'absorbance'=>0.1,'dose_kgy'=>$index===0?2:15,'booking_batch_id'=>$batch->id]);
        $detail=$record->details()->first();
        $detail->update(['booking_batch_id'=>null]);
        $this->get(route('admin.dispatch.show',$booking))->assertOk()->assertSee('batch/product assignment is missing');
        $this->dispatch($booking)->assertSessionHasNoErrors()->assertSessionHas('success');
        $dispatch=DB::table('order_dispatches')->where('booking_id',$booking->id)->first();
        $this->assertEquals(['box'=>100,'drum'=>5],json_decode($dispatch->quantity_by_unit,true));
        $this->assertNotEmpty(json_decode($dispatch->verification_warnings,true));
    }

    public function test_dosimeter_cannot_reference_a_batch_from_another_order(): void
    {
        $booking=$this->order();$batch=$this->completed($booking);
        $other=$this->order();$foreign=$other->batches()->create(['batch_number'=>1,'quantity'=>100,'unit'=>'box','status'=>'done']);
        $record=DosimeterRecord::where('booking_id',$booking->id)->firstOrFail();
        $record->details()->update(['absorbance'=>null,'dose_kgy'=>null]);
        $this->postJson(route('admin.dosimeter.store-absorbance',$record->id),[
            'dosimeter_number'=>[1=>'D-TEST'],'absorbance'=>[1=>0.1],'batch_id'=>[1=>$foreign->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('batch_id');
        $this->assertNull($record->details()->first()->booking_batch_id);
    }

    public function test_multi_product_measurements_allow_unassigned_rows_and_preserve_existing_assignment_when_omitted(): void
    {
        $booking=$this->order(); $batch=$this->completed($booking);
        $booking->products()->create(['product_name'=>'Other product','product_type'=>'Food','quantity'=>5,'unit'=>'box','dmin'=>1,'dmax'=>5,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
        $record=DosimeterRecord::where('booking_id',$booking->id)->firstOrFail();
        $record->details()->update(['absorbance'=>null,'dose_kgy'=>null]);
        $payload=['dosimeter_number'=>[1=>'D-OPTIONAL'],'absorbance'=>[1=>0.1]];
        $this->postJson(route('admin.dosimeter.store-absorbance',$record),$payload)->assertOk();
        $this->assertNull($record->details()->first()->booking_batch_id);
        $record->details()->first()->update(['booking_batch_id'=>$batch->id]);
        $this->postJson(route('admin.dosimeter.store-absorbance',$record),$payload)->assertUnprocessable();
        $this->assertEquals($batch->id,$record->details()->first()->booking_batch_id);
    }

    public function test_user_print_preview_and_legacy_pdf_download_both_work(): void
    {
        $booking=$this->order();$this->completed($booking);
        $this->get(route('admin.orders.report.print',$booking))->assertOk()->assertSee('window.print()',false);
        $this->get(route('admin.orders.report.pdf',$booking))->assertOk()->assertHeader('Content-Type','application/pdf');
        $this->get(route('admin.workspace.show',['booking'=>$booking,'stage'=>'documents']))->assertOk();
    }

    public function test_populated_operational_pages_render_and_inline_scripts_parse(): void
    {
        $this->withoutExceptionHandling();
        $booking=$this->order();$batch=$this->completed($booking);
        $test=\App\Models\ProductTest::create(['test_code'=>'PT-POPULATED','sample_name'=>'Sample','status'=>'completed','production_line_id'=>$this->machine->id]);
        $porter=\App\Models\Porter::create(['name'=>'Fixture Porter','is_active'=>true]);
        $pic=\App\Models\WarehousePic::create(['name'=>'Fixture PIC','email'=>'pic@test.example','is_active'=>true]);
        $this->machine->update(['name'=>"Unit O'Reilly \"A\"\nsecond line"]);
        $pages=[
            route('admin.workspace.production-board',['batch'=>$batch->id]), route('admin.workspace.show',$booking),
            route('admin.dispatch.show',$booking),route('admin.dosimeter.show',$booking->id),route('admin.pallets.index'),
            route('admin.bookings'),route('admin.business.detail',$booking->id),route('admin.enhancements.templates'),
            route('admin.enhancements.quality',$booking),route('admin.enhancements.documents',$booking),
            route('admin.porter.edit',$porter),route('admin.warehouse-pics.edit',$pic),route('admin.profile.edit',$this->operator->id),
            route('admin.customerList.index'),route('admin.business.index'),route('admin.production-lines.index'),
            route('admin.testing.report',$test),
        ];
        foreach($pages as $page) {
            $response=$this->get($page)->assertOk();
            $this->assertAlpineExpressionsParse($response->getContent(),$page);
            preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/is',$response->getContent(),$matches,PREG_SET_ORDER);
            foreach($matches as $match) {
                if(str_contains($match[1],'application/json') || trim($match[2])==='') continue;
                $path=tempnam(sys_get_temp_dir(),'beam-js-').'.js';
                try {
                    file_put_contents($path,$match[2]);
                    $process=new \Symfony\Component\Process\Process(['node','--check',$path]);
                    $process->run();
                    $this->assertTrue($process->isSuccessful(),$page.' inline script: '.$process->getErrorOutput());
                } finally { @unlink($path); @unlink(substr($path,0,-3)); }
            }
        }
    }

    public function test_dosimeter_row_correction_requires_both_confirmations_reindexes_and_retains_audit(): void
    {
        $booking=$this->order();$this->completed($booking);
        $record=DosimeterRecord::where('booking_id',$booking->id)->firstOrFail();
        $record->details()->update(['absorbance'=>null,'dose_kgy'=>null]);
        $record->update(['tablet_quantity'=>2]);
        $target=$record->details()->first();
        $target->update(['image'=>'dosimeter_images/retained-evidence.png']);
        $remaining=$record->details()->create(['tablet_number'=>2,'dosimeter_number'=>'KEPT','absorbance'=>0.1,'dose_kgy'=>2]);
        $url=route('admin.dosimeter.details.destroy',$target);
        $this->deleteJson($url,['confirm_delete'=>true])->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->deleteJson($url,['confirmed'=>true,'confirm_delete'=>true])->assertOk()->assertJsonPath('tablet_quantity',1);
        $this->assertSame(1,(int)$remaining->fresh()->tablet_number);
        $this->assertSame('KEPT',$remaining->fresh()->dosimeter_number);
        $event=DB::table('operational_audit_events')->where('action','dosimetry.detail_deleted')->first();
        $snapshot=json_decode($event->details,true);
        $this->assertSame($target->id,$snapshot['deleted_detail']['id']);
        $this->assertSame('dosimeter_images/retained-evidence.png',$snapshot['deleted_detail']['image']);
        $this->deleteJson(route('admin.dosimeter.details.destroy',$remaining),['confirmed'=>true,'confirm_delete'=>true])->assertUnprocessable()->assertJsonValidationErrors('dosimetry');
        $this->dispatch($booking)->assertSessionHasNoErrors();
        $this->deleteJson(route('admin.dosimeter.details.destroy',$remaining),['confirmed'=>true,'confirm_delete'=>true])->assertUnprocessable()->assertJsonValidationErrors('dosimetry');
        $this->assertDatabaseHas('dosimeter_details',['id'=>$remaining->id]);
    }

    public function test_document_draft_preserves_all_dosimetry_sets_and_versions(): void
    {
        $booking=$this->order();$this->completed($booking);
        $record=DosimeterRecord::create(['booking_id'=>$booking->id,'tablet_quantity'=>1]);
        $record->details()->create(['tablet_number'=>1,'dosimeter_number'=>'SECOND-SET','absorbance'=>0.1,'dose_kgy'=>2]);
        foreach([1,2] as $version) {
            $this->post(route('admin.enhancements.documents.generate',$booking),['document_type'=>'quality_summary'])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
            $draft=DB::table('order_document_drafts')->where('booking_id',$booking->id)->orderByDesc('id')->first();
            $this->assertSame($version,(int)$draft->version);
            $snapshot=json_decode($draft->snapshot,true);
            $this->assertCount(2,$snapshot['dosimeters']);
            $this->assertCount(2,$snapshot['dosimeter_sets']);
            $this->assertSame('SECOND-SET',$snapshot['dosimeter_sets'][1]['details'][0]['dosimeter_number']);
        }
    }

    public function test_dosimetry_set_selection_cannot_modify_another_order(): void
    {
        $booking=$this->order();$this->completed($booking);
        $second=DosimeterRecord::create(['booking_id'=>$booking->id,'tablet_quantity'=>1]);
        $second->details()->create(['tablet_number'=>1]);
        $first=DosimeterRecord::where('booking_id',$booking->id)->orderBy('id')->first();
        $this->get(route('admin.dosimeter.show',['booking_id'=>$booking->id,'record'=>$second->id]))->assertOk()->assertSee('Set #'.$second->id);
        $this->postJson(route('admin.dosimeter.store-quantity'),['booking_id'=>$booking->id,'record_id'=>$second->id,'tablet_quantity'=>2])->assertOk();
        $this->assertSame(1,(int)$first->fresh()->tablet_quantity);
        $other=$this->order();
        $this->get(route('admin.dosimeter.show',['booking_id'=>$other->id,'record'=>$second->id]))->assertNotFound();
    }

}
