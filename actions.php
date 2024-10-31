<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


if(!empty($_POST['pps_check_broken']))
{
	include_once('lib/checkbroken.php');	
	if(!empty($_POST['url']))
	{
		if(validate_url($_POST['url']))
		{
			echo 'true'; exit;
		}
	}
	echo 'false'; exit;
}

if(!empty($_POST['pps_check_status']))
{
	include_once('lib/checkstatus.php');
	pps_checkStatus();
}

if(!empty($_POST['pps_check_density']))
{
	include_once('lib/density.php');	
	pps_checkDensity();
}

if(!empty($_POST['pps_check_readability']))
{
	include_once('lib/checkReadability.php');	
	pps_checkReadability();
}

if(!empty($_POST['pps_check_plag']))
{
	include_once('lib/checkPlag.php');
	pps_checkPlag();
}


if(!empty($_POST['pps_check_imgDetails']))
{
	include_once('lib/imgDetails.php');	
	pps_checkImageDetails();
}

if(!empty($_POST['pps_img_compress']))
{
	include_once('lib/imgDetails.php');	
	pps_compressImg();
}


if(!empty($_POST['pps_check_grammar']))
{
	include_once('lib/checkGrammar.php');	
	pps_checkGrammar();
}

if(!empty($_POST['pps_check_siteDetails']))
{
	include_once('lib/siteDetails.php');	
	pps_checkSiteSEO();
	exit;
}

if(!empty($_POST['pps_check_links_array']))
{
	include('lib/checkBroken.php');	
	checkLinksStatusArray();
}

if(!empty($_POST['pps_post_meta']))
{
	
	if(!empty($_POST['id']))
	{
		$arr = array(
			"plag" => $_POST['data'],
			"score" => $_POST['score'],
			"time" => time(),
			"docView" => $_POST['rView'],
			"ignoreUrl" => @$_POST['ignoreUrl'],
			"content" => $_POST['content']
		);
		$metaData = json_encode($arr);
		if (get_post_meta($_POST['id'],"pps_post_meta",true)) { 
			update_post_meta( $_POST['id'], 'pps_post_meta', $metaData );
		}else{
			add_post_meta( $_POST['id'], 'pps_post_meta', $metaData);
		}
	}
	exit;
}

