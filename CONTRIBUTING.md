# Contributing

Thanks for your interest in contributing to conductor/orkes-laravel. This file is part of Phase 10 (Documentation & CI).

## Development setup

Clone the repo and install dependencies. Then run tests and static analysis:

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

On push and pull requests to `main` and `feature/**`, GitHub Actions run:

- **Tests** — PHPUnit on PHP 8.2, 8.3, 8.4
- **PHPStan** — Static analysis (level 5)
- **Code style** — PHP-CS-Fixer (dry run)

Ensure all pass before submitting a PR.

## Documentation

- README and `docs/` are the source of truth; update them when adding or changing behaviour
- See [docs/README.md](docs/README.md) for the documentation index and [docs/installation.md](docs/installation.md) for the installation guide
