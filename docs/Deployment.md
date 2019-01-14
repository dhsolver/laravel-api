## Deployment

### Current State:
The application is currently deployed on a single EC2 instance using Nginx and PHP 7.2 FPM along with a MySQL database and Redis cache.
Storage is configured using S3 buckets
Email is configured using SES
Cache is configured using Redis

### ENV
You should keep the .env that is currently deployed on the server, but in the event of starting from scratch (including the database) there is a .env.production example file to get started with the right config.  Just fill in the missing vars (reference Development docs).

### Deployment Script
There is a deploy.sh bash script available to trigger deployment on the production server.  To deploy, just run this script.

```
./deploy.sh
```
