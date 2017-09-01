<?php
	class CommonQueries extends myOracle{
		//Hold my Data
		private $data = array();
		private $r;
		//Constructors and Descructors
		public function __construct(){
			//Connect to Oracle
			parent::__construct();	
		}
		public function __destruct(){
			oci_free_statement($this->r);
			//parent::__destruct;
			
		}
		
		private function getResults($sql){
			$this->data = array();
			$this->r = $this->runQuery($sql);
			while($row = oci_fetch_array($this->r)){
				array_push($this->data,$row);	
			}	
		}
		
		//End Private functions
		
		public function getBusiness($b){ //$b will be either SBP or GOLD
			$sql = "select st.address_id, a2.attribute2 MAC
					from 
						BLACKR.SERVICE_TYPES st,
						sm.product p,
						(select distinct a1.address_id,a.attribute2 from
							inv.asset a,
							inv.asset_status a1,
							(select model_id from inv.model where asset_type = 2) m
						where
							a.asset_id = a1.asset_id
							and a.MODEL_ID = m.model_id
							and a1.status = 1) a2
					where
						st.product_id = p.product_id
						and st.address_id = a2.address_id
						and ST.LOOKUP_ID = 2
						and p.description like '%".$b."%'";
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getFootprint(){
			$sql = 'select distinct footprint_id,footprint_seq from osp.address order by footprint_id';
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getLandUse(){
			$sql = 'select distinct land_use from osp.address';
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getADID($a){
			$sql = 'select * from osp.address where address_id = '.$a;
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getDropTypes(){
			$sql = 'select distinct droptype from osp.address';	
			$this->getResults($sql);
			return $this->data;
		}
		
		public function checkTelco($a){
			$sql = 'select address_id,telco_address_id from osp.address_ids_with_telcos where address_id = '.$a;
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getMakeModel($at = 2){ //asset type $at can be values between 1 and 6 defaults to 2
			$sql = "select model_id,attribute1 make,attribute2 model from inv.model where asset_type = ".$at;
			$this->getResults($sql);
			return $this->data;
		}
		
		public function getswitchModel(){
			$sql = "select switch_model_id,manufacturer||' '||description mm from inv.switch_model";
			$this->getResults($sql);
			return $this->data;
		}
		
			
		
	}
?>