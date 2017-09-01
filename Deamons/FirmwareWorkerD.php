<?php
//Load up dependencies
const SPATH = '/var/www/mu/Applications/';
require_once(SPATH.'vendor/autoload.php');
require_once('config/globals.php');
function loadclass($class){		
		foreach($class as $c){
			
			$source = array(SPATH.'DHCP/data/'.$c.'.php',SPATH.'Common/'.$c.'.php',SPATH.'Firmware/data/'.$c.'.php',SPATH.'Firmware/control/'.$c.'.php');			
			foreach($source as $s){
				if(file_exists($s)){
					//echo $s."<br>";
					require_once($s);
				}
			}
		}
	}
$runLog = SPATH.'Deamons/logs/DeamonLog.log';
loadclass($globals['class']);
//Configure Objects
$queu = new \Pheanstalk\Pheanstalk('127.0.0.1');
$queu->watchOnly('firmware');

$run = true;
//Setup the Signal Management;
pcntl_signal(SIGINT, function() use (&$run,&$runLog){
    $run = false;
    error_log("shutting down \n",3,$runLog);
});

declare(ticks=1);
$run = true;

while($run){
	$job = $queu->reserve(10); //Listen for 10 seconds then go to sleep for a bit
	if(!$job){
		sleep(40); //take a nap as no work to do
		continue;
	}
	$FM = new firmWare();
	$upgrade = json_decode($job->getData(),true);	
	error_log('Starting Work on Upgrading '.$upgrade['IP']."\n",3,$runLog);
	$queu->delete($job);
	$results = $FM->runUpdate($upgrade);	
	error_log($upgrade['IP']."\n",3,$runLog);	
	if($results == 1){
		error_log('Upgrade of '.$upgrade['IP']." Successful \n",3,$runLog);
		$message = 'Upgrade of '.$upgrade['IP'].' Successful';
		$msg = new messaging();
		$msg->pushlog($message,'firmWare','upgradeONT');
		unset($msg);
	}else{		
		error_log('Upgrade of '.$upgrade['IP']." Failed Please Investigate\n $results",3,$runLog);}
	
}

function ping($h){
		exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($h)), $res, $rval);
        return $rval === 0;	
}

function upgradeONT($ONT){
		$TELNET = new PHPTelnet();
		$conCheck = $TELNET->Connect($ONT['ip'],$this->user,$this->pass);		
		$TELNET->doCommand($this->user,$output);				
		$TELNET->doCommand($this->pass,$output);				
		$output = trim($output);
		$output = substr($output,strlen($output)-1,strlen($output));
		if($output == '>'){			
			$TELNET->doCommand('en',$output);
			$output = trim($output);
			$output = substr($output,strlen($output)-1,strlen($output));
			//echo $output."<br>";
			if(trim($output) == '#'){				
				$TELNET->doCommand('archive download-sw '.$ONT['URL'],$output);				
				$output = trim($output);
				$output = substr($output,strlen($output)-1,strlen($output));
				sleep(240);
				$TELNET->doCommand("\n",$output);				
				///echo 'Before trim and string chomp'.$output.'<br>';
				$output = trim($output);
				//echo '['.$output.']<br>';
				preg_match('/successful/',$output,$m);
				//echo $m."<br>";
				if(count($m)>0){
					//echo 'Success!<br>';
					$output = substr($output,strlen($output)-1,strlen($output));
				}else {
					$message = "'Upgrade of ".$upgrade['IP']." Failed Please Investigate";
					$msg = new messaging($message,'FirmwareWorkerD','upgradeONT');
					return -1;}
				if(trim($output) == '#'){
					$TELNET->doCommand('reload sw',$output);
					echo 'sleeping now<Br>';
					sleep(240);					
					$check = ping($ONT['ip']);
					if($check) return 1;
				}else return -2;
			} else return -3;
		}else return -4;						
	}


?>