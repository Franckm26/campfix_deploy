<?php
/**
 * Quick script to link maintenance staff to user accounts
 * Run this from command line: php link_maintenance_staff.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MaintenanceStaff;
use App\Models\User;

echo "========================================\n";
echo "   Link Maintenance Staff to Users\n";
echo "========================================\n\n";

// Get all maintenance staff without linked user accounts
$staffWithoutUsers = MaintenanceStaff::whereNull('user_id')
    ->whereNull('deleted_at')
    ->get();

if ($staffWithoutUsers->isEmpty()) {
    echo "✅ All maintenance staff already have linked user accounts!\n\n";
    exit(0);
}

echo "Found {$staffWithoutUsers->count()} maintenance staff without linked user accounts:\n\n";

foreach ($staffWithoutUsers as $staff) {
    echo "ID: {$staff->id}\n";
    echo "Name: {$staff->name}\n";
    echo "Email: {$staff->email}\n";
    
    // Try to find user by email
    if ($staff->email) {
        $user = User::where('email', $staff->email)
            ->where('role', 'maintenance')
            ->first();
        
        if ($user) {
            echo "   → Found matching user: {$user->name} (ID: {$user->id})\n";
            echo "   → Linking...\n";
            
            $staff->user_id = $user->id;
            $staff->save();
            
            echo "   ✅ Linked successfully!\n\n";
        } else {
            echo "   ❌ No matching user found\n";
            echo "   → Please create a user account with:\n";
            echo "      Email: {$staff->email}\n";
            echo "      Role: maintenance\n";
            echo "      Then run this script again\n\n";
        }
    } else {
        echo "   ❌ No email address - cannot auto-link\n";
        echo "   → Please add email to maintenance staff record\n\n";
    }
    
    echo "----------------------------------------\n";
}

echo "\nDone! Check the results above.\n";
echo "========================================\n";
