# Conductor PHP SDK & Laravel Integration

Single package for Conductor workflows (Orkes Conductor Cloud and Netflix Conductor): framework-agnostic SDK plus Laravel service provider, Artisan commands, DSL, and testing utilities.

## Installation

```bash
composer require conductor/orkes-laravel
```

## Requirements

- PHP 8.2+
- Composer 2.x
- Laravel 11 or 12 (for Laravel integration)

## Usage

**Laravel:** Use the facade and config after installing and publishing config:

```php
Conductor::workflow()->start('order_processing', ['order_id' => 123]);
Conductor::tasks()->poll('process_payment');
Conductor::workers()->listen('my_task', $handler)->run();
```

**Standalone SDK:** Use the client directly:

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(new HttpClient('http://localhost:8080/api', 'your-token'));
$client->workflow()->start('order_processing', ['order_id' => 123]);
```

See [docs/ROADMAP.md](docs/ROADMAP.md) for the implementation roadmap. Phases 1–4 (HTTP client, Workflow client, Task client, Worker system) are complete.

## Development

```bash
composer install
composer test
composer phpstan
composer cs-check
```

## License

MIT
