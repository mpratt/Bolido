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

namespace Bolido\Filesystem;

use Symfony\Component\Yaml\Parser;

/**
 * Class that is responsable for normalizing the front-matter data
 */
class FrontMatter
{
    /** @var array The processed front-matter */
    protected $matter = array();

    /** @var string The guessed content of the file */
    protected $contents;

    /** @var string The namespace */
    protected $ns;

    /**
     * Construct
     *
     * @param array $data
     * @param string $ns
     * @return void
     */
    public function __construct(array $data, $ns)
    {
        list($matter, $this->contents) = $data;
        $yaml = new Parser();
        $this->matter = $yaml->parse($matter);

        if (isset($this->matter['namespace'])) {
            $this->ns = $this->matter['namespace'];
        } else {
            $this->ns = $ns;
        }
    }

    /**
     * Returns an array with the normalized front-matter
     *
     * @return array
     */
    public function getResults()
    {
        $this->findCategories();
        $this->findExcerpt();
        return (array) $this->matter;
    }

    /**
     * Finds tags/categories in the front-matter and normalizes their content/keys
     *
     * @return void
     */
    protected function findCategories()
    {
        $registry = array();
        foreach (array_keys($this->matter) as $key) {
            // Search for keys named tags/category/categories
            if (in_array(strtolower($key), array('tags', 'category', 'categories'))) {
                foreach ((array) $this->matter[$key] as $raw) {
                    // Normalize category keys into categories
                    $normalized = str_replace('category', 'categories', strtolower($key));
                    $urlParts = array(
                        '/' . $this->ns,
                        substr($normalized, 0, 3) . '-' . \URLify::filter($raw) . '.html'
                    );

                    $registry[$normalized][] = array(
                        'category_name' => trim($raw, '/ '),
                        'category_url' => preg_replace('~//+~', '/', implode('/', $urlParts)),
                    );
                }

                unset($this->matter[$key]);
            }
        }

        $this->matter = array_merge($this->matter, $registry);
    }

    /**
     * From the given file content, tries to extract an excerpt
     *
     * @return void
     */
    protected function findExcerpt()
    {
        if (isset($this->matter['excerpt']) || empty($this->contents)) {
            return ;
        }

        $contents = substr(trim(strip_tags($this->contents), ' .'), 0, 500);
        if (strpos($contents, '.') !== false) {
            $contents = substr($contents, 0, strrpos($contents, '.'));
        }

        $this->matter['excerpt'] = $contents;
    }
}
?>
