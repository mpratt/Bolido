<?php
/**
 * Hooks.php
 * A really simple hook system
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Hooks
{
    protected $triggers = array();
    protected $calledTriggers = array();

    /**
     * Construct
     *
     * @param array $files An array with files that register hooks.
     * @return void
     */
    public function __construct(array $files = array())
    {
        if (!empty($files))
        {
            foreach ($files as $file)
            {
                if (file_exists($file))
                    include $file;
            }
        }
    }

    /**
     * Runs the code for a specific section/event.
     * The Method should have as the first value the name of the hook.
     *
     * @param array mixed params that are determined by the func_get_args() function.
     * @return mixed
     *
     * @throws InvalidArgumentException when no arguments are passed.
     */
    public function run()
    {
        if (func_num_args() <= 0)
            throw new \InvalidArgumentException('No arguments passed to the Hook runner');

        $args = func_get_args();
        $section = strtolower($args[0]);
        array_shift($args);

        if (isset($args[0]))
            $return = $args[0];
        else
            $return = $args[0] = null;

        if (!empty($this->triggers[$section]))
        {
            // Organize the hooks by defined position. Little numbers get executed earlier
            usort($this->triggers[$section], function ($a, $b) {
                return ($a['position'] >= $b['position']);
            });

            foreach ($this->triggers[$section] as $value)
            {
                if (empty($value['from_module']))
                    continue ;

                $return = call_user_func_array($value['call'], $args);

                // Reassign the new return value back into the args ONLY if the type matches
                if (!$args[0] || gettype($args[0]) == gettype($return))
                    $args[0] = $return;
                else
                    $return = $args[0];

                $this->calledTriggers[$section][] = $value['from_module'];
            }
        }

        return $return;
    }

    /**
     * Associates a function/method to a specific section.
     *
     * @param callable $callback    A callable object/function
     * @param string   $trigger
     * @param string   $moduleName
     * @param int      $position    Little numbers get executed earlier
     * @return void
     */
    public function append(Callable $callback, $trigger, $moduleName = 'temp', $position = 0)
    {
        $this->triggers[$trigger][] = array(
            'from_module' => strtolower($moduleName),
            'position'    => intval($position),
            'call'        => $callback
        );
    }

    /**
     * Removes hooks from a module
     *
     * @param string $moduleName
     * @return void
     */
    public function removeModule($moduleName)
    {
        array_walk_recursive($this->triggers, array($this, 'filterModuleCallback'), $moduleName);
    }

    /**
     * Removes hooks from a specific trigger
     * and module
     *
     * @param string $moduleName
     * @param string $trigger
     * @return void
     */
    public function removeModuleByTrigger($moduleName, $trigger)
    {
        if (!empty($this->triggers[$trigger]))
            array_walk_recursive($this->triggers[$trigger], array($this, 'filterModuleCallback'), $moduleName);
    }

    /**
     * Removes all hooks called by a Trigger
     *
     * @param string $name
     * @return void
     */
    public function removeTrigger($name) { unset($this->triggers[$name]); }

    /**
     * This is a callback method for the removeModule
     * and removeModuleTrigger methods
     *
     * @param string $item Passed By Reference
     * @param string $key
     * @param string $moduleName
     * @return void
     */
    protected function filterModuleCallback(&$item, $key, $moduleName)
    {
        if ($key == 'from_module' && $item == strtolower($moduleName))
            $item = null;
    }

    /**
     * Returns an array with all the triggers called
     *
     * @return array
     */
    public function calledTriggers() { return $this->calledTriggers; }
}
?>
