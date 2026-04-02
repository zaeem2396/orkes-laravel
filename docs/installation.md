# Installation

Install the Conductor PHP SDK and Laravel integration via Composer. This guide covers Laravel and standalone (no framework) usage.

## Requirements

- **PHP** 8.2 or higher
- **Composer** 2.x
- **Laravel** 11 or 12 (optional; the package works as a standalone SDK when not using Laravel)

## Install the package

From your project root (Laravel app or any PHP project):

```bash
composer require conductor/orkes-laravel
```

## Laravel setup (optional)

If you use Laravel, the service provider and `Conductor` facade are auto-discovered (no manual registration). To customize Conductor configuration, publish the config file:

```bash
php artisan vendor:publish --tag=conductor-config
```

This creates `config/conductor.php`. Configure the API base URL and authentication in `.env`.

**Conductor OSS / local:**

```env
CONDUCTOR_SERVER=http://localhost:8080/api
CONDUCTOR_TOKEN=your-token-if-required
```

**Orkes Conductor Cloud** (application access key — recommended):

```env
CONDUCTOR_SERVER_URL=https://your-cluster.orkescloud.com/api
CONDUCTOR_AUTH_KEY=your-key-id
CONDUCTOR_AUTH_SECRET=your-key-secret
```

See [Orkes Cloud](orkes-cloud.md) and the [README](../README.md) for the full list of environment variables. For the implementation roadmap, see [ROADMAP.md](ROADMAP.md).

## Standalone (no Laravel)

Use the SDK without Laravel by constructing the client manually (e.g. in a CLI script or another framework):

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(new HttpClient(
    'http://localhost:8080/api',
    'your-token',
    30
));
$client->workflow()->start('my_workflow', ['key' => 'value']);
```

You can also use **`ConductorClient::fromArray()`**, including HTTP retry options and **Orkes** fields (`auth_key`, `auth_secret`, `auth_header_style`) as in Laravel’s `config/conductor.php`:

```php
$client = ConductorClient::fromArray([
    'base_url' => 'http://localhost:8080/api',
    'token' => 'your-token',
    'timeout' => 30,
    'retry_enabled' => true,
    'retry_max_attempts' => 3,
    'retry_initial_delay_ms' => 1000,
]);
```

## Next steps

- [Workflow example](workflow-example.md) — start workflows and use the DSL
- [Worker example](worker-example.md) — run task workers and register handlers
- [Testing](testing.md) — use `Conductor::fake()` in tests
- [DSL reference](dsl.md) — define workflows in PHP

Implementation roadmap: [ROADMAP.md](ROADMAP.md).
