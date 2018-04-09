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

5. Configure enviornment (database/redis)

6. Migrate & seed database
```
php artisan migrate:fresh --seed
```

