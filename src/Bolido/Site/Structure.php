<?php
/**
 * Structure.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use Bolido\Filesystem\Collection;
use Bolido\Filesystem\FilesystemInterface;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable of generating the folder structure
 * of the site
 */
class Structure
{
    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object instance of Collection */
    protected $collection;

    /** @var object Implementing FilesystemInterface */
    protected $filesystem;

    /**
     * Construct
     *
     * @param object $outputter
     * @param object $collection
     * @param object $filesystem
     * @return void
     */
    public function __construct(OutputterInterface $outputter, Collection $collection, FilesystemInterface $filesystem)
    {
        $this->collection = $collection;
        $this->outputter = $outputter;
        $this->filesystem = $filesystem;
    }

    /**
     * Creates/mirrors the folder structure into the $destiny
     * folder.
     *
     * @param string $destiny
     * @return void
     */
    public function mirror($destiny)
    {
        $this->outputter->write('<info>Creating folder structure</info>');
        $directories = $this->collection->getDirectories();

        foreach ($directories as $dir) {
            $full = rtrim($destiny, '/') . '/' . ltrim($dir->getRelativePath(), '/');
            $this->filesystem->mkdir($full);

            $this->cleanFolder($full, array(
                'index.html', 'categories.html', 'tags.html'
            ));
        }

        $this->outputter->write('<info>Finished folder structure</info>');
    }

    /**
     * Cleans some files from the
     *
     *
     */
    protected function cleanFolder($context = '', array $files = array())
    {
        $context = rtrim($context, '/');
        foreach ($files as $file) {
            $file = $context . '/' . $file;
            if ($this->filesystem->exists($file)) {
                $this->outputter->write('<comment>Cleaning </comment> ' . $file);
                $this->filesystem->unlink($file);
            }
        }
    }
}

?>
