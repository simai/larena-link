<?php

declare(strict_types=1);

namespace Larena\Link\Http\Controllers\Public;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Larena\Link\Runtime\CmsPublicContentLinkReadModel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The read-only JSON preview of a public content link, from the link lookup table (local and testing only).
 * It moved here from Root with its route (developer remark 5, 2026-10-26).
 */
final class CmsPublicContentLinkPreviewController
{
    public function __invoke(string $id): JsonResponse
    {
        if (!App::environment(['local', 'testing'])) {
            throw new NotFoundHttpException();
        }

        /** @var ConnectionResolverInterface $database */
        $database = App::make('db');
        $readModel = CmsPublicContentLinkReadModel::fromDatabase($database->connection(), $id);
        $status = (string) ($readModel['status'] ?? 'failed');

        return new JsonResponse([
            'schema' => 'larena.cms_public_content_link_public_read_only_preview',
            'status' => $status === 'passed' ? 'passed' : 'failed',
            'surface' => 'public_read_only_preview',
            'read_model' => $readModel,
            'route_provider_topology' => [
                'current_owner' => 'larena/link',
                'route' => '/larena/cms/content-links/{id}/preview',
                'route_name' => 'larena.cms-public-content-link.preview',
                'mounted_by' => 'Larena\\Link\\Providers\\LinkServiceProvider',
                'package_provider_promotion' => 'promoted_to_package_provider',
            ],
            'safe_trace' => [
                'local_testing_only' => true,
                'mutates_state' => false,
                'database_lookup' => true,
                'database_read' => ($readModel['safe_trace']['database_read'] ?? false) === true,
                'database_write' => false,
                'file_streaming' => false,
                'token_material_generation' => false,
                'public_delivery_adapter' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
            'known_limitations' => [
                'local_testing_read_only_preview_only',
                'database_select_only',
                'no_file_streaming',
                'no_token_material_generation',
                'no_public_delivery_adapter',
                'not_release_ready',
            ],
        ], self::httpStatus($status));
    }

    private static function httpStatus(string $status): int
    {
        return match ($status) {
            'passed' => 200,
            'validation_error' => 422,
            'not_found' => 404,
            'failed_closed' => 503,
            default => 500,
        };
    }
}
