# Recreate Patient API - Plan

## Objective

Re-create the Patient API so it follows the same architecture as the Users API.
The Users API uses `UserModel` directly inside `UserController` (service layer was removed),
a consistent `$this->response->setJSON()` response shape with `status/message/data/errors`,
pagination `meta`, search filters, and uniqueness checks that include soft-deleted rows.

## Branch

- Continue on existing branch: `feat/new-patient-api` (already up to date with origin).

## Decisions

- Remove `PatientService` entirely (mirrors the earlier `UserService` removal).
- Keep all patient-specific endpoints: `showByParent`, `listWithParents`/`with-parents`,
  and the age (`usia`) join computation in `PatientModel`.
- Keep existing routes unchanged.

## Files touched

| File | Change |
| ---- | ------ |
| `app/Controllers/Api/V1/PatientController.php` | Rewrite to mirror `UserController` |
| `app/Models/PatientModel.php` | Small edits (pagination page param, `no_kk` unique validation, explicit `deletedField`) |
| `app/Services/PatientService.php` | Delete |
| `tests/unit/PatientControllerTest.php` | Update to mock `PatientModel` instead of `PatientService` |

## 1. Rewrite `PatientController`

Mirror `UserController`:

- Use `PatientModel` directly in constructor (drop `PatientService`).
- **`index()`**: read `page` + `per_page`; filters:
  - `name` -> like
  - `gender_code` -> where
  - `user_id` -> where
  - paginate via `getPaginatedWithAge($perPage, $page)`; return `setJSON` with `meta`
    (`total`, `per_page`, `current_page`, `last_page`).
- **`show($id)`**: `findWithAge`; 404 via `setJSON`
  `{status:'failed', message:'Patient not found', data:$id, errors:'404'}`; success via `setJSON`.
- **`create()`**: `getJSON(true)`; `no_kk` uniqueness via `withDeleted()` -> `409` setJSON;
  insert failure -> `400` with model errors; success returns `findWithAge(insertID)`.
- **`update($id)`**: existence check -> 404; `no_kk` uniqueness excluding self -> `409`;
  update; success via `setJSON`.
- **`delete($id)`**: existence check -> 404; soft-delete; `respondDeleted`.
- **Keep** `showByParent($parentID)` (with pagination + meta) and `listWithParents(?int $parentId)`.

## 2. Update `PatientModel`

- Add `$page` param to `getPaginatedWithAge()` and `getByParentWithAge()`:
  `paginate($perPage, 'default', $page)`.
- Add `no_kk` validation: `'required|is_unique[patients.no_kk,id,{id}]'`.
- Add explicit `deletedField = 'deleted_at'` (mirroring `UserModel`).

## 3. Delete `PatientService`

- Remove `app/Services/PatientService.php` (mirrors `UserService` removal).

## 4. Update `PatientControllerTest`

- Rewrite `testShowByParent...` to mock `PatientModel` injected via
  `setPrivateProperty('patientModel', ...)` instead of the removed `PatientService`.

## 5. Routes

- Unchanged. Existing `patients` resource + `show`, `showByParent`, `with-parents` remain valid.

## Verification

- `php -l` on all changed PHP files.
- `composer test` (phpunit).

## Approval gate

- Do not modify code without user approval.
- Implement approved plan, run checks, summarize changes, wait for user testing.
- Do not commit until explicitly requested.
