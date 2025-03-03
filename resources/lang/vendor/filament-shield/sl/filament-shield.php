<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Naziv',
    'column.guard_name' => 'Zaščita (guard)',
    'column.team' => 'Stran',
    'column.roles' => 'Vloge',
    'column.permissions' => 'Pravice',
    'column.updated_at' => 'Posodobljeno',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Naziv',
    'field.guard_name' => 'Zaščita (guard)',
    'field.permissions' => 'Pravice',
    'field.team' => 'Stran',
    'field.team.placeholder' => 'Izberi stran ...',
    'field.select_all.name' => 'Izberi vse',
    'field.select_all.message' => 'Omogoči/onemogoči vse pravice za to vlogo',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Uporabniki',
    'nav.role.label' => 'Vloge',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Vloga',
    'resource.label.roles' => 'Vloge',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Entitete',
    'resources' => 'Podatki',
    'widgets' => 'Pripomočki',
    'pages' => 'Strani',
    'custom' => 'Pravice po meri',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Nimate dovoljenja za dostop',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Ogled',
        'view_any' => 'Pregled seznama',
        'create' => 'Ustvari',
        'update' => 'Posodobi',
        'delete' => 'Izbriši',
        'delete_any' => 'Izbriši vse',
        'force_delete' => 'Trajno izbriši',
        'force_delete_any' => 'Trajno izbriši vse',
        'restore' => 'Obnovi',
        'reorder' => 'Preuredi',
        'restore_any' => 'Obnovi vse',
        'replicate' => 'Podvoji',
    ],
];
