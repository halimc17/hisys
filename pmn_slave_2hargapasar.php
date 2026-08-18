<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
require_once('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');

//$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";

$proses = checkPostGet('proses', '');
$psrId = checkPostGet('psrId', '');
$periodePsr = checkPostGet('periodePsr', '');
$komoditi = checkPostGet('komoditi', '');
$idPasar = checkPostGet('idPasar', '');
$idMatauang = checkPostGet('idMatauang', '');
$hrgPasar = checkPostGet('hrgPasar', '');
$tglHarga = tanggalsystem(checkPostGet('tglHarga', ''));
$tanggalakhir1 = tanggalsystem(checkPostGet('tanggalakhir1', ''));
$tanggalmulai1 = tanggalsystem(checkPostGet('tanggalmulai1', ''));


$whr = "kelompokbarang='400'";
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', $whr);

if ($psrId != '') {
    $where.=" and pasar='" . $psrId . "'";
} else {
    exit("Warning : Pasar Tidak Boleh Kosong");
}
// if ($periodePsr != '') {
//     $where.=" and tanggal like '" . $periodePsr . "%'";
// } else {
//     exit("Warning : Periode Tidak Boleh Kosong");
// }

if ($komoditi != '') {
    $where.=" and kodeproduk = '" . $komoditi . "'";
} else {
    exit("Warning : Komoditi Tidak Boleh Kosong");
}

if (($tanggalakhir1 - $tanggalmulai1) < 0) {
    echo " Gagal: Periksa kembali periode tanggal, Tanggal akhir lebih kecil dari tanggal mulai.";
} else {
switch ($proses) {

    case'preview':

     if($tanggalmulai1==''){
            exit("Warning : Periode tanggal mulai harus diisi.");
        }
        if($tanggalakhir1==''){
            exit("Warning : Periode tanggal akhir harus diisi.");
        }

        echo"
    <table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>" . $_SESSION['lang']['tanggal'] . "</td>
	   <td>" . $_SESSION['lang']['komoditi'] . "</td>
	   <td>" . $_SESSION['lang']['satuan'] . "</td>
	   <td>" . $_SESSION['lang']['pasar'] . "</td>
	   <td>" . $_SESSION['lang']['matauang'] . "</td>
           <td>" . $_SESSION['lang']['harga'] . "</td></tr></thead><tbody>";

           $tgl1=explode("-",$_POST['tgl_dr']);
            $tangglAwl=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
            $tgl2=explode("-",$_POST['tgl_samp']);
            $tangglSmp=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
            
        $str = "select * from " . $dbname . ".pmn_hargapasar where (tanggal between '" . $tanggalmulai1 . "' and '" . $tanggalakhir1 . "') ".$where." order by `tanggal` asc";
        // exit('error: '.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $barisData = owlBaris($res);

        if ($barisData > 0) {
            //while($bar=mysql_fetch_object($res))
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {

                $no+=1;

                echo"<tr class=rowcontent id='tr_" . $no . "'>
                        <td>" . $no . "</td>

                        <td>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>" . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td>" . $bar->satuan . "</td>
                        <td>" . $bar->pasar . "</td>
                        <td>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        </tr>";
            }
        } else {
            echo"<tr class=rowcontent><td colspan=8>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo"</tbody></table>";
        break;
    case'jpgraph':
        $bln = array();
        $num = date('t', mktime(0, 0, 0, substr($periodePsr, 5, 2), 2, substr($periodePsr, 0, 4)));
        $labels = array();
        for ($x = 1; $x <= $num; $x++) {
            array_push($labels, $x);
            if ($x < 10)
                $y = '0' . $x;
            else
                $y = $x;
            array_push($bln, $y);
        }
        $datay1 = array();
        $datay2 = array();
        for ($x = 0; $x < count($bln); $x++) {
            $str = "select
                                      harga,kodeproduk,matauang
                                      from " . $dbname . ".pmn_hargapasar 
                                      where pasar='" . $psrId . "' and tanggal = '" . $periodePsr . "-" . $bln[$x] . "' and kodeproduk = '" . $komoditi . "'";
            $datay1[$x] = 0; 
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
            
                if ($bar->harga != 0) {

                    $datay1[$x] = $bar->harga;

                }
            }
        }
        //===========

        $graph = new Graph(750, 450);
        $graph->img->SetMargin(40, 40, 40, 80);
        $graph->img->SetAntiAliasing();
        $graph->SetScale("textlin");
        $graph->SetShadow();
        $graph->title->Set(strtoupper($psrId) . "  " . $periodePsr);
        $graph->title->SetFont(FF_DEFAULT, FS_NORMAL, 14);

        $graph->xaxis->SetFont(FF_DEFAULT, FS_NORMAL, 11);
        $graph->xaxis->SetTickLabels($labels);
        $graph->xaxis->SetLabelAngle(45);

        $p1 = new LinePlot($datay1);

        $p1->SetLegend($optNmBarang[$komoditi]);
        $graph->legend->Pos(0.02, 0.03);

        $p1->mark->SetType(MARK_SQUARE);
        // $p1->SetImpuls();
        $p1->mark->SetFillColor("red");
        $p1->mark->SetWidth(4);
        $p1->SetColor("blue");
        $p1->SetCenter();
        $graph->Add(array($p1));
        $graph->Stroke();
        break;

    case'excel':
        $bgcoloraja = "bgcolor=#DEDEDE align=center";
        $tab.="
    <table class=sortable cellspacing=1 border=1>
     <thead>
	  <tr class=rowheader>
	   <td " . $bgcoloraja . ">No</td>
	   <td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>
	   <td " . $bgcoloraja . ">" . $_SESSION['lang']['komoditi'] . "</td>
	   <td " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>
	   <td " . $bgcoloraja . ">" . $_SESSION['lang']['pasar'] . "</td>
	   <td " . $bgcoloraja . ">" . $_SESSION['lang']['matauang'] . "</td>
           <td " . $bgcoloraja . ">" . $_SESSION['lang']['harga'] . "</td></tr></thead><tbody>";
        $str = "select * from " . $dbname . ".pmn_hargapasar where (tanggal between '" . $tanggalmulai1 . "' and '" . $tanggalakhir1 . "') ".$where." order by `tanggal` asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $barisData=owlBaris($res);

        
   
            if ($barisData > 0) {
               // while ($bar = mysql_fetch_object($res))
                $res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
                                {

                    $no+=1;


                    $tab.="<tr class=rowcontent>
                        <td>" . $no . "</td>
                        <td>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>" . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td>" . $bar->satuan . "</td>
                        <td>" . $bar->pasar . "</td>
                        <td>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        </tr>";
                }
            } else {
                $tab.="<tr class=rowcontent><td colspan=8>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
            }
        $tab.="</tbody></table>";
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "hargaPasar_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls.gz';
                </script>";
        break;
    default:
        break;
    }
}
?>