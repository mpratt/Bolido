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

        // Load headers and footers in the template queue by default. If you dont need them then remove them
        $this->load('main/main-header-above');
        $this->load('main/main-footer-bottom');

        // Default Data
        $this->setHtmlTitle();
        $this->allowHtmlIndexing();
        $this->highlightHtmlTab();
    }

    /**
     * Appends a template in the template queue
     *
     * @param string $template Name of the Template file
     * @return string The name full path to the template or an exception otherwise
     */
    public function load($template)
    {
        $locations = array($template,
                           $this->config->get('moduledir') . '/' . $this->moduleContext . '/templates/' . $this->config->get('skin') . '/' . $template);

        // Loading the template from another module context? As in users/where
        if (strpos($template, '/') !== false)
        {
            $parts = explode('/', strtolower($template));
            if (count($parts) == 2)
            {
                $locations[] = $this->config->get('moduledir') . '/' . $parts['0'] . '/templates/default/' . $parts['1'];
                $locations[] = $this->config->get('moduledir') . '/' . $parts['0'] . '/templates/' . $this->config->get('skin') . '/' . $parts['1'];
            }
        }

        $file = false;
        foreach ($locations as $location)
        {
            // sniff if the user is in a mobile plataform and load the mobile template. Else load the regular one
            if ($this->browser->isMobile() && is_readable($location . '.mobile.tpl.php'))
                $file = $location . '.mobile.tpl.php';
            else if (is_readable($location . '.tpl.php'))
                $file = $location . '.tpl.php';

            if (!empty($file))
                break;
        }

        if ($file === false)
            throw new Exception('The template "' . $template . '" was not found');

        // If the footer exists, append the template before it
        $key = $this->search('main-footer-bottom');
        if ($key !== false)
            array_splice($this->queue, $key, 0, $file);
        else
            $this->queue[] = $file;

        return $file;
    }

    /**
     * Actually processes the template queue and saves its output to $this->body
     *
     * @return string
     */
    protected function generateBody()
    {
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
                if (!empty($this->contentType))
                    header('Content-Type: ' . $this->contentType . '; charset=' . $this->config->get('charset'));
            }

            $this->hooks->run('before_template_display', $this->contentType);
            echo $body;
        }
    }

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
        $allowed = array('css'  => '<link rel="stylesheet" href="{|placeholder|}' . (IN_DEVELOPMENT ? '?' . time() : '') . '" type="text/css" />',
                         'js'   => '<script type="text/javascript" src="{|placeholder|}' . (IN_DEVELOPMENT ? '?' . time() : '') . '"></script>',
                         'ijs'  => '<script type="text/javascript">{|placeholder|}</script>',
                         'fjs'  => '<script type="text/javascript" src="{|placeholder|}' . (IN_DEVELOPMENT ? '?' . time() : '') . '"></script>',
                         'fijs' => '<script type="text/javascript">{|placeholder|}</script>');

        if (!isset($allowed[$section]) || empty($action))
            throw new Exception('Unknown method ' . $method . ' Or empty action ' . $action);

        while (isset($this->toHeader[$priority]) || isset($this->toFooter[$priority]))
                $priority++;

        $code = str_replace('{|placeholder|}', $action, $allowed[$section]);
        if (in_array($section, array('css', 'js', 'ijs', 'jscss')))
            $this->toHeader[$priority] = $code;
        else
            $this->toFooter[$priority] = $code;

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
     * @return void
     */
    public function setHtmlNotification($notification = '', $type = 'success')
    {
        $types = array('success'  => 'htmlSuccessMessage',
                       'error'    => 'htmlErrorMessage',
                       'warning'  => 'htmlWarningMessage',
                       'question' => 'htmlQuestionMessage');

        if (!isset($types[$type]))
            $type = 'error';

        $this->session->set($types[$type], $notification);
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
    protected function search($template)
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