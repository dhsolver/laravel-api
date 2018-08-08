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
| client     |       Yes      |        Yes        |        No        |
| user       |       Yes      |         No        |        No        |


### Facebook Login

Users have the option to utilize the Facebook OAuth API to create and login to accounts.  The following .env variables are required:

```
FACEBOOK_APP_ID={your_app_id}
FACEBOOK_TOKEN={your_facebook_token}
```
