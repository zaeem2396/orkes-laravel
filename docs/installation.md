# Installation

Install the Conductor PHP SDK and Laravel integration via Composer. This guide covers both Laravel and standalone usage.

## Requirements

- **PHP** 8.2 or higher
- **Composer** 2.x
- **Laravel** 11 or 12 (optional; only if you use the Laravel integration and Artisan commands)

## Install the package

From your project root:

```bash
composer require conductor/orkes-laravel
```

## Laravel setup (optional)

If you use Laravel, the service provider and `Conductor` facade are auto-discovered. To customize Conductor configuration, publish the config file:

```bash
php artisan vendor:publish --tag=conductor-config
```

This creates `config/conductor.php`. Configure your Conductor server URL and (optionally) token in `.env`:

```env
CONDUCTOR_SERVER=http://localhost:8080/api
CONDUCTOR_TOKEN=your-token-if-required
```

See the [README](../README.md) for the full list of environment variables. Implementation roadmap: [ROADMAP.md](ROADMAP.md).

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

## Next steps

- [Workflow example](workflow-example.md) — start workflows and use the DSL
- [Worker example](worker-example.md) — run task workers and register handlers
- [Testing](testing.md) — use `Conductor::fake()` in tests
- [DSL reference](dsl.md) — define workflows in PHP
