<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tahun=checkPostGet('tahun','');
$jeniscuti=checkPostGet('jeniscuti','');
$tipekar=checkPostGet('tipekar','');
$golkar=checkPostGet('golkar','');

// $tgl=  tanggalsystemn(checkPostGet('tgl',''));
// $pengali=checkPostGet('pengali','');
// $makan=checkPostGet('makan','');
// $kawin=checkPostGet('kawin','');
// $bulanawal=checkPostGet('bulanawal','');
// $bulanakhir=checkPostGet('bulanakhir','');

$optTk=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optJab=makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optgol=  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');


if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1>";
}

$x=readTextFile('config/jumlahcuti.lst');
if(intval($x)>0)
	$hakcuti=$x;
else
	$hakcuti=12;  



$where="";

if($tipekar=='' || $tahun==''){
	exit("Warning:Lengkapi Pengisian");
}

if($tipekar!=''){
	$where.=" and tipekaryawan='".$tipekar."' ";
}

if($golkar!=''){
	$where.=" and kodegolongan='".$golkar."' ";
}


$stream.="<thead class=rowheader>
    <tr class=rowheader>";
$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
$stream.="       
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>    
		 <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['lokasitugas']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['subbagian']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tipekaryawan']."</td>
		<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodegolongan']."</td>
		<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['bagian']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tmk']."</td>
		  <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['dari']."</td>
			<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggalsampai']."</td>
        <td bgcolor=#CCCCCC align=center>Cuti</td>
    </tr>";
$stream.="</thead>";

#bentuk list karyawan
$str="select karyawanid,namakaryawan,nik,tipekaryawan,kodejabatan,kodegolongan,lokasitugas,subbagian,bagian,tanggalmasuk 
		from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and tanggalkeluar='0000-00-00' ".$where."
		";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    @$counterKar+=1;
    @$idKar[$bar['karyawanid']]=$bar['karyawanid'];
    $nama[$bar['karyawanid']]=$bar['namakaryawan'];
    $tk[$bar['karyawanid']]=$bar['tipekaryawan'];
    $nik[$bar['karyawanid']]=$bar['nik'];
    $lokasi[$bar['karyawanid']]=$bar['lokasitugas'];
    $subBag[$bar['karyawanid']]=$bar['subbagian'];
    $jab[$bar['karyawanid']]=$bar['kodejabatan'];
    $bag[$bar['karyawanid']]=$bar['bagian'];
	$gol[$bar['karyawanid']]=$bar['kodegolongan'];
    $tglMasuk[$bar['karyawanid']]=$bar['tanggalmasuk'];
}



// ##buat excel ngambil total 
// $str="select * from ".$dbname.".sdm_5cutilainht where periodegaji='".$per."' and kodeorg='".$unit."' and idkomponen='".$jenis."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $gajisave[$bar['karyawanid']]=$bar['jumlah'];
// }



if(is_array(isset($idKar)?$idKar:'')){
    foreach ($idKar as $kar){
		
		
		$tgl=substr(str_replace("-","",$tglMasuk[$kar]),4,4);		
		$dari=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$tahun);
		$dari=date('Ymd',$dari);
		$sampai=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),$tahun+1);		
		$sampai=date('Ymd',$sampai);
		#jika tahun masuk masih belum 1tahun maka 0
		$d=str_replace("-","",$tglMasuk[$kar]);
		if($d==$dari)
			$hakcuti=0;
		
		
        @$no+=1;
        $stream.="<tr class=rowcontent id=row".$no.">";
		$stream.="<td align=center>".$no."</td>";
			if ($proses != 'excel') {
				$stream.="<td hidden id=karidsave".$no.">".$kar."</td>";
			}	
		$stream.="<td>".$nik[$kar]."</td>";
		$stream.="<td>".$nama[$kar]."</td>";
			$stream.="<td>".$lokasi[$kar]."</td>";
		$stream.="<td>".$subBag[$kar]."</td>";
		$stream.="<td>".$optTk[$tk[$kar]]."</td>";
		$stream.="<td>".$optgol[$gol[$kar]]."</td>";
		$stream.="<td>".$bag[$kar]."</td>";
		$stream.="<td>".$optJab[$jab[$kar]]."</td>";
		$stream.="<td >".tanggalnormal($tglMasuk[$kar])."</td>";
		$stream.="<td id=dari".$no.">".$dari."</td>";
		$stream.="<td id=sampai".$no.">".$sampai."</td>";
		if ($proses != 'excel') {
			$stream.="<td><input type=text  id=hakcuti".$no." size=10 value='".$hakcuti."' onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:100px;\"></td>";
		}else{
			$stream.="<td>".number_format($cutisave[$kar])."</td>";
		}			
        $stream.="</tr>";   
    }
}
if ($proses != 'excel') 
{//;saveAll(".$no.")
    $stream.="<button class=mybutton onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
}//saveAll

$stream.="</tbody></table>";
		
switch($proses)
{
    
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_tunjangan_".$jenis._.$tglSkrg;
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
                
	default:
}



?>