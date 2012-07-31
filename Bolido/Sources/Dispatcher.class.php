<?php
/**
 * Dispatcher.class.php
 * This class just executes the modules
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

class Dispatcher
{
    protected $config;
    protected $router;
    protected $cache;
    protected $hooks;
    protected $error;
    protected $db;
    protected $session;
    protected $sessionDB;
    protected $requestMethod;

    /**
     * Construct
     *
     * @param object $config
     * @param string $requestMethod Normally the $_SERVER['REQUEST_METHOD']
     * @return void
     */
    public function __construct(iConfig $config, $requestMethod = '')
    {
        $this->config  = $config;
        if (empty($requestMethod))
        {
            if (!empty($_SERVER['REQUEST_METHOD']))
                $requestMethod = $_SERVER['REQUEST_METHOD'];
            else
                $requestMethod = '';
        }

        $this->requestMethod = trim(strtolower($requestMethod));
    }

    /**
     * Instantiate important objects
     *
     * @return void
     */
    public function loadServices()
    {
        if (function_exists('apc_cache_info') && function_exists('apc_store'))
            $this->cache = new ApcCache();
        else
            $this->cache = new FileCache($this->config->get('cachedir'));

        $this->hooks   = new Hooks($this->config->get('moduledir') . '/*/hooks/*.hook.php', $this->cache);
        $this->session = new Session($this->config, $this->hooks);
        $this->error   = new ErrorHandler($this->config, $this->session, $this->hooks);

        try
        {
            $this->db = new DatabaseHandler($this->config->get('dbInfo'));
        }
        catch(Exception $e) { $this->error->display('Error on Database Connection', 503); }
    }

    /**
     * Lord Vader - Rise!
     * Starts the session and dispatches the main action to the respective module.
     *
     * @param string $uri The request uri, normally a cleaned $_SERVER['REQUEST_URI']
     * @return void
     */
    public function connect($uri)
    {
        $this->hooks->run('before_module_execution', $this->db, $this->session, $this->error, $this->hooks);

        // Check if the server has the resources to serve the page
        if ($this->config->get('serverAutoBalance') && $this->config->get('serverLoad') > $this->config->get('serverOverloaded'))
        {
            $this->error->log('No Resources Available - ' . $this->config->get('serverLoad') . '/' . $this->config->get('serverOverloaded'));
            $this->error->display('No resources Available!', 503);
        }
        else
        {
            $this->session->start();

            $this->router = new Router($uri, $this->requestMethod);
            $this->hooks->run('append_routes', $this->router);
            $found = $this->router->find();

            if (!$found || !$this->execute($this->router->get('module'), $this->router->get('action'), $this->router->get('subModule')))
            {
                $this->session->close();
                $this->error->display('Page not found', 404);
            }

            $this->session->close();
        }
    }

    /**
     * This Method does the dirty work of loading the module.
     * If the module doesnt exist we get a 404.
     *
     * @param string $module The Name of the Module
     * @param string $action The Name of the action
     * @param string $subModule The Name of the action
     * @return mixed
     */
    protected function execute($module = '', $action = '', $subModule = '')
    {
        $moduleObject = null;
        $flushTemplates = true;

        // Are we trying to execute a submodule action?
        if (!empty($subModule)
            && ucfirst($subModule) != 'Index'
            && is_readable($this->config->get('moduledir') . '/' . $module . '/' . ucfirst($subModule) . '.Module.php'))
        {
            require($this->config->get('moduledir') . '/' . $module . '/' . ucfirst($subModule) . '.Module.php');
            $module .= '_' . strtolower($subModule);
            $moduleObject = new $module();

            // Dont flush the templates when executing a submodule
            $flushTemplates = false;
        }
        else if (is_readable($this->config->get('moduledir') . '/' . $module . '/Index.Module.php'))
        {
            require($this->config->get('moduledir') . '/' . $module . '/Index.Module.php');
            $moduleObject = new $module();
        }

        // Check that the action exists inside this module.
        if (is_object($moduleObject) && method_exists($moduleObject, $action))
        {
            // make sure the module url is case sensitive
            if (get_class($moduleObject) != $module)
                return false;

            // Make sure the module action is case sensitive
            $reflectionMethod = new ReflectionMethod($moduleObject, $action);
            if ($reflectionMethod->name != $action)
                return false;

            // Only public and unlisted methods are callable.
            if (!$reflectionMethod->isPublic()
                || in_array(strtolower($action), array('inject', 'loadsettings', 'beforeaction', 'flushtemplates', 'shutdownmodule',
                                                       '__construct', '__destruct', '__tostring', '__call', '__set', '__sleep', '__wakeup', '__get',
                                                       '__unset')))
            {
                $this->error->log('Visitor tried to access a protected/blacklisted method - ' . get_class($moduleObject) . '::' . $action);
                return false;
            }

            // Free Memory
            unset($reflectionMethod);

            // Load Module Settings
            $moduleObject->loadSettings($this->config);

            // Inject important dependencies
            $moduleObject->inject($this->db, $this->session, $this->error, $this->hooks, $this->router, $this->cache);

            // Need to load something else before executing $action
            $moduleObject->beforeAction();

            // Run the called action
            $moduleObject->{$action}();

            // Display the templates when we are processing a regular module.
            if ($flushTemplates)
                $moduleObject->flushTemplates();

            // Shutdowns the module
            $moduleObject->shutdownModule();
            unset($moduleObject);

            return true;
        }

        return false;
    }

}
?>
