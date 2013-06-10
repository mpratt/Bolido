<?php
/**
 * TestHooks.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class HookableClass
{
    public $p = 0;
    public function __construct($int = 0) { $this->p = $int; }
    public function addFive($value) { return ($value + 5); }
    public function increaseProperty() { $this->p += 1; }
}

class TestHooks extends PHPUnit_Framework_TestCase
{
    protected $hooks = array();

    /**
     * Sets up some default hooks for testing
     */
    public function appendHooks($hooks)
    {
        $hooks->append(function ($a) { $a[] = 1; return $a; }, 'order_call', 'order_module', -99);
        $hooks->append(function ($a) { $a[] = 2; return $a; }, 'order_call', 'order_module', 0);
        $hooks->append(function ($a) { $a[] = 3; return $a; }, 'order_call', 'order_module', 5);

        $hooks->append(function () {}, 'no_return', 'return_module');
        $hooks->append(function () {}, 'no_return', 'return_module');

        $hooks->append(function () { return 'string'; }, 'return_string', 'return_string_module');

        $hooks->append(function ($s) { return str_ireplace('The Bolido Framework', 'turtles', $s); }, 'replace', 'replace_module');

        $hooks->append(function ($s) { return ($s + 1); }, 'add_int', 'add_int_module');

        $hooks->append(function($a) { $a[] = 'other'; return $a; }, 'add_to_array', 'add_to_array_module');
    }

    public function testFindHookFile()
    {
        $hooks = new \Bolido\Hooks(array(BASE_DIR . '/../modules/main/hooks/Hooks.php'));
        $headers = $hooks->run('modify_http_headers', array());
        $this->assertTrue((count($headers) > 0));

        $lang = $hooks->run('modify_lang', new MockLang());
        $this->assertTrue(is_object($lang));
    }

    public function testCalledTriggers()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->run('order_call');
        $this->assertCount(1, $hooks->calledTriggers());

        $hooks->run('order_call');
        $this->assertCount(1, $hooks->calledTriggers());

        $hooks->run('unknown_hook');
        $this->assertCount(1, $hooks->calledTriggers());
    }

    public function testTriggerWithIntParameters()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('add_int', 5);
        $this->assertEquals($output, 6);
    }

    public function testTriggerWithArrayParameters()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('add_to_array', array('first'));
        $this->assertEquals(count($output), 2);
        $this->assertEquals($output, array('first', 'other'));
    }

    public function testTriggerWithStringParameters()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('replace', 'I Love The Bolido Framework');
        $this->assertEquals($output, 'I Love turtles');
    }

    public function testUnknownTriggerWithObjectParameters()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('unknown_hook', new HookableClass());
        $this->assertInstanceOf('HookableClass', $output);
    }

    public function testKnownTriggerWithObjectParameters()
    {
        $hooks = new \Bolido\Hooks();

        $hooks->append(function ($o) {
            $o->increaseProperty();
        }, 'modify_object');

        $output = $hooks->run('modify_object', new HookableClass(2));

        $this->assertInstanceOf('HookableClass', $output);
        $this->assertEquals($output->p, 3);
    }

    public function testReturnTypeConsistency()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('return_string', array('hi'));

        $this->assertEquals(gettype($output), 'array');
        $this->assertEquals($output, array('hi'));
    }

    public function testReturnTypeConsistency2()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $object = (object) array('1', '2', '3');
        $output = $hooks->run('return_string', $object);

        $this->assertEquals(gettype($output), 'object');
        $this->assertEquals($output, $object);
    }

    public function testReturnConsistency()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->append(function($p1, $p2, $p3){ $p1[] = 2; return $p1; }, 'dummy_hook');

        $output = $hooks->run('dummy_hook', array('1'), 'second param', 'third param');

        $this->assertEquals(gettype($output), 'array');
        $this->assertEquals($output, array('1', '2'));
    }

    public function testHooksOrder()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('order_call', array());
        $this->assertEquals($output, array('1', '2', '3'));
    }

    public function testHooksOrder2()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->append(function($a) { $a[] = 4; return $a; }, 'order_call', 'temp', 90);

        $output = $hooks->run('order_call', array());
        $this->assertEquals($output, array('1', '2', '3', '4'));

        $hooks->append(function($a) { $a[] = -1; return $a; }, 'order_call', 'temp', -9999);
        $output = $hooks->run('order_call', array());

        $this->assertEquals($output, array('-1', '1', '2', '3', '4'));

        $hooks->append(function($a) { $a[] = '3/2'; return $a; }, 'order_call', 'temp', 0);
        $output = $hooks->run('order_call', array());

        $this->assertEquals($output, array('-1', '1', '2', '3/2', '3', '4'));
    }

    public function testHooksOrder3()
    {
        $hooks = new \Bolido\Hooks();

        $hooks->append(function($a) { $a[] = 1; return $a; }, 'order_call_fly', 'test', 0);
        $hooks->append(function($a) { $a[] = 2; return $a; }, 'order_call_fly', 'test', 0);
        $hooks->append(function($a) { $a[] = 3; return $a; }, 'order_call_fly', 'test', 3);
        $hooks->append(function($a) { $a[] = -1; return $a; }, 'order_call_fly', 'test',  -100);

        $output = $hooks->run('order_call_fly', array());

        $this->assertEquals($output, array('-1', '1', '2', '3'));
    }

    public function testAppendCapabilites()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        try {
            $hooks->append('fake_function', 'dummy_trigger_new_trigger');
            $this->failed('This should had failed!! Since we gave an uncallable function name');
        } catch(\Exception $e) {}

        $this->assertEquals('string', $hooks->run('return_string'));
    }

    public function testAppendCapabilities2()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->append(function($v) { return ($v - 5); }, 'dummy_created');
        $output = $hooks->run('dummy_created', 5);

        $this->assertEquals($output, 0);
    }

    public function testRemoveFunction()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->removeModule('add_int_module');
        $output = $hooks->run('add_int', 5);

        $this->assertEquals($output, 5);
    }

    public function testRemoveTrigger()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $hooks->run('no_return');
        $this->assertCount(1, $hooks->calledTriggers());

        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);
        $hooks->removeTrigger('no_return');

        $hooks->run('no_return');
        $this->assertCount(0, $hooks->calledTriggers());
    }

    public function testRemoveByModule()
    {
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run('add_int', 5);
        $this->assertEquals($output, 6);

        $hooks->removeModuleByTrigger('add_int_module', 'add_int');
        $output = $hooks->run('add_int', 5);
        $this->assertEquals($output, 5);

        $output = $hooks->run('return_string');
        $this->assertEquals($output, 'string');
    }

    public function testHookRunnerPassObject()
    {
        $hooks = new \Bolido\Hooks();

        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created', 'main');

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 10);
    }

    public function testHookRunnerUnknownFunctionMethod()
    {
        $hooks = new \Bolido\Hooks();

        try {
            $hooks->append(array(new HookableClass(), 'nonexistantMethod'), 'dummy_created', 'main');
            $this->failed('This should had failed!! Since we gave an uncallable method name');
        } catch(\Exception $e) {}

        try {
            $hooks->append(array('HookableClass', 'OtherNonexistantMethod'), 'dummy_created', 'main');
            $this->failed('This should had failed!! Since we gave an uncallable method name');
        } catch(\Exception $e) {}

        try {
            $hooks->append('nonexistantFunction', 'dummy_created', 'main');
            $this->failed('This should had failed!! Since we gave an uncallable method name');
        } catch(\Exception $e) {}

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 5);
    }

    public function testUncommonScenario()
    {
        $hooks = new \Bolido\Hooks();

        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');
        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');

        /**
         * Tests how the Hook object behaves when registering two identical hooks (Result: It does the same thing twice)
         */
        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 15);
    }

    public function testUncommonScenario2()
    {
        $this->setExpectedException('InvalidArgumentException');
        $hooks = new \Bolido\Hooks();
        $this->appendHooks($hooks);

        $output = $hooks->run();
    }

    public function testUncommonScenario3()
    {
        $hooks = new \Bolido\Hooks();

        try {
            $hooks->append(array(new HookableClass(), array('addFive')), 'dummy_created');
        } catch(\Exception $e) {}

        $output = $hooks->run('dummy_created', 5);
        $this->assertEquals($output, 5);

        $output = $hooks->run('dummy_trigger_invalid_call', 10);
        $this->assertEquals($output, 10);
    }

    public function testUncommonScenario4()
    {
        $hooks = new \Bolido\Hooks();

        $hooks->append(array(new HookableClass(), 'addFive'), 'dummy_created');

        $output = $hooks->run('dummy_created', $hooks->run('dummy_created', 5));
        $this->assertEquals($output, 15);
    }
}

?>
