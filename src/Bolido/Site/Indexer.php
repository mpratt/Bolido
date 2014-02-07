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

use Bolido\Config;
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
    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object instance of Collection */
    protected $collection;

    /** @var object instance of Collection */
    protected $analizer;

    /** @var array Categories/tags */
    protected $categories = array();

    /**
     * Construct
     *
     * @param object $outputter
     * @param object $collection
     * @return void
     */
    public function __construct(Collection $collection, FileAnalyzer $fileAnalizer, OutputterInterface $outputter)
    {
        $this->collection = $collection;
        $this->outputter = $outputter;
        $this->analizer = $fileAnalizer;
        $this->index();
    }

    /**
     * Indexes each parsable file
     *
     * @param object $collection
     * @return void
     */
    protected function index()
    {
        $this->outputter->write('<info>Analyzing file headers and roles</info>');
        foreach ($this->collection as $res) {

            $data = $this->analizer->getMetaData($res);
            if (empty($data)) {
                $this->outputter->write(sprintf('<comment>* No meta data for "%s"</comment>', $res->getBasename()));
                continue ;
            }

            $this->findCategories($data);
            if (!$data['is_indexable']) {
                $this->outputter->write(sprintf('<comment>* The file "%s" is not indexable</comment>', $res->getBasename()));
            } else {
                $this->outputter->write(
                    sprintf('<comment>Registering entry "%s" on namespace "%s"</comment>', $res->getBasename(), $data['namespace'])
                );

                $this->categories[$data['namespace']]['all_entries'][] = $data;

                // Sort entries by date
                uasort($this->categories[$data['namespace']]['all_entries'], function ($a, $b) {
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
     * @param array  $data
     * @return void
     */
    protected function findCategories(array $data)
    {
        foreach (array('tags', 'categories') as $key) {

            // ignore entries without tags/categories
            if (empty($data['matter'][$key])) {
                continue;
            }

            $this->outputter->write(
                sprintf(
                    '<comment>Storing "%s" from "%s" into namespace "%s"</comment>',
                    $key, basename($data['file']), $data['namespace']
                )
            );

            $ns = $data['namespace'];
            foreach ((array) $data['matter'][$key] as $d) {
                $d = array_merge($d, array(
                    'entries' => array($data),
                    'count' => 1,
                ));

                $name = basename($d['category_name']);
                if (isset($this->categories[$ns]['all_' . $key][$name]['entries'])) {
                    $this->categories[$ns]['all_' . $key][$name]['count']++;
                    $this->categories[$ns]['all_' . $key][$name]['entries'][] = $data;
                } else {
                    $this->categories[$ns]['all_' . $key][$name] = $d;
                }

                // Sort categories/tags by number of entries
                uasort($this->categories[$ns]['all_' . $key], function ($a, $b) {
                    return ($a['count'] < $b['count']);
                });
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
