<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PdfUrlFetcherService
{
    /**
     * Maximum allowed download size (100 MB in bytes).
     */
    public const MAX_FILE_SIZE_BYTES = 104857600; // 100 * 1024 * 1024

    /**
     * Maximum number of allowed redirects.
     */
    public const MAX_REDIRECTS = 5;

    /**
     * Connect timeout in seconds.
     */
    public const CONNECT_TIMEOUT = 5;

    /**
     * Total request timeout in seconds.
     */
    public const TIMEOUT = 30;

    /**
     * Optional custom DNS resolver callable for testing.
     * signature: fn(string $host): array<string>
     *
     * @var (callable(string): array<string>)|null
     */
    protected $dnsResolver = null;

    /**
     * Optional custom HTTP transport callable for testing.
     * signature: fn(string $url, string $tempFilePath, array $options): array{statusCode: int, headers: array, error?: string}
     *
     * @var callable|null
     */
    protected $httpTransport = null;

    /**
     * Set a custom DNS resolver (primarily for testing).
     */
    public function setDnsResolver(?callable $resolver): self
    {
        $this->dnsResolver = $resolver;
        return $this;
    }

    /**
     * Set a custom HTTP transport (primarily for testing).
     */
    public function setHttpTransport(?callable $transport): self
    {
        $this->httpTransport = $transport;
        return $this;
    }

    /**
     * Fetch a PDF file from a public URL securely with full SSRF protection.
     *
     * @param  string  $rawUrl
     * @return array{
     *     tempId: string,
     *     filePath: string,
     *     fileName: string,
     *     fileSize: int,
     *     fileSizeFormatted: string
     * }
     *
     * @throws Exception
     */
    public function fetch(string $rawUrl): array
    {
        $this->purgeStaleTempFiles();

        $rawUrl = trim($rawUrl);
        if (empty($rawUrl)) {
            throw new Exception('URL tidak boleh kosong.');
        }

        // 1. URL Normalization (Google Drive, Dropbox, OneDrive)
        $normalizedUrl = $this->normalizeUrl($rawUrl);

        $tempDir = storage_path('app/temp');
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
        }

        $tempDownloadPath = $tempDir . DIRECTORY_SEPARATOR . 'download_' . Str::random(32) . '.tmp';

        try {
            $fetchResult = $this->executeSecureDownload($normalizedUrl, $tempDownloadPath);

            // Verify downloaded file size
            if (!file_exists($tempDownloadPath) || filesize($tempDownloadPath) === 0) {
                throw new Exception('File yang diunduh kosong atau tidak dapat diakses.');
            }

            $fileSize = filesize($tempDownloadPath);
            if ($fileSize > self::MAX_FILE_SIZE_BYTES) {
                throw new Exception('Ukuran file melebihi batas maksimum 100 MB.');
            }

            // 2. Validate PDF Magic Bytes (%PDF-)
            $this->validatePdfMagicBytes($tempDownloadPath);

            // Determine safe file name
            $safeFileName = $this->determineFileName($fetchResult['contentDisposition'] ?? '', $fetchResult['finalUrl'] ?? $normalizedUrl);

            // Generate unpredictable token and permanent temp location
            $tempId = Str::random(40);
            $finalPath = $tempDir . DIRECTORY_SEPARATOR . 'url_import_' . $tempId . '.pdf';

            if (!rename($tempDownloadPath, $finalPath)) {
                throw new Exception('Gagal menyimpan file PDF sementara.');
            }

            return [
                'tempId' => $tempId,
                'filePath' => $finalPath,
                'fileName' => $safeFileName,
                'fileSize' => $fileSize,
                'fileSizeFormatted' => $this->formatBytes($fileSize),
            ];
        } catch (Exception $e) {
            // Guarantee cleanup on failure
            if (file_exists($tempDownloadPath)) {
                @unlink($tempDownloadPath);
            }

            // Safe user-friendly error message, never expose internal exception or paths
            throw new Exception($this->sanitizeErrorMessage($e->getMessage()));
        }
    }

    /**
     * Download with manual redirect handling and strict SSRF / DNS checks on every hop.
     *
     * @return array{
     *     statusCode: int,
     *     finalUrl: string,
     *     contentType: string,
     *     contentDisposition: string
     * }
     *
     * @throws Exception
     */
    protected function executeSecureDownload(string $initialUrl, string $destinationPath): array
    {
        $currentUrl = $initialUrl;
        $redirectCount = 0;

        while (true) {
            // Validate Scheme, Host, and IPs for current URL
            $urlParts = $this->validateAndResolveUrl($currentUrl);

            // If a custom HTTP transport is provided (testing), use it
            if ($this->httpTransport !== null) {
                $transport = $this->httpTransport;
                $res = $transport($currentUrl, $destinationPath, [
                    'resolvedIps' => $urlParts['ips'],
                    'port' => $urlParts['port'],
                    'host' => $urlParts['host'],
                ]);

                $statusCode = $res['statusCode'] ?? 200;
                $headers = $res['headers'] ?? [];

                if (isset($res['error'])) {
                    throw new Exception($res['error']);
                }

                if (in_array($statusCode, [301, 302, 303, 307, 308], true)) {
                    $redirectCount++;
                    if ($redirectCount > self::MAX_REDIRECTS) {
                        throw new Exception('Terlalu banyak pengalihan (redirect).');
                    }
                    $location = $headers['location'] ?? $headers['Location'] ?? null;
                    if (!$location) {
                        throw new Exception('Pengalihan tanpa header Location.');
                    }
                    $currentUrl = $this->resolveRedirectUrl($currentUrl, $location);
                    if (file_exists($destinationPath)) {
                        @unlink($destinationPath);
                    }
                    continue;
                }

                if ($statusCode !== 200) {
                    throw new Exception("Server mengembalikan status HTTP {$statusCode}.");
                }

                return [
                    'statusCode' => $statusCode,
                    'finalUrl' => $currentUrl,
                    'contentType' => $headers['content-type'] ?? $headers['Content-Type'] ?? '',
                    'contentDisposition' => $headers['content-disposition'] ?? $headers['Content-Disposition'] ?? '',
                ];
            }

            // Real cURL execution with pinned IP and strict streaming limits
            $ch = curl_init();
            $fp = fopen($destinationPath, 'wb');
            if (!$fp) {
                throw new Exception('Gagal membuat file sementara.');
            }

            $responseHeaders = [];
            $downloadAbortedReason = null;
            $downloadedBytesCount = 0;

            // Pin host:port to verified public IP to prevent DNS rebinding
            $targetIp = $urlParts['ips'][0];
            $resolveEntry = "{$urlParts['host']}:{$urlParts['port']}:{$targetIp}";

            curl_setopt($ch, CURLOPT_URL, $currentUrl);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Manually handle redirects!
            curl_setopt($ch, CURLOPT_RESOLVE, [$resolveEntry]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ToolsDKV-PdfFetcher/1.0');

            // Header callback: examine headers as they arrive
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curlHandle, $headerLine) use (&$responseHeaders, &$downloadAbortedReason) {
                $len = strlen($headerLine);
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $headerName = strtolower(trim($parts[0]));
                    $headerVal = trim($parts[1]);
                    $responseHeaders[$headerName] = $headerVal;

                    // Abort immediately if Content-Length exceeds maximum
                    if ($headerName === 'content-length') {
                        $contentLength = (int) $headerVal;
                        if ($contentLength > self::MAX_FILE_SIZE_BYTES) {
                            $downloadAbortedReason = 'Ukuran file melebihi batas maksimum 100 MB.';
                            return 0; // Returning 0 causes cURL to abort download
                        }
                    }
                }
                return $len;
            });

            // Progress callback: hard abort if bytes streamed exceed 100 MB (even without Content-Length)
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($curlHandle, $dlTotal, $dlNow, $ulTotal, $ulNow) use (&$downloadAbortedReason, &$downloadedBytesCount) {
                $downloadedBytesCount = (int) $dlNow;
                if ($downloadedBytesCount > self::MAX_FILE_SIZE_BYTES) {
                    $downloadAbortedReason = 'Ukuran file melebihi batas maksimum 100 MB.';
                    return 1; // Non-zero return value aborts cURL transfer
                }
                return 0;
            });

            $curlSuccess = curl_exec($ch);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            fclose($fp);
            curl_close($ch);

            if ($downloadAbortedReason !== null) {
                throw new Exception($downloadAbortedReason);
            }

            if (!$curlSuccess && $curlErrno !== 0) {
                throw new Exception('Gagal menghubungi server URL tujuan: ' . ($curlError ?: 'Koneksi gagal atau timeout.'));
            }

            // Check if redirect
            if (in_array($httpCode, [301, 302, 303, 307, 308], true)) {
                $redirectCount++;
                if ($redirectCount > self::MAX_REDIRECTS) {
                    throw new Exception('Terlalu banyak pengalihan (redirect).');
                }

                $location = $responseHeaders['location'] ?? null;
                if (!$location) {
                    throw new Exception('Pengalihan tanpa header Location.');
                }

                $currentUrl = $this->resolveRedirectUrl($currentUrl, $location);

                // Clean up partial download before following redirect
                if (file_exists($destinationPath)) {
                    @unlink($destinationPath);
                }
                continue;
            }

            if ($httpCode !== 200) {
                throw new Exception("Server mengembalikan status HTTP {$httpCode}.");
            }

            return [
                'statusCode' => $httpCode,
                'finalUrl' => $currentUrl,
                'contentType' => $responseHeaders['content-type'] ?? '',
                'contentDisposition' => $responseHeaders['content-disposition'] ?? '',
            ];
        }
    }

    /**
     * Validate Scheme, Host, and resolve DNS IPs, ensuring NO private/reserved/cloud metadata IPs.
     *
     * @return array{
     *     scheme: string,
     *     host: string,
     *     port: int,
     *     ips: array<string>
     * }
     *
     * @throws Exception
     */
    public function validateAndResolveUrl(string $url): array
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme'])) {
            throw new Exception('URL tidak valid.');
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new Exception('Skema URL tidak diizinkan. Hanya http dan https yang didukung.');
        }

        if (empty($parsed['host'])) {
            throw new Exception('URL tidak valid atau tidak memiliki host.');
        }

        $host = strtolower(trim($parsed['host']));
        $port = isset($parsed['port']) ? (int) $parsed['port'] : ($scheme === 'https' ? 443 : 80);

        // Host name checks
        if ($this->isForbiddenHostname($host)) {
            throw new Exception('Akses ke host lokal atau terlarang tidak diizinkan.');
        }

        // Resolve DNS
        $ips = $this->resolveHost($host);
        if (empty($ips)) {
            throw new Exception('Domain tidak dapat dihubungi atau tidak valid.');
        }

        // Validate EVERY resolved IP address
        foreach ($ips as $ip) {
            if ($this->isPrivateOrReservedIp($ip)) {
                throw new Exception('Akses ke alamat IP privat atau lokal tidak diizinkan.');
            }
        }

        return [
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'ips' => $ips,
        ];
    }

    /**
     * Check if a hostname is forbidden (localhost, cloud metadata, etc.).
     */
    public function isForbiddenHostname(string $host): bool
    {
        $host = strtolower(trim($host));

        $forbiddenExact = [
            'localhost',
            'metadata.google.internal',
            'instance-data',
            'metadata',
        ];

        if (in_array($host, $forbiddenExact, true)) {
            return true;
        }

        if (str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return true;
        }

        // Direct IP as host
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPrivateOrReservedIp($host);
        }

        return false;
    }

    /**
     * Resolve host to all available IPv4 and IPv6 addresses.
     *
     * @return array<string>
     */
    public function resolveHost(string $host): array
    {
        if ($this->dnsResolver !== null) {
            return ($this->dnsResolver)($host);
        }

        // If host is already an IP
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $ips = [];

        // Try dns_get_record for A and AAAA
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ip'])) {
                    $ips[] = $record['ip'];
                } elseif (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        // Fallback to gethostbynamel for IPv4
        if (empty($ips)) {
            $v4List = @gethostbynamel($host);
            if (is_array($v4List)) {
                $ips = array_merge($ips, $v4List);
            }
        }

        return array_values(array_unique($ips));
    }

    /**
     * Check whether an IP is private, loopback, link-local, multicast, cloud metadata, or reserved.
     */
    public function isPrivateOrReservedIp(string $ip): bool
    {
        $ip = trim($ip);

        // Check for IPv4-mapped IPv6 (e.g. ::ffff:127.0.0.1)
        if (str_starts_with(strtolower($ip), '::ffff:')) {
            $extractedV4 = substr($ip, 7);
            if (filter_var($extractedV4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $this->isPrivateOrReservedIp($extractedV4);
            }
        }

        // PHP built-in check for private and reserved ranges
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        // IPv4 specific CIDRs & Cloud Metadata checks
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $restrictedCidrs = [
                '0.0.0.0/8',          // Current network
                '10.0.0.0/8',         // Private-use
                '100.64.0.0/10',      // Shared / CGNAT
                '127.0.0.0/8',        // Loopback
                '169.254.0.0/16',     // Link-local / Cloud metadata (AWS, GCP, Azure 169.254.169.254)
                '172.16.0.0/12',      // Private-use
                '192.0.0.0/24',       // IETF protocol assignments
                '192.0.2.0/24',       // Documentation
                '192.88.99.0/24',     // 6to4 relay anycast
                '192.168.0.0/16',     // Private-use
                '198.18.0.0/15',      // Benchmarking
                '198.51.100.0/24',    // Documentation
                '203.0.113.0/24',     // Documentation
                '224.0.0.0/4',        // Multicast
                '240.0.0.0/4',        // Reserved
                '255.255.255.255/32', // Broadcast
            ];

            foreach ($restrictedCidrs as $cidr) {
                if ($this->ipMatchesCidr($ip, $cidr)) {
                    return true;
                }
            }

            if ($ip === '169.254.169.254') {
                return true;
            }
        }

        // IPv6 specific checks
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $lower = strtolower($ip);
            if ($lower === '::1' || $lower === '::') {
                return true;
            }
            // Unique local unicast (fc00::/7) or link-local (fe80::/10)
            if (str_starts_with($lower, 'fc') || str_starts_with($lower, 'fd') || str_starts_with($lower, 'fe8') || str_starts_with($lower, 'fe9') || str_starts_with($lower, 'fea') || str_starts_with($lower, 'feb')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper to test if an IPv4 matches a CIDR range.
     */
    protected function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - (int) $mask);
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Validate the file contains PDF magic bytes (%PDF-) within the initial header bytes.
     *
     * @throws Exception
     */
    public function validatePdfMagicBytes(string $filePath): void
    {
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            throw new Exception('Gagal membaca file yang diunduh.');
        }

        $headerSample = fread($handle, 1024);
        fclose($handle);

        if ($headerSample === false || strlen($headerSample) < 5) {
            throw new Exception('File yang diunduh terlalu kecil atau rusak.');
        }

        // Detect if content is HTML / XML / JSON
        $lowerSample = strtolower($headerSample);
        if (
            str_contains($lowerSample, '<!doctype html') ||
            str_contains($lowerSample, '<html') ||
            str_contains($lowerSample, '<?xml') ||
            str_starts_with(trim($lowerSample), '{"') ||
            str_starts_with(trim($lowerSample), '[{"')
        ) {
            throw new Exception('Tautan mengembalikan halaman web/HTML dan bukan dokumen PDF.');
        }

        // Must contain %PDF- in the header chunk
        if (strpos($headerSample, '%PDF-') === false) {
            throw new Exception('File yang diunduh bukan dokumen PDF yang valid (magic bytes tidak sesuai).');
        }
    }

    /**
     * Normalize URLs for Google Drive, Dropbox, and OneDrive.
     */
    public function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return $url;
        }

        $host = strtolower($parsed['host']);

        // Google Drive public share conversion to direct download
        if (str_contains($host, 'drive.google.com')) {
            if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
                return "https://drive.google.com/uc?export=download&id=" . $matches[1];
            }
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (!empty($queryParams['id'])) {
                    return "https://drive.google.com/uc?export=download&id=" . $queryParams['id'];
                }
            }
        }

        // Dropbox public share conversion from dl=0 to dl=1
        if (str_contains($host, 'dropbox.com')) {
            if (str_contains($url, 'dl=0')) {
                return str_replace('dl=0', 'dl=1', $url);
            }
            if (!str_contains($url, 'dl=1')) {
                $separator = str_contains($url, '?') ? '&' : '?';
                return $url . $separator . 'dl=1';
            }
        }

        return $url;
    }

    /**
     * Resolve a redirect location (handles relative and absolute URLs).
     */
    protected function resolveRedirectUrl(string $currentUrl, string $location): string
    {
        $location = trim($location);

        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parsed = parse_url($currentUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        if (str_starts_with($location, '/')) {
            return "{$scheme}://{$host}{$port}{$location}";
        }

        $path = $parsed['path'] ?? '/';
        $dir = rtrim(dirname($path), '/\\');
        return "{$scheme}://{$host}{$port}{$dir}/{$location}";
    }

    /**
     * Extract a safe filename from Content-Disposition or URL path.
     */
    protected function determineFileName(string $contentDisposition, string $url): string
    {
        $fileName = '';

        if (!empty($contentDisposition)) {
            if (preg_match('/filename\*=UTF-8\'\'([^;]+)/i', $contentDisposition, $matches)) {
                $fileName = rawurldecode(trim($matches[1], '"\' '));
            } elseif (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $matches)) {
                $fileName = trim($matches[1], '"\' ');
            }
        }

        if (empty($fileName)) {
            $path = parse_url($url, PHP_URL_PATH);
            if (!empty($path)) {
                $base = basename($path);
                if (str_ends_with(strtolower($base), '.pdf')) {
                    $fileName = $base;
                }
            }
        }

        if (empty($fileName)) {
            $fileName = 'document_' . date('Ymd_His') . '.pdf';
        }

        // Sanitize
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $slug = Str::slug($baseName) ?: 'downloaded-pdf';
        return $slug . '.pdf';
    }

    /**
     * Purge stale fetched files older than 15 minutes.
     */
    public function purgeStaleTempFiles(): void
    {
        $tempDir = storage_path('app/temp');
        if (!File::isDirectory($tempDir)) {
            return;
        }

        $files = File::files($tempDir);
        $threshold = time() - 900; // 15 minutes

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ((str_starts_with($filename, 'url_import_') || str_starts_with($filename, 'download_')) && $file->getMTime() < $threshold) {
                @unlink($file->getRealPath());
            }
        }
    }

    /**
     * Safe user-facing error message sanitizer.
     */
    protected function sanitizeErrorMessage(string $msg): string
    {
        // Whitelist common safe messages
        $safeKeywords = [
            'URL tidak boleh kosong',
            'URL tidak valid',
            'Skema URL tidak diizinkan',
            'Akses ke host lokal',
            'Akses ke alamat IP privat',
            'Domain tidak dapat dihubungi',
            'Ukuran file melebihi',
            'magic bytes tidak sesuai',
            'halaman web/HTML',
            'bukan dokumen PDF yang valid',
            'Terlalu banyak pengalihan',
            'Server mengembalikan status HTTP',
            'Koneksi gagal atau timeout',
        ];

        foreach ($safeKeywords as $keyword) {
            if (str_contains($msg, $keyword)) {
                return $msg;
            }
        }

        return 'Gagal mengambil file dari URL. Pastikan URL publik valid dan file berupa dokumen PDF.';
    }

    /**
     * Format bytes into human readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 Bytes';
        $units = ['Bytes', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
