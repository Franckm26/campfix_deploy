<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    $e = App\Models\EventRequest::where('status','Pending')->first();
    if($e) {
        $e->status = 'Cancelled';
        $e->save();
        echo 'Save OK: ' . $e->id . PHP_EOL;
        App\Models\ActivityLog::log('event_cancelled', 'Event cancelled: ' . $e->title, null);
        echo 'Log OK' . PHP_EOL;
    } else {
        echo 'No pending events found' . PHP_EOL;
    }
} catch(Exception $ex) {
    echo 'ERROR: ' . $ex->getMessage() . PHP_EOL;
    echo 'TRACE: ' . $ex->getTraceAsString() . PHP_EOL;
}
