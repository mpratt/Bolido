<?php
/**
 * Setup the environment
 */
define('BOLIDO', 'TestSuite');
define('DEVELOPMENT_MODE', true);
date_default_timezone_set('America/Bogota');

/**
 * Register the Autoloaders
 */
spl_autoload_register(function ($class) {
    if (stripos($class, 'bolido') === false)
        return ;

    $class = str_replace('\\', DIRECTORY_SEPARATOR, ltrim($class, '\\'));
    if (strpos($class, 'Bolido/Modules') !== false)
    {
        $class = str_replace('Bolido/Modules', realpath(__DIR__ . '/../modules'), $class) . '.php';
        if (file_exists($class))
            require $class;
    }
    else if (file_exists($f = realpath(__DIR__ . '/../src/' . $class . '.php')))
        require $f;
});
require __DIR__ . '/../vendor/autoload.php';

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
 * Define Mock Objects
 */
class TestConfig extends \Bolido\Adapters\BaseConfig {}

class MockDB extends \Bolido\Database
{
    public function __construct(array $config) { return false;}
}

class MockError extends \Bolido\ErrorHandler
{
    public function __construct(){}
    public function display($message, $code = 500, $template = '') { return ; }
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
    public function find($requestPath = '') { return $this->found; }
    public function __get($v) { return $this->{$v}; }
    public function __set($k, $v) { $this->{$k} = $v; }
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
