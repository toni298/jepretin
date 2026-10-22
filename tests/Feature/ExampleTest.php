<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_asset_urls_use_the_forwarded_https_scheme(): void
    {
        $this->withHeader('X-Forwarded-Proto', 'https')->get('/');

        $this->assertStringStartsWith('https://', URL::asset('build/assets/app.css'));
    }
}
