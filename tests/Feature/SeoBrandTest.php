<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoBrandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_exposes_brand_for_branded_searches(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>IFIX Detallado Automotriz', false)
            ->assertSee('<span class="sr-only">IFIX Detallado Automotriz: </span>', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"alternateName":["Ifix","IFIX Detallado","IFIX Detallado Automotriz","IFIX México"]', false);
    }

    public function test_brand_schema_is_present_on_every_public_page(): void
    {
        $this->get('/productos')
            ->assertOk()
            ->assertSee('"@id":"'.url('/').'#organization"', false);
    }

    public function test_search_console_verification_is_rendered_when_configured(): void
    {
        config(['seo.google_site_verification' => 'abc123']);

        $this->get('/')->assertSee('<meta name="google-site-verification" content="abc123">', false);
    }
}
