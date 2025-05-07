Welcome to the Eclipse Core documentation!

🛠️️️ **This is obviously a work in progress!** 🛠️  
Docs will be restructured when things get implemented.

<!-- TOC -->
* [Introduction](#introduction)
  * [💻 System requirements](#-system-requirements)
    * [Server](#server)
    * [Development](#development)
  * [🛟 Getting started](#-getting-started)
* [🔌 Plugin development](#-plugin-development)
  * [⚙️ Setting up](#-setting-up)
  * [▶️ Running tests](#-running-tests)
    * [Console](#console)
    * [PhpStorm](#phpstorm)
    * [Testing with multiple PHP versions](#testing-with-multiple-php-versions)
  * [🪲 Debugging](#-debugging)
    * [Xdebug in PhpStorm](#xdebug-in-phpstorm)
    * [Laravel Telescope](#laravel-telescope)
* [📑 Core concepts](#-core-concepts)
  * [🙋‍♂️ Users](#-users)
    * [Authorization](#authorization)
  * [🔍 Search](#-search)
    * [Set up Scout and Typesense](#set-up-scout-and-typesense)
    * [Preparing the model for search](#preparing-the-model-for-search)
    * [Enabling search in Filament controllers](#enabling-search-in-filament-controllers)
    * [Indexing](#indexing-)
    * [Debugging](#debugging)
<!-- TOC -->

# Introduction

## 💻 System requirements

### Server
* PHP >= 8.3
    * See the `require` section in [composer.json](../composer.json) for any required PHP extensions
* MariaDB >= 10.11 
* Composer
* Node.js and npm

### Development
Although not obligatory, [Lando](https://lando.dev/) is recommended for setting up the Docker containers. All Eclipse packages already ship with a Lando config file.

If you create the project via `git clone`, there are no additional requirements.

However, if you want to create a new project via composer (recommended), you need to have on your system:
* PHP >= 8.3
* Composer

The Lando instance automatically includes all other system requirements.
Node.js and npm are provided in the lando container.

## 🛟 Getting started
1. Create a new project with composer:
    ```shell
    composer create-project eclipsephp/app myprojectname -s dev
    ````
2. Set the app URL to your desired URL
    * `APP_URL` in `.env`
    * `name` property in `.lando.dist.yml` (just the subdomain part)
    * Also fix proxy URLs in `.lando.dist.yml`
3. Build and start the app instance
    ```shell
    lando start
    ```` 

That's it! Open the provided link and the public page should be shown. Add `/admin` to the URL to see the admin panel. The database is already migrated and seeded, and the test logins have already been set.

By doing these few steps you now have a functional Filament app with our Eclipse core package.

# 🔌 Plugin development
Unfortunately, plugin development is not yet fully self-contained, meaning you need the app skeleton, where you will add the plugin you want to develop as a dependency that is symlinked from a local folder.  
However, this is needed only if you want to manually test the plugin in the app (in your browser), since running the tests uses a default Testbench Laravel skeleton.

🔶 **Please note**: the core package is in fact not a Filament plugin, but nevertheless, everything for plugin development also applies for core development.  

## ⚙️ Setting up
1. Follow the above [Getting started](Documentation.md#-getting-started) section to set up an app skeleton.
2. Then, `git clone` the plugin you want to work on to a local folder inside the app, e.g. `packages/my-package`.
3. Add the local folder as a repository in the app's `composer.json`, e.g.:
    ```
    "repositories": [
        {
            "type": "path",
            "url": "./packages/my-package"
        }
    ]
    ```
4. Run `composer update` to create the symlink.

## ▶️ Running tests

### Console
You can run the tests inside the package with
```shell
  lando test
```
... or within the container:
```shell
  composer test
```
This is just a proxy to the testbench `package:test` command.

🔶 If you are developing the Core package, you must publish the Eclipse-provided config files before you can run tests.
```shell
  lando testbench vendor:publish --tag=eclipse-config
```
The reason for this is that these config files include other vendor configs that are already pre-configured the way we want them to be (i.e., multi-tenancy). All other plugins do not have this requirement — they must be built to fit any configuration.

💡 If you ever get an error stating your app encryption key is not set, it means the Testbench skeleton is not set up. Run `composer setup` and everything needed will be set up. 

### PhpStorm
See our [Testing with PhpStorm](https://github.com/DataLinx/php-package-template/blob/main/docs/Testing%20with%20PhpStorm.md) guide to set up testing in PhpStorm.

⚠️ Please note: if you run tests in PhpStorm, the Pest cache in the `vendor/pestphp/pest/.temp` dir is created with your root user for some reason. It's not a problem until you want to run tests in the console. If you want to switch to testing in the console, you have to delete the created directories inside the `.temp` dir.
If you know how to fix this, please open a discussion or better yet, submit a pull request.

### Testing with multiple PHP versions
See our [Running tests for a specific PHP version with Lando](https://github.com/DataLinx/php-package-template/blob/main/docs/Running%20tests%20for%20a%20specific%20PHP%20version.md#running-tests-for-a-specific-php-version-with-lando) guide.

Apart from that, make sure that your alternative `composer.json`: 
* has a `name` attribute
* includes the package service provider in `extra.laravel`
* includes the same `autoload` and `autoload-dev` lines  

... since these cannot be merged from the main `composer.json`.

Also, when testing inside the alternative Lando env, for some reason, you must use the long form `composer run-script test` instead of just `composer test`, like in the project root.

## 🪲 Debugging

### Xdebug in PhpStorm
If you follow the guide above on how to set up testing in PhpStorm, you are all set to debug while running tests. Just set breakpoints in code and run the test.

<!--
### Xdebug through the browser
TODO
-->

### Laravel Telescope
Telescope is already installed with the core package and ready to use in the development environment.
To enable it, just set the `TELESCOPE_ENABLED` variable in your `.env` file to `true` and visit the `/telescope` URL or click the _Tools > Telescope_ link in the panel navigation.

To use the dark theme also set the `TELESCOPE_DARK_THEME`.

# 📑 Core concepts

The following section explains the core concepts and applies to both app and plugin development.

## 🙋‍♂️ Users

### Authorization
For user authorization, we use the [spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v6) package and the [Filament Shield plugin](https://filamentphp.com/plugins/bezhansalleh-shield).

This setup does not change any core [Laravel authorization](https://laravel.com/docs/authorization) principles, but it expands and makes it work in Filament.

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

With this done, Filament will automatically use the model policies for access authorization, as described in the [Filament docs](https://filamentphp.com/docs/3.x/panels/resources/getting-started#authorization), while you can also use all the standard [Laravel authorization methods](https://laravel.com/docs/authorization#authorizing-actions-using-policies), such as `$user->can('update', $locale)` etc. 

## 🔍 Search
  
Eclipse plugins are already integrated with the global search and Typesense.

### Set up Scout and Typesense
In the default Eclipse app, Typesense is already configured as the Laravel Scout driver.

However, in case you need to add Typesense to an existing project, first add the service specification in your Lando file:
```yaml
services:
  typesense:
    type: typesense:28.0
    portforward: 8108
    apiKey: abc
```
Rebuild the container with `lando rebuild -y`.

Secondly, follow the Scout installation instructions [here](https://laravel.com/docs/scout#installation), but do not change your model — this is done later below.

Then, set the same API key in your `.env` file and use `typesense` as host.
```dotenv
SCOUT_DRIVER=typesense
SCOUT_QUEUE=true

TYPESENSE_API_KEY=abc
TYPESENSE_HOST=typesense
```
The Typesense service will now be running and ready to use by your app.

### Preparing the model for search
1. Add the `Eclipse\Common\Foundation\Models\IsSearchable` trait to your model class (e.g. `Product`).
2. Implement the `getTypesenseSettings()` method in your model. For the key-value format, follow the [Laravel Scout docs](https://laravel.com/docs/scout#preparing-data-for-storage-in-typesense).  
   See our [Product specification here](https://github.com/DataLinx/eclipsephp-catalogue-plugin/blob/98a0d4e35741d28c010c1a5a56de5b2cf34a8dbf/src/Models/Product.php#L48) in the catalogue plugin for a working example.  
   Notes:
   * See Typesense docs for the `collection-schema` specification [here](https://typesense.org/docs/28.0/api/collections.html#schema-parameters).
   * [Here](https://typesense.org/docs/28.0/api/collections.html#field-types) are the available field types.
   * Translatable attributes should be specified with a dot-underscore-asterisk notation, e.g. `name_.*` for the field name parameter, and with underscore-asterisk only for the `search-parameters` array.  
3. Add the model settings to the Scout config.  
   In your service provider `register` method, inject your model's settings into the `scout.typesense.model-settings` config array, e.g.:  
   ```php
   public function register()
   {
        parent::register();

        $settings = Config::get('scout.typesense.model-settings', []);

        $settings += [
            Product::class => Product::getTypesenseSettings(),
            // More models here...
        ];

        Config::set('scout.typesense.model-settings', $settings);
   }
   ```
    
### Enabling search in Filament controllers
1. Add the `Eclipse\Common\Foundation\Pages\HasScoutSearch` trait to your Filament `List` controller (e.g. `ListProducts`).
2. Add the `->searchable()` method call to your table definition in your Filament `Resource` class (e.g. `ProductResource`).
    ```php
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),

                TextColumn::make('name')
                    ->toggleable(false),
            ])
            ->searchable();
    }
    ```
3. Implement the `getGloballySearchableAttributes()` method in the `Resource` class to enable global search for the model, e.g.:
    ```php
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'code',
            'barcode',
            'manufacturers_code',
            'suppliers_code',
            'name',
            'short_description',
            'description',
        ];
    }
    ```
   
### Indexing 
Indexing happens on the fly when a model is saved, thanks to the `IsSearchable` trait.  
However, when initially setting things up for an existing model with records in the database, you need to run the batch import. For our `Product` model, you could do that in the console like so:
```shell
  php artisan scout:import "Eclipse\Catalogue\Product"
```
Read more about indexing in the Laravel docs [here](https://laravel.com/docs/scout#indexing).

### Debugging
To better understand and help you debug any problems you may encounter when implementing search with Typesense, you can use the Typesense Dashboard app that is available for all platforms [here](https://github.com/bfritscher/typesense-dashboard).  
To connect, use the parameters you specified in your Lando file.
