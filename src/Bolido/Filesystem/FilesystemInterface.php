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
     * @return bool
     */
    public function mkdir($dir);

    /**
     * Creates/Copies a file based on a string
     *
     * @param string $str
     * @param string $dst
     * @param object $res
     * @param bool $overwrite
     * @return bool
     */
    public function copyFromString($str, $dst, Resource $res = null, $overwrite = true);

    /**
     * Copies a file
     *
     * @param string $str
     * @param string $dst
     * @param bool $overwrite
     * @return bool
     */
    public function copy($src, $dst, $overwrite = true);
}

?>
