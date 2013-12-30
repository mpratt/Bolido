<?php
/**
 * OutputterInterface.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Outputter;

/**
 * Interface for outputters, that is, classes that can output stuff
 * into whatever environment
 */
interface OutputterInterface
{
    /**
     * Construct
     *
     * @param string $write
     * @return void
     */
    public function write($msg);
}

?>
