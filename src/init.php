<?php

ini_set('display_errors', 1);
define("ROOT_DIR", dirname(dirname(__FILE__)));
define("APP_DIR", ROOT_DIR . '/app');
define("SRC_DIR", ROOT_DIR . '/src');
define("WEB_DIR", ROOT_DIR . '/web');

// setup autoloader
require SRC_DIR . '/Core/AutoLoader.php';
spl_autoload_register("Riddle\Core\Autoloader::loadClass");