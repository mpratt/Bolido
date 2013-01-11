<?php
/**
 * Dispatcher.php
 * This class executes the loaded module.
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

class Dispatcher
{
    public $app;

    /**
     * Constructor
     *
     * @param object $app Object that implements the ArrayAccess interface.
     * @return void
     */
    public function __construct(\ArrayAccess $app) { $this->app = $app; }

    /**
     * Lord Vader - Rise!
     * Starts the session and dispatches the main action to the respective module.
     *
     * @param string $uri The request uri, normally a cleaned $_SERVER['REQUEST_URI']
     * @return bool
     */
    public function connect($uri)
    {
        $this->app['hooks']->run('before_module_execution', $this->app);
        $this->app['session']->start();

        $found = $this->app['router']->find($uri);
        if (!$found || !$this->execute($this->app['router']->module, $this->app['router']->action, $this->app['router']->controller))
        {
            $this->app['session']->close();
            $this->app['error']->display('Page not found', 404);
            return false;
        }

        $this->app['session']->close();
        return true;
    }

    /**
     * This Method does the dirty work of loading the module.
     * If the module doesnt exist we get a 404.
     *
     * @param string $module The Name of the Module
     * @param string $action The Name of the action
     * @param string $controller The Name of the controller
     * @return bool
     */
    protected function execute($module, $action, $controller)
    {
        // This is the class with namespaces that we should load
        $objectString = '\\' . implode('\\', array('Bolido', 'Modules', $module, $controller));

        try {
            // Lets fetch some info about the controller
            $reflectionClass = new \ReflectionClass($objectString);

            /**
             * Perform important consistency/security checks
             * - Urls (and most importantly the actions) should be case sensitive.
             * - The action called must have a public visibility.
             * - Dont execute methods that start with an undercore.
             */
            $reflectionMethod = $reflectionClass->getMethod($action);
            if ($reflectionMethod->name != $action || !$reflectionMethod->isPublic() || strncmp('_', $action, 1) == 0)
                return false;

            unset($reflectionMethod);

            try {
                // Create an instance of the Object
                $moduleObject = $reflectionClass->newInstance();
                unset($reflectionClass);

                // Load Module Settings
                $moduleObject->_loadSettings($this->app);

                // Need to load something else before executing $action?
                $moduleObject->_beforeAction();

                // Run the called action
                $moduleObject->{$action}();

                // Flush the Templates to the browser
                $moduleObject->_flushTemplates();

                // Shutdown the module
                $moduleObject->_shutdownModule();

                unset($moduleObject);

            } catch (\Exception $e) {
                $this->app['error']->display($e->getMessage(), 500);
                return false;
            }

            return true;

        } catch(\Exception $e) { return false;  }
    }
}
?>
