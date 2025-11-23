<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with non-hashed passwords
        $users = \DB::table('nguoi_dung')->get();
        
        foreach ($users as $user) {
            // Check if the password is not already hashed
            if (!empty($user->mat_khau) && !preg_match('/^\$2y\$.*/', $user->mat_khau)) {
                \DB::table('nguoi_dung')
                    ->where('id', $user->id)
                    ->update([
                        'mat_khau' => \Hash::make($user->mat_khau)
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration, we can't reverse hashing
        // So we'll leave this empty
    }
};
