<?php
/**
 * Application.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Console;

use Bolido\Console\BolidoCommand;
use Symfony\Component\Console\Application as ConsoleApp;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Runs Bolido as a CLI application
 */
class Application extends ConsoleApp
{
    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    /** inline {@inheritdoc} */
    protected function getCommandName(InputInterface $input)
    {
        return 'bolido';
    }

    /** inline {@inheritdoc} */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(new BolidoCommand()));
    }
}

?>
