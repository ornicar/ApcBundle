<?php
$message = '';
$success = true;

if(%user%) {
    if (function_exists('wincache_ucache_clear') && wincache_ucache_clear()) {
        $message .= 'Wincache User Cache: success.';
    } elseif (function_exists('apcu_clear_cache') && apcu_clear_cache()) {
        $message .= 'APC User Cache: success.';
    } elseif (function_exists('apc_clear_cache') && function_exists('opcache_reset') && apc_clear_cache()) {
        $message .= 'APC User Cache: success.';
    } elseif (function_exists('apc_clear_cache') && apc_clear_cache('user')) {
        $message .= 'APC User Cache: success.';
    } elseif (function_exists('xcache_clear_cache')) {
            $cnt = xcache_count(XC_TYPE_VAR);
            for ($i=0; $i < $cnt; $i++) {
                xcache_clear_cache(XC_TYPE_VAR, $i);
            }
        $message .= 'XCache User Cache: success.';
    } else {
        $success = false;
        $message .= 'User Cache: failure.';
    }
}

if(%opcode%) {
    if (function_exists('opcache_reset') && opcache_reset()) {
        $message .= ' Zend OPcache: success.';
    } elseif (function_exists('apc_clear_cache') && apc_clear_cache('opcode')) {
        $message .= ' APC Opcode Cache: success.';
    } elseif (function_exists('xcache_clear_cache')) {
        $cnt = xcache_count(XC_TYPE_PHP);
        for ($i=0; $i < $cnt; $i++) {
            xcache_clear_cache(XC_TYPE_PHP, $i);
        }
        $message .= ' XCache Opcode Cache: success.';
    } else {
        $success = false;
        $message .= ' Opcode Cache: failure.';
    }
}

die(json_encode(array('success' => $success, 'message' => $message)));
