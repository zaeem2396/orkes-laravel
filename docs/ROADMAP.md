# Orkes Conductor PHP SDK & Laravel Toolkit — Roadmap

This roadmap is based on the Cursor master prompt. It divides the implementation into **sub-modules** with begin/completion dates and status tracking.

**Project goal:** Production-quality PHP SDK and Laravel integration for Conductor workflows (Orkes Conductor Cloud + Netflix Conductor). Single package: `conductor/orkes-laravel`.

---

## Overview

| Phase | Module | Begin | Completion | Status |
|-------|--------|-------|------------|--------|
| 1 | Core HTTP client | 2025-03-08 | 2025-03-14 | Done |
| 2 | Workflow client | 2025-03-15 | 2025-03-21 | Done |
| 3 | Task client | 2025-03-22 | 2025-03-28 | Done |
| 4 | Worker system | 2025-03-29 | 2025-04-04 | Done |
| 5 | Retry logic & exceptions | 2025-04-05 | 2025-04-11 | Done |
| 6 | Laravel service provider | 2025-04-12 | 2025-04-18 | Done |
| 7 | Artisan commands | 2025-04-19 | 2025-04-25 | Done |
| 8 | Workflow DSL | 2025-04-26 | 2025-05-09 | Done |
| 9 | Testing utilities | 2025-05-10 | 2025-05-16 | Done |
| 10 | Documentation & CI | 2025-05-17 | 2025-05-30 | Done |

**Status legend:** `Planned` | `In progress` | `Done` | `Blocked`

---

## Phase 1 — Core HTTP client

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-03-08  
**Completion:** 2025-03-14  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 1.1 | Guuzzle wrapper | `HttpClient` class wrapping Guuzzle | 2025-03-08 | 2025-03-10 | Done |
| 1.2 | Base URL & config | Base URL, timeout, JSON options | 2025-03-08 | 2025-03-10 | Done |
| 1.3 | Auth headers | Token / key-based authentication | 2025-03-09 | 2025-03-11 | Done |
| 1.4 | Request API | `request(string $method, string $uri, array $data = [])` | 2025-03-10 | 2025-03-12 | Done |
| 1.5 | ConductorClient entrypoint | Main SDK client with `workflow()`, `tasks()`, `workers()` | 2025-03-12 | 2025-03-14 | Done |

### Deliverables

- `src/Client/HttpClient.php`
- `src/Client/ConductorClient.php`
- PSR-12, PHP 8.2+, strict types, docblocks

---

## Phase 2 — Workflow client

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-03-15  
**Completion:** 2025-03-21  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 2.1 | WorkflowClient class | Class and constructor | 2025-03-15 | 2025-03-15 | Done |
| 2.2 | startWorkflow / getWorkflow | Start and fetch workflow | 2025-03-15 | 2025-03-17 | Done |
| 2.3 | terminate / retry / pause / resume | Lifecycle operations | 2025-03-17 | 2025-03-19 | Done |
| 2.4 | getWorkflowStatus | Status and search helpers | 2025-03-18 | 2025-03-19 | Done |
| 2.5 | registerWorkflowDefinition / updateWorkflowDefinition | Definition CRUD | 2025-03-19 | 2025-03-21 | Done |

### Deliverables

- `src/Workflow/WorkflowClient.php`
- Methods: `startWorkflow`, `getWorkflow`, `terminateWorkflow`, `retryWorkflow`, `pauseWorkflow`, `resumeWorkflow`, `getWorkflowStatus`, `registerWorkflowDefinition`, `updateWorkflowDefinition`

---

## Phase 3 — Task client

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-03-22  
**Completion:** 2025-03-28  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 3.1 | TaskClient class | Class and HTTP wiring | 2025-03-22 | 2025-03-23 | Done |
| 3.2 | poll | Poll for tasks by task type | 2025-03-23 | 2025-03-24 | Done |
| 3.3 | complete / fail / update | Task result submission | 2025-03-24 | 2025-03-26 | Done |
| 3.4 | ack | Acknowledge task (extend lease) | 2025-03-26 | 2025-03-27 | Done |
| 3.5 | Error handling & exceptions | TaskException, validation | 2025-03-27 | 2025-03-28 | Done |

### Deliverables

- `src/Task/TaskClient.php`
- `src/Exceptions/TaskException.php`
- Methods: `poll`, `complete`, `fail`, `update`, `ack`

---

## Phase 4 — Worker system

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-03-29  
**Completion:** 2025-04-04  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 4.1 | Worker class | Worker loop skeleton | 2025-03-29 | 2025-03-30 | Done |
| 4.2 | listen(taskType, callback) | Register task handlers | 2025-03-30 | 2025-04-01 | Done |
| 4.3 | Infinite polling & sleep interval | Configurable poll + sleep | 2025-04-01 | 2025-04-02 | Done |
| 4.4 | Retry & failure handling | On failure: retry or fail task | 2025-04-02 | 2025-04-04 | Done |
| 4.5 | Output contract | Return `status`, `outputData` from handler | 2025-04-03 | 2025-04-04 | Done |

### Deliverables

- `src/Task/Worker.php`
- Support: infinite polling, sleep interval, retry, failure handling, callback returning `COMPLETED`/`FAILED` + outputData
- Optional `workerId`, `domain`, and `maxRetries` constructor options; `runOneCycle()` for single-cycle execution (e.g. tests)

---

## Phase 5 — Retry logic & exceptions

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-04-05  
**Completion:** 2025-04-11  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 5.1 | RetryHandler | Exponential backoff, max attempts | 2025-04-05 | 2025-04-07 | Done |
| 5.2 | Delay strategy | Configurable delay (exponential/linear) | 2025-04-06 | 2025-04-08 | Done |
| 5.3 | ConductorException hierarchy | Base + specific exceptions | 2025-04-07 | 2025-04-09 | Done |
| 5.4 | AuthenticationException / WorkflowException / TaskException | Specific exception types | 2025-04-08 | 2025-04-10 | Done |
| 5.5 | Integrate retry into HttpClient | Optional retry on 5xx / timeouts | 2025-04-09 | 2025-04-11 | Done |

### Deliverables

- `src/Retry/RetryHandler.php`, `src/Retry/DelayStrategy.php`, `ExponentialDelayStrategy`, `LinearDelayStrategy`
- `src/Exceptions/ConductorException.php`, `AuthenticationException`, `WorkflowException`, `TaskException`, `RetryableException`
- HttpClient: optional `RetryHandler` constructor arg; 401 → `AuthenticationException`; 5xx/connection errors → retry then `RetryableException` or `ConductorException`

---

## Phase 6 — Laravel service provider

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-04-12  
**Completion:** 2025-04-18  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 6.1 | Package skeleton | composer.json, src layout | 2025-04-12 | 2025-04-12 | Done |
| 6.2 | ConductorServiceProvider | Register SDK client from config | 2025-04-12 | 2025-04-14 | Done |
| 6.3 | Publish config | config/conductor.php | 2025-04-13 | 2025-04-14 | Done |
| 6.4 | Facade Conductor | Conductor::workflow(), tasks(), workers() | 2025-04-14 | 2025-04-16 | Done |
| 6.5 | Config: base_url, auth_token, worker_concurrency, poll_interval | Env-driven config | 2025-04-15 | 2025-04-18 | Done |

### Deliverables

- `src/Laravel/Providers/ConductorServiceProvider.php` — registers ConductorClient singleton from config, optional RetryHandler when `retry_enabled`
- `src/Laravel/Facades/Conductor.php`
- `config/conductor.php` (publishable); keys: base_url, auth_token, timeout, worker_concurrency, poll_interval, retry_enabled, retry_max_attempts, retry_initial_delay_ms
- `composer.json` extra.laravel (providers, aliases) for auto-discovery

---

## Phase 7 — Artisan commands

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-04-19  
**Completion:** 2025-04-25  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 7.1 | conductor:start | Start workflow by name (e.g. order_processing) | 2025-04-19 | 2025-04-20 | Done |
| 7.2 | conductor:work | Worker daemon; options: --task, --concurrency, --queue | 2025-04-20 | 2025-04-22 | Done |
| 7.3 | conductor:inspect | Active workflows, failed workflows, pending tasks, workers | 2025-04-22 | 2025-04-24 | Done |
| 7.4 | conductor:local | Local dev: run workers with --once for single cycle | 2025-04-23 | 2025-04-25 | Done |
| 7.5 | conductor:failures | Observability: list failed workflows, optional --retry | 2025-04-24 | 2025-04-25 | Done |

### Deliverables

- `src/Laravel/Console/StartWorkflowCommand.php` — conductor:start with --input, --correlation-id, --wf-version
- `src/Laravel/Console/WorkerCommand.php` — conductor:work using config task_handlers, --task, --queue
- `src/Laravel/Console/InspectCommand.php` — conductor:inspect via WorkflowClient::search
- `src/Laravel/Console/LocalCommand.php` — conductor:local with --once
- `src/Laravel/Console/FailuresCommand.php` — conductor:failures with --retry
- WorkflowClient::search() for workflow search API

---

## Phase 8 — Workflow DSL

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-04-26  
**Completion:** 2025-05-09  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 8.1 | Workflow::define(name) | Entry point, fluent builder | 2025-04-26 | 2025-04-28 | Done |
| 8.2 | ->task(name) chaining | Linear task chain | 2025-04-28 | 2025-05-01 | Done |
| 8.3 | Conductor JSON generation | Emit Conductor workflow definition JSON | 2025-05-01 | 2025-05-05 | Done |
| 8.4 | Register via SDK | registerWorkflowDefinition from DSL | 2025-05-05 | 2025-05-07 | Done |
| 8.5 | Docs & examples | README, docs/dsl.md, examples/ for DSL | 2025-05-07 | 2025-05-09 | Done |

### Deliverables

- `src/Laravel/DSL/Workflow.php` (entry point), `src/Laravel/DSL/WorkflowDefinition.php` (fluent builder)
- Developer-friendly API: `Workflow::define('order_processing')->task('validate_order')->task('charge_payment')->task('send_confirmation');` with optional description, version, ownerEmail, inputParameters, outputParameters.
- toArray(), toJson(), register(WorkflowClient); description(), version(), ownerEmail(), inputParameters(), outputParameters() builders
- Auto-generation of Conductor JSON definitions (schemaVersion 2, SIMPLE tasks). Tests in tests/Laravel/DSL/. See docs/dsl.md and examples/.

---

## Phase 9 — Testing utilities

**Package:** `conductor/orkes-laravel`  
**Begin:** 2025-05-10  
**Completion:** 2025-05-16  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 9.1 | ConductorFake | Fake workflow/tasks/workers for tests | 2025-05-10 | 2025-05-12 | Done |
| 9.2 | Conductor::fake() | Swap real client with fake via Facade | 2025-05-11 | 2025-05-13 | Done |
| 9.3 | assertWorkflowStarted | Assertion helpers (assertWorkflowStartedWithInput, assertNoWorkflowsStarted, recordedStartedWorkflows) | 2025-05-12 | 2025-05-14 | Done |
| 9.4 | PHPUnit tests for SDK | tests/ (Client, Workflow, Task, Retry, etc.) | 2025-05-13 | 2025-05-15 | Done |
| 9.5 | PHPUnit tests for Laravel | tests/Laravel/ (ConductorFakeTest, ConductorFakeFacadeTest, Console, DSL, etc.) | 2025-05-14 | 2025-05-16 | Done |

### Deliverables

- `src/Laravel/Testing/ConductorFake.php`, `FakeWorkflowClient.php`, `FakeTaskClient.php`, `FakeWorker.php` (fakes for workflow, tasks, workers). FakeWorkflowClient::start() returns 'fake-workflow-id'.
- `Conductor::fake()` on Facade (returns ConductorFake); example: `Conductor::fake(); Conductor::workflow()->start('order_processing'); Conductor::assertWorkflowStarted('order_processing');`
- Assertion helpers: `assertWorkflowStarted`, `assertWorkflowStartedWithInput`, `assertNoWorkflowsStarted`, `recordedStartedWorkflows`. docs/testing.md.
- PHPUnit tests in tests/ (SDK) and tests/Laravel/ (Laravel, ConductorFakeTest, ConductorFakeFacadeTest, Console, DSL). See docs/testing.md.

---

## Phase 10 — Documentation & CI

**Scope:** Repository-wide (README, docs/, examples/, .github/workflows).  
**Begin:** 2025-05-17  
**Completion:** 2025-05-30  
**Status:** Done

### Sub-modules

| # | Sub-module | Description | Begin | Completion | Status |
|---|------------|-------------|-------|------------|--------|
| 10.1 | README.md (SDK) | Installation, usage, standalone examples | 2025-05-17 | 2025-05-19 | Done |
| 10.2 | README.md (Laravel) | Laravel setup, config, Artisan commands | 2025-05-18 | 2025-05-20 | Done |
| 10.3 | docs/ + examples/ | installation.md, workflow-example.md, worker-example.md, docs/README | 2025-05-19 | 2025-05-23 | Done |
| 10.4 | GitHub Actions CI | tests.yml (PHP 8.2/8.3/8.4), phpstan.yml, format.yml | 2025-05-22 | 2025-05-25 | Done |
| 10.5 | PHPStan | Static analysis level 5 (phpstan.neon.dist) | 2025-05-24 | 2025-05-28 | Done |
| 10.6 | Testing examples in docs | testing.md Testing examples section | 2025-05-26 | 2025-05-30 | Done |

### Deliverables

- `README.md` — SDK and Laravel usage, installation, testing, development
- `docs/` — installation.md, workflow-example.md, worker-example.md, dsl.md, testing.md, ROADMAP.md
- `examples/` — order_processing_workflow.php, README
- `.github/workflows/` — tests.yml (PHP 8.2/8.3/8.4 matrix), phpstan.yml, format.yml (PHP-CS-Fixer). See CONTRIBUTING.md.
- PHPStan level 5 (phpstan.neon.dist); clean docblocks and examples

---

## Implementation order (summary)

1. Core HTTP client  
2. Workflow client  
3. Task client  
4. Worker system  
5. Retry logic & exceptions  
6. Laravel service provider  
7. Artisan commands (conductor:start, conductor:work, conductor:inspect, conductor:local, conductor:failures)  
8. Workflow DSL (Workflow::define, ->task(), toArray, toJson, register; docs/dsl.md, examples/)  
9. Testing utilities (Conductor::fake(), ConductorFake, FakeTaskClient, FakeWorker, assertion helpers, docs/testing.md)  
10. Documentation & CI (docs/installation, workflow/worker examples, CI workflows, testing examples)  

---

## Expected final result (Phase 10 complete)

Developers will be able to:

```bash
composer require conductor/orkes-laravel
```

```php
Conductor::workflow()->start('order_processing', ['order_id' => 1]);

// Or define with DSL and register:
Workflow::define('order_processing')->task('validate_order')->task('charge_payment')->register(Conductor::workflow());
```

```bash
php artisan conductor:work
php artisan conductor:inspect
php artisan conductor:failures --retry
```

Testing: `Conductor::fake(); Conductor::workflow()->start('order_processing'); Conductor::assertWorkflowStarted('order_processing');` (see docs/testing.md).

---

## Coding standards

- PSR-12  
- PHP 8.2+  
- Strict types  
- SOLID, dependency injection  
- Typed properties  
- Docblocks, usage examples, and proper exception handling on every class  
