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
        $router = new Router('/', 'GET');
        $router->find();

        $this->assertEquals($router->get('action'), 'index');
        $this->assertEquals($router->get('module'), 'home');
    }

    /**
     * Test Modified Main Module
     */
    public function testDefaultRoutes2()
    {
        $router = new Router('/', 'GET', 'MainModuleName');
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
        $router = new Router('/moduleName/ActionName', 'GET');
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
        $router = new Router('/moduleName/ProcessName/ActionName', 'GET');
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
        $router = new Router('/bookings/reserve/', 'GET');
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
        $router = new Router('/bookings/90', 'GET');
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
        $router = new Router('/bookings/notnumeric/airport/airplane', 'GET');
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
        $router = new Router('/bookings/nothex/airport/airplane', 'GET');
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
        $router = new Router('/bookings/corrupte  /  d?&^url/airport/airplane', 'GET');
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
        $router = new Router('/bookings/12345', 'GET');
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
        $router = new Router('/bookings/afbe', 'GET');
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
        $router = new Router('/bolido-framework/page/40', 'GET');
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
        $router = new Router('/history/colombia/presidents/1998/Ernesto-SampeR', 'GET');
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
     * Test If you can map a rule twice
     */
    public function testRouterMapOverwrite()
    {
        $router = new Router('/', 'GET');

        try
        {
            $router->map('/House/flOor/2/BedRooM');
            $router->map('/House/flOor/2/BedRooM');

        } catch (Exception $expected) { return ; }

        $this->fail('TestRouterMapOverwrite expects an exception');
    }

    /**
     * Test for mapping exceptions
     */
    public function testRouterMapOverwrite2()
    {
        $router = new Router('/', 'GET');

        $router->map('/House/flOor/2/BedRooM');
        $router->map('/House/flOor/2/BedRooM', array(), '', true);
        $router->map('/House/flOor/2/BedROOM');

        $router->map('/House/flOor/2/[i:id]', array('action' => 'getInt'));
        $router->map('/House/flOor/2/[i:id]', array('action' => 'overwriteInt'), true);
   }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive()
    {
        $router = new Router('/ModuleName/ActionName', 'GET');
        $router->find();

        $this->assertFalse(($router->get('module') == 'modulename'));
    }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive2()
    {
        $router = new Router('/house/floor/2/bedroom', 'GET');
        $router->map('/House/flOor/2/BedRooM', array('module' => 'MyHouseModel'));
        $found = $router->find();

        $this->assertFalse($found);
    }

    /**
     * Test that the router is case sensitive
     */
    public function testRouterCaseSensitive3()
    {
        $router = new Router('/House/fLoOr/2/bedrOom', 'GET');
        $router->map('/House/fLoOr/2/bedrOom', array('module' => 'MyHouseModel'));
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'MyHouseModel');
        $this->assertEquals($router->get('action'), 'index');
        $this->assertTrue($found);
    }

    /**
     * Test that the router request methods
     */
    public function testRouterMethod()
    {
        $router = new Router('/smodcast/feed', 'POST');
        $router->map('/smodcast/feed', array('module' => 'MyHouseModel'), 'GET');
        $found = $router->find();

        $this->assertFalse($found);
    }

    /**
     * Test that the router request methods
     */
    public function testRouterMethod1()
    {
        $router = new Router('/smodcast/feed', 'POST');
        $router->map('/smodcast/feed', array('module' => 'smodcastGET'), 'GET');
        $router->map('/smodcast/feed', array('module' => 'smodcastPOST'), 'POST');
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'smodcastPOST');
        $this->assertEquals($router->get('action'), 'index');
        $this->assertTrue($found);
    }

    /**
     * Test that the router request methods
     */
    public function testRouterMethod2()
    {
        $router = new Router('/smodcast/feed', 'DELETE');
        $router->map('/smodcast/feed', array('module' => 'smodcastGET'), 'GET');
        $router->map('/smodcast/feed', array('module' => 'smodcastPOST'), 'POST');
        $router->map('/smodcast/feed', array('module' => 'smodcastDELETE', 'action' => 'deleteStuff'), 'DELETE');
        $found = $router->find();

        $this->assertEquals($router->get('module'), 'smodcastDELETE');
        $this->assertEquals($router->get('action'), 'deleteStuff');
        $this->assertTrue($found);
    }

    /**
     * Test that the router request methods
     */
    public function testRouterMethod3()
    {
        $router = new Router('/smodcast/feed', 'UNKNOWN');
        $router->map('/smodcast/feed', array('module' => 'smodcastGET'), 'UNKNOWN');
        $found = $router->find();

        $this->assertFalse($found);
    }
}
?>
