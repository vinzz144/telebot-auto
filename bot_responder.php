<?php
$url='http://localhost/bot321/public/bot/auto-responder';
date_default_timezone_get('Asia/Jakarta');
$stop=false;
while ( $stop!=true) {

    // echo $reply;
    $chInit = curl_init();
    curl_setopt($chInit, CURLOPT_URL, $url);
    curl_setopt($chInit, CURLOPT_RETURNTRANSFER, true);
    $exec = curl_exec ($chInit);
    echo "run at ".date('y-m-d H:i:s')."\n";//.": response--> ".$exec."\n";
    curl_close ($chInit);
    sleep(5);
}
?>
