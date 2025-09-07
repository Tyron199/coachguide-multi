
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

# Configuration

The package is highly configurable. This page covers what you can configure in the `config/tenancy.php` file, but note that many more things are configurable. Some things can be changed by extending classes (e.g. the `Tenant` model), and **many** things can be changed using static properties. These things will *usually* be mentioned on the respective pages of the documentation, but not every time. For this reason, don't be afraid to dive into the package's source code — whenever the class you're using has a `public static` property, **it's intended to be configured**.

## Static properties

You can set static properties like this (example):

```
\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::$onFail = function () {
    return redirect('https://my-central-domain.com/');
};

```

A good place to put these calls is your `app/Providers/TenancyServiceProvider`'s `boot()` method.

### Tenant model

`tenancy.tenant_model`

This config specifies what `Tenant` model should be used by the package. There's a high chance you're using a custom model, as instructed to by the [Tenants](https://tenancyforlaravel.com/docs/v3/tenants) page, so be sure to change it in the config.

### Unique ID generator

`tenancy.id_generator`

The `Stancl\Tenancy\Database\Concerns\GeneratesIds` trait, which is applied on the default `Tenant` model, will generate a unique ID (uuid by default) if no tenant id is supplied.

If you wish to use autoincrement ids instead of uuids:

1. set this config key to null, or create a custom tenant model that doesn't use this trait
2. update the `tenants` table migration to use an incrementing column type instead of `string`
3. update the `domains` table migration `tenant_id` column to the same type as `tenants` id

### Domain model

`tenancy.domain_model`

Similar to the Tenant model config. If you're using a custom model for domains, change it in this config. If you're not using domains (e.g. if you're using path or request data identification) at all, ignore this config key altogether.

### Central domains

`tenancy.central_domains`

The list of domains that host your [central app](https://tenancyforlaravel.com/docs/v3/the-two-applications). This is used by (among other things):

* the `PreventAccessFromCentralDomains` middleware, to prevent access from central domains to tenant routes,
* the `InitializeTenancyBySubdomain` middleware, to check whether the current hostname is a subdomain on one of your central domains.

### Bootstrappers

`tenancy.bootstrappers`

This config array lets you enable, disable or add your own [tenancy bootstrappers](https://tenancyforlaravel.com/docs/v3/tenancy-bootstrappers).

### Database

> If you're using Laravel Sail, please refer the [Laravel Sail integration guide](https://tenancyforlaravel.com/docs/v3/integrations/sail).

This section is relevant to the multi-database tenancy, specifically, to the `DatabaseTenancyBootstrapper` and logic that manages tenant databases.

See this section in the config, it's documented with comments.

### Cache

`tenancy.cache.*`

This section is relevant to cache separation, specifically, to the `CacheTenancyBootstrapper`.

Note: To use the cache separation, you need to use a cache store that supports tagging, which is usually Redis.

See this section in the config, it's documented with comments.

### Filesystem

`tenancy.filesystem.*`

This section is relevant to storage separation, specifically, to the `FilesystemTenancyBootstrapper`.

See this section in the config, it's documented with comments.

### Redis

`tenancy.redis.*`

This section is relevant to Redis data separation, specifically, to the `RedisTenancyBootstrapper`.

Note: To use this bootstrapper, you need phpredis.

See this section in the config, it's documented with comments.

### Features

`tenancy.features`

This config array lets you enable, disable or add your own [feature classes](https://tenancyforlaravel.com/docs/v3/optional-features).

### Migration parameters

`tenancy.migration_parameters`

This config array lets you set parameters used by default when running the `tenants:migrate` command (or when this command is executed using the `MigrateDatabase` job). Of course, all of these parameters can be overridden by passing them directly in the command call, be it in CLI or using `Artisan::call()`.

### Seeder parameters

`tenancy.seeder_parameters`

The same as migration parameters, but for `tenants:seed` and the `SeedDatabase` job.

[Edit on GitHub](https://github.com/stancl/tenancy-docs/edit/master/source/docs/v3/configuration.blade.md)

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

