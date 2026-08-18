<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/HtmlExcel.php');
//fungsi selisih waktu
function datediff1($tgl1, $tgl2){
$tgl1 = strtotime($tgl1);
$tgl2 = strtotime($tgl2);
$diff_secs = abs($tgl1 - $tgl2);
$base_year = min(date("Y", $tgl1), date("Y", $tgl2));
$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
return array( "years" => date("Y", $diff) - $base_year, "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff), "months" => date("n", $diff) - 1, "days_total" => floor($diff_secs / (3600 * 24)), "days" => date("j", $diff) - 1, "hours_total" => floor($diff_secs / 3600), "hours" => date("G", $diff), "minutes_total" => floor($diff_secs / 60), "minutes" => (int) date("i", $diff), "seconds_total" => $diff_secs, "seconds" => (int) date("s", $diff) );
}

##### declarasi variabel ##### 
$proses=$_GET['proses'];

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$kdOrg=     isset($_POST['kdOrg'])? $_POST['kdOrg']: '';
$kdAst=     isset($_POST['kdAst'])? $_POST['kdAst']: '';
$tpAsset=   isset($_POST['tpAsset'])? $_POST['tpAsset']: '';
$unitCode = isset($_POST['unit'])? $_POST['unit']: '';
$kodeproject = isset($_POST['unit'])? $_POST['kodeproject']: '';
$kodeasset = isset($_POST['kodeasset'])? $_POST['kodeasset']: '';
$subtpAsset=checkPostGet('subtpAsset','');
$jenisbiaya=checkPostGet('jenisbiaya','');
$status=checkPostGet('status','');
$tipelaporan=checkPostGet('tipelaporan','');

if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
if($kdAst=='')$kdAst=$_GET['kdAst'];
if($unitCode=='')$unitCode=$_GET['unit'];
if($kodeproject=='')$kodeproject=$_GET['kodeproject'];
if($kodeasset=='')$kodeasset=$_GET['kodeasset'];
if($tpAsset=='')$tpAsset=   isset($_GET['tpAsset'])? $_GET['tpAsset']: '';
##### selesai ##### 
$bulanDt=explode("-",$kdAst);


## nama karyawan
$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namakar[$bar->karyawanid]=$bar->namakaryawan;
}
## selesai nama karyawan

##### ambil kode aset dan organisasi untuki option text ##### 
//get org
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";
    $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
    $rOrg=$qOrg->fetch();
    $nmOrg=$rOrg['namaorganisasi'];

$brd=0;
$bgBelakang="bgcolor=#AFACAC align=center";
if($proses=='excel')    
{
    $brd=1;
}
$where="";
if($tpAsset!='') {
    $where=" and tipeasset='".$tpAsset."'";
}

if($jenisbiaya!='') {
    $where=" and jenis_biaya='".$jenisbiaya."'";
}

if($kdAst!='')
{
    $where.=" and awalpenyusutan <='".$kdAst."' ";
    
}
if($kodeproject!='')
{
    $where.=" and kodeproject like '%".$kodeproject."%' ";
    
}
if($kodeasset!='')
{
    $where.=" and kodeasset like '%".$kodeasset."%' ";
    
}
if($status!='')
{
    $where.=" and status='".$status."' ";
}


$where.=" and (substr(tanggaldisposal,1,7)>'".$kdAst."' or tanggaldisposal='0000-00-00') ";
$wheredisposal.=" and (substr(tanggaldisposal,1,7)>'".$kdAst."' or tanggaldisposal='0000-00-00') ";
// echo $where;



if($subtpAsset!=''){
    $where.=" and kodeasset like '%".$tpAsset.$subtpAsset."%' ";    
}
$arrDt=array("1"=>"Biaya Langsung","2"=>"Tidak Langsung","3"=>"Operasi");
$arrstatus=array("0"=>$_SESSION['lang']['tidakaktif'],"1"=>$_SESSION['lang']['aktif'],"2"=>$_SESSION['lang']['dlm_rusak_rmh'],"4"=>$_SESSION['lang']['dijual']);
##### preview ##### 
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitCode."'");
if($proses=='excel'){	
	$data ="<b>Laporan Daftar Asset</b><br>";
	$data.=$_SESSION['lang']['daftarasset']." : ".$nmOrg."<br>";
	if($unitCode==''){
		$unit 	= 'Seluruh Unit';
		$data.=$_SESSION['lang']['unit']." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ".$unit."<br>";
	}else{
		$unit = $unitCode;
		$data.=$_SESSION['lang']['unit']." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ".$unit." - ".$nmorg[$unit]."<br>";
	}
	$data.=$_SESSION['lang']['periode']." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ".$kdAst;
}
// $data.=$_SESSION['lang']['unit']." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ".$unit." - ".$nmorg[$unit]."<br>";
$data.="<div class='table-scroll' style=height:60vh><table class=sortable cellpadding=5 cellspacing=1 border=".$brd."><thead>";
         $data.=" <tr class=rowheader>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >No</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['kodeorganisasi']."</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['kodeasset']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['kodeasset']." Lama</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['namakelompok']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['induk']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >Tanggal Perolehan</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >Tanggal Disposal</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['namaasset']."</th>";	
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['tipemodel']."</th>";	
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['nomorrangka']."</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['nomormesin']."</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['jenisbiaya']."</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['status']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['hargaperolehan']."</th> ";
			// $data.="<th align=center ".$bgBelakang.">".$_SESSION['lang']['penambah']."</th> ";
			// $data.="<th align=center ".$bgBelakang.">".$_SESSION['lang']['pengurang']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['jumlahbulanpenyusutan']."</th> "; 
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['usia']." (".$_SESSION['lang']['bulan'].")</th> "; 
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['sisa']." (".$_SESSION['lang']['bulan'].")</th> "; 
			
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['keterangan']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['awalpenyusutan']."</th> ";
		   
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['akhirpenyusutan']."</th> ";
			for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){
				$data.="<th align=center ".$bgBelakang."  rowspan=2 > ".numToMonth($awalan)."-".$bulanDt[0]."</th> ";
			}
			$data.="<th align=center ".$bgBelakang." rowspan=2 >Penyusutan ".$bulanDt[0]."</th> ";
			// $data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['penyusutan']." ".$_SESSION['lang']['tambah']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['akumulasipenyusutan']."</th> ";
			$data.="<th align=center ".$bgBelakang." colspan=3 >Disposal</th> ";   
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['nilaibuku']."</th> "; 
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['persendecline']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >Leasing</th>";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >".$_SESSION['lang']['periode']." ".$_SESSION['lang']['nonaktif']."</th> ";
			$data.="<th align=center ".$bgBelakang." rowspan=2 >Tipe Lokasi</th> ";
                        
            $data.="</tr><tr class=rowheader>";
            $data.="<th align=center ".$bgBelakang.">Nilai Disposal-Aset</th><th align=center ".$bgBelakang.">Nilai Disposal-Penyusutan</th>
            <th align=center ".$bgBelakang.">Laba/Rugi</th> </tr></thead><tbody>";
//$where.=" and kodeasset='CKS-PR01000585' ";
//$sList="select * from ".$dbname.".sdm_daftarasset where tipeasset='".$kdAst."' and kodeorg='".$kdOrg."' order by kodeorg";

if(empty($unitCode)) {
    $sList="select * from ".$dbname.".sdm_daftarasset where  
        kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."') ".$where." order by tipeasset";
} else {
    $sList="select * from ".$dbname.".sdm_daftarasset where  kodeorg = '".$unitCode."' ".$where." order by tipeasset";
}
 // $sList="select * from ".$dbname.".sdm_daftarasset where  kodeasset in ('AAL-AB02000001','AAL-KP98000003') and (substr(tanggaldisposal,1,7)>'".$kdAst."' or tanggaldisposal='0000-00-00') ";

// echo $sList;

$optMetode = makeOption($dbname,'sdm_5tipeasset','kodetipe,metodepenyusutan');
$optMetode2 = makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
//echo $sList;
$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
$qList->setFetchMode(PDO::FETCH_ASSOC);
$no = 0;
$totHarga=0;
$totHargaAkumul=0;
$totNilai=0;
$bulanan=0;
$tpengurang=0;
$tpenambah=0;
while($bar=$qList->fetch()){
    $bar['periodenonaktif']=substr($bar['tanggaldisposal'],0,7);
    if(empty($unitCode)) {
        $scounttipe="select count(tipeasset) as countasset from ".$dbname.".sdm_daftarasset 
				where tipeasset='".$bar['tipeasset']."' and 
				kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."') 
				and awalpenyusutan<='".$kdAst."' ".$wheredisposal." order by tipeasset";
    } else {
        $scounttipe="select count(tipeasset) as countasset from ".$dbname.".sdm_daftarasset 
		where tipeasset='".$bar['tipeasset']."' and kodeorg = '".$unitCode."' 
		and awalpenyusutan<='".$kdAst."' ".$wheredisposal." order by tipeasset";
    }
	
	// echo $scounttipe.________;
	
    if($tempTipe!=$bar['tipeasset']){
        $tempTipe=$bar['tipeasset'];
        $awalTipeAsset=1;
    }else{
        $awalTipeAsset+=1;
    }
    $qcounttipe=$owlPDO->query($scounttipe) or die(print " Gagal: ".PDOException::getMessage());
    $qcounttipe->setFetchMode(PDO::FETCH_NUM);
    $ccounttipe=$qcounttipe->fetch();
    $rowsCount=$ccounttipe[0];
	
	// echo $rowsCount;
	
    $no+=1;
    $tgl1=$bar['awalpenyusutan']."-01";
    $tgl2=$kdAst."-02";
    //$selisih=datediff1($tgl1,$tgl2);
    $tahun1=substr($tgl1,0,4);
    $bulan1=substr($tgl1,5,2);
    $tahun2=substr($tgl2,0,4);
    $bulan2=substr($tgl2,5,2);
    if(substr($bar['awalpenyusutan'],0,4)==$bulanDt[0]){
        if($bar['awalpenyusutan']==$kdAst){
            $perkalibulanan=1;  
        }else{
            $perkalibulanan=($bulanDt[1]-intval(substr($bar['awalpenyusutan'],5,2))+1);
        }
    }else{
        $perkalibulanan=intval($bulanDt[1]);
    }
    
    
    
    $selisih['months_total']=($tahun2*12)+$bulan2 - (($tahun1*12)+$bulan1);
    $selisih['months_total']+=1;
    //$selisih['months_total']=(($tahun2*12)+$bulan2) - ((($tahun1*12)+$bulan1)+1);
    
    

    //=(2015*12)+7 - ((2012*12)+7)+1;
    //  =24187 - 24152
    $data.="<tr class=rowcontent>";
    $data.="<td align=center>".$no."</td>";
        $data.="<td>".$bar['kodeorg']."</td>";    
        $data.="<td nowrap>".$bar['kodeasset']."</td>";    
        $data.="<td nowrap>".$bar['kodeassetlama']."</td>";    
        $data.="<td nowrap>".$optMetode2[$bar['tipeasset']]."</td>";    
        
         $data.="<td nowrap>".$bar['induk']."</td>";    
        if($proses=='excel'){
            $data.="<td align=center nowrap>".$bar['tanggalperolehan']."</td>";    
        }else{
            $data.="<td align=center nowrap>".tanggalnormal($bar['tanggalperolehan'])."</td>";    
        }
        if($proses=='excel'){
            $data.="<td align=center nowrap>".$bar['tanggaldisposal']."</td>";        
        }else{
            $data.="<td align=center nowrap>".tanggalnormal($bar['tanggaldisposal'])."</td>";        
        }
        $data.="<td nowrap>".$bar['namasset']."</td>"; 	
        $data.="<td nowrap>".$bar['tipemodel']."</td>"; 	
        $data.="<td nowrap>".$bar['norangka']."</td>"; 
        $data.="<td nowrap>".$bar['nomesin']."</td>"; 
        $data.="<td nowrap>".$arrDt[$bar['jenis_biaya']]."</td>";
           
		
   
		 $data.="<td nowrap>".$arrstatus[$bar['status']]."</td>";
        $tgl1=$bar['awalpenyusutan']."-01";
        // if($selisih['months_total']>$bar['jlhblnpenyusutan'])
        // {
        //     $selisih['months_total']=$bar['jlhblnpenyusutan'];
            
        // }
        #periksa siapa lebih besar
        if($tgl1>$tgl2)
        {
            $selisih['months_total']=0;
        }
        
		
		#= perhitungan bulanan
		$rupiahbulanan=$bar['bulanan'];
		$usiaaset=$selisih['months_total'];
        
        $sisabln=$bar['jlhblnpenyusutan']-$selisih['months_total'];
        $sisblnCek=$sisabln;
        if($sisabln<0){
            $sisabln=0;
			$selisih['months_total']=$bar['jlhblnpenyusutan'];
        }
		
		
		 
		
		// echo $selisih['months_total']._.$sisabln._.$tgl1._.$tgl2;
		
        // $akumulasiBulanan=($bar['bulanan']*$selisih['months_total'])+$bar['akumulasiadjust'];
        $akumulasiBulanan=($bar['bulanan']*$selisih['months_total']);
        if(abs($akumulasiBulanan)>=abs($bar['hargaperolehan']))
        {
            $akumulasiBulanan=$bar['hargaperolehan'];
        }
		
		#= buat bulan terakhir penyusutan
		$akhirpenyusutan=periodelalu(jumlahbulandepan($bar['awalpenyusutan'],$bar['jlhblnpenyusutan']));
        
		#= selisih penyusutan
		#= untuk akumulasi dan perbulan juga
		#= variable nilaiselisih (untuk perbulan) dan nilaiselisihakumulasi (untuk nilai buku)
		//$nilaiselisih=$nilaiselisihakumulasi=$bar['hargaperolehan']-($bar['bulanan']*$bar['jlhblnpenyusutan']);
		$nilaiselisih=$bar['hargaperolehan']-($bar['bulanan']*$bar['jlhblnpenyusutan']);
		
		for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){			
			if($awalan<10){
                $prdcek=$bulanDt[0]."0".$awalan;
            }else{
                $prdcek=$bulanDt[0]."".$awalan;
            }

			if($prdcek<=str_replace("-","",$akhirpenyusutan) and $bar['pengakuanadjust']=='1'){
				$bar['akumulasiadjust']=$bar['akumulasiadjust'];
			}else if($bar['pengakuanadjust']=='0') {
				$bar['akumulasiadjust']=$bar['akumulasiadjust'];
			}else{
				$bar['akumulasiadjust']=0;
			}
		}
		
		
		#jika ini ada maka nanti akan muncul selisih di setiap asset yg sudah habis
		if($sisabln<=0){
			$bar['akumulasiadjust']=0;
		}
		
		
		//echo $nilaiselisihakumulasi;
		$akumulasiBulanan=$akumulasiBulanan+$nilaiselisihakumulasi+$bar['akumulasiadjust'];
		
		
        $nilai=$bar['hargaperolehan']-$akumulasiBulanan;
		
        #jika doubledeclining
        if($bar['persendecline']>'0'){
            $thnawal=substr($bar['awalpenyusutan'],0,4);
            $blnawal=substr($bar['awalpenyusutan'],5,2);
            $total=($thnawal*12)+$blnawal;
            
            $thnNow=substr($kdAst,0,4);
            $blnNow=substr($kdAst,5,2);
            
            $totalBulanAwal = 12-$blnawal+1;
            $totalTahun = $thnNow-$thnawal-1;
            
            $totalNow=($thnNow*12)+$blnNow+1;
            $selisihNow=$totalNow-$total;
			
            $sekarang=0;
            $out=0;
            $akumNow=0;
            
            // Depresiasi s/d akhir tahun
            $before = $sekarang = $bar['hargaperolehan'];
            if($totalTahun>-1) {
                $akumNow += $totalBulanAwal/12 * $bar['persendecline']/100 * $sekarang;
            }
            $sekarang -= $akumNow;
            
            // Depresiasi per Tahun
            if($totalTahun>0) {
                for($i=0;$i<$totalTahun;$i++) {
                    $akumNow += $sekarang*$bar['persendecline']/100;
                    $sekarang -= $sekarang*$bar['persendecline']/100;
                    
                }
            }
            
            // Depresiasi per Bulan
            $out = $sekarang*($bar['persendecline']/100)/12;
            //if($bar->jlhblnpenyusutan==$selisihNow) {
            if($bar['jlhblnpenyusutan']<$selisihNow) {
                $akumNow += $sekarang;
                $sekarang = $out = 0;
            } else {
                if($totalTahun>-1) {
                    if(intval($blnNow)>0) {
                        $akumNow += (intval($blnNow)*$out);
                        $sekarang -= (intval($blnNow)*$out);
                    }
                } else {
                    $akumNow += ($blnNow-$blnawal+1)*$out;
                    $sekarang -= ($blnNow-$blnawal+1)*$out;
                }
            }
            
            $akumulasiBulanan=$akumNow;
            $nilai=$sekarang;
            $bar['bulanan']=$out;
        }
		
		
        if($nilai==0){
            if($sisblnCek<0){
                //$bar['hargaperolehan'] = 0;
                $bar['jlhblnpenyusutan'] = 0;
                $selisih['months_total'] = 0;
                $sisabln = 0;
                // $akumulasiBulanan = 0;
                $akumulasiBulanan = $bar['hargaperolehan'];
                $bar['bulanan']=0;
                $nilai=0;   
            }
        }
        
        if($sisblnCek<0){
            if(($bar['status']!=0)&&($bar['status']!=1)){
                //$bar['hargaperolehan'] = 0;
                $bar['jlhblnpenyusutan'] = 0;
                $selisih['months_total'] = 0;
                $sisabln = 0;
                $akumulasiBulanan = 0;
                $bar['bulanan']=0;
                $nilai=0;
            }
        }
		if($sisblnCek<=0){
			if($akumulasiBulanan != $bar['hargaperolehan']){
				$bar['jlhblnpenyusutan'] = 0;
                $selisih['months_total'] = 0;
                $sisabln = 0;
                $akumulasiBulanan = $bar['hargaperolehan'];
                $bar['bulanan']=0;
                $nilai=0;   
			}
        }
		
        $data.="<td align=right>".hidezerodecimal($bar['hargaperolehan'],2)."</td>"; 
        $data.="<td align=right>".$bar['jlhblnpenyusutan']."</td>";  
        $data.="<td align=right>".$usiaaset."</td>";  //ini dia
        $data.="<td align=right>".hidezerodecimal($sisabln)."</td>";  
        $data.="<td nowrap>".$bar['keterangan']."</td>";                   
        $data.="<td nowrap>".$bar['awalpenyusutan']."</td>"; 
		
		#= ambil data total nilai aset
		$data.="<td>".$akhirpenyusutan."</td>"; 
		
        for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){
            if($awalan<10){
				$prdcek=$bulanDt[0]."0".$awalan;
            }else{
                $prdcek=$bulanDt[0]."".$awalan;
            }
            
            if($bar['periodenonaktif']!="0000-00"){
                $isiPeriode=str_replace("-","",$bar['periodenonaktif']);
                $cekperiodedt=intval($isiPeriode-$prdcek);
                if($cekperiodedt<=0){
                    $bar['bulanan']=0;
                }
            }
		
			#= jika akhir penyusutan=periode ini maka tambah selisih (jika ada selisih)
			if($prdcek==str_replace("-","",$akhirpenyusutan)){
				$datanilaiselisih=$nilaiselisih;
			}else{
				$datanilaiselisih=0;
			}
			
            $blnBerjalan=0;
			
			if($bulanDt[0]==substr($bar['awalpenyusutan'],0,4)){
				$blnawal=intval(substr($bar['awalpenyusutan'],5,2));
				if($awalan>=$blnawal){
					$blnBerjalan=$bar['bulanan'];
					$data.="<td align=right>".hidezerodecimal($rupiahbulanan+$datanilaiselisih,2)."</td>";//ini yg 0
					$thnan[$bar['kodeasset']]+=$rupiahbulanan+$datanilaiselisih;
					$arrSubBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
					
					$totPerBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
				}else{
					$data.="<td align=right>0</td>";//ini yg 0
				}   
			}else{
				if($prdcek>str_replace("-","",$akhirpenyusutan)){
					$data.="<td align=right>0</td>";//ini yg 0
				}else{					
					$blnBerjalan=$bar['bulanan'];
					$data.="<td align=right>".hidezerodecimal($rupiahbulanan+$datanilaiselisih,2)."</td>";//ini yg 0
					$thnan[$bar['kodeasset']]+=$rupiahbulanan+$datanilaiselisih;
					$arrSubBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
					$totPerBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
				}
			}
        }

        $data.="<td align=right nowrap>".hidezerodecimal(($thnan[$bar['kodeasset']]),2)."</td>";//ini yg 0
		
		
        // $data.="<td align=right nowrap>".hidezerodecimal($akumulasiBulanan+$datanilaiselisih+$datanilaiselisihakhir,2)."</td>"; 
        $data.="<td align=right nowrap>".hidezerodecimal($akumulasiBulanan,2)."</td>"; 
		
		//+$bar['akumulasiadjust']

        #disposal
        $strpa="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='DISPOGAIN'";
        $respa=$owlPDO->query($strpa) or die(print " Gagal: ".PDOException::getMessage());
        $respa->setFetchMode(PDO::FETCH_ASSOC);
        $barpa=$respa->fetch();
        $dislabarugi=$barpa['nilai'];


        $ressup=$owlPDO->query("select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='DIS".substr($bar['kodeasset'],4,2)."'");
        $ressup->setFetchMode(PDO::FETCH_ASSOC);
        $barsup=$ressup->fetch();
        $dispenyusutan=$barsup['noakundebet'];
        $disaset=$barsup['noakunkredit'];


        $sDispoaset="select a.kodeasset, b.noakun, sum(b.jumlah) as nilai from ".$dbname.".keu_disposalasset a 
                left join ".$dbname.".keu_jurnaldt b on a.notransaksi=b.noreferensi where a.kodeasset='".$bar['kodeasset']."' 
                and b.noakun in ('".$dislabarugi."','".$dispenyusutan."','".$disaset."') group by a.kodeasset,b.noakun";
        $qDispoaset=$owlPDO->query($sDispoaset) or die(print " Gagal: ".PDOException::getMessage());
        $qDispoaset->setFetchMode(PDO::FETCH_ASSOC);
        $arrDisponilai=array();
        while ($barDispoaset=$qDispoaset->fetch()) {
            $arrDisponilai[$barDispoaset['noakun']]=$barDispoaset['nilai'];
        }
        
        $data.="<td align=right>".hidezerodecimal(-1*($arrDisponilai[$disaset]),2)."</td>";
        $data.="<td align=right>".hidezerodecimal($arrDisponilai[$dispenyusutan],2)."</td>";
        $data.="<td align=right>".hidezerodecimal($arrDisponilai[$dislabarugi],2)."</td>";

        $data.="<td align=right>".hidezerodecimal($nilai,2)."</td>"; 
        
        $data.="<td align=right>".hidezerodecimal($bar['persendecline'],2)."</td>";        
        $data.="<td nowrap>";
            (@$bar['leasing']==0?@$data.="Not Leasing":(@$bar['leasing']==1?$data.="Leasing":$data.="Ex Leasing"));
        $data.="</td>";
        $data.="<td align=center>".($bar['periodenonaktif']=='0000-00'?'':$bar['periodenonaktif'])."</td>";        
        $data.="<td align=center>".$bar['tipelokasi']."</td>";        
        
        $data.="</tr>";
        
        #subtotal
        $subTotalHargaPerolehan[] = $bar['hargaperolehan'];
        $subTotalJlhPenyusutan[] = $bar['jlhblnpenyusutan'];
        // $subTotalUsia[] = $selisih['months_total'];
        $subTotalUsia[] = $usiaaset;
        $subTotalSisa[] = $sisabln;
        // $subTotalAkumulasiPenyusutan[] = $akumulasiBulanan+$datanilaiselisih;
        // $subTotalAkumulasiPenyusutan[] = $akumulasiBulanan+$datanilaiselisih+$datanilaiselisihakhir;
        $subTotalAkumulasiPenyusutan[] = $akumulasiBulanan;
        $subTotalNilaiBuku[] = $nilai;
        $subTotalNilaiBulanan[] = $bar['bulanan'];
        $tahunanSusut[$thnNow][]=$thnan[$bar['kodeasset']];
        if($awalTipeAsset== $rowsCount){
            $data.="<tr>
                   <td colspan=14>Sub Total</td>
                   <td style='text-align:right;'>".hidezerodecimal(array_sum($subTotalHargaPerolehan),2)."</td>
                   <td style='text-align:right;'>".array_sum($subTotalJlhPenyusutan)."</td>
                   <td style='text-align:right;'>".array_sum($subTotalUsia)."</td>
                   <td style='text-align:right;'>".array_sum($subTotalSisa)."</td>
                   
                   <td colspan=3></td>";
				   // $totPerBulan=array();
                   for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){
                   $data.="
                   <td style='text-align:right;'>".hidezerodecimal($arrSubBulan[$awalan],2)."</td>";
						//$totPerBulan[$awalan]+=$arrSubBulan[$awalan];
                   }
                   $arrSubBulan=array();
                   $data.="
                   <td style='text-align:right;'>".hidezerodecimal(array_sum($tahunanSusut[$thnNow]),2)."</td>


                   <td style='text-align:right;'>".hidezerodecimal(array_sum($subTotalAkumulasiPenyusutan),2)."</td>
				   <td colspan=3></td>
                   <td style='text-align:right;'>".hidezerodecimal(array_sum($subTotalNilaiBuku),2)."</td>
                   <td colspan=4></td>
                   </tr>";
            $subTotalHargaPerolehan = array();
            $subTotalJlhPenyusutan = array();
            $subTotalUsia = array();
            $subTotalSisa = array();
            $subTotalAkumulasiPenyusutan = array();
            $subTotalakumulasiadjust = array();
            $subTotalNilaiBuku = array();
            $tahunanSusut= array();
        }
        
        @$totHarga+=$bar['hargaperolehan'];
        // @$totHargaAkumul+=$akumulasiBulanan+$datanilaiselisih+$datanilaiselisihakhir;
        @$totHargaAkumul+=$akumulasiBulanan;
        @$totNilai+=$nilai;
        @$bulanan+=$bar['bulanan'];
        // @$tahunan+=($bar['bulanan']*$perkalibulanan);
        // @$tpengurang+=$selisih['months_total'];
        @$tpengurang+=$usiaaset;
        @$tpenambah+= $bar['jlhblnpenyusutan'];
        @$tsisa+=$sisabln;
        
}



$data.="<tr bgcolor='#AFACAC'><td colspan=14><b>".$_SESSION['lang']['total']."</b></td>";
$data.="<td align=right><b>".hidezerodecimal($totHarga,2)."</b></td>";
$data.="<td align=right><b>".$tpenambah."</b></td>";
$data.="<td align=right><b>".$tpengurang."</b></td>";

$data.="<td align=right><b>".$tsisa."</b></td>";

$data.="<td colspan=3>&nbsp;</td>";
$tahunan=0;
for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){
	$data.="<td align=right><b>".hidezerodecimal($totPerBulan[$awalan],2)."</b></td>";
	@$tahunan+=$totPerBulan[$awalan];
}
$data.="<td align=right><b>".hidezerodecimal($tahunan,2)."</b></td>";
// $data.="<td align=right><b>".hidezerodecimal($totHargaakumulasiadjust,2)."</b></td>";
$data.="<td align=right><b>".hidezerodecimal($totHargaAkumul,2)."</b></td>";
$data.="<td colspan=3>&nbsp;</td>";
$data.="<td align=right><b>".hidezerodecimal($totNilai,2)."</b></td>";
$data.="<td colspan=4>&nbsp;</td>";
$data.="</tbody>
</table></div>";


##### untuk menu button dan panggil menu dr atas ##### 
switch($proses)
{
        // tampilkan preview //
        case'preview':
                if($kdOrg=='')
                {
                    echo "warning : Organization code is obligatory";
                    exit(); 
                }
                else if($kdAst=='')
                {
                    echo "warning : Bulan is obligatory";
                    exit(); 
                }
                else 
                {
                    if($tipelaporan == '1') {
                        echo reportAssetDetail();
                    } else if($tipelaporan == '2') {
                        echo $data; //panggil data dari preview di atas
                    } else {
                        exit("Warning : Tipe Laporan ini belum ada, silahkan hubungi Tim IT");
                    }
                }
        break;


        // tampilkan exel //
        case 'excel':

                if($kdOrg=='')
                {
                        echo "warning : Organization code is obligatory";
                        exit(); 
                }
                else if($kdAst=='')
                {
                        echo "warning : Asset type is obligatory";
                        exit(); 
                }
				
				
				$nop = "Laporan_Daftar_Asset_.xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("dataasset", $data);
				$xls->headers($nop);
				echo $xls->buildFile();
				
				/*
                $data.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];  
                $tglSkrg=date("Ymd");
                $nop_="Laporan_Daftar_Asset_".$tglSkrg;
                //$nop_"Laporan Daftar Asset ".$nmOrg."_".$nmAst;
                //$nop_="Daftar Asset : ".$nmOrg." ".$nmAst;
                if(strlen($data)>0)
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
                        if(!fwrite($handle,$data))
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
*/				
                break;          
                // tutup tampilakn panggil exel //

        // tampilkan PDF //     
        case'pdf':

                if($kdOrg=='')
                {
                        echo "warning : Organization code is obligatory";
                        exit(); 
                }
                else if($kdAst=='')
                {
                        echo "warning : Asset type is obligatory";
                        exit(); 
                }

    //buat header pdf
    class PDF extends FPDF
                    {
                            function Header() {
                                    //declarasi header variabel
                                    global $conn;
                                    global $dbname;
                                    global $align;
                                    global $length;
                                    global $colArr;
                                    global $title;

                                    global $nmOrg;
                                    global $kdOrg;
                                    global $kdAst;
                                    global $nmAst;
                                    global $thnPer;
                                    global $nmAsst;
                                    global $namakar;
                                    global $selisih;
                                    global $where;
                                    global $owlPDO;


                                    //alamat PT minanga dan logo
                                    $query = selectQuery($dbname,'organisasi','alamat,telepon',
                                            "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                                    $orgData = fetchData($query);

                                    $width = $this->w - $this->lMargin - $this->rMargin;
                                    $height = 20;
                                    if($_SESSION['org']['kodeorganisasi']=='HIP'){  $path='images/hip_logo.jpg'; } else if($_SESSION['org']['kodeorganisasi']=='SIL'){  $path='images/sil_logo.jpg'; } else if($_SESSION['org']['kodeorganisasi']=='SIP'){  $path='images/sip_logo.jpg'; }
                                    $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                                    $this->SetFont('Arial','B',9);
                                    $this->SetFillColor(255,255,255);   
                                    $this->SetX(100);   
                                    $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');  
                                    $this->SetX(100);       
                                    $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');  
                                    $this->SetX(100);           
                                    $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L'); 
                                    $this->Line($this->lMargin,$this->tMargin+($height*4),
                                    $this->lMargin+$width,$this->tMargin+($height*4));
                                    $this->Ln();
                                    //tutup logo dan alamat

                                    //untuk sub judul
                                    $this->SetFont('Arial','B',10);
                                    $this->Cell((20/100*$width)-5,$height,"Asset List",'',0,'L');
                                    $this->Ln();
                                    $this->SetFont('Arial','',8);
                                    $this->Cell((100/100*$width)-5,$height,"Printed By : ".$namakar[$_SESSION['standard']['userid']],'',0,'R');
                                    $this->Ln();
                                    $this->Cell((100/100*$width)-5,$height,"Date : ".date('d-m-Y'),'',0,'R');
                                    $this->Ln();
                                    $this->Cell((100/100*$width)-5,$height,"Time : ".date('h:i:s'),'',0,'R');
                                    $this->Ln();
                                    $this->Ln();
                                    //tutup sub judul

                                    //judul tengah
                                    $this->SetFont('Arial','B',12);
                                    $this->Cell($width,$height,strtoupper("Asset List "."$nmAst")." ".$_SESSION['lang']['periode'].":".$kdAst,'',0,'C');
                                    $this->Ln();
                                    $this->Cell($width,$height,strtoupper("$nmOrg"),'',0,'C');
                                    $this->Ln();
                                    $this->Ln();
                                    //tutup judul tengah

                                    //isi atas tabel
                                    $this->SetFont('Arial','B',6);
                                    $this->SetFillColor(220,220,220);
                                    $this->Cell(2/100*$width,$height,"No",1,0,'C',1);
                                    $this->Cell(7/100*$width,$height,$_SESSION['lang']['kodeorganisasi'],1,0,'C',1);
                                    $this->Cell(7/100*$width,$height,$_SESSION['lang']['kodeasset'],1,0,'C',1);
                                    $this->Cell(7/100*$width,$height,$_SESSION['lang']['thnperolehan'],1,0,'C',1);
                                    $this->Cell(15/100*$width,$height,$_SESSION['lang']['namaasset'],1,0,'C',1);
                                    //$this->Cell(5/100*$width,$height,$_SESSION['lang']['status'],1,0,'C',1);
                                    $this->Cell(9/100*$width,$height,$_SESSION['lang']['hargaperolehan'],1,0,'C',1);
                    
                                    $this->Cell(9/100*$width,$height,$_SESSION['lang']['jumlahbulanpenyusutan'],1,0,'C',1);
                                    $this->Cell(6/100*$width,$height,$_SESSION['lang']['usia']." (".$_SESSION['lang']['bulan'].")",1,0,'C',1);
                                    $this->Cell(6/100*$width,$height,$_SESSION['lang']['sisa']." (".$_SESSION['lang']['bulan'].")",1,0,'C',1);
                                    $this->Cell(9/100*$width,$height,$_SESSION['lang']['akumulasipenyusutan'],1,0,'C',1);
                                    $this->Cell(9/100*$width,$height,$_SESSION['lang']['nilaibuku'],1,0,'C',1);

                                    $this->Cell(9/100*$width,$height,$_SESSION['lang']['awalpenyusutan'],1,0,'C',1);
                                    $this->Cell(6/100*$width,$height,$_SESSION['lang']['bulanan'],1,1,'C',1);



                                    //tutup isi tabel
                            }//tutup header pdfnya


                            function Footer()
                            {
                                    $this->SetY(-15);
                                    $this->SetFont('Arial','I',8);
                                    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
                            }
                    }
                    //untuk tampilan setting pdf
                    $pdf=new PDF('L','pt','Legal');//untuk kertas L=len p=pot
                    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                    $height = 20;
                    $pdf->AddPage();
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',6);//ukuran tulisan
                    //tutup tampilan setting


                    //isi tabel dan tabelnya
                    $no=0;
                    $sql="select * from ".$dbname.".sdm_daftarasset where kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$kdOrg."') ".$where." order by kodeasset";
                    $qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                    $qDet->setFetchMode(PDO::FETCH_ASSOC);
                    while($res=$qDet->fetch())
                    {
                            $no+=1;
                            $tgl1=$res['awalpenyusutan']."-01";
                            $tgl2=$kdAst."-02";
                            $selisih=datediff1($tgl1,$tgl2);
                            if($selisih[months_total]>$res['jlhblnpenyusutan'])
                            {
                            $selisih[months_total]=$res['jlhblnpenyusutan'];
                            }
                            #periksa siapa lebih besar
                           if($tgl1>$tgl2)
                            {
                                $selisih[months_total]=0;
                            }           
                            $sisabln=$res['jlhblnpenyusutan']-$selisih[months_total];
                            if(substr($sisabln,0,1)=='-')
                            {
                            $sisabln=0;
                            }
                            $akumulasiBulanan=$res['bulanan']*$selisih[months_total];
                            if($akumulasiBulanan>$res['hargaperolehan'])
                            {
                            $akumulasiBulanan=$res['hargaperolehan'];
                            }
                            $nilai=$res['hargaperolehan']-$akumulasiBulanan;
                            $pdf->Cell(2/100*$width,$height,$no,1,0,'C',1);
                            $pdf->Cell(7/100*$width,$height,$res['kodeorg'],1,0,'L',1); 
                            $pdf->Cell(7/100*$width,$height,$res['kodeasset'],1,0,'L',1);   
                            $pdf->Cell(7/100*$width,$height,$res['tahunperolehan'],1,0,'R',1);  
                            $pdf->Cell(15/100*$width,$height,$res['namasset'],1,0,'L',1);
                            $pdf->Cell(9/100*$width,$height,hidezerodecimal($res['hargaperolehan'],2),1,0,'R',1); 
                            $pdf->Cell(9/100*$width,$height,hidezerodecimal($res['jlhblnpenyusutan'],2),1,0,'R',1);   
                            $pdf->Cell(6/100*$width,$height,$selisih[months_total],1,0,'C',1);
                            $pdf->Cell(6/100*$width,$height,$sisabln,1,0,'C',1);
                            $pdf->Cell(9/100*$width,$height,hidezerodecimal($akumulasiBulanan,2),1,0,'C',1);
                            $pdf->Cell(9/100*$width,$height,hidezerodecimal($nilai,2),1,0,'C',1);
                            $pdf->Cell(9/100*$width,$height,$res['awalpenyusutan'],1,0,'L',1);  
                            $pdf->Cell(6/100*$width,$height,hidezerodecimal($res['bulanan'],2),1,1,'R',1);                          
                            //$pdf->Ln();   
                            $totHarga+=$res['hargaperolehan'];
                            $totHargaAkumul+=$akumulasiBulanan;
                            $totNilai+=$nilai;
                            $bulanan+=$res['bulanan'];
                    }
                    $pdf->Cell(38/100*$width,$height,$_SESSION['lang']['total'],1,0,'R',1);
                    $pdf->Cell(9/100*$width,$height,hidezerodecimal($totHarga,2),1,0,'R',1);
                    $pdf->Cell(21/100*$width,$height,'',1,0,'R',1);
                    $pdf->Cell(9/100*$width,$height,hidezerodecimal($totHargaAkumul,2),1,0,'R',1);
                    $pdf->Cell(9/100*$width,$height,hidezerodecimal($totNilai,2),1,0,'R',1);
                    $pdf->Cell(9/100*$width,$height,'',1,0,'R',1);
                    $pdf->Cell(6/100*$width,$height,hidezerodecimal($bulanan,2),1,0,'R',1);
            $pdf->Output();
##### Tutup PDF #####

break;
default;

}    


function reportAssetDetail() {
    global $conn;
    global $dbname;
    global $owlPDO;
    global $param;
    global $where;

    # {PREPARE}
    $getdata = 'OBJECT';
    $hargaperolehan = [];
    $bulanandt = [];
    $subtotalHarga = [];
    $subtotalMutasi = [];
    $subtotalNilaiBuku = [];
    $grandtotalNilaiBuku = 0;

    # ARRAY KELOMPOK
     $arrkelompok = array(
        'A' => array(
            'NILAI PEROLEHAN AKTIVA TETAP' => 
            array(
                'KB' => 'Kelompok Bangunan', 
                'KBB' => 'Kelompok Bukan Bangunan'
            )
        ),
        'B' => array(
            'AKUMULASI PENYUSUTAN AKTIVA' => 
            array(
                'KB' => 'Kelompok Bangunan', 
                'KBB' => 'Kelompok Bukan Bangunan'
            )
        )
    );

    # ARRAY KELOMPOR PER TIPE ASSET
    $arrkelompokkb = array('IS','TN');
    $arrkelompokkbb = array('AB','AT','BD','BO','DU','HA','IL','JB','JL','JM','JU','KD','KL','KP','KS','MJ','MP','MS','OA','PA','PI','PK','PP','PR','PS','PU','PT','PW','PY','SP','TM','TW');

    # SQL QUERY
    $sql = selectQuery($dbname,"sdm_5tipeasset");
    $res = fetchData($sql,$getdata);

    foreach($res as $v):
        if(in_array($v->kodetipe,$arrkelompokkb)) {
            $arrtipeasset['KB'][$v->kodetipe] = $v->kodetipe;
            $getkelompokasset[$v->kodetipe] = 'KB';
        }
            
        if(in_array($v->kodetipe,$arrkelompokkbb)) {
            $arrtipeasset['KBB'][$v->kodetipe] = $v->kodetipe;
            $getkelompokasset[$v->kodetipe] = 'KBB';
        }
    endforeach;

    if(empty($param['unit'])) {
        $sql = "select * from ".$dbname.".sdm_daftarasset where  
            kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kdOrg']."') ".$where." order by tipeasset";
    } else {
        $sql = "select * from ".$dbname.".sdm_daftarasset where kodeorg = '".$param['unit']."' ".$where." order by tipeasset";
    }

    $res = fetchData($sql,$getdata);
    foreach($res as $v):
        # Dapatkan Harga Perolehan
        $bulananx[$v->kodeasset][$v->kodeorg] = $v->bulanan; # Bulanan
        $selisihbulananx[$v->kodeasset][$v->kodeorg] = ($v->hargaperolehan-($v->bulanan*$v->jlhblnpenyusutan)); # Selisih Bulan Penyusutan

        #= buat bulan terakhir penyusutan
		$akhirpenyusutan[$v->kodeasset][$v->kodeorg]=substr(periodelalu(jumlahbulandepan($v->awalpenyusutan,$v->jlhblnpenyusutan)),0,4);
      
        #= jika akhir penyusutan = periode ini maka tambah selisih (jika ada selisih)
        if(substr($param['kdAsset'],0,4)==$akhirpenyusutan[$v->kodeasset][$v->kodeorg]){
            $datanilaiselisih[$v->tipeasset][$v->kodeorg] = $selisihbulananx[$v->kodeasset][$v->kodeorg];
        }else{
            $datanilaiselisih= 0;
        }

        # A = Nilai Perolehan Aktiva Tetap
        $arrdata['A'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] = $v->tipeasset;
        $hargaperolehan['A'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] += $v->hargaperolehan;
        $bulanandt['A'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] += ($v->bulanan*12);

        # B = Akumulasi Penyusutan
        $arrdata['B'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] = $v->tipeasset;
        $hargaperolehan['B'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] += $v->hargaperolehan;
        $bulanandt['B'][$getkelompokasset[$v->tipeasset]][$v->tipeasset][$v->kodeorg] += ($v->bulanan*12);

    endforeach;

    # MAKE OPTION
    $optNamaTipe = makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
    $optNamaOrg  = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

    // echo "<pre>";
    // print_r($datanilaiselisih);

    $tab = "";

    $tab .= "<table class='sortable' cellpadding=5 cellspacing=1 width=100%>";
        $tab .= "<thead>";
            $tab .= "<tr class=rowheader>";
                // $tab .= "<th>".$_SESSION['lang']['nourut']."</th>";
                $tab .= "<th rowspan=2 colspan=2>".$_SESSION['lang']['deskripsi']."</th>";
                $tab .= "<th rowspan=2 >".$_SESSION['lang']['kodeorganisasi']."</th>";
                $tab .= "<th>Nilai Perolehan</th>";

                // for($awalan=1;$awalan<=intval($param['kdAsset']);$awalan++){
                    $tab .= "<th align=center>Mutasi - 2024</th> ";
                // }
                $tab .= "<th align=center>Nilai Buku</th> ";
            $tab .= "</tr>";

            $tab .= "<tr class=rowheader>";
                $tab .= "<td align=center>(Rp)</td>";
                $tab .= "<td align=center>(Rp)</td>";
                $tab .= "<td align=center>(Rp)</td>";
            $tab .= "</tr>";
        $tab .= "</thead>";
        
        foreach($arrkelompok as $abjad => $valabjad):
            foreach($valabjad as $head => $valhead):
                $tab .= "<tr class=rowcontent>";
                    $tab .= "<td align=center><b>".$abjad."</b></td>";
                    $tab .= "<td colspan=99><b><u>".$head."</u></b></td>";
                    // $tab .= "<td></td>";
                $tab .= "</tr>";
                foreach($valhead as $subhead => $valsubhead):
                    # Kelompok Asset
                    $tab .= "<tr class=rowcontent>";
                        $tab .= "<td></td>";
                        $tab .= "<td colspan=99 align=left><i>".$valsubhead."</i></td>";
                    $tab .= "</tr>";

                    # Tipe Asset
                    foreach($arrtipeasset[$subhead] as $tipeassetdt => $valtipeasset):
                        if($arrdata[$abjad][$subhead][$tipeassetdt] != '') { # Jika Ada Data
                            foreach($arrdata[$abjad][$subhead][$tipeassetdt] as $kodeorgdt => $valkddt):
                                $tab .= "<tr class=rowcontent>";
                                    $tab .= "<td></td>";
                                    $tab .= "<td align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".ucwords(strtolower($optNamaTipe[$tipeassetdt]))."</td>";
                                    $tab .= "<td align=center>[".$kodeorgdt."] - ".$optNamaOrg[$kodeorgdt]."</td>";
                                    $tab .= "<td align=right>".number_format($hargaperolehan[$abjad][$subhead][$tipeassetdt][$kodeorgdt])."</td>";
                                    $tab .= "<td align=right>".number_format($bulanandt[$abjad][$subhead][$tipeassetdt][$kodeorgdt]+$datanilaiselisih[$tipeassetdt][$kodeorgdt])."</td>";

                                    # Nilai Buku
                                    $nilaibuku[$abjad][$subhead][$tipeassetdt][$kodeorgdt]=($hargaperolehan[$abjad][$subhead][$tipeassetdt][$kodeorgdt]-($bulanandt[$abjad][$subhead][$tipeassetdt][$kodeorgdt]+$datanilaiselisih[$tipeassetdt][$kodeorgdt]));

                                    $tab .= "<td align=right>".number_format($nilaibuku[$abjad][$subhead][$tipeassetdt][$kodeorgdt]+$datanilaiselisih[$tipeassetdt][$kodeorgdt])."</td>";
                                $tab .= "</tr>";

                                $subtotalMutasi[$abjad]+=$bulanandt[$abjad][$subhead][$tipeassetdt][$kodeorgdt]+$datanilaiselisih[$tipeassetdt][$kodeorgdt];
                                $subtotalHarga[$abjad]+=$hargaperolehan[$abjad][$subhead][$tipeassetdt][$kodeorgdt];
                                $subtotalNilaiBuku[$abjad]+=$nilaibuku[$abjad][$subhead][$tipeassetdt][$kodeorgdt];
                                $grandtotalNilaiBuku+=$nilaibuku[$abjad][$subhead][$tipeassetdt][$kodeorgdt];
                            endforeach;
                        } else { # Jika Data Kosong
                            $tab .= "<tr class=rowcontent>";
                                $tab .= "<td></td>";
                                $tab .= "<td align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".ucwords(strtolower($optNamaTipe[$tipeassetdt]))."</td>";
                                $tab .= "<td></td>";
                                $tab .= "<td align=right>".number_format(0)."</td>";
                                $tab .= "<td align=right>".number_format(0)."</td>";
                                $tab .= "<td align=right>".number_format(0)."</td>";
                            $tab .= "</tr>";
                        }
                    endforeach;

                endforeach;

                # SUBTOTAL
                $tab .= "<tr class=rowcontent>";
                    $tab .= "<td colspan=3></td>";
                    $tab .= "<td align=right><b>".number_format($subtotalHarga[$abjad])."</b></td>";
                    $tab .= "<td align=right><b>".number_format($subtotalMutasi[$abjad])."</b></td>";
                    $tab .= "<td align=right><b>".number_format($subtotalNilaiBuku[$abjad])."</b></td>";
                $tab .= "</tr>";
                
                # PEMBATAS
                $tab .= "<tr class=rowcontent>";
                    $tab .= "<td colspan=5></td>";
                    $tab .= "<td align=right><b>".number_format($grandtotalNilaiBuku)."</b></td>";
                $tab .= "</tr>";
            endforeach;
        endforeach;


    $tab .= "</table>";

    return $tab;
}
?>
