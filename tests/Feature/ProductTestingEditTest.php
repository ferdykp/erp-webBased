<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ProductionLine;
use App\Models\ProductTest;
use Illuminate\Support\Facades\DB;
use Tests\Support\NonDestructiveDatabase;
use Tests\TestCase;

class ProductTestingEditTest extends TestCase
{
    use NonDestructiveDatabase;

    private ProductTest $test;

    private ProductionLine $line;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => bcrypt('password'), 'role' => 'superadmin']);
        $this->actingAs($this->admin, 'admin');
        $this->line = ProductionLine::create(['name' => 'Test machine']);
        $this->test = ProductTest::create(['test_code' => 'PT-EDIT', 'sample_name' => 'Original', 'status' => 'completed', 'processed_at' => now()->subDay()]);
        $this->test->dosimeters()->create(['sequence' => 1, 'dosimeter_number' => 'D1', 'absorbance' => 0.1, 'dose_kgy' => 3.4566]);
        $this->test->dosimeters()->create(['sequence' => 2, 'dosimeter_number' => 'D2', 'absorbance' => 0.2, 'dose_kgy' => 9.7692]);
    }

    public function test_superadmin_edits_completed_information_and_parameters_with_audit(): void
    {
        $processedAt = $this->test->processed_at;
        $this->get(route('admin.testing.edit', $this->test))->assertOk();
        $this->get(route('admin.testing.parameters', $this->test))->assertOk();
        $this->put(route('admin.testing.update', $this->test), ['sample_name' => 'Corrected', 'dmin' => 1, 'dmax' => 5])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.testing.report', $this->test));
        $this->put(route('admin.testing.parameters.update', $this->test), $this->parameters())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.testing.report', $this->test));
        $this->assertSame('Corrected', $this->test->fresh()->sample_name);
        $this->assertSame('completed', $this->test->fresh()->status);
        $this->assertTrue($processedAt->equalTo($this->test->fresh()->processed_at));
        $event = DB::table('operational_audit_events')->where('action', 'admin.action')->orderBy('id')->first();
        $details = json_decode($event->details, true);
        $this->assertEquals($this->admin->id, $event->admin_id);
        $this->assertNotNull($event->created_at);
        $this->assertSame('Original', $details['before'][0]['data']['sample_name']);
        $this->assertSame('Corrected', $details['after'][0]['data']['sample_name']);
    }

    public function test_superadmin_updates_adds_and_removes_final_readings_and_recalculates_results(): void
    {
        $this->put(route('admin.testing.dosimeters.update', $this->test), ['readings' => [
            ['dosimeter_number' => 'Changed', 'absorbance' => 0.3],
            ['dosimeter_number' => 'D2', 'absorbance' => 0.2],
            ['dosimeter_number' => 'D3', 'absorbance' => 0.4],
        ]])->assertSessionHasNoErrors();
        $this->assertSame(3, $this->test->dosimeters()->count());
        $event = DB::table('operational_audit_events')->where('action', 'product_test.dosimetry_saved')->first();
        $history = json_decode($event->details, true);
        $this->assertEquals($this->admin->id, $event->admin_id);
        $this->assertSame('D1', $history['before'][0]['dosimeter_number']);
        $this->assertSame('Changed', $history['after'][0]['dosimeter_number']);
        $this->assertCount(3, $history['after']);
        $dose = fn ($x) => round(13.099 * $x ** 3 + 8.7891 * $x ** 2 + 57.786 * $x - 2.423, 4);
        $this->assertEquals($dose(0.3), $this->test->dosimeters()->first()->dose_kgy);
        $this->get(route('admin.testing.report', $this->test))->assertOk()
            ->assertViewHas('doseStats', fn ($stats) => $stats['min'] === $dose(0.2) && $stats['max'] === $dose(0.4));
        $this->put(route('admin.testing.dosimeters.update', $this->test), ['readings' => [
            ['dosimeter_number' => 'D3', 'absorbance' => 0.4],
        ]])->assertSessionHasNoErrors();
        $this->assertSame(1, $this->test->dosimeters()->count());
        $this->put(route('admin.testing.dosimeters.update', $this->test), ['readings' => []])->assertSessionHasNoErrors();
        $this->assertSame(0, $this->test->dosimeters()->count());
        $this->get(route('admin.testing.report', $this->test))->assertOk()
            ->assertViewHas('doseStats', fn ($stats) => $stats['min'] === null && $stats['max'] === null);
    }

    public function test_invalid_changes_do_not_write_data_or_audit(): void
    {
        $this->putJson(route('admin.testing.update', $this->test), ['sample_name' => 'Invalid', 'dmin' => 5, 'dmax' => 1])->assertUnprocessable();
        $this->putJson(route('admin.testing.parameters.update', $this->test), $this->parameters() + ['status' => 'pending'])
            ->assertRedirect(route('admin.testing.report', $this->test));
        $this->putJson(route('admin.testing.parameters.update', $this->test), array_replace($this->parameters(), ['beam_speed' => 0]))->assertUnprocessable();
        $count = DB::table('operational_audit_events')->count();
        $this->putJson(route('admin.testing.dosimeters.update', $this->test), ['readings' => [['absorbance' => 6]]])->assertUnprocessable();
        $this->assertSame('Original', $this->test->fresh()->sample_name);
        $this->assertSame('completed', $this->test->fresh()->status);
        $this->assertSame($count, DB::table('operational_audit_events')->count());
    }

    public function test_other_roles_cannot_edit_completed_records_or_final_readings(): void
    {
        $this->admin->update(['role' => 'production']);
        $this->get(route('admin.testing.edit', $this->test))->assertRedirect(route('admin.testing.report', $this->test));
        $this->putJson(route('admin.testing.update', $this->test), ['sample_name' => 'Blocked'])->assertUnprocessable();
        $this->putJson(route('admin.testing.parameters.update', $this->test), $this->parameters())->assertUnprocessable();
        $this->putJson(route('admin.testing.dosimeters.update', $this->test), ['readings' => []])->assertUnprocessable();
        $this->admin->update(['role' => 'manager']);
        $this->putJson(route('admin.testing.update', $this->test), ['sample_name' => 'Blocked'])->assertUnprocessable();
        $this->assertSame('Original', $this->test->fresh()->sample_name);
    }

    public function test_zero_absorbance_is_recalculated_and_dur_handles_nonpositive_dose(): void
    {
        $this->put(route('admin.testing.dosimeters.update', $this->test), ['readings' => [
            ['dosimeter_number' => 'ZERO', 'absorbance' => 0],
        ]])->assertSessionHasNoErrors();
        $this->assertEquals(-2.423, $this->test->dosimeters()->first()->dose_kgy);
        $this->get(route('admin.testing.report', $this->test))->assertOk()
            ->assertViewHas('doseStats', fn ($stats) => $stats['min'] === -2.423 && $stats['max'] === -2.423);
    }

    public function test_pending_workflow_still_accepts_production_edits(): void
    {
        $this->test->update(['status' => 'parameter_pending', 'processed_at' => null]);
        $this->admin->update(['role' => 'production']);
        $this->put(route('admin.testing.update', $this->test), ['sample_name' => 'Pending edit'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.testing.parameters', $this->test));
        $this->put(route('admin.testing.parameters.update', $this->test), $this->parameters())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.testing.report', $this->test));
        $this->assertSame('completed', $this->test->fresh()->status);
        $this->assertNotNull($this->test->fresh()->processed_at);
    }

    private function parameters(): array
    {
        return ['production_line_id' => $this->line->id, 'beam_speed' => 10, 'loading_mode' => 'custom', 'custom_loading_mode' => 'Trial mode', 'target_dose' => 5, 'freq' => 100, 'scan_gear' => 2];
    }
}
