<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pmn_5jenisspk.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php


$optdebet=$optkredit=$optaktif= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "SELECT * FROM " . $dbname . ".keu_5akun
		where detail='1' order by noakun asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if(substr($bar['noakun'],0,1)==8){
		 $optdebet.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
	}
	
	if(substr($bar['noakun'],0,2)==21){
		$optkredit.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
	}
   
    
}

 $optaktif.="<option value='1'>".$_SESSION['lang']['ya']."</option>";
 $optaktif.="<option value='0'>".$_SESSION['lang']['tidak']."</option>";

$frm[0]='';


OPEN_BOX('','<span class=judul>'.getMenu('pmn_5jenisspk').'</span>');


$frm[0].="<fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		
		
		<tr>
			<td>".$_SESSION['lang']['kode']."</td> 
			<td>:</td>
			<td>
				<input type=text  id=kode nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:205px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nama']."</td> 
			<td>:</td>
			<td>
				<input type=text  id=nama nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:205px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['debet']."</td> 
			<td>:</td>
			<td>
				<select id=akundebet style=\"width:205px;\">" . $optdebet . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kredit']."</td> 
			<td>:</td>
			<td>
				<select id=akunkredit style=\"width:205px;\">" . $optkredit . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td> 
			<td>:</td>
			<td>
				<input type=text  id=keterangan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:205px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['file']."</td> 
			<td>:</td>
			<td>
				<input type=text  id=file nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:205px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['file']." ".$_SESSION['lang']['nonsales']."</td> 
			<td>:</td>
			<td>
				<input type=text  id=filenonpenjualan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:205px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['penjualan']."</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=penjualan>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nonpenjualan']."</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=nonpenjualan>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>

</fieldset>";


$frm[0].="<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";

echo $frm[0];	
	
CLOSE_BOX();
echo close_body();                  
?>