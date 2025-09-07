
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

# Session scoping

Session scoping is one thing that you might have to deal with yourself.

The issue occurs when you're using multiple tenant databases. Users can change their session cookie's domain and their session data will be shared in another tenant's application.

Here's how you can prevent this.

## Storing sessions in the database

> Note: This approach has more variables than Redis, making it less reliable. It's recommended to use phpredis + the Redis session driver for proper session scoping.

Since the databases are automatically separated, simply using the database as the session driver will make this problem disappear altogether.

## Storing sessions in Redis

This is the same solution as using the DB session driver. If you use the [`RedisTenancyBootstrapper`](https://tenancyforlaravel.com/docs/v3/tenancy-bootstrappers), your Redis databases will be automatically separated for your tenants, and as such, any sessions stored in those Redis databases will be scoped correctly.

## Using a middleware to prevent session forgery

Alternatively, you may use the `Stancl\Tenancy\Middleware\ScopeSessions` middleware on your tenant routes to make sure that any attempts to manipulate the session will result in a 403 unauthorized response.

This will work with all storage drivers, **but only assuming you use a domain per tenant.** If you use path identification, you **need** to store sessions in the database (if using multi-DB tenancy), or you need to use single-DB tenancy (which is probably more common with path identification).

[Edit on GitHub](https://github.com/stancl/tenancy-docs/edit/master/source/docs/v3/session-scoping.blade.md)

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

