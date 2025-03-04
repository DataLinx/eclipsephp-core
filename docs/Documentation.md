Welcome to the Eclipse Core documentation!

🛠️️️ **This is obviously a work in progress!** 🛠️

## Users

### Authorization
For user authorization purposes, we use the [spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v6) package and the [Filament Shield plugin](https://filamentphp.com/plugins/bezhansalleh-shield).

This setup does not change any core [Laravel authorization](https://laravel.com/docs/11.x/authorization) principles, but it expands and makes it work in Filament.

There are two important points of integration:

1. **Defining what permissions make sense for a Filament resource.** This is done by setting custom permissions prefixes in the resource. Read more [here](https://filamentphp.com/plugins/bezhansalleh-shield#custom-permissions), but this is the gist of it:

    ```php
    <?php

    namespace Eclipse\Core\Filament\Resources;
     
    use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
    ...
     
    class LocaleResource extends Resource implements HasShieldPermissions
    {
        ...
     
        public static function getPermissionPrefixes(): array
        {
            return [
                'view_any',
                'create',
                'update',
                'delete',
                'delete_any',
            ];
        }
     
        ...
    }
    ```
2. **Generating model policies and permission database records.** You can do this for a single resource by running Shield's command:
    
    ```shell
   php artisan shield:generate --panel=admin --resource=LocaleResource
   ```
   This creates a policy class in your app policies directory, so if developing a plugin, you must move it to your plugin's policies directory.

With this done, Filament will automatically use the model policies for access authorization, as described in the [Filament docs](https://filamentphp.com/docs/3.x/panels/resources/getting-started#authorization), while you can also use all the standard [Laravel authorization methods](https://laravel.com/docs/11.x/authorization#authorizing-actions-using-policies), such as `$user->can('update', $locale)` etc. 
