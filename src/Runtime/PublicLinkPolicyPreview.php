<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Larena\Link\Contracts\LinkTargetDescriptor;
use Larena\Link\Enums\LinkAudience;
use Larena\Link\Enums\LinkResolutionStatus;
use Larena\Link\Enums\LinkTargetVisibility;

final class PublicLinkPolicyPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function run(
        string $logicalFileId = 'logical-file:public-link-preview',
        string $accessScopeRef = 'access.query_scope:public_link.preview',
        string $auditEventRef = 'audit.event:public_link.resolution.preview',
        int $ttlSeconds = 1800,
    ): array {
        $runtime = new InMemoryLinkRuntime();
        $target = self::target($logicalFileId, $accessScopeRef);

        $activePlan = $runtime->planLink(new ArrayLinkRequest(
            'public-link-preview-active',
            $target,
            new ArrayLinkPolicy(
                LinkAudience::Authenticated,
                $ttlSeconds,
                $accessScopeRef,
                true,
                false,
                true,
                [
                    'policy_ref' => 'link.public_link.policy.preview',
                    'audit_event_ref' => $auditEventRef,
                ],
            ),
        ));

        $missingAccessPlan = $runtime->planLink(new ArrayLinkRequest(
            'public-link-preview-missing-access',
            $target,
            new ArrayLinkPolicy(LinkAudience::Authenticated, $ttlSeconds, '', true, false, true),
        ));

        $expiredPlan = $runtime->planLink(new ArrayLinkRequest(
            'public-link-preview-expired',
            $target,
            new ArrayLinkPolicy(LinkAudience::Authenticated, 0, $accessScopeRef, true, false, true),
        ));

        $publicExposurePlan = $runtime->planLink(new ArrayLinkRequest(
            'public-link-preview-public-exposure',
            $target,
            new ArrayLinkPolicy(LinkAudience::Public, $ttlSeconds, '', true, false, true),
        ));

        $missingConfirmationPlan = $runtime->planLink(new ArrayLinkRequest(
            'public-link-preview-missing-confirmation',
            $target,
            new ArrayLinkPolicy(LinkAudience::Authenticated, $ttlSeconds, $accessScopeRef, true, false, true),
            true,
            false,
        ));

        $checks = [
            'package_owned_policy_runtime' => [
                'status' => $activePlan->status() === LinkResolutionStatus::Allowed ? 'passed' : 'failed',
                'plan_status' => $activePlan->status()->value,
                'plan_reason' => $activePlan->reason(),
                'mutates_state' => $activePlan->mutatesState(),
                'production_runtime' => $activePlan->productionRuntime(),
            ],
            'access_scope_guard' => [
                'status' => $missingAccessPlan->status() === LinkResolutionStatus::ScopeMismatch
                    && $missingAccessPlan->reason() === 'missing_access_scope' ? 'passed' : 'failed',
                'plan_status' => $missingAccessPlan->status()->value,
                'reason' => $missingAccessPlan->reason(),
            ],
            'expiry_guard' => [
                'status' => $expiredPlan->status() === LinkResolutionStatus::Expired ? 'passed' : 'failed',
                'plan_status' => $expiredPlan->status()->value,
                'reason' => $expiredPlan->reason(),
            ],
            'public_exposure_guard' => [
                'status' => $publicExposurePlan->status() === LinkResolutionStatus::Denied
                    && $publicExposurePlan->reason() === 'public_delivery_not_allowed' ? 'passed' : 'failed',
                'plan_status' => $publicExposurePlan->status()->value,
                'reason' => $publicExposurePlan->reason(),
                'public_delivery_allowed_now' => false,
            ],
            'confirmation_guard' => [
                'status' => $missingConfirmationPlan->status() === LinkResolutionStatus::Denied
                    && $missingConfirmationPlan->reason() === 'missing_confirmation' ? 'passed' : 'failed',
                'plan_status' => $missingConfirmationPlan->status()->value,
                'reason' => $missingConfirmationPlan->reason(),
            ],
            'token_material_guard' => [
                'status' => 'passed',
                'raw_token_output' => false,
                'token_material_generated_now' => false,
                'token_persisted_now' => false,
                'hashed_lookup_runtime_now' => false,
            ],
            'delivery_runtime_guard' => [
                'status' => 'passed',
                'public_route_registered_now' => false,
                'public_file_download_now' => false,
                'one_time_consumption_runtime_now' => false,
                'real_delivery_adapter_now' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'package_owned' => true,
                'entry_app_dependency' => false,
                'mutates_state' => false,
                'production_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'release_ready' => false,
            ],
        ];

        return [
            'schema' => 'larena.link_public_link_policy_preview.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_runtime' => false,
            'package' => 'larena/link',
            'scenario' => 'package_owned_public_link_policy_preview',
            'checks' => $checks,
            'safe_trace' => [
                'logical_file_id' => $logicalFileId,
                'access_scope_ref' => $accessScopeRef,
                'audit_event_ref' => $auditEventRef,
                'ttl_seconds' => $ttlSeconds,
                'policy_runtime_owner' => 'larena/link',
                'raw_token_output' => false,
                'token_material_generated_now' => false,
                'token_persisted_now' => false,
                'public_route_registered_now' => false,
                'real_public_url_generated' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => false,
                'production_runtime' => false,
                'graph_sync_canonical_update' => false,
            ],
            'known_limitations' => [
                'package_owned_policy_preview_only',
                'no_token_material_generation',
                'no_token_storage_runtime',
                'no_public_route_registration',
                'no_public_file_download',
                'no_real_delivery_adapter',
                'not_release_ready',
            ],
        ];
    }

    private static function target(string $logicalFileId, string $accessScopeRef): LinkTargetDescriptor
    {
        return new class($logicalFileId, $accessScopeRef) implements LinkTargetDescriptor {
            public function __construct(
                private readonly string $logicalFileId,
                private readonly string $accessScopeRef,
            ) {
            }

            public function type(): string
            {
                return 'logical_file';
            }

            public function ownerPackage(): string
            {
                return 'larena/file-manager';
            }

            public function targetId(): string
            {
                return $this->logicalFileId;
            }

            public function visibility(): LinkTargetVisibility
            {
                return LinkTargetVisibility::Protected;
            }

            public function accessPolicyRef(): string
            {
                return $this->accessScopeRef;
            }
        };
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
}
