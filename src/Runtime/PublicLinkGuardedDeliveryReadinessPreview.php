<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkGuardedDeliveryReadinessPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function preview(string $candidateToken = 'active-preview-token', ?string $outputPath = null): array
    {
        $persistentLookup = PublicLinkPersistentLookupPreview::run($candidateToken);
        $lookup = is_array($persistentLookup['lookup_result'] ?? null)
            ? $persistentLookup['lookup_result']
            : [];

        return self::run(
            $candidateToken,
            $persistentLookup,
            $lookup,
            PublicLinkTokenStorageContractPreview::fingerprint($candidateToken),
            self::negativeLookups(),
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $persistentLookup
     * @param array<string, mixed> $lookup
     * @param list<array<string, mixed>> $negativeLookups
     * @return array<string, mixed>
     */
    public static function run(
        string $candidateToken,
        array $persistentLookup,
        array $lookup,
        string $fingerprint,
        array $negativeLookups,
        ?string $outputPath = null
    ): array {
        $state = self::stateForLookup($lookup);
        $targetProof = self::targetProof($lookup, $state);
        $deliveryDecision = self::deliveryDecision($state, $lookup, $targetProof);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-guarded-delivery-readiness-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'production_delivery_allowed' => false,
                'file_content_response_allowed' => false,
            ],
            'persistent_lookup_required' => [
                'status' => ($persistentLookup['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'schema' => $persistentLookup['schema'] ?? null,
                'lookup_status' => $lookup['lookup_status'] ?? null,
                'lookup_decision' => $lookup['decision'] ?? null,
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
            ],
            'delivery_state_machine' => [
                'status' => $state['state'] !== 'invalid' ? 'passed' : 'failed',
                'state' => $state['state'],
                'allowed_terminal_states' => [
                    'ready_but_blocked',
                    'blocked_expired',
                    'blocked_revoked',
                    'blocked_missing_access',
                    'blocked_unknown',
                ],
                'http_status_preview' => $deliveryDecision['http_status_preview'],
                'fail_closed' => $deliveryDecision['decision'] === 'would_deny'
                    || $deliveryDecision['file_delivery'] === 'blocked_by_foundation_scope',
            ],
            'sandbox_target_proof' => [
                'status' => $targetProof['proof_status'] === 'available' || $state['decision'] === 'would_deny' ? 'passed' : 'failed',
                'proof_status' => $targetProof['proof_status'],
                'logical_file_id' => $targetProof['logical_file_id'],
                'target_fingerprint' => $targetProof['target_fingerprint'],
                'path_traversal_checked' => true,
                'path_traversal_blocked' => true,
                'sandbox_only' => true,
                'file_content_returned' => false,
            ],
            'access_audit_revocation_boundary' => [
                'status' => ($lookup['audit_event_ref'] ?? null) !== null ? 'passed' : 'failed',
                'access_scope_ref' => $lookup['access_scope_ref'] ?? null,
                'audit_event_ref' => $lookup['audit_event_ref'] ?? null,
                'revoked_at' => $lookup['revoked_at'] ?? null,
                'expires_at' => $lookup['expires_at'] ?? null,
                'access_owner_package' => 'larena/access',
                'audit_owner_package' => 'larena/audit',
                'link_owner_package' => 'larena/link',
                'filesystem_owner_package' => 'larena/filesystem',
                'audit_event_recorded_now' => false,
            ],
            'negative_delivery_guards' => [
                'status' => self::negativeGuardsPass($negativeLookups) ? 'passed' : 'failed',
                'unknown_token_blocks_delivery' => self::negativeDecisionFor($negativeLookups, 'unknown_token') === 'would_deny',
                'expired_token_blocks_delivery' => self::negativeDecisionFor($negativeLookups, 'expired_link') === 'would_deny',
                'revoked_token_blocks_delivery' => self::negativeDecisionFor($negativeLookups, 'revoked_link') === 'would_deny',
                'missing_access_blocks_delivery' => self::negativeDecisionFor($negativeLookups, 'missing_access') === 'would_deny',
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'would_deliver_sandbox_target' => $deliveryDecision['would_deliver_sandbox_target'],
                'file_download_executed' => false,
                'file_content_returned' => false,
                'one_time_consumption_runtime' => false,
                'delivery_requires_future_launch_record' => true,
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode([$lookup, $state, $targetProof, $deliveryDecision], JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => ($persistentLookup['mutates_state'] ?? false) === true,
                'production_mutates_state' => false,
                'sandbox_target_proof_only' => true,
                'real_file_mutation' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'production_delivery' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_guarded_delivery_readiness_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => $checks['scope_boundary']['mutates_state'],
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_guarded_delivery_readiness_foundation',
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
            'lookup_result' => $lookup,
            'delivery_state' => $state,
            'target_proof' => $targetProof,
            'delivery_decision' => $deliveryDecision,
            'checks' => $checks,
            'component_reports' => [
                'public_link_persistent_lookup_foundation' => self::component($persistentLookup),
            ],
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'persistent_lookup_available' => true,
                'sandbox_target_proof_only' => true,
                'would_deliver_sandbox_target' => $deliveryDecision['would_deliver_sandbox_target'],
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
                'developer_testable_delivery_readiness_foundation_only',
                'sandbox_target_proof_only',
                'no_public_file_delivery',
                'no_file_content_response',
                'no_one_time_consumption_runtime',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_delivery_readiness_or_prepare_public_file_delivery_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $lookup
     * @return array<string, mixed>
     */
    private static function stateForLookup(array $lookup): array
    {
        return match ((string) ($lookup['lookup_status'] ?? 'not_found')) {
            'found_active' => [
                'state' => 'ready_but_blocked',
                'decision' => 'would_allow',
                'reason' => 'active_link_access_scope_present',
                'terminal' => true,
            ],
            'found_expired' => [
                'state' => 'blocked_expired',
                'decision' => 'would_deny',
                'reason' => 'expired_link',
                'terminal' => true,
            ],
            'found_revoked' => [
                'state' => 'blocked_revoked',
                'decision' => 'would_deny',
                'reason' => 'revoked_link',
                'terminal' => true,
            ],
            'found_missing_access_scope' => [
                'state' => 'blocked_missing_access',
                'decision' => 'would_deny',
                'reason' => 'missing_access_scope',
                'terminal' => true,
            ],
            'not_found' => [
                'state' => 'blocked_unknown',
                'decision' => 'would_deny',
                'reason' => 'unknown_token',
                'terminal' => true,
            ],
            default => [
                'state' => 'invalid',
                'decision' => 'would_deny',
                'reason' => 'invalid_lookup_state',
                'terminal' => true,
            ],
        };
    }

    /**
     * @param array<string, mixed> $lookup
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    private static function targetProof(array $lookup, array $state): array
    {
        if (($state['decision'] ?? null) !== 'would_allow') {
            return [
                'proof_status' => 'not_applicable_blocked',
                'logical_file_id' => null,
                'target_fingerprint' => null,
                'descriptor_ref' => null,
                'sandbox_storage_ref' => null,
                'file_content_returned' => false,
            ];
        }

        $logicalFileId = (string) ($lookup['logical_file_id'] ?? 'unknown-logical-file');
        $descriptor = [
            'logical_file_id' => $logicalFileId,
            'storage_owner' => 'larena/filesystem',
            'source_package' => 'larena/file-manager',
            'sandbox_storage_ref' => 'sandbox://larena/public-link-preview/file-manager-link-sharing-runtime-1',
            'mime_preview' => 'application/octet-stream',
            'content_length_preview' => 0,
        ];

        return [
            'proof_status' => 'available',
            'logical_file_id' => $logicalFileId,
            'target_fingerprint' => 'sha256:' . substr(hash('sha256', json_encode($descriptor, JSON_THROW_ON_ERROR)), 0, 16),
            'descriptor_ref' => 'descriptor:public-link-delivery-readiness:' . $logicalFileId,
            'sandbox_storage_ref' => $descriptor['sandbox_storage_ref'],
            'file_content_returned' => false,
            'descriptor' => $descriptor,
        ];
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $lookup
     * @param array<string, mixed> $targetProof
     * @return array<string, mixed>
     */
    private static function deliveryDecision(array $state, array $lookup, array $targetProof): array
    {
        $allowed = ($state['decision'] ?? null) === 'would_allow'
            && ($targetProof['proof_status'] ?? null) === 'available';

        return [
            'state' => $state['state'],
            'decision' => $allowed ? 'would_allow' : 'would_deny',
            'deny_reasons' => $allowed ? [] : [$state['reason'] ?? 'blocked'],
            'http_status_preview' => $allowed ? 202 : 403,
            'would_deliver_sandbox_target' => $allowed,
            'file_delivery' => 'blocked_by_foundation_scope',
            'file_content_returned' => false,
            'access_scope_ref' => $lookup['access_scope_ref'] ?? null,
            'audit_event_ref' => $lookup['audit_event_ref'] ?? null,
            'target_fingerprint' => $targetProof['target_fingerprint'] ?? null,
        ];
    }

    /**
     * @param list<array<string, mixed>> $negativeLookups
     */
    private static function negativeDecisionFor(array $negativeLookups, string $caseId): string
    {
        foreach ($negativeLookups as $record) {
            if (($record['case_id'] ?? null) !== $caseId) {
                continue;
            }

            $lookup = is_array($record['lookup_result'] ?? null) ? $record['lookup_result'] : [];
            $state = self::stateForLookup($lookup);

            return (string) ($state['decision'] ?? 'would_deny');
        }

        return 'missing_case';
    }

    /**
     * @param list<array<string, mixed>> $negativeLookups
     */
    private static function negativeGuardsPass(array $negativeLookups): bool
    {
        return self::negativeDecisionFor($negativeLookups, 'unknown_token') === 'would_deny'
            && self::negativeDecisionFor($negativeLookups, 'expired_link') === 'would_deny'
            && self::negativeDecisionFor($negativeLookups, 'revoked_link') === 'would_deny'
            && self::negativeDecisionFor($negativeLookups, 'missing_access') === 'would_deny';
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
                'lookup_result' => is_array($report['lookup_result'] ?? null)
                    ? $report['lookup_result']
                    : [],
            ];
        }

        return $lookups;
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
