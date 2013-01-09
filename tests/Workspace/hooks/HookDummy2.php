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

$this->append(function () {}, 'dummy_trigger_no_return', 'test', 5);
$this->append(function ($s) { return ($s + 1); }, 'dummy_trigger_int', 'main', 5);
$this->append(function ($o) { $o->dummy(); return $o; }, 'dummy_trigger_object', 'test', 2);
$this->append(function ($s) { return 'This is a String'; }, 'dummy_trigger_return_string', 'test');
$this->append(function ($a) { return array('This is an array'); }, 'dummy_trigger_return_array', 'test');
$this->append(function ($a) { $a[] = 2; return $a; }, 'dummy_trigger_call_order', 'test', 0);
$this->append(function ($a) { $a[] = 1; return $a; }, 'dummy_trigger_call_order', 'test', -99);
$this->append(function ($a) { $a[] = 3; return $a; }, 'dummy_trigger_call_order', 'test', 5);
?>
