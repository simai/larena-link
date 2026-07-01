<?php

declare(strict_types=1);

return [
    'public_routes' => [
        'enabled' => filter_var(getenv('LARENA_LINK_PUBLIC_ROUTES') ?: false, FILTER_VALIDATE_BOOL),
        'local_testing_only' => true,
        'route' => '/larena/link/{token}',
        'name' => 'larena.public-link-runtime-hardening.resolve',
        'middleware' => ['web'],
        'entry_app_compatibility_route' => 'larena.public-link-runtime-hardening.resolve',
    ],
];
