<?php
set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

// Enable Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Prevent session cookies
ini_set('session.use_cookies', 0);
