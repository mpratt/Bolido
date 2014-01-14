<?php
/**
 * Linker.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use Bolido\Filesystem\Resource;

/**
 * Class responsable of generating the folder structure
 * of the site
 */
class Linker
{
    /** @var array Configuration directives */
    protected $config;

    /** @var object instance of Resource */
    protected $resource;

    /**
     * Construct
     *
     * @param array $config
     * @param object $resource
     * @return void
     */
    public function __construct(array $config, Resource $resource)
    {
        $this->config = $config;
        $this->resource = $resource;
    }

    /**
     * Creates a link from the given resource
     *
     * @return string
     */
    public function getLinkFilePath($relative = false)
    {
        $matter = $this->resource->getFrontMatter();
        if (!empty($matter['permalink'])) {
            $suffix = $this->urlifyRecursive($matter['permalink']);
        } else if (!empty($matter['title'])) {
            $suffix = $this->urlifyRecursive($this->resource->getNamespace() . '/' . $matter['title']);
        } else {
            $file = preg_replace('~/((\d{4})-(\d{1,2})-(\d{1,2})(?:[-_]*))~', '/', $this->resource->getRelativePath());
            $suffix = $this->urlifyRecursive($file);
        }

        if ($relative) {
            return '/' . ltrim($suffix, '/');
        }

        return rtrim($this->config['output_dir'], '/') . '/' . ltrim($suffix, '/');
    }

    /**
     * Urlifys a string recursively, skipping directory separators
     *
     * @param string $str
     * @return string
     */
    protected function urlifyRecursive($str)
    {
        if (substr($str, -1) == '/') {
            $ext = 'index.html';
        } else if ($ext = pathinfo($str, PATHINFO_EXTENSION)) {
            $str = preg_replace('~\.' . $ext . '~', '', $str);
            $ext = $this->getLinkExtension($ext);
        } else {
            $ext = '.html';
        }

        $result = array();
        $parts = explode('/', $str);
        foreach ($parts as $p) {
            $result[] = $this->urlify($p);
        }

        return implode('/', $result) . $ext;
    }

    /**
     * Converts a string into a viable url file
     *
     * @param string $str
     * @return string
     */
    protected function urlify($str)
    {
        $str = \URLify::downcode($str);
        $str = preg_replace('~[^-\.\w\s]~', '', $str); // remove unneeded chars
        $str = str_replace('_', ' ', $str); // treat underscores as spaces
        $str = preg_replace('~^\s+|\s+$~', '', $str); // trim leading~trailing spaces
        $str = preg_replace('~[-\s]+~', '-', $str); //convert spaces to hyphens

        return strtolower($str);
    }

    /**
     * Gets the viable link extension from a file extension
     *
     * @param string $ext
     * @return string
     */
    protected function getLinkExtension($ext)
    {
        $ext = strtolower($ext);
        if (in_array($ext, array('twig', 'md', 'markdown'))) {
            return '.html';
        }

        if ($ext == 'less' && $this->config['compile_less']) {
            return '.css';
        }

        return '.' . $ext;
    }
}

?>
