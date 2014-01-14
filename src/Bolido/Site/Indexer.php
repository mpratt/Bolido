<?php
/**
 * Indexer.php
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
use Bolido\Filesystem\Collection;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable for indexing all the parsable files
 * and fetching tags/categories/entries and grouping
 * them.
 */
class Indexer
{
    /** @var array Configuration directives */
    protected $config;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object instance of Collection */
    protected $collection;

    /** @var array Categories/tags */
    protected $categories = array();

    /**
     * Construct
     *
     * @param object $outputter
     * @param object $collection
     * @return void
     */
    public function __construct(array $config, OutputterInterface $outputter, Collection $collection)
    {
        $this->config = $config;
        $this->collection = $collection;
        $this->outputter = $outputter;
        $this->analize();
    }

    /**
     * Reads front-matter from available files
     *
     * @param object $collection
     * @return void
     */
    protected function analize()
    {
        $this->outputter->write('<info>Analyzing file headers and roles</info>');
        foreach ($this->collection as $res) {

            $matter = $res->getFrontMatter();
            $link = new Linker($this->config, $res);
            $entry = array(
                'title' => $res->getTitle(),
                'url' => $link->getLinkFilePath(true),
                'date' => $res->getDate(),
                'stamp' => $res->getDate('U'),
                'matter' => $matter,
            );

            if ($matter) {
                $this->outputter->write('<comment>Reading front-matter: </comment>' . $res->getBasename());
                $this->findCategories($res, $entry, $matter);
            } elseif (!$res->isLess()){
                $this->outputter->write('<comment>* No front-matter found on </comment>' . $res->getBasename());
            }

            if ($res->isIndexable()) {
                $this->outputter->write(
                    '<comment>Registering entry </comment>' . $res->getBasename() .
                    ' <comment>on namespace</comment> ' . $res->getNamespace()
                );

                $this->categories[$res->getNamespace()]['all_entries'][] = $entry;

                // Sort entries by date
                uasort($this->categories[$res->getNamespace()]['all_entries'], function ($a, $b) {
                    return ($a['stamp'] < $b['stamp']);
                });
            }
        }
    }

    /**
     * Finds and groups front matter tags/categories into the
     * categories property
     *
     * @param object $res Instance of Resource
     * @param array  $frontMatter
     * @return void
     */
    protected function findCategories(Resource $res, array $entry, array $frontMatter)
    {
        foreach (array_keys($frontMatter) as $key) {
            // Search for keys named tags/category/categories
            if (in_array(strtolower($key), array('tags', 'category', 'categories'))) {

                $this->outputter->write(
                    '<comment>Storing ' . $key . ' from </comment>' . $res->getBasename() .
                    ' <comment>into namespace</comment> ' . $res->getNamespace()
                );

                foreach ((array) $frontMatter[$key] as $rawValue) {
                    $key = str_replace('category', 'categories', strtolower($key));
                    $value = substr($key, 0, 3) . '-' . \URLify::filter($rawValue);
                    $data = array(
                        'category_name' => trim($rawValue, '/ '),
                        'category_url' => '/' . $res->getNamespace() . '/' . $value . '.html',
                        'entries' => array($entry),
                        'count' => 1,
                    );

                    $data['category_url'] = str_replace('//', '/', $data['category_url']);
                    if (isset($this->categories[$res->getNamespace()]['all_' . $key][$value]['entries'])) {
                        $this->categories[$res->getNamespace()]['all_' . $key][$value]['count']++;
                        $this->categories[$res->getNamespace()]['all_' . $key][$value]['entries'][] = $entry;
                    } else {
                        $this->categories[$res->getNamespace()]['all_' . $key][$value] = $data;
                    }

                    // Sort categories/tags by number of entries
                    uasort($this->categories[$res->getNamespace()]['all_' . $key], function ($a, $b) {
                        return (count($a['entries']) < count($b['entries']));
                    });
                }
            }
        }
    }

    /**
     * Returns an array with known tags/categories based
     * on a specific namespace
     *
     * @param string $ns
     * @return array
     */
    public function getByNamespace($ns)
    {
        if (!empty($this->categories[$ns])) {
            return $this->categories[$ns];
        }

        return array();
    }

    /**
     * Returns an array with known tags/categories for the whole
     * project
     *
     * @return array
     */
    public function getAll()
    {
        return $this->categories;
    }
}

?>
