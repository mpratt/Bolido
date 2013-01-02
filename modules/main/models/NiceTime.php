<?php
/**
 * NiceTime.php
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

class NiceTime
{
    protected $lang;

    /**
     * Construct
     *
     * @param object $lang
     * @return void
     */
    public function __construct(\Bolido\Lang $lang) { $this->lang = $lang; }

    /**
     * Converts 2 dates to its relative time
     *
     * @param string $fromTime
     * @param string $currentTime
     * @param string $dateFormat Used when no suitable relative date was found
     * @return string
     */
    public function niceTime($fromTime, $currentTime = 0, $dateFormat = 'Y-m-d H:i:s')
    {
        if ($currentTime == 0)
            $currentTime = time();

        if (!ctype_digit($fromTime))
            $fromTime = strtotime($fromTime);

        if (!ctype_digit($currentTime))
            $currentTime = strtotime($currentTime);

        $difference = ($currentTime - $fromTime);
        $dayDifference = floor(abs($difference)/86400);

        if ($difference == 0)
            return $this->lang->get('common_time_since_now');

        if ($difference > 0)
        {
            if ($dayDifference == 0)
            {
                if ($difference < 60)
                    return $this->lang->get('common_time_since_just_now');
                else if ($difference < 120)
                    return $this->lang->get('common_time_since_minute', 1);
                else if ($difference < 3600)
                    return $this->lang->get('common_time_since_minutes', floor($difference/60));
                else if ($difference < 7200)
                    return $this->lang->get('common_time_since_hour', 1);
                else if ($difference < 86400)
                    return $this->lang->get('common_time_since_hours', floor($difference/3600));
            }

            if ($dayDifference == 1)
                return $this->lang->get('common_time_since_yesterday');
            else if ($dayDifference < 7)
                return $this->lang->get('common_time_since_days', $dayDifference);
            else if ($dayDifference < 31)
                return $this->lang->get('common_time_since_weeks', ceil($dayDifference/7));
            else if ($dayDifference < 60)
                return $this->lang->get('common_time_since_last_month', 1);
        }
        else
        {
            if ($dayDifference == 0)
            {
                if ($difference < 120)
                    return $this->lang->get('common_time_until_minute', 1);
                else if ($difference < 3600)
                    return $this->lang->get('common_time_until_minutes', floor($difference/60));
                else if ($difference < 7200)
                    return $this->lang->get('common_time_until_hour', 1);
                else if ($difference < 86400)
                    return $this->lang->get('common_time_until_hours', floor($difference/3600));
            }

            if ($dayDifference == 1)
                return $this->lang->get('common_time_until_tomorrow');
            else if ($dayDifference < 7)
                return $this->lang->get('common_day_' . strtolower(date('l', $fromTime)));
            else if (date('n', $fromTime) == (date('n') + 1))
                return $this->lang->get('common_time_until_next_month');
        }

        return date($dateFormat, $fromTime);
    }
}
?>
