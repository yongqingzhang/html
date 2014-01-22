<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="mainstyles.css" rel="stylesheet" type="text/css" />
</head>
<?php
	if(empty($_REQUEST)) {
		// new page, show something else
?>
<body>
<?
	} else {
		$num_spc = $_REQUEST["num_spc"];
?>
<script language="javascript">
function resize_tbody() {

  if (document.getElementById("internal_table")) {

	 var winHeight = document.body.parentNode.clientHeight - 20;
	 document.getElementById("internal_table").style.height = winHeight + "px";
	 <?php
		for($i = 0; $i < $num_spc; $i++) {
	 ?>
	 document.getElementById("<?php echo $_REQUEST["speciesdb"][$i]; ?>").height 
	 	= winHeight / <?php echo $num_spc; ?> - 16;
	 <?php
		}
	 ?>
	 //window.alert(document.body.parentNode.clientHeight + "px");

  }
}
</script>
<?php echo "<body onresize=\"resize_tbody();\">"; ?>
<table width="98%" border="0" bordercolor="#003366" bgcolor="#003366" id="internal_table">
  <?php
		// TODO: may need to do something about the species here
		for($i = 0; $i < $num_spc; $i++) {
	?>
  <tr>
    <td class="tableHeader"><!-- Insert Species 1 Name here -->
      <em><?php echo $_REQUEST["speciesname"][$i]; ?></em> (<?php echo $_REQUEST["speciescmnname"][$i]; ?>)
       [Database: <?php echo $_REQUEST["speciesdb"][$i]; ?>]<br />
      <iframe id="<?php echo $_REQUEST["speciesdb"][$i]; ?>" name="<?php echo $_REQUEST["speciesdb"][$i]; ?>" src="<?php 
	$file = fopen("/home/yongqing/cepbrowser_record.txt", "w");
	fwrite($file, $REQUEST["speciescmnname"][$i]);
	fclose($file);	  echo "../cgi-bin/hgTracks?clade=mammal&org=" . $_REQUEST["speciescmnname"][$i] . "&db=" . $_REQUEST["speciesdb"][$i] . "&position=" . urlencode($_REQUEST[$_REQUEST["speciesdb"][$i]]) . "&pix=" . (850 + $i) . "&guidelines=off&Submit=submit&hgsid=" . (220 + $i);
	 ?>" width="100%" marginwidth="0" height="100%" marginheight="0" scrolling="auto">Your browser doesn't support &lt;iframe&gt; tag. You need a browser supporting &lt;iframe&gt; tag to use Comparison Browser. (Latest versions of mainstream browsers should all support this tag.)</iframe></td>
  </tr>
  <?php
  		}
	?>
</table>
<script language="javascript">
resize_tbody();
</script>
<?php
	}
?>
</body>
</html>
