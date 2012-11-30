<?php
/**
 * Apc.php
 * This class has the hability to cache data into a file.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\App\Cache;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Apc implements Bolido\App\Interfaces\ICache
{
    protected $enabled = true;

    /**
     * Stores the cache data to a file
     *
     * @param string $key The Identifier key for the file
     * @param mixed $data The data that is going to be saved
     * @param int $ttl The time in seconds that the cache is going to last
     * @return bool True if the cache was saved successfully. False otherwise
     */
    public function store($key, $data, $ttl)
    {
        if (!$this->enabled || empty($data) || empty($key))
            return false;

        $this->delete($key);

        $ttl = (is_numeric($ttl) && $ttl > 30 ? $ttl : 30);

        if (is_string($data))
            return apc_add($key, $data, $ttl);

        return @apc_store($key, $data, $ttl);
    }

    /**
     * Reads cache data
     *
     * @param string $key the identifier of the cache file
     * @return mixed The cached data or null if it failed
     */
    public function read($key)
    {
        if ($this->enabled && apc_exists($key))
        {
            $return = apc_fetch($key);
            if (!empty($return))
                return $return;
        }

        return ;
    }

    /**
     * Deletes a Cache file based on its key
     *
     * @param string $key the identifier of the cache file
     * @return bool True if the file was deleted, false otherwise
     */
    public function delete($key)
    {
        if (apc_exists($key))
            return apc_delete($key);

        return false;
    }

    /**
     * flushes all cache
     *
     * @return int The count of files deleted
     */
    public function flush()
    {
        $info = apc_cache_info('user');
        $usedCache = count($info['cache_list']);

        apc_clear_cache();
        apc_clear_cache('user');

        return $usedCache;
    }

    /**
     * flushes all cache stuff matching certain $pattern
     *
     * @param string $pattern The pattern we need to match
     * @return int The count of files deleted
     */
    public function flushPattern($pattern)
    {
        if (empty($pattern) || !is_string($pattern) || trim($pattern) == '*')
            return $this->flush();

        $count = 0;
        $info  = apc_cache_info('user');
        if (!empty($info['cache_list']))
        {
            $pattern = str_replace('\*', '(.*)', preg_quote($pattern, '~'));
            foreach ($info['cache_list']  as $cache)
            {
                if (preg_match('~' . $pattern . '~i', $cache['info']))
                {
                    $this->delete($cache['info']);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Enables the cache functionality
     *
     * @param bool $bool True if the cache should be disabled, false otherwise
     * @return void
     */
    public function disableCache($bool) { $this->enabled = !$bool; }

    /**
     * Shows how many files were read from the cache.
     *
     * @return int
     */
    public function usedCache()
    {
        $info = apc_cache_info('user');
        return $info['num_entries'];
    }

    /**
     * Destruct Method
     * If the cache is disabled, flushes all the cache.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->enabled)
            $this->flush();
    }
}
?>
