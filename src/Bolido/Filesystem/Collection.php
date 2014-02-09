<?php
/**
 * Collection.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Filesystem;

/**
 * Class that handles a collection of Resources (files/folders)
 */
class Collection implements \IteratorAggregate, \Countable
{
    /** @var array with all the files/folders */
    public $collection = array();

    /**
     * Construct
     *
     * @param array $resources
     * @return void
     */
    public function __construct(array $resources = array())
    {
        foreach ($resources as $res) {
            $this->add($res);
        }
    }

    /**
     * Adds a new resource into the collection
     *
     * @param object $resource
     * @return void
     */
    public function add(Resource $resource)
    {
        $this->collection[] = $resource;
    }

    /**
     * Returns a collection of files based on the callback function
     *
     * @param callable $fn
     * @return object
     */
    public function getByCallback(Callable $fn)
    {
        return new self(array_filter($this->collection, $fn));
    }

    /**
     * Returns a collection of resources that are files
     *
     * @return object
     */
    public function getFiles()
    {
        $files = array_filter($this->collection, function ($resource) {
            return ($resource->isFile());
        });

        return new self($files);
    }

    /**
     * Returns a collection of resources that are folders/directories
     *
     * @return array
     */
    public function getDirectories()
    {
        $dirs = array_filter($this->collection, function ($resource) {
            return ($resource->isDir());
        });

        // Longer directories are at the end
        usort($dirs, function ($a, $b) {
            return (strlen($a) > strlen($b));
        });

        return new self($dirs);
    }

    /**
     * Method required by the IteratorAgreggate Interface
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * Method required by the Countable Interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }
}
?>
