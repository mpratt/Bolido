<?php
/**
 * FileCache.class.php
 * This class has the hability to cache data into a file.
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class FileCache implements iCache
{
    protected $tracked = 0;
    protected $enabled = true;
    protected $prefix  = 'bolidoCache';
    protected $location;

    /**
     * Construct
     *
     * @param string $location The path where the files are going to be stored
     * @return void
     */
    public function __construct($location)
    {
        $this->location = $location;
        if (empty($this->location) || !is_dir($this->location) || !is_writable($this->location))
        {
            $this->enabled = false;
            if (IN_DEVELOPMENT)
                throw new Exception('Disabling Cache. The Cache dir is not writable!');
        }
    }

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

        $dataArray = array('expire_time' => (time() + ((is_numeric($ttl) && $ttl > 0 ? $ttl : 60))),
                           'content'     => $data,
                           'created'     => date('Y-m-d H:i:s'));

        $createFile = file_put_contents($this->location . '/' . $this->createFileName($key), serialize($dataArray), LOCK_EX);

        return (bool) ($createFile !== false && $createFile > 0);
    }

    /**
     * Reads cache data
     *
     * @param string $key the identifier of the cache file
     * @return mixed The cached data or null if it failed
     */
    public function read($key)
    {
        $file = $this->location . '/' . $this->createFileName($key);
        if (!$this->enabled || !file_exists($file))
            return null;

        $data = unserialize(file_get_contents($file));
        if (!$data || !is_array($data) || empty($data['expire_time']) || empty($data['content']) || ($data['expire_time'] < time()))
        {
            $this->delete($key);
            return null;
        }

        $this->tracked++;
        return $data['content'];
    }

    /**
     * Deletes a Cache file based on its key
     *
     * @param string $key the identifier of the cache file
     * @return bool True if the file was deleted, false otherwise
     */
    public function delete($key)
    {
        $file = $this->location . '/' . $this->createFileName($key);
        if (file_exists($file))
            return unlink($file);

        return false;
    }

    /**
     * flushes all cache files in $this->location
     *
     * @return int The count of files deleted
     */
    public function flush()
    {
        return $this->flushPattern('*');
    }

    /**
     * flushes all cache files in $this->location matching certain $pattern
     *
     * @param string $pattern The pattern we need to match
     * @return int The count of files deleted
     */
    public function flushPattern($pattern)
    {
        $count = 0;
        $pattern .= '*';
        foreach (glob($this->location . '/' . $pattern) as $file)
        {
            $file = basename($file);
            if (is_file($this->location . '/' . $file) && strpos($file, '.cache') !== false)
            {
                unlink($this->location . '/' . $file);
                $count++;
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
    public function usedCache() { return $this->tracked; }

    /**
     * Calculates the filename for $key
     *
     * @param string $key The key identifier for the file
     * @return string
     */
    protected function createFileName($key)
    {
        return $this->prefix . '-' . str_replace(array('/', '"', '\'', '.'), '', $key) . '_' . md5($key) . '.cache';
    }
}
?>
