<?php
/**
 * TestFileCache.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

if (!defined('BOLIDO'))
    define('BOLIDO', 'TestBrowserHandler');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Interfaces/iCache.interface.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/FileCache.class.php');

class TestFileCache extends PHPUnit_Framework_TestCase
{
    protected $cacheDir;

    /**
     * Setup the environment
     */
    public function setUp()
    {
        $this->cacheDir = realpath(dirname(__FILE__) . '/../Workspace/cache/');
        $cache = new FileCache($this->cacheDir);
        $cache->flush();
    }

    /**
     * Cleanup the environment after testing
     */
    public function tearDown()
    {
        $cache = new FileCache($this->cacheDir);
        $cache->flush();
    }

    /**
     * Test Cache stores arrays
     */
    public function testStoreArray()
    {
        $cache = new FileCache($this->cacheDir);
        $array = array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_array', $array, 10));
        $this->assertEquals($cache->read('key_array'), $array);
    }

    /**
     * Test Cache stores Objects
     */
    public function testStoreObjects()
    {
        $cache  = new FileCache($this->cacheDir);
        $object = (object) array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_object', $object, 10));
        $this->assertEquals($cache->read('key_object'), $object);
    }

    /**
     * Test Cache stores Strings
     */
    public function testStoreStrings()
    {
        $cache  = new FileCache($this->cacheDir);
        $string = 'This is a string! ? \' 345 345 sdf # @ $ % & *';

        $this->assertTrue($cache->store('key_string', $string, 10));
        $this->assertEquals($cache->read('key_string'), $string);
    }

    /**
     * Test Cache for nonexistant keys
     */
    public function testNonExistant()
    {
        $cache  = new FileCache($this->cacheDir);
        $this->assertNull($cache->read('unknown_key'));
    }

    /**
     * Test Cache Duration
     */
    public function testDuration()
    {
        $cache  = new FileCache($this->cacheDir);

        $this->assertTrue($cache->store('timed_string', 'Dummy Data', 1));
        sleep(2);
        $this->assertNull($cache->read('timed_string'));
    }

    /**
     * Test Cache deletion
     */
    public function testDelete()
    {
        $cache  = new FileCache($this->cacheDir);
        $this->assertTrue($cache->store('delete_key', 'this is an example', 10));
        $this->assertTrue($cache->delete('delete_key'));
    }

    /**
     * Test Cache Disable
     */
    public function testDisabled()
    {
        $cache  = new FileCache($this->cacheDir);
        $cache->disableCache(true);

        $this->assertFalse($cache->store('disabled_key', 'Dummy Data', 10));
        $this->assertNull($cache->read('disabled_key'));
    }

    /**
     * Test Cache Flush
     */
    public function testFlush()
    {
        $cache  = new FileCache($this->cacheDir);
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key3', 'Dummy Data', 10));

        $this->assertEquals($cache->flush(), 3);

        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
    }

    /**
     * Test Cache Flush Pattern
     */
    public function testFlushPattern()
    {
        $cache  = new FileCache($this->cacheDir);
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1_pat1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key2_pat1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key3_pat2', 'Dummy Data', 10));

        $this->assertEquals($cache->flushPattern('*_pat1'), 2);

        $this->assertNull($cache->read('flush_key1_pat1'));
        $this->assertNull($cache->read('flush_key1_pat1'));
        $this->assertEquals($cache->read('flush_key3_pat2'), 'Dummy Data');

        $cache->flush();
    }

    /**
     * Test Cache counts the used cache correctly
     */
    public function testUsedCache()
    {
        $cache  = new FileCache($this->cacheDir);

        $this->assertTrue($cache->store('key_1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('key_2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('key_3', 'Dummy Data', 10));

        $cache->read('key_1');
        $cache->read('key_2');

        $this->assertEquals($cache->usedCache(), 2);

        $cache->read('key_3');

        $this->assertEquals($cache->usedCache(), 3);
    }
}
?>
