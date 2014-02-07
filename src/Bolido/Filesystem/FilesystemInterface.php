<?php
/**
 * FileSystemInterface.php
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
 * Class responsable of generating a site
 */
interface FilesystemInterface
{
    /**
     * Sets the configuration options
     *
     * @param object $outputter Implementing OutputterInterface
     * @return void
     */
    public function __construct(OutputterInterface $outputter);

    /**
     * Creates a new directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $recursive = true);

    /**
     * Creates/Copies a file based on a string
     *
     * @param string $str
     * @param string $dst
     * @return bool
     */
    public function copyFromString($str, $dst);

    /**
     * Copies a file
     *
     * @param object $str
     * @param string $dst
     * @return bool
     */
    public function copy(Resource $src, $dst);

    /**
     * Checks if a file/dir exists
     *
     * @param string $file
     * @return bool
     */
    public function exists($file);

    /**
     * Removes a file or directory
     *
     * @param string $file
     * @return bool
     */
    public function unlink($file);
}

?>
