<?php

if (sizeof($argv) != 3){
    echo "Insufficient args!";
    exit(1);
}

ini_set('memory_limit', '-1');
set_time_limit(0);
require_once ('src/whatsprot.class.php');
$starttime = time();
global $w;
connectwa();


function sync(){
global $w;
$myContacts = array('917576049117', '8614705167975'); // Please change with some real WhatsApp Number
try{
	$w->sendSync($myContacts, null, 0);
}
catch(Exception $e){
	echo $e;
}
sleep(5);
exit();
}



function connectwa() {
    global $w;
    global $argv;
	$wait = mt_rand(10, 15);
	 global $waittime;
	$waittime = $wait*60;
	echo $waittime;
    $log = false;
    $debug = false;
    $nickname = 'POND\'S';
    $username = $argv[1];
    $password = $argv[2];
    $w = new WhatsProt($username, $nickname, $debug, $log);
    $w->eventManager()->bind('onGetMessage', 'onGetMessage');
    $w->eventManager()->bind('onGetUrl', 'onGetUrl');
    $w->eventManager()->bind('onGetDocument', 'onGetDocument');
    $w->eventManager()->bind('onGetAudio', 'onGetAudio');
    $w->eventManager()->bind('onGetImage', 'onGetImage');
    $w->eventManager()->bind('onGetVideo', 'onGetVideo');
    $w->eventManager()->bind('onGetvCard', 'onGetvCard');

    
    

    try {
        $w->connect();
    }
    catch(Exception $e) {
        echo 'Connection error: ' . $e->getMessage();
        exit(0);
    }
    try {
        $w->loginWithPassword($password);
    }
    catch(Exception $e) {
        echo 'Login error: ' . $e->getMessage();
        exit(0);
    }
	echo "connected \n";
	sync();

}


while (1) {
    try {
        $w->pollMessage();
        #$w->sendSetProfilePicture("abc.jpg");
        if (time() - $starttime > $waittime) {
           echo "Disconnecting\n";
           $starttime = time();
	   $w->disconnect();
        }

    }
    catch(Exception $e) {
    }
    if (!$w->isConnected()) {
        echo "disconnected\n";
	system('clear');
        connectwa();
    }
}
?>
