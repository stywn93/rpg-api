# New Medical Service API - Plan

## Objective

Create a full REST CRUD API for the `medical_services` table that mirrors the
architecture of the Users and Patients APIs. It uses `MedicalServiceModel` directly
inside `MedicalServiceController` (no service layer), a consistent
`$this->response->setJSON()` response shape with `status/message/data/errors`,
pagination `meta`, search filters, and uniqueness checks.

## Branch

- Already on branch: `feat/new-med-service-api` (up to date with origin).

## Decisions

- Mirror the `medical_services` table **as-is** (approved): the table only has
  `service_id` and `service_name`, so the model uses **no soft deletes and no
  timestamps**; `delete()` performs a hard delete.
- `visit_services.service_id` has an FK `RESTRICT` on `medical_services.service_id`,
  so deleting a service that is still referenced throws a DB error. `delete()` wraps
  the call in a try/catch (`DatabaseException`) and returns a friendly `409`.
- Endpoint path: `medical-services` (plural resource, singular controller), added
  inside the existing `jwt` protected route group.

## Files touched

| File | Change |
| ---- | ------ |
| `app/Models/MedicalServiceModel.php` | New model |
| `app/Controllers/Api/V1/MedicalServiceController.php` | New controller |
| `app/Config/Routes.php` | Add `medical-services` resource + show route |
| `tests/unit/MedicalServiceControllerTest.php` | New unit test |
| `docs/new-med-service-api.MD` | This plan |

## 1. `MedicalServiceModel`

- `$table = 'medical_services'`, `$primaryKey = 'service_id'`,
  `$useAutoIncrement = true`, `$returnType = 'array'`.
- No `useSoftDeletes` / `useTimestamps` (table has no such columns).
- `$allowedFields = ['service_name']`.
- Validation: `service_name => required|min_length[2]|max_length[100]`.

## 2. `MedicalServiceController`

- Uses `MedicalServiceModel` directly in the constructor.
- **`index()`**: `page` + `per_page` (defaults 1 / 10); filters:
  - `service_id` -> `where`
  - `service_name` -> `like`
  - paginate; return `setJSON` with `meta` (`total`, `per_page`, `current_page`,
    `last_page`).
- **`show($id)`**: `find`; 404 via `setJSON`
  `{status:'failed', message:'Medical service not found', data:$id, errors:'404'}`;
  success via `setJSON`.
- **`create()`**: `getJSON(true)`; `service_name` uniqueness -> `409` setJSON;
  insert failure -> model errors; success returns `find(getInsertID())`.
- **`update($id)`**: existence check -> 404; `service_name` uniqueness excluding
  self -> `409`; update; success via `setJSON`.
- **`delete($id)`**: existence check -> 404; hard delete wrapped in try/catch for
  `DatabaseException` (FK RESTRICT) -> `409`; success via `respondDeleted`.

## 3. Routes

Inside the `jwt` group in `app/Config/Routes.php`:

```php
$routes->resource('medical-services', ['controller' => 'MedicalServiceController', 'except' => ['show', 'edit']]);
$routes->get('medical-services/(:num)', 'MedicalServiceController::show/$1');
```

## 4. Test

- `tests/unit/MedicalServiceControllerTest.php` mirrors `PatientControllerTest`:
  mock `MedicalServiceModel` via `setPrivateProperty`, stub the pager, and assert
  the `index()` JSON shape.

## Verification

- `php -l` on all changed PHP files.
- `composer test` (phpunit).

## Approval gates

- Plan approved by user before implementation.
- Implemented, checks run, then wait for user testing.
- Do not commit until explicitly requested.
