<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=$_GET['proses'];
$lokasi=$_SESSION['empl']['lokasitugas'];
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');
// $mandor=checkPostGet('mandor','');
// $tanggal=checkPostGet('tanggal','');
// $tanggal2=checkPostGet('tanggal2','');

// $tanggal=tanggalsystem($tanggal); $tanggal=substr($tanggal,0,4).'-'.substr($tanggal,4,2).'-'.substr($tanggal,6,2);
// $tanggal2=tanggalsystem($tanggal2); $tanggal2=substr($tanggal2,0,4).'-'.substr($tanggal2,4,2).'-'.substr($tanggal2,6,2);

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kebun==''){
        echo"Error: Kebun tidak boleh kosong."; exit;
    }
    // if(($tanggal=='--' || $tanggal2=='--')){
    //     echo"Error: Periode tanggal harus diisi."; exit;
    // }
}

if($proses=='getkud'){
	if($kebun==''){
		$op="<option value=''></option>";
		$op2="<option value=''></option>";
		echo $op."###".$op2;
		exit();
	}
	$str="select a.kodeunit, a.afdeling, a.kodesupplier, b.namasupplier from ".$dbname.".kebun_5namakud a
			left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid
			where (a.kodeunit like '".$kebun."%' or a.afdeling like '".$kebun."%' )";
		  $op="<option value=''>".$_SESSION['lang']['all']."</option>";
		  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  while($bar=$res->fetch()) 
		  {
			  $op.="<option value='".$bar->afdeling."'>".$bar->afdeling." [".$bar->namasupplier."]</option>";
		  }
		  
	// $str2="select a.nikmandor as nik, b.namakaryawan as nama, b.lokasitugas from ".$dbname.".kebun_aktifitas a
 //        left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid 
 //        where a.kodeorg like '%".$kebun."%' and a.nikmandor != ''
 //        group by a.nikmandor
 //        order by b.namakaryawan";
 //      $op2="<option value=''>".$_SESSION['lang']['all']."</option>";
 //      $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	//   $res2->setFetchMode(PDO::FETCH_OBJ);
 //      while($bar2=$res2->fetch()) 
 //      {
 //          $op2.="<option value='".$bar2->nik."'>".$bar2->nama."[".$bar2->nik."]</option>";
 //      }
	
	echo $op."###".$op2;
}

$stream="";
if ($proses=='excel' or $proses=='preview')
{
    $border=0;
    if($proses=='excel')$border=1;

    // if(substr($tanggal,0,7)!=substr($tanggal2,0,7)){
    //     exit("error: Hanya bisa menampilkan laporan dalam periode yang sama");
    // }
    
	$sMan="select supplierid, namasupplier from ".$dbname.".log_5supplier
			where supplierid in (select kodesupplier from ".$dbname.".kebun_5namakud where 1)
			";
	$qMan=$owlPDO->query($sMan) or die(print " Gagal: ".PDOException::getMessage());
	$qMan->setFetchMode(PDO::FETCH_ASSOC);
	while($rMan=$qMan->fetch()){
		$kamuskud[$rMan['supplierid']]=$rMan['namasupplier'];
	}

    // ambil PLASMA-nya
    $str="select a.kodeunit, a.afdeling, a.kodesupplier, b.namasupplier from ".$dbname.".kebun_5namakud a
            left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid
            where (a.kodeunit like '".$kebun."%' or a.afdeling like '".$kebun."%' )";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()) 
    {
        $kebun=substr($bar->afdeling,0,4);
    }
	
	$str="select a.*, b.kodesupplier from ".$dbname.".kebun_5kavling a
        left join ".$dbname.".kebun_5namakud b on a.afdeling = b.afdeling
        where (a.kodeunit like '".$kebun."%' or a.afdeling like '".$kebun."%' ) and a.afdeling like '".$divisi."%' 
        order by kodeunit, afdeling, kodeblok, no_hamp, no_kavl, nama ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$stream.="<table cellspacing='1' cellpadding='5' border='".$border."' class='sortable'>
	<thead>
	<tr class=rowheader>
        <th>".$_SESSION['lang']['nomor']."</th>
        <th>".$_SESSION['lang']['kebun']."</th>    
		<th>Nama KUD Organisasi</th>
		<th>".$_SESSION['lang']['kodeblok']."</th>
		<th>Hamparan</th>
		<th>Kavling</th>
		<th>".$_SESSION['lang']['tahuntanam']."</th>            
		<th>".$_SESSION['lang']['nama']."</th>            
		<th>".$_SESSION['lang']['status']."</th>            
	</tr></thead>
	<tbody>";
        $no=$jhk=$umr=$insentif=0;
        while($bar=$res->fetch())
        {
        	$listkavling[$bar->id]=$bar->id;

        	$datakav[$bar->id]['kodeunit']=$bar->kodeunit;
        	$datakav[$bar->id]['afdeling']=$bar->afdeling;
        	$datakav[$bar->id]['kodesupplier']=$bar->kodesupplier;
        	$datakav[$bar->id]['kodeblok']=$bar->kodeblok;
        	$datakav[$bar->id]['no_hamp']=$bar->no_hamp;
        	$datakav[$bar->id]['no_kavl']=$bar->no_kavl;
        	$datakav[$bar->id]['t_tnm']=$bar->t_tnm;
        	$datakav[$bar->id]['nama']=$bar->nama;
        	$datakav[$bar->id]['lunas']=$bar->lunas;
        	$datakav[$bar->id]['tgl_lunas']=$bar->tgl_lunas;
        	$datakav[$bar->id]['aktif']=$bar->aktif;
        }   

        $kamusaktif=array('0'=>'Tidak','1'=>'Aktif');
        
        $numur=0;
        if(!empty($listkavling))foreach($listkavling as $idkav){
        	$numur+=1;
            $stream.="
            <tr class=rowcontent>
            <td align=right>".number_format($numur)."</td>
            <td>".$datakav[$idkav]['kodeunit']."</td>
            <td>".$kamuskud[$datakav[$idkav]['kodesupplier']]."</td>
            <td>".$datakav[$idkav]['kodeblok']."</td>
            <td>".$petik.$datakav[$idkav]['no_hamp']."</td>
            <td>".$petik.$datakav[$idkav]['no_kavl']."</td>
            <td>".$datakav[$idkav]['t_tnm']."</td>
            <td>".$datakav[$idkav]['nama']."</td>
            <td>".$kamusaktif[$datakav[$idkav]['aktif']]."</td>
            </tr>";
        }else{
            $stream.="
            <tr class=rowcontent>
            <td colspan=9 align=center>Data Kosong</td>
            </tr>";
        }        
            
	$stream.="
        </tbody></table>";
}  
switch($proses)
{
    case'preview':
        echo $stream;    
    break;
    case 'excel':
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHms");
        $nop_="Kavling".$kebun."_".$divisi."_".date('YmdHis');
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $stream);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";            
    break;    
}

?>