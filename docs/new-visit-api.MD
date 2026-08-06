# New Visit API - Plan

## Objective

Create a full REST CRUD API for the `visits` table that mirrors the architecture of
the Users, Patients, and Medical Services APIs. It uses `VisitModel` directly inside
`VisitController` (no service layer), a consistent `$this->response->setJSON()`
response shape with `status/message/data/errors`, pagination `meta`, and search filters.

## Branch

- Already on branch: `feat/new-visit-api` (created from `main`).

## Decisions

- Pure CRUD only (approved): no patient-specific listing endpoint.
- Mirror the `visits` table **as-is**: the table only has `id`, `patient_id`, and
  `visit_date`, so the model uses **no soft deletes and no timestamps**;
  `delete()` performs a hard delete.
- `visit_date` DB-defaults to `CURRENT_TIMESTAMP`, so validation uses
  `permit_empty|valid_date` to let clients omit it.
- `visits` has no natural unique field, so no uniqueness / `409` checks.
- `visit_services.visit_id` has an FK `CASCADE` on `visits.id`, so deleting a visit
  is safe (no `DatabaseException` handling needed).
- Endpoint path: `visits` (plural resource, singular controller), added inside the
  existing `jwt` protected route group.

## Files touched

| File | Change |
| ---- | ------ |
| `app/Models/VisitModel.php` | New model |
| `app/Controllers/Api/V1/VisitController.php` | New controller |
| `app/Config/Routes.php` | Add `visits` resource + show route |
| `tests/unit/VisitControllerTest.php` | New unit test |
| `docs/new-visit-api.MD` | This plan |

## 1. `VisitModel`

- `$table = 'visits'`, `$primaryKey = 'id'`, `$useAutoIncrement = true`,
  `$returnType = 'array'`.
- No `useSoftDeletes` / `useTimestamps` (table has no such columns).
- `$allowedFields = ['patient_id', 'visit_date']`.
- Validation:
  - `patient_id => required|integer`
  - `visit_date => permit_empty|valid_date`

## 2. `VisitController`

- Uses `VisitModel` directly in the constructor.
- **`index()`**: `page` + `per_page` (defaults 1 / 10); filters:
  - `patient_id` -> `where`
  - `visit_date` -> `where`
  - paginate; return `setJSON` with `meta` (`total`, `per_page`, `current_page`,
    `last_page`).
- **`show($id)`**: `find`; 404 via `setJSON`
  `{status:'failed', message:'Visit not found', data:$id, errors:'404'}`;
  success via `setJSON`.
- **`create()`**: `getJSON(true)`; insert failure -> model errors; success returns
  `find(getInsertID())`.
- **`update($id)`**: existence check -> 404; update; success via `setJSON`.
- **`delete($id)`**: existence check -> 404; hard delete (CASCADE, no try/catch);
  success via `respondDeleted`.

## 3. Routes

Inside the `jwt` group in `app/Config/Routes.php`:

```php
$routes->resource('visits', ['controller' => 'VisitController', 'except' => ['show', 'edit']]);
$routes->get('visits/(:num)', 'VisitController::show/$1');
```

## 4. Test

- `tests/unit/VisitControllerTest.php` mirrors `MedicalServiceControllerTest` /
  `PatientControllerTest`: mock `VisitModel` via `setPrivateProperty`, stub the
  pager, and assert the `index()` JSON shape.

## Verification

- `php -l` on all changed PHP files.
- `composer test` (phpunit) — note: currently blocked in this environment because the
  available portable PHP build lacks the `intl` extension (`Class "Locale" not found`
  fails the whole suite, including pre-existing tests).

## Approval gates

- Plan approved by user before implementation.
- Implemented, checks run, then wait for user testing.
- Do not commit until explicitly requested.
