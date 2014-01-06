<?php
$rpc_user="";
$rpc_pass="";
$rpc_host="127.0.0.1";
$rpc_port="9332";


$transactions_count=20;
$confirmations_req=20;


function getHumanReadableSize($size, $unit = null, $decemals = 2) {
    $byteUnits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    if (!is_null($unit) && !in_array($unit, $byteUnits)) {
        $unit = null;
    }
    $extent = 1;
    foreach ($byteUnits as $rank) {
        if ((is_null($unit) && ($size < $extent <<= 10)) || ($rank == $unit)) {
            break;
        }
    }
    return number_format($size / ($extent >> 10), $decemals) . $rank;
}

?>
