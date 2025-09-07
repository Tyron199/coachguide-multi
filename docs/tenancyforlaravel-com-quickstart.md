
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

# Quickstart Tutorial

This tutorial focuses on getting you started with stancl/tenancy 3.x quickly. It implements multi-database tenancy & domain identification. If you need a different implementation, then **that's absolutely possible with this package** and it's very easy to refactor to a different implementation.

We recommend following this tutorial just **to get things working** so that you can play with the package. Then if you need to, you can refactor the details of the multi-tenancy implementation (e.g. single-database tenancy, request data identification, etc).

## Installation

First, require the package using composer:

```
composer require stancl/tenancy

```

Then, run the `tenancy:install` command:

```
php artisan tenancy:install

```

This will create a few files: migrations, config file, route file and a service provider.

Let's run the migrations:

```
php artisan migrate

```

Register the service provider in `bootstrap/providers.php`:

```
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class, // <-- here
];

```
## Creating a tenant model

Now you need to create a Tenant model. The package comes with a default Tenant model that has many features, but it attempts to be mostly unopinionated and as such, we need to create a custom model to use domains & databases. Create the file `app/Models/Tenant.php` like this:

```
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}

```

*Please note: if you have the models anywhere else, you should adjust the code and commands of this tutorial accordingly.*

Now we need to tell the package to use this custom model. Open the `config/tenancy.php` file and modify the line below:

```
'tenant_model' => \App\Models\Tenant::class,

```
## Events

The defaults will work out of the box here, but a short explanation will be useful. The `TenancyServiceProvider` file in your `app/Providers` directory maps tenancy events to listeners. By default, when a tenant is created, it runs a `JobPipeline` (a smart thing that's part of this package) which makes sure that the `CreateDatabase`, `MigrateDatabase` and optionally other jobs (e.g. `SeedDatabase`) are ran sequentially.

In other words, it creates & migrates the tenant's database after he's created — and it does this in the correct order, because normal event-listener mapping would execute the listeners in some stupid order that would result in things like the database being migrated before it's created, or seeded before it's migrated.

## Central routes

We'll make a small change to your existing route files. Specifically, we'll make sure that central routes are registered on central domains only:

```
// routes/web.php, api.php or any other central route files you have

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // your actual routes
    });
}

```

Alternatively, to keep your route files more clean, you can use [this approach](https://github.com/archtechx/tenancy/pull/1180#issuecomment-2006098346) to register all of your routes in the `using` callback of the Application Builder.

## Central domains

Now we need to actually specify the central domains. A central domain is a domain that serves your "central app" content, e.g. the landing page where tenants sign up. Open the `config/tenancy.php` file and add them in:

```
'central_domains' => [
    'saas.test', // Add the ones that you use. I use this one with Laravel Valet.
],

```

If you're using Laravel Sail, no changes are needed, default values are good to go:

```
'central_domains' => [
    '127.0.0.1',
    'localhost',
],

```
## Tenant routes

Your tenant routes will look like this by default:

```
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});

```

These routes will only be accessible on tenant (non-central) domains — the `PreventAccessFromCentralDomains` middleware enforces that.

Let's make a small change to dump all the users in the database, so that we can actually see multi-tenancy working. Open the file `routes/tenant.php` and apply the modification below:

```
Route::get('/', function () {
    dd(\App\Models\User::all());
    return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
});

```
## Migrations

To have users in tenant databases, let's move the `users` table migration (the file `database/migrations/0001_01_01_000000_create_users_table.php` or similar) to `database/migrations/tenant`. This will prevent the table from being created in the central database, and it will be instead created in the tenant database when a tenant is created — thanks to our event setup. If you have any other migrations that are necessary for your application, move those migrations as well.

## Creating tenants

> If you're using Laravel Sail, please refer the [Laravel Sail integration guide](https://tenancyforlaravel.com/docs/v3/integrations/sail):

For testing purposes, we'll create a tenant in `tinker` — no need to waste time creating controllers and views for now.

```
$ php artisan tinker
>>> $tenant1 = App\Models\Tenant::create(['id' => 'foo']);
>>> $tenant1->domains()->create(['domain' => 'foo.localhost']);
>>>
>>> $tenant2 = App\Models\Tenant::create(['id' => 'bar']);
>>> $tenant2->domains()->create(['domain' => 'bar.localhost']);

```

Now we'll create a user inside each tenant's database:

```
App\Models\Tenant::all()->runForEach(function () {
    App\Models\User::factory()->create();
});

```
## Trying it out

Now we visit `foo.localhost` in our browser, replace `localhost` with one of the values of `central_domains` in the file `config/tenancy.php` as modified previously. We should see a dump of the users table where we see some user. If we visit `bar.localhost`, we should see a different user.

[Edit on GitHub](https://github.com/stancl/tenancy-docs/edit/master/source/docs/v3/quickstart.blade.md)

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

