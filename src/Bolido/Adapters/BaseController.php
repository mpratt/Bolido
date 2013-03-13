<?php
/**
 * BaseController.php
 * The main module adapter class.
 * All controllers should extend this class
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Adapters;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

abstract class BaseController
{
    protected $flushTemplates = true;
    protected $settings = array();
    protected $app;

    /**
     * Every module must have an index method by default!
     *
     * @return void
     */
    abstract public function index();

    /**
     * Loads important module information.
     * This method is called by the Bootstrap file.
     *
     * @param object $app
     * @return void
     */
    public function _loadSettings(\ArrayAccess $app)
    {
        $this->app = $app;
        $this->settings['controller'] = $this->app['router']->controller;
        $this->settings['module']     = $this->app['router']->module;
        $this->settings['action']     = $this->app['router']->action;
        $this->settings['path']       = $this->app['config']->moduleDir . '/' . $this->settings['module'];
        $this->settings['url']        = $this->app['config']->mainUrl . '/' . $this->settings['module'];

        if (is_dir($this->settings['path'] . '/templates/' . $this->app['config']->skin))
        {
            $skin = $this->app['config']->skin;
            $this->settings['template_path'] = $this->settings['path'] . '/templates/' . $skin;
            $this->settings['template_url']  = $this->app['config']->mainUrl . '/modules/' . $this->settings['module']. '/templates/' . $skin;
        }
        else
        {
            $this->settings['template_path'] = $this->settings['path'] . '/templates/default';
            $this->settings['template_url']  = $this->app['config']->mainUrl . '/modules/' . $this->settings['module'] . '/templates/default';
        }

        // Try to autoload the language file of this module
        $this->app['lang']->load($this->settings['module'] . '/' . $this->settings['module']);

        // Load Custom Module Settings
        if (is_readable($this->settings['path'] . '/Settings.json'))
            $this->settings['module_settings'] = json_decode(file_get_contents($this->settings['path'] . '/Settings.json'), true);

        $this->app['hooks']->run('modify_template_engine', $this->app['template']);
    }

    /**
     * Gets a setting for this module
     *
     * @param string $key The Name of the setting
     * @return mixed
     */
    protected function setting($key)
    {
        if (isset($this->settings['module_settings'][$key]))
            return $this->settings['module_settings'][$key];
        else if (isset($this->settings[$key]))
            return $this->settings[$key];

        return null;
    }

    /**
     * Alias function for the setting method
     * @codeCoverageIgnore
     */
    protected function settings($key) { return $this->setting($key); }

    /**
     * Flushes all the templates loaded by the templateHandler class.
     * It also tries to append basic stuff.
     *
     * This method is called by the Dispatcher Object and it can be overwritten
     * inside the module itself!
     *
     * @return void
     */
    public function _flushTemplates()
    {
        if (!$this->flushTemplates)
            return ;

        // Append some stuff to the theme before this shit goes down!
        try
        {
            if (file_exists($this->settings['template_path'] . '/ss/' . $this->settings['module'] . '.css'))
                $this->app['template']->css($this->settings['template_url'] . '/ss/' . $this->settings['module'] . '.css');

            if (file_exists($this->settings['template_path'] . '/js/' . $this->settings['module'] . '.js'))
                $this->app['template']->js($this->settings['template_url'] . '/js/' . $this->settings['module'] . '.js');

            if (!$this->app['template']->hasTitle())
                $this->app['template']->setHtmlTitle('');

        } catch (\Exception $e) {}

        if (!empty($this->app['user']))
            $this->app['template']->set('user', $this->app['user'], true);

        $this->app['template']->set('moduleUrl', $this->settings['url'], true);
        $this->app['template']->set('moduleTemplateUrl', $this->settings['template_url'], true);
        $this->app['template']->display();
    }

    /**
     * This method is called by the Dispatcher Object and it should be used
     * if the module wants to setup custom stuff before executing the main method.
     *
     * It should be overwritten by the module itself XD!
     *
     * @return void
     */
    public function _beforeAction() {}

    /**
     * This method is called by the Dispatcher Object and it should be used
     * if the module wants to do an action before destruction.
     * Its something like a scheduled __destruct() method.
     *
     * It should be overwritten by the module itself XD!
     * By Default, outputs debug information in development mode and HTMl pages..
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function _shutdownModule()
    {
        if (DEVELOPMENT_MODE)
        {
            foreach (headers_list() as $header)
            {
                // Append debug/performance information to html pages
                if (stripos($header, 'Content-Type: text/html') !== false)
                {
                    echo PHP_EOL;
                    echo '<!-- Total Errors: ' . $this->app['error']->totalErrors() . ' -->' . PHP_EOL;

                    if (function_exists('memory_get_peak_usage'))
                        echo '<!-- Memory peak ' . round((memory_get_peak_usage()/1024), 1) . 'KB/' . (@ini_get('memory_limit') != '' ? ini_get('memory_limit') : 'unknown') . ' -->' . PHP_EOL;

                    echo '<!-- ' . count(get_included_files()) . ' Includes -->' . PHP_EOL;
                    echo '<!-- Used Cache files: ' . $this->app['cache']->usedCache() . ' -->' . PHP_EOL;

                    $dbDebug = $this->app['db']->debug();
                    echo '<!-- Database Information: ' . $dbDebug['queries'] . ' queries in ' . $dbDebug['total_time']. ' seconds -->' . PHP_EOL;

                    try {
                        echo '<!-- Benchmark timer: ' . $this->app['benchmark']->stopTimerTracker('Bootstrap-start') . ' seconds -->' . PHP_EOL;
                    } catch (\Exception $e) {}
                    break;
                }
            }
        }
    }
}
?>
