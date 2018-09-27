## API Docs

API documentation is generated using the [OpenAPI 2.0 specification](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md).

| File          | Description                          |
|---------------|--------------------------------------|
| [docs/api.yaml](api.yaml) | The documentation source (OpenAPI)   |
| [docs/api.html](api.html) | The compiled/generated API docs      |


### Updating the docs

Documentation is generated with [Redoc](https://github.com/Rebilly/ReDoc) using the [redoc-cli](https://github.com/Rebilly/ReDoc/blob/master/cli/README.md) tool.

First, install redoc-cli globally:
```
npm install -g redoc-cli
```

Then you can update the docs/api.yaml file, and run the following command to generate an updated version of the HTML docs:
**Make sure you have the global npm bin directory in your PATH
```
php artisan docs:generate
```

This should display a success message and overwrite the docs/api.html documentation file.

### Change Log

v 1.0.1

- fb_id field added to auth user response
- Previous auth endpoint named 'Profile' changed to 'User Session'
- Added view / edit user profile endpoints
- Added a new Profile resource
