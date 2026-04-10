<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DtrSetting;

$user = User::where('email', 'seed_intern@example.com')->first();
if (!$user) {
    echo "NO_USER\n";
    exit(0);
}
$setting = DtrSetting::where('user_id', $user->id)->first();
if (!$setting) {
    echo "NO_SETTING\n";
    exit(0);
}
echo "USER:" . $user->email . "\n";
echo "STARTING_DATE:" . ($setting->starting_date ?? 'NULL') . "\n";
echo "TOTAL_HOURS:" . ($setting->total_hours ?? 'NULL') . "\n";
