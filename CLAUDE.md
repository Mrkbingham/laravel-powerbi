# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

This is a Laravel package that provides a REST API client for Microsoft Power BI. It uses Saloon v3 for HTTP interactions and implements two OAuth2 authentication flows with Azure AD:
- **Client Credentials Grant** for Service Principal authentication (server-to-server)
- **Authorization Code Grant** for Azure User authentication (user-delegated)

**Namespace**: `InterWorks\PowerBI`

## Development Commands

### Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run a single test file
vendor/bin/pest tests/Unit/Requests/Groups/GetGroupsTest.php

# Run a specific test by name
vendor/bin/pest --filter "can get groups"
```

### Code Quality

```bash
# Format code with Laravel Pint
composer format

# Run static analysis (PHPStan level 10)
composer analyse
```

### Package Setup

```bash
# Refresh package discovery
composer prepare
```

## Architecture

### HTTP Client Layer (Saloon)

The package uses a hierarchical connector architecture to support multiple OAuth2 flows:

**Base Connector**:
- **`PowerBIConnectorBase`** (`src/PowerBIConnectorBase.php`): Abstract base class that provides:
  - Base URL resolution (`https://api.powerbi.com/v1.0/myorg`)
  - Request sending with automatic account type restriction enforcement
  - Custom exception handling (admin endpoints, access denied, etc.)
  - Common functionality shared by all connector types

**Concrete Connectors**:
- **`PowerBIServicePrincipal`** (`src/Connectors/PowerBIServicePrincipal.php`):
  - Uses `ClientCredentialsGrant` trait from Saloon
  - Supports `ServicePrinciple` and `AdminServicePrinciple` account types
  - Azure AD v1.0 endpoints with `resource` parameter
  - Token endpoint: `https://login.windows.net/{tenant}/oauth2/token`
  - Server-to-server authentication without user interaction

- **`PowerBIAzureUser`** (`src/Connectors/PowerBIAzureUser.php`):
  - Uses `AuthorizationCodeGrant` trait from Saloon
  - Supports `AzureUser` account type only
  - Azure AD v1.0 endpoints
  - Authorization endpoint: `https://login.microsoftonline.com/{tenant}/oauth2/authorize`
  - Token endpoint: `https://login.microsoftonline.com/{tenant}/oauth2/token`
  - User-delegated authentication with browser redirect
  - Supports access token refresh via refresh tokens

**Factory Class**:
- **`PowerBI`** (`src/PowerBI.php`): Factory class that provides:
  - Static `create()` method to instantiate appropriate connector based on account type
  - Convenience methods: `servicePrinciple()`, `AdminServicePrinciple()`, `azureUser()`
  - Legacy constructor support for backward compatibility (Service Principal only)
  - Configuration resolution from `config/powerbi.php`

### Request/Response Pattern

All API requests follow this structure:

1. **Request classes** extend `Saloon\Http\Request` (e.g., `src/Requests/Groups/GetGroups.php`)
   - Define HTTP method and endpoint
   - Implement `createDtoFromResponse()` to transform responses into DTOs

2. **DTO classes** (Data Transfer Objects) in `src/DTO/`
   - Immutable readonly properties
   - Implement `WithResponse` interface from Saloon
   - Use `HasResponse` trait to attach response metadata
   - Collection DTOs include static `fromArray()` factory methods

Example flow:

```text
PowerBI connector → GetGroups request → Groups DTO → Collection<Group>
```

### Authentication

The package supports two OAuth2 authentication flows:

**1. Client Credentials Grant (Service Principal)**:
- Used for server-to-server authentication
- No user interaction required
- Azure AD v1.0 endpoints
- Token endpoint: `https://login.windows.net/{tenant}/oauth2/token`
- Resource parameter: `https://analysis.windows.net/powerbi/api`
- Environment variables:
  - `POWER_BI_TENANT` - Azure AD tenant ID
  - `POWER_BI_CLIENT_ID` - Standard service principal credentials
  - `POWER_BI_CLIENT_SECRET` - Standard service principal credentials
  - `POWER_BI_ADMIN_CLIENT_ID` - Admin service principal credentials (for admin endpoints)
  - `POWER_BI_ADMIN_CLIENT_SECRET` - Admin service principal credentials

**2. Authorization Code Grant (Azure User)**:
- Used for user-delegated authentication
- Requires user browser interaction and consent
- Azure AD v1.0 endpoints
- Authorization endpoint: `https://login.microsoftonline.com/{tenant}/oauth2/authorize`
- Token endpoint: `https://login.microsoftonline.com/{tenant}/oauth2/token`
- Environment variables:
  - `POWER_BI_TENANT` - Azure AD tenant ID
  - `POWER_BI_USER_CLIENT_ID` - User application client ID
  - `POWER_BI_USER_CLIENT_SECRET` - User application client secret

### Account Type Restrictions

Power BI REST API enforces different access levels based on authentication type:

- **ServicePrinciple**: Can access most endpoints but NOT individual resource endpoints (`/reports/{id}`, `/dashboards/{id}`). Must use group-scoped endpoints instead.
- **AdminServicePrinciple**: Can access admin endpoints (`/admin/*`) and all non-admin endpoints including individual resources.
- **AzureUser**: Can access all non-admin endpoints including individual resource endpoints.

The package automatically enforces these restrictions via the `HasAccountTypeRestrictions` trait. Requests that use this trait will throw `AccountTypeRestrictedException` before making API calls if the account type is not allowed.

### Testing Strategy

Tests use **Saloon's MockClient** with fixture files:

- Fixtures stored in `tests/Fixtures/Saloon/{endpoint}/{action}.json`
- `PowerBIFixture` class handles sensitive data redaction (Authorization headers, IDs, tokens)
- Tests mock both the request class and verify DTO structure
- Environment configuration loaded from `tests/.env` via `TestCase`

**Key pattern**: Tests authenticate the connector, send the request with a mock client, and verify both HTTP status and DTO structure.

## Configuration

The package uses Spatie's `laravel-package-tools` for service provider scaffolding:

- Config file: `config/powerbi.php`
- Service provider: `src/PowerBIServiceProvider.php`
- Facade: `src/Facades/PowerBI.php`

## Static Analysis

PHPStan configuration (`phpstan.neon`):

- Level 10 (maximum strictness)
- Includes baseline for existing issues
- Octane compatibility checks enabled
- Model properties validation enabled

## Usage Examples

### Service Principal (Client Credentials)

```php
use InterWorks\PowerBI\PowerBI;

// Using factory method (recommended)
$connector = PowerBI::servicePrinciple();

// Or using create method
$connector = PowerBI::create(ConnectionAccountType::ServicePrinciple);

// Authenticate
$token = $connector->getAccessToken();
$connector->authenticate($token);

// Make API calls
$request = new GetGroups();
$response = $connector->send($request);
$groups = $response->dto();
```

### Admin Service Principal

```php
use InterWorks\PowerBI\PowerBI;

// Using convenience method (recommended)
$connector = PowerBI::AdminServicePrinciple();

// Authenticate
$token = $connector->getAccessToken();
$connector->authenticate($token);

// Access admin endpoints
$request = new GetGroupsAsAdmin();
$response = $connector->send($request);
```

### Azure User (Authorization Code)

```php
use InterWorks\PowerBI\PowerBI;

// Step 1: Generate authorization URL
$connector = PowerBI::azureUser();
$authUrl = $connector->getAuthorizationUrl();
$state = $connector->getState();

// Store $state in session
session(['oauth_state' => $state]);

// Redirect user to $authUrl
return redirect($authUrl);

// Step 2: Handle callback (in your redirect URI controller)
$code = request()->get('code');
$state = request()->get('state');
$sessionState = session('oauth_state');

// Exchange code for token
$token = $connector->getAccessToken($code, $state, $sessionState);
$connector->authenticate($token);

// Step 3: Store token for later use
cache()->put("powerbi_token_{$userId}", serialize($token), $token->getExpiresAt());

// Step 4: Retrieve and refresh token when needed
$token = unserialize(cache()->get("powerbi_token_{$userId}"));
if ($token->hasExpired()) {
    $token = $connector->refreshAccessToken($token);
    cache()->put("powerbi_token_{$userId}", serialize($token), $token->getExpiresAt());
}
$connector->authenticate($token);

// Make API calls
$request = new GetReport('report-id');
$response = $connector->send($request);
```

## Adding New Endpoints

When adding a new Power BI endpoint:

1. Create request class in `src/Requests/{Category}/{Action}.php`
2. Create DTO(s) in `src/DTO/` with readonly properties
3. Implement `createDtoFromResponse()` in request class with PHPStan type annotations
4. **If endpoint has account type restrictions**, use the `HasAccountTypeRestrictions` trait:
   ```php
   use InterWorks\PowerBI\Requests\Concerns\HasAccountTypeRestrictions;

   class GetReport extends Request
   {
       use HasAccountTypeRestrictions;

       public function restrictedAccountTypes(): array
       {
           return [ConnectionAccountType::ServicePrinciple];
       }
   }
   ```
   The connector will automatically enforce restrictions before sending the request.
5. Add test in `tests/Unit/Requests/{Category}/{Action}Test.php`
6. If using `HasAccountTypeRestrictions`, add tests for each account type to verify access control
7. Create fixture JSON in `tests/Fixtures/Saloon/{category}/{action}.json`
8. Run `composer format` and `composer analyse` before committing
