<?php

require(__DIR__ . '/../vendor/autoload.php');
umask(0);

function take_time(\Closure $param): float
{
    $start = microtime(true);
    call_user_func($param);
    $stop = microtime(true);

    return round(($stop - $start) * 1000, 2);
}