<?php
/**
 * TemplateHandler.class.php
 * Handles templates
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

class TemplateHandler
{
    protected $config;
    protected $user;
    protected $Lang;
    protected $session;
    protected $hooks;
    protected $moduleContext;
    public $browser;

    // Template Information
    protected $queue = array();
    protected $templateValues = array();
    protected $toHeader = array();
    protected $toFooter = array();
    protected $contentType = 'text/html';
    protected $appendPriority = '1000';

    // Overload array
    protected $overload = array();

    /**
     * Construct
     * Loads important objects and sets flags for future tests
     *
     * @param object $config
     * @param object $user
     * @param object $lang
     * @param object $hooks
     * @param string $moduleContext
     * @return void
     */
    public function __construct(Config $config, iUser $user, Lang $lang, SessionHandler $session, Hooks $hooks, $moduleContext = 'main')
    {
        $this->config  = $config;
        $this->user    = $user;
        $this->lang    = $lang;
        $this->session = $session;
        $this->hooks   = $hooks;
        $this->browser = new BrowserHandler((!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        $this->moduleContext = $moduleContext;

        // Default Data
        $this->setHtmlTitle();
        $this->allowHtmlIndexing();
    }

    /**
     * Appends a template in the template queue
     *
     * @param string $template Name of the Template file
     * @param array  $data Associative array with data to be passed to the template
     * @return void
     */
    public function load($template, $data = array())
    {
        $this->queue[] = $this->findTemplate($template);

        if (!empty($data))
        {
            foreach ($data as $name => $value)
                $this->set($name, $value);
        }
    }

    /**
     * Finds the full path to a template file.
     * It decides if it should be a regular template or a mobile one.
     *
     * @param string $template Name of the Template file
     * @return string Full path to the template or throw an exception if not found.
     */
    protected function findTemplate($template)
    {
        $template  = str_replace(array('.mobile.tpl.php', '.tpl.php'), '', $template);
        $locations = array($this->config->get('moduledir') . '/' . $this->moduleContext . '/templates/' . $this->config->get('skin') . '/' . $template);

        // Loading the template from another module context? As in users/where
        if (strpos($template, '/') !== false)
        {
            $parts = explode('/', strtolower($template));
            if (count($parts) == 2)
            {
                $locations[] = $this->config->get('moduledir') . '/' . $parts['0'] . '/templates/default/' . $parts['1'];

                if ($this->config->get('skin') != 'default')
                    $locations[] = $this->config->get('moduledir') . '/' . $parts['0'] . '/templates/' . $this->config->get('skin') . '/' . $parts['1'];
            }

            $locations[] = $template;
        }

        foreach ($locations as $location)
        {
            // sniff if the user is in a mobile plataform and load the mobile template. Else load the regular one
            if ($this->browser->isMobile() && is_readable($location . '.mobile.tpl.php'))
                return $location . '.mobile.tpl.php';
            else if (is_readable($location . '.tpl.php'))
                return $location . '.tpl.php';
        }

        throw new Exception('The template "' . $template . '" was not found');
    }

    /**
     * Alias for the findTemplate method
     */
    public function f($template) { return $this->findTemplate($template); }

    /**
     * Actually processes the template queue and saves its output to $this->body
     *
     * @return string
     */
    protected function generateBody()
    {
        $this->readHtmlNotifications();
        if (!empty($this->queue))
        {
            $this->toHeader = $this->hooks->run('template_append_to_header', $this->toHeader);
            $this->toFooter = $this->hooks->run('template_append_to_footer', $this->toFooter);

            ksort($this->toHeader);
            ksort($this->toFooter);

            ob_start();
            extract($this->templateValues);
            foreach ($this->queue as $template)
                include($template);

            $body = ob_get_contents();
            ob_end_clean();

            $body = $this->hooks->run('filter_template_body', $body);
            $this->lang->free();

            return $body;
        }

        return ;
    }

    /**
     * Sends headers and displays the html.
     *
     * @param string $contentType. The content type header that will be sent
     * @return void
     */
    public function display()
    {
        $body = $this->generateBody();
        if (!empty($body))
        {
            if (!headers_sent())
            {
                header('Cache-Control: private');
                header('Pragma: private');
                header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

                if (!empty($this->contentType))
                    header('Content-Type: ' . $this->contentType . '; charset=' . $this->config->get('charset'));
            }

            $this->hooks->run('before_template_display', $this->contentType);
            echo $body;
        }
    }

    /**
     * This method overloads this class with new methods.
     * This way you can extend the functionality of this object.
     *
     * @param string $name The name of the function/method.
     * @param mixed $function the function or object method.
     * @return void
     */
    public function addMethod($name, $function) { $this->overload[strtolower($name)] = $function; }

    /**
     * Methods that append javascript or stylesheets
     * to the template.
     *
     * It takes into account the following methods
     * js   Appends js file to html header
     * css  Appends css file to html header
     * ijs  Appends inline js to the header
     * fjs  Appends js file to html footer
     * fijs Appends inline js to the footer
     *
     * @return void
     */
    public function __call($method, $parameters)
    {
        $section  = strtolower($method);
        $action   = $parameters['0'];
        $priority = (!empty($parameters['1']) ? intval($parameters['1']) : $this->appendPriority);
        $allowed = array('css'  => '<link rel="stylesheet" href="{|placeholder|}" type="text/css" />',
                         'js'   => '<script type="text/javascript" src="{|placeholder|}"></script>',
                         'ijs'  => '<script type="text/javascript">{|placeholder|}</script>',
                         'fjs'  => '<script type="text/javascript" src="{|placeholder|}"></script>',
                         'fijs' => '<script type="text/javascript">{|placeholder|}</script>');

        // Is the method known?
        if (!isset($allowed[$section]))
        {
            // Try to find out if the method was overloaded elsewhere.
            if (!empty($this->overload[$section]))
                return call_user_func_array($this->overload[$section], $parameters);

            throw new Exception('Unknown method ' . $method);
        }

        if (empty($action))
            throw new Exception('Empty Action ' . $action);

        if (strpos($action, '{|placeholder|}') !== false)
            throw new Exception('You cannot use the word {|placeholder|} inside your code');

        // Append timestamp to css, js and footer js in development mode
        if (IN_DEVELOPMENT && in_array($section, array('css', 'js', 'fjs')))
        {
                if (strpos($action, '?') !== false)
                    $action .= '&bolidoNoCacheRandNumber=' . time();
                else
                    $action .= '?' . time();
        }

        $code = trim(str_replace('{|placeholder|}', $action, $allowed[$section]));
        if (in_array($section, array('css', 'js', 'ijs')))
        {
            if (!empty($this->toHeader[$priority]))
                array_splice($this->toHeader, --$priority, 0, $code);
            else
                $this->toHeader[$priority] = $code;
        }
        else
        {
            if (!empty($this->toFooter[$priority]))
                array_splice($this->toFooter, --$priority, 0, $code);
            else
                $this->toFooter[$priority] = $code;
        }

        $this->appendPriority++;
        return ;
    }

    /**
     * Passes values so that they can be used inside the templates
     *
     * @param string $key
     * @param string $value
     * @param bool $ignoreTaken When true, overwrite allready setted keys
     * @return void
     */
    public function set($key, $value, $ignoreTaken = false)
    {
        if (isset($this->templateValues[$key]) && !$ignoreTaken)
            throw new Exception('Template key ' . $key . ' is already taken');

        $this->templateValues[$key] = $value;
    }

    /**
     * Sets the http header content type
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType) { $this->contentType = $contentType; }

    /**
     * Sets the Title tag for HTML pages
     *
     * @param string $title
     * @param bool $appendSiteTitle When true appends the site title
     * @return void
     */
    public function setHtmlTitle($title = '', $appendSiteTitle = true)
    {
        if (!empty($title))
        {
            if ($appendSiteTitle)
                $htmlTitle = $this->config->get('siteTitle') . ' - ' . $title;
            else
                $htmlTitle = $title;
        }
        else
            $htmlTitle = $this->config->get('siteTitle');

        $this->set('htmlTitle', htmlspecialchars($htmlTitle, ENT_QUOTES, 'UTF-8', false), true);
    }

    /**
     * Sets the Description tag for HTML pages
     *
     * @param string $htmlDescription
     * @return void
     */
    public function setHtmlDescription($htmlDescription = '')
    {
        $htmlDescription = strip_tags($htmlDescription);
        if (strlen($htmlDescription) > 500)
            $htmlDescription = substr($htmlDescription, 0, strpos($htmlDescription, ' ', 500)) . '...';

        $this->set('htmlDescription', htmlspecialchars($htmlDescription, ENT_QUOTES, 'UTF-8', false), true);
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

    /**
     * Reads if there are any html notifications for the current page
     * and uses jquery to display them.
     *
     * @return void
     */
    protected function readHtmlNotifications()
    {
        if ($this->session->has('htmlNotifications') && is_array($this->session->get('htmlNotifications')))
        {
            $notifications = $this->session->get('htmlNotifications');

            if (!empty($notifications))
            {
                $this->css('/Modules/main/templates/default/ss/frameworkCSS.css');
                $this->fjs('/Modules/main/templates/default/js/frameworkJS.js');

                foreach($notifications as $n)
                    $this->fijs('$(function(){ BolidoDisplayNotifications(\'' . addcslashes($n['message'], '\'') . '\', \'' . addcslashes($n['class'], '\'') . '\', \'' . addcslashes($n['prepend'], '\'') . '\')})');
            }

            $this->session->delete('htmlNotifications');
        }
    }

    /**
     * Allows or dissallows crawlers to index the current page
     *
     * @param bool $allow
     * @return void
     */
    public function allowHtmlIndexing($allow = true) { $this->set('htmlIndexing', $allow, true); }

    /**
     * Highlights a tab
     *
     * @param string $tab
     * @return void
     */
    public function highlightHtmlTab($tab = 'index') { $this->set('htmlTab', $tab, true); }

    /**
     * Appends a template in the template queue
     *
     * @param string $template Name of the Template file
     * @return int The key where the template is loaded or false if not found
     */
    public function search($template)
    {
        if (empty($this->queue))
            return false;

        foreach ($this->queue as $k => $v)
        {
            if (stripos($v, $template . '.tpl.php') !== false || stripos($v, $template . '.mobile.tpl.php'))
                return $k;
        }

        return false;
    }

    /**
     * Removes all templates in the template queue
     *
     * @return void
     */
    public function resetTemplates() { $this->queue = array(); }
    public function clearTemplates() { $this->resetTemplates(); }

    /**
     * Removes a template from the queue
     *
     * @param string $template Name of the Template file
     * @return bool
     */
    public function remove($template)
    {
        if (empty($this->queue))
            return false;

        $key = $this->search($template);
        if ($key !== false)
        {
            unset($this->queue[$key]);
            return true;
        }

        return false;
    }
}
?>