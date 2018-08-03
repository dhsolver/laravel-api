## API Docs

API documentation is generated using the [https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md](OpenAPI 2.0 specification).

| File          | Description                          |
|---------------|--------------------------------------|
| [docs/api.yaml](api.yaml) | The documentation source (OpenAPI)   |
| [docs/api.html](api.html) | The compiled/generated API docs      |


### Updating the docs

Documentation is generated with [](Redoc) using the [](redoc-cli) tool.

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