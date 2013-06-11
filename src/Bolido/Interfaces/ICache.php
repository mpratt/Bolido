<?php
/**
 * ICache.php
 * This is the interface that should be used by Objects containing
 * Cache capabilities.
 *
 * @package Bolido.Interfaces
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Interfaces;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface ICache
{
    /**
     * Stores the cache data to a resource
     *
     * @param string $key
     * @param mixed $data
     * @param int $ttl The time in seconds that the cache is going to last
     * @return bool
     */
    public function store($key, $data, $ttl);

    /**
     * Reads cache data
     *
     * @param string $key
     * @return mixed
     */
    public function read($key);

    /**
     * Deletes a Cache resource based on its key
     *
     * @param string $key
     * @return bool
     */
    public function delete($key);

    /**
     * Disables/Enables the cache functionality
     *
     * @param bool $bool True if the cache should be disabled, false otherwise
     * @return void
     */
    public function disableCache($bool);

    /**
     * flushes all cache resources
     *
     * @return int The count of deleted resources
     */
    public function flush();

    /**
     * flushes all cache resource matching certain $pattern
     *
     * @param string $pattern The pattern you need to match
     * @return int The count of deleted resources
     */
    public function flushPattern($pattern);

    /**
     * Shows how many identifiers were used by the engine.
     *
     * @return int
     */
    public function usedCache();
}
?>
