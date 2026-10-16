<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\Support\NonDestructiveDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MenuSmokeTest extends TestCase
{
    use NonDestructiveDatabase;

    public function test_all_static_admin_pages_render(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs(Admin::create([
            'name' => 'Menu Tester', 'email' => 'menus@example.test',
            'password' => bcrypt('test-password'), 'role' => 'superadmin',
        ]), 'admin');

        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods()) || !str_starts_with($route->uri(), 'admin/')
                || str_contains($route->uri(), '{') || str_contains($route->uri(), 'password')
                || str_contains($route->uri(), 'generate-code') || str_contains($route->uri(), 'get-code-by-date')) {
                continue;
            }
            $response = $this->get('/'.$route->uri());
            $this->assertContains($response->status(), [200, 301, 302], $route->uri());
        }
    }

    public function test_password_recovery_pages_render(): void
    {
        $this->withoutExceptionHandling();
        foreach (['admin', 'customer'] as $portal) {
            $this->get('/'.$portal.'/forgot-password')->assertOk();
            $this->get('/'.$portal.'/reset-password/test-token?email=test@example.test')->assertOk()->assertSee('test-token');
        }
    }

    public function test_admin_password_recovery_sends_notification(): void
    {
        $this->withoutExceptionHandling();
        \Illuminate\Support\Facades\Notification::fake();
        $admin = Admin::create(['name' => 'Recovery', 'email' => 'reset@example.test',
            'password' => bcrypt('old-password'), 'role' => 'superadmin']);
        $this->post('/admin/forgot-password', ['email' => $admin->email])->assertRedirect();
        \Illuminate\Support\Facades\Notification::assertSentTo($admin, \Illuminate\Auth\Notifications\ResetPassword::class);
        $token = \Illuminate\Support\Facades\Password::broker('admins')->createToken($admin);
        $this->post('/admin/reset-password', ['email' => $admin->email, 'token' => $token,
            'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])
            ->assertRedirect(route('admin.login'))->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $admin->fresh()->password));
    }

    public function test_customer_pages_and_booking_submission(): void
    {
        $this->withoutExceptionHandling();
        $user = \App\Models\User::create(['username' => 'customer', 'email' => 'customer@example.test',
            'password' => 'test-password', 'role' => 'customer']);
        $customer = $user->customer()->create(['company_name' => 'Test Company',
            'email' => $user->email, 'profile_completed' => true, 'status' => 'active']);
        $this->actingAs($user, 'customer');
        foreach (['dashboard', 'profile', 'profile/edit', 'history', 'booking/create'] as $path) {
            $this->get('/customer/'.$path)->assertOk();
        }
        $this->post('/customer/booking/store', [
            'product_name' => 'Sample', 'product_type' => 'Food', 'quantity' => 10,
            'unit' => 'box', 'dmin' => 1, 'dmax' => 2, 'dim_length' => '10',
            'dim_width' => '20', 'dim_height' => '30', 'gross_weight_per_pcs' => 5,
        ])->assertRedirect(route('customer.dashboard'))->assertSessionHasNoErrors();
        $booking = $customer->bookings()->firstOrFail();
        $this->assertSame('10x20x30', $booking->products()->firstOrFail()->dimension_pack);
        $this->get('/customer/booking/'.$booking->id)->assertOk();
        $this->get('/customer/history')->assertOk();
        $this->get('/customer/booking/'.$booking->id.'/print')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs(Admin::create(['name' => 'Operator', 'email' => 'operator@example.test',
            'password' => bcrypt('test-password'), 'role' => 'superadmin']), 'admin');
        foreach (['/admin/orders/'.$booking->id, '/admin/orders/'.$booking->id.'/receive',
            '/admin/business/'.$booking->id.'/detail', '/admin/bookings/'.$booking->id.'/edit'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_visible_navigation_links_are_accessible_for_each_staff_role(): void
    {
        $this->withoutExceptionHandling();
        foreach (['superadmin', 'manager', 'production', 'cargo_admin'] as $role) {
            $this->actingAs(Admin::create(['name' => $role, 'email' => $role.'@example.test',
                'password' => bcrypt('test-password'), 'role' => $role]), 'admin');
            foreach (['/admin/dashboard', '/admin/workspace-settings'] as $page) {
                $response = $this->get($page)->assertOk();
                preg_match_all('/href="([^"#]+)"/', $response->getContent(), $matches);
                foreach (array_unique($matches[1]) as $url) {
                    $path = parse_url(html_entity_decode($url), PHP_URL_PATH);
                    if (!str_starts_with($path ?? '', '/admin/')) continue;
                    $this->get($path)->assertSuccessful();
                }
            }
        }
    }

    public function test_controller_routes_reference_existing_methods(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (!str_starts_with($action, 'App\\Http\\Controllers\\')) {
                continue;
            }
            [$controller, $method] = explode('@', $action);
            $this->assertTrue(method_exists($controller, $method), $action);
        }
    }
}
