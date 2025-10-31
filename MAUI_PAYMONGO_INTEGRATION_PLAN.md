# 📱 MAUI App - PayMongo Integration Plan

## ✅ Laravel API - COMPLETED

Your Laravel API now has complete PayMongo integration with the following endpoints ready to use.

---

## 🎯 MAUI App Implementation Plan

### **Phase 1: Setup & Configuration**

#### 1.1 Add NuGet Packages
```bash
# Add these packages to your MAUI project
- System.Net.Http
- Newtonsoft.Json (or System.Text.Json)
```

#### 1.2 Create Configuration
```csharp
// Constants.cs or AppSettings.cs
public static class PaymentConfig
{
    public const string BaseApiUrl = "https://your-ngrok-url.ngrok-free.app/api";
    // Or localhost for testing: "http://127.0.0.1:8000/api"
}
```

---

### **Phase 2: Create Models**

#### 2.1 Payment Models
```csharp
// Models/PaymentIntent.cs
public class PaymentIntent
{
    public string TransactionId { get; set; }
    public string PaymentIntentId { get; set; }
    public string ClientKey { get; set; }
    public decimal Amount { get; set; }
    public string Currency { get; set; }
}

// Models/PaymentSource.cs
public class PaymentSource
{
    public string SourceId { get; set; }
    public string CheckoutUrl { get; set; }
    public string Status { get; set; }
}

// Models/PaymentMethod.cs
public class PaymentMethod
{
    public string Name { get; set; }
    public string Type { get; set; }
    public bool Enabled { get; set; }
    public string Icon { get; set; } // Optional: add icon URLs
}

// Models/PaymentStatus.cs
public class PaymentStatus
{
    public string TransactionId { get; set; }
    public string PaymentStatus { get; set; }
    public decimal Amount { get; set; }
    public DateTime? PaidAt { get; set; }
    public OrderInfo Order { get; set; }
}

public class OrderInfo
{
    public int Id { get; set; }
    public string OrderNumber { get; set; }
    public string Status { get; set; }
}
```

---

### **Phase 3: Create Payment Service**

#### 3.1 PaymentService.cs
```csharp
public class PaymentService
{
    private readonly HttpClient _httpClient;
    private readonly string _baseUrl;
    private string _authToken;

    public PaymentService(HttpClient httpClient)
    {
        _httpClient = httpClient;
        _baseUrl = PaymentConfig.BaseApiUrl;
    }

    public void SetAuthToken(string token)
    {
        _authToken = token;
        _httpClient.DefaultRequestHeaders.Authorization = 
            new AuthenticationHeaderValue("Bearer", token);
    }

    // Step 1: Get available payment methods
    public async Task<List<PaymentMethod>> GetPaymentMethodsAsync()
    {
        var response = await _httpClient.GetAsync($"{_baseUrl}/payments/methods");
        
        if (response.IsSuccessStatusCode)
        {
            var json = await response.Content.ReadAsStringAsync();
            var result = JsonConvert.DeserializeObject<ApiResponse<PaymentMethodsData>>(json);
            return result.Data.PaymentMethods;
        }
        
        throw new Exception("Failed to load payment methods");
    }

    // Step 2: Create payment intent for order
    public async Task<PaymentIntent> CreatePaymentIntentAsync(int orderId)
    {
        var content = new StringContent(
            JsonConvert.SerializeObject(new { order_id = orderId }),
            Encoding.UTF8,
            "application/json"
        );

        var response = await _httpClient.PostAsync(
            $"{_baseUrl}/payments/create-intent",
            content
        );

        if (response.IsSuccessStatusCode)
        {
            var json = await response.Content.ReadAsStringAsync();
            var result = JsonConvert.DeserializeObject<ApiResponse<PaymentIntent>>(json);
            return result.Data;
        }

        throw new Exception("Failed to create payment intent");
    }

    // Step 3: Create payment source (for GCash, GrabPay, etc.)
    public async Task<PaymentSource> CreatePaymentSourceAsync(
        string transactionId,
        string paymentMethod,
        string successUrl = null,
        string failedUrl = null)
    {
        var requestData = new
        {
            transaction_id = transactionId,
            payment_method = paymentMethod,
            success_url = successUrl,
            failed_url = failedUrl
        };

        var content = new StringContent(
            JsonConvert.SerializeObject(requestData),
            Encoding.UTF8,
            "application/json"
        );

        var response = await _httpClient.PostAsync(
            $"{_baseUrl}/payments/create-source",
            content
        );

        if (response.IsSuccessStatusCode)
        {
            var json = await response.Content.ReadAsStringAsync();
            var result = JsonConvert.DeserializeObject<ApiResponse<PaymentSource>>(json);
            return result.Data;
        }

        throw new Exception("Failed to create payment source");
    }

    // Step 4: Check payment status
    public async Task<PaymentStatus> CheckPaymentStatusAsync(string transactionId)
    {
        var response = await _httpClient.GetAsync(
            $"{_baseUrl}/payments/{transactionId}/status"
        );

        if (response.IsSuccessStatusCode)
        {
            var json = await response.Content.ReadAsStringAsync();
            var result = JsonConvert.DeserializeObject<ApiResponse<PaymentStatus>>(json);
            return result.Data;
        }

        throw new Exception("Failed to check payment status");
    }
}

// Helper class for API responses
public class ApiResponse<T>
{
    public string Status { get; set; }
    public string Message { get; set; }
    public T Data { get; set; }
}

public class PaymentMethodsData
{
    public List<PaymentMethod> PaymentMethods { get; set; }
}
```

---

### **Phase 4: Create Payment UI Pages**

#### 4.1 Payment Method Selection Page
```xaml
<!-- Views/PaymentMethodsPage.xaml -->
<ContentPage Title="Select Payment Method">
    <CollectionView ItemsSource="{Binding PaymentMethods}">
        <CollectionView.ItemTemplate>
            <DataTemplate>
                <Frame>
                    <StackLayout>
                        <Label Text="{Binding Name}" FontSize="18" FontAttributes="Bold"/>
                        <Label Text="{Binding Type}" FontSize="12"/>
                    </StackLayout>
                    <Frame.GestureRecognizers>
                        <TapGestureRecognizer Command="{Binding SelectPaymentCommand}" 
                                              CommandParameter="{Binding}"/>
                    </Frame.GestureRecognizers>
                </Frame>
            </DataTemplate>
        </CollectionView.ItemTemplate>
    </CollectionView>
</ContentPage>
```

#### 4.2 Payment Processing Page
```xaml
<!-- Views/PaymentProcessingPage.xaml -->
<ContentPage Title="Processing Payment">
    <StackLayout Padding="20" VerticalOptions="Center">
        <ActivityIndicator IsRunning="True" IsVisible="{Binding IsLoading}"/>
        <Label Text="{Binding StatusMessage}" HorizontalTextAlignment="Center"/>
        <Button Text="Check Payment Status" 
                Command="{Binding CheckStatusCommand}"
                IsVisible="{Binding ShowCheckButton}"/>
    </StackLayout>
</ContentPage>
```

#### 4.3 WebView for Checkout
```xaml
<!-- Views/PaymentWebViewPage.xaml -->
<ContentPage Title="Complete Payment">
    <WebView Source="{Binding CheckoutUrl}" 
             Navigated="OnWebViewNavigated"/>
</ContentPage>
```

---

### **Phase 5: Complete Payment Flow**

#### 5.1 Payment Flow ViewModel
```csharp
public class CheckoutViewModel : BaseViewModel
{
    private readonly PaymentService _paymentService;
    private readonly OrderService _orderService;
    
    public async Task ProcessCheckoutAsync(int orderId)
    {
        try
        {
            // Step 1: Create payment intent
            var paymentIntent = await _paymentService.CreatePaymentIntentAsync(orderId);
            
            // Store transaction ID
            TransactionId = paymentIntent.TransactionId;
            Amount = paymentIntent.Amount;
            
            // Step 2: Show payment method selection
            await NavigateToPaymentMethodSelection();
        }
        catch (Exception ex)
        {
            await ShowErrorAlert("Failed to initialize payment", ex.Message);
        }
    }
    
    public async Task SelectPaymentMethodAsync(PaymentMethod method)
    {
        try
        {
            // Step 3: Create payment source
            var source = await _paymentService.CreatePaymentSourceAsync(
                TransactionId,
                method.Type,
                "myapp://payment/success",
                "myapp://payment/failed"
            );
            
            // Step 4: Open checkout URL in WebView
            await OpenPaymentWebView(source.CheckoutUrl);
        }
        catch (Exception ex)
        {
            await ShowErrorAlert("Payment failed", ex.Message);
        }
    }
    
    public async Task CheckPaymentStatusAsync()
    {
        try
        {
            var status = await _paymentService.CheckPaymentStatusAsync(TransactionId);
            
            if (status.PaymentStatus == "paid")
            {
                // Payment successful!
                await NavigateToOrderConfirmation(status.Order);
            }
            else
            {
                // Still pending or failed
                await ShowAlert("Payment Status", $"Status: {status.PaymentStatus}");
            }
        }
        catch (Exception ex)
        {
            await ShowErrorAlert("Status check failed", ex.Message);
        }
    }
}
```

---

### **Phase 6: Integrate with Existing Basket Flow**

#### 6.1 Update BasketCheckout
```csharp
// When user clicks "Checkout" from basket
public async Task OnCheckoutButtonClicked()
{
    try
    {
        // 1. Create order from basket (existing code)
        var order = await _basketService.CheckoutAsync();
        
        // 2. Navigate to payment
        await Navigation.PushAsync(new PaymentMethodsPage(order.Id));
    }
    catch (Exception ex)
    {
        await DisplayAlert("Error", ex.Message, "OK");
    }
}
```

---

### **Phase 7: Handle Deep Links (Return from Payment)**

#### 7.1 Configure Deep Links
```csharp
// MauiProgram.cs or App.xaml.cs
protected override void OnAppLinkRequestReceived(Uri uri)
{
    base.OnAppLinkRequestReceived(uri);
    
    if (uri.ToString().Contains("payment/success"))
    {
        // Payment completed, check status
        MessagingCenter.Send(this, "PaymentCompleted");
    }
    else if (uri.ToString().Contains("payment/failed"))
    {
        // Payment failed
        MessagingCenter.Send(this, "PaymentFailed");
    }
}
```

---

### **Phase 8: Testing Workflow**

#### 8.1 Test Payment Flow
```
1. User adds items to basket
2. User clicks "Checkout"
3. App creates order via: POST /api/basket/checkout
4. App creates payment intent: POST /api/payments/create-intent
5. App shows payment methods: GET /api/payments/methods
6. User selects GCash (or other method)
7. App creates source: POST /api/payments/create-source
8. App opens checkout URL in WebView
9. User completes payment on PayMongo page
10. PayMongo redirects back to app
11. App checks status: GET /api/payments/{transactionId}/status
12. If paid, show order confirmation
```

#### 8.2 Test Credentials (PayMongo Test Mode)
```
Test Card: 4343 4343 4343 4345
CVC: Any 3 digits
Exp: Any future date

GCash Test:
- Use any mobile number
- OTP: 123456

GrabPay Test:
- Use any email
- Complete flow in test environment
```

---

### **Phase 9: Error Handling**

#### 9.1 Common Scenarios
```csharp
// Handle payment timeout
public async Task HandlePaymentTimeout()
{
    // Allow user to retry or cancel
    var retry = await DisplayAlert(
        "Payment Timeout",
        "Would you like to try again?",
        "Retry",
        "Cancel"
    );
    
    if (retry)
    {
        await CheckPaymentStatusAsync();
    }
}

// Handle network errors
public async Task HandleNetworkError(Exception ex)
{
    await DisplayAlert(
        "Connection Error",
        "Please check your internet connection and try again.",
        "OK"
    );
}
```

---

### **Phase 10: UI/UX Enhancements**

#### 10.1 Payment Method Icons
```csharp
public string GetPaymentMethodIcon(string type)
{
    return type switch
    {
        "gcash" => "gcash_icon.png",
        "grab_pay" => "grabpay_icon.png",
        "paymaya" => "paymaya_icon.png",
        "card" => "card_icon.png",
        _ => "payment_icon.png"
    };
}
```

#### 10.2 Loading States
- Show spinner during payment intent creation
- Display "Processing..." while checking status
- Show success animation when payment completes

---

## 🚀 Quick Start Implementation Order

1. ✅ **Add Models** - Create payment-related models
2. ✅ **Create PaymentService** - HTTP client for API calls
3. ✅ **Payment Methods Page** - UI to select payment method
4. ✅ **WebView Page** - Display PayMongo checkout
5. ✅ **Status Checking** - Poll payment status
6. ✅ **Integrate Basket** - Connect to checkout flow
7. ✅ **Test** - Use PayMongo test mode
8. ✅ **Error Handling** - Handle edge cases
9. ✅ **Polish UI** - Add loading states & animations

---

## 📋 API Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/payments/methods` | Get available payment methods |
| POST | `/api/payments/create-intent` | Create payment intent for order |
| POST | `/api/payments/create-source` | Create payment source (GCash, etc.) |
| GET | `/api/payments/{transactionId}/status` | Check payment status |

---

## 🎯 Key Implementation Notes

1. **Always check payment status** after user returns from WebView
2. **Store transaction_id** to track payment
3. **Handle deep links** for payment return URLs
4. **Use HTTPS** for ngrok or production API
5. **Test with test API keys** before going live
6. **Implement retry logic** for failed payments
7. **Show clear error messages** to users
8. **Add loading indicators** for better UX

---

## ✅ Laravel API Endpoints Ready

Your Laravel API now provides:
- ✅ Payment intent creation
- ✅ Payment source creation (GCash, GrabPay, etc.)
- ✅ Payment status checking
- ✅ Webhook handling (automatic order updates)
- ✅ Multiple payment methods support
- ✅ Transaction logging
- ✅ Order-payment linking

---

## 🔐 Security Notes

1. **Never expose secret keys** in MAUI app
2. **Use Bearer token** for authenticated requests
3. **Validate payment status** server-side (via webhooks)
4. **Don't trust client-side** payment confirmation alone
5. **Use HTTPS** for all API calls

---

## 📞 Support

Laravel API is ready with:
- ✅ Complete PayMongo integration
- ✅ Database migrations run
- ✅ Payment routes configured
- ✅ Webhook handler ready
- ✅ Multiple payment methods supported

Next: Implement MAUI app following this plan! 🚀
