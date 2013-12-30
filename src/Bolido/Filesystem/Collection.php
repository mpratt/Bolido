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
 * Implements the IteratorAggregate interface in order to give Iterator
 * capabilities to the object
 */
class Collection implements \IteratorAggregate
{
    /** @var Array Collection */
    public $collection = array();

    /**
     * Construct
     *
     * @param array $resources
     * @return void
     */
    public function __construct(array $resources = array())
    {
        $this->collection = array();
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
     * Returns an array with files that should be parsable
     *
     * @return array
     */
    public function getParsableFiles()
    {
        $files = array_filter($this->collection, function ($resource) {
            return ($resource->isParsable());
        });

        return new self($files);
    }

    /**
     * Returns an array of resources that are files
     *
     * @return array
     */
    public function getFiles()
    {
        $files = array_filter($this->collection, function ($resource) {
            return ($resource->isFile());
        });

        return new self($files);
    }

    /**
     * Returns an array of files that are located inside a
     * specified relative directory or namespace
     *
     * @param string $dir
     * @return array
     */
    public function getFilesOn($dir)
    {
        $files = array_filter($this->collection, function ($resource) use ($dir) {
            return ($resource->isFile() && trim($dir) == trim($resource->getNamespace()));
        });

        return new self($files);
    }

    /**
     * Returns an array of resources that are folders/directories
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
     * Runs a callable function/operation to all the contents of the collection
     *
     * @param callable $func
     * @return void
     */
    public function onEach($func)
    {
        if (!is_callable($func)) {
            throw new InvalidArgumentException('The given function is not callable');
        }

        array_walk($this->collection, $func);
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
}
?>
