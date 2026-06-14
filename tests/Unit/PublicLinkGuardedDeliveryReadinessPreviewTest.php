<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkGuardedDeliveryReadinessPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$persistentLookup = [
    'schema' => 'larena.public_link_persistent_lookup_foundation.v1',
    'status' => 'passed',
    'scenario' => 'public_link_persistent_lookup_foundation',
    'mutates_state' => true,
    'production_mutates_state' => false,
];

$lookup = [
    'lookup_status' => 'found_active',
    'decision' => 'would_allow',
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'access_scope_ref' => 'access.scope:public-link.preview',
    'audit_event_ref' => 'audit.event:public-link.lookup.preview',
    'revoked_at' => null,
    'expires_at' => '2099-12-31T00:00:00Z',
];

$negativeLookups = [
    [
        'case_id' => 'unknown_token',
        'lookup_result' => [
            'lookup_status' => 'not_found',
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.lookup.unknown',
        ],
    ],
    [
        'case_id' => 'expired_link',
        'lookup_result' => [
            'lookup_status' => 'found_expired',
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.lookup.expired',
        ],
    ],
    [
        'case_id' => 'revoked_link',
        'lookup_result' => [
            'lookup_status' => 'found_revoked',
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.lookup.revoked',
        ],
    ],
    [
        'case_id' => 'missing_access',
        'lookup_result' => [
            'lookup_status' => 'found_missing_access_scope',
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.lookup.missing_access',
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-guarded-delivery-readiness-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkGuardedDeliveryReadinessPreview::run(
    'active-preview-token',
    $persistentLookup,
    $lookup,
    'sha256:preview-token-fingerprint',
    $negativeLookups,
    $outputPath,
);

assert_true($report['schema'] === 'larena.public_link_guarded_delivery_readiness_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Guarded delivery readiness preview must pass.');
assert_true($report['scenario'] === 'public_link_guarded_delivery_readiness_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === true, 'Preview must preserve local-testing transition flag.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['delivery_state']['state'] === 'ready_but_blocked', 'Active lookup must become ready-but-blocked.');
assert_true($report['delivery_decision']['decision'] === 'would_allow', 'Active lookup must allow preview decision.');
assert_true($report['delivery_decision']['file_delivery'] === 'blocked_by_foundation_scope', 'File delivery must stay blocked.');
assert_true($report['delivery_decision']['file_content_returned'] === false, 'File content must stay blocked.');
assert_true($report['target_proof']['proof_status'] === 'available', 'Target proof must be available.');
assert_true(str_starts_with($report['target_proof']['target_fingerprint'], 'sha256:'), 'Target fingerprint missing.');
assert_true($report['checks']['persistent_lookup_required']['status'] === 'passed', 'Persistent lookup check must pass.');
assert_true($report['checks']['delivery_state_machine']['status'] === 'passed', 'State machine check must pass.');
assert_true($report['checks']['sandbox_target_proof']['status'] === 'passed', 'Sandbox target proof check must pass.');
assert_true($report['checks']['negative_delivery_guards']['status'] === 'passed', 'Negative guard check must pass.');
assert_true($report['checks']['file_delivery_block']['status'] === 'passed', 'File delivery block check must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['safe_trace']['sandbox_target_proof_only'] === true, 'Sandbox-only proof flag missing.');
assert_true($report['safe_trace']['production_delivery'] === false, 'Production delivery must stay disabled.');
assert_true($report['safe_trace']['file_download_executed'] === false, 'File download must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['one_time_consumption_runtime'] === false, 'One-time consumption must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked into report.');
assert_true(in_array('no_public_file_delivery', $report['known_limitations'], true), 'Public delivery limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkGuardedDeliveryReadinessPreviewTest passed.\n";
