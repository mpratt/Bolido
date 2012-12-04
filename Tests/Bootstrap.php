<?php
    define('BOLIDO', 'TestSuite');
    define('DEVELOPMENT_MODE', true);
    date_default_timezone_set('America/Bogota');

    require_once('../Source/Bolido/Adapters/BaseConfig.php');
    class TestConfig extends \Bolido\App\Adapters\BaseConfig {}
?>
