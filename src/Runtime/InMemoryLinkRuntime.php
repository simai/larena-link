<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Larena\Link\Enums\LinkAudience;
use Larena\Link\Enums\LinkResolutionStatus;

final class InMemoryLinkRuntime
{
    public function planLink(ArrayLinkRequest $request): ArrayLinkPlan
    {
        $target = $request->target();
        if ($target === null || trim($request->requestId()) === '') {
            return $this->deny(LinkResolutionStatus::UnknownTarget, 'missing_target_identity');
        }

        $policy = $request->policy();
        if ($policy === null) {
            return $this->deny(LinkResolutionStatus::Denied, 'missing_link_policy', [
                'target_type' => $target->type(),
                'owner_package' => $target->ownerPackage(),
            ]);
        }

        if ($target->visibility()->requiresAccessPolicy() && trim($target->accessPolicyRef()) === '') {
            return $this->deny(LinkResolutionStatus::AccessDenied, 'missing_target_access_policy');
        }

        if ($policy->audience()->requiresAccessScope() && trim($policy->accessScopeRef()) === '') {
            return $this->deny(LinkResolutionStatus::ScopeMismatch, 'missing_access_scope');
        }

        if ($policy->temporary() && (($policy->ttlSeconds() ?? 0) <= 0)) {
            return $this->deny(LinkResolutionStatus::Expired, 'invalid_temporary_link_ttl');
        }

        if ($policy->audience() === LinkAudience::Public && !$policy->publicDeliveryAllowed()) {
            return $this->deny(LinkResolutionStatus::Denied, 'public_delivery_not_allowed');
        }

        if ($request->requiresConfirmation() && !$request->confirmationProvided()) {
            return $this->deny(LinkResolutionStatus::Denied, 'missing_confirmation');
        }

        return new ArrayLinkPlan(
            LinkResolutionStatus::Allowed,
            'planned',
            false,
            false,
            [
                'request_id' => $request->requestId(),
                'target_type' => $target->type(),
                'owner_package' => $target->ownerPackage(),
                'audience' => $policy->audience()->value,
                'temporary' => $policy->temporary(),
            ],
        );
    }

    public function planRevocation(ArrayLinkRevocationPlan $plan): ArrayLinkPlan
    {
        if (trim($plan->linkIdentityRef()) === '') {
            return $this->deny(LinkResolutionStatus::UnknownTarget, 'missing_link_identity');
        }

        if (trim($plan->requestedByRef()) === '') {
            return $this->deny(LinkResolutionStatus::AccessDenied, 'missing_revocation_actor');
        }

        if (trim($plan->reasonRef()) === '') {
            return $this->deny(LinkResolutionStatus::Denied, 'missing_revocation_reason');
        }

        if (!$plan->confirmed()) {
            return $this->deny(LinkResolutionStatus::Denied, 'missing_revocation_confirmation');
        }

        return new ArrayLinkPlan(
            LinkResolutionStatus::Allowed,
            'revocation_planned',
            false,
            false,
            [
                'link_identity_ref_present' => true,
                'requested_by_ref_present' => true,
                'reason_ref_present' => true,
            ],
        );
    }

    public function diagnosticsReport(): ArrayLinkDiagnosticsReport
    {
        return new ArrayLinkDiagnosticsReport(
            'developer_testable_foundation',
            [
                'no_public_routes',
                'no_token_storage_runtime',
                'no_one_time_consumption_runtime',
                'no_file_manager_integration',
            ],
            false,
            false,
            [
                'planning_runtime' => true,
                'runtime_kind' => 'in_memory_link_planning',
            ],
        );
    }

    /**
     * @param array<string, scalar|null> $diagnostics
     */
    private function deny(LinkResolutionStatus $status, string $reason, array $diagnostics = []): ArrayLinkPlan
    {
        if ($status->permitsResolution()) {
            $status = LinkResolutionStatus::Denied;
        }

        return new ArrayLinkPlan($status, $reason, false, false, $diagnostics);
    }
}
