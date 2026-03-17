# Examples

Runnable examples for the Conductor PHP SDK. See [docs/dsl.md](../docs/dsl.md) for the Workflow DSL reference and [docs/README.md](../docs/README.md) for all documentation.

## order_processing_workflow.php

Defines an `order_processing` workflow using the Workflow DSL and outputs the Conductor JSON definition.

```bash
php examples/order_processing_workflow.php
```

Use `$def->register($workflowClient)` to register with Conductor when you have a client (e.g. Laravel `Conductor::workflow()`). Task types (e.g. `validate_order`) must be registered as Conductor task definitions before running the workflow. See [docs/dsl.md](../docs/dsl.md) for the full API.
