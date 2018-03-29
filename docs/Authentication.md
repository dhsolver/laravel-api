## Authentication

Each section has a different user type so access has to be restricted accordingly.  To authenticate to a section, you must access the proper auth route:

```
CMS : https://api.wejunket.com/cms/auth/login 
Admin : https://api.wejunket.com/admin/auth/login 
Mobile : https://api.wejunket.com/mobile/auth/login 
```

### Roles and Access

| Role       | Can Access CMS | Can Access Mobile | Can Access Admin |
|------------|----------------|-------------------|------------------|
| superadmin |       Yes      |        Yes        |        Yes       |
| admin      |       Yes      |        Yes        |        Yes       |
| business   |       Yes      |        Yes        |        No        |
| user       |       Yes      |         No        |        No        |

### 3rd Party

- Roles and permissions provided by [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- All auth is based on JWT tokens using [tymondesigns/jwt-auth](https://github.com/tymondesigns/jwt-auth)