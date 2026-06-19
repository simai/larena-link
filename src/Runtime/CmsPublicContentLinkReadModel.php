<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Illuminate\Database\ConnectionInterface;

final class CmsPublicContentLinkReadModel
{
    public const DEMO_ID = 'cms-link:demo';

    /**
     * @return array<string, mixed>
     */
    public static function forId(string $id = self::DEMO_ID, ?string $outputPath = null): array
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            return self::finish(self::failed('validation_error', 'cms_public_content_link_id_required'), $outputPath);
        }

        if ($normalizedId !== self::DEMO_ID) {
            return self::finish(self::failed('not_found', 'cms_public_content_link_not_found'), $outputPath);
        }

        $report = [
            'schema' => 'larena.cms_public_content_link_read_model.v1',
            'status' => 'passed',
            'generated_at' => gmdate('c'),
            'owner_package' => 'larena/link',
            'input_contract_owner' => 'larena/rest',
            'mutates_state' => false,
            'production_mutates_state' => false,
            'scenario' => 'cms_public_content_link_read_model_fixture',
            'read_model' => [
                'id' => self::DEMO_ID,
                'title' => 'Demo public content link',
                'status' => 'draft_preview',
                'target_type' => 'content_page',
                'target_ref' => 'content.page:demo',
                'visibility' => 'authenticated_preview_only',
                'public_url_preview' => '/preview/cms/content-links/demo',
                'expires_at' => null,
                'access_scope_ref' => 'access.scope:cms-public-content-link-demo',
                'audit_event_ref' => 'audit.event:cms-public-content-link-demo',
            ],
            'checks' => [
                'operation_contract' => [
                    'status' => 'passed',
                    'operation_key' => 'cms.public_content_link.show',
                    'read_only' => true,
                ],
                'fixture_boundary' => [
                    'status' => 'passed',
                    'source' => 'package_local_fixture',
                    'database_query_executed' => false,
                    'external_io_executed' => false,
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
            'safe_trace' => self::safeTrace(),
            'known_limitations' => [
                'package_local_fixture_only',
                'no_database_query',
                'no_file_streaming',
                'no_token_material_generation',
                'no_public_delivery_adapter',
                'no_admin_crud',
                'not_release_ready',
            ],
            'next_recommended_step' => 'wire_read_model_to_read_only_admin_detail_after_contract_review',
            'evidence_path' => $outputPath,
        ];

        return self::finish($report, $outputPath);
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromDatabase(ConnectionInterface $connection, string $id = self::DEMO_ID, ?string $outputPath = null): array
    {
        return self::finish(CmsPublicContentLinkDatabaseReadSource::forId($connection, $id), $outputPath);
    }

    /**
     * @return array<string, mixed>
     */
    private static function failed(string $status, string $code): array
    {
        return [
            'schema' => 'larena.cms_public_content_link_read_model.v1',
            'status' => $status,
            'generated_at' => gmdate('c'),
            'owner_package' => 'larena/link',
            'mutates_state' => false,
            'production_mutates_state' => false,
            'scenario' => 'cms_public_content_link_read_model_fixture',
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
                ],
            ],
            'safe_trace' => self::safeTrace(),
            'known_limitations' => [
                'package_local_fixture_only',
                'not_release_ready',
            ],
        ];
    }

    /**
     * @return array<string, bool>
     */
    private static function safeTrace(): array
    {
        return [
            'database_read' => false,
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
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private static function finish(array $report, ?string $outputPath): array
    {
        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function writeJson(string $outputPath, array $payload): void
    {
        $directory = dirname($outputPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(
            $outputPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        );
    }
}
