
[![](/assets/img/tenancyforlaravel.svg)](https://tenancyforlaravel.com)

```
min
```

Version 3.x

[Version 1.x](https://tenancyforlaravel.com/docs/v1)
[Version 2.x](https://tenancyforlaravel.com/docs/v2)
[Version 3.x](https://tenancyforlaravel.com/docs/v3)

[GitHub](https://github.com/stancl/tenancy)

* [SaaS boilerplate](https://tenancyforlaravel.com/saas-boilerplate/)
* [GitHub](https://github.com/stancl/tenancy)
* [Donate](https://tenancyforlaravel.com/donate)
* [Upgrading from 2.x](https://tenancyforlaravel.com/docs/v3/upgrading)
* Introduction

  + [Introduction](https://tenancyforlaravel.com/docs/v3/introduction)
  + [Quickstart](https://tenancyforlaravel.com/docs/v3/quickstart)
  + [Installation](https://tenancyforlaravel.com/docs/v3/installation)
  + [Configuration](https://tenancyforlaravel.com/docs/v3/configuration)
  + [Compared to other packages](https://tenancyforlaravel.com/docs/v3/package-comparison)
* Concepts

  + [The two applications](https://tenancyforlaravel.com/docs/v3/the-two-applications)
  + [Tenants](https://tenancyforlaravel.com/docs/v3/tenants)
  + [Domains](https://tenancyforlaravel.com/docs/v3/domains)
  + [Event system](https://tenancyforlaravel.com/docs/v3/event-system)
  + [Routes](https://tenancyforlaravel.com/docs/v3/routes)
  + [Tenancy bootstrappers](https://tenancyforlaravel.com/docs/v3/tenancy-bootstrappers)
  + [Optional Features](https://tenancyforlaravel.com/docs/v3/optional-features)
    - [User impersonation](https://tenancyforlaravel.com/docs/v3/features/user-impersonation)
    - [Telescope tags](https://tenancyforlaravel.com/docs/v3/features/telescope-tags)
    - [Tenant Config](https://tenancyforlaravel.com/docs/v3/features/tenant-config)
    - [Cross-domain redirect](https://tenancyforlaravel.com/docs/v3/features/cross-domain-redirect)
    - [Universal routes](https://tenancyforlaravel.com/docs/v3/features/universal-routes)
    - [Vite bundler](https://tenancyforlaravel.com/docs/v3/features/vite-bundler)
* Tenancy modes

  + [Automatic mode](https://tenancyforlaravel.com/docs/v3/automatic-mode)
  + [Manual mode](https://tenancyforlaravel.com/docs/v3/manual-mode)
* Single-database tenancy

  + [Single-database tenancy](https://tenancyforlaravel.com/docs/v3/single-database-tenancy)
* Identifying tenants

  + [Tenant identification](https://tenancyforlaravel.com/docs/v3/tenant-identification)
  + [Early identification](https://tenancyforlaravel.com/docs/v3/early-identification)
* Multi-database tenancy

  + [Multi-database tenancy](https://tenancyforlaravel.com/docs/v3/multi-database-tenancy)
  + [Migrations](https://tenancyforlaravel.com/docs/v3/migrations)
  + [Customizing databases](https://tenancyforlaravel.com/docs/v3/customizing-databases)
  + [Synced resources between tenants](https://tenancyforlaravel.com/docs/v3/synced-resources-between-tenants)
  + [Session scoping](https://tenancyforlaravel.com/docs/v3/session-scoping)
  + [Queues](https://tenancyforlaravel.com/docs/v3/queues)
* Digging deeper

  + [Manual initialization](https://tenancyforlaravel.com/docs/v3/manual-initialization)
  + [Testing](https://tenancyforlaravel.com/docs/v3/testing)
  + [Integrating with other packages](https://tenancyforlaravel.com/docs/v3/integrating)
    - [Spatie packages](https://tenancyforlaravel.com/docs/v3/integrations/spatie)
    - [Horizon](https://tenancyforlaravel.com/docs/v3/integrations/horizon)
    - [Passport](https://tenancyforlaravel.com/docs/v3/integrations/passport)
    - [Nova](https://tenancyforlaravel.com/docs/v3/integrations/nova)
    - [Telescope](https://tenancyforlaravel.com/docs/v3/integrations/telescope)
    - [Livewire](https://tenancyforlaravel.com/docs/v3/integrations/livewire)
    - [Orchid](https://tenancyforlaravel.com/docs/v3/integrations/orchid)
    - [Sanctum](https://tenancyforlaravel.com/docs/v3/integrations/sanctum)
    - [Sail](https://tenancyforlaravel.com/docs/v3/integrations/sail)
    - [Vite](https://tenancyforlaravel.com/docs/v3/features/vite-bundler)
  + [Console commands](https://tenancyforlaravel.com/docs/v3/console-commands)
  + [Tenant-aware commands](https://tenancyforlaravel.com/docs/v3/tenant-aware-commands)
  + [Tenant attribute encryption](https://tenancyforlaravel.com/docs/v3/tenant-attribute-encryption)
  + [Cached lookup](https://tenancyforlaravel.com/docs/v3/cached-lookup)
  + [Real-time facades](https://tenancyforlaravel.com/docs/v3/realtime-facades)
  + [Tenant maintenance mode](https://tenancyforlaravel.com/docs/v3/tenant-maintenance-mode)
* Sponsor-only content

  + [Exclusive content for sponsors](https://sponsors.tenancyforlaravel.com/)
  + [Billable Tenants with Cashier](https://sponsors.tenancyforlaravel.com/billable-tenants-with-cashier)
  + [Central (SSO-like) Authentication](https://sponsors.tenancyforlaravel.com/central-sso-like-authentication)
  + [Customer HTTPS Certificates with Ploi](https://sponsors.tenancyforlaravel.com/customer-https-certificates-with-ploi)
  + [Deploying Applications to Ploi](https://sponsors.tenancyforlaravel.com/deploying-applications-to-ploi)
  + [Frictionless Testing Setup](https://sponsors.tenancyforlaravel.com/frictionless-testing-setup)
  + [Queued Onboarding Flow](https://sponsors.tenancyforlaravel.com/queued-onboarding-flow)
  + [Structuring the Codebase for Clarity](https://sponsors.tenancyforlaravel.com/structuring-the-codebase-for-clarity)
  + [Tenant Database Management with Ploi](https://sponsors.tenancyforlaravel.com/tenant-database-management-with-ploi)
  + [Universal (Central & Tenant) Nova](https://sponsors.tenancyforlaravel.com/universal-central-and-tenant-nova)

# Tenancy bootstrappers

Tenancy bootstrappers are classes which make your application tenant-aware in such a way that you don't have to change a line of your code, yet things will be scoped to the current tenant.

The package comes with these bootstrappers out of the box:

## Database tenancy bootstrapper

The database tenancy bootstrapper switches the **default** database connection to `tenant` after it constructs the connection for that tenant.

[Customizing databases](https://tenancyforlaravel.com/docs/v3/customizing-databases)

Note that only the **default** connection is switched. If you use another connection explicitly, be it using `DB::connection('...')`, a model `getConnectionName()` method, or a model trait like `CentralConnection`, **it will be respected.** The bootstrapper doesn't **force** any connections, it merely switches the default one.

## Cache tenancy bootstrapper

The cache tenancy bootstrapper replaces the Laravel's CacheManager instance with a custom CacheManager that adds tags with the current tenant's ids to each cache call. This scopes cache calls and lets you selectively clear tenants' caches:

```
php artisan cache:clear --tag=tenant_123

```

Note that you must use a cache store that supports tagging, e.g. Redis.

## Filesystem tenancy bootstrapper

The filesystem bootstrapper makes your app's `Storage` facade and the `storage_path()` and `asset()` helpers tenant-aware by modifying the paths they return.

> Note: If you want to bootstrap filesystem tenancy differently (e.g. provision an S3 bucket for each tenant), you can absolutely do that. Take a look at the package's bootstrappers to get an idea of how to write one yourself, and feel free to implement it any way you want.

### Storage path helper

The bootstrapper suffixes the path returned by `storage_path()` to make the helper tenant-aware.

* The suffix is built by appending the tenant key to your `suffix_base`. The `suffix_base` is `tenant` by default, but feel free to change it in the `tenancy.filesystem` config. For example, the suffix will be `tenant42` if the tenant's key is `42` and the `suffix_base` is `tenant`.
* After the suffixing, `storage_path()` helper returns `"/$path_to_your_application/storage/tenant42/"`

Since `storage_path()` will be suffixed, your folder structure will look like this:

![The folder structure](/assets/images/file_structure_tenancy.png)

Logs will be saved in `storage/logs` regardless of any changes to `storage_path()` and regardless of the tenant.

### Storage facade

The bootstrapper also makes the `Storage` facade tenant-aware by suffixing the roots of disks listed in `config('tenancy.filesystem.disks')` and by overriding the disk roots in `config('tenancy.filesystem.root_override')` (disk root = the disk path used by the `Storage` facade).

The root of each disk listed in `config('tenancy.filesystem.disks')` will be suffixed. Doing that alone could cause unwanted behavior since Laravel does its own suffixing, so the filesystem config has the `root_override` section, which lets you override the disk roots **after** tenancy has been initialized:

```
// Tenancy config (tenancy.filesystem.root_override)
// %storage_path% gets replaced by storage_path()'s output
// E.g. Storage::disk('local')->path('') will return "/$path_to_your_application/storage/tenant42/app"
// (Given a suffix_base of 'tenant' and a tenant with a key of `42`. Same as in the example above in the Storage path helper section)
'root_override' => [
    'local' => '%storage_path%/app/',
    'public' => '%storage_path%/app/public/',
],

```

To make the tenant-aware `Storage` facade work with a custom disk, add the disk's name to `config('tenancy.filesystem.disks')` and if the disk is local, override its root in `config('tenancy.filesystem.root_override')` as shown above. With S3, overriding the disk roots is not necessary – `Storage::disk('s3')->path('foo.txt')` will return `/tenant42/foo.txt`.

### Assets

The filesystem bootstrapper makes the `asset()` helper link to the files *of the current tenant*. By default, the bootstrapper makes the helper output a URL pointing to the TenantAssetsController (`/tenancy/assets/...`), which returns a file response:

```
// TenantAssetsController
return response()->file(storage_path('app/public/' . $path));

```

The package expects the assets to be stored in your tenant's `app/public/` directory. For global assets (non-private assets shared among all tenants), you may want to create a disk and use URLs from that disk instead. For example:

```
Storage::disk('branding')->url('header-logo.png');

```

To access global assets such as JS/CSS assets, you can use `global_asset()` and `mix()`.

Configuring the asset URL (`ASSET_URL` in your `.env`) changes the `asset()` helper's behavior – when the asset URL is set, the bootstrapper will suffix the configured asset URL (the same way `storage_path()` gets suffixed), and make the `asset()` helper output that instead of a path to the TenantAssetsController.

You can disable tenancy of `asset()` in the config (`tenancy.filesystem.asset_helper_tenancy`) and explicitly use `tenant_asset()` instead. `tenant_asset()` **always** returns a path to the TenantAssetController: `tenant_asset('foo.txt')` returns `your-site.com/tenancy/assets/foo.txt`. You may want to do that if you're facing issues using a package that utilizes `asset()` inside the tenant app.

Before using the `asset()` helper, make sure to [assign the identification middleware you're using in your app to TenantAssetsController's `$tenancyMiddleware`](https://tenancyforlaravel.com/docs/v3/configuration#static-properties):

```
// TenancyServiceProvider (don't forget to import the classes)

public function boot()
{
    // Update the middleware used by the asset controller
    TenantAssetsController::$tenancyMiddleware = InitializeTenancyByDomainOrSubdomain::class;
}

```
## Queue tenancy bootstrapper

This bootstrapper adds the current tenant's ID to the queued job payloads, and initializes tenancy based on this ID when jobs are being processed.

The bootstrapper has a static `$forceRefresh` property which is `false` by default. Setting the property to `true` will make tenancy re-initialize for each queued job. This is useful when you're changing the tenant's state (e.g. properties in the `data` column) and want the next job to initialize tenancy again with the new data. Features like the Tenant Config are only executed when tenancy is initialized, so the re-initialization is needed in some cases.

You can read more about this on the *Queues* page:

[Queues](https://tenancyforlaravel.com/docs/v3/queues)

## Redis tenancy bootstrapper

If you're using `Redis` calls (not cache calls, **direct** Redis calls) inside the tenant app, you will want to scope Redis data too. To do this, use this bootstrapper. It changes the Redis prefix for each tenant.

Note that you need phpredis, predis won't work.

## Writing custom bootstrappers

If you want to bootstrap tenancy for something not covered by this package — or something covered by this package, but you want different behavior — you can do that by creating a bootstrapper class.

The class must implement the `Stancl\Tenancy\Contracts\TenancyBootstrapper` interface:

```
namespace App;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class MyBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        // ...
    }

    public function revert()
    {
        // ...
    }
}

```

Then, register it in the `tenancy.bootstrappers` config:

```
'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,

    App\MyBootstrapper::class,
],

```

[Edit on GitHub](https://github.com/stancl/tenancy-docs/edit/master/source/docs/v3/tenancy-bootstrappers.blade.md)

#### Documentation

* [Tenants](/docs/v3/tenants)
* [Event system](/docs/v3/event-system)
* [Configuration](/docs/v3/configuration)

#### Documentation

* [Compared to other packages](/docs/v3/package-comparison)
* [Integrations](/docs/v3/integrating)
* [Tenant identification](/docs/v3/tenant-identification)

#### Business

* [SaaS boilerplate](/saas-boilerplate)
* [Consulting](/contact)
* [Audits](/contact)

#### Links

* [Branding](/branding)
* [GitHub](https://github.com/stancl/tenancy)
* [Discord](https://archte.ch/discord)
* [Donate](/donate)

#### Subscribe to our newsletter

Receive notifications about important releases, new packages and other updates.

Subscribe

[Twitter](https://twitter.com/samuelstancl)
[GitHub](https://github.com/stancl/tenancy)

Made by [ArchTech](https://archte.ch). © 2025 All rights reserved.

Save time with our SaaS application template.

Want to save time? Get our multi-tenant SaaS application template.

[SaaS boilerplate](https://tenancyforlaravel.com/saas-boilerplate/)

