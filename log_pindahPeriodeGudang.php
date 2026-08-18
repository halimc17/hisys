<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_pindahPeriodeGudang.js'></script>
<script language=javascript src='js/log_rekalgudang.js'></script>
<?php

include('master_mainMenu.php');

if (isTransactionPeriod()) {//check if transaction period is normal
    OPEN_BOX('','<span class=judul>'.getMenu('log_pindahPeriodeGudang').'</span><br>'); 

	$str="select a.kodeorganisasi,a.namaorganisasi,b.periode,b.tanggalmulai,b.tanggalsampai
    from ".$dbname.".organisasi a left join ".$dbname.".setup_periodeakuntansi b on a.kodeorganisasi=b.kodeorg
    where a.kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and a.tipe like 'GUDANG%' and b.tutupbuku=0"; 
	$res=fetchdata($str);
	$awalperiode = $res[0]['tanggalmulai'];
	$akhirperiode = $res[0]['tanggalsampai'];
	
    $frm[0] = '';
    $frm[1] = '';
    echo "<fieldset style=float:left><legend>";
    echo" <b>" . $_SESSION['lang']['periode'] . ": <span id=displayperiod>" . tanggalnormal($awalperiode) . " - " . tanggalnormal($akhirperiode) . "</span></b>";
    echo"</legend>";

	if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
        $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where (tipe='GUDANG' 
            and induk in(select kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_SESSION['empl']['regional'] . "'))
            or ( kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%' and tipe like 'GUDANG%')
            order by namaorganisasi";
    }else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
        $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where (tipe='GUDANG' 
            and induk in(select kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_SESSION['empl']['regional'] . "'))
            or ( kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%' and tipe like 'GUDANG%')
            order by namaorganisasi";
    } else {
        $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
    }
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $optsloc = "<option value=''></option>";
    while ($bar = $res->fetch()) {
        $optsloc.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
    }

    echo"<fieldset style=float:left>
     <legend>
	 " . $_SESSION['lang']['daftargudang'] . "
     </legend>
	  " . $_SESSION['lang']['pilihgudang'] . ": <select id=sloc>" . $optsloc . "</select>
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>" . $_SESSION['lang']['save'] . "</button>
	   <button onclick=setSloc('ganti') class=mybutton>" . $_SESSION['lang']['cancel'] . "</button>
	  
	 </fieldset>";

    $frm[0].="
          <div id=infoDisplay style=height:450px>

		  </div>
         ";
//	echo"<pre>";
//print_r($_SESSION['gudang']);		  
//	echo"</pre>";
//==================masukkan variable periode gudang
//$sess=$_SESSION['gudang'];
    foreach ($_SESSION['gudang'] as $key => $val) {
        //  echo	$sess[$key]['start'];

        $frm[0].="<input type=hidden id='" . $key . "_start' value='" . $_SESSION['gudang'][$key]['start'] . "'>
	     <input type=hidden id='" . $key . "_end' value='" . $_SESSION['gudang'][$key]['end'] . "'>
		";
    }
    // $frm[0].="</fieldset>";
	CLOSE_BOX();
	OPEN_BOX();
	echo $frm[0];
// //========================
    // $hfrm[0] = $_SESSION['lang']['daftarproses'];
// //draw tab, jangan ganti parameter pertama, krn dipakai di javascript
    // drawTab('FRM', $hfrm, $frm, 100, 900);
// //===============================================	 
} else {
    echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>