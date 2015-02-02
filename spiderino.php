<?php


$url_queue = array();

$start_seed =  $_SERVER["argv"][1]; /* initial url seed */

$delay = 1; /* default call delay second */

if(isset($_SERVER["argv"][2])) {
    $delay = $_SERVER["argv"][2]; /* set delay with start arg  */

}



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
	echo "Try getting url: ".$siteUrl."\n";
    $result = file_get_contents($siteUrl); /* download page */
   
    /*check if page is empty*/
    if( $result )
    {
        
        echo "Start Parsing url: ".$siteUrl."\n";
        //echo "QUIII! \n";
        
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

            print_r($item[1]. " \n");
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

/*
function check_file_ext($url,$toReturn){
		;
}*/


?>
