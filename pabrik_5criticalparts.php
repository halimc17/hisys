<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
include('lib/zFunction.php');
?>

<script language=javascript1.2 src='js/pabrik_5criticalparts.js'></script>

<?php


$optunit=$optstation=$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select *   from ".$dbname.".log_5masterbarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5criticalparts').'</span>');

//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=unit onchange=getstation() style=\"width:150px;\">'".$optunit."'</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td><select id=station style=\"width:150px;\">'".$optstation."'</select></td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td><select id=kodebarang style=\"width:150px;\">'".$optbarang."'</select>
						<img id='kodebarang' onclick=z.elSearch('kodebarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
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
			<script>loaddata()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>