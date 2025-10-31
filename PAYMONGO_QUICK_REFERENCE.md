# 🚀 PayMongo Quick Reference Card

## 📱 MAUI App - API Endpoints

### Base URL
```
http://127.0.0.1:8000/api          (Local testing)
https://your-domain.com/api         (Production)
```

### Headers (All Protected Routes)
```
Authorization: Bearer {your_auth_token}
Content-Type: application/json
Accept: application/json
```

---

## 🎯 Payment API Calls (In Order)

### 1️⃣ Get Available Payment Methods
```http
GET /api/payments/methods
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "payment_methods": [
            {
                "name": "GCash",
                "type": "gcash",
                "enabled": true
            },
            {
                "name": "GrabPay",
                "type": "grab_pay",
                "enabled": true
            }
        ]
    }
}
```

---

### 2️⃣ Create Payment Intent
```http
POST /api/payments/create-intent
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": 1
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Payment intent created",
    "data": {
        "transaction_id": "TXN-20251023-ABC12345",
        "payment_intent_id": "pi_xxxxxxxxxxxxx",
        "client_key": "pi_xxxxxxxxxxxxx_client_xxxxxxxxxxxxx",
        "amount": "570.00",
        "currency": "PHP"
    }
}
```

**💾 Save:** `transaction_id` and `client_key`

---

### 3️⃣ Create Payment Source (Open Checkout)
```http
POST /api/payments/create-source
Authorization: Bearer {token}
Content-Type: application/json

{
    "transaction_id": "TXN-20251023-ABC12345",
    "payment_method": "gcash",
    "success_url": "myapp://payment/success",
    "failed_url": "myapp://payment/failed"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Payment source created",
    "data": {
        "source_id": "src_xxxxxxxxxxxxx",
        "checkout_url": "https://pay.paymongo.com/xxxxx",
        "status": "pending"
    }
}
```

**🌐 Action:** Open `checkout_url` in WebView

---

### 4️⃣ Check Payment Status
```http
GET /api/payments/{transaction_id}/status
Authorization: Bearer {token}
```

Example:
```http
GET /api/payments/TXN-20251023-ABC12345/status
```

**Response (Paid):**
```json
{
    "status": "success",
    "data": {
        "transaction_id": "TXN-20251023-ABC12345",
        "payment_status": "paid",
        "amount": "570.00",
        "paid_at": "2025-10-23 14:30:00",
        "order": {
            "id": 1,
            "order_number": "ORD-2025-001",
            "status": "pending"
        }
    }
}
```

**Response (Pending):**
```json
{
    "status": "success",
    "data": {
        "transaction_id": "TXN-20251023-ABC12345",
        "payment_status": "processing",
        "amount": "570.00"
    }
}
```

---

## 💳 Payment Methods

| Type | MAUI Value | User Sees |
|------|------------|-----------|
| GCash | `"gcash"` | GCash E-Wallet |
| GrabPay | `"grab_pay"` | GrabPay E-Wallet |
| PayMaya | `"paymaya"` | PayMaya |
| Card | `"card"` | Credit/Debit Card |
| BillEase | `"billease"` | BillEase (BNPL) |

---

## 🔄 MAUI App Payment Flow

```csharp
// 1. Create order from basket
var order = await BasketService.CheckoutAsync();

// 2. Create payment intent
var paymentIntent = await PaymentService.CreatePaymentIntentAsync(order.Id);
string transactionId = paymentIntent.TransactionId;

// 3. Show payment method selection
var selectedMethod = await ShowPaymentMethodPicker();

// 4. Create payment source
var source = await PaymentService.CreatePaymentSourceAsync(
    transactionId,
    selectedMethod.Type,
    "myapp://payment/success",
    "myapp://payment/failed"
);

// 5. Open checkout URL
await OpenWebView(source.CheckoutUrl);

// 6. User completes payment (WebView)
// PayMongo redirects back to: myapp://payment/success

// 7. Check payment status
var status = await PaymentService.CheckPaymentStatusAsync(transactionId);

if (status.PaymentStatus == "paid")
{
    // ✅ Payment successful!
    await NavigateToOrderConfirmation(status.Order);
}
else
{
    // ⏳ Still processing or ❌ failed
    await ShowStatusMessage(status.PaymentStatus);
}
```

---

## 🧪 Test Credentials

### PayMongo Test Mode

**GCash:**
```
Mobile: Any number
OTP: 123456
```

**Card (Success):**
```
Number: 4343 4343 4343 4345
Expiry: 12/25
CVC: 123
```

**Card (Failure):**
```
Number: 4571 7360 0000 0183
Expiry: 12/25
CVC: 123
```

---

## ⚡ Quick Tips

### ✅ DO
- Store `transaction_id` immediately
- Check payment status after redirect
- Handle "processing" status (user may need to wait)
- Show loading indicators
- Implement retry for failed payments

### ❌ DON'T
- Trust client-side payment status alone
- Expose secret keys in app
- Skip status checking after redirect
- Assume instant payment confirmation

---

## 🐛 Common Issues

### Issue: "Transaction not found"
**Solution:** Ensure transaction_id is correct and belongs to authenticated user

### Issue: Checkout URL not loading
**Solution:** Check internet connection, verify payment source created successfully

### Issue: Payment stuck in "processing"
**Solution:** This is normal for some methods, check status after 1-2 minutes

### Issue: Webhook not received
**Solution:** Verify webhook URL in PayMongo dashboard, check Laravel logs

---

## 📊 Payment Status Values

| Status | Meaning | Action |
|--------|---------|--------|
| `pending` | Payment initiated | Wait or check again |
| `processing` | Payment in progress | Check status periodically |
| `paid` | ✅ Payment successful | Proceed with order |
| `failed` | ❌ Payment failed | Show error, allow retry |
| `cancelled` | User cancelled | Allow retry or new order |

---

## 🔧 Laravel Configuration

### Required in .env
```env
PAYMONGO_SECRET_KEY=sk_test_xxxxx
PAYMONGO_PUBLIC_KEY=pk_test_xxxxx
PAYMONGO_WEBHOOK_SECRET=whsec_xxxxx
```

### Get Keys From
https://dashboard.paymongo.com → Developers

---

## 📞 Support

**Laravel API:** ✅ Ready
**Endpoints:** ✅ Tested
**Database:** ✅ Migrated
**Webhooks:** ✅ Configured

**MAUI Implementation:** See `MAUI_PAYMONGO_INTEGRATION_PLAN.md`

---

## ⚡ Quick Copy-Paste

### C# HTTP Client Setup
```csharp
httpClient.DefaultRequestHeaders.Authorization = 
    new AuthenticationHeaderValue("Bearer", authToken);
httpClient.DefaultRequestHeaders.Accept.Add(
    new MediaTypeWithQualityHeaderValue("application/json"));
```

### Create Payment Intent Call
```csharp
var content = new StringContent(
    JsonConvert.SerializeObject(new { order_id = orderId }),
    Encoding.UTF8,
    "application/json"
);
var response = await httpClient.PostAsync(
    $"{baseUrl}/payments/create-intent",
    content
);
```

---

🎉 **Everything is ready! Start building your MAUI payment integration!**
