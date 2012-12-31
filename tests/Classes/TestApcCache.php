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

require_once('../vendor/Bolido/Interfaces/ICache.php');
require_once('../vendor/Bolido/Cache/ApcEngine.php');
class TestApcCache extends PHPUnit_Framework_TestCase
{
    /**
     * Setup the environment
     */
    public function setUp()
    {
        if (!function_exists('apc_cache_info'))
        {
            $this->markTestSkipped('APC extension is not installed');
            return ;
        }

        $cache = new \Bolido\Cache\ApcEngine();
        $cache->flush();
    }

    /**
     * Cleanup the environment after testing
     */
    public function tearDown()
    {
        if (!function_exists('apc_cache_info'))
        {
            $this->markTestSkipped('APC extension is not installed');
            return ;
        }

        $cache = new \Bolido\Cache\ApcEngine();
        $cache->flush();
    }

    /**
     * Test Cache stores arrays
     */
    public function testStoreArray()
    {
        $cache = new \Bolido\Cache\ApcEngine();
        $cache->flush();

        $array = array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_array', $array, 10));
        $this->assertEquals($cache->read('key_array'), $array);
    }

    /**
     * Test Cache stores Objects
     */
    public function testStoreObjects()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $cache->flush();

        $object = (object) array('1', 'asdasd eregrergfdgf dfgdfgjk dfg', '#$^4@35454*(/)');

        $this->assertTrue($cache->store('key_object', $object, 10));
        $this->assertEquals($cache->read('key_object'), $object);
    }

    /**
     * Test Cache stores Strings
     */
    public function testStoreStrings()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $cache->flush();

        $string = 'This is a string! ?  345 345 sdf # @ $ % & *';

        $this->assertTrue($cache->store('key_string', $string, 10));
        $this->assertEquals($cache->read('key_string'), $string);
    }

    /**
     * Test Cache for nonexistant keys
     */
    public function testNonExistant()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $this->assertNull($cache->read('unknown_key'));
    }

    /**
     * Test Cache deletion
     */
    public function testDelete()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $cache->flush();

        $this->assertTrue($cache->store('del_key', 'this is an example', 10));
        $this->assertTrue($cache->delete('del_key'));
    }

    /**
     * Test Cache Disable
     */
    public function testDisabled()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $cache->disableCache(true);

        $this->assertFalse($cache->store('disabled_key', 'Dummy Data', 10));
        $this->assertNull($cache->read('disabled_key'));
    }

    /**
     * Test Cache Flush
     */
    public function testFlush()
    {
        $cache  = new \Bolido\Cache\ApcEngine();
        $cache->flush();

        $this->assertTrue($cache->store('flush_key1', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key2', 'Dummy Data', 10));
        $this->assertTrue($cache->store('flush_key3', 'Dummy Data', 10));

        $this->assertEquals($cache->flush(), 3);

        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
        $this->assertNull($cache->read('flush_key1'));
    }
}
?>
