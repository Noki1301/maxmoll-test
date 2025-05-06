#  Maxmoll Test (Mini CRM)

Laravel-приложение, реализующее REST API для управления заказами, товарами и складами.

## Технологии
- Laravel 7.x
- PHP 7.4+
- MySQL / MariaDB
- Postman (для тестирования)
- Архитектура: MVC, REST API, Eloquent ORM

## Как запустить

```bash
git clone https://github.com/Noki1301/maxmoll-test.git
cd maxmoll-test
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan seed:test-data
php artisan serve
