# News Portal - Articles & Categories

A Laravel + Livewire app that lists news articles with real-time search and pagination, plus a JSON API.

## Requirements
- PHP 8.5+
- Composer 2.x

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

> Uses SQLite by default. If `database/database.sqlite` doesn't exist, `php artisan migrate` will offer to create it.

## Usage
- Open **http://127.0.0.1:8000/** to see the article list (search by title + pagination).
- Click an article to view its detail page.
- JSON API: **GET http://127.0.0.1:8000/api/articles**
