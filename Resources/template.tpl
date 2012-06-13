<?php
$message = 'Clear APC';
$success = true;

if(%user%) {
    if (apc_clear_cache('user')) {
        $message .= ' User Cache: success';
    }
    else {
        $success = false;
        $message .= ' User Cache: failure';
    }
}

if(%opcode%) {
    if (apc_clear_cache('opcode')) {
        $message .= ' Opcode Cache: success';
    }
    else {
        $success = false;
        $message .= ' Opcode Cache: failure';
    }
}

die(json_encode(array('success' => $success, 'message' => $message)));
