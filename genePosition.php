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
	
	$arrSpeciesDbInit=array("Human"=>"hg19","Mouse"=>"mm9","Pig"=>"susScr2");
	
	$arrSpeciesDb=array();// Other species need to query and it coressponding database name
    if(array_key_exists($species,$arrSpeciesDbInit)){
        $arrSpeciesDb = array_diff($arrSpeciesDbInit,array($species => $arrSpeciesDbInit[$species]));
    }else{
        die("Error:$species<br/>");
    }
    //print_r($arrSpeciesDb);
	
	//traverse three species to find gene name
	$sql1="select * from $arrSpeciesDbInit[$species].multishade where chrom='$chromosome' AND chromStart>='$geneStart' AND chromEnd<='$geneEnd'";
	$result = mysql_query($sql1,$mysqlcon);
	
	$region=array();
	
	$arr_1=array();// Keep the finish result, it also means the first dimension of the array
	if(mysql_num_rows($result)) {
		while($row1 = mysql_fetch_array($result)) {
			$genename = $row1[name];
			$str1 = "$arrSpeciesDbInit[$species]".": chrom:$row1[chrom] chromStart:$row1[chromStart] chromEnd:$row1[chromEnd] name:$row1[name]:$row1[strand]<br/>";
			echo $str1;
			// The fourth dimension of the array
			$arr_4=array("chr"     => $row1[chrom],
						"start" => $row1[chromStart],
						"end"   => $row1[chromEnd],
						"strand"     => $row1[strand]
			            );
			// The gene name exists, num++
			if(array_key_exists($genename,$arr_1)){
				$arr_3_key = array_keys($arr_1[$genename][$species]);// The array Key of the third dimension array, like NUM_xx
				$max_key = 0;
				foreach($arr_3_key as  $skey){// Search the max value in NUM_xx
					$akey = substr($skey,strpos($skey,'_')+1) + 0;
					if($max_key < $akey){
						$max_key = $akey;	
					}	
				}
				$max_key++;
				$arr_3=array("NUM_$max_key" => $arr_4);// General the result of the third array
				$arr_2=array($species => array_merge($arr_1[$genename][$species],$arr_3));// Merge the new array and get the result of the second array
				$arr_1[$genename]=$arr_2;// Point to the new array of the second dimension
				continue;// Have queried the gene name in other spicies, so don't need to query again
			}else{
				$arr_3=array("NUM_1" => $arr_4);// The first time to query the gene name
				$arr_2=array($species => $arr_3);
				$arr_1[$genename]=$arr_2;
			}

			foreach($arrSpeciesDb as $speci => $dbname){// Search the gene position by gene name in other species one by one
				$sql2 = "select * from $dbname.multishade where name='$genename'";
				$result_spe2 = mysql_query($sql2,$mysqlcon);
				if(mysql_num_rows($result_spe2)){
					while($row2 = mysql_fetch_array($result_spe2)){
						$str2 = "$dbname: chrom:$row2[chrom] chromStart:$row2[chromStart] chromEnd:$row2[chromEnd] name:$row2[name]:$row2[strand]<br/>";
						echo $str2;	
						$arr_4=array("chr"     => $row2[chrom],
									"start" => $row2[chromStart],
									"end"   => $row2[chromEnd],
									"strand"     => $row2[strand]
									);
						if(array_key_exists($speci,$arr_1[$genename])){// The species information has existed, need to consider NUM++ in the third dimension array
							$arr_3_key = array_keys($arr_1[$genename][$speci]);
							$max_key = 0;
							foreach($arr_3_key as  $skey){
								$akey = substr($skey,strpos($skey,'_')+1) + 0;
								if($max_key < $akey){
									$max_key = $akey;	
								}	
							}
							$max_key++;
							$arr_3=array("NUM_$max_key" => $arr_4);
							$arr_2=array($speci => array_merge($arr_1[$genename][$speci],$arr_3));// Merge
						}else{
							$arr_3=array("NUM_1" => $arr_4);
							$arr_2=array($speci => $arr_3);
						}			
						$arr_1[$genename]=array_merge($arr_1[$genename],$arr_2);// Merge
					}
				}else{
					echo "$dbname: Not found $genename <br/>";
				}
			}
		echo "<br/>";
		}
		
	}else{
		echo "Not found any gene~";
	}
	//print_r($arr_1);
	
/*
Array ( 
	[OR4F17_0] => Array ( 
		[Human] => Array ( 
			[NUM_1] => Array ( [chrom] => chr1 [chromStart] => 56703 [chromEnd] => 59682 [strand] => + ) 
			[NUM_2] => Array ( [chrom] => chr1 [chromStart] => 11111 [chromEnd] => 22222 [strand] => + ) 
		) 
		[Mouse] => Array ( 
			[NUM_1] => Array ( [chrom] => chr2 [chromStart] => 111313507 [chromEnd] => 111315657 [strand] => + ) 
		) 
		[Pig] => Array ( 
			[NUM_1] => Array ( [chrom] => chr7 [chromStart] => 86042192 [chromEnd] => 86044585 [strand] => + ) 
		) 
	) 
	[OR4F17_1] => Array ( 
		[Human] => Array ( 
			[NUM_1] => Array ( [chrom] => chr1 [chromStart] => 59682 [chromEnd] => 61179 [strand] => + ) 
		) 
		[Mouse] => Array ( 
			[NUM_1] => Array ( [chrom] => chr2 [chromStart] => 111315843 [chromEnd] => 111317276 [strand] => + ) 
		) 
		[Pig] => Array ( 
			[NUM_1] => Array ( [chrom] => chr7 [chromStart] => 134565397 [chromEnd] => 134566510 [strand] => - ) 
		) 
	) 
) 
*/
	//merge the same gene
	// Don't consider "strand" value, according to the end_arr of the first insert
	$end_arr=array();		
	foreach($arr_1 as $gName => $arrTmp_2){
		$geneName = substr($gName,0,strpos($gName,'_'));
		if(array_key_exists($geneName,$end_arr)){// The gene exist, such as "OR4F17"
			foreach($arrTmp_2 as $speName => $arrTmp_3){// Merge the result according to the each species
				if(array_key_exists($speName,$end_arr[$geneName])){// The species exist, such as "human"
					foreach($arrTmp_3 as $num => $arrTmp_4){// Merge the result according to the "Num"
						if(array_key_exists($num,$end_arr[$geneName][$speName])){//it's "num" exist, such as "num_1"
							if($end_arr[$geneName][$speName][$num][chr] == $arrTmp_4[chr]){// How to deal with the different "chr". The "chr" must equal so can merge
								$old_start = $end_arr[$geneName][$speName][$num]["start"] + 0;
								$old_end   = $end_arr[$geneName][$speName][$num]["end"] + 0;
								$now_start = $arrTmp_4["start"]+ 0;
								$now_end   = $arrTmp_4["end"]+ 0;
								// Update the "start" and "end"
								if($now_start < $old_start){
									$end_arr[$geneName][$speName][$num]["start"] = $now_start;
								}
								if($now_end > $old_end){
									$end_arr[$geneName][$speName][$num]["end"] = $now_end;
								}
							}
						}else{
							$end_arr[$geneName][$speName][$num] = $arrTmp_4;
						}
					}
				}else{
					$end_arr[$geneName][$speName] = $arrTmp_3;
				}
			}
			
		}else{
			$end_arr[$geneName] = $arrTmp_2;
		}
	}
	
	//echo "<br/>-------------------------------------------<br/>";
	//print_r($end_arr);  // Output the last result
	echo "<br>Finally result<br/>=============================================<br/>";
	foreach ($end_arr as $gname => $onearr){
		echo "$gname<br/>";
		foreach ($onearr as $spec => $num_info){
			echo "$spec<br/>";
			foreach($num_info as $num => $info){
				echo "$num: chr:$info[chr],start:$info[start],end:$info[end],strand:$info[strand]<br/>";
			}
		}
		echo "<br/>=============================================<br/>";
	}
	
	
	
	mysql_close($mysqlcon);
	echo "<br>Test End<br>";
?> 
