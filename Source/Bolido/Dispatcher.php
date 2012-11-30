<?php
/**
 * Dispatcher.php
 * This class executes the modules based on the paths
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

class Dispatcher
{
    public $app = array();

    /**
     * Constructor
     *
     * @param array $objects
     * @return void
     */
    public function __construct(array $objects = array())
    {
        if (!empty($objects))
        {
            foreach ($objects as $k => $v)
            {
                $this->attach($k, $v);
            }
        }
    }

    /**
     * Attaches objects to the app property
     *
     * @param string $key
     * @param object $object
     * @return void
     */
    public function attach($key, $object)
    {
        if (!isset($this->app) || !is_array($this->app))
            $this->app = array();

        if (is_object($object))
            $this->app[$key] = $object;
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
        $this->app['hooks']->run('before_module_execution', $this->app);

        $this->session->start();
        $this->router = new Router($uri, $this->requestMethod);
        $this->app['hooks']->run('append_routes', $this->router);
        $found = $this->router->find();

        if (!$found || !$this->execute($this->router->get('module'), $this->router->get('action'), $this->router->get('subModule')))
        {
            $this->session->close();
            $this->error->display('Page not found', 404);
        }

        $this->session->close();
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
