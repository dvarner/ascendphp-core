<?php namespace Ascend\Core;


use Exception;

class Request
{
    public function get($variable_name = null)
    {
        if (is_null($variable_name)) {
            return $_GET;
        } else {
            try {
                return $_GET[$variable_name];
            } catch (Exception $e) {
                throw new Exception('Request GET variable(s) "' . $variable_name . '" does not exist: "' . $e->getMessage());
            }
        }
    }

    public function post($variable_name = null): array
    {
        if (is_null($variable_name)) {
            return $_POST;
        } else {
            try {
                return $_POST[$variable_name];
            } catch (Exception $e) {
                throw new Exception('Request POST variable(s) "' . $variable_name . '" does not exist: "' . $e->getMessage());
            }
        }
    }

    public function put(): array
    {
        return [];
    }

    public function delete(): array
    {
        return [];
    }

    public function request(): array
    {
        return $_REQUEST;
    }
}