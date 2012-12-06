<?php
/**
 * HookDummy2.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

/**
 * HookDummy1.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

$hooks['dummy_trigger_no_return'][] = array('from_module' => 'test',
                                            'position' => 5,
                                            'requires' => __FILE__,
                                            'call' => 'testFunctionNoReturn2');

$hooks['dummy_trigger_int'][] = array('from_module' => 'main',
                                      'position' => 5,
                                      'requires' => __FILE__,
                                      'call' => 'testAddOne');

$hooks['dummy_trigger_object'][] = array('from_module' => 'test',
                                         'position' => 2,
                                         'requires' => __FILE__,
                                         'call' => 'testParamObjects');

$hooks['dummy_trigger_return_string'][] = array('from_module' => 'test',
                                                'position' => 0,
                                                'requires' => __FILE__,
                                                'call' => 'testReturnStringAlways');

$hooks['dummy_trigger_return_array'][] = array('from_module' => 'test',
                                               'position' => 0,
                                               'requires' => __FILE__,
                                               'call' => 'testReturnArrayAlways');

$hooks['dummy_trigger_call_order'][] = array('from_module' => 'test',
                                             'position' => 5,
                                             'requires' => __FILE__,
                                             'call' => 'testCallOrder2');

$hooks['dummy_trigger_call_order'][] = array('from_module' => 'test',
                                             'position' => 1,
                                             'requires' => __FILE__,
                                             'call' => 'testCallOrder1');

$hooks['dummy_trigger_call_order'][] = array('from_module' => 'test',
                                             'position' => 8,
                                             'requires' => __FILE__,
                                             'call' => 'testCallOrder3');

if (!function_exists('testFunctionNoReturn2'))
{
    function testFunctionNoReturn2() {}
    function testParamObjects($object)
    {
        $object->dummy();
        return $object;
    }

    function testReturnStringAlways($value)
    {
        return 'This is a String';
    }

    function testReturnArrayAlways($value)
    {
        return array('this is an array');
    }

    function testCallOrder1($array)
    {
        $array[] = 1;
        return $array;
    }

    function testCallOrder2($array)
    {
        $array[] = 2;
        return $array;
    }

    function testCallOrder3($array)
    {
        $array[] = 3;
        return $array;
    }

    function testAddOne($value)
    {
        return ($value + 1);
    }
}

?>
