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

    /**
     * Construct
     *
     * @param object $config
     * @return void
     */
    public function __construct(Config $config) { $this->config  = $config; }

    /**
     * Instantiate important objects
     *
     * @param bool $dbServices Wether or not to use the database with some services
     * @return void
     */
    public function loadServices($dbServices = true)
    {
        if (function_exists('apc_cache_info') && function_exists('apc_store'))
            $this->cache = new ApcCache();
        else
            $this->cache = new FileCache($this->config->get('cachedir'));

        $this->hooks   = new Hooks($this->config->get('moduledir') . '/*/hooks/*.hook.php', $this->cache);
        $this->session = new SessionHandler($this->config, $this->hooks);
        $this->error   = new ErrorHandler($this->config, $this->session, $this->hooks);

        try
        {
            $this->db = new DatabaseHandler($this->config->get('dbInfo'));

            // Store sessions and errors in the DB
            if ($dbServices)
            {
                $this->sessionDB = new SessionHandlerDB($this->db, $this->session);
                $this->sessionDB->register();

                $this->error->setDBEngine($this->db);
            }
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
        // Check if the server has the resources to serve the page
        if ($this->config->get('serverAutoBalance') && $this->config->get('serverLoad') > $this->config->get('serverOverloaded'))
        {
            $this->error->log('No Resources Available - ' . $this->config->get('serverLoad') . '/' . $this->config->get('serverOverloaded'));
            $this->error->display('No resources Available!', 503);
        }
        else
        {
            $this->session->start();

            $this->router = new Router($uri);
            $this->hooks->run('append_routes', $this->router);
            $found = $this->router->find();

            if (!$found || !$this->execute($this->router->get('module'), $this->router->get('action'), $this->router->get('process')))
            {
                $this->session->close();
                $this->error->display('Page not Found', 404);
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
     * @return mixed
     */
    public function execute($module, $action, $process)
    {
        $module  = $module;
        $action  = $action;
        $process = $process;
        $moduleObject = null;

        // Are we trying to execute a module action?
        if ($process == 'actions' && is_readable($this->config->get('moduledir') . '/' . $module . '/Actions.Module.php'))
        {
            require($this->config->get('moduledir') . '/' . $module . '/Actions.Module.php');
            $module .= '_actions';
            $moduleObject = new $module();
        }
        // Are we trying to execute a module installation?
        else if ($process == 'install' && is_readable($this->config->get('moduledir') . '/' . $module . '/Install.Module.php'))
        {
            require($this->config->get('moduledir') . '/' . $module . '/Install.Module.php');
            $module .= '_install';
            $moduleObject = new $module();
        }
        // Are we trying to execute some ajax?
        else if ($process == 'ajax' && is_readable($this->config->get('moduledir') . '/' . $module . '/Ajax.Module.php'))
        {
            require($this->config->get('moduledir') . '/' . $module . '/Ajax.Module.php');
            $module .= '_ajax';
            $moduleObject = new $module();
        }
        // Default behaviour
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

            // Displays the templates loaded in $action, avoid this when processing ajax or form actions requests
            if (!in_array($process, array('ajax', 'actions')))
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
