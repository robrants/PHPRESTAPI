<?php
class ONT_Data extends myOracle{
	private $ONTS = array();
	/*
	 array of the following info address_id,mac,model_id,uptime(added in ONT_Zhone class);
	*/
	public function __construct(){
		parent::__construct();
	}
	
	private function scrubValue($val){
			if(count($tmp = explode('\"',$val)) > 1){
				$newval = $tmp[1];
			}elseif(count($tmp = explode('"',$val)) > 1){
				$newval=  $tmp[1];
			}elseif(count($tmp = explode(':',$val)) > 1){
				$newval = implode(':',array_slice($tmp,0,count($tmp)-2));
			}else{
				$newval= $val;
			}
			return $newval;
		}
	
	private function pullOids($model_id,$descr){
		$descr = rtrim($descr);
		$data = array();
		//echo 'We are in the oids<br>';
		$sql = "select o.oid,st.model,o.descr from 
					blackr.oids o, 
					blackr.oid_to_switch os,
					(select model_id,attribute1||' '|| attribute2 model from inv.model  where attribute1 = 'Zhone') st 
				where 
					os.SNMP_ID = o.SMNP_ID and
					os.model_id = st.model_id and
					o.DESCR like '$descr' and st.model_id = $model_id";
		//echo "$sql <br>";
		$r = $this->runQuery($sql);	
		
		while($row = oci_fetch_row($r)){			
			$rec = array('oid' 	=> $row[0],
						'model'	=> $row[1],
						'descr'	=> $row[2]);
			array_push($data,$rec);
		}		
		return $data;
	}
	
	private function pullActiveZhones(){
		$sql = "select 
					a.address_id,
					o.short_name isp,
					a2.attribute2 mac,
					m.model_id
				from
					osp.address a,
					inv.asset_status a1,
					inv.asset a2,
					inv.model m,
					(select distinct address_id,subscriber_id from sm.service where service_end_date is null) s,
					sm.subscriber sb,
					sm.organization o
				where
					a.address_id = s.address_id and
					s.subscriber_id = sb.subscriber_id and
					sb.service_provider_id = o.organization_id and
					a.address_id = a1.address_id and
					a1.asset_id = a2.asset_id and
					a2.model_id = m.model_id and
					m.attribute1 = 'Zhone' and
					a1.STATUS = 1 and
					a1.status_date = (select max(status_date) from inv.asset_status where address_id = a1.address_id)
				order by a.address_id";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('address_id' 	=> $row['ADDRESS_ID'],
						'isp'			=> $row['ISP'],
						'mac'			=> $row['MAC'],
						'model_id'		=> $row['MODEL_ID']);
			array_push($this->ONTS,$rec);
		}
				
	}
	
	private function pullSingleONT($address_id){
		$sql ="select 
					a.address_id,
					o.short_name isp,
					a2.attribute2 mac,
					m.model_id
				from
					osp.address a,
					inv.asset_status a1,
					inv.asset a2,
					inv.model m,
					(select distinct address_id,subscriber_id from sm.service where service_end_date is null) s,
					sm.subscriber sb,
					sm.organization o
				where
					a.address_id = s.address_id and
					s.subscriber_id = sb.subscriber_id and
					sb.service_provider_id = o.organization_id and
					a.address_id = a1.address_id and
					a1.asset_id = a2.asset_id and
					a2.model_id = m.model_id and
					m.attribute1 = 'Zhone' and
					a1.STATUS = 1 and
					a1.status_date = (select max(status_date) from inv.asset_status where address_id = a1.address_id)
					and a.address_id = ".$address_id."
				order by a.address_id";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('address_id' 	=> $row['ADDRESS_ID'],
						'isp'			=> $row['ISP'],
						'mac'			=> $row['MAC'],
						'model_id'		=> $row['MODEL_ID']);
			array_push($this->ONTS,$rec);
		}
	}
	
	private function addONT($ont){
		$sql = "insert into blackr.ontdevice values('".$ont['mac']."',".$ont['address_id'].",".$ont['model_id'].",'".$ont['uptime']."')";
		$r = $this->RunInsert($sql);
		if($r == 1 )return 1;
		else return -1;
	}
	
	private function checkONT($ont){
		$sql = "select count(1) from blackr.ontdevice where mac= '".$ont['mac']."'";
		$r = $this->runQuery($sql);
		$row = oci_fetch_row($r);
		if($row[0] > 0) return 1;
		else return $this->addONT($ont);
	}
	
	private function breakOutData($ont){
		$x = 1;
		$port = array(); //place holder for port data to be added to ont
		//use loop to allow us to work through all the ONT ports
		while ($x < count($ont['ports'])){
			//Assign the next port number
			$port['pdesc'] = $ont['ports'][$x]['pdescr'];
			//Find the vlan that matches the port description and assign it to the port array
			foreach($ont['vlans'] as $v){				
				if($v['pdescr'] == $port['pdesc']){					
					$port['vlan'] = $v['vlan'];					
					break;
				}
			}
			//Get the Speed for the port
			$port['speed'] = $ont['speed'][$x]['speed'];
			//Get the port status 
			$port['up'] = $ont['up'][$x]['updown'];
			$port['in'] = $ont['in'][$x];
			$port['out'] = $ont['out'][$x];
			$port['mac'] = $ont['mac'];
			print_r($port);
			echo '<br>';
			$this->addPortRec($port);
			$x++;
		}							
		
		return 1;
	}
	
	private function addPortRec($port){
		//Build SQL and run insert
		$insert = "insert into blackr.ontport values('".$port['mac']."',sysdate,'".$port['pdesc']."',".$port['speed'].",'".$port['up']."',".$port['vlan'].",".$port['in'].",".$port['out'].")";
		return  $this->runInsert($insert);		
	}
	
	public function getAllActiveZhones(){
			$this->pullActiveZhones();
			return $this->ONTS;
	}
	public function pushONT($ont){
		$check = $this->checkONT($ont);
		if($check == 1){
			return $this->breakOutData($ont);
		}
	}
	public function getSingleONT($address_id){		
		$this->pullSingleONT($address_id);
		return $this->ONTS;
	}
	public function getoids($model_id,$descr){
		return $this->pullOids($model_id,$descr);
	}
	public function RunscrubValue($val){
		return $this->scrubValue($val);
	}
}