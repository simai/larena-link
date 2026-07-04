<?php

declare(strict_types=1);

namespace Larena\Link\Admin;

use InvalidArgumentException;

final class PublicLinkAdminSurfaceContributor
{
    public const OWNER_PACKAGE = 'larena/link';

    public const CONTRIBUTION_COUNT = 17;

    /**
     * @return list<array<string, mixed>>
     */
    public static function contributions(): array
    {
        return self::validate(self::descriptors());
    }

    /**
     * @param list<array<string, mixed>> $descriptors
     * @return list<array<string, mixed>>
     */
    public static function validate(array $descriptors): array
    {
        $seen = [];

        foreach ($descriptors as $descriptor) {
            $id = (string) ($descriptor['id'] ?? '');
            if ($id === '') {
                throw new InvalidArgumentException('Public link admin contribution is missing id.');
            }

            if (isset($seen[$id])) {
                throw new InvalidArgumentException("Duplicate public link admin contribution id [{$id}].");
            }
            $seen[$id] = true;

            if (($descriptor['owner_package'] ?? null) !== self::OWNER_PACKAGE) {
                throw new InvalidArgumentException("Public link admin contribution [{$id}] must be owned by " . self::OWNER_PACKAGE . '.');
            }

            if ((string) ($descriptor['contribution_type'] ?? '') === '') {
                throw new InvalidArgumentException("Public link admin contribution [{$id}] is missing contribution_type.");
            }

            if ((string) ($descriptor['href'] ?? '') === '' || (string) ($descriptor['machine_href'] ?? '') === '') {
                throw new InvalidArgumentException("Public link admin contribution [{$id}] is missing href or machine_href.");
            }

            $method = strtoupper((string) ($descriptor['method'] ?? 'GET'));
            if ($method !== 'GET') {
                throw new InvalidArgumentException("Public link admin contribution [{$id}] exposes unsafe method [{$method}].");
            }

            if (($descriptor['write_capable'] ?? false) === true || ($descriptor['mutates_state'] ?? false) === true) {
                throw new InvalidArgumentException("Public link admin contribution [{$id}] must stay read-only.");
            }
        }

        if (count($descriptors) !== self::CONTRIBUTION_COUNT) {
            throw new InvalidArgumentException('Public link admin contribution count drifted from ' . self::CONTRIBUTION_COUNT . '.');
        }

        return $descriptors;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function descriptors(): array
    {
        return [
            [
                'id' => 'public_content_link_flow',
                'label' => 'Public Content Link Flow',
                'href' => '/larena/internal/public-content-link-flow',
                'machine_href' => '/larena/internal/public-content-link-flow?format=json',
                'purpose' => 'developer-testable content/file/link flow with share planning, temporary link contract, expiry, access/audit, revocation and public runtime guards',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_runtime_planning',
                'label' => 'Public Link Runtime Planning',
                'href' => '/larena/internal/public-link-runtime-planning',
                'machine_href' => '/larena/internal/public-link-runtime-planning?format=json',
                'purpose' => 'developer-testable planning surface for future public link runtime, token policy, route gate, replay/nonce/rate-limit guards and promotion boundary',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_dry_run_runtime',
                'label' => 'Public Link Dry-Run Runtime',
                'href' => '/larena/internal/public-link-dry-run-runtime',
                'machine_href' => '/larena/internal/public-link-dry-run-runtime?format=json',
                'purpose' => 'developer-testable dry-run runtime for future public link resolution decisions, negative guards, access recheck and audit trace without enabling public routes or token storage',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_runtime_hardening',
                'label' => 'Public Link Runtime Hardening',
                'href' => '/larena/internal/public-link-runtime-hardening',
                'machine_href' => '/larena/internal/public-link-runtime-hardening?format=json',
                'purpose' => 'developer-testable route hardening foundation for future public link resolution with token redaction, access/audit checks and fail-closed guards without file delivery',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_token_storage_contract',
                'label' => 'Public Link Token Storage Contract',
                'href' => '/larena/internal/public-link-token-storage-contract',
                'machine_href' => '/larena/internal/public-link-token-storage-contract?format=json',
                'purpose' => 'developer-testable hash-only token lookup contract for future public link persistence without raw token storage, database migration or file delivery',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_persistent_lookup',
                'label' => 'Public Link Persistent Lookup',
                'href' => '/larena/internal/public-link-persistent-lookup',
                'machine_href' => '/larena/internal/public-link-persistent-lookup?format=json',
                'purpose' => 'developer-testable local/testing persistent hashed public link lookup with reversible schema, seed, access/audit metadata and no public file delivery',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_guarded_delivery_readiness',
                'label' => 'Public Link Guarded Delivery Readiness',
                'href' => '/larena/internal/public-link-guarded-delivery-readiness',
                'machine_href' => '/larena/internal/public-link-guarded-delivery-readiness?format=json',
                'purpose' => 'developer-testable public link delivery-readiness foundation with sandbox target proof and no public file delivery',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'diagnostic',
            ],
            [
                'id' => 'public_link_controlled_delivery_simulation',
                'label' => 'Public Link Controlled Delivery Simulation',
                'href' => '/larena/internal/public-link-controlled-delivery-simulation',
                'machine_href' => '/larena/internal/public-link-controlled-delivery-simulation?format=json',
                'purpose' => 'developer-testable public link delivery response simulation with safe headers, sandbox target metadata and no file body',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_one_time_consumption_lifecycle',
                'label' => 'Public Link One-Time Consumption Lifecycle',
                'href' => '/larena/internal/public-link-one-time-consumption-lifecycle',
                'machine_href' => '/larena/internal/public-link-one-time-consumption-lifecycle?format=json',
                'purpose' => 'developer-testable one-time public link lifecycle with simulated consumption and fail-closed consumed/expired/revoked states',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_guarded_real_delivery_adapter',
                'label' => 'Public Link Guarded Real Delivery Adapter',
                'href' => '/larena/internal/public-link-guarded-real-delivery-adapter',
                'machine_href' => '/larena/internal/public-link-guarded-real-delivery-adapter?format=json',
                'purpose' => 'developer-testable public link real-delivery adapter contract with adapter metadata only and no file stream',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_operator_lifecycle_management',
                'label' => 'Public Link Operator Lifecycle Management',
                'href' => '/larena/internal/public-link-operator-lifecycle-management',
                'machine_href' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
                'purpose' => 'developer-testable read-only operator registry for public link lifecycle states, blocked delivery decisions and future action gates',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
            [
                'id' => 'public_link_guarded_admin_mutation_planning',
                'label' => 'Public Link Guarded Admin Mutation Planning',
                'href' => '/larena/internal/public-link-guarded-admin-mutation-planning',
                'machine_href' => '/larena/internal/public-link-guarded-admin-mutation-planning?format=json',
                'purpose' => 'developer-testable read-only plan for future public link revoke, regenerate and cleanup launch records, rollback proof and negative tests',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'action',
            ],
            [
                'id' => 'public_link_revoke_action',
                'label' => 'Public Link Revoke Action',
                'href' => '/larena/internal/public-link-revoke-action',
                'machine_href' => '/larena/internal/public-link-revoke-action?format=json',
                'purpose' => 'developer-testable guarded public link revoke action with before/after snapshots, rollback proof, access/audit refs and negative guards',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'action',
            ],
            [
                'id' => 'public_link_regenerate_action',
                'label' => 'Public Link Regenerate Action',
                'href' => '/larena/internal/public-link-regenerate-action',
                'machine_href' => '/larena/internal/public-link-regenerate-action?format=json',
                'purpose' => 'developer-testable guarded public link regenerate action with old/new fingerprints, rollback proof, access/audit refs and negative guards',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'action',
            ],
            [
                'id' => 'public_link_cleanup_action',
                'label' => 'Public Link Cleanup Action',
                'href' => '/larena/internal/public-link-cleanup-action',
                'machine_href' => '/larena/internal/public-link-cleanup-action?format=json',
                'purpose' => 'developer-testable guarded public link cleanup action with candidate set, retention policy, rollback/replay proof, access/audit refs and negative guards',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'action',
            ],
            [
                'id' => 'public_link_mutation_ladder_review',
                'label' => 'Public Link Mutation Ladder Review',
                'href' => '/larena/internal/public-link-mutation-ladder-review',
                'machine_href' => '/larena/internal/public-link-mutation-ladder-review?format=json',
                'purpose' => 'developer-testable consolidated operator action matrix for public link planning, revoke, regenerate and cleanup status semantics',
                'category' => 'guarded_actions',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'action',
            ],
            [
                'id' => 'public_link_delivery_contract_hardening',
                'label' => 'Public Link Delivery Contract Hardening',
                'href' => '/larena/internal/public-link-delivery-contract-hardening',
                'machine_href' => '/larena/internal/public-link-delivery-contract-hardening?format=json',
                'purpose' => 'developer-testable public link delivery state-to-response contract with HTTP status policy, safe headers, body policy, access/audit recheck points and negative guards',
                'category' => 'data_content',
                'state' => 'review_surface_ready',
                'owner_package' => self::OWNER_PACKAGE,
                'contribution_type' => 'screen',
            ],
        ];
    }
}
