<?php

declare(strict_types=1);

namespace Larena\Link\Support;

use Larena\Core\Starter\CoreAssetActivationContract;

final class PreviewResponder
{
    /**
     * @return list<string>
     */
    public static function publicLinkPreviewAssetTags(): array
    {
        return CoreAssetActivationContract::renderTags([
            [
                'asset_key' => 'link.internal.public_link_review.css',
                'kind' => 'css',
                'critical' => true,
                'activation_owner' => CoreAssetActivationContract::OWNER,
                'physical_publication_ready' => true,
                'final_path' => '/larena/internal/public-link/assets/link.internal.public_link_review.css',
            ],
        ]);
    }
}
