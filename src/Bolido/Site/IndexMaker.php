<?php
/**
 * IndexMaker.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use Bolido\Filesystem\FilesystemInterface;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable of generating the folder structure
 * of the site
 */
class IndexMaker
{
    /** @var Array with configuration directives */
    protected $config;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object instance of Parser */
    protected $parser;

    /** @var object instance of Indexer */
    protected $indexer;

    /** @var object Implementing FilesystemInterface */
    protected $filesystem;

    /**
     * Construct
     *
     * @param array $config
     * @param object $indexer
     * @param object $outputter
     * @param object $filesystem
     * @return void
     */
    public function __construct(
        array $config,
        Indexer $indexer,
        Parser $parser,
        OutputterInterface $outputter,
        FilesystemInterface $filesystem
    ) {
        $this->config = $config;
        $this->parser = $parser;
        $this->indexer = $indexer;
        $this->outputter = $outputter;
        $this->filesystem = $filesystem;
    }

    /**
     * Creates/mirrors the folder structure into the $destiny
     * folder.
     *
     * @param string $destiny
     * @return void
     */
    public function makeAll()
    {
        $this->outputter->write('<info>Creating Indexes/Categories/Tags</info>');
        foreach ($this->indexer->getAll() as $ns => $v) {
            if (!empty($v['all_categories'])) {
                $this->makeCategories($ns, $v['all_categories']);
            }

            if (!empty($v['all_tags'])) {
                $this->makeCategories($ns, $v['all_tags'], 'tag');
            }

            if (!empty($v['all_entries'])) {
                $this->makeIndex($ns, $v['all_entries']);
            }
        }
    }

    /**
     * Creates the categories html for a specific namespace
     *
     * @param string $ns
     * @param array $categories
     * @return void
     */
    public function makeCategories($ns, array $categories, $name = 'category')
    {
        $allCats = array();
        foreach ($categories as $type => $cat) {
            $this->outputter->write(
                '<comment>Creating ' . $name . '</comment>: ' . $type .
                '.html <comment>on</comment> ' . $ns
            );

            $dst = rtrim($this->config['output_dir'], '/') . '/' . ltrim($cat['category_url'], '/');
            $parsed = $this->parser->parseTwigFromFile($name . '-single.twig', $cat);
            $this->filesystem->copyFromString($parsed, $dst, null, true);

            $allCats[] = array(
                'category_name' => $cat['category_name'],
                'category_url' => '/' . ltrim($cat['category_url'], '/ '),
                'count' => count($cat['entries']),
            );
        }

        $html = 'tags';
        if ($name == 'category') {
            $html = 'categories';
        }

        $dst = rtrim($this->config['output_dir'], '/') . '/' . trim($ns, '/') . '/' . $html . '.html';
        $this->outputter->write('<comment>Building Index of ' . $name . ' on</comment> ' . $dst);
        if ($this->filesystem->exists($dst)) {
            $this->outputter->write($dst . ' <comment>was already created</comment>');
        } else {
            $parsed = $this->parser->parseTwigFromFile($name . '-list.twig', array($html => $allCats));
            $this->filesystem->copyFromString($parsed, $dst, null, true);
        }
    }

    public function makeIndex($ns, array $entries)
    {
        $dst = rtrim($this->config['output_dir'], '/') . '/' . trim($ns, '/') . '/index.html';
        $this->outputter->write('<comment>Building Index of ' . $ns . ' on</comment> ' . $dst);
        if ($this->filesystem->exists($dst)) {
            $this->outputter->write($dst . ' <comment>was already created</comment>');
        } else {
            $parsed = $this->parser->parseTwigFromFile('index.twig', array('entries' => $entries));
            $this->filesystem->copyFromString($parsed, $dst, null, true);
        }
    }
}

?>
