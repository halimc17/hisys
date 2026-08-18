<?php
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/biReport.php');
include_once('lib/zPdfMaster.php');
$optSat=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$level = $_GET['level'];
if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} else {
    $mode = 'preview';
}
if($mode=='pdf') {
    $param = $_GET;
    unset($param['mode']);
    unset($param['level']);
} else {
    $param = $_POST;
}

// echo"<pre>";
// print_r($param);        
// echo"</pre>";

# Tanggal Margin
$currTahun = $tahun = $param['periode_tahun'];
$currBulan = $bulan = $param['periode_bulan'];
$bulan++;
if($bulan>12) {
    $bulan = 1;
    $tahun++;
}
if($bulan<10) {
    $bulan = '0'.$bulan;
}
$tanggalM = $tahun."-".$bulan."-01";

# Current Periode
if($currBulan<10) {
    $currBulan = '0'.$currBulan;
}
$currPeriod = $currTahun.$currBulan;

switch($level) {
    case '0':
        # Data
        # Afdeling dan Blok
        $afd = substr($param['kodeorg'],0,6);
        $kodeorg = substr($param['kodeorg'],0,4);
        //$optBelow = getOrgBelow($dbname,$kodeorg);

        # Mutasi Blok
        if($_SESSION['language']=='EN'){
            $zz='namakegiatan1 as namakegiatan';
        }else{
            $zz='namakegiatan';
        }
        $wbarang='';
        if($_POST['barang']!=''){
            $wbarang=" and kodebarang='".$_POST['barang']."' ";
        }
        $wkegiatan='';
        if($_POST['kegiatan']!=''){
            $wkegiatan=" and per.kodekegiatan='".$_POST['kegiatan']."' ";
        }
        $cols = "mat.notransaksi,akt.tanggal,keg.".$zz.",substr(mat.kodeorg,1,6)as divisi,mat.kodeorg,mat.kodebarang,mat.kwantitas,mat.hargasatuan,per.hasilkerja,keg.kodekegiatan";

        $where = "left(mat.notransaksi,6)='".$currPeriod."' and ".
            "left(mat.kodeorg,4)='".$kodeorg."' ". $wbarang." ". $wkegiatan;
        
        $query = "select ".$cols." from `".$dbname."`.`kebun_pakaimaterial` as mat 
                        left join `".$dbname."`.`kebun_aktifitas` as akt on akt.notransaksi=mat.notransaksi 
                        left join `".$dbname."`.`kebun_perawatan_vw` as per on per.notransaksi=mat.notransaksi and per.kodekegiatan=mat.kodekegiatan 
                        left join `".$dbname."`.`setup_kegiatan` as keg on keg.kodekegiatan=per.kodekegiatan 
                        where ".$where." group by notransaksi,tanggal,kodekegiatan,kodeorg,kodebarang order by substr(mat.kodeorg, 1,6),kodebarang,tanggal asc";    
        $tmpRes = fetchData($query);
        if(empty($tmpRes)) {
            echo 'Warning : Data tidak ditemukan: '.$currPeriod.' '.$_POST['kegiatan'].' '.$_POST['barang'].' ';
            exit;
        }

        // echo $query; exit;
        
        # Options
        $whereBrg = "";
        foreach($tmpRes as $key=>$row) {
            if($key==0) {
                $whereBrg .= "kodebarang='".$row['kodebarang']."'";
            } else {
                $whereBrg .= "or kodebarang='".$row['kodebarang']."'";
            }
        }
        $optBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',
            $whereBrg);

        $strJ="select induk from ".$dbname.".organisasi where kodeorganisasi = '".$kodeorg."'";
        $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
        $resJ->setFetchMode(PDO::FETCH_ASSOC);
        while($barJ=$resJ->fetch()){
            $induk=$barJ["induk"];
        }
        // $resJ=mysql_query($strJ,$conn);
        // while($barJ=mysql_fetch_object($resJ))
        // {
                
        // }


        # Set Data
        $data = $tmpRes;
        $dataShow = $dataExcel = $data;
		$tharga=0;
        $tluas=0;
        $tmaterial=0;
        $subtot= array();
        foreach($data as $key=>$row) {
            $dataShow[$key]['kodeorg'] = $row['kodeorg'];
            $dataShow[$key]['divisi'] = $row['divisi'];
            $dataShow[$key]['tanggal'] = tanggalnormal($row['tanggal']);
            $dataShow[$key]['kodebarang'] = $optBrg[$row['kodebarang']];
            $dataShow[$key]['namakegiatan'] = $row['namakegiatan'];
            $dataShow[$key]['kwantitas'] = number_format($row['kwantitas'],2);
            $dataShow[$key]['hargasatuan'] =number_format($row['hargasatuan'],2);//number_format($optHrg[$row['kodebarang']],2);
            $dataShow[$key]['kodekegiatan'] = $optSat[$row['kodekegiatan']];
            $dataExcel[$key]['kodeorg'] = $row['kodeorg'];
            $dataExcel[$key]['tanggal'] = $row['tanggal'];
            $dataExcel[$key]['kodebarang'] = $optBrg[$row['kodebarang']];
            $dataExcel[$key]['kwantitas'] = number_format($row['kwantitas'],2);
            $dataExcel[$key]['hargasatuan'] = number_format($row['hargasatuan'],2);
            $dataExcel[$key]['kodekegiatan'] = $optSat[$row['kodekegiatan']];
            @$subtot['material'][substr($row['kodeorg'], 0,6)][$row['kodebarang']]+=$row['kwantitas'];
            @$subtot['harga'][substr($row['kodeorg'], 0,6)][$row['kodebarang']]=$row['hargasatuan'];
            // @$subtot['harga'][substr($row['kodeorg'], 0,6)][$row['kodebarang']]+=$row['hargasatuan']*$row['kwantitas'];
            @$subtot['hasilkerja'][substr($row['kodeorg'], 0,6)][$row['kodebarang']]+=$row['hasilkerja'];
            $tmaterial+=$row['kwantitas'];
            $tharga+=$row['hargasatuan']*$row['kwantitas'];
            $tluas+=$row['hasilkerja'];
        }

        # Report Gen
        $theCols = array(
            $_SESSION['lang']['notransaksi'],
            $_SESSION['lang']['tanggal'],
            $_SESSION['lang']['namakegiatan'],
            $_SESSION['lang']['divisi'],
            $_SESSION['lang']['blok'],
            $_SESSION['lang']['kodebarang'],
            $_SESSION['lang']['kwantitas'],
            $_SESSION['lang']['hargasatuan'],
            substr($_SESSION['lang']['hasilkerja'],0,12),
            $_SESSION['lang']['satuan'],
        );
        $align = explode(",","L,L,L,L,L,L,R,R,R,L");
        break;
    default:
    break;
}

switch($mode) {
    case 'pdf':
        /** Report Prep **/
        $colPdf = array('notransaksi','tanggal','kodekegiatan','kodeorg','kodebarang','kwantitas',
            'hargasatuan','hasilkerja','satuan');
        $title = $_SESSION['lang']['lapmaterial'];
        $length = explode(",","15,7,20,10,10,10,10,10,10");

        $pdf = new zPdfMaster('L','pt','A4');
        $pdf->setAttr1($title,$align,$length,$colPdf);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();

        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);

        # Content
        $pdf->SetFont('Arial','',9);
        foreach($dataShow as $key=>$row) {
            $i=0;
            foreach($row as $head=>$cont) {
                $pdf->Cell($length[$i]/100*$width,$height,$cont,1,0,$align[$i],1);
                $i++;
            }
            $pdf->Ln();
        }

        $pdf->Output();
        break;
    default:
        # Redefine Align
        $alignPrev = array();
        foreach($align as $key=>$row) {
            switch($row) {
                case 'L':
                    $alignPrev[$key] = 'left';
                    break;
                case 'R':
                    $alignPrev[$key] = 'right';
                    break;
                case 'C':
                    $alignPrev[$key] = 'center';
                    break;
                default:
            }
        }

        /** Mode Header **/
        if($mode=='excel') {
            $tab = "<table border='1' cellspacing=0 class=sortable>";
            $tab .= "<thead style=\"background-color:#222222\"><tr class='rowheader'>";
        } else {
            $tab = "<table id='kasharian' class='sortable'>";
            $tab .= "<thead><tr class='rowheader'>";
        }

        /** Generate Table **/
        foreach($theCols as $head) {
            $tab .= "<th>".$head."</th>";
        }
        $tab .= "</tr></thead>";
        $tab .= "<tbody>";

        # Content
        $orgkod='';
        $brgkod='';
        $jang=0;
        foreach($data as $key=>$row) {
            $i=0;

            if(($orgorg!=substr($row['kodeorg'], 0,6)) or ($barbar!=$row['kodebarang'])){
                $tab .= "<tr class=rowcontent><td colspan=6><b>Subtotal Divisi ".substr($row['kodeorg'], 0,6)." Material ".$optBrg[$row['kodebarang']]." </b></td><td align=right><b>".number_format($subtot['material'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td align=right><b>".number_format($subtot['harga'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td align=right><b>".number_format($subtot['hasilkerja'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td>&nbsp;</td></tr>";
                // $tab .= "<tr class='rowcontent'>";
                $orgorg=substr($row['kodeorg'], 0,6);
                $barbar=$row['kodebarang'];
            }

            $tab .= "<tr class='rowcontent'>";
            foreach($row as $head=>$cont) {
                if($mode=='excel') {
                    $tab .= "<td align='".$alignPrev[$i]."'>".$dataExcel[$key][$head]."</td>";
                } else {
                    $tab .= "<td align='".$alignPrev[$i]."'>".$dataShow[$key][$head]."</td>";
                }
                $i++;
            }
            $tab .= "</tr>";


            // $i=0;
            // if($orgkod=='')
            // {
            //     $orgkod=substr($row['kodeorg'], 0,6);
            // }
            // if($brgkod=='')
            // {
            //     $brgkod=$row['kodebarang'];
            // }

            // if ($orgkod!=substr($row['kodeorg'], 0,6) or $brgkod!=$row['kodebarang']) {
            //     $orgkod=substr($row['kodeorg'], 0,6);
            //     $brgkod=$row['kodebarang'];
            //     $jang=1;
            // }
            // else
            // {
            //     $tab .= "<tr class='rowcontent'>";
            // }

            // if($jang==1)
            // {

            //     $tab .= "<tr class=rowcontent><td colspan=6><b>Subotal Divisi ".substr($row['kodeorg'], 0,6)." Material ".$optBrg[$row['kodebarang']]." </b></td><td align=right><b>".number_format($subtot['material'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td align=right><b>".number_format($subtot['harga'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td align=right><b>".number_format($subtot['hasilkerja'][substr($row['kodeorg'], 0,6)][$row['kodebarang']],2)."</b></td><td>&nbsp;</td></tr>";
            //     $tab .= "<tr class='rowcontent'>";
            //     $jang=0;
            // }

            
            // foreach($row as $head=>$cont) {
            //     if($mode=='excel') {
            //         $tab .= "<td align='".$alignPrev[$i]."'>".$dataExcel[$key][$head]."</td>";
            //     } else {
            //         $tab .= "<td align='".$alignPrev[$i]."'>".$dataShow[$key][$head]."</td>";
            //     }
            //     $i++;
            // }
            // $tab .= "</tr>";
        }
         // $tab .= "<tr class=rowcontent><td colspan=6><b>Subotal Divisi ".substr($row['kodeorg'], 0,6)." Material ".$optBrg[$row['kodebarang']]."</b></td><td align=right><b>".number_format($subtot['material'][$orgkod][$brgkod],2)."</b></td><td align=right><b>".number_format($subtot['harga'][$orgkod][$brgkod],2)."</b></td><td align=right><b>".number_format($subtot['hasilkerja'][$orgkod][$brgkod],2)."</b></td><td>&nbsp;</td></tr>";

        $tab .= "</tbody>";
        // $tab .= "<tfoot><tr class=rowcontent><td colspan=6><b>Total</b></td><td align=right><b>".number_format($tmaterial,2)."</b></td><td align=right><b>".number_format($tharga)."</b></td><td align=right><b>".number_format($tluas,2)."</b></td><td>&nbsp;</td></tr></tfoot>";
        $tab .= "<tfoot><tr class=rowcontent><td colspan=6><b>Total</b></td><td align=right><b>".number_format($tmaterial,2)."</b></td><td align=right></td><td align=right><b>".number_format($tluas,2)."</b></td><td>&nbsp;</td></tr></tfoot>";
        
        $tab .= "</table>";

        /** Output Type **/
        if($mode=='excel') {
            $stream = $tab;
            $nop_="PakaiMaterial";
            if(strlen($stream)>0) {
                # Delete if exist
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            @unlink('tempExcel/'.$file);
                        }
                    }	
                    closedir($handle);
                }

                # Write to File
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
                    echo "Error : can not write file";
                    exit;
                } else {
                    echo $nop_;
                }
                fclose($handle);
            }
        } else {
            echo $tab;
        }
        break;
}
?>