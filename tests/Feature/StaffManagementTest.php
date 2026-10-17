<?php
namespace Tests\Feature;

use App\Models\{Admin, ProductionLine};
use Illuminate\Support\Facades\DB;
use Tests\Support\NonDestructiveDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use NonDestructiveDatabase;

    private function admin(string $role, string $suffix): Admin
    {
        return Admin::create(['name'=>$suffix,'email'=>$suffix.'@staff.test','password'=>bcrypt('test-password'),'role'=>$role]);
    }

    public function test_every_staff_role_can_edit_own_profile_but_not_another_account(): void
    {
        $other=$this->admin('manager','other');
        foreach(['superadmin','manager','production','cargo_admin'] as $role) {
            $actor=$this->admin($role,$role); $this->actingAs($actor,'admin');
            $this->get(route('admin.profile.edit',$actor))->assertOk()->assertSee('name="current_password"',false);
            $this->put(route('admin.profile.update',$actor),['name'=>'Changed '.$role,'email'=>$actor->email])->assertSessionHasNoErrors()->assertRedirect(route('admin.profile'));
            $this->assertSame('Changed '.$role,$actor->fresh()->name);
            if($role!=='superadmin') {
                $this->get(route('admin.profile.edit',$other))->assertForbidden();
                $this->put(route('admin.profile.update',$other),['name'=>'Wrong account','email'=>$other->email])->assertForbidden();
            }
        }
        $this->assertSame('other',$other->fresh()->name);
        $this->get('/admin/profile/not-an-id/edit')->assertNotFound();
    }

    public function test_superadmin_editing_other_staff_does_not_display_own_password_form(): void
    {
        $actor=$this->admin('superadmin','actor'); $other=$this->admin('production','operator');
        $this->actingAs($actor,'admin');
        $this->get(route('admin.profile.edit',$other))->assertOk()->assertDontSee('name="current_password"',false)->assertDontSee('const passwordInput',false);
        $this->put(route('admin.profile.update',$other),['name'=>'Updated operator','email'=>$other->email])->assertSessionHasNoErrors()->assertRedirect(route('admin.profile.profileList'));
        $this->assertSame('actor',$actor->fresh()->name);
    }

    public function test_template_author_without_audit_event_cannot_be_deleted(): void
    {
        $actor=$this->admin('superadmin','actor'); $author=$this->admin('production','author');
        $this->actingAs($actor,'admin');
        $machine=ProductionLine::create(['name'=>'Unit']);
        DB::table('production_parameter_templates')->insert(['name'=>'Recorded recipe','production_line_id'=>$machine->id,'target_dose'=>2,'beam_speed'=>10,'loading_mode'=>'single_side','created_by'=>$author->id,'created_at'=>now(),'updated_at'=>now()]);
        $this->delete(route('admin.profile.destroy',$author),['confirmed'=>1])->assertSessionHas('error');
        $this->assertDatabaseHas('admins',['id'=>$author->id]);
    }

    public function test_unreferenced_staff_can_be_deleted_but_own_account_is_protected(): void
    {
        $actor=$this->admin('superadmin','actor'); $other=$this->admin('production','draft');
        $this->actingAs($actor,'admin');
        $this->delete(route('admin.profile.destroy',$actor),['confirmed'=>1])->assertSessionHas('error');
        $this->delete(route('admin.profile.destroy',$other),['confirmed'=>1])->assertRedirect(route('admin.profile.profileList'));
        $this->assertSoftDeleted('admins',['id'=>$other->id]);
        $this->assertDatabaseHas('admins',['id'=>$actor->id]);
    }
}
