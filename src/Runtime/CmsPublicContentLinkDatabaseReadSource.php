<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Illuminate\Database\ConnectionInterface;
use Throwable;

final class CmsPublicContentLinkDatabaseReadSource
{
    private const TABLE = 'larena_public_link_lookup';

    /**
     * @return array<string, mixed>
     */
    public static function forId(ConnectionInterface $connection, string $id): array
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            return self::failed('validation_error', 'cms_public_content_link_id_required', databaseReadAttempted: false);
        }

        try {
            $row = $connection->selectOne(
                'select id, link_identity_ref, logical_file_id, access_scope_ref, audit_event_ref, status, expires_at, revoked_at, preview_metadata from ' . self::TABLE . ' where link_identity_ref = ? limit 1',
                [$normalizedId],
                true,
            );
        } catch (Throwable) {
            return self::failed('failed_closed', 'cms_public_content_link_table_unavailable', databaseReadAttempted: true);
        }

        if ($row === null) {
            return self::failed('not_found', 'cms_public_content_link_not_found', databaseReadAttempted: true);
        }

        $payload = self::rowToArray($row);
        $metadata = self::decodeMetadata($payload['preview_metadata'] ?? null);
        $revokedAt = self::nullableString($payload['revoked_at'] ?? null);
        $status = self::stringValue($payload['status'] ?? 'unknown');

        return [
            'schema' => 'larena.cms_public_content_link_read_model.v1',
            'status' => 'passed',
            'generated_at' => gmdate('c'),
            'owner_package' => 'larena/link',
            'input_contract_owner' => 'larena/rest',
            'mutates_state' => false,
            'production_mutates_state' => false,
            'scenario' => 'cms_public_content_link_database_read_model',
            'read_model' => [
                'id' => $normalizedId,
                'title' => self::stringValue($metadata['title'] ?? 'CMS public content link'),
                'status' => $status,
                'target_type' => self::stringValue($metadata['target_type'] ?? 'public_link_lookup'),
                'target_ref' => self::nullableString($payload['logical_file_id'] ?? null),
                'visibility' => self::stringValue($metadata['visibility'] ?? 'authenticated_preview_only'),
                'public_url_preview' => self::nullableString($metadata['public_url_preview'] ?? null),
                'expires_at' => self::nullableString($payload['expires_at'] ?? null),
                'revoked_at' => $revokedAt,
                'access_scope_ref' => self::nullableString($payload['access_scope_ref'] ?? null),
                'audit_event_ref' => self::stringValue($payload['audit_event_ref'] ?? ''),
                'database_ref' => [
                    'table' => self::TABLE,
                    'row_id' => self::nullableString($payload['id'] ?? null),
                    'link_identity_ref' => self::nullableString($payload['link_identity_ref'] ?? null),
                ],
            ],
            'checks' => [
                'database_read_boundary' => [
                    'status' => 'passed',
                    'source' => 'database_select_only',
                    'table' => self::TABLE,
                    'database_read_executed' => true,
                    'database_write_executed' => false,
                ],
                'runtime_guards' => [
                    'status' => 'passed',
                    'database_write' => false,
                    'file_streaming' => false,
                    'token_material_generation' => false,
                    'public_delivery_adapter' => false,
                    'production_runtime' => false,
                ],
            ],
            'safe_trace' => self::safeTrace(databaseRead: true),
            'known_limitations' => [
                'database_read_only',
                'no_file_streaming',
                'no_token_material_generation',
                'no_public_delivery_adapter',
                'no_admin_crud',
                'not_release_ready',
            ],
            'next_recommended_step' => 'wire_registered_get_handler_after_rest_gate',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function failed(string $status, string $code, bool $databaseReadAttempted): array
    {
        return [
            'schema' => 'larena.cms_public_content_link_read_model.v1',
            'status' => $status,
            'generated_at' => gmdate('c'),
            'owner_package' => 'larena/link',
            'mutates_state' => false,
            'production_mutates_state' => false,
            'scenario' => 'cms_public_content_link_database_read_model',
            'errors' => [
                [
                    'code' => $code,
                    'message' => 'CMS public content link read model is unavailable.',
                ],
            ],
            'checks' => [
                'fails_closed' => [
                    'status' => 'passed',
                    'handler_may_run' => false,
                    'database_read_attempted' => $databaseReadAttempted,
                    'database_write_executed' => false,
                ],
            ],
            'safe_trace' => self::safeTrace(databaseRead: $databaseReadAttempted),
            'known_limitations' => [
                'database_read_only',
                'not_release_ready',
            ],
        ];
    }

    /**
     * @return array<string, bool>
     */
    private static function safeTrace(bool $databaseRead): array
    {
        return [
            'database_read' => $databaseRead,
            'database_write' => false,
            'file_streaming' => false,
            'token_material_generation' => false,
            'public_delivery_adapter' => false,
            'admin_crud' => false,
            'production_runtime' => false,
            'release_ready' => false,
            'graph_sync_canonical_update' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function rowToArray(mixed $row): array
    {
        if (is_array($row)) {
            return $row;
        }

        if (is_object($row)) {
            return get_object_vars($row);
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeMetadata(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (!is_string($metadata) || trim($metadata) === '') {
            return [];
        }

        $decoded = json_decode($metadata, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
    }
}
