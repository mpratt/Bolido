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
    private $cache;
    private $filesLoaded = array();
    private $triggers    = array();
    private $calledTriggers = array();

    /**
     * Construct
     *
     * @param array $files
     * @return void
     */
    public function __construct(array $files = array())
    {
        $hooks = array();
        if (!empty($files))
        {
            foreach ($files as $file)
            {
                $this->filesLoaded[] = $file;
                include($file);
            }

            // Organize the hooks by defined position. Little numbers get executed earlier
            foreach ($hooks as $k => $v)
                usort($hooks[$k], function ($a, $b) { return intval($a['position']) > intval($b['position']); });
        }

        $this->triggers = $hooks;
    }

    /**
     * Removes hooks by module and trigger
     *
     * @param string $moduleName
     * @param string $trigger  Only remove functions registered to this trigger
     * @return void
     */
    public function clearModuleTriggers($moduleName, $trigger = '')
    {
        if (empty($this->triggers))
            return ;

        $moduleName = strtolower($moduleName);
        $trigger = strtolower($trigger);
        foreach ($this->triggers as $t => $values)
        {
            foreach ($values as $k => $v)
            {
                if (strtolower($v['from_module']) == $moduleName && (empty($trigger) || strtolower($trigger) == $t))
                    unset($this->triggers[$t][$k]);
            }
        }

        $this->triggers = array_filter($this->triggers);
    }

    /**
     * Removes all hooks called by a Trigger
     *
     * @param string $triggerName
     * @return void
     */
    public function clearTrigger($name)
    {
        unset($this->triggers[$name]);
    }

    /**
     * Runs the code for a specific section/event.
     * The Method should have as the first value the name of the hook.
     *
     * @return mixed
     */
    public function run()
    {
        if (func_num_args() > 0)
        {
            $args = func_get_args();
            $section = strtolower($args['0']);
            $return  = (isset($args['1']) ? $args['1'] : null);
            $returnClass = (is_object($return) ? get_class($return) : null);
            array_shift($args);

            if (!empty($this->triggers[$section]))
            {
                $this->calledTriggers[] = $section;
                foreach ($this->triggers[$section] as $value)
                {
                    $function = $this->determineAction($value['call']);
                    if (is_callable($function))
                    {
                        $return = call_user_func_array($function, $args);

                        if (!isset($args[0]) || empty($return))
                            $return = null;
                        else
                        {
                            // Reassign the new return value back into the args ONLY if the type matches
                            if (gettype($args[0]) == gettype($return) && (!is_object($return) || get_class($return) == $returnClass))
                                $args[0] = $return;
                            else
                                $return = $args[0];
                        }
                    }
                }
            }

            return $return;
        }
        else
            throw new \Exception('No arguments passed to the Hook runner');
    }

    /**
     * Finds out wether the $call is a normal function or
     * a method inside an object.
     *
     * @param mixed $call The name of the function that should be called Or an array for class methods
     * @return mixed
     *
     * codeCoverageIgnore
     */
    protected function determineAction($call)
    {
        if (is_array($call) && count($call) >= 2)
        {
            list ($objectName, $methodName) = $call;
            if (is_object($objectName))
                return array($objectName, $methodName);

            if (is_string($objectName) && class_exists($objectName))
            {
                $reflection = new \ReflectionClass($objectName);
                if ($reflection->hasMethod($methodName))
                    return array($reflection->newInstance(), $methodName);
            }
        }

        return $call;
    }

    /**
     * Associates a function/method to a specific section.
     *
     * @param mixed  $func It can be the name of the function or an anonymous function or an array with all the information
     * @param string $trigger The section where $func is going to be triggered
     * @param mixed  $moduleName Only used when $func is a string
     * @return void
     *
     * @example
     * $this->append(array('from_module' => 'module_name', 'call' => array('Object', 'Method')), 'name_of_the_trigger');
     */
    public function append(Callable $callback, $trigger, $moduleName = 'temp')
    {
        $hook = array_merge(array('from_module' => $moduleName,
                                  'position' => 0,
                                  'call' => null), array('call' => $callback));

        $this->triggers[$trigger][] = $hook;
    }

    /**
     * Gets all the loaded triggers
     *
     * @return array
     */
    public function listTriggers() { return array_keys($this->triggers); }

    /**
     * Returns an array with all the triggers called
     *
     * @return array
     */
    public function calledTriggers() { return array_unique($this->calledTriggers); }
}
?>
