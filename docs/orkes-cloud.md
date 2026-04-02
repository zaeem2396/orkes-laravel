# Orkes Conductor Cloud

This package works with **Orkes Conductor Cloud** and **Netflix Conductor OSS** using the same REST API, with Orkes-specific authentication and start-workflow paths.

## Authentication

Orkes issues **application access keys** (Key ID + Key Secret). The SDK calls `POST {base_url}/token` with JSON `keyId` and `keySecret`, receives a JWT, and sends it on **`X-Authorization`** for subsequent API calls (not `Authorization: Bearer`).

Configure in `.env` (or `ConductorClient::fromArray()`):

| Variable | Purpose |
|----------|---------|
| `CONDUCTOR_SERVER_URL` | API base URL (preferred; often ends with `/api`) |
| `CONDUCTOR_SERVER` | Fallback base URL if `CONDUCTOR_SERVER_URL` is unset |
| `CONDUCTOR_AUTH_KEY` | Application Key ID |
| `CONDUCTOR_AUTH_SECRET` | Application Key Secret |
| `CONDUCTOR_TOKEN` | Optional static JWT; use with `CONDUCTOR_AUTH_HEADER_STYLE=orkes` for UI tokens |
| `CONDUCTOR_AUTH_HEADER_STYLE` | `bearer` (default) or `orkes` (`X-Authorization` for static token) |

If both `CONDUCTOR_TOKEN` and key/secret are set, the static token wins.

See [Orkes: Authentication and Access Keys](https://orkes.io/content/sdks/authentication).

## Starting workflows (Orkes)

Orkes documents **`POST /api/workflow/{workflowName}`** with the **workflow input object** as the JSON body (query params: `version`, `correlationId`, `priority`). The SDK’s `WorkflowClient::start()` uses this path style so the same code works against Orkes and OSS deployments that support it.

Plain-text workflow execution IDs returned by Orkes (not only strict UUIDs) are normalized to `workflowId` in the client response.

## Laravel

Publish config (`php artisan vendor:publish --tag=conductor-config`) or merge keys into `config/conductor.php`: `base_url`, `auth_token`, `auth_key`, `auth_secret`, `auth_header_style`.

## Standalone

```php
$client = ConductorClient::fromArray([
    'base_url' => 'https://your-cluster.orkescloud.com/api',
    'auth_key' => getenv('CONDUCTOR_AUTH_KEY'),
    'auth_secret' => getenv('CONDUCTOR_AUTH_SECRET'),
    'timeout' => 30,
]);
$client->workflow()->start('my_workflow', ['key' => 'value']);
```

## Token lifetime

JWTs from the token endpoint expire. The Laravel `ConductorClient` singleton fetches a token at container resolution time; restart workers or the app after expiry, or extend the SDK with refresh-on-401 if you need long-lived processes.
