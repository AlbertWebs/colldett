<?php

namespace Tests\Feature;

use App\Support\AdminStoredSettings;
use App\Support\TeamDirectory;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamProfileHtmlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AdminStoredSettings::flushCache();
        Storage::fake('local');
    }

    public function test_team_profile_renders_html_bio_instead_of_showing_tags(): void
    {
        Storage::disk('local')->put(TeamDirectory::STORAGE_PATH, json_encode([
            [
                'slug' => 'evance-odhiambo',
                'name' => 'Evance Odhiambo',
                'role' => 'Chief Executive Officer (CEO) & Managing Director',
                'department' => 'Executive Leadership',
                'image' => '',
                'bio' => '<p>Evance Odhiambo is the Chief Executive Officer and Managing Director of Colldett Trace Limited.</p><p><br></p><p>A qualified lawyer, Evance brings over 25 years of senior leadership experience.</p>',
                'experience_years' => 12,
                'location' => 'Nairobi, Kenya',
                'email' => 'evance@colldetttrace.com',
                'seo_description' => '<p>Profile of Evance Odhiambo</p>',
                'specialties' => ['<p>Executive Strategy', 'Institutional Relationships', 'Performance Governance</p>'],
                'credentials' => ['<p>Corporate Leadership', 'Recovery Oversight', 'Strategic Planning</p>'],
                'industries' => ['<p>Banking', 'Microfinance', 'Corporate Services</p>'],
                'principles' => ['<p>Integrity', 'Accountability', 'Results leadership</p>'],
                'is_active' => true,
            ],
        ], JSON_UNESCAPED_UNICODE));

        $response = $this->get('/team/evance-odhiambo');

        $response->assertOk();
        $response->assertSee('Evance Odhiambo is the Chief Executive Officer', false);
        $response->assertSee('A qualified lawyer, Evance brings over 25 years', false);
        $response->assertSee('Executive Strategy', false);
        $response->assertSee('Corporate Leadership', false);
        $response->assertSee('Banking', false);
        $response->assertSee('Integrity', false);
        $response->assertDontSee('&lt;p&gt;', false);
        $response->assertDontSee('&lt;/p&gt;', false);
        $response->assertDontSee('&lt;br&gt;', false);
        $response->assertDontSee('<span>&lt;p&gt;', false);
        $response->assertDontSee('<li>&lt;p&gt;', false);
    }
}
