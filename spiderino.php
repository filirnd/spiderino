<?php

/* sudo chmod -R 777 /var/www

-calcolo profondità
-usare md5 per gli url
-visitare prima gli url dello stesso sito? */

require ("libs/database.php");

ini_set('memory_limit', '1024M');

/*init database */
Database::createDb();
Database::createTable();

/* test insert func*/
/*
$fromDb = Database::insert(10,"www.son.com","www.father.com",2,32); 
if ($fromDb == 0){
	echo "Url non ripetuto \n";
	

}else {
	echo "Url ripetuto non devo parsarlo \n";
}
*/

$url_queue = array();

$argv = $_SERVER["argv"];
$argc = count($argv);

if( $argc < 5){
		echo "Error: too few arguments!\nUsage: php ./spiderino SEED1 [SEED2 SEED3 .. ] -t TIME_SIM KEYWORD1 [KEYWORD2 KEYWORD3 .. ]\n";
 		return 0;
	}

$start_seed =  $argv[1]; /* initial url seed */

if($argv[2] !== '-t'){
		echo "Error: wrong arguments!\nUsage: php ./spiderino SEED1 [SEED2 SEED3 .. ] -t TIME_SIM KEYWORD1 [KEYWORD2 KEYWORD3 .. ]\n";
 		return 0;
	}
	
$simulationTime = $argv[3] * 60;

$key = $argv[4];

$delay = 0; /* default call delay second */

$timeStart  = strtotime(date("d-m-Y h:i:s"));


/*
if(isset($_SERVER["argv"][2])) {
    $delay = $_SERVER["argv"][2]; /* set delay with start arg  

}*/

if (!is_dir('output')) {
	mkdir('output', 0777, true);
}

$idURL = 1;

/*read first seed url and add it in queue*/
/*echo "URL SEED ".$start_seed." \n";
echo "QUEU".count($url_queue)."\n";
*/

/*Clean seed url*/
$start_seed = clean_url($start_seed);
	if(check_file_ext($start_seed) == 0) /*Check if url is valid*/
		return 0;

/*Query to insert seed url in DB*/
$fromDb = Database::insert(NULL, $start_seed, "Initial seed Url", NULL, NULL); 

/*Parse seed url to find new Urls*/
readUrls($start_seed,$url_queue);

$index = 0;

/* Loop readPage while there is element in queue */
while($index < count($url_queue)) {
	$timeActual = strtotime(date("d-m-Y h:i:s"));
	$differenceInSeconds = $timeActual - $timeStart;
	if($differenceInSeconds > $simulationTime) {
		echo "Simulation completed! Exiting...\n";
		break;
	}
		
	//echo ("-----WHILE ENTER WITH INDEX:".$index."\n");
	readUrls($url_queue[$index],$url_queue);
	$index++;
	//sleep($delay);

}

/* function that keep urls in &$queue from a page $siteUrl*/

function readUrls($siteUrl, &$queue){
	
	global $idURL, $key, $argc, $argv;
	//$siteUrl = clean_url($siteUrl);
	//if(check_file_ext($siteUrl) == 0) /*Check if url is valid*/
	//	return 0;

	echo "Try getting url: ".$siteUrl."\n";
	$result = file_get_contents($siteUrl); /* download page */

	/*check if page is not empty*/
	if( $result )
	{
		echo "Start Parsing url: ".$siteUrl."\n";
      	
		/* check if there is url in siteUrl */
		preg_match_all( '/<a.+?href="((http:\/\/|https:\/\/|\/|)[a-zA-Z0-9].+?)"/', $result, $urlmatch, PREG_SET_ORDER );

		/*Extract domain url (actual folder)*/
		$domain = $siteUrl;
		$pos = strrpos( substr($siteUrl, 7), '/');
		if ($pos !== false) {
			$domain = substr($siteUrl, 0, 7 + $pos);
		}
		//echo ">> dominio  ".$domain."\n";

		/*Extract main domain url*/
		$firstDomain = substr($siteUrl,7);
		$pos = strstr($firstDomain, '/', true);
		if ($pos !== false) {
			$firstDomain = "http://".$pos;
			//echo ">> dominio first  ".$firstDomain."\n";
		}
		//$firstDomain = "http://".strstr($firstDomain, '/');

		$nURLFounded = 0;

		foreach( $urlmatch as $item ) /*Add founded urls in queue*/
		{
			$tempUrl = $item[1];
			print_r("Found > " .$tempUrl. " \n");

            //$domain = substr(strrchr($siteUrl, "."), 1);
            //$domain = substr($siteUrl, 0, strrpos( substr($siteUrl, 0, 7), '/') );

            if (substr($tempUrl, 0, 1) === '/') //quando c'è lo slash vuol dire che devo aggiungere il primo livello senza cartelle
            $tempUrl = $firstDomain.$tempUrl;
            else if(substr($tempUrl, 0, 4) !== 'http')
            	$tempUrl = $domain."/".$tempUrl;
            
            $tempUrl = clean_url($tempUrl);
            if(check_file_ext($tempUrl) == 1) { 

	            /*Query to insert url in DB*/
	            $fromDb = Database::insert(NULL, $tempUrl, $siteUrl, NULL, NULL); 
				if ($fromDb == 0){
					//print_r("Valid  " .$tempUrl. " \n");
	            	array_push($queue,$tempUrl);
					$nURLFounded++;
					$mem_usage = getMemoryUsage();
       				echo "Dimensione coda ".count($queue). " Memoria usata: " .$mem_usage."\n";

				} else {
					echo "Url ".$tempUrl." ripetuto. \n";
				}	

            	
            }
        }

        $valid = 0;
        $actualIdFile = 0;

		/* check if searched words are in page*/
		$found = preg_match_all( '/'.$key.'/i', $result, $words, PREG_SET_ORDER );

		if($found > 0){ /*If first keyword founded*/
			echo "Word ".$key. " is in page ".$siteUrl."\n";
			if($argc == 5)  /*Case if there is only one keyword*/
				$valid = 1;
			for($i = 5; $i < $argc; $i++) { /*check if there is almost one another keyword*/
				//echo "search word ".$argv[$i]."\n";
				$found = preg_match_all( '/'.$argv[$i].'/i', $result, $words, PREG_SET_ORDER );
				if($found > 0) {
					$valid = 1;
					echo "Word ".$argv[$i]. " is in page ".$siteUrl."\n";
					break;
				}
			}

			if($valid == 1) { /*If in this page there are keywords*/

				/*Write a file with idURL as name*/
				$myfile = fopen("output/".$idURL.".txt", "w") or die("Unable to open file!");        	
				fwrite($myfile, $result);
				fclose($myfile);
				$actualIdFile = $idURL;
				$idURL++;

				//$myfile = fopen("pagewithword.txt", "a") or die("Unable to open file!");        	
				//$txt = "word mail is in page ".$siteUrl."\n";
				//fwrite($myfile, $txt);
				//fclose($myfile);
			}

		}

		
		if($nURLFounded != 0 || $actualIdFile !=0) {
			/*Query to update url's info in DB*/
			$fromDb = Database::update($actualIdFile, $siteUrl, $nURLFounded);
			//if ($fromDb == 0){
			//	print_r("*** Update > " .$siteUrl. " \n");

			//}
		}
		
		echo "Finish Parsing url: ".$siteUrl."\n\n";

    } 
    /*else {
    	/*append url that not working in a file
    	$myfile = fopen("notworkingurls.txt", "a") or die("Unable to open file!");        	
    	$txt = "not work ".$siteUrl."\n";
    	fwrite($myfile, $txt);
    	fclose($myfile);
    	
    	
    }*/
}

function clean_url($url){
	if (substr($url, 0, 11) === 'http://www.')	
		$url = "http://".substr($url, 11);

	else if (substr($url, 0, 4) === 'www.')
		$url = "http://".substr($url, 4);

	else if (substr($url, 0, 8) === 'https://')
		$url = "http://".substr($url, 8);

	else if (substr($url, 0, 12) === 'https://www.')
		$url = "http://".substr($url, 12);
	
	else if(substr($url, 0, 7) !== 'http://') 
		$url = "http://".$url;

	//usare mb_substr?
	if(substr($url, -1) === '/')
		$url = substr($url, 0, -1);
		//$url = $url."/";
	return $url;
}

function check_file_ext($url){
	
	/*$url_arr = explode(".",$url);
	$arrLength = count($url_arr);
	$lastEle = $arrLength - 1;
	$fileExt = $url_arr[$arrLength - 1]; //Gives the file extension
	unset($url_arr[$lastEle]);
	$urlMinusExt = implode(".",$url_arr);
	return $fileExt;*/
	if (strpos(substr($url, 7),'/') === false) { /*check if is domain homepage*/
		return 1;
	}

	$ext = substr(strrchr($url, "."), 1);
	if(($ext === "html") || ($ext === "htm") || ($ext === "xhtml") || ($ext === "xml") || ($ext === "php")
		|| ($ext === "txt") || ($ext === "asp") || ($ext === "aspx") || ($ext === "jsp") || ($ext === "jspx"))
		return 1;
	if (strpos($ext,'/') !== false) {		/*Case of folder*/
		return 1;		
	}
	return 0; 		/*Link not supported*/
}

function getMemoryUsage() {
	$mem_usage = memory_get_usage(true);
       
    if ($mem_usage < 1024)
        $mem_usage = $mem_usage." bytes";
    elseif ($mem_usage < 1048576)
        $mem_usage = round($mem_usage/1024,2)." KB";
    else
        $mem_usage = round($mem_usage/1048576,2)." MB"; 
	return $mem_usage;
}


?>
