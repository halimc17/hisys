<?php ini_set('display_errors',0);
 error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$divisi=checkPostGet('divisi','');
$tipekar=checkPostGet('tipekar','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$GJTHNLU=$bar['nilai'];

$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

$ketipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$kept= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
@$tipeorg = $ketipeorg[$unit];
@$kodept=$kept[$unit];


$where1='';
if(strlen($divisi)=='6'){
	$where1.=" and subbagian='".$divisi."'";
} else if(strlen($divisi)=='4'){
	$where1.=" and subbagian=''";
}
if ($tipekar!='') {
$where2.=" and tipekaryawan='".$tipekar."'";
	# code...
}

$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
@$regorg=$regional[$unit];


#bentuk list karyawan
$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where1." ".$where2."
		and (tanggalkeluar='00000-00-00' or tanggalkeluar>= '".$tgl2."') ".$where." order by namakaryawan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$dtafd[$bar['subbagian']]=$bar['subbagian'];
	$listidkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	$nik[$bar['subbagian']][$bar['karyawanid']]=$bar['nik'];
	$nmkar[$bar['subbagian']][$bar['karyawanid']]=$bar['namakaryawan'];
	$stpajak[$bar['subbagian']][$bar['karyawanid']]=$bar['statuspajak'];
	$tpkar[$bar['subbagian']][$bar['karyawanid']]=$bar['tipekaryawan'];
	$jabatan[$bar['subbagian']][$bar['karyawanid']]=$bar['kodejabatan'];
	$dtbpjskerja[$bar['subbagian']][$bar['karyawanid']]=$bar['jms'];
	$dtbpjssehat[$bar['subbagian']][$bar['karyawanid']]=$bar['bpjs'];
	$dtbpjspensiun[$bar['subbagian']][$bar['karyawanid']]=$bar['pensiun'];
	
	//bentuk tpkar untuk foreach pejabat bkm
	$tpkarbkm[$bar['karyawanid']]=$bar['tipekaryawan'];
}

@$cekdata=count($dtkarid);
if($cekdata<1){
	exit("Warning:Data Kosong");
}

$dtkommin=array();
$dtkommin=array();
#gaji_vw

#gaji plus bhl/khl
$str = "select a.*,b.lokasitugas,b.subbagian, c.id as id, c.name, c.plus from ".$dbname.".sdm_gaji a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		left join ".$dbname.".sdm_ho_component c on c.id=a.idkomponen
        where lokasitugas='".$unit."' and periodegaji ='".substr($tgl1,0,7)."' and b.tipekaryawan ='4' ";
//exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
	if($bar['plus']=='1'){
		if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
			@$dtkomplus[$bar['id']]=$bar['id'];
		}
		
		#@$dtkomplus[$bar['id']]=$bar['id'];
		/* if($ketipeorg[$_SESSION['empl']['lokasitugas']]=='HOLDING'){
		}else{
			if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
				@$dtkomplus[$bar['id']]=$bar['id'];
			}
		} */
	}else{
		@$dtkommin[$bar['id']]=$bar['id'];
	}
	$nmkom[$bar['id']]=$bar['name'];
}

#yang bukan bulan berjalan ('32','33','40','45','62','63','64','65')
$str = "select a.*,b.lokasitugas,b.subbagian, c.id as id, c.name, c.plus from ".$dbname.".sdm_gaji a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		left join ".$dbname.".sdm_ho_component c on c.id=a.idkomponen
        where lokasitugas='".$unit."' and periodegaji ='".substr($tgl1,0,7)."' and idkomponen not in (".$GJTHNLU.")  and b.tipekaryawan !='4' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
	if($bar['plus']=='1'){
		if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
			@$dtkomplus[$bar['id']]=$bar['id'];
		}
		
		#@$dtkomplus[$bar['id']]=$bar['id'];
		/* if($ketipeorg[$_SESSION['empl']['lokasitugas']]=='HOLDING'){
		}else{
			if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
				@$dtkomplus[$bar['id']]=$bar['id'];
			}
		} */
	}else{
		@$dtkommin[$bar['id']]=$bar['id'];
	}
	$nmkom[$bar['id']]=$bar['name'];
}

$str = "select a.*,b.lokasitugas,b.subbagian, c.id as id, c.name, c.plus from ".$dbname.".sdm_gaji a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		left join ".$dbname.".sdm_ho_component c on c.id=a.idkomponen
        where lokasitugas='".$unit."' and periodegaji ='".periodelalu(substr($tgl1,0,7))."' and idkomponen  in (".$GJTHNLU.") and b.tipekaryawan!='4' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiah[$bar['subbagian']][$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
	if($bar['plus']=='1'){
		if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
			@$dtkomplus[$bar['id']]=$bar['id'];
		}
		
		#@$dtkomplus[$bar['id']]=$bar['id'];
		/* if($ketipeorg[$_SESSION['empl']['lokasitugas']]=='HOLDING'){
		}else{
			if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
				@$dtkomplus[$bar['id']]=$bar['id'];
			}
		} */
	}else{
		@$dtkommin[$bar['id']]=$bar['id'];
	}
	$nmkom[$bar['id']]=$bar['name'];
}

//ditambah 1 untuk total
@$tbrskomplus=count($dtkomplus)+1;
@$tbrskommin=count($dtkommin)+1;

#jamlembur
$str="select a.*,b.subbagian from ".$dbname.".sdm_lemburdt a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where substring(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodept."')
		and a.tanggal like '".substr(date('Y-m-d', strtotime('-1 month', strtotime($tgl1))), 0,7)."%'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jamlembur[$bar['subbagian']][$bar['karyawanid']]+=$bar['jamaktual'];
}

#jamlembur aktual dan konversi
$str1="select a.karyawanid, a.jamaktual as jamakt, b.jamlembur as jamlemb,c.subbagian from ".$dbname.".sdm_lemburdt a 
		left join ".$dbname.".sdm_5lembur b on a.jamaktual=b.jamaktual and a.kodeorg=b.kodeorg
		left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid 
		where a.kodeorg='".$unit."' and a.tanggal BETWEEN '".$tgl1."' AND '".$tgl2."' group by a.karyawanid, a.tanggal";
		// echo $str1;
		// exit();
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
while($bar1=$res1->fetch()){
	@$lmbraktual[$bar1['subbagian']][$bar1['karyawanid']]+=$bar1['jamakt'];
	@$lmbrkonversi[$bar1['subbagian']][$bar1['karyawanid']]+=$bar1['jamlemb'];
}

$coslpantafd=$tbrskomplus+$tbrskommin+8;
$stream = "";
if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class='sortable' cellspacing='1'>";
}

array_multisort($dtafd,SORT_ASC);



$stream.="<thead><tr class=rowcontent>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nomor']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['jabatan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['status']."</th>";
	#$stream.="<th align=center rowspan=2>".$_SESSION['lang']['hk']."</th>";
	$stream.="<th align=center colspan=2>".$_SESSION['lang']['lembur']."</th>";
	
	$stream.="<th align=center colspan=".$tbrskomplus.">".$_SESSION['lang']['penambah']."</th>";
	$stream.="<th align=center colspan=".$tbrskommin.">".$_SESSION['lang']['pengurang']."</th>";
	$stream.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
	
$stream.="</tr>";

$stream.="<tr>";
$stream.="<th align=center>".$_SESSION['lang']['aktual']."</th>";
$stream.="<th align=center>".$_SESSION['lang']['konversi']."</th>";
foreach (@$dtkomplus as $komplus){
		$stream.="<th align=center>".$nmkom[$komplus]."</th>";
}
$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
foreach (@$dtkommin as $kommin){
		$stream.="<th align=center>".$nmkom[$kommin]."</th>";
}
$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
$stream.="</tr>";	
$stream.="</thead>";


foreach ($dtafd as $afd){
	if(@$nmorg[$afd]==''){
		@$kdafd='Umum';
	}else{
		@$kdafd=$afd;
	}
	foreach ($dtkarid as $karid){
		if(@$listidkar[$afd][$karid]!=''){
			#cek hanya data dengan netto 0 yang di tampilkan
			foreach ($dtkomplus as $komplus){
				@$cektkomplus[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
			}
			foreach (@$dtkommin as $kommin){
				@$cektkommin[$afd][$karid]+=$rupiah[$afd][$karid][$kommin];
			}
			@$cektnettokar[$afd][$karid]=$cektkomplus[$afd][$karid]-$cektkommin[$afd][$karid];
			##tutup cek
			
			if($cektnettokar[$afd][$karid]>0){			
				@$no++;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$kdafd."</td>";
				$stream.="<td>".$nik[$afd][$karid]."</td>";
				$stream.="<td>".$nmkar[$afd][$karid]."</td>";
				$stream.="<td>".$optnmjab[$jabatan[$afd][$karid]]."</td>";
				$stream.="<td>".$nmtipekar[$tpkar[$afd][$karid]]."</td>";
				$stream.="<td>".$stpajak[$afd][$karid]."</td>";
				#$stream.="<td align=right>".@number_format($hk[$afd][$karid],2)."</td>";
				// $stream.="<td align=right>".@number_format($jamlembur[$afd][$karid],2)."</td>";
				$stream.="<td align=right>".@hidezerodecimal($lmbraktual[$afd][$karid],2)."</td>";
				$stream.="<td align=right>".@hidezerodecimal($lmbrkonversi[$afd][$karid],2)."</td>";
				foreach ($dtkomplus as $komplus){
					$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$komplus])."</td>";
					@$tkomplus[$afd][$karid]+=$rupiah[$afd][$karid][$komplus];
					@$subtkomplus[$afd][$komplus]+=$rupiah[$afd][$karid][$komplus];
				}
				$stream.="<td align=right>".@number_format($tkomplus[$afd][$karid])."</td>";
				foreach ($dtkommin as $kommin){
					$stream.="<td align=right>".@number_format($rupiah[$afd][$karid][$kommin])."</td>";
					@$tkommin[$afd][$karid]+=$rupiah[$afd][$karid][$kommin];
					@$subtkommin[$afd][$kommin]+=$rupiah[$afd][$karid][$kommin];
				}
				$stream.="<td align=right>".@number_format($tkommin[$afd][$karid])."</td>";
				@$tnettokar[$afd][$karid]=$tkomplus[$afd][$karid]-$tkommin[$afd][$karid];
				$stream.="<td align=right>".@number_format($tnettokar[$afd][$karid])."</td>";
				$stream.="</tr>";
				@$ttlhk[$afd]+=$hk[$afd][$karid];
				@$ttlmbraktual[$afd]+=$lmbraktual[$afd][$karid];
				@$ttlmbrkonversi[$afd]+=$lmbrkonversi[$afd][$karid];
			}
						
		}
	}
	$stream.="<tr bgcolor=lightgray>";
	$stream.="<td align=center colspan=7>".$_SESSION['lang']['subtotal']." ".$kdafd." - ".@$nmorg[$kdafd]."</td>";
	#$stream.="<td align=right>".@number_format($ttlhk[$afd],2)."</td>";
	$stream.="<td align=right>".@number_format($ttlmbraktual[$afd],2)."</td>";
	$stream.="<td align=right>".@number_format($ttlmbrkonversi[$afd],2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($subtkomplus[$afd][$komplus])."</td>";
		@$tsubtkomplus[$afd]+=$subtkomplus[$afd][$komplus];
		@$gtkomplus[$komplus]+=$subtkomplus[$afd][$komplus];
	}
	$stream.="<td align=right>".@number_format($tsubtkomplus[$afd])."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($subtkommin[$afd][$kommin])."</td>";
		@$tsubtkommin[$afd]+=$subtkommin[$afd][$kommin];
		@$gtkommin[$kommin]+=$subtkommin[$afd][$kommin];
	}
	$stream.="<td align=right>".@number_format($tsubtkommin[$afd])."</td>";
	@$tsubtnetto[$afd]=$tsubtkomplus[$afd]-$tsubtkommin[$afd];
	
	$stream.="<td align=right>".@number_format($tsubtnetto[$afd])."</td>";
	$stream.="</tr>";
	
	#bentuk grandtotal
	@$gthk+=$ttlhk[$afd];
	@$gtllmbraktual+=$ttlmbraktual[$afd];
	@$gtllmbrkonversi+=$ttlmbrkonversi[$afd];
	@$gtnetto+=$tsubtnetto[$afd];
	
}
$stream.="<thead><tr>";
$stream.="<td align=center colspan=7>".$_SESSION['lang']['grnd_total']."</td>";
	#$stream.="<td align=right>".@number_format($gthk,2)."</td>";
	$stream.="<td align=right>".@number_format($gtllmbraktual,2)."</td>";
	$stream.="<td align=right>".@number_format($gtllmbrkonversi,2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($gtkomplus[$komplus])."</td>";
		@$tgtkomplus+=$gtkomplus[$komplus];
	}
	$stream.="<td align=right>".@number_format($tgtkomplus)."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($gtkommin[$kommin])."</td>";
		@$tgtkommin+=$gtkommin[$kommin];
	}
	$stream.="<td align=right>".@number_format($tgtkommin)."</td>";
	$stream.="<td align=right>".@number_format($gtnetto)."</td>";
	
$stream.="</tr></thead>";
$stream.="<tbody></table>";
switch($proses){
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optdivisi;
			break;
    case 'preview':
        echo $stream;
    break;
    case 'excel':
        $tglSkrg=date("Ymd");
        $nop_="laporan_gaji_harian";
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