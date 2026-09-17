<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'medico_ocupacional@test.com';
$password = 'password123';

$user = \App\Models\User::where('email', $email)->first();
echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
if ($user) {
    echo "User ID: {$user->id}\n";
    echo "Active: {$user->activo}\n";
    echo "Must change password: {$user->must_change_password}\n";
    echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
    echo "Password check: " . (Hash::check($password, $user->password) ? 'VALID' : 'INVALID') . "\n";
}
