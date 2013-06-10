<?php
/**
 * TestRelativeTime.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\RelativeTime as RelativeTime;

class TestRelativeTime extends PHPUnit_Framework_TestCase
{
    public function setUp() { $this->lang = new MockLang(); }

    public function testRelativetimeObjectPast()
    {
        $relativeTime = new RelativeTime($this->lang);
        $this->assertEquals($relativeTime->calculate('2012-09-10', '2013-01-08'), 'relative_time_since_months_days_3_28');
        $this->assertEquals($relativeTime->calculate('04:00', '06:05'), 'relative_time_since_hours_minutes_2_5');
        $this->assertEquals($relativeTime->calculate('2013-01-09 04:00', '2013-01-9 06:05'), 'relative_time_since_hours_minutes_2_5');
        $this->assertEquals($relativeTime->calculate('01-09', '01-10'), 'relative_time_since_days_1');
        $this->assertEquals($relativeTime->calculate('01-09 12:40', '01-10 00:30'), 'relative_time_since_hours_minutes_11_50');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2013-09-17 10:00:08'), 'relative_time_since_seconds_3');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2013-09-17 10:10:08'), 'relative_time_since_minutes_seconds_10_3');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2013-09-17 11:00:08'), 'relative_time_since_hours_1');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2013-09-17 19:00:08'), 'relative_time_since_hours_9');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2014-09-17 19:00:08'), 'relative_time_since_years_1');
        $this->assertEquals($relativeTime->calculate(date('Y-m-d H:i:s')), 'relative_time_just_now');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:05', '2013-09-18 10:00:05'), 'relative_time_since_days_1');
    }

    public function testRelativetimeObjectFuture()
    {
        $relativeTime = new RelativeTime($this->lang);
        $this->assertEquals($relativeTime->calculate(date('Y-m-d H:i:s')), 'relative_time_just_now');
        $this->assertEquals($relativeTime->calculate('2013-01-08', '2012-09-10'), 'relative_time_until_months_days_3_29');
        $this->assertEquals($relativeTime->calculate('06:05', '04:00'), 'relative_time_until_hours_minutes_2_5');
        $this->assertEquals($relativeTime->calculate('2013-01-09 06:05', '2013-01-9 04:00'), 'relative_time_until_hours_minutes_2_5');
        $this->assertEquals($relativeTime->calculate('01-10', '01-09'), 'relative_time_until_days_1');
        $this->assertEquals($relativeTime->calculate('01-10 00:30', '01-09 12:40'), 'relative_time_until_hours_minutes_11_50');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:00:08', '2013-09-17 10:00:05'), 'relative_time_until_seconds_3');
        $this->assertEquals($relativeTime->calculate('2013-09-17 10:10:08', '2013-09-17 10:00:05'), 'relative_time_until_minutes_seconds_10_3');
        $this->assertEquals($relativeTime->calculate('2013-09-17 11:00:08', '2013-09-17 10:00:05'), 'relative_time_until_hours_1');
        $this->assertEquals($relativeTime->calculate('2013-09-17 19:00:08', '2013-09-17 10:00:05'), 'relative_time_until_hours_9');
        $this->assertEquals($relativeTime->calculate('2014-09-17 19:00:08', '2013-09-17 10:00:05'), 'relative_time_until_years_1');
    }

    public function testRelativeTimeBadFormats()
    {
        $this->setExpectedException('InvalidArgumentException');
        $relativeTime = new RelativeTime($this->lang);

        $relativeTime->calculate('bad format');
    }
}
?>
