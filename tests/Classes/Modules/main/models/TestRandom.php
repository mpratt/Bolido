<?php
/**
 * TestRandom.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\Random as Random;

require_once(BASE_DIR . '/../modules/main/models/Random.php');
class TestRandom extends PHPUnit_Framework_TestCase
{
    /**
     * Test Random bytes Generator
     */
    public function testBytes()
    {
        $random = new Random();
        $bytes1 = $random->bytes(2);
        $bytes2 = $random->bytes(2);
        $bytes3 = $random->bytes(2);
        $bytes4 = $random->bytes(2);
        $bytes5 = $random->bytes(2);
        $bytes6 = $random->bytes(2);

        $this->assertTrue((strlen($bytes1) == 2));
        $this->assertTrue((strlen($bytes2) == 2));
        $this->assertTrue((strlen($bytes3) == 2));
        $this->assertTrue((strlen($bytes4) == 2));
        $this->assertTrue((strlen($bytes5) == 2));
        $this->assertTrue((strlen($bytes6) == 2));
        $this->assertTrue(($bytes1 != $bytes2 && $bytes2 != $bytes3 && $bytes3 != $bytes1));
        $this->assertTrue(($bytes4 != $bytes5 && $bytes5 != $bytes6 && $bytes4 != $bytes6));
        $this->assertTrue(($bytes1 != $bytes6 && $bytes1 != $bytes4 && $bytes5 != $bytes1));
        $this->assertTrue(($bytes2 != $bytes6 && $bytes2 != $bytes4 && $bytes5 != $bytes2));
        $this->assertTrue(($bytes3 != $bytes6 && $bytes3 != $bytes4 && $bytes5 != $bytes3));
    }

    /**
     * Test Random bytes Generator
     */
    public function testBytesInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');

        $random = new Random();
        $bytes1 = $random->bytes();
    }

    /**
     * Test Random integer range Generator
     */
    public function testRange()
    {
        $random = new Random();
        $r1 = $random->range(5, 25);
        $r2 = $random->range(5, 25);
        $r3 = $random->range(5, 25);

        $this->assertTrue(($r1 != $r2 && $r2 != $r3 && $r3 != $r1));
        $this->assertTrue(($r1 >= 5 && $r1 <= 25));
        $this->assertTrue(($r2 >= 5 && $r2 <= 25));
        $this->assertTrue(($r3 >= 5 && $r3 <= 25));
    }

    /**
     * Test Random integer range Generator
     */
    public function testRange2()
    {
        $random = new Random();
        $r1 = $random->range(5, 200);
        $r2 = $random->range(5, 200);
        $r3 = $random->range(5, 200);

        $this->assertTrue(($r1 != $r2 && $r2 != $r3 && $r3 != $r1));
        $this->assertTrue(($r1 >= 5 && $r1 <= 200));
        $this->assertTrue(($r2 >= 5 && $r2 <= 200));
        $this->assertTrue(($r3 >= 5 && $r3 <= 200));
    }

    /**
     * Test Random bytes Generator
     */
    public function testRangeInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');

        $random = new Random();
        $random->range(10, 5);
    }

    /**
     * Test Random bool Generator
     */
    public function testBool()
    {
        $random = new Random();
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
        $this->assertTrue(is_bool($random->bool()));
    }

    /**
     * Test random string Generator
     */
    public function testString()
    {
        $random = new Random();
        $s1 = $random->string(4);
        $s2 = $random->string(4);
        $s3 = $random->string(4);

        $this->assertTrue(($s1 != $s2 && $s2 != $s3 && $s3 != $s1));
        $this->assertEquals(strlen($s1), 4);
        $this->assertEquals(strlen($s2), 4);
        $this->assertEquals(strlen($s3), 4);
        $this->assertTrue(is_string($s1));
        $this->assertTrue(is_string($s2));
        $this->assertTrue(is_string($s3));
    }

    /**
     * Test random string Generator
     */
    public function testStringChars()
    {
        $random = new Random();
        $chars = 'aGhretdPxZmlL046s1r';
        $s1 = $random->string(4, $chars);
        $s2 = $random->string(4, $chars);
        $s3 = $random->string(4, $chars);

        $this->assertTrue(($s1 != $s2 && $s2 != $s3 && $s3 != $s1));
        $this->assertEquals(strlen($s1), 4);
        $this->assertEquals(strlen($s2), 4);
        $this->assertEquals(strlen($s3), 4);
        $this->assertTrue(is_string($s1));
        $this->assertTrue(is_string($s2));
        $this->assertTrue(is_string($s3));
        $this->assertTrue((strpos('u', $s1) === false));
        $this->assertTrue((strpos('u', $s2) === false));
        $this->assertTrue((strpos('u', $s3) === false));
    }

    /**
     * Test random string Generator
     */
    public function testStringInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $random = new Random();
        $random->string();
    }
}
?>
