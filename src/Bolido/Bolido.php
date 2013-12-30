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

use Bolido\Site\Generator;
use Bolido\Filesystem\Scanner;
use Bolido\Filesystem\FileSystemInterface;
use Bolido\Filesystem\ScannerInterface;
use Bolido\Outputter\OutputterInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The Main class of the project
 */
class Bolido
{
    /** @var float Current project version */
    const VERSION = '0.1';

    /** @var array Configuration directives */
    protected $config = array();

    /** @var object Implementing ScannerInterface */
    protected $scanner;

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /**
     * Construct
     *
     * @param object $outputter Implementing OutputterInterface
     * @param array $config
     * @return void
     */
    public function __construct(OutputterInterface $outputter, array $config = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->config = $resolver->resolve($config);
        $this->outputter = $outputter;

        $this->outputter->write('<info>Initializing Bolido</info>');
        $this->outputter->write('<info>Source Dir</info>: ' . $this->config['source_dir']);
        $this->outputter->write('<info>Output Dir</info>: ' . $this->config['output_dir']);
    }

    /**
     * Sets the scanner object
     *
     * @param object $scanner Implementing ScannerInterface
     * @return object Instance of the current object
     */
    public function setScanner(ScannerInterface $scanner)
    {
        $this->scanner = $scanner;
        return $this;
    }

    /**
     * Sets the filesystem object
     *
     * @param object $filesystem Implementing FileSystemInterface
     * @return object Instance of the current object
     */
    public function setFileSystem(FileSystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * Sets default options
     *
     * @param object $resolver Object implementing the OptionsResolverInterface
     * @return void
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'source_dir' => '',
            'output_dir' => '',
            'layout_dir' => function (Options $options) {
                return $options['source_dir'] . '/layouts';
            },
            'plugin_dir' => function (Options $options) {
                return $options['source_dir'] . '/plugins';
            },
            'exclude' => function (Options $options, $value) {
                return array_unique(array_merge(array(
                    $options['layout_dir'],
                    $options['plugin_dir'],
                    'config.yml'
                ), (array) $value));
            },
            'tmp_dir' => sys_get_temp_dir(),
            'compile_less' => true,
        ));

        $resolver->setAllowedTypes(array(
            'source_dir' => 'dir',
            'layout_dir' => 'dir',
            'plugin_dir' => 'dir',
            'output_dir' => array('writable', 'dir'),
            'tmp_dir' => array('writable', 'dir'),
            'exclude' => 'array',
            'compile_less' => 'bool',
        ));

        $resolver->setNormalizers(array(
            'source_dir' => function (Options $options, $value) {
                return rtrim(realpath($value), '/');
            },
            'output_dir' => function (Options $options, $value) {
                if (strpos($value, '..') !== false) {
                    $value = realpath($options['source_dir'] . '/' . trim($value, '/'));
                }

                return rtrim(realpath($value), '/');
            },
        ));
    }

    /**
     * Runs the application
     *
     * @return void
     */
    public function create()
    {
        $collection = $this->scanner->scan($this->config['source_dir'], $this->config['exclude']);
        $generator = new Generator($this->config, $collection, $this->outputter, $this->filesystem);
        $generator->create();
    }
}

?>
