<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRuntimeHardeningPreview
{
    /**
     * @param array<string, mixed> $dryRun
     * @param array<string, mixed> $tokenStorage
     * @param array<string, mixed> $persistentLookup
     * @param array<string, mixed> $deliveryReadiness
     * @param array<string, mixed> $deliverySimulation
     * @param array<string, mixed> $consumptionLifecycle
     * @param array<string, mixed> $deliveryAdapter
     * @return array<string, mixed>
     */
    public static function run(
        array $dryRun,
        array $tokenStorage,
        array $persistentLookup,
        array $deliveryReadiness,
        array $deliverySimulation,
        array $consumptionLifecycle,
        array $deliveryAdapter,
        string $candidateToken = 'active-preview-token',
        ?string $outputPath = null
    ): array {
        $persistentMutatesState = ($persistentLookup['mutates_state'] ?? false) === true
            || ($deliveryReadiness['mutates_state'] ?? false) === true
            || ($deliverySimulation['mutates_state'] ?? false) === true
            || ($consumptionLifecycle['mutates_state'] ?? false) === true
            || ($deliveryAdapter['mutates_state'] ?? false) === true;
        $case = self::caseForToken($candidateToken, $dryRun['dry_run_cases'] ?? []);
        $fingerprint = self::fingerprint($candidateToken);
        $decision = (string) ($deliveryReadiness['delivery_decision']['decision'] ?? $persistentLookup['lookup_result']['decision'] ?? $case['decision'] ?? 'would_deny');
        $denyReasons = ['unknown_token_case'];
        if (is_array($deliveryReadiness['delivery_decision']['deny_reasons'] ?? null)) {
            $denyReasons = $deliveryReadiness['delivery_decision']['deny_reasons'];
        } elseif (is_array($persistentLookup['lookup_result']['deny_reasons'] ?? null)) {
            $denyReasons = $persistentLookup['lookup_result']['deny_reasons'];
        } elseif (is_array($case['deny_reasons'] ?? null)) {
            $denyReasons = $case['deny_reasons'];
        }
        $storageDecision = (string) ($tokenStorage['lookup_result']['decision'] ?? 'would_deny');
        $persistentDecision = (string) ($persistentLookup['lookup_result']['decision'] ?? 'would_deny');
        $deliveryDecision = (string) ($deliveryReadiness['delivery_decision']['decision'] ?? 'would_deny');
        $simulationDecision = (string) ($deliverySimulation['simulated_response']['decision'] ?? 'would_deny');
        $consumptionDecision = (string) ($consumptionLifecycle['consumption_plan']['decision'] ?? 'would_deny');
        $adapterDecision = (string) ($deliveryAdapter['adapter_decision']['decision'] ?? 'would_deny');

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-runtime-hardening-foundation.json',
                'ready_to_code' => true,
                'production_runtime_allowed' => false,
                'release_ready_claim_allowed' => false,
            ],
            'route_hardening_contract' => [
                'status' => 'passed',
                'route_shape' => '/larena/link/{token}',
                'route_registered_for_local_testing' => true,
                'returns_file_content' => false,
                'returns_decision_trace_only' => true,
                'requires_future_production_launch_record' => true,
            ],
            'token_redaction' => [
                'status' => str_contains(json_encode($case, JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
                'raw_token_visible' => false,
                'token_fingerprint' => $fingerprint,
                'raw_token_storage_allowed' => false,
                'token_material_persisted' => false,
            ],
            'access_recheck' => [
                'status' => ($case['access_rechecked'] ?? false) === true ? 'passed' : 'failed',
                'owner_package' => 'larena/access',
                'access_scope_ref' => $case['access_scope_ref'] ?? null,
                'missing_access_scope_blocked' => self::decisionFor($dryRun, 'missing_access_scope') === 'would_deny',
            ],
            'token_storage_contract' => [
                'status' => ($tokenStorage['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $tokenStorage['schema'] ?? null,
                'lookup_status' => $tokenStorage['lookup_result']['lookup_status'] ?? null,
                'lookup_decision' => $storageDecision,
                'token_fingerprint' => $tokenStorage['candidate_lookup']['token_fingerprint'] ?? $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'persistent_token_table' => true,
                'database_migration' => true,
                'production_lookup' => false,
            ],
            'persistent_lookup_foundation' => [
                'status' => ($persistentLookup['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $persistentLookup['schema'] ?? null,
                'lookup_status' => $persistentLookup['lookup_result']['lookup_status'] ?? null,
                'lookup_decision' => $persistentDecision,
                'token_fingerprint' => $persistentLookup['candidate_lookup']['token_fingerprint'] ?? $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'persistent_token_table' => true,
                'production_lookup' => false,
                'file_delivery' => 'blocked_by_foundation_scope',
            ],
            'guarded_delivery_readiness' => [
                'status' => ($deliveryReadiness['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $deliveryReadiness['schema'] ?? null,
                'delivery_state' => $deliveryReadiness['delivery_state']['state'] ?? null,
                'delivery_decision' => $deliveryDecision,
                'would_deliver_sandbox_target' => $deliveryReadiness['delivery_decision']['would_deliver_sandbox_target'] ?? false,
                'target_fingerprint' => $deliveryReadiness['target_proof']['target_fingerprint'] ?? null,
                'file_delivery' => 'blocked_by_foundation_scope',
                'file_content_returned' => false,
            ],
            'controlled_delivery_simulation' => [
                'status' => ($deliverySimulation['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $deliverySimulation['schema'] ?? null,
                'simulation_state' => $deliverySimulation['simulated_response']['simulation_state'] ?? null,
                'simulation_decision' => $simulationDecision,
                'http_status_preview' => $deliverySimulation['simulated_response']['http_status_preview'] ?? null,
                'body_included' => $deliverySimulation['simulated_response']['body_included'] ?? true,
                'file_delivery' => 'blocked_by_foundation_scope',
                'file_content_returned' => false,
            ],
            'one_time_consumption_lifecycle' => [
                'status' => ($consumptionLifecycle['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $consumptionLifecycle['schema'] ?? null,
                'lifecycle_state' => $consumptionLifecycle['lifecycle_state']['state'] ?? null,
                'consumption_decision' => $consumptionDecision,
                'plan_status' => $consumptionLifecycle['consumption_plan']['plan_status'] ?? null,
                'consume_now' => $consumptionLifecycle['consumption_plan']['consume_now'] ?? true,
                'persistent_consumed_at_write' => $consumptionLifecycle['consumption_plan']['persistent_consumed_at_write'] ?? true,
                'file_delivery' => 'blocked_by_foundation_scope',
                'file_content_returned' => false,
            ],
            'guarded_real_delivery_adapter' => [
                'status' => ($deliveryAdapter['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $deliveryAdapter['schema'] ?? null,
                'adapter_state' => $deliveryAdapter['adapter_decision']['adapter_state'] ?? null,
                'adapter_decision' => $adapterDecision,
                'adapter_id' => $deliveryAdapter['adapter_decision']['adapter_id'] ?? null,
                'stream_now' => $deliveryAdapter['adapter_decision']['stream_now'] ?? true,
                'adapter_stream_invoked' => $deliveryAdapter['adapter_decision']['adapter_stream_invoked'] ?? true,
                'file_body_included' => $deliveryAdapter['adapter_decision']['file_body_included'] ?? true,
                'file_delivery' => 'blocked_by_foundation_scope',
                'file_content_returned' => false,
            ],
            'audit_trace' => [
                'status' => ($case['audit_event_planned'] ?? false) === true ? 'passed' : 'failed',
                'owner_package' => 'larena/audit',
                'audit_event_ref' => $case['audit_event_ref'] ?? null,
                'audit_event_recorded_now' => false,
                'audit_runtime_required_before_delivery' => true,
            ],
            'expiry_revocation_guards' => [
                'status' => self::allPassed([
                    self::decisionFor($dryRun, 'expired_link') === 'would_deny' ? 'passed' : 'failed',
                    self::decisionFor($dryRun, 'revoked_link') === 'would_deny' ? 'passed' : 'failed',
                ]) ? 'passed' : 'failed',
                'expired_link_blocked' => true,
                'revoked_link_blocked' => true,
            ],
            'replay_nonce_rate_limit_guards' => [
                'status' => self::allPassed([
                    self::decisionFor($dryRun, 'replay_detected') === 'would_deny' ? 'passed' : 'failed',
                    self::decisionFor($dryRun, 'nonce_missing') === 'would_deny' ? 'passed' : 'failed',
                    self::decisionFor($dryRun, 'rate_limit_exceeded') === 'would_deny' ? 'passed' : 'failed',
                ]) ? 'passed' : 'failed',
                'replay_blocked' => true,
                'nonce_missing_blocked' => true,
                'rate_limit_exceeded_blocked' => true,
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'file_download_executed' => false,
                'file_content_returned' => false,
                'one_time_consumption_runtime' => false,
                'delivery_requires_future_launch_record' => true,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => $persistentMutatesState,
                'production_mutates_state' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => $persistentMutatesState,
                'token_storage_runtime' => false,
                'token_material_generated_now' => false,
                'file_download_executed' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_runtime_hardening_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => $persistentMutatesState,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_runtime_hardening_foundation',
            'packages' => [
                'larena/link',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'candidate_request' => [
                'route_shape' => '/larena/link/{token}',
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
            ],
            'resolution_decision' => [
                'case_id' => $case['id'] ?? 'unknown',
                'decision' => $decision,
                'token_storage_decision' => $storageDecision,
                'persistent_lookup_decision' => $persistentDecision,
                'delivery_readiness_decision' => $deliveryDecision,
                'controlled_delivery_simulation_decision' => $simulationDecision,
                'one_time_consumption_lifecycle_decision' => $consumptionDecision,
                'guarded_real_delivery_adapter_decision' => $adapterDecision,
                'deny_reasons' => $denyReasons,
                'explanation' => $case['explanation'] ?? 'Unknown candidate link state.',
                'http_status_preview' => $simulationDecision === 'would_allow' ? 202 : 403,
                'file_delivery' => 'blocked_by_foundation_scope',
            ],
            'simulated_response' => $deliverySimulation['simulated_response'] ?? null,
            'one_time_consumption' => [
                'lifecycle_state' => $consumptionLifecycle['lifecycle_state'] ?? null,
                'consumption_plan' => $consumptionLifecycle['consumption_plan'] ?? null,
            ],
            'guarded_real_delivery_adapter' => [
                'adapter_decision' => $deliveryAdapter['adapter_decision'] ?? null,
            ],
            'hardening_steps' => [
                'accept_candidate_token_without_logging_raw_value',
                'derive_redacted_token_fingerprint',
                'resolve_hash_only_token_storage_contract',
                'resolve_persistent_hash_lookup_foundation',
                'map_candidate_to_resolution_case',
                'recheck_access_expiry_revocation_and_audit',
                'evaluate_replay_nonce_and_rate_limit_guards',
                'evaluate_guarded_real_delivery_adapter_contract',
                'return_decision_trace_without_file_delivery',
                'stop_before_token_storage_one_time_consumption_or_public_download',
            ],
            'checks' => $checks,
            'component_reports' => [
                'public_link_dry_run_runtime' => self::component($dryRun),
                'public_link_token_storage_contract' => self::component($tokenStorage),
                'public_link_persistent_lookup_foundation' => self::component($persistentLookup),
                'public_link_guarded_delivery_readiness_foundation' => self::component($deliveryReadiness),
                'public_link_controlled_delivery_simulation_foundation' => self::component($deliverySimulation),
                'public_link_one_time_consumption_lifecycle_foundation' => self::component($consumptionLifecycle),
                'public_link_guarded_real_delivery_adapter_foundation' => self::component($deliveryAdapter),
            ],
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'route_registered_for_local_testing' => true,
                'token_storage_contract_available' => true,
                'persistent_lookup_available' => true,
                'guarded_delivery_readiness_available' => true,
                'controlled_delivery_simulation_available' => true,
                'one_time_consumption_lifecycle_available' => true,
                'guarded_real_delivery_adapter_available' => true,
                'simulated_response_only' => true,
                'simulated_consumption_only' => true,
                'real_delivery_adapter_contract_only' => true,
                'adapter_stream_invoked' => false,
                'stream_now' => false,
                'consume_now' => false,
                'persistent_consumed_at_write' => false,
                'token_storage_enabled_now' => false,
                'persistent_token_table' => true,
                'database_migration' => true,
                'production_lookup' => false,
                'would_deliver_sandbox_target' => $deliveryReadiness['delivery_decision']['would_deliver_sandbox_target'] ?? false,
                'sandbox_target_proof_only' => true,
                'response_body_included' => false,
                'token_material_generated_now' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => $persistentMutatesState,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_public_link_runtime_hardening_foundation_only',
                'no_public_file_delivery',
                'hash_only_token_storage_contract_preview_only',
                'persistent_lookup_foundation_local_testing_only',
                'no_one_time_consumption_runtime',
                'no_real_file_mutation',
                'no_real_database_mutation',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_public_link_token_storage_contract_or_prepare_persistent_hashed_token_lookup_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param mixed $cases
     * @return array<string, mixed>
     */
    private static function caseForToken(string $candidateToken, mixed $cases): array
    {
        $caseId = match (true) {
            str_contains($candidateToken, 'expired') => 'expired_link',
            str_contains($candidateToken, 'revoked') => 'revoked_link',
            str_contains($candidateToken, 'missing-access') => 'missing_access_scope',
            str_contains($candidateToken, 'replay') => 'replay_detected',
            str_contains($candidateToken, 'nonce-missing') => 'nonce_missing',
            str_contains($candidateToken, 'rate-limited') => 'rate_limit_exceeded',
            str_contains($candidateToken, 'unknown') => 'unknown',
            default => 'active_link_with_access',
        };

        if (is_array($cases)) {
            foreach ($cases as $case) {
                if (is_array($case) && ($case['id'] ?? null) === $caseId) {
                    return $case;
                }
            }
        }

        return [
            'id' => 'unknown',
            'decision' => 'would_deny',
            'deny_reasons' => ['unknown_token_case'],
            'access_rechecked' => false,
            'audit_event_planned' => false,
            'explanation' => 'The candidate token could not be matched to a known safe dry-run case.',
        ];
    }

    /**
     * @param array<string, mixed> $dryRun
     */
    private static function decisionFor(array $dryRun, string $caseId): ?string
    {
        $cases = $dryRun['dry_run_cases'] ?? [];
        if (!is_array($cases)) {
            return null;
        }

        foreach ($cases as $case) {
            if (is_array($case) && ($case['id'] ?? null) === $caseId) {
                return is_string($case['decision'] ?? null) ? $case['decision'] : null;
            }
        }

        return null;
    }

    private static function fingerprint(string $candidateToken): string
    {
        return 'sha256:' . substr(hash('sha256', $candidateToken), 0, 16);
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
