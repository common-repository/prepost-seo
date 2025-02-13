<?php 
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__))
	die('Direct Access not permitted...');


function pps_checkReadability()
{

	$text = @$_POST['data'];
	
	$_res = [];
	$_res['error'] = "0";
	
	try{


		$readability = readability($text);
		$_res['result'] =  $readability;		

	}catch(Exception $e){

		$_res['error'] = "1";
		$_res['result'] = "something wrong happened";
		$_res['message'] = json_encode($e);
	}
	
	echo json_encode($_res);
	exit;
	
}


function readability($text){
	$text = trim($text);
	$chars = strlen(preg_replace("/[^a-zA-Z0-9]+/", "",$text));
	$text_nostopwords = removeCommonWords($text);
	$words = str_word_count($text);
	$uniqueWords = count(array_unique(str_word_count($text, 1)));
	$keywords = str_word_count($text_nostopwords);
	$uniqueKeyWords = count(array_unique(str_word_count($text_nostopwords, 1)));
	$kwRatio = round(($keywords/$words)*100, 1);
	
	$sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $text);
	$totalS = count($sentences);
	
	
	$mapping = array_combine($sentences, array_map('strlen', $sentences));
	$maxK = array_keys($mapping, max($mapping));
	$longestSentence = $maxK[0];
	$longSWords = str_word_count($longestSentence);
	//echo $longstSentence;
	
	$syllables = beliefmedia_count_syllables($text);
	
	$FleschKincaidReadingEase = 206.835 - 1.015 * ($words/$totalS) - 84.6 * ($syllables/$words);
	$FleschKincaidGradeLevel = 0.39 * ($words/$totalS) + 11.8 * ($syllables/$words) - 15.59;
	$AutomatedReadabilityIndex = 4.71 * ($chars/$words) + 0.5 * ($words/$totalS) - 21.43;
	$ColemanLiauIndex = 5.89 * ($chars/$words) - 0.3 * ($totalS/$words) - 15.8;
	
	
	$readingTime = round($words/200);
	if($readingTime < 1){
	$readingTime = 0.5;
	}
	
	$speakingTime = round($words/125);
	if($speakingTime < 1){
	$speakingTime = 0.5;
	}
	
	$_D = array();
	
	$_D['totalWords'] = $words;
	$_D['totalWordsUnique'] = $uniqueWords;
	$_D['keywords'] = $keywords;
	$_D['keywordsUnique'] = $uniqueKeyWords;
	$_D['stopWords'] = $words - $keywords;
	$_D['kwRatio'] = $kwRatio;
	$_D['chars'] = $chars;
	$_D['charsPerWord'] = round($chars/$words, 1);
	$_D['sentences'] = $totalS;
	$_D['longestSentence'] = $longestSentence;
	$_D['longestSentenceWords'] = $longSWords;
	$_D['wordsPerSentence'] = round($words/$totalS, 1);
	$_D['syllables']= $syllables;
	$_D['syllablesPerWord']= round($syllables/$words, 1);
	$_D['syllablesPerSentence']= round($syllables/$totalS, 1);
	$_D['FleschKincaidReadingEase'] = round($FleschKincaidReadingEase, 1);
	$_D['FleschKincaidGradeLevel'] = round($FleschKincaidGradeLevel, 1);
	$_D['AutomatedReadabilityIndex'] = round($AutomatedReadabilityIndex, 1);
	$_D['ColemanLiauIndex'] = round($ColemanLiauIndex, 1);
	$_D['avarageIndex'] = round(($ColemanLiauIndex+$AutomatedReadabilityIndex)/2, 1);
	$_D['readingTime'] = $readingTime;
	$_D['speakingTime'] = $speakingTime;

	return $_D;
	
	// For Future: Vocabulary URL
	// http://www.manythings.org/vocabulary/lists/n/
	//
	}
	
	
	
	function beliefmedia_count_syllables($word) {
	
	$subsyl = Array('cial','tia','cius','cious','giu','ion','iou','sia$','.ely$');
	$addsyl = Array('ia','riet','dien','iu','io','ii','[aeiouym]bl$','[aeiou]{3}','^mc','ism$','([^aeiouy])\1l$','[^l]lien','^coa[dglx].','[^gq]ua[^auieo]','dnt$');
	
	// / Based on Greg Fast's Perl module Lingua::EN::Syllables /
	$word = preg_replace('/[^a-z]/is', '', strtolower($word));
	$word_parts = preg_split('/[^aeiouy]+/', $word);
	
	foreach ($word_parts as $key => $value) {
	if ($value <> '') {
	$valid_word_parts[] = $value;
	}
	}
	
	$syllables = 0;
	foreach ($subsyl as $syl) {
	if (strpos($word, $syl) !== false) {
	$syllables--;
	}
	}
	
	foreach ($addsyl as $syl) {
	if (strpos($word, $syl) !== false) {
	$syllables++;
	}
	}
	
	if (strlen($word) == 1) {
	$syllables++;
	}
	
	$syllables += count($valid_word_parts);
	$syllables = ($syllables == 0) ? 1 : $syllables;
	
	return $syllables;
}

function removeCommonWords($input){     
	// EEEEEEK Stop words
	$commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');
		
	return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$input);
}