# Crystal Bubble Laundry Shop API (LMS-API)

This is a Laravel 9.x RESTful API backend for Crystal Bubble Laundry Shop, designed to power mobile and web applications for laundry management and customer service.

## Features
- **User Authentication** (Sanctum)
- **Basket & Order Management**
- **Add-ons System** (JSON storage, hardcoded prices)
- **Service Catalog** (Laundry services per kilogram)
- **Payment Integration** (PayMongo)
- **Notification System** (Email & SMS via Semaphore)
- **Loyalty Points System** (Earn points for every paid order)
- **Profile & Preferences** (Email/SMS notification toggles)
- **SQLite Database** (easy migration to MySQL)

## API Highlights
- **Basket:** Add, update, and checkout laundry items with add-ons
- **Orders:** Create, view, and manage orders
- **Services:** List all available laundry services
- **Notifications:** Automatic email/SMS after payment
- **Loyalty Points:** 1 point per ₱10 spent, tracked per user

## Add-ons
Add-ons are stored as JSON arrays in basket and order items. Prices are hardcoded in models for consistency with the .NET MAUI app.

## Service List
- Wash Only - No Soap (₱75/kg)
- Wash Only - With Soap and Fabric Conditioner (₱100/kg)
- Dry Only (₱75/kg)
- Wash & Dry - Without Soap and Fabric Conditioner (₱140/kg)
- Wash & Dry - With Soap and Fabric Conditioner (₱170/kg)
- Full Service - Wash & Dry with Soap, Fold and Fabric Conditioner (₱200/kg)
- Comforter Wash & Dry - With Soap and Fabric Conditioner (₱200/kg)

## Loyalty Points
- Users earn 1 point for every ₱10 spent on paid orders
- Points are tracked and returned in all user profile responses

## Getting Started
1. Clone the repo
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure
4. Run migrations: `php artisan migrate`
5. Seed services: `php artisan db:seed --class=NewServiceSeeder`
6. Start server: `php artisan serve`

## API Documentation
See the included Postman collections and markdown files for endpoint details and sample requests.

## .NET MAUI Integration
- Use `/api/auth/profile` to fetch loyalty points and user info
- Use `/api/services` to list available services
- Use `/api/orders` to create orders and earn points

## License
MIT

---
Developed for Crystal Bubble Laundry Shop


