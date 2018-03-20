## Junket CMS and Mobile API

### Development Setup

Clone repo
```
git clone https://github.com/faze11/junket-api.git && cd junket-api
```

Run composer install
```
composer install
```

Generate secrets
```
php artisan key:generate
php artisan jwt:generate
```

Migrate & seed database
```
php artisan migrate:fresh --seed
```
