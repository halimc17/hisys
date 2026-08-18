<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5jenispelanggaran.js></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5jenispelanggaran').'</span>');

$str="select * from ".$dbname.".sdm_5jenissp where not kode='BAPP'";
$optjenissp="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optjenissp.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->keterangan."</option>";
}

?>
<br>
<fieldset style="float:left">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
		<table border="0" cellspacing="0">
			<tr>
			    <td><?echo $_SESSION['lang']['tipe']." ".$_SESSION['lang']['surat'];?>
			    </td><td> : </td>
			    <td><select style="width:212px"  id="sppelanggaran" onchange="getId(0,0)"><?echo $optjenissp;?></select></td>
	  		</tr>
	  		
	  		<tr>
			    <td><?echo $_SESSION['lang']['kode']." ".$_SESSION['lang']['surat'];?></td><td> : </td>
			    <td><input type="text" class=myinputtext id="kodesp"  style="width:100px" maxlength=15 onkeypress="return tanpa_kutip(event)" disabled>
				<input type="text"  maxlength=3 class=myinputtext id="idpelanggaran" placeholder="Auto Generate" style="width:100px" onkeypress="return tanpa_kutip(event)" readonly disabled /></td>

			</tr>

	    	<tr>
			    <td><?echo $_SESSION['lang']['jenis']." ".$_SESSION['lang']['pelanggaran'];?>
			    </td><td> : </td>
			    <td><textarea style="width:192px" id='jenispel' ></textarea></td>
	  		</tr>
	    	
	    	<tr>
			    <td><?echo $_SESSION['lang']['status'];?>
			    </td><td> : </td>
			    <td colspan=4><input type=checkbox id=statusDt>Check = Nonaktif, Uncheck = Aktif</td>
			</tr>
	  		<input type=hidden value='insert' id=method>
			
			<tr>
				<td><td>
				<td colspan=3>
					<button class=mybutton onclick=simpan()><?echo $_SESSION['lang']['save'];?></button>
					<button class=mybutton onclick=cancel()><?echo $_SESSION['lang']['cancel'];?></button>
				</td>
				</td></td>
			</tr>
		</table>
</fieldset>
<?php
CLOSE_BOX();
OPEN_BOX();

echo"<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		<div id=container style='overflow:auto;height:300px;max-width:1235px'> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";

CLOSE_BOX();
echo close_body();
?>