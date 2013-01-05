<?php
/**
 * HookDummy1.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

$this->append(function () {}, 'dummy_trigger_no_return', 'main', 5);
$this->append(function () {}, 'dummy_trigger_no_return', 'main', 4);
$this->append(function () {}, 'dummy_trigger_no_return2', 'test', 2);
$this->append(function ($string) { return str_ireplace('The Bolido Framework', 'turtles', $string); }, 'dummy_trigger_string', 'test');
$this->append(function($v){ return ($v + 6); }, 'dummy_trigger_int', 'test', 0);
$this->append(function($a) { $a[] = 'second'; return $a; }, 'dummy_trigger_array', 'test');
?>
