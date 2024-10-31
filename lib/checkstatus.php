<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


function pps_checkStatus()
{
	$actionSite = PPS_ACTION_SITE;
	$data = array(
		"key" => @$_POST['key'],
		"v" => @$_POST['v']
	);
	$fields = $data;
	$target = $actionSite.'frontend/checkAccountStatus';
	
	$response = wp_remote_post( $target, array(
		'body' => $data,
		)
	);
	if($response['response']['code'] == 200){
		echo $response['body'];
	}else{
		echo "something wrong happened";
	}
	exit;
}