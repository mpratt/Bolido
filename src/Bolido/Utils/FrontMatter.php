<?php
/**
 * FrontMatter.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Utils;

use Bolido\Filesystem\Resource;
use Symfony\Component\Yaml\Parser;

/**
 * Class that is responsable for normalizing the front-matter data
 */
class FrontMatter
{
    /**
     * @var string Regex used to match front-matter
     * @link Based on http://stackoverflow.com/questions/7052611/parsing-yaml-front-matter-with-php
     */
    protected $frontMatterRegex = '~(\n|\r\n)*[-]{3,}(\n|\r\n)*~';

    /** @var array The processed front-matter */
    protected $matter = array();

    /** @var string The content of the file, without front-matter */
    protected $contents;

    /**
     * Construct
     *
     * @param object $resource
     * @return void
     */
    public function __construct(Resource $resource)
    {
        $this->contents = $resource->getContents();
        $parts = preg_split($this->frontMatterRegex, $this->contents, 2, PREG_SPLIT_NO_EMPTY);

        if (count($parts) > 1) {
            $this->parse($parts);
        }
    }

    /**
     * Returns the parsed front-matter
     *
     * @return array
     */
    public function getMatter()
    {
        return (array) $this->matter;
    }

    /**
     * Returns the file contents
     *
     * @return string
     */
    public function getContents()
    {
        return (string) $this->contents;
    }

    /**
     * Parses the front-matter, separates the content
     * and stores the relevant data in properties.
     *
     * @param array $parts
     * @return void
     */
    protected function parse(array $parts = array())
    {
        try {
            $yaml = new Parser();
            $this->matter = $yaml->parse($parts['0']);
            $this->contents = $parts['1'];
            $this->buildExcerpt();
        } catch (\Exception $e) {
            // echo $e->getMessage();
        }
    }

    /**
     * From the given file content, tries to extract an excerpt
     *
     * @return void
     */
    protected function buildExcerpt()
    {
        if (isset($this->matter['excerpt'])) {
            return ;
        }

        $contents = preg_replace('~\{%.*?%\}|\n|\r~', '', $this->contents);
        $contents = preg_replace('~\s+~', ' ', $contents);
        $contents = substr(trim(strip_tags($contents), ' .'), 0, 500);

        if (strpos($contents, '.') !== false) {
            $contents = substr($contents, 0, strrpos($contents, '.'));
        }

        $this->matter['excerpt'] = $contents;
    }
}
?>
