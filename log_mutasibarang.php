<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/log_mutasi.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
include('master_mainMenu.php');

if(isTransactionPeriod())//check if transaction period is normal
{
OPEN_BOX('','<span class=judul>'.getMenu('log_mutasibarang').'</span><br>');

$frm[0]='';
$frm[1]='';
echo "<fieldset><legend>";

echo" <b>".$_SESSION['lang']['periode'].": <span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</span></b>";
echo"</legend>";
#kodeorganisasi untuk klinik harus berakhiran PK
if(($_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['tipelokasitugas']=='HOLDING')  and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG'
       and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
       order by namaorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";
}else{
	$gudangdivisi='';
	$gudangxxx=makeOption($dbname,'kebun_5gudangtransaksi','afdeling,kodegudang',"status='1'");
	if($_SESSION['empl']['subbagian']!='' and $gudangxxx[$_SESSION['empl']['subbagian']]!=''){
		$gudangdivisi=" and kodeorganisasi ='".$gudangxxx[$_SESSION['empl']['subbagian']]."'";
	}

	$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
	$gudang_detailAkses=" and induk IN (".$unitDetailAkses.") ";

	if(count($unitDetailAkses) > 0){
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$gudang_detailAkses." and tipe like 'GUDANG%' order by kodeorganisasi";
	}else{
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' 
       	or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') ".$gudangdivisi." and tipe like 'GUDANG%' order by kodeorganisasi";
	}


}
// echo $str;

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	$d=substr($bar->kodeorganisasi,0,4);
	if($d!=$n){	
		$optsloc.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}

		$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
		$n=$d;

	if($d!=$n){		
		$optsloc.="</optgroup>";
	}
}

echo"<fieldset  style=float:left>
     <legend>
	 ".$_SESSION['lang']['daftargudang']."
     </legend>
	  ".$_SESSION['lang']['pilihgudang']." : <select class=select2 id=sloc onchange=getPT(this.options[this.selectedIndex].value)>".$optsloc."</select>
	   ".$_SESSION['lang']['ptpemilikbarang']." : <select class=select2 id=pemilikbarang style='width:200px;'>
	   <option value=''></option>
	   </select>
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['cancel']."</button>
	  
	 </fieldset>";
	 

echo"<fieldset style=float:left>
     <legend>
	 ".$_SESSION['lang']['info']."
     </legend>
	  Jika setelah simpan gudang <b>No.Dokumen</b> tidak muncul,<br>coba cek periode akuntansi gudangnya.
	 </fieldset><div style=clear:both></div>";	 
	 
$kodept = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$ptinduk = $kodept[$_SESSION['empl']['lokasitugas']];





$tipeg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"tipe='GUDANG'");
	 
$inti_plasma=1;
$cekApakahdivisi=0;
$optlokasitujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' ){
	$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' ){
	$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}else if($_SESSION['empl']['tipelokasitugas'] == 'BULKING' ){
	$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (".getOrgDetail(2).")) and induk!='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}else{
	$gudangdivisi='';
	$gudangxxx=makeOption($dbname,'kebun_5gudangtransaksi','afdeling,kodegudang',"status='1'");
	if($_SESSION['empl']['subbagian']!='' and $gudangxxx[$_SESSION['empl']['subbagian']]!=''){
		$gudangdivisi=" and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
		$cekApakahdivisi=1;
		$cek_i_p="select inti from ".$dbname.".organisasi where tipe like 'GUDANG%' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' limit 1 ";
		$stq_cek_i_p = fetchdata($cek_i_p);
		$inti_plasma = $stq_cek_i_p[0]['inti'];
	}else{
		if($_SESSION['empl']['lokasitugas']=='SDKM'){			
			$tambahksbw=" or kodeorganisasi = 'KSBW52' ";
		}
	}
	// sri rahayu grup: tambah KSBW... SDKM mau mutasi ke KSBW tidak bisa karena beda regional
	$gudangdivisi.=" and kodeorganisasi not like '".$_SESSION['empl']['lokasitugas']."%'";
	// $str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and (induk in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') ".$tambahksbw." ) ".$gudangdivisi." order by kodeorganisasi";
	$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' ".$gudangdivisi." order by kodeorganisasi";
}
$optlokasitujuan.="<optgroup label='".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."'>";
if($cekApakahdivisi == 1){
	if($inti_plasma == 0){
		$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' order by kodeorganisasi";
	}else{
		$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by kodeorganisasi";
	}
}else{
	$sql="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and (kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' or inti='0')  order by kodeorganisasi";
}
$stq = fetchdata($sql);
foreach($stq as $val){
	$optlokasitujuan.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}
$optlokasitujuan.="</optgroup>";

// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''></option>";
$n='';
while($bar=$res->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($bar->kodeorganisasi,0,4)."'");
	$d=$induk[substr($bar->kodeorganisasi,0,4)];

	if($d!==$n && $n!==""){			
		$optlokasitujuan.="</optgroup>";
	}

	if($d!=$n){			
		$optlokasitujuan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	if(substr($bar->kodeorganisasi,0,4)==$_SESSION['empl']['lokasitugas']){		
		$optlokasitujuan.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}elseif($tipeg[$bar->kodeorganisasi]!=''){
		$optlokasitujuan.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}
	
	$n=$d;
	if($d!=$n){			
		$optlokasitujuan.="</optgroup>";
	}
}

$str="  select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe = 'GUDANG' and (induk in (select kodeorganisasi from ".$dbname.".organisasi where tipe IN ('KANWIL','BULKING') and induk = '".$ptinduk."')) and induk!='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
$res = fetchdata($str);
foreach($res as $bar){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($bar['kodeorganisasi'],0,4)."'");
	$d=$induk[substr($bar['kodeorganisasi'],0,4)];
	if($d!=$n){			
		$optlokasitujuan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optlokasitujuan.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optlokasitujuan.="</optgroup>";
	}
}


$optsubunit="<option value=''></option>";

// Surat Jalan
$optSJ = array(""=>$_SESSION['lang']['pilihdata']);

//===================================
$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
     <tr>
		<td>".$_SESSION['lang']['momordok']."</td><td>:</td>
		<td><input type=text id=nodok style='width:245px;' disabled class=myinputtext></td>	 
	    <td class='bintang'>".$_SESSION['lang']['tanggal']."</td><td>:</td><td>
		     <input style=width:80px type=text class=myinputtext id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly>
		</td>
	 </tr>
	 <tr>
	 <td class='bintang'>".$_SESSION['lang']['tujuan']."</td><td>:</td><td><select class=select2 id=kegudang style='width:250px;' onchange=cekGudang(this)>".$optlokasitujuan."</select></td>
 	 <td>".$_SESSION['lang']['note']."</td><td>:</td><td><input type=text id=catatan name=catatan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style='width:245px;' maxlength=80></td>
	 </td>
	 </tr>";
// if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){   
$frm[0].="<tr>";
// $frm[0].='<td>Surat Jalan</td><td colspan="2">:
	// <select id="noSj" onchange="getdatabarang(this);">
	// </select>
	// <img id="noSj_find" onclick="z.elSearch(\'noSj\',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
	// </td>';
	
	$frm[0].="<td>Surat Jalan</td><td>:</td><td><input type=text id=noSj_find name=noSj_find class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
	
	<td>Expeditor </td><td>:</td><td><input type=text id=expeditor name=expeditor class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
	</tr>
	<tr>
		<td>Jenis Kendaraan </td><td>:</td><td><input type=text id=jeniskendaraan name=jeniskendaraan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
		<td>No Pol </td><td>:</td><td><input type=text id=nopol name=nopol class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
	</tr>
	<tr>
		<td>Driver </td><td>:</td><td><input type=text id=driver name=driver class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
		<td> HP Driver </td><td>:</td><td><input type=text id=hpdriver name=hpdriver class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 ></td>
	</tr>
";
// }
$frm[0]	.="
	 </table>";
//==================masukkan variable periode gudang
//$sess=$_SESSION['gudang'];
foreach($_SESSION['gudang'] as $key=>$val){
 //  echo	$sess[$key]['start'];

	$frm[0].="<input type=hidden id='".$key."_start' value='".$_SESSION['gudang'][$key]['start']."'>
	     <input type=hidden id='".$key."_end' value='".$_SESSION['gudang'][$key]['end']."'>
		";
}	 
	 
$frm[0].="</fieldset>
	<fieldset hidden>
		<legend>Tambah dari Surat Jalan</legend>
		".makeElement('noSj','selectsearch','',array(),$optSJ).
		makeElement('btnSj','btn',$_SESSION['lang']['tambah'],array('onclick'=>'tambahSj()'))."
	</fieldset>
	<input type=hidden id=isNewTrans value=0 /><input type=hidden id=jns value='' />
	<fieldset>
	   <legend>".$_SESSION['lang']['detail']."</legend>";

$frm[0]	.="<div id=container>
			  <script>getdata('container','defaulttemplatedetail');</script>
		</div>	   
	 </fieldset>

    <fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	   <table class=sortable cellpadding=5 cellspacing=1 border=0>
		   <thead>
		   <tr class=rowheader>
		   <th align=center>No</th>
		    <th align=center>".$_SESSION['lang']['kodebarang']."</th>
			<th align=center>".$_SESSION['lang']['namabarang']."</th>
			<th align=center>".$_SESSION['lang']['satuan']."</th>
			<th align=center>".$_SESSION['lang']['jumlah']."</th>
			<!--<th align=center>".$_SESSION['lang']['nopo']."</th>-->
			<th align=center colspan=2>Action</th>
 		   </tr>
		   </thead>
			   <tbody id=bastcontainer>			   
			   </tbody>
		   <tfoot>
		   </tfoot>
	   </table>
	 </fieldset>
	 	 
	 ";
	 
$frm[1].="
	  <fieldset style=float:left>
		<table>
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>
					<input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=12>
				</td>
			
				<td>".$_SESSION['lang']['nosj']."</td>
				<td>:</td>
				<td>
					<input type=text id=txbnosj size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
				</td>
			
			
				<td>
					<button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button>
				</td>
			</tr>
		</table>
	  </fieldset>
	  <div style='clear:both'></div>
	  <br>
	  <table class=sortable cellpadding=5 cellspacing=1 border=0 >
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <th align=center>".$_SESSION['lang']['sloc']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>
	  <th align=center>".$_SESSION['lang']['nosj']."</th>
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pemilik']."</th>
	  <th align=center>".$_SESSION['lang']['tujuan']."</th>	  	 
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['posted']."</th>
	  <th align=center colspan=3>Action</th>
	  </tr>
	  </head>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	  
	 ";	 
//========================
$hfrm[0]=$_SESSION['lang']['mutasi'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================	 
}
else
{
	echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>