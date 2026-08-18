<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_cuti_nonstaff').'</span></br>');
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['izinkntor']." / ".$_SESSION['lang']['cuti']).'</span>');
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

<script type="application/javascript" src="js/sdm_cuti_nonstaff.js"></script>

<?php

##Option Hours & Minute
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
	$jm.="<option value=".$i.">".$i."</option>";
	$i++;
}

for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
	$mnt.="<option value=".$i.">".$i."</option>";
	$i++;
}

$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar'  and tipekaryawan in ('0') and karyawanid!='".$_SESSION['standard']['userid']."' and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%asisten%') order by namakaryawan asc";
// exit('error: '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optKary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$optKary1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar'  and tipekaryawan in ('0') and karyawanid!='".$_SESSION['standard']['userid']."' and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%manager%') order by namakaryawan asc";
// exit('error: '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optKary1.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

//Pengambilan karyawan HRD
$optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and tipekaryawan in ('0','7','8') and bagian in ('HCGA') and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
// exit('error :'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optHRD.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
}

$optpengganti="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan,nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and karyawanid!='".$_SESSION['standard']['userid']."' and tipekaryawan IN ('3','4','5','6') and kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%krani%') order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optpengganti.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$str1=$owlPDO->query("select idjenis,jenisijin from ".$dbname.".sdm_5jenisijin 
      order by jenisijin");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str1->fetch()){
    $optJenis.="<option value='".$bar->idjenis."'>".$bar->jenisijin."</option>";
}

// $optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $arragama=getEnum($dbname,'sdm_ijin','jenisijin');
// foreach($arragama as $kei=>$fal)
// {
// 	if($_SESSION['language']=='ID')
// 	{
// 		$optJenis.="<option value='".$kei."'>".$fal."</option>";
// 	}
// 	else
// 	{
// 		switch($fal)
// 		{
// 			case 'TERLAMBAT':
// 				$fal='Late for work';
// 			break;
			
// 			case 'KELUAR':
// 				$fal='Out of Office';
// 			break;
			
// 			case 'PULANGAWAL':
// 				$fal='Home early';
// 			break;
			
// 			case 'IJINLAIN':
// 				$fal='Other purposes';
// 			break;   
            
// 			case 'CUTI':
// 				$fal='Leave';
// 			break;
			
// 			case 'MELAHIRKAN':
// 				$fal='Maternity';
// 			break;
								
// 			case 'CUTIPOTONGGAJI':
// 				$fal='Leave';
// 			break;

// 			case 'TERLAMBATPOTONGGAJI':
// 				$fal='Late for work';
// 			break;
			
// 			case 'PULANGAWALPOTONGGAJI':
// 				$fal='Home early';
// 			break;   
			
// 			default:
// 				$fal='Wedding, Circumcision or Graduation';
// 			break;                              
// 		}
		
// 		$optJenis.="<option value='".$kei."'>".$fal."</option>";       
// 	}
// }  

//ambil cuti ybs
// Ambil tanggal masuk ybs
$str="select right(tanggalmasuk,5) as tanggalmasuk from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid'];
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$tglmasup='';
$hrini=date('md');#default
while($bar=$res->fetch())
{
    $tglmasup=str_replace("-","",$bar->tanggalmasuk);#replace with data karyawan
}

if($tglmasup>$hrini)
{
	$tahunplafon=(date('Y')-2);
}
else
{
    $tahunplafon=date('Y');
}
   
#penguncian agar cuti yang sudah hangus tidak dapat diambil
$optPeriodec="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPeriodec.="<option value=".$tahunplafon.">".$tahunplafon."</option>";
$optPeriodec.="<option value=".($tahunplafon+1).">".($tahunplafon+1)."</option>"; 

$str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$_SESSION['standard']['userid']." and periodecuti=".$tahunplafon;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$sisa='';
while($barf=$res->fetch())
{
    $sisa=$barf->sisa;
}
if($sisa=='')
    $sisa=0;

echo"<input type='hidden' id='proses' name='proses' value='insert'  />
<div id='headher'>
<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['form']."</legend>
<table cellspacing='1' border='0'>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." Pengajuan</td>
		<td>:</td>
		<td>
			<input type='text' style='width:100px;' class='myinputtext' id='tglIzin' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' onkeypress=\"return false;\" value='".date('d-m-Y')."' disabled>
		</td>
		<td id='tdkanan' rowspan=8 style='vertical-align:top;padding-left:10px'>
			<table cellspacing='1' border='0'>
				<tr>
					<td>".$_SESSION['lang']['keperluan']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' style='width:245px;' id='keperluan' name='keperluan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
					</td>
				</tr>
				<tr>
					<td valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td valign=top>:</td>
					<td>
						<textarea id='ket'  style='width:228px;'  onkeypress='return tanpa_kutip(event);'></textarea>
					</td>
				</tr>
				<tr> 
					<td valign=top>".$_SESSION['lang']['alamat']."</td>
					<td valign=top>:</td>
					<td valign=top>
						<textarea id='alamatcuti'  style='width:228px;'  onkeypress=return tanpa_kutip(event);></textarea>
					</td>	
					<td>		 
				</tr>
				<tr> 
					<td>".$_SESSION['lang']['tanggal']." Bekerja Kembali</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggalkerja size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
					</td>	
				</tr>
				<tr> 
					<td>".$_SESSION['lang']['personalpengganti']."</td>
					<td>:</td>
					<td>
						<select id=pengganti style=width:250px>".$optpengganti."</select>
						<img id='pengganti' onclick=z.elSearch('pengganti',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>		 
				</tr>
				<tr>
					<td>".$_SESSION['lang']['persetujuan']." 1</td>
					<td>:</td>
					<td>
						<select id='atasan' style='width:250px'>".$optKary."</select>
						<img id='atasan' onclick=z.elSearch('atasan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['persetujuan']." 2</td>
					<td>:</td>
					<td>
						<select id='atasan2' style='width:250px'>".$optKary1."</select>
						<img id='atasan2' onclick=z.elSearch('atasan2',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['hrd']."</td>
					<td>:</td>
					<td>
						<select id='hrd' style='width:250px'>".$optHRD."</select>
						<img id='hrd' onclick=z.elSearch('hrd',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jenisijin']."</td>
		<td>:</td>
		<td>
			<select id='jnsIjin' name='jnsIjin' style='width:185px' onchange='loadSisaCuti(".$_SESSION['standard']['userid'].")'>".$optJenis."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td>
			<select id='periodec'  style='width:105px' onchange='loadSisaCuti(".$_SESSION['standard']['userid'].")'>".$optPeriodec."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['tanggal']." & ".$_SESSION['lang']['jam']."</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglAwal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
			<select id='jam1'>".$jm."></select>:<select id='mnt1'>".$mnt."></select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['tanggal']." & ".$_SESSION['lang']['jam']."</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglEnd' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
			<select id='jam2'>".$jm."</select>:<select id='mnt2'>".$mnt."></select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil']."</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:100px;'  id='jumlahhk' name='keperluan' onkeypress='return angka_doang(event);' maxlength='5' value='0' /> ".$_SESSION['lang']['hari']." - (".$_SESSION['lang']['sisa'].":<span id='sis'>".$sisa." ".$_SESSION['lang']['hari']."</span>)
		</td>
	</tr>
	<tr>
		<td>No. Handphone</td>
		<td>:</td>
		<td>
			<input type='text' style='width:100px;' class='myinputtext' onkeypress='return angka_doang(event);' id='nohp' size='10' style='width:150px;' />
		</td>
	</tr>
	<tr>
		<td>Home Trip</td>
		<td>:</td>
		<td>
			<input type='checkbox' id='hometrip' onclick='checkhometrip(this)'>
		</td>
	</tr>
	<tr id='trtanggalberangkat' style='display:none'>
		<td>Tanggal Berangkat</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglberangkat' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
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
			<input type='text' class='myinputtext'  style='width:100px;' id='tglpulang' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
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
		<td colspan=4 id='tmblHeader' style='text-align:center'>
			<button class=mybutton id=dtlForm onclick=saveForm()>".$_SESSION['lang']['save']."</button>
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

<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['list']."</legend>

<table cellspacing='1' border='0' class='sortable'>
	<thead>
	<tr class='rowheader'>
		<td align=center>No.</td>
		<td align=center>".$_SESSION['lang']['tanggal']."</td>
		<td align=center>".$_SESSION['lang']['keperluan']."</td>
		<td align=center>".$_SESSION['lang']['jenisijin']."</td>
		<td align=center>Hometrip</td>
		<td align=center>".$_SESSION['lang']['persetujuan']."</td>
		<td align=center>".$_SESSION['lang']['approval_status']."</td>
		<td align=center>".$_SESSION['lang']['persetujuan']." 2</td>
		<td align=center>".$_SESSION['lang']['approval_status']." 2</td>
		<td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
		<td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
		<td align=center>Action</td>
	</tr>
	</thead>
	<tbody id='contain'>";

$arrNmkary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan=array(0=>$_SESSION['lang']['diajukan'],1=>$_SESSION['lang']['disetujui'],2=>$_SESSION['lang']['ditolak']);
$userOnline=$_SESSION['standard']['userid'];
$limit=10;
$page=0;

if(isset($_POST['page']))
{
	$page=$_POST['page'];
	if($page<0)
		$page=0;
}

$offset=$page*$limit;

$str="select count(*) as jmlhrow from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'  order by `tanggal` desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$jlhbrs= $jsl->jmlhrow;
}

$str="select * from ".$dbname.".sdm_ijin where karyawanid='".$_SESSION['standard']['userid']."'   order by `tanggal` desc limit ".$offset.",".$limit." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$user_online=$_SESSION['standard']['userid'];
while($bar=$res->fetch())
{
	$no+=1;
	$nmAkun = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');
	echo"<tr class='rowcontent'>
		<td>".$no."</td>
		<td>".tanggalnormal($bar['tanggal'])."</td>
		<td>".$bar['keperluan']."</td>
		<td align=left>" . (isset($nmAkun[$bar['idjenis']]) ? $nmAkun[$bar['idjenis']] : '') . "</td>

		<td style='text-align:center'>".($bar['hometrip']=='1'?'Ya':'Tidak')."</td>
		<td>".$arrNmkary[$bar['persetujuan1']]."</td>
		<td>".$arrKeputusan[$bar['stpersetujuan1']]."</td>
		<td>".$arrNmkary[$bar['persetujuan4']]."</td>
		<td>".$arrKeputusan[$bar['stpersetujuan4']]."</td>
		<td>".tanggalnormald($bar['darijam'])."</td>
		<td>".tanggalnormald($bar['sampaijam'])."</td>";
		
		if($bar['stpersetujuan1']==0 and $bar['stpersetujuanrd']==0)
		{
			echo"<td>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['keperluan']."','".tanggalnormal($bar['tanggal'])."','".$bar['idjenis']."','".$bar['persetujuan1']."','".$bar['stpersetujuan1']."','".$bar['persetujuan4']."','".$bar['stpersetujuan4']."','".$bar['darijam']."','".$bar['sampaijam']."','".$bar['hrd']."','".$bar['jumlahhari']."','".$bar['periodecuti']."','" . $bar['keterangan'] . "','" . $bar['alamatcuti'] . "','" . tanggalnormal($bar['tanggalkerja']) . "','" . $bar['pengganti'] . "','".$bar['nohp']."','".$bar['hometrip']."','".tanggalnormal($bar['tanggalberangkat'])."','".$bar['rutekeberangkatan']."','".tanggalnormal($bar['tglpulang'])."','".$bar['rutekepulangan']."');\">
				
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".tanggalnormal($bar['tanggal'])."');\" >
				
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($bar['tanggal']) . "','" . $bar['karyawanid'] . "',event)\"></td>";
		}
		else
		{
			echo "<td>
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($bar['tanggal']) . "','" . $bar['karyawanid'] . "',event)\"></td>";
		}
	echo"</tr>";
}

echo"<tr class=rowheader>
	<td colspan=11 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
    </td>
</tr>
</tbody>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>