
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

include_once('lib/zMysql.php');
require_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$tmp=explode(',',$_GET['column']);
$notransaksi=$tmp[0];
$data['notransaksi']=$notransaksi;
$kodeorg=$tmp[1];
$data['kodeorg']=$kodeorg;
$jenis=$tmp[2];
$data['jenis']=$jenis;
$optPt = makeOption($dbname,'organisasi','kodeorganisasi,alokasi',"kodeorganisasi='".$data['kodeorg']."'");
$optKodeorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjenis = makeOption($dbname,"lgl_5checklist","kode,jenis","status=1");
			
		$sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
        $qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk=$qInduk->fetch();

        $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
            $nama=$bar1->namaorganisasi;
        }

        $arrHead = setheadreport('',$rInduk['induk']);
        $path=$arrHead['logo'];
       /* $formdet="<style type='text/css'>
        .ispo table {
         border-collapse: collapse;
        }
        .ispo td {
         border-top: 1px solid #000;
         border-bottom: 0px solid #000;
         border-left: 1px solid #000;
         border-right: 1px solid #000;
        }
        </style>";*/
        $formdet="<div'>";
        $formdet.="<p align=center style=font-weight:bold;><font size='5'>Checklist Form</font> </p>";
        $formdet.="<p align=center style=font-weight:bold;>".$nama."</p>";
        $formdet.="<p align=center style=font-weight:bold;>NO TRANSAKSI :".$notransaksi."</p>";
        $formdet.="<hr>";


        $stredit="select * from ".$dbname.".lgl_checklistht where notransaksi='".$data['notransaksi']."' ";
        $resedit=fetchData($stredit);

        $streditdet="select * from ".$dbname.".lgl_checklistdt where notransaksi='".$data['notransaksi']."' ";
        $reseditdet=fetchData($streditdet);
        $dataedit=array();
        foreach ($reseditdet as $ky => $vle) {
            $dataedit[$vle['kodechdt']]['checklist']=$vle['cheklist'];
            $dataedit[$vle['kodechdt']]['keterangan']=$vle['keterangan'];
        }

    /*print_r($dataedit);
    exit();*/

        if($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' ){
        $formdet .= "<table align=center >";

        $formdet .= "<tr>";
        $formdet .= "<td>PT</td>";
        $formdet .= "<td>".$optKodeorg[$optPt[$data['kodeorg']]]."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Berkedudukan Di</td>";
        $formdet .= "<td>".$resedit[0]['berkedudukandi']."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Letak Tanah</td>";
        $formdet .= "<td>".$resedit[0]['letaktanah']."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Desa</td>";
        $formdet .= "<td>".$resedit[0]['desa']."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Kecamatan</td>";
        $formdet .= "<td>".$resedit[0]['kecamatan']."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Kabupaten</td>";
        $formdet .= "<td>".$resedit[0]['kabupaten']."</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>Luas</td>";
        $formdet .= "<td>".$resedit[0]['luastanah']."Ha</td>";
        $formdet .= "</tr>";

        $formdet .= "</table>";
        }
        
        $formdet .= "<table cellspacing='0'  border=1>";
        $formdet .= "<thead>";
        $formdet .= "<tr>";
        $formdet .= "<td>Dokumen Referensi</td>";
        $formdet .= "<td>Pertanyaan</td>";
        if($optjenis[$data['jenis']]!='ISPO'){
        $formdet .= "<td>Panduan</td>";}
        if($optjenis[$data['jenis']]=='ISPO'){
        $formdet .= "<td></td>";
        $formdet .= "<td>Kriteria</td>";
        $formdet .= "<td></td>";
        $formdet .= "<td>Indikator</td>";
        $formdet .= "<td>Panduan</td>";
        }
        $formdet .= "<td>Ya/Tidak</td>";
        $formdet .= "<td>Keterangan</td>";
        $formdet .= "</tr>";
        $formdet .= "</thead>";
        $formdet .= "<tbody>";
        
        $str="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and tipe=1 order by nourut";    
        $res=fetchData($str);
        $deskripsi=array();
        foreach ($res as $key => $val) {
            $deskripsi[$val['noinduk']]=$val['deskripsi'];
        }

        $str="select min(kode) as minnourut, max(kode) as maxnourut from lgl_5checklistdet where kodeheader='".$data['jenis']."' and tipe=0 ";    
        $res=fetchData($str);
        $maxnourut=$res[0]['maxnourut'];
        $minnourut=$res[0]['minnourut'];

        /*print_r($deskripsi);
        exit();*/
        $str0="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk=0 and tipe=0 order by nourut"; 
        $res0=fetchData($str0);
        foreach ($res0 as $key0 => $value0) 
        {
            
            $formdet .= "<tr>";
            $formdet .= "<td style='vertical-align:top;'>".$value0['nourut']."</td>";
            $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$value0['deskripsi'])."</td>";
            if($optjenis[$data['jenis']]=='SMK3'){
                if($deskripsi[$value0['kode']]=='')
                {
                    $formdet .= "<td></td><td></td><td></td>";
                }
                else
                {
                    $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                    $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td style='vertical-align:top'>".$dataedit[$value0['kode']]['keterangan']."</td>";
                   
                }
            }
            elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
            {
                if($deskripsi[$value0['kode']]=='')
                {
                    $formdet .= "<td></td>";
                    $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td style='vertical-align:top'>".$dataedit[$value0['kode']]['keterangan']."</td>";
                }
                else
                {
                    $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                    $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td style='vertical-align:top'>".$dataedit[$value0['kode']]['keterangan']."</td>";
                   
                }
                
            }
            if($optjenis[$data['jenis']]!='ISPO'){
            $formdet .= "</tr>";}
            $str1="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value0['kode']."' and tipe=0 order by nourut";    
            $res1=fetchData($str1);
            foreach ($res1 as $key1 => $value1)
            {
                if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "<tr>";}
                if($optjenis[$data['jenis']]=='ISPO' && $key1>0){
                    $formdet .= "<tr><td></td><td></td>";}
                $formdet .= "<td style='vertical-align:top'>".$value0['nourut'].".".$value1['nourut']."</td>";
                $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$value1['deskripsi'])."</td>";
                if($optjenis[$data['jenis']]=='SMK3'){
                    if($deskripsi[$value1['kode']]=='')
                    {
                        $formdet .= "<td></td><td></td><td></td>";
                    }
                    else
                    {
                        $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                        $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value1['kode'],'check',$dataedit[$value1['kode']]['checklist'])."</td>";
                        $formdet .= "<td style='vertical-align:top'>".$dataedit[$value1['kode']]['keterangan']."</td>";
                        
                    }
                }
                elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                {
                    if($deskripsi[$value0['kode']]=='')
                    {
                        $formdet .= "<td></td>";
                        $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                        $formdet .= "<td style='vertical-align:top'>".$dataedit[$value0['kode']]['keterangan']."</td>";
                    }
                    else
                    {
                        $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                        $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                        $formdet .= "<td style='vertical-align:top'>".$dataedit[$value0['kode']]['keterangan']."</td>";
                       
                    }
                    
                }
                if($optjenis[$data['jenis']]!='ISPO'){
                $formdet .= "</tr>";}
                $str2="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value1['kode']."' and tipe=0 order by nourut";    
                $res2=fetchData($str2);
                foreach ($res2 as $key2 => $value2)
                {
                    if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "<tr>";
                    $formdet .= "<td style='vertical-align:top'>".$value0['nourut'].".".$value1['nourut'].".".$value2['nourut']."</td>";}
                    else{
                        if($key2>0)
                        {
                            $formdet .= "<tr><td></td><td></td><td></td><td></td><td style='vertical-align:top'>".$value2['nourut']."</td>";
                        }
                        else
                        {
                            $formdet .= "<td style='vertical-align:top'>".$value2['nourut']."</td>";
                        }
                    }
                    $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$value2['deskripsi'])."</td>";
                    if($optjenis[$data['jenis']]=='SMK3' || $optjenis[$data['jenis']]=='ISPO'){
                        if($deskripsi[$value2['kode']]=='')
                        {
                            
                            if($optjenis[$data['jenis']]=='ISPO')
                            {   
                                $formdet .= "<td></td>";
                                $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value2['kode'],'check',$dataedit[$value2['kode']]['checklist'])."</td>";
                                $formdet .= "<td style='vertical-align:top'>".$dataedit[$value2['kode']]['keterangan']."</td></tr>";
                                
                            }
                            else
                            {
                                $formdet .= "<td></td><td></td><td></td>";
                            }
                        }
                        else
                        {   
                            if($optjenis[$data['jenis']]=='ISPO')
                            {   
                                $arrexplode=explode('####', $deskripsi[$value2['kode']]);
                                foreach ($arrexplode as $kx => $vx) {
                                    if($vx!=''){
                                        if($kx==0){
                                           $formdet .= "<td style='vertical-align:top'>".$vx."</td>";
                                           $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value2['kode'],'check',$dataedit[$value2['kode']]['checklist'])."</td>";
                                            $formdet .= "<td style='vertical-align:top'>".$dataedit[$value2['kode']]['keterangan']."</td>";
                                            $formdet .= "</tr>";
                                        }
                                        else
                                        {
                                            $formdet .= "<tr >";
                                            $formdet .= "<td ></td><td></td><td></td><td></td><td></td><td></td>";
                                            $formdet .= "<td >".$vx."</td><td></td><td></td>";
                                            $formdet .= "</tr>";
                                        }
                                    }

                                }
                                
                            }
                            else
                            {
                                $formdet .= "<td style='vertical-align:top'>".str_replace('####',"<br/>",$deskripsi[$value2['kode']])."</td>";
                                $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value2['kode'],'check',$dataedit[$value2['kode']]['checklist'])."</td>";
                                $formdet .= "<td style='vertical-align:top'>".$dataedit[$value2['kode']]['keterangan']."</td>";
                            }
                            
                            
                        }
                    }
                    elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                        {
                            $formdet .= "<td></td><td></td>";
                        }
                    if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "</tr>";}
                    $str3="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value2['kode']."' and tipe=0 order by nourut";    
                    $res3=fetchData($str3);
                    foreach ($res3 as $key3 => $value3)
                    {
                        if($optjenis[$data['jenis']]!='ISPO'){
                        $formdet .= "<tr>";}
                        $formdet .= "<td style='vertical-align:top'>".$value0['nourut'].".".$value1['nourut'].".".$value2['nourut'].".".$value3['nourut']."</td>";
                        $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$value3['deskripsi'])."</td>";
                        if($optjenis[$data['jenis']]=='SMK3'){
                            if($deskripsi[$value3['kode']]=='')
                            {
                                $formdet .= "<td></td><td></td><td></td>";
                            }
                            else
                            {
                                $formdet .= "<td style='vertical-align:top'>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                                $formdet .= "<td style='vertical-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".makeElement('checklist_'.$value3['kode'],'check',$dataedit[$value3['kode']]['checklist'])."</td>";
                                $formdet .= "<td style='vertical-align:top'>".$dataedit[$value3['kode']]['keterangan']."</td>";
                                
                            }
                        }
                        elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                        {
                            $formdet .= "<td></td><td></td>";
                        }
                    }

                }
            }
        
                        $formdet .= "</tr>";
        }

        
        $formdet .= "</tbody>";
        $formdet.="</table>";
        

        $formdet.="</div>";
			$dompdf = new Dompdf();
            $dompdf->loadHtml($formdet);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("form checklist",array("Attachment"=>0));