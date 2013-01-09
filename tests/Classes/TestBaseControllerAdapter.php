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
use \Bolido\Modules\main\Controller as Controller;

require_once(BASE_DIR . '/../modules/main/Controller.php');
class TestBaseControllerAdapter extends PHPUnit_Framework_TestCase
{
    protected $app;

    /**
     * Set up the environment
     */
    public function setUp()
    {
        $this->app = new \Bolido\AppRegistry();
        $config = new TestConfig();
        $config->mainUrl = 'http://example.com';
        $config->initialize();

        $this->app['config'] = $config;
        $this->app['lang']   = new MockLang();
        $this->app['router'] = new MockRouter();
        $this->app['hooks']  = new MockHooks();
        $this->app['template'] = new MockTemplateExtended();
    }

    /**
     * Test the index Action
     */
    public function testIndexAction()
    {
        $controller = new Controller();
        $controller->_loadSettings($this->app);
        $controller->index();

        $this->assertArrayHasKey('checks', $this->app['template']->values);
        $this->assertTrue(in_array('main/main', $this->app['lang']->files));
    }
}
?>
