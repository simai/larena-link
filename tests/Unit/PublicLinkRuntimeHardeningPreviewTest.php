<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRuntimeHardeningPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

function runtime_source_report(string $schema, string $scenario, bool $mutates = false): array
{
    return [
        'schema' => $schema,
        'status' => 'passed',
        'scenario' => $scenario,
        'mutates_state' => $mutates,
        'production_mutates_state' => false,
    ];
}

$dryRun = [
    'schema' => 'larena.public_link_dry_run_runtime_preview.v1',
    'status' => 'passed',
    'scenario' => 'public_link_dry_run_runtime_preview',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'dry_run_cases' => [
        [
            'id' => 'active_link_with_access',
            'decision' => 'would_allow',
            'deny_reasons' => [],
            'access_rechecked' => true,
            'access_scope_ref' => 'access.query_scope:public_link.active',
            'audit_event_planned' => true,
            'audit_event_ref' => 'audit.event:public_link.active',
            'explanation' => 'Active preview link can produce a decision trace.',
        ],
        ['id' => 'expired_link', 'decision' => 'would_deny', 'deny_reasons' => ['expired'], 'access_rechecked' => true, 'audit_event_planned' => true],
        ['id' => 'revoked_link', 'decision' => 'would_deny', 'deny_reasons' => ['revoked'], 'access_rechecked' => true, 'audit_event_planned' => true],
        ['id' => 'missing_access_scope', 'decision' => 'would_deny', 'deny_reasons' => ['missing_access'], 'access_rechecked' => true, 'audit_event_planned' => true],
        ['id' => 'replay_detected', 'decision' => 'would_deny', 'deny_reasons' => ['replay'], 'access_rechecked' => true, 'audit_event_planned' => true],
        ['id' => 'nonce_missing', 'decision' => 'would_deny', 'deny_reasons' => ['nonce_missing'], 'access_rechecked' => true, 'audit_event_planned' => true],
        ['id' => 'rate_limit_exceeded', 'decision' => 'would_deny', 'deny_reasons' => ['rate_limit'], 'access_rechecked' => true, 'audit_event_planned' => true],
    ],
];

$tokenStorage = runtime_source_report('larena.public_link_token_storage_contract_foundation.v1', 'public_link_token_storage_contract_foundation');
$tokenStorage['lookup_result'] = ['decision' => 'would_allow', 'lookup_status' => 'hash_match_preview'];
$tokenStorage['candidate_lookup'] = ['token_fingerprint' => 'sha256:fixture'];

$persistentLookup = runtime_source_report('larena.public_link_persistent_lookup_foundation.v1', 'public_link_persistent_lookup_foundation', false);
$persistentLookup['lookup_result'] = ['decision' => 'would_allow', 'lookup_status' => 'hash_lookup_preview'];
$persistentLookup['candidate_lookup'] = ['token_fingerprint' => 'sha256:fixture'];

$deliveryReadiness = runtime_source_report('larena.public_link_guarded_delivery_readiness_foundation.v1', 'public_link_guarded_delivery_readiness_foundation');
$deliveryReadiness['delivery_state'] = ['state' => 'ready_preview'];
$deliveryReadiness['delivery_decision'] = ['decision' => 'would_allow', 'deny_reasons' => [], 'would_deliver_sandbox_target' => true];
$deliveryReadiness['target_proof'] = ['target_fingerprint' => 'sha256:target'];

$deliverySimulation = runtime_source_report('larena.public_link_controlled_delivery_simulation_foundation.v1', 'public_link_controlled_delivery_simulation_foundation');
$deliverySimulation['simulated_response'] = ['decision' => 'would_allow', 'simulation_state' => 'decision_trace_only', 'http_status_preview' => 202, 'body_included' => false];

$consumptionLifecycle = runtime_source_report('larena.public_link_one_time_consumption_lifecycle_foundation.v1', 'public_link_one_time_consumption_lifecycle_foundation');
$consumptionLifecycle['lifecycle_state'] = ['state' => 'active_one_time_preview'];
$consumptionLifecycle['consumption_plan'] = ['decision' => 'would_allow', 'plan_status' => 'simulated_only', 'consume_now' => false, 'persistent_consumed_at_write' => false];

$deliveryAdapter = runtime_source_report('larena.public_link_guarded_real_delivery_adapter_foundation.v1', 'public_link_guarded_real_delivery_adapter_foundation');
$deliveryAdapter['adapter_decision'] = ['decision' => 'would_allow', 'adapter_state' => 'ready_preview', 'adapter_id' => 'preview', 'stream_now' => false, 'adapter_stream_invoked' => false, 'file_body_included' => false];

$outputPath = sys_get_temp_dir() . '/larena-link-runtime-hardening-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkRuntimeHardeningPreview::run(
    $dryRun,
    $tokenStorage,
    $persistentLookup,
    $deliveryReadiness,
    $deliverySimulation,
    $consumptionLifecycle,
    $deliveryAdapter,
    'active-preview-token',
    $outputPath
);

assert_true($report['schema'] === 'larena.public_link_runtime_hardening_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Runtime hardening preview must pass.');
assert_true($report['scenario'] === 'public_link_runtime_hardening_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview runtime hardening must stay no-write.');
assert_true($report['production_mutates_state'] === false, 'Production mutation must stay false.');
assert_true($report['resolution_decision']['decision'] === 'would_allow', 'Active decision must allow preview.');
assert_true($report['resolution_decision']['http_status_preview'] === 202, 'Active preview status must be 202.');
assert_true($report['resolution_decision']['file_delivery'] === 'blocked_by_foundation_scope', 'File delivery must stay blocked.');
assert_true(str_starts_with($report['candidate_request']['token_fingerprint'], 'sha256:'), 'Fingerprint missing.');
assert_true($report['candidate_request']['raw_token_visible'] === false, 'Raw token must stay hidden.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked.');

foreach (['launch_record_scope', 'route_hardening_contract', 'token_redaction', 'token_storage_contract', 'persistent_lookup_foundation', 'access_recheck', 'audit_trace', 'expiry_revocation_guards', 'replay_nonce_rate_limit_guards', 'file_delivery_block', 'scope_boundary'] as $check) {
    assert_true($report['checks'][$check]['status'] === 'passed', $check . ' must pass.');
}

assert_true($report['safe_trace']['token_storage_enabled_now'] === false, 'Token storage runtime must stay disabled.');
assert_true($report['safe_trace']['persistent_token_table'] === true, 'Persistent table marker must be preserved.');
assert_true($report['safe_trace']['database_migration'] === true, 'Database migration marker must be preserved.');
assert_true($report['safe_trace']['real_database_mutation'] === false, 'Preview DB mutation marker must stay false.');
assert_true($report['safe_trace']['production_lookup'] === false, 'Production lookup must stay false.');
assert_true($report['safe_trace']['file_download_executed'] === false, 'File download must stay false.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay false.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$expired = PublicLinkRuntimeHardeningPreview::run(
    $dryRun,
    $tokenStorage,
    $persistentLookup,
    $deliveryReadiness,
    $deliverySimulation,
    $consumptionLifecycle,
    $deliveryAdapter,
    'expired-preview-token'
);

assert_true($expired['resolution_decision']['case_id'] === 'expired_link', 'Expired token must map to expired case.');
assert_true(!str_contains(json_encode($expired, JSON_THROW_ON_ERROR), 'expired-preview-token'), 'Expired raw token leaked.');

echo "PublicLinkRuntimeHardeningPreviewTest passed.\n";
