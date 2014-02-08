<?php
/**
 * Slug.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Utils;

use Bolido\Config;
use Bolido\Filesystem\Resource;

/**
 * Class responsable of generating links/slugs
 */
class Slug
{
    /** @var object Configuration directives */
    protected $config;

    /** @var array mapping of extensions */
    protected $extensions = array();

    /**
     * Construct
     *
     * @param object $config
     * @return void
     *
     * @throws RuntimeException when the iconv extension is not loaded
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        if (!extension_loaded('iconv')) {
            throw new \RuntimeException('The iconv module/extension must be loaded');
        }
    }

    /**
     * Registers extensions that should be changed on the url
     *
     * @param string $fromExt
     * @param string $toExt
     * @return void
     */
    public function addExtension($fromExt, $toExt)
    {
        $this->extensions[strtolower($fromExt)] = strtolower($toExt);
    }

    /**
     * Removes a registered extension
     *
     * @param string $ext
     * @return void
     */
    public function removeExtension($ext)
    {
        unset($this->extensions[$ext]);
    }

    /**
     * Creates a link from the given resource
     *
     * @param object $resource
     * @return string
     */
    public function fromResource(Resource $resource)
    {
        $file = $resource->getFilenameShort(false);
        $path = $resource->getRelativePath(true);
        return $this->urlizeRecursive($path . '/' . $file);
    }

    /**
     * Creates a slug from a string
     *
     * @param string $url
     * @return string
     */
    public function fromString($url)
    {
        return $this->urlizeRecursive($url);
    }

    /**
     * Creates a slug to be used for pretty URLs
     *
     * @param string $str
     * @param string $delimiter
     * @return string
     *
     * Based on
     * @link http://cubiq.org/the-perfect-php-clean-url-generator
     */
    public function urlize($str, $delimiter = '-')
    {
        // Save the old locale and set the new locale to UTF-8
        $oldLocale = setlocale(LC_ALL, 'en_US.UTF-8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);

        $clean = preg_replace("~[^a-zA-Z0-9\/_|+ -]~i", '', $clean);
        $clean = preg_replace("~[\/_|+ -]+~", $delimiter, $clean);
        $clean = trim($clean, $delimiter);
        $clean = strtolower($clean);

        // Revert back to the old locale
        setlocale(LC_ALL, $oldLocale);
        return trim($clean);
    }

    /**
     * Urlifys a string recursively, skipping directory separators
     *
     * @param string $url
     * @return string
     */
    public function urlizeRecursive($url)
    {
        // Add prefix and/or slash
        $url = rtrim($this->config['url_prefix'], '/ ') . '/' . $url;

        // if the last char is a slash, append index.html
        if (substr(trim($url), -1) == '/') {
            $url .= 'index.html';
        }

        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if ($ext) {
            $url = preg_replace('~\.' . $ext . '~', '', $url);
        }

        $result = array();
        foreach (explode('/', $url) as $p) {
            $result[] = $this->urlize($p);
        }

        $full = '/' . implode('/', $result) . $this->findExtension($ext);
        return preg_replace('~//+~', '/', $full); // Just remove consecutive /'s
    }

    /**
     * Gets the viable link extension from a file extension
     * when none is found, return ".html" as default
     *
     * @param string $ext
     * @return string
     */
    protected function findExtension($ext)
    {
        $ext = strtolower(trim($ext, ' .'));
        if (isset($this->extensions[$ext])) {
            return '.' . $this->extensions[$ext];
        }

        return '.html';
    }
}

?>
