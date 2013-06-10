<?php
/**
 * TestBenchmark.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestBenchmark extends PHPUnit_Framework_TestCase
{
    public function testTimer()
    {
        $b = new \Bolido\Benchmark();
        $b->startTimerTracker('hola');
        $this->assertTrue($b->stopTimerTracker('hola') <= time());
    }

    public function testTimer2()
    {
        $b = new \Bolido\Benchmark();
        $b->startTimerTracker('hola');
        $b->startTimerTracker('bola');
        $b->stopAllWatches();

        $this->assertCount(2, $b->results);
    }

    public function testRepeatTimer()
    {
        $this->setExpectedException('InvalidArgumentException');

        $b = new \Bolido\Benchmark();
        $b->startTimerTracker('hola');
        $b->startTimerTracker('hola');
    }

    public function testMemory()
    {
        $b = new \Bolido\Benchmark();
        $b->startMemoryTracker('hola');
        $this->assertTrue($b->stopMemoryTracker('hola') >= 0);
    }

    public function testMemory2()
    {
        $b = new \Bolido\Benchmark();
        $b->startMemoryTracker('hola');
        $b->startMemoryTracker('bola');
        $b->stopAllMemoryTrackers();

        $this->assertCount(2, $b->results);
    }

    public function testRepeatMemory()
    {
        $this->setExpectedException('InvalidArgumentException');

        $b = new \Bolido\Benchmark();
        $b->startMemoryTracker('hola');
        $b->startMemoryTracker('hola');
    }
}
?>
