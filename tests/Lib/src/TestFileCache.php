<?php
/**
 * TestFileCache.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestFileCache extends PHPUnit_Framework_TestCase
{
    protected $cacheDir;

    public function setUp()
    {
        $this->cacheDir = CACHE_DIR;
        $cache = new \Bolido\Cache\FileEngine($this->cacheDir);
        $cache->flush();
    }

    public function tearDown()
    {
        $cache = new \Bolido\Cache\FileEngine($this->cacheDir);
        $cache->flush();
    }

    public function testStoreArray()
    {
        $cache = new \Bolido\Cache\FileEngine($this->cacheDir);
        $array = array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_array', $array, 10));
        $this->assertEquals($cache->read('key_array'), $array);
    }

    public function testStoreObjects()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $object = (object) array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_object', $object, 10));
        $this->assertEquals($cache->read('key_object'), $object);
    }

    public function testStoreStrings()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $string = 'This is a string! ? \' 345 345 sdf # @ $ % & *';

        $this->assertTrue($cache->store('key_string', $string, 10));
        $this->assertEquals($cache->read('key_string'), $string);
    }

    public function testNonExistant()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $this->assertNull($cache->read('unknown_key'));
    }

    public function testDuration()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);

        $this->assertTrue($cache->store('timed_string', 'Dummy Data', 1));
        sleep(2);
        $this->assertNull($cache->read('timed_string'));
    }

    public function testDelete()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $this->assertTrue($cache->store('delete_key', 'this is an example', 10));
        $this->assertTrue($cache->delete('delete_key'));
    }

    public function testDisabled()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $cache->disableCache(true);

        $this->assertFalse($cache->store('disabled_key', 'Dummy Data', 10));
        $this->assertNull($cache->read('disabled_key'));
    }

    public function testFlush()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key3', 'Dummy Data', 10));

        $this->assertEquals($cache->flush(), 3);

        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
    }

    public function testFlushPattern()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
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

    public function testUsedCache()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);

        $this->assertTrue($cache->store('key_1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('key_2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('key_3', 'Dummy Data', 10));

        $cache->read('key_1');
        $cache->read('key_2');

        $this->assertEquals($cache->usedCache(), 2);

        $cache->read('key_3');

        $this->assertEquals($cache->usedCache(), 3);
    }

    public function testInvalidLocation()
    {
        $this->setExpectedException('RuntimeException');
        $cache  = new \Bolido\Cache\FileEngine('/invalid/cache/path/');

        $this->assertFalse($cache->store('key_1', 'Dummy Data', 10));
        $this->assertfalse($cache->store('key_2', 'Dummy Data', 10));
        $this->assertFalse($cache->store('key_3', 'Dummy Data', 10));
    }

    public function testInvalidDelete()
    {
        $cache  = new \Bolido\Cache\FileEngine($this->cacheDir);
        $this->assertFalse($cache->delete('unexistant key'));
    }
}
?>
