<?php namespace Ascend\Core;

class Validate
{
    public static function post($variable)
    {
        // @todo 20190712 do some sanitation on this later
        return $_POST[$variable];
    }

    public static function displayError($html)
    {
        $error = Session::getErrorString();
        if ($error != '') {
            return str_replace('{{error}}', $error, $html);
        } else {
            return '';
        }
    }

    public static function displaySuccess($html)
    {
        $success = Session::getSuccessString();
        if ($success != '') {
            return str_replace('{{success}}', $success, $html);
        } else {
            return '';
        }
    }

    public static function date($field) {
        // $pattern = '@^([0-1]{1}[0-9]{1})/([0-3]{1}[0-9]{1})/([1-2]{1}[0-9]{1}[0-9]{2})$@';
        // preg_match($pattern, $put['date_of_birth'],$matches_birthday);
        $date_parts  = explode('/', $field);
        if (count($date_parts) == 3) { //  && count($matches_birthday) === 4
            if (checkdate($date_parts[0], $date_parts[1], $date_parts[2])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    public static function passwordStrong($password) {
        $success = true;
        if (strlen($password) < 8) $success = false;
        if (!preg_match("#[0-9]+#", $password)) $success = false;
        if (!preg_match("#[a-zA-Z]+#", $password)) $success = false;
        return $success;
    }

    public static function username($username) {
        preg_match('@^[a-zA-Z]{1}[a-zA-Z0-9_]{7,30}$@',$username,$matches_user);
        return count($matches_user) == 1;
    }
}