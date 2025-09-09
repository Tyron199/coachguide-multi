
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

# Tenants

A tenant can be any model that implements the `Stancl\Tenancy\Contracts\Tenant` interface.

The package comes with a base `Tenant` model that's ready for common things, though will require extending in most cases as it attempts not to be too opinionated.

The base model has the following features on top of the ones that are necessary by the interface:

* Forced central connection (lets you interact with `Tenant` models even in the tenant context)
* Data column trait — lets you store arbitrary keys. Attributes that don't exist as columns on your `tenants` table go to the `data` column as serialized JSON.
* Id generation trait — when you don't supply an ID, a random uuid will be generated. An alternative to this would be using AUTOINCREMENT columns. If you wish to use numerical ids, change the `create_tenants_table` migration to use `bigIncrements()` or some such column type, and set `tenancy.id_generator` config to null. That will disable the ID generation altogether, falling back to the database's autoincrement mechanism.

## Tenant Model

**Most** applications using this package will want domain/subdomain identification and tenant databases. To do this, create a new model, e.g. `App\Tenant`, that looks like this:

```
<?php

namespace App;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}

```

Then, configure the package to use this model in `config/tenancy.php`:

```
'tenant_model' => \App\Tenant::class,

```

If you want to customize the `Domain` model, you can do that too.

**If you don't need domains or databases, ignore the steps above.** Everything will work just as well.

## Creating tenants

You can create tenants like any other models:

```
$tenant = Tenant::create([
    'plan' => 'free',
]);

```

After the tenant is created, an event will be fired. This will result in things like the database being created and migrated, depending on what jobs listen to the event.

## Custom columns

Attributes of the tenant model which don't have their own column will be stored in the `data` JSON column. You can set these attributes like you'd set normal model attributes:

```
$tenant->update([
    'attributeThatHasNoColumn' => 'value', // stored in the `data` JSON column
    'plan' => 'free' // stored in the dedicated `plan` column (see below)
]);

```

or

```
$tenant->customAttribute = 'value'; // stored in the `data` JSON column
$tenant->plan = 'free'; // stored in the `plan` column (see below)
$tenant->save();

```

You may define the custom columns (that **won't** be stored in the `data` JSON column) by overriding the `getCustomColumns()` method on your `Tenant` model:

```
public static function getCustomColumns(): array
{
    return [
        'id',
        'plan',
    ];
}

```

**Don't forget to keep `id` in the custom columns!**

If you want to rename the `data` column, rename it in a migration and implement this method on your model:

```
public static function getDataColumn(): string
{
    return 'my-data-column';
}

```

Note that querying data inside the `data` column with `where()` will require that you do for example:

```
where('data->foo', 'bar')

```

The data column is encoded/decoded only on model retrieval and saving.

Also a good rule of thumb is that when you need to query the data with `WHERE` clauses, it should have a dedicated column. This will improve performance and you won't have to think about the `data->` prefixing.

## Running commands in the tenant context

You may run commands in a tenant's context and then return to the previous context (be it central, or another tenant's) by passing a callable to the `run()` method on the tenant object. For example:

```
$tenant->run(function () {
    User::create(...);
});

```
## Internal keys

Keys that start with the internal prefix (`tenancy_` by default, but you can customize this by overriding the `internalPrefix()` method) are for internal use, so don't start any attribute/column names with that.

## Events

The `Tenant` model dispatches Eloquent events, all of which have their own respective class. You can read more about this on the [Event system](https://tenancyforlaravel.com/docs/v3/event-system) page.

## Accessing the current tenant

You may access the current tenant using the `tenant()` helper. You can also pass an argument to get an attribute from that tenant model, e.g. `tenant('id')`.

Alternatively, you may typehint the `Stancl\Tenancy\Contracts\Tenant` interface to inject the model using the service container.

## Incrementing IDs

By default, the migration uses `string` for the `id` column, and the model generates UUIDs when you don't supply an `id` during tenant creation.

If you'd like to use incrementing ids instead, you can override the `getIncrementing()` method:

```
public function getIncrementing()
{
    return true;
}

```

[Edit on GitHub](https://github.com/stancl/tenancy-docs/edit/master/source/docs/v3/tenants.blade.md)

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

