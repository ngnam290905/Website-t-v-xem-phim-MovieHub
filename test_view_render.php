<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing staff suat-chieu view rendering...\n";
    
    // Set up authentication (simulate staff user)
    $user = \App\Models\NguoiDung::whereHas('vaiTro', function($query) {
        $query->where('ten', 'staff');
    })->first();
    
    if (!$user) {
        echo "❌ No staff user found\n";
        exit;
    }
    
    auth()->login($user);
    
    // Get data
    $suatChieu = \App\Models\SuatChieu::with(['phim', 'room'])
        ->whereHas('room', function($query) {
            $query->where('status', 'active');
        })
        ->orderBy('start_time', 'desc')
        ->paginate(10);
    
    $phim = \App\Models\Movie::where('trang_thai', 1)->get();
    $phongChieu = \App\Models\PhongChieu::where('status', 'active')->get();
    
    // Test view rendering
    $view = view('staff.suat-chieu.index', compact('suatChieu', 'phim', 'phongChieu'));
    $html = $view->render();
    
    echo "✓ View rendered successfully\n";
    echo "✓ HTML length: " . strlen($html) . " characters\n";
    
    // Check for common issues
    if (strpos($html, 'undefined') !== false) {
        echo "❌ Found 'undefined' in HTML\n";
    } else {
        echo "✓ No 'undefined' found\n";
    }
    
    if (strpos($html, 'NaN') !== false) {
        echo "❌ Found 'NaN' in HTML\n";
    } else {
        echo "✓ No 'NaN' found\n";
    }
    
    if (strpos($html, 'error') !== false) {
        echo "⚠️  Found 'error' in HTML (might be normal)\n";
    }
    
    // Check for JavaScript syntax issues
    $jsStart = strpos($html, '<script>');
    $jsEnd = strpos($html, '</script>');
    
    if ($jsStart !== false && $jsEnd !== false) {
        $js = substr($html, $jsStart + 8, $jsEnd - $jsStart - 8);
        echo "✓ JavaScript found: " . strlen($js) . " characters\n";
        
        // Check for common JS issues
        if (strpos($js, 'function(') !== false) {
            echo "✓ Functions found\n";
        }
        
        if (strpos($js, 'addEventListener') !== false) {
            echo "✓ Event listeners found\n";
        }
    }
    
    echo "\nView rendering test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

