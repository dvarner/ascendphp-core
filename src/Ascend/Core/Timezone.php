<?php namespace Ascend\Core;

class Timezone
{
    public static function databaseDateFormat($timestamp = null)
    {
        $timestamp = (is_null($timestamp) ? time() : $timestamp);
        $timestamp = (!is_numeric($timestamp) ? time() : $timestamp);
        return date('Y-m-d H:i:s', $timestamp);
    }
}