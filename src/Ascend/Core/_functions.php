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