<?php
// Debug dump functions
function p() { echo '<pre>', print_r(func_get_args(), 1), '</pre>'; }
function pd() { echo '<pre>', print_r(func_get_args(), 1), '</pre>'; die; }

include __DIR__ . '/../app/config.php';
$app = include __DIR__ . '/../app/bootstrap.php';
$app['debug'] = true;
$app->run();
