<?php
/**
 * Bolido.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido;

use Bolido\Outputter\OutputterInterface;
use Bolido\Filesystem\Scanner;
use Bolido\Filesystem\Collection;
use Bolido\Filesystem\FileSystemInterface;
use Bolido\Filesystem\ScannerInterface;
use Bolido\Parser\ParserInterface;
use Bolido\Parser\MarkdownParser;
use Bolido\Parser\TwigParser;
use Bolido\Parser\LessParser;
use Bolido\Site\FileAnalyzer;
use Bolido\Site\SiteBuilder;
use Bolido\Site\Indexer;
use Bolido\Utils\Slug;

/**
 * The Main class of the project
 */
class Bolido
{
    /** @var float Current project version */
    const VERSION = '0.1';

    /** @var object Instance of Bolido\Config */
    protected $config;

    /** @var object Implementing ScannerInterface */
    protected $scanner;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object Slug */
    protected $slug;

    /** @var array Parsers */
    protected $parsers = array();

    /**
     * Construct
     *
     * @param object $config Instance of Bolido\Config
     * @param object $scanner Implementing ScannerInterface
     * @param object $filesystem Implementing FileSystemInterface
     * @param object $outputter Implementing OutputterInterface
     * @return void
     */
    public function __construct(Config $config, ScannerInterface $scanner, FileSystemInterface $filesystem, OutputterInterface $outputter)
    {
        $this->config = $config;
        $this->scanner = $scanner;
        $this->filesystem = $filesystem;
        $this->outputter = $outputter;
        $this->slug = new Slug($config);

        $this->outputter->write('<info>Initializing Bolido</info>');
        $this->outputter->write('<info>Source Dir</info>: ' . $this->config['source_dir']);
        $this->outputter->write('<info>Output Dir</info>: ' . $this->config['output_dir']);
        $this->setDefaultParsers();
    }

    /**
     * Adds a new parser
     *
     * @param string|array $extension
     * @param object $parser
     * @param string $toExt
     * @return void
     */
    public function addParser($extension, ParserInterface $parser, $toExt = 'html')
    {
        foreach ((array) $extension as $ext) {
            $ext = trim(strtolower($ext), '. ');
            $toExt = trim(strtolower($toExt), '. ');

            $this->parsers[$ext] = $parser;
            $this->slug->addExtension($ext, $toExt);

            $this->outputter->write(
                sprintf('<comment>Adding parser for "%s" extensions</comment>', $ext)
            );
        }
    }

    /**
     * Removes a parser
     *
     * @param string $extension
     * @return void
     */
    public function removeParser($extension)
    {
        $extension = trim(strtolower($extension), '. ');
        if (isset($this->parsers[$extension])) {
            unset($this->parsers[$extension]);
            $this->slug->removeExtension($extension);
        }
    }

    /**
     * Sets default options
     *
     * @param object $resolver Object implementing the OptionsResolverInterface
     * @return void
     */
    protected function setDefaultParsers()
    {
        $twig = new TwigParser($this->config);
        $this->addParser('twig', $twig);
        $this->addParser(array('md', 'markdown'), new MarkdownParser($this->config, $twig));

        if ($this->config['compile_less']) {
            $this->addParser('less', new LessParser(), 'css');
        }
    }

    /**
     * Runs the application
     *
     * @return void
     */
    public function create()
    {
        $exclude = array_unique(array_merge($this->config['exclude'], array(
            $this->config['layout_dir'], $this->config['plugin_dir'],
        )));

        $collection = $this->scanner->scan($this->config['source_dir'], $exclude);
        $fileAnalyzer = new FileAnalyzer($this->config, $this->slug, array_keys($this->parsers));

        $generator = new SiteBuilder($this->config, $fileAnalyzer, $this->filesystem, $this->outputter);
        $generator->setParsers($this->parsers);
        $generator->create($collection);
    }

}
?>
