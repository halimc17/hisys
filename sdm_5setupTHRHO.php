<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?> 
<script type="text/javascript" src="js/sdm_payrollHO.js?v=<?php echo time(); ?>"></script>

<link rel=stylesheet type=text/css href=style/payroll.css>
<?
include('master_mainMenu.php');
//+++++++++++++++++++++++++++++++++++++++++++++
//list employee
OPEN_BOX('','<span class=judul>'.strtoupper('THR SETUP').'</span>');
		echo"<div id=EList>";
		
		//get current
		$arrCurr=Array();
		$stra="select * from ".$dbname.".sdm_ho_thr_setup";
		$resa=$owlPDO->query($stra) or die(print " Gagal: ".PDOException::getMessage());
		$resa->setFetchMode(PDO::FETCH_OBJ);
		while($bara=$resa->fetch())
		{
			array_push($arrCurr,$bara->component);
		}
 
		$strOrg = "SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi where tipe='PT'";
		$resOrg = $owlPDO->query($strOrg) or die(print " Gagal: ".PDOException::getMessage());
		echo "<label for='kdpt'>Kode PT: </label>";
		echo "<select id='kdpt' name='kdpt'>";
		while($rowOrg = $resOrg->fetch(PDO::FETCH_OBJ)) {
			echo "<option value='".$rowOrg->kodeorganisasi."'>".$rowOrg->namaorganisasi."</option>";
		}
		echo "</select><br><br>";

		//get component
		$str="select * from ".$dbname.".sdm_ho_component where type='basic'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);		
		echo"<fieldset>
		      <legend>Component yang akan ditarik menjadi base THR</legend>
			 ";
		while($bar=$res->fetch())
		{
			if($bar->id==1)
			  $s=' disabled ';
			else
			  $s='';
			    
			if (in_array($bar->id, $arrCurr)) {
    			echo"<input type=checkbox ".$s." checked onclick=thrSetup(this,this.value) value=".$bar->id." id=com".$bar->id.">".$bar->name."<br>";
			}
            else
			{
			echo"<input type=checkbox ".$s."  onclick=thrSetup(this,this.value) value=".$bar->id." id=com".$bar->id.">".$bar->name."<br>";
			}
		}	 
		echo"</fieldet>"; 
		echo"</div>";
	CLOSE_BOX();	
//+++++++++++++++++++++++++++++++++++++++++++
echo close_body();
?>