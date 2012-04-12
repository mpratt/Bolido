<?php
/**
 * TestRouter.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

if (!defined('BOLIDO'))
    define('BOLIDO', 'TestRouter');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Router.class.php');

class TestRouter extends PHPUnit_Framework_TestCase
{
    /**
     * Test Default Routes
     */
    public function testDefaultRoutes()
    {
        $router = new Router('/');
        $router->find();

        $this->assertEquals($router->get('action'), 'index');
        $this->assertEquals($router->get('module'), 'home');
    }

    /**
     * Test Modified Main Module
     */
    public function testDefaultRoutes2()
    {
        $router = new Router('/', 'MainModuleName');
        $router->find();

        $this->assertEquals($router->get('action'), 'index');
        $this->assertEquals($router->get('module'), 'MainModuleName');
    }

    /**
     * Test Module and Actions detection
     */
    public function testDefaultRoutes3()
    {
        // Test actions
        $router = new Router('/moduleName/ActionName');
        $router->find();

        $this->assertEquals($router->get('action'), 'ActionName');
        $this->assertEquals($router->get('module'), 'moduleName');
    }

    /**
     * Test Module, Actions and Process detection
     */
    public function testDefaultRoutes4()
    {
        // Test actions and process
        $router = new Router('/moduleName/ProcessName/ActionName');
        $router->find();

        $this->assertEquals($router->get('process'), 'ProcessName');
        $this->assertEquals($router->get('action'), 'ActionName');
        $this->assertEquals($router->get('module'), 'moduleName');
    }

    /**
     * Test basic mapping with module overwritting
     */
    public function testRouterMappings()
    {
        $router = new Router('/bookings/reserve/');
        $router->map('/bookings/[a:action]/', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'bookings');
        $this->assertEquals($router->get('action'), 'reserve');
        $this->assertTrue($found);
    }

    /**
     * Test Mappings with module overwritting and custom parameter
     */
    public function testRouterMappings2()
    {
        $router = new Router('/bookings/90');
        $router->map('/bookings/[i:id]', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'bookings');
        $this->assertEquals($router->get('action'), 'index');
        $this->assertEquals($router->get('id'), '90');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping with invalid int parameter
     */
    public function testRouterMappings3()
    {
        $router = new Router('/bookings/notnumeric/airport/airplane');
        $router->map('/bookings/[i:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertFalse($router->get('id'));
        $this->assertFalse($found);
    }

    /**
     * Test Mapping with invalid hex parameter
     */
    public function testRouterMappings4()
    {
        $router = new Router('/bookings/nothex/airport/airplane');
        $router->map('/bookings/[h:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertFalse($found);
        $this->assertFalse($router->get('id'));
    }

    /**
     * Test Mapping with invalid/corrupted path
     */
    public function testRouterMappings5()
    {
        $router = new Router('/bookings/corrupted?&^url/airport/airplane');
        $router->map('/bookings/[a:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertFalse($router->get('id'));
        $this->assertFalse($found);
    }

    /**
     * Test Mapping preference depending on matched regex
     */
    public function testRouterMappings6()
    {
        $router = new Router('/bookings/12345');
        $router->map('/bookings/[i:id]', array('module' => 'bookings', 'action' => 'fetchId'));
        $router->map('/bookings/[h:id]', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'bookings');
        $this->assertEquals($router->get('action'), 'fetchId');
        $this->assertEquals($router->get('id'), '12345');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping preference depending on mtched regex
     */
    public function testRouterMappings7()
    {
        $router = new Router('/bookings/afbe');
        $router->map('/bookings/[i:id]', array('module' => 'bookings', 'action' => 'fetchId'));
        $router->map('/bookings/[h:id]', array('module' => 'bookings'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'bookings');
        $this->assertEquals($router->get('action'), 'index');
        $this->assertEquals($router->get('id'), 'afbe');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping and matched conditions
     */
    public function testRouterMappings8()
    {
        $router = new Router('/bolido-framework/page/40');
        $router->setMainModule('defaultModuleName');
        $router->map('/bolido-framework/page/[i:id]', array('action' => 'fetchId'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'defaultModuleName');
        $this->assertEquals($router->get('action'), 'fetchId');
        $this->assertEquals($router->get('id'), '40');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping and matched conditions
     */
    public function testRouterMappings9()
    {
        $router = new Router('/history/colombia/presidents/1998/Ernesto-SampeR');
        $router->map('/history/[a:country]/presidents/[i:year]/[a:name]', array('action' => 'getPresidents'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'home');
        $this->assertEquals($router->get('action'), 'getPresidents');
        $this->assertEquals($router->get('year'), '1998');
        $this->assertEquals($router->get('country'), 'colombia');
        $this->assertEquals($router->get('name'), 'Ernesto-SampeR');
        $this->assertTrue($found);
    }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive()
    {
        $router = new Router('/ModuleName/ActionName');
        $router->find();

        $this->assertFalse(($router->get('module') == 'modulename'));
    }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive2()
    {
        $router = new Router('/house/floor/2/bedroom');
        $router->map('/House/flOor/2/BedRooM', array('module' => 'MyHouseModel'));
        $found = $router->find();

        $this->assertFalse($found);
    }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive3()
    {
        $router = new Router('/House/fLoOr/2/bedrOom');
        $router->map('/House/fLoOr/2/bedrOom', array('module' => 'MyHouseModel'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'MyHouseModel');
        $this->assertEquals($router->get('action'), 'index');
        $this->assertTrue($found);
    }
}
?>
