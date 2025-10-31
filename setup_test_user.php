<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update first customer
$user = App\Models\User::where('email', 'customer@test.com')->first();

if ($user) {
    $user->password = bcrypt('password');
    $user->mobile_number = '09171234567';
    $user->allow_email_notifications = true;
    $user->allow_sms_notifications = true;
    $user->save();
    
    echo "Updated user credentials:\n";
    echo "Email: {$user->email}\n";
    echo "Password: password\n";
    echo "Mobile: {$user->mobile_number}\n";
    echo "Email notifications: enabled\n";
    echo "SMS notifications: enabled\n";
} else {
    echo "User not found\n";
}
