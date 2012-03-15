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
    protected $template;

    /**
     * The template engine.
     *
     * @param object $template
     * @return void
     */
    public function setTemplateEngine($template)
    {
        $this->template = $template;
        $this->template->hooks->append(array('from_module' => 'main',
                                             'call' => array($this, 'readHtmlNotifications'), 'before_template_body_generation'));
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
        if ($this->template->session->has('htmlNotifications') && is_array($this->template->session->get('htmlNotifications')))
            $notifications = $this->template->session->get('htmlNotifications');

        $notifications[] = array('message' => $message, 'class' => 'bolido-' . $type, 'prepend' => $prependTo);
        $this->template->session->set('htmlNotifications', $notifications);
    }

    /**
     * Reads if there are any html notifications for the current page
     * and uses jquery to display them.
     *
     * @return void
     */
    public function readHtmlNotifications()
    {
        if ($this->template->session->has('htmlNotifications') && is_array($this->template->session->get('htmlNotifications')))
        {
            $notifications = $this->template->session->get('htmlNotifications');

            if (!empty($notifications))
            {
                $this->template->css('/Modules/main/templates/default/ss/frameworkCSS.css');
                $this->template->fjs('/Modules/main/templates/default/js/frameworkJS.js');

                foreach($notifications as $n)
                    $this->template->fijs('$(function(){ BolidoDisplayNotifications(\'' . addcslashes($n['message'], '\'') . '\', \'' . addcslashes($n['class'], '\'') . '\', \'' . addcslashes($n['prepend'], '\'') . '\')})');
            }

            $this->template->session->delete('htmlNotifications');
        }
    }
}
?>