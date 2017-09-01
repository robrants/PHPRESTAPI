<?php
class assignIfindex extends myOracle{
	private $switchPorts = array();
	
	public function __construct(){
		parent::__construct();
	}
	
	
	public function pullPortsAdid(){
		$sql = "select lp.IFIDXID,a1.address_id from blackr.localports lp,
					(select distinct s.address_id,a.switch_id,' '||a.switch_slot||'/'||a.switch_card||'/'||a.switch_port port,a.footprint_id,sw.network_name 
					from 
						osp.address a,
						(select distinct address_id from sm.service where service_end_date is null) s,
						blackr.masterads sw
					where
						a.address_id = s.address_id and
						a.switch_id = sw.switch_id and a.switch_card != 0) a1
				where
					lp.switch_id = a1.switch_id 
					and lp.port = a1.port";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('ifindex' 	=> $row['IFIDXID'],
						'adid'		=> $row['ADDRESS_ID']);
			array_push($this->switchPorts,$rec);
		}
		
		foreach($this->switchPorts as $sw){
			$check = 1;
			$update = "update osp.address set ifindex = ".$sw['ifindex']." where address_id = ".$sw['adid'];
			if($r = $this->runInsert($update)) continue;
			else {
				$check = -1;
				break;
			}
		}
		return $check;
	}
}
?>