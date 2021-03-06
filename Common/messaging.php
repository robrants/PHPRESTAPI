<?php

class messaging extends myOracle{
	
	public function __construct(){
		parent::__construct();
	}

	public function notifyNoc($subject,$message){
		$to = 'NOC@utopiafiber.com';
		
		return mail($to,$subject,$message);
	}
	
	public function notifyEngineer($subject,$message){
		$to = 'engineering@utopiafiber.com';
		return mail($to,$subject,$message);
	}
	
	private function SMS($toNumber,$message){
		$TEXT = ([
			'to' =>	$toNumber,
			'from' => '12059202967',
			'text' => $message
		]);
		
		$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic('e02c3e8d','5491dc0d0e165976'));
		$response = $client->message()->send($TEXT);
								   
								   
		
		return json_decode($response,true);		
	}
	
	public function sendSMS($to,$message){
		return $this->SMS($to,$message);		
	}
	
	public function pushLog($message,$class,$method){
		//$DB = myOracle();
		$insert = "insert into blackr.applog (stamp,class,method,data) values(sysdate,'".$class."','".$method."','".$message."')";		
		return $this->runInsert($insert);		
	}
	
}
?>