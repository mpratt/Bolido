<?php
/**
 * FileAnalyzer.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use Bolido\Config;
use Bolido\Utils\Slug;
use Bolido\Utils\FrontMatter;
use Bolido\Filesystem\Resource;

/**
 * This class analizes parsable files
 */
class FileAnalyzer
{
    /** @var array Registry where entries are cached */
    protected $registry = array();

    /** @var object Slug */
    protected $slug;

    /** @var object instance of Config */
    protected $config;

    /** @var object Implementing FilesystemInterface */
    protected $parsableExtensions;

    /**
     * Construct
     *
     * @param object $config
     * @param object $slug
     * @param array $parsableExtensions
     * @return void
     */
    public function __construct(Config $config, Slug $slug, array $parsableExtensions)
    {
        $this->slug = $slug;
        $this->config = $config;
        $this->parsableExtensions = $parsableExtensions;
    }

    /**
     * Calculates the metadata for a resource
     *
     * @param object $resource
     * @return array
     */
    public function getMetaData(Resource $resource)
    {
        if (!in_array($resource->getExtension(), $this->parsableExtensions)) {
            return array();
        }

        if (isset($this->registry[md5((string) $resource)])) {
            return $this->registry[md5((string) $resource)];
        }

        $frontMatter = new FrontMatter($resource);
        $matter = $this->normalizeCategories($resource, $frontMatter->getMatter());
        return $this->registry[md5((string) $resource)] = array(
            'file' => (string) $resource,
            'title' => $this->getTitle($resource, $matter),
            'url' => $this->getUrl($resource, $matter),
            'date' => $this->getDate($resource, $matter),
            'stamp' => $this->getDate($resource, $matter, 'U'),
            'namespace' => $this->getNamespace($resource, $matter),
            'is_indexable' => ((isset($matter['indexable']) && !$matter['indexable']) || $resource->isIndexable()),
            'matter' => $matter,
        );
    }

    /**
     * Returns the contents of a file, strips front-matter when needed
     *
     * @param object $resource
     * @return string
     */
    public function getContents(Resource $resource)
    {
        $frontMatter = new FrontMatter($resource);
        return $frontMatter->getContents();
    }

    /**
     * Returns the url to the file
     *
     * @param object $resource
     * @param array $matter
     * @return string
     */
    public function getUrl(Resource $resource, array $matter = array())
    {
        if (!empty($matter['permalink'])) {
            return $this->slug->fromString($matter['permalink']);
        } else if (!empty($matter['title'])) {
            $fullPath = $this->getNamespace($resource, $matter) . '/' . $matter['title'];
            return $this->slug->fromString($fullPath);
        } else {
            return $this->slug->fromResource($resource);
        }
    }

    /**
     * Returns the file namespace based their location or
     * front-matter
     *
     * @param object $resource
     * @param array $matter
     * @return string
     */
    public function getNamespace(Resource $resource, array $matter = array())
    {
        if (!empty($matter['namespace'])) {
            $ns = trim($matter['namespace']);
        } else {
            $ns = $resource->getRelativePath(true);
        }

        return '/' . str_replace(array(' ', '_'), '-', trim($ns, '/ '));
    }

    /**
     * Returns the valid title of a resource
     *
     * @param object $resource
     * @param array $matter
     * @param array $chars Chars that should be converted to spaces
     * @return string
     */
    public function getTitle(Resource $resource, array $matter = array(), array $chars = array('_', '-', '.'))
    {
        if (!empty($matter['title'])) {
            return $matter['title'];
        }

        // Get the File name and convert
        return trim(str_replace($chars, ' ', trim($resource->getFilenameShort(), implode('', $chars))));
    }

    /**
     * Returns the valid creation date of a resource
     *
     * @param object $resource
     * @param array $matter
     * @param string $format
     * @return string
     */
    public function getDate(Resource $resource, array $matter = array(), $format = 'Y-m-d')
    {
        $date = $resource->getCTime();
        if (!empty($matter['date'])) {
            $date = $matter['date'];
        } elseif (preg_match('~^((\d{4})-(\d{1,2})-(\d{1,2})(?:[-_]*))~', $resource->getFilename(), $m)) {
            $date = trim($m['0'], '-_ ');
        }

        if (ctype_digit($date)) {
            $date = date('Y-m-d H:i:s', $date);
        }

        $dateTime = new \DateTime($date);
        return $dateTime->format($format);
    }

    /**
     * Normalizes Tags/Categories in the front-matter and normalizes their content/keys
     *
     * @param object $resource
     * @param array $matter
     * @return string
     */
    protected function normalizeCategories(Resource $resource, array $matter = array())
    {
        $registry = array();
        foreach (array_keys($matter) as $key) {

            // Search for keys named tags/tag/category/categories
            if (in_array(strtolower($key), array('tags', 'tag',  'category', 'categories'))) {
                foreach ((array) $matter[$key] as $raw) {

                    $translation = array('category' => 'categories');
                    $normalized = str_replace(array_keys($translation), array_values($translation), strtolower($key));
                    $name = trim(strtolower($raw), '/ ');
                    $path = $this->getNamespace($resource, $matter) . '/' . substr($normalized, 0, 3) . '-' . $name . '.html';
                    $registry[$normalized][] = array(
                        'category_name' => $name,
                        'category_url' => $this->slug->fromString($path)
                    );
                }

                // unset the key and use the normalized one
                unset($matter[$key]);
            }
        }

        return array_merge($matter, $registry);
    }

}

?>
