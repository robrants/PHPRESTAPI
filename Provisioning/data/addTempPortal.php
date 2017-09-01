<?
class addTempPortal extends myOracle{
	private $hut = array();
	private $devid;
	public function __construct(){
		parent::__construct();		
	}
	
	private function addHut($footprint,$ip){
		$sql = "insert into osp.TEMPDEVICES values(osp.seq_tmpdevice.nextval,'".$footprint."','".$ip."')";
		return $r = $this->runInsert($sql);
	}
	public function pushhut($input){
		return $this->addHut($input[0],$input[1]);
	}
	
}
?>