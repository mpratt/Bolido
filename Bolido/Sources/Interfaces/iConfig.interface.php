<?php
/**
 * iConfig.interface.php
 * This is the interface that should be used by the Configuration Object

 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
 if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface iConfig
{
    public function set($var, $value);
    public function get($var);
}
?>
