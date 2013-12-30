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
    public function mkdir($dir)
    {
        if (!file_exists($dir)) {
            $this->outputter->write('<comment>Creating dir</comment>: ' . $dir);
            return mkdir($dir);
        }

        return true;
    }

    /** inline {@inheritdoc} */
    public function exists($file)
    {
        return file_exists($file);
    }

    /** inline {@inheritdoc} */
    public function unlink($file)
    {
        return unlink($file);
    }

    /** inline {@inheritdoc} */
    public function copyFromString($str, $dst, Resource $res = null, $overwrite = true)
    {
        if (file_exists($dst)) {
            if (!$overwrite && !is_null($res) && $res->getCTime() < filectime($dst)) {
                $this->outputter->write('<comment>Skipping</comment>: ' . $dst);
                return ;
            }

            $this->outputter->write('<comment>Overwriting</comment>: ' . $dst);
            unlink($dst);
        } else {
            $this->outputter->write('<comment>Creating file</comment>: ' . $dst);
        }

        if (!is_dir(dirname($dst))) {
            $this->outputter->write('<comment>Creating dir</comment>: ' . dirname($dst));
            mkdir(dirname($dst), 0777, true);
        }

        return file_put_contents($dst, $str);
    }

    /** inline {@inheritdoc} */
    public function copy($src, $dst, $overwrite = true)
    {
        if (file_exists($dst)) {
            if (!$overwrite && $src->getCTime() < filectime($dst)) {
                $this->outputter->write('<comment>Skipping</comment>: ' . $dst);
                return ;
            }

            $this->outputter->write('<comment>Overwriting</comment>: ' . $dst);
            unlink($dst);
        } else {
            $this->outputter->write('<comment>Copying file</comment>: ' . $dst);
        }

        return copy($src, $dst);
    }
}

?>
