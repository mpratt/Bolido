<?php
/**
 * MainNotificationExtender.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Module\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class MainNotificationExtender
{
    protected $session;
    protected $htmlExtender;

    /**
     * Construct
     *
     * @param object $htmlExtender
     * @param object $session
     * @return void
     */
    public function __construct(\Bolido\Session $session, \Bolido\Module\main\models\MainTemplateExtender $htmlExtender)
    {
        $this->htmlExtender = $htmlExtender;
        $this->session = $session;
    }

    /**
     * Reads if there are any html notifications for the current page
     * and uses jquery to display them.
     *
     * @param object config
     * @return void
     */
    public function detect(\Bolido\Config $config)
    {
        if ($this->session->has('bolidoHtmlNotifications') && is_array($this->session->get('bolidoHtmlNotifications')))
        {
            $notifications = $this->session->get('bolidoHtmlNotifications');
            if (!empty($notifications))
            {
                $this->htmlExtender->css($config->mainUrl . '/Modules/main/templates/default/ss/notifications.css');
                foreach ($notifications as $n)
                    $this->htmlExtender->fijs('$(function(){ Bolido.notify(\'' . addcslashes($n['message'], '\'') . '\', \'' . addcslashes($n['class'], '\'') . '\', \'' . addcslashes($n['prepend'], '\'') . '\', ' . $n['delay'] . '); })');
            }

            $this->session->delete('bolidoHtmlNotifications');
        }
    }

    /**
     * Sets Error/Warning/Success Notification
     *
     * @param string $notification The Message
     * @param string $type The type of the notification
     * @param string $prependTo The div were the notification should appear
     * @param int    $delay
     * @return void
     */
    protected function setHtmlNotification($message = '', $type = 'success', $prependTo = 'body', $delay = 0)
    {
        if (!in_array($type, array('success', 'error', 'warning', 'question')))
            $type = 'error';

        $notifications = array();
        if ($this->session->has('bolidoHtmlNotifications') && is_array($this->session->get('bolidoHtmlNotifications')))
            $notifications = $this->session->get('bolidoHtmlNotifications');

        $notifications[] = array('message' => $message, 'class' => 'bolido-' . $type, 'prepend' => $prependTo, 'delay' => (int) $delay);
        $this->session->set('bolidoHtmlNotifications', $notifications);
    }

    /**
     * Helper Methods for the setHtmlNotification method.
     */
    public function notifyError($message = '', $prependTo = 'body', $delay = 0)
    {
        $this->setHtmlNotification($message, 'error', $prependTo, $delay);
    }

    public function notifyWarning($message = '', $prependTo = 'body', $delay = 0)
    {
        $this->setHtmlNotification($message, 'warning', $prependTo, $delay);
    }

    public function notifySuccess($message = '', $prependTo = 'body', $delay = 0)
    {
        $this->setHtmlNotification($message, 'success', $prependTo, $delay);
    }

    public function notifyQuestion($message = '', $prependTo = 'body', $delay = 0)
    {
        $this->setHtmlNotification($message, 'question', $prependTo, $delay);
    }
}
?>
