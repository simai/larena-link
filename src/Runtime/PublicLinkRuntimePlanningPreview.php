<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRuntimePlanningPreview
{
    /**
     * @param array<string, mixed> $contentLinkFlow
     * @param array<string, mixed> $fileManagerLink
     * @param array<string, mixed> $linkSafety
     * @return array<string, mixed>
     */
    public static function run(
        array $contentLinkFlow,
        array $fileManagerLink,
        array $linkSafety,
        ?string $outputPath = null,
    ): array {
        $logicalFileId = (string) ($contentLinkFlow['safe_trace']['logical_file_id'] ?? 'not_available');
        $linkIdentityRef = (string) ($contentLinkFlow['safe_trace']['link_identity_ref'] ?? 'not_available');
        $accessScopeRef = (string) ($contentLinkFlow['safe_trace']['access_scope_ref'] ?? 'not_available');
        $auditEventRef = (string) ($contentLinkFlow['safe_trace']['audit_event_ref'] ?? 'not_available');
        $ttlSeconds = (int) ($contentLinkFlow['safe_trace']['ttl_seconds'] ?? 0);

        $packagePolicy = PublicLinkPolicyPreview::run(
            $logicalFileId,
            $accessScopeRef,
            $auditEventRef,
            $ttlSeconds,
        );

        $checks = [
            'package_owned_policy_runtime' => [
                'status' => ($packagePolicy['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'package' => $packagePolicy['package'] ?? 'larena/link',
                'schema' => $packagePolicy['schema'] ?? null,
                'policy_runtime_owner' => $packagePolicy['safe_trace']['policy_runtime_owner'] ?? null,
                'raw_token_output' => $packagePolicy['safe_trace']['raw_token_output'] ?? false,
                'token_material_generated_now' => $packagePolicy['safe_trace']['token_material_generated_now'] ?? false,
                'token_persisted_now' => $packagePolicy['safe_trace']['token_persisted_now'] ?? false,
                'public_route_registered_now' => $packagePolicy['safe_trace']['public_route_registered_now'] ?? false,
                'real_delivery_adapter_now' => $packagePolicy['checks']['delivery_runtime_guard']['real_delivery_adapter_now'] ?? false,
            ],
            'source_flow_contract' => [
                'status' => ($contentLinkFlow['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'source_schema' => $contentLinkFlow['schema'] ?? null,
                'source_scenario' => $contentLinkFlow['scenario'] ?? null,
                'logical_file_id' => $logicalFileId,
                'link_identity_ref' => $linkIdentityRef,
                'mutates_state' => false,
            ],
            'future_route_gate' => [
                'status' => 'passed',
                'route_runtime_state' => 'blocked_pending_launch_record',
                'future_route_shape' => '/larena/link/{token}',
                'route_registered_now' => false,
                'public_route_enabled' => false,
                'requires' => [
                    'signed_or_unguessable_token_policy',
                    'rate_limit_policy',
                    'replay_guard',
                    'access_scope_recheck',
                    'audit_resolution_event',
                ],
            ],
            'token_policy_gate' => [
                'status' => 'passed',
                'token_runtime_state' => 'blocked_pending_storage_contract',
                'token_storage_enabled_now' => false,
                'token_material_generated_now' => $packagePolicy['safe_trace']['token_material_generated_now'] ?? false,
                'token_leaks_to_logs' => $packagePolicy['safe_trace']['raw_token_output'] ?? false,
                'requires' => [
                    'hashed_token_storage',
                    'token_redaction_in_logs',
                    'expiry_index',
                    'revocation_index',
                    'one_time_consumption_strategy',
                ],
            ],
            'expiry_access_audit_gate' => [
                'status' => self::allPassed([
                    $contentLinkFlow['checks']['expiry_access_audit_contract']['status'] ?? null,
                    $fileManagerLink['checks']['expiry_policy_guard']['status'] ?? null,
                    $linkSafety['checks']['temporary_link_planning']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'ttl_seconds' => $ttlSeconds,
                'access_scope_ref' => $accessScopeRef,
                'audit_event_ref' => $auditEventRef,
                'access_rechecked_on_resolution_required' => true,
                'audit_resolution_event_required' => true,
                'mutates_state' => false,
            ],
            'revocation_resolution_gate' => [
                'status' => self::allPassed([
                    $contentLinkFlow['checks']['revocation_contract']['status'] ?? null,
                    $fileManagerLink['checks']['revocation_policy_guard']['status'] ?? null,
                    $linkSafety['checks']['revocation_confirmation_guard']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'revocation_ref' => $linkIdentityRef,
                'revocation_runtime_state' => 'planned_only',
                'revoked_link_resolution_required' => 'denied',
                'missing_revocation_confirmation_blocked' => true,
                'mutates_state' => false,
            ],
            'negative_runtime_guards' => [
                'status' => self::allPassed([
                    $contentLinkFlow['checks']['public_runtime_guards']['status'] ?? null,
                    $fileManagerLink['checks']['public_exposure_guard']['status'] ?? null,
                    $linkSafety['checks']['public_exposure_guard']['status'] ?? null,
                    $linkSafety['checks']['confirmation_guard']['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'missing_access_scope_blocked' => true,
                'expired_link_blocked' => true,
                'missing_confirmation_blocked' => true,
                'public_exposure_without_policy_blocked' => true,
                'replay_guard_required_before_runtime' => true,
                'nonce_guard_required_before_runtime' => true,
                'rate_limit_required_before_runtime' => true,
            ],
            'promotion_boundary' => [
                'status' => 'passed',
                'next_allowed_stage' => 'public_link_dry_run_runtime_launch_record',
                'requires_launch_record_before_runtime' => true,
                'requires_migration_plan_before_token_storage' => true,
                'requires_security_tests_before_route_registration' => true,
                'requires_localization_plan_before_user_facing_ui' => true,
                'release_candidate_allowed' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'public_route' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'token_storage_runtime' => false,
                'token_material_generated_now' => $packagePolicy['safe_trace']['token_material_generated_now'] ?? false,
                'one_time_consumption_runtime' => false,
                'queue_or_scheduler' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_runtime_planning_preview.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'guarded_public_link_runtime_planning',
            'packages' => [
                'larena/filesystem',
                'larena/file-manager',
                'larena/link',
                'larena/access',
                'larena/audit',
            ],
            'runtime_contract' => [
                'status_cap' => 'developer_testable_foundation',
                'runtime_state' => 'planning_only',
                'future_route_shape' => '/larena/link/{token}',
                'target_type' => 'logical_file',
                'audience' => 'authenticated_or_policy_scoped_public',
                'requires' => [
                    'route_launch_record',
                    'hashed_token_storage_contract',
                    'expiry_index',
                    'revocation_index',
                    'access_recheck_on_resolution',
                    'audit_resolution_event',
                    'replay_guard',
                    'nonce_guard',
                    'rate_limit',
                    'negative_security_tests',
                ],
                'forbidden_now' => [
                    'public_route_registration',
                    'token_material_generation',
                    'token_persistence',
                    'public_file_download',
                    'one_time_consumption',
                    'production_file_mutation',
                    'production_database_mutation',
                    'release_candidate_claim',
                ],
            ],
            'planning_steps' => [
                'reuse_verified_content_file_link_flow',
                'define_future_public_route_gate',
                'define_token_storage_gate',
                'require_expiry_access_audit_on_resolution',
                'require_revocation_resolution_gate',
                'require_replay_nonce_rate_limit_guards',
                'stop_before_public_runtime',
            ],
            'checks' => $checks,
            'component_reports' => [
                'larena_link_policy_preview' => self::component($packagePolicy),
                'public_content_link_flow' => self::component($contentLinkFlow),
                'file_manager_link_sharing' => self::component($fileManagerLink),
                'link_sharing_safety' => self::component($linkSafety),
            ],
            'safe_trace' => [
                'logical_file_id' => $logicalFileId,
                'link_identity_ref' => $linkIdentityRef,
                'access_scope_ref' => $accessScopeRef,
                'audit_event_ref' => $auditEventRef,
                'ttl_seconds' => $ttlSeconds,
                'policy_runtime_owner' => $packagePolicy['safe_trace']['policy_runtime_owner'] ?? 'larena/link',
                'future_route_shape' => '/larena/link/{token}',
                'route_registered_now' => false,
                'token_storage_enabled_now' => false,
                'token_material_generated_now' => $packagePolicy['safe_trace']['token_material_generated_now'] ?? false,
                'raw_token_output' => $packagePolicy['safe_trace']['raw_token_output'] ?? false,
                'public_route' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'production_runtime' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_public_link_runtime_planning_only',
                'no_public_route_registration',
                'no_token_storage_runtime',
                'no_token_material_generation',
                'no_public_file_download',
                'no_one_time_consumption_runtime',
                'no_real_file_mutation',
                'no_real_database_mutation',
                'not_release_ready',
            ],
            'next_recommended_step' => 'prepare_public_link_dry_run_runtime_launch_record_or_review_planning_surface',
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
     * @return array<string, mixed>
     */
    private static function component(array $report): array
    {
        return [
            'schema' => $report['schema'] ?? null,
            'status' => $report['status'] ?? null,
            'scenario' => $report['scenario'] ?? null,
            'mutates_state' => $report['mutates_state'] ?? false,
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
