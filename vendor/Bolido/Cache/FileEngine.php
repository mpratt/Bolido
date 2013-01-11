<?php
/**
 * FileEngine.php
 * This class has the hability to cache data into a file.
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

class FileEngine implements \Bolido\Interfaces\ICache
{
    protected $tracked = array();
    protected $enabled = true;
    protected $prefix  = 'bolidoCache';
    protected $location;

    /**
     * The relevant documentation can be found on the
     * \Bolido\Interfaces\ICache file.
     *
     * This class uses __destruct and __construct methods
     * that are not defined by the interface, but are documented
     * in this file.
     */

    /**
     * Construct
     *
     * @param string $location The path where the files are going to be stored
     * @return void
     *
     * @throws RuntimeException when the $location is invalid and Development mode
     *                          is enabled.
     */
    public function __construct($location)
    {
        $this->location = $location;

        // @codeCoverageIgnoreStart
        if (!is_dir($this->location) || !is_writable($this->location))
        {
            $this->enabled = false;
            if (DEVELOPMENT_MODE)
                throw new \RuntimeException('Disabling Cache. The Cache dir is not writable!');
        }
        // @codeCoverageIgnoreEnd
    }

    public function store($key, $data, $ttl)
    {
        if (!$this->enabled)
            return false;

        $dataArray = array('expire_time' => (time() + ((is_numeric($ttl) && $ttl > 0 ? $ttl : 60))),
                           'content'     => $data,
                           'created'     => date('Y-m-d H:i:s'));

        $createFile = file_put_contents($this->location . '/' . $this->createFileName($key), serialize($dataArray), LOCK_EX);

        return (bool) ($createFile !== false && $createFile > 0);
    }

    public function read($key)
    {
        $file = $this->location . '/' . $this->createFileName($key);
        if (!$this->enabled || !file_exists($file))
            return null;

        $data = unserialize(file_get_contents($file));
        if (empty($data['expire_time']) || ($data['expire_time'] < time()))
        {
            $this->delete($key);
            return null;
        }

        $this->tracked[$key] = true;
        return $data['content'];
    }

    public function delete($key)
    {
        $file = $this->location . '/' . $this->createFileName($key);
        return @unlink($file);
    }

    public function flush() { return $this->flushPattern('*'); }

    public function flushPattern($pattern)
    {
        $count = 0;
        $pattern .= '*';
        foreach (glob($this->location . '/' . $pattern) as $file)
        {
            $file = basename($file);
            if (strpos($file, '.cache') !== false)
            {
                @unlink($this->location . '/' . $file);
                $count++;
            }
        }

        return $count;
    }

    public function disableCache($bool) { $this->enabled = !$bool; }

    public function usedCache() { return count($this->tracked); }

    protected function createFileName($key)
    {
        return $this->prefix . '-' . str_replace(array('/', '"', '\'', '.'), '', $key) . '_' . md5($key) . '.cache';
    }

    /**
     * Destruct Method
     * If the cache is disabled, flushes all the cache
     * files.
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
