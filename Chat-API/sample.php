<?php

if (sizeof($argv) != 4){
    echo "Insufficient args!";
    exit(1);
}

ini_set('memory_limit', '-1');
set_time_limit(0);
require_once ('src/whatsprot.class.php');
$socket_path = "./reply_module/myserver.sock";
$starttime = time();
global $w;
global $count;
$count = 0;
connectwa();



function sendMessage($contact, $type, $reply){
        global $w;
        global $count;
        global $argv;
        $w->sendMessageComposing($contact);
        sleep(2);
        $w->sendMessagePaused($contact);
        sleep(2);
        $w->sendMessage($contact, $reply);
        
        if (array_key_exists('image', $type)){
            $w->sendMessageImage($contact,"Content/".$argv[1].".png");
        }
        $w->pollMessage();

        echo $count.". Sending ".$type['name'].' '.$contact."\n";

        $count = $count + 1;
        if($count > 100){
            $w->sendMessage('917576049117',"Mela has reached".$count);
            $w->sendMessage('919991722903',"Mela has reached".$count);
            $count = 0;
        }
}


function onGetMessage($object)
{
        global $w;
        $message = $object->getMessage();
        $contact = explode("@", $object->getFrom()) [0];
        $name = $object->getNotify();
        $type = "text";
        echo "Received ".$message." from ".$contact."\n";
        $reply = getreply($name, $contact, $message);
        sendMessage($contact, array('type' => $type, 'name' => $name), $reply);
    
    
}

function onGetUrl($urlObject)
{       $message = "sent url";
        $message = $urlObject->getMessage();
        $contact = explode("@", $urlObject->getFrom()) [0];
        $type = "You have sent *Simple URL* in Message.\n".$urlObject->getUrl();
        sendMessage($contact, $type, $message);
}

function onGetImage($imageObject){
        $message = "sent image";
        file_put_contents('Downloads/images/'.time().'.jpg', $imageObject->DownloadFile());
        $contact = explode("@", $imageObject->getFrom()) [0];
        $type = "You have sent *Image* in Message.";
        sendMessage($contact, $type, $message);

}
function onGetVideo($videoobject){
        $message = "sent video";
        file_put_contents('Downloads/media/'.time().'.mp4', $videoobject->DownloadFile());
        $contact = explode("@", $videoobject->getFrom()) [0];
        $type = "You have sent *Video* in Message.";
        sendMessage($contact, $type, $message);
}
function onGetvCard($vcardObject){
        $message = "sent contact number";
        $contact = explode("@", $vcardObject->getFrom()) [0];
        $type = "You have sent *Contact Number* in Message.";
        sendMessage($contact, $type, $message);

}
function onGetDocument($documentObject){
        $message = "sent document";
        file_put_contents('Downloads/documents/'.time(), $documentObject->DownloadFile());
        $contact = explode("@", $documentObject->getFrom()) [0];
        $type = "You have sent *Documents* in Message.";
        sendMessage($contact, $type, $message);
}

function onGetAudio($audioObject){
        $message = "sent audio";
        file_put_contents('Downloads/audio/'.time().'.mp3', $audioObject->DownloadFile());
        $contact = explode("@", $audioObject->getFrom()) [0];
        $type = "You have sent *Audio* in Message.";
        sendMessage($contact, $type, $message);

}

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
    $w->eventManager()->bind('onGetMessage', 'onGetMessage');
    $w->eventManager()->bind('onGetUrl', 'onGetUrl');
    $w->eventManager()->bind('onGetDocument', 'onGetDocument');
    $w->eventManager()->bind('onGetAudio', 'onGetAudio');
    $w->eventManager()->bind('onGetImage', 'onGetImage');
    $w->eventManager()->bind('onGetVideo', 'onGetVideo');
    $w->eventManager()->bind('onGetvCard', 'onGetvCard');

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

    echo "connected $argv[1]\n";
}

function getreply($name, $number, $msg){
    global $argv;
	global $socket_path;
	$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    while(1){
        try{
            socket_connect($socket, $socket_path);
            break;
        }
        catch(Exception $e){
            echo "Couldn't connect to reply server, please check\nTrying in 10 seconds";
            sleep(10);
        }
    }
    $data = json_encode(array(
    	'type' => 'getreply', 
    	'name' => $name, 
    	'number' => $number, 
    	'msg' => $msg,
        'botid' => $argv[1]));
    socket_send($socket, $data, strlen($data), MSG_EOR);
    $reply = json_decode(socket_read($socket, 10000), true);
    return $reply['reply'];
}

function sendall(){
	global $socket_path;
	global $argv;
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    while(1){
        try{
            socket_connect($socket, $socket_path);
            break;
        }
        catch(Exception $e){
            echo "Couldn't connect to reply server, please check\nTrying in 10 seconds";
            sleep(10);
        }
    }
    $data = json_encode(array('type' => 'numbers', 'number' => $argv[1]));
    socket_send($socket, $data, strlen($data), MSG_EOR);
    $reply = json_decode(socket_read($socket, 10000), true);
    socket_close($socket);

    foreach ($reply['numbers'] as $value) {
        sendMessage(
            $value[0], 
            array(
                'type' => 'text', 
                'name' => $value[1],
                'image' => true
            ), 
            str_replace("<name>",$value[1],$reply['msg'])
        );
    }
    echo "\nMessaging Done\n\n";
}

if ($argv[3] == '1'){
    sendall();
}

while (1)
    {
    try
        {
        $w->pollMessage();
        if (time() - $starttime > $waittime)
            {
            echo "Disconnecting\n";
            $starttime = time();
            $w->disconnect();
            }
        }

    catch(Exception $e)
        {
        echo "handle error here";
        }

    if (!$w->isConnected())
        {
        echo "disconnected\n";
        system('clear');
        connectwa();
        }
    }
?>
