<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BasketController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    // Customer routes
    Route::post('/customer/register', [AuthController::class, 'registerCustomer']);
    Route::post('/customer/login', [AuthController::class, 'customerLogin']);
    
    // Admin routes
    Route::post('/admin/register', [AuthController::class, 'registerAdmin']); // For testing only
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});

// Public routes - Services (anyone can view services)
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

// Public routes - Add-ons (anyone can view add-ons)
Route::get('/addons', [BasketController::class, 'getAddons']);

// Public routes - Feedback (anyone can submit feedback)
Route::post('/feedback', [FeedbackController::class, 'store']);

// Public routes - PayMongo Webhook
Route::post('/payments/webhook', [PaymentController::class, 'handleWebhook']);

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    
    // Order routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    
    // Basket routes
    Route::get('/basket', [BasketController::class, 'index']);
    Route::post('/basket', [BasketController::class, 'store']);
    Route::put('/basket/{id}', [BasketController::class, 'update']);
    Route::delete('/basket/{id}', [BasketController::class, 'destroy']);
    Route::delete('/basket', [BasketController::class, 'clear']);
    Route::post('/basket/checkout', [BasketController::class, 'checkout']);
    
    // Feedback routes (protected - admin only)
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/statistics', [FeedbackController::class, 'statistics']);
    Route::get('/feedback/{id}', [FeedbackController::class, 'show']);
    Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy']);
    
    // Payment routes
    Route::get('/payments/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/create-source', [PaymentController::class, 'createPaymentSource']);
    Route::get('/payments/{transactionId}/status', [PaymentController::class, 'checkPaymentStatus']);
    Route::post('/payments/{transactionId}/process', [PaymentController::class, 'processPaymentSource']);
    
    // Example protected route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


