<?php
/**
 * RelativeTime.php
 * This class is used to calculate relative times.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class RelativeTime
{
    protected $lang, $currentTime, $fromTime, $interval;

    /**
     * Construct
     *
     * @param object $lang
     * @return void
     */
    public function __construct(\Bolido\Lang $lang)
    {
        $this->lang = $lang;
        $this->lang->load('main/relativeTime');
    }

    /**
     * Converts 2 dates to its relative time.
     *
     * @param string $fromTime
     * @param string $currentTime When null is given, use the current date.
     * @return string
     */
    public function calculate($fromTime, $currentTime = null)
    {
        $this->fromTime    = new \DateTime($this->formatFinder($fromTime));
        $this->currentTime = new \DateTime($this->formatFinder($currentTime));
        $this->interval    = $this->currentTime->diff($this->fromTime);
        $units = array('seconds' => (int) $this->interval->format('%s'),
                       'minutes' => (int) $this->interval->format('%i'),
                       'hours'   => (int) $this->interval->format('%h'),
                       'days'    => (int) $this->interval->format('%d'),
                       'months'  => (int) $this->interval->format('%m'),
                       'years'   => (int) $this->interval->format('%Y'));

        $units = $this->filterOrder($units);
        if (empty($units))
            return $this->lang->get('relative_time_just_now');

        if (intval($this->interval->format('%r1')) <= 0)
            return $this->translateTime($units, 'relative_time_since_');

        return $this->translateTime($units, 'relative_time_until_');
    }

    /**
     * Tries to guess common formats given and completes
     * the whole string to have more accurate date calculations.
     *
     * @param string $date When null is given, use the current time/date.
     * @return formated string
     */
    protected function formatFinder($date = null)
    {
        if (is_null($date))
            return date('Y-m-d H:i:s');
        else if (ctype_digit($date))
            return date('Y-m-d H:i:s', $date);

        $date = str_replace('/', '-', trim($date));
        $formats = array('(\d{4})-(\d{1,2})-(\d{1,2})' => '{holder} 00:01:01', // Formats like Y-m-d like 2013-01-08
                         '(\d{4})-(\d{1,2})'   => '{holder}-01 00:01:01', // Formats like Y-m like 2013-04
                         '(\d{1,2})-(\d{1,2})' => 'Y-{holder} 00:01:01', // Formats like d-m like 12-05
                         '(\d{1,2}):(\d{2})'   => 'Y-m-d {holder}:s', // Formats like H:i Like 16:04
                         '(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})' => '{holder}:01', // Formats like Y-m-d H:1 like 2013-01-08 16:04
                         '(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})' => '{holder}', // Formats like Y-m-d H:i:s like 2013-01-08 16:04:03
                         '(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})' => 'Y-{holder}:01', // Formats like m-d H:1 like 01-08 16:04
                         '(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})' => 'Y-{holder}', // Formats like m-d H:1:s like 2013-01-08 16:04:04
                         '(\d{1,2}):(\d{2}):(\d{2})' => 'Y-m-d {holder}'); // Formats H:i:s like 16:04:01

        foreach($formats as $regex => $format)
        {
            if (preg_match('~^' . $regex . '$~', $date))
                return date(str_replace('{holder}', $date, $format));
        }

        throw new \InvalidArgumentException('Could not understand the date ' . $date);
    }

    /**
     * Verifies that the units (seconds, minutes, hours, etc)
     * are given are in correct order.
     *
     * @param array $units
     * @return array
     */
    protected function filterOrder(array $units)
    {
        $units = array_filter($units);
        if (empty($units))
            return array();

        $order = array('seconds', 'minutes', 'hours', 'days', 'months', 'years');
        $items = array_keys($units);

        if (array_slice($order, array_search($items['0'], $order), count($units)) === $items)
            return array_reverse($units);

        // return the last element when the order is invalid.
        return array_slice($units, -1, 1);
    }

    /**
     * Actually translates the dates based on the units and
     * prefix.
     *
     * @param array $units
     * @param string $prefix of the language string
     * @return string
     */
    protected function translateTime(array $units, $prefix = '')
    {
        $prefix .= implode('_', array_keys($units));
        return call_user_func_array(array($this->lang, 'get'), array_merge(array($prefix), $units));
    }
}
?>
