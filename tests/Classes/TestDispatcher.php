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

class FakeInvalidUsersModule
{
    public function id() { return 0; }
}

class FakeValidUsersModule implements \Bolido\Interfaces\IUser
{
    /**
     * The \Bolido\Interfaces\IUser
     * has the proper documentation.
     */
    public function id() { return 0; }
    public function token() { return ''; }
    public function name() { return ''; }
    public function getData() { return array(); }
    public function loadUserData($userId) { return array(); }
    public function update(array $data, $userId = 0) { return false; }
    public function can($permission) { return false; }
    public function isLogged() { return false; }
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
        $this->app['db']   = new MockDB(array());
        $this->app['hooks']   = new MockHooks();
        $this->app['session'] = new MockSession();
        $this->app['router']  = new MockRouter();
        $this->app['error']   = new MockError();
        $this->app['config']  = new TestConfig();
        $this->app['config']->usersModule = 'FakeInvalidUsersModule';

        // The dispatcher needs an autoloader
        spl_autoload_register(function($class){
            $paths = array('Bolido\Modules' => MODULE_DIR . '/modules');
            $class = str_replace('\\', DIRECTORY_SEPARATOR, ltrim(str_replace(array_keys($paths), array_values($paths), $class), '\\'));
            if (file_exists($class . '.php'))
                require_once $class . '.php';
        });
    }

    /**
     * Test Dispatcher finds fake module
     */
    public function testDispatcherConnect()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertTrue($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when a controller was not found
     */
    public function testDispatcherConnect2()
    {
        $this->app['router']->found = false;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when a controller was not found
     */
    public function testDispatcherConnect3()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'UnknownController';
        $this->app['config']->usersModule = 'FakeValidUsersModule';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the controller action throws an error
     */
    public function testDispatcherConnect4()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'throwError';
        $this->app['router']->controller = 'Controller';
        $this->app['config']->usersModule = '';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the called action is private
     */
    public function testDispatcherConnect5()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'privateMethod';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the called action is protected
     */
    public function testDispatcherConnect6()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'protectedMethod';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the called action starts with an underscore
     */
    public function testDispatcherConnect7()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = '_underscore';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the called action has underscores
     */
    public function testDispatcherConnect8()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'fake_module';
        $this->app['router']->action = 'method_with_underscore';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertTrue($dispatcher->connect('fake url'));
    }

    /**
     * Test Dispatcher behaviour when the module doesnt exist
     */
    public function testDispatcherConnect9()
    {
        $this->app['router']->found = true;
        $this->app['router']->module = 'unknown_module';
        $this->app['router']->action = 'index';
        $this->app['router']->controller = 'Controller';
        $dispatcher = new \Bolido\Dispatcher($this->app);
        $this->assertFalse($dispatcher->connect('fake url'));
    }
}
?>
