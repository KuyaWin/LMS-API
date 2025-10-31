# PayMongo Payment Integration Guide for .NET MAUI

Complete guide for integrating PayMongo payment gateway (GCash, GrabPay, PayMaya, Cards) into your .NET MAUI laundry app.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Project Setup](#project-setup)
3. [Create Payment Models](#create-payment-models)
4. [Create Payment Service](#create-payment-service)
5. [Deep Link Configuration](#deep-link-configuration)
6. [Payment Flow Implementation](#payment-flow-implementation)
7. [UI Implementation](#ui-implementation)
8. [Testing Guide](#testing-guide)
9. [Troubleshooting](#troubleshooting)

---

## 📦 Prerequisites

### Required NuGet Packages

```xml
<!-- Add to your .csproj file -->
<ItemGroup>
    <PackageReference Include="Microsoft.Maui.Essentials" Version="8.0.0" />
    <PackageReference Include="System.Text.Json" Version="8.0.0" />
</ItemGroup>
```

### API Endpoints Available

```
Base URL: https://your-api.com/api

Authentication:
- POST /auth/customer/login
- POST /auth/customer/register

Orders:
- POST /basket/checkout → Creates order

Payments:
- GET  /payments/methods → List payment methods
- POST /payments/create-intent → Create payment intent
- POST /payments/create-source → Create payment source (for GCash/GrabPay)
- POST /payments/{transactionId}/process → Process payment after redirect
- GET  /payments/{transactionId}/status → Check payment status
```

---

## 🔧 Project Setup

### 1. Register Deep Link Scheme

#### **For Android** (`Platforms/Android/AndroidManifest.xml`):

```xml
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <application android:allowBackup="true" android:icon="@mipmap/appicon" android:roundIcon="@mipmap/appicon_round" android:supportsRtl="true">
        <activity android:name=".MainActivity" android:exported="true">
            <intent-filter>
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="laundryapp" android:host="payment" />
            </intent-filter>
        </activity>
    </application>
    
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
</manifest>
```

#### **For iOS** (`Platforms/iOS/Info.plist`):

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleURLName</key>
        <string>com.yourcompany.laundryapp</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>laundryapp</string>
        </array>
    </dict>
</array>

<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <true/>
</dict>
```

---

## 📐 Create Payment Models

Create a new folder `Models` and add these classes:

### **PaymentModels.cs**

```csharp
using System.Text.Json.Serialization;

namespace YourApp.Models;

// Payment Methods Response
public class PaymentMethodsResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("data")]
    public PaymentMethodsData Data { get; set; }
}

public class PaymentMethodsData
{
    [JsonPropertyName("payment_methods")]
    public List<PaymentMethod> PaymentMethods { get; set; }
}

public class PaymentMethod
{
    [JsonPropertyName("name")]
    public string Name { get; set; }

    [JsonPropertyName("type")]
    public string Type { get; set; }

    [JsonPropertyName("enabled")]
    public bool Enabled { get; set; }

    // For UI display
    public string Icon => Type switch
    {
        "gcash" => "💰",
        "grab_pay" => "🚗",
        "paymaya" => "💳",
        "card" => "💳",
        "billease" => "📱",
        _ => "💵"
    };
}

// Payment Intent Response
public class PaymentIntentResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("message")]
    public string Message { get; set; }

    [JsonPropertyName("data")]
    public PaymentIntentData Data { get; set; }
}

public class PaymentIntentData
{
    [JsonPropertyName("transaction_id")]
    public string TransactionId { get; set; }

    [JsonPropertyName("payment_intent_id")]
    public string PaymentIntentId { get; set; }

    [JsonPropertyName("client_key")]
    public string ClientKey { get; set; }

    [JsonPropertyName("amount")]
    public decimal Amount { get; set; }

    [JsonPropertyName("currency")]
    public string Currency { get; set; }
}

// Payment Source Response
public class PaymentSourceResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("message")]
    public string Message { get; set; }

    [JsonPropertyName("data")]
    public PaymentSourceData Data { get; set; }
}

public class PaymentSourceData
{
    [JsonPropertyName("source_id")]
    public string SourceId { get; set; }

    [JsonPropertyName("checkout_url")]
    public string CheckoutUrl { get; set; }

    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("transaction_id")]
    public string TransactionId { get; set; }
}

// Payment Status Response
public class PaymentStatusResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("message")]
    public string Message { get; set; }

    [JsonPropertyName("data")]
    public PaymentStatusData Data { get; set; }
}

public class PaymentStatusData
{
    [JsonPropertyName("transaction_id")]
    public string TransactionId { get; set; }

    [JsonPropertyName("payment_status")]
    public string PaymentStatus { get; set; }

    [JsonPropertyName("amount")]
    public decimal Amount { get; set; }

    [JsonPropertyName("payment_id")]
    public string PaymentId { get; set; }

    [JsonPropertyName("paid_at")]
    public string PaidAt { get; set; }
}

// Process Payment Response
public class ProcessPaymentResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; }

    [JsonPropertyName("message")]
    public string Message { get; set; }

    [JsonPropertyName("data")]
    public ProcessPaymentData Data { get; set; }
}

public class ProcessPaymentData
{
    [JsonPropertyName("transaction_id")]
    public string TransactionId { get; set; }

    [JsonPropertyName("payment_id")]
    public string PaymentId { get; set; }

    [JsonPropertyName("payment_status")]
    public string PaymentStatus { get; set; }

    [JsonPropertyName("amount")]
    public decimal Amount { get; set; }

    [JsonPropertyName("source_status")]
    public string SourceStatus { get; set; }
}
```

---

## 🔌 Create Payment Service

Create `Services/PaymentService.cs`:

```csharp
using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Text;
using System.Text.Json;
using YourApp.Models;

namespace YourApp.Services;

public class PaymentService
{
    private readonly HttpClient _httpClient;
    private readonly string _baseUrl;
    private string _authToken;

    public PaymentService()
    {
        _httpClient = new HttpClient();
        _baseUrl = "https://your-api.com/api"; // Replace with your API URL
    }

    public void SetAuthToken(string token)
    {
        _authToken = token;
        _httpClient.DefaultRequestHeaders.Authorization = 
            new AuthenticationHeaderValue("Bearer", token);
    }

    #region Payment Methods

    /// <summary>
    /// Get list of available payment methods
    /// </summary>
    public async Task<PaymentMethodsResponse> GetPaymentMethodsAsync()
    {
        try
        {
            var response = await _httpClient.GetAsync($"{_baseUrl}/payments/methods");
            response.EnsureSuccessStatusCode();

            var content = await response.Content.ReadAsStringAsync();
            return JsonSerializer.Deserialize<PaymentMethodsResponse>(content);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error getting payment methods: {ex.Message}");
            throw;
        }
    }

    /// <summary>
    /// Step 1: Create payment intent
    /// </summary>
    public async Task<PaymentIntentResponse> CreatePaymentIntentAsync(int orderId)
    {
        try
        {
            var payload = new { order_id = orderId };
            var json = JsonSerializer.Serialize(payload);
            var content = new StringContent(json, Encoding.UTF8, "application/json");

            var response = await _httpClient.PostAsync(
                $"{_baseUrl}/payments/create-intent", 
                content
            );

            var responseContent = await response.Content.ReadAsStringAsync();
            
            if (!response.IsSuccessStatusCode)
            {
                Console.WriteLine($"Error: {responseContent}");
                throw new Exception($"Failed to create payment intent: {response.StatusCode}");
            }

            return JsonSerializer.Deserialize<PaymentIntentResponse>(responseContent);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error creating payment intent: {ex.Message}");
            throw;
        }
    }

    /// <summary>
    /// Step 2: Create payment source (for GCash, GrabPay, PayMaya)
    /// </summary>
    public async Task<PaymentSourceResponse> CreatePaymentSourceAsync(
        string transactionId, 
        string paymentMethod)
    {
        try
        {
            var payload = new
            {
                transaction_id = transactionId,
                payment_method = paymentMethod
            };

            var json = JsonSerializer.Serialize(payload);
            var content = new StringContent(json, Encoding.UTF8, "application/json");

            Console.WriteLine($"Creating payment source: {paymentMethod}");
            Console.WriteLine($"Transaction ID: {transactionId}");

            var response = await _httpClient.PostAsync(
                $"{_baseUrl}/payments/create-source",
                content
            );

            var responseContent = await response.Content.ReadAsStringAsync();
            Console.WriteLine($"Response: {responseContent}");

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception($"Failed to create payment source: {response.StatusCode}");
            }

            return JsonSerializer.Deserialize<PaymentSourceResponse>(responseContent);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error creating payment source: {ex.Message}");
            throw;
        }
    }

    /// <summary>
    /// Step 3: Process payment after user returns from payment gateway
    /// This charges the source and updates the order
    /// </summary>
    public async Task<ProcessPaymentResponse> ProcessPaymentAsync(string transactionId)
    {
        try
        {
            Console.WriteLine($"Processing payment for transaction: {transactionId}");

            var response = await _httpClient.PostAsync(
                $"{_baseUrl}/payments/{transactionId}/process",
                null
            );

            var responseContent = await response.Content.ReadAsStringAsync();
            Console.WriteLine($"Process response: {responseContent}");

            if (!response.IsSuccessStatusCode)
            {
                throw new Exception($"Failed to process payment: {response.StatusCode}");
            }

            return JsonSerializer.Deserialize<ProcessPaymentResponse>(responseContent);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error processing payment: {ex.Message}");
            throw;
        }
    }

    /// <summary>
    /// Check payment status
    /// </summary>
    public async Task<PaymentStatusResponse> CheckPaymentStatusAsync(string transactionId)
    {
        try
        {
            var response = await _httpClient.GetAsync(
                $"{_baseUrl}/payments/{transactionId}/status"
            );

            var content = await response.Content.ReadAsStringAsync();
            
            if (!response.IsSuccessStatusCode)
            {
                throw new Exception($"Failed to check payment status: {response.StatusCode}");
            }

            return JsonSerializer.Deserialize<PaymentStatusResponse>(content);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error checking payment status: {ex.Message}");
            throw;
        }
    }

    #endregion
}
```

---

## 🔗 Deep Link Configuration

### **App.xaml.cs**

Update your App.xaml.cs to handle deep links:

```csharp
public partial class App : Application
{
    public static string CurrentTransactionId { get; set; }

    public App()
    {
        InitializeComponent();
        MainPage = new AppShell();
    }

    protected override void OnStart()
    {
        // Handle app activation from deep link
        Microsoft.Maui.ApplicationModel.AppActions.OnAppAction += HandleDeepLink;
    }

    private void HandleDeepLink(object sender, Microsoft.Maui.ApplicationModel.AppActionEventArgs e)
    {
        // This will be called when app receives deep link
        if (e.AppAction.Id.StartsWith("payment"))
        {
            MainThread.BeginInvokeOnMainThread(() =>
            {
                // Navigate to payment result page
                Shell.Current.GoToAsync($"//PaymentResultPage");
            });
        }
    }
}
```

### **MainActivity.cs** (Android)

```csharp
using Android.App;
using Android.Content;
using Android.Content.PM;
using Android.OS;

namespace YourApp;

[Activity(Theme = "@style/Maui.SplashTheme", 
    MainLauncher = true, 
    ConfigurationChanges = ConfigChanges.ScreenSize | ConfigChanges.Orientation | ConfigChanges.UiMode,
    LaunchMode = LaunchMode.SingleTop)]
[IntentFilter(new[] { Intent.ActionView },
    Categories = new[] { Intent.CategoryDefault, Intent.CategoryBrowsable },
    DataScheme = "laundryapp",
    DataHost = "payment")]
public class MainActivity : MauiAppCompatActivity
{
    protected override void OnCreate(Bundle savedInstanceState)
    {
        base.OnCreate(savedInstanceState);
        HandleIntent(Intent);
    }

    protected override void OnNewIntent(Intent intent)
    {
        base.OnNewIntent(intent);
        HandleIntent(intent);
    }

    private void HandleIntent(Intent intent)
    {
        if (intent?.Data != null)
        {
            var uri = intent.Data.ToString();
            Console.WriteLine($"Deep link received: {uri}");

            // Parse: laundryapp://payment/success?transaction_id=TXN-xxx
            if (uri.Contains("transaction_id="))
            {
                var transactionId = uri.Split("transaction_id=")[1].Split('&')[0];
                App.CurrentTransactionId = transactionId;

                // Navigate to payment result page
                MainThread.BeginInvokeOnMainThread(() =>
                {
                    Shell.Current.GoToAsync($"//PaymentResultPage?transactionId={transactionId}");
                });
            }
        }
    }
}
```

---

## 🔄 Payment Flow Implementation

### **CheckoutPage.xaml.cs**

Complete payment flow with method selection:

```csharp
using YourApp.Models;
using YourApp.Services;

namespace YourApp.Pages;

public partial class CheckoutPage : ContentPage
{
    private readonly PaymentService _paymentService;
    private int _orderId;
    private string _currentTransactionId;
    private List<PaymentMethod> _paymentMethods;

    public CheckoutPage(int orderId)
    {
        InitializeComponent();
        _paymentService = new PaymentService();
        _paymentService.SetAuthToken(Preferences.Get("auth_token", ""));
        _orderId = orderId;

        LoadPaymentMethods();
    }

    private async void LoadPaymentMethods()
    {
        try
        {
            LoadingIndicator.IsRunning = true;
            LoadingIndicator.IsVisible = true;

            var response = await _paymentService.GetPaymentMethodsAsync();
            
            if (response.Status == "success")
            {
                _paymentMethods = response.Data.PaymentMethods;
                PaymentMethodsCollectionView.ItemsSource = _paymentMethods;
            }
        }
        catch (Exception ex)
        {
            await DisplayAlert("Error", $"Failed to load payment methods: {ex.Message}", "OK");
        }
        finally
        {
            LoadingIndicator.IsRunning = false;
            LoadingIndicator.IsVisible = false;
        }
    }

    private async void OnPaymentMethodSelected(object sender, SelectionChangedEventArgs e)
    {
        if (e.CurrentSelection.FirstOrDefault() is PaymentMethod selectedMethod)
        {
            Console.WriteLine($"Payment method selected: {selectedMethod.Type}");
            
            // Deselect immediately for better UX
            ((CollectionView)sender).SelectedItem = null;

            await ProcessPayment(selectedMethod.Type);
        }
    }

    private async Task ProcessPayment(string paymentMethod)
    {
        try
        {
            LoadingIndicator.IsRunning = true;
            LoadingIndicator.IsVisible = true;
            StatusLabel.Text = "Creating payment...";

            // Step 1: Create Payment Intent
            Console.WriteLine($"Step 1: Creating payment intent for order {_orderId}");
            var intentResponse = await _paymentService.CreatePaymentIntentAsync(_orderId);

            if (intentResponse.Status != "success")
            {
                await DisplayAlert("Error", intentResponse.Message ?? "Failed to create payment", "OK");
                return;
            }

            _currentTransactionId = intentResponse.Data.TransactionId;
            App.CurrentTransactionId = _currentTransactionId;
            
            Console.WriteLine($"Payment intent created: {_currentTransactionId}");
            StatusLabel.Text = $"Processing {paymentMethod}...";

            // Step 2: Create Payment Source
            Console.WriteLine($"Step 2: Creating payment source for {paymentMethod}");
            var sourceResponse = await _paymentService.CreatePaymentSourceAsync(
                _currentTransactionId, 
                paymentMethod
            );

            if (sourceResponse.Status != "success")
            {
                await DisplayAlert("Error", sourceResponse.Message ?? "Failed to create payment source", "OK");
                return;
            }

            var checkoutUrl = sourceResponse.Data.CheckoutUrl;
            Console.WriteLine($"Checkout URL: {checkoutUrl}");

            // Step 3: Open payment gateway in browser
            StatusLabel.Text = "Opening payment gateway...";
            await Task.Delay(500); // Brief delay for user feedback

            var browserOptions = new BrowserLaunchOptions
            {
                LaunchMode = BrowserLaunchMode.SystemPreferred,
                TitleMode = BrowserTitleMode.Show,
                PreferredToolbarColor = Colors.Blue,
                PreferredControlColor = Colors.White
            };

            await Browser.OpenAsync(checkoutUrl, browserOptions);

            // Note: User will be redirected back via deep link after payment
            // The deep link will be handled in MainActivity.cs
            StatusLabel.Text = "Waiting for payment completion...";
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Payment error: {ex.Message}");
            await DisplayAlert("Error", $"Payment failed: {ex.Message}", "OK");
        }
        finally
        {
            LoadingIndicator.IsRunning = false;
            LoadingIndicator.IsVisible = false;
        }
    }
}
```

### **PaymentResultPage.xaml.cs**

Handle payment result after deep link redirect:

```csharp
using YourApp.Services;

namespace YourApp.Pages;

[QueryProperty(nameof(TransactionId), "transactionId")]
public partial class PaymentResultPage : ContentPage
{
    private readonly PaymentService _paymentService;
    private string _transactionId;

    public string TransactionId
    {
        get => _transactionId;
        set
        {
            _transactionId = value;
            ProcessPaymentResult();
        }
    }

    public PaymentResultPage()
    {
        InitializeComponent();
        _paymentService = new PaymentService();
        _paymentService.SetAuthToken(Preferences.Get("auth_token", ""));
    }

    protected override void OnAppearing()
    {
        base.OnAppearing();
        
        // Check if we have a transaction ID from App.xaml.cs
        if (string.IsNullOrEmpty(_transactionId) && !string.IsNullOrEmpty(App.CurrentTransactionId))
        {
            _transactionId = App.CurrentTransactionId;
            ProcessPaymentResult();
        }
    }

    private async void ProcessPaymentResult()
    {
        if (string.IsNullOrEmpty(_transactionId))
        {
            await DisplayAlert("Error", "No transaction ID found", "OK");
            await Shell.Current.GoToAsync("//OrdersPage");
            return;
        }

        try
        {
            LoadingIndicator.IsRunning = true;
            LoadingIndicator.IsVisible = true;
            StatusLabel.Text = "Processing your payment...";

            Console.WriteLine($"Processing payment result for: {_transactionId}");

            // Step 3: Process the payment (charges the source)
            var processResponse = await _paymentService.ProcessPaymentAsync(_transactionId);

            Console.WriteLine($"Process response: {processResponse.Status}");

            if (processResponse.Status == "success" || processResponse.Status == "pending")
            {
                var paymentStatus = processResponse.Data.PaymentStatus.ToLower();

                if (paymentStatus == "paid" || paymentStatus == "succeeded")
                {
                    // Payment successful
                    StatusIcon.Text = "✅";
                    StatusLabel.Text = "Payment Successful!";
                    MessageLabel.Text = $"Your payment of ₱{processResponse.Data.Amount:N2} has been processed successfully.";
                    StatusIcon.TextColor = Colors.Green;

                    await Task.Delay(2000);
                    await Shell.Current.GoToAsync("//OrdersPage");
                }
                else if (paymentStatus == "pending" || paymentStatus == "processing")
                {
                    // Payment pending
                    StatusIcon.Text = "⏳";
                    StatusLabel.Text = "Payment Processing";
                    MessageLabel.Text = "Your payment is being processed. Please check back in a few moments.";
                    StatusIcon.TextColor = Colors.Orange;
                }
                else
                {
                    // Payment failed
                    ShowPaymentFailed("Payment was not completed");
                }
            }
            else
            {
                ShowPaymentFailed(processResponse.Message ?? "Payment processing failed");
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error processing payment result: {ex.Message}");
            ShowPaymentFailed($"An error occurred: {ex.Message}");
        }
        finally
        {
            LoadingIndicator.IsRunning = false;
            LoadingIndicator.IsVisible = false;
            App.CurrentTransactionId = null; // Clear the transaction ID
        }
    }

    private void ShowPaymentFailed(string message)
    {
        StatusIcon.Text = "❌";
        StatusLabel.Text = "Payment Failed";
        MessageLabel.Text = message;
        StatusIcon.TextColor = Colors.Red;
        RetryButton.IsVisible = true;
    }

    private async void OnRetryClicked(object sender, EventArgs e)
    {
        await Shell.Current.GoToAsync("//CheckoutPage");
    }

    private async void OnBackToOrdersClicked(object sender, EventArgs e)
    {
        await Shell.Current.GoToAsync("//OrdersPage");
    }
}
```

---

## 🎨 UI Implementation

### **CheckoutPage.xaml**

```xml
<?xml version="1.0" encoding="utf-8" ?>
<ContentPage xmlns="http://schemas.microsoft.com/dotnet/2021/maui"
             xmlns:x="http://schemas.microsoft.com/winfx/2009/xaml"
             x:Class="YourApp.Pages.CheckoutPage"
             Title="Payment">
    
    <ScrollView>
        <VerticalStackLayout Padding="20" Spacing="20">
            
            <!-- Loading Indicator -->
            <ActivityIndicator x:Name="LoadingIndicator"
                             IsRunning="False"
                             IsVisible="False"
                             Color="{StaticResource Primary}"
                             VerticalOptions="Center" />
            
            <Label x:Name="StatusLabel"
                   Text="Select Payment Method"
                   FontSize="18"
                   FontAttributes="Bold"
                   HorizontalOptions="Center" />
            
            <!-- Payment Methods -->
            <Label Text="Choose how to pay:"
                   FontSize="16"
                   Margin="0,10,0,5" />
            
            <CollectionView x:Name="PaymentMethodsCollectionView"
                          SelectionMode="Single"
                          SelectionChanged="OnPaymentMethodSelected">
                <CollectionView.ItemTemplate>
                    <DataTemplate>
                        <Frame Padding="15"
                               Margin="0,5"
                               BorderColor="{StaticResource Primary}"
                               CornerRadius="10"
                               HasShadow="True">
                            <HorizontalStackLayout Spacing="15">
                                <Label Text="{Binding Icon}"
                                       FontSize="32"
                                       VerticalOptions="Center" />
                                <Label Text="{Binding Name}"
                                       FontSize="18"
                                       VerticalOptions="Center" />
                            </HorizontalStackLayout>
                        </Frame>
                    </DataTemplate>
                </CollectionView.ItemTemplate>
            </CollectionView>
            
        </VerticalStackLayout>
    </ScrollView>
    
</ContentPage>
```

### **PaymentResultPage.xaml**

```xml
<?xml version="1.0" encoding="utf-8" ?>
<ContentPage xmlns="http://schemas.microsoft.com/dotnet/2021/maui"
             xmlns:x="http://schemas.microsoft.com/winfx/2009/xaml"
             x:Class="YourApp.Pages.PaymentResultPage"
             Title="Payment Result">
    
    <VerticalStackLayout Padding="20" 
                        Spacing="20"
                        VerticalOptions="Center">
        
        <!-- Loading Indicator -->
        <ActivityIndicator x:Name="LoadingIndicator"
                         IsRunning="True"
                         IsVisible="True"
                         Color="{StaticResource Primary}"
                         WidthRequest="50"
                         HeightRequest="50" />
        
        <!-- Status Icon -->
        <Label x:Name="StatusIcon"
               Text="⏳"
               FontSize="72"
               HorizontalOptions="Center" />
        
        <!-- Status Label -->
        <Label x:Name="StatusLabel"
               Text="Processing payment..."
               FontSize="24"
               FontAttributes="Bold"
               HorizontalOptions="Center"
               HorizontalTextAlignment="Center" />
        
        <!-- Message -->
        <Label x:Name="MessageLabel"
               Text="Please wait while we verify your payment..."
               FontSize="16"
               HorizontalOptions="Center"
               HorizontalTextAlignment="Center"
               Margin="20,0" />
        
        <!-- Retry Button (Hidden by default) -->
        <Button x:Name="RetryButton"
                Text="Try Again"
                IsVisible="False"
                Clicked="OnRetryClicked"
                Margin="0,20,0,0"
                BackgroundColor="{StaticResource Primary}" />
        
        <!-- Back to Orders -->
        <Button Text="Back to Orders"
                Clicked="OnBackToOrdersClicked"
                Margin="0,10,0,0"
                BackgroundColor="{StaticResource Secondary}" />
        
    </VerticalStackLayout>
    
</ContentPage>
```

---

## 🧪 Testing Guide

### **Test Payment Credentials**

#### **GCash (Test Mode):**
```
Mobile Number: Any 11-digit number (e.g., 09123456789)
OTP: 123456
```

#### **Credit Card (Test Mode):**
```
Success Card:
Number: 4343434343434345
Expiry: 12/25
CVC: 123

Failed Card:
Number: 4571736000000075
Expiry: 12/25
CVC: 123
```

### **Testing Flow:**

1. **Login to your app**
2. **Add items to basket**
3. **Checkout** → Creates order
4. **Select payment method** (GCash, GrabPay, etc.)
5. **Complete payment** in WebView
6. **App receives deep link** → Returns to PaymentResultPage
7. **Payment automatically processes** via `/process` endpoint
8. **Order status updated** to "paid"

### **Debug Logging:**

Add console logging throughout:

```csharp
Console.WriteLine($"[PAYMENT] Transaction ID: {transactionId}");
Console.WriteLine($"[PAYMENT] Checkout URL: {checkoutUrl}");
Console.WriteLine($"[PAYMENT] Payment Status: {paymentStatus}");
```

---

## 🐛 Troubleshooting

### **Issue: Deep link not working**

**Solution:**
- Check `AndroidManifest.xml` has correct intent filter
- Verify scheme is `laundryapp` not `https`
- Test deep link: `adb shell am start -W -a android.intent.action.VIEW -d "laundryapp://payment/success?transaction_id=TEST"`

### **Issue: Payment not processing**

**Solution:**
- Check auth token is set: `_paymentService.SetAuthToken(token)`
- Verify transaction ID is passed correctly
- Check Laravel logs: `storage/logs/laravel.log`

### **Issue: WebView closes immediately**

**Solution:**
- Use `Browser.OpenAsync()` instead of WebView
- Set `LaunchMode = BrowserLaunchMode.SystemPreferred`

### **Issue: 401 Unauthorized**

**Solution:**
```csharp
// Make sure to set token before calling API
var token = Preferences.Get("auth_token", "");
if (string.IsNullOrEmpty(token))
{
    await DisplayAlert("Error", "Please login first", "OK");
    await Shell.Current.GoToAsync("//LoginPage");
    return;
}
_paymentService.SetAuthToken(token);
```

---

## 📊 Complete Flow Diagram

```
┌─────────────────┐
│   User Action   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 1. Checkout → Creates Order         │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 2. POST /payments/create-intent     │
│    Response: transaction_id          │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 3. POST /payments/create-source     │
│    Body: { transaction_id, method }│
│    Response: checkout_url           │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 4. Browser.OpenAsync(checkout_url)  │
│    → Opens GCash/GrabPay payment    │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 5. User Completes Payment           │
│    → PayMongo redirects to:         │
│    https://your-api.com/payment/    │
│    success?transaction_id=XXX       │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 6. Laravel redirects to:            │
│    laundryapp://payment/success?    │
│    transaction_id=XXX               │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 7. MAUI receives deep link          │
│    → Navigates to PaymentResultPage │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 8. POST /payments/{id}/process ⭐   │
│    → Charges the source             │
│    → Updates order to "paid"        │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ 9. Show Success/Failure UI          │
│    → Navigate to Orders Page        │
└─────────────────────────────────────┘
```

---

## 🎯 Key Points to Remember

1. **Always call `/process` endpoint after redirect** - This is what actually charges the payment
2. **Store transaction_id globally** in `App.CurrentTransactionId` for deep link handling
3. **Use system browser** not WebView for better payment gateway compatibility
4. **Handle all payment statuses**: `paid`, `pending`, `processing`, `failed`
5. **Clear sensitive data** after processing
6. **Add proper error handling** for network issues
7. **Test with real test credentials** from PayMongo documentation

---

## 📞 Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check MAUI console output
3. Verify deep link configuration
4. Test API endpoints with Postman first
5. Ensure auth token is valid

---

**Happy Coding! 🚀**
