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
    // Instance containers
    protected $hooks;

    // Request properties
    protected $requestPath;

    // Do not Touch
    protected $module;
    protected $action;
    protected $process;
    protected $routes = array();
    protected $rules  = array();
    protected $params = array();
    protected $matched;

    /**
     * Construct
     *
     * @param string $path
     * @param object $hooks
     * @return void
     */
    public function __construct($path, Hooks $hooks)
    {
        $this->hooks = $hooks;

        // Default Values
        $this->module  = 'home';
        $this->action  = 'index';
        $this->process = '';
        $this->requestPath = '/' . trim($path, '/');

        $this->getRoutes();
    }

    /**
     * Loads all the routes and maps them.
     *
     * @return void
     */
     public function getRoutes()
     {
        $routes = $this->hooks->run('load_routes', array());
        if (!is_array($routes))
            throw new Exception('The load_routes hook must return an array');

        // Append Default Rules
        $routes[] = array('rule' => '/', 'conditions' => array('module' => $this->module, 'action' => $this->action));
        $routes[] = array('rule' => '/[a:module]');
        $routes[] = array('rule' => '/[a:module]/[a:action]');
        $routes[] = array('rule' => '/[a:module]/[a:process]/[a:action]');

        foreach($routes as $r)
            $this->map($r['rule'], (!empty($r['conditions']) ? $r['conditions'] :  array()));
    }

    /**
     * Maps a route in the registry
     *
     * @param string $rule The Rule that is going to be used
     * @param array $conditions Custom targets/placeholders for the rule
     * @return void
     *
     * @example:
     * $this->map('/users/[i:id]', array('module' => 'users'))
     * $this->map('/users/[i:id]', array('module' => 'users', 'id' => ''))
     * $this->map('/login',  array('action' => 'login')); Calls The Home module and executes the login method
     * $this->map('/login', array('module' => 'users', 'action' => 'login'))
     *
     */
    public function map($rule, $conditions = array())
    {
        $this->rules[] = $rule;
        $this->conditions[$rule] = $conditions;
    }

    /**
     * Matches the current $path with the controller/action/process
     *
     * @return bool True if a route was found, false otherwise
     */
    public function find()
    {
        foreach ($this->rules as $rule)
        {
            // translate the rule to a regex
            $regex = preg_replace_callback('~\[([a-z_]+):([a-z_]+)\]~i', array(&$this, 'createRegex'), $rule);
            if (preg_match('~^' . $regex . '$~', $this->requestPath, $m))
            {
                if (!empty($this->conditions[$rule]))
                    $this->params = array_merge($m, $this->conditions[$rule]);
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
        return 'Module: ' . $this->module . '<br />
                Action: ' . $this->action . '<br />
                Process: ' . $this->process . '<br />
                Params: ' . print_r($this->params, true) . '<br />
                Request Path: ' . $this->requestPath . '<br />
                Matched Rule: ' . $this->matched . '<br />
                Rules: ' . print_r($this->rules, true);
    }
}
?>