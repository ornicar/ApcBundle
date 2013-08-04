<?php

$message = 'Clear APC';
$success = true;

if (%clear_user_cache%) {
    if (apc_clear_cache('user')) {
        $message .= ' User Cache: success';
    } else {
        $success = false;
        $message .= ' User Cache: failure';
    }
}

if (%clear_opcode_cache%) {
    if (apc_clear_cache('opcode')) {
        $message .= ' Opcode Cache: success';
    } else {
        $success = false;
        $message .= ' Opcode Cache: failure';
    }
}

die(json_encode(array('success' => $success, 'message' => $message)));
