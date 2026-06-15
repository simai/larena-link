<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkMutationLadderReviewPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

function source_report(string $schema, string $scenario, string $state, bool $mutates = false): array
{
    return [
        'schema' => $schema,
        'status' => 'passed',
        'scenario' => $scenario,
        'mutates_state' => $mutates,
        'production_mutates_state' => false,
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
        'state' => $state,
    ];
}

$planning = source_report(
    'larena.public_link_guarded_admin_mutation_planning_foundation.v1',
    'public_link_guarded_admin_mutation_planning_foundation',
    'review_surface_ready'
);
$revoke = source_report(
    'larena.public_link_revoke_action_foundation.v1',
    'public_link_revoke_action_foundation',
    'developer_preview_accepted'
);
$regenerate = source_report(
    'larena.public_link_regenerate_action_foundation.v1',
    'public_link_regenerate_action_foundation',
    'developer_preview_accepted'
);
$cleanup = source_report(
    'larena.public_link_cleanup_action_foundation.v1',
    'public_link_cleanup_action_foundation',
    'dry_run_ready'
);

$outputPath = sys_get_temp_dir() . '/larena-link-mutation-ladder-review-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkMutationLadderReviewPreview::run($planning, $revoke, $regenerate, $cleanup, $outputPath);
$previewOutputPath = sys_get_temp_dir() . '/larena-link-mutation-ladder-review-preview-' . bin2hex(random_bytes(4)) . '.json';
$previewReport = PublicLinkMutationLadderReviewPreview::preview($planning, $previewOutputPath);

assert_true($report['schema'] === 'larena.public_link_mutation_ladder_review_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Mutation ladder review preview must pass.');
assert_true($report['scenario'] === 'public_link_mutation_ladder_review_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Review must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Review must not mutate production state.');
assert_true(count($report['operator_action_matrix']) === 4, 'Action matrix must contain four rows.');
assert_true(array_column($report['operator_action_matrix'], 'action') === [
    'planning',
    'revoke_link',
    'regenerate_link',
    'cleanup_links',
], 'Unexpected action matrix order.');

foreach ($report['operator_action_matrix'] as $row) {
    assert_true($row['state_label'] !== '', 'State label missing.');
    assert_true($row['state_hint'] !== '', 'State hint missing.');
    assert_true($row['review_href'] !== '', 'Review href missing.');
    assert_true($row['machine_href'] !== '', 'Machine href missing.');
    assert_true($row['smoke_command'] !== '', 'Smoke command missing.');
    assert_true($row['safe_boundary']['production_mutates_state'] === false, 'Production mutation boundary failed.');
    assert_true($row['safe_boundary']['production_runtime'] === false, 'Production runtime boundary failed.');
    assert_true($row['safe_boundary']['release_ready'] === false, 'Release-ready boundary failed.');
}

assert_true($report['checks']['source_slice_composition']['status'] === 'passed', 'Source composition check must pass.');
assert_true($report['checks']['operator_action_matrix']['status'] === 'passed', 'Action matrix check must pass.');
assert_true($report['checks']['human_status_semantics']['status'] === 'passed', 'Status semantics check must pass.');
assert_true($report['checks']['machine_detail_links']['status'] === 'passed', 'Machine detail links check must pass.');
assert_true($report['checks']['safe_boundary_aggregation']['status'] === 'passed', 'Boundary aggregation check must pass.');
assert_true($report['scope_boundaries']['consolidated_review_only'] === true, 'Review-only boundary missing.');
assert_true($report['scope_boundaries']['new_mutation_behavior_added'] === false, 'New mutation behavior must stay false.');
assert_true($report['scope_boundaries']['production_runtime'] === false, 'Production runtime must stay false.');
assert_true($report['scope_boundaries']['public_delivery'] === false, 'Public delivery must stay false.');
assert_true($report['scope_boundaries']['file_deletion'] === false, 'File deletion must stay false.');
assert_true($report['scope_boundaries']['release_ready'] === false, 'Release-ready must stay false.');
assert_true($report['safe_trace']['consolidated_review_only'] === true, 'Safe trace review-only flag missing.');
assert_true($report['safe_trace']['new_mutation_behavior_added'] === false, 'Safe trace mutation flag must stay false.');
assert_true($report['safe_trace']['queue_or_scheduler_executed'] === false, 'Queue/scheduler must stay false.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay false.');
assert_true(in_array('developer_testable_operator_review_only', $report['known_limitations'], true), 'Operator review limitation missing.');
assert_true(in_array('no_new_public_link_mutation_behavior', $report['known_limitations'], true), 'Mutation limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');
assert_true($previewReport['schema'] === 'larena.public_link_mutation_ladder_review_foundation.v1', 'Preview helper schema mismatch.');
assert_true($previewReport['status'] === 'passed', 'Preview helper must pass.');
assert_true(count($previewReport['operator_action_matrix']) === 4, 'Preview helper must keep four action rows.');
assert_true($previewReport['checks']['source_slice_composition']['status'] === 'passed', 'Preview helper source composition must pass.');
assert_true($previewReport['scope_boundaries']['consolidated_review_only'] === true, 'Preview helper review-only boundary missing.');
assert_true($previewReport['scope_boundaries']['new_mutation_behavior_added'] === false, 'Preview helper must not add mutation behavior.');
assert_true($previewReport['safe_trace']['production_runtime'] === false, 'Preview helper must not enable production runtime.');
assert_true($previewReport['safe_trace']['release_ready'] === false, 'Preview helper must not claim release readiness.');
assert_true(is_file($previewOutputPath), 'Preview helper must write JSON evidence when output path is provided.');

echo "PublicLinkMutationLadderReviewPreviewTest passed.\n";
