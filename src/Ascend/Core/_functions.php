<?php namespace Ascend\Core;

function file2code_example($file) {
    if (file_exists($file)) {
        // PATH_MODELS . 'Example.php'
        $content = file_get_contents($file);
        $content_cleaned = htmlentities($content);
        $per_line = explode(RET,$content_cleaned);
        $code_display = '';
        foreach ($per_line AS $line) {
            $code_display .= '<span>' . $line . '</span>';
        }
        return $code_display;
    } else {
        return 'File not found!';
    }
}

function currency($n) {
    return number_format($n, 0,'.',',');
}

function varDumpToString($arr) {
    ob_start();
    var_dump($arr);
    return ob_get_clean();
}

function htmlMailer($to = [], $subject, $html_body) {
    $from_name = DOMAIN;
    $from_email = DEFAULT_EMAIL;
    $headers  = "From: {$from_email} >\n"; //{$from_name} <
    // $headers .= "Cc: testsite < mail@testsite.com >\n";
    // $headers .= "X-Sender: testsite < mail@testsite.com >\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    // $headers .= "X-Priority: 1\n"; // Urgent message!
    // $headers .= "Return-Path: {$from_email}\n"; // Return path for errors
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=iso-8859-1\n";
    $to_string = implode(',',$to);
    return mail($to_string, $subject, $html_body, $headers);
}

/**
 * decrypt AES 256
 *
 * @param data $edata
 * @param string $password
 * @return decrypted data
 */
function decrypt($edata, $password) {
    $data = base64_decode($edata);
    $salt = substr($data, 0, 16);
    $ct = substr($data, 16);

    $rounds = 3; // depends on key length
    $data00 = $password.$salt;
    $hash = array();
    $hash[0] = hash('sha256', $data00, true);
    $result = $hash[0];
    for ($i = 1; $i < $rounds; $i++) {
        $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
        $result .= $hash[$i];
    }
    $key = substr($result, 0, 32);
    $iv  = substr($result, 32,16);

    return openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);
}

/**
 * crypt AES 256
 *
 * @param data $data
 * @param string $password
 * @return base64 encrypted data
 */
function encrypt($data, $password) {
    // Set a random salt
    $salt = openssl_random_pseudo_bytes(16);

    $salted = '';
    $dx = '';
    // Salt the key(32) and iv(16) = 48
    while (strlen($salted) < 48) {
        $dx = hash('sha256', $dx.$password.$salt, true);
        $salted .= $dx;
    }

    $key = substr($salted, 0, 32);
    $iv  = substr($salted, 32,16);

    $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
    return base64_encode($salt . $encrypted_data);
}