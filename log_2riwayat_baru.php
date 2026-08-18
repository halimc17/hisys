<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
require_once('master_mainMenu.php');
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script type="text/javascript" src="js/log_2riwayatPP.js" /></script>
<script type="text/javascript" src="js/log_pp.js" /></script>
<script type="text/javascript" src="js/log_link.js" /></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php

$arrPil = array(
    "1" => $_SESSION['lang']['proses'] . ' ' . $_SESSION['lang']['persetujuan'] . ' ' . $_SESSION['lang']['prmntaanPembelian'],
    "2" => $_SESSION['lang']['proses'] . ' ' . $_SESSION['lang']['purchasing'],
    "3" => $_SESSION['lang']['jmlh_brg_sdh_po'],
    "4" => $_SESSION['lang']['jmlh_brg_blm_po'],
    "5" => $_SESSION['lang']['ditolak']);
$optPil = '';
foreach ($arrPil as $id => $isi) {
    $optPil.="<option value=" . $id . ">" . $isi . "</option>";
}

$optLokal = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrPo = array("0" => "Pusat", "1" => "Lokal");
foreach ($arrPo as $brsLokal => $isiLokal) {
    $optLokal.="<option value=" . $brsLokal . ">" . $isiLokal . "</option>";
}

$tempTahun="";
$sTgl = "select distinct substr(tanggal,1,7) as periode from " . $dbname . ".log_prapoht order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$optper = "";
while ($rTgl = $qTgl->fetch()) {
    if ($tempTahun != substr($rTgl['periode'], 0, 4)) {
        $tempTahun = substr($rTgl['periode'], 0, 4);
        $optper.="<option value='" . substr($rTgl['periode'], 0, 4) . "'>" . substr($rTgl['periode'], 0, 4) . "</option>";
    }
    $optper.="<option value='" . $rTgl['periode'] . "'>" . substr($rTgl['periode'], 5, 2) . "-" . substr($rTgl['periode'], 0, 4) . "</option>";
}

$optPersetujuan = '';
$arrPersetujuan = array();
// for ($i = 1; $i < 6; $i++) {
    // $sPersetujuan = "select distinct(a.persetujuan" . $i . ") as persetujuan, b.namakaryawan as namakaryawan from " . $dbname . ".log_prapoht a 
				// left join " . $dbname . ".datakaryawan b on a.persetujuan" . $i . " = b.karyawanid 
				// where a.persetujuan" . $i . " != NULL or a.persetujuan" . $i . " != '' order by b.namakaryawan asc";
    // $qPersetujuan=$owlPDO->query($sPersetujuan) or die(print " Gagal: ".PDOException::getMessage());
	// $qPersetujuan->setFetchMode(PDO::FETCH_ASSOC);
    // while ($rPersetujuan = $qPersetujuan->fetch()) {
        // $arrPersetujuan[$rPersetujuan['persetujuan']] = array("nik" => $rPersetujuan['persetujuan'], "nama" => $rPersetujuan['namakaryawan']);
    // }
// }
foreach ($arrPersetujuan as $value) {
    $optPersetujuan.="<option value='" . $value['nik'] . "'>" . $value['nama'] . "</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{   
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    while($bar=$res->fetch())
    {
        if ($bar->kodeorganisasi==$_SESSION['empl']['kodeorganisasi']) {
           $optpt.="<option value='".$bar->kodeorganisasi."' selected>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
        }else{
            $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
        }
    }
}
else
{
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);                   
    $bar=$res->fetch();
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}





#= opt pt terbaru


# ambil unit
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lstUnit=getOrgDetail(1);
$dtMul=0;
$listOrg='';
foreach($lstUnit as $row=>$isiDt){
    if(substr($row,0,5)=='Pilih'){
        continue;
    }
    if($dtMul==0){
        $listOrg="'".$row."'";
        $dtMul=1;
    }else{
        $listOrg.=",'".$row."'";
    }
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$listOrg.")";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";    
}

$str="select * from ".$dbname.".sdm_5departemen";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optdept.="<option value='".$bar['kode']."'>".$bar['kode']." - ".$bar['nama']."</option>";    
}











##Status PO
$stPo = array("0" => "Belum Selesai", "1" => "sudah selesai dan diajukan", "2#PO" => "Purchase Order (PO) sudah dapat di kirim (persetujuan selesai)", "2#SO" => "Service Order (SO) tidak ada pengiriman (persetujuan selesai)", "3" => "Barang sudah diterima");
$optstatuspo = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct statuspo from ".$dbname.".log_poht order by statuspo";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);                   
while($bar=$res->fetch())
{
    if ($bar->statuspo=='2') {
        $optstatuspo.="<option value='2#PO'>".$stPo['2#PO']."</option>";
        $bar->statuspo=$bar->statuspo."#SO";
    }
    $optstatuspo.="<option value='".$bar->statuspo."'>".$stPo[$bar->statuspo]."</option>";
}

$datefirst = date("01-m-Y");
$datenow = date("d-m-Y");

$arr = "##nopp##tgl##per1##per2##lok##stat##sup##nama##psj##previewdata##statuspo##dept"; // style='float:left;'

OPEN_BOX('','<span class=judul>'.getMenu('log_2riwayat_baru').'</span><br>');

echo"<fieldset style='float:left;'>
        <legend><b>Form</b></legend>
            <table cellpadding=1 cellspacing=1 border=0>
                <tr>
                    <td>" . $_SESSION['lang']['nopp'] . "</td>
                    <td>:</td>
                    <td><input type='text' id='nopp' name='nopp' onkeypress='return tanpa_kutip(event)' style='width:150px' class=myinputtext /></td>
                    
                    <td>" . $_SESSION['lang']['tanggal'] . " PR/SR </td>
                    <td>:</td>
                    <td><input type=text class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;   maxlength=10 style=width:150px readonly/></td>

                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><input type=text class=myinputtext id=per1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datefirst."' readonly/> S/D
                    <input type=text class=myinputtext id=per2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datenow."' readonly/></td>

                    <td>".$_SESSION['lang']['status']." PO</td>
                    <td>:</td>
                    <td><select class=select2 id=statuspo style='width:155px;'>".$optstatuspo."</select></td>

					<td>Close PO</td>
                    <td>:</td>
                    <td><input type=checkbox id=previewdata></td>
                </tr>
                <tr>

                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=lok style='width:155px;'>" . $optpt . "</select></td>
                    
					<td>" . $_SESSION['lang']['namabarang'] . "</td>
                    <td>:</td>
                    <td><input type='text' id='nama' name='namabarang' onkeypress='return tanpa_kutip(event)' style='width:150px' class=myinputtext /></td> 
                    
                     <td>" . $_SESSION['lang']['supplier'] . "</td>
                    <td>:</td>
                    <td><input type='text' id='sup' name='supplier' onkeypress='return tanpa_kutip(event)' style='width:168px' class=myinputtext /></td> 

					<td>" . $_SESSION['lang']['status'] . " PR/SR</td>
                    <td>:</td>
                    <td><select class=select2 id=stat name=stat style='width:155px;'><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optPil . "</select></td>
					
					<td>" . $_SESSION['lang']['departemen'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=dept name=dept style='width:155px;'><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optdept . "</select></td>
                </tr>
                <tr style='display:none'>
                    
					<td>" . $_SESSION['lang']['persetujuan'] . "</td>
                    <td>:</td>
                    <td><select id=psj name=psj style='width:150px;'><option value=''>" . $_SESSION['lang']['all'] . "</option>" . $optPersetujuan . "</select></td>     
                </tr>";
echo"           <tr>
                    <td><td><td colspan=4>
                    <button onclick=zPreview('log_slave_2riwayat_baru','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'log_slave_2riwayat_baru.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>"; // <button onclick=zPdf('log_slave_2riwayat','".$arr."','printContainer')  class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

CLOSE_BOX();

OPEN_BOX();
echo "
<div id='printContainer' style='overflow:auto;height:65vh;width:100%' class='table-scroll'></div>";
CLOSE_BOX();
echo close_body();
