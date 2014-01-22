<?php
// open the databases:human,mouse and pig gene database
$host = "localhost";
$user = "genomebrowser";
$pwd  = "rZbSxdsuZWhcmEX5b9M87XMV9Sq8aPjpHug";
$mysqlcon = mysql_connect($host, $user, $pwd,true);
if (!$mysqlcon) {
	die('Could not connect:'.mysql_error());
}else {
	echo "You Succeed con~<br>";
}
echo "22<br/>";

$sql = "select name from hg19.multishade";
$result = mysql_query($sql,$mysqlcon);
//$name=array();
echo "11<br/>";
while($row = mysql_fetch_array($result)){

echo "$row<br/>";
	if(strcmp($name[$row],"")==0){
		$name[$row]="1";
	}else{
		$name[$row]="2";
	}	
	echo "$name[$row]"."***";
}
//echo "$name";
/*
foreach($name as $gname=>$gnum){
echo "sfd<br/>";
	if(strcmp($gnum,"2")==0){
		echo $gname;
	}
}*/
?>
