<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_laporan_jurnal_piutang_staff').'</span><br>');
$optnoakun="<option value=''></option>";
//list akun
// $str=$owlPDO->query("select b.noakun, b.namaakun from  ".$dbname.".keu_5akun b 
//       where detail=1 and (noakun like '113%' or noakun like '114%' or noakun like '211%' or noakun like '118%') order by b.noakun"); 
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optnoakun="<option value=''></option>";
// while($bar=$str->fetch())
// {
//         $optnoakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
// }
//list org
$str=$owlPDO->query("select kodeorganisasi, namaorganisasi from  ".$dbname.".organisasi 
      where length(kodeorganisasi)=3 order by kodeorganisasi"); 

$str->setFetchMode(PDO::FETCH_OBJ);
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str->fetch())
{
    $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

// //list karyawan
// $str=$owlPDO->query("select a.nik, b.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
//       left join ".$dbname.".datakaryawan b on a.nik = b.karyawanid
//       where a.kodeorg ='".$_SESSION['empl']['lokasitugas']."' and a.nik!='0'
//       and a.nik != '' and a.noakun != '' group by a.nik order by b.namakaryawan"); // hanya menampilkan nama yang ada di jurnal 
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optnamakaryawan="<option value=''></option>";
// while($bar=$str->fetch())
// {
//         $optnamakaryawan.="<option value='".$bar->nik."'>".$bar->namakaryawan."</option>";
// }



echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
         ".$_SESSION['lang']['kodeorg']." <select id=kodeorg onchange=getNoakun() >".$optorg."</select>  
         ".$_SESSION['lang']['noakun']." <select id=noakun style=width:150px; >".$optnoakun."</select>";
echo"".$_SESSION['lang']['tanggalmulai']." : <input class=\"myinputtext\" id=\"tanggalmulai\" size=\"12\" onmousemove=\"setCalendar(this.id)\" maxlength=\"10\" onkeypress=\"return false;\" type=\"text\" readonly>
         s/d <input class=\"myinputtext\" id=\"tanggalsampai\" size=\"12\" onmousemove=\"setCalendar(this.id)\" maxlength=\"10\" onkeypress=\"return false;\" type=\"text\" readonly>";
         // ".$_SESSION['lang']['periode']." : <select  id=\"tanggalmulai\"  style=width:150px; >".$optnoakun."<select>
         // s/d <select  id=\"tanggalsampai\"  style=width:150px; >".$optnoakun."<select>
echo"<button class=mybutton onclick=getLaporanJurnalPiutangKaryawan()>".$_SESSION['lang']['proses']."</button>
         </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"
	 <span id=printPanel style='display:none;'>
     <img onclick=piutangKaryawanKeExcel(event,'keu_laporanJurnalPiutangKaryawan_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>
         </span>    
         <div style='width:100%;height:450px;overflow:auto;'>
         <div id=container></div>
         </div>";
CLOSE_BOX();
close_body();
?>