<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkDeliveryContractHardeningPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

function delivery_source_report(string $schema, string $scenario): array
{
    return [
        'schema' => $schema,
        'status' => 'passed',
        'scenario' => $scenario,
        'mutates_state' => false,
        'production_mutates_state' => false,
        'safe_trace' => [
            'adapter_stream_invoked' => false,
            'file_download_executed' => false,
            'file_content_returned' => false,
            'queue_or_scheduler_executed' => false,
            'queue_executed' => false,
            'scheduler_executed' => false,
            'production_runtime' => false,
            'release_ready' => false,
        ],
    ];
}

function delivery_planning_report(): array
{
    return [
        'schema' => 'larena.public_link_guarded_admin_mutation_planning_foundation.v1',
        'status' => 'passed',
        'scenario' => 'public_link_guarded_admin_mutation_planning_foundation',
        'mutates_state' => false,
        'production_mutates_state' => false,
        'mutation_plan_registry' => [
            ['action' => 'revoke_link'],
            ['action' => 'regenerate_link'],
            ['action' => 'cleanup_links'],
        ],
        'safe_trace' => [
            'production_runtime' => false,
            'release_ready' => false,
            'file_content_returned' => false,
            'queue_or_scheduler_executed' => false,
            'queue_executed' => false,
            'scheduler_executed' => false,
            'file_deletion_executed' => false,
            'raw_token_visible' => false,
            'public_delivery_enabled_by_this_action' => false,
        ],
    ];
}

$runtime = delivery_source_report('larena.public_link_runtime_hardening_foundation.v1', 'public_link_runtime_hardening_foundation');
$adapter = delivery_source_report('larena.public_link_guarded_real_delivery_adapter_foundation.v1', 'public_link_guarded_real_delivery_adapter_foundation');
$mutation = delivery_source_report('larena.public_link_mutation_ladder_review_foundation.v1', 'public_link_mutation_ladder_review_foundation');

$outputPath = sys_get_temp_dir() . '/larena-link-delivery-contract-hardening-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkDeliveryContractHardeningPreview::run($runtime, $adapter, $mutation, $outputPath);
$previewOutputPath = sys_get_temp_dir() . '/larena-link-delivery-contract-hardening-preview-' . bin2hex(random_bytes(4)) . '.json';
$previewReport = PublicLinkDeliveryContractHardeningPreview::preview($runtime, $adapter, delivery_planning_report(), $previewOutputPath);

assert_true($report['schema'] === 'larena.public_link_delivery_contract_hardening_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Delivery contract hardening preview must pass.');
assert_true($report['scenario'] === 'public_link_delivery_contract_hardening_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true(count($report['delivery_decision_matrix']) === 8, 'Delivery matrix must contain eight states.');

$statuses = [];
foreach ($report['delivery_decision_matrix'] as $row) {
    $statuses[$row['state']] = $row['http_status'];
    assert_true($row['access_scope_ref'] !== '', 'Access scope ref missing.');
    assert_true($row['audit_event_ref'] !== '', 'Audit event ref missing.');
    assert_true($row['body_policy']['file_body_included'] === false, 'File body must stay excluded.');
    assert_true($row['headers']['X-Larena-File-Body'] === 'blocked', 'File body header must stay blocked.');
    assert_true($row['headers']['X-Larena-Production-Delivery'] === 'false', 'Production delivery header must stay false.');
}

assert_true($statuses['active_allowed'] === 202, 'Active preview status must be 202.');
assert_true($statuses['expired'] === 410, 'Expired status must be 410.');
assert_true($statuses['revoked'] === 410, 'Revoked status must be 410.');
assert_true($statuses['consumed'] === 410, 'Consumed status must be 410.');
assert_true($statuses['missing_access'] === 403, 'Missing access status must be 403.');
assert_true($statuses['unknown_token'] === 404, 'Unknown token status must be 404.');
assert_true($statuses['adapter_refused'] === 503, 'Adapter refused status must be 503.');
assert_true($statuses['missing_file'] === 404, 'Missing file status must be 404.');

foreach (['source_slice_composition', 'delivery_decision_matrix', 'http_status_policy', 'safe_header_policy', 'body_policy', 'access_audit_recheck_points', 'negative_delivery_guards', 'scope_boundary'] as $check) {
    assert_true($report['checks'][$check]['status'] === 'passed', $check . ' must pass.');
}

assert_true($report['safe_trace']['delivery_contract_hardening_available'] === true, 'Contract hardening flag missing.');
assert_true($report['safe_trace']['public_delivery_contract_only'] === true, 'Contract-only flag missing.');
assert_true($report['safe_trace']['production_public_delivery'] === false, 'Production public delivery must stay false.');
assert_true($report['safe_trace']['file_body_included'] === false, 'File body must stay false.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay false.');
assert_true($report['safe_trace']['file_download_executed'] === false, 'File download must stay false.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');
assert_true($previewReport['schema'] === 'larena.public_link_delivery_contract_hardening_foundation.v1', 'Preview helper schema mismatch.');
assert_true($previewReport['status'] === 'passed', 'Preview helper must pass.');
assert_true($previewReport['source_reports']['mutation_ladder_review']['schema'] === 'larena.public_link_mutation_ladder_review_foundation.v1', 'Preview helper must compose mutation ladder source.');
assert_true($previewReport['checks']['source_slice_composition']['status'] === 'passed', 'Preview helper source composition must pass.');
assert_true(count($previewReport['delivery_decision_matrix']) === 8, 'Preview helper must keep eight delivery states.');
assert_true($previewReport['safe_trace']['production_public_delivery'] === false, 'Preview helper must not enable production delivery.');
assert_true($previewReport['safe_trace']['file_content_returned'] === false, 'Preview helper must not return file content.');
assert_true($previewReport['safe_trace']['release_ready'] === false, 'Preview helper must not claim release readiness.');
assert_true(is_file($previewOutputPath), 'Preview helper must write JSON evidence when output path is provided.');

echo "PublicLinkDeliveryContractHardeningPreviewTest passed.\n";
