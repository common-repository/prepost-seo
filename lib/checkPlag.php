<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


function pps_checkPlag()
{

	// $_D['docView'] = '';
	// $_D['match'] = '';
	// $_D['plagP'] = 50;
	// $_D['uniqueP'] = 50;
	// $_D['paraPercent'] =  25;
	// echo json_encode($_D);
	// exit;

	$actionSite = PPS_ACTION_SITE;
	$query = @$_POST['query-list'];
	$data = array(
		"data" => $query,
		"key" => @$_POST['key'],
		"ignore" => get_site_url()
	);
	$fields = http_build_query($data);
	$target = $actionSite.'apis/checkPlag';
	//$target = 'http://192.168.100.78/proj/prepostseo/apis/checkPlag';
	$response = wp_remote_post( $target, array(
		'timeout' => 4500,
		'body' => $data,
		)
	);

	if ( is_wp_error( $response ) ) {
		echo 'ERROR: ' . $response->get_error_message();
		exit;
	 }
	 
	if ($response['response']['code'] == 200) {
		//$totalU = 0;
		$response = json_decode($response['body']);
		// print_r( '<pre>' );
		// print_r( $response );
		// exit;
		$docHtml = '';
		$alertHtml = '';
		$matchHtml = '';
		if(!empty($response->error) && $response->error == "Account Limit Reached"){
			$_D['error'] = "2";
			$data = trim($query);
			$replaceThis = array("'", '"', '“', '”', ",");
			$replaceWith = array(" "," "," "," ", " ");
			$data = str_replace($replaceThis, $replaceWith, $data);
			
			$text = preg_replace("/[\r\n]+/", " ", $data);
			$text = preg_replace("/\s+/", ' ', $text);
			
			$Arr1 = preg_split('/([.?!])\s*(?=[a-z]|[A-Z])/', $text);
			//$words = explode(" ", $data);
			$queries = array();
			
			$tmpVar = '';
			foreach($Arr1 as $key => $s)
			{
				$queryVal = $s;
				if(strlen($s) > 95){
					$queryVal = substr($queryVal,0,93).'....';
				}
				$alertHtml .= "<div class='alert alert-danger my-alert limit-reach'><p>".$queryVal."</p><a class='comp-btn btn btn-default btn-xs'  href = 'https://www.google.com/search?q=\"".str_replace(" ","+",$queryVal)."\"' target='_blank'>Check Manually</a></div>";	
				$tmpVar .= $queryVal." ";
				if(strlen($tmpVar) > 55)
				{
					$queries[] = preg_replace("/\s+/", ' ', trim($tmpVar));
					$tmpVar = '';
				}
			}
			
			$_D['data']['display1'] = $alertHtml;
			$_D['data']['display2'] = '<b>Queries limit Reached!</b> We know you hate it, but checking plagiarism consumes high resources.<br>Still, you can check plagiarism free at <a target="_blank" href="https://www.prepostseo.com/plagiarism-checker">prepostseo</a> or check plagiarism of each sentence manually below';
			
			$_D['data']['display3'] = '<div class="tab plag-limit-reach-btn">';
			$_D['data']['display3'] .= '<a  title="'.PPS_ACTION_SITE.'" class="btn check-site-btn" href="javascript:void(0)">Check From Site</a>';
			$_D['data']['display3'] .= '<a class="btn"  id="manual-check" href="javascript:void(0)">Manual Checks</a>';
			$_D['data']['display3'] .= '<a target="_blank" class="btn upgrade-pro-btn" href="https://www.prepostseo.com/plans">Upgrade To Pro</a>';
			$_D['data']['display3'] .= '</div>';

			echo json_encode($_D);
			exit;
		}
		if(!empty($response->error) && $response->error == "Error in verification"){
			$_D['error'] = "1";
			echo json_encode($_D);
			exit;
		}
		if(!empty($response->error)){
			$_D['error'] = "1";
			$_D['message'] = $response->error;
			echo json_encode($_D);
			exit;
		}
		
		if(!empty($response->details)){
			foreach($response->details as $k => $r){
				$queryVal = $r->query;
				if(strlen($r->query) > 95){
					$queryVal = substr($queryVal,0,93).'....';
				}
				if($r->unique == "true")
				{	
					$docHtml .= '<span id="'.$k.'" class="match unique-match">' .$r->query. '</span> ' ;
				} 
				else if($r->paraphrase == "true")
				{
					$docHtml .= '<span id="'.$k.'" class="match inexact-match active">' .$r->query. '</span> ';
				}
				else
				{
					$docHtml .= '<span id="'.$k.'" class="match exact-match">' .$r->query. '</span> ';
				}
			}
		}
		if($response->sources){

			if($response->totalQueries){
				$uparts=100/$response->totalQueries;
				$uparts = round($uparts,2);
			}

			foreach($response->sources as $key => $links){
				if($key < 10){
					$newpercent = $uparts*$links->count;
					$newpercent = round($newpercent);
					if($newpercent < 1)
					{
						$newpercent = "< 1";
					}
					$matchHtml .= '<tr><td><span class="title"><a target="_blank" href="'.$links->link.'">'.$links->link.'</a></span></td><td class="s_p">'.$newpercent.'%</td></tr>';
				}
				
			}
		}else{
			$matchHtml = "<h3>No Match Resources Found</h3>";
		}
		
		$_D['paraPercent'] =  $response->paraphrasePercent;
		$_D['match'] = $matchHtml;
		$_D['uniqueP'] = $response->uniquePercent;
		$_D['plagP'] = $response->plagPercent;
		$_D['docView'] = html_entity_decode(str_replace('&amp;lt;br&amp;gt;', '&lt;br&gt;', $docHtml));
	}else{
		$_D['error'] = "1";
	}
	
	echo json_encode($_D);
	exit;
}