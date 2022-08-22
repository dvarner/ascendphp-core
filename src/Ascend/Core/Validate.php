<?php namespace Ascend\Core;

class Validate
{
    private bool $success = true;

    public function success(): bool
    {
        return $this->success;
    }

    public function date($field)
    {
        // $pattern = '@^([0-1]{1}[0-9]{1})/([0-3]{1}[0-9]{1})/([1-2]{1}[0-9]{1}[0-9]{2})$@';
        // preg_match($pattern, $put['date_of_birth'],$matches_birthday);
        $date_parts = explode('/', $field);
        if (count($date_parts) == 3) { //  && count($matches_birthday) === 4
            if (!checkdate($date_parts[0], $date_parts[1], $date_parts[2])) {
                $this->success = false;
            }
        } else {
            $this->success = false;
        }
    }

    public function email($email)
    {
        $this->success = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function passwordStrong($password)
    {
        $success = true;
        if (strlen($password) < 8) $success = false;
        if (!preg_match("#[0-9]+#", $password)) $success = false;
        if (!preg_match("#[a-zA-Z]+#", $password)) $success = false;
        $this->success = $success;
    }

    public function passwordMatch($password, $password_confirm)
    {
        $this->success = $password === $password_confirm;
    }

    public function username($username)
    {
        preg_match('@^[a-zA-Z]{1}[a-zA-Z0-9_]{7,30}$@', $username, $matches_user);
        $this->success = count($matches_user) == 1;
    }
}