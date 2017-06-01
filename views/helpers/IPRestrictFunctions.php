<?php

/**
 * Return if a ip is in a range
 * 
 * @param type $ip
 * @param type $range
 * @return boolean
 */
function ipInsideRange($ip,$range){
    $ips = explode('-', $range);
    $iplong = ip2long($ip);
    $low_ip = ip2long($ips[0]);
    if (count($ips)==1)
        $high_ip = $low_ip;
    else
        $high_ip = ip2long($ips[1]);
    if ($iplong <= $high_ip and $iplong >= $low_ip) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Returns aliases of ranges of ip in an array
 * 
 * @param type $ranges
 * @return type array
 */
function getRangesAliases($ranges){
    $alias = array();
    $range = explode(';', $ranges);
    foreach ($range as $r){
        $div = explode(':',$r);
        $alias[] = $div[0];
    }
    return $alias;
}

/**
 * Returns array with ranges IP available, index by alias
 * @param type $ranges
 */
function getRangesIPs($ranges){
    $rangesArray = array();
    $range = explode(';', $ranges);
    foreach ($range as $r){
        $div = explode(':',$r);
        $alias = $div[0];
        $ips = explode(',',$div[1]);
        foreach ($ips as $ip){
            $rangesArray[$alias][] = $ip;
        }
    } 
    return $rangesArray;
}

function countFilesOfItem(){
    $item = get_current_record('item');
    $num = 0;
    foreach (loop('files', $item->Files) as $file){
        $num++;
    }
    return $num;
}

function validateIPRanges($IpRangeString){
    $return = TRUE;
    $areas = explode(';', $IpRangeString);
    foreach ($areas as $area){
        $setofips = explode(':', $area);
        $ipranges = explode(',', $setofips[1]);
        foreach ($ipranges as $iprange){
            $ips = explode('-', $iprange);
            foreach ($ips as $ip){
                if (!filter_var($ip, FILTER_VALIDATE_IP))
                    $return = FALSE;
            }
        }
    }
    return $return;
}