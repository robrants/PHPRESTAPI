<?php
class ZoneWalker extends snmpData{
	public $ZoneArray = array();
	public $data;
	public $ip;
	public $oidBaseOut = '1.3.6.1.4.1.5504.2.5.41.1.4.4.1.2.1.';
	public $oidBaseIn = '1.3.6.1.4.1.5504.2.5.41.1.4.4.1.5.1.';
	public $host = 'ut0p1a5nmp';
	private function pullSpeeds($zone){
		/*
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.2.1.2 – eth1 outbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.2.1.3 – eth2 outbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.2.1.4 – eth3 outbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.2.1.5 – eth4 outbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.5.1.2 – eth1 inbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.5.1.3 – eth2 inbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.5.1.4 – eth3 inbound rate
			1.3.6.1.4.1.5504.2.5.41.1.4.4.1.5.1.5 – eth4 inbound rate

		*/	
		$host = $this->host; //Set host to expected community
		$device = array($zone['ads'],$zone['address_id'],$zone['ip'],$zone['mac'],$zone['descr']);
		//echo 'ADID: '.$zone['address_id'].'<br>';
		//echo 'IP: '.$zone['ip'].'<br>';
		//echo 'MAC: '.$zone['mac'].'<br>';
		//echo 'Provision Speed: '.$zone['descr'].'<br>';
		$inbound = array();
		$outbound = array();
		for($x=0; $x<$zone['ports']; $x++){			
			if($zone['ip'] == null || $zone['ip'] == ''){break;} //If there is no IP no point it going any further
			
			$curport = $x+2; //ports start @ 2 and increment by 1 till we exhuast the ports on the device;
			
			$oidout = $this->oidBaseOut.$curport; //set the OID for outbound traffic			
			$oidin = $this->oidBaseIn.$curport; //Set the OID for Inbound Traffic
			
			$rateout = snmp2_get($zone['ip'],$host,$oidout);
			if($rateout == false){
				$host = 'public'; //Change community to public and retest.
				$rateout = snmp2_get($zone['ip'],$host,$oidout);
			}
			$p = $x+1;
			if(!$rateout){
				$rateout = 'Port Disabled';
			}else{				
				$rout = explode(':',$rateout);
				$rateout = $rout[1];				
			}
			
			if($rateout >= 250 and $rateout <= 350){$outbound[$x] = 'Device Provisioned Correctly'; break;}
			//parse output
			$outbound[$x] = $rateout;
			//echo 'Device Outbound Speed on Port: '.$p.' is '.$rateout.'<br>';			
			//Begin inbound testing
			$ratein = snmp2_get($zone['ip'],$host,$oidin);			
			if(!$ratein){
				$ratein = 'Port Disabled';
			}else{				
				$rin = explode(':',$ratein);
				$ratein = $rin[1];				
			}
			$inbound[$x] = $ratein;
			//echo 'Device Inbound Speed on Port: '.$p.' is '.$ratein.'<br>';
		}
		return array_merge($device,$outbound,$inbound);
	}
	
	public function runSpeed250(){
		$this->data = $this->getZones250();
		$results = array();
		$header = array('ADS','ADID','IP','MAC','PRODUCT','OUT BOUND 1','OUT BOUND 2','OUT BOUND 3','OUT BOUND 4','OUT BOUND 5','OUT BOUND 6','OUT BOUND 7','OUT BOUND 8','IN BOUND 1','IN BOUND 2','IN BOUND 3','IN BOUND 4','IN BOUND 5','IN BOUND 6','IN BOUND 7','IN BOUND 8');
		//build file headers for sending as attachment
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=ZhoneAudit250.csv');
		foreach($this->data as $zone){
			$row = $this->pullSpeeds($zone);
			array_push($results,$row);
		}
		if($FILE = fopen('php://output', 'w')) {
			fputcsv($FILE,$header);
			foreach($results as $row){
				fputcsv($FILE,$row);
			}
		}else print "$f\n";
		fclose($FILE);
		
	}
	
}
?>