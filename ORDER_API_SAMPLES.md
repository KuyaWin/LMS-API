# Order API Response Samples

This document provides sample JSON responses for all Order-related API endpoints.

---

## 1. Create Order (Checkout)

**Endpoint:** `POST /api/basket/checkout`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "pickup_date": "2025-02-15",
  "pickup_time": "14:30",
  "pickup_address": "123 Main St, Brgy. San Juan, Manila",
  "is_rush_service": true,
  "special_instructions": "Please handle with care. White clothes separate.",
  "promo_code": "PROMO2025"
}
```

**Response (201 Created):**
```json
{
  "status": "success",
  "message": "Order created successfully. Basket has been cleared.",
  "data": {
    "order": {
      "id": 42,
      "order_number": "ORD-20250215-0042",
      "user_id": 1,
      "pickup_date": "Feb 15, 2025",
      "pickup_time": "02:30 PM",
      "pickup_address": "123 Main St, Brgy. San Juan, Manila",
      "is_rush_service": true,
      "special_instructions": "Please handle with care. White clothes separate.",
      "promo_code": "PROMO2025",
      "status": "pending",
      "payment_status": "pending",
      "payment_method": null,
      "services": [
        {
          "id": 101,
          "service_name": "Wash & Fold",
          "quantity": 5,
          "unit": "kg",
          "unit_price": "45.00",
          "total_price": "225.00"
        },
        {
          "id": 102,
          "service_name": "Dry Cleaning - Suit",
          "quantity": 2,
          "unit": "piece",
          "unit_price": "150.00",
          "total_price": "300.00"
        },
        {
          "id": 103,
          "service_name": "Iron Only",
          "quantity": 10,
          "unit": "piece",
          "unit_price": "15.00",
          "total_price": "150.00"
        }
      ],
      "subtotal": "675.00",
      "rush_fee": "135.00",
      "discount_amount": "50.00",
      "total_amount": "760.00",
      "placed_on": "Feb 15, 2025 10:23 AM",
      "paid_at": null
    }
  }
}
```

**Notes:**
- `rush_fee` is calculated as 20% of subtotal when `is_rush_service` is true
- `services` array contains all order items with service details
- `payment_status` starts as "pending" until payment is completed
- `paid_at` is null until payment is confirmed
- Basket is automatically cleared after successful order creation

---

## 2. List All Orders

**Endpoint:** `GET /api/orders`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "orders": [
      {
        "id": 42,
        "order_number": "ORD-20250215-0042",
        "pickup_date": "Feb 15, 2025",
        "pickup_time": "02:30 PM",
        "status": "processing",
        "payment_status": "paid",
        "total_amount": "760.00",
        "is_rush_service": true,
        "services": [
          {
            "name": "Wash & Fold",
            "quantity": "5 kg"
          },
          {
            "name": "Dry Cleaning - Suit",
            "quantity": "2 piece"
          },
          {
            "name": "Iron Only",
            "quantity": "10 piece"
          }
        ],
        "created_at": "Feb 15, 2025"
      },
      {
        "id": 41,
        "order_number": "ORD-20250214-0041",
        "pickup_date": "Feb 14, 2025",
        "pickup_time": "09:00 AM",
        "status": "completed",
        "payment_status": "paid",
        "total_amount": "450.00",
        "is_rush_service": false,
        "services": [
          {
            "name": "Wash & Fold",
            "quantity": "3 kg"
          },
          {
            "name": "Iron Only",
            "quantity": "5 piece"
          }
        ],
        "created_at": "Feb 13, 2025"
      },
      {
        "id": 40,
        "order_number": "ORD-20250210-0040",
        "pickup_date": "Feb 10, 2025",
        "pickup_time": "11:30 AM",
        "status": "cancelled",
        "payment_status": "refunded",
        "total_amount": "320.00",
        "is_rush_service": false,
        "services": [
          {
            "name": "Wash & Fold",
            "quantity": "2 kg"
          }
        ],
        "created_at": "Feb 09, 2025"
      }
    ]
  }
}
```

**Notes:**
- Returns the latest 5 orders for the authenticated user
- Orders are sorted by creation date (newest first)
- `created_at` shows when the order was placed (formatted as `MMM dd, YYYY`)
- `services` array contains service names with quantity and unit combined
- Useful for displaying recent order history in a list view
- To get older orders, use the order details endpoint for specific order IDs

---

## 3. Get Order Details

**Endpoint:** `GET /api/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "order": {
      "id": 42,
      "order_number": "ORD-20250215-0042",
      "customer": {
        "name": "Juan Dela Cruz",
        "email": "juan@example.com",
        "mobile_number": "+639171234567"
      },
      "pickup_date": "Feb 15, 2025",
      "pickup_time": "02:30 PM",
      "pickup_address": "123 Main St, Brgy. San Juan, Manila",
      "is_rush_service": true,
      "special_instructions": "Please handle with care. White clothes separate.",
      "promo_code": "PROMO2025",
      "status": "processing",
      "payment_status": "paid",
      "payment_method": "gcash",
      "services": [
        {
          "id": 101,
          "service_name": "Wash & Fold",
          "quantity": 5,
          "unit": "kg",
          "unit_price": "45.00",
          "total_price": "225.00"
        },
        {
          "id": 102,
          "service_name": "Dry Cleaning - Suit",
          "quantity": 2,
          "unit": "piece",
          "unit_price": "150.00",
          "total_price": "300.00"
        },
        {
          "id": 103,
          "service_name": "Iron Only",
          "quantity": 10,
          "unit": "piece",
          "unit_price": "15.00",
          "total_price": "150.00"
        }
      ],
      "subtotal": "675.00",
      "rush_fee": "135.00",
      "discount_amount": "50.00",
      "total_amount": "760.00",
      "placed_on": "Feb 15, 2025 10:23 AM",
      "paid_at": "Feb 15, 2025 10:27 AM"
    }
  }
}
```

**Response (404 Not Found):**
```json
{
  "status": "error",
  "message": "Order not found"
}
```

**Notes:**
- Returns full order details including customer information
- `customer` object contains user details from the order owner
- `payment_method` shows the payment method used (gcash, paymaya, grab_pay, card, etc.)
- `paid_at` timestamp is included when payment is confirmed
- All financial amounts are in PHP (Philippine Peso)

---

## Order Status Values

Orders can have the following status values:

| Status | Description |
|--------|-------------|
| `pending` | Order placed, waiting for pickup |
| `in_transit` | Laundry is being transported to facility |
| `picked_up` | Laundry has been picked up from customer |
| `processing` | Laundry is being washed/cleaned |
| `ready` | Order is ready for delivery |
| `out_for_delivery` | Order is on the way to customer |
| `completed` | Order delivered and completed |
| `cancelled` | Order was cancelled |

---

## Payment Status Values

Orders can have the following payment status values:

| Payment Status | Description |
|----------------|-------------|
| `pending` | Payment not yet made |
| `processing` | Payment being processed |
| `paid` | Payment confirmed and successful |
| `failed` | Payment attempt failed |
| `refunded` | Payment was refunded |

---

## Integration Notes for MAUI App

### 1. Order Model (C#)

```csharp
public class Order
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("order_number")]
    public string OrderNumber { get; set; }

    [JsonPropertyName("pickup_date")]
    public string PickupDate { get; set; }

    [JsonPropertyName("pickup_time")]
    public string PickupTime { get; set; }

    [JsonPropertyName("pickup_address")]
    public string PickupAddress { get; set; }

    [JsonPropertyName("is_rush_service")]
    public bool IsRushService { get; set; }

    [JsonPropertyName("special_instructions")]
    public string SpecialInstructions { get; set; }

    [JsonPropertyName("promo_code")]
    public string PromoCode { get; set; }

    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("payment_status")]
    public string PaymentStatus { get; set; }

    [JsonPropertyName("payment_method")]
    public string PaymentMethod { get; set; }

    [JsonPropertyName("services")]
    public List<OrderService> Services { get; set; }

    [JsonPropertyName("subtotal")]
    public string Subtotal { get; set; }

    [JsonPropertyName("rush_fee")]
    public string RushFee { get; set; }

    [JsonPropertyName("discount_amount")]
    public string DiscountAmount { get; set; }

    [JsonPropertyName("total_amount")]
    public string TotalAmount { get; set; }

    [JsonPropertyName("placed_on")]
    public string PlacedOn { get; set; }

    [JsonPropertyName("paid_at")]
    public string PaidAt { get; set; }

    [JsonPropertyName("customer")]
    public Customer Customer { get; set; }

    // For list view
    [JsonPropertyName("services_count")]
    public int ServicesCount { get; set; }
}

public class OrderService
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("service_name")]
    public string ServiceName { get; set; }

    [JsonPropertyName("quantity")]
    public int Quantity { get; set; }

    [JsonPropertyName("unit")]
    public string Unit { get; set; }

    [JsonPropertyName("unit_price")]
    public string UnitPrice { get; set; }

    [JsonPropertyName("total_price")]
    public string TotalPrice { get; set; }
}

public class Customer
{
    [JsonPropertyName("name")]
    public string Name { get; set; }

    [JsonPropertyName("email")]
    public string Email { get; set; }

    [JsonPropertyName("mobile_number")]
    public string MobileNumber { get; set; }
}
```

### 2. API Response Wrappers

```csharp
public class OrderResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("message")]
    public string Message { get; set; }

    [JsonPropertyName("data")]
    public OrderData Data { get; set; }
}

public class OrderData
{
    [JsonPropertyName("order")]
    public Order Order { get; set; }
}

public class OrderListResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("data")]
    public OrderListData Data { get; set; }
}

public class OrderListData
{
    [JsonPropertyName("orders")]
    public List<Order> Orders { get; set; }
}
```

### 3. Order Service Methods

```csharp
public class OrderService
{
    private readonly HttpClient _httpClient;
    private const string BaseUrl = "https://kxenk6r4vr.ap.loclx.io/api";

    public OrderService(HttpClient httpClient)
    {
        _httpClient = httpClient;
    }

    public async Task<OrderResponse> CreateOrderAsync(
        string pickupDate,
        string pickupTime,
        string pickupAddress,
        bool isRushService = false,
        string specialInstructions = null,
        string promoCode = null)
    {
        var requestBody = new
        {
            pickup_date = pickupDate,
            pickup_time = pickupTime,
            pickup_address = pickupAddress,
            is_rush_service = isRushService,
            special_instructions = specialInstructions,
            promo_code = promoCode
        };

        var json = JsonSerializer.Serialize(requestBody);
        var content = new StringContent(json, Encoding.UTF8, "application/json");

        var response = await _httpClient.PostAsync($"{BaseUrl}/basket/checkout", content);
        var responseJson = await response.Content.ReadAsStringAsync();

        return JsonSerializer.Deserialize<OrderResponse>(responseJson);
    }

    public async Task<OrderListResponse> GetOrdersAsync()
    {
        var response = await _httpClient.GetAsync($"{BaseUrl}/orders");
        var responseJson = await response.Content.ReadAsStringAsync();

        return JsonSerializer.Deserialize<OrderListResponse>(responseJson);
    }

    public async Task<OrderResponse> GetOrderDetailsAsync(int orderId)
    {
        var response = await _httpClient.GetAsync($"{BaseUrl}/orders/{orderId}");
        var responseJson = await response.Content.ReadAsStringAsync();

        return JsonSerializer.Deserialize<OrderResponse>(responseJson);
    }
}
```

### 4. Usage Example

```csharp
// In your MAUI Page
private async void OnCheckoutClicked(object sender, EventArgs e)
{
    try
    {
        var orderService = new OrderService(_httpClient);
        
        var response = await orderService.CreateOrderAsync(
            pickupDate: "2025-02-15",
            pickupTime: "14:30",
            pickupAddress: AddressEntry.Text,
            isRushService: RushServiceSwitch.IsToggled,
            specialInstructions: InstructionsEditor.Text,
            promoCode: PromoCodeEntry.Text
        );

        if (response.Status == "success")
        {
            var order = response.Data.Order;
            
            // Navigate to payment page
            await Navigation.PushAsync(new CheckoutPage(order.Id, order.TotalAmount));
        }
    }
    catch (Exception ex)
    {
        await DisplayAlert("Error", ex.Message, "OK");
    }
}
```

---

## Important Notes

1. **Authentication Required:** All order endpoints require authentication via Bearer token in the Authorization header.

2. **Date/Time Formats:**
   - Input dates: `YYYY-MM-DD` (e.g., "2025-02-15")
   - Input times: `HH:mm` (24-hour format, e.g., "14:30")
   - Output dates: `MMM dd, YYYY` (e.g., "Feb 15, 2025")
   - Output times: `hh:mm A` (12-hour format, e.g., "02:30 PM")

3. **Monetary Values:** All amounts are returned as strings to preserve decimal precision (e.g., "760.00")

4. **Rush Service:** When enabled, adds 20% fee to the subtotal automatically

5. **Order Workflow:**
   - Create order (POST /basket/checkout)
   - Get order ID from response
   - Proceed to payment with order ID
   - After payment success, order payment_status updates to "paid"

6. **Status Tracking:** Use GET /orders/{id} to poll for order status updates during fulfillment process
