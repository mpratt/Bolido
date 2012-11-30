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

namespace Bolido\App;

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
    public function __construct($files)
    {
        $hooks = array();
        if (!empty($files) && is_array($files))
        {
            foreach ($files as $file)
            {
                if (is_readable($file))
                {
                    $this->filesLoaded[] = $file;
                    include($file);
                }
            }

            // Organize the hooks by defined priority
            if (!empty($hooks))
            {
                foreach ($hooks as $k => $v)
                {
                    usort($hooks[$k], function ($a, $b) {
                        if (!isset($a['position']) || !is_numeric($a['position']))
                            $a['position'] = 0;

                        if (!isset($b['position']) || !is_numeric($b['position']))
                            $b['position'] = 0;

                        return $a['position'] > $b['position']; }
                    );
                }
            }
        }

        $this->triggers = $hooks;
    }

    /**
     * Removes all hooks called by a Module
     *
     * @param string $moduleName
     * @return void
     */
    public function removeModuleHooks($moduleName)
    {
        $moduleName = strtolower($moduleName);
        if (!empty($this->triggers) && $moduleName != 'main')
        {
            foreach ($this->triggers as $trigger => $values)
            {
                foreach ($values as $k => $v)
                {
                    if (!empty($v['from_module']) && strtolower($v['from_module']) == $moduleName)
                        unset($this->triggers[$trigger][$k]);
                }
            }

            $this->triggers = array_filter($this->triggers);
        }
    }

    /**
     * Removes a function found on a trigger
     *
     * @param string $functionName The name of a function or an object method like Object::method
     * @return void
     */
    public function removeFunction($functionName)
    {
        if (!empty($this->triggers))
        {
            $functionName = strtolower($functionName);
            foreach ($this->triggers as $trigger => $values)
            {
                foreach ($values as $k => $v)
                {
                    if (!empty($v['call']))
                    {
                        if (is_array($v['call']) && count($v['call']) >= 2 && is_string($v['call'][0]) && is_string($v['call'][1]))
                            $v['call'] = strtolower($v['call'][0] . '::' . $v['call'][1]);

                        if (is_string($v['call']) && strtolower($v['call']) == $functionName)
                        {
                            unset($this->triggers[$trigger][$k]);
                            $this->triggers[$trigger] = array_filter($this->triggers[$trigger]);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Removes all hooks called by a Trigger
     *
     * @param string $triggerName
     * @return void
     */
    public function removeTrigger($name)
    {
        if (isset($this->triggers[$name]))
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
            $args    = func_get_args();
            $section = strtolower($args['0']);
            array_shift($args);

            $return  = (isset($args['0']) ? $args['0'] : null);
            $returnClass = (is_object($return) ? get_class($return) : null);
            if (!empty($this->triggers[$section]) && is_array($this->triggers[$section]))
            {
                $this->calledTriggers[] = $section;
                foreach ($this->triggers[$section] as $value)
                {
                    // If no function was defined dont do nothing
                    if (empty($value['from_module']) || empty($value['call']))
                        continue;

                    // Do we need to include a file?
                    if (!empty($value['requires']) && is_file($value['requires']))
                        require_once($value['requires']);

                    $function = $this->determineAction($value['call']);
                    if (is_callable($function))
                    {
                        $return = call_user_func_array($function, $args);

                        // Reassign the new return value back into the args ONLY if the type matches
                        if (!isset($args[0]) || empty($return))
                            $return = null;
                        else
                        {
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
     * @return mixed Null when failure!
     */
    protected function determineAction($call)
    {
        // Find out if were talking about an object
        if (is_array($call))
        {
            if (empty($call) || empty($call['1']))
                return ;

            $objectName = $call['0'];
            $methodName = $call['1'];
            $constructorArgs = (!empty($call['2']) ? $call['2'] : array());

            if (is_object($objectName) && method_exists($objectName, $methodName))
                return array($objectName, $methodName);

            if (!is_string($objectName) || !class_exists($objectName))
                return ;

            $reflection = new ReflectionClass($objectName);
            if (!$reflection->hasMethod($methodName))
                return ;

            if ($reflection->isInstantiable())
            {
                if (!empty($constructorArgs) && ($reflection->hasMethod('__construct') || $reflection->hasMethod($objectName)))
                    $object = $reflection->newInstanceArgs($constructorArgs);
                else
                    $object = $reflection->newInstance();

                return array($object, $methodName);
            }

            return ;
        }
        // A Normal Function, perhaps?
        else if (!empty($call))
            return $call;
        else
            return ;
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
    public function append($func = array(), $trigger, $moduleName = 'temp')
    {
        if (is_array($func))
            $this->triggers[$trigger][] = $func;
        else
            $this->triggers[$trigger][] = array('from_module' => $moduleName,
                                                'call' => $func);
    }

    /**
     * Gets all the loaded triggers
     *
     * @return array
     */
    public function listTriggers()
    {
        return array_unique(array_keys($this->triggers));
    }

    /**
     * Returns an array with all the triggers called
     *
     * @return array
     */
    public function calledTriggers()
    {
        return array_unique($this->calledTriggers);
    }

    /**
     * For Debugging purposes only!
     *
     * @return string
     */
    public function __toString()
    {
        return 'Files Loaded: ' . count($this->hooksLoaded);
    }
}
?>
