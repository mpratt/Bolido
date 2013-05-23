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
     * Flushes all the templates loaded by the templateHandler class.
     * It also tries to append basic stuff.
     *
     * @param string $templates
     * @param array $values
     * @param string $contentType
     * @return void
     */
    protected function display($template, array $values = array(), $contentType = 'text/html')
    {
        $moduleAssets = array();
        if ($this->app['session']->has('bolidoHtmlNotifications'))
        {
            $notifications = (array) $this->app['session']->get('bolidoHtmlNotifications');
            $moduleAssets[] = '<script type="text/javascript">$(function(){ Bolido.notify(' . json_encode($notifications) . '); })</script>';
            $this->app['session']->delete('bolidoHtmlNotifications');
        }

        if (file_exists($this->settings['template_path'] . '/ss/' . $this->settings['module'] . '.css'))
        {
            $moduleAssets[] = sprintf('<link rel="stylesheet" href="%s" type="text/css">',
                                      $this->settings['template_url'] . '/ss/' . $this->settings['module'] . '.css');
        }

        if (file_exists($this->settings['template_path'] . '/js/' . $this->settings['module'] . '.js'))
        {
            $moduleAssets[] = sprintf('<script type="text/javascript" src="%s"></script>',
                                      $this->settings['template_url'] . '/js/' . $this->settings['module'] . '.js');
        }

        $defaultValues = array('user' => $this->app['user'],
                               'moduleTemplateUrl' => $this->settings['template_url'],
                               'moduleUrl' => $this->settings['url'],
                               'moduleAssets' => implode('', $moduleAssets),
                               'developmentMode' => DEVELOPMENT_MODE);

        $this->sendHeaders($contentType);
        echo $this->app['twig']->render($template, array_merge($defaultValues, $values));
    }

    /**
     * Sends the headers for this request.
     *
     * @param string $contentType
     * @return void
     *
     * @codeCoverageIgnore
     */
    protected function sendHeaders($contentType)
    {
        if (!headers_sent())
        {
            $headers = array('cache-control' => 'private',
                             'pragma'  => 'private',
                             'expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
                             'last-modified' => gmdate('D, d M Y H:i:s') . ' GMT',
                             'content-type'  => $contentType . '; charset=UTF-8');

            $headers = $this->app['hooks']->run('modify_http_headers', $headers);
            foreach ($headers as $k => $v)
            {
                if (!is_numeric($k))
                {
                    $k = implode('-', array_map('ucfirst', explode('-', $k)));
                    header($k . ': ' . $v);
                }
                else
                    header($v);
            }
        }
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
     *
     * @return void
     */
    public function _shutdownModule() {}

    /**
     * Sets Error/Warning/Success Notifications
     *
     * @param string $notification  The Message
     * @param string $type          The type of the notification {success, error, question, warning}
     * @param string $prependTo     The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function notify($message = '', $type = 'success', $prependTo = 'body', $delay = 0)
    {
        $notifications = array();
        if ($this->app['session']->has('bolidoHtmlNotifications'))
            $notifications = (array) $this->app['session']->get('bolidoHtmlNotifications');

        $notifications[] = array('message' => $this->app['lang']->get($message),
                                 'class' => 'bolido-' . $type,
                                 'prepend' => $prependTo,
                                 'delay' => (int) $delay);

        $this->app['session']->set('bolidoHtmlNotifications', $notifications);
    }

    /**
     * Sets a Error Notification
     *
     * @param string $notification  The Message
     * @param string $prependTo     The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function notifyError($message = '', $prependTo = 'body', $delay = 0) { $this->notify($message, 'error', $prependTo, $delay); }

    /**
     * Sets a Warning Notification
     *
     * @param string $notification  The Message
     * @param string $prependTo     The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function notifyWarning($message = '', $prependTo = 'body', $delay = 0) { $this->notify($message, 'warning', $prependTo, $delay); }

    /**
     * Sets a Success Notification
     *
     * @param string $notification  The Message
     * @param string $prependTo     The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function notifySuccess($message = '', $prependTo = 'body', $delay = 0) { $this->notify($message, 'success', $prependTo, $delay); }

    /**
     * Sets a Question Notification
     *
     * @param string $notification  The Message
     * @param string $prependTo     The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function notifyQuestion($message = '', $prependTo = 'body', $delay = 0) { $this->notify($message, 'question', $prependTo, $delay); }
}

?>
