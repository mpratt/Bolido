<?php
    define('BOLIDO', 'TestSuite');
    define('IN_DEVELOPMENT', true);
    date_default_timezone_set('America/Bogota');

    if (!class_exists('Config'))
        require_once(dirname(__FILE__) . '/Config-Test.php');
?>
