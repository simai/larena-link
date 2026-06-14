<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkTokenStorageContractPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$candidateToken = 'active-preview-token';
$outputPath = sys_get_temp_dir() . '/larena-link-public-link-token-storage-contract-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkTokenStorageContractPreview::run($candidateToken, $outputPath);

assert_true($report['schema'] === 'larena.public_link_token_storage_contract_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Contract preview must pass.');
assert_true($report['scenario'] === 'public_link_token_storage_contract_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Contract preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Contract preview must not mutate production state.');
assert_true($report['checks']['hash_only_storage_contract']['status'] === 'passed', 'Hash-only contract must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token leak guard must pass.');
assert_true($report['checks']['scope_boundary']['persistent_token_table'] === false, 'Persistent table must stay disabled.');
assert_true($report['checks']['scope_boundary']['database_migration'] === false, 'Database migration must stay disabled.');
assert_true($report['checks']['scope_boundary']['real_database_mutation'] === false, 'Database mutation must stay disabled.');
assert_true($report['checks']['scope_boundary']['real_file_mutation'] === false, 'File mutation must stay disabled.');
assert_true($report['checks']['scope_boundary']['file_download_executed'] === false, 'File download must stay disabled.');
assert_true($report['checks']['scope_boundary']['release_ready'] === false, 'Preview must not claim release readiness.');
assert_true($report['candidate_lookup']['lookup_status'] === 'found_active', 'Active lookup status expected.');
assert_true($report['candidate_lookup']['decision'] === 'would_allow', 'Active link should allow in contract preview.');
assert_true(str_starts_with($report['candidate_lookup']['token_fingerprint'], 'sha256:'), 'Fingerprint missing.');
assert_true($report['candidate_lookup']['raw_token_visible'] === false, 'Raw token must not be visible.');
assert_true($report['candidate_lookup']['raw_token_persisted'] === false, 'Raw token must not persist.');
assert_true($report['safe_trace']['persistent_token_table'] === false, 'Safe trace must keep persistent table disabled.');
assert_true($report['safe_trace']['database_migration'] === false, 'Safe trace must keep database migration disabled.');
assert_true($report['safe_trace']['real_database_mutation'] === false, 'Safe trace must keep database mutation disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('no_persistent_token_table', $report['known_limitations'], true), 'Persistent table limitation missing.');
assert_true(in_array('no_database_migration', $report['known_limitations'], true), 'Database migration limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(!str_contains(json_encode($report, JSON_THROW_ON_ERROR), $candidateToken), 'Raw candidate token must not leak.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$expired = PublicLinkTokenStorageContractPreview::lookup('expired-preview-token');
$revoked = PublicLinkTokenStorageContractPreview::lookup('revoked-preview-token');
$missingAccess = PublicLinkTokenStorageContractPreview::lookup('missing-access-preview-token');
$unknown = PublicLinkTokenStorageContractPreview::lookup('unknown-preview-token');

assert_true($expired['decision'] === 'would_deny', 'Expired link should deny.');
assert_true($revoked['decision'] === 'would_deny', 'Revoked link should deny.');
assert_true($missingAccess['decision'] === 'would_deny', 'Missing access scope should deny.');
assert_true($unknown['decision'] === 'would_deny', 'Unknown link should deny.');
assert_true(PublicLinkTokenStorageContractPreview::fingerprint($candidateToken) === $report['candidate_lookup']['token_fingerprint'], 'Fingerprint wrapper must be stable.');

echo "PublicLinkTokenStorageContractPreviewTest passed.\n";
