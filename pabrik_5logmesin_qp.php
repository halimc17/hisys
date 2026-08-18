<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pabrik_5logmesin_qp.js'></script>

<?php





OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5logmesin_qp').'</span>');

$optunit=$optst=$optht="<option value=''></option>";
$optdt="<option value='0'></option>";

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
        . " where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
        . " where induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('STATION','MAINTENANCE')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optst.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".pabrik_5logmesin_klasifikasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['tipe']=='ht'){
		$optht.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}else{
		$optdt.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
    
}


//print_r($_SESSION['empl']['regional']);
echo"<br><fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=unit style=\"width:150px;\">'".$optunit."'</select></td>
					</tr>
                <tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td><select id=st style=\"width:150px;\">'".$optst."'</select></td>
					</tr>
                <tr>
				
				<tr>
					<td>".$_SESSION['lang']['header']."</td>
					<td>:</td>
					<td><select id=ht style=\"width:150px;\">'".$optht."'</select></td>
					</tr>
                <tr>
				<tr>
					<td>".$_SESSION['lang']['detail']."</td>
					<td>:</td>
					<td><select id=dt style=\"width:150px;\">'".$optdt."'</select></td>
					</tr>
                <tr>
				
				
				<tr>
				<td>".$_SESSION['lang']['nilai']."</td>
				<td>:</td>		
				<td><input type=text id=nil onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:145px;\"></td>	
				</tr>
				

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>";
        


CLOSE_BOX();
?>





<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>