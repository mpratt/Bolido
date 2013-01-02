<?php
/**
 * Template.php
 * Handles templates
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Template
{
    public $config;
    public $hooks;
    public $lang;
    public $session;
    protected $extensions = array();

    // Template Information
    protected $templates = array();
    protected $templateValues = array();
    protected $contentType = 'text/html';

    /**
     * Construct
     * Loads important objects and sets flags for future tests
     *
     * @param object $config
     * @param object $lang
     * @param object $session
     * @param object $hooks
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config,
                                \Bolido\Lang $lang,
                                \Bolido\Session $session,
                                \Bolido\Hooks $hooks)
    {
        $this->config  = $config;
        $this->lang    = $lang;
        $this->session = $session;
        $this->hooks   = $hooks;
    }

    /**
     * Appends a template in the template queue
     *
     * @param string $template Name of the Template file
     * @param array  $data     Associative array with data to be passed to the template
     * @param bool   $lazy     Wether to queue the output of the current template and data
     *                         or just queue the template file and process the queue later.
     *                         This allows to load a template file or a html string multiple times,
     *                         with different values.
     * @return void
     */
    public function load($template, $data = array(), $lazy = false)
    {
        $template = preg_replace('~\.tpl\.php$~i', '', $template);
        if ($lazy)
        {
            if (!empty($data) && is_array($data))
                extract($data);

            ob_start();
            try
            {
                include($this->findTemplate($template));
            } catch (\Exception $e) { echo $template; }

            $body = ob_get_contents();
            ob_end_clean();

            if (!empty($body))
                $this->templates[] = array('string' => $body);
        }
        else
        {
            $this->queueTemplate($template);
            if (!empty($data))
            {
                foreach ($data as $name => $value)
                    $this->set($name, $value);
            }
        }
    }

    /**
     * Finds and puts a template file in the queue.
     *
     * @param string $template
     * @return void
     */
    public function queueTemplate($template)
    {
        $template = preg_replace('~\.tpl\.php$~i', '', $template);
        $this->templates[$template] = array('file' => $this->findTemplate($template));
    }

    /**
     * Finds the full path to a template file.
     *
     * @param string $template Name of the Template file
     * @param bool $appendToQueue Wether or not the template should be appended to the queue.
     * @return string The full path to the template file.
     */
    protected function findTemplate($template)
    {
        if (strpos($template, '/') === false)
            throw new \InvalidArgumentException('The template "' . $template . '" seems to be invalid.');

        $locations = array();
        $template = preg_replace('~\.tpl\.php$~i', '', $template);
        list($module, $file) = explode('/', $template, 2);

        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/' . $this->config->skin . '/' . $file . '.tpl.php';
        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/default/' . $file . '.tpl.php';
        $locations[] = $file . '.tpl.php';
        $locations[] = $file;

        foreach(array_unique($locations) as $l)
        {
            if (is_readable($l))
                return $l;
        }

        throw new \InvalidArgumentException('The template "' . $template . '" was not found');
    }

    /**
     * Alias for the findTemplate method
     */
    protected function f($template) { return $this->findTemplate($template); }

    /**
     * Actually processes the template queue
     *
     * @return string
     */
    protected function generateBody()
    {
        $this->hooks->run('before_template_body', $this);
        if (!empty($this->templates))
        {
            ob_start();
            extract($this->templateValues);
            foreach ($this->templates as $template)
            {
                if (!empty($template['file']))
                    include($template['file']);
                else if (!empty($template['string']))
                    echo $template['string'];
            }

            $body = ob_get_contents();
            ob_end_clean();

            $body = $this->hooks->run('filter_template_body', $body);
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
                        $headers['content-type'] = $this->contentType . '; charset=' . $this->config->charset;

                    foreach ($headers as $k => $v)
                    {
                        if (!empty($k) && !is_numeric($k))
                        {
                            $k = implode('-', array_map('ucfirst', explode('-', $k)));
                            header($k . ': ' . $v);
                        }
                        else
                            header($v);
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
     * @param bool $overwrite When true, overwrite allready setted keys
     * @return void
     */
    public function set($key, $value, $overwrite = false)
    {
        if (isset($this->templateValues[$key]) && !$overwrite)
            throw new \InvalidArgumentException('Template key ' . $key . ' was already defined');

        $this->templateValues[$key] = $value;
    }

    /**
     * Extends the functionality of this object
     *
     * @param string $name The name of the method
     * @param callable $callable A function or a
     * @return void
     */
    public function extend($name, $callable)
    {
        if (isset($this->extensions[$name]) || method_exists($this, $name))
            throw new \InvalidArgumentException('The method ' . $name . ' already exists.');

        if (is_callable($callable) || is_object($callable))
        {
            if (is_object($callable))
            {
                if (!is_a($callable, '\Closure') && !is_callable(array($callable, $name)))
                    throw new \InvalidArgumentException('You cannot extend the template object with this object.');
            }

            $this->extensions[$name] = $callable;
            return ;
        }

        throw new \InvalidArgumentException('The specified extension is invalid.');
    }

    /**
     * This magic method calls extensions that were registered.
     *
     * A Exception is thrown if a method was not found.
     *
     * @param string $method The name of the method.
     * @param array  $parameters Parameters passed to the method.
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset($this->extensions[$method]))
        {
            if (is_object($this->extensions[$method]) && !is_a($this->extensions[$method], '\Closure'))
                return call_user_func_array(array($this->extensions[$method], $method), $parameters);
            else
                return call_user_func_array($this->extensions[$method], $parameters);
        }

        throw new \RuntimeException('Unknown method ' . $method . ' in the Template Object');
    }

    /**
     * Sets the http header content type
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType) { $this->contentType = $contentType; }

    /**
     * Removes all templates in the templates queue
     *
     * @return void
     */
    public function clear() { $this->templates = array(); }

    /**
     * Removes a template from the templates queue
     *
     * @param string $template Name of the Template file
     * @return bool
     */
    public function remove($template)
    {
        $template = str_replace('.tpl.php', '', $template);
        if (isset($this->templates[$template]))
        {
            unset($this->templates[$template]);
            return true;
        }

        return false;
    }
}
?>
