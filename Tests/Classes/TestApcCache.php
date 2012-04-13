<?php
/**
 * TestApcCache.php
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
    define('BOLIDO', 'TestApcCache');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Interfaces/iCache.interface.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/ApcCache.class.php');

class TestApcCache extends PHPUnit_Framework_TestCase
{
    /**
     * Setup the environment
     */
    public function setUp()
    {
        $cache = new ApcCache();
        $cache->flush();
    }

    /**
     * Cleanup the environment after testing
     */
    public function tearDown()
    {
        $cache = new ApcCache();
        $cache->flush();
    }

    /**
     * Test Cache stores arrays
     */
    public function testStoreArray()
    {
        $cache = new ApcCache();
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
        $cache  = new ApcCache();
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
        $cache  = new ApcCache();
        $cache->flush();

        $string = 'This is a string! ?  345 345 sdf # @ $ % & *';

        $this->assertTrue($cache->store('key_string', $string, 10));
        //$this->assertEquals($cache->read('key_string'), $string);
    }

    /**
     * Test Cache for nonexistant keys
     */
    public function testNonExistant()
    {
        $cache  = new ApcCache();
        $this->assertNull($cache->read('unknown_key'));
    }

    /**
     * Test Cache deletion
     */
    public function testDelete()
    {
        $cache  = new ApcCache();
        $cache->flush();

        $this->assertTrue($cache->store('del_key', 'this is an example', 10));
        //$this->assertTrue($cache->delete('del_key'));
    }

    /**
     * Test Cache Disable
     */
    public function testDisabled()
    {
        $cache  = new ApcCache();
        $cache->disableCache(true);

        $this->assertFalse($cache->store('disabled_key', 'Dummy Data', 10));
        $this->assertNull($cache->read('disabled_key'));
    }

    /**
     * Test Cache Flush
     */
    public function testFlush()
    {
        $cache  = new ApcCache();
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
