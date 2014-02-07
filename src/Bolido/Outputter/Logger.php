<?php
/**
 * Logger.php
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
 * Logger Outputter
 * Stores the content into a specific folder
 */
class Logger implements OutputterInterface
{
    /** @var string The file where the messages are going to be saved */
    protected $file;

    /** @var int time when the operation was started */
    protected $startTime = 0;

    /**
     * Construct
     *
     * @param string $dir
     * @return void
     */
    public function __construct($dir)
    {
        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(
                sprintf('The logger directory/file "%s" is not writable', $dir)
            );
        }

        if (is_file($dir)) {
            $this->file = $dir;
        } else {
            $this->file = rtrim($dir, '/ ') . '/bolido-' . date('Y-m-d') . '.log';
        }

        $this->startTime = microtime(true);
    }

    /**
     * Strips tags from the given message
     *
     * @param string $msg
     * @return string
     */
    protected function cleanMsg($msg)
    {
        $words = array('question', 'error', 'comment', 'info');
        return preg_replace('~</?(' . implode('|', $words) . ')>~i', '', $msg);
    }

    /** inline {@inheritdoc} */
    public function write($msg)
    {
        $msg = $this->cleanMsg($msg);
        file_put_contents($this->file, $msg . PHP_EOL, FILE_APPEND);
    }

    /**
     * Destruct
     * Used to output duration of the operation
     *
     * @return void
     */
    public function __destruct()
    {
        $dur = round(microtime(true) - $this->startTime, 3);
        $this->write('<info>Total Duration: </info>' . $dur . ' seconds');
        $this->write('|==================================|' . PHP_EOL . PHP_EOL);
    }
}
?>
