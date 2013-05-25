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

// The dispatcher needs an autoloader for the dummy module
spl_autoload_register(function($class){
    $paths = array('Bolido\Modules' => MODULE_DIR . '/modules');
    $class = str_replace('\\', DIRECTORY_SEPARATOR, ltrim(str_replace(array_keys($paths), array_values($paths), $class), '\\'));
    if (file_exists($class . '.php'))
        require_once $class . '.php';
});

class TestDispatcher extends PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp() { $this->app = new TestContainer(); }

    public function testDispatcherConnect()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertTrue($dispatcher->connect());
    }

    public function testDispatcherConnect2()
    {
        $this->app['router']->found = false;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect3()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'UnknownController';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect4()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'throwError';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect5()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'privateMethod';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect6()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'protectedMethod';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect7()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = '_underscore';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }

    public function testDispatcherConnect8()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake';
        $this->app['router']->action = 'method_with_underscore';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertTrue($dispatcher->connect());
    }

    public function testDispatcherConnect9()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'unknown_module';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';

        $dispatcher = new \Bolido\Dispatcher($this->app);

        $this->assertFalse($dispatcher->connect());
    }
}
?>
