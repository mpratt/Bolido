<?php
/**
 * Hooks.class.php
 * A really simple hook system
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

final class Hooks
{
    private $config;
    private $sections = array();
    private $calledSections = array();

    /**
     * Construct
     *
     * @param object $config
     * @param bool $cacheHooks Cache the hook files.
     * @return void
     */
    public function __construct(Config $config, $cacheHooks = true)
    {
        $this->config = $config;
        $this->readHooks($cacheHooks);
    }

    /**
     * Searches for hooks and registers them.
     * It searches a directory for files ending with .hook.php
     *
     * @param bool $cacheHooks Cache the hooks
     * @return void
     */
    protected function readHooks($cacheHooks = true)
    {
        $cache = new FileCache($this->config->get('cachedir'));
        if (!$cacheHooks)
            $cache->disableCache(true);

        $hooks = $cache->read('hooks_events');
        if (empty($hooks))
        {
            $hooks = array();
            foreach (glob($this->config->get('moduledir') . '/*/hooks/*.hook.php') as $hook)
            {
                if (is_readable($hook))
                    include($hook);
            }

            // Organize the hooks
            if (!empty($hooks))
            {
                foreach ($hooks as $k => $v)
                    usort($hooks[$k], array(&$this, 'orderHooksByPosition'));
            }

            // Store for 30 minutes
            $cache->store('hooks_events', $hooks, 30);
        }

        if (!empty($hooks))
            $this->sections = $hooks;
    }

    /**
     * Callback method that organizes the hooks by its position
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function orderHooksByPosition($a, $b)
    {
        if (!isset($a['position']) || !is_numeric($a['position']))
            $a['position'] = 0;

        if (!isset($b['position']) || !is_numeric($b['position']))
            $b['position'] = 0;

        return $a['position'] - $b['position'];
    }

    /**
     * Removes all hooks called by a Module
     *
     * @param string $moduleName
     * @return void
     */
    public function removeModuleHooks($moduleName)
    {
        if (!empty($this->sections))
        {
            $moduleName = strtolower($moduleName);
            foreach ($this->sections as $trigger => $value)
            {
                foreach ($values as $k => $v)
                {
                    if (!empty($v['from_module']) && strtolower($v['from_module']) == $moduleName)
                    {
                        unset($this->sections[$trigger][$k]);
                        break;
                    }
                }
            }
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
        if (!empty($this->sections))
        {
            $moduleName = strtolower($moduleName);
            foreach ($this->sections as $trigger => $value)
            {
                foreach ($values as $k => $v)
                {
                    if (!empty($v['call']))
                    {
                        if (is_array($v['call']) && count($v['call']) >= 2 && is_string($v['call'][0]) && is_string($v['call'][1]))
                            $v['call'] = $v['call'][0] . '::' . $v['call'][1];

                        if (is_string($v['call']) && strtolower($v['call']) == $functionName)
                        {
                            unset($this->sections[$trigger][$k]);
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
    public function removeTrigger($triggerName)
    {
        if (isset($this->sections[$triggerName]))
            unset($this->sections[$triggerName]);
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
            $return  = (isset($args['1']) ? $args['1'] : null);
            array_shift($args);

            $this->calledSections[] = $section;
            if (!empty($this->sections[$section]))
            {
                foreach ($this->sections[$section] as $value)
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

                        // Free some memory
                        if (is_object($function))
                            unset($function);

                        // Reassign the new return value back into the args
                        if (isset($args[0]))
                            $args[0] = $return;
                    }
                }
            }

            return $return;
        }
        else
            throw new Exception('No arguments passed to the Hook runner');
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
            if (empty($call) || empty($call['1']) || !class_exists($call['0']))
                return ;

            $objectName = $call['0'];
            $methodName = $call['1'];
            $constructorArgs = (!empty($call['2']) ? $call['2'] : array());

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
     * $this->append(array('from_module' => 'test_module', 'call' => 'blah::fuck'), 'filter_before_shutdown');
     */
    public function append($func = array(), $trigger, $moduleName = 'temp')
    {
        if (is_array($func))
            $this->sections[$trigger][] = $func;
        else
            $this->sections[$trigger][] = array('from_module' => $moduleName,
                                                'call' => $func);
    }

    /**
     * For Debugging purposes only!
     *
     * @return string
     */
    public function __toString()
    {
        return 'Hooks Found: ' . print_r($this->sections, true) . '<br /> Called Events: ' . print_r($this->calledSections, true);
    }
}
?>