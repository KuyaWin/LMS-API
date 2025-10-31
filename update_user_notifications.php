<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find and update the first customer user
$user = App\Models\User::where('role', 'customer')->first();

if ($user) {
    $user->allow_email_notifications = true;
    $user->allow_sms_notifications = true;
    $user->mobile_number = '09171234567'; // Add test mobile number
    $user->save();
    
    echo "Updated user: {$user->name} ({$user->email})\n";
    echo "Email notifications: " . ($user->allow_email_notifications ? 'enabled' : 'disabled') . "\n";
    echo "SMS notifications: " . ($user->allow_sms_notifications ? 'enabled' : 'disabled') . "\n";
    echo "Mobile number: {$user->mobile_number}\n";
    echo "Access token for testing: Create a new token via login\n";
} else {
    echo "No customer user found\n";
}
