<?php
define('BOLIDO', 'TestSuite');
define('DEVELOPMENT_MODE', true);
date_default_timezone_set('America/Bogota');

/**
 * Define important Constants
 */
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

/**
 * Include important stuff
 */
require_once('vendor/Bolido/Interfaces/ICache.php');
require_once('vendor/Bolido/Interfaces/IUser.php');
require_once('vendor/Bolido/Interfaces/IDatabaseHandler.php');
require_once('vendor/Bolido/Database.php');
require_once('vendor/Bolido/Cache/ApcEngine.php');
require_once('vendor/Bolido/Cache/FileEngine.php');
require_once('vendor/Bolido/AppRegistry.php');
require_once('vendor/Bolido/Dispatcher.php');
require_once('vendor/Bolido/ErrorHandler.php');
require_once('vendor/Bolido/Hooks.php');
require_once('vendor/Bolido/Session.php');
require_once('vendor/Bolido/Router.php');
require_once('vendor/Bolido/Lang.php');
require_once('vendor/Bolido/Template.php');
require_once('vendor/Bolido/Functions.php');
require_once('vendor/Bolido/UrlParser.php');
require_once('vendor/Bolido/Adapters/BaseConfig.php');
require_once('vendor/Bolido/Adapters/BaseController.php');

/**
 * Define Mock Objects
 */
class TestConfig extends \Bolido\Adapters\BaseConfig {}

class MockError extends \Bolido\ErrorHandler
{
    public function __construct(){}
    public function display() { return ; }
}

class MockHooks extends \Bolido\Hooks
{
    public function __construct() {}
    public function run()
    {
            $args    = func_get_args();
            $section = strtolower($args['0']);
            $return  = (isset($args['1']) ? $args['1'] : null);
            return $return;
    }
}

class MockSession extends \Bolido\Session
{
    public $values = array();
    public function __construct() {}
    public function start() {}
    public function close() {}
    public function has($k) { return (bool) isset($this->values[$k]) ; }
    public function get($k) { return $this->values[$k]; }
    public function set($k, $v) { $this->values[$k] = $v; }
    public function delete($k) { unset($this->values[$k]); }
}

class MockRouter extends \Bolido\Router
{
    public $found;
    public function __construct() {}
    public function find() { return $this->found; }
    public function __get($v) { return $this->{$v}; }
    public function __set($k, $v) { $this->{$k} = $v; }
}

class MockTemplate extends \Bolido\Template
{
    public $values = array();
    public function __construct() {}
    public function load($k, array $v = array())
    {
        $this->set($k, $v);
        if (!empty($v))
        {
            foreach ($v as $k2 => $v2)
                $this->set($k2, $v2);
        }
    }
    public function set($k, $v) { $this->values[$k] = $v; }
    public function display() {}
}

class MockTemplateExtended extends MockTemplate
{
    public function js($js) { $this->set('js', $js); }
    public function css($js) { $this->set('css', $js); }
    public function __call($m, $a) {}
}

class MockLang extends \Bolido\Lang
{
    public $files = array();
    public function __construct() {}
    public function load($f) { $this->files[] = $f; return true;}
    public function free() { return null; }
    public function get()
    {
        $args = func_get_args();
        $index = $args['0'];
        array_shift($args);

        if (!isset($args))
            $args = array();

        return $index . (!empty($args) ? '_' . implode('_', $args) : '');
    }
}
?>
