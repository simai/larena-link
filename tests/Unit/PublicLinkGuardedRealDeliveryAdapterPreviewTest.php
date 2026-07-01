<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Larena\Link\Runtime\PublicLinkGuardedRealDeliveryAdapterPreview;

require_once __DIR__ . '/../bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

function boot_preview_database(): void
{
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
}

$lifecycle = [
    'schema' => 'larena.public_link_one_time_consumption_lifecycle_foundation.v1',
    'status' => 'passed',
    'scenario' => 'public_link_one_time_consumption_lifecycle_foundation',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'safe_trace' => [
        'production_runtime' => false,
        'release_ready' => false,
    ],
];

$lifecycleState = [
    'state' => 'simulated_consumption_planned',
    'decision' => 'would_allow',
    'reason' => 'active_link_consumption_would_be_recorded_by_future_launch_record',
    'terminal' => true,
];

$consumptionPlan = [
    'plan_status' => 'simulation_ready',
    'decision' => 'would_allow',
    'reason' => 'active_link_consumption_would_be_recorded_by_future_launch_record',
    'token_fingerprint' => 'sha256:preview-token-fingerprint',
    'access_scope_ref' => 'access.scope:public-link.preview',
    'audit_event_ref' => 'audit.event:public_link.consumption.simulated',
    'target_fingerprint' => 'sha256:sandbox-target-preview',
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'consume_now' => false,
    'persistent_consumed_at_write' => false,
    'consumed_at_preview' => 'future_runtime_timestamp',
    'requires_future_launch_record' => true,
    'file_delivery' => 'blocked_by_foundation_scope',
    'file_content_returned' => false,
    'production_delivery' => false,
];

$negativeLifecycles = [
    [
        'case_id' => 'already_consumed',
        'fingerprint' => 'sha256:consumed-preview',
        'lifecycle_state' => [
            'state' => 'blocked_already_consumed',
            'decision' => 'would_deny',
            'reason' => 'already_consumed',
        ],
        'consumption_plan' => [
            'plan_status' => 'blocked',
            'reason' => 'already_consumed',
            'access_scope_ref' => 'access.query_scope:public_link.blocked',
            'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
        ],
    ],
    [
        'case_id' => 'expired_link',
        'fingerprint' => 'sha256:expired-preview',
        'lifecycle_state' => [
            'state' => 'blocked_expired',
            'decision' => 'would_deny',
            'reason' => 'expired_link',
        ],
        'consumption_plan' => [
            'plan_status' => 'blocked',
            'reason' => 'expired_link',
            'access_scope_ref' => 'access.query_scope:public_link.blocked',
            'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
        ],
    ],
    [
        'case_id' => 'revoked_link',
        'fingerprint' => 'sha256:revoked-preview',
        'lifecycle_state' => [
            'state' => 'blocked_revoked',
            'decision' => 'would_deny',
            'reason' => 'revoked_link',
        ],
        'consumption_plan' => [
            'plan_status' => 'blocked',
            'reason' => 'revoked_link',
            'access_scope_ref' => 'access.query_scope:public_link.blocked',
            'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
        ],
    ],
    [
        'case_id' => 'missing_access',
        'fingerprint' => 'sha256:missing-access-preview',
        'lifecycle_state' => [
            'state' => 'blocked_missing_access',
            'decision' => 'would_deny',
            'reason' => 'missing_access_scope',
        ],
        'consumption_plan' => [
            'plan_status' => 'blocked',
            'reason' => 'missing_access_scope',
            'access_scope_ref' => 'access.query_scope:public_link.blocked',
            'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
        ],
    ],
    [
        'case_id' => 'unknown_token',
        'fingerprint' => 'sha256:unknown-preview',
        'lifecycle_state' => [
            'state' => 'blocked_unknown',
            'decision' => 'would_deny',
            'reason' => 'unknown_token',
        ],
        'consumption_plan' => [
            'plan_status' => 'blocked',
            'reason' => 'unknown_token',
            'access_scope_ref' => 'access.query_scope:public_link.blocked',
            'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-guarded-real-delivery-adapter-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkGuardedRealDeliveryAdapterPreview::run(
    'active-preview-token',
    $lifecycle,
    $lifecycleState,
    $consumptionPlan,
    'sha256:preview-token-fingerprint',
    $negativeLifecycles,
    $outputPath,
);

assert_true($report['schema'] === 'larena.public_link_guarded_real_delivery_adapter_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Guarded real delivery adapter preview must pass.');
assert_true($report['scenario'] === 'public_link_guarded_real_delivery_adapter_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['adapter_decision']['adapter_state'] === 'adapter_ready_preview', 'Active adapter must be preview-ready.');
assert_true($report['adapter_decision']['decision'] === 'would_allow', 'Active adapter must allow metadata preview.');
assert_true($report['adapter_decision']['adapter_id'] === 'larena.filesystem.public_link_sandbox_delivery_adapter', 'Adapter id missing.');
assert_true($report['adapter_decision']['stream_now'] === false, 'Adapter must not stream now.');
assert_true($report['adapter_decision']['adapter_stream_invoked'] === false, 'Adapter stream must not be invoked.');
assert_true($report['adapter_decision']['file_body_included'] === false, 'File body must stay blocked.');
assert_true($report['adapter_decision']['file_content_returned'] === false, 'File content must stay blocked.');
assert_true($report['adapter_decision']['persistent_consumed_at_write'] === false, 'Consumed-at write must stay blocked.');
assert_true($report['adapter_decision']['requires_future_launch_record'] === true, 'Future launch record requirement missing.');
assert_true($report['checks']['one_time_lifecycle_required']['status'] === 'passed', 'Lifecycle check must pass.');
assert_true($report['checks']['adapter_contract']['status'] === 'passed', 'Adapter contract check must pass.');
assert_true($report['checks']['negative_adapter_guards']['status'] === 'passed', 'Negative guards must pass.');
assert_true($report['checks']['access_audit_delivery_trace']['status'] === 'passed', 'Access/audit trace must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['file_delivery_block']['status'] === 'passed', 'File delivery block must pass.');
assert_true($report['safe_trace']['guarded_real_delivery_adapter_available'] === true, 'Adapter flag missing.');
assert_true($report['safe_trace']['real_delivery_adapter_contract_only'] === true, 'Contract-only flag missing.');
assert_true($report['safe_trace']['adapter_stream_invoked'] === false, 'Safe trace adapter stream flag must stay false.');
assert_true($report['safe_trace']['stream_now'] === false, 'Safe trace stream-now flag must stay false.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'Safe trace file content flag must stay false.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked into report.');
assert_true(in_array('adapter_metadata_only', $report['known_limitations'], true), 'Adapter limitation missing.');
assert_true(in_array('no_file_streaming', $report['known_limitations'], true), 'Streaming limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$composedOutputPath = sys_get_temp_dir() . '/larena-link-guarded-real-delivery-composed-' . bin2hex(random_bytes(4)) . '.json';
boot_preview_database();
$composed = PublicLinkGuardedRealDeliveryAdapterPreview::preview('active-preview-token', $composedOutputPath);

assert_true($composed['schema'] === 'larena.public_link_guarded_real_delivery_adapter_foundation.v1', 'Unexpected composed schema.');
assert_true($composed['status'] === 'passed', 'Composed guarded real delivery adapter preview must pass.');
assert_true($composed['adapter_decision']['adapter_state'] === 'adapter_ready_preview', 'Composed adapter must be preview-ready.');
assert_true($composed['adapter_decision']['stream_now'] === false, 'Composed adapter must not stream now.');
assert_true($composed['adapter_decision']['adapter_stream_invoked'] === false, 'Composed adapter stream must not be invoked.');
assert_true($composed['adapter_decision']['file_content_returned'] === false, 'Composed adapter must not return file content.');
assert_true($composed['adapter_decision']['persistent_consumed_at_write'] === false, 'Composed adapter must not write consumed_at.');
assert_true($composed['safe_trace']['production_delivery'] === false, 'Composed adapter must keep production delivery disabled.');
assert_true($composed['safe_trace']['release_ready'] === false, 'Composed adapter must not claim release readiness.');
assert_true(!str_contains(json_encode($composed, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Composed raw token leaked into report.');
assert_true(is_file($composedOutputPath), 'Composed preview must write JSON evidence when output path is provided.');

$consumedState = [
    'state' => 'blocked_already_consumed',
    'decision' => 'would_deny',
    'reason' => 'already_consumed',
    'terminal' => true,
];
$consumedPlan = [
    'plan_status' => 'blocked',
    'decision' => 'would_deny',
    'reason' => 'already_consumed',
    'token_fingerprint' => 'sha256:consumed-preview',
    'access_scope_ref' => 'access.query_scope:public_link.blocked',
    'audit_event_ref' => 'audit.event:public_link.consumption.blocked',
    'consume_now' => false,
    'persistent_consumed_at_write' => false,
    'requires_future_launch_record' => true,
    'file_content_returned' => false,
    'production_delivery' => false,
];
$consumed = PublicLinkGuardedRealDeliveryAdapterPreview::run(
    'consumed-preview-token',
    $lifecycle,
    $consumedState,
    $consumedPlan,
    'sha256:consumed-preview',
    $negativeLifecycles,
);
assert_true($consumed['status'] === 'passed', 'Consumed adapter report must pass.');
assert_true($consumed['adapter_decision']['adapter_state'] === 'adapter_blocked_already_consumed', 'Consumed adapter must fail closed.');
assert_true($consumed['adapter_decision']['decision'] === 'would_deny', 'Consumed adapter must deny.');
assert_true($consumed['adapter_decision']['adapter_id'] === null, 'Consumed adapter id must be null.');
assert_true($consumed['adapter_decision']['stream_now'] === false, 'Consumed adapter must not stream.');
assert_true(!str_contains(json_encode($consumed, JSON_THROW_ON_ERROR), 'consumed-preview-token'), 'Consumed raw token leaked.');

echo "PublicLinkGuardedRealDeliveryAdapterPreviewTest passed.\n";
