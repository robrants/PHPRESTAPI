<?php
	//namespace Reports\data;
	use \Illuminate\support\Collection;
	//use Common\myOracle;
	class custCountFootprint {
	protected $data = array();
	protected $footprintArray = array();
	
	protected $sql = "
			select 
				address_id,
				custtype,
				footprint_id,
				org				
			from
				(select
					distinct s1.address_id,
					s1.custType,
					a.FOOTPRINT_ID,
					case when a.ORGANIZATION_ID = 1001 then 'Utopia' else 'UIA' end Org
				from
					(
					select
						address_id,
						case when c.lookup_id = 1 then 'Residential' else 'Business' end custType
					from
						sm.service s,
						sm.product p,
						SM.PRODUCT_CATEGORY pc,
						sm.category c
					where
						s.product_id = p.product_id
						and p.product_id = pc.product_id
						and pc.category_id = c.category_id
						and s.service_end_date is null
					order by address_id) s1,
					osp.address a
				where
					a.address_id = s1.address_id 
				order by 3,1
				)			
			order by 3,1";
		protected $conn;
		
		public function __construct(){
			$this->conn = new myOracle();
		}
		
		public function __destruc(){
			$this->conn = NULL;
		}
		
		public function getData(){
			$results = $this->conn->runQuery($this->sql);
			while($row = oci_fetch_array($results)){
				$rec = array(
					'address_id' 	=> $row['ADDRESS_ID'],
					'CustType'		=> $row['CUSTTYPE'],
					'footprint_id'	=> $row['FOOTPRINT_ID'],
					'org'			=> $row['ORG'],
					'total'			=> $row['TOTAL']
				);
				array_push($this->data,$rec);
			}
		}
		
		public function formatData(){
			$collection = collect($this->data); //make collection
			$footprints = $collection->pluck('footprint_id')->unique()->toArray();
			
			$x = 0;
			foreach($footprints as $footprint){ //rearrange data into proper group counts
				$pass = 0;
				foreach($this->data as $rec){
					if(($rec['footprint_id'] === $footprint) and ($pass === 0)){
						$this->footprintArray[$x]['footprint'] = $footprint;
						$pass = 1;
					}elseif(($rec['footprint_id'] === $footprint) and ($pass === 1)){
						if($rec['org'] == 'Utopia'){
							if($rec['CustType'] == 'Business'){
								$this->footprintArray[$x]['Utopia']['Business'] += 1;
							}else{
								$this->footprintArray[$x]['Utopia']['Residential'] += 1;
							}
						}else{
							if($rec['CustType'] == 'Business'){
								$this->footprintArray[$x]['UIA']['Business'] += 1;
							}else{
								$this->footprintArray[$x]['UIA']['Residential'] += 1;
							}
						}						
					}					
					
				}
			$x++;
			}				
		
			return $this->footprintArray;
		}
	}
?>