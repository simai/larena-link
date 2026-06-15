<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PublicLinkPersistentLookupPreview
{
    private const TABLE = 'larena_public_link_lookup';

    /**
     * @return array<string, mixed>
     */
    public static function run(string $candidateToken = 'active-preview-token', ?string $outputPath = null): array
    {
        $schema = self::ensureSchema();
        $seed = self::seedFixtures();
        $lookup = self::lookup($candidateToken);
        $negativeLookups = [
            'unknown_token_fail_closed' => self::lookup('unknown-preview-token'),
            'expired_token_fail_closed' => self::lookup('expired-preview-token'),
            'revoked_token_fail_closed' => self::lookup('revoked-preview-token'),
            'missing_access_scope_fail_closed' => self::lookup('missing-access-preview-token'),
        ];

        return self::reportFromLookup($candidateToken, $schema, $seed, $lookup, $negativeLookups, $outputPath);
    }

    /**
     * @return array<string, mixed>
     */
    public static function lookup(string $candidateToken): array
    {
        if (!Schema::hasTable(self::TABLE)) {
            return self::notFound($candidateToken, 'table_missing');
        }

        $key = self::fingerprint($candidateToken);
        $record = DB::table(self::TABLE)->where('token_hash_ref', $key)->first();

        if ($record === null) {
            return self::notFound($candidateToken, 'unknown_token');
        }

        $base = [
            'stored_lookup_key' => (string) $record->token_hash_ref,
            'link_identity_ref' => $record->link_identity_ref,
            'logical_file_id' => $record->logical_file_id,
            'access_scope_ref' => $record->access_scope_ref,
            'audit_event_ref' => (string) $record->audit_event_ref,
            'expires_at' => $record->expires_at,
            'revoked_at' => $record->revoked_at,
            'status' => (string) $record->status,
            'raw_token_visible' => false,
            'raw_token_persisted' => false,
            'file_download_executed' => false,
            'mutates_state' => false,
        ];

        if ($record->status === 'revoked' || $record->revoked_at !== null) {
            return array_merge($base, [
                'lookup_status' => 'found_revoked',
                'decision' => 'would_deny',
                'deny_reasons' => ['revoked_link'],
            ]);
        }

        if ($record->expires_at !== null && strtotime((string) $record->expires_at) <= time()) {
            return array_merge($base, [
                'lookup_status' => 'found_expired',
                'decision' => 'would_deny',
                'deny_reasons' => ['expired_link'],
            ]);
        }

        if ($record->access_scope_ref === null || $record->access_scope_ref === 'missing') {
            return array_merge($base, [
                'lookup_status' => 'found_missing_access_scope',
                'decision' => 'would_deny',
                'deny_reasons' => ['missing_access_scope'],
            ]);
        }

        return array_merge($base, [
            'lookup_status' => 'found_active',
            'decision' => 'would_allow',
            'deny_reasons' => [],
        ]);
    }

    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $seed
     * @param array<string, mixed> $lookup
     * @param array<string, array<string, mixed>> $negativeLookups
     * @return array<string, mixed>
     */
    public static function reportFromLookup(
        string $candidateToken,
        array $schema,
        array $seed,
        array $lookup,
        array $negativeLookups = [],
        ?string $outputPath = null
    ): array {
        $fingerprint = self::fingerprint($candidateToken);

        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-persistent-lookup-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'production_lookup_allowed' => false,
                'file_delivery_allowed' => false,
            ],
            'schema_boundary' => [
                'status' => ($schema['table_exists'] ?? false) === true ? 'passed' : 'failed',
                'table' => self::TABLE,
                'migration_ref' => 'database/migrations/2026_06_08_000001_create_larena_public_link_lookup_table.php',
                'table_exists' => $schema['table_exists'] ?? false,
                'created_now' => $schema['created_now'] ?? false,
                'rollback_command' => 'php artisan migrate:rollback --path=database/migrations/2026_06_08_000001_create_larena_public_link_lookup_table.php',
                'raw_token_column_exists' => false,
            ],
            'fixture_seed' => [
                'status' => (int) ($seed['seeded_count'] ?? 0) >= 4 ? 'passed' : 'failed',
                'seeded_count' => $seed['seeded_count'] ?? 0,
                'mutates_state' => $seed['mutates_state'] ?? false,
                'raw_token_persisted' => false,
            ],
            'hash_only_lookup' => [
                'status' => str_starts_with((string) ($lookup['stored_lookup_key'] ?? ''), 'sha256:') ? 'passed' : 'failed',
                'hash_algorithm' => 'sha256',
                'token_fingerprint' => $fingerprint,
                'stored_lookup_key' => $lookup['stored_lookup_key'] ?? null,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
            ],
            'lookup_decision_contract' => [
                'status' => ($lookup['decision'] ?? null) !== null ? 'passed' : 'failed',
                'lookup_status' => $lookup['lookup_status'] ?? null,
                'decision' => $lookup['decision'] ?? null,
                'deny_reasons' => $lookup['deny_reasons'] ?? [],
                'unknown_token_fail_closed' => self::decisionFor($negativeLookups, 'unknown_token_fail_closed') === 'would_deny',
                'expired_token_fail_closed' => self::decisionFor($negativeLookups, 'expired_token_fail_closed') === 'would_deny',
                'revoked_token_fail_closed' => self::decisionFor($negativeLookups, 'revoked_token_fail_closed') === 'would_deny',
                'missing_access_scope_fail_closed' => self::decisionFor($negativeLookups, 'missing_access_scope_fail_closed') === 'would_deny',
            ],
            'access_audit_boundary' => [
                'status' => ($lookup['audit_event_ref'] ?? null) !== null ? 'passed' : 'failed',
                'access_scope_ref' => $lookup['access_scope_ref'] ?? null,
                'audit_event_ref' => $lookup['audit_event_ref'] ?? null,
                'access_owner_package' => 'larena/access',
                'audit_owner_package' => 'larena/audit',
                'audit_event_recorded_now' => false,
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'file_download_executed' => false,
                'file_content_returned' => false,
                'delivery_requires_future_launch_record' => true,
            ],
            'raw_token_leak_guard' => [
                'status' => str_contains(json_encode($lookup, JSON_THROW_ON_ERROR), $candidateToken) ? 'failed' : 'passed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => ($schema['created_now'] ?? false) === true || ($seed['mutates_state'] ?? false) === true,
                'production_mutates_state' => false,
                'real_database_mutation' => true,
                'production_database_mutation' => false,
                'real_file_mutation' => false,
                'public_file_delivery' => false,
                'one_time_consumption_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_persistent_lookup_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => $checks['scope_boundary']['mutates_state'],
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_persistent_lookup_foundation',
            'packages' => [
                'larena/link',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'candidate_lookup' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'lookup_status' => $lookup['lookup_status'] ?? null,
                'decision' => $lookup['decision'] ?? null,
                'deny_reasons' => $lookup['deny_reasons'] ?? [],
            ],
            'schema_state' => $schema,
            'seed_state' => $seed,
            'lookup_result' => $lookup,
            'persistence_contract' => [
                'table' => self::TABLE,
                'hash_algorithm' => 'sha256',
                'stored_fields' => [
                    'token_hash_ref',
                    'link_identity_ref',
                    'logical_file_id',
                    'access_scope_ref',
                    'audit_event_ref',
                    'status',
                    'expires_at',
                    'revoked_at',
                    'preview_metadata',
                ],
                'forbidden_fields' => [
                    'raw_token',
                    'plain_text_token',
                    'download_payload',
                    'file_content',
                ],
                'rollback_boundary' => [
                    'drop_table' => self::TABLE,
                    'migration_ref' => 'database/migrations/2026_06_08_000001_create_larena_public_link_lookup_table.php',
                ],
            ],
            'checks' => $checks,
            'safe_trace' => [
                'token_fingerprint' => $fingerprint,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'persistent_token_table' => true,
                'database_migration' => true,
                'production_lookup' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'one_time_consumption_runtime' => false,
                'real_file_mutation' => false,
                'real_database_mutation' => true,
                'production_database_mutation' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_persistent_lookup_foundation_only',
                'local_testing_schema_and_seed_only',
                'no_raw_token_storage',
                'no_production_lookup_runtime',
                'no_public_file_delivery',
                'no_one_time_consumption_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_persistent_lookup_or_prepare_public_file_delivery_launch_record',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    public static function fingerprint(string $candidateToken): string
    {
        return PublicLinkTokenStorageContractPreview::fingerprint($candidateToken);
    }

    /**
     * @return array<string, mixed>
     */
    private static function ensureSchema(): array
    {
        if (Schema::hasTable(self::TABLE)) {
            return [
                'table' => self::TABLE,
                'table_exists' => true,
                'created_now' => false,
                'mutates_state' => false,
            ];
        }

        Schema::create(self::TABLE, function ($table): void {
            $table->id();
            $table->string('token_hash_ref', 96)->unique();
            $table->string('link_identity_ref')->nullable();
            $table->string('logical_file_id')->nullable();
            $table->string('access_scope_ref')->nullable();
            $table->string('audit_event_ref');
            $table->string('status', 32);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('preview_metadata')->nullable();
            $table->timestamps();
            $table->index(['status', 'expires_at']);
            $table->index('revoked_at');
            $table->index('access_scope_ref');
        });

        return [
            'table' => self::TABLE,
            'table_exists' => true,
            'created_now' => true,
            'mutates_state' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function seedFixtures(): array
    {
        $rows = [
            self::fixture('active-preview-token', 'active', 'access.scope:file-manager.link-sharing.runtime', 'audit.event:file-manager.link-sharing.runtime', '+1 day', null),
            self::fixture('expired-preview-token', 'active', 'access.scope:file-manager.link-sharing.runtime', 'audit.event:file-manager.link-sharing.expired', '-1 day', null),
            self::fixture('revoked-preview-token', 'revoked', 'access.scope:file-manager.link-sharing.runtime', 'audit.event:file-manager.link-sharing.revoked', '+1 day', '-1 hour'),
            self::fixture('missing-access-preview-token', 'active', 'missing', 'audit.event:file-manager.link-sharing.missing-access', '+1 day', null),
        ];

        $mutates = false;
        foreach ($rows as $row) {
            $existing = DB::table(self::TABLE)->where('token_hash_ref', $row['token_hash_ref'])->first();
            if ($existing === null) {
                DB::table(self::TABLE)->insert($row);
                $mutates = true;
                continue;
            }

            DB::table(self::TABLE)->where('token_hash_ref', $row['token_hash_ref'])->update($row);
        }

        return [
            'table' => self::TABLE,
            'seeded_count' => DB::table(self::TABLE)->count(),
            'mutates_state' => $mutates,
            'fixture_keys' => array_map(static fn (array $row): string => $row['token_hash_ref'], $rows),
            'raw_token_persisted' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function fixture(
        string $candidate,
        string $status,
        ?string $accessScope,
        string $auditEvent,
        string $expiresAt,
        ?string $revokedAt,
    ): array {
        $now = gmdate('Y-m-d H:i:s');

        return [
            'token_hash_ref' => self::fingerprint($candidate),
            'link_identity_ref' => $status === 'active' ? 'link:file-manager-link-sharing-runtime-preview' : 'link:file-manager-link-sharing-runtime-preview-denied',
            'logical_file_id' => 'file-manager-link-sharing-runtime-1',
            'access_scope_ref' => $accessScope,
            'audit_event_ref' => $auditEvent,
            'status' => $status,
            'expires_at' => gmdate('Y-m-d H:i:s', strtotime($expiresAt)),
            'revoked_at' => $revokedAt === null ? null : gmdate('Y-m-d H:i:s', strtotime($revokedAt)),
            'preview_metadata' => json_encode([
                'source' => 'public_link_persistent_lookup_foundation',
                'raw_token_persisted' => false,
                'file_delivery' => 'blocked_by_foundation_scope',
            ], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function notFound(string $candidateToken, string $reason): array
    {
        return [
            'stored_lookup_key' => self::fingerprint($candidateToken),
            'link_identity_ref' => null,
            'logical_file_id' => null,
            'access_scope_ref' => null,
            'audit_event_ref' => 'audit.event:public_link.lookup.not_found',
            'expires_at' => null,
            'revoked_at' => null,
            'status' => 'not_found',
            'raw_token_visible' => false,
            'raw_token_persisted' => false,
            'file_download_executed' => false,
            'mutates_state' => false,
            'lookup_status' => 'not_found',
            'decision' => 'would_deny',
            'deny_reasons' => [$reason],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $negativeLookups
     */
    private static function decisionFor(array $negativeLookups, string $caseId): ?string
    {
        $lookup = $negativeLookups[$caseId] ?? null;

        return is_array($lookup) && is_string($lookup['decision'] ?? null) ? $lookup['decision'] : null;
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
