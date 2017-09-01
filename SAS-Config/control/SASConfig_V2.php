<?php
//Dependencies myOracle.php, UtopiaDHCP.php, vlans.php

class sasDeploy extends myOracle{
	
	private $cfg_file 	= '';
	private $patterns	= array();
	private $devType 	= '';
	private $ip 		= '';
	private $mac 		= '';
	private $gateway 	= '';
	private $inputs		= array();
	private $ADS		= '';
	private $DHCP 		= '';
	private $hostname	= '';
	private $samid		= '';
	private $orderid	= 0;
	private $order		= array();
	private $vlan		= array();
	private $subid1		= '';
	private $custport	= '';
	private $speed		= array();
	private $CMBS		= array(0=>array('device'=>'SASD','CBS'=>1638,'MBS'=>8192),
								1=>array('device'=>'SAST','CBS'=>214696,'MBS'=>1073479),
								2=>array('device'=>'SASM','CBS'=>214696,'MBS'=>1073479)
								);
	
	public function __construct() {
		//create myOracle Instance
		parent::__construct();	
	}
	
	public static function CManage($dtype,$city,$adid,$mac){
		$instance = new self();
		$instance->RunCManage($dtype,$city,$adid,$mac);
		return $instance;
	}
	
	public static function provisionService($stype,$dtype,$orderid,$port,$speed,$vlan){
		$instance = new self();
		$instance->addService($stype,$dtype,$orderid,$port,$speed,$vlan);
		return $instance;
	}
	
	protected function RunCManage($dtype,$city,$adid,$mac){
		$this->patterns = array('/#MAC#/','/#IP#/','/#GATEWAY#/','/#HOSTNAME#/','/#SAMID#/');
		$this->devType = $dtype;		
		$this->DHCP = new UtopiaDHCP();			
		$this->ip = $this->DHCP->getNextIP($city);
		$this->buildIPData($city);
		$this->mac = $mac;
		$this->inputs['mac'] = $this->mac;
		$this->inputs['hostname'] = $adid;
		$this->hostname = $adid;
		$this->inputs['ip'] = $this->ip;
		$this->DHCP->addStatic($this->inputs);
		$this->ip = $this->ip.'/17';
		$this->gateway = $this->inputs['network'].'.1';
		$this->buildMgr();	
	}
		
	protected function addService($stype,$dtype,$orderid,$port,$speed,$vlan){
		//Assign all private values for later use
		$this->patterns = array('/#MBPS#/','/#CIR#/','/#PIR#/','/#DEVICECBS#/','/#DEVICEMBS#/','/#CUSTOMERPORT#/','/#CUSTOMERNAME#/','/#ORDER#/','/#VLAN#/');
		$this->devType = $dtype;
		$this->orderid = $orderid;
		$this->custport = $port;	
		$this->getSpeed($speed);
		$this->getOrderInfo();
		$policysql = "select * from osp.sasconfig where DEVNAME = 'ALL'";
		$r = $this->runQuery($policysql);
		while ($row = oci_fetch_array($r)){
			$this->cfg_file = $row['CONF_FILE'];	
		}
		oci_free_statement($r);
		$confsql = "select * from osp.sasconfig where devname = '".$this->devType."' and conftype = '".$stype."'";
		$r = $this->runQuery($confsql);
		while ($row = oci_fetch_array($r)){
			$this->cfg_file = $this->cfg_file.$row['CONF_FILE'];
		}
		//Get the VLAN data
		if($vlan == '0') $this->getVlan();
		else{
			$V = new vlan();
			$this->vlan['vid'] = 0;
			$this->vlan['vlan'] = $vlan;
			$V->addVlan($this->formatVlanData());
		}
		$this->buildSrv();
		
	}
	
	private function buildMgr(){
		if(isset($this->devType)){
			$sql = 'select * from osp.sasconfig where device = '.$this->devType;
			$r = $this->runQuery($sql);
			while ($row = oci_fetch_array($r)){
				$this->cfg_file = $row['CONF_FILE'];	
			}
			//substitute the supplied values into the config file			
			$this->getSamID();
			$replace = array($this->mac,$this->ip,$this->gateway,$this->hostname,$this->samid);
			$this->subConfig($replace);
			$this->addDeployStatus();
			$this->capture_config();
		}
	}
	
	
	private function buildSrv(){
		//Here is where we will build out the service snipit;
		$policy = array();
		if($this->devType == 'SASD'){
			$policy = $this->CMBS[0];	
		}else $policy = $this->CMBS[1];
		$replace = array($this->speed['MBPS'],$this->speed['CIR'],$this->speed['PIR'],$policy['CBS'],$policy['MBS'],$this->custport,$this->order['CUSTNAME'],$this->orderid,$this->vlan['vlan']);
		$this->subConfig($replace);
	}
	
	private function addDeployStatus(){
		$sql = "insert into osp.devicedeploy (deviceid,adid,mac,confdate,status) values(osp.seq_devdeploy.nextval,".$this->hostname.",'".$this->mac."',sysdate,1)";
		$r = $this->runInsert($sql);
		if(!$r) exit;
	}
	
	private function buildIPData($city){
		$this->inputs = $this->DHCP->getSingleCity($city);	
	}
	
	private function subConfig($replace){		
		$this->cfg_file = preg_replace($this->patterns,$replace,$this->cfg_file);	
	}
	//Capture the config file that was created
	private function capture_config(){
		$insert = "insert into osp.device_config (confid,adid,config,createdate) values(osp.seq_confid.nextval,".$this->hostname.",'".$this->cfg_file."',sysdate)";
		$r = $this->runInsert($insert);
		if(!$r) exit;
		
	}
	private function getSamID(){
		$sql = "select samid from OSP.SAM_SITEIDS
				order by
				to_number(substr(samid,1,instr(samid,'.')-1)) ,
				to_number(substr(samid,instr(samid,'.')+1, instr(samid,'.',1,2) - instr(samid,'.') - 1)),
				to_number(substr(samid,instr(samid,'.',1,2)+1, instr(samid,'.',1,3) - instr(samid,'.',1,2) - 1)),
				to_number(substr(samid,instr(samid,'.',1,3)+1))";
		$r = $this->runQuery($sql);
		$x = 0;
		while($row = oci_fetch_row($r)){
			$siteids[$x] = $row[0];
			$x++;	
		};
				
		$siteid = explode('.',$siteids[count($siteids)-1]);
		if($siteid[3] < 255){
			$siteid[3]++;	
		}else{
			$siteid[3] = 1;
			if($siteid[2] < 255){
				$siteid[2]++;	
			}else{
				$siteid[2] = 1;
				if($siteid[1] < 255){
					$siteid[1]++;							
				}else{
					$siteid[1] = 1;	
					$siteid[0]++;
				}
			}
		}
		$ip = implode('.',$siteid);
		$this->samid = $ip;
		$insert = "insert into osp.sam_siteids (samid,mac) values('".$this->samid."','".$this->mac."')";
		$r = $this->runInsert($insert);
		if(!$r) exit;
	}
	
	private function formatVlanData(){
		if($this->vlan['vid'] == 0){
			$tmp = array('vlan' => $this->vlan['vlan'],'universal'=>'Non','name'=>$this->order['CUSTNAME'],'description'=>$this->order['ORDERID'],'sp'=>$this->order['ISP'],'rvlan'=>0);
		}
		else{
			$tmp = array('vid'=>$this->vlan['vid'],'vlan' => $this->vlan['vlan'],'universal'=>'Non','name'=>$this->order['CUSTNAME'],'description'=>$this->order['ORDERID'],'sp'=>$this->order['ISP'],'rvlan'=>0);	
		}
		return $tmp;
	}
	
	private function getVlan(){
		//Pull the vlan based on the ISP
		//ISPs 1047,1025 and 1013 use first vlan assigned to them with no description all others use next max vlanid
		$V = new vlan();
		$tmpvlan = 0;
		switch($this->order['ISP']){
			case 1047:
			case 1025:
			case 1013:
				$sql = "select vid,vlan from osp.vlan where service_provider = ".$this->order['ISP']." and (name is null or name = '') and (description is null or description = '')";
				$r = $this->runQuery($sql);
				$tmpvlan = oci_fetch_row($r);
				$this->vlan['vid'] = $tmpvlan[0];
				$this->vlan['vlan'] = $tmpvlan[1];
				//$V->updateVlan($this->formatVlanData());
			break;
			default:
				$sql = "select max(vlan)+1 vlan from osp.vlan";
				$r = $this->runQuery($sql);
				$tmpvlan = oci_fetch_row($r);
				$this->vlan['vid'] = 0;
				$this->vlan['vlan'] = $tmpvlan[0];
				//$V->addVlan($this->formatVlanData());
			break;
		}

	}
	
	private function getOrderInfo(){
		//pull the order info and place it in $this->order
		$sql = "select oh.order_header_id as orderid, sub.sp_sub_id1 as custname,sub.service_provider_id as isp from 
				sm.order_header oh,
				sm.subscriber sub
				where oh.subscriber_id = sub.subscriber_id and oh.order_header_id = ".$this->orderid;
		$r = $this->runQuery($sql);
		$tmp = array();
		while ($row = oci_fetch_array($r)){
			array_push($tmp,$row);	
		}
		$this->order = $tmp[0];
	}
	
	private function getSpeed($speed){
		$sql = "select MBPS,CIR,PIR from osp.servicespeed where mbps = ".$speed;
		$r = $this->runQuery($sql);
		$tmp = array();
		while ($row = oci_fetch_array($r)){
			array_push($tmp,$row);	
		}
		$this->speed = $tmp[0];
	}
	
	//End Private Functions
	
	public function returnConfig(){
		$cfg = implode('<br>',explode('|',$this->cfg_file));		
		return $cfg;	
	}	
	
	public function testOutput(){
		print_r($this->order);
	}
}

?>