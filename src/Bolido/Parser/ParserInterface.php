<?php
/**
 * ParserInterface.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Parser;

use Bolido\Filesystem\Resource;

/**
 * Interface used by parser objects
 */
interface ParserInterface
{
    /**
     * Parses a Resource
     *
     * @param object $resource Instance of Resource
     * @param array $variables Additional Variables
     * @return string
     */
    public function parseResource(Resource $resource, array $variables = array());

    /**
     * Parses a string
     *
     * @param string $string
     * @param array $variables Additional Variables
     * @return string
     */
    public function parseString($string, array $variables = array());
}

?>
