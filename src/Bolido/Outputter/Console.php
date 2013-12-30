<?php
/**
 * Console.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Outputter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command-line Outputter
 * Its basically a decorator for symfony's console Outputter
 */
class Console implements OutputterInterface
{
    /**
     * Construct
     *
     * @param object $outputter Implementing OutputInterface
     * @return void
     */
    public function __construct(OutputInterface $outputter)
    {
        $this->outputter = $outputter;
    }

    /** inline {@inheritdoc} */
    public function write($msg)
    {
        $this->outputter->writeln($msg);
    }
}
