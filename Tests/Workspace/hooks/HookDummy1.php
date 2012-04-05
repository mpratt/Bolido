<?php
/**
 * HookDummy1.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

$hooks['dummy_trigger_no_return'][] = array('from_module' => 'main',
                                    'position' => 5,
                                    'requires' => __FILE__,
                                    'call' => 'testFunctionNoReturn1');

$hooks['dummy_trigger_no_return'][] = array('from_module' => 'main',
                                    'position' => 4,
                                    'requires' => __FILE__,
                                    'call' => 'testFunctionNoReturn1');


$hooks['dummy_trigger_no_return2'][] = array('from_module' => 'test',
                                             'position' => 2,
                                             'requires' => __FILE__,
                                             'call' => 'testFunctionNoReturn1');

$hooks['dummy_trigger_string'][] = array('from_module' => 'test',
                                         'position' => 0,
                                         'requires' => __FILE__,
                                         'call' => 'testReplaceString');

$hooks['dummy_trigger_int'][] = array('from_module' => 'test',
                                    'position' => 0,
                                    'requires' => __FILE__,
                                    'call' => 'testAddSix');

$hooks['dummy_trigger_array'][] = array('from_module' => 'test',
                                        'position' => 0,
                                        'requires' => __FILE__,
                                        'call' => 'testAppendToArray');

function testFunctionNoReturn1() {}

function testReplaceString($string)
{
    return str_ireplace('The Bolido Framework', 'turtles', $string);
}

function testAddSix($value)
{
    return ($value + 6);
}

function testAppendToArray($array)
{
    $array[] = 'second';
    return $array;
}

?>
