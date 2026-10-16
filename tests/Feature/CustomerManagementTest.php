<?php
namespace Tests\Feature;

use App\Models\{Admin, Customer, User};
use Illuminate\Support\Facades\Hash;
use Tests\Support\NonDestructiveDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use NonDestructiveDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::create(['name'=>'Customer admin','email'=>'admin@customer.test','password'=>bcrypt('test-password'),'role'=>'superadmin']), 'admin');
    }

    private function customer(string $email): Customer
    {
        $user=User::create(['username'=>$email,'email'=>$email,'password'=>'test-password','role'=>'customer']);
        return $user->customer()->create(['company_name'=>'Original','email'=>$email,'profile_completed'=>true]);
    }

    private function data(string $email): array
    {
        return ['company_name'=>'Updated','email'=>$email,'contact_name'=>'Contact','contact_phone'=>'+6212345','address_line'=>'New office','industry'=>'Food','npwp'=>'TAX-123'];
    }

    public function test_create_preserves_tax_id_and_does_not_assign_shared_default_password(): void
    {
        $payload=$this->data('new@customer.test'); $payload['name']='New Contact';
        $this->post(route('admin.customerList.store'),$payload)->assertSessionHasNoErrors();
        $customer=Customer::where('email',$payload['email'])->firstOrFail();
        $this->assertSame('TAX-123',$customer->npwp);
        $this->assertFalse(Hash::check('12345678',$customer->user->password));
        $this->assertSame('New Contact',$customer->contacts()->first()->name);
    }

    public function test_duplicate_email_is_validation_error_without_partial_customer_or_user_updates(): void
    {
        $customer=$this->customer('first@customer.test'); $other=$this->customer('second@customer.test');
        $this->put(route('admin.customerList.update',$customer),$this->data($other->email))->assertSessionHasErrors('email');
        $this->assertSame('first@customer.test',$customer->fresh()->email);
        $this->assertSame('first@customer.test',$customer->user->fresh()->email);
        $this->assertSame('Original',$customer->fresh()->company_name);
        $this->assertDatabaseCount('customer_addresses',0);
    }

    public function test_edit_accepts_own_email_and_saves_company_tax_address_and_contact_together(): void
    {
        $customer=$this->customer('same@customer.test');
        $this->put(route('admin.customerList.update',$customer),$this->data($customer->email))->assertSessionHasNoErrors();
        $this->assertSame('TAX-123',$customer->fresh()->npwp);
        $this->assertSame('New office',$customer->addresses()->first()->address_line);
        $this->assertSame('Contact',$customer->contacts()->first()->name);
        $this->assertSame('same@customer.test',$customer->user->fresh()->email);
    }

}
