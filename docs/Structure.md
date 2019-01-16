## Code Structure

This repo is the backend API used for both the CMS and the moble apps, and is set up to use seperate Controllers, Requests, and Resources, while sharing all of the models.

The traditional Http directory contains the resources for the CMS
```
app/Http
```

While the CMS admin Controllers and Requests can be found inside of Admin directories in the CMS code
```
app/Http/Controllers/Admin
app/Http/Requests/Admin
```

The Mobile API resources are located in their own separate folder, but there are some shared items that get used from the CMS files.
```
app/Mobile
```


### Routes
The route files are broken apart from the traditional 'web' and 'api' files to represent the separate areas of the site.

| Route File | Purpose |
| -------------- | ---------- |
| routes/cms.php | Client CMS |
| routes/admin.php | CMS Admin |
| routes/api.php | Shared resources (auth) |
| routes/mobile.php | Mobile API |
