<?php

use NeoP\Log\Log;
use NeoP\DI\Container;


if (!function_exists('logger')) {
    function logger()
    {
        return Container::getDefinition(Log::class);
    }
}