<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


function pps_checkDensity()
{
	// $actionSite = PPS_ACTION_SITE;
	// $data = array(
	// 	"data" => $_POST['data']
	// );
	// $fields = http_build_query($data);
	// $target = $actionSite.'frontend/checkDensityWP';
	
	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, $target); // Define target site
	// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Return page in string
	// curl_setopt($ch, CURLOPT_POST, TRUE);
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Follow redirects
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	// curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	// $page = curl_exec($ch);
	// echo $page;
	// exit;

	$text = @$_POST['data'];
	
	$density = getDensity($text, $stopwords = 0, $checkStopWords = true);
	echo json_encode($density);
	// echo json_last_error_msg ();
	exit;

}

class Ultimate
{
	var $minlength;
	var $minoc;
	
	//get text from html 
	function getOnlyText($text)
	{
		$text=strtolower($text);
		$text=str_replace("\n\r"," ",$text);
		$text=str_replace("\n"," ",$text);
		$text=str_replace("\r"," ",$text);
		//erasing scripts
		$tmp=$this->my_preg_match_all('<script','</script>',$text);
		foreach($tmp as $k=>$v) $text=str_replace($v,'',$text);
		//erasing styles
		$tmp=$this->my_preg_match_all('<style','</style>',$text);
		foreach($tmp as $k=>$v) $text=str_replace($v,'',$text);
		
		//erasing links because we don't count text from hrefs 
		$tmp=$this->my_preg_match_all('<a','</a>',$text);
		foreach($tmp as $k=>$v) $text=str_replace($v,'',$text);
		//erasing options from select 
		$tmp=$this->my_preg_match_all('<option','</option>',$text);
		foreach($tmp as $k=>$v) $text=str_replace($v,'',$text);
		
		
		$tmp=$this->my_preg_match_all('<','>',$text);
		foreach($tmp as $k=>$v) $text=str_replace($v,'',$text);
		//print_r($tmp);
		/*$text=str_replace('>','> ',$text);
		$text=str_replace('<',' <',$text);
		$text = strip_tags($text);
		$text = str_replace("<!--", "&lt;!--", $text);
		$text = preg_replace("/(\<)(.*?)(--\>)/mi", "".nl2br("\\2")."", $text);
		while($text != strip_tags($text)) {$text = strip_tags($text);}
		$text=ereg_replace('&nbsp;'," ",$text);
		$text = ereg_replace("[^[:alpha:]]", " ", $text);*/
		//by adnan
		//while(strpos($text,'  ')!==false) $text = ereg_replace("  ", " ", $text);
		while(strpos($text,'  ')!==false) $text = preg_replace("/\s\s/", " ", $text);		
		
		//echo $text;
		return $this->unhtmlentities($text);
	}
	function getNrWords($text)
	{
	
		//$text = ereg_replace("[^[:alnum:]]", " ", $text);
		$text = preg_replace("/[^[:alnum:]]/", " ", $text);		
		
		//byadnan
		//while(strpos($text,'  ')!==false) $text = ereg_replace("  ", " ", $text);
		while(strpos($text,'  ')!==false) $text = preg_replace("/\s\s/", " ", $text);
		$text=$string=strtolower($text);
		$text=explode(" ",$text);
		return count($text);
	}
	function getCounts($text)
	{
	
		//$text = ereg_replace("[^[:alnum:]]", " ", $text);
		$text = preg_replace("/[^[:alnum:]]/", " ", $text);
		//while(strpos($text,'  ')!==false) $text = ereg_replace("\s{2}", " ", $text);
		while(strpos($text,'  ')!==false) $text = preg_replace("/\s\s/", " ", $text);		
		$text=$string=strtolower($text);
		//echo $text;
		$text=explode(" ",$text);
		$keywords=array();
		$text=array_unique($text);
		$nr_words=$this->nr_cuvinte($string);
		foreach($text as $t=>$k)
		{
			if(!empty($k))
			{
				$nr_finds=$this->nr_gasiri($k,$string);	
				//here we will need to put min of the appearencies and min length
				if($nr_finds>=$this->minoc && strlen($k)>=$this->minlength) $keywords[$k]=$nr_finds;	
			}
		}
		arsort($keywords);
		return $keywords;
	}
	function getCounts_2($text)
	{

			function altEach(&$data)
			{
				$key = key($data);
				$ret = ($key === null)? false: [$key, current($data), 'key' => $key, 'value' => current($data)];
				next($data);
				return $ret;
			}

		//$text = ereg_replace("[^[:alnum:]]", " ", $text);
		$text = preg_replace("/[^[:alnum:]]/", " ", $text);
		//while(strpos($text,'  ')!==false) $text = ereg_replace("  ", " ", $text);
		while(strpos($text,'  ')!==false) $text = preg_replace("/\s\s/", " ", $text);		
		$text=$string=strtolower($text);
		$text=explode(" ",$text);
		$new_text=array();
		$i=0;
		foreach($text as $k=>$t)
		{
			if(strlen(trim($t))>0) $new_text[$i]=trim($t);
			$i++;
		}
		$text=$new_text;
		$keywords=array();
		//making array with 2 words
		while (list($key, $val) = altEach($text)) 
		{
			$tmp=$val;
			list($key, $val) = altEach($text);
			$tmp=$tmp." ".$val;
			if(!empty($tmp))
			{
				$nr_finds=$this->nr_gasiri($tmp,$string);
				if($nr_finds>=$this->minoc && strlen($tmp)>=2*$this->minlength) $keywords[$tmp]=$nr_finds;	
			}
		}
		arsort($keywords);
		return $keywords;
	}
	function getCounts_3($text)
	{
		//$text = ereg_replace("[^[:alnum:]]", " ", $text);
		$text = preg_replace("/[^[:alnum:]]/", " ", $text);
		//while(strpos($text,'  ')!==false) $text = ereg_replace("  ", " ", $text);
		while(strpos($text,'  ')!==false) $text = preg_replace("/\s\s/", " ", $text);		
		$text=$string=strtolower($text);
		$text=explode(" ",$text);
		$new_text=array();
		$i=0;
		foreach($text as $k=>$t)
		{
			if(strlen(trim($t))>0) $new_text[$i]=trim($t);
			$i++;
		}
		$text=$new_text;
		
		$keywords=array();
		//making array with 3 words
		while (list($key, $val) = altEach($text)) 
		{
			$tmp=$val;
			list($key, $val) = altEach($text);
			$tmp=$tmp." ".$val;
			list($key, $val) = altEach($text);
			$tmp=$tmp." ".$val;
			if(!empty($tmp))
			{
				$nr_finds=$this->nr_gasiri($tmp,$string);
				if($nr_finds>=$this->minoc && strlen($tmp)>=3*$this->minlength) $keywords[$tmp]=$nr_finds;	
			}
		}
		arsort($keywords);
		return $keywords;
	}
	
	function nr_cuvinte($str)
	{
		$tmp=0;
		$tok = strtok ($str," ");
		while ($tok) {
		$tmp++;
		$tok = strtok (" ");
		}
		return $tmp;
	}
	function nr_gasiri($key,$string)
	{
		$q=0;
		$nr=0;
		$key=strtolower($key);
		$string=strtolower($string);
		while($q==0)
		{
	
			$pos = strpos($string,$key);
			if ($pos===false) $q=1;
			else 
			{
				$string = substr ($string,$pos+strlen($key));
				$nr++;
			}
		}
		return $nr;
	}
	function my_preg_match_all($start,$end,$string)
	{
		$res=array();
		while(strpos($string,$start)!==FALSE && strpos($string,$end)!==FALSE)
		{
			$first=strpos($string,$start);
			$string=substr($string,$first);
			$last=strpos($string,$end);
			$res[]=substr($string,0,$last+strlen($end));
			$length=$last;
			$string=substr($string,$length);
		}
		return $res;
	}
	function unhtmlentities($string)
	{
	   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
	   $trans_tbl = array_flip($trans_tbl);
	   return strtr($string, $trans_tbl);
	}
}


function getDensity($textData = NULL, $stopwords = 0,$checkStopWords = true)
{
	$post=$_POST;
	$json=array();
	$error=false;
	$msg="";	
	$text="";
	$ultimate = new Ultimate();
	
	$ultimate->minoc=1;
	$ultimate->minlength=4; //(3+1)
	
	$res_counts=array();
	$json['results']=array();
	$total_words=0;
	$results=array();
	$no_of_words=3;
	$no_of_terms=5;
	$results_1 = array();
	$results_2 = array();
	$results_3 = array();
			
	$text= $ultimate->getOnlyText($textData);

		
	if(!$error && $text!="")
	{	
		if(empty($stopwords)){
			if($checkStopWords){
				$text = removeCommonWords($text);
			}
		}
		//end erasing stop words
		
		
		$total_words=$ultimate->getNrWords($text);	
								
		
			$res_counts_1 =$ultimate->getCounts($text);
			//getting 2 word
			$res_counts_2 =$ultimate->getCounts_2($text);
			//getting 3 word
			$res_counts_3 =$ultimate->getCounts_3($text);
		
		//////////////////////////////////
		if(count($res_counts_1) && $total_words > 0)
		{
			$i=0;
			foreach($res_counts_1 as $k=>$t)
			{
				
				$term=$k;
				$occurance=$t;
				$density=$t*100/$total_words;
				$density=round($density,2);
				$results_1[$i]['term']=$term;
				$results_1[$i]['count']=$occurance;
				$results_1[$i]['density']=$density;
				$i++;						
				
				if($i==$no_of_terms)
					break;
			}
		}
		
		if(count($res_counts_2) && $total_words > 0)
		{
			$i=0;
			foreach($res_counts_2 as $k=>$t)
			{
				
				$term=$k;
				$occurance=$t;
				$density=$t*100/$total_words;
				$density=round($density,2);
				$results_2[$i]['term']=$term;
				$results_2[$i]['count']=$occurance;
				$results_2[$i]['density']=$density;
				$i++;						
				
				if($i==$no_of_terms)
					break;
			}
		}
		
		if(count($res_counts_3) && $total_words > 0)
		{
			$i=0;
			foreach($res_counts_3 as $k=>$t)
			{
				
				$term=$k;
				$occurance=$t;
				$density=$t*100/$total_words;
				$density=round($density,2);
				$results_3[$i]['term']=$term;
				$results_3[$i]['count']=$occurance;
				$results_3[$i]['density']=$density;
				$i++;						
				
				if($i==$no_of_terms)
					break;
			}
		}
										
	}
	else
	{
		$error = 1;
		$msg = 'Invalid Input';
	}
	$json['results_1']=$results_1;
	$json['results_2']=$results_2;
	$json['results_3']=$results_3;
	$json['error']=$error;
	$json['msg']=$msg;
	return $json;
		
}


function removeCommonWords($input){     
	// EEEEEEK Stop words
	$commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');
		
	return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);
}