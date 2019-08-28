<?php namespace Ascend\Core;

class TimeZone
{
    public static function set($tz) {
        date_default_timezone_set($tz);
    }
    // $tz = user timezone
    public static function showDateTime($time, $tz)
    {
        $timezone = new \DateTimeZone($tz); // America/New_York
        if ($time == null) {
            $date = new \DateTime();
        } else {
            $date = new \DateTime($time);
        }
        $date->setTimeZone($timezone);
        return $date->format('M d, Y') . ' at ' . $date->format('h:i:s A');
        // return date('Y-m-d H:i:s', $timestamp);
    }

    // $tz = user timezone
    public static function showDate($time, $tz)
    {
        $timezone = new \DateTimeZone($tz); // America/New_York
        if ($time == null) {
            $date = new \DateTime();
        } else {
            $date = new \DateTime($time);
        }
        $date->setTimeZone($timezone);
        return $date->format('M d, Y');
    }

    public static function dateFormatDB($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}