<?php

/* 
sudo chmod -R 777 /var/www

nel regex dei link aggiungere l'https 
sempre nel regex se il link inizia con lo / allora bisogna appendere l'url all'inizio


*/

$url_queue = array();

$start_seed =  $_SERVER["argv"][1]; /* initial url seed */

$delay = 1; /* default call delay second */

if(isset($_SERVER["argv"][2])) {
    $delay = $_SERVER["argv"][2]; /* set delay with start arg  */

}

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
	
	global $nURL;
	$siteUrl = clean_url($siteUrl);
	if(check_file_ext($siteUrl) == 0)
		return 0;
	echo "Try getting url: ".$siteUrl."\n";
    $result = file_get_contents($siteUrl); /* download page */
   
    /*check if page is not empty*/
    if( $result )
    {
        $myfile = fopen("output/".$nURL.".txt", "a") or die("Unable to open file!");        	
		fwrite($myfile, $result);
		fclose($myfile);
		$nURL++;
        echo "Start Parsing url: ".$siteUrl."\n";
        //echo "QUIII! \n";
        
        //echo "\n*** ".check_file_ext($siteUrl)."\n";

        /* check if there is url in siteUrl */
        preg_match_all( '/<a.+?href="(http:\/\/[^0-9].+?)"/', $result, $urlmatch, PREG_SET_ORDER );
		
       
        /* check if searched words are in page*/
        $isMail=preg_match_all( '/mail/', $result, $words, PREG_SET_ORDER );
    
        if($isMail > 0){
        	echo "word mail is in page ".$siteUrl."\n";
			$myfile = fopen("pagewithword.txt", "a") or die("Unable to open file!");        	
			$txt = "word mail is in page ".$siteUrl."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
        	
        }
        
        foreach( $urlmatch as $item )
        {

            print_r("Found > " .$item[1]. " \n");
            /*if (!in_array($item[1], $queue)) { 
					//echo ("item ".$item[1]." not in array.\n");
					$file_ext="";
					//check_file_ext($item[1],&$file_ext);
					//check if url is a webpage (or txt). If is zip for example crawler don't add to queue
					if(){
						
					}
					
					
			}*/
			array_push($queue,$item[1]);
            
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
