<?php
/**
 * TemplateNotificationHelper.model.php
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

class TemplateNotificationHelper
{
    protected $session;
    protected $hooks;

    /**
     * The session engine.
     *
     * @param object $session
     * @return void
     */
    public function setSessionEngine($session)
    {
        $this->session = $session;
    }

    /**
     * The Hooks engine.
     *
     * @param object $hooks
     * @return void
     */
    public function setHooksEngine($hooks)
    {
        $this->hooks = $hooks;
        $this->hooks->append(array('from_module' => 'main',
                                   'call' => array($this, 'readHtmlNotifications')), 'template_append_to_header');
    }

    /**
     * Reads if there are any html notifications for the current page
     * and uses jquery to display them.
     *
     * @param object template
     * @return void
     */
    public function readHtmlNotifications($template)
    {
        if ($this->session->has('htmlNotifications') && is_array($this->session->get('htmlNotifications')))
        {
            $notifications = $this->session->get('htmlNotifications');
            if (!empty($notifications))
            {
                $template->css('/Modules/main/templates/default/ss/frameworkCSS.css');
                $template->fjs('/Modules/main/templates/default/js/frameworkJS.js');

                foreach ($notifications as $n)
                    $template->fijs('$(function(){ BolidoDisplayNotifications(\'' . addcslashes($n['message'], '\'') . '\', \'' . addcslashes($n['class'], '\'') . '\', \'' . addcslashes($n['prepend'], '\'') . '\'); })');
            }

            $this->session->delete('htmlNotifications');
        }

    }

    /**
     * Sets Error/Warning/Success Notification
     *
     * @param string $notification The Message
     * @param string $type The type of the notification
     * @param string $prependTo The div were the notification should appear
     * @return void
     */
    public function setHtmlNotification($message = '', $type = 'success', $prependTo = 'body')
    {
        if (!in_array($type, array('success', 'error', 'warning', 'question')))
            $type = 'error';

        $notifications = array();
        if ($this->session->has('htmlNotifications') && is_array($this->session->get('htmlNotifications')))
            $notifications = $this->session->get('htmlNotifications');

        $notifications[] = array('message' => $message, 'class' => 'bolido-' . $type, 'prepend' => $prependTo);
        $this->session->set('htmlNotifications', $notifications);
    }
}
?>