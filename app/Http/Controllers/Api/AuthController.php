<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Customer Login
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'customer')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials or not a customer account'
            ], 401);
        }

        // Create token
        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Customer logged in successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role,
                    'allow_email_notifications' => $user->allow_email_notifications,
                    'allow_sms_notifications' => $user->allow_sms_notifications,
                    'loyalty_points' => $user->loyalty_points,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 200);
    }

    /**
     * Admin Login
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'admin')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials or not an admin account'
            ], 401);
        }

        // Create token
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Admin logged in successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role,
                    'allow_email_notifications' => $user->allow_email_notifications,
                    'allow_sms_notifications' => $user->allow_sms_notifications,
                    'loyalty_points' => $user->loyalty_points,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 200);
    }

    /**
     * Register Customer
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'allow_email_notifications' => 'boolean',
            'allow_sms_notifications' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'allow_email_notifications' => $request->allow_email_notifications ?? false,
            'allow_sms_notifications' => $request->allow_sms_notifications ?? false,
        ]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Customer registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role,
                    'allow_email_notifications' => $user->allow_email_notifications,
                    'allow_sms_notifications' => $user->allow_sms_notifications,
                    'loyalty_points' => $user->loyalty_points,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Register Admin (Protected - only for testing)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'allow_email_notifications' => 'boolean',
            'allow_sms_notifications' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'allow_email_notifications' => $request->allow_email_notifications ?? false,
            'allow_sms_notifications' => $request->allow_sms_notifications ?? false,
        ]);

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Admin registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role,
                    'allow_email_notifications' => $user->allow_email_notifications,
                    'allow_sms_notifications' => $user->allow_sms_notifications,
                    'loyalty_points' => $user->loyalty_points,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Logout
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'mobile_number' => $request->user()->mobile_number,
                    'role' => $request->user()->role,
                    'allow_email_notifications' => $request->user()->allow_email_notifications,
                    'allow_sms_notifications' => $request->user()->allow_sms_notifications,
                    'loyalty_points' => $request->user()->loyalty_points,
                ]
            ]
        ], 200);
    }

    /**
     * Update user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'mobile_number' => 'sometimes|string|max:20',
            'allow_email_notifications' => 'sometimes|boolean',
            'allow_sms_notifications' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        
        $user->update($request->only([
            'name',
            'mobile_number',
            'allow_email_notifications',
            'allow_sms_notifications',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role,
                    'allow_email_notifications' => $user->allow_email_notifications,
                    'allow_sms_notifications' => $user->allow_sms_notifications,
                ]
            ]
        ], 200);
    }

    /**
     * Change Password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Check if new password is different from current
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'New password must be different from current password'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optionally revoke all tokens except current
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ], 200);
    }
}


