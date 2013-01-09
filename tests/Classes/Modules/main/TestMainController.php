<?php
/**
 * TestMainController.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * This object Acts like a controller
 */
class MockController extends \Bolido\Adapters\BaseController
{
    public $app;
    public $settings = array();
    public $flushTemplates = true;

    public function index() { return 'hello world'; }
    public function getSetting($s) { return $this->setting($s); }
}

class TestMainController extends PHPUnit_Framework_TestCase
{
    protected $app;

    /**
     * Set up the environment
     */
    public function setUp()
    {
        $this->app = new \Bolido\AppRegistry();
        $config = new TestConfig();
        $config->initialize();
        $config->moduleDir = MODULE_DIR . '/modules';

        $router = new MockRouter();
        $router->controller = 'Controller';
        $router->action     = 'index';
        $router->module     = 'fake_module';

        $this->app['config'] = $config;
        $this->app['router'] = $router;
        $this->app['lang']   = new MockLang();
        $this->app['hooks']  = new MockHooks();
        $this->app['template'] = new MockTemplateExtended();
    }

    /**
     * Test the _loadSettings Method
     */
    public function testLoadSettings()
    {
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');
    }

    /**
     * Test the _loadSettings Method
     */
    public function testLoadSettings2()
    {
        // This skin doesnt exist, should go back to default
        $this->app['config']->skin = 'Yalla';
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');
    }

    /**
     * Test the _loadSettings Method
     */
    public function testLoadSettings3()
    {
        $this->app['config']->skin = 'other';
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/other');
    }

    /**
     * Test the _loadSettings Method
     * and the setting method.
     */
    public function testLoadSettingsAndSettingsMethod()
    {
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');

        // Test Settings.json
        $this->assertEquals('Hello', $controller->getSetting('verb1'));
        $this->assertEquals('World', $controller->getSetting('verb2'));
        $this->assertEquals($controller->settings['path'], $controller->getSetting('path'));
        $this->assertEquals(null, $controller->getSetting('verb_unknown'));
    }

    /**
     * Test the _beforeAction Method
     */
    public function testBeforeAction()
    {
        $controller = new MockController();
        $this->assertNull($controller->_beforeAction());
    }

    /**
     * Test the _flushTemplates Method
     */
    public function testFlushTemplates()
    {
        $this->app['config']->skin = 'other';
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/other');

        $controller->_flushTemplates();
        $this->assertArrayHasKey('moduleUrl', $this->app['template']->values);
        $this->assertArrayHasKey('moduleTemplateUrl', $this->app['template']->values);
        $this->assertFalse(in_array('js', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('css', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('user', array_keys($this->app['template']->values)));
    }

    /**
     * Test the _flushTemplates Method
     */
    public function testFlushTemplates2()
    {
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');

        $controller->flushTemplates = false;
        $controller->_flushTemplates();
        $this->assertFalse(in_array('js', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('css', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('moduleUrl', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('moduleTemplateUrl', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('user', array_keys($this->app['template']->values)));
    }

    /**
     * Test the _flushTemplates Method
     */
    public function testFlushTemplates3()
    {
        $this->app['template'] = new MockTemplateExtended();
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');

        $controller->_flushTemplates();
        $this->assertTrue(in_array('js', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('css', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('moduleUrl', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('moduleTemplateUrl', array_keys($this->app['template']->values)));
        $this->assertFalse(in_array('user', array_keys($this->app['template']->values)));
    }

    /**
     * Test the _flushTemplates Method
     */
    public function testFlushTemplates4()
    {
        $this->app['template'] = new MockTemplateExtended();
        $this->app['user']     = (object) array('dummy object');
        $controller = new MockController();
        $controller->_loadSettings($this->app);

        $this->assertEquals($controller->settings['controller'], $this->app['router']->controller);
        $this->assertEquals($controller->settings['module'], $this->app['router']->module);
        $this->assertEquals($controller->settings['action'], $this->app['router']->action);
        $this->assertEquals($controller->settings['path'], $this->app['config']->moduleDir . '/' . $controller->settings['module']);
        $this->assertEquals($controller->settings['template_path'], $controller->settings['path'] . '/templates/default');

        $controller->_flushTemplates();
        $this->assertTrue(in_array('js', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('css', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('moduleUrl', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('moduleTemplateUrl', array_keys($this->app['template']->values)));
        $this->assertTrue(in_array('user', array_keys($this->app['template']->values)));
    }
}
?>
