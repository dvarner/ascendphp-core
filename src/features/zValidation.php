<?php namespace Ascend;
/*
class Validation
{
    public function getRequest($var)
    {

        if (isset($_REQUEST[$var])) {
            if (is_string($_REQUEST[$var])) {
                return trim($_REQUEST[$var]);
            } else {
                return $_REQUEST[$var];
            }
        } else {
            return false;
        }
    }

    public function getAllRequest(){
        return $_REQUEST;
    }

    public function getPost($var)
    {
        if (isset($_POST[$var])) {
            if (is_string($_POST[$var])) {
                return trim($_POST[$var]);
            } else {
                return $_POST[$var];
            }
        } else {
            return false;
        }
    }

    public function checkError($arr)
    {
        if (
            isset($arr) && is_array($arr) && count($arr) > 0
            && isset($arr['error']) && is_array($arr['error']) && count($arr['error']) > 0
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function valid($required_arr)
    {
        $this->result = array();
        // *** Loops through required fields
        foreach ($required_arr AS $field => $validate_arr) {
            $this->field = $field;
            $this->result['debug'][] = 'Field: ' . $field;
            // $this->result['debug'][] = $validate_arr;
            // *** Checks to see if variable exists
            if ($this->getRequest($field)) {
                // *** Set variable found to value
                $value = $this->getRequest($field);
                $this->result['debug'][] = 'REQUEST set: ' . $field . ' = ' . $value;
                // *** loop through validations
                if (isset($validate_arr) && is_array($validate_arr) && count($validate_arr) > 0) {
                    foreach ($validate_arr AS $validate => $specific) {
                        $this->result['debug'][] = 'validations: ' . $validate . ' | ' . $specific;
                        // *** if key was not set meaning no specifics to the requirement then make value validate to key which is the req field
                        if (is_numeric($validate)) {
                            $validate = $specific;
                        }
                        // *** go through matching each type of validation and return results (true/faluse)
                        // if($validate == 'int'){}  
                        // if($validate == 'tinyint'){}  
                        // if($validate == 'bigint'){}  
                        // if($validate == 'char'){}  
                        // if($validate == 'text'){}  

                        if ($validate == 'accepted') {
                            $this->formatAccepted($value);
                        }
                        // if($validate == 'alpha'){       $this->formatAlpha($value); }
                        if ($validate == 'confirm') {
                            $this->formatConfirm($value, $specific);
                        }
                        if ($validate == 'email') {
                            $this->formatEmail($value);
                        }
                        // if($validate == 'exists'){      $this->formatExists($field, $value, $specific); }
                        // if($validate == 'image'){       $this->formatImage($value); }
                        // if($validate == 'max'){         $this->formatMax($value); }
                        // if($validate == 'min'){         $this->formatMin($value); }
                        // if($validate == 'not'){} // Not name as field listed
                        if ($validate == 'numeric') {
                            $this->formatNumeric($value);
                        }
                        // *** @todo do format check on security of password. Let this be set in config.
                        if ($validate == 'password') {
                            $this->formatPassword($value);
                        }
                        if (false !== strpos($validate, 'regex:')) {
                            $this->formatRegex($value, $validate);
                        }
                        // *** "required" is in else part of statement
                        if ($validate == 'unique') {
                            $this->formatUnique($value, $specific);
                        }
                        /*if ($validate == 'uniquei'){
                            $this->formatUniqueIncase($value, $specific);
                        }* /
                        if ($validate == 'url') {
                            $this->formatUrl($value);
                        }
                        if ($validate == 'username') {
                            $this->formatUsername($value);
                        }
                        unset($validate, $specific);
                    }
                }
            } else {
                // $this->result['error'][] = 'Variable "'.$field.'" not passed';
                // *** Required Check

                if (false !== array_search('required', $validate_arr)) {
                    $this->result['error'][] = 'Field "' . $field . '" is required';
                }
            }
            // *** if false found then break. no reason to continue
            // if($result === false){ break; }* /

            unset($field, $validate_arr);
        }
        return $this->result;
    }

    /**
     * Accepted must be yes, on, or 1. Great for TOS.
     * /
    public function formatAccepted($value)
    {
        if ($value == 'yes' || $value == 'on' || $value == 1) {
            $this->result['success'][] = 'Accepted Success';
        } else {
            $this->result['error'][] = 'Accepted Failed';
        }
    }

    /**
     * Only Letters
     * /
    public function formatAlpha($value)
    {

    }

    /**
     * Checks for [field]_confirm to match [field]
     * /
    public function formatConfirm($value, $specific)
    {
        if ($this->getRequest($specific) == $value) {
            $this->result['success'][] = 'Fields match "' . $this->field . '"';
        } else {
            $this->result['error'][] = 'Field "' . $this->field . '" does not match "' . $specific . '"';
        }
    }

    /**
     * Valid Email
     * /
    public function formatEmail($value)
    {
        preg_match('/[a-zA-Z0-9-_]{1,50}\@[a-zA-Z0-9-_]{1,50}\.[a-zA-Z\.]{1,20}/', $value, $matches);
        if (isset($matches[0])) {
            $this->result['success'][] = 'Email Success';
        } else {
            $this->result['error'][] = 'Email Failed';
        }
    }

    /*
     * Validates existance of field value in table
     * /

    public function formatExist($field, $value, $arr)
    {
        /* $result = $this->_class['db']->select($arr[0],$arr[1])->first();
          if(count($result) > 0){
          $this->result['success'][] = 'Successful "'.$value.'" in "'.$arr[0].'.'.$arr[1].'"';
          }else{
          $this->result['error'][] = 'Value "'.$value.'" not found in "'.$arr[0].'.'.$arr[1].'"';
          } */
    }

    /**
     * Validates it is an image
     * /
    public function formatImage($value)
    {

    }

    /**
     * Max # of characters
     * /
    public function formatMax($value)
    {

    }

    /*
     * Min # of charaters
     * /
    public function formatMin($value)
    {

    }

    /**
     * Only Numbers
     * /
    public function formatNumeric($value)
    {
        if (is_numeric($value)) {
            $this->result['success'][] = 'Is Numeric';
        } else {
            $this->result['error'][] = 'Is NOT Numeric';
        }
    }

    public function formatPassword($value, $json = true)
    {
        if ($value == '') {
            $this->result['error'][] = 'Blank Password';
        } else {
            $e = true;
            if (strlen($value) < 8) {
                $e = false;
            }
            // -- Atleast one number -- //
            if (!preg_match("#[0-9]+#", $value)) {
                $e = false;
            }
            // -- Atlease one letter -- //
            if (!preg_match("#[a-zA-Z]+#", $value)) {
                $e = false;
            }
            // -- atleast one caps -- //
            // if( !preg_match("#[A-Z]+#", $pwd) ) { $e = false; }
            // -- atleast one symbol -- //
            // if( !preg_match("#\W+#", $pwd) ) { $e = false; }
            if ($json === true) {
                if ($e === true) {
                    $this->result['success'][] = 'Password Good';
                } else {
                    $this->result['error'][] = 'Password needs to be stronger.' .
                        ' Requires numbers, letters and at least 8 charaters.';
                }
            } else {
                return $e;
            }
        }
    }

    /**
     * Regular Expression Check
     * /
    public function formatRegex($value, $field)
    {
        $count = strlen('regex:');
        $regex = substr($field, $count);
        preg_match('/' . $regex . '/', $value, $matches);
        if (isset($matches[0])) {
            $this->result['success'][] = 'Matched';
        } else {
            $this->result['error'][] = 'Did NOT Match';
        }
    }

    /**
     * Required Field
     * /
    public function formatRequired($value)
    {

    }

    /**
     * Unique table,column
     * /
    public function formatUnique($value, $table)
    {
        $result = BS::getController('Database')
            ->select($table, 'id')
            ->where($table, $this->field, '=', $value)
            ->first();
        // $this->result['debug'][] = 'formatUnique: ';
        // $this->result['debug'][] = print_r($result,true);
        if (is_array($result) && count($result) == 1) {
            $this->result['error'][] = 'Unique "' . $this->field . '" failed';
            // $this->result['error'][] = $this->_class['db']->db->lastQuery();
        } else {
            $this->result['success'][] = 'Field "' . $this->field . '" is unique';
        }
    }

    /*public function formatUniqueIncase($value, $table){
        $result = BS::getClass('Database')
                ->select($table, 'id')
                ->where($table, $this->field, 'like', $value)
                ->first();
        // $this->result['debug'][] = 'formatUnique: ';
        // $this->result['debug'][] = print_r($result,true);
        if (is_array($result) && count($result) == 1) {
            $this->result['error'][] = 'Unique "' . $this->field . '" failed';
            // $this->result['error'][] = $this->_class['db']->db->lastQuery();
        } else {
            $this->result['success'][] = 'Field "' . $this->field . '" is unique';
        }
    }* /

    public function formatUrl($value)
    {
        preg_match("/https?:\/\//", $value, $matches);
        if (isset($matches[0])) {
            $this->result['success'][] = 'Url Successful';
        } else {
            $this->result['error'][] = 'Url Bad';
        }
    }

    public function formatUsername($value)
    {
        preg_match("/[a-zA-Z]{1}[a-zA-Z0-9]{1,29}/", $value, $matches);
        if (isset($matches[0])) {
            $this->result['success'][] = 'Username Successful';
        } else {
            $this->result['error'][] = 'Username must start with letter and only' .
                'letter, numbers, underscores, and dashes allowed.';
        }
    }

}
*/