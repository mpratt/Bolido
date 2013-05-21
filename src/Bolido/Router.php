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
     * @param string $requestMethod The current request method, Generally $_SERVER['REQUEST_METHOD']
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
     * Blacklist a rule
     *
     * @param string $rule
     * @param string $method
     * @return void
     */
    public function blacklistRule($rule, $method = 'get')
    {
        $method = $this->filterMethod($method);
        $rule = $this->filterPath($rule);

        $rule = preg_replace_callback('~\[([iha]):([a-z_]+)\]~i', array($this, 'translateRule'), $rule);
        $this->blacklist[$method][] = $rule;
    }

    /**
     * Normalizes a given Method
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
            throw new \InvalidArgumentException('Mapping Invalid Request Method ' . $method);

        // Translate a head request into a get
        if ($method == 'head')
            $method = 'get';

        return $method;
    }

    /**
     * Normalizes a given Path
     *
     * @param string $path
     * @return string
     */
    protected function filterPath($path)
    {
        if (trim($path) == '/')
            return trim($path);

        return rtrim($path, '/');
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
     * $this->map('/login', array('module' => 'users', 'action' => 'login', 'controller' => 'LoginController'))
     *
     */
    public function map($rule, array $conditions = array(), $method = 'get', $overwrite = false)
    {
        if (is_array($method))
        {
            foreach ($method as $m)
                $this->map($rule, $conditions, $m, $overwrite);
        }
        else
        {
            $method = $this->filterMethod($method);
            $rule = $this->filterPath($rule);

            // Dont map the rule if its not going to be used by this request
            if ($method == $this->requestMethod)
            {
                if (isset($this->rules[$rule][$method]) && !$overwrite)
                    throw new \Exception('Mapping Error, The rule ' . $rule . ' with ' . $method . ' was already defined');

                $this->rules[$rule][$method] = $conditions;
            }
        }
    }

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
     * Maps default Routes
     *
     * @return void
     */
    protected function mapDefaultRoutes()
    {
        foreach (array('/', '/[a:module]/[a:action]', '/[a:module]') as $rule)
        {
            try {
                $this->map($rule);
            } catch(\Exception $e) {}
        }
    }

    /**
     * Finds a path that matches a previously defined rule.
     * It returns false if no rule was found or if the path has been
     * blacklisted. It returns true otherwise.
     *
     * @param string $requestPath something like $_SERVER['REQUEST_URI']
     * @return bool
     */
    public function find($requestPath)
    {
        $this->mapDefaultRoutes();
        $requestPath = $this->filterPath($requestPath);

        // check if the path is blacklisted
        if (!empty($this->blacklist[$this->requestMethod])
            && preg_match('~^(:?' . implode('|', $this->blacklist[$this->requestMethod]) . ')$~', $requestPath))
            return false;

        foreach (array_keys($this->rules) as $rule)
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
     * Returns a Url placeholder
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->params[$name]))
            return $this->params[$name];
        else if (in_array($name, array('controller', 'matched', 'action', 'module', 'requestMethod')))
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
