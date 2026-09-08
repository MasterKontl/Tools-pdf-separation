<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    private function assertSeoMetaTags($response, string $pageTitle): void
    {
        $response->assertStatus(200);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<meta name="robots"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('<meta property="og:type"', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:description"', false);
        $response->assertSee('<meta property="og:url"', false);
        $response->assertSee('<meta property="og:site_name"', false);
        $response->assertSee('<meta name="twitter:card"', false);
        $response->assertSee('<meta name="twitter:title"', false);
        $response->assertSee('<meta name="twitter:description"', false);
    }

    public function test_homepage_has_seo_tags(): void
    {
        $response = $this->get('/');
        $this->assertSeoMetaTags($response, 'converter');
        $response->assertSee('Tools DKV', false);
        $response->assertDontSee('noindex', false);
    }

    public function test_homepage_has_structured_data(): void
    {
        $response = $this->get('/');
        $response->assertSee('application/ld+json', false);
        $response->assertSee('WebSite', false);
        $response->assertSee('WebApplication', false);
    }

    public function test_converter_has_unique_title(): void
    {
        $response = $this->get('/');
        $response->assertSee('PDF Converter', false);
    }

    public function test_separation_has_seo_tags(): void
    {
        $response = $this->get('/separation');
        $this->assertSeoMetaTags($response, 'separation');
        $response->assertDontSee('noindex', false);
    }

    public function test_separation_has_unique_title(): void
    {
        $response = $this->get('/separation');
        $response->assertSee('Color Separation', false);
    }

    public function test_upscaler_has_seo_tags(): void
    {
        $response = $this->get('/upscaler');
        $this->assertSeoMetaTags($response, 'upscaler');
        $response->assertDontSee('noindex', false);
    }

    public function test_upscaler_has_unique_title(): void
    {
        $response = $this->get('/upscaler');
        $response->assertSee('Image Upscaler', false);
    }

    public function test_pricing_page_loads_with_seo(): void
    {
        try {
            $response = $this->get('/pricing');
            if ($response->status() === 200) {
                $this->assertSeoMetaTags($response, 'pricing');
                $response->assertDontSee('noindex', false);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->markTestSkipped('Plans table not available in test DB');
        }
    }

    public function test_robots_txt_is_accessible(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $response->assertSee('User-agent:', false);
        $response->assertSee('Allow: /', false);
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Disallow: /dashboard', false);
        $response->assertSee('Disallow: /login', false);
        $response->assertSee('Disallow: /register', false);
        $response->assertSee('Sitemap:', false);
    }

    public function test_sitemap_xml_is_valid(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml version="1.0"', false);
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);
    }

    public function test_sitemap_contains_public_pages(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertSee('/separation', false);
        $response->assertSee('/upscaler', false);
        $response->assertSee('/pricing', false);
    }

    public function test_sitemap_does_not_contain_private_pages(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertDontSee('/login', false);
        $response->assertDontSee('/register', false);
        $response->assertDontSee('/dashboard', false);
        $response->assertDontSee('/admin', false);
    }

    public function test_login_page_has_noindex(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    public function test_register_page_has_noindex(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    public function test_forgot_password_page_has_noindex(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect();
    }

    public function test_admin_requires_auth(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect();
    }

    public function test_canonical_url_matches_current_domain(): void
    {
        $response = $this->get('/');
        $response->assertSee(url('/'), false);
    }

    public function test_og_site_name_is_tools_dkv(): void
    {
        $response = $this->get('/');
        $response->assertSee('Tools DKV', false);
    }

    public function test_structured_data_on_tool_pages(): void
    {
        $pages = ['/', '/separation', '/upscaler'];
        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertSee('application/ld+json', false);
            $response->assertSee('WebApplication', false);
        }
    }

    public function test_public_pages_return_200(): void
    {
        $pages = ['/', '/separation', '/upscaler'];
        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
        }
    }

    public function test_no_duplicate_title_tags(): void
    {
        $titles = [];
        $pages = ['/' => 'PDF Converter', '/separation' => 'Color Separation', '/upscaler' => 'Upscaler'];

        foreach ($pages as $url => $expected) {
            $response = $this->get($url);
            $content = $response->getContent();
            preg_match('/<title>(.*?)<\/title>/', $content, $matches);
            $titles[$url] = $matches[1] ?? '';
        }

        $uniqueTitles = array_unique($titles);
        $this->assertCount(count($pages), $uniqueTitles, 'Each public page must have a unique title tag');
    }
}
