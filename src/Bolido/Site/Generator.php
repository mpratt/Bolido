<?php
/**
 * Generator.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use Bolido\Filesystem\Collection;
use Bolido\Filesystem\FilesystemInterface;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable of generating a site
 */
class Generator
{
    /** @var array Configuration directives */
    protected $config = array();

    /** @var array Properties fetched for each individual file */
    protected $properties = array();

    /** @var array Total Categories or Tags fetched from font matter*/
    protected $categories = array();

    /** @var object Instance of Collection */
    protected $collection;

    /** @var object Implementing FilesystemInterface */
    protected $filesystem;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /**
     * Construct
     *
     * @param array $options
     * @param object $collection
     * @param object $outputter
     * @param object $filesystem
     * @return void
     */
    public function __construct(
        array $config,
        Collection $collection,
        OutputterInterface $outputter,
        FilesystemInterface $filesystem
    ) {
        $this->config = $config;
        $this->collection = $collection;
        $this->outputter = $outputter;
        $this->filesystem = $filesystem;
    }

    /**
     * Creates the site
     *
     * @return void
     */
    public function create($overwrite = true)
    {
        $this->outputter->write('<info>Building site</info>');

        // Create folder structure
        $dirs = $this->collection->getDirectories();
        $structure = new Structure($this->outputter, $dirs, $this->filesystem);
        $structure->mirror($this->config['output_dir']);

        // Find all entries/categories/tags
        $parsable = $this->collection->getParsableFiles();
        $indexer = new Indexer($this->config, $this->outputter, $parsable);

        /**
         * Loop across the files/folders and:
         * - If the file is parsable, parse it, determine output file and copy it over
         * - Otherwise just copy the files over to the output dir, disregarding the extension.
         */
        $this->outputter->write('<info>Parsing and creating output files</info>');
        $parser = new Parser($this->config, $this->outputter);
        foreach ($this->collection->getFiles() as $file) {
            if ($file->isParsable()) {
                $link = new Linker($this->config, $file);
                $categories = $indexer->getByNamespace($file->getNamespace());
                $parsed = $parser->parse($file, $categories);
                $this->filesystem->copyFromString($parsed, $link->getLinkFilePath(), $file, $overwrite);
            } else {
                $dst = rtrim($this->config['output_dir'], '/') . '/' . ltrim($file->getRelativePath(), '/');
                $this->filesystem->copy($file, $dst);
            }
        }

        // Create index/categories/tags indexes
        $indexMaker = new IndexMaker($this->config, $indexer, $parser, $this->outputter, $this->filesystem);
        $indexMaker->makeAll();

        $this->outputter->write('<info>Site creation complete</info>');
    }
}

?>
