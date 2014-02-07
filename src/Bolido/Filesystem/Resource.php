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
    /** @var string The relative path of the file/folder */
    protected $relative;

    /**
     * Construct
     *
     * @param string $resource
     * @param string $relative
     * @return void
     */
    public function __construct($resource, $relative = null)
    {
        parent::__construct($resource);
        $this->relative = '/' . trim($relative, '/ ');
    }

    /**
     * Returns the relative path of the file/folder
     *
     * @return string
     */
    public function getRelativePath($showDirOnly = false)
    {
        if ($showDirOnly && $this->isFile()) {
            return dirname($this->relative);
        }

        return $this->relative;
    }

    /**
     * Checks if the resource can be indexed.
     * By default only markdown and twig files are indexable
     *
     * @return bool
     */
    public function isIndexable()
    {
        return (in_array($this->getExtension(), array('twig','md', 'markdown')));
    }

    /**
     * Returns the extension of the file
     *
     * @return string
     */
    public function getExtension()
    {
        return strtolower(parent::getExtension());
    }

    /**
     * Returns the file name without date prefix and extensions
     *
     * @param bool $withoutExtension
     * @return string
     */
    public function getFilenameShort($withoutExtension = true)
    {
        // Remove date from the start of the file
        $regex = array('~^((\d{4})-(\d{1,2})-(\d{1,2})(?:[-_]*))~');

        // Remove the extension of the file
        if ($withoutExtension) {
            $regex[] = '~\.*' . preg_quote($this->getExtension(), '~') . '$~i';
        }

        return trim(preg_replace($regex, '', $this->getFilename()));
    }

    /**
     * Returns the contents of this file
     *
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this);
    }
}
?>
