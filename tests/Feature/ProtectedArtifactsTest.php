<?php
namespace Tests\Feature;

use Tests\TestCase;

class ProtectedArtifactsTest extends TestCase
{
    public function test_create_order_and_document_artifacts_match_the_pre_integration_baseline(): void
    {
        $hashes = json_decode(file_get_contents(base_path('tests/Fixtures/protected-artifacts.json')), true, flags: JSON_THROW_ON_ERROR);
        // Preserve user revisions from 16 October, including Create Order committed in 0ed0c28.
        $revisions = json_decode(file_get_contents(base_path('tests/Fixtures/protected-user-revisions-2026-10-16.json')), true, flags: JSON_THROW_ON_ERROR);
        $rbac = json_decode(file_get_contents(base_path('tests/Fixtures/protected-rbac-revisions-2026-10-16.json')), true, flags: JSON_THROW_ON_ERROR);
        $workflow = json_decode(file_get_contents(base_path('tests/Fixtures/protected-workflow-revisions-2026-10-17.json')), true, flags: JSON_THROW_ON_ERROR);
        $hashes = array_replace($hashes, $revisions, $rbac, $workflow);
        foreach ($hashes as $path => $expected) {
            $this->assertSame($expected, hash_file('sha256', base_path($path)), 'Protected artifact changed: '.$path);
        }
    }
}
