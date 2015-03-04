<?php

/*
This file is part of spiderino package.
Writen by
	Cantarella Danilo (http://cantarelladanilo.com)
    Maccarrone Roberta (http://robertamaccarrone.altervista.org)
    Parasiliti Parracello Cristina (http://parasiliticristina.altervista.org)
    Randazzo Filippo (http://randazzofilippo.com);
    Safarally Dario (http://dariosafarally.altervista.org);
    Siragusa Sebastiano (http://sebastianosiragusa.altervista.org/)
    Vindigni Federico (http://federicovindigni.altervista.org)
Full spiderino is released by GPL3 licence.
*/

require ("libs/database.php"); 				/*Require php file for database function*/

ini_set('memory_limit', '1024M'); 			/*Setting max memory used*/

$tableName = str_replace(array(' ', '-', ':'), "_", date("d-m-Y h:i:s"));		/*Set table name with actual date*/

/*init database */
Database::createDb();
Database::createTable($tableName);

$argv = $_SERVER["argv"];
$argc = count($argv);

/*Check input parameters*/
if( $argc < 5){				
	echo "Error: too few arguments!\nUsage: php ./spiderino SEED1 [SEED2 SEED3 .. ] -t TIME_SIM KEYWORD1 [KEYWORD2 KEYWORD3 .. ]\n";
 	return 0;
}

$i = 1;	
$totUrl = 0;

while($argv[$i] != '-t') { 					/*Extract all seed urls*/
	$start_seed = clean_url($argv[$i]);		/*Clean seed url*/
	if(check_file_ext($start_seed) != 0) 	/*Check if url is valid*/
	{
		$fromDb = Database::insert($tableName, $totUrl, -1, $start_seed, "Initial seed Url", 0, -1);  /*Query to insert seed url in DB*/
		$totUrl++;
	}	
	$i++; 				/*Go to next ssed url*/
	
}

if($argv[$i] !== '-t'){					/*Check if there is time limit*/
		echo "Error: wrong arguments!\nUsage: php ./spiderino SEED1 [SEED2 SEED3 .. ] -t TIME_SIM KEYWORD1 [KEYWORD2 KEYWORD3 .. ]\n";
 		return 0;
	}
	
$i++;	
$simulationTime = $argv[$i] * 60;		/*Convert time limit in seconds*/

$firstKey = $i + 1; 					/*Extract first keyword*/

$timeStart  = strtotime(date("d-m-Y h:i:s"));		/*Save start time of simulation*/

$folderName = "output_".$tableName; 				/*Set output folder's name*/

if (!is_dir($folderName)) {							/*Create output folder*/
	mkdir($folderName, 0777, true);
}

$idURL = 1; 								/*Index to save files on disk*/

$index = 0; 								/*Index to read next url from DB*/

/* Loop readUrls while there is element in queue */
while($index < $totUrl) {
	$timeActual = strtotime(date("d-m-Y h:i:s"));			/*Save actual time to check if simulation is over*/
	$differenceInSeconds = $timeActual - $timeStart;
	if($differenceInSeconds > $simulationTime) {			/*Exit if simulation is over*/
		echo "Simulation completed! Exiting...\n";
		break;
	}
		
	$url = Database::getUrl($tableName, $index);			/*Get next url from DB*/
	readUrls($url);											/*Url's parsing*/
	$index++;
}

/* Function that parse url to find new urls e keywords */
function readUrls($siteUrl){
	
	global $idURL, $key, $argc, $argv, $totUrl, $tableName, $folderName, $firstKey;
	
	echo "Try getting url: ".$siteUrl."\n";
	$result = file_get_contents($siteUrl); 					/* download page */

	if( $result )											/*check if page is not empty*/
	{
		echo "Start Parsing url: ".$siteUrl."\n";
      	
		/* Find all valid urls in siteUrl */
		preg_match_all( '/<a.+?href="((http:\/\/|https:\/\/|\/|)[a-zA-Z0-9].+?)"/', $result, $urlmatch, PREG_SET_ORDER );

		$domain = $siteUrl;									/*Extract domain url (actual folder)*/
		$pos = strrpos( substr($siteUrl, 7), '/');			
		if ($pos !== false) {
			$domain = substr($siteUrl, 0, 7 + $pos);
		}

		$firstDomain = $siteUrl;							/*Extract main domain url*/
		$pos = strstr(substr($siteUrl,7), '/', true);
		if ($pos !== false) {
			$firstDomain = "http://".$pos;
		}
		
		$nURLFounded = 0;									/*Counter for numbers of founded urls*/

		$depthFather = Database::getDepth($tableName, $siteUrl);		/*Get father's deep from DB*/

		foreach( $urlmatch as $item ) 						/*Add founded urls in DB*/
		{
			$tempUrl = $item[1];
			print_r("Found > " .$tempUrl. " \n");

            if (substr($tempUrl, 0, 1) === '/') 			/*If there is / in first position, adding main domain*/
            $tempUrl = $firstDomain.$tempUrl;
            else if(substr($tempUrl, 0, 4) !== 'http')
            	$tempUrl = $domain."/".$tempUrl;
            
            $tempUrl = clean_url($tempUrl);					/*Clean url*/
            if(check_file_ext($tempUrl) == 1) { 			/*Check if urls has valid extension*/

            	/*Query to insert url in DB*/
	            $fromDb = Database::insert($tableName, $totUrl, -1, $tempUrl, $siteUrl, $depthFather + 1, -1); 
	            
				if ($fromDb == 0){							/*If it's not in DB*/
					$nURLFounded++;
					$totUrl++;
					$mem_usage = getMemoryUsage();
       				echo "Queue size: ".$totUrl. " Memory used: " .$mem_usage."\n";

				} else {
					echo "Url ".$tempUrl." repeated. \n";
				}	
			}
        }

        $valid = 0;
        $actualIdFile = 0;

        /*Check if there is almost one keyword*/
        for($i = $firstKey; $i < $argc; $i++) { 			
			$found = preg_match_all( '/'.$argv[$i].'/i', $result, $words, PREG_SET_ORDER );		/*Regex to find keywords*/
			if($found > 0) {
				$valid = 1;													/*If there is one keyword, page is valid*/
				echo "Key: ".$argv[$i]. " is in page ".$siteUrl."\n";
				break;
			}
		}

		if($valid == 1) { 									/*If in this page there is almost one keyword*/
			/*Write a file with idURL as name*/
			$myfile = fopen($folderName."/".$idURL.".txt", "w") or die("Unable to open file!");        	
			fwrite($myfile, $result);
			fclose($myfile);
			$actualIdFile = $idURL;
			$idURL++;
		}
		/*Query to update url's info in DB*/
		$fromDb = Database::update($tableName, $actualIdFile, $siteUrl, $nURLFounded);
		echo "Finish Parsing url: ".$siteUrl."\n\n";
	}
}

/*Function to clean url and have it in the form http://site.com */
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

	if(substr($url, -1) === '/')
		$url = substr($url, 0, -1);
	return $url;
}

/*Function to check is url has a valid extension*/
function check_file_ext($url){
	
	if (strpos(substr($url, 7),'/') === false) { 			/*check if url is domain homepage*/
		return 1;
	}

	$ext = substr(strrchr($url, "."), 1);
	if(($ext === "html") || ($ext === "htm") || ($ext === "xhtml") || ($ext === "xml") || ($ext === "php")
		|| ($ext === "txt") || ($ext === "asp") || ($ext === "aspx") || ($ext === "jsp") || ($ext === "jspx"))
		return 1;
	if (strpos($ext,'/') !== false) {		/*Case of folder. Ex: http://site.com/homepage/  */
		return 1;		
	}
	return 0; 						/*Url not supported*/
}

/*Function to get memory usage*/
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

/*
This file is part of spiderino package.
Writen by
	Cantarella Danilo (http://cantarelladanilo.com)
    Maccarrone Roberta (http://robertamaccarrone.altervista.org)
    Parasiliti Parracello Cristina (http://parasiliticristina.altervista.org)
    Randazzo Filippo (http://randazzofilippo.com);
    Safarally Dario (http://dariosafarally.altervista.org);
    Siragusa Sebastiano (http://sebastianosiragusa.altervista.org/)
    Vindigni Federico (http://federicovindigni.altervista.org)
Full spiderino is released by GPL3 licence.
*/

?>
