<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * NOTE: property shapes ($aliases/$globals/$methods/$filters) track CodeIgniter 4's
 * Filters config across recent 4.x releases -- verify against your installed version.
 */
class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'jwtauth'  => \App\Filters\JwtAuthFilter::class,
        'role'     => \App\Filters\RoleFilter::class,
        'throttle' => \App\Filters\ThrottleFilter::class,
    ];

    public array $globals = [
        'before' => [
            // CSRF is intentionally NOT global -- it only applies to any server-rendered
            // Bootstrap form routes, never to this JSON API (see architecture doc Section 5)
        ],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [
        'throttle' => ['before' => ['api/v1/auth/login']],
        // jwtauth and role are applied per route-group directly in Routes.php instead of
        // here, since only the authenticated portion of the API needs them
    ];
}
