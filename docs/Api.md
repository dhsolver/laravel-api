## API Docs

API documentation is generated using the OpenApi 3.0 specification.  

| File          | Description                          |
|---------------|--------------------------------------|
| docs/api.yaml | The documentation source (OpenApi)   |
| docs/api.html | The compiled/generated API docs      |

If you have swagger-codegen installed, you can generate a fresh copy of the documentation using the custom artisan command.  

First update the api.yaml file, then run the following command:
```
php artisan docs:generate
```
This should display a success message and overwrite the api.html documentation file.


If you do not have swagger-codegen installed, install it:
```
brew install swagger-codegen
```
