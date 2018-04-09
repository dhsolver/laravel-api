## Testing

There is a complete test suite available for the API.  You can edit the phpuint config at /phpunit.xml.  The testing database is set to run sqlite in memory and all storage calls are faked.

After a composer install, you can run phpunit by calling:

```
vendor/phpunit/phpunit/phpunit
```
