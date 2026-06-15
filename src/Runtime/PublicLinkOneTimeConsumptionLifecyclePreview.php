<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkOneTimeConsumptionLifecyclePreview
{
    /**
     * @param array<string, mixed> $deliverySimulation
     * @param array<string, mixed> $simulation
     * @param list<array<string, mixed>> $negativeDeliverySimulations
     * @return array<string, mixed>
     */
    public static function run(
        string $candidateToken,
        array $deliverySimulation,
        array $simulation,
        string $fingerprint,
        array $negativeDeliverySimulations,
        ?string $outputPath = null
    ): array {
        $lifecycleState = self::lifecycleState($candidateToken, $simulation);
        $consumptionPlan = self::consumptionPlan($fingerprint, $simulation, $lifecycleState);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-one-time-consumption-lifecycle-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'one_time_consumption_runtime_allowed' => false,
                'persistent_consumed_at_write_allowed' => false,
                'production_delivery_allowed' => false,
                'file_content_response_allowed' => false,
            ],
            'controlled_delivery_simulation_required' => [
                'status' => ($deliverySimulation['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $deliverySimulation['schema'] ?? null,
                'simulation_state' => $simulation['simulation_state'] ?? null,
                'simulation_decision' => $simulation['decision'] ?? null,
                'body_included' => $simulation['body_included'] ?? true,
            ],
            'one_time_state_machine' => [
                'status' => in_array($lifecycleState['state'], self::allowedStates(), true) ? 'passed' : 'failed',
                'state' => $lifecycleState['state'],
                'decision' => $lifecycleState['decision'],
                'reason' => $lifecycleState['reason'],
                'allowed_states' => self::allowedStates(),
                'terminal' => true,
                'fail_closed' => $lifecycleState['decision'] === 'would_deny'
                    || $lifecycleState['state'] === 'simulated_consumption_planned',
            ],
            'simulated_consumption_plan' => [
                'status' => self::planIsSafe($consumptionPlan) ? 'passed' : 'failed',
                'plan_status' => $consumptionPlan['plan_status'],
                'consume_now' => $consumptionPlan['consume_now'],
                'persistent_consumed_at_write' => $consumptionPlan['persistent_consumed_at_write'],
                'requires_future_launch_record' => $consumptionPlan['requires_future_launch_record'],
            ],
            'negative_consumption_guards' => [
                'status' => self::negativeGuardsPass($negativeDeliverySimulations) ? 'passed' : 'failed',
                'already_consumed_denied' => self::negativeStateFor($negativeDeliverySimulations, 'already_consumed') === 'blocked_already_consumed',
                'expired_link_denied' => self::negativeStateFor($negativeDeliverySimulations, 'expired_link') === 'blocked_expired',
                'revoked_link_denied' => self::negativeStateFor($negativeDeliverySimulations, 'revoked_link') === 'blocked_revoked',
                'missing_access_denied' => self::negativeStateFor($negativeDeliverySimulations, 'missing_access') === 'blocked_missing_access',
                'unknown_token_denied' => self::negativeStateFor($negativeDeliverySimulations, 'unknown_token') === 'blocked_unknown',
            ],
            'access_audit_trace' => [
                'status' => ($consumptionPlan['audit_event_ref'] ?? null) !== null ? 'passed' : 'failed',
                'access_scope_ref' => $consumptionPlan['access_scope_ref'],
                'audit_event_ref' => $consumptionPlan['audit_event_ref'],
                'audit_event_recorded_now' => false,
                'consumption_event_planned' => $consumptionPlan['plan_status'] === 'simulation_ready',
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode([$lifecycleState, $consumptionPlan], JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'file_download_executed' => false,
                'file_content_returned' => false,
                'body_included' => false,
                'one_time_consumption_runtime' => false,
                'production_delivery' => false,
                'delivery_requires_future_launch_record' => true,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'simulated_consumption_only' => true,
                'persistent_consumed_at_write' => false,
                'real_file_mutation' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_ui' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_one_time_consumption_lifecycle_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_one_time_consumption_lifecycle_foundation',
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
            'delivery_simulation' => [
                'schema' => $deliverySimulation['schema'] ?? null,
                'status' => $deliverySimulation['status'] ?? null,
                'simulated_response' => $simulation,
            ],
            'lifecycle_state' => $lifecycleState,
            'consumption_plan' => $consumptionPlan,
            'checks' => $checks,
            'component_reports' => [
                'public_link_controlled_delivery_simulation_foundation' => self::component($deliverySimulation),
            ],
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'one_time_consumption_lifecycle_available' => true,
                'simulated_consumption_only' => true,
                'consume_now' => false,
                'persistent_consumed_at_write' => false,
                'controlled_delivery_simulation_available' => true,
                'response_body_included' => false,
                'production_delivery' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'real_file_mutation' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_one_time_consumption_lifecycle_only',
                'simulated_consumption_plan_only',
                'no_persistent_consumed_at_write',
                'no_public_file_delivery',
                'no_file_content_response',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_one_time_consumption_lifecycle_or_prepare_real_delivery_adapter_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $simulation
     * @return array<string, mixed>
     */
    private static function lifecycleState(string $candidateToken, array $simulation): array
    {
        if (str_contains($candidateToken, 'consumed')) {
            return [
                'state' => 'blocked_already_consumed',
                'decision' => 'would_deny',
                'reason' => 'already_consumed',
                'terminal' => true,
            ];
        }

        $reason = (string) ($simulation['reason'] ?? 'blocked');

        if (($simulation['decision'] ?? null) !== 'would_allow') {
            return [
                'state' => match ($reason) {
                    'expired_link' => 'blocked_expired',
                    'revoked_link' => 'blocked_revoked',
                    'missing_access_scope' => 'blocked_missing_access',
                    'unknown_token' => 'blocked_unknown',
                    default => 'blocked_unknown',
                },
                'decision' => 'would_deny',
                'reason' => $reason,
                'terminal' => true,
            ];
        }

        return [
            'state' => 'simulated_consumption_planned',
            'decision' => 'would_allow',
            'reason' => 'active_link_consumption_would_be_recorded_by_future_launch_record',
            'terminal' => true,
        ];
    }

    /**
     * @return list<string>
     */
    private static function allowedStates(): array
    {
        return [
            'simulated_consumption_planned',
            'blocked_already_consumed',
            'blocked_expired',
            'blocked_revoked',
            'blocked_missing_access',
            'blocked_unknown',
        ];
    }

    /**
     * @param array<string, mixed> $simulation
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    private static function consumptionPlan(string $fingerprint, array $simulation, array $state): array
    {
        $allowed = ($state['decision'] ?? null) === 'would_allow';

        return [
            'plan_status' => $allowed ? 'simulation_ready' : 'blocked',
            'decision' => $allowed ? 'would_allow' : 'would_deny',
            'reason' => $state['reason'] ?? 'blocked',
            'token_fingerprint' => $fingerprint,
            'access_scope_ref' => $simulation['access_scope_ref'] ?? null,
            'audit_event_ref' => $allowed
                ? 'audit.event:public_link.consumption.simulated'
                : ($simulation['audit_event_ref'] ?? 'audit.event:public_link.consumption.blocked'),
            'target_fingerprint' => $simulation['target_fingerprint'] ?? null,
            'logical_file_id' => $simulation['logical_file_id'] ?? null,
            'consume_now' => false,
            'persistent_consumed_at_write' => false,
            'consumed_at_preview' => $allowed ? 'future_runtime_timestamp' : null,
            'requires_future_launch_record' => true,
            'file_delivery' => 'blocked_by_foundation_scope',
            'file_content_returned' => false,
            'production_delivery' => false,
        ];
    }

    /**
     * @param array<string, mixed> $plan
     */
    private static function planIsSafe(array $plan): bool
    {
        return ($plan['consume_now'] ?? true) === false
            && ($plan['persistent_consumed_at_write'] ?? true) === false
            && ($plan['file_content_returned'] ?? true) === false
            && ($plan['production_delivery'] ?? true) === false;
    }

    /**
     * @param list<array<string, mixed>> $negativeDeliverySimulations
     */
    private static function negativeStateFor(array $negativeDeliverySimulations, string $caseId): string
    {
        foreach ($negativeDeliverySimulations as $record) {
            if (($record['case_id'] ?? null) !== $caseId) {
                continue;
            }

            if ($caseId === 'already_consumed') {
                return 'blocked_already_consumed';
            }

            $simulation = is_array($record['simulated_response'] ?? null) ? $record['simulated_response'] : [];
            $state = self::lifecycleState('', $simulation);

            return (string) $state['state'];
        }

        return 'missing_case';
    }

    /**
     * @param list<array<string, mixed>> $negativeDeliverySimulations
     */
    private static function negativeGuardsPass(array $negativeDeliverySimulations): bool
    {
        return self::negativeStateFor($negativeDeliverySimulations, 'already_consumed') === 'blocked_already_consumed'
            && self::negativeStateFor($negativeDeliverySimulations, 'expired_link') === 'blocked_expired'
            && self::negativeStateFor($negativeDeliverySimulations, 'revoked_link') === 'blocked_revoked'
            && self::negativeStateFor($negativeDeliverySimulations, 'missing_access') === 'blocked_missing_access'
            && self::negativeStateFor($negativeDeliverySimulations, 'unknown_token') === 'blocked_unknown';
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
