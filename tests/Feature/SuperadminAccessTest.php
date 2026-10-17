<?php
namespace Tests\Feature;

use App\Models\{Admin, Booking, BookingBatch, DosimeterRecord, Pallet, PalletContent, PlacementDetail, ProductionLine, ProductTest, User};
use Illuminate\Support\Facades\{DB, Gate, Route};
use Tests\Support\NonDestructiveDatabase;
use Tests\TestCase;

class SuperadminAccessTest extends TestCase
{
    use NonDestructiveDatabase;
    private Admin $admin;
    private Pallet $slot;
    private ProductionLine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin=$this->staff('superadmin'); $this->actingAs($this->admin,'admin');
        $this->slot=Pallet::create(['line'=>'RBAC','slot_section'=>1,'status'=>'empty']);
        $this->machine=ProductionLine::create(['name'=>'RBAC Unit']);
    }
    private function staff(string $role): Admin
    {
        return Admin::create(['name'=>$role,'email'=>uniqid().'@rbac.test','password'=>bcrypt('test-password'),'role'=>$role]);
    }
    private function override(array $data=[]): array
    {
        return $data+['confirmed'=>1,'override_reason'=>'Correct a verified historical record'];
    }
    private function order(int $quantity=100): Booking
    {
        $user=User::create(['username'=>uniqid(),'email'=>uniqid().'@customer.test','password'=>'test-password','role'=>'customer']);
        $customer=$user->customer()->create(['company_name'=>'RBAC customer','email'=>$user->email,'profile_completed'=>true]);
        $booking=Booking::create(['booking_code'=>uniqid('RBAC-'),'booking_type'=>'regular','customer_id'=>$customer->id,'user_id'=>$user->id,'status'=>'completed','arrival_time'=>now(),'qr_token'=>uniqid()]);
        $product=$booking->products()->create(['product_name'=>'Stock','product_type'=>'Food','quantity'=>$quantity,'unit'=>'box','dmin'=>1,'dmax'=>5,'dimension_pack'=>'1x1x1','density_gross'=>1,'density_nett'=>1]);
        PalletContent::create(['booking_id'=>$booking->id,'booking_product_id'=>$product->id,'pallet_id'=>$this->slot->id,'product_name'=>'Stock','quantity'=>$quantity,'unit'=>'box']);
        PlacementDetail::create(['booking_id'=>$booking->id,'booking_product_id'=>$product->id,'line'=>'RBAC','slot_section'=>1,'sequence'=>1,'quantity'=>$quantity,'unit'=>'box','status'=>'placed']);
        \App\Services\WarehouseInventory::sync($this->slot);
        $batch=$booking->batches()->create(['booking_product_id'=>$product->id,'batch_number'=>1,'quantity'=>$quantity,'unit'=>'box','status'=>'done','production_line_id'=>$this->machine->id,'target_dose'=>2,'beam_speed'=>10,'loading_mode'=>'single_side']);
        $batch->qa()->create(['actual_dose'=>2,'visual_check'=>'pass','indicator_check'=>'changed','is_damaged'=>false,'damaged_qty'=>0,'inspected_at'=>now()]);
        $record=DosimeterRecord::create(['booking_id'=>$booking->id,'tablet_quantity'=>1]);
        $record->details()->create(['tablet_number'=>1,'dosimeter_number'=>'D-'.$booking->id,'absorbance'=>0.1,'dose_kgy'=>2]);
        return $booking;
    }

    public function test_superadmin_bypasses_existing_role_middleware_and_gate_but_other_roles_do_not(): void
    {
        Route::middleware(['web','auth:admin','role:production'])->get('/rbac-only-production',fn()=>'allowed');
        Gate::define('rbac-denied',fn($user)=>false);
        $this->get('/rbac-only-production')->assertOk();
        $this->assertTrue(Gate::forUser($this->admin)->allows('rbac-denied'));
        $manager=$this->staff('manager'); $this->actingAs($manager,'admin');
        $this->get('/rbac-only-production')->assertForbidden();
        $this->assertFalse(Gate::forUser($manager)->allows('rbac-denied'));
    }

    public function test_csrf_is_still_required_for_superadmin_actions(): void
    {
        $csrf=new class($this->app,$this->app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
            protected function runningUnitTests() { return false; }
        };
        $this->app->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,$csrf);
        $this->withSession(['_token'=>'rbac-csrf'])->post(route('admin.porter.store'),['name'=>'Must not save','is_active'=>1])->assertRedirect(route('admin.login'));
        $this->assertDatabaseMissing('porters',['name'=>'Must not save']);
    }

    public function test_staff_role_and_password_changes_require_confirmation_and_redact_secrets(): void
    {
        $staff=$this->staff('production');
        $data=['name'=>$staff->name,'email'=>$staff->email,'role'=>'manager','password'=>'New-test-password-2026!','password_confirmation'=>'New-test-password-2026!'];
        $url=route('admin.profile.update',$staff->id);
        $this->putJson($url,$data)->assertUnprocessable();
        $this->assertSame('production',$staff->fresh()->role);
        $this->put($url,$this->override($data))->assertSessionHasNoErrors();
        $this->assertSame('manager',$staff->fresh()->role);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($data['password'],$staff->fresh()->password));
        $event=DB::table('operational_audit_events')->where('action','admin.action')->latest('id')->first();
        $this->assertStringNotContainsString($data['password'],$event->details);
        $this->assertStringNotContainsString($staff->fresh()->password,$event->details);
        $this->actingAs($staff,'admin');
        $this->put($url,['name'=>$staff->name,'email'=>$staff->email,'role'=>'superadmin'])->assertSessionHasNoErrors();
        $this->assertSame('manager',$staff->fresh()->role);
    }

    public function test_customer_password_reset_requires_confirmation_and_keeps_other_accounts_unchanged(): void
    {
        $booking=$this->order(); $other=$this->order();
        $customer=$booking->customer; $otherHash=$other->customer->user->password;
        $data=['company_name'=>'Corrected customer','email'=>$customer->email,'contact_name'=>'Contact','contact_phone'=>'08123456789','address_line'=>'Test address','password'=>'Customer-new-2026!','password_confirmation'=>'Customer-new-2026!'];
        $url=route('admin.customerList.update',$customer->id);
        $this->putJson($url,$data)->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $this->put($url,$this->override($data))->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($data['password'],$customer->user->fresh()->password));
        $this->assertSame($otherHash,$other->customer->user->fresh()->password);
        $event=DB::table('operational_audit_events')->where('action','admin.action')->latest('id')->first();
        $this->assertStringNotContainsString($data['password'],$event->details);
        $this->assertCount(2,json_decode($event->details,true)['before']);
    }

    public function test_completed_batches_are_locked_for_every_staff_role(): void
    {
        $booking=$this->order(); $batch=$booking->batches()->first();
        foreach (['superadmin','production'] as $role) {
            $this->actingAs($role==='superadmin' ? $this->admin : $this->staff($role),'admin');
            $this->putJson(route('admin.production.batches.parameter.update',$batch),['production_line_id'=>$this->machine->id,'beam_speed'=>20,'target_dose'=>2,'loading_mode'=>'single_side'])->assertUnprocessable();
            $this->putJson(route('admin.production.batches.start',$batch))->assertUnprocessable();
            $this->postJson(route('admin.production.sessions.store',$batch),['started_at'=>'2026-10-10T08:00','ended_at'=>'2026-10-10T09:00'])->assertUnprocessable();
            $this->putJson(route('admin.production.batches.finish',$batch),['actual_dose'=>2,'visual_check'=>'pass','indicator_check'=>'changed','is_damaged'=>'no'])->assertUnprocessable();
        }
        $this->assertSame('done',$batch->fresh()->status);
        $this->assertEquals(10,$batch->fresh()->beam_speed);
    }

    public function test_legacy_override_flags_cannot_reopen_workflow(): void
    {
        $booking=$this->order(); $batch=$booking->batches()->first();
        $data=$this->override(['superadmin_override'=>1]);
        $this->putJson(route('admin.production.batches.start',$batch),$data)->assertUnprocessable();
        $this->actingAs($this->staff('production'),'admin');
        $this->putJson(route('admin.production.batches.start',$batch),$data)->assertForbidden();
        $this->assertSame('done',$batch->fresh()->status);
    }

    public function test_completed_order_hides_receive_process_edit_and_blocks_direct_forms(): void
    {
        $booking=$this->order();
        $this->get(route('admin.bookings'))->assertOk()
            ->assertDontSee(route('admin.smart.receive',$booking),false)
            ->assertDontSee(route('admin.smart.batch-plan',$booking),false)
            ->assertDontSee(route('admin.bookings.edit',$booking),false);
        $this->get(route('admin.smart.receive',$booking))->assertRedirect(route('admin.bookings'));
        $this->get(route('admin.smart.batch-plan',$booking))->assertRedirect();
        $this->get(route('admin.bookings.edit',$booking))->assertStatus(409);
        $this->putJson(route('admin.bookings.update',$booking),['status'=>'pending'])->assertUnprocessable();
        $this->putJson(route('admin.bookings.update-status',$booking),['status'=>'pending'])->assertUnprocessable();
        $this->assertSame('completed',$booking->fresh()->status);
    }

    public function test_active_stock_blocks_trash_and_preserves_other_orders(): void
    {
        $booking=$this->order(); $other=$this->order(320);
        $this->deleteJson(route('admin.bookings.destroy',$booking),['confirmed'=>1])->assertUnprocessable();
        $this->assertNotNull(Booking::find($booking->id));
        $this->assertEquals(420,$this->slot->fresh()->filled_boxes);
        $this->assertDatabaseHas('pallet_contents',['booking_id'=>$other->id,'quantity'=>320]);
        $this->assertDatabaseCount('trash_entries',0);
    }

    public function test_handover_is_final_and_completed_order_can_be_trashed_and_restored_without_reopening(): void
    {
        $booking=$this->order(); $other=$this->order(320); $batch=$booking->batches()->first();
        $this->post(route('admin.dispatch.confirm',$booking),['reference'=>'FINAL','physical_handover'=>1,'quantity_confirmed'=>1])->assertSessionHasNoErrors();
        $this->postJson(route('admin.dispatch.confirm',$booking),['reference'=>'REPEAT','physical_handover'=>1,'quantity_confirmed'=>1])->assertUnprocessable();
        $this->postJson(route('admin.dosimeter.store-quantity'),['booking_id'=>$booking->id,'tablet_quantity'=>2])->assertUnprocessable();
        $this->get(route('admin.dispatch.show',$booking))->assertOk()->assertDontSee('name="physical_handover"',false);
        $this->delete(route('admin.bookings.destroy',$booking),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('bookings',['id'=>$booking->id]);
        $this->assertDatabaseHas('booking_batches',['id'=>$batch->id,'status'=>'done']);
        $this->assertFalse(BookingBatch::whereKey($batch->id)->exists());
        $this->assertEquals(320,$this->slot->fresh()->filled_boxes);
        $this->get(route('admin.workspace.show',$booking))->assertNotFound();
        $entry=DB::table('trash_entries')->where('model_type',Booking::class)->where('record_id',$booking->id)->first();
        $this->get(route('admin.trash.index'))->assertOk()->assertSee($booking->booking_code);
        $this->post(route('admin.trash.restore',$entry->id),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->post(route('admin.trash.restore',$entry->id),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSame('completed',Booking::findOrFail($booking->id)->status);
        $this->assertTrue(BookingBatch::whereKey($batch->id)->exists());
        $this->get(route('admin.smart.receive',$booking))->assertRedirect(route('admin.bookings'));
        $this->assertEquals(320,$this->slot->fresh()->filled_boxes);
        $this->assertSame(1,DB::table('operational_audit_events')->where('action','trash.restored')->count());
    }

    public function test_trash_restore_requires_superadmin_authentication_and_confirmation(): void
    {
        $porter=\App\Models\Porter::create(['name'=>'Archive me','is_active'=>true]);
        $this->delete(route('admin.porter.destroy',$porter),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('porters',['id'=>$porter->id]);
        $entry=DB::table('trash_entries')->where('record_id',$porter->id)->where('model_type',get_class($porter))->first();
        $this->postJson(route('admin.trash.restore',$entry->id))->assertUnprocessable();
        $this->actingAs($this->staff('manager'),'admin');
        $this->get(route('admin.trash.index'))->assertForbidden();
        $this->postJson(route('admin.trash.restore',$entry->id),['confirmed'=>1])->assertForbidden();
        $this->assertSoftDeleted('porters',['id'=>$porter->id]);
    }

    public function test_final_measurement_set_cannot_be_edited_deleted_or_expanded(): void
    {
        $booking=$this->order(); $record=DosimeterRecord::where('booking_id',$booking->id)->first();
        $this->postJson(route('admin.dosimeter.store-absorbance',$record),['dosimeter_number'=>[1=>'REPLACE'],'absorbance'=>[1=>0.5]])->assertUnprocessable();
        $this->postJson(route('admin.dosimeter.store-quantity'),['booking_id'=>$booking->id,'tablet_quantity'=>2])->assertUnprocessable();
        $this->deleteJson(route('admin.dosimeter.details.destroy',$record->details()->first()),['confirmed'=>1,'confirm_delete'=>1])->assertUnprocessable();
        $this->assertEquals(2,$record->details()->first()->dose_kgy);
    }

    public function test_archive_rolls_back_when_snapshot_fails(): void
    {
        $porter=\App\Models\Porter::create(['name'=>'Atomic trash','is_active'=>true]);
        $service=\Mockery::mock(\App\Services\AdminRecordHistory::class)->makePartial();
        $service->shouldReceive('snapshot')->once()->andReturn(['before'=>'retained']);
        $service->shouldReceive('snapshot')->once()->andThrow(new \RuntimeException('Snapshot unavailable'));
        $this->app->instance(\App\Services\AdminRecordHistory::class,$service);
        $this->withoutExceptionHandling();
        try { $this->delete(route('admin.porter.destroy',$porter),['confirmed'=>1]); $this->fail('Expected snapshot failure'); }
        catch (\RuntimeException $e) { $this->assertSame('Snapshot unavailable',$e->getMessage()); }
        $this->assertNotNull(\App\Models\Porter::find($porter->id));
        $this->assertDatabaseCount('trash_entries',0);
    }

    public function test_force_delete_is_disabled(): void
    {
        $this->expectException(\LogicException::class);
        $this->admin->forceDelete();
    }
    public function test_customer_trash_preserves_contacts_and_restores_linked_login(): void
    {
        $user=User::create(['username'=>uniqid(),'email'=>uniqid().'@restore.test','password'=>'password','role'=>'customer']);
        $customer=$user->customer()->create(['company_name'=>'Restore customer','email'=>$user->email,'profile_completed'=>true]);
        $contact=$customer->contacts()->create(['name'=>'Retained contact','phone'=>'08123','is_primary'=>true]);
        $this->delete(route('admin.customerList.destroy',$customer),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('customers',['id'=>$customer->id]);
        $this->assertSoftDeleted('users',['id'=>$user->id]);
        $this->assertDatabaseHas('customer_contacts',['id'=>$contact->id]);
        $entry=DB::table('trash_entries')->where('model_type',get_class($customer))->where('record_id',$customer->id)->first();
        $this->post(route('admin.trash.restore',$entry->id),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertNotNull(User::find($user->id));
        $this->assertNotNull(\App\Models\Customer::find($customer->id));
        $this->assertSame(0,DB::table('trash_entries')->whereNull('restored_at')->count());
    }

    public function test_blank_measurement_row_can_be_trashed_and_restored_without_index_collision(): void
    {
        $booking=$this->order(); $record=DosimeterRecord::where('booking_id',$booking->id)->first();
        $record->details()->update(['absorbance'=>null,'dose_kgy'=>null]);
        $record->update(['tablet_quantity'=>2]);
        $row=$record->details()->create(['tablet_number'=>2]);
        $this->deleteJson(route('admin.dosimeter.details.destroy',$row),['confirmed'=>1,'confirm_delete'=>1])->assertOk();
        $this->assertSoftDeleted('dosimeter_details',['id'=>$row->id]);
        $entry=DB::table('trash_entries')->where('model_type',get_class($row))->where('record_id',$row->id)->first();
        $this->post(route('admin.trash.restore',$entry->id),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSame(2,$record->details()->count());
        $this->assertSame([1,2],$record->details()->pluck('tablet_number')->all());
        $this->assertEquals(2,$record->fresh()->tablet_quantity);
    }

    public function test_archived_machine_cannot_be_selected_by_direct_submission(): void
    {
        $machine=ProductionLine::create(['name'=>'Archived machine']);
        $this->delete(route('admin.production-lines.destroy',$machine),['confirmed'=>1])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('production_lines',['id'=>$machine->id]);
        $booking=$this->order(); $batch=$booking->batches()->first();$batch->update(['status'=>'pending']);$booking->update(['status'=>'processing']);
        $this->putJson(route('admin.production.batches.parameter.update',$batch),['production_line_id'=>$machine->id,'beam_speed'=>10,'target_dose'=>2,'loading_mode'=>'single_side'])->assertUnprocessable()->assertJsonValidationErrors('production_line_id');
        $this->assertEquals($this->machine->id,$batch->fresh()->production_line_id);
    }

    public function test_handover_blocks_document_generation_and_quality_reviews(): void
    {
        $booking=$this->order();
        $this->post(route('admin.dispatch.confirm',$booking),['reference'=>'FINAL','physical_handover'=>1,'quantity_confirmed'=>1])->assertSessionHasNoErrors();
        $this->postJson(route('admin.enhancements.documents.generate',$booking),['document_type'=>'production_summary'])->assertUnprocessable();
        $this->postJson(route('admin.enhancements.quality.review',$booking),['decision'=>'approved'])->assertUnprocessable();
        $this->get(route('admin.enhancements.documents',$booking))->assertOk()->assertDontSee('Generate draft snapshot');
        $this->get(route('admin.enhancements.quality',$booking))->assertOk()->assertDontSee('Record Quality Review');
        $this->assertDatabaseCount('quality_reviews',0);
        $this->assertDatabaseCount('order_document_drafts',0);
    }

}
