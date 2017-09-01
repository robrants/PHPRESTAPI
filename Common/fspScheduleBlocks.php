<?php
class fspScheduleBlocks extends myOracle{
	private $techGroups = array();
	private $StartDate;
	private $CurrentDate;
	private $EndDate;
	private $numDays;
	private $holidays = array();
	private $holidayPosition = 0;
	const HOURS = 8;
	const HOLIDAYFILE = 'txtFiles/holiday.txt';
	
	public function __construct(){
		//Load Oracle
		parent::__construct();
		//Setup Holidays
		$tempholidays = file(self::HOLIDAYFILE);
		for($x=0;$x<count($tempholidays);$x++){
			$this->holidays[$x] = new DateTime($tempholidays[$x]);	
		}
	}
	
	private function setupRun($startDay,$numDays,$techGroup=0){
		$this->StartDate = new DateTime($startDay);
		$this->CurrentDate = $this->StartDate;
		$this->EndDate = new DateTime($startDay);
		$this->EndDate->modify('+'.$numDays.' day');
		if($techGroup === 0){
			$s = "select fsp_tech_group_id from sm.FSP_TECH_GROUP where fsp_status = 'A'";
			$r = $this->runQuery($s);
			while($row = oci_fetch_row($r)){
				array_push($this->techGroups,$row[0]);	
			}
			oci_free_statement($r);
		}elseif(is_array($techGroup)){
			$techGroup = implode(',',$techGroup);
			$s = "select fsp_tech_group_id from sm.FSP_TECH_GROUP where fsp_status = 'A' and fsp_tech_group in (".$techGroup.")";
			$r = $this->runQuery($s);
			while($row = oci_fetch_row($r)){
				array_push($this->techGroups,$row[0]);
			}
			oci_free_statement($r);
		}
		for($x=0; $x <= count($this->holidays); $x++){
			if($this->StartDate->format('m/d/Y') < $this->holidays[$x]->format('m/d/Y'))continue;
			else {
				$this->holidayPosition = $x;
				break;
			}
		}
	}
	
	private function addScheduleBlock(){
		for($x=0; $x<count($this->techGroups); $x++){
			//Set up the needed block dates and build out the inserts for each am and pm blocks by UNIT IDS
			$amStart = $this->CurrentDate->format('m/d/Y').' 8:00:00 AM';
			$amEnd = $this->CurrentDate->format('m/d/Y').' 12:00:00 PM';
			$pmStart  = $this->CurrentDate->format('m/d/Y').' 12:00:00 PM';
			$pmEnd	 = $this->CurrentDate->format('m/d/Y').' 5:00:00 PM';
			$s = "insert into sm.fsp_schedule_block (FSP_SCHEDULE_BLOCK_ID,FSP_TECH_GROUP_ID,BLOCK_START,BLOCK_END,HOURS,AVAILABLE,CREATED_BY,DATE_CREATED) VALUES(sm.SEQ_FSP_SCHEDULE_BLOCK.nextval,".$this->techGroups[$x].",to_date('".$amStart."','MM/DD/YYYY HH:MI:SS AM'),to_date('".$amEnd."','MM/DD/YYYY HH:MI:SS PM'),".self::HOURS.",".self::HOURS.",6150,sysdate)";
			//return $s;
			if($r = $this->runInsert($s)){
				$s = "insert into sm.fsp_schedule_block (FSP_SCHEDULE_BLOCK_ID,FSP_TECH_GROUP_ID,BLOCK_START,BLOCK_END,HOURS,AVAILABLE,CREATED_BY,DATE_CREATED) VALUES(sm.SEQ_FSP_SCHEDULE_BLOCK.nextval,".$this->techGroups[$x].",to_date('".$pmStart."','MM/DD/YYYY HH:MI:SS PM'),to_date('".$pmEnd."','MM/DD/YYYY HH:MI:SS PM'),".self::HOURS.",".self::HOURS.",6150,sysdate)";								
				$r =$this->runInsert($s);
			}else return false;
		}
	}
	
	public function CreateSchedule($input){
		//Break out the input and validate
		$start = $input['start'];
		$numdays = $input['numdays'];
		$techgroup = array();
		if(isset($input['techgroup']) && is_array($input['techgroup'])){
			$techgroup = $input['techgroup'];
			$this->setupRun($start,$numdays,$techgroup);
		}else $this->setupRun($start,$numdays);
		//Loop Through current day until we reach numdays and add blocks where needed
		while($this->CurrentDate <= $this->EndDate){
			$day = getdate($this->CurrentDate->format('U'));
			if($day['wday'] != 0){
				if($this->CurrentDate->format('m/d/Y') != $this->holidays[$this->holidayPosition]->format('m/d/Y')){
					$this->addScheduleBlock();
				}else if($this->holidayPosition < count($this->holidays)-1)$this->holidayPosition++; 
			}
			$this->CurrentDate->modify('+1 day');
		}
	}
}
?>