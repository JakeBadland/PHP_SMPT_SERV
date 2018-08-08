<?php

include ('server.php');

$server = new SmtpServer();

$result = $server->start();
if (!$result){
    die('Unable to start... exiting');
}

$server->startListen();