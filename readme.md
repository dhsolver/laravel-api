## Junket CMS and Mobile API

### Database questions
- Safe to hard code tour pricing type ?
- Safe to hard code tour type ?
- Shouldn't start / end point get set on the tour manager?  That way message and media is done once


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
