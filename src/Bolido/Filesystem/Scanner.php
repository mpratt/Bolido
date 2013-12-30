<?php
/**
 * Scanner.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Filesystem;

use Bolido\Outputter\OutputterInterface;

/**
 * Scans a Directory recursively for files/folders
 */
class Scanner implements ScannerInterface
{
    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** inline {@inheritdoc} */
    public function __construct(OutputterInterface $outputter)
    {
        $this->outputter = $outputter;
    }

    /** inline {@inheritdoc} */
    public function scan($path, array $exclude = array())
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(
                sprintf('The given path "%s" is not a directory', $path)
            );
        }

        $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);

        $exclude = array_map(function ($pattern) {
            return str_replace('\$', '$', preg_quote($pattern, '~'));
        }, $exclude);

        $this->outputter->write('<info>Scanning</info>: ' . realpath($path));

        $found = array();
        foreach ($iterator as $resource) {
            if (!empty($exclude) && preg_match('~' . implode('|', $exclude) . '~', $resource)) {
                $this->outputter->write('<comment>* Excluding</comment>: ' . $resource);
                continue ;
            }

            // Calculate the relative path
            $relative = preg_replace('~^' . preg_quote(rtrim($path, '/')) . '~', '', $resource);
            $found[] = new Resource($resource, $relative);
        }

        $this->outputter->write('<comment>End of scan, ' . count($found) . ' elements found</comment>');
        return new Collection($found);
    }
}

?>
