<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['type' => 'sales', 'format' => 'print']))
            ->assertOk();
    }
}
