<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pabrik_5tangkiv2.js?ver=1.5'></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5tangkiv2').'</span>');

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['kodeorganisasi']."</td> 
                    <td>:</td>
                    <td><select id=kodeorg style='width:155px;'>".$optunit."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodetangki']."</td> 
                    <td>:</td>
                    <td><input type=text id=kodetangki onkeypress='return tanpa_kutip(event)' class=myinputtext  style=width:150px></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['komoditi']."</td> 
                    <td>:</td>
                    <td>
                    <select id=komoditi style='width:155px;'>
                    <option value=''>Pilih Data</option>
                    <option value=CPO>CPO</option>
                    <option value=KER>KER</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kapasitas']." (Kg)</td> 
                    <td>:</td>
                    <td><input type=text id=kapasitas onkeypress='return tanpa_kutip(event)' onkeyup=z.numberFormat('kapasitas',2) class=myinputtextnumber  style=width:150px></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['keterangan']."</td> 
                    <td>:</td>
                    <td><input type=text id=keterangan onkeypress='return tanpa_kutip(event)' class=myinputtext  style=width:150px></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['cycling']."</td> 
                    <td>:</td>
                    <td><input type=text id=cycling onkeypress='return tanpa_kutip(event)' class=myinputtext  style=width:150px></td>
                </tr>
				
                <tr>
                	<td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Batal</button>
                    </td>
                </tr>

        </table></fieldset>
        <input type=hidden id=method value='insert'>";
        


CLOSE_BOX();
?>





<?php
OPEN_BOX();

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