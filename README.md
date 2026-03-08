# Orkes Conductor PHP SDK & Laravel Toolkit

Monorepo for production-quality PHP support for Conductor workflows (Orkes Conductor Cloud and Netflix Conductor).

## Packages

| Package | Description |
|---------|-------------|
| [orkes-php-sdk](packages/orkes-php-sdk) | Framework-agnostic PHP SDK: REST client, workflows, tasks, workers, retries |
| [laravel-conductor](packages/laravel-conductor) | Laravel integration: service provider, Artisan commands, DSL, testing utilities |

## Roadmap

See [docs/ROADMAP.md](docs/ROADMAP.md) for the full implementation roadmap, sub-modules, and status.

## Requirements

- PHP 8.2+
- Composer 2.x

## Development

```bash
# Install dependencies (from repo root)
composer install

# Run SDK tests
cd packages/orkes-php-sdk && composer test

# Run Laravel package tests
cd packages/laravel-conductor && composer test
```

## License

TBD (open-source).
