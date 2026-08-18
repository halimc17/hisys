<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_GET['proses'];
$_POST['tahun']==''?$tahun=$_GET['tahun']:$tahun=$_POST['tahun'];
$_POST['kodeorg']==''?$kodeorg=$_GET['kodeorg']:$kodeorg=$_POST['kodeorg'];
$_POST['kegiatan']==''?$kegiatan=$_GET['kegiatan']:$kegiatan=$_POST['kegiatan'];

if($proses=='cekkegiatan'){
    $optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select a.kegiatan, b.namakegiatan from ".$dbname.".bgt_budget a 
        left join ".$dbname.".setup_kegiatan b on a.kegiatan=b.kodekegiatan
        where a.tahunbudget = ".$tahun." and a.kodeorg LIKE '".$kodeorg."%' and a.kegiatan is not NULL
        group by a.kegiatan
        ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $optkegiatan.="<option value='".$bar->kegiatan."'>".$bar->kegiatan." - ".$bar->namakegiatan."</option>";
    }
        
    echo $optkegiatan;
    exit;
}

   if($tahun==''||$kodeorg==''||$kegiatan=='')
{
    exit("Error:Field Tidak Boleh Kosong");
}

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
//$optAk=makeOption($dbname, 'keu_5akun', 'noakun,namaakun','level=5');
$optKg=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
	$namakar[$bar->karyawanid]=$bar->namakaryawan;
}

if($_GET['proses']=='excel')
{
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
    $tab.="<table>
        <tr><td colspan=4 align=left>".$optNm[$kodeorg]."</td></tr>   
        <tr><td colspan=4>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['budget']." ".$optKg[$kegiatan]." ".$_SESSION['lang']['budgetyear'].": ".$tahun."</td></tr>   
        </table>";
}
else
{
    $bg="";
    $brdr=0;
}

// kamus barang
$sDetail="select a.* from ".$dbname.".log_5masterbarang a where 1";
$qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
$qDetail->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$qDetail->fetch())
{
    $kamusbarang[$rDetail['kodebarang']]['nama']=$rDetail['namabarang'];
    $kamusbarang[$rDetail['kodebarang']]['satuan']=$rDetail['satuan'];
}

$sDetail="select a.* from ".$dbname.".bgt_budget a
    where a.tahunbudget = ".$tahun." and a.kodeorg like '".$kodeorg."%' and a.kegiatan = '".$kegiatan."' and a.rupiah>0 and tipebudget = 'ESTATE'";
$qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
$qDetail->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$qDetail->fetch())
{
    $listblok[$rDetail['kodeorg']]=$rDetail['kodeorg'];
            
    // volume
    $volume[$rDetail['kodeorg']]=$rDetail['volume'];
    $satvol=$rDetail['satuanv'];
    $rotasi[$rDetail['kodeorg']]=$rDetail['rotasi'];
    
    // sdm
    if((substr($rDetail['kodebudget'],0,3)=='SDM')or($rDetail['kodebudget']=='SUPERVISI')){
        $sdm[$rDetail['kodebudget']]=$rDetail['kodebudget'];
        $datasdm[$rDetail['kodebudget']]+=$rDetail['jumlah'];
        $datasdmsatuan[$rDetail['kodebudget']]=$rDetail['satuanj'];
        $totalrupiah+=$rDetail['rupiah'];
    }
    
    // supervisi
    
    // barang
    if(substr($rDetail['kodebudget'],0,3)=='M-3'){
        $barang[$rDetail['kodebarang']]=$rDetail['kodebarang'];
        $databarang[$rDetail['kodebarang']]+=$rDetail['jumlah'];
        $totalrupiah+=$rDetail['rupiah'];
    }   
    
    // peralatan
    if($rDetail['kodebudget']=='TOOL'){
        $alat[$rDetail['kodebarang']]=$rDetail['kodebarang'];
        $dataalat[$rDetail['kodebarang']]+=$rDetail['jumlah'];
        $totalrupiah+=$rDetail['rupiah'];
    }   
    
    // kendaraan
    if($rDetail['kodebudget']=='VHC'){
        $kendaraan[$rDetail['kodevhc']]=$rDetail['kodevhc'];
        $datakendaraan[$rDetail['kodevhc']]+=$rDetail['jumlah'];
        $datakendaraansatuan[$rDetail['kodevhc']]=$rDetail['satuanj'];
        $totalrupiah+=$rDetail['rupiah'];
    }   

    // kontrak
    if($rDetail['kodebudget']=='KONTRAK'){
        $kontrakrupiah+=$rDetail['rupiah'];
        $totalrupiah+=$rDetail['rupiah'];
    }   

}

if(!empty($listblok))sort($listblok);
if(!empty($sdm))sort($sdm);
if(!empty($barang))sort($barang);
if(!empty($alat))sort($alat);
if(!empty($kendaraan))sort($kendaraan);

if(!empty($sdm))$jumlahsdm=count($sdm);
if(!empty($barang))$jumlahbarang=count($barang);
if(!empty($alat))$jumlahalat=count($alat);
if(!empty($kendaraan))$jumlahkendaraan=count($kendaraan);

//echo "<pre>";
//print_r($datasdm);
//echo "</pre>";

$jumlahblok=0;
if(!empty($listblok))foreach($listblok as $bloknya){
    $jumlahblok+=1;
    $volumenya+=$volume[$bloknya];
    $rotasinya+=$rotasi[$bloknya];    
//    echo $bloknya." ".$volume[$bloknya].'</br>';
}

$brscek=owlBaris($qDetail);
if($brscek!=0)
{

    $tab.="<table cellspacing=1 cellpadding=1 border=".$brdr." class=sortable><thead>";
    $tab.="<tr class=rowheader>";
    $tab.="<td align=center colspan=2 ".$bg.">".$_SESSION['lang']['keterangan']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['satuan']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jumlah']."</td>";
    $tab.="</tr></thead><tbody>";
    
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['volume']."</td>";
        $tab.="<td>".$satvol."</td>";
        $tab.="<td align=right>".number_format($volumenya,2)."</td>";
    $tab.="</tr>";
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['rotasi']."</td>";
        $tab.="<td>".$_SESSION['lang']['kali']."</td>";
        @$totalrotasi=$rotasinya/$jumlahblok;
        $tab.="<td align=right>".number_format($totalrotasi)."</td>";
    $tab.="</tr>";
    
    // hk
    if(!empty($sdm)){
        $tab.="<tr class=rowcontent>";
        $jumlahbarissdm=$jumlahsdm;
        $tab.="<td rowspan=".$jumlahbarissdm.">".$_SESSION['lang']['hk']."</td>";
        $totalsdm=0;
            foreach($sdm as $sdmnya){
            if($barissdm>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$sdmnya."</td>";
            $tab.="<td>".$datasdmsatuan[$sdmnya]."</td>";
            $tab.="<td align=right>".number_format($datasdm[$sdmnya])."</td>";
            $tab.="</tr>";
            $barissdm+=1;
            $totalsdm+=$datasdm[$sdmnya];
            $satuansdm=$datasdmsatuan[$sdmnya];
        }
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['hk']."</td><td></td><td></td>";  
    }
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['hk']." ".$_SESSION['lang']['total']."</td>";
        $tab.="<td>".$satuansdm."</td>";
        $tab.="<td align=right>".number_format($totalsdm)."</td>";
    $tab.="</tr>";
    
    // hk/sat
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['hk']."/".$_SESSION['lang']['satuan']."</td>";
        $tab.="<td>".$satuansdm."/".$satvol."</td>";
        @$hkpersat=$totalsdm/$volumenya;
        $tab.="<td align=right>".number_format(@$hkpersat,2)."</td>";
    $tab.="</tr>";
    
    // barang
    $barisbarang=0;
    if(!empty($barang)){
        $tab.="<tr class=rowcontent>";
        $jumlahbarisbarang=$jumlahbarang;
        $tab.="<td rowspan=".$jumlahbarisbarang.">".$_SESSION['lang']['namabarang']."</td>";
        foreach($barang as $barangnya){
            if($barisbarang>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kamusbarang[$barangnya]['nama']."</td>";
            $tab.="<td>".$kamusbarang[$barangnya]['satuan']."</td>";
            $tab.="<td align=right>".number_format($databarang[$barangnya])."</td>";
            $tab.="</tr>";
            $barisbarang+=1;
        }
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['namabarang']."</td><td></td><td></td>";        
    }
            
    // barang / satuan
    $barisbarang=0;
    if(!empty($barang)){
        $tab.="<tr class=rowcontent>";
        $jumlahbarisbarang=$jumlahbarang;
        $tab.="<td rowspan=".$jumlahbarisbarang.">".$_SESSION['lang']['namabarang']."/".$_SESSION['lang']['satuan']."</td>";
        foreach($barang as $barangnya){
            if($barisbarang>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kamusbarang[$barangnya]['nama']."</td>";
            $tab.="<td>".$kamusbarang[$barangnya]['satuan']."/".$satvol."</td>";
            @$barangpersat=$databarang[$barangnya]/$volumenya;
            $tab.="<td align=right>".number_format(@$barangpersat,5)."</td>";
            $tab.="</tr>";
            $barisbarang+=1;
        }
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['namabarang']."/".$_SESSION['lang']['satuan']."</td><td></td><td></td>";        
    }
        
    // alat
    $barisalat=0;
    if(!empty($alat)){
        $tab.="<tr class=rowcontent>";
        $jumlahbarisalat=$jumlahalat;
        $tab.="<td rowspan=".$jumlahbarisalat.">".$_SESSION['lang']['peralatan']."</td>";
            foreach($alat as $alatnya){
            if($barisalat>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kamusbarang[$alatnya]['nama']."</td>";
            $tab.="<td>".$kamusbarang[$alatnya]['satuan']."</td>";
            $tab.="<td align=right>".number_format($dataalat[$alatnya])."</td>";
            $tab.="</tr>";
            $barisalat+=1;
        }
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['peralatan']."</td><td></td><td></td>";        
    }
            
    // alat / satuan
    $barisalat=0;
    if(!empty($alat)){
        $tab.="<tr class=rowcontent>";
        $jumlahbarisalat=$jumlahalat;
        $tab.="<td rowspan=".$jumlahbarisalat.">".$_SESSION['lang']['peralatan']."/".$_SESSION['lang']['satuan']."</td>";
            foreach($alat as $alatnya){
            if($barisalat>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kamusbarang[$alatnya]['nama']."</td>";
            $tab.="<td>".$kamusbarang[$alatnya]['satuan']."/".$satvol."</td>";
            @$alatpersat=$dataalat[$barangnya]/$volumenya;
            $tab.="<td align=right>".number_format(@$alatpersat,5)."</td>";
            $tab.="</tr>";
            $barisalat+=1;
        }        
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['peralatan']."/".$_SESSION['lang']['satuan']."</td><td></td><td></td>";        
    }

    // kendaraan
    $bariskendaraan=0;
    if(!empty($kendaraan)){
        $tab.="<tr class=rowcontent>";
        $jumlahbariskendaraan=$jumlahkendaraan;
        $tab.="<td rowspan=".$jumlahbariskendaraan.">".$_SESSION['lang']['kendaraan']."</td>";
            foreach($kendaraan as $kendaraannya){
            if($bariskendaraan>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kendaraannya."</td>";
            $tab.="<td>".$datakendaraansatuan[$kendaraannya]."</td>";
            $tab.="<td align=right>".number_format($datakendaraan[$kendaraannya])."</td>";
            $tab.="</tr>";
            $bariskendaraan+=1;
        }
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['kendaraan']."</td><td></td><td></td>";        
    }
            
    // kendaraan / satuan
    $bariskendaraan=0;
    if(!empty($kendaraan)){
        $tab.="<tr class=rowcontent>";
        $jumlahbariskendaraan=$jumlahkendaraan;
        $tab.="<td rowspan=".$jumlahbariskendaraan.">".$_SESSION['lang']['kendaraan']."/".$_SESSION['lang']['satuan']."</td>";
            foreach($kendaraan as $kendaraannya){
            if($bariskendaraan>0){
                $tab.="<tr class=rowcontent>";
            }
            $tab.="<td>".$kendaraannya."</td>";
            $tab.="<td>".$datakendaraansatuan[$kendaraannya]."/".$satvol."</td>";
            @$kendaraanpersat=$datakendaraan[$kendaraannya]/$volumenya;
            $tab.="<td align=right>".number_format(@$kendaraanpersat,5)."</td>";
            $tab.="</tr>";
            $bariskendaraan+=1;
        }      
    }else{
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['kendaraan']."/".$_SESSION['lang']['satuan']."</td><td></td><td></td>";        
    }

        
    // kontrak
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['kontrak']."</td>";
        $tab.="<td align=right>".number_format($kontrakrupiah)."</td>";
    $tab.="</tr>";
            
    // kontrak / satuan
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['kontrak']."/".$_SESSION['lang']['satuan']."</td>";
        @$kontrakrupiahpersat=$kontrakrupiah/$volumenya;
        $tab.="<td align=right>".number_format(@$kontrakrupiahpersat)."</td>";
    $tab.="</tr>";

    // total
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']." (Rp.)</td>";
        $tab.="<td align=right>".number_format($totalrupiah)."</td>";
    $tab.="</tr>";
            
    // total / satuan
    $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']." (Rp.)/".$_SESSION['lang']['satuan']."</td>";
        $totalrupiahpersat=$totalrupiah/$volumenya;
        $tab.="<td align=right>".number_format($totalrupiahpersat)."</td>";
    $tab.="</tr>";
        
    $tab.="</tbody></table>";
            
 }
 else
 {
     exit("Error:Data Kosong");
 }

	switch($proses)
        {
            case'preview':
            	echo $tab;
            break;
                        
            case'excel':
           
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            
            $nop_="laporanBudgeRekap_".$kegiatan."_".$kodeorg."_".$tahun."___".$dte;
            $stream=$tab;
            if(strlen($stream)>0)
            {
                 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                 gzwrite($gztralala, $stream);
                 gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
            } 
            break;
			
			
	default;
	break;
	
	
}    
?>