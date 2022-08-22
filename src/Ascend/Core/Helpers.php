<?php

function eo($message, $value = null)
{
    if (is_null($value)) {
        echo $message . '<br />' . PHP_EOL;
    } else {
        echo $message . '=' . $value . '<br />' . PHP_EOL;
    }
//    $a = func_get_args();
//    foreach ($a AS $v) {
//        echo $v.'<br />'.PHP_EOL;
//    }
//    echo '======<br />'.PHP_EOL;
}

function vd()
{
    echo '<pre style="background: #CCC;">';
    $i = 1;
    $where = debug_backtrace()[$i]['function'] . '()';

    $line = debug_backtrace()[$i]['line'] ?? null;
    if (!is_null($line)) {
        $where .= ' @ line ' . $line;
    }

    $where .= ' | ' . debug_backtrace()[$i]['file'];

    eo('====', $where);
    var_dump(func_get_args());
    echo '</pre>';
}

function dd()
{
    echo '<pre>';
    var_dump(func_get_args());
    echo '</pre>';
    exit;
}
