<?php
/**
 * TestBaseControllerAdapter.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestBaseControllerAdapter extends PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new TestContainer();
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->module = 'main';
    }

    public function testIndexAction()
    {
        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertNull($controller->_beforeAction());
        $this->assertNull($controller->index());
        $this->assertNull($controller->_shutdownModule());
        $this->assertTrue(in_array('main/main', $this->app['lang']->files));
    }

    public function testNotify()
    {
        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $reflection = new ReflectionClass('TestBolidoController');
        $method = $reflection->getMethod('notify');
        $method->setAccessible(true);

        $method->invoke($controller, 'Message');
        $this->assertCount(1, $this->app['session']->get('bolidoHtmlNotifications'));

        $method->invoke($controller, 'Message 2');
        $this->assertCount(2, $this->app['session']->get('bolidoHtmlNotifications'));
    }

    public function testLoadSettings()
    {
        $this->app = new TestContainer();
        $this->app['config']->moduleDir = MODULE_DIR . '/modules';
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->controller = 'Controller';
        $this->app['router']->action = 'index';
        $this->app['router']->module = 'fake';
        $this->app['config']->skin = 'default';

        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');
    }

    public function testLoadSettings2()
    {
        $this->app = new TestContainer();
        $this->app['config']->moduleDir = MODULE_DIR . '/modules';
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->controller = 'Controller';
        $this->app['router']->action = 'index';
        $this->app['router']->module = 'fake';
        $this->app['config']->skin = 'Yalla';

        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');
    }

    public function testLoadSettings3()
    {
        $this->app = new TestContainer();
        $this->app['config']->moduleDir = MODULE_DIR . '/modules';
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->controller = 'Controller';
        $this->app['router']->action = 'index';
        $this->app['router']->module = 'fake';
        $this->app['config']->skin = 'other';

        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/other');
    }

    public function testLoadSettingsAndSettingsMethod()
    {
        $this->app = new TestContainer();
        $this->app['config']->moduleDir = MODULE_DIR . '/modules';
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->controller = 'Controller';
        $this->app['router']->action = 'index';
        $this->app['router']->module = 'fake';
        $this->app['config']->skin = 'default';

        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');

        $this->assertEquals('Hello', $controller->getSetting('verb1'));
        $this->assertEquals('World', $controller->getSetting('verb2'));
        $this->assertEquals($controller->settings['path'], $controller->getSetting('path'));
        $this->assertEquals(null, $controller->getSetting('verb_unknown'));
    }
}
?>
