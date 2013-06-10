<?php
/**
 * TestMainController.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class TestMainController extends PHPUnit_Framework_TestCase
{
    public function testIndexMethod()
    {
        $this->app = new TestContainer();
        $this->app['config']->moduleDir = MODULE_DIR . '/modules';
        $this->app['config']->logsDir = 'hi';
        $this->app['config']->uploadsDir = 'hi';
        $this->app['config']->cacheDir = 'hi';
        $this->app['router']->controller = 'Controller';
        $this->app['router']->action = 'index';
        $this->app['router']->module = 'main';
        $this->app['config']->skin = 'default';

        $controller = new TestBolidoController();
        $controller->_loadSettings($this->app);

        $this->assertNull($controller->index());
        $this->assertTrue(in_array('main/main', $this->app['lang']->files));
    }
}
?>
