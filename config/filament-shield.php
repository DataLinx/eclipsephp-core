<?php

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

return [
    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => false,
        ],
    ],

    'tenant_model' => \Eclipse\Core\Models\Site::class,

    'auth_provider_model' => \Eclipse\Core\Models\User::class,

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    'permissions' => [
        'separator' => '_',
        'case' => 'lower_snake',
        'generate' => true,
    ],

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny', 'view', 'create', 'update', 'restore', 'restoreAny',
            'replicate', 'reorder', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny',
        ],
        'single_parameter_methods' => [
            'viewAny', 'create', 'deleteAny', 'forceDeleteAny', 'restoreAny', 'reorder',
        ],
    ],

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield',
    ],

    'resources' => [
        'subject' => 'model',
        'manage' => [
            RoleResource::class => [
                'viewAny', 'view', 'create', 'update', 'delete',
            ],
        ],
        'exclude' => [],
    ],

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            Dashboard::class,
        ],
    ],

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ],
    ],

    'custom_permissions' => [],

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    'register_role_policy' => true,
];
