# Contributing

Thanks for your interest in contributing to conductor/orkes-laravel.

## Development setup

```bash
composer install
composer test
composer phpstan
composer cs-check
```

## Code standards

- PSR-12 code style (enforced by PHP-CS-Fixer; run `composer cs-fix` to auto-fix)
- PHP 8.2+
- Strict types and docblocks on public APIs
- See [docs/ROADMAP.md](docs/ROADMAP.md) § Coding standards

## CI

On push and pull requests, GitHub Actions run:

- **Tests** — PHPUnit on PHP 8.2, 8.3, 8.4
- **PHPStan** — Static analysis (level 5)
- **Code style** — PHP-CS-Fixer (dry run)

Ensure all pass before submitting a PR.

## Documentation

- Update [README.md](README.md) and [docs/](docs/) as needed for new features
- See [docs/README.md](docs/README.md) for the documentation index
