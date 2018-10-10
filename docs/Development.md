## Development Setup

1. Clone repo
```
git clone https://github.com/faze11/junket-api.git && cd junket-api
```

2. Copy .env file
```
cp .env.example .env
```

3. Run composer install
```
composer install
```

4. Generate secrets
```
php artisan key:generate
php artisan jwt:generate
```

5. Setup database credentials in .env file

6. Migrate & seed database
```
php artisan migrate:fresh --seed
```

7. Storage config
By default the .env is set to use the local storage driver, which you may need to create a symlink for by running the following on your homestead box:
```
php artisan storage:link
```

You can also set up S3 storage by getting an S3 API key and changing the following vars in the .env:
```
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID={access_key_id_here}
AWS_SECRET_ACCESS_KEY={secret_key_here}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=staging.wejunket.com
AWS_URL=http://staging.wejunket.com/
```

8. Other config

Facebook API keys are required to enable FB login as per [Authentication Docs](docs/Authentication.md)

In order for password reset emails to dispatch properly the env variable CMS_URL needs to be set.

ffmpeg must be installed and you should set the full path to the binaries in the .env file
```
FFMPEG_BINARY=/usr/bin/ffmpeg
FFPROBE_BINARY=/usr/bin/ffprobe
```