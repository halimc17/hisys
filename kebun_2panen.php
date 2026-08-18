<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_panen.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2panen').'</span>');
//get existing period
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_aktifitas
      where tipetransaksi = 'PNN' order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optper="";
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	
//=================ambil PT;  
// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
//       where tipe='PT'
//           order by namaorganisasi";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar=$res->fetch())
// {
// 	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	
// }
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrunit = getOrgDetail(3);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optpt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optpt.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optpt.="</optgroup>";
	}
}

//=================ambil gudang;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                where tipe='KEBUN'";

$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
$optblok="<option value=''>".$_SESSION['lang']['all']."</option>";

$optpil="<option value='fisik'>".$_SESSION['lang']['fisik']."</option>";
$optpil.="<option value='lokasi'>".$_SESSION['lang']['lokasi']."</option>";

//===ambil inti/plasma===//
$arrOptIP = getEnum($dbname,'setup_blok','intiplasma');
// $optIP = '';
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOptIP as $val){
	if($val=="I"){
		$optIP .= "<option value='".$val."'>Inti</option>";
	}else{
		$optIP .= "<option value='".$val."'>Plasma</option>";
	}
}

$frm[0]="<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select class=select2 id=pt style='width:170px;' onchange=getKbn();hideById('printPanel')>".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kebun']."</td>
				<td>:</td>
				<td><select class=select2 id=gudang style='width:170px;' onchange=getAfd();hideById('printPanel')>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td><select class=select2 id=afdeling style='width:170px;' onchange=hideById('printPanel')>".$optgudang."</select></td>
			</tr>
			<tr hidden>
				<td>".$_SESSION['lang']['intiplasma']."</td>
				<td>:</td>
				<td><select class=select2 id=intiplasma style=width:170px; >".$optIP."</select></td>
				
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=8 maxlength=10 value='".date('01-m-Y')."' readonly> - <input type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10 value='".date('d-m-Y')."' readonly>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td><button class=mybutton onclick=getLaporanPanen()>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=getLaporanPanen('excel')>".$_SESSION['lang']['excel']."</button></td>
			</tr>
		</table>
         </fieldset>
		 <div style='clear:both'></div>";
$frm[0].="<span id=printPanel style='display:none;'>
     <!--<img onclick=fisikKeExcel(event,'kebun_laporanPanen_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>-->
	 <!--<img onclick=fisikKePDF(event,'kebun_laporanPanen_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>-->
	 </span>    
	 <div  id=container style='min-height:359px;'></div>";

$frm[1]="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['perusahaan']."</td>
				<td>:</td>
				<td><select class=select2 id=pt_1 name=pt_1 style='width:170px;' onchange=getKbn_1()>".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kebun']."</td>
				<td>:</td>
				<td><select class=select2 id=unit_1 name=unit_1 style=width:170px; onchange=getAfd_1();bersih_1()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td><select class=select2 id=afdeling_1 style='width:170px;' onchange=bersih_1()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['intiplasma']."</td>
				<td>:</td>
				<td><select class=select2 id=intiplasma_1 style=width:170px; >".$optIP."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tgl1_1 onchange=bersih_1() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=8 maxlength=10 value='".date('01-m-Y')."' readonly> - <input type=text class=myinputtext id=tgl2_1 onchange=bersih_1() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10 value='".date('d-m-Y')."' readonly></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td><button class=mybutton onclick=getLaporanPanen_1() >".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=getLaporanPanen_1('excel') >".$_SESSION['lang']['excel']."</button>
					<input type=hidden name=hidden_1 id=hidden_1 value=hiddenvalue1 /></td>
			</tr>
		</table>";

$frm[1].="</fieldset><div style=\"clear:both;\"></div>";
$frm[1].="<span id=printPanel_1 style='display:none;'>
     <!--<img onclick=laporanKeExcel_1(event,'kebun_laporanPanen_tanggal_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>--> 
	 <!--<img onclick=laporanKePDF_1(event,'kebun_laporanPanen_tanggal_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>-->
	 </span>    
	 <div class='table-scroll' id=container_1 style='min-height:450px;'>
	 </div>	 Note : Fill warna merah jika dalam satu blok ada panen lebih dari 1 hari";

$frm[2]="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['perusahaan']."</td>
				<td>:</td>
				<td><select class=select2 id=pt_2 name=pt_2 style='width:170px;' onchange=getKbn_2()>".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kebun']."</td>
				<td>:</td>
				<td><select class=select2 id=unit_2 name=unit_2 style=width:170px; onchange=getAfd_2();bersih_2()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td><select class=select2 id=afdeling_2 style='width:170px;' onchange=bersih_2()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['intiplasma']."</td>
				<td>:</td>
				<td><select class=select2 id=intiplasma_2 style=width:170px; >".$optIP."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tgl1_2 onchange=bersih_2() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=8 maxlength=10 value='".date('01-m-Y')."' readonly> - <input type=text class=myinputtext id=tgl2_2 onchange=bersih_2() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10 value='".date('d-m-Y')."' readonly></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['pilih']."</td>
				<td>:</td>
				<td><select class=select2 id=pil_2 name=pil_2 style='width:170px;'><option value='fisik'>".$_SESSION['lang']['fisik']."</option></select></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td><button class=mybutton onclick=getLaporanPanen_2() >".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=getLaporanPanen_2('excel') >".$_SESSION['lang']['excel']."</button>
					<input type=hidden name=hidden_2 id=hidden_2 value=hiddenvalue2 /></td>
			</tr>
		</table>";

$frm[2].="</fieldset><div style=\"clear:both;\"></div>";
$frm[2].="<span id=printPanel_2 style='display:none;'></span>
         <div style='min-height:359px;' id=container_2 class='table-scroll'></div>";

$frm[3]="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[3].="<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['perusahaan']."</td>
				<td>:</td>
				<td><select class=select2 id=pt_3 name=pt_3 style='width:170px;' onchange=getKbn_3()>".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kebun']."</td>
				<td>:</td>
				<td><select class=select2 id=unit_3 name=unit_3 style=width:170px; onchange=getAfd_3();bersih_3()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td><select class=select2 id=afdeling_3 style='width:170px;' onchange=bersih_3()>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['intiplasma']."</td>
				<td>:</td>
				<td><select class=select2 id=intiplasma_3 style=width:170px;>".$optIP."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tgl1_3 onchange=bersih_3() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=8 maxlength=10 value='".date('01-m-Y')."' readonly> - <input type=text class=myinputtext id=tgl2_3 onchange=bersih_3() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10 value='".date('d-m-Y')."' readonly></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td><button class=mybutton onclick=getLaporanPanen_3() >".$_SESSION['lang']['preview']."</button>
					<input type=hidden name=hidden_3 id=hidden_3 value=hiddenvalue2 /></td>
			</tr>
		</table>";

$frm[3].="</fieldset><div style=\"clear:both;\"></div>";
$frm[3].="<span id=printPanel_3 style='display:none;'>
     <img onclick=laporanKeExcel_3(event,'kebun_laporanPanen_spbwb_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
         </span>    
         <div style='min-height:359px;' class='table-scroll'>
			   <table class=sortable cellspacing=1 border=0 id=container_3>
           </table>
     </div>";

$frm[4]="<fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select class=select2 id='pt_4' style='width:170px;' onchange=\"getKbn_4();hideById('printPanel_4')\">".$optpt."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td><select class=select2 id='gudang_4' style='width:170px;' onchange=\"getAfd_4();hideById('printPanel_4')\">".$optgudang."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td><select class=select2 id='afdeling_4' style='width:170px;' onchange=\"getBlok_4();hideById('printPanel_4')\">".$optgudang."</select></td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['intiplasma']."</td>
			<td>:</td>
			<td><select class=select2 id='intiplasma_4' style=width:170px; >".$optIP."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['blok']."</td>
			<td>:</td>
			<td><select class=select2 id='blok_4' style='width:170px;' onchange=hideById('printPanel_4')>".$optblok."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id='tgl1_4' onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=8 maxlength=10 value='".date('01-m-Y')."' readonly> - <input type=text class=myinputtext id='tgl2_4' onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=9 maxlength=10 value='".date('d-m-Y')."' readonly>
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><button class=mybutton onclick=getLaporanPanen_4()>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=getLaporanPanen_4('excel')>".$_SESSION['lang']['excel']."</button></td>
		</tr>
	</table>
</fieldset>
		<div style='clear:both'></div>";
$frm[4].="<span id='printPanel_4' style='display:none;'>
	 </span>    
	 <div  id='container_4' style='min-height:359px;'></div>";

//========================
$hfrm[0]=$_SESSION['lang']['laporanpanen']." ".$_SESSION['lang']['detail'];
$hfrm[1]=$_SESSION['lang']['laporanpanen']." per ".$_SESSION['lang']['tanggal'];
$hfrm[2]=$_SESSION['lang']['laporanpanen']." per ".$_SESSION['lang']['orang'];
$hfrm[3]=$_SESSION['lang']['laporanpanen']." SPB vs WB";
$hfrm[4]=$_SESSION['lang']['laporanpanen']." ".$_SESSION['lang']['detail']." Per ".$_SESSION['lang']['blok'];
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================

close_body();
CLOSE_BOX();
?>