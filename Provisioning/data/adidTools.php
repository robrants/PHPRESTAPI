<?php
class adidTools extends myOracle{
	
	public function __construct(){
		parent::__construct();
	}
	
	private function setDropType($adid,$dtype){
		$sql = "update osp.address set drop_type = '".$dtype."' where address_id = ".$adid;
		error_log($sql,0);
		return $this->runInsert($sql);
	}
	
	public function pushDropType($input){
		$input = json_decode($input,true);
		return $this->setDropType($input['adid'],$input['droptype']);
	}
	
	public function pulldrop_types(){
		$sql = 'select distinct drop_type as drop_type from osp.address where drop_type is not null';
		$dropType = array();
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('droptype' => $row['DROP_TYPE']);
			array_push($dropType,$rec);
		}
		return $dropType;
	}
}
?>