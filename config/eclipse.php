<?php

return [

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
                        'email' => 'another@example.com',
                    ],
                ],
            ]
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
        'Editor' => 'another@example.com',
    ],

];
