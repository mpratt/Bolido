<?php
/**
 * Router.php, Guide me in the darkness
 * The router reads the request url and extracts the important stuff from it
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
    protected $translator;
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

        // Translate
        $this->translator = function ($matches){
            list(, $modifier, $name) = $matches;
            switch (strtolower($modifier))
            {
                case 'int':
                case 'i':
                    $regex = '[0-9]+';
                    break;

                case 'hex':
                case 'h':
                    $regex = '[a-fA-F0-9]+';
                    break;

                case 'all':
                case 'a':
                default :
                    $regex = '[\w0-9\-\_\+\;\.\%]+';
                    break;
            }
            return '(?P<' . $name . '>' . $regex . ')';
        };
    }

    /**
     * Sets the Main Module
     *
     * @param string $moduleName
     * @return void
     */
    public function setMainModule($moduleName) { $this->module = $moduleName; }

    /**
     * Blacklist a path
     *
     * @param string $rule
     * @param string $method
     * @return void
     */
    public function blacklistRule($rule, $method = 'get')
    {
        // Dont map a blacklist if its not used in this request
        $method = $this->filterMethod($method);
        if ($method != $this->requestMethod)
            return ;

        if (strlen($rule) > 1)
            $rule = rtrim($rule, '/');

        $rule = preg_replace_callback('~\[([a-z_]+):([a-z_]+)\]~i', $this->translator, $rule);
        if (!empty($this->blacklist[$method]))
            $this->blacklist[$method] .= '|' . $rule;
        else
            $this->blacklist[$method] = $rule;
    }

    /**
     * Checks and normalizes a given Method.
     * Throws an Exception otherwise.
     *
     * @param string $method (The request Method)
     * @return string
     */
    protected function filterMethod($method)
    {
        $method = trim(strtolower($method));
        if (empty($method) || !in_array($method, array('get', 'post', 'put', 'delete', 'head', 'options')))
            throw new \Exception('Mapping wrong Request Method ' . $method);

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
    public function map($rule, $conditions = array(), $method = 'get', $overwrite = false)
    {
        if (empty($rule) || trim($rule) == '')
            return ;

        if (strlen($rule) > 1)
            $rule = rtrim($rule, '/');

        if (is_array($method))
        {
            foreach ($method as $m)
                $this->map($rule, $conditions, $m, $overwrite);
        }
        else
        {
            $method = $this->filterMethod($method);

            // Dont map the rule if its not used in this request
            if ($method != $this->requestMethod)
                return ;

            if (trim($rule) != '/')
                $rule = rtrim($rule, '/');

            if (isset($this->rules[$rule][$method]) && !$overwrite)
                throw new \Exception('Mapping Error, The rule ' . $rule . ' with ' . $method . ' was already defined');

            $this->rules[$rule][$method] = $conditions;
        }
    }

    /**
     * Maps default Routes
     *
     * @return void
     */
     protected function mapDefaultRoutes()
     {
        $routes = array();
        $routes[] = array('rule' => '/', 'conditions' => array('module' => $this->module, 'action' => $this->action));
        $routes[] = array('rule' => '/[a:module]/[a:action]');

        /**
         * Deprecated Routes. Define explicitly the routes you need.
         * $routes[] = array('rule' => '/[a:module]');
         * $routes[] = array('rule' => '/[a:module]/[a:controller]/[a:action]');
         */

        foreach($routes as $r)
        {
            if (empty($this->rules[$r['rule']]))
                $this->rules[$r['rule']][$this->requestMethod] = (!empty($r['conditions']) ? $r['conditions'] :  array());
        }
    }

    /**
     * Matches the current $path with the controller/action/process
     *
     * @return bool True if a route was found, false otherwise
     */
    public function find($requestPath)
    {
        if (!empty($requestPath))
        {
            if (trim($requestPath) != '/')
                $requestPath = rtrim($requestPath, '/');

            $this->mapDefaultRoutes();
            $rules = array_keys($this->rules);
            foreach ($rules as $rule)
            {
                // check if the requestPath is blacklisted
                if (!empty($this->blacklist[$this->requestMethod])
                    && preg_match('~^(:?' . $this->blacklist[$this->requestMethod] . ')$~', $requestPath))
                    return false;

                // translate the rule to a regex
                $regex = preg_replace_callback('~\[([a-z_]+):([a-z_]+)\]~i', $this->translator, $rule);
                if (preg_match('~^' . $regex . '$~', $requestPath, $m))
                {
                    if (!empty($this->rules[$rule][$this->requestMethod]))
                        $this->params = array_merge($m, $this->rules[$rule][$this->requestMethod]);
                    else
                        $this->params = $m;

                    if (!empty($this->params['module']))
                        $this->module = $this->params['module'];

                    if (!empty($this->params['action']))
                        $this->action = $this->params['action'];

                    if (!empty($this->params['controller']))
                        $this->controller = $this->params['controller'];

                    $this->matched = $rule . ' (~^' . htmlspecialchars($regex) . '$~i)';
                    return true;
                }
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
        else if (property_exists($this, $name) && !is_object($this->{$name}))
            return $this->{$name};
        else
            return false;
    }

    /**
     * Checks if a url placeholder was set.
     *
     * @param string $name
     * @return mixed
     */
    public function __isset($name) { return ($this->__get($name) !== false); }
}
?>
