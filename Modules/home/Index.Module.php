<?php
/**
 * Index.Module.php, Home Module.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class home extends ModuleAdapter
{
    /**
     * The frontpage!
     * @return void
     */
    public function index()
    {
        echo 'Hola!';
    }
}
?>