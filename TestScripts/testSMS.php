<?php
require '../vendor/autoload.php';
require '../Common/messaging.php';
$MSG = new messaging();
$to = '18016691520';
$message = 'Wow this crap really works!';
echo $MSG->sendSMS($to,$message);
?>