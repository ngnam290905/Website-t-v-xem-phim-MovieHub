<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/tin-tuc', 'GET')
);

echo "Status: " . $response->getStatusCode() . "\n";

// Simulate what the controller returns
$controller = new App\Http\Controllers\PublicController();
$request = new Illuminate\Http\Request();

try {
    // Get news count
    $news = \App\Models\TinTuc::published()->get();
    echo "Published news count: " . $news->count() . "\n";
    
    if ($news->count() > 0) {
        echo "First article: " . $news->first()->tieu_de . "\n";
        echo "Published date: " . $news->first()->ngay_dang . "\n";
        echo "Status: " . ($news->first()->trang_thai ? 'true' : 'false') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
