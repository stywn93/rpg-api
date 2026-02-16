# Standard JSON Response Format
## success
```{
"status": "success",
"message": "Queue created successfully",
"data": {...},
"errors": null
}
```
## error
```
{
  "status": "error",
  "message": "Validation failed",
  "data": null,
  "errors": {
    "patient_id": "Patient is required"
  }
}

```

## Structure
```
app/
 ├── Config/
 ├── Controllers/
 │    └── Api/
 │         └── V1/
 │              ├── AuthController.php
 │              ├── UsersController.php
 │              ├── PatientsController.php
 │              ├── SchedulesController.php
 │              ├── QueuesController.php
 │              └── DashboardController.php
 ├── Models/
 │    ├── UserModel.php
 │    ├── PatientModel.php
 │    ├── ScheduleModel.php
 │    ├── QueueModel.php
 │    └── ServiceTypeModel.php
 ├── Services/
 │    ├── AuthService.php
 │    ├── QueueService.php
 │    └── ScheduleService.php
 ├── Filters/
 │    ├── JWTAuthFilter.php
 │    └── RoleFilter.php
 ├── Libraries/
 │    └── JwtLibrary.php
 └── Helpers/
```