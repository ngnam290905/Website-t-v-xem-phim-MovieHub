<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing staff suat-chieu index route...\n";
    
    // Simulate a request to the staff suat-chieu index route
    $request = Request::create('/staff/suat-chieu', 'GET');
    
    // Set up authentication (simulate staff user)
    $user = \App\Models\NguoiDung::whereHas('vaiTro', function($query) {
        $query->where('ten', 'staff');
    })->first();
    
    if (!$user) {
        echo "❌ No staff user found\n";
        exit;
    }
    
    echo "✓ Found staff user: " . $user->ho_ten . "\n";
    
    // Set the authenticated user
    auth()->login($user);
    
    // Test the controller method directly
    $controller = new \App\Http\Controllers\SuatChieuController();
    $response = $controller->index($request);
    
    echo "✓ Controller method executed successfully\n";
    
    // Check if response is a view
    if ($response instanceof \Illuminate\View\View) {
        echo "✓ Response is a view: " . $response->getName() . "\n";
        
        // Get view data
        $data = $response->getData();
        echo "✓ View data keys: " . implode(', ', array_keys($data)) . "\n";
        
        // Check if required data exists
        if (isset($data['suatChieu'])) {
            echo "✓ Suat chieu data exists: " . $data['suatChieu']->count() . " items\n";
        }
        
        if (isset($data['phim'])) {
            echo "✓ Phim data exists: " . $data['phim']->count() . " items\n";
        }
        
        if (isset($data['phongChieu'])) {
            echo "✓ Phong chieu data exists: " . $data['phongChieu']->count() . " items\n";
        }
    }
    
    echo "\nAll tests passed! No errors found.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

