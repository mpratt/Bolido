<?php
/**
 * Filesystem.php
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
class Filesystem implements FilesystemInterface
{
    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** inline {@inheritdoc} */
    public function __construct(OutputterInterface $outputter)
    {
        $this->outputter = $outputter;
    }

    /** inline {@inheritdoc} */
    public function mkdir($dir, $recursive = true)
    {
        if (!file_exists($dir)) {
            $this->outputter->write('<comment>Creating dir</comment>: ' . $dir);
            return mkdir($dir, 0777, $recursive);
        }

        return true;
    }

    /** inline {@inheritdoc} */
    public function copyFromString($str, $dst)
    {
        $this->prepare($dst);
        if (!is_dir(dirname($dst))) {
            $this->outputter->write('<comment>Creating dir</comment>: ' . dirname($dst));
            $this->mkdir(dirname($dst), true);
        }

        return file_put_contents($dst, $str);
    }

    /** inline {@inheritdoc} */
    public function copy(Resource $src, $dst)
    {
        $this->prepare($dst);
        return copy($src, $dst);
    }

    /** inline {@inheritdoc} */
    public function exists($file)
    {
        return file_exists($file);
    }

    /** inline {@inheritdoc} */
    public function unlink($file)
    {
        if (is_dir($file)) {
            return rmdir($file);
        }

        return unlink($file);
    }

    /**
     * Prepares the destination file/dir
     *
     * @param string $dst
     * @return void
     */
    protected function prepare($dst)
    {
        if (file_exists($dst)) {
            $this->outputter->write('<comment>Overwriting</comment>: ' . $dst);
            $this->unlink($dst);
        } else {
            $this->outputter->write('<comment>Copying file</comment>: ' . $dst);
        }
    }
}

?>
