<?php
class zabbix_interface{
	private $ZABBIX_URL = 'http://10.250.255.213/zabbix/api_jsonrpc.php';
	private $ZABBIX_USER = 'Admin';
	private $ZABBIX_PASS = 'zabbix';
	private $request_opts = array(); //Holds all http request header options
	private $request_content = array();	
	private $request_id = 1;
	private $auth_token;
	public function __construct(){
		$this->request_opts = array('http' => array('protocol => 1.1,method' => 'POST',
							  'header' 	=> 'Content-Type: application/json-rpc'));
		$this->request_content = array('jsonrpc' => '2.0',
									  'method'	=> 'user.login',
									  'params'	=> array('user' 	=> $this->ZABBIX_USER,
														'password'	=> $this->ZABBIX_PASS),
									  'id'		=> '1'
									  );
		$content = json_encode($this->request_content);
		$this->request_opts['http']['content'] 	= $content;
		$context = stream_context_create($this->request_opts);
		$results = json_decode(file_get_contents($this->ZABBIX_URL,false,$context),true);
		$this->request_id++;
		$this->auth_token = $results['result'];
		//echo $this->auth_token;
	}
	public function showToken(){echo $this->auth_token;}
	
	public function runAPICall($method,$parms){
		$this->request_opts = array('http' => array('protocol => 1.1,method' => $method,
							  'header' 	=> 'Content-Type: application/json-rpc'));
		$this->request_content = $parms;
		$this->request_content['id'] = $this->request_id;
		$this->request_opts['http']['content'] 	= json_encode($this->request_content);
		$context = stream_context_create($this->request_opts);
		$results = json_decode(file_get_contents($this->ZABBIX_URL,false,$context),true);
		$this->request_id++;
		return $results;
	}
	
	
}
?>