<?php

class tempManager extends myOracle{
	private $pollingData 	= array();	//Temps pulled from database
	private $deviceList 	= array();
	private $dbpolling 		= array();
	private $sensors 		= array();
	private $display		= array();
	private $config 		= array();
	/*Config will hold the 
		email address to notify
		first threshold temp
			interval for first Notification
		second threshold temp
			interval for second Notification
		third threshold temp
			interval for third Notification
	*/
	
	
	public function __construct($config){
		parent::__construct	();
		$this->config = $config;
		$this->buildDevices();
	}
	
	private function buildDevices(){
		$sql = 'select footprint_id,device,ip from osp.tempDevices';
		$r = $this->runQuery($sql);
		while ($row = oci_fetch_array($r)){
			$rec = array('footprint' 	=> $row['FOOTPRINT_ID'],
						'device'		=> $row['DEVICE'],
						'ip'			=> $row['IP']
					);
			array_push($this->deviceList,$rec);
		} 
	}
	
	private function pollTemp($ip){		
		$url = 'http://'.$ip.':80/state.xml';
		$this->sensors = array();
		echo $url."\n";
		// get the Curl session
		$session = curl_init($url);

		// set some options for curl
		curl_setopt($session, CURLOPT_HEADER, false); // don't return the header
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true); // return the result as a string
		curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 3); // timeout if we don't connect after 3 seconds to the device

		// send the request
		if($xml = curl_exec($session)){
			curl_close($session);
		//$xml = file_get_contents('http://10.34.16.6/state.xml');
		//echo "XML retreived\n".$xml;
			$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
			$this->sensors[0] = $xml->sensor1temp; //Middle
			echo $xml->sensor1temp."\n";
			$this->sensors[1] = $xml->sensor2temp; //Outside
			echo $xml->sensor2temp."\n";
			$this->sensors[2] = $xml->sensor3temp; //Ac1 West Wall
			echo $xml->sensor3temp."\n";
			$this->sensors[3] = $xml->sensor4temp; //Ac2 East Wall
			echo $xml->sensor4temp."\n";
			return $this->sensors;
		}
	}
	
	private function writeTemps(){
		foreach($this->pollingData as $temp){
			echo count($temp['temp'])."\n";
			$insert = "insert into osp.temppolling (device,temp,captured,alert,temp2,temp3,temp4) values (".$temp['device'].",".$temp['temp'][0].",sysdate,".$temp['alert'].",".$temp['temp'][1].",".$temp['temp'][2].",".$temp['temp'][3].")";
			echo "\n".$insert."\n";
			$this->runInsert($insert);	
		}	
	}
	

	
	private function updateStats(){
		//Pull current days states for each device and calculate average temp for each hour then store in warehouse table
	}
	
	private function write_Device($i){
		
		$insert = "insert into osp.tempdevices values(osp.seq_tmpdevice.nextval,'".$i['footprint_id']."','".$i['ip']."')";
		$r = $this->runInsert($insert);
		if($r){
			return 1;	
		} else return 0;
	}
	
	private function Notification($lvl,$devid,$ip){
		
		$sql = "select tp.alert,to_char(tp.captured,'mi') captured,tp.temp,td.footprint_id from osp.temppolling tp, OSP.TEMPDEVICES td where td.device = tp.device and tp.device = ".$devid." and tp.captured = (select max(captured) from osp.temppolling where device = ".$devid.")";
		echo "\n $sql";	
		$r = $this->runQuery($sql);
		while ($row = oci_fetch_row($r)){
			$curinv = array($row[0],$row[1],$row[2],$row[3]);
		}
		//Check the current interval against config
		if($lvl === 1){
			if((($curinv[0] === 0) || ($curinv[0] >= $this->config['thrInv1']) || ($curinv[0] === NULL)) && $curinv[2] >= $this->config['thr1']){
				$curinv[0] = 1;
				$sent = $this->sendNotice(1,$curinv[1],$ip,$curinv[3]);
				if(!$sent) return -1;
				else return $curinv[0];
			}else return $curinv[0]+1;
		}elseif($lvl === 2){
			if((($curinv[0] === 0) || ($curinv[0] >= $this->config['thrInv2']) || ($curinv[0] === NULL)) && $curinv[2] >= $this->config['thr2']){
				$curinv[0] = 1;
				$sent = $this->sendNotice(2,$curinv[1],$ip,$curinv[3]);
				if(!$sent) return -1;
				else return $curinv[0];				
			}else return $curinv[0]+1;			
		}else{
			if((($curinv[0] === 0) || ($curinv[0] >= $this->config['thrInv3']) || ($curinv[0] === NULL)) && $curinv[2] >= $this->config['thr3']){
				$curinv[0] = 1;
				$sent = $this->sendNotice(3,$curinv[1],$ip,$curinv[3]);
				if(!$sent) return -1;
				else return $curinv[0];
			}else return $curinv[0]+1;	
		}
		return 0;
	}
	
	private function sendNotice($lvl,$captured,$ip,$footprint){
		if($lvl === 1){
			$subject = 'Threshold 1 breached'." on $footprint";
			$msg ="Some Text to be determined\n Middle Sensor Temp = ".$this->sensors[0]."\n Outside Temp = ".$this->sensors[1]."\n AC1 Temp = ".$this->sensors[2]."\n AC2 Temp = ".$this->sensors[3]."\n Pervious Temp Taken at ".$captured."\n http://$ip";
					
		} elseif ($lvl === 2){
			$subject = 'Threshold 2 breached'." on $footprint";
			$msg ="Some Text to be determined\n Middle Sensor Temp = ".$this->sensors[0]."\n Outside Temp = ".$this->sensors[1]."\n AC1 Temp = ".$this->sensors[2]."\n AC2 Temp = ".$this->sensors[3]."\n Pervious Temp Taken at ".$captured."\n http://$ip";
			
		}else{
			$subject = 'Threshold 3 breached'." on $footprint";	
			$msg ="Some Text to be determined\n Middle Sensor Temp = ".$this->sensors[0]."\n Outside Temp = ".$this->sensors[1]."\n AC1 Temp = ".$this->sensors[2]."\n AC2 Temp = ".$this->sensors[3]."\n Pervious Temp Taken at ".$captured."\n http://$ip";	
		}
		
		$s = mail($this->config['email'],$subject,$msg);
		return $s;		
	}
	
	private  function fahrenheit_to_celsius($given_value)
    {
        $celsius=5/9*($given_value-32);
        return $celsius ;
    }

    private function celsius_to_fahrenheit($given_value)
    {
        $fahrenheit=$given_value*9/5+32;
        return $fahrenheit ;
    }
	
	private function push_ads_temp($input){
		foreach($input as $i){
			$sql = "insert into osp.ads_tmp values('".$i['switch']."',".$i['temp'].",sysdate)";
			//echo $sql;
			//return $sql;
			$r = $this->runQuery($sql);	
			if(!$r) return -1;
		}
		return 1;
	}
	
	private function pullCurrentTemps(){
		$sql1 = "select to_char(p.captured,'HH24:MI') captured,p.temp,p.temp2,p.temp3,p.temp4,d.footprint_id 
				from OSP.TEMPPOLLING p,
					OSP.TEMPDEVICES d 
				where 
					d.device = p.device and
					captured = (select max(captured) from osp.temppolling)";
					  
		$sql2 = "select f1.footprint_id,f1.network_name,(t.temp*9/5+32)temp,to_char(t.poll_date,'HH24:MI') poll_date from OSP.ADS_TMP t,
				(select s.network_name,f.footprint_id from inv.switch s,OSP.FOOTPRINT_SEQ f 
					where F.FOOTPRINT_SEQ = s.footprint_seq) f1
				where t.ads = f1.network_name
				and t.poll_date = (select max(poll_date) from OSP.ADS_TMP)
				and (t.temp > 49 or t.temp < 20) order by t.temp desc";
				
		$polledTemp = array();
		$polledTemp['probs'] = array();
		$polledTemp['ads'] = array();
		$r = $this->runQuery($sql1);
		while($row = oci_fetch_array($r)){
			$rec = array('footprint' 	=> $row['FOOTPRINT_ID'],
						 'temp'			=> $row['TEMP'],
						 'temp2'		=> $row['TEMP2'],
						 'temp3'		=> $row['TEMP3'],
						 'temp4'		=> $row['TEMP4'],
						 'date'			=> $row['CAPTURED']	
						 );
			array_push($polledTemp['probs'],$rec);
		}

		//$pulledTemp = array();
		oci_free_statement($r);
		$r1 = $this->runQuery($sql2);
		while($row = oci_fetch_array($r1)){
			//$tmp = $this->celsius_to_fahrenheit($row['TEMP']);
			$rec = array('footprint'	=> $row['FOOTPRINT_ID'],			
						 'temp'			=> $row['TEMP'],
						 'ADS'			=> $row['NETWORK_NAME'],
						 'date'			=> $row['POLL_DATE']
						 );
			array_push($polledTemp['ads'],$rec);
		}
		oci_free_statement($r1);
		return $polledTemp;
	}
	
	//End Private function
	
	public function pollDevices(){
		foreach ($this->deviceList as $device){
			//echo $device['ip']."\n";
			$sensorTemp = $this->pollTemp($device['ip']); //Get the temp from sensor 1 and add it to rec
			//echo $sensorTemp."\n";
			if(is_array($sensorTemp)){
				echo $device['ip']."\n"; 
				$rec['device'] = $device['device'];
				$rec['temp'][0] = $sensorTemp[0];
				$rec['temp'][1] = $sensorTemp[1];
				$rec['temp'][2] = $sensorTemp[2];
				$rec['temp'][3] = $sensorTemp[3];
				//print_r($rec);
			}else {
				echo "Skipping device ".$device['device']."\n";	
				continue;
			}
			/*$rec['device'] = $device['device'];
			$rec['temp'][0] = $sensorTemp[0];
			$rec['temp'][1] = $sensorTemp[1];
			$rec['temp'][2] = $sensorTemp[2];
			$rec['temp'][3] = $sensorTemp[3];
			*/

			//Now check the temp of sensor 1 against the configured thresholds
			//pass the device_id and the alert level 1 thru 3 to the Notification function if temp fails threshold checks
			//update interval and set it to the new record

			if ($sensorTemp[0] >= $this->config['thr1']){
				echo "\n First Threshold $sensorTemp";
				if($sensorTemp[0] >= $this->config['thr2']){
					echo "\n Second Threshold $sensorTemp";
					if($sensorTemp[0] >= $this->config['thr3']){
						//echo "\n $sensorTemp";
						$sent = $this->Notification(3,$rec['device'],$device['ip']); 
						if($sent === -1) echo 'Failed To send';
						$rec['alert'] = $sent;
						array_push($this->pollingData,$rec);
					}else{						
						$sent = $this->Notification(2,$rec['device'],$device['ip']);
						if($sent === -1) echo 'Failed To send';
						$rec['alert'] = $sent;
						array_push($this->pollingData,$rec);
					}
				}else{
					echo "\n in email threshold 1";
					$sent = $this->Notification(1,$rec['device'],$device['ip']);
					//echo "\n $sent";
					if($sent === -1) echo 'Failed To send';
					$rec['alert'] = $sent;
					array_push($this->pollingData,$rec);
				}
			}else {
				echo "Pushing to polling Data\n";
				$rec['alert'] = 0;
				//print_r($rec);
				array_push($this->pollingData,$rec);
				print_r($this->pollingData);
			}						
		}
		$this->writeTemps();
			
			/*if ($sensorTemp[0] >= $this->config['thr1']){
				echo "\n First Threshold $sensorTemp";
				if($sensorTemp[0] >= $this->config['thr2']){
					echo "\n Second Threshold $sensorTemp";
					if($sensorTemp[0] >= $this->config['thr3']){
						echo "\n $sensorTemp";
						$sent = $this->Notification(3,$rec['device'],$device['ip']); 
						if($sent === -1) echo 'Failed To send';
						$rec['alert'] = $sent;
						array_push($this->pollingData,$rec);
					}else{						
						$sent = $this->Notification(2,$rec['device'],$device['ip']);
						if($sent === -1) echo 'Failed To send';
						$rec['alert'] = $sent;
						array_push($this->pollingData,$rec);
					}
				}else{
					echo "\n in email threshold 1";
					$sent = $this->Notification(1,$rec['device'],$device['ip']);
					echo "\n $sent";
					if($sent === -1) echo 'Failed To send';
					$rec['alert'] = $sent;
					array_push($this->pollingData,$rec);
				}
			}else {
				$rec['alert'] = 0;
				array_push($this->pollingData,$rec);
			}
		}

		$this->writeTemps();
		*/
	}
	
	public function add_device($i){
		$v = $this->write_Device($i);
		return $v;	
	}

	public function buildDash(){
		foreach($this->deviceList as $device){
			$this->getDisplayData($device);
		}
		return $this->display;
	}
	//Depricated below
	public function buildDeviceDetails($i){
		
		$count = $i['hrs']*60; //total pulls needed for averaging
		$stats = $this->getDeviceDetails($count,$i['avgBy'],$i['device']);
		return $stats;
	}
	
	public function get_ADS_temps($input){
		
			$r = $this->push_ads_temp($input);
			//return $r;
			if(!$r) return -1;
		
		return 1;
	}
	
	public function getDisplay(){
		$ret = $this->pullCurrentTemps();
		//$ret = 1;
		return $ret;
	}
	
}