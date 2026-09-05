<?php

namespace Tests\Feature;

use App\Exceptions\QuotaExceededException;
use App\Models\ConversionUsage;
use App\Models\Plan;
use App\Models\User;
use App\Services\QuotaService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuotaTest extends TestCase
{
    use RefreshDatabase;

    protected QuotaService $quotaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->quotaService = app(QuotaService::class);
    }

    public function test_entitlement_precedence_rules(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $studentPlan = Plan::where('slug', 'student')->first();

        // 1. User with unlimited = true always gets unlimited, regardless of plan or daily_limit
        $unlimitedUser = User::factory()->create([
            'plan_id' => $freePlan->id,
            'daily_limit' => 5,
            'unlimited' => true,
        ]);
        $entitlement = $this->quotaService->resolveEntitlement($unlimitedUser);
        $this->assertTrue($entitlement['unlimited']);
        $this->assertNull($entitlement['daily_limit']);

        // 2. User with custom daily_limit overrides plan limit
        $customLimitUser = User::factory()->create([
            'plan_id' => $freePlan->id, // free is 3
            'daily_limit' => 15,
            'unlimited' => false,
        ]);
        $entitlement = $this->quotaService->resolveEntitlement($customLimitUser);
        $this->assertFalse($entitlement['unlimited']);
        $this->assertEquals(15, $entitlement['daily_limit']);

        // 3. User with plan and no custom limit uses plan limit
        $planUser = User::factory()->create([
            'plan_id' => $studentPlan->id, // student is 20
            'daily_limit' => null,
            'unlimited' => false,
        ]);
        $entitlement = $this->quotaService->resolveEntitlement($planUser);
        $this->assertFalse($entitlement['unlimited']);
        $this->assertEquals(20, $entitlement['daily_limit']);

        // 4. Default fallback when no plan and no custom limit
        $defaultUser = User::factory()->create([
            'plan_id' => null,
            'daily_limit' => null,
            'unlimited' => false,
        ]);
        $entitlement = $this->quotaService->resolveEntitlement($defaultUser);
        $this->assertFalse($entitlement['unlimited']);
        $this->assertEquals(3, $entitlement['daily_limit']);
    }

    public function test_free_user_can_convert_up_to_daily_limit_and_is_blocked_on_exceed(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $user = User::factory()->create([
            'plan_id' => $freePlan->id,
            'daily_limit' => null,
            'unlimited' => false,
        ]);

        $this->assertTrue($this->quotaService->canConvert($user));

        // Use up 3 slots
        $r1 = $this->quotaService->reserveQuotaSlot($user);
        $r1->finalize();
        $this->assertEquals(1, $this->quotaService->getUsageCount($user));

        $r2 = $this->quotaService->reserveQuotaSlot($user);
        $r2->finalize();
        $this->assertEquals(2, $this->quotaService->getUsageCount($user));

        $r3 = $this->quotaService->reserveQuotaSlot($user);
        $r3->finalize();
        $this->assertEquals(3, $this->quotaService->getUsageCount($user));

        // Limit reached: canConvert is now false
        $this->assertFalse($this->quotaService->canConvert($user));

        // Reserving 4th slot throws QuotaExceededException
        $this->expectException(QuotaExceededException::class);
        $this->quotaService->reserveQuotaSlot($user);
    }

    public function test_quota_slot_reservation_is_rolled_back_on_failure(): void
    {
        $user = User::factory()->create([
            'daily_limit' => 3,
            'unlimited' => false,
        ]);

        $this->assertEquals(0, $this->quotaService->getUsageCount($user));

        // Reserve slot
        $reservation = $this->quotaService->reserveQuotaSlot($user);
        $this->assertEquals(1, $this->quotaService->getUsageCount($user));

        // Simulate conversion failure -> release
        $reservation->release();
        $this->assertEquals(0, $this->quotaService->getUsageCount($user));
    }

    public function test_unlimited_user_can_reserve_indefinitely(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'unlimited' => true,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($this->quotaService->canConvert($admin));
            $res = $this->quotaService->reserveQuotaSlot($admin);
            $res->finalize();
        }

        $this->assertEquals(10, $this->quotaService->getUsageCount($admin));
        $this->assertTrue($this->quotaService->canConvert($admin));
    }

    public function test_convert_endpoint_rejects_when_quota_exhausted(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $user = User::factory()->create([
            'plan_id' => $freePlan->id,
            'daily_limit' => 3,
            'unlimited' => false,
        ]);

        // Manually fill user quota for today
        ConversionUsage::create([
            'user_id' => $user->id,
            'usage_date' => now()->toDateString(),
            'count' => 3,
        ]);

        $pdf = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post('/convert', [
            'pdf' => $pdf,
            'format' => 'png',
            'dpi' => 150,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Batas harian konversi tercapai', session('error'));
    }

    /**
     * Helper to create a valid minimal 1-page PDF file.
     */
    protected function createValidPdfFile(): UploadedFile
    {
        $body = "%PDF-1.4\n" .
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n" .
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n" .
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 100 100] /Contents 4 0 R >> endobj\n" .
            "4 0 obj << /Length 27 >> stream\nBT /F1 12 Tf 10 80 Td (Test) Tj ET\nendstream endobj\n" .
            "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000216 00000 n \n" .
            "trailer << /Size 5 /Root 1 0 R >>\nstartxref\n293\n%%EOF";

        $tempPath = tempnam(sys_get_temp_dir(), 'quota_test_') . '.pdf';
        file_put_contents($tempPath, $body);

        return new UploadedFile($tempPath, 'document.pdf', 'application/pdf', null, true);
    }

    public function test_guest_quota_allows_first_conversion_and_rejects_second_on_same_day(): void
    {
        // 1. First conversion as guest -> PASS (HTTP 200)
        $file1 = $this->createValidPdfFile();
        $response1 = $this->post('/convert', [
            'pdf' => $file1,
            'format' => 'png',
            'dpi' => 150,
        ]);
        $response1->assertStatus(200);

        // 2. Second conversion as guest on the same day -> REJECT
        $file2 = $this->createValidPdfFile();
        $response2 = $this->post('/convert', [
            'pdf' => $file2,
            'format' => 'png',
            'dpi' => 150,
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('Batas kuota harian tamu tercapai', session('error'));
    }

    public function test_registered_user_quota_allows_three_conversions_and_rejects_fourth(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $user = User::factory()->create([
            'plan_id' => $freePlan->id,
            'daily_limit' => null,
            'unlimited' => false,
        ]);

        // Conversion 1 -> PASS
        $f1 = $this->createValidPdfFile();
        $r1 = $this->actingAs($user)->post('/convert', ['pdf' => $f1, 'format' => 'png', 'dpi' => 150]);
        $r1->assertStatus(200);
        $this->assertEquals(1, $this->quotaService->getUsageCount($user));

        // Conversion 2 -> PASS
        $f2 = $this->createValidPdfFile();
        $r2 = $this->actingAs($user)->post('/convert', ['pdf' => $f2, 'format' => 'png', 'dpi' => 150]);
        $r2->assertStatus(200);
        $this->assertEquals(2, $this->quotaService->getUsageCount($user));

        // Conversion 3 -> PASS
        $f3 = $this->createValidPdfFile();
        $r3 = $this->actingAs($user)->post('/convert', ['pdf' => $f3, 'format' => 'png', 'dpi' => 150]);
        $r3->assertStatus(200);
        $this->assertEquals(3, $this->quotaService->getUsageCount($user));

        // Conversion 4 -> REJECT
        $f4 = $this->createValidPdfFile();
        $r4 = $this->actingAs($user)->post('/convert', ['pdf' => $f4, 'format' => 'png', 'dpi' => 150]);
        $r4->assertRedirect();
        $r4->assertSessionHas('error');
        $this->assertStringContainsString('Batas harian konversi tercapai', session('error'));
        $this->assertEquals(3, $this->quotaService->getUsageCount($user));
    }

    public function test_dpi_restrictions_guest_and_user_rejected_admin_allowed(): void
    {
        // 1. Guest with 600 DPI -> REJECT
        $guestPdf = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $guestResp = $this->post('/convert', [
            'pdf' => $guestPdf,
            'format' => 'png',
            'dpi' => 600,
        ]);
        $guestResp->assertSessionHasErrors('dpi');

        // 2. Regular registered USER with 600 DPI -> REJECT
        $user = User::factory()->create([
            'role' => 'USER',
            'unlimited' => false,
        ]);
        $userPdf = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $userResp = $this->actingAs($user)->post('/convert', [
            'pdf' => $userPdf,
            'format' => 'png',
            'dpi' => 600,
        ]);
        $userResp->assertSessionHasErrors('dpi');

        // 3. ADMIN with 600 DPI -> ALLOWED (200 OK)
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'unlimited' => true,
        ]);
        $adminPdf = $this->createValidPdfFile();
        $adminResp = $this->actingAs($admin)->post('/convert', [
            'pdf' => $adminPdf,
            'format' => 'png',
            'dpi' => 600,
        ]);
        $adminResp->assertStatus(200);
        $adminResp->assertSessionHasNoErrors();
    }
}
