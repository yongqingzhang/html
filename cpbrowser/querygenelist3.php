<?php
	//ini_set("log_errors", 1);
	//ini_set("error_log", "/home/yongqing/error_php.log");
	//step 1: use the gene position to find corresponding gene name in one species;
	//step 2: then use the gene name to fine another species's gene name
	//step 3: finally, merge the search result;
	
	// acquire the form information
	$species = $_REQUEST["species"];
	$position   = $_REQUEST["geneName"];
	//$chromosome = substr($position,0,strpos($position,":"));
	//$geneStart  = substr($position,strpos($position,":")+1,strpos($position,"-")-strpos($position,":")-1);
	//$geneEnd    = substr($position,strpos($position,"-")+1); 
 	//echo "$species  "."$chromosome:"."$geneStart--"."$geneEnd<br>";
 	$genePosition = preg_split("/[\s-:]+/",$position);
 	//print_r($genePosition); echo "<br/>";
 	
 	$chromosome = strtolower($genePosition[0]);
 	$geneStart = $genePosition[1];
 	$geneEnd = $genePosition[2];

	// open the databases:human,mouse and pig gene databases
	$host = "localhost";
	$user = "genomebrowser";
	$pwd  = "rZbSxdsuZWhcmEX5b9M87XMV9Sq8aPjpHug";
	$mysqlcon = mysql_connect($host, $user, $pwd,true);
	if (!$mysqlcon) {
		die('Could not connect:'.mysql_error());
	}else {
		//echo "You Succeed con~<br>";
	}
	
	//$arrSpeciesDbInit=array("hg19"=>"hg19","mm9"=>"mm9","susScr2"=>"susScr2");	//This use to the CEpBrowser php
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
	$result1 = mysql_query($sql1,$mysqlcon);
	
	$arr_1=array();// Keep the finish result, it also means the first dimension of the array
	if(mysql_num_rows($result1)) {
		while($row1 = mysql_fetch_array($result1)) {
			$genename = $row1[name];
			$str1 = "$arrSpeciesDbInit[$species] $row1[chrom]:$row1[chromStart]--$row1[chromEnd] $row1[strand] $row1[name]<br/>";
			echo $str1;
			// The fourth dimension of the array
			$arr_4=array("chr"     => $row1['chrom'],
						"start" => $row1['chromStart'],
						"end"   => $row1['chromEnd'],
						"strand"     => $row1['strand']
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
				$arr_3=array("$max_key" => $arr_4);// General the result of the third array
				$arr_2=array($arrSpeciesDbInit[$species] => array_merge($arr_1[$genename][$species],$arr_3));// Merge the new array and get the result of the second array
				$arr_1[$genename]=$arr_2;// Point to the new array of the second dimension
				continue;// Have queried the gene name in other spicies, so don't need to query again
			}else{
				$arr_3=array("0" => $arr_4);// The first time to query the gene name
				$arr_2=array($arrSpeciesDbInit[$species] => $arr_3);
				$arr_1[$genename]=$arr_2;
			}

			foreach($arrSpeciesDb as $speci => $dbname){// Search the gene position by gene name in other species one by one
				$sql2 = "select * from $dbname.multishade where name='$genename'";
				$result_spe2 = mysql_query($sql2,$mysqlcon);
				if(mysql_num_rows($result_spe2)){
					while($row2 = mysql_fetch_array($result_spe2)){
						$str2 = "$dbname $row2[chrom]:$row2[chromStart]--$row2[chromEnd] $row2[strand] $row2[name]<br/>";
						echo $str2;	
						$arr_4=array("chr"     => $row2['chrom'],
									"start" => $row2['chromStart'],
									"end"   => $row2['chromEnd'],
									"strand"     => $row2['strand']
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
							$arr_3=array("$max_key" => $arr_4);
							$arr_2=array($arrSpeciesDbInit[$speci] => array_merge($arr_1[$genename][$speci],$arr_3));// Merge
						}else{
							$arr_3=array("0" => $arr_4);
							$arr_2=array($arrSpeciesDbInit[$speci] => $arr_3);
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
	
/*
Array ( 
	[OR4F17_0] => Array ( 
		[Human] => Array ( 
			[NUM_1] => Array ( [chrom] => chr1 [chromStart] => 56703 [chromEnd] => 59682 [strand] => + ) 
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
	[OR4F37_1] => Array ( 
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
	
	//merge the gene region according to the same gene//
	
	// Revise the array Key, from gene name to region name
	$temp = array();
	$regionNum = 1;
	foreach($arr_1 as $gName=>$arrTmp_2){
		$gName = "region_"."$regionNum";
		$temp[$gName] = $arrTmp_2;
		$regionNum++;
	}
	print_r($temp);
	
	$result = array();
	$regionNum = 1;
	foreach($temp as $geneRegion3 =>$arrTmp_3){
		echo $regionName = "region".$regionNum."<br/>";
		
		if(strcmp($result[$regionName]['hg19']['0']['chr'],"") != 0){
			if(strcmp($temp[$geneRegion3]['mm9']['0']['chr'],"") != 0 && strcmp($result[$regionName]['mm9']['0']['strand'],$temp[$geneRegion3]['mm9']['0']['strand']) == 0 && strcmp($result[$regionName]['mm9']['0']['chr'],$temp[$geneRegion3]['mm9']['0']['chr']) == 0){
				$chro1 = $result[$regionName]['mm9']['0']['chr'];
				$Start_1 = $result[$regionName]['mm9']['0']['start'];
				$End_1 = $result[$regionName]['mm9']['0']['end'];
				$Start_2 = $temp[$geneRegion3]['mm9']['0']['start'];
				$End_2  = $temp[$geneRegion3]['mm9']['0']['end'];
				$newStart = $End_1;
				$newEnd = $Start_2;
				
				$sql3="select * from mm9.multishade where chrom='$chro1' AND chromStart>='$newStart' AND chromEnd<='$newEnd'";
				$result3 = mysql_query($sql3,$mysqlcon);
				echo mysql_num_rows($result3)."<br/>";

				if(mysql_num_rows($result3) > 0){
					echo "Regionada<br/>";
					echo $regionNum++;
					$regionName = "region".$regionNum;
					echo $regionName."111111111111111111111111111<br/>";
					$result[$regionName] = $arrTmp_3;
				
				}else{
					echo $result[$regionName]['hg19']['0']['start']."<br/>";
					$result[$regionName]['hg19']['0']['end'] = $temp[$geneRegion3]['hg19']['0']['end'];
					$result[$regionName]['mm9']['0']['end'] = $temp[$geneRegion3]['mm9']['0']['end'];
					$result[$regionName]['susScr2']['0']['end']=$temp[$geneRegion3]['susScr2']['0']['end'];
				}
			}else{
				$regionNum++;
				$regionName = "region".$regionNum;
				$result[$regionName] = $arrTmp_3;
			}
		}else{
			echo "ab<br/>";
			$result[$regionName] = $arrTmp_3;
		}
	}

	//echo "<br/>-------------------------------------------<br/>";
	//print_r($result);  // Output the last result
	echo "<br>Finally result<br/>=============================================<br/>";
	foreach ($result as $gname => $onearr){
		echo "$gname<br/>";
		foreach ($onearr as $spec => $num_info){
			echo "$spec<br/>";
			foreach($num_info as $num => $info){
				echo "$num: chr:$info[chr],start:$info[start],end:$info[end],strand:$info[strand]<br/>";
			}
		}
		echo "=============================================<br/>";
	}

	mysql_close($mysqlcon);
	//echo "<br>Test End<br/><br/>";
?> 
