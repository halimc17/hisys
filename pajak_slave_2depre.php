<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$periode=checkPostGet('periode','');
$tpAsset=checkPostGet('tpAsset','');
$subtpAsset=checkPostGet('subtpAsset','');
$method=checkPostGet('method','');
$proses=checkPostGet('proses','');

switch ($method) {
    case 'getunit':

        $optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sunit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".sdm_daftarasset a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where b.induk='".$pt."' order by namaorganisasi";
        $runit=$owlPDO->query($sunit) or die(print " Gagal: ".PDOException::getMessage());
        $runit->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$runit->fetch()){
            $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }

        echo $optunit;

    break;

    case 'getperiode':

        if ($unit!='') {
            $whr=" and kodeorg='".$unit."' ";
        }
        $optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sper="select distinct periode from ".$dbname.".setup_periodeakuntansi where char_length(kodeorg)='4' ".$whr." order by periode desc";
        $res=$owlPDO->query($sper) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($rper=$res->fetch())
        {
            $optper.="<option value='".$rper['periode']."'>".$rper['periode']."</option>";
        }


        echo $optper;

    break;

    case'getsubtpasset':

        $opt.="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$tpAsset."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()) {
            $opt.="<option value=".$bar['kodesub'].">".$bar['namasub']."</option>";
        }

        echo $opt;  

    break;

    case 'getlaporan':

        $whr=" a.kodeorg='".$unit."'";
        if ($unit=='') {
            $whr=" a.kodeorg in (select kodeorganisasi from organisasi where induk='".$pt."')";
        }
        if ($tpAsset!='') {
            $whr.=" and a.tipeasset='".$tpAsset."'";
        }
        if ($subtpAsset!='') {
            $whr.=" and b.kodesub='".$subtpAsset."'";
        }

        $listdt=array();
        $str="select a.kodeasset,a.tipeasset,a.posisiasset as lokasiasset,b.namasub as subkategori,c.namaharta as namaharta,a.jlhblnpenyusutan as usia_bln,c.jumlah_bulan as usia_pajak,
              a.tanggalperolehan as tanggal_pembelian,a.hargaperolehan as nilai_perolehan,a.namasset as nama_asset,a.bulanan,left(a.tanggalperolehan,7) as awalsusut,a.awalpenyusutan,a.tanggaldisposal,a.status,b.id_namaharta 
              from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5subtipeasset b on a.tipeasset=b.kodetipe and a.subtipe=b.kodesub left join ".$dbname.".keu_5asset_namaharta c on b.id_namaharta=c.id_namaharta 
              where ".$whr." and left(tanggalperolehan,7)<='".$periode."-12' order by a.kodeasset asc, a.posisiasset asc";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()) {
            $listdt[$bar['kodeasset']]['jenisharta']=substr($bar['id_namaharta'],0,1);
            $listdt[$bar['kodeasset']]['klmpkharta']=substr($bar['id_namaharta'],0,2);
            $listdt[$bar['kodeasset']]['jnsusaha']=substr($bar['id_namaharta'],0,3);
            $listdt[$bar['kodeasset']]['nmharta']=$bar['id_namaharta'];
            $listdt[$bar['kodeasset']]['usia_bln']=$bar['usia_bln'];
            $listdt[$bar['kodeasset']]['usia_pajak']=$bar['usia_pajak'];
            $blnthndt=explode("-",$bar['tanggal_pembelian']);
            $listdt[$bar['kodeasset']]['blnperolehan']=intval($blnthndt[1]);
            $listdt[$bar['kodeasset']]['tahunperolehan']=$blnthndt[0];
            $listdt[$bar['kodeasset']]['nilai_perolehan']=$bar['nilai_perolehan'];
            $listdt[$bar['kodeasset']]['nama_asset']=$bar['nama_asset'];
            $listdt[$bar['kodeasset']]['tanggaldisposal']=$bar['tanggaldisposal'];
            @$listdt[$bar['kodeasset']]['bulanan']=$bar['nilai_perolehan']/$bar['usia_pajak'];
            $listdt[$bar['kodeasset']]['awalsusut']=$bar['awalsusut'];
            @$bar['bulanan']=$bar['nilai_perolehan']/$bar['usia_pajak'];

            #komersial
            $bar['periodenonaktif']=substr($bar['tanggaldisposal'],0,7);
            //$ytdbulankomersial=selisihbulan($periode,$bar['awalpenyusutan']);
            $tahun2=substr($periode,0,4);
            $bulan2=substr($periode,5,2);
            $tahun1=substr($bar['awalpenyusutan'],0,4);
            $bulan1=substr($bar['awalpenyusutan'],5,2);
            $ytdbulankomersial=(($tahun2*12)+intval($bulan2)) - (($tahun1*12)+intval($bulan1));
            $ytdbulankomersial+=1;
            if($bar['periodenonaktif']!='0000-00'){
                if($periode>=$bar['periodenonaktif']){
                    $ytdbulankomersial=0;
                    $tahun2=substr($periode,0,4);
                    $bulan2=substr($periode,5,2);
                    $tahun1=substr($bar['periodenonaktif'],0,4);
                    $bulan1=substr($bar['periodenonaktif'],5,2);
                    $ytdbulankomersial=(($tahun2*12)+intval($bulan2)) - (($tahun1*12)+intval($bulan1));
                    $ytdbulankomersial+=1;
                    if(substr($periode,0,4)!=substr($bar['periodenonaktif'],0,4)){
                        $bar['bulanan']=0;    
                    }
                }    
            }
            if($ytdbulankomersial>$bar['usia_bln']) {
                $ytdbulankomersial=$bar['usia_bln'];
            }    
            $sisakomersial=$bar['usia_bln']-$ytdbulankomersial;
            if($sisakomersial<=0){
                $sisakomersial=0;
            }
            $listdt[$bar['kodeasset']]['sisakomersial']=$sisakomersial;
            $listdt[$bar['kodeasset']]['ytdkomersial']=$ytdbulankomersial;


            #fiskal
            //$ytdbulan=selisihbulan($periode,$bar['awalsusut']);#jumlah bulan akumulasi fiskal
            $tahun2=substr($periode,0,4);
            $bulan2=substr($periode,5,2);
            $tahun1=substr($bar['awalsusut'],0,4);
            $bulan1=substr($bar['awalsusut'],5,2);
            $ytdbulan=(($tahun2*12)+intval($bulan2)) - (($tahun1*12)+intval($bulan1));
            // if($bar['kodeasset']=='TML-AB02000003'){
            //     exit('warning'.$ytdbulan);
            // }
            $ytdbulan+=1;
            if ($ytdbulan>$bar['usia_pajak']) {
                $ytdbulan=$bar['usia_pajak'];
            }
            $listdt[$bar['kodeasset']]['ytdbulan']=$ytdbulan;
            $sisabulan=$bar['usia_pajak']-$ytdbulan;
            if($sisabulan<0){
                $sisabulan=0;
            }
            $listdt[$bar['kodeasset']]['sisabulan']=$sisabulan;
            $akumulasidep=$bar['bulanan']*$ytdbulan;
            $listdt[$bar['kodeasset']]['akumulasidep']=$akumulasidep;
            $nilaibuku=$bar['nilai_perolehan']-$akumulasidep;
            $listdt[$bar['kodeasset']]['nilaibuku']=$nilaibuku;
            if($nilaibuku<0){
                $bar['bulanan']=0;
            }
            #selisih bulan
            $biaya_dep=0;
            
            $tgl1=$bar['awalpenyusutan']."-01";
            $tgl2=$periode."-02";
            $bulanDt=explode("-",$periode);
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
                $blnBerjalan=0;
                if($bulanDt[0]==substr($bar['awalpenyusutan'],0,4)){
                    $blnawal=intval(substr($bar['awalpenyusutan'],5,2));
                    if($awalan>=$blnawal){
                        $blnBerjalan=$bar['bulanan'];
                    }else{
                    }   
                }else{
                    $blnBerjalan=$bar['bulanan'];
                }
                //$thnan[$bar['kodeasset']]+=$blnBerjalan;
                $biaya_dep+=$blnBerjalan;  
            }
            $listdt[$bar['kodeasset']]['biaya_dep']=$biaya_dep;
        }   

        // echo "<pre>";
        // echo print_r($listdt);
        // echo "</pre>";
        // exit('warning : ');

        $border=0;
        $bgclr="";
        if ($proses=='excel') {
            $border=1;
            $bgclr="bgcolor='33FF00'";
        }
        //Jenis Harta   kelompok Harta  Jenis Usaha Nama Harta  Bln Perolehan   Thn Perolehan   Jenis Penyusutan Komersial  Jenis Penyusutan Fiskal Harga Perolehan Nilai Sisa Buku Penyusutan fiskal tahun ini Keterangan nama harta

        $tab.="<table class=sortable cellspacing=1 border=".$border." style='width:100%'>
        <thead>
            <tr>
                <td ".$bgclr." align=center>".$_SESSION['lang']['jenisharta']."</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['kelompokharta']."</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['jenisusaha']."</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['namaharta']."</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['blnperolehan']."</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['tahunperolehan']."</td>
                <td ".$bgclr." align=center>Jenis Penyusutan Komersial</td>
                <td ".$bgclr." align=center>Jenis Penyusutan Fiskal</td>
                <td ".$bgclr." align=center>".$_SESSION['lang']['hargaperolehan']."</td>
                <td ".$bgclr." align=center>Nilai Sisa Buku</td>
                <td ".$bgclr." align=center>Penyusutan fiskal tahun ini</td>
                <td ".$bgclr." align=center>Keterangan nama harta</td>
            </tr>
            
        </thead>";

        $no=0;
        foreach ($listdt as $kodeasset => $data){
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=right>".$data['jenisharta']."</td>"; 
            $tab.="<td align=right>".$data['klmpkharta']."</td>"; 
            $tab.="<td align=right>".$data['jnsusaha']."</td>"; 
            $tab.="<td align=right>".$data['nmharta']."</td>"; 
            $tab.="<td align=right>".$data['blnperolehan']."</td>"; 
            $tab.="<td align=right>".$data['tahunperolehan']."</td>"; 
            $tab.="<td align=right>1</td>"; 
            $tab.="<td align=right>1</td>"; 
            $tab.="<td align=right>".$data['nilai_perolehan']."</td>"; 
            $tab.="<td align=right>".$data['nilaibuku']."</td>"; 
            $tab.="<td align=right>".$data['akumulasidep']."</td>"; 
            $tab.="<td>".$data['nama_asset']."</td>"; 
            $tab.="</tr>";
        }
        $tab.="</table>";

        if ($proses=='preview') {
            echo $tab;
        }else{
            $tglSkrg = date("Ymd");
            $nop_ = "Lap_fafiscal_".$pt."_periode_".$periode;
            if (strlen($tab) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $tab)) {
                    echo "<script language=javascript1.2>
                            parent.window.alert('Can't convert to excel format');
                            </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
                }
                fclose($handle);
            }
        }
            
    break;
    
    default:
    break;
}

?>