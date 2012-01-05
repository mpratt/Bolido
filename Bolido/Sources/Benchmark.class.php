<?php
/**
 * Benchmark.class.php
 * This class is used to benchmark the framework
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
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Benchmark
{
    protected static $instance = null;
    protected $timers = array();
    protected $memoryTrackers = array();
    protected $results = array();

    /**
     * Singleton
     *
     * @return Instance of the class
     */
    public static function getInstance()
    {
        if (self::instance === null)
            self::instance = new self;

        return self::instance;
    }

    /**
     * Gets started Benchmarks
     *
     * @return array
     */
    public function getAllBenchmarks()
    {
        $this->stopAllMemoryTrackers();
        $this->stopAllWatches();
        return $this->results;
    }

    /**
     * Start Memory Tracker
     *
     * @param  string $name Unique memory tracker name
     * @return void
     */
    public function startMemoryTracker($name)
    {
        if (isset($this->memoryTrackers[$name]))
            throw new Exception('A memory tracker named ' . $name . ' already exists');

        $this->memoryTrackers[$name] = memory_get_usage();
    }

    /**
     * Starts a Timer Tracker
     *
     * @param  string $name Unique timer tracker name
     * @return void
     */
    public function startTimerTracker($name)
    {
        if (isset($this->timers[$name]))
            throw new Exception('A timer tracker named ' . $name . ' already exists');

        $this->timers[$name] = microtime(true);
    }

    /**
     * Stops a Timer Tracker
     *
     * @param  string $name Unique timer tracker name
     * @return int The time elapsed between the timer tracker $name and now
     */
    public function stopTimerTracker($name)
    {
        if (!isset($this->timers[$name]))
            throw new Exception('The timer tracker named ' . $name . ' doesnt exist');

        return $this->results['timer_' . $name] = microtime(true) - $this->timers[$name];
    }

    /**
     * Stops a Memory Tracker
     *
     * @param  string $name Unique memory tracker name
     * @return int The memory elapsed
     */
    public function stopMemoryTracker($name)
    {
        if (!isset($this->memoryTrackers[$name]))
            throw new Exception('The memory tracker named ' . $name . ' doesnt exist');

        return $this->results['memory_' . $name] = number_format(((memory_get_usage() - $this->memoryTrackers[$name])/1000), 2);
    }

    /**
     * Stops all timers
     *
     * @return void
     */
    public function stopAllWatches()
    {
        foreach($this->timers as $name => $time)
            $this->stopTimerTracker($name);
    }

    /**
     * Stops all Memory Trackers
     *
     * @return void
     */
    public function stopAllMemoryTrackers()
    {
        foreach($this->memoryTrackers as $name => $trackers)
            $this->stopMemoryTracker($name);
    }
}
?>