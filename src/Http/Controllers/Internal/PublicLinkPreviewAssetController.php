<?php

declare(strict_types=1);

namespace Larena\Link\Http\Controllers\Internal;

use Illuminate\Http\Response;
use Larena\Core\Starter\CoreAssetActivationContract;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicLinkPreviewAssetController
{
    private const ASSET_KEY = 'link.internal.public_link_review.css';
    private const ASSET_PATH = __DIR__ . '/../../../../resources/assets/internal/public-link-review.css';

    public function __invoke(string $assetKey): Response
    {
        if ($assetKey !== self::ASSET_KEY || !is_file(self::ASSET_PATH)) {
            throw new NotFoundHttpException();
        }

        return new Response((string) file_get_contents(self::ASSET_PATH), 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'X-Larena-Owner' => 'larena/link',
            'X-Larena-Asset-Activation-Owner' => CoreAssetActivationContract::OWNER,
            'X-Larena-Root-Copy' => 'false',
        ]);
    }
}
