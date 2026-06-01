<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// In dev: vendor/ sits one level above this file (nirvighna/vendor/).
// On Hostinger: this file is copied to public_html/ while the app lives in
// public_html/nirvighna/ — detect by checking if vendor/ exists at ../
$basePath = is_dir(__DIR__.'/../vendor')
    ? realpath(__DIR__.'/..')
    : realpath(__DIR__.'/../nirvighna');

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

