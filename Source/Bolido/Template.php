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
    protected $config;
    protected $hooks;
    protected $Lang;

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
     * @param object $hooks
     * @return void
     */
    public function __construct(\Bolido\App\Adapters\BaseConfig $config, \Bolido\App\Lang $lang, \Bolido\App\Hooks $hooks)
    {
        $this->config  = $config;
        $this->lang    = $lang;
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
        $this->findTemplate($template);
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
     * @return void.
     */
    public function findTemplate($template)
    {
        if (strpos($template, '/') === false)
            throw new \InvalidArgumentException('The template "' . $template . '" seems to be invalid.');

        $locations = array();
        $template  = str_replace('.tpl.php', '', $template);
        list($module, $file) = explode('/', $template, 2);

        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/' $this->config->skin . '/' . $file . '.tpl.php';
        $locations[] = $this->config->moduleDir . '/' . $module . '/templates/default/' . $file . '.tpl.php';
        $locations[] = $file . '.tpl.php';
        $locations[] = $file;

        foreach(array_unique($locations) as $l)
        {
            if (is_readable($l))
            {
                $this->templates[$template] = $l;
                return ;
            }
        }

        throw new \InvalidArgumentException('The template "' . $template . '" was not found');
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
    public function clearTemplates() { $this->templates = array(); }

    /**
     * Removes a template from the templates queue
     *
     * @param string $template Name of the Template file
     * @return bool
     */
    public function removeTemplate($template)
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
