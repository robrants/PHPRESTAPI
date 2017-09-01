<?php
class audit_ONT_CSV extends myOracle{
	private $sql = "select 
    		o.mac,o.address_id,m.ATTRIBUTE1||' '||m.ATTRIBUTE2 mm,o.uptime,p.q_date run_date,p.portnum,p.speed,p.up_down,p.vlan_num,p.inbytes,p.outbytes 
		from 
			blackr.ontdevice o,
			blackr.ontport p,
			inv.model m 
		where 
			o.MAC = p.MAC and
			o.model_id = m.model_id";
	private $ONT = array();
	private $sysUtil;
	
	public function __construct(){
		parent::__construct();
		$this->sysUtil = new sysUtils();
	}
	
	private function buildReport(){
		$r = $this->RunQuery($this->sql);
		while($row = oci_fetch_array($r)){
			$rec = array('mac' 		=> $row['MAC'],
						'adid' 		=> $row['ADDRESS_ID'],
						'mm' 		=> $row['MM'],
						'uptime'	=> $row['UPTIME'],
						'run_date'	=> $row['RUN_DATE'],
						'portnum'	=> $row['PORTNUM'],
						'speed'		=> $row['SPEED'],
						'up_down'	=> $row['UP_DOWN'],
						'vlan_num'	=> $row['VLAN_NUM'],
						'inb'		=> $row['INBYTES'],
						'outb'		=> $row['OUTBYTES']);
			array_push($this->ONT,$rec);
		}
	}
	
	public function getReport(){
		$this->buildReport();
		$header = array('MAC','ADID','MM','UPTIME','RUN_DATE','PORTNUM','SPEED','UP_DOWN','VLAN_NUM','INBYTES','OUTBYTES');
		$filename = 'ontAudit.csv';
		$title = 'Zhone ONT Audit';
		$this->sysUtil->exportReport($filename,$this->ONT,$header,$title);
	}
}