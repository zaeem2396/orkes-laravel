# Conductor Orkes PHP SDK

Framework-agnostic PHP SDK for Conductor workflows (Orkes Conductor Cloud and Netflix Conductor). Supports PHP 8.2, 8.3, and 8.4.

## Installation

```bash
composer require conductor/orkes-php-sdk
```

## Usage

```php
use Conductor\Client\ConductorClient;
use Conductor\Client\HttpClient;

$client = new ConductorClient(
    new HttpClient(
        baseUrl: 'http://localhost:8080/api',
        token: 'your-token',
    )
);

$client->workflow()->start('order_processing', ['order_id' => 123]);
```

## Roadmap

See [docs/ROADMAP.md](../../docs/ROADMAP.md) in the monorepo root.
