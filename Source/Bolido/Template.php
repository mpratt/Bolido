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

namespace Bolido\App;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Template
{
    public $config;
    public $hooks;
    public $Lang;
    public $session;
    protected $obj = array();
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
    public function __construct(\Bolido\App\Adapters\BaseConfig $config,
                                \Bolido\App\Lang $lang,
                                \Bolido\App\Session $session,
                                \Bolido\App\Hooks $hooks)
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
     * @param array  $data Associative array with data to be passed to the template
     * @return void
     */
    public function load($template, $data = array())
    {
        $this->findTemplate($template, true);
        if (!empty($data))
        {
            foreach ($data as $name => $value)
                $this->set($name, $value);
        }
    }

    /**
     * Finds the full path to a template file, and stores them
     * in the templates property queue.
     *
     * @param string $template Name of the Template file
     * @param bool $appendToQueue Wether or not the template should be appended to the queue.
     * @return string The full path to the template file.
     */
    protected function findTemplate($template, $appendToQueue = false)
    {
        if (strpos($template, '/') === false)
            throw new \InvalidArgumentException('The template "' . $template . '" seems to be invalid.');

        $locations = array();
        $template  = str_replace('.tpl.php', '', $template);
        list($module, $file) = explode('/', $template, 2);

        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/' . $this->config->skin . '/' . $file . '.tpl.php';
        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/default/' . $file . '.tpl.php';
        $locations[] = $file . '.tpl.php';
        $locations[] = $file;

        foreach(array_unique($locations) as $l)
        {
            if (is_readable($l))
            {
                if ($appendToQueue)
                    $this->templates[$template] = $l;

                return $l;
            }
        }

        throw new \InvalidArgumentException('The template "' . $template . '" was not found');
    }

    /**
     * Alias for the findTemplate method
     */
    protected function f($template, $append = false) { return $this->findTemplate($template, $append); }

    /**
     * Actually processes the template queue and saves its output to $this->body
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
                        $headers['content-type'] = $this->contentType . '; charset=' . $this->config->charset;

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
     * Adds a new property to this object.
     *
     * @param string $property
     * @param object $object
     * @return void
     */
    public function addObjectProperty($property, $object)
    {
        if (!is_object($object))
            throw new InvalidArgumentException('Only objects are allowed!');
        else if (property_exists($this, $property) || isset($this->obj[$property]))
            throw new InvalidArgumentException('A property named ' . $property . ' is already defined');

        $this->obj[$property] = $object;
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
            throw new InvalidArgumentException('Template key ' . $key . ' was already defined');

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

        throw new \Exception('Unknown method ' . $method . ' in the Template Object');
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
