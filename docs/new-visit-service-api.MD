# New Visit Service API - Plan

## Objective

Create a full REST CRUD API for the `visit_services` table that mirrors the
architecture of the Users, Patients, Medical Services, and Visits APIs. It uses
`VisitServiceModel` directly inside `VisitServiceController` (no service layer), a
consistent `$this->response->setJSON()` response shape with
`status/message/data/errors`, pagination `meta`, and search filters.

## Branch

- Already on branch: `feat/new-visit-api`.

## Decisions

- Mirror the `visit_services` table **as-is**: `visit_service_id` (PK),
  `visit_id`, `service_id`, `result` (nullable), `performed_by` (nullable).
  The model uses **no soft deletes and no timestamps**; `delete()` performs a hard
  delete (no inbound FKs, so it is safe).
- Enrich `index()`/`show()` (and `create()`/`update()` responses) with all joins
  (approved): `medical_services.service_name`, `patients.name as patient_name`
  (via `visits`), and `users.name as performed_by_name`.
- `visit_services` has no natural unique field, so no uniqueness / `409` checks.
- Filters qualify joined columns (`visit_services.service_id`, etc.) to avoid
  ambiguous-column SQL errors (`service_id` exists in `medical_services` too).
- Endpoint path: `visit-services` (plural resource, singular controller), added
  inside the existing `jwt` protected route group.

## Files touched

| File | Change |
| ---- | ------ |
| `app/Models/VisitServiceModel.php` | New model |
| `app/Controllers/Api/V1/VisitServiceController.php` | New controller |
| `app/Config/Routes.php` | Add `visit-services` resource + show route |
| `tests/unit/VisitServiceControllerTest.php` | New unit test |
| `docs/new-visit-service-api.MD` | This plan |

## 1. `VisitServiceModel`

- `$table = 'visit_services'`, `$primaryKey = 'visit_service_id'`,
  `$useAutoIncrement = true`, `$returnType = 'array'`.
- No `useSoftDeletes` / `useTimestamps` (table has no such columns).
- `$allowedFields = ['visit_id', 'service_id', 'result', 'performed_by']`.
- Validation:
  - `visit_id => required|integer`
  - `service_id => required|integer`
  - `result => permit_empty|max_length[100]`
  - `performed_by => permit_empty|integer`
- Join helpers (mirror `VisitModel`):
  - `getPaginatedWithDetails(int $perPage, int $page)`
  - `findWithDetails(int $id)`
  - private `buildVisitServiceWithDetailsQuery()` that selects
    `visit_services.*, medical_services.service_name, patients.name as patient_name,
    users.name as performed_by_name` with left joins on `medical_services.service_id`,
    `visits.id`, `patients.id`, `users.id`.

## 2. `VisitServiceController`

- Uses `VisitServiceModel` directly in the constructor.
- **`index()`**: `page` + `per_page` (defaults 1 / 10); filters (passed to
  `getVisitServicesWithPatient` as an array; empty values are dropped):
  - `visit_id` -> `where visit_services.visit_id`
  - `service_id` -> `where visit_services.service_id`
  - `performed_by` -> `where visit_services.performed_by`
  - `visit_date` -> `where visits.visit_date`
  - `visit_status` -> `where visits.visit_status`
  - `patient_name` -> `like patients.name`
  - `parent_id` -> `where users.id`
  - `gender` -> accepts `L`/`P` or `Laki-laki`/`Perempuan`, `where patients.gender_code`
  - paginate via `getVisitServicesWithPatient`; return `setJSON` with `meta`.
- **`show($id)`**: `findWithDetails`; 404 via `setJSON`
  `{status:'failed', message:'Visit service not found', data:$id, errors:'404'}`.
- **`create()`**: `getJSON(true)`; insert failure -> model errors; success returns
  `findWithDetails(getInsertID())`.
- **`update($id)`**: existence check -> 404; update; success via `setJSON`.
- **`delete($id)`**: existence check -> 404; hard delete; success via `respondDeleted`.

## 3. Routes

Inside the `jwt` group in `app/Config/Routes.php`:

```php
$routes->resource('visit-services', ['controller' => 'VisitServiceController', 'except' => ['new', 'show', 'edit']]);
$routes->get('visit-services/(:num)', 'VisitServiceController::show/$1');
```

## 4. Test

- `tests/unit/VisitServiceControllerTest.php` mirrors `VisitControllerTest`: mock
  `VisitServiceModel` via `setPrivateProperty`, stub the pager, and assert the
  `index()` JSON shape.

## Verification

- `php -l` on all changed PHP files.
- `composer test` (phpunit) — note: currently blocked in this environment because the
  available portable PHP build lacks the `intl` extension (`Class "Locale" not found`
  fails the whole suite, including pre-existing tests).

## Approval gates

- Plan approved by user before implementation.
- Implemented, checks run, then wait for user testing.
- Do not commit until explicitly requested.
