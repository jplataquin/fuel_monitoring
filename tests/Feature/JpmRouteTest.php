<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JpmRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the JPM application main index loads successfully and has the base URL injected.
     */
    public function test_jpm_index_loads_with_base_url(): void
    {
        $response = $this->get('/jpm8000');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<base href="/jpm8000/">', false);
    }

    /**
     * Test that trailing slash loads index.html too.
     */
    public function test_jpm_index_loads_with_trailing_slash(): void
    {
        $response = $this->get('/jpm8000/');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<base href="/jpm8000/">', false);
    }

    /**
     * Test that static CSS files are served with correct MIME type.
     */
    public function test_jpm_css_assets_are_served(): void
    {
        $response = $this->get('/jpm8000/style.css');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }

    /**
     * Test that WASM files are served with the crucial application/wasm MIME type.
     */
    public function test_jpm_wasm_assets_are_served(): void
    {
        $response = $this->get('/jpm8000/public/dsp_wasm_bg.wasm');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/wasm');
    }

    /**
     * Test that JS assets are served with correct MIME type.
     */
    public function test_jpm_js_assets_are_served(): void
    {
        $response = $this->get('/jpm8000/src/main.js');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript');
    }

    /**
     * Test that non-existent files return 404.
     */
    public function test_jpm_invalid_asset_returns_404(): void
    {
        $response = $this->get('/jpm8000/non_existent_file.xyz');

        $response->assertStatus(404);
    }

    /**
     * Test that directory traversal attempts are blocked and return 404.
     */
    public function test_jpm_directory_traversal_returns_404(): void
    {
        $response = $this->get('/jpm8000/../Cargo.toml');

        $response->assertStatus(404);
    }
}
