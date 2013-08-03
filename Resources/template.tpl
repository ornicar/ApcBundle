<?php

$message = 'Clear APC';
$success = true;

$opcache_loaded = extension_loaded("Zend OPcache");

if(%user%) {

    if ($opcache_loaded) {
        $user_cache_cleared = apc_clear_cache();
    } else {
        // assuming apcu has been installed
        $user_cache_cleared = apc_clear_cache('user');
    }

    if ($user_cache_cleared) {
        $message .= ' User Cache: success';
    }
    else {
        $success = false;
        $message .= ' User Cache: failure';
    }
}

if(%opcode%) {

    if ($opcache_loaded) {
        $opcode_cache_cleared = opcache_reset();
    } else {
        // assuming apcu has been installed
        $opcode_cache_cleared = apc_clear_cache('opcode');
    }

    if ($opcode_cache_cleared) {
        $message .= ' Opcode Cache: success';
    }
    else {
        $success = false;
        $message .= ' Opcode Cache: failure';
    }
}

die(json_encode(array('success' => $success, 'message' => $message)));
