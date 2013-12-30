<?php
/**
 * ScannerInterface.php
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
interface ScannerInterface
{
    /**
     * Construct
     *
     * @param object Instance of OutputterInterface
     * @return void
     */
    public function __construct(OutputterInterface $outputter);

    /**
     * Finds files/folders recursively inside a directory and
     * returns a collection object with the found data
     *
     * @param string $path The directory to be scan
     * @param array  $exclude Patterns/strings that should be excluded
     * @return object Instance of Collection
     */
    public function scan($path, array $exclude = array());
}

?>
