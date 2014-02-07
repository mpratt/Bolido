<?php
/**
 * LessParser.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Parser;

use \Less_Parser;
use Bolido\Filesystem\Resource;

/**
 * Parser of Less files
 */
class LessParser implements ParserInterface
{
    /** @var object Less Parser */
    protected $parser;

    /**
     * Construct
     *
     * @param Config $config
     * @return void
     */
    public function __construct()
    {
        $this->parser = new Less_Parser();
    }

    /** inline {@inheritdoc} */
    public function parseResource(Resource $resource, array $variables = array())
    {
        $this->parser->parseFile((string) $resource);
        return $this->parse->getCss();
    }

    /** inline {@inheritdoc} */
    public function parseString($string, array $variables = array())
    {
        $this->parser->parse($string);
        return $this->parse->getCss();
    }
}

?>
