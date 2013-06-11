<?php
/**
 * Benchmark.php
 * This class is used to benchmark the framework
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Benchmark
{
    protected $timers = array();
    protected $memoryTrackers = array();
    public $results = array();

    /**
     * Start Memory Tracker
     *
     * @param  string $name Unique memory tracker name
     * @return void
     *
     * @throws InvalidArgumentException when a tracker doesnt exist
     */
    public function startMemoryTracker($name)
    {
        if (isset($this->memoryTrackers[$name]))
            throw new \InvalidArgumentException('A memory tracker named ' . $name . ' already exists');

        $this->memoryTrackers[$name] = memory_get_usage();
    }

    /**
     * Starts a Timer Tracker
     *
     * @param  string $name Unique timer tracker name
     * @return void
     *
     * @throws InvalidArgumentException when a tracker doesnt exist
     */
    public function startTimerTracker($name)
    {
        if (isset($this->timers[$name]))
            throw new \InvalidArgumentException('A timer tracker named ' . $name . ' already exists');

        $this->timers[$name] = microtime(true);
    }

    /**
     * Stops a Timer Tracker
     *
     * @param  string $name Unique timer tracker name
     * @return int The time elapsed between the timer tracker $name and now
     *
     * @throws InvalidArgumentException when a tracker doesnt exist
     */
    public function stopTimerTracker($name)
    {
        if (!isset($this->timers[$name]))
            throw new \InvalidArgumentException('The timer tracker named ' . $name . ' doesnt exist');

        return $this->results['timer_' . $name] = microtime(true) - $this->timers[$name];
    }

    /**
     * Stops a Memory Tracker
     *
     * @param  string $name Unique memory tracker name
     * @return int The memory elapsed
     *
     * @throws InvalidArgumentException when a tracker doesnt exist
     */
    public function stopMemoryTracker($name)
    {
        if (!isset($this->memoryTrackers[$name]))
            throw new \InvalidArgumentException('The memory tracker named ' . $name . ' doesnt exist');

        return $this->results['memory_' . $name] = number_format(((memory_get_usage() - $this->memoryTrackers[$name])/1024), 2);
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
