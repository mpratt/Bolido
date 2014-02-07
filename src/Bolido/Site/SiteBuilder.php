<?php
/**
 * SiteBuilder.php
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
use Bolido\Filesystem\Collection;
use Bolido\Filesystem\Resource;
use Bolido\Filesystem\FilesystemInterface;
use Bolido\Outputter\OutputterInterface;
use Bolido\Parser\ParserInterface;

/**
 * Class responsable of generating a site
 */
class SiteBuilder
{
    /** @var array Total Categories or Tags fetched from font matter */
    protected $categories = array();

    /** @var array Parsers */
    protected $parsers = array();

    /** @var object Configuration directives */
    protected $config;

    /** @var object Instance of FileAnalyzer */
    protected $analyzer;

    /** @var object Implementing FilesystemInterface */
    protected $filesystem;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /**
     * Construct
     *
     * @param object $config
     * @param object $analyzer
     * @param object $filesystem
     * @param object $outputter
     * @return void
     */
    public function __construct(Config $config, FileAnalyzer $analyzer, FilesystemInterface $filesystem, OutputterInterface $outputter)
    {
        $this->config = $config;
        $this->analyzer = $analyzer;
        $this->filesystem = $filesystem;
        $this->outputter = $outputter;
    }

    /**
     * Sets the parsers array
     *
     * @param array $parsers
     * @return void
     */
    public function setParsers(array $parsers)
    {
        $this->parsers = array_filter($parsers, function ($parser) {
            return ($parser instanceof ParserInterface);
        });
    }

    /**
     * Checks wether a resource is parsable or not
     *
     * @param object $resource
     * @return bool
     */
    protected function isParsable(Resource $resource)
    {
        return (in_array($resource->getExtension(), array_keys($this->parsers)));
    }

    /**
     * Gets the relevant parser for a resource
     *
     * @param object $resource
     * @return object
     */
    protected function getParser(Resource $resource)
    {
        return $this->parsers[$resource->getExtension()];
    }

    /**
     * Creates the site
     *
     * @param object $collection
     * @return void
     */
    public function create(Collection $collection)
    {
        $this->outputter->write('<info>Building site</info>');

        // Create the folder structure
        $this->createStructure($collection);

        // Find all entries/categories/tags
        $indexer = $this->getIndexer($collection);

        /**
         * Loop across the files and:
         * - Copy the files that are not parsable
         * - If the file is parsable, parse it, determine output file and copy it over
         * - Otherwise just copy the files over to the output dir, disregarding the extension.
         */
        $this->outputter->write('<info>Parsing and creating output files</info>');
        foreach ($collection->getFiles() as $resource) {

            // Just copy the file
            if (!$this->isParsable($resource)) {
                $dst = rtrim($this->config['output_dir'], '/') . '/' . ltrim($resource->getRelativePath(), '/');
                $this->filesystem->copy($resource, $dst);
                continue ;
            }

            // Now we get into parsing
            $this->outputter->write('<comment>Parsing </comment>: ' . $resource->getBasename());

            $metaData = $this->analyzer->getMetadata($resource);
            $variables = $this->getFileVariables($metaData, $indexer->getByNamespace($metaData['namespace']));
            $parsed = $this->getParser($resource)->parseString($this->analyzer->getContents($resource), $variables);
            $dst = $this->config['output_dir'] . $metaData['url'];
            $this->filesystem->copyFromString($parsed, $dst, $resource);
        }

        // Create index/categories/tags indexes
        $builder = new IndexBuilder($this->config, $this->filesystem, $this->parsers, $this->outputter);
        $builder->makeAll($indexer->getAll());
        $this->outputter->write('<info>Site creation complete</info>');
    }

    /**
     * Creates/mirrors the folder structure into the output_dir folder.
     *
     * @param object $collection
     * @return void
     */
    public function createStructure(Collection $collection)
    {
        $this->outputter->write('<info>Creating folder structure</info>');
        $directories = $collection->getDirectories();

        foreach ($directories as $dir) {
            $full = $this->config['output_dir'] . '/' . ltrim($dir->getRelativePath(), '/');
            $this->filesystem->mkdir($full);

            // Remove index.html/categories.html/tags.html from the directory, generate them later
            foreach (array('index.html', 'categories.html', 'tags.html') as $file) {
                $file = $full . '/' . $file;
                if ($this->filesystem->exists($file)) {
                    $this->outputter->write('<comment>Cleaning </comment> ' . $file);
                    $this->filesystem->unlink($file);
                }
            }
        }

        $this->outputter->write('<info>Finished folder structure</info>');
    }

    /**
     * Returns an Indexer object
     *
     * @param object $collection
     * @return object Indexer
     */
    protected function getIndexer(Collection $collection)
    {
        $parsable = $collection->getByCallback(function ($resource) {
            return $this->isParsable($resource);
        });

        return new Indexer($parsable, $this->analyzer, $this->outputter);
    }

    /**
     * Gets all the available variables for the twig engine to process.
     *
     * @param array $metaData
     * @param array $categories
     * @return array
     */
    protected function getFileVariables(array $metaData = array(), array $categories = array())
    {
        $localVars = array_merge(array(
            'layout' => 'default.twig',
            'block' => 'content',
        ), $metaData['matter'], $metaData);
        unset($localVars['matter']);

        $globalVars = array();
        $namespace = str_replace('/', '_', trim($localVars['namespace'], '/'));
        if (!empty($this->config[$namespace])) {
            $globalVars = (array) $this->config[$namespace];
        }

        return array_merge($globalVars, $localVars, $categories);
    }

}

?>
