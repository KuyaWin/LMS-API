# Crystal Bubble Laundry Shop API (LMS-API)

This is a Laravel 9.x RESTful API backend for Crystal Bubble Laundry Shop, designed to power mobile and web applications for laundry management and customer service.

---

## 🎯 Features

- **User Authentication** (Sanctum, customer/admin roles)
- **Basket & Order Management**
- **Add-ons System** (JSON storage, hardcoded prices)
- **Service Catalog** (Laundry services per kilogram)
- **Payment Integration** (PayMongo)
- **Notification System** (Email & SMS via Semaphore)
- **Loyalty Points System** (Earn points for every paid order)
- **Profile & Preferences** (Email/SMS notification toggles)
- **Order Tracking** (8-stage order status flow)
- **Promo Codes** (structure ready for discounts)
- **SQLite Database** (easy migration to MySQL)
- **Comprehensive Validation** (all endpoints)
- **CORS Configured** (frontend ready)

---

## 📋 Requirements

- PHP 7.4 or higher
- Composer
- SQLite3

---

## 🚀 Getting Started

1. **Clone the repo**
   ```bash
   cd lms-api
   ```
2. **Install dependencies**
   ```bash
   composer install
   ```
3. **Configure environment**
   - Copy `.env.example` to `.env` and configure
   - Database: SQLite (`database/database.sqlite`)
4. **Generate application key**
   ```bash
   php artisan key:generate
   ```
5. **Run migrations**
   ```bash
   php artisan migrate
   ```
6. **Seed services**
   ```bash
   php artisan db:seed --class=NewServiceSeeder
   ```
7. **Start server**
   ```bash
   php artisan serve
   ```

---

## 📡 API Documentation

Base URL: `http://localhost:8000/api`

All API responses follow this format:
```json
{
    "status": "success" | "error",
    "message": "Optional message",
    "data": { ... }
}
```

### Authentication Endpoints
- `POST /auth/customer/register` - Register customer
- `POST /auth/customer/login` - Customer login
- `POST /auth/admin/register` - Register admin
- `POST /auth/admin/login` - Admin login
- `GET /auth/profile` - Get user profile (protected)
- `POST /auth/logout` - Logout (protected)
- `POST /auth/change-password` - Change password (protected)

### Services Endpoints
- `GET /services` - List all services (public)
- `GET /services/{id}` - Get service details (public)

### Orders Endpoints
- `POST /orders` - Create order (protected)
- `GET /orders` - List orders (protected)
- `GET /orders/{id}` - Get order details (protected)
- `PATCH /orders/{id}/status` - Update order status (protected)

---

## 🧺 Service List

- Wash Only - No Soap (₱75/kg)
- Wash Only - With Soap and Fabric Conditioner (₱100/kg)
- Dry Only (₱75/kg)
- Wash & Dry - Without Soap and Fabric Conditioner (₱140/kg)
- Wash & Dry - With Soap and Fabric Conditioner (₱170/kg)
- Full Service - Wash & Dry with Soap, Fold and Fabric Conditioner (₱200/kg)
- Comforter Wash & Dry - With Soap and Fabric Conditioner (₱200/kg)

---

## 🏅 Loyalty Points

- Users earn 1 point for every ₱10 spent on paid orders
- Points are tracked and returned in all user profile responses

---

## 🧩 Add-ons

Add-ons are stored as JSON arrays in basket and order items. Prices are hardcoded in models for consistency with the .NET MAUI app.

---

## 💰 Pricing Calculation

- **Subtotal** = Sum of all service totals
- **Rush Fee** = 25% of subtotal (if rush service)
- **Total** = Subtotal + Rush Fee - Discount

---

## 🗄️ Database Structure

- **Users Table:** id, name, email, mobile_number, password, role, loyalty_points, notification preferences
- **Services Table:** id, name, description, price, unit, icon, is_active
- **Orders Table:** id, order_number, user_id, pickup_date, pickup_time, pickup_address, is_rush_service, special_instructions, promo_code, subtotal, rush_fee, discount_amount, total_amount, status, payment_status, payment_method
- **Order Items Table:** id, order_id, service_id, quantity, unit_price, total_price, addons

---

## 🔄 Order Status Flow

```
pending → in_transit → picked_up → processing → ready → out_for_delivery → completed
(cancelled at any point)
```

---

## 🛠️ Development Commands

```bash
php artisan serve
php artisan migrate
php artisan db:seed --class=NewServiceSeeder
php artisan make:migration create_table_name
php artisan make:controller Api/ControllerName
php artisan make:model ModelName -m
php artisan cache:clear
php artisan config:clear
php artisan route:list --path=api
php artisan test
```

---

## 📁 Project Structure

```
lms-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   └── Models/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── database.sqlite
├── routes/
│   └── api.php
└── .env
```

---

## 🔒 API Security

- Token-based authentication (Sanctum)
- Role-based access (customer/admin)
- Request validation
- CORS configuration
- Password hashing (bcrypt)
- Protected routes require valid bearer token

---

## 🚀 Production Deployment

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Migrate to MySQL for production
- Optimize with `php artisan config:cache`, `route:cache`, `view:cache`, `optimize`
- Security: change default passwords, configure CORS, set up SSL, rate limiting, error monitoring, backups, restrict admin registration
- Set proper file permissions

---

## .NET MAUI Integration

- Use `/api/auth/profile` to fetch loyalty points and user info
- Use `/api/services` to list available services
- Use `/api/orders` to create orders and earn points

---

## 🆘 Troubleshooting

- **Unauthenticated error:** Check Bearer token in header
- **Pickup date error:** Use today/future date in YYYY-MM-DD
- **DB error:** Ensure `database/database.sqlite` exists and is writable
- **Service not found:** Run seeder: `php artisan db:seed --class=NewServiceSeeder`

---

## 📄 License

MIT

---

Developed for Crystal Bubble Laundry Shop

---

**You're Ready!**  
Your laundromat API is fully configured and ready to use. Start testing with Postman!

**Server:** `http://localhost:8000`  
**API Base:** `http://localhost:8000/api`

---

# Full API Reference & Examples

## API Endpoint Summary

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/services` | List all services |
| GET | `/services/{id}` | Get service details |
| POST | `/auth/customer/register` | Register customer |
| POST | `/auth/customer/login` | Customer login |
| POST | `/auth/admin/register` | Register admin |
| POST | `/auth/admin/login` | Admin login |

### Protected Endpoints (Require Auth Token)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/auth/profile` | Get user profile |
| POST | `/auth/logout` | Logout user |
| POST | `/auth/change-password` | Change password |
| POST | `/orders` | Create new order |
| GET | `/orders` | Get user's orders |
| GET | `/orders/{id}` | Get order details |
| PATCH | `/orders/{id}/status` | Update order status |

---

## Quick Test Flow

1. **Get Services (Public)**
   ```
   GET http://localhost:8000/api/services
   ```

2. **Register Customer**
   ```
   POST http://localhost:8000/api/auth/customer/register
   
   {
       "name": "Test Customer",
       "email": "test@example.com",
       "mobile_number": "+639123456789",
       "password": "password123",
       "password_confirmation": "password123"
   }
   ```
   *Copy the token from response*

3. **Create Order**
   ```
   POST http://localhost:8000/api/orders
   Authorization: Bearer YOUR_TOKEN
   
   {
       "pickup_date": "2025-10-25",
       "pickup_time": "14:30",
       "pickup_address": "123 Main St, Manila",
       "is_rush_service": true,
       "services": [
           {
               "service_id": 1,
               "quantity": 5
           }
       ]
   }
   ```

4. **View Orders**
   ```
   GET http://localhost:8000/api/orders
   Authorization: Bearer YOUR_TOKEN
   ```

---

## Pricing Examples

**Example 1: Simple Order**
- Wash & Fold: 5kg × PHP 50 = PHP 250
- **Total: PHP 250**

**Example 2: Rush Service**
- Wash & Fold: 5kg × PHP 50 = PHP 250
- Rush Fee (20%): PHP 50
- **Total: PHP 300**

**Example 3: Multiple Services**
- Wash & Fold: 5kg × PHP 50 = PHP 250
- Dry Cleaning: 2 items × PHP 100 = PHP 200
- Wash & Iron: 3kg × PHP 75 = PHP 225
- Subtotal: PHP 675
- Rush Fee (20%): PHP 135
- **Total: PHP 810**

---

## API Structure

### Routes
All API routes are defined in `routes/api.php` and automatically prefixed with `/api`.

Example route:
```php
Route::get('/users', [UserController::class, 'index']);
```
Accessible at: `http://localhost:8000/api/users`

### Creating API Endpoints

1. **Create a Controller**
   ```bash
   php artisan make:controller Api/YourController
   ```

2. **Create a Model with Migration**
   ```bash
   php artisan make:model YourModel -m
   ```

3. **Create an API Resource** (for response formatting)
   ```bash
   php artisan make:resource YourResource
   ```

4. **Define Routes** in `routes/api.php`
   ```php
   Route::apiResource('your-endpoint', YourController::class);
   ```

---

## Database Configuration

### Current: SQLite
The project is currently configured to use SQLite. The database file is located at:
```
database/database.sqlite
```

### Migrating to MySQL
When ready to migrate to MySQL:

1. Update `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. Create the MySQL database
3. Run migrations: `php artisan migrate`

---

## 🛠️ Development Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Seed services data
php artisan db:seed --class=ServiceSeeder

# Create new migration
php artisan make:migration create_table_name

# Create controller
php artisan make:controller Api/ControllerName

# Create model with migration
php artisan make:model ModelName -m

# Clear cache
php artisan cache:clear
php artisan config:clear

# List all routes
php artisan route:list --path=api

# Run tests
php artisan test
```

---

## 🔒 API Security

- **Token-based authentication** using Laravel Sanctum
- **Role-based access** (customer/admin)
- **Request validation** on all endpoints
- **CORS configuration** for frontend integration
- **Password hashing** with bcrypt
- **Protected routes** require valid bearer token

---

## 🚀 Production Deployment

Before deploying to production:

1. **Set environment to production**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Migrate to MySQL** (recommended for production)
   - Update `.env` with MySQL credentials
   - Run migrations

3. **Optimize the application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

4. **Security checklist**
   - [ ] Change all default passwords
   - [ ] Configure CORS for your domain
   - [ ] Set up SSL/HTTPS
   - [ ] Configure rate limiting
   - [ ] Set up error monitoring
   - [ ] Configure backups
   - [ ] Review and restrict admin registration endpoint

5. **Ensure proper file permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

---

## 📊 Database Schema

### Migration to MySQL

When ready for production, update your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundromat_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run:
```bash
php artisan migrate --seed
```

---

## Full API Reference & Examples

### Authentication

#### 1. Customer Registration
**Endpoint:** `POST /auth/customer/register`

**Request:**
```json
{
    "name": "John Doe",
    "email": "customer@example.com",
    "mobile_number": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "Customer registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "customer@example.com",
            "mobile_number": "+1234567890",
            "role": "customer"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

#### 2. Customer Login
**Endpoint:** `POST /auth/customer/login`

**Request:**
```json
{
    "email": "customer@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "customer@example.com",
            "mobile_number": "+1234567890",
            "role": "customer"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

#### 3. Admin Registration
**Endpoint:** `POST /auth/admin/register`

**Request:**
```json
{
    "name": "Admin User",
    "email": "admin@example.com",
    "mobile_number": "+0987654321",
    "password": "admin123",
    "password_confirmation": "admin123"
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "Admin registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "mobile_number": "+0987654321",
            "role": "admin"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

#### 4. Admin Login
**Endpoint:** `POST /auth/admin/login`

**Request:**
```json
{
    "email": "admin@example.com",
    "password": "admin123"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "mobile_number": "+0987654321",
            "role": "admin"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

#### 5. Get Profile (Protected)
**Endpoint:** `GET /auth/profile`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "customer@example.com",
            "mobile_number": "+1234567890",
            "role": "customer",
            "loyalty_points": 10,
            "notification_preferences": {
                "email": true,
                "sms": false
            }
        }
    }
}
```

#### 6. Logout (Protected)
**Endpoint:** `POST /auth/logout`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Response (200):**
```json
{
    "status": "success",
    "message": "Logout successful"
}
```

#### 7. Change Password (Protected)
**Endpoint:** `POST /auth/change-password`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Request:**
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Password changed successfully"
}
```

---

### Services

#### Get All Services (Public)
**Endpoint:** `GET /services`

**Response:**
```json
{
    "status": "success",
    "data": {
        "services": [
            {
                "id": 1,
                "name": "Wash & Fold",
                "description": "Professional washing and folding service",
                "price": "50.00",
                "unit": "kg",
                "icon": "👕",
                "is_active": true
            },
            {
                "id": 2,
                "name": "Dry Cleaning",
                "description": "Expert dry cleaning for delicate items",
                "price": "100.00",
                "unit": "item",
                "icon": "🧥",
                "is_active": true
            },
            {
                "id": 3,
                "name": "Wash & Iron",
                "description": "Complete wash and ironing service",
                "price": "75.00",
                "unit": "kg",
                "icon": "👔",
                "is_active": true
            },
            {
                "id": 4,
                "name": "Iron Only",
                "description": "Professional ironing service",
                "price": "40.00",
                "unit": "kg",
                "icon": "🔥",
                "is_active": true
            }
        ]
    }
}
```

#### Get Single Service (Public)
**Endpoint:** `GET /services/{id}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "service": {
            "id": 1,
            "name": "Wash & Fold",
            "description": "Professional washing and folding service",
            "price": "50.00",
            "unit": "kg",
            "icon": "👕",
            "is_active": true
        }
    }
}
```

---

### Orders

#### Create Order (Protected)
**Endpoint:** `POST /orders`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Request:**
```json
{
    "pickup_date": "2025-10-25",
    "pickup_time": "14:30",
    "pickup_address": "123 Main St, Manila",
    "is_rush_service": true,
    "special_instructions": "Handle with care",
    "promo_code": "FIRST20",
    "payment_method": "gcash",
    "services": [
        {
            "service_id": 1,
            "quantity": 5
        },
        {
            "service_id": 3,
            "quantity": 3
        }
    ]
}
```

**Required Fields:**
- `pickup_date` (date, YYYY-MM-DD, today or future)
- `pickup_time` (time, HH:MM, 24-hour format)
- `pickup_address` (string)
- `services` (array, min 1 item)
  - `service_id` (integer, valid service ID)
  - `quantity` (decimal, min 0.1)

**Optional Fields:**
- `is_rush_service` (boolean, adds 20% fee)
- `special_instructions` (string)
- `promo_code` (string)
- `payment_method` (string)

**Response (201):**
```json
{
    "status": "success",
    "message": "Order created successfully",
    "data": {
        "order": {
            "id": 1,
            "order_number": "ORD-2025-001",
            "pickup_date": "Oct 25, 2025",
            "pickup_time": "02:30 PM",
            "pickup_address": "123 Main St, Manila",
            "is_rush_service": true,
            "status": "pending",
            "payment_status": "paid",
            "services": [
                {
                    "name": "Wash & Fold",
                    "quantity": "5.00",
                    "unit": "kg",
                    "unit_price": "50.00",
                    "total_price": "250.00"
                },
                {
                    "name": "Wash & Iron",
                    "quantity": "3.00",
                    "unit": "kg",
                    "unit_price": "75.00",
                    "total_price": "225.00"
                }
            ],
            "subtotal": "475.00",
            "rush_fee": "95.00",
            "discount_amount": "0.00",
            "total_amount": "570.00"
        }
    }
}
```

**Pricing Calculation:**
- **Subtotal** = Sum of all service totals
- **Rush Fee** = 25% of subtotal (if rush service)
- **Total** = Subtotal + Rush Fee - Discount

#### Get All Orders (Protected)
**Endpoint:** `GET /orders`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "orders": [
            {
                "id": 1,
                "order_number": "ORD-2025-001",
                "pickup_date": "Oct 25, 2025",
                "pickup_time": "10:30 AM",
                "status": "processing",
                "payment_status": "paid",
                "is_rush_service": true,
                "services": [
                    {
                        "name": "Wash & Fold",
                        "quantity": "5.00 kg"
                    }
                ],
                "total_amount": "450.00",
                "created_at": "Jan 15, 2025"
            }
        ]
    }
}
```

#### Get Order Details (Protected)
**Endpoint:** `GET /orders/{id}`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "order": {
            "id": 1,
            "order_number": "ORD-2025-001",
            "pickup_date": "Oct 25, 2025",
            "pickup_time": "02:30 PM",
            "pickup_address": "123 Main St, Manila",
            "is_rush_service": true,
            "status": "pending",
            "payment_status": "paid",
            "services": [
                {
                    "name": "Wash & Fold",
                    "quantity": "5.00",
                    "unit": "kg",
                    "unit_price": "50.00",
                    "total_price": "250.00"
                },
                {
                    "name": "Wash & Iron",
                    "quantity": "3.00",
                    "unit": "kg",
                    "unit_price": "75.00",
                    "total_price": "225.00"
                }
            ],
            "subtotal": "475.00",
            "rush_fee": "95.00",
            "discount_amount": "0.00",
            "total_amount": "570.00",
            "special_instructions": "Handle with care",
            "promo_code": "FIRST20",
            "payment_method": "gcash",
            "created_at": "2025-10-20T10:00:00Z",
            "updated_at": "2025-10-20T10:00:00Z"
        }
    }
}
```

#### Update Order Status (Protected)
**Endpoint:** `PATCH /orders/{id}/status`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Request:**
```json
{
    "status": "processing"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Order status updated successfully"
}
```

**Valid Statuses:**
- `pending` - Order placed, awaiting pickup
- `in_transit` - Rider on the way to pickup
- `picked_up` - Items collected from customer
- `processing` - Items being processed/cleaned
- `ready` - Items ready for delivery
- `out_for_delivery` - Items out for delivery
- `completed` - Order completed
- `cancelled` - Order cancelled

---

## Error Handling

All error responses follow this format:
```json
{
    "status": "error",
    "message": "Error description",
    "data": null
}
```

### Common Errors

- **400 Bad Request:** Validation errors, missing parameters
- **401 Unauthorized:** Invalid or missing authentication token
- **403 Forbidden:** Insufficient permissions for the requested action
- **404 Not Found:** Resource not found (e.g., order, service)
- **500 Internal Server Error:** Unexpected server error

---

## Testing with Postman

### Environment Setup

- **Base URL:** `http://localhost:8000/api`
- **Authorization:** Bearer token for protected routes

### Sample Requests

1. **Get Services**
   - Method: `GET`
   - URL: `/services`

2. **Register Customer**
   - Method: `POST`
   - URL: `/auth/customer/register`
   - Body:
     ```json
     {
         "name": "Test Customer",
         "email": "test@example.com",
         "mobile_number": "+639123456789",
         "password": "password123",
         "password_confirmation": "password123"
     }
     ```

3. **Create Order**
   - Method: `POST`
   - URL: `/orders`
   - Headers: `Authorization: Bearer YOUR_TOKEN`
   - Body:
     ```json
     {
         "pickup_date": "2025-10-25",
         "pickup_time": "14:30",
         "pickup_address": "123 Main St, Manila",
         "is_rush_service": true,
         "services": [
             {
                 "service_id": 1,
                 "quantity": 5
             }
         ]
     }
     ```

4. **View Orders**
   - Method: `GET`
   - URL: `/orders`
   - Headers: `Authorization: Bearer YOUR_TOKEN`

---

## API Security Best Practices

- **Use HTTPS** to encrypt data in transit
- **Validate input** on all endpoints to prevent injection attacks
- **Limit login attempts** to prevent brute force attacks
- **Regularly update** dependencies and server software
- **Monitor logs** for suspicious activity
- **Backup data** regularly and test restore procedures

---

## Performance Optimization

- **Use caching** for frequently accessed data (e.g., services list)
- **Optimize database queries** with proper indexing
- **Use queueing** for time-consuming tasks (e.g., sending notifications)
- **Serve static files** (e.g., images, CSS) from a CDN
- **Enable Gzip compression** to reduce response size

---

## Common API Workflows

### 1. User Registration and Order Creation

1. **Register user**
   - Endpoint: `POST /auth/customer/register`
   - Body: `{ "name": "...", "email": "...", "mobile_number": "...", "password": "...", "password_confirmation": "..." }`

2. **Login user**
   - Endpoint: `POST /auth/customer/login`
   - Body: `{ "email": "...", "password": "..." }`

3. **Create order**
   - Endpoint: `POST /orders`
   - Headers: `Authorization: Bearer USER_TOKEN`
   - Body: `{ "pickup_date": "...", "pickup_time": "...", "pickup_address": "...", "services": [ { "service_id": 1, "quantity": 5 } ] }`

### 2. Admin Order Management

1. **Admin login**
   - Endpoint: `POST /auth/admin/login`
   - Body: `{ "email": "...", "password": "..." }`

2. **Get all orders**
   - Endpoint: `GET /orders`
   - Headers: `Authorization: Bearer ADMIN_TOKEN`

3. **Update order status**
   - Endpoint: `PATCH /orders/{id}/status`
   - Headers: `Authorization: Bearer ADMIN_TOKEN`
   - Body: `{ "status": "completed" }`

---

## Conclusion

This API provides a comprehensive backend solution for the Crystal Bubble Laundry Shop, covering all aspects of laundry service management, from user authentication to order processing and payment integration. With its modular design and clear documentation, it is ready to be integrated with frontend applications or used as a standalone service.

For any questions or support, please contact the development team or refer to the Laravel documentation for more detailed technical guidance.



