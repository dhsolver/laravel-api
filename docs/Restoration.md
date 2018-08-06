## How to restore/migrate iTourMobile data into Junket database

You will need the backup database file and the zip/folder of all the old itourfiles from the original iTour server. 

Locate the sql backup file for the iTourMobile Database and move it to the production server.

Run the mysql restore function on the server:
```
mysql -u {user} -p -h {host} < itourmobile-live-backup.sql
```

Then you need to set the config vars in the .env file properly:
```
ITOUR_DB_HOST=localhost
ITOUR_DB_PORT=3306
ITOUR_DB_DATABASE=itourmob_itour
ITOUR_DB_USERNAME=
ITOUR_DB_PASSWORD=
ITOUR_BACKUP_DIR=/location/to/
```
Alternatively you could plug directly into the production iTour mysql server if that is still up, but you still need the tourfiles directory.

Create a fresh migration so the database is clear.
```
php artisan migrate:fresh
```

Run the restore itour custom laravel command to convert the database and upload files.  (This will take a long time)
```
php artisan itourmobile:restore
```
