<?php
/**
 * ApcEngine.php
 * This class has the hability to cache data if the apc extension
 * is loaded.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Cache;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class ApcEngine implements \Bolido\Interfaces\ICache
{
    protected $enabled = true;
    protected $usedCache = array();

    /**
     * The relevant documentation can be found on the
     * \Bolido\Interfaces\ICache file.
     *
     * This class uses a __destruct method that is
     * not defined by the interface, but is documented
     * at the end of this file.
     */
    public function store($key, $data, $ttl)
    {
        if (!$this->enabled)
            return false;

        return @apc_store($key, $data, (int) $ttl);
    }

    public function read($key)
    {
        if ($this->enabled && apc_exists($key))
        {
            $this->usedCache[$key] = true;
            return apc_fetch($key);
        }

        return null;
    }

    public function delete($key)
    {
        return @apc_delete($key);
    }

    public function flush()
    {
        $info = apc_cache_info('user');
        $usedCache = count($info['cache_list']);

        apc_clear_cache();
        apc_clear_cache('user');

        return $usedCache;
    }

    public function flushPattern($pattern)
    {
        if (empty($pattern) || trim($pattern) == '*')
            return $this->flush();

        $count = 0;
        $info  = apc_cache_info('user');
        if (!empty($info['cache_list']))
        {
            $pattern = str_replace('\*', '(.)+', preg_quote($pattern, '~'));
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

    public function disableCache($bool) { $this->enabled = !$bool; }

    public function usedCache()
    {
        /* $info = apc_cache_info('user'); return $info['num_entries']; */
        return count($this->usedCache);
    }

    /**
     * Destruct Method
     * If the cache is disabled, flushes all the cache
     * data.
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
