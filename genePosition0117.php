<?php
	ini_set("log_errors", 1);
	ini_set("error_log", "/home/yongqing/error_php.log");
	//step 1: use the gene position to find corresponding gene name in one species;
	//step 2: then use the gene name to fine another species's gene name
	//step 3: finally, merge the search result;
	
	// acquire the form information
	$species    = $_POST["species"];
	$position   = $_POST["genePosition"];
	$chromosome = substr($position,0,strpos($position,":"));
	$geneStart  = substr($position,strpos($position,":")+1,strpos($position,"-")-strpos($position,":")-1);
	$geneEnd    = substr($position,strpos($position,"-")+1); 
 	echo "$species  "."$chromosome:"."$geneStart--"."$geneEnd<br>";

	// open the databases:human,mouse and pig gene databases
	$host = "localhost";
	$user = "genomebrowser";
	$pwd  = "rZbSxdsuZWhcmEX5b9M87XMV9Sq8aPjpHug";
	$mysqlcon = mysql_connect($host, $user, $pwd,true);
	if (!$mysqlcon) {
		die('Could not connect:'.mysql_error());
	}else {
		echo "You Succeed con~<br>";
	}
	
	//$array_species=array("Human"=>"hg19","Mouse"=>"mm9","Pig"=>"susScr2");
	if(strcmp($species,"Human") == 0){
		$db1 = "hg19";
		$db2 = "mm9";
		$db3 = "susScr2";
		$sp1 = "Human";
		$sp2 = "Mouse";
		$sp3 = "P i g";
	}else if(strcmp($species,"Mouse") == 0){
		$db1 = "mm9";
		$db2 = "hg19";
		$db3 = "susScr2";
		$sp1 = "Mouse";
		$sp2 = "Human";
		$sp3 = "P i g";
	}else if(strcmp($species,"Pig") == 0){
		$db1 = "susScr2";
		$db2 = "hg19";
		$db3 = "mm9";
		$sp1 = "P i g";
		$sp2 = "Human";
		$sp3 = "Mouse";
	}else{
		die("Error:$species");
	}
	
	$array_result=array();	// $array_result is two dimension array
	//traverse three species to find gene name
	$sql1="select * from $db1.multishade where chrom='$chromosome' AND chromStart>='$geneStart' AND chromEnd<='$geneEnd'";
	$result = mysql_query($sql1,$mysqlcon);
	
	if(mysql_num_rows($result)) {
		while($row1 = mysql_fetch_array($result)) {
			$str1 = "$db1".": chrom:$row1[chrom] chromStart:$row1[chromStart] chromEnd:$row1[chromEnd] name:$row1[name]<br/>";
			//echo $str1;
			$str_spe1="$row1[chrom]:$row1[chromStart]-$row1[chromEnd]";

			$sql2 = "select * from $db2.multishade where name='$row1[name]'";
			$result_spe2 = mysql_query($sql2,$mysqlcon);
			if($row2 = mysql_fetch_array($result_spe2)){
				$str2 = "$db2: chrom:$row2[chrom] chromStart:$row2[chromStart] chromEnd:$row2[chromEnd] name:$row2[name]<br/>";
				//echo $str2;	
				$str_spe2="$row2[chrom]:$row2[chromStart]-$row2[chromEnd]";
			}else{
				$str_spe2="";
				//echo "$db2: Not found $row1[name] <br/>";
			}

			$sql3 = "select * from $db3.multishade where name='$row1[name]'";
			$result_spe3 = mysql_query($sql3,$mysqlcon);
			if($row3 = mysql_fetch_array($result_spe3)){
				$str3 = "Scr2: chrom:$row3[chrom] chromStart:$row3[chromStart] chromEnd:$row3[chromEnd] name:$row3[name]<br/>";
				//echo $str3;
				$str_spe3="$row3[chrom]:$row3[chromStart]-$row3[chromEnd]";
			}else{
				$str_spe3="";
				//echo "$db3: Not found $row1[name] <br/>";
			}
				
			$one_ret=array($sp1 => $str_spe1);
			if(strcmp($str_spe2,"") != 0){
				$one_ret[$sp2] = $str_spe2;
			}
			if(strcmp($str_spe3,"") != 0){
				$one_ret[$sp3] = $str_spe3;
			}
			$array_result[$row1[name]]=$one_ret;

			//echo "<br/>";
		}
	}else{
		echo "Not found any gene~";
	}
			
	//print_r($array_reult);
/*
	echo "<br/>-------------------------------------------<br/>";
	foreach ($array_result as $gname => $onearr){
		echo "$gname<br/>";
		foreach ($onearr as $spec => $pos){
			echo "$spec $pos<br/>";
		}
		echo "-------------------------------------------<br/>";
	}
*/
	$end_arr=array();	
	//merge the same gene
	foreach($array_result as $gname => $onearr){
		$geneName = substr($gname,0,strpos($gname,'_'));
		if(array_key_exists($geneName,$end_arr)){
			foreach($onearr as $pec => $pos){
				$chrom = substr($pos,0,strpos($pos,':'));
				$start = substr($pos,strpos($pos,':')+1,strpos($pos,'-')-strpos($pos,':')-1) + 0;
				$end = substr($pos,strpos($pos,'-')+1) + 0;
				if(array_key_exists($pec,$end_arr[$geneName])){
					$pos_old = $end_arr[$geneName][$pec];
					$start_old = substr($pos_old,strpos($pos_old,':')+1,strpos($pos_old,'-')-strpos($pos_old,':')-1) + 0;
					$end_old = substr($pos_old,strpos($pos_old,'-')+1) + 0;
					if($start > $start_old){
						$start = $start_old; 	
					}
					if($end < $end_old){
						$end = $end_old;
					}
				}
				$new_pos="$chrom:$start-$end";
				$end_arr[$geneName][$pec] = $new_pos;
			}
		}else{
			$end_arr[$geneName]=$onearr;
		}
	}
	
	echo "<br>Finally result<br/>=============================================<br/>";
	foreach ($end_arr as $gname => $onearr){
		echo "$gname<br/>";
		foreach ($onearr as $spec => $pos){
			echo "$spec $pos<br/>";
		}
		echo "=============================================<br/>";
	}
	
	mysql_close($mysqlcon);
	echo "<br>Test End<br>";
?> 
