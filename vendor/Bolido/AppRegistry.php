<?php
/**
 * AppRegistry.php
 * A basic registry.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class AppRegistry implements \ArrayAccess
{
    private $values = array();

    /**
     * Stores an object
     *
     * @param string $id
     * @param object $value
     * @return void
     *
     * @throws InvalidArgumentException if the $value is not an object
     */
    public function offsetSet($id, $value)
    {
        if (!is_object($value))
            throw new \InvalidArgumentException('The App registry only accepts objects.');

        $this->values[$id] = $value;
    }

    /**
     * An alias method for storing objects
     */
    public function attach($id, $value) { $this->offsetSet($id, $value); }

    /**
     * Fetches the object mapped to the $id key
     *
     * @param string $id
     * @return object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id)
    {
        if (!isset($this->values[$id]))
            throw new \InvalidArgumentException('The registry key "' . $id . '" doesnt exist.');

        return $this->values[$id];
    }

    /**
     * Checks if an object is mapped
     *
     * @param string $id
     * @return Boolean
     */
    public function offsetExists($id) { return (isset($this->values[$id])); }

    /**
     * Unsets an object.
     *
     * @param string $id
     * @return void
     */
    public function offsetUnset($id)
    {
        if (isset($this->values[$id]))
            unset($this->values[$id]);
    }

    /**
     * Returns all defined keys.
     *
     * @return array
     */
    public function keys() { return array_keys($this->values); }
}
?>
