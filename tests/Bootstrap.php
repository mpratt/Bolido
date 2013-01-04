<?php
define('BOLIDO', 'TestSuite');
define('DEVELOPMENT_MODE', true);
date_default_timezone_set('America/Bogota');

if (!defined('BASE_DIR'))
    define('BASE_DIR', __DIR__);

if (!defined('SOURCE_DIR'))
    define('SOURCE_DIR', __DIR__ . '/Classes');

if (!defined('MODULE_DIR'))
    define('MODULE_DIR', __DIR__ . '/Workspace');

if (!defined('ASSETS_DIR'))
    define('ASSETS_DIR', MODULE_DIR);

if (!defined('CACHE_DIR'))
    define('CACHE_DIR', ASSETS_DIR . '/cache');

if (!defined('LOGS_DIR'))
    define('LOGS_DIR', ASSETS_DIR . '/logs');

require_once('../vendor/Bolido/Adapters/BaseConfig.php');
class TestConfig extends \Bolido\Adapters\BaseConfig {}

?>
