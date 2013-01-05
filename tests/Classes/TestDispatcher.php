<?php
/**
 * TestDispatcher.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

//require_once('../vendor/Bolido/AppRegistry.php');
require_once('../vendor/Bolido/Dispatcher.php');
require_once('../vendor/Bolido/ErrorHandler.php');
require_once('../vendor/Bolido/Hooks.php');
require_once('../vendor/Bolido/Session.php');
require_once('../vendor/Bolido/Router.php');

class MockError extends \Bolido\ErrorHandler
{
    public function __construct(){}
    public function display() { return ; }
}

class MockHooks3 extends \Bolido\Hooks
{
    public function __construct() {}
    public function run()
    {
        if (func_num_args() > 0)
        {
            $args    = func_get_args();
            $section = strtolower($args['0']);
            $return  = (isset($args['1']) ? $args['1'] : null);
            return $return;
        }
    }
}

class MockSession2 extends \Bolido\Session
{
    public function __construct() {}
    public function start() {}
    public function close() {}
}

class MockRouter extends \Bolido\Router
{
    public $found;
    public function __construct() {}
    public function find() { return $this->found; }
    public function __get($v) { return $this->{$v}; }
    public function __set($k, $v) { $this->{$k} = $v; }
}

class TestDispatcher extends PHPUnit_Framework_TestCase
{
    protected $app;

    /**
     * SetUp the environment
     */
    public function setUp()
    {
        $this->app = new \Bolido\AppRegistry();
        $this->app['hooks']   = new MockHooks3();
        $this->app['session'] = new MockSession2();
        $this->app['router']  = new MockRouter();
        $this->app['error']   = new MockError();
    }

    /**
     * Test Dispatcher
     */
    public function testDispatcherConnect()
    {
        $dispatcher = new \Bolido\Dispatcher($this->app);
    }
}
?>
