## Restoration

There is built in functionality to restore / import Tours from the origianl iTourMobile database into the Junket database.


### Requirements

This guide requires that you have SQL access to the production iTourMobile database (or backup), as well as a copy of the storage directory 'tourfiles' from the production server.


### Configuration

The following .env variables are required.  The ITOUR_BACKUP_DIR is the path to the folder that contains the 'tourfiles' directory.  The rest are basic database credentials to the iTourMobile database.
```
ITOUR_BACKUP_DIR=

ITOUR_DB_HOST=
ITOUR_DB_PORT=3306
ITOUR_DB_DATABASE=
ITOUR_DB_USERNAME=
ITOUR_DB_PASSWORD=
```


### Running

In order to ensure there are no issues with duplicate users, it is best to do this with an empty database.  You should run a fresh migration first.

```
php artisan migrate:fresh
```

To run the backup script you can use the custom Artisan command itourmobile:restore.  The command requires one parameter, which is the password to reset all of the users to.  This should be secure!

In order to ensure there are no issues with duplicate users, it is best to do this with an empty database.  You should run a fresh migration first.

```
php artisan itourmobile:restore {password}
```

This will fetch data from the live database and copy it into the new database, as well as copy / upload all related files to the Tours.    This will take a long time, there are lots of files.


### Issues

While running you will see a lot of errors, mostly talking about missing data.  The original database has a lot of either deleted, or just missing, Tours and files.