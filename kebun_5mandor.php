<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/kebun_5mandor.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
	$dataunitx='';
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
	$res=fetchdata($str);
	foreach($res as $val){
		if($dataunitx==""){
			$dataunitx.="'".$val['kodeorganisasi']."'";				
		}else{
			$dataunitx.=",'".$val['kodeorganisasi']."'";				
		}
	}

	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
	$res=fetchdata($str);
	foreach($res as $val){
		if($dataunitx==""){
			$dataunitx.="'".$val['kodeorganisasi']."'";				
		}else{
			$dataunitx.=",'".$val['kodeorganisasi']."'";				
		}
	}
}

if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
	$dataunitx='';
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
	$res=fetchdata($str);
	foreach($res as $val){
		if($dataunitx==""){
			$dataunitx.="'".$val['kodeorganisasi']."'";				
		}else{
			$dataunitx.=",'".$val['kodeorganisasi']."'";				
		}
	}

	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
	$res=fetchdata($str);
	foreach($res as $val){
		if($dataunitx==""){
			$dataunitx.="'".$val['kodeorganisasi']."'";				
		}else{
			$dataunitx.=",'".$val['kodeorganisasi']."'";				
		}
	}
}

if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
	$dataunitx='';
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
	$res=fetchdata($str);
	foreach($res as $val){
		if($dataunitx==""){
			$dataunitx.="'".$val['kodeorganisasi']."'";				
		}else{
			$dataunitx.=",'".$val['kodeorganisasi']."'";				
		}
	}
}

$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg in (".$dataunitx.") and tipe in ('BKM','PNN')";
$res=fetchdata($str);
foreach($res as $bar){
	//if($bar['kolom']=='mandor'){
		$mdr=$bar['jabatan'];
	//}
}

$d=$n="";
if($mdr!=''){
	// $whr=" and t1.kodejabatan in (".$mdr.")";
}else{
	// $whr=" and t2.namajabatan like '%mandor%' or t2.namajabatan  like '%mandor%1%' or t2.namajabatan  like '%kadiv%' or t2.namajabatan  like '%asis%'";
}

$whr="";

$optmandor='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
$str="select t1.karyawanid, t1.nik, t1.namakaryawan, t2.namajabatan, t1.subbagian from ".$dbname.".datakaryawan t1
	left join ".$dbname.".sdm_5jabatan t2 on t1.kodejabatan=t2.kodejabatan where 1=1 ".$whr."
	and t1.lokasitugas in (".$dataunitx.") 
	and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") 
	and t1.alokasi = 0 
	and not exists (select t3.karyawanid from ".$dbname.".kebun_5mandor t3 where t1.karyawanid=t3.karyawanid)
    order by t2.namajabatan, t1.namakaryawan";
// echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$d=$bar->namajabatan;
	if($d!=$n){			
		$optmandor.="<optgroup label='".$d."'>";
	}
    $optmandor.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [ ".($bar->nik)." ]</option>";
	$n=$d;
	if($d!=$n){			
		$optmandor.="</optgroup>";
	}
}



$where = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$optdiv = "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') ".$where." order by kodeorganisasi";
$res = fetchData($str);
foreach($res as $key => $val){
	$n="";
	if($_SESSION['empl']['subbagian']==$val['kodeorganisasi']){
		$n="selected";
	}
	$optdiv.="<option value=".$val['kodeorganisasi']." ".$n.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
}

$optkaryawan='<option value=\'\'></option>';
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5mandor').'</span><br>');

echo"<div id=forminputdetail><fieldset>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table border=0>
	 <tr>
	   <td>".$_SESSION['lang']['mandor']."</td>
	   <td>:</td>
	   <td> <select onchange=\"pilihmandor();\" id=mandor style='width:200px'>".$optmandor."</select>
	   <img id='mandor' onclick=z.elSearch('mandor',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	   </td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['karyawan']."</td>
	   <td>
                :</td>
	   <td> <select id=karyawan onchange=\"getnourut();\" style='width:200px'>".$optkaryawan."</select>
				<img id='karyawan' onclick=z.elSearch('karyawan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['urutan']."</td>
		<td>
			:</td>
	   <td> <input type=text class=myinputtext onkeypress=\"return angka_doang(event);\" id=urut size=3 maxlength=3 class=myinputtextnumber>
		</td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>
			:</td>
	   <td> <select id=status disabled>
				<option value='1'>Aktif</option>
				<option value='0'>Tidak Aktif</option>
			</select>
		</td>
	 </tr>
	 <tr>
		<td></td>
		<td></td>
		<td>
			 <input type='hidden' id='procces' value='tambahkaryawan'>
			 <button class=mybutton onclick=tambahkaryawan()>".$_SESSION['lang']['save']."</button>
			 <button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			 <button class=mybutton onclick=multiinput('multi')>Multiple</button>
		</td>
	 </tr>
	 
	 <tr>
	   <td colspan=4><hr></td>
	 </tr>
	 <tr>
	   <td colspan=3><div id=anggota style='display:none'></td>
	 </tr>
	 </table>
     </fieldset></div>";
	 
echo"<div  id=forminputtabular style=display:none><fieldset>
     <legend>".$_SESSION['lang']['form']."</legend>
		<table border=0>
	 <tr>
	   <td>".$_SESSION['lang']['divisi']."</td>
	   <td>:</td>
	   <td><select id=divisi style='width:200px'>".$optdiv."</select>
	   </td>
	 </tr>
	 <tr>
		<td></td>
		<td></td>
		<td>
			 <button class=mybutton onclick=lihatdatakary()>".$_SESSION['lang']['preview']."</button>
			 <button class=mybutton onclick=multiinput('single')>Single</button>
		</td>
	 </tr>
	 </table>
	 
     </fieldset></div>";
CLOSE_BOX();

OPEN_BOX();

echo"<div id=listdatadetail><table class=sortable cellspacing=1 cellpadding=5 border=0>
     <thead>
	  <tr class=rowheader>
	   <th align=center>No</th>
	   <th align=center>".$_SESSION['lang']['nik']."</th>
	   <th align=center>".$_SESSION['lang']['mandor']."</th>
	   <th align=center>".$_SESSION['lang']['kodeorg']."</th>
	   <th align=center>".$_SESSION['lang']['divisi']."</th>
	   <th colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</th>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>tampilmandor()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset></div>";
	 
echo"<div id=previewdatatabular style=display:none></div>";	 
CLOSE_BOX();
echo close_body();
?>