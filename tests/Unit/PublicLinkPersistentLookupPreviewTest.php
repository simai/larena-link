<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkPersistentLookupPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$fingerprint = PublicLinkPersistentLookupPreview::fingerprint('active-preview-token');
$schema = [
    'table' => 'larena_public_link_lookup',
    'table_exists' => false,
    'created_now' => false,
    'mutates_state' => false,
    'preview_lookup_mode' => 'in_memory_fixture',
    'migration_execution_allowed' => false,
];
$seed = [
    'table' => 'larena_public_link_lookup',
    'seeded_count' => 4,
    'mutates_state' => false,
    'preview_lookup_mode' => 'in_memory_fixture',
    'fixture_keys' => [
        $fingerprint,
        PublicLinkPersistentLookupPreview::fingerprint('expired-preview-token'),
        PublicLinkPersistentLookupPreview::fingerprint('revoked-preview-token'),
        PublicLinkPersistentLookupPreview::fingerprint('missing-access-preview-token'),
    ],
    'raw_token_persisted' => false,
];
$lookup = [
    'stored_lookup_key' => $fingerprint,
    'link_identity_ref' => 'link:file-manager-link-sharing-runtime-preview',
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
    'audit_event_ref' => 'audit.event:file-manager.link-sharing.runtime',
    'expires_at' => gmdate('Y-m-d H:i:s', strtotime('+1 day')),
    'revoked_at' => null,
    'status' => 'active',
    'raw_token_visible' => false,
    'raw_token_persisted' => false,
    'file_download_executed' => false,
    'mutates_state' => false,
    'lookup_status' => 'found_active',
    'decision' => 'would_allow',
    'deny_reasons' => [],
];
$negativeLookups = [
    'unknown_token_fail_closed' => ['decision' => 'would_deny'],
    'expired_token_fail_closed' => ['decision' => 'would_deny'],
    'revoked_token_fail_closed' => ['decision' => 'would_deny'],
    'missing_access_scope_fail_closed' => ['decision' => 'would_deny'],
];

$outputPath = sys_get_temp_dir() . '/larena-link-persistent-lookup-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkPersistentLookupPreview::reportFromLookup(
    'active-preview-token',
    $schema,
    $seed,
    $lookup,
    $negativeLookups,
    $outputPath,
);

assert_true($report['schema'] === 'larena.public_link_persistent_lookup_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Persistent lookup preview must pass.');
assert_true($report['scenario'] === 'public_link_persistent_lookup_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview lookup must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Production mutation must stay disabled.');
assert_true($report['checks']['schema_boundary']['status'] === 'passed', 'Schema boundary must pass.');
assert_true($report['checks']['schema_boundary']['created_now'] === false, 'Preview must not create schema.');
assert_true($report['checks']['schema_boundary']['migration_execution_allowed'] === false, 'Preview migration execution must stay disabled.');
assert_true(
    $report['checks']['schema_boundary']['migration_ref'] === 'larena/link::2026_06_08_000001_create_larena_public_link_lookup_table',
    'Schema boundary must expose package-owned migration ref.',
);
assert_true(
    !str_contains(json_encode($report['checks']['schema_boundary'], JSON_THROW_ON_ERROR), 'database/migrations/'),
    'Schema boundary must not expose app migration paths.',
);
assert_true($report['checks']['fixture_seed']['status'] === 'passed', 'Fixture seed must pass.');
assert_true($report['checks']['fixture_seed']['mutates_state'] === false, 'Preview fixture seed must not write DB rows.');
assert_true($report['checks']['hash_only_lookup']['status'] === 'passed', 'Hash-only lookup must pass.');
assert_true($report['checks']['lookup_decision_contract']['unknown_token_fail_closed'] === true, 'Unknown token must fail closed.');
assert_true($report['checks']['lookup_decision_contract']['expired_token_fail_closed'] === true, 'Expired token must fail closed.');
assert_true($report['checks']['lookup_decision_contract']['revoked_token_fail_closed'] === true, 'Revoked token must fail closed.');
assert_true($report['checks']['lookup_decision_contract']['missing_access_scope_fail_closed'] === true, 'Missing access scope must fail closed.');
assert_true($report['checks']['access_audit_boundary']['status'] === 'passed', 'Access/audit boundary must pass.');
assert_true($report['checks']['file_delivery_block']['file_download_executed'] === false, 'File delivery must stay disabled.');
assert_true($report['checks']['scope_boundary']['production_database_mutation'] === false, 'Production DB mutation must stay disabled.');
assert_true($report['checks']['scope_boundary']['real_database_mutation'] === false, 'Preview DB mutation must stay disabled.');
assert_true($report['safe_trace']['persistent_token_table'] === true, 'Persistent table marker missing.');
assert_true($report['safe_trace']['real_database_mutation'] === false, 'Preview DB mutation marker must stay false.');
assert_true($report['safe_trace']['production_lookup'] === false, 'Production lookup must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready claim must stay disabled.');
assert_true(in_array('no_production_lookup_runtime', $report['known_limitations'], true), 'Production lookup limitation missing.');
assert_true(in_array('preview_uses_in_memory_fixture_lookup', $report['known_limitations'], true), 'In-memory preview limitation missing.');
assert_true(in_array('no_public_file_delivery', $report['known_limitations'], true), 'Public delivery limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Report must not expose raw token.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'database/migrations/'), 'Report must not expose app migration paths.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkPersistentLookupPreviewTest passed.\n";
