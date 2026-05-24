<?php

use Illuminate\Support\Facades\Route;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$routes = Route::getRoutes();

foreach ($routes as $route) {
    if ($route->uri() === 'admin/pjp/{jadwal}' && in_array('DELETE', $route->methods())) {
        echo "Found DELETE route for admin/pjp/{jadwal}\n";
        echo 'Action: '.$route->getActionName()."\n";
    }
}

echo "\nAll routes:\n";
foreach ($routes as $route) {
    echo $route->uri().' ['.implode(',', $route->methods())."]\n";
}
