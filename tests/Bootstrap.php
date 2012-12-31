<?php
    define('BOLIDO', 'TestSuite');
    define('DEVELOPMENT_MODE', true);
    date_default_timezone_set('America/Bogota');

    require_once('../vendor/Bolido/Adapters/BaseConfig.php');
    class TestConfig extends \Bolido\Adapters\BaseConfig {}
?>
