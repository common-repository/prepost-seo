<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');



function pps_checkImageDetails()
{
	$imgsArr = array(); 
	
	$data = json_decode(stripslashes($_POST['data']));
	
	foreach($data as $img)
	{
		$head = get_headers($img->url, 1);
		$data['size'] = round($head['Content-Length']/1024, 1);
		$data['type'] = $head['Content-Type'];
		$name = pathinfo($url, PATHINFO_BASENAME);
		$imgsArr[] = array(
			"alt" => $img->alt,
			"src" => $img->url,
			"size" => $data['size'],
			"type" => $data['type']
		);
	}
	echo json_encode($imgsArr);
	exit;
}

function pps_compressImg()
{
	
	$url = PPS_ACTION_SITE.'compressImg';
	$img = $_POST['url'];
	$data = array(
		"key" => $_POST['key'],
		"img" => $img
	);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,  $url );
	curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
	curl_setopt($ch , CURLOPT_HEADER , false);
	curl_setopt($ch , CURLOPT_POST , 1);
	curl_setopt($ch  , CURLOPT_SSL_VERIFYPEER , false);
	curl_setopt($ch , CURLOPT_POSTFIELDS , $data);
	$result = curl_exec($ch);
	
	$r = json_decode($result);
	
	$comImg = $r->output->url;	
	
	
	add_filter( 'intermediate_image_sizes', '__return_empty_array' );
	$tmp = download_url( $comImg );
	$file_array = array(
			'name' => 'ppscom-'.basename( $img ),
			'tmp_name' => $tmp
	);
	
	$id = media_handle_sideload( $file_array, 0 );
	$attachment_url = wp_get_attachment_url( $id );
	$data['newSrc'] = $attachment_url;
	
	echo json_encode($data); 
	
	exit;
}