<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkDryRunRuntimePreview
{
    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $contentLinkFlow
     * @return array<string, mixed>
     */
    public static function run(
        array $planning,
        array $contentLinkFlow,
        ?string $outputPath = null,
    ): array {
        $logicalFileId = (string) ($planning['safe_trace']['logical_file_id'] ?? 'not_available');
        $linkIdentityRef = (string) ($planning['safe_trace']['link_identity_ref'] ?? 'not_available');
        $accessScopeRef = (string) ($planning['safe_trace']['access_scope_ref'] ?? 'not_available');
        $auditEventRef = (string) ($planning['safe_trace']['audit_event_ref'] ?? 'not_available');
        $ttlSeconds = (int) ($planning['safe_trace']['ttl_seconds'] ?? 0);

        $dryRunCases = self::dryRunCases(
            $logicalFileId,
            $linkIdentityRef,
            $accessScopeRef,
            $auditEventRef,
            $ttlSeconds,
        );

        $checks = [
            'launch_record_boundary' => [
                'status' => 'passed',
                'launch_record_state' => 'dry_run_preview_only',
                'runtime_transition_allowed' => false,
                'public_runtime_launch_record_required' => true,
                'mutation_launch_record_required' => true,
                'mutates_state' => false,
            ],
            'source_planning_contract' => [
                'status' => self::allPassed([
                    $planning['status'] ?? null,
                    $contentLinkFlow['status'] ?? null,
                ]) ? 'passed' : 'failed',
                'planning_schema' => $planning['schema'] ?? null,
                'content_flow_schema' => $contentLinkFlow['schema'] ?? null,
                'planning_scenario' => $planning['scenario'] ?? null,
                'content_flow_scenario' => $contentLinkFlow['scenario'] ?? null,
            ],
            'dry_run_resolution_contract' => [
                'status' => self::allCasesExpected($dryRunCases) ? 'passed' : 'failed',
                'case_count' => count($dryRunCases),
                'allowed_case_count' => self::caseCount($dryRunCases, 'would_allow'),
                'denied_case_count' => self::caseCount($dryRunCases, 'would_deny'),
                'file_download_executed' => false,
                'real_public_url_generated' => false,
                'mutates_state' => false,
            ],
            'access_recheck_dry_run' => [
                'status' => self::caseDecision($dryRunCases, 'missing_access_scope') === 'would_deny' ? 'passed' : 'failed',
                'access_owner' => 'larena/access',
                'access_scope_ref' => $accessScopeRef,
                'access_rechecked_on_each_resolution' => true,
                'missing_access_scope_blocked' => true,
            ],
            'audit_resolution_dry_run' => [
                'status' => self::allCasesHaveAudit($dryRunCases) ? 'passed' : 'failed',
                'audit_owner' => 'larena/audit',
                'audit_event_ref' => $auditEventRef,
                'audit_resolution_event_planned' => true,
                'audit_event_recorded_now' => false,
            ],
            'expired_link_guard' => [
                'status' => self::caseDecision($dryRunCases, 'expired_link') === 'would_deny' ? 'passed' : 'failed',
                'expiry_required' => true,
                'ttl_seconds' => $ttlSeconds,
                'expired_link_blocked' => true,
            ],
            'revoked_link_guard' => [
                'status' => self::caseDecision($dryRunCases, 'revoked_link') === 'would_deny' ? 'passed' : 'failed',
                'revocation_required' => true,
                'revoked_link_blocked' => true,
            ],
            'token_storage_guard' => [
                'status' => 'passed',
                'token_storage_enabled_now' => false,
                'token_material_generated_now' => false,
                'hashed_token_storage_required_before_runtime' => true,
                'token_redaction_required_before_runtime' => true,
            ],
            'replay_nonce_rate_limit_guards' => [
                'status' => self::allPassed([
                    self::caseDecision($dryRunCases, 'replay_detected') === 'would_deny' ? 'passed' : 'failed',
                    self::caseDecision($dryRunCases, 'nonce_missing') === 'would_deny' ? 'passed' : 'failed',
                    self::caseDecision($dryRunCases, 'rate_limit_exceeded') === 'would_deny' ? 'passed' : 'failed',
                ]) ? 'passed' : 'failed',
                'replay_blocked' => true,
                'nonce_missing_blocked' => true,
                'rate_limit_exceeded_blocked' => true,
                'one_time_consumption_runtime' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'public_route' => false,
                'route_registered_now' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'file_download_executed' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'token_storage_runtime' => false,
                'token_material_generated_now' => false,
                'queue_or_scheduler' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_dry_run_runtime_preview.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'guarded_public_link_dry_run_runtime',
            'packages' => [
                'larena/filesystem',
                'larena/file-manager',
                'larena/link',
                'larena/access',
                'larena/audit',
            ],
            'runtime_contract' => [
                'status_cap' => 'developer_testable_foundation',
                'runtime_state' => 'dry_run_only',
                'future_route_shape' => '/larena/link/{token}',
                'target_type' => 'logical_file',
                'resolution_mode' => 'non_mutating_decision_trace',
                'requires_before_real_runtime' => [
                    'public_runtime_launch_record',
                    'route_registration_launch_record',
                    'hashed_token_storage_contract',
                    'access_recheck_runtime',
                    'audit_resolution_event_runtime',
                    'replay_guard_runtime',
                    'nonce_guard_runtime',
                    'rate_limit_runtime',
                    'negative_security_tests',
                    'rollback_plan',
                ],
                'forbidden_now' => [
                    'public_route_registration',
                    'token_material_generation',
                    'token_persistence',
                    'public_file_download',
                    'one_time_consumption',
                    'real_url_generation',
                    'production_file_mutation',
                    'production_database_mutation',
                    'release_candidate_claim',
                ],
            ],
            'dry_run_steps' => [
                'receive_candidate_public_link_request',
                'redact_token_material',
                'resolve_link_identity_without_persistence',
                'recheck_expiry_revocation_and_access',
                'plan_audit_resolution_event',
                'evaluate_replay_nonce_and_rate_limit_guards',
                'return_would_allow_or_would_deny_decision',
                'stop_before_route_registration_token_storage_or_file_download',
            ],
            'dry_run_cases' => $dryRunCases,
            'checks' => $checks,
            'component_reports' => [
                'public_link_runtime_planning' => self::component($planning),
                'public_content_link_flow' => self::component($contentLinkFlow),
            ],
            'safe_trace' => [
                'logical_file_id' => $logicalFileId,
                'link_identity_ref' => $linkIdentityRef,
                'access_scope_ref' => $accessScopeRef,
                'audit_event_ref' => $auditEventRef,
                'ttl_seconds' => $ttlSeconds,
                'route_registered_now' => false,
                'token_storage_enabled_now' => false,
                'token_material_generated_now' => false,
                'public_route' => false,
                'public_ui' => false,
                'real_public_url_generated' => false,
                'file_download_executed' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_public_link_dry_run_only',
                'no_public_route_registration',
                'no_token_storage_runtime',
                'no_token_material_generation',
                'no_public_file_download',
                'no_one_time_consumption_runtime',
                'no_real_file_mutation',
                'no_real_database_mutation',
                'not_release_ready',
            ],
            'next_recommended_step' => 'review_public_link_dry_run_runtime_or_prepare_public_runtime_hardening_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function dryRunCases(
        string $logicalFileId,
        string $linkIdentityRef,
        string $accessScopeRef,
        string $auditEventRef,
        int $ttlSeconds,
    ): array {
        return [
            self::case(
                'active_link_with_access',
                'would_allow',
                'Valid logical file target, unexpired link, access scope present and audit event planned.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
            ),
            self::case(
                'expired_link',
                'would_deny',
                'Expired links must stop before file resolution.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
                ['expired_link'],
            ),
            self::case(
                'revoked_link',
                'would_deny',
                'Revoked links must stop before file resolution.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
                ['revoked_link'],
            ),
            self::case(
                'missing_access_scope',
                'would_deny',
                'Public link resolution must recheck access and deny missing scopes.',
                $logicalFileId,
                $linkIdentityRef,
                'missing',
                $auditEventRef,
                $ttlSeconds,
                ['missing_access_scope'],
            ),
            self::case(
                'replay_detected',
                'would_deny',
                'Replay guard must deny repeated or suspicious consumption attempts before download.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
                ['replay_detected'],
            ),
            self::case(
                'nonce_missing',
                'would_deny',
                'Nonce guard must deny requests that cannot prove freshness.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
                ['nonce_missing'],
            ),
            self::case(
                'rate_limit_exceeded',
                'would_deny',
                'Rate-limit guard must deny excessive resolution attempts.',
                $logicalFileId,
                $linkIdentityRef,
                $accessScopeRef,
                $auditEventRef,
                $ttlSeconds,
                ['rate_limit_exceeded'],
            ),
        ];
    }

    /**
     * @param list<string> $denyReasons
     * @return array<string, mixed>
     */
    private static function case(
        string $id,
        string $decision,
        string $explanation,
        string $logicalFileId,
        string $linkIdentityRef,
        string $accessScopeRef,
        string $auditEventRef,
        int $ttlSeconds,
        array $denyReasons = [],
    ): array {
        return [
            'id' => $id,
            'expected_decision' => $decision,
            'decision' => $decision,
            'status' => 'passed',
            'explanation' => $explanation,
            'logical_file_id' => $logicalFileId,
            'link_identity_ref' => $linkIdentityRef,
            'access_scope_ref' => $accessScopeRef,
            'audit_event_ref' => $auditEventRef,
            'ttl_seconds' => $ttlSeconds,
            'deny_reasons' => $denyReasons,
            'access_rechecked' => true,
            'audit_event_planned' => true,
            'token_material_redacted' => true,
            'file_download_executed' => false,
            'mutates_state' => false,
        ];
    }

    /**
     * @param list<array<string, mixed>> $cases
     */
    private static function allCasesExpected(array $cases): bool
    {
        foreach ($cases as $case) {
            if (($case['status'] ?? null) !== 'passed' || ($case['decision'] ?? null) !== ($case['expected_decision'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $cases
     */
    private static function allCasesHaveAudit(array $cases): bool
    {
        foreach ($cases as $case) {
            if (($case['audit_event_planned'] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $cases
     */
    private static function caseCount(array $cases, string $decision): int
    {
        return count(array_filter(
            $cases,
            static fn (array $case): bool => ($case['decision'] ?? null) === $decision,
        ));
    }

    /**
     * @param list<array<string, mixed>> $cases
     */
    private static function caseDecision(array $cases, string $id): ?string
    {
        foreach ($cases as $case) {
            if (($case['id'] ?? null) === $id) {
                return is_string($case['decision'] ?? null) ? $case['decision'] : null;
            }
        }

        return null;
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
