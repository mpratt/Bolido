<?php
/**
 * IndexBuilder.php
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
use Bolido\Filesystem\FilesystemInterface;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable of generating the folder structure
 * of the site
 */
class IndexBuilder
{
    /** @var array Parser list */
    protected $parsers = array();

    /** @var object Implementing OutputterInterface */
    protected $outputter;

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
    public function __construct(Config $config, FilesystemInterface $filesystem, array $parsers = array(), OutputterInterface $outputter)
    {
        $this->config = $config;
        $this->parsers = $parsers;
        $this->outputter = $outputter;
        $this->filesystem = $filesystem;
    }

    /**
     * Creates index and category files
     *
     * @param array $data
     * @return void
     */
    public function makeAll(array $data = array())
    {
        $this->outputter->write('<info>Creating Indexes/Categories/Tags</info>');
        foreach ($data as $ns => $v) {
            if (!empty($v['all_categories'])) {
                $this->makeCategories($v['all_categories']);
            }

            if (!empty($v['all_tags'])) {
                $this->makeCategories($v['all_tags'], 'tag');
            }

            if (!empty($v['all_entries'])) {
                $this->makeIndex($ns, $v['all_entries']);
            }
        }
    }

    /**
     * Creates the categories html for a specific namespace
     *
     * @param array $categories
     * @param string $name
     * @return void
     */
    public function makeCategories(array $categories, $name = 'category')
    {
        $allCats = array();
        foreach ($categories as $cat) {

            $namespace = dirname($cat['category_url']);
            $this->outputter->write(
                '<comment>Creating ' . $name . ' index</comment>: ' . basename($cat['category_url']) .
                ' <comment>on</comment> ' . $namespace
            );

            $template = $this->config['layout_dir'] . '/' . $name . '-single.twig';

            if (!$this->filesystem->exists($template)) {
                $this->outputter->write('<error>Could not find template</error>: ' . $template);
                continue;
            }

            $parsed = $this->parsers['twig']->parseResource(new Resource($template), $cat);

            $dst = $this->config['output_dir'] . $cat['category_url'];
            $this->filesystem->copyFromString($parsed, $dst, null);

            $allCats[] = array(
                'category_name' => $cat['category_name'],
                'category_url' => $cat['category_url'],
                'count' => count($cat['entries']),
            );
        }

        $this->outputter->write('<comment>Building Index of ' . $name . ' on</comment> ' . $dst);

        if ($name == 'category') {
            $html = 'categories';
        } else {
            $html = 'tags';
        }

        $dst = preg_replace('~//+~', '/', $this->config['output_dir'] . $namespace . '/' . $html . '.html');
        $template = $this->config['layout_dir'] . '/' . $name . '-list.twig';
        $this->buildTemplate($template, $dst, array($html => $allCats));
    }

    /**
     * Builds index files
     *
     * @param string $ns
     * @param array $entries
     * @return void
     */
    public function makeIndex($ns, array $entries)
    {
        $template = $this->config['layout_dir'] . '/index.twig';
        $dst = preg_replace('~//+~', '/', $this->config['output_dir'] . $ns . '/index.html');

        $this->outputter->write('<comment>Building Index of ' . $ns . ' on</comment> ' . $dst);
        $this->buildTemplate($template, $dst, array('entries' => $entries));
    }

    /**
     * Builds/compiles the template and stores them
     *
     * @param string $template
     * @param string $dst
     * @param array $values
     * @return void
     */
    protected function buildTemplate($template, $dst, array $values = array())
    {
        if ($this->filesystem->exists($dst)) {
            $this->outputter->write($dst . ' <comment>was already created</comment>');
        } else if ($this->filesystem->exists($template)) {
            $parsed = $this->parsers['twig']->parseResource(new Resource($template), $values);
            $this->filesystem->copyFromString($parsed, $dst, null);
        } else {
            $this->outputter->write('<error>Could not find template</error>: ' . $template);
        }
    }
}

?>
