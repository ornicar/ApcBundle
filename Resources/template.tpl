<?php

if (!in_array($_SERVER['REMOTE_ADDR'], array('localhost', '127.0.0.1', '::1')))
{
    header("HTTP/1.0 404 Not Found");
    die;
}

if(%user%)   apc_clear_cache('user');
if(%opcode%) apc_clear_cache('opcode');

die(json_encode(array('success' => true, 'message' => sprintf('Clear APC user:%user%, opcode:%opcode%'))));
