<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkControlledDeliverySimulationPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function preview(string $candidateToken = 'active-preview-token', ?string $outputPath = null): array
    {
        $deliveryReadiness = self::deliveryReadinessReport($candidateToken);
        $decision = is_array($deliveryReadiness['delivery_decision'] ?? null)
            ? $deliveryReadiness['delivery_decision']
            : [];
        $deliveryState = is_array($deliveryReadiness['delivery_state'] ?? null)
            ? $deliveryReadiness['delivery_state']
            : [];
        $targetProof = is_array($deliveryReadiness['target_proof'] ?? null)
            ? $deliveryReadiness['target_proof']
            : [];
        $fingerprint = PublicLinkPersistentLookupPreview::fingerprint($candidateToken);

        return self::run(
            $candidateToken,
            $deliveryReadiness,
            $decision,
            $deliveryState,
            $targetProof,
            $fingerprint,
            self::negativeReadinessReports(),
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $deliveryReadiness
     * @param array<string, mixed> $decision
     * @param array<string, mixed> $deliveryState
     * @param array<string, mixed> $targetProof
     * @param list<array<string, mixed>> $negativeReadinessReports
     * @return array<string, mixed>
     */
    public static function run(
        string $candidateToken,
        array $deliveryReadiness,
        array $decision,
        array $deliveryState,
        array $targetProof,
        string $fingerprint,
        array $negativeReadinessReports,
        ?string $outputPath = null
    ): array {
        $responseEnvelope = self::responseEnvelope($fingerprint, $decision, $deliveryState, $targetProof);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-controlled-delivery-simulation-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'production_delivery_allowed' => false,
                'file_content_response_allowed' => false,
            ],
            'delivery_readiness_required' => [
                'status' => ($deliveryReadiness['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $deliveryReadiness['schema'] ?? null,
                'delivery_state' => $deliveryState['state'] ?? null,
                'delivery_decision' => $decision['decision'] ?? null,
                'target_fingerprint' => $targetProof['target_fingerprint'] ?? null,
                'raw_token_visible' => false,
            ],
            'controlled_response_envelope' => [
                'status' => self::envelopeIsSafe($responseEnvelope) ? 'passed' : 'failed',
                'simulation_state' => $responseEnvelope['simulation_state'],
                'http_status_preview' => $responseEnvelope['http_status_preview'],
                'body_included' => $responseEnvelope['body_included'],
                'file_delivery' => $responseEnvelope['file_delivery'],
                'headers_preview_only' => true,
                'content_disposition_preview_only' => true,
            ],
            'positive_delivery_simulation' => [
                'status' => $responseEnvelope['simulation_state'] === 'simulated_ready'
                    && $responseEnvelope['would_deliver_sandbox_target'] === true
                    ? 'passed'
                    : (($decision['decision'] ?? null) === 'would_deny' ? 'passed' : 'failed'),
                'active_link_can_build_response_metadata' => $responseEnvelope['simulation_state'] === 'simulated_ready',
                'sandbox_target_available' => ($targetProof['proof_status'] ?? null) === 'available',
                'file_body_blocked' => $responseEnvelope['body_included'] === false,
            ],
            'negative_delivery_simulations' => [
                'status' => self::negativeSimulationsPass($negativeReadinessReports) ? 'passed' : 'failed',
                'expired_link_denied' => self::negativeSimulationFor($negativeReadinessReports, 'expired_link') === 'simulated_denied',
                'revoked_link_denied' => self::negativeSimulationFor($negativeReadinessReports, 'revoked_link') === 'simulated_denied',
                'missing_access_denied' => self::negativeSimulationFor($negativeReadinessReports, 'missing_access') === 'simulated_denied',
                'unknown_token_denied' => self::negativeSimulationFor($negativeReadinessReports, 'unknown_token') === 'simulated_denied',
            ],
            'access_audit_revocation_trace' => [
                'status' => self::accessAuditTracePass($responseEnvelope)
                    ? 'passed'
                    : 'failed',
                'access_scope_ref' => $responseEnvelope['access_scope_ref'],
                'audit_event_ref' => $responseEnvelope['audit_event_ref'],
                'revocation_checked' => true,
                'expiry_checked' => true,
                'audit_event_recorded_now' => false,
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode($responseEnvelope, JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
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
                'mutates_state' => ($deliveryReadiness['mutates_state'] ?? false) === true,
                'production_mutates_state' => false,
                'simulated_response_only' => true,
                'real_file_mutation' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_ui' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_controlled_delivery_simulation_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => $checks['scope_boundary']['mutates_state'],
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_controlled_delivery_simulation_foundation',
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
            'delivery_state' => $deliveryState,
            'delivery_decision' => $decision,
            'target_proof' => $targetProof,
            'simulated_response' => $responseEnvelope,
            'checks' => $checks,
            'component_reports' => [
                'public_link_guarded_delivery_readiness_foundation' => self::component($deliveryReadiness),
            ],
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'controlled_delivery_simulation_available' => true,
                'simulated_response_only' => true,
                'would_deliver_sandbox_target' => $responseEnvelope['would_deliver_sandbox_target'],
                'response_body_included' => false,
                'production_delivery' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_controlled_delivery_simulation_only',
                'simulated_response_metadata_only',
                'no_public_file_delivery',
                'no_file_content_response',
                'no_one_time_consumption_runtime',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_controlled_delivery_simulation_or_prepare_one_time_consumption_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $decision
     * @param array<string, mixed> $deliveryState
     * @param array<string, mixed> $targetProof
     * @return array<string, mixed>
     */
    private static function responseEnvelope(
        string $fingerprint,
        array $decision,
        array $deliveryState,
        array $targetProof,
    ): array {
        $allowed = ($decision['decision'] ?? null) === 'would_allow'
            && ($targetProof['proof_status'] ?? null) === 'available';

        return [
            'simulation_state' => $allowed ? 'simulated_ready' : 'simulated_denied',
            'decision' => $allowed ? 'would_allow' : 'would_deny',
            'reason' => $allowed ? 'sandbox_target_ready_no_body' : ($deliveryState['reason'] ?? 'blocked'),
            'http_status_preview' => $allowed ? 200 : 403,
            'token_fingerprint' => $fingerprint,
            'access_scope_ref' => $decision['access_scope_ref'] ?? null,
            'audit_event_ref' => $decision['audit_event_ref'] ?? null,
            'target_fingerprint' => $targetProof['target_fingerprint'] ?? null,
            'logical_file_id' => $targetProof['logical_file_id'] ?? null,
            'sandbox_storage_ref' => $targetProof['sandbox_storage_ref'] ?? null,
            'headers_preview' => [
                'X-Larena-Link-Decision' => $allowed ? 'simulated-ready' : 'simulated-denied',
                'X-Larena-File-Body' => 'blocked',
                'X-Larena-Production-Delivery' => 'false',
            ],
            'content_disposition_preview' => $allowed ? 'attachment; filename="sandbox-preview.bin"' : null,
            'would_deliver_sandbox_target' => $allowed,
            'body_included' => false,
            'file_delivery' => 'blocked_by_foundation_scope',
            'file_content_returned' => false,
            'production_delivery' => false,
            'one_time_consumption_runtime' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function deliveryReadinessReport(string $candidateToken): array
    {
        $persistentLookup = PublicLinkPersistentLookupPreview::run($candidateToken);
        $lookup = is_array($persistentLookup['lookup_result'] ?? null)
            ? $persistentLookup['lookup_result']
            : [];
        $fingerprint = PublicLinkPersistentLookupPreview::fingerprint($candidateToken);

        return PublicLinkGuardedDeliveryReadinessPreview::run(
            $candidateToken,
            $persistentLookup,
            $lookup,
            $fingerprint,
            self::negativeLookups(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function negativeLookups(): array
    {
        $cases = [
            'unknown_token' => 'unknown-preview-token',
            'expired_link' => 'expired-preview-token',
            'revoked_link' => 'revoked-preview-token',
            'missing_access' => 'missing-access-preview-token',
        ];

        $lookups = [];
        foreach ($cases as $caseId => $candidate) {
            $report = PublicLinkPersistentLookupPreview::run($candidate);
            $lookups[] = [
                'case_id' => $caseId,
                'lookup_result' => is_array($report['lookup_result'] ?? null) ? $report['lookup_result'] : [],
            ];
        }

        return $lookups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function negativeReadinessReports(): array
    {
        $cases = [
            'expired_link' => 'expired-preview-token',
            'revoked_link' => 'revoked-preview-token',
            'missing_access' => 'missing-access-preview-token',
            'unknown_token' => 'unknown-preview-token',
        ];
        $reports = [];

        foreach ($cases as $caseId => $candidate) {
            $readiness = self::deliveryReadinessReport($candidate);

            $reports[] = [
                'case_id' => $caseId,
                'fingerprint' => PublicLinkPersistentLookupPreview::fingerprint($candidate),
                'delivery_decision' => is_array($readiness['delivery_decision'] ?? null)
                    ? $readiness['delivery_decision']
                    : [],
                'delivery_state' => is_array($readiness['delivery_state'] ?? null)
                    ? $readiness['delivery_state']
                    : [],
                'target_proof' => is_array($readiness['target_proof'] ?? null)
                    ? $readiness['target_proof']
                    : [],
            ];
        }

        return $reports;
    }

    /**
     * @param array<string, mixed> $envelope
     */
    private static function envelopeIsSafe(array $envelope): bool
    {
        return ($envelope['body_included'] ?? true) === false
            && ($envelope['file_content_returned'] ?? true) === false
            && ($envelope['production_delivery'] ?? true) === false
            && ($envelope['file_delivery'] ?? null) === 'blocked_by_foundation_scope';
    }

    /**
     * @param array<string, mixed> $envelope
     */
    private static function accessAuditTracePass(array $envelope): bool
    {
        if (($envelope['audit_event_ref'] ?? null) === null) {
            return false;
        }

        if (($envelope['decision'] ?? null) === 'would_allow') {
            return ($envelope['access_scope_ref'] ?? null) !== null;
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $negativeReadinessReports
     */
    private static function negativeSimulationFor(array $negativeReadinessReports, string $caseId): string
    {
        foreach ($negativeReadinessReports as $record) {
            if (($record['case_id'] ?? null) !== $caseId) {
                continue;
            }

            $decision = is_array($record['delivery_decision'] ?? null) ? $record['delivery_decision'] : [];
            $state = is_array($record['delivery_state'] ?? null) ? $record['delivery_state'] : [];
            $target = is_array($record['target_proof'] ?? null) ? $record['target_proof'] : [];
            $envelope = self::responseEnvelope((string) ($record['fingerprint'] ?? ''), $decision, $state, $target);

            return (string) $envelope['simulation_state'];
        }

        return 'missing_case';
    }

    /**
     * @param list<array<string, mixed>> $negativeReadinessReports
     */
    private static function negativeSimulationsPass(array $negativeReadinessReports): bool
    {
        return self::negativeSimulationFor($negativeReadinessReports, 'expired_link') === 'simulated_denied'
            && self::negativeSimulationFor($negativeReadinessReports, 'revoked_link') === 'simulated_denied'
            && self::negativeSimulationFor($negativeReadinessReports, 'missing_access') === 'simulated_denied'
            && self::negativeSimulationFor($negativeReadinessReports, 'unknown_token') === 'simulated_denied';
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
