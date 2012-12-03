<?php
/**
 * ICache.php
 * This is the interface that should be used by Objects containing
 * Cache capabilities.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\App\Interfaces;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface ICache
{
    /**
     * Stores the cache data to a resource
     *
     * @param string $key The Identifier key for the file
     * @param mixed $data The data that is going to be saved
     * @param int $ttl The time in seconds that the cache is going to last
     * @return bool True if the cache was saved successfully. False otherwise
     */
    public function store($key, $data, $ttl);

    /**
     * Reads cache data
     *
     * @param string $key the identifier of the cache resource
     * @return mixed The cached data or null if it failed
     */
    public function read($key);

    /**
     * Deletes a Cache resource based on its key
     *
     * @param string $key the identifier of the cache resource
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
     * @return int The count of resources
     */
    public function flush();

    /**
     * flushes all cache resource matching certain $pattern
     *
     * @param string $pattern The pattern we need to match
     * @return int The count of resources deleted
     */
    public function flushPattern($pattern);

    /**
     * Shows how many identefiers were used by the engine.
     *
     * @return int
     */
    public function usedCache();
}
?>
