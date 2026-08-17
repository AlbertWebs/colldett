<?php

namespace Tests\Feature;

use App\Support\AdminStoredSettings;
use App\Support\ServiceCatalog;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicHtmlEscapingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AdminStoredSettings::flushCache();
        Storage::fake('local');
    }

    public function test_services_listing_renders_html_description_without_showing_tags(): void
    {
        Storage::disk('local')->put('admin/services.json', json_encode([
            [
                'id' => 1,
                'name' => 'Debt Recovery',
                'slug' => 'debt-recovery',
                'description' => '<p>End-to-end commercial and consumer debt recovery.</p><p><br></p><p>Structured escalation and compliance-focused execution.</p>',
                'image' => '',
            ],
        ], JSON_UNESCAPED_UNICODE));

        $this->assertNotEmpty(ServiceCatalog::all());

        $response = $this->get('/services');

        $response->assertOk();
        $response->assertSee('End-to-end commercial and consumer debt recovery.', false);
        $response->assertSee('Structured escalation and compliance-focused execution.', false);
        $response->assertDontSee('&lt;p&gt;', false);
        $response->assertDontSee('&lt;br&gt;', false);
    }
}
