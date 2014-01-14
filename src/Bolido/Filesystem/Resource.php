<?php
/**
 * Resource.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Filesystem;

/**
 * Class that handles information about a file/folder
 */
class Resource extends \SplFileInfo
{
    /** @var array Registry with cached results */
    protected $registry = array(
        'front-matter' => null,
        'file-contents' => null,
    );

    /** @var string The relative path of the file/folder */
    protected $relative;

    /**
     * @var string Regex used to match front-matter
     * @link Based on http://stackoverflow.com/questions/7052611/parsing-yaml-front-matter-with-php
     */
    protected $frontMatterRegex = '~(\n|\r\n)*[-]{3,}(\n|\r\n)*~';

    /**
     * Construct
     *
     * @param string $resource
     * @param array $config
     */
    public function __construct($resource, $relative)
    {
        if (trim($relative, '/ ') == '') {
            $relative = '/';
        }

        $this->relative = str_replace(' ', '-', trim($relative));
        parent::__construct($resource);
    }

    /**
     * Returns the relative path of the file/folder
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relative;
    }

    /**
     * Returns wether the file is parsable, meaning, it is either
     * a markdown, twig or less file.
     *
     * @return bool
     */
    public function isParsable()
    {
        return ($this->isLess() || $this->isMarkdown() || $this->isTwig());
    }

    /**
     * Checks if can be indexed
     *
     * @return bool
     */
    public function isIndexable()
    {
        $matter = $this->getFrontMatter();
        if (isset($matter['indexable'])) {
            return (bool) $matter['indexable'];
        }

        return ($this->isMarkdown() || $this->isTwig());
    }

    /**
     * Returns wether the file is less
     *
     * @return bool
     */
    public function isLess()
    {
        $ext = strtolower($this->getExtension());
        return (in_array($ext, array('less')));
    }

    /**
     * Returns wether the file is markdown
     *
     * @return bool
     */
    public function isMarkdown()
    {
        $ext = strtolower($this->getExtension());
        return (in_array($ext, array('md', 'markdown')));
    }

    /**
     * Returns wether the file is a twig template
     *
     * @return bool
     */
    public function isTwig()
    {
        $ext = strtolower($this->getExtension());
        return (in_array($ext, array('twig')));
    }

    /**
     * Returns the file namespace based their location or front-matter
     *
     * @param bool $useFrontMatter Wether or not to take into account the front-matter
     * @return string
     */
    public function getNamespace($useFrontMatter = true)
    {
        if ($this->isDir()) {
            $ns = $this->relative;
        } else {
            $ns = dirname($this->relative);
        }

        if ($useFrontMatter && $matter = $this->getFrontMatter()) {
            if (!empty($matter['namespace'])) {
                $ns = $matter['namespace'];
            }
        }

        return \URLify::filter($ns);
    }

    /**
     * Gets the parsed frontMatter of this file
     *
     * @return array
     */
    public function getFrontMatter()
    {
        if (!is_null($this->registry['front-matter'])) {
            return $this->registry['front-matter'];
        }

        if ($this->isMarkdown() || $this->isTwig()) {
            $content = file_get_contents($this);
            $parts = preg_split($this->frontMatterRegex, $content, 2, PREG_SPLIT_NO_EMPTY);

            if (count($parts) > 1) {
                $frontMatter = new FrontMatter($parts, $this->getNamespace(false));
                return $this->registry['front-matter'] = $frontMatter->getResults();
            }
        }

        return $this->registry['front-matter'] = array();
    }

    /**
     * Returns the contents of this file, without posible front matter
     *
     * @return string
     */
    public function getContents()
    {
        if (!is_null($this->registry['file-contents'])) {
            return $this->registry['file-contents'];
        }

        $content = file_get_contents($this);
        if ($this->getFrontMatter()) {
            $parts = preg_split($this->frontMatterRegex, $content, 2, PREG_SPLIT_NO_EMPTY);
            return $this->registry['file-contents'] = $parts['1'];
        }

        return $this->registry['file-contents'] = $content;
    }

    /**
     * Returns the title of the file
     *
     * @return string
     */
    public function getTitle()
    {
        $matter = $this->getFrontMatter();
        if (!empty($matter['title'])) {
            return $matter['title'];
        }

        $regex = array(
            '~^((\d{4})-(\d{1,2})-(\d{1,2})(?:[-_]*))~', // Remove date at the start of the file
            '~\.*' . preg_quote($this->getExtension(), '~') . '$~i' // Remove extension
        );

        $title = preg_replace($regex, '', $this->getFilename());
        return str_replace(array('-', '_'), ' ', trim($title));
    }

    /**
     * Returns the date of the file, based on $format
     *
     * @param string $format
     * @return string
     */
    public function getDate($format = 'Y-m-d')
    {
        $matter = $this->getFrontMatter();
        if (!empty($matter['date'])) {
            $date = $matter['date'];
        } else if (preg_match('~^((\d{4})-(\d{1,2})-(\d{1,2})(?:[-_]*))~', $this->getFilename(), $m)) {
            $date = trim($m['0'], '-_ ');
        } else {
            $date = $this->getCTime();
        }

        if (ctype_digit($date)) {
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($date);
        } else {
            $dateTime = new \DateTime($date);
        }

        return $dateTime->format($format);
    }
}
?>
