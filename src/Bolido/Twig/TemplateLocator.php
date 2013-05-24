<?php
/**
 * TemplateLocator.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Twig;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class TemplateLocator implements \Twig_LoaderInterface
{
    protected $config;

    /**
     * Construct
     *
     * @param object $config
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config) { $this->config = $config; }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param  string $name string The name of the template to load
     * @return string The template source code
     */
    public function getSource($name)
    {
        $file = $this->findByName($name);
        return file_get_contents($file);
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name string The name of the template to load
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
      return md5($this->findByName($name));
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     */
    public function isFresh($name, $time)
    {
        return (filemtime($this->findByName($name)) <= $time);
    }

    /**
     * Finds the full path to a template file.
     *
     * @param string $template Name of the Template file.
     * @return string The full path to the template file.
     *
     * @throws Exception when the file was not found
     */
    protected function findByName($template)
    {
        list($module, $file) = explode('/', $template, 2);
        $file = preg_replace('~\.twig$~i', '', $file);
        $files = array($this->config->moduleDir . '/' . $module . '/templates/' . $this->config->skin . '/' . $file . '.twig',
                       $this->config->moduleDir . '/' . $module . '/templates/default/' . $file . '.twig');

        foreach(array_unique($files) as $f)
        {
            if (is_readable($f))
                return $f;
        }

        throw new \Exception('The Template ' . $template . ' could not be found');
    }
}

?>
