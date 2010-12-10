<?php

if(%user%)   apc_clear_cache('user');
if(%opcode%) apc_clear_cache('opcode');

die(json_encode(array('success' => true, 'message' => sprintf('Clear APC user:%user%, opcode:%opcode%'))));
