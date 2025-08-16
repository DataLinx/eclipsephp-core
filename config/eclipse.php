<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable/disable multi-site feature
    |--------------------------------------------------------------------------
    */
    'multi_site' => (bool) env('ECLIPSE_MULTI_SITE', false),

    /*
    |--------------------------------------------------------------------------
    | Enable/disable user email verification
    |--------------------------------------------------------------------------
    | Set this boolean to true if you want to enable parts of the application
    | related to user email address verification
    */
    'email_verification' => (bool) env('ECLIPSE_EMAIL_VERIFICATION', false),

    /*
    |--------------------------------------------------------------------------
    | Seeder setup
    |--------------------------------------------------------------------------
    | Here you can specify any data you want seeded by default.
    | All settings are optional.
    */
    'seed' => [
        'roles' => [
            // Number of randomly generated roles
            'count' => 2,
            // Roles with preset data
            // Required attributes: name, guard_name
            'presets' => [
                [
                    'data' => [
                        'name' => 'admin',
                        'guard_name' => 'web',
                    ],
                ],
            ],
        ],
        'users' => [
            // Number of randomly generated users
            'count' => 5,
            // Users with preset data
            'presets' => [
                [
                    'data' => [
                        // Email is required
                        'email' => 'test@example.com',
                        // Additional attributes — if any is omitted, faker will be used
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'password' => 'test123',
                    ],
                    // Optional role(s) to set (for multiple, use an array)
                    'role' => 'super_admin',
                ],
                [
                    'data' => [
                        'email' => 'admin@example.com',
                    ],
                    'role' => 'admin',
                ],
            ],
        ],
        // Sites — only used if the multi-site feature is enabled above
        'sites' => [
            // Number of randomly generated sites
            'count' => 0,
            // Sites with preset data
            'presets' => [
                [
                    'data' => [
                        'domain' => basename(config('app.url')),
                        'name' => config('app.name'),
                    ],
                ],
                [
                    'data' => [
                        'domain' => 'another.lndo.site',
                        'name' => 'Another site',
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Developer logins
    |--------------------------------------------------------------------------
    | Provide a list of users to use as config for the "Developer logins"
    | Filament plugin
    */
    'developer_logins' => [
        'Super admin' => 'test@example.com',
        'Admin' => 'admin@example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope package additional configuration
    |--------------------------------------------------------------------------
    */
    'telescope' => [
        /*
         * Enable dark theme?
         */
        'dark_theme' => (bool) env('TELESCOPE_DARK_THEME', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Horizon package additional configuration
    |--------------------------------------------------------------------------
    */
    'horizon' => [
        /*
         * List of email addresses of users that are allowed to view the Horizon
         * panel in non-local environments
         */
        'emails' => [
            //
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional tools to be displayed in the "Tools" menu of the admin panel
    |--------------------------------------------------------------------------
    */
    'tools' => [
        'phpmyadmin' => env('PHPMYADMIN_URL'),
        'typesense_dashboard' => env('TYPESENSE_DASHBOARD_URL'),
    ],

];
