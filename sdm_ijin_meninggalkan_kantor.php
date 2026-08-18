<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_ijin_meninggalkan_kantor').'</span>');
$_SESSION['buktiizin'] = array();
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script>
	jdl_ats_0='<?php echo $_SESSION['lang']['find']?>';
	jdl_ats_1='<?php echo $_SESSION['lang']['findBrg']?>';
	content_0='<fieldset><legend><?php echo $_SESSION['lang']['findnoBrg']?></legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div>';
	
	nmSaveHeader='';
	nmCancelHeader='';
	nmDetialDone='<?php echo $_SESSION['lang']['done']?>';
	nmDetailCancel='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script type="application/javascript" src="js/sdm_ijin_meninggalkan_kantor.js?v=<?php echo time(); ?>"></script>
<?php
##Option Hours & Minute
$jm=$mnt="";
for($i=0;$i<24;){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$jm.="<option value=".$i.">".$i."</option>";
	$i++;
}

for($i=0;$i<60;){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$mnt.="<option value=".$i.">".$i."</option>";
	$i++;
}

$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
// exit('error: '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$optKary1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('7','8') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
// exit('error: '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optKary1.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

//Pengambilan karyawan HRD
$optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and bagian in ('HCGA') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optHRD.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
}

$optpengganti="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan,nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid!='".$_SESSION['standard']['userid']."' and tipekaryawan IN ('1','0','9') order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpengganti.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$str1=$owlPDO->query("select idjenis,jenisijin from ".$dbname.".sdm_5jenisijin where status='1'
      order by jenisijin");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str1->fetch()){
    $optJenis.="<option value='".$bar->idjenis."'>".$bar->jenisijin."</option>";
}


$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$_SESSION['standard']['userid']."'");
$departemen=$optdepartmen[$_SESSION['standard']['userid']];


##CEK PER DEPARTEMEN
$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$_SESSION['empl']['lokasitugas']."' and jenispersetujuan='IJS' and departemen='".$departemen."'";
$res=fetchdata($str);
$perdepartemen=$res[0]['kodeunit'];
$where="";
if($perdepartemen>0){
	$where.=" and a.departemen='".$departemen."'";
}else{
	$where.=" and a.departemen=''";
}

// echo$where;
$levelpersetujuan=0;
$optgol 	= makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
$optper4=$optper3=$optper2=$optper1="<option value=''>&nbsp;</option>";
$str="select distinct a.karyawanid,a.level, b.kodejabatan from ".$dbname.".setup_approval a
left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
where jenispersetujuan='IJS' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and a.golongan='".substr($optgol[$_SESSION['empl']['kodegolongan']],0,1)."' and a.karyawaniduser='".$_SESSION['standard']['userid']."' ".$where." ";
$res=fetchData($str);
if(count($res)==0){
	$str="select distinct a.karyawanid,a.level, b.kodejabatan from ".$dbname.".setup_approval a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where jenispersetujuan='IJS' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and a.golongan='".substr($optgol[$_SESSION['empl']['kodegolongan']],0,1)."' ".$where." ";
	$res=fetchData($str);
	if(count($res)==0){
		$str="select distinct a.karyawanid,a.level, b.kodejabatan from ".$dbname.".setup_approval a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where jenispersetujuan='IJS' and kodeunit='".$_SESSION['empl']['lokasitugas']."' ".$where." ";
		$res=fetchData($str);
		if(count($res)==0){			
			$str="select distinct a.karyawanid, a.level, b.kodejabatan from ".$dbname.".setup_approval a
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where jenispersetujuan='IJS' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and a.golongan='".substr($optgol[$_SESSION['empl']['kodegolongan']],0,1)."'";
			$res=fetchData($str);
		}
	}
}

if(count($res) > 0){
	foreach($res as $key => $bar){
		$whr		=" karyawanid='".$bar['karyawanid']."'";
		$optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
		$whrjbtn	=" kodejabatan='".$bar['kodejabatan']."'";
		$optjbtn 	= makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',$whrjbtn);
		if($bar['level']==1){
			$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==2){
			$optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==3){
			$optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==4){
			$optper4.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}

		if($levelpersetujuan<$bar['level'])
		{
			$levelpersetujuan=$bar['level'];
		}
	}
}else{
	
	$str="select a.*,b.kodejabatan from ".$dbname.".setup_approval a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where jenispersetujuan='IJS' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and a.golongan='".substr($optgol[$_SESSION['empl']['kodegolongan']],0,1)."' and a.karyawaniduser='' ".$where." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$whr		=" karyawanid='".$bar['karyawanid']."'";
		$optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
		$whrjbtn	=" kodejabatan='".$bar['kodejabatan']."'";
		$optjbtn 	= makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',$whrjbtn);
		if($bar['level']==1){
			$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==2){
			$optper2.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==3){
			$optper3.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}
		if($bar['level']==4){
			$optper4.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]." - [".$optjbtn[$bar['kodejabatan']]."]</option>";
		}

		if($levelpersetujuan<$bar['level'])
		{
			$levelpersetujuan=$bar['level'];
		}
	}
}
// print_r($res->fetch());

# Ambil tanggal masuk ybs//bandingkan tahunnnya jika ditahun yag sama maka hanya satu pilihan
$str="select right(tanggalmasuk,5) as tanggalmasuk,left(tanggalmasuk,4) as thn from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid'];
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$tglmasup='';
$hrini=date('md');#default
while($bar=$res->fetch()){
    $tglmasup=str_replace("-","",$bar->tanggalmasuk);#replace with data karyawan
	$thnmasuk=$bar->thn;
}
// echo($tglmasup);
// echo "<br>";
// echo($hrini);
// exit();
if($thnmasuk!=date('Y')){
	//if($tglmasup>$hrini){
		//  $tahunplafon=(date('Y')-1);
		$tahunplafon=(date('Y')-1);//dikurangi 2 thn
	//}else{
	//	$tahunplafon=date('Y');
	//}
}else{
	$tahunplafon=date('Y');
}

//    echo $tglmasup.'__'.$hrini;
#penguncian agar cuti yang sudah hangus tidak dapat diambil
$optPeriodec="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPeriodec.="<option value=".$tahunplafon.">".$tahunplafon."</option>";
if($thnmasuk!=date('Y')){
$optPeriodec.="<option value=".($tahunplafon+1).">".($tahunplafon+1)."</option>"; 
}

// $str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." and periodecuti=".$tahunplafon;
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// $sisa='';
// while($barf=$res->fetch()){
//     $sisa=$barf->sisa;
// }
// if($sisa==''){
//     $sisa=0;
// }

##################
$karyawanid = $_SESSION['standard']['userid'];
$hariini = date("Y-m-d");
$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	from ".$dbname.".sdm_cutiht a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1
	and a.periodecuti='".$tahunplafon."' 
	and a.karyawanid = '".$karyawanid."'
	and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan"; 
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$res1->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res1->fetch();
	$diambil=$bar['diambil'];
	$hakcuti=$bar['hakcuti'];
	$sisa=$bar['sisa'];

//Get Personalia by notransaksi : 
$sdhambil=0;
$str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijin a 
left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$tahunplafon."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by notransaksi asc";
$res=fetchdata($str);
foreach ($res as $key => $val){
	if($val['statuspersetujuan']=='1' && $val['statuspotongan']!='0') {
		$sdhambil+=$val['jumlahhari'];
	}
}

/**
 * Ini sisa sudah ambil cuti carry over
 * Harusnya bisa di refactor lagi tapi udah pusing
 */

$sdhambil2=array();
$str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan,darijam from ".$dbname.".sdm_ijin a 
left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$tahunplafon."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by darijam asc";
$res=fetchdata($str);
foreach ($res as $key => $val){
	if($val['statuspersetujuan']=='1' && $val['statuspotongan']!='0' ){
		$sdhambil2[substr($val['darijam'],0,4)]+=$val['jumlahhari'];
		$jlhhariambillast[substr($val['darijam'],0,4)] = $val['jumlahhari'];
	}
}

//Get Personalia by notransaksi : 
$sdhambil=0;
$str="select a.notransaksi,a.jumlahhari,a.stpersetujuan4,a.statuspersetujuan_cancel,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijin a
		left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis 
		where karyawanid='".$karyawanid."' and periodecuti='".$tahunplafon."' and a.statuspersetujuan_cancel = '0' order by notransaksi asc";
$res=fetchdata($str);
foreach ($res as $key => $val){
	if($val['stpersetujuan4']=='1' && ($val['statuspotongan']!='0')){
		$sdhambil+=$val['jumlahhari'];
	}
}

$sisaprdsblmnya=$sisaprdsblm-$sdhambil;

$periodenext = $tahunplafon+1;
$sisaprdsblm = $hakcuti;
$sisaprdsblm = $sisaprdsblm - $sdhambil;
if (substr($jmDr,0,4) != $tahunplafon) {//jika tanggal pengajuan tidak sesuai dengan periode cuti yang dipilih
	if($sdhambil2[$tahunplafon] == 12){//jika sisa cuti di periode yang dipilih sudah kosong
		$sisaprdsblm = 0 ;
	}else if($sdhambil2[$tahunplafon] > 6 ){// apabila jatah cuti periode yang dipilih di tahun yang sama dengan periode melebihi 6
		$sisaprdsblm = $hakcuti - $sdhambil2[$tahunplafon];
	}else{
		$sisaprdsblm = 6;
	}

	$sisaprdsblm = $sisaprdsblm - $sdhambil2[$periodenext];

}else{
	$sisaprdsblm = $sisaprdsblm - $sdhambil2[$tahunplafon];

}
##################

if ($_SESSION['empl']['tipekaryawan']==0) {
	$disable="";
}else{
	$disable="";
}

$optpoh = makeOption($dbname,'datakaryawan','karyawanid,lokasipenerimaan',"karyawanid='".$_SESSION['standard']['userid']."'");
$poh = $optpoh[$_SESSION['standard']['userid']];

echo"<input type='hidden' id='proses' name='proses' value='insert'  />
<div id='headher'>
<fieldset >
<legend>".$_SESSION['lang']['form']."</legend>
<table cellspacing='1' border='0'>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." Pengajuan</td>
		<td>:</td>
		<td>
			<input type='text' style='width:145px;' class='myinputtext' id='tglIzin' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' onkeypress=\"return false;\" value='".date('d-m-Y')."'  disabled>
		</td>
		<td id='tdkanan' rowspan=80 style='vertical-align:top;padding-left:10px'>
			<table cellspacing='1' border='0'>
				<tr>
					<td>".$_SESSION['lang']['keperluan']." <font size=2px style=color:red><b>*</b></font></td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' style='width:245px;' id='keperluan' name='keperluan' onkeypress='return tanpa_kutip(event);' maxlength='30' />
					</td>
				</tr>
				<tr>
					<td valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td valign=top>:</td>
					<td>
						<input type='text' class='myinputtext' style='width:245px;' id='ket' name='ket' onkeypress='return tanpa_kutip(event);'>
						<!--<textarea id='ket'  style='width:228px;'  onkeypress='return tanpa_kutip(event);'></textarea>-->
					</td>
				</tr>
				<tr> 
					<td valign=top>".$_SESSION['lang']['alamat']."</td>
					<td valign=top>:</td>
					<td valign=top>
						<input type='text' class='myinputtext' style='width:245px;' id='alamatcuti' name='alamatcuti' onkeypress='return tanpa_kutip(event);'>
						<!--<textarea id='alamatcuti'  style='width:228px;'  onkeypress=return tanpa_kutip(event);></textarea>-->
					</td>	
					<td>		 
				</tr>
				
				<tr> 
					<td>".$_SESSION['lang']['personalpengganti']."</td>
					<td>:</td>
					<td>
						<select id=pengganti style=width:250px>".$optpengganti."</select>
						<img id='pengganti' onclick=z.elSearch('pengganti',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>		 
				</tr>
				<tr hidden>
					<td>".$_SESSION['lang']['approve']." 1</td>
					<td>:</td>
					<td>
						<select id='persetujuan1' style='width:250px' ".$disable." >".$optper1."</select>
						<img id='persetujuan1' onclick=z.elSearch('persetujuan1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr hidden>
					<td>".$_SESSION['lang']['approve']." 2</td>
					<td>:</td>
					<td>
						<select id='persetujuan2' style='width:250px'>".$optper2."</select>
						<img id='persetujuan2' onclick=z.elSearch('persetujuan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr hidden>
					<td>".$_SESSION['lang']['approve']." 3</td>
					<td>:</td>
					<td>
						<select id='persetujuan3' style='width:250px'>".$optper3."</select>
						<img id='persetujuan3' onclick=z.elSearch('persetujuan3',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr hidden>
					<td>".$_SESSION['lang']['approve']." 4</td>
					<td>:</td>
					<td>
						<select id='persetujuan4' style='width:250px'>".$optper4."</select>
						<img id='persetujuan4' onclick=z.elSearch('persetujuan4',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
			</table>
		</td>
		<td hidden rowspan=8 style='vertical-align:top'>
		<fieldset style='min-height:180px'><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td></td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=30px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset>
		</fieldset>
		</td>
	</tr>
	<tr hidden>
		<td>POH</td>
		<td>:</td>
		<td>
			<input type='text' style='width:145px;' class='myinputtext' onkeypress='return angka_doang(event);' id='poh' style='width:150px;' disabled value=".$poh." />
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jenisijin']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<select id='jnsIjin' name='jnsIjin' style='width:150px' onchange='loadSisaCuti(".$_SESSION['standard']['userid'].")'>".$optJenis."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tahun']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<select id='periodec'  style='width:150px' onchange='loadSisaCuti(".$_SESSION['standard']['userid'].")'>".$optPeriodec."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['tanggal']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' onchange='getjumlahcuti()' style='width:145px;' id='tglAwal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
			<select style=display:none id='jam1' onchange='getjumlahcuti()'>".$jm."></select><select style=display:none id='mnt1' onchange='getjumlahcuti()'>".$mnt."></select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['tanggal']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' onchange='getjumlahcuti()'  style='width:145px;' id='tglEnd' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
			<select style=display:none id='jam2' onchange='getjumlahcuti()'>".$jm."</select><select style=display:none id='mnt2' onchange='getjumlahcuti()'>".$mnt."></select>
		</td>
	</tr>
	<tr> 
		<td>".$_SESSION['lang']['tanggal']." Bekerja Kembali <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggalkerja style='width:145px;' onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly>
		</td>	
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil']." (".$_SESSION['lang']['hari'].")</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:50px;'  id='jumlahhk' name='keperluan' onkeypress='return angka_doang(event);' maxlength='5' value='0' /> ".$_SESSION['lang']['sisa']." : <span id='sis'>".$sisaprdsblm." ".$_SESSION['lang']['hari']." 
		</td>
	</tr>

	<tr>
		<td>No. Handphone <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' style='width:145px;' class='myinputtext' onkeypress='return angka_doang(event);' id='nohp' size='10' style='width:150px;' />
		</td>
	</tr>
	<tr style=display:none>
		<td >Home Trip</td>
		<td >:</td>
		<td >
			<input type='checkbox' id='hometrip' onclick='checkhometrip(this)'>
		</td>
	</tr>
	<tr id='trtanggalberangkat' style='display:none'>
		<td>Tanggal Berangkat</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglberangkat' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
		</td>
	</tr>
	<tr id='trrutekeberangkatan' style='display:none'>
		<td>Rute Keberangkatan</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:245px;' id='rutekeberangkatan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
		</td>
	</tr>
	<tr id='trtanggalpulang' style='display:none'>
		<td>Tanggal Pulang</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglpulang' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
		</td>
	</tr>
	<tr id='trrutekepulangan' style='display:none'>
		<td>Rute Kepulangan</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:245px;' id='rutekepulangan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
		</td>
	</tr>
	<tr>
		<td></td><td></td>
		<td colspan=3 id='tmblHeader' style='text-align:center'>
			<button class=mybutton id=dtlForm onclick=saveForm('".$levelpersetujuan."')>".$_SESSION['lang']['save']."</button>
			<button class=mybutton id=cancelForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</fieldset>
</table>
<input type='hidden' id='atsSblm' name='atsSblm' />
<input type='hidden' id='atsSblm2' name='atsSblm2' />
</div>";

CLOSE_BOX();

echo"<div id='list_ganti'>";

OPEN_BOX();

echo"<div id='action_list'></div>



<table cellspacing='1' cellpadding='5' border='0' class='sortable'>
	<thead>
	<tr class='rowheader'>
		<th align=center>No.</th>
		<th align=center>".$_SESSION['lang']['notransaksi']."</th>
		<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
		<th align=center  width=75px>Tanggal Pengajuan</th>
		<th hidden align=center>POH</th>
		<th align=center>".$_SESSION['lang']['jenisijin']."</th>
		<th align=center>".$_SESSION['lang']['periode']."</th>
		<th align=center>".$_SESSION['lang']['dari']."</th>
		<th align=center>".$_SESSION['lang']['sampai']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center>Kembali</th>
		<th align=center>No Handphone</th>
		<th align=center>".$_SESSION['lang']['keperluan']."</th>
		<th align=center>".$_SESSION['lang']['keterangan']."</th>
		<th align=center>".$_SESSION['lang']['alamat']."</th>
		<th align=center>Pengganti</th>
		<th align=center>".$_SESSION['lang']['approval_status']."</th>
		<th align=center>Status Pembatalan</th>";
	echo"<th align=center>Action</th>
	</tr>
	</thead>
	<tbody id='contain'><script>loadNData()</script></tbody>
	
	</table>";
	
CLOSE_BOX();
echo"</div>";

	echo"<div id=realisasi style=display:none>";

OPEN_BOX();

    echo "<div id=realisasidata></div>";

CLOSE_BOX();
echo "</div>";
echo close_body();
?>