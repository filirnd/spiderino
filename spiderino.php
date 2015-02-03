<?php

/* 
sudo chmod -R 777 /var/www

nel regex dei link aggiungere l'https 
sempre nel regex se il link inizia con lo / allora bisogna appendere l'url all'inizio
tempo
ripetizioni con db
calcolo profondità
nel db url, indice


*/

$url_queue = array();

$argv = $_SERVER["argv"];
$argc = count($argv);

$start_seed =  $argv[1]; /* initial url seed */
$key = $argv[2];

$delay = 0; /* default call delay second */

/*
if(isset($_SERVER["argv"][2])) {
    $delay = $_SERVER["argv"][2]; /* set delay with start arg  

}*/

if (!is_dir('output')) {
    mkdir('output', 0777, true);
}

$nURL = 0;

/*read first seed url and add it in queue*/
/*echo "URL SEED ".$start_seed." \n";
echo "QUEU".count($url_queue)."\n";
*/
readUrls($start_seed,$url_queue);

$index=0;

/* Loop readPage while there is element in queue */
while($index < count($url_queue)-1) {
	echo ("-----WHILE ENTER WITH INDEX:".$index."\n");
	readUrls($url_queue[$index],$url_queue);
	$index++;
	sleep($delay);

}

/* function that keep urls in &$queue from a page $siteUrl*/

function readUrls($siteUrl,&$queue){
	
	global $nURL, $key, $argc, $argv;
	$siteUrl = clean_url($siteUrl);
	if(check_file_ext($siteUrl) == 0)
		return 0;
	echo "Try getting url: ".$siteUrl."\n";
    $result = file_get_contents($siteUrl); /* download page */
   
    /*check if page is not empty*/
    if( $result )
    {
        echo "Start Parsing url: ".$siteUrl."\n";
        //echo "QUIII! \n";
        
        //echo "\n*** ".check_file_ext($siteUrl)."\n";

        /* check if there is url in siteUrl */
        preg_match_all( '/<a.+?href="((http:\/\/|https:\/\/|\/|)[a-zA-Z0-9].+?)"/', $result, $urlmatch, PREG_SET_ORDER );
		$valid = 0;
       
        /* check if searched words are in page*/
        $found = preg_match_all( '/'.$key.'/i', $result, $words, PREG_SET_ORDER );
    
        if($found > 0){
        	echo "word ".$key. " is in page ".$siteUrl."\n";
        	if($argc == 3) 
        		$valid = 1;
        	for($i = 3; $i < $argc; $i++) {
        		echo "search word ".$argv[$i]."\n";
        		$found = preg_match_all( '/'.$argv[$i].'/i', $result, $words, PREG_SET_ORDER );
        		if($found > 0) {
        			$valid = 1;
        			echo "word ".$argv[$i]. " is in page ".$siteUrl."\n";
        			break;
        		}
        	}
        	
        	if($valid == 1) {

        		$myfile = fopen("output/".$nURL.".txt", "w") or die("Unable to open file!");        	
				fwrite($myfile, $result);
				fclose($myfile);
				$nURL++;

				$myfile = fopen("pagewithword.txt", "a") or die("Unable to open file!");        	
				$txt = "word mail is in page ".$siteUrl."\n";
				fwrite($myfile, $txt);
				fclose($myfile);
			}
        	
        }

        $domain = $siteUrl;
        $pos = strrpos( substr($siteUrl, 7), '/');
		if ($pos !== false) {
		    $domain = substr($siteUrl, 0, 7 + $pos);
		}
		echo ">>dominio  ".$domain."\n";

		$firstDomain = substr($siteUrl,7);
		$pos = strstr($firstDomain, '/', true);
		if ($pos !== false) {
		    $firstDomain = "http://".$pos;
		    echo ">>dominio first  ".$firstDomain."\n";
		}
		//$firstDomain = "http://".strstr($firstDomain, '/');

		
    
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
				print_r("**Valid > " .$tempUrl. " \n");
				array_push($queue,$tempUrl);
            }
        }

	echo "Finish Parsing url: ".$siteUrl."\n";

    } else {
    	/*append url that not working in a file*/
    		$myfile = fopen("notworkingurls.txt", "a") or die("Unable to open file!");        	
			$txt = "not work ".$siteUrl."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
    	
    	
    }
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


?>
