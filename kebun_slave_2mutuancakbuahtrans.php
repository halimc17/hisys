<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt = checkPostGet('pt', '');
$rangetanggal = rangeTanggal($tgl1, $tgl2);

$unit = checkPostGet('unit', '');
$divisi = checkPostGet('divisi', '');


function my_operator($a, $b, $char) {
    switch($char) {
        case '=': return $a == $b;
        case '<=': return $a <= $b;
        case '>=': return $a >= $b;
        case '<': return $a < $b;
        case '>': return $a > $b;
    }
}

$where='';
if($pt!=''){
    $where.=" and b.induk='".$pt."'";
}

switch ($proses) {
    case 'preview':
        $str="select * from ".$dbname.".kebun_5jenismutu";
        $res= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $jenis[$bar['jenis']][$bar['kriteria']]=$bar['idjenis'];
        }


        $str="select * from ".$dbname.".kebun_5mutu";
        $res= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $idjenis[$bar['idjenis']]=$bar['idjenis'];
            $pt[$bar['pt']]=$bar['pt']; 
            $nilai[$bar['pt']][$bar['idjenis']]=$bar['idjenis'];
            $warna[$bar['pt']][$bar['warna']]=$bar['warna'];
            $rangedari[$bar['pt']][$bar['idjenis']]['rangedari']=$bar['rangedari'];
            $rangesampai[$bar['pt']][$bar['idjenis']]['rangesampai']=$bar['rangesampai'];
        }
        
        // echo "<pre>";
        // print_r($nilai);
        // echo "</pre>";



        $strtransport = "select b.induk as pt, substr(a.divisi,1,4) as unit, a.* from ".$dbname.". kebun_mutuancaktransport a left join ".$dbname.". organisasi b on substr(a.divisi,1,4)=b.kodeorganisasi where  a.tanggal between '".$tgl1."' and '".$tgl2."' ".$where;                     

/*select b.pt as pt, b.nilai, substr(a.divisi,1,4) as unit, a.* from ((kebun_mutuancaktransport a left join kebun_5mutu b on ((a.pt=b.pt))) left join kebun_5jenismutu c on ((b.idjenis=c.idjenis)))  where c.jenis='Mutu Hancak' and c.kriteria='Buah Tinggal' and (a.jjgancak/a.pokoksample) between b.rangedari and rangesampai*/
        $restransport = $owlPDO->query($strtransport) or die(print " Gagal: " . PDOException::getMessage());
        $restransport->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $restransport->fetch()) {
            $unit[$bar['unit']]=$bar['unit'];   
            $divisi[$bar['divisi']]=$bar['divisi'];
            $pt[$bar['pt']]=$bar['pt']; 
            $listdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]=$bar['divisi'];
            $listunit[$bar['pt']][$bar['unit']]=$bar['unit'];

            $pokoksampleunit[$bar['pt']][$bar['unit']]+=$bar['pokoksample']; 
            $pokoksampledivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['pokoksample'];           

            $jjgancakunit[$bar['pt']][$bar['unit']]+=$bar['jjgancak'];
            $jjgancakdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['jjgancak'];

            $btrancakunit[$bar['pt']][$bar['unit']]+=$bar['btrancak'];
            $btrancakdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['btrancak'];

            $jumlahtphunit[$bar['pt']][$bar['unit']]+=$bar['jumlahtph'];
            $jumlahtphdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['jumlahtph'];

            $jjgtransportunit[$bar['pt']][$bar['unit']]+=$bar['jjgtransport'];
            $jjgtransportdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['jjgtransport'];

            $btrtransportunit[$bar['pt']][$bar['unit']]+=$bar['btrtransport'];
            $btrtransportdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['btrtransport'];    
        }

        $strbuah = "select b.induk as pt, substr(a.divisi,1,4) as unit, a.* from ".$dbname.". kebun_mutubuah a left join ".$dbname.". organisasi b on substr(a.divisi,1,4)=b.kodeorganisasi where  a.tanggal between '".$tgl1."' and '".$tgl2."'".$where;  
        $resbuah = $owlPDO->query($strbuah) or die(print " Gagal: " . PDOException::getMessage());
        $resbuah->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $resbuah->fetch()) {

            $unit[$bar['unit']]=$bar['unit'];
              $pt[$bar['pt']]=$bar['pt'];   
            $divisi[$bar['divisi']]=$bar['divisi'];
            $listunit[$bar['pt']][$bar['unit']]=$bar['unit'];
            $listdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]=$bar['divisi'];
            
            $totaljjgunit[$bar['pt']][$bar['unit']]+=$bar['totaljjg']; 
            $totaljjgdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['totaljjg'];           

            $buahmatangunit[$bar['pt']][$bar['unit']]+=$bar['buahmatang'];
            $buahmatangdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['buahmatang'];

            $buahmentahunit[$bar['pt']][$bar['unit']]+=$bar['buahmentah'];
            $buahmentahdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['buahmentah'];

            $kurangmatangunit[$bar['pt']][$bar['unit']]+=$bar['kurangmatang'];
            $kurangmatangdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['kurangmatang'];

            $lewatmatangunit[$bar['pt']][$bar['unit']]+=$bar['lewatmatang'];
            $lewatmatangdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['lewatmatang'];

            $jjgkosongunit[$bar['pt']][$bar['unit']]+=$bar['jjgkosong'];
            $jjgkosongdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['jjgkosong']; 

            $tangkaipanjangunit[$bar['pt']][$bar['unit']]+=$bar['tangkaipanjang'];
            $tangkaipanjangdivisi[$bar['pt']][$bar['unit']][$bar['divisi']]+=$bar['tangkaipanjang'];    
        }

        $tdivisi=count($divisi);
        $tunit=count($unit);

        array_multisort($divisi,SORT_ASC);

        echo"<pre>";
        print_r($jenis);
        echo"</pre>";

        $stream.="<table border=0 cellpadding=1 cellspacing=1 class=sortable>";
        $stream.="<thead>";
        $stream.="<tr class=rowheader>";// style=width:300px
        $stream.="<td align=center rowspan='2' colspan='2'>Parameter</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center colspan='3'>".$_SESSION['lang']['kebun']." ".$kdunit."</td>"; 
                    foreach ($divisi as $kddivisi) {
                    if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                         $stream.="<td align=center colspan='3'>".$kddivisi."</td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowheader>";

        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>SAT</td>";
                    $stream.="<td align=center>Qty</td>";
                    $stream.="<td align=center>Nilai</td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){   
                            $stream.="<td align=center>SAT</td>";
                            $stream.="<td align=center>Qty</td>";
                            $stream.="<td align=center>Nilai</td>";
                        }
                    }
                }
            }
        }

        $stream.="</tr>";
        $stream.="<thead>";

        echo"<pre>";
        //print_r($tpokoksample);
        echo"</pre>";

        $stream.="<tbody>";
        $stream.="<tr class=rowcontent>";   
        $stream.="<td rowspan=2 style=width:150px>".$_SESSION['lang']['mutuancak']."</td>";
        $stream.="<td style=width:150px>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['tinggal']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td>".$_SESSION['lang']['jjg']."/ha</td>";


                    //$rangedari[$bar['pt']][$bar['idjenis']]['rangedari']=$bar['rangedari'];
                    // $rangesampai[$bar['pt']][$bar['idjenis']]['rangesampai']=$bar['rangesampai'];


                    // if ($jjgancakunit[$kdpt][$kdunit]/$pokoksampleunit[$kdpt][$kdunit]>$rangedari[$bar['pt']][$bar['idjenis']]['rangedari'])

                    $stream.="<td>".@number_format($jjgancakunit[$kdpt][$kdunit]/$pokoksampleunit[$kdpt][$kdunit],2)."</td>";
                    $stream.="<td>".$jenis[$_SESSION['lang']['mutuancak']][$_SESSION['lang']['buah']." ".$_SESSION['lang']['tinggal']]."</td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>".$_SESSION['lang']['jjg']."/ha</td>";
                            $stream.="<td>".@number_format($jjgancakdivisi[$kdpt][$kdunit][$kddivisi]/$pokoksampledivisi[$kdpt][$kdunit][$kddivisi],2)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";


        $stream.="<tr class=rowcontent>";   
        $stream.="<td>Brondolan ".$_SESSION['lang']['tinggal']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td>".$_SESSION['lang']['btr']."/".$_SESSION['lang']['pokok']."</td>";
                    $stream.="<td>".@number_format($btrancakunit[$kdpt][$kdunit]/$pokoksampleunit[$kdpt][$kdunit],2)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>".$_SESSION['lang']['btr']."/".$_SESSION['lang']['pokok']."</td>";
                            $stream.="<td>".@number_format($btrancakdivisi[$kdpt][$kdunit][$kddivisi]/$pokoksampledivisi[$kdpt][$kdunit][$kddivisi],2)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowcontent>";   
        $stream.="<td rowspan=2>".$_SESSION['lang']['mutu']." ".$_SESSION['lang']['transport']."</td>";
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['tinggal']." di TPH</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td>".$_SESSION['lang']['jjg']."/TPH</td>";
                    $stream.="<td>".@number_format($jjgtransportunit[$kdpt][$kdunit]/$jumlahtphunit[$kdpt][$kdunit],2)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>".$_SESSION['lang']['jjg']."/TPH</td>";
                            $stream.="<td>".@number_format($jjgtransportdivisi[$kdpt][$kdunit][$kddivisi]/$jumlahtphdivisi[$kdpt][$kdunit][$kddivisi],2)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>Brondolan ".$_SESSION['lang']['tinggal']." di TPH</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td>".$_SESSION['lang']['btr']."/ TPH</td>";
                    $stream.="<td>".@number_format($btrtransportunit[$kdpt][$kdunit]/$jumlahtphunit[$kdpt][$kdunit],2)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>".$_SESSION['lang']['btr']."/ TPH</td>";
                            $stream.="<td>".@number_format($btrtransportdivisi[$kdpt][$kdunit][$kddivisi]/$jumlahtphdivisi[$kdpt][$kdunit][$kddivisi],2)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";  

        $stream.="<tr class=rowcontent>";   
        $stream.="<td rowspan=6>".$_SESSION['lang']['mutu']." ".$_SESSION['lang']['buah']."</td>";
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['mentah']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($buahmentahunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($buahmentahdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['kurang']." ".$_SESSION['lang']['matang']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($kurangmatangunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($kurangmatangdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['matang']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($buahmatangunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($buahmatangdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";  

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['lewat']." ".$_SESSION['lang']['matang']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($lewatmatangunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($lewatmatangdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";  

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>".$_SESSION['lang']['buah']." ".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kosong']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($jjgkosongunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($jjgkosongdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";

        $stream.="<tr class=rowcontent>";   
        $stream.="<td>".$_SESSION['lang']['tangkai']." ".$_SESSION['lang']['panjang']."</td>";
        foreach ($pt as $kdpt) {
            foreach ($unit as $kdunit) {
                if($listunit[$kdpt][$kdunit]!=''){
                    $stream.="<td align=center>%</td>";
                    $stream.="<td>".@number_format(($tangkaipanjangunit[$kdpt][$kdunit]/$totaljjgunit[$kdpt][$kdunit])*100)."</td>";
                    $stream.="<td></td>";
                    foreach ($divisi as $kddivisi) {
                        if($listdivisi[$kdpt][$kdunit][$kddivisi]!=''){
                            $stream.="<td>%</td>";
                            $stream.="<td>".@number_format(($tangkaipanjangdivisi[$kdpt][$kdunit][$kddivisi]/$totaljjgdivisi[$kdpt][$kdunit][$kddivisi])*100)."</td>";
                            $stream.="<td></td>";
                        }   
                    }
                }
            }
        }
        $stream.="</tr>";
        $stream.="</tbody>
                  </table>";

        echo $stream;
        break;
    
    default:
        # code...
        break;
}


?>

