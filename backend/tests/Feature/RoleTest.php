<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_receives_403_forbidden_on_admin_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'USER',
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);

        $responseUsers = $this->actingAs($user)->get('/admin/users');
        $responseUsers->assertStatus(403);

        $responsePlans = $this->actingAs($user)->get('/admin/plans');
        $responsePlans->assertStatus(403);

        $responsePayments = $this->actingAs($user)->get('/admin/payments');
        $responsePayments->assertStatus(403);

        $responseSubs = $this->actingAs($user)->get('/admin/subscriptions');
        $responseSubs->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard_and_panels(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'unlimited' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('ADMIN PANEL');

        $responseUsers = $this->actingAs($admin)->get('/admin/users');
        $responseUsers->assertStatus(200);
        $responseUsers->assertSee('Manajemen Pengguna');

        $responsePlans = $this->actingAs($admin)->get('/admin/plans');
        $responsePlans->assertStatus(200);
        $responsePlans->assertSee('Manajemen Paket (Plans)');

        $responsePayments = $this->actingAs($admin)->get('/admin/payments');
        $responsePayments->assertStatus(200);
        $responsePayments->assertSee('Riwayat Transaksi Pembayaran');

        $responseSubs = $this->actingAs($admin)->get('/admin/subscriptions');
        $responseSubs->assertStatus(200);
        $responseSubs->assertSee('Daftar Langganan Pengguna');
    }

    public function test_admin_can_update_user_role_and_entitlements(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $targetUser = User::factory()->create([
            'email' => 'target@example.com',
            'role' => 'USER',
            'unlimited' => false,
            'daily_limit' => null,
        ]);

        $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
            'name' => 'Target Updated',
            'email' => 'target@example.com',
            'role' => 'ADMIN',
            'plan_id' => $proPlan->id,
            'daily_limit' => 50,
            'unlimited' => '1',
        ]);

        $response->assertRedirect('/admin/users');
        $targetUser->refresh();

        $this->assertEquals('Target Updated', $targetUser->name);
        $this->assertEquals('ADMIN', $targetUser->role);
        $this->assertEquals($proPlan->id, $targetUser->plan_id);
        $this->assertEquals(50, $targetUser->daily_limit);
        $this->assertTrue($targetUser->unlimited);
    }
}
