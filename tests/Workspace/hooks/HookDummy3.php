<?php
/**
 * HookDummy3.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

/**
 * HookDummy3.php
 * Test/Example hook File
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

$hooks['dummy_trigger_invalid_call'][] = array('from_module' => 'test',
                                               'position' => 5,
                                               'call' => array('TestConfig', 'NonExistantMethod'));
?>
