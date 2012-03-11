<?php

$customHeader = 'HTTP_'.str_replace(" ", "_", strtoupper(str_replace("-", " ", %customHeader%)));

var_dump($_SERVER, $customHeader);

if ($_SERVER[$customHeader]) {
    $results = array();

    if (%user%) {
        $results[] = apc_clear_cache('user');
    }

    if (%opcode%) {
        $results[] = apc_clear_cache('opcode');
    }

    $protocol = $_SERVER['SERVER_PROTOCOL'];

    $uniqueResults = array_unique($results);

    if (count($uniqueResults) === 1 && $uniqueResults[0] === true) {
        header($protocol.' 200 OK');
    } else {
        header($protocol.' 500 Internal Server Error');
    }

    header('S2-APC-Cleanup: 1');
}
