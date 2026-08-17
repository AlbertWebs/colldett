<?php

namespace Tests\Unit;

use App\Support\TeamDirectory;
use Tests\TestCase;

class TeamDirectoryPlainTextTest extends TestCase
{
    public function test_list_fields_strip_html_tags(): void
    {
        $member = TeamDirectory::normalizeMember([
            'slug' => 'example',
            'name' => 'Example',
            'specialties' => ['<p>Executive Strategy', 'Institutional Relationships', 'Performance Governance</p>'],
            'credentials' => ['<p>Corporate Leadership</p>'],
        ]);

        $this->assertSame(['Executive Strategy', 'Institutional Relationships', 'Performance Governance'], $member['specialties']);
        $this->assertSame(['Corporate Leadership'], $member['credentials']);
    }
}
