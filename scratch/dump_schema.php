<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
foreach($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "Table: $tableName\n";
    $columns = DB::select("DESCRIBE $tableName");
    foreach($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) [{$column->Key}]\n";
    }
    echo "\n";
}
