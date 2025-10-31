# Laravel API Project Instructions

This is a Laravel web application configured as an API backend using SQLite database.

## Project Type
- Framework: Laravel 9.x (PHP)
- Purpose: RESTful API backend
- Database: SQLite (easily migrated to MySQL later)

## Development Guidelines
- Follow Laravel best practices for API development
- Use API resources for response formatting
- Implement proper validation in Form Requests
- Use Eloquent ORM for database operations
- Follow RESTful conventions for routes
- API routes should be defined in `routes/api.php`
- All API routes are prefixed with `/api` automatically

## Database
- Currently using SQLite
- Database file location: `database/database.sqlite`
- Ready for migration to MySQL when needed
- Change `DB_CONNECTION` in `.env` to `mysql` and configure connection details when ready to migrate

## API Structure
- All API routes should be in `routes/api.php`
- Use API middleware group for protection
- Enable CORS configuration in `config/cors.php` as needed
- Use Laravel Sanctum for API authentication if needed

## Common Commands
- Start development server: `php artisan serve`
- Run migrations: `php artisan migrate`
- Create controller: `php artisan make:controller Api/ControllerName`
- Create model: `php artisan make:model ModelName -m`
- Clear cache: `php artisan cache:clear && php artisan config:clear`
