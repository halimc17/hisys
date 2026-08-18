<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kdorg = checkPostGet('kdorg', '');
$divisi = checkPostGet('divisi', '');
$per2 = checkPostGet('per2', '');
$per1 = checkPostGet('per1', '');
$noakun = checkPostGet('noakun', '');
$tt = checkPostGet('tt', '');
$status = checkPostGet('status', '');
 

if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}

if ($method == 'excel1') {
    $stream = "<table class=sortable cellspacing=1 border=1>";

} else {
    $stream = "<table class=sortable cellspacing=1>";
}

$stream.="
    <thead>
        <tr class=rowheader>
    <td align='center'  width='10' rowspan='4'>No</td>
    <td align='center'  width='30' rowspan='4'>Group Code</td>
    <td align='center'  width='29' rowspan='4'>".$_SESSION['lang']['noakun']."</td>
    <td align='center'  width='29' rowspan='4'>".$_SESSION['lang']['divisi']."</td>
    <td align='center'  width='29' rowspan='4'>".$_SESSION['lang']['periode']."</td>
    <td align='center'  width='29' rowspan='4'>Job Description</td>
    <td align='center'  width='20' rowspan='4'>Source Type</td>
    <td align='center'  width='18' rowspan='4'>Remarks</td>
    <td align='center'  width='29' rowspan='4'>UOM</td>
    <td align='center'  width='30' rowspan='4'>Rp Actual</td>
    <td align='center'  width='30' rowspan='4'>Qty Actual</td>
    <td align='center'  width='30' rowspan='4'>Unit Actual</td>
    <td align='center'  width='30' rowspan='4'>Output Actual</td>
  </tr>
        ";
$stream.="
        </tr>
    </thead>
 <tbody>";

###
#prepare data
###
	$where='';
if($divisi!=''){
	$where.= " and kodeorg like '".$divisi."%'";
}  else {
	$where.= " and kodeorg like '".$kdorg."%'";
}

$sourcetype = array('A' => 'Labour', 'B' => 'Supervision', 'C' => 'Material', 'D' => 'Transport' );

$strbarang="select * from ".$dbname.". log_5masterbarang";
$res=$owlPDO->query($strbarang) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $nmbrg[$bar['kodebarang']]=$bar['namabarang'];
    $satuan[$bar['kodebarang']]=$bar['satuan'];
}

$strkegiatan="select * from ".$dbname.". setup_kegiatan";
$res=$owlPDO->query($strkegiatan) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $nmkegiatan[$bar['kodekegiatan']]=$bar['namakegiatan'];
}


// $strvhc="select a.kodekegiatan, a.periode, a.noakun, a.kodeblok, a.kodeorg, a.kodevhc, b.detailvhc, b.kodevhc from ".$dbname.".keu_jurnaldt_vw a 
// left join vhc_5master b on b.kodevhc=a.kodevhc 
// where a.kodejurnal like '%vhc%' 
// and a.noakun like '".$noakun."%' 
// and a.kodeorg = '".$kdorg."'
// and a.periode between '".$per1."' and '".$per2."' 
// and a.kodeblok like '".$divisi."%' 
// and a.kodeblok != '' ";
// $res=$owlPDO->query($strvhc) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
// {
//      $dataR[$divisi][$bar['kodekegiatan']]['Transport'][$bar['kodevhc']]=$bar['detailvhc'];
// }

$strr="select f.detailvhc, e.statusblok, e.tahuntanam, b.hasilkerja, b.jumlahhk, a.periode, a.noakun, a.kodeblok, a.kodeorg, a.noreferensi, b.kodekegiatan, c.kodebarang, d.namabarang, c.kwantitas, satuan, c.hargasatuan, b.upahkerja
from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".kebun_prestasi b on b.notransaksi = a.noreferensi
left join ".$dbname.".kebun_pakaimaterial c on c.notransaksi = b.notransaksi
left join ".$dbname.".log_5masterbarang d on d.kodebarang = c.kodebarang
left join ".$dbname.".setup_blok e on e.kodeorg = a.kodeblok
left join ".$dbname.".vhc_5master f on f.kodevhc = a.kodevhc
where a.noakun like '".$noakun."%' 
and b.kodekegiatan != ''
and a.kodeorg = '".$kdorg."'
and a.periode between '".$per1."' and '".$per2."' 
and a.kodeblok like '".$divisi."%' 
and a.kodeblok != ''
";
// and e.tahuntanam = '".$tt."'
// and e.statusblok = '".$status."'
// exit($strr);

$res=$owlPDO->query($strr) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $divisi=substr($bar['kodeblok'], 0,6);
    $kdkegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan']; 
    $div[$divisi]=$divisi; 
    $kddivisi[$divisi][$bar['kodekegiatan']]=$divisi;
    $kdbrg[$bar['kodebarang']]=$bar['kodebarang'];
    $noref[$bar['noreferensi']]=$bar['noreferensi'];
    $data[$divisi][$bar['kodekegiatan']]['noakun']=$bar['noakun']; 
    $data[$divisi][$bar['kodekegiatan']]['kodekegiatan']=$bar['kodekegiatan']; 
    $data[$divisi][$bar['kodekegiatan']]['kodeblok']=$bar['kodeblok']; 
    $data[$divisi][$bar['kodekegiatan']]['periode']=$bar['periode']; 
    $data[$divisi][$bar['kodekegiatan']]['Labour']['uom']='HK'; 
    $data[$divisi][$bar['kodekegiatan']]['Material']['uom']=$bar['satuan'];  
    $data[$divisi][$bar['kodekegiatan']]['Labour']['qty']=$bar['hasilkerja'];
    $data[$divisi][$bar['kodekegiatan']]['Material']['qty']=$bar['kwantitas'];
    $data[$divisi][$bar['kodekegiatan']]['Material']['rpactual']=$bar['hargasatuan'];
    $dataR[$divisi][$bar['kodekegiatan']]['Labour']='Labour'; 
    $dataR[$divisi][$bar['kodekegiatan']]['Supervision']='Supervision';
    $dataR[$divisi][$bar['kodekegiatan']]['Transport']='Transport';
    $dataR2[$divisi][$bar['kodekegiatan']]['Material'][$bar['kodebarang']]=$bar['namabarang'];
    $dataR2[$divisi][$bar['kodekegiatan']]['Transport'][$bar['kodevhc']]=$bar['detailvhc'];

}

// echo "<pre>";
// print_r($datashow);
// echo "</pre>";



$no=0;
foreach ($div as $keydiv) {
    foreach ($kdkegiatan as $keykegiatan) {
        $subqty=0;
        if($kddivisi[$keydiv][$keykegiatan]!=''){
            foreach ($sourcetype as $key => $value) {
                $no+=1;
                if($value != 'Material')
                {
                    $stream.="<tr class=rowcontent>";   
                    $stream.="<td align=center  style='width:30px;'>".$no."</td>";
                    $stream.="<td align=center  style='width:30px;'>".$noakun."</td>";
                    $stream.="<td align=center  style='width:30px;'>".$data[$keydiv][$keykegiatan]['noakun']."</td>";
                    $stream.="<td align=center  style='width:30px;'>".substr($data[$keydiv][$keykegiatan]['kodeblok'], 0,6)."</td>";
                    $stream.="<td align=center  style='width:30px;'>".$data[$keydiv][$keykegiatan]['periode']."</td>";
                    $stream.="<td align=left >".$nmkegiatan[$data[$keydiv][$keykegiatan]['kodekegiatan']]."</td>";
                    $stream.="<td align=left >".$key.".".$value."</td>";
                    $stream.="<td align=left >".$dataR[$keydiv][$keykegiatan][$value]."</td>";
                    $stream.="<td align=left>".$data[$keydiv][$keykegiatan][$value]['uom']."</td>";
                    $stream.="<td align=left>".$data[$keydiv][$keykegiatan][$value]['rpactual']."</td>";
                    $stream.="<td align=right>".$data[$keydiv][$keykegiatan][$value]['qty']."</td>";
                    $stream.="<td align=left></td>";
                    $stream.="<td align=left></td>";
                }
                else
                {   
                    foreach ($dataR2[$keydiv][$keykegiatan][$value] as $keyx => $valx) {
                        $stream.="<tr class=rowcontent>";   
                        $stream.="<td align=center  style='width:30px;'>".$no."</td>";
                        $stream.="<td align=center  style='width:30px;'>".$noakun."</td>";
                        $stream.="<td align=center  style='width:30px;'>".$data[$keydiv][$keykegiatan]['noakun']."</td>";
                        $stream.="<td align=center  style='width:30px;'>".substr($data[$keydiv][$keykegiatan]['kodeblok'], 0,6)."</td>";
                        $stream.="<td align=center  style='width:30px;'>".$data[$keydiv][$keykegiatan]['periode']."</td>";
                        $stream.="<td align=left >".$nmkegiatan[$data[$keydiv][$keykegiatan]['kodekegiatan']]."</td>";
                        $stream.="<td align=left >".$key.".".$value."</td>";
                        $stream.="<td align=left >".$valx."</td>";
                        $stream.="<td align=left >".$data[$keydiv][$keykegiatan][$value]['uom']."</td>";
                        $stream.="<td align=left>".$data[$keydiv][$keykegiatan][$value]['rpactual']."</td>";
                        $stream.="<td align=right>".$data[$keydiv][$keykegiatan][$value]['qty']."</td>";
                        $stream.="<td align=left></td>";
                        $stream.="<td align=left></td>";
                        $stream.="</tr>";
                    }
                }
                $stream.="</tr>";
                $subqty+=$data[$keydiv][$keykegiatan][$value]['qty'];
            }
                $stream.="<tr class=rowcontent>"; 
                $stream.="<td align=center colspan=6>Sub Total</td>";
                $stream.="<td></td>";
                $stream.="<td></td>";
                $stream.="<td></td>";
                $stream.="<td></td>";
                $stream.="<td align=right>".$subqty."</td>";
                $stream.="<td></td>";
                $stream.="<td></td>";
                $stream.="</tr>";
        }
    }
}

$stream.="
 </tbody>
     </table>";

switch ($method) {
######PREVIEW
    case 'html1':
        echo $stream;
        break;

######EXCEL	
    case 'excel1':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_LV1_" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
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
        break;
}
?>