<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pabrik_5mr_roa_formatlaporan.js'></script>

<?php


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5mr_roa_formatlaporan').'</span>');



$optjenis=$optparameter=$optkomponen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$str="select jenis,nama from ".$dbname.".pabrik_5mr_roa_jenis";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optjenis.="<option value=".$bar['jenis'].">".$bar['nama']."</option>";
}

$str="select parameter,nama from ".$dbname.".pabrik_5mr_roa_parameter";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optparameter.="<option value=".$bar['parameter'].">".$bar['nama']."</option>";
}

$str="select komponen,nama from ".$dbname.".pabrik_5mr_roa_komponen";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkomponen.="<option value=".$bar['komponen'].">".$bar['nama']."</option>";
}


//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['jenis']."</td> 
                    <td>:</td>
                    <td><select id=jenis  style=\"width:150px;\">".$optjenis."</select></td>
                </tr>
                
				<tr>
                    <td>".$_SESSION['lang']['kodeparameter']."</td> 
                    <td>:</td>
                    <td><select id=parameter  style=\"width:150px;\">".$optparameter."</select></td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['idkomponen']."</td> 
                    <td>:</td>
                    <td><select id=komponen  style=\"width:150px;\">".$optkomponen."</select></td>
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