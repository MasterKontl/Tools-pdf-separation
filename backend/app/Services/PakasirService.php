<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class PakasirService
{
    protected string $slug;
    protected string $apiKey;
    protected string $mode;
    protected string $baseUrl;

    /**
     * Optional custom verifier for testing.
     * signature: fn(string $orderId, float $amount): array
     *
     * @var callable|null
     */
    protected $customVerifier = null;

    public function __construct()
    {
        $this->slug = (string) config('services.pakasir.slug', env('PAKASIR_SLUG', 'tools-dkv'));
        $this->apiKey = (string) config('services.pakasir.api_key', env('PAKASIR_API_KEY', ''));
        $this->mode = (string) config('services.pakasir.mode', env('PAKASIR_MODE', 'sandbox'));
        $this->baseUrl = 'https://app.pakasir.com';
    }

    /**
     * Set a custom verifier callable for testing.
     */
    public function setCustomVerifier(?callable $verifier): self
    {
        $this->customVerifier = $verifier;
        return $this;
    }

    /**
     * Generate official Pakasir checkout redirect URL.
     * Format: https://app.pakasir.com/pay/{slug}/{amount}?order_id={order_id}&redirect={redirect_url}
     */
    public function buildCheckoutUrl(string $orderId, float $amount, ?string $redirectUrl = null): string
    {
        $amountInt = (int) round($amount);
        $encodedSlug = rawurlencode($this->slug);
        $url = "{$this->baseUrl}/pay/{$encodedSlug}/{$amountInt}?order_id=" . rawurlencode($orderId);

        if ($redirectUrl) {
            $url .= '&redirect=' . rawurlencode($redirectUrl);
        }

        return $url;
    }

    /**
     * Verify transaction directly with Pakasir official API.
     * GET https://app.pakasir.com/api/transactiondetail?project={slug}&amount={amount}&order_id={order_id}&api_key={api_key}
     *
     * @return array{
     *     verified: bool,
     *     status: string,
     *     order_id: string,
     *     amount: float,
     *     raw: array
     * }
     *
     * @throws Exception
     */
    public function verifyTransaction(string $orderId, float $amount): array
    {
        if ($this->customVerifier !== null) {
            return ($this->customVerifier)($orderId, $amount);
        }

        $amountInt = (int) round($amount);

        // If no API key configured (development/sandbox mock fallback)
        if (empty($this->apiKey)) {
            if (app()->environment('production')) {
                throw new Exception('Sistem pembayaran Pakasir belum dikonfigurasi (API key tidak ditemukan).');
            }

            // Only in local/testing environment without key, allow simulation
            return [
                'verified' => false,
                'status' => 'unconfigured',
                'order_id' => $orderId,
                'amount' => (float) $amountInt,
                'raw' => ['simulated' => false, 'reason' => 'missing_api_key'],
            ];
        }

        $response = Http::timeout(10)->get("{$this->baseUrl}/api/transactiondetail", [
            'project' => $this->slug,
            'amount' => $amountInt,
            'order_id' => $orderId,
            'api_key' => $this->apiKey,
        ]);

        if (!$response->successful()) {
            throw new Exception("Gagal menghubungi server Pakasir (HTTP {$response->status()}).");
        }

        $data = $response->json() ?: [];
        $status = strtolower((string) ($data['status'] ?? ''));

        $isPaid = in_array($status, ['completed', 'paid', 'success', 'settled'], true);

        return [
            'verified' => $isPaid,
            'status' => $status,
            'order_id' => (string) ($data['order_id'] ?? $orderId),
            'amount' => (float) ($data['amount'] ?? $amountInt),
            'raw' => $data,
        ];
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
