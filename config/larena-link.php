<?php

declare(strict_types=1);

return [
    // The public link routes are the package's own (developer remark 5, 2026-10-26: Root keeps no copy). The
    // provider mounts them where APP_ENV is local or testing only; they are on there by default and off with
    // LARENA_LINK_PUBLIC_ROUTES=false.
    'public_routes' => [
        'enabled' => filter_var(
            getenv('LARENA_LINK_PUBLIC_ROUTES') === false ? true : getenv('LARENA_LINK_PUBLIC_ROUTES'),
            FILTER_VALIDATE_BOOL,
        ),
        'local_testing_only' => true,
        'content_link_preview_route' => '/larena/cms/content-links/{id}/preview',
        'content_link_preview_name' => 'larena.cms-public-content-link.preview',
        'middleware' => ['web'],
    ],
];
