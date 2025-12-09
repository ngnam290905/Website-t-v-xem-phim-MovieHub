<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NguoiDung;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$user = NguoiDung::where('email', 'staff@example.com')->with('vaiTro')->first();

if ($user) {
    echo "User found: " . $user->ho_ten . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role ID: " . $user->id_vai_tro . "\n";
    echo "Role relation loaded: " . ($user->relationLoaded('vaiTro') ? 'Yes' : 'No') . "\n";
    if ($user->vaiTro) {
        echo "Role name: " . $user->vaiTro->ten . "\n";
    } else {
        echo "Role name: NULL (không có vai trò)\n";
    }
} else {
    echo "User not found\n";
}
?>
