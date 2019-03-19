<?php

ini_set('memory_limit', '-1');
set_time_limit(0);
require_once ('src/whatsprot.class.php');
$socket_path = "./reply_module/myserver.sock";
$starttime = time();
global $w;
global $count;
$count = 0;
connectwa();

function reload(){
    $wait = mt_rand(2, 7);
    global $w;
    $w->disconnect();
    sleep($wait);
    connectwa();
}


function connectwa() {
    global $w;
    $wait = mt_rand(10, 15);
    global $waittime;
    global $argv;
    $waittime = $wait*60;
    echo $waittime;
    $log = false;
    $debug = false;
    $nickname = 'POND\'S';
    $username = $argv[1];
    $password = $argv[2];
    $w = new WhatsProt($username, $nickname, $debug, $log);

    try
        {
        $w->connect();
        }

    catch(Exception $e)
        {
        echo 'Connection error: ' . $e->getMessage();
        reload();
        }

    try
        {
        $w->loginWithPassword($password);
        }

    catch(Exception $e)
        {
        echo 'Login error: ' . $e->getMessage();
        reload();
        }

    echo "connected \n";
}

sleep(2);
// $w->sendRemoveProfilePicture();
$w->sendSetProfilePicture($argv[1].'.jpg');
sleep(2);
echo "Profile Picture updated successfully\n";
?>
