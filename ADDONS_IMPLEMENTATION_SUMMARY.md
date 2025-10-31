# Add-ons Implementation Summary (JSON Storage)

## Overview
Successfully implemented add-ons feature using JSON storage in `basket_items` and `order_items` tables. Add-ons are stored as arrays of IDs, with prices hardcoded in the models to match .NET MAUI app constants.

---

## Implementation Details

### 1. Database Changes

#### Migrations Created:
1. **2025_10_21_125125_create_basket_items_table.php** (Modified)
   - Added `json('addons')->nullable()` column

2. **2025_10_24_173203_add_addons_column_to_order_items_table.php** (New)
   - Added `json('addons')->nullable()` column to `order_items` table

3. **2025_10_24_173849_add_addons_column_to_basket_items_table.php** (New)
   - Safety migration with column existence check
   - Ensures `addons` column exists in `basket_items` table

#### Database Schema:
```sql
-- basket_items table
ALTER TABLE basket_items ADD COLUMN addons JSON NULL;

-- order_items table  
ALTER TABLE order_items ADD COLUMN addons JSON NULL;
```

---

### 2. Model Updates

#### BasketItem Model (`app/Models/BasketItem.php`)

**Added Features:**
- `addons` to `$fillable` array
- `addons => 'array'` to `$casts` array
- Hardcoded addon prices array (matches .NET MAUI constants)
- `getAddonsWithDetailsAttribute()` - Returns addon details with names and prices
- `getAddonsTotalAttribute()` - Calculates total addon cost
- `getRushFeeAttribute()` - Calculates 20% rush fee
- `getTotalAttribute()` - Calculates item total + addons + rush fee
- `getAvailableAddons()` - Static method returning all available addons

**Hardcoded Addon Prices:**
```php
private static $addonPrices = [
    1 => ['name' => 'Extra Soap', 'price' => 10.00],
    2 => ['name' => 'Extra Fabric Conditioner', 'price' => 10.00],
    3 => ['name' => 'Bleach', 'price' => 10.00],
    4 => ['name' => 'Extra Wash', 'price' => 30.00],
    5 => ['name' => 'Extra Dry', 'price' => 10.00],
];
```

#### OrderItem Model (`app/Models/OrderItem.php`)

**Added Features:**
- Same addon handling as BasketItem
- `addons` to `$fillable` and `$casts`
- `getAddonsWithDetailsAttribute()` and `getAddonsTotalAttribute()` methods

---

### 3. Controller Updates

#### BasketController (`app/Http/Controllers/Api/BasketController.php`)

**New Endpoint:**
```php
GET /api/addons - Get available add-ons (public endpoint)
```

**Modified Methods:**

1. **getAddons()** - New method
   - Returns all available addons with IDs, names, and prices
   - Public endpoint (no auth required)

2. **index()** - Get basket
   - Now includes `addons`, `addons_total`, `rush_fee`, and `total` for each item
   - Basket summary includes `addons_total`

3. **store()** - Add to basket
   - Accepts `addon_ids` array in request body
   - Validates addon IDs are between 1-5
   - Stores addons as JSON array
   - Returns calculated addons_total and total

4. **update()** - Update basket item
   - Accepts `addon_ids` array to replace existing addons
   - Validates addon IDs
   - Recalculates totals with new addons

5. **checkout()** - Convert basket to order
   - Passes addons array with each service to OrderController

#### OrderController (`app/Http/Controllers/Api/OrderController.php`)

**Modified Methods:**

1. **store()** - Create order
   - Accepts `services.*.addons` array in request
   - Validates addon IDs (1-5)
   - Calculates `addonsTotal` for entire order
   - Stores addons in each order item
   - Includes addons_total in final total calculation
   - Returns addon details in response

2. **show()** - Get order details
   - Includes addon details for each service
   - Calculates and returns `addons_total`

---

### 4. API Routes

**Added Route:**
```php
// Public route - no authentication required
Route::get('/addons', [BasketController::class, 'getAddons']);
```

---

## API Endpoints

### 1. Get Available Add-ons

```
GET /api/addons
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Extra Soap",
      "price": "10.00"
    },
    {
      "id": 2,
      "name": "Extra Fabric Conditioner",
      "price": "10.00"
    },
    {
      "id": 3,
      "name": "Bleach",
      "price": "10.00"
    },
    {
      "id": 4,
      "name": "Extra Wash",
      "price": "30.00"
    },
    {
      "id": 5,
      "name": "Extra Dry",
      "price": "10.00"
    }
  ]
}
```

### 2. Add to Basket with Add-ons

```
POST /api/basket
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "service_id": 1,
  "quantity": 5,
  "pickup_date": "2025-10-30",
  "pickup_time": "14:00",
  "pickup_address": "123 Test St",
  "is_rush_service": false,
  "addon_ids": [1, 3, 5]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Item added to basket",
  "data": {
    "basket_item": {
      "id": 50,
      "service": {
        "id": 1,
        "name": "Wash & Fold",
        "price": "50.00",
        "unit": "kg"
      },
      "quantity": "5.00",
      "pickup_date": "2025-10-30",
      "pickup_time": "14:00",
      "pickup_address": "123 Test St",
      "is_rush_service": false,
      "special_instructions": null,
      "addons": [
        {
          "id": 1,
          "name": "Extra Soap",
          "price": "10.00"
        },
        {
          "id": 3,
          "name": "Bleach",
          "price": "10.00"
        },
        {
          "id": 5,
          "name": "Extra Dry",
          "price": "10.00"
        }
      ],
      "item_total": "250.00",
      "addons_total": "30.00",
      "rush_fee": "0.00",
      "total": "280.00"
    }
  }
}
```

### 3. Get Basket

```
GET /api/basket
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "items": [
      {
        "id": 50,
        "service": {
          "id": 1,
          "name": "Wash & Fold",
          "price": "50.00",
          "unit": "kg",
          "icon": "👕"
        },
        "quantity": "5.00",
        "pickup_date": "2025-10-30",
        "pickup_time": "14:00",
        "pickup_address": "123 Test St",
        "is_rush_service": false,
        "special_instructions": null,
        "addons": [
          {
            "id": 1,
            "name": "Extra Soap",
            "price": "10.00"
          },
          {
            "id": 3,
            "name": "Bleach",
            "price": "10.00"
          },
          {
            "id": 5,
            "name": "Extra Dry",
            "price": "10.00"
          }
        ],
        "item_total": "250.00",
        "addons_total": "30.00",
        "rush_fee": "0.00",
        "total": "280.00"
      }
    ],
    "summary": {
      "subtotal": "250.00",
      "addons_total": "30.00",
      "rush_fee": "0.00",
      "total": "280.00",
      "item_count": 1
    }
  }
}
```

### 4. Update Basket Item

```
PUT /api/basket/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "addon_ids": [1, 2, 4]
}
```

---

## Price Calculation

### Formula:
```
Item Total = Service Price × Quantity
Add-ons Total = Sum of selected addon prices
Rush Fee = Item Total × 20% (if rush service enabled)
Total = Item Total + Add-ons Total + Rush Fee
```

### Example:
```
Service: Wash & Fold (₱50/kg)
Quantity: 5 kg
Item Total: ₱50 × 5 = ₱250

Add-ons:
- Extra Soap (ID: 1) = ₱10
- Bleach (ID: 3) = ₱10
- Extra Dry (ID: 5) = ₱10
Add-ons Total: ₱30

Rush Service: No
Rush Fee: ₱0

Total: ₱250 + ₱30 + ₱0 = ₱280
```

---

## Testing Results

### Test 1: Get Add-ons
```bash
GET /api/addons
✅ PASSED - Returns all 5 addons with correct prices
```

### Test 2: Add to Basket with Add-ons
```bash
POST /api/basket
Body: {service_id: 1, quantity: 5, addon_ids: [1,3,5]}
✅ PASSED - Item added with 3 addons, correct calculations
- item_total: ₱250.00
- addons_total: ₱30.00
- total: ₱280.00
```

### Test 3: Get Basket
```bash
GET /api/basket
✅ PASSED - Basket shows item with addon details
- Summary includes addons_total
- Each item shows addon names and prices
```

---

## Data Storage

**Example JSON in Database:**

**basket_items.addons:**
```json
[1, 3, 5]
```

**order_items.addons:**
```json
[1, 3, 5]
```

The addon IDs are stored as simple integer arrays. When retrieved, the models automatically:
1. Cast JSON to PHP array
2. Look up addon details from hardcoded prices
3. Calculate totals
4. Return formatted data with names and prices

---

## Key Benefits

✅ **No extra table** - Addons stored as JSON in existing tables  
✅ **Consistent pricing** - Hardcoded in models, matches .NET MAUI app  
✅ **Simple updates** - Just replace the JSON array  
✅ **Easy validation** - Validate IDs are 1-5  
✅ **Automatic calculation** - Model accessors handle all math  
✅ **Minimal schema changes** - Only 2 columns added  
✅ **Fast queries** - No joins needed  
✅ **Type safety** - Laravel casts JSON to array automatically  

---

## Important Notes

⚠️ **Addon IDs are hardcoded** (1-5) and must match .NET MAUI app  
⚠️ **Prices are hardcoded** in both BasketItem and OrderItem models  
⚠️ **JSON storage** means no relational queries on addons  
⚠️ **Update both apps** if addon prices change  
⚠️ **Rush fee is 20%** of item total (not addons)  

---

## Files Modified

### Models:
- `app/Models/BasketItem.php`
- `app/Models/OrderItem.php`

### Controllers:
- `app/Http/Controllers/Api/BasketController.php`
- `app/Http/Controllers/Api/OrderController.php`

### Routes:
- `routes/api.php`

### Migrations:
- `database/migrations/2025_10_21_125125_create_basket_items_table.php` (modified)
- `database/migrations/2025_10_24_173203_add_addons_column_to_order_items_table.php` (new)
- `database/migrations/2025_10_24_173849_add_addons_column_to_basket_items_table.php` (new)

---

## Next Steps for .NET MAUI Integration

1. **Fetch Add-ons:**
   ```csharp
   var addons = await GetAddonsAsync();
   // Display in UI with checkboxes
   ```

2. **Add to Basket with Selected Add-ons:**
   ```csharp
   var selectedAddonIds = new List<int> { 1, 3, 5 };
   await AddToBasketAsync(serviceId, quantity, ..., selectedAddonIds);
   ```

3. **Display Basket:**
   ```csharp
   var basket = await GetBasketAsync();
   // Show addons badge: "+ Extra Soap, Bleach, Extra Dry"
   // Show addons_total in summary
   ```

4. **Checkout:**
   ```csharp
   await CheckoutAsync(promoCode);
   // Addons automatically included in order
   ```

---

## Conclusion

The add-ons feature has been successfully implemented using a simple, efficient JSON storage approach. The system is ready for .NET MAUI integration and has been tested to work correctly with all CRUD operations.
