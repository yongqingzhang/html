<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gene List</title>
<link href="mainstyles.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
	if(empty($_REQUEST)) {
		// new page, doesn't do anything
	} else {
		// open database for table list (species)
		$conn = mysql_connect('localhost', 'genomebrowser', 'ucscgbrowser');
		if(empty($conn)) {
			die("Comparison database is not ready.");
		}
		$db = mysql_select_db('compbrowser', $conn);
		if(empty($db)) {
			die("Comparison database is not ready.");
		}
		
		// first connect to database and find the number of species
		$species = mysql_query("SELECT * FROM species");
		echo $_REQUEST[$spcitor["dbname"]]. "1";
		echo "test";
		while($spcitor = mysql_fetch_assoc($species)) {
			// get all the species ready
			if(isset($_REQUEST[$spcitor["dbname"]])) { //should use this later
			//if($spcitor["dbname"] == "hg19" || $spcitor["dbname"] == "mm9") {
				$spcinfo[] = $spcitor;
				$spcflag[] = true;
				$spcmultiflag[] = false;
			}
		}
		
		$num_spc = sizeof($spcinfo);
		echo $num_spc;
		$directsubmit = false;
		
		if($_REQUEST["genelist"] != "(none)") {
			// TODO: gene selected, directly reload the genes
			for($i = 0; $i < $num_spc; $i++) {
				$genes[] = mysql_query("SELECT * FROM " . $spcinfo[$i]["dbname"] 
					. " WHERE genename = '" . $_REQUEST["genelist"] . "' ORDER BY liftovers DESC, similarity DESC");
			}
			$directsubmit = true;
		} else {
			// find the gene
			for($i = 0; $i < $num_spc; $i++) {
				$genes[] = mysql_query("SELECT * FROM " . $spcinfo[$i]["dbname"] 
					. " WHERE genename LIKE '%" . $_REQUEST["gene_name"] . 
					"%' ORDER BY genename, liftovers DESC, similarity DESC");
			}
		}
		for($i = 0; $i < $num_spc; $i++) {
			$nextGene[] = mysql_fetch_assoc($genes[$i]);
		}
?>
<table width="100%" border="1" cellspacing="0" bordercolor="#336600">
  <tr>
    <td bgcolor="#336600" class="tableHeader">Gene Information</td>
  </tr>
  <?php
		$count = 0;
		while($nextGene[0]) {
			$currentGeneArray = array();
			$currentGeneName = $nextGene[0]["genename"];
			for($i = 0; $i < $num_spc; $i++) {
				$currentSpeciesGeneArray = array();
				while($nextGene[$i] && $nextGene[$i]["genename"] == $currentGeneName) {
					$currentSpeciesGeneArray []= $nextGene[$i];
					$nextGene[$i] = mysql_fetch_assoc($genes[$i]);
				}
				$currentGeneArray []= $currentSpeciesGeneArray;
			}
			
  ?>
  <tr>
    <td class="formstyle"><form id="<?php echo $currentGeneName; ?>" name="<?php echo $currentGeneName; ?>" method="post" action="cpbrowser.php" target="cpbrowser">
        <table width="100%" border="0">
          <tr>
            <td valign="top"><strong><?php echo $currentGeneName; ?></strong></td>
            <td align="right" valign="top"><input name="speciesdb[]" type="hidden" value="<?php echo $spcinfo[$i]["dbname"]; ?>" />
              <input name="speciesname[]" type="hidden" value="<?php echo $spcinfo[$i]["name"]; ?>" />
              <input name="speciescmnname[]" type="hidden" value="<?php echo $spcinfo[$i]["commonname"]; ?>" />
              <input name="num_spc" id="num_spc" type="hidden" value="<?php echo $num_spc; ?>" />
              <input name="showinbrowser" type="submit" id="showinbrowser" value="Show in Browser" />
            </td>
          </tr>
          <tr class="smallformstyle">
            <td width="20%" valign="top"><?php echo $spcinfo[$i]["commonname"]; ?></td>
            <?php
			for($i = 1; $i < $num_spc; $i++) {
				if(sizeof($currentGeneArray[$i]) > 0) {
					$spcflag[$i] = true;
					if(sizeof($currentGeneArray[$i]) > 1) {
						$spcmultiflag[$i] = true;
					} else {
						$spcmultiflag[$i] = false;
					}
				} else {
					$spcflag[$i] = false;
				}
			}
			for($i = 0; $i < $num_spc; $i++) {
				if($spcflag[$i]) {
						 ?>
            <td width="80%" valign="top"><?php echo $currentGeneArray[$i][0]["nameinspc"]; ?><br />
              <?php 
					if(!$spcmultiflag[$i]) {
					?>
              <input name="<?php echo $spcinfo[$i]["dbname"]; ?>" type="hidden" id="<?php echo $spcinfo[$i]["dbname"]; ?>"
						 value="<?php echo $currentGeneArray[$i][0]["chr"] . ":" . $currentGeneArray[$i][0]["extendedstart"] . "-" . $currentGeneArray[$i][0]["extendedend"]; ?>" />
              <?php
					} else {
						 ?>
              <select class="combostyle" name="<?php echo $spcinfo[$i]["dbname"]; ?>" type="hidden" id="<?php echo $spcinfo[$i]["dbname"]; ?>" />
              
              <?php
						for($j = 0; $j < sizeof($currentGeneArray[$i]); $j++) {
					?>
              <option selected="selected" value="<?php echo $currentGeneArray[$i][$j]["chr"] . ":" . $currentGeneArray[$i][$j]["extendedstart"] . "-" . $currentGeneArray[$i][$j]["extendedend"]; ?>"><?php echo $currentGeneArray[$i][$j]["chr"] . ":" . $currentGeneArray[$i][$j]["genestart"] . "-" . $currentGeneArray[$i][$j]["geneend"]; 
			  				if($currentGeneArray[$i][$j]["liftovers"] <= 0) {
								echo "(*)";
							}
			  ?></option>
              <?php
						}
					} 
 ?></td>
          </tr>
          <?php 
					if(!$directsubmit) {
						$currentGene[$i] = mysql_fetch_assoc($genes[$i]);
					}
				}
			}
		  ?>
        </table>
      </form></td>
  </tr>
  <?php
  			if($directsubmit) {
				break;
			}
		} 
		
		if($directsubmit) {
			?>
  <script language="javascript">
				document.getElementById("<?php echo $currentGene[0]["genename"]; ?>").submit();
			</script>
  <?php
		}
  ?>
</table>
<?php
	}
?>
</body>
</html>
