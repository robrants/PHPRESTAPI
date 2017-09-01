<?php
class snmpData extends myOracle{
	use crudBuilder;
	public $switches = array();
	private $zones = array();
	public function __construct(){
		parent::__construct();
	}
//Below may need to be moved
	private function getResZones(){
		$DHCP = new UtopiaDHCP();
		$sql = "select s1.network_name ADS,s.address_id,p.description product,a2.attribute2 mac,m.attribute4 ports
				from
					osp.address a,
					sm.service s,
					sm.product p,
					SM.PRODUCT_CATEGORY pc,
					sm.category c,
					inv.asset_status a1,
					inv.asset a2,
					inv.model m,
					inv.switch s1
				where
					a.address_id = s.address_id and
					s.address_id = a1.address_id and
					s1.switch_id = a.switch_id and
					s.product_id = p.product_id and
					a1.asset_id = a2.asset_id and
					p.product_id = pc.product_id and
					pc.CATEGORY_ID = c.CATEGORY_ID and
					a2.MODEL_ID = m.MODEL_ID and
					c.LOOKUP_ID = 1 and
					p.product_type = 5 and					
					(a.ORGANIZATION_ID is null or a.ORGANIZATION_ID = 999) and 
					p.description like '%250%' and
					a2.model_id in (1153,1142,1149,1147,9006,9008,9015,9010) and
					a1.STATUS = 1 and
					a1.status_date = (select max(status_date) from inv.asset_status a3 where a1.address_id = a3.address_id) and 
					s.service_end_date is null
					order by 1";
		$r = $this->runQuery($sql);
		while ($row = oci_fetch_array($r)){
			$rec = array('address_id' 	=> $row['ADDRESS_ID'],
						 'ads'			=> $row['ADS'],
						'descr'			=> $row['PRODUCT'],
						'mac'			=> $row['MAC'],
						'ports'			=> $row['PORTS']);
			$rec['ip'] = $DHCP->getIP($rec['mac']);
			array_push($this->zones,$rec);			
		}				
	}

	public function getoids($model_id,$descr){
		$descr = rtrim($descr);		
		$data = array();
		$sql = "select o.oid,st.model,o.descr from 
					blackr.oids o, 
					blackr.oid_to_switch os,
					blackr.switchTypes st 
				where 
					os.SNMP_ID = o.SMNP_ID and
					os.model_id = st.model_id and
					o.DESCR like '$descr' and st.model_id = $model_id";		
		$r = $this->runQuery($sql);	
		
		while($row = oci_fetch_row($r)){			
			$rec = array('oid' 	=> $row[0],
						'model'	=> $row[1],
						'descr'	=> $row[2]);
			array_push($data,$rec);
		}		
		return $data;
	}
	
	public function getSwitchConn($id){
		$sql = "select ip_address,model_id,'ut0p1a5nmp' community from blackr.masterads where switch_id = $id";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			return array('ip' => $row['IP_ADDRESS'],'community' => $row['COMMUNITY'], 'model' => $row['MODEL_ID']);
		}
	}
	
	public function getmngFootprints(){
		$sql = 'select * from blackr.mngfootprints';
		$data = array();
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('serviceid' 	=> $row['SERVICEID'],
						'footprint'		=> $row['FOOTPRINTID']);
			array_push($data,$rec);
		}
		return $data;
	}
	
	public function getADSifIndex(){
		$sql = "select
					s.switch_id,
					s.ip_address
				from
					BLACKR.MASTERADS s,
					BLACKR.IFINDEX i
				where
					s.switch_id = i.switch_id(+) and					
					s.status = 'Active' and
					i.ifidxid is null";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('switch_id' 	=> $row[0],
						'ip'			=> $row[1]);
			array_push($this->switches,$rec);
		}
	}
	
	public function getNetworks(){
		$sql = "select switch_id,network_name from blackr.masterads where status = 'Active' order by 2";
		$data = array();
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('switch_id' => $row['SWITCH_ID'],
						'network_name' => $row['NETWORK_NAME']);
			array_push($data,$rec);
		}
		return $data;
	}
	
	public function modelidToDevType($model){
		$sql = "select * from blackr.devtype where model_id = ".$model;
		$r = $this->runQuery($sql);
		$row = oci_fetch_row($r);
		return $row[1];
	}
	
	public function pullExceptions($oids,$firmware){
		
		//Check for oids that have changed in new versions that are not yet universally deployed
		
		$sql = 'select oe.modeltype,oe.oid,oe.firmware from 
					blackr.oidexceptions oe,
					blackr.oids o
				where
					o.SMNP_ID = oe.SNMPID and
					o.oid =';
		$f = explode('-',$firmware);
		$fware = explode('.',$f[2]);
		$x = 0;
		foreach($oids as $oid){
			$sql2 = $sql;
			$sql2 .= "'".$oid['oid']."'";
			$r = $this->runQuery($sql2);			
			while($row = oci_fetch_array($r)){				
				//explode the passed firmware to an array of the version for comparision with an explodes version of row['FIRMWARE']
				$f = explode('-',$row['FIRMWARE']);
				$efware = explode('.',$f[2]);
				if($fware[0] >= $efware[0]){
					error_log('oid to replace '.$oids[$x]['oid'].' with '.$row['OID'],0);
					$oids[$x]['oid'] = $row['OID'];
				}
			}
			$x++;
		}
		return $oids;
	}
	
	public function writeIfIndex($data){
		foreach($data as $d){
			$insert = $this->buildInsert('blackr.IFINDEX',$d,0);
			//echo $insert."<br>";
			$ret = $this->runInsert($insert);
			if($ret != true || $ret != 1){
				error_log($insert,0);
			}
		}
		return $ret;
	}
			
	public function getZones250(){
		$this->getResZones();
		return $this->zones;
	}
	
}
?>