# Breeze CRUD
Generate CRUD based on Laravel Breeze starter kit. It will generate Model, resource Controller and Views. It also register resource controller to `routes/web.php`. There are 2 attributes as a sample (title and description). Oh you have to generate migration manually for it.

## Installation via composer
Run command:
```
composer require --dev tediscript/breeze-crud
```

Or add this script to your `composer.json` and run `composer update`

```
    "require-dev": {
        "tediscript/breeze-crud": "^0.1.0"
    }
```

## Usage
Just like you create model via php artisan.

### Create CRUD
```
php artisan breeze:crud ModelName
```
Given ModelName is `School` then it will generate:
- `App/Http/Controllers/SchoolController.php`
- `app/Models/School.php`
- `resources/views/schools/create.blade.php`
- `resources/views/schools/edit.blade.php`
- `resources/views/schools/index.blade.php`
- `resources/views/schools/show.blade.php`

and add resource controller to `routes/web.php`

### Delete CRUD
```
php artisan breeze:crud ModelName -d
```
Above command will delete all file generated before.

Thats it.
