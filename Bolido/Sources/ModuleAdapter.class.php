<?php
/**
 * ModuleAdapter.class.php
 * The main module adapter class. All modules should extend this class
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

abstract class ModuleAdapter
{
    // Module Information and Settings
    protected $module   = array();
    protected $settings = array();

    // Injected Objects
    protected $config;
    protected $db;
    protected $session;
    protected $error;
    protected $hooks;
    protected $router;

    // Instantiated objects
    protected $input;
    protected $lang;
    protected $cache;
    protected $user;
    protected $template;

    /**
     * Every module must have an index method by default!
     */
    abstract public function index();

    /**
     * Inject dependencies to the module
     * This method is called by the Dispatcher object.
     *
     * @param object $config
     * @param object $db
     * @param object $session
     * @param object $errorHandler
     * @param object $hooks
     * @param object $router
     * @param object $cache
     * @return void
     */
    final public function inject(iDatabaseHandler $db, Session $session, ErrorHandler $errorHandler, Hooks $hooks, Router $router, iCache $cache)
    {
        // Injected Objects
        $this->db       = $db;
        $this->session  = $session;
        $this->error    = $errorHandler;
        $this->hooks    = $hooks;
        $this->router   = $router;
        $this->cache    = $cache;

        // Instantiate important objects
        $this->input = new Input();
        $this->lang  = new Lang($this->config, $this->hooks, $this->module['classname']);

        // Load the user module
        if ($this->loadModel($this->config->get('usersModule')))
        {
            $className  = basename($this->config->get('usersModule'), '.php');
            $this->user = new $className($this->config, $this->db, $this->session, $this->hooks);
        }
        else
            $this->user = new DummyUser();

        $this->template = new TemplateHandler($this->config, $this->user, $this->lang, $this->session, $this->hooks, $this->module['classname']);
    }

    /**
     * Loads important module information.
     * This method is called by the Dispatcher object.
     *
     * @return void
     */
    final public function loadSettings(iConfig $config)
    {
        $this->config = $config;
        $this->module['classname'] = str_replace(array('_actions', '_ajax', '_install'), '', get_class($this));
        $this->module['path'] = $this->config->get('moduledir') . '/' . $this->module['classname'];
        $this->module['url']  = $this->config->get('mainurl') . '/' . basename($this->module['path']);

        // Get the template Suffix
        $templateSuffix = $this->config->get('mainurl');
        $parsedMainUrl  = parse_url($this->config->get('mainurl'));
        if (!empty($parsedMainUrl['path']) && preg_match('~^/[a-z]{2}/~', $parsedMainUrl['path'] . '/'))
        {
            $oldUrl = $this->config->get('mainurl');
            $newUrl = substr($this->config->get('mainurl'), 0 , -(strlen($this->config->get('language'))));
            $templateSuffix = str_replace($oldUrl, $newUrl, $templateSuffix);
        }

        // Get moudle template paths/urls
        if ($this->config->get('skin') != 'default' && is_dir($this->module['path'] . '/templates/' . $this->config->get('skin')))
        {
            $this->module['template_path'] = $this->module['path'] . '/templates/' . $this->config->get('skin');
            $this->module['template_url'] = $templateSuffix . '/Modules/' . $this->module['classname'] . '/templates/' . $this->config->get('skin');
        }
        else
        {
            $this->module['template_path'] = $this->module['path'] . '/templates/default';
            $this->module['template_url'] = $templateSuffix . '/Modules/' . $this->module['classname'] . '/templates/default';
        }

        // Load Custom Settings
        if (is_readable($this->module['path'] . '/Settings.json'))
        {
            $this->module['settings'] = json_decode(file_get_contents($this->module['path'] . '/Settings.json'), true);
            $this->module['loaded_settings'][$this->module['classname']] = $this->module['path'] . '/Settings.json';
        }
    }

    /**
     * Gets a setting for this module
     *
     * @param string $key The Name of the setting
     * @return void
     */
    final protected function setting($key)
    {
        if (isset($this->module['settings'][$key]))
            return $this->module['settings'][$key];
        else if (isset($this->module[$key]))
            return $this->module[$key];
        else
            return null;
    }

    /**
     * Flushes all the templates loaded by the templateHandler class.
     * Before it, it tries to append basic stuff.
     *
     * This method is called by the Dispatcher Object and it can be overwritten
     * inside the module itself!
     *
     * @return void
     */
    public function flushTemplates()
    {
        // Append some stuff to the theme before the shit goes down!
        if (file_exists($this->module['template_path'] . '/ss/' . $this->module['classname'] . '.css'))
            $this->template->css($this->module['template_url'] . '/ss/' . $this->module['classname'] . '.css');

        if (file_exists($this->module['template_path'] . '/js/' . $this->module['classname'] . '.js'))
            $this->template->js($this->module['template_url'] . '/js/' . $this->module['classname'] . '.js');

        $this->template->set('moduleUrl', $this->module['url']);
        $this->template->set('moduleTemplateUrl', $this->module['template_url']);

        $this->template->display();
    }

    /**
     * Loads the model based on the module context
     *
     * @param string $model the model
     * @return bool wether it worked
     */
    final protected function loadModel($model)
    {
        if (empty($model))
            return false;

        // Loading the template of another module? As in module/model
        if (strpos($model, '/') !== false)
        {
            $parts = explode('/', $model, 2);
            if (count($parts) > 0)
            {
                // Load the settings for this module and append the module name to each setting
                if (!isset($this->module['loaded_settings'][$parts['0']]) && is_readable($this->config->get('moduledir') . '/' . $parts['0'] . '/Settings.json'))
                {
                    $moduleSettings = json_decode(file_get_contents($this->config->get('moduledir') . '/' . $parts['0'] . '/Settings.json'), true);
                    foreach ($moduleSettings as $k => $v)
                        $this->module['settings'][$parts['0'] . '_' . $k] = $v;

                    $this->module['loaded_settings'][$parts['0']] = $this->config->get('moduledir') . '/' . $parts['0'] . '/Settings.json';
                }


                if (is_readable($this->config->get('moduledir') . '/' . $parts['0'] . '/models/' . $parts['1']. '.model.php'))
                    return require_once($this->config->get('moduledir') . '/' . $parts['0'] . '/models/' . $parts['1']. '.model.php');
                else if (is_readable($this->config->get('moduledir') . '/' . $parts['0'] . '/models/' . $parts['1']))
                    return require_once($this->config->get('moduledir') . '/' . $parts['0'] . '/models/' . $parts['1']);
            }

            unset($parts);
        }

        // Search the model inside this module
        if (is_readable($this->module['path'] . '/models/' . $model . '.model.php'))
            return require_once($this->module['path'] . '/models/' . $model . '.model.php');
        else if (is_readable($this->module['path'] . '/models/' . $model))
            return require_once($this->module['path'] . '/models/' . $model);

        return false;
    }

    /**
     * Loads a Third party library
     *
     * @param string $vendor the location
     * @return bool wether it worked
     */
    final protected function loadVendor($vendor)
    {
        $vendorPath = realpath($this->config->get('sourcedir') . '/../Vendor');

        if (is_readable($vendorPath . '/' . $vendor))
            return require_once($vendorPath . '/' . $vendor);
        else if (is_readable($vendor))
            return require_once($vendor);
        else
            return false;
    }

    /**
     * This method is called by the Dispatcher Object and it should be used
     * if the module wants to setup custom stuff before executing the do_action method.
     *
     * It should be overwritten by the module itself XD!
     *
     * @return void
     */
    public function beforeAction() {}

    /**
     * This method is called by the Dispatcher Object and it should be used
     * if the module wants to do an action before destruction.
     * Its something like a __destruct().
     *
     * It should be overwritten by the module itself XD!
     * By Default, outputs debug information in development mode.
     *
     * @return void
     */
    public function shutdownModule()
    {
        // Things we might want to do on development machines
        if (IN_DEVELOPMENT)
        {
            // Append debug/performance information to html pages
            foreach (headers_list() as $header)
            {
                if (stripos($header, 'text/html') !== false)
                {
                    echo  PHP_EOL;

                    if (defined('START_TIME'))
                        echo '<!-- created in ' . sprintf('%01.4f', ((float) array_sum(explode(' ',microtime())) - START_TIMER)) . ' seconds -->' . PHP_EOL;

                    echo '<!-- Total Errors: ' . $this->error->totalErrors() . ' -->' . PHP_EOL;
                    echo '<!-- Memory used ' . round((memory_get_peak_usage()/1024), 1) . 'KB/ ' . (@ini_get('memory_limit') != '' ? ini_get('memory_limit') : 'unknown') . ' -->' . PHP_EOL;
                    echo '<!-- ' . count(get_included_files()) . ' Includes -->' . PHP_EOL;
                    echo '<!-- ' . $this->config->get('serverLoad') . ' System Load -->' . PHP_EOL;
                    echo '<!-- Used Cache files: ' . $this->cache->usedCache() . ' -->' . PHP_EOL;
                    echo '<!-- Database Information: ' . $this->db . ' -->' . PHP_EOL;
                    echo '<!-- Router Information: ' . $this->router . ' -->' . PHP_EOL;
                    echo '<!-- Headers: ' . print_r(headers_list(), true) . '-->' . PHP_EOL;
                    break;
                }
            }

            if (mt_rand(0, 10) > 8)
                $this->cache->flush();
        }
    }
}
?>
