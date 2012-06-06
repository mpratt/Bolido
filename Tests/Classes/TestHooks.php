<?php
/**
 * TestHooks.php
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
    define('BOLIDO', 'TestHooks');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Interfaces/iCache.interface.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/FileCache.class.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/Hooks.class.php');

class HookableClass
{
    public function dummy() {}
    public function addFive($value) { return ($value + 5); }
    public function dummy3() {}
}

class TestHooks extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that the Hook object uses finds files inside the path correctly
     */
    public function testFindHookFile()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*1.php', $cache);

        $this->assertEquals(count($hooks->listTriggers()), 5);
    }

    /**
     * Tests that the Hook object uses finds files inside the path correctly
     */
    public function testFindHookFile2()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $this->assertEquals(count($hooks->listTriggers()), 9);
    }

    /**
     * Tests that the Hook object calls triggers
     */
    public function testCalledTriggers()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

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
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_int', 5);
        $this->assertEquals($output, 12);
    }

    /**
     * Tests that the Hook object calls triggers with array parameters
     */
    public function testTriggerWithArrayParameters()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_array', array('first'));
        $this->assertEquals(count($output), 2);
        $this->assertEquals($output, array('first', 'second'));
    }

    /**
     * Tests that the Hook object calls triggers with string parameters
     */
    public function testTriggerWithStringParameters()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_string', 'I Love The Bolido Framework');
        $this->assertEquals($output, 'I Love turtles');
    }

    /**
     * Tests that the Hook object calls triggers with object parameters
     */
    public function testTriggerWithObjectParameters()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_object', new HookableClass());
        $this->assertInstanceOf('HookableClass', $output);
    }

    /**
     * Tests that the Run method returns a value of the same type of the first parameter
     */
    public function testReturnTypeConsistency()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_return_string', array('hi'));
        $this->assertEquals(gettype($output), 'array');
        $this->assertEquals($output, array('hi'));
    }

    /**
     * Tests that the Run method returns a value of the same type of the first parameter
     */
    public function testReturnTypeConsistency2()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_return_array', $cache);
        $this->assertEquals(gettype($output), 'object');
        $this->assertEquals($output, $cache);
    }

    /**
     * Tests that the Hook object respects the hook position key
     */
    public function testHooksOrder()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $output = $hooks->run('dummy_trigger_call_order', array());
        $this->assertEquals($output, array('1', '2', '3'));
    }

    /**
     * Tests that the Hook object is capable of dynamically register functions
     */
    public function testAppendCapabilites()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->append(array('from_module' => 'main',
                             'call' => 'fake_function'), 'dummy_trigger_new_trigger');

        $this->assertEquals(count($hooks->listTriggers()), 10);
    }

    /**
     * Tests that the Hook object can remove functions by name
     */
    public function testRemoveFunction()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->removeFunction('testAddSix');
        $output = $hooks->run('dummy_trigger_int', 5);
        $this->assertEquals($output, 6);
    }

    /**
     * Tests that the Hook object can remove triggers
     */
    public function testRemoveTrigger()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->removeTrigger('dummy_trigger_no_return');
        $this->assertEquals(count($hooks->listTriggers()), 8);
    }

    /**
     * Tests that the Hook object can remove based on the module
     */
    public function testRemoveByModule()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->removeModuleHooks('test');
        $this->assertEquals(count($hooks->listTriggers()), 1);

        $hooks->removeModuleHooks('main');
        $this->assertEquals(count($hooks->listTriggers()), 1);
    }

    /**
     * Tests that the Hook object can detect what to do when an a class and method are passed
     */
    public function testHookRunnerInstantiate()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->append(array('from_module' => 'main',
                             'call' => array('HookableClass', 'addFive')), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);
    }

    /**
     * Tests that the Hook object can detect what to do when an object and a method are passed
     */
    public function testHookRunnerPassObject()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->append(array('from_module' => 'main',
                             'call' => array(new HookableClass(), 'addFive')), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);
    }

    /**
     * Tests how the Hook object behaves if a non callable method/function is passed
     */
    public function testHookRunnerUnknownFunctionMethod()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->append(array('from_module' => 'main',
                             'call' => array(new HookableClass(), 'nonexistantMethod')), 'dummy_created');

        $hooks->append(array('from_module' => 'main',
                             'call' => 'nonexistantFunction'), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 5);
    }

    /**
     * Tests how the Hook object behaves when registering two identical hooks (Result: It does the same thing twice)
     */
    public function testUncommonScenrio()
    {
        $cache = $this->getMockBuilder('FileCache')->disableOriginalConstructor()->getMock();
        $hooks = new Hooks(dirname(__FILE__) . '/../Workspace/hooks/*.php', $cache);

        $hooks->append(array('from_module' => 'main',
                             'call' => array(new HookableClass(), 'addFive')), 'dummy_created');

        $hooks->append(array('from_module' => 'main',
                             'call' => array(new HookableClass(), 'addFive')), 'dummy_created');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 15);
    }
}
?>
