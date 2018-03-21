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
