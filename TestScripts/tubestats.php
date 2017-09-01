<?php
require '../vendor/autoload.php';
$que = new \Pheanstalk\Pheanstalk('127.0.0.1');
$results = $que->statsTube('firmware');
print_r($results);
echo '<br>';
foreach($results as $keys => $r){
	echo $keys.' '.$r;
	echo '<br>';
}

?>