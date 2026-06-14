<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicContentLinkFlowPreview
{
    /**
     * @param array<string, mixed> $fileOperation
     * @param array<string, mixed> $fileManagerLink
     * @param array<string, mixed> $linkSafety
     * @return array<string, mixed>
     */
    public static function run(
        array $fileOperation,
        array $fileManagerLink,
        array $linkSafety,
        ?string $outputPath = null,
    ): array {
        $logicalFileId = (string) ($fileManagerLink['safe_trace']['logical_file_id'] ?? 'not_available');
        $linkIdentityRef = (string) ($fileManagerLink['safe_trace']['link_identity_ref'] ?? 'not_available');
        $accessScopeRef = (string) ($fileManagerLink['safe_trace']['access_scope_ref'] ?? 'not_available');
        $auditEventRef = (string) ($fileManagerLink['safe_trace']['audit_event_ref'] ?? 'not_available');
        $ttlSeconds = (int) ($fileManagerLink['safe_trace']['ttl_seconds'] ?? 0);

        $checks = [
            'content_file_target' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['logical_file_target']['status'] ?? null,
                    $linkSafety['checks']['filesystem_logical_file']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'target_type' => 'logical_file',
                'logical_file_id' => $logicalFileId,
                'owner_package' => 'larena/filesystem',
                'metadata_redacted' => $linkSafety['checks']['filesystem_logical_file']['metadata_redacted'] ?? null,
                'runtime_state' => 'in_memory_only',
                'mutates_state' => false,
            ],
            'file_manager_share_plan' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['file_manager_share_intake']['status'] ?? null,
                    $linkSafety['checks']['file_manager_share_plan']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'share_status' => $fileManagerLink['checks']['file_manager_share_intake']['share_status'] ?? null,
                'share_explain_code' => $fileManagerLink['checks']['file_manager_share_intake']['share_explain_code'] ?? null,
                'owner_package' => 'larena/file-manager',
                'mutates_state' => false,
            ],
            'temporary_link_contract' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['temporary_link_policy']['status'] ?? null,
                    $linkSafety['checks']['temporary_link_planning']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'link_identity_ref' => $linkIdentityRef,
                'owner_package' => 'larena/link',
                'audience' => $fileManagerLink['checks']['temporary_link_policy']['audience'] ?? null,
                'temporary' => $fileManagerLink['checks']['temporary_link_policy']['temporary'] ?? null,
                'ttl_seconds' => $ttlSeconds,
                'revocable' => $fileManagerLink['checks']['temporary_link_policy']['revocable'] ?? null,
                'real_public_url_generated' => false,
                'token_storage_runtime' => false,
                'mutates_state' => false,
            ],
            'expiry_access_audit_contract' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['expiry_policy_guard']['status'] ?? null,
                    $fileManagerLink['checks']['access_boundary']['status'] ?? null,
                    $fileManagerLink['checks']['audit_boundary']['status'] ?? null,
                    $linkSafety['checks']['access_boundary']['status'] ?? null,
                    $linkSafety['checks']['audit_boundary']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'expiry_required' => true,
                'access_owner' => 'larena/access',
                'access_scope_ref' => $accessScopeRef,
                'audit_owner' => 'larena/audit',
                'audit_event_ref' => $auditEventRef,
                'mutates_state' => false,
            ],
            'revocation_contract' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['revocation_policy_guard']['status'] ?? null,
                    $linkSafety['checks']['revocation_planning']['status'] ?? null,
                    $linkSafety['checks']['revocation_confirmation_guard']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'revocation_status' => $fileManagerLink['checks']['revocation_policy_guard']['revocation_status'] ?? null,
                'confirmation_required' => true,
                'revocation_without_confirmation_blocked' => true,
                'production_runtime' => false,
                'mutates_state' => false,
            ],
            'public_runtime_guards' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['public_exposure_guard']['status'] ?? null,
                    $fileManagerLink['checks']['confirmation_guard']['status'] ?? null,
                    $fileManagerLink['checks']['link_missing_access_scope_guard']['status'] ?? null,
                    $linkSafety['checks']['public_exposure_guard']['status'] ?? null,
                    $linkSafety['checks']['confirmation_guard']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'public_route' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'token_storage_runtime' => false,
                'one_time_consumption_runtime' => false,
                'missing_access_scope_blocked' => true,
                'missing_confirmation_blocked' => true,
                'mutates_state' => false,
            ],
            'guarded_file_operation_boundary' => [
                'status' => ($fileOperation['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'file_operation_scenario' => $fileOperation['scenario'] ?? null,
                'sandbox_state_mutated' => $fileOperation['sandbox_state_mutated'] ?? false,
                'production_file_mutation' => $fileOperation['safe_trace']['real_file_mutation'] ?? true,
                'production_database_mutation' => $fileOperation['safe_trace']['real_database_mutation'] ?? true,
                'public_route' => $fileOperation['safe_trace']['public_route'] ?? true,
            ],
            'scope_boundary' => [
                'status' => self::allPassed([
                    $fileManagerLink['checks']['scope_boundary']['status'] ?? null,
                    $linkSafety['checks']['scope_boundary']['status'] ?? null,
                    $fileOperation['checks']['scope_boundary']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'sandbox_state_mutated_by_component' => true,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'public_route' => false,
                'public_ui' => false,
                'production_runtime' => false,
                'queue_or_scheduler' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_content_link_flow_preview.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'sandbox_state_mutated_by_component' => true,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'guarded_public_content_file_link_flow',
            'packages' => [
                'larena/storage',
                'larena/filesystem',
                'larena/file-manager',
                'larena/link',
                'larena/access',
                'larena/audit',
            ],
            'flow_contract' => [
                'source' => 'logical_content_file',
                'target_type' => 'temporary_authenticated_link',
                'status_cap' => 'developer_testable_foundation',
                'requires' => [
                    'logical_file_target',
                    'file_manager_share_plan',
                    'temporary_link_contract',
                    'expiry_policy',
                    'access_scope',
                    'audit_event',
                    'revocation_plan',
                    'public_runtime_guards',
                ],
                'forbidden' => [
                    'public_route',
                    'public_ui',
                    'real_public_url_generation',
                    'token_storage_runtime',
                    'one_time_consumption_runtime',
                    'real_file_mutation',
                    'real_database_mutation',
                    'release_candidate_claim',
                ],
            ],
            'flow_steps' => [
                'logical_content_file_selected',
                'file_manager_share_plan_prepared',
                'temporary_link_contract_checked',
                'expiry_policy_checked',
                'access_scope_declared',
                'audit_event_declared',
                'revocation_plan_checked',
                'public_exposure_blocked',
                'missing_confirmation_blocked',
                'stopped_before_public_runtime',
            ],
            'checks' => $checks,
            'component_reports' => [
                'file_operation_guarded_flow' => self::component($fileOperation),
                'file_manager_link_sharing' => self::component($fileManagerLink),
                'link_sharing_safety' => self::component($linkSafety),
            ],
            'safe_trace' => [
                'logical_file_id' => $logicalFileId,
                'link_identity_ref' => $linkIdentityRef,
                'access_scope_ref' => $accessScopeRef,
                'audit_event_ref' => $auditEventRef,
                'ttl_seconds' => $ttlSeconds,
                'content_file_target_available' => true,
                'share_plan_available' => true,
                'temporary_link_contract_available' => true,
                'expiry_required' => true,
                'revocation_available' => true,
                'public_route' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'token_storage_runtime' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'production_runtime' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_public_content_link_flow_only',
                'no_public_route',
                'no_public_ui',
                'no_real_public_url',
                'no_token_storage_runtime',
                'no_one_time_consumption_runtime',
                'no_real_file_mutation',
                'no_real_database_mutation',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_public_content_link_flow_or_next_guarded_data_content_batch',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param list<mixed> $statuses
     */
    private static function allPassed(array $statuses): bool
    {
        foreach ($statuses as $status) {
            if ($status !== 'passed') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $checks
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
     * @return array<string, mixed>
     */
    private static function component(array $report): array
    {
        return [
            'schema' => $report['schema'] ?? null,
            'status' => $report['status'] ?? null,
            'scenario' => $report['scenario'] ?? null,
            'mutates_state' => $report['mutates_state'] ?? false,
            'sandbox_state_mutated' => $report['sandbox_state_mutated'] ?? false,
            'production_mutates_state' => $report['production_mutates_state'] ?? false,
        ];
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
