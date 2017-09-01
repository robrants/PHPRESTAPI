<?php

class firmWareMonitor extends firmWareData{
	private $queu = array('currentJobsWaiting'=>0,'currentJobsReady'=>0);
	private $monitored;
	
	public function __construct(){
		parent::__construct();
	}
	
	private function pullQue($q){
		$tube = new \Pheanstalk\Pheanstalk('127.0.0.1');
		$queu = $tube->statsTube('firmware');
		if(count($this->queu)>= 1){
			$this->queu['currentJobsWaiting'] = $queu['current-watching'];
			$this->queu['currentJobsReady'] = $queu['current-jobs-ready'];
		}
	}
	
	public function pullJobStats($input){
		$input = json_decode($input,true);
		$this->pullQue($input['tube']);
		if(!isset($input['rows'])) $input['rows'] = 50;
		if(!isset($input['stamp'])) $input['stamp'] = 'sysdate';
		$this->monitored = $this->pullCompletedUpgrades($input['rows'],$input['stamp']);
		$output = array();
		$output['queu'] = $this->queu;
		$output['completed'] = $this->monitored;
		return $output;
	}
}

?>