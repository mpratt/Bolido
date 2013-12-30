<?php
/**
 * BolidoCommand.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Console;

use Bolido\Bolido;
use Bolido\Outputter\Console;
use Bolido\Filesystem\Scanner;
use Bolido\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command-line interface
 */
class BolidoCommand extends Command
{
    /** inline {@inheritdoc} */
    protected function configure()
    {
        $this->setName('bolido')
            ->setDescription('A static website generator')
            ->addArgument(
                'source|config',
                InputArgument::REQUIRED,
                'Directory where the source and config files are located'
            );
    }

    /** inline {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $config = $this->parseSourceConfig($input);
            $bolido = $this->createInstance($output, $config);
            $bolido->create();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        return 0;
    }

    /**
     * Creates a new Bolido instance
     *
     * @param object $output OutputInterface object
     * @param array  $config Configuration Directives
     * @return object Instance of Bolido
     */
    protected function createInstance(OutputInterface $output, array $config)
    {
        $outputter = new Console($output);

        $bolido = new Bolido($outputter, $config);
        $bolido->setScanner(new Scanner($outputter))
               ->setFileSystem(new Filesystem($outputter));

        return $bolido;
    }

    /**
     * Parses a configuration file, based on the given source directory
     * or config.yml inside a source file
     *
     * @param object $input
     * @return array
     *
     * @throws InvalidArgumentException when no configuration file was found
     */
    protected function parseSourceConfig(InputInterface $input)
    {
        $sourceDir = rtrim($input->getArgument('source|config'), '/');
        if (is_file($sourceDir)) {
            return $this->parseConfigFile($sourceDir);
        } else if (is_dir($sourceDir)) {
            return $this->parseConfigFile($sourceDir . '/config.yml');
        }

        throw new InvalidArgumentException(
            sprintf('Could not find configuration file config.yml in "%s"', $sourceDir)
        );
    }

    /**
     * Parses a YAML configuration file into an array
     *
     * @param string $file
     * @return array
     */
    protected function parseConfigFile($file)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a file', $file)
            );
        }

        $yaml = new Parser();
        $options = $yaml->parse(file_get_contents($file));
        if (empty($options['source_dir']) || in_array($options['source_dir'], array('.'))) {
            $options['source_dir'] = rtrim(dirname($file), '/');
        }

        return $options;
    }
}
