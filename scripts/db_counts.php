<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Boot the framework
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DtrLog;
use App\Models\DailyTimeRecord;
use App\Models\User;

echo 'DtrLog: ' . DtrLog::count() . PHP_EOL;
echo 'DailyTimeRecord: ' . DailyTimeRecord::count() . PHP_EOL;
echo 'Users: ' . User::count() . PHP_EOL;

$users = User::all();
foreach($users as $u){
	echo "User: id={$u->id}, email={$u->email}, name={$u->name}\n";
}

$profiles = \Illuminate\Support\Facades\DB::table('intern_profiles')->get();
foreach($profiles as $p){
	echo "InternProfile: id={$p->id}, name={$p->name}, required_hours={$p->required_hours}\n";
}

$sample = DtrLog::orderBy('log_date')->take(3)->get();
foreach($sample as $s){
	echo "DtrLog: id={$s->id}, intern_id={$s->intern_id}, log_date={$s->log_date}, daily_total_hours={$s->daily_total_hours}\n";
}
