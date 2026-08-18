<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');

$nmklas=makeOption($dbname,'pabrik_5logmesin_klasifikasi','kode,nama');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');



#bentuk tanggal awal dan akhir dari periode akuntansi
$str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$per."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$tgl1=$bar['tanggalmulai'];
	$tgl2=$bar['tanggalsampai'];
	





########################################################################################
#############prepare data
########################################################################################

$arrtgl=rangeTanggalarr($tgl1,$tgl2);
$ttgl=count($arrtgl);
$exper=explode('-',$per);
$jumweek=weeks($exper[0],$exper[1]);
$jumsisatgl=$ttgl%7;
if($jumsisatgl!=0){
	$jumweek=$jumweek-1;
}

########################################################################################
#############tampilkan data
########################################################################################

if($proses=='excel'){
	$border='border=1';
}else{
	$border='border=0';
}

echo"<pre>";
$stream="";
$stream.="<table cellspacing=1 class=sortable cellpadding=1 ".$border.">";
$stream.="<thead>";

$stream.="<tr>";
$stream.="<td align=center  rowspan=3>".strtoupper($_SESSION['lang']['nourut'])."</td>";
$stream.="<td align=center  rowspan=3>".strtoupper($_SESSION['lang']['station'])."</td>";
$stream.="<td align=center  rowspan=3>".strtoupper($_SESSION['lang']['kegiatan'])."</td>";
$stream.="<td align=center  rowspan=3>HM STANDART</td>";
$stream.="<td align=center  align=center  rowspan=3>HM PER 31 DESEMBER 2016</td>";
$stream.="<td align=center  rowspan=3>LIFETIME (HM)</td>";
$stream.="<td align=center colspan='".$ttgl."'>".strtoupper(numToMonth($exper[1],'I','long'))."</td>";
$stream.="<td align=center  rowspan=3>".strtoupper('remark')."</td>";
$stream.="</tr>";
$stream.="<tr>";
for($i=1;$i<=$jumweek;$i++){
	$stream.="<td align=center  colspan=7>WEEK ".$i."</td>";
}
if($jumsisatgl!=0){
	$stream.="<td align=center  colspan='".$jumsisatgl."'>WEEK ".$i."</td>";
}
$stream.="</tr>";
$stream.="<tr>";
foreach($arrtgl as $tgl){
	$extgl=explode('-',$tgl);
	$day = date('D', strtotime($tgl));
	if($day=='Sun')$libur=true; else $libur=false;
	// kamus hari libur
	$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".$unit."')";
	$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
	$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
	while($roworg=$queorg->fetch()){
		if($roworg['keterangan']=='libur')$libur=true;
		if($roworg['keterangan']=='masuk')$libur=false;
	}
	if($libur==true){
		$stream.="<td align=right><font color=red>".intval($extgl[2])."</font></td>";
	}else{
		$stream.="<td align=center >".intval($extgl[2])."</td>";
	}
	
	
	
}
$stream.="</tr>";
$stream.="</thead>";


#prepare data
$str="select * from ".$dbname.".pabrik_5preventive_maintenance where kodemesin  like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrstation[substr($bar['kodemesin'],0,6)]=substr($bar['kodemesin'],0,6);
	$arrmesin[$bar['kodemesin']]=$bar['kodemesin'];
	$listarrmesin[substr($bar['kodemesin'],0,6)][$bar['kodemesin']]=$bar['kodemesin'];
	$arrkegiatan[$bar['kode_preven_maintenance']]=$bar['kode_preven_maintenance'];
	$arrnmkegiatan[$bar['kode_preven_maintenance']]=$bar['preven_keterangan'];
	$listkegiatan[substr($bar['kodemesin'],0,6)][$bar['kodemesin']][$bar['kode_preven_maintenance']]=$bar['kode_preven_maintenance'];
	$hmstandard[substr($bar['kodemesin'],0,6)][$bar['kodemesin']][$bar['kode_preven_maintenance']]=$bar['hm_standard'];
}

#jam olah
$str="select * from ".$dbname.".pabrik_pengolahan where kodeorg='".$unit."' and tanggal like '".$per."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$jamolah[$bar['tanggal']]+=$bar['jamdinasbruto'];
}

#bentuk budget
$str="select * from ".$dbname.".pabrik_5preventive_maintenance where kodemesin  like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
}



$stream.="<tr class=rowcontent>";
	$stream.="<td align=center rowspan=2></td>";
	$stream.="<td align=center rowspan=2></td>";
	$stream.="<td align=center rowspan=2></td>";
	$stream.="<td align=center rowspan=2></td>";
	$stream.="<td align=center rowspan=2></td>";
	$stream.="<td>HARI KERJA</td>";
	foreach($arrtgl as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".$unit."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			if($libur==true){
				$stream.="<td align=right><font color=red>KL</font></td>";
			}else{
				$stream.="<td align=right>K</td>";
			}
		
	}
	$stream.="<td align=center></td>";
$stream.="</tr>";

$stream.="<tr class=rowcontent>";
	$stream.="<td>REAL JAM OLAH</td>";
	foreach($arrtgl as $tgl){
		$stream.="<td align=right>".number_format($jamolah[$tgl],2)."</td>";
	}
	$stream.="<td align=center></td>";
$stream.="</tr>";



$spanstation=$ttgl+6;
$spanmesin=$spanstation-1;







foreach($arrstation as $station){
	@$no+=1;
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center>".$no."</td>";
	$stream.="<td colspan='".$spanstation."'><b>".$nmorg[$station]."</b></td>";
	$stream.="</tr>";
	foreach($arrmesin as $mesin){
		if($listarrmesin[$station][$mesin]!=''){
			$stream.="<tr class=rowcontent>";
			$stream.="<td></td>";
			$stream.="<td></td>";
			$stream.="<td colspan='".$spanmesin."'><b>".$nmorg[$mesin]."</b></td>";
			$stream.="</tr>";
			foreach($arrkegiatan as $keg){
				if($listkegiatan[$station][$mesin][$keg]!=''){
					$stream.="<tr class=rowcontent>";
					$stream.="<td align=left rowspan=3></td>";
					$stream.="<td align=left rowspan=3></td>";
					$stream.="<td align=left rowspan=3>".$arrnmkegiatan[$keg]."</td>";
					$stream.="<td align=right rowspan=3>".$hmstandard[$station][$mesin][$keg]."</td>";
					$stream.="<td align=right rowspan=3>0</td>";
					$stream.="<td>PLAN(BUDGET)</td>";
					$stream.="</tr>";
					
				
					$stream.="<tr class=rowcontent>";
						$stream.="<td>ACTUAL</td>";
					$stream.="</tr>";
					
					$stream.="<tr class=rowcontent>";
							$stream.="<td>ACTION</td>";
					$stream.="</tr>";
					
				}
			}
			
		}
	}
}


// echo"<pre>";
// print_r($arrkegiatan);
// echo"</pre>";












$stream.="<tbody></table>";
switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_oee_".$unit;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
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
}
?>