<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$per=checkPostGet('periode','');
$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$karyawan=checkPostGet('karyawan','');
$jumlah=checkPostGet('jumlah','');
$tipekar=checkPostGet('tipekar','');
$tipe = checkPostGet('tipe', '');


$tanggal=tanggalsystem($tanggal);
switch($method)
{

    case 'loadLaporan':

    $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='GJTHNLU' ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch();
    $gjthnlu=$bar['nilai'];

    $str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='KOMGJEXSLP'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch(); 
    $exslip=$bar['nilai'];

    if ($karyawan!='') {
        $kary=" and a.karyawanid='".$karyawan."'";
    }

    if ($tipekar!='') {
        $tpkary=" and b.tipekaryawan ='".$tipekar."'";
    }

    ####################data  
          //prepare array data gaji karyawan,nama
       $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,b.norekeningbank,d.realkode,b.namabank,b.nik from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".keu_5daftarbank d on d.namabank = a.namabank
                where a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and a.periodegaji = '".$per."' ".$tpkary." ".$kary." and a.idkomponen not in (".$gjthnlu.")";
        $qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
        $qSlip->setFetchMode(PDO::FETCH_ASSOC);
        $rCek=owlBaris($qSlip);
        if($rCek>0)
        {
            while($rSlip=$qSlip->fetch())
            {
                if($rSlip['karyawanid']!='')
                {   
                    $arrKary[$rSlip['karyawanid']]=$rSlip['karyawanid'];
                    $arrKomp[$rSlip['karyawanid']]=$rSlip['idkomponen'];
                    $arrNmKary[$rSlip['karyawanid']]=$rSlip['namakaryawan'];
                    $arrNorek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
                    $arrNmBank[$rSlip['karyawanid']]=$rSlip['namabank'];
                    $arrNik[$rSlip['karyawanid']]=$rSlip['nik'];
                    $kodebank[$rSlip['karyawanid']]=$rSlip['realkode'];
                   
                    $arrJmlh[$rSlip['karyawanid'].$rSlip['idkomponen']]=$rSlip['jumlah'];
                    
                    $TarrJmlh[$rSlip['idkomponen']]+=$rSlip['jumlah'];
                }
            }
             $sSliplalu="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.norekeningbank,e.realkode from 
               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
               left join ".$dbname.".keu_5daftarbank e on e.namabank = a.namabank
               where a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and a.periodegaji = '".periodelalu($per)."' ".$tpkary." ".$kary." and a.idkomponen  in (".$gjthnlu.")";
   
            $qSliplalu=$owlPDO->query($sSliplalu) or die(print " Gagal: ".PDOException::getMessage());
            $qSliplalu->setFetchMode(PDO::FETCH_ASSOC);
            $rCeklalu=owlBaris($qSliplalu);
            if($rCeklalu>0)
            {
                while($rSliplalu=$qSliplalu->fetch())
                {
                    if(in_array($rSliplalu['karyawanid'], $arrKary[$rSlip['karyawanid']]))
                    {
                        $arrJmlh[$rSliplalu['karyawanid'].$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];
                        
                        $TarrJmlh[$rSliplalu['idkomponen']]+=$rSliplalu['jumlah'];
                    }
                    else
                    {
                        $arrKary[$rSliplalu['karyawanid']]=$rSliplalu['karyawanid'];
                        $arrKomp[$rSliplalu['karyawanid']]=$rSliplalu['idkomponen'];
                        $arrNmKary[$rSliplalu['karyawanid']]=$rSliplalu['namakaryawan'];
                        $arrNorek[$rSlip['karyawanid']]=$rSlip['norekeningbank'];
                        $kodebank[$rSlip['karyawanid']]=$rSlip['realkode'];

                        $arrJmlh[$rSliplalu['karyawanid'].$rSliplalu['idkomponen']]=$rSliplalu['jumlah'];

                        $TarrJmlh[$rSliplalu['idkomponen']]+=$rSliplalu['jumlah'];
                    }
                }
            }

            //array data komponen penambah dan pengurang
             $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
               left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
               where a.plus=1 and b.jumlah!=0 and b.periodegaji='".$per."' 
               and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and a.id not in ('26','28',".$gjthnlu.",".$exslip.")
               and b.karyawanid like '%".$karyawan."%' order by a.id";
            $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
            $qKomp->setFetchMode(PDO::FETCH_ASSOC);
            $arrIdKompPls = array();
            $n=-1;
            while($rKomp=$qKomp->fetch()) {
                 $n++;
                  $arrIdKompPls[$n]=$rKomp['id'];
                  $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
            }

            #periode lalu
            $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component  a
               left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
               where a.plus=1 and b.jumlah!=0 and b.periodegaji='".periodelalu($per)."' 
               and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.id in (".$gjthnlu.")
               and b.karyawanid like '%".$karyawan."%' order by a.id";
            $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
            $qKomp->setFetchMode(PDO::FETCH_ASSOC);
    
            while($rKomp=$qKomp->fetch()) {
                 $n++;
                  $arrIdKompPls[$n]=$rKomp['id'];
                  $arrNmKomPls[$rKomp['id']]=$rKomp['name'];
            }

            $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
               left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
               where a.plus=0 and b.jumlah!=0 and b.periodegaji='".$per."' 
               and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')  
               and b.karyawanid like '%".$karyawan."%' and a.id not in (".$gjthnlu.") order by a.id";
            $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
            $qKomp->setFetchMode(PDO::FETCH_ASSOC);
            $arrIdKompMin = array();
            while($rKomp=$qKomp->fetch()) {
                  $arrIdKompMin[]=$rKomp['id'];
                  $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
            }

            #periode lalu
            $sKomp="select distinct id,name from ".$dbname.".sdm_ho_component a
               left join ".$dbname.".sdm_gaji_vw b on a.id=b.idkomponen
               where a.plus=0 and b.jumlah!=0 and b.periodegaji='".periodelalu($per)."' 
               and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')  
               and b.karyawanid like '%".$karyawan."%' and a.id  in (".$gjthnlu.") order by a.id";
            //echo $sKomp;
            $qKomp=$owlPDO->query($sKomp) or die(print " Gagal: ".PDOException::getMessage());
            $qKomp->setFetchMode(PDO::FETCH_ASSOC);
            //$arrIdKompMin = array();
            while($rKomp=$qKomp->fetch()) {
                  $arrIdKompMin[]=$rKomp['id'];
                  $arrNmKomMin[$rKomp['id']]=$rKomp['name'];
            }
            
            
            $str="SELECT a.*,b.namakaryawan,b.nik,b.statuspajak,b.norekeningbank,b.tipekaryawan,c.namajabatan
            FROM ".$dbname.".sdm_catu a
            left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
            left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
            where periodegaji='".$per."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar=$res->fetch()){
                $arrIdKompPls[$n+1]='60';
                $arrNmKomPls['60']='Natura';
                $arrJmlh[$bar['karyawanid'].'60']=$bar['jumlahrupiah'];
                $totalcatu[$bar['karyawanid']]=$bar['totalcatu'];

                $TarrJmlh['60']+=$bar['jumlahrupiah'];
            }


    ####################data



    $stream.="<table class=sortable cellpading=1 cellspacing=1 border=0 width=100% name='laporan_Gaji_transfer_".periodelalu($per)."-".$pt."' data-print='true'>";
    $stream.="<thead>";
    $stream.="<tr class=rowheader>";
    $stream.="<td align=center>No</td>";
    $stream.="<td align=center>NP</td>";
    $stream.="<td align=center>Name</td>";
    $stream.="<td align=center>Terima <br> (IDR)</td>";
    $stream.="<td align=center>Bank Name <br> Account Number <br> Branch</td>";
    $stream.="</tr>";
    $stream.="</thead>";
    $stream.="<tbody>";

    $no=1;
    foreach($arrKary as $dtKary){
        if($arrNmKary[$dtKary]!=''){
      
            $stspajak=  makeOption($dbname, 'datakaryawan', 'karyawanid,statuspajak',"karyawanid='".$karid."'");
            $nonpwp=  makeOption($dbname, 'datakaryawan', 'karyawanid,npwp',"karyawanid='".$karid."'");
            $optemail=  makeOption($dbname, 'datakaryawan', 'karyawanid,email');
            
            $arrPlus=Array();
            $s=0;
            foreach($arrIdKompPls as $idKompPls){
                setIt($arrJmlh[$dtKary.$idKompPls],0);
                $arrPlus[$s]=$arrJmlh[$dtKary.$idKompPls];
                $s++;
            }

            $arrMin=Array();
            $q=0;
            foreach($arrIdKompMin as $idKompMin){
                setIt($arrJmlh[$dtKary.$idKompMin],0);
                $arrMin[$q]=$arrJmlh[$dtKary.$idKompMin];
                $q++;
            }
            $gajiBersih=array_sum($arrPlus)-array_sum($arrMin);

            $stream.="<tr class='rowcontent datarekening'>";
            $stream.="<td>".$no++."</td>";
            $stream.="<td>".$arrNik[$dtKary]."</td>";
            $stream.="<td>".$arrNmKary[$dtKary]."</td>";
            $stream.="<td align=right>".number_format(ceil($gajiBersih))."</td>";
            $stream.="<td>".$arrNmBank[$dtKary]." - ".$arrNorek[$dtKary]."</td>";
            $stream.="</tr>";

        }
    }
    
    
    $stream.="<tr bgcolor=lightgray>";

    
    $stream.="<tbody></table>";
    
    if($tipe=='excel'){

        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=laporanpajak-".$pt."-".periodelalu($per).".csv");

        echo"P,".$tanggal.",".$sumberrek.",".$ttlbaris.",".ceil($ttlamount).",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\n";

        foreach($arrKary as $dtKary){
            if ($kodebank[$dtKary]== $kodebanksumber) {
                $jenis[$dtKary]='IBU';
                $rek[$dtKary]='';
            }else{
                if ($gajiBersih<100000000) {
                    $jenis[$dtKary]='LBU';
                    $rek[$dtKary]=$optreklbu[$kodebank[$dtKary]];
                }
                else
                {
                    $jenis[$dtKary]='RBU';
                    $rek[$dtKary]=$optrekrbu[$kodebank[$dtKary]];
                }
            }

            if ($optemail[$dtKary]!='') {
                $mail[$karid]='Y';
                $email[$karid]=$optemail[$karid];
            }
            else
            {
                $mail[$karid]='N';
                $email[$karid]='';
            }
        }


        foreach ($arrKary as $dtKary) {
           $arrPlus=Array();
           $s=0;
           foreach($arrIdKompPls as $idKompPls){
            setIt($arrJmlh[$dtKary.$idKompPls],0);
            $arrPlus[$s]=$arrJmlh[$dtKary.$idKompPls];
            $s++;
        }
        $arrMin=Array();
        $q=0;
        foreach($arrIdKompMin as $idKompMin){
            setIt($arrJmlh[$dtKary.$idKompMin],0);
            $arrMin[$q]=$arrJmlh[$dtKary.$idKompMin];
            $q++;
        }

        $gajiBersih=array_sum($arrPlus)-array_sum($arrMin);
        
        echo"".$arrNorek[$dtKary].",".$arrNmKary[$dtKary].",,,,IDR,".ceil($gajiBersih).",,,".$jenis[$dtKary].",".$rek[$dtKary].",".$optkombinasinama[$kodebank[$dtKary]].",,,,,".$mail[$dtKary].",".$email[$dtKary].",,,,,,,,,,,,,,,,,,,,,OUR,N\n";
    }   

    }
    else
    {

      echo $stream;
  }
}

  break;

   

    case 'getunit':

        if(strlen($pt)<4){
            $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='kebun'";
            $res=fetchData($str);
            foreach ($res as $val) {
                if($val['kodeorganisasi']==$unit){
                    $optOrg.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";                  
                }else{
                    $optOrg.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
                }
            }
        }
      
        echo $optOrg;
    break;

    case 'getkar':


            $optkar="<option value='' selected>".$_SESSION['lang']['all']."</option>";
            $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where kodeorganisasi='".$pt."' and tipekaryawan='".$tipekar."' ";
            $res=fetchData($str);
            foreach ($res as $val) {
              
                    $optkar.="<option value='".$val['karyawanid']."' >".$val['karyawanid']."-".$val['namakaryawan']."</option>";                  
               
            }
        
        echo $optkar.'##';

    break;
        default:
        break;
}


?>