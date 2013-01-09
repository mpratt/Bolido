<?php
/**
 * TestHooks.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class HookableClass
{
    public function dummy() {}
    public function addFive($value) { return ($value + 5); }
    public function dummy3() {}
}

class TestHooks extends PHPUnit_Framework_TestCase
{
    protected $hooks = array();

    /**
     * Setup the environment
     */
    public function setUp() { $this->hooks = glob(__DIR__ . '/../Workspace/hooks/*.php'); }

    /**
     * Tests that the Hook object uses finds files inside the path correctly
     */
    public function testFindHookFile()
    {
        $hooks = new \Bolido\Hooks(array(__DIR__ . '/../Workspace/hooks/HookDummy1.php'));
        $this->assertEquals(count($hooks->listTriggers()), 5);
    }

    /**
     * Tests that the Hook object uses finds files inside the path correctly
     */
    public function testFindHookFile2()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $this->assertEquals(count($hooks->listTriggers()), 9);
    }

    /**
     * Tests that the Hook object calls triggers
     */
    public function testCalledTriggers()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->run('dummy_trigger_no_return');
        $hooks->run('dummy_trigger_no_return2');
        $hooks->run('dummy_unexistant_trigger');

        $this->assertEquals(count($hooks->calledTriggers()), 2);
    }

    /**
     * Tests that the Hook object calls triggers with int parameters
     */
    public function testTriggerWithIntParameters()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_int', 5);
        $this->assertEquals($output, 12);
    }

    /**
     * Tests that the Hook object calls triggers with array parameters
     */
    public function testTriggerWithArrayParameters()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_array', array('first'));
        $this->assertEquals(count($output), 2);
        $this->assertEquals($output, array('first', 'second'));
    }

    /**
     * Tests that the Hook object calls triggers with string parameters
     */
    public function testTriggerWithStringParameters()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_string', 'I Love The Bolido Framework');
        $this->assertEquals($output, 'I Love turtles');
    }

    /**
     * Tests that the Hook object calls triggers with object parameters
     */
    public function testTriggerWithObjectParameters()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_object', new HookableClass());
        $this->assertInstanceOf('HookableClass', $output);
    }

    /**
     * Tests that the Run method returns a value of the same type of the first parameter
     */
    public function testReturnTypeConsistency()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_return_string', array('hi'));
        $this->assertEquals(gettype($output), 'array');
        $this->assertEquals($output, array('hi'));
    }

    /**
     * Tests that the Run method returns a value of the same type of the first parameter
     */
    public function testReturnTypeConsistency2()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        $object = (object) array('1', '2', '3');
        $output = $hooks->run('dummy_trigger_return_array', $object);
        $this->assertEquals(gettype($output), 'object');
        $this->assertEquals($output, $object);
    }

    /**
     * Tests that the Hook object respects the hook position key
     */
    public function testHooksOrder()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run('dummy_trigger_call_order', array());
        $this->assertEquals($output, array('1', '2', '3'));
    }

    /**
     * Tests that the Hook object respects the hook position key
     */
    public function testHooksOrder2()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(function($a) { $a[] = 4; return $a; }, 'dummy_trigger_call_order', 'test', 40);

        $output = $hooks->run('dummy_trigger_call_order', array());
        $this->assertEquals($output, array('1', '2', '3', '4'));
    }

    /**
     * Tests that the Hook object respects the hook position key
     */
    public function testHooksOrder3()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(function($a) { $a[] = -1; return $a; }, 'dummy_trigger_call_order', 'test', '-100');

        $output = $hooks->run('dummy_trigger_call_order', array());
        $this->assertEquals($output, array('-1', '1', '2', '3'));
    }

    /**
     * Tests that the Hook object respects the hook position key
     */
    public function testHooksOrder4()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(function($a) { $a[] = 1; return $a; }, 'dummy_trigger_call_order_fly', 'test', 0);
        $hooks->append(function($a) { $a[] = 2; return $a; }, 'dummy_trigger_call_order_fly', 'test', 0);
        $hooks->append(function($a) { $a[] = -1; return $a; }, 'dummy_trigger_call_order_fly', 'test',  -100);
        $hooks->append(function($a) { $a[] = 3; return $a; }, 'dummy_trigger_call_order_fly', 'test', 3);

        $output = $hooks->run('dummy_trigger_call_order_fly', array());
        $this->assertEquals($output, array('-1', '1', '2', '3'));
    }
    /**
     * Tests that the Hook object is capable of dynamically register functions
     */
    public function testAppendCapabilites()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        try {
            $hooks->append('fake_function', 'dummy_trigger_new_trigger');
        } catch(\Exception $e) {}

        $this->assertEquals(count($hooks->listTriggers()), 9);
    }

    /**
     * Tests how the Hook object behaves when registering two identical hooks (Result: It does the same thing twice)
     */
    public function testAppendCapabilities2()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(function($v) { return ($v - 5); }, 'dummy_created');
        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 0);
    }

    /**
     * Tests that the Hook object can remove functions by name
     */
    public function testRemoveFunction()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->clearModuleTriggers('test', 'dummy_trigger_int');
        $output = $hooks->run('dummy_trigger_int', 5);
        $this->assertEquals($output, 6);
    }

    /**
     * Tests that the Hook object can remove triggers
     */
    public function testRemoveTrigger()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->clearTrigger('dummy_trigger_no_return');
        $this->assertEquals(count($hooks->listTriggers()), 8);
    }

    /**
     * Tests that the Hook object can remove based on the module
     */
    public function testRemoveByModule()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->clearModuleTriggers('test');
        $this->assertEquals(count($hooks->listTriggers()), 2);

        $hooks->clearModuleTriggers('main');
        $this->assertEquals(count($hooks->listTriggers()), 0);
    }

    /**
     * Tests that the Hook object can detect what to do when an a class and method are passed
     */
    public function testHookRunnerInstantiate()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        $hooks->append(array('HookableClass', 'addFive'), 'dummy_created', 'main');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);
    }

    /**
     * Tests that the Hook object can detect what to do when an object and a method are passed
     */
    public function testHookRunnerPassObject()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created', 'main');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);
    }

    /**
     * Tests how the Hook object behaves if a non callable method/function is passed
     */
    public function testHookRunnerUnknownFunctionMethod()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        try {
            $hooks->append(array(new HookableClass(), 'nonexistantMethod'), 'dummy_created', 'main');
        } catch(\Exception $e) {}

        try {
            $hooks->append(array('HookableClass', 'OtherNonexistantMethod'), 'dummy_created', 'main');
        } catch(\Exception $e) {}

        try {
            $hooks->append('nonexistantFunction', 'dummy_created', 'main');
        } catch(\Exception $e) {}

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 5);
    }

    /**
     * Tests how the Hook object behaves when registering two identical hooks (Result: It does the same thing twice)
     */
    public function testUncommonScenario()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');
        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 15);
    }

    /**
     * Tests how the Hook object behaves when the run method is called without
     * any parameters.
     */
    public function testUncommonScenario2()
    {
        $this->setExpectedException('Exception');
        $hooks = new \Bolido\Hooks($this->hooks);
        $output = $hooks->run();
    }

    /**
     * Tests how the Hook object behaves when invalid data is being
     * appended
     */
    public function testUncommonScenario3()
    {
        $hooks = new \Bolido\Hooks($this->hooks);

        try {
            $hooks->append(array(new HookableClass(), array('addFive')), 'dummy_created');
        } catch(\Exception $e) {}

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 5);

        $output = $hooks->run('dummy_trigger_invalid_call', 10);
        $this->assertEquals($output, 10);
    }

    /**
     * Tests how the Hook object behaves when the same hook is runed
     * twice
     */
    public function testUncommonScenario4()
    {
        $hooks = new \Bolido\Hooks($this->hooks);
        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);

        $output = $hooks->run('dummy_created', $output);
        $this->assertEquals($output, 15);
    }

}
?>
