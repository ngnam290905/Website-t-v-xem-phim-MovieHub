<?php
// Test session và CSRF
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Session Driver: " . config('session.driver') . "\n";
echo "Session Path: " . config('session.path') . "\n";
echo "Session Cookie: " . config('session.cookie') . "\n";
echo "Session Domain: " . (config('session.domain') ?: 'null') . "\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "\nTest hoàn tất!";
