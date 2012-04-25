<?php
/**
 * Config-Test.php
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

require_once(dirname(__FILE__). '/../Bolido/Sources/Interfaces/iConfig.interface.php');
final class TestConfig implements iConfig
{
    /**
     * Returns the value of the config var
     *
     * @return mixed
     */
    public function get($var)
    {
        if (property_exists($this, $var))
            return $this->$var;

        throw new Exception('Unknown Config Var: ' . $var);
    }

    /**
     * Sets the value of a config
     *
     * @return void
     */
    public function set($var, $value)
    {
        $this->$var = $value;
    }
}
?>
