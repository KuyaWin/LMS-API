# Laundromat API - Laravel Backend

A complete Laravel-based RESTful API for a laundromat service booking application. Built with SQLite database (easily migrated to MySQL) and Laravel Sanctum for authentication.

## 🎯 Features

### Core Functionality
- **User Authentication** - Separate login for customers and admins
- **Service Management** - Pre-loaded laundry services with pricing
- **Order Booking** - Complete order creation with multiple services
- **Order Tracking** - 8-stage order status flow
- **Rush Service** - Priority processing with 20% fee
- **Promo Codes** - Structure ready for discount implementation

### Technical Features
- **Laravel 9.x** - Modern PHP framework
- **SQLite Database** - Lightweight and portable (easily migrated to MySQL)
- **RESTful API** - Clean API architecture with `/api` prefix
- **Laravel Sanctum** - Token-based API authentication
- **CORS Configured** - Ready for frontend integration
- **Comprehensive Validation** - Request validation on all endpoints

## 📋 Requirements

- PHP 7.4 or higher
- Composer
- SQLite3

## 🚀 Installation

1. **Clone or navigate to the project directory**
   ```bash
   cd lms-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   - The `.env` file is already configured
   - Database: SQLite (database/database.sqlite)

4. **Generate application key** (if not already set)
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed the database with services**
   ```bash
   php artisan db:seed --class=ServiceSeeder
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```
---

## 📡 API Documentation

Base URL: `http://localhost:8000/api`

### Response Format
All API responses follow this format:
```json
{
    "status": "success" | "error",
    "message": "Optional message",
    "data": {
        // Response data
    }
}
```

---

## 🔐 Authentication Endpoints

### 1. Customer Registration
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

### 2. Customer Login
**Endpoint:** `POST /auth/customer/login`

**Request:**
```json
{
    "email": "customer@example.com",
    "password": "password123"
}
```

### 3. Admin Registration
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

### 4. Admin Login
**Endpoint:** `POST /auth/admin/login`

**Request:**
```json
{
    "email": "admin@example.com",
    "password": "admin123"
}
```

### 5. Get Profile (Protected)
**Endpoint:** `GET /auth/profile`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

### 6. Logout (Protected)
**Endpoint:** `POST /auth/logout`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

### 7. Change Password (Protected)
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

---

## 🧺 Services Endpoints

### Get All Services (Public)
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

### Get Single Service (Public)
**Endpoint:** `GET /services/{id}`

---

## 📦 Orders Endpoints

### Create Order (Protected)
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
- **Rush Fee** = 20% of subtotal (if rush service)
- **Total** = Subtotal + Rush Fee - Discount

### Get All Orders (Protected)
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

### Get Order Details (Protected)
**Endpoint:** `GET /orders/{id}`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

### Update Order Status (Protected)
**Endpoint:** `PATCH /orders/{id}/status`
**Headers:** `Authorization: Bearer YOUR_TOKEN`

**Request:**
```json
{
    "status": "processing"
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

## 💰 Services & Pricing

| Service | Price | Unit | Description |
|---------|-------|------|-------------|
| Wash & Fold | PHP 50 | kg | Professional washing and folding |
| Dry Cleaning | PHP 100 | item | Expert dry cleaning for delicates |
| Wash & Iron | PHP 75 | kg | Complete wash and ironing |
| Iron Only | PHP 40 | kg | Professional ironing service |

**Rush Service:** Adds 20% fee to subtotal for priority processing

---

## 🧪 Testing with Postman

### Quick Test Flow

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

### Pricing Examples

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

## 🗄️ Database Structure

### Users Table
- Authentication and customer information
- Fields: id, name, email, mobile_number, password, role

### Services Table
- Available laundry services
- Fields: id, name, description, price, unit, icon, is_active

### Orders Table
- Order master records
- Fields: id, order_number, user_id, pickup_date, pickup_time, pickup_address, is_rush_service, special_instructions, promo_code, subtotal, rush_fee, discount_amount, total_amount, status, payment_status, payment_method

### Order Items Table
- Line items for each order
- Fields: id, order_id, service_id, quantity, unit_price, total_price

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

## 📁 Project Structure

```
lms-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── AuthController.php
│   │           ├── ServiceController.php
│   │           └── OrderController.php
│   └── Models/
│       ├── User.php
│       ├── Service.php
│       ├── Order.php
│       └── OrderItem.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_services_table.php
│   │   ├── create_orders_table.php
│   │   └── create_order_items_table.php
│   ├── seeders/
│   │   └── ServiceSeeder.php
│   └── database.sqlite
├── routes/
│   └── api.php
└── .env
```

---

## 🔄 Order Status Flow

```
pending (Order placed)
   ↓
in_transit (Pickup in progress)
   ↓
picked_up (Items collected)
   ↓
processing (Being cleaned)
   ↓
ready (Ready for delivery)
   ↓
out_for_delivery (Being delivered)
   ↓
completed (Finished)

Can be cancelled at any point → cancelled
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

## 📝 API Endpoint Summary

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

## 💡 Tips & Best Practices

- **Always use tokens in headers**: `Authorization: Bearer YOUR_TOKEN`
- **Validate dates**: Pickup date must be today or in the future
- **Rush service**: Automatically adds 20% fee to subtotal
- **Order numbers**: Auto-generated in format `ORD-YYYY-XXX`
- **Multiple services**: You can add multiple services to one order
- **Status updates**: Follow the proper order status flow
- **Error handling**: Check response status and error messages

---

## 🆘 Troubleshooting

**Issue: "Unauthenticated" error**
- Solution: Make sure you're including the Bearer token in the Authorization header

**Issue: "The pickup date must be after or equal to today"**
- Solution: Use today's date or a future date in YYYY-MM-DD format

**Issue: Database connection error**
- Solution: Check that `database/database.sqlite` exists and has proper permissions

**Issue: Service not found**
- Solution: Run the seeder: `php artisan db:seed --class=ServiceSeeder`

---

## 📞 Support

For issues and questions:
1. Check the API response error messages
2. Review this documentation
3. Check Laravel logs in `storage/logs/`

---

## 📄 License

This Laravel application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🎉 You're Ready!

Your laundromat API is fully configured and ready to use. Start testing with Postman!

**Server:** `http://localhost:8000`  
**API Base:** `http://localhost:8000/api`


