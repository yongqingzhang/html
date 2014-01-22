<?php
	$conn = mysql_connect('localhost', 'cpbrowser', 'compbr0wser5');
	if(empty($conn)) {
		die("Comparison database is not ready.");
	}
	$db = mysql_select_db('compbrowser', $conn);
	if(empty($db)) {
		die("Comparison database is not ready.");
	}
	$generesult = mysql_query("SELECT * FROM (SELECT * FROM `alias` WHERE `alias` LIKE '" 
		. mysql_real_escape_string($_REQUEST['name']) 
		. "%' ORDER BY `isGeneName` DESC, `genename`) as `T` GROUP BY `genename`");
	$result = array();
	if(mysql_num_rows($generesult) <= 0) {
		$result["(none)"] = "none";
		//die(json_encode($result));
	} else {
		while($row = mysql_fetch_assoc($generesult)) {
			if(!empty($result[$row["alias"]])) {
				$suffix = 1;
				while(!empty($result[$row["alias"] . "__" . $suffix])) {
					$suffix++;
				}
				$result[$row["alias"] . "__" . $suffix] = $row["genename"];
			} else {
				$result[$row["alias"]] = $row["genename"];
			}
		}
	}
	echo json_encode($result);
	mysql_free_result($generesult);
	mysql_close($conn);

?>