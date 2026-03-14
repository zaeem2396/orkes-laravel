# Conductor PHP SDK & Laravel Integration

Single package for Conductor workflows (Orkes Conductor Cloud and Netflix Conductor): framework-agnostic SDK plus Laravel service provider, Artisan commands, DSL, and testing utilities.

## Installation

```bash
composer require conductor/orkes-laravel
```

## Requirements

- PHP 8.2+
- Composer 2.x
- Laravel 11 or 12 (for Laravel integration; service provider and facade auto-discovered)

## Usage

**Laravel:** Use the facade and config after installing and publishing config:

```php
Conductor::workflow()->start('order_processing', ['order_id' => 123]);
Conductor::tasks()->poll('process_payment');
Conductor::workers()->listen('my_task', $handler)->run();
```

Artisan commands:

```bash
php artisan conductor:start order_processing --input='{"order_id":123}'
php artisan conductor:work --task=process_payment
php artisan conductor:inspect
php artisan conductor:local --once
php artisan conductor:failures --retry
```

**Standalone SDK:** Use the client directly:

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(new HttpClient('http://localhost:8080/api', 'your-token'));
$client->workflow()->start('order_processing', ['order_id' => 123]);
```

See [docs/ROADMAP.md](docs/ROADMAP.md) for the implementation roadmap. Phases 1–7 (HTTP client, Workflow client, Task client, Worker system, retry & exceptions, Laravel service provider, Artisan commands) are complete. The Laravel service provider registers the SDK from config and the Conductor facade is auto-discovered; Artisan commands include conductor:start, conductor:work, conductor:inspect, conductor:local, and conductor:failures.

### Laravel setup

After installing, publish the config so you can edit it (optional):

```bash
php artisan vendor:publish --tag=conductor-config
```

Configure in `.env`:

| Variable | Description | Default |
|----------|-------------|---------|
| `CONDUCTOR_SERVER` | Conductor API base URL | `http://localhost:8080/api` |
| `CONDUCTOR_TOKEN` | Bearer token (optional; omit for no auth) | — |
| `CONDUCTOR_TIMEOUT` | Request timeout (seconds) | `30` |
| `CONDUCTOR_WORKER_CONCURRENCY` | Worker concurrency | `5` |
| `CONDUCTOR_POLL_INTERVAL` | Poll interval (seconds) | `5` |
| `CONDUCTOR_RETRY_ENABLED` | Enable HTTP retry on 5xx/timeouts | `false` |
| `CONDUCTOR_RETRY_MAX_ATTEMPTS` | Max retry attempts | `3` |
| `CONDUCTOR_RETRY_INITIAL_DELAY_MS` | Initial retry delay (ms) | `1000` |

See `config/conductor.php` for all options.

The SDK throws `AuthenticationException` on 401, `WorkflowException` for workflow errors, and `TaskException` for task errors. Optional retry with exponential backoff is available via `RetryHandler` when constructing `HttpClient`, or by setting `CONDUCTOR_RETRY_ENABLED=true` in Laravel.

## Development

```bash
composer install
composer test
composer phpstan
composer cs-check
```

## License

MIT
