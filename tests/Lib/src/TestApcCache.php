<?php
/**
 * TestApcCache.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestApcCache extends PHPUnit_Framework_TestCase
{
    protected $id = 'uniquestring';

    public function setUp()
    {
        if (!function_exists('apc_cache_info'))
        {
            $this->markTestSkipped('APC extension is not installed');
            return ;
        }

        $cache = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();
    }

    public function tearDown()
    {
        if (!function_exists('apc_cache_info'))
        {
            $this->markTestSkipped('APC extension is not installed');
            return ;
        }

        $cache = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();
    }

    public function testStoreArray()
    {
        $cache = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $array = array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_array', $array, 10));
        $this->assertEquals($cache->read('key_array'), $array);
    }

    public function testStoreObjects()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $object = (object) array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_object', $object, 10));
        $this->assertEquals($cache->read('key_object'), $object);
    }

    public function testStoreStrings()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $string = 'This is a string! ?  345 345 sdf # @ $ % & *';

        $this->assertTrue($cache->store('key_string', $string, 10));
        $this->assertEquals($cache->read('key_string'), $string);
    }

    public function testNonExistant()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $this->assertNull($cache->read('unknown_key'));
    }

    public function testDelete()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $this->assertTrue($cache->store('del_key', 'this is an example', 10));
        $this->assertTrue($cache->delete('del_key'));
    }

    public function testDisabled()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->disableCache(true);

        $this->assertFalse($cache->store('disabled_key', 'Dummy Data', 10));
        $this->assertNull($cache->read('disabled_key'));
    }

    public function testFlush()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key3', 'Dummy Data', 10));

        $this->assertEquals($cache->flush(), 3);

        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key2'));
        $this->assertNull($cache->read('flush_key3'));
    }

    /**
     * Test Cache Duration
     * The last part of this test is going to fail, here is the reason:
     *
     * https://bugs.php.net/bug.php?id=59343
     * Generally you never do a store and a fetch from within the
     * same request.  It makes very little sense to do so since you
     * have the data you stored already.  In order to save a system
     * call, apc_fetch() call uses the request time which is set at
     * the start of the request to check the ttl of the entry.  If
     * you really want to store and fetch from within the same
     * request set: apc.use_request_time = 0
     */
    public function testDuration()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);

        $this->assertTrue($cache->store('timed_string', 'Dummy Data', 1));

        // sleep(3);
        // $this->assertNull($cache->read('timed_string'));
    }

    public function testFlushPattern()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1_pat1', 'Dummy Data 1', 10));
        $this->assertTrue($cache->store('flush_key2_pat1', 'Dummy Data 2', 10));
        $this->assertTrue($cache->store('flush_key3_pat2', 'Dummy Data', 10));

        $this->assertEquals($cache->flushPattern('*_pat1'), 2);

        //$this->assertNull($cache->read('flush_key1_pat1'));
        //$this->assertNull($cache->read('flush_key1_pat1'));
        $this->assertEquals($cache->read('flush_key3_pat2'), 'Dummy Data');

        $cache->flush();

    }

    public function testUsedCache()
    {
        $cache  = new \Bolido\Cache\ApcEngine($this->id);

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