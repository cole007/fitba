<?php

	include_once('simplehtml/simple_html_dom.php');

	$link = mysql_connect('localhost', 'root', 'monkey00');
	mysql_select_db('fitba', $link);

	$q = "SELECT * FROM source";
	$result = mysql_query($q, $link);
	
	function getPlayer($player,$start,$end,$link) {
		$q = "SELECT * FROM players WHERE `year_start` = '" . $start . "' AND `year_end` = '" . $end . "' AND `player_url` = '" . $player . "'";
		$result = mysql_query($q, $link);
		if (mysql_num_rows($result) == 0) return true;
		else return false;
	}
	function addPlayer($player,$player_url,$start,$end,$slug,$link) {
		// echo $player;
		$q = "INSERT INTO `players` (`year_start`,`year_end`,`team`,`player`,`player_url`) VALUES ('" . $start . "','" . $end . "','" . $slug . "','" . $player . "','" . $player_url . "')";
		echo $q;
		$result = mysql_query($q, $link);	
	}

	while ($row = mysql_fetch_assoc($result)) {
		
		// declare variables
		$slug = $row['url'];
		$start = $row['year_start'];
		$end = $row['year_end'];
		
		// get HTML
		$html = str_get_html($row['blob']);

		$table = [];
		$i = 0;

		// loop through tables - find largest one with players as a heading
		foreach($html->find('table') as $e) {
			// echo count($e->find('th[plaintext^=Player]')) . '<br />';
			if (count($e->find('th[plaintext^=Player]')) > 0) {
				// echo count($e->find('tr')) . ': '; 
				if ($i == 0) $table = $e;
				elseif (count($e->find('tr')) > count($table->find('tr'))) $table = $e;
				$i++;
			}
			
		}

		if (count($table) > 0) {
				
			// loop through headings - find index of one containing players
			$i = 0;
			foreach ($table->find('th') AS $th) {
				if ($th->innertext == 'Player') $index = $i;
				$i++;
			}
			
			// loop through table rows - fetch player table cell
			$i = 0;
			foreach ($table->find('tr') AS $tr) {
				// fetch index containing player data
				
				if (count($tr->find('td')) > 0 && isset($tr->find('td')[$index])) {
					$td = $tr->find('td')[$index];				
					$player_url = $td->find('a', 0)->href;
					$player =  $td->find('a', 0)->plaintext;
					if (getPlayer($player_url, $start, $end, $link)) {
						addPlayer($player, $player_url, $start, $end, $slug, $link);
						echo $player_url;
						
						sleep(1);
					} 
				}			
				// if ($i == 2) exit;
				// echo $td->find('a', 0);
				// echo $td;
				// echo $player->plaintext;
				// if ($player->find('a')->href) echo $player->find('a')->href;
				$i++;
			}
		}

	}
	mysql_close($link);