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
        $data = (array) $data;
        if ($lazy)
        {
            extract($data);
            ob_start();

            try {
                include $this->findTemplate($template);
            } catch (\Exception $e) { echo $template; }

            $this->templates[] = ob_get_contents();
            ob_end_clean();
        }
        else
        {
            $template = preg_replace('~\.tpl\.php$~i', '', $template);
            $this->templates[$template] = $this->findTemplate($template);
            $this->templateValues = array_merge($data, $this->templateValues);
        }
    }

    /*
     * Finds the full path to a template file.
     *
     * @param string $template Name of the Template file
     * @param bool $appendToQueue Wether or not the template should be appended to the queue.
     * @return string The full path to the template file.
     */
    protected function findTemplate($template)
    {
        if (strpos($template, '/') !== false)
        {
            list($module, $file) = explode('/', $template, 2);
            $files = array($this->config->moduleDir . '/' . $module . '/templates/' . $this->config->skin . '/' . $file . '.tpl.php',
                           $this->config->moduleDir . '/' . $module . '/templates/default/' . $file . '.tpl.php');

            foreach(array_unique($files) as $f)
            {
                if (is_readable($f))
                    return $f;
            }
        }

        throw new \InvalidArgumentException('The template "' . $template . '" was not found');
    }

    /**
     * Actually processes the template queue
     *
     * @return string
     */
    protected function generateBody()
    {
        $this->hooks->run('before_template_body', $this);
        if (empty($this->templates))
            return ;

        ob_start();
        extract($this->templateValues);
        foreach ($this->templates as $template)
        {
            if (file_exists($template))
                include($template);
            else
            {
                echo $template;
            }
        }

        $body = ob_get_contents();
        ob_end_clean();

        return $this->hooks->run('filter_template_body', $body);
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
        $headers = array('cache-control' => 'private',
                         'pragma'  => 'private',
                         'expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
                         'last-modified' => gmdate('D, d M Y H:i:s') . ' GMT',
                         'content-type'  => $this->contentType . '; charset=' . $this->config->charset);

        $headers = $this->hooks->run('modify_http_headers', $headers);

        // @codeCoverageIgnoreStart
        if (!headers_sent())
        {
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
        // @codeCoverageIgnoreEnd

        $this->hooks->run('before_template_display', $this->contentType);
        echo $body;
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
    public function extend($name, Callable $callback)
    {
        if (isset($this->extensions[$name]) || method_exists($this, $name))
            throw new \InvalidArgumentException('The method ' . $name . ' already exists.');

        $this->extensions[strtolower($name)] = $callback;
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
        $method = strtolower($method);
        if (isset($this->extensions[$method]))
            return call_user_func_array($this->extensions[$method], $parameters);

        throw new \RuntimeException('Unknown method ' . $method . ' in the Template Object');
    }

    /**
     * Sets the http header content type
     *
     * @param string $contentType
     * @return void
     *
     * @codeCoverageIgnore
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
     * @return void
     */
    public function remove($template)
    {
        $template = str_replace('.tpl.php', '', $template);
        unset($this->templates[$template]);
    }
}
?>
