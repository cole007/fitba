<?php
	
	// include_once('simplehtml/simple_html_dom.php');

    // Defining the basic cURL function
    // http://www.jacobward.co.uk/web-scraping-with-php-curl-part-1/
    function curl($url) {
        $ch = curl_init();  // Initialising cURL
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        $info = curl_getinfo($ch);
		// $curr = curl_errno($ch);
        // print_r($curr);
        // print_r($info);
        if ($info['http_code'] == 301 && $info['redirect_url']) $response = curl($info['redirect_url']); 
        curl_close($ch);    // Closing cURL
        if (!$response) {
        	$response['HTML'] = $data;
        	$response['code'] = $info['http_code'];	
        }        
        return $response;   // Returning the data from the function
    }

    // $scraped_website = curl('https://en.wikipedia.org/wiki/List_of_Premier_League_seasons');  // Executing our curl function to scrape the webpage http://www.example.com and return the results into the $scraped_website variable
    // print_r($scraped_website);

    // $html = new simple_html_dom();
    // $html->load($scraped_website, true, false);

    // $season = $html->find('#Seasons');
	// print_r($season);

	$link = mysql_connect('localhost', 'root', 'monkey00');
	mysql_select_db('fitba', $link);

	// $q = "SELECT * FROM ref LIMIT 0,1";
	$q = "SELECT * FROM ref";

	$result = mysql_query($q, $link);

	while ($row = mysql_fetch_assoc($result)) {
		$q2 = "SELECT * FROM teams WHERE team = '".$row['team']."'";
		$r2 = mysql_query($q2, $link);	
		// echo mysql_num_rows($r2).'<br />';
		while ($f2 = mysql_fetch_assoc($r2)) {			
			
			// print_r($f2);
			
			$url = 'https://en.wikipedia.org/wiki/';
			$url .= str_replace('?','–',$f2['year']) . '_' . $row['slug'] . '_season';
			
			// echo $url;
			// $url = 'https://en.wikipedia.org/wiki/List_of_Premier_League_seasons';
			// https://en.wikipedia.org/wiki/List_of_Premier_League_seasons
			// https://en.wikipedia.org/wiki/2015–16_A.F.C._Bournemouth_season

			$date = explode('?',$f2['year']);
    		// exit;
        
    		// print_r($scraped_website);
    		$start = $date[0];
    		$end = $date[1];

    		if ($end != 2000 && $end < 20) $end = '20' . $end;
	    	elseif ($end != 2000 && $end > 20) $end = '19' . $end;

			$getQ = "SELECT * FROM `source` WHERE `url` = '".$row['slug']."' AND `year_start` = '" . $start . "' AND `year_end` = '" . $end . "'";
    		$getR = mysql_query($getQ, $link) or die(mysql_error());
    		$getN = mysql_num_rows($getR);
			
			if ($getN == 0) {
			
				$response = curl($url);
	    		$scraped_website = $response['HTML'];
	    		$code = $response['code'];
    		
    			echo $getQ;
	    		// echo $getN;

				echo '<br />';
    		    		
    			$insQ = "INSERT INTO source (`year_start`,`year_end`,`url`,`code`,`blob`) VALUES ('".$start."','".$end."','".$row['slug']."','".$code."','".mysql_real_escape_string($scraped_website)."')";
	    		$insR = mysql_query($insQ, $link) or die(mysql_error());
	    		echo $row['slug'] . ": " . $start . "-" . $end;
	    		sleep(1);
	    		// exit;
	    	}
	    	
    		// echo $insQ;

		}
	}

	// echo 'Connected successfully';
	mysql_close($link);