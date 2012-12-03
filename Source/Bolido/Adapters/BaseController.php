<?php
/**
 * BaseController.php
 * The main module adapter class. All controllers should extend this class
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\App\Adapters;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

abstract class BaseController
{
    protected $settings = array();
    protected $app;

    /**
     * Every module must have an index method by default!
     */
    abstract public function index();

    /**
     * Loads important module information.
     * This method is called by the Dispatcher object.
     *
     * @param objecy $app
     * @return void
     */
    public function _loadSettings(\ArrayAccess &$app)
    {
        $this->app = $app;
        $this->settings['controller'] = $this->app['router']->controller;
        $this->settings['module']     = $this->app['router']->module;
        $this->settings['action']     = $this->app['router']->action;
        $this->settings['path']       = $this->app['config']->moduleDir . '/' . $this->settings['module'];
        $this->settings['url']        = $this->app['config']->mainUrl . '/' . $this->settings['module'];

        if (is_dir($this->settings['path'] . '/templates/' . $this->app['config']->skin))
        {
            $this->settings['template_path'] = $this->settings['path'] . '/templates/' . $this->app['config']->skin;
            $this->settings['template_url']  = $this->settings['url'] . '/templates/' . $this->app['config']->skin;
        }
        else
        {
            $this->settings['template_path'] = $this->settings['path'] . '/templates/default';
            $this->settings['template_url']  = $this->settings['url'] . '/templates/default';
        }

        // Load Custom Settings
        if (is_readable($this->settings['path'] . '/Settings.json'))
            $this->settings['module_settings'] = json_decode(file_get_contents($this->settings['path'] . '/Settings.json'), true);
    }

    /**
     * Gets a setting for this module
     *
     * @param string $key The Name of the setting
     * @return void
     */
    protected function setting($key)
    {
        if (isset($this->settings[$key]))
            return $this->settings[$key];
        else if (isset($this->settings['module_settings'][$key]))
            return $this->settings['module_settings'][$key];

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
    public function _flushTemplates()
    {
        // Append some stuff to the theme before the shit goes down!
        try
        {
            if (file_exists($this->settings['template_path'] . '/ss/' . $this->settings['module'] . '.css'))
                $this->app['template']->css($this->settings['template_url'] . '/ss/' . $this->settings['module'] . '.css');

            if (file_exists($this->settings['template_path'] . '/js/' . $this->settings['module'] . '.js'))
                $this->app['template']->js($this->settings['template_url'] . '/js/' . $this->settings['module'] . '.js');
        } catch (\Exception $e) {}

        $this->app['template']->set('moduleUrl', $this->settings['url']);
        $this->app['template']->set('moduleTemplateUrl', $this->settings['template_url']);
        $this->app['template']->display();
    }

    /**
     * This method is called by the Dispatcher Object and it should be used
     * if the module wants to setup custom stuff before executing the do_action method.
     *
     * It should be overwritten by the module itself XD!
     *
     * @return void
     */
    public function _beforeAction() {}

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
    public function _shutdownModule()
    {
        // Things we might want to do on development machines
        if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE)
        {
            // Append debug/performance information to html pages
            foreach (headers_list() as $header)
            {
                if (stripos($header, 'Content-Type: text/html') !== false)
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
