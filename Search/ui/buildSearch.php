<?php
function encodeme(&$item){
	$item = rawurlencode($item);
}
function buildSearch($input){
	$input = json_decode($input,true);
	$post = $input['post'];	
	$verb = $input['verb'];
	$URL = 'http://192.168.253.13/Applications/Search/address_search/'.$verb.'/';
	if($post === 'POST'){
		$content = json_encode($input['content']);
		/*$URL = urlencode($URL);
		$curl = curl_init($URL);
		curl_setopt($curl,CURLOPT_URL,$URL);
		curl_setopt($curl,CURLOPT_POST,TRUE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);*/
		
	}else {
		array_walk($input['content'],'encodeme');
		$URL .= implode('/',$input['content']);
		
		/*$curl = curl_init($URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_URL,$URL);*/
	}
	
	//$resutls = curl_exec($curl);
	//echo $URL.'<br>';
	$results = file_get_contents($URL);	
	return $results;
	//curl_close($curl);
}
?>