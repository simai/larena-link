<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkControlledDeliverySimulationPreview;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/../bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$deliveryReadiness = [
    'schema' => 'larena.public_link_guarded_delivery_readiness_foundation.v1',
    'status' => 'passed',
    'scenario' => 'public_link_guarded_delivery_readiness_foundation',
    'mutates_state' => true,
    'production_mutates_state' => false,
];

$decision = [
    'state' => 'ready_but_blocked',
    'decision' => 'would_allow',
    'deny_reasons' => [],
    'http_status_preview' => 202,
    'would_deliver_sandbox_target' => true,
    'file_delivery' => 'blocked_by_foundation_scope',
    'file_content_returned' => false,
    'access_scope_ref' => 'access.scope:public-link.preview',
    'audit_event_ref' => 'audit.event:public-link.delivery.preview',
    'target_fingerprint' => 'sha256:sandbox-target-preview',
];

$deliveryState = [
    'state' => 'ready_but_blocked',
    'decision' => 'would_allow',
    'reason' => 'active_link_access_scope_present',
    'terminal' => true,
];

$targetProof = [
    'proof_status' => 'available',
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'target_fingerprint' => 'sha256:sandbox-target-preview',
    'descriptor_ref' => 'descriptor:public-link-delivery-readiness:file-manager-link-sharing-runtime-1',
    'sandbox_storage_ref' => 'sandbox://larena/public-link-preview/file-manager-link-sharing-runtime-1',
    'file_content_returned' => false,
];

$negativeReadinessReports = [
    [
        'case_id' => 'expired_link',
        'fingerprint' => 'sha256:expired-preview',
        'delivery_decision' => [
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.delivery.expired',
        ],
        'delivery_state' => [
            'state' => 'blocked_expired',
            'reason' => 'expired_link',
        ],
        'target_proof' => [
            'proof_status' => 'not_applicable_blocked',
        ],
    ],
    [
        'case_id' => 'revoked_link',
        'fingerprint' => 'sha256:revoked-preview',
        'delivery_decision' => [
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.delivery.revoked',
        ],
        'delivery_state' => [
            'state' => 'blocked_revoked',
            'reason' => 'revoked_link',
        ],
        'target_proof' => [
            'proof_status' => 'not_applicable_blocked',
        ],
    ],
    [
        'case_id' => 'missing_access',
        'fingerprint' => 'sha256:missing-access-preview',
        'delivery_decision' => [
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.delivery.missing-access',
        ],
        'delivery_state' => [
            'state' => 'blocked_missing_access',
            'reason' => 'missing_access_scope',
        ],
        'target_proof' => [
            'proof_status' => 'not_applicable_blocked',
        ],
    ],
    [
        'case_id' => 'unknown_token',
        'fingerprint' => 'sha256:unknown-preview',
        'delivery_decision' => [
            'decision' => 'would_deny',
            'audit_event_ref' => 'audit.event:public-link.delivery.unknown',
        ],
        'delivery_state' => [
            'state' => 'blocked_unknown',
            'reason' => 'unknown_token',
        ],
        'target_proof' => [
            'proof_status' => 'not_applicable_blocked',
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-controlled-delivery-simulation-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkControlledDeliverySimulationPreview::run(
    'active-preview-token',
    $deliveryReadiness,
    $decision,
    $deliveryState,
    $targetProof,
    'sha256:preview-token-fingerprint',
    $negativeReadinessReports,
    $outputPath,
);

assert_true($report['schema'] === 'larena.public_link_controlled_delivery_simulation_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Controlled delivery simulation preview must pass.');
assert_true($report['scenario'] === 'public_link_controlled_delivery_simulation_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === true, 'Preview must preserve local-testing transition flag.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['simulated_response']['simulation_state'] === 'simulated_ready', 'Active response must be simulated-ready.');
assert_true($report['simulated_response']['decision'] === 'would_allow', 'Active response must allow metadata simulation.');
assert_true($report['simulated_response']['http_status_preview'] === 200, 'Active response must preview HTTP 200.');
assert_true($report['simulated_response']['body_included'] === false, 'Response body must stay blocked.');
assert_true($report['simulated_response']['file_content_returned'] === false, 'File content must stay blocked.');
assert_true($report['simulated_response']['production_delivery'] === false, 'Production delivery must stay disabled.');
assert_true($report['simulated_response']['file_delivery'] === 'blocked_by_foundation_scope', 'File delivery must stay blocked.');
assert_true($report['checks']['delivery_readiness_required']['status'] === 'passed', 'Delivery readiness check must pass.');
assert_true($report['checks']['controlled_response_envelope']['status'] === 'passed', 'Response envelope check must pass.');
assert_true($report['checks']['positive_delivery_simulation']['status'] === 'passed', 'Positive simulation check must pass.');
assert_true($report['checks']['negative_delivery_simulations']['status'] === 'passed', 'Negative simulation check must pass.');
assert_true($report['checks']['access_audit_revocation_trace']['status'] === 'passed', 'Access/audit trace check must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['file_delivery_block']['status'] === 'passed', 'File delivery block check must pass.');
assert_true($report['safe_trace']['controlled_delivery_simulation_available'] === true, 'Controlled simulation flag missing.');
assert_true($report['safe_trace']['simulated_response_only'] === true, 'Simulated-only flag missing.');
assert_true($report['safe_trace']['response_body_included'] === false, 'Safe trace body flag must be false.');
assert_true($report['safe_trace']['production_delivery'] === false, 'Production delivery must stay disabled.');
assert_true($report['safe_trace']['file_download_executed'] === false, 'File download must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['one_time_consumption_runtime'] === false, 'One-time consumption must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked into report.');
assert_true(in_array('simulated_response_metadata_only', $report['known_limitations'], true), 'Simulation limitation missing.');
assert_true(in_array('no_public_file_delivery', $report['known_limitations'], true), 'Public delivery limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$composedOutputPath = sys_get_temp_dir() . '/larena-link-controlled-delivery-simulation-composed-' . bin2hex(random_bytes(4)) . '.json';
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
DB::swap($capsule->getDatabaseManager());
Schema::swap($capsule->getConnection()->getSchemaBuilder());

$composedReport = PublicLinkControlledDeliverySimulationPreview::preview('active-preview-token', $composedOutputPath);

assert_true($composedReport['schema'] === 'larena.public_link_controlled_delivery_simulation_foundation.v1', 'Composed preview schema mismatch.');
assert_true($composedReport['status'] === 'passed', 'Composed preview must pass.');
assert_true($composedReport['simulated_response']['simulation_state'] === 'simulated_ready', 'Composed preview must be simulated-ready.');
assert_true($composedReport['component_reports']['public_link_guarded_delivery_readiness_foundation']['status'] === 'passed', 'Composed preview must include delivery readiness.');
assert_true($composedReport['checks']['negative_delivery_simulations']['status'] === 'passed', 'Composed preview negative simulations must pass.');
assert_true($composedReport['safe_trace']['simulated_response_only'] === true, 'Composed preview must stay simulation-only.');
assert_true($composedReport['safe_trace']['file_content_returned'] === false, 'Composed preview must not return file content.');
assert_true(!str_contains(json_encode($composedReport, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked from composed preview.');
assert_true(is_file($composedOutputPath), 'Composed preview must write JSON evidence when output path is provided.');

echo "PublicLinkControlledDeliverySimulationPreviewTest passed.\n";
