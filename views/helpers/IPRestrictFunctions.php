<?php

function ipInsideRange($ip,$range){
    $ips = explode('-', $range);
    $iplong = ip2long($ip);
    $low_ip = ip2long($ips[0]);
    $high_ip = ip2long($ips[1]);
    if ($iplong <= $high_ip && $low_ip <= $iplong) {
        return true;
    }
    else {
        return false;
    }
}

