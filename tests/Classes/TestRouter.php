<?php
/**
 * TestRouter.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../vendor/Bolido/Router.php');
class TestRouter extends PHPUnit_Framework_TestCase
{
    /**
     * Test Default Routes
     */
    public function testDefaultRoutes()
    {
        $router = new \Bolido\Router('GET');
        $router->find('/');

        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->module, 'main');
        $this->assertEquals($router->controller, 'Controller');
    }

    /**
     * Test Modified Main Module
     */
    public function testDefaultRoutes2()
    {
        $router = new \Bolido\Router('GET', 'MainModuleName');
        $router->find('/');

        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->module, 'MainModuleName');
        $this->assertEquals($router->controller, 'Controller');
    }

    /**
     * Test Module and Actions detection
     */
    public function testDefaultRoutes3()
    {
        // Test actions
        $router = new \Bolido\Router('GET');
        $router->find('/moduleName/ActionName');

        $this->assertEquals($router->action, 'ActionName');
        $this->assertEquals($router->module, 'moduleName');
        $this->assertEquals($router->controller, 'Controller');
    }

    /**
     * Test Module, Actions and subModule detection
     */
    public function testDefaultRoutes4()
    {
        $router = new \Bolido\Router('GET');
        $router->find('/moduleName/CustomController/ActionName');

        $this->assertEquals($router->controller, 'CustomController');
        $this->assertEquals($router->action, 'ActionName');
        $this->assertEquals($router->module, 'moduleName');
    }

    /**
     * Test basic mapping with module overwritting
     */
    public function testRouterMappings()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[a:action]/', array('module' => 'bookings'));
        $found = $router->find('/bookings/reserve');

        $this->assertEquals($router->module, 'bookings');
        $this->assertEquals($router->action, 'reserve');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertTrue($found);
    }

    /**
     * Test Mappings with module overwritting and custom parameter
     */
    public function testRouterMappings2()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[i:id]/', array('module' => 'bookings'));
        $found = $router->find('/bookings/90');

        $this->assertEquals($router->module, 'bookings');
        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertEquals($router->id, '90');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping with invalid int parameter
     */
    public function testRouterMappings3()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[i:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find('/bookings/notnumeric/airport/airplane/');

        $this->assertFalse($router->id);
        $this->assertFalse($found);
    }

    /**
     * Test Mapping with invalid hex parameter
     */
    public function testRouterMappings4()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[h:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find('/bookings/nothex/airport/airplane/');

        $this->assertFalse($found);
        $this->assertFalse($router->id);
    }

    /**
     * Test Mapping with invalid/corrupted path
     */
    public function testRouterMappings5()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[a:id]/airport/airplane', array('module' => 'bookings'));
        $found = $router->find('/bookings/corrupte  /  d?&^url/airport/airplane');

        $this->assertFalse($router->id);
        $this->assertFalse($found);
    }

    /**
     * Test Mapping preference depending on matched regex
     */
    public function testRouterMappings6()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[i:id]', array('module' => 'bookings', 'action' => 'fetchId'));
        $router->map('/bookings/[h:id]', array('module' => 'bookings'));
        $found = $router->find('/bookings/12345/');

        $this->assertEquals($router->module, 'bookings');
        $this->assertEquals($router->action, 'fetchId');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertEquals($router->id, '12345');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping preference depending on mtched regex
     */
    public function testRouterMappings7()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/bookings/[i:id]', array('module' => 'bookings', 'action' => 'fetchId'));
        $router->map('/bookings/[h:id]', array('module' => 'bookings'));
        $found = $router->find('/bookings/afbe/');

        $this->assertEquals($router->module, 'bookings');
        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertEquals($router->id, 'afbe');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping and matched conditions
     */
    public function testRouterMappings8()
    {
        $router = new \Bolido\Router('GET');
        $router->setMainModule('defaultModuleName');
        $router->map('/bolido-framework/page/[i:id]', array('action' => 'fetchId', 'controller' => 'MyController'));
        $found = $router->find('/bolido-framework/page/40/');

        $this->assertEquals($router->module, 'defaultModuleName');
        $this->assertEquals($router->controller, 'MyController');
        $this->assertEquals($router->action, 'fetchId');
        $this->assertEquals($router->id, '40');
        $this->assertTrue($found);
    }

    /**
     * Test Mapping and matched conditions
     */
    public function testRouterMappings9()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/history/[a:country]/presidents/[i:year]/[a:name]/', array('action' => 'getPresidents'));
        $found = $router->find('/history/colombia/presidents/1998/Ernesto-SampeR');

        $this->assertEquals($router->module, 'main');
        $this->assertEquals($router->action, 'getPresidents');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertEquals($router->year, '1998');
        $this->assertEquals($router->country, 'colombia');
        $this->assertEquals($router->name, 'Ernesto-SampeR');
        $this->assertTrue($found);
    }

    /**
     * Test If you can map a rule twice
     */
    public function testRouterMapOverwrite()
    {
        $router = new \Bolido\Router('GET');

        try
        {
            $router->map('/House/flOor/2/BedRooM');
            $this->assertTrue($router->find('/House/flOor/2/BedRoom'));

            $router->map('/House/flOor/2/BedRooM');

        } catch (Exception $expected) { return ; }

        $this->fail('TestRouterMapOverwrite expects an exception');
    }

    /**
     * Test for mapping exceptions
     */
    public function testRouterMapOverwrite2()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/House/flOor/2/BedRooM');
        $router->map('/House/flOor/2/BedRooM', array(), 'get', true);
        $router->map('/House/flOor/2/BedROOM');

        $router->map('/House/flOor/2/[i:id]', array('action' => 'getInt'));
        $router->map('/House/flOor/2/[i:id]', array('action' => 'overwriteInt'), 'GET', true);

        $this->assertTrue($router->find('/House/flOor/2/BedRooM'));
   }

    /**
     * Test that the\Bolido\Router is case sensitive
     */
    public function testRouterCaseSensitive()
    {
        $router = new \Bolido\Router('GET');
        $router->find('/ModuleName/ActionName');

        $this->assertFalse(($router->module == 'modulename'));
    }

    /**
     * Test that the\Bolido\Router is case sensitive
     */
    public function testRouterCaseSensitive2()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/House/flOor/2/BedRooM', array('module' => 'MyHouseModel'));
        $found = $router->find('/house/floor/2/bedroom/');

        $this->assertFalse($found);
    }

    /**
     * Test that the\Bolido\Router is case sensitive
     */
    public function testRouterCaseSensitive3()
    {
        $router = new \Bolido\Router('GET');
        $router->map('/House/fLoOr/2/bedrOom', array('module' => 'MyHouseModel'));
        $found = $router->find('/House/fLoOr/2/bedrOom');

        $this->assertEquals($router->module, 'MyHouseModel');
        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertTrue($found);
    }

    /**
     * Test that the\Bolido\Router request methods
     */
    public function testRouterMethod()
    {
        $router = new \Bolido\Router('POST');
        $router->map('/queens/of/the/stone/age/', array('module' => 'MyHouseModel'), 'GET');
        $found = $router->find('/queens/of/the/stone/age/');

        $this->assertFalse($found);
    }

    /**
     * Test that the\Bolido\Router request methods
     */
    public function testRouterMethod1()
    {
        $router = new \Bolido\Router('POST');
        $router->map('/smodcast/feed', array('module' => 'smodcastGET'), 'GET');
        $router->map('/smodcast/feed', array('module' => 'smodcastPOST'), 'POST');
        $found = $router->find('/smodcast/feed');

        $this->assertEquals($router->module, 'smodcastPOST');
        $this->assertEquals($router->action, 'index');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertTrue($found);
    }

    /**
     * Test that the\Bolido\Router request methods
     */
    public function testRouterMethod2()
    {
        $router = new \Bolido\Router('DELETE');
        $router->map('/smodcast/feed', array('module' => 'smodcastGET'), 'GET');
        $router->map('/smodcast/feed', array('module' => 'smodcastPOST'), 'POST');
        $router->map('/smodcast/feed', array('module' => 'smodcastDELETE', 'action' => 'deleteStuff'), 'DELETE');
        $found = $router->find('/smodcast/feed');

        $this->assertEquals($router->module, 'smodcastDELETE');
        $this->assertEquals($router->action, 'deleteStuff');
        $this->assertEquals($router->controller, 'Controller');
        $this->assertTrue($found);
    }

    /**
     * Test that the\Bolido\Router request methods
     */
    public function testRouterMethod3()
    {
        $router = new \Bolido\Router('DELETE');
        $router->map('/smodcast/popcorn/house/rock/', array('module' => 'smodcastGET'), 'POST');
        $found = $router->find('/smodcast/popcorn/house/rock');
        $this->assertFalse($found);
    }
}
?>
