# Conductor Laravel

Laravel integration for Conductor workflows: Artisan commands, workflow DSL, and testing utilities. Supports Laravel 11 and 12, PHP 8.2–8.4.

## Installation

```bash
composer require conductor/laravel-conductor
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=conductor-config
```

## Usage

```php
use Conductor\Laravel\Facades\Conductor;

Conductor::workflow()->start('order_processing', ['order_id' => 1]);
```

```bash
php artisan conductor:work
```

## Roadmap

See [docs/ROADMAP.md](../../docs/ROADMAP.md) in the monorepo root.
