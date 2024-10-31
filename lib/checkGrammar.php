<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');

function pps_checkGrammar()
{
	$actionSite = "https://languagetool.org/api/v2/check";
	$data = array(
		"text" => @$_POST['data'],
		"language" => @$_POST['language']
	);
	$fields = $data;
	$target = $actionSite;
	$response = wp_remote_post( $target, array(
		'timeout' => 4500,
		'body' => $data
		)
	);

	$_D = [];
	$_D['error'] = "0";

	if($response['response']['code'] == 200){

		$grammar = json_decode($response['body'], true);
		
		if(is_array($grammar['matches'])){
			$_D['result'] = sizeof($grammar['matches']);
		}

	}else{
		$_D['error'] = "1";
		$_D['result'] = "something wrong happened";
	}

	echo json_encode($_D);
	exit;
}