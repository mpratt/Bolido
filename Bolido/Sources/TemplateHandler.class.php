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
    protected $hooks;
    protected $moduleContext;
    protected $session;
    protected $user;
    protected $Lang;
    public $browser;

    // Template Information
    protected $queue = array();
    protected $templateValues = array();
    protected $contentType = 'text/html';

    // Helpers
    protected $helpers = array();

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
    public function __construct(iConfig $config, iUser $user, Lang $lang, Session $session, Hooks $hooks, $moduleContext = 'main')
    {
        $this->config  = $config;
        $this->user    = $user;
        $this->lang    = $lang;
        $this->session = $session;
        $this->hooks   = $hooks;
        $this->browser = new BrowserHandler((!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        $this->moduleContext = $moduleContext;

        $this->loadHelpers();
    }

    /**
     * Loads Template helpers in the $this->helpers array
     *
     * @return void
     */
    protected function loadHelpers()
    {
        $helpers = $this->hooks->run('template_register_helpers', array());
        if (!empty($helpers) && is_array($helpers))
        {
            foreach($helpers as $h)
                $this->registerHelper($h);
        }
    }

    /**
     * Appends a single Helper to this object.
     * Is used for overloading methods.
     *
     * @param mixed $helper
     * @return void
     */
    public function registerHelper($helper)
    {
        if (empty($helper))
            return ;

        if (is_object($helper))
        {
            if (method_exists($helper, 'setConfigEngine'))
                $helper->setConfigEngine($this->config);

            if (method_exists($helper, 'setSessionEngine'))
                $helper->setSessionEngine($this->session);

            if (method_exists($helper, 'setLanEngine'))
                $helper->setLangEngine($this->lang);

            if (method_exists($helper, 'setUserEngine'))
                $helper->setUserEngine($this->user);

            if (method_exists($helper, 'setHooksEngine'))
                $helper->setHooksEngine($this->hooks);
        }

        $this->helpers[] = $helper;
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
        $this->hooks->run('before_template_body_generation', $this);
        if (!empty($this->queue))
        {
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
                $headers = array('cache-control' => 'private',
                                 'pragma' => 'private',
                                 'expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
                                 'last-modified' => gmdate('D, d M Y H:i:s') . ' GMT');

                $headers = $this->hooks->run('modify_http_headers', $headers);
                if (!empty($headers) && is_array($headers))
                {
                    if (!empty($this->contentType))
                        $headers['content-type'] = $this->contentType . '; charset=' . $this->config->get('charset');

                    foreach ($headers as $k => $v)
                    {
                        if (strpos($k, '-') !== false)
                        {
                            $parts = explode('-', $k);
                            $name  = implode('-', array_map('ucfirst', $parts));
                        }
                        else
                            $name = ucfirst($k);

                        header($name . ': ' . $v);
                    }
                }
            }

            $this->hooks->run('before_template_display', $this->contentType);
            echo $body;
        }
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

    /**
     * This magic method is used for calling
     * previously registered methods.
     *
     * A Exception is thrown if a method was not found.
     *
     * @param string $method The name of the method.
     * @param array $parameters Parameters passed to the method.
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!empty($this->helpers))
        {
            $ret = null;
            foreach ($this->helpers as $helper)
            {
                if (method_exists($helper, $method))
                    return call_user_func_array(array($helper, $method), $parameters);
                else if ($helper == $method && is_callable($helper))
                    return call_user_func_array($helper, $parameters);
            }
        }

        throw new Exception('Unknown method ' . $method . ' in the Template Object');
    }
}
?>
