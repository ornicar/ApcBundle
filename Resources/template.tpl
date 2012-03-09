<?php

$results = array();

if (%user%) {
    $results[] = apc_clear_cache('user');
}

if (%opcode%) {
    $results[] = apc_clear_cache('opcode');
}

$protocol = $_SERVER['SERVER_PROTOCOL'];

if (count(array_unique($results)) === 1) {
    header($protocol.' 200 OK');
} else {
    header($protocol.' 500 Internal Server Error');
}
