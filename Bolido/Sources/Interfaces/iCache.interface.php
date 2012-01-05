<?php
/**
 * iCache.interface.php
 * This is the interface that should be used by Objects containing
 * Cache capabilities.
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

interface iCache
{
    public function store($key, $data, $MinutesToLive);
    public function read($key);
    public function delete($key);
    public function disableCache($bool);
    public function flush();
    public function usedCache();
}
?>