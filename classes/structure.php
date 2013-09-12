<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace calendartype_japanese;
use core_calendar\type_base;

/**
 * Handles calendar functions for the japanese calendar.
 *
 * @package calendartype_japanese
 * @copyright 2013 Adrian Greeve <adrian@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class structure extends type_base {

    /**
     * Returns a list of all the possible days for all months.
     *
     * This is used to generate the select box for the days
     * in the date selector elements. Some months contain more days
     * than others so this function should return all possible days as
     * we can not predict what month will be chosen (the user
     * may have JS turned off and we need to support this situation in
     * Moodle).
     *
     * @return array the days
     */
    protected function get_days() {
        $days = array();

        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }

        return $days;
    }

    /**
     * Returns a list of all the names of the months.
     *
     * @return array the month names
     */
    protected function get_months() {
        $months = array();

        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = get_string('month', 'calendartype_japanese', $i);
        }

        return $months;
    }

    /**
     * Returns a list of all of the years being used.
     * Years available 1900 - 2050 (I suspect that the unix timestamp does not extend to these dates.
     *
     * @return array the years.
     */
    public function get_years() {
        $years = array();

        $yearvalue = 1900;
        for ($i = 32; $i <= 43; $i++) {
            $years[$yearvalue] = get_string('meiji', 'calendartype_japanese', $i);
            $yearvalue++;
        }
        $a = new \stdClass();
        $a->old = 44;
        $a->new = 1;
        $years[$yearvalue] = get_string('meijihandover', 'calendartype_japanese', $a);
        $yearvalue++;

        for ($i = 2; $i <= 14; $i++) {
            $years[$yearvalue] = get_string('taishou', 'calendartype_japanese', $i);
            $yearvalue++;
        }
        $a = new \stdClass();
        $a->old = 15;
        $a->new = 1;
        $years[$yearvalue] = get_string('taishouhandover', 'calendartype_japanese', $a);
        $yearvalue++;

        for ($i = 2; $i <= 63; $i++) {
            $years[$yearvalue] = get_string('shouwa', 'calendartype_japanese', $i);
            $yearvalue++;
        }
        $a = new \stdClass();
        $a->old = 64;
        $a->new = 1;
        $years[$yearvalue] = get_string('shouwahandover', 'calendartype_japanese', $a);
        $yearvalue++;

        for ($i = 2; $i <= 62; $i++) {
            $years[$yearvalue] = get_string('heisei', 'calendartype_japanese', $i);
            $yearvalue++;
        }

        // Reduce years from set minimum year.
        for ($i = 0; $i < $this->minyear; $i++) {
            unset($years[$i]);
        }

        // Reduce years from set maximum year.
        for ($i = 2050; $i > $this->maxyear; $i--) {
            unset($years[$i]);
        }

        return $years;
    }

    /**
     * Returns a multidimensional array with information for day, month, year
     * and the order they are displayed when selecting a date.
     * The order in the array will be the order displayed when selecting a date.
     * Override this function to change the date selector order.
     *
     * @param int $minyear The year to start with.
     * @param int $maxyear The year to finish with.
     * @return array Full date information.
     */
    public function date_order($minyear = 0, $maxyear = 0) {

        if (!empty($minyear)) {
            $this->minyear = $minyear;
        }
        if (!empty($maxyear)) {
            $this->maxyear = $maxyear;
        }
        $dateinfo = array();
        $dateinfo['year'] = $this->get_years();
        $dateinfo['month'] = $this->get_months();
        $dateinfo['day'] = $this->get_days();

        return $dateinfo;
    }

    /**
     * Returns a formatted string that represents a date in user time.
     *
     * Returns a formatted string that represents a date in user time
     * <b>WARNING: note that the format is for strftime(), not date().</b>
     * Because of a bug in most Windows time libraries, we can't use
     * the nicer %e, so we have to use %d which has leading zeroes.
     * A lot of the fuss in the function is just getting rid of these leading
     * zeroes as efficiently as possible.
     *
     * If parameter fixday = true (default), then take off leading
     * zero from %d, else maintain it.
     *
     * @param int $date the timestamp in UTC, as obtained from the database
     * @param string $format strftime format
     * @param int|float|string $timezone the timezone to use
     *        {@link http://docs.moodle.org/dev/Time_API#Timezone}
     * @param bool $fixday if true then the leading zero from %d is removed,
     *        if false then the leading zero is maintained
     * @param bool $fixhour if true then the leading zero from %I is removed,
     *        if false then the leading zero is maintained
     * @return string the formatted date/time
     */
    public function timestamp_to_date_string($date, $format, $timezone, $fixday, $fixhour) {
        global $CFG;

        if (empty($format)) {
            $format = get_string('strftimedaydatetime', 'langconfig');
        }

        if (!empty($CFG->nofixday)) { // Config.php can force %d not to be fixed.
            $fixday = false;
        } else if ($fixday) {
            $formatnoday = str_replace('%d', 'DD', $format);
            $fixday = ($formatnoday != $format);
            $format = $formatnoday;
        }

        // Note: This logic about fixing 12-hour time to remove unnecessary leading
        // zero is required because on Windows, PHP strftime function does not
        // support the correct 'hour without leading zero' parameter (%l).
        if (!empty($CFG->nofixhour)) {
            // Config.php can force %I not to be fixed.
            $fixhour = false;
        } else if ($fixhour) {
            $formatnohour = str_replace('%I', 'HH', $format);
            $fixhour = ($formatnohour != $format);
            $format = $formatnohour;
        }

        // Add daylight saving offset for string timezones only, as we can't get dst for
        // float values. if timezone is 99 (user default timezone), then try update dst.
        if ((99 == $timezone) || !is_numeric($timezone)) {
            $date += dst_offset_on($date, $timezone);
        }

        $timezone = get_user_timezone_offset($timezone);

        // If we are running under Windows convert to windows encoding and then back to UTF-8
        // (because it's impossible to specify UTF-8 to fetch locale info in Win32).
        if (abs($timezone) > 13) { // Server time.
            $datestring = date_format_string($date, $format, $timezone);
            if ($fixday) {
                $daystring  = ltrim(str_replace(array(' 0', ' '), '', strftime(' %d', $date)));
                $datestring = str_replace('DD', $daystring, $datestring);
            }
            if ($fixhour) {
                $hourstring = ltrim(str_replace(array(' 0', ' '), '', strftime(' %I', $date)));
                $datestring = str_replace('HH', $hourstring, $datestring);
            }
        } else {
            $date += (int)($timezone * 3600);
            $datestring = date_format_string($date, $format, $timezone);
            if ($fixday) {
                $daystring  = ltrim(str_replace(array(' 0', ' '), '', gmstrftime(' %d', $date)));
                $datestring = str_replace('DD', $daystring, $datestring);
            }
            if ($fixhour) {
                $hourstring = ltrim(str_replace(array(' 0', ' '), '', gmstrftime(' %I', $date)));
                $datestring = str_replace('HH', $hourstring, $datestring);
            }
        }

        // Change the year if the format contains a year.
        if (preg_match('/\%Y年/', $format)) {
            $year = date_format_string($date, '%Y', $timezone);
            $jyears = self::get_years();
            $datestring = preg_replace('/\d{4}年/', $jyears[$year], $datestring);
        }

        return $datestring;
    }

    /**
     * Given a $time timestamp in GMT (seconds since epoch), returns an array that
     * represents the date in user time.
     *
     * @param int $time Timestamp in GMT
     * @param float|int|string $timezone offset's time with timezone, if float and not 99, then no
     *        dst offset is applied {@link http://docs.moodle.org/dev/Time_API#Timezone}
     * @return array an array that represents the date in user time
     */
    public function timestamp_to_date_array($time, $timezone) {
        return usergetdate($time, $timezone);
    }

    /**
     * Provided with a day, month, year, hour and minute in a specific
     * calendar type convert it into the equivalent Gregorian date.
     *
     * In this function we don't need to do anything except pass the data
     * back as an array. This is because the date received is Gregorian.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @return array the converted day, month, year, hour and minute.
     */
    public function convert_from_gregorian($year, $month, $day, $hour = 0, $minute = 0) {
        $date = array();
        $date['year'] = $year;
        $date['month'] = $month;
        $date['day'] = $day;
        $date['hour'] = $hour;
        $date['minute'] = $minute;

        return $date;
    }

    /**
     * Provided with a day, month, year, hour and minute in a specific
     * calendar type convert it into the equivalent Gregorian date.
     *
     * In this function we don't need to do anything except pass the data
     * back as an array. This is because the date received is Gregorian.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @return array the converted day, month, year, hour and minute.
     */
    public function convert_to_gregorian($year, $month, $day, $hour = 0, $minute = 0) {
        $date = array();
        $date['year'] = $year;
        $date['month'] = $month;
        $date['day'] = $day;
        $date['hour'] = $hour;
        $date['minute'] = $minute;

        return $date;
    }
}
