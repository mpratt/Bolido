<?php
/**
 * Router.php
 * Guide me in the darkness. The router reads the request url
 * and extracts the important stuff from it
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

class Router
{
    protected $module;
    protected $action;
    protected $controller;
    protected $requestMethod;
    protected $routes = array();
    protected $rules  = array();
    protected $params = array();
    protected $blacklist = array();
    protected $matched;

    /**
     * Construct
     *
     * @param string $requestMethod The current request method
     * @param string $defaultModule
     * @return void
     */
    public function __construct($requestMethod, $defaultModule = 'main')
    {
        $this->requestMethod = $this->filterMethod($requestMethod);
        $this->module  = $defaultModule;
        $this->action  = 'index';
        $this->controller = 'Controller';
    }

    /**
     * Sets the Main Module.
     * This method is used to overwrite the module
     * that handles the / requests.
     *
     * @param string $moduleName
     * @return void
     */
    public function setMainModule($moduleName) { $this->module = $moduleName; }

    /**
     * Translates a rule into a compatible regex
     * Its used as a callback function.
     *
     * @param array $matches
     * @return string
     */
    protected function translateRule($matches)
    {
        list(, $modifier, $name) = $matches;
        $regex = array('i' => '[0-9]+', // Integers
                       'h' => '[a-fA-F0-9]+', // Hexadecimal
                       'a' => '[\w0-9\-\_\+\;\.\%]+'); // Default matcher

        return '(?P<' . $name . '>' . $regex[$modifier] . ')';
    }

    /**
     * Blacklist a path
     *
     * @param string $rule
     * @param string $method
     * @return void
     */
    public function blacklistRule($rule, $method = 'get')
    {
        $method = $this->filterMethod($method);
        if (strlen($rule) > 1)
            $rule = rtrim($rule, '/');

        $rule = preg_replace_callback('~\[([iha]):([a-z_]+)\]~i', array($this, 'translateRule'), $rule);
        if (!empty($this->blacklist[$method]))
            $this->blacklist[$method] .= '|' . $rule;
        else
            $this->blacklist[$method] = $rule;
    }

    /**
     * Checks and normalizes a given Method.
     *
     * @param string $method
     * @return string
     *
     * @throws InvalidArgumentException when an invalid method is given.
     */
    protected function filterMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, array('get', 'post', 'put', 'delete', 'head', 'options')))
            throw new \InvalidArgumentException('Mapping wrong Request Method ' . $method);

        return $method;
    }

    /**
     * Maps a route in the registry
     *
     * @param string $rule The Rule that is going to be used
     * @param array  $conditions Custom targets/placeholders for the rule
     * @param mixed  $method A string with the request method for the rule Or an array with all the request methods.
     * @param bool   $overwrite Wether to overwrite the rule if exists
     * @return void
     *
     * @example:
     * $this->map('/users/[i:id]', array('module' => 'users'))
     * $this->map('/users/[i:id]', array('module' => 'users', 'id' => ''))
     * $this->map('/login',  array('action' => 'login')); Calls The Home module and executes the login method
     * $this->map('/login', array('module' => 'users', 'action' => 'login'))
     *
     */
    public function map($rule, array $conditions = array(), $method = 'get', $overwrite = false)
    {
        if (strlen($rule) > 1)
            $rule = rtrim($rule, '/');

        if (is_array($method))
        {
            foreach ($method as $m)
                $this->map($rule, $conditions, $m, $overwrite);
        }
        else
        {
            // Dont map the rule if its not used in this request
            $method = $this->filterMethod($method);
            if ($method == $this->requestMethod)
            {
                if (isset($this->rules[$rule][$method]) && !$overwrite)
                    throw new \Exception('Mapping Error, The rule ' . $rule . ' with ' . $method . ' was already defined');

                $this->rules[$rule][$method] = $conditions;
            }
        }
    }

    /**
     * Maps default Routes
     *
     * @return void
     */
     protected function mapDefaultRoutes()
     {
        try {
            $this->map('/', array('module' => $this->module, 'action' => $this->action));
        } catch(\Exception $e) {}

        try {
            $this->map('/[a:module]/[a:action]');
        } catch(\Exception $e) {}

        try {
            $this->map('/[a:module]');
        } catch(\Exception $e) {}
    }

    /**
     * Matches the current $path with the controller/action/process
     *
     * @return bool
     */
    public function find($requestPath)
    {
        $this->mapDefaultRoutes();
        if (trim($requestPath) != '/')
            $requestPath = rtrim($requestPath, '/');

        // check if the requestPath is blacklisted
        if (isset($this->blacklist[$this->requestMethod]) && preg_match('~^(:?' . $this->blacklist[$this->requestMethod] . ')$~', $requestPath))
            return false;

        $rules = array_keys($this->rules);
        foreach ($rules as $rule)
        {
            $regex = preg_replace_callback('~\[([iha]):([a-z_]+)\]~i', array($this, 'translateRule'), $rule);
            if (preg_match('~^' . $regex . '$~', $requestPath, $m))
            {
                $this->params  = array_merge($m, $this->rules[$rule][$this->requestMethod]);
                $this->matched = $rule . ' (~^' . htmlspecialchars($regex) . '$~i) - Method: ' . $this->requestMethod;
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a url placeholder.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->params[$name]))
            return $this->params[$name];
        else if (property_exists($this, $name) && is_string($this->{$name}))
            return $this->{$name};

        return false;
    }

    /**
     * Checks if a url placeholder was set.
     *
     * @param string $name
     * @return mixed
     */
    public function __isset($name) { return (bool) ($this->__get($name) !== false); }
}
?>
