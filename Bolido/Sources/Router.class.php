<?php
/**
 * Router.class.php, Guide me in the darkness
 * The router reads the request url and extracts the important stuff from it
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

class Router
{
    // Request properties
    protected $requestPath;

    // Do not Touch
    protected $module;
    protected $action;
    protected $process;
    protected $requestMethod;
    protected $routes = array();
    protected $rules  = array();
    protected $params = array();
    protected $matched;

    /**
     * Construct
     *
     * @param string $path
     * @param string $requestMethod The current request method
     * @param string $defaultModule
     * @return void
     */
    public function __construct($path, $requestMethod = '', $defaultModule = 'home')
    {
        // Default Values
        $this->module  = $defaultModule;
        $this->action  = 'index';
        $this->process = '';

        if (!empty($path))
            $this->requestPath = '/' . trim($path, '/');
        else
            $this->requestPath = null;

        if (empty($requestMethod))
            throw new Exception('The Request Method is empty');

        $this->requestMethod = trim(strtolower($requestMethod));
    }

    /**
     * Sets the Main Module
     *
     * @param string $moduleName
     * @return void
     */
    public function setMainModule($moduleName)
    {
        $this->module = $moduleName;
    }

    /**
     * Maps a route in the registry
     *
     * @param string $rule The Rule that is going to be used
     * @param array  $conditions Custom targets/placeholders for the rule
     * @param string $method The request method for the rule
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
        if (empty($rule))
            return ;

        if (empty($method))
            $method = 'get';

        $method = strtolower($method);
        if (!in_array($method, array('get', 'post', 'put', 'delete', 'head', 'options')))
            return ;

        if (isset($this->rules[$method][$rule]) && !$overwrite)
            throw new Exception('Mapping Error, The rule ' . $rule . ' in ' . $method . ' was already defined');

        $this->rules[$method][$rule] = $conditions;
    }

    /**
     * Maps default Routes
     *
     * @return void
     */
     protected function mapDefaultRoutes()
     {
        // Append Default Rules
        $routes = array();
        $routes[] = array('rule' => '/', 'conditions' => array('module' => $this->module, 'action' => $this->action));
        $routes[] = array('rule' => '/[a:module]');
        $routes[] = array('rule' => '/[a:module]/[a:action]');
        $routes[] = array('rule' => '/[a:module]/[a:process]/[a:action]');

        foreach($routes as $r)
            $this->map($r['rule'], (!empty($r['conditions']) ? $r['conditions'] :  array()), 'get', true);
    }

    /**
     * Matches the current $path with the controller/action/process
     *
     * @return bool True if a route was found, false otherwise
     */
    public function find()
    {
        if (!empty($this->requestPath))
        {
            $this->mapDefaultRoutes();
            if (empty($this->rules[$this->requestMethod]))
                return false;

            $rules = array_keys($this->rules[$this->requestMethod]);
            foreach ($rules as $rule)
            {
                // translate the rule to a regex
                $regex = preg_replace_callback('~\[([a-z_]+):([a-z_]+)\]~i', array(&$this, 'createRegex'), $rule);
                if (preg_match('~^' . $regex . '$~', $this->requestPath, $m))
                {
                    if (!empty($this->rules[$this->requestMethod][$rule]))
                        $this->params = array_merge($m, $this->rules[$this->requestMethod][$rule]);
                    else
                        $this->params = $m;

                    if (!empty($this->params['module']))
                        $this->module = $this->params['module'];

                    if (!empty($this->params['action']))
                        $this->action = $this->params['action'];

                    if (!empty($this->params['process']))
                        $this->process = $this->params['process'];

                    $this->matched = $rule . ' (~^' . htmlspecialchars($regex) . '$~i)';
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Callback method that writes a regex for a specific rule.
     *
     * @param array $matches The matched identifiers
     * @return string
     */
    protected function createRegex($matches)
    {
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
    }

    /**
     * Gets a url placeholder
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
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
    public function has($name)
    {
        return ($this->get($name) !== false);
    }

    /**
     * For debugging only
     *
     * @return string
     */
    public function __toString()
    {
        return 'Request Path: ' . $this->requestPath . ' Matched Rule: ' . $this->matched;
    }
}
?>
