<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkGuardedRealDeliveryAdapterPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function preview(string $candidateToken = 'active-preview-token', ?string $outputPath = null): array
    {
        $lifecycle = PublicLinkOneTimeConsumptionLifecyclePreview::preview($candidateToken);
        $lifecycleState = is_array($lifecycle['lifecycle_state'] ?? null)
            ? $lifecycle['lifecycle_state']
            : [];
        $consumptionPlan = is_array($lifecycle['consumption_plan'] ?? null)
            ? $lifecycle['consumption_plan']
            : [];

        return self::run(
            $candidateToken,
            $lifecycle,
            $lifecycleState,
            $consumptionPlan,
            PublicLinkTokenStorageContractPreview::fingerprint($candidateToken),
            self::negativeLifecycles(),
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $lifecycle
     * @param array<string, mixed> $lifecycleState
     * @param array<string, mixed> $consumptionPlan
     * @param list<array<string, mixed>> $negativeLifecycles
     * @return array<string, mixed>
     */
    public static function run(
        string $candidateToken,
        array $lifecycle,
        array $lifecycleState,
        array $consumptionPlan,
        string $fingerprint,
        array $negativeLifecycles,
        ?string $outputPath = null
    ): array {
        $adapter = self::adapterDecision($fingerprint, $lifecycleState, $consumptionPlan);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-guarded-real-delivery-adapter-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'real_file_delivery_allowed' => false,
                'file_streaming_allowed' => false,
                'file_content_response_allowed' => false,
                'persistent_consumed_at_write_allowed' => false,
                'production_delivery_allowed' => false,
            ],
            'one_time_lifecycle_required' => [
                'status' => ($lifecycle['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $lifecycle['schema'] ?? null,
                'lifecycle_state' => $lifecycleState['state'] ?? null,
                'lifecycle_decision' => $lifecycleState['decision'] ?? null,
                'consume_now' => $consumptionPlan['consume_now'] ?? true,
                'persistent_consumed_at_write' => $consumptionPlan['persistent_consumed_at_write'] ?? true,
            ],
            'adapter_contract' => [
                'status' => self::adapterContractIsSafe($adapter) ? 'passed' : 'failed',
                'adapter_state' => $adapter['adapter_state'],
                'adapter_id' => $adapter['adapter_id'],
                'decision' => $adapter['decision'],
                'stream_now' => $adapter['stream_now'],
                'file_body_included' => $adapter['file_body_included'],
                'content_length_known' => $adapter['content_length_known'],
                'requires_future_launch_record' => $adapter['requires_future_launch_record'],
            ],
            'negative_adapter_guards' => [
                'status' => self::negativeAdapterGuardsPass($negativeLifecycles) ? 'passed' : 'failed',
                'already_consumed_blocked' => self::adapterStateFor($negativeLifecycles, 'already_consumed') === 'adapter_blocked_already_consumed',
                'expired_link_blocked' => self::adapterStateFor($negativeLifecycles, 'expired_link') === 'adapter_blocked_expired',
                'revoked_link_blocked' => self::adapterStateFor($negativeLifecycles, 'revoked_link') === 'adapter_blocked_revoked',
                'missing_access_blocked' => self::adapterStateFor($negativeLifecycles, 'missing_access') === 'adapter_blocked_missing_access',
                'unknown_token_blocked' => self::adapterStateFor($negativeLifecycles, 'unknown_token') === 'adapter_blocked_unknown',
            ],
            'access_audit_delivery_trace' => [
                'status' => ($adapter['audit_event_ref'] ?? null) !== null
                    && ($adapter['access_scope_ref'] ?? null) !== null
                    ? 'passed'
                    : 'failed',
                'access_scope_ref' => $adapter['access_scope_ref'],
                'audit_event_ref' => $adapter['audit_event_ref'],
                'adapter_invocation_event_recorded_now' => false,
                'adapter_invocation_planned' => $adapter['adapter_state'] === 'adapter_ready_preview',
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode([$adapter, $lifecycleState, $consumptionPlan], JSON_THROW_ON_ERROR), $candidateToken)
                    ? 'failed'
                    : 'passed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'adapter_stream_invoked' => false,
                'stream_now' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'file_body_included' => false,
                'persistent_consumed_at_write' => false,
                'production_delivery' => false,
                'delivery_requires_future_launch_record' => true,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'real_delivery_adapter_contract_only' => true,
                'real_file_delivery' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'adapter_stream_invoked' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_ui' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_guarded_real_delivery_adapter_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_guarded_real_delivery_adapter_foundation',
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
            'lifecycle_gate' => [
                'schema' => $lifecycle['schema'] ?? null,
                'status' => $lifecycle['status'] ?? null,
                'state' => $lifecycleState,
                'consumption_plan' => $consumptionPlan,
            ],
            'adapter_decision' => $adapter,
            'checks' => $checks,
            'component_reports' => [
                'public_link_one_time_consumption_lifecycle_foundation' => self::component($lifecycle),
            ],
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'guarded_real_delivery_adapter_available' => true,
                'real_delivery_adapter_contract_only' => true,
                'adapter_stream_invoked' => false,
                'stream_now' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'file_body_included' => false,
                'persistent_consumed_at_write' => false,
                'production_delivery' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_real_delivery_adapter_contract_only',
                'adapter_metadata_only',
                'no_file_streaming',
                'no_file_content_response',
                'no_persistent_consumed_at_write',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_guarded_real_delivery_adapter_or_prepare_admin_operator_lifecycle_management',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    private static function adapterDecision(string $fingerprint, array $state, array $plan): array
    {
        $allowed = ($state['decision'] ?? null) === 'would_allow'
            && ($state['state'] ?? null) === 'simulated_consumption_planned'
            && ($plan['plan_status'] ?? null) === 'simulation_ready';
        $reason = (string) ($state['reason'] ?? $plan['reason'] ?? 'blocked');
        $blockedState = self::blockedAdapterState((string) ($state['state'] ?? 'blocked_unknown'));

        return [
            'adapter_state' => $allowed ? 'adapter_ready_preview' : $blockedState,
            'decision' => $allowed ? 'would_allow' : 'would_deny',
            'reason' => $allowed ? 'lifecycle_gate_passed_adapter_metadata_ready' : $reason,
            'adapter_id' => $allowed ? 'larena.filesystem.public_link_sandbox_delivery_adapter' : null,
            'adapter_contract_ref' => 'larena/filesystem:public_link_delivery_adapter.v1',
            'token_fingerprint' => $fingerprint,
            'access_scope_ref' => $plan['access_scope_ref'] ?? 'access.query_scope:public_link.blocked',
            'audit_event_ref' => $allowed
                ? 'audit.event:public_link.delivery_adapter.invocation.simulated'
                : 'audit.event:public_link.delivery_adapter.blocked',
            'target_fingerprint' => $plan['target_fingerprint'] ?? null,
            'logical_file_id' => $plan['logical_file_id'] ?? null,
            'headers_preview' => [
                'X-Larena-Delivery-Adapter' => $allowed ? 'filesystem-sandbox-preview' : 'blocked',
                'X-Larena-File-Body' => 'blocked',
                'X-Larena-Production-Delivery' => 'false',
            ],
            'content_disposition_preview' => $allowed ? 'attachment; filename="sandbox-preview.bin"' : null,
            'content_length_known' => false,
            'adapter_stream_invoked' => false,
            'stream_now' => false,
            'file_body_included' => false,
            'file_content_returned' => false,
            'file_download_executed' => false,
            'persistent_consumed_at_write' => false,
            'production_delivery' => false,
            'requires_future_launch_record' => true,
        ];
    }

    private static function blockedAdapterState(string $lifecycleState): string
    {
        return match ($lifecycleState) {
            'blocked_already_consumed' => 'adapter_blocked_already_consumed',
            'blocked_expired' => 'adapter_blocked_expired',
            'blocked_revoked' => 'adapter_blocked_revoked',
            'blocked_missing_access' => 'adapter_blocked_missing_access',
            default => 'adapter_blocked_unknown',
        };
    }

    /**
     * @param array<string, mixed> $adapter
     */
    private static function adapterContractIsSafe(array $adapter): bool
    {
        return ($adapter['adapter_state'] ?? null) !== null
            && ($adapter['stream_now'] ?? true) === false
            && ($adapter['adapter_stream_invoked'] ?? true) === false
            && ($adapter['file_body_included'] ?? true) === false
            && ($adapter['file_content_returned'] ?? true) === false
            && ($adapter['persistent_consumed_at_write'] ?? true) === false
            && ($adapter['production_delivery'] ?? true) === false
            && ($adapter['requires_future_launch_record'] ?? false) === true;
    }

    /**
     * @param list<array<string, mixed>> $negativeLifecycles
     */
    private static function negativeAdapterGuardsPass(array $negativeLifecycles): bool
    {
        return self::adapterStateFor($negativeLifecycles, 'already_consumed') === 'adapter_blocked_already_consumed'
            && self::adapterStateFor($negativeLifecycles, 'expired_link') === 'adapter_blocked_expired'
            && self::adapterStateFor($negativeLifecycles, 'revoked_link') === 'adapter_blocked_revoked'
            && self::adapterStateFor($negativeLifecycles, 'missing_access') === 'adapter_blocked_missing_access'
            && self::adapterStateFor($negativeLifecycles, 'unknown_token') === 'adapter_blocked_unknown';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function negativeLifecycles(): array
    {
        $cases = [
            'already_consumed' => 'consumed-preview-token',
            'expired_link' => 'expired-preview-token',
            'revoked_link' => 'revoked-preview-token',
            'missing_access' => 'missing-access-preview-token',
            'unknown_token' => 'unknown-preview-token',
        ];
        $reports = [];

        foreach ($cases as $caseId => $candidate) {
            $lifecycle = PublicLinkOneTimeConsumptionLifecyclePreview::preview($candidate);

            $reports[] = [
                'case_id' => $caseId,
                'fingerprint' => PublicLinkTokenStorageContractPreview::fingerprint($candidate),
                'lifecycle_state' => is_array($lifecycle['lifecycle_state'] ?? null)
                    ? $lifecycle['lifecycle_state']
                    : [],
                'consumption_plan' => is_array($lifecycle['consumption_plan'] ?? null)
                    ? $lifecycle['consumption_plan']
                    : [],
            ];
        }

        return $reports;
    }

    /**
     * @param list<array<string, mixed>> $negativeLifecycles
     */
    private static function adapterStateFor(array $negativeLifecycles, string $caseId): string
    {
        foreach ($negativeLifecycles as $record) {
            if (($record['case_id'] ?? null) !== $caseId) {
                continue;
            }

            $state = is_array($record['lifecycle_state'] ?? null) ? $record['lifecycle_state'] : [];
            $plan = is_array($record['consumption_plan'] ?? null) ? $record['consumption_plan'] : [];
            $adapter = self::adapterDecision((string) ($record['fingerprint'] ?? ''), $state, $plan);

            return (string) ($adapter['adapter_state'] ?? 'unknown');
        }

        return 'missing';
    }

    /**
     * @param array<string, mixed> $checks
     */
    private static function status(array $checks): string
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? null) === 'failed') {
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
            'mutates_state' => $report['mutates_state'] ?? null,
            'production_runtime' => $report['safe_trace']['production_runtime'] ?? null,
            'release_ready' => $report['safe_trace']['release_ready'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $report
     */
    private static function writeJson(string $path, array $report): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
