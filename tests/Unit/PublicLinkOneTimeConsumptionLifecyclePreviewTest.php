<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Larena\Link\Runtime\PublicLinkOneTimeConsumptionLifecyclePreview;

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

$deliverySimulation = [
    'schema' => 'larena.public_link_controlled_delivery_simulation_foundation.v1',
    'status' => 'passed',
    'scenario' => 'public_link_controlled_delivery_simulation_foundation',
    'mutates_state' => true,
    'production_mutates_state' => false,
];

$simulation = [
    'simulation_state' => 'simulated_ready',
    'decision' => 'would_allow',
    'reason' => 'sandbox_target_ready_no_body',
    'http_status_preview' => 200,
    'token_fingerprint' => 'sha256:preview-token-fingerprint',
    'access_scope_ref' => 'access.scope:public-link.preview',
    'audit_event_ref' => 'audit.event:public-link.delivery.preview',
    'target_fingerprint' => 'sha256:sandbox-target-preview',
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'body_included' => false,
    'file_delivery' => 'blocked_by_foundation_scope',
    'file_content_returned' => false,
    'production_delivery' => false,
    'one_time_consumption_runtime' => false,
];

$negativeDeliverySimulations = [
    [
        'case_id' => 'already_consumed',
        'fingerprint' => 'sha256:consumed-preview',
        'simulated_response' => $simulation,
    ],
    [
        'case_id' => 'expired_link',
        'fingerprint' => 'sha256:expired-preview',
        'simulated_response' => [
            'decision' => 'would_deny',
            'reason' => 'expired_link',
            'audit_event_ref' => 'audit.event:public-link.delivery.expired',
            'body_included' => false,
            'file_content_returned' => false,
            'production_delivery' => false,
        ],
    ],
    [
        'case_id' => 'revoked_link',
        'fingerprint' => 'sha256:revoked-preview',
        'simulated_response' => [
            'decision' => 'would_deny',
            'reason' => 'revoked_link',
            'audit_event_ref' => 'audit.event:public-link.delivery.revoked',
            'body_included' => false,
            'file_content_returned' => false,
            'production_delivery' => false,
        ],
    ],
    [
        'case_id' => 'missing_access',
        'fingerprint' => 'sha256:missing-access-preview',
        'simulated_response' => [
            'decision' => 'would_deny',
            'reason' => 'missing_access_scope',
            'audit_event_ref' => 'audit.event:public-link.delivery.missing-access',
            'body_included' => false,
            'file_content_returned' => false,
            'production_delivery' => false,
        ],
    ],
    [
        'case_id' => 'unknown_token',
        'fingerprint' => 'sha256:unknown-preview',
        'simulated_response' => [
            'decision' => 'would_deny',
            'reason' => 'unknown_token',
            'audit_event_ref' => 'audit.event:public-link.delivery.unknown',
            'body_included' => false,
            'file_content_returned' => false,
            'production_delivery' => false,
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-one-time-consumption-lifecycle-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkOneTimeConsumptionLifecyclePreview::run(
    'active-preview-token',
    $deliverySimulation,
    $simulation,
    'sha256:preview-token-fingerprint',
    $negativeDeliverySimulations,
    $outputPath,
);

assert_true($report['schema'] === 'larena.public_link_one_time_consumption_lifecycle_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'One-time lifecycle preview must pass.');
assert_true($report['scenario'] === 'public_link_one_time_consumption_lifecycle_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['lifecycle_state']['state'] === 'simulated_consumption_planned', 'Active link must plan simulated consumption.');
assert_true($report['lifecycle_state']['decision'] === 'would_allow', 'Active link must allow simulated consumption.');
assert_true($report['consumption_plan']['plan_status'] === 'simulation_ready', 'Consumption plan must be simulation-ready.');
assert_true($report['consumption_plan']['consume_now'] === false, 'Consume-now must stay false.');
assert_true($report['consumption_plan']['persistent_consumed_at_write'] === false, 'Persistent consumed_at write must stay false.');
assert_true($report['consumption_plan']['consumed_at_preview'] === 'future_runtime_timestamp', 'Consumed-at preview missing.');
assert_true($report['checks']['controlled_delivery_simulation_required']['status'] === 'passed', 'Controlled delivery simulation check must pass.');
assert_true($report['checks']['one_time_state_machine']['status'] === 'passed', 'State machine check must pass.');
assert_true($report['checks']['simulated_consumption_plan']['status'] === 'passed', 'Consumption plan check must pass.');
assert_true($report['checks']['negative_consumption_guards']['status'] === 'passed', 'Negative guard check must pass.');
assert_true($report['checks']['access_audit_trace']['status'] === 'passed', 'Access/audit trace check must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['file_delivery_block']['status'] === 'passed', 'File delivery block check must pass.');
assert_true($report['safe_trace']['one_time_consumption_lifecycle_available'] === true, 'Lifecycle flag missing.');
assert_true($report['safe_trace']['simulated_consumption_only'] === true, 'Simulated-only flag missing.');
assert_true($report['safe_trace']['consume_now'] === false, 'Safe trace consume-now must stay false.');
assert_true($report['safe_trace']['persistent_consumed_at_write'] === false, 'Safe trace consumed_at write must stay false.');
assert_true($report['safe_trace']['production_delivery'] === false, 'Production delivery must stay disabled.');
assert_true($report['safe_trace']['file_download_executed'] === false, 'File download must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Raw token leaked into report.');
assert_true(in_array('simulated_consumption_plan_only', $report['known_limitations'], true), 'Simulation limitation missing.');
assert_true(in_array('no_persistent_consumed_at_write', $report['known_limitations'], true), 'Persistent write limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$composedOutputPath = sys_get_temp_dir() . '/larena-link-one-time-composed-' . bin2hex(random_bytes(4)) . '.json';
boot_preview_database();
$composed = PublicLinkOneTimeConsumptionLifecyclePreview::preview('active-preview-token', $composedOutputPath);

assert_true($composed['schema'] === 'larena.public_link_one_time_consumption_lifecycle_foundation.v1', 'Unexpected composed schema.');
assert_true($composed['status'] === 'passed', 'Composed one-time lifecycle preview must pass.');
assert_true($composed['lifecycle_state']['state'] === 'simulated_consumption_planned', 'Composed preview must plan simulated consumption.');
assert_true($composed['consumption_plan']['consume_now'] === false, 'Composed preview must not consume now.');
assert_true($composed['consumption_plan']['persistent_consumed_at_write'] === false, 'Composed preview must not write consumed_at.');
assert_true($composed['safe_trace']['production_delivery'] === false, 'Composed preview must keep production delivery disabled.');
assert_true($composed['safe_trace']['file_content_returned'] === false, 'Composed preview must not return file content.');
assert_true($composed['safe_trace']['release_ready'] === false, 'Composed preview must not claim release readiness.');
assert_true(!str_contains(json_encode($composed, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Composed raw token leaked into report.');
assert_true(is_file($composedOutputPath), 'Composed preview must write JSON evidence when output path is provided.');

$consumed = PublicLinkOneTimeConsumptionLifecyclePreview::run(
    'consumed-preview-token',
    $deliverySimulation,
    $simulation,
    'sha256:consumed-preview',
    $negativeDeliverySimulations,
);
assert_true($consumed['status'] === 'passed', 'Consumed state report must pass.');
assert_true($consumed['lifecycle_state']['state'] === 'blocked_already_consumed', 'Consumed link must fail closed.');
assert_true($consumed['consumption_plan']['consume_now'] === false, 'Consumed link must not consume now.');
assert_true(!str_contains(json_encode($consumed, JSON_THROW_ON_ERROR), 'consumed-preview-token'), 'Consumed raw token leaked.');

echo "PublicLinkOneTimeConsumptionLifecyclePreviewTest passed.\n";
