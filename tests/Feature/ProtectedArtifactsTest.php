<?php
namespace Tests\Feature;

use Tests\TestCase;

class ProtectedArtifactsTest extends TestCase
{
    public function test_create_order_and_document_artifacts_match_the_pre_integration_baseline(): void
    {
        $hashes = json_decode(file_get_contents(base_path('tests/Fixtures/protected-artifacts.json')), true, flags: JSON_THROW_ON_ERROR);
        // The user revised these two report files on 16 October; preserve that explicit revision.
        $revisions = json_decode(file_get_contents(base_path('tests/Fixtures/protected-user-revisions-2026-10-16.json')), true, flags: JSON_THROW_ON_ERROR);
        $hashes = array_replace($hashes, $revisions);
        foreach ($hashes as $path => $expected) {
            $this->assertSame($expected, hash_file('sha256', base_path($path)), 'Protected artifact changed: '.$path);
        }
    }
}
