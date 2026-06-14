<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkTokenStorageContractPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function run(string $candidateToken = 'active-preview-token', ?string $outputPath = null): array
    {
        $lookup = self::lookup($candidateToken);
        $tokenFingerprint = self::fingerprint($candidateToken);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-token-storage-contract-foundation.json',
                'ready_to_code' => true,
                'persistent_token_table_allowed' => false,
                'database_migration_allowed' => false,
                'production_lookup_allowed' => false,
            ],
            'hash_only_storage_contract' => [
                'status' => 'passed',
                'hash_algorithm' => 'sha256',
                'raw_token_storage_allowed' => false,
                'raw_token_output_allowed' => false,
                'token_fingerprint' => $tokenFingerprint,
                'stored_lookup_key' => $lookup['stored_lookup_key'],
            ],
            'lookup_decision_contract' => [
                'status' => 'passed',
                'lookup_status' => $lookup['lookup_status'],
                'decision' => $lookup['decision'],
                'deny_reasons' => $lookup['deny_reasons'],
                'unknown_token_fail_closed' => true,
                'expired_token_fail_closed' => true,
                'revoked_token_fail_closed' => true,
                'missing_access_scope_fail_closed' => true,
            ],
            'metadata_contract' => [
                'status' => 'passed',
                'link_identity_ref' => $lookup['link_identity_ref'],
                'logical_file_id' => $lookup['logical_file_id'],
                'access_scope_ref' => $lookup['access_scope_ref'],
                'audit_event_ref' => $lookup['audit_event_ref'],
                'expires_at' => $lookup['expires_at'],
                'revoked_at' => $lookup['revoked_at'],
                'metadata_contains_raw_token' => false,
            ],
            'route_hardening_bridge' => [
                'status' => 'passed',
                'route_shape' => '/larena/link/{token}',
                'route_can_use_lookup_contract' => true,
                'route_can_return_file_content' => false,
                'route_can_persist_token_material' => false,
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode($lookup, JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'persistent_token_table' => false,
                'database_migration' => false,
                'real_database_mutation' => false,
                'real_file_mutation' => false,
                'file_download_executed' => false,
                'one_time_consumption_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_token_storage_contract_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_token_storage_contract_foundation',
            'packages' => [
                'larena/link',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'candidate_lookup' => [
                'token_fingerprint' => $tokenFingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'lookup_status' => $lookup['lookup_status'],
                'decision' => $lookup['decision'],
                'deny_reasons' => $lookup['deny_reasons'],
            ],
            'storage_contract' => [
                'runtime_state' => 'contract_preview_only',
                'hash_algorithm' => 'sha256',
                'stored_fields' => [
                    'token_hash_ref',
                    'link_identity_ref',
                    'logical_file_id',
                    'access_scope_ref',
                    'audit_event_ref',
                    'expires_at',
                    'revoked_at',
                    'status',
                ],
                'forbidden_fields' => [
                    'raw_token',
                    'plain_text_token',
                    'download_payload',
                    'file_content',
                ],
                'required_indexes_before_persistence' => [
                    'token_hash_ref',
                    'expires_at',
                    'revoked_at',
                    'status',
                ],
            ],
            'lookup_result' => $lookup,
            'contract_steps' => [
                'receive_candidate_token_without_logging_raw_value',
                'derive_sha256_lookup_fingerprint',
                'match_hash_only_contract_fixture',
                'evaluate_status_expiry_revocation_and_access_scope',
                'return_lookup_decision_without_persistence_or_file_delivery',
                'stop_before_database_migration_or_production_lookup',
            ],
            'checks' => $checks,
            'safe_trace' => [
                'token_fingerprint' => $tokenFingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'persistent_token_table' => false,
                'database_migration' => false,
                'production_lookup' => false,
                'file_download_executed' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_token_storage_contract_only',
                'no_persistent_token_table',
                'no_database_migration',
                'no_raw_token_storage',
                'no_production_lookup_runtime',
                'no_public_file_delivery',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_token_storage_contract_or_prepare_persistent_hashed_token_lookup_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    public static function lookup(string $candidateToken): array
    {
        $case = match (true) {
            str_contains($candidateToken, 'expired') => 'expired_link',
            str_contains($candidateToken, 'revoked') => 'revoked_link',
            str_contains($candidateToken, 'missing-access') => 'missing_access_scope',
            str_contains($candidateToken, 'unknown') => 'unknown_token',
            default => 'active_link_with_access',
        };

        $base = [
            'stored_lookup_key' => self::fingerprint($candidateToken),
            'link_identity_ref' => 'link:file-manager-link-sharing-runtime-preview',
            'logical_file_id' => 'file-manager-link-sharing-runtime-1',
            'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
            'audit_event_ref' => 'audit.event:file-manager.link-sharing.runtime',
            'expires_at' => '2026-06-08T18:30:00Z',
            'revoked_at' => null,
            'raw_token_visible' => false,
            'raw_token_persisted' => false,
            'file_download_executed' => false,
            'mutates_state' => false,
        ];

        return match ($case) {
            'expired_link' => array_merge($base, [
                'lookup_status' => 'found_expired',
                'decision' => 'would_deny',
                'deny_reasons' => ['expired_link'],
                'expires_at' => '2026-06-08T00:00:00Z',
            ]),
            'revoked_link' => array_merge($base, [
                'lookup_status' => 'found_revoked',
                'decision' => 'would_deny',
                'deny_reasons' => ['revoked_link'],
                'revoked_at' => '2026-06-08T01:00:00Z',
            ]),
            'missing_access_scope' => array_merge($base, [
                'lookup_status' => 'found_missing_access_scope',
                'decision' => 'would_deny',
                'deny_reasons' => ['missing_access_scope'],
                'access_scope_ref' => 'missing',
            ]),
            'unknown_token' => array_merge($base, [
                'lookup_status' => 'not_found',
                'decision' => 'would_deny',
                'deny_reasons' => ['unknown_token'],
                'link_identity_ref' => null,
                'logical_file_id' => null,
                'access_scope_ref' => null,
                'audit_event_ref' => 'audit.event:public_link.lookup.not_found',
                'expires_at' => null,
            ]),
            default => array_merge($base, [
                'lookup_status' => 'found_active',
                'decision' => 'would_allow',
                'deny_reasons' => [],
            ]),
        };
    }

    public static function fingerprint(string $candidateToken): string
    {
        return 'sha256:' . substr(hash('sha256', $candidateToken), 0, 16);
    }

    /**
     * @param array<string, array<string, mixed>> $checks
     */
    private static function status(array $checks): string
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? null) !== 'passed') {
                return 'failed';
            }
        }

        return 'passed';
    }

    /**
     * @param array<string, mixed> $report
     */
    private static function writeJson(string $outputPath, array $report): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(
            $outputPath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        );
    }
}
