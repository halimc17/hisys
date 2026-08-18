<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
include_once('lib/utilities.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_pie.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_pie3d.php');
require_once ('jpgraph/jpgraph_line.php');
error_reporting(0);
use Dompdf\Dompdf;

$method 	= checkPostGet('method','');
$tipeprint 	= checkPostGet('tipeprint','');

$pt 	= checkPostGet('pt','');
$tahun 	= checkPostGet('tahun','');

//Umar
$induk 	= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', 'kodeorganisasi="'.$_SESSION['empl']['lokasitugas'].'"');
//End Umar

switch ($method){
	case'prveiewcanvas':
		$tanggal=date('Y-m-d');
		$periode=date('Y-m');
		$expperiode=explode('-',$periode);
		
		$arrlist=array('0'=>'TBS','1'=>'Potongan');
		$arrlist2=array('0'=>'Kg');
		$arrlist5=array('0'=>'FFB Production');
		$arrlist3=array('0'=>'Supplier','1'=>'Potongan');
		$arrlist4=array('0'=>'TBS OLAH','1'=>'OER','2'=>'KER');
		$arrlist6=array('0'=>'TBS OLAH','1'=>'Utility');
		$arrlist7=array('0'=>'Kernel Losses','1'=>'Oil Losses');
		$arrhari=array();
		$jlhhari = cal_days_in_month(CAL_GREGORIAN, $expperiode[1], $expperiode[0]);
		for ($i=1; $i < $jlhhari+1; $i++){
			$arrhari[$i]=$i;
		}
		
		## GET WB
		$arrsupplier=array();
		$arrval=array();
		$arrval2=array();
		// $str="select supplier,netto,potongan,waktukeluar from ".$dbname.".wb where kodebarang='40000003' and waktukeluar like '".$periode."%' AND millcode IN (SELECT DISTINCT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$induk[$_SESSION['empl']['lokasitugas']]."')";
		$str="select supplier,netto,potongan,waktukeluar from ".$dbname.".wb where kodebarang='40000003' and waktukeluar like '".$periode."%'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrsupplier[getInisialupplier($val['supplier'])]=getInisialupplier($val['supplier']);
			$arrval[0][getInisialupplier($val['supplier'])]+=($val['netto']+$val['potongan']);
			$arrval[1][getInisialupplier($val['supplier'])]+=$val['potongan'];
			$arrval2[0][intval(substr($val['waktukeluar'],8,2))]+=($val['netto']+$val['potongan']);
			$arrval2[1][intval(substr($val['waktukeluar'],8,2))]+=$val['potongan'];
		}

		$arrval9=array();
		$str="select supplier,netto,potongan,waktukeluar from ".$dbname.".wb where kodebarang='40000003' and waktukeluar LIKE '".$tanggal."%'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrsupplier[getInisialupplier($val['supplier'])]=getInisialupplier($val['supplier']);
			$arrval9[0][getInisialupplier($val['supplier'])]+=($val['netto']+$val['potongan']);
			$arrval9[1][getInisialupplier($val['supplier'])]+=$val['potongan'];
		}
		
		## GET PRODUKSI
		$arrval3=array();
		$arrval4=array();
		$arrval5=array();
		$str="select tanggal,tbsdiolah,tbsdiolahnetto,oer,oerpk from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%'";
		// echo $str;
		// exit('ERROR');
		$res=fetchdata($str);
		foreach($res as $val){
			$oer=0;
			$oerpk=0;
			$utility=0;
			if($val['tbsdiolah']>0){
				$oer=(($val['oer']/$val['tbsdiolah'])*100);
				$oerpk=(($val['oerpk']/$val['tbsdiolah'])*100);
				$utility=(($val['tbsdiolah']/(20*60000))*100);
			}
			$arrval3[0][intval(substr($val['tanggal'],8,2))]+=$val['tbsdiolah'];
			$arrval3[1][intval(substr($val['tanggal'],8,2))]+=$val['tbsdiolahnetto'];
			$arrval4[0][intval(substr($val['tanggal'],8,2))]+=$val['tbsdiolah'];
			$arrval4[1][intval(substr($val['tanggal'],8,2))]+=number_format($oer,2);
			$arrval4[2][intval(substr($val['tanggal'],8,2))]+=number_format($oerpk,2);
			$arrval5[0][intval(substr($val['tanggal'],8,2))]+=$val['oerpk'];
			$arrval6[0][intval(substr($val['tanggal'],8,2))]+=$val['oer'];
			$arrval7[0][intval(substr($val['tanggal'],8,2))]+=$val['tbsdiolah'];
			$arrval7[1][intval(substr($val['tanggal'],8,2))]+=number_format($utility,2);
			//echo (($val['oerpk']/$val['tbsdiolah'])*100);
		}


		$str="select * from ".$dbname.".pabrik_mr_roa  where tanggal like '".$periode."%'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['nilai']==''){
				$val['nilai']=0;
			}
			if(substr($val['parameter'], 0,1)=='A'){
				$arrval8[1][intval(substr($val['tanggal'],8,2))]+=number_format($val['nilai'],2);
			}
			if(substr($val['parameter'], 0,1)=='B'){
				$arrval8[0][intval(substr($val['tanggal'],8,2))]+=number_format($val['nilai'],2);
			}
		}

		// echo "<pre>";
		// print_r($arrval4);
		// echo "</pre>";
		// exit('ERROR');
		echo json_encode($arrsupplier)."####".json_encode($arrlist)."####".json_encode($arrval)."####".json_encode($arrhari)."####".json_encode($arrval2)."####".json_encode($arrval3)."####".json_encode($arrval4)."####".json_encode($arrval5)."####".json_encode($arrlist2)."####".json_encode($arrlist3)."####".json_encode($arrval9)."####".json_encode($arrlist4)."####".json_encode($arrlist5)."####".json_encode($arrval6)."####".json_encode($arrval7)."####".json_encode($arrlist6)."####".json_encode($arrval8)."####".json_encode($arrlist7)."####".json_encode($arrval9);
	break;

    case 'loadtableatt':
        $table1  = "";

        $stream  .= "<table class='sortable' cellspacing='1' cellpadding='3' border='0' style='width:100%'>";
            $stream .= "<thead>";
                $stream .= "<tr class='rowheader'>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Serial Number</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Scan Date</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>PIN</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Verify Mode</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>In Out Mode</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Reserved</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Work Code</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Att ID</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Flag</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Upload Time</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Latitude</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Longtitude</th>";
                $stream .= "</tr>";
            $stream .= "</thead>";
            $stream .= "<tbody>";
                $query  = "SELECT * FROM $dbname.att_log ORDER BY scan_date DESC LIMIT 10";
                $result = fetchData($query, 'OBJECT');
                if (count($result) < 1) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle' colspan='12'>Data Kosong</td>";
                    $stream .= "</tr>";
                }

                foreach ($result as $key => $value) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->sn."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->scan_date."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->pin."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->verifymode."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->inoutmode."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->reserved."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->work_code."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->att_id."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->flag."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->waktu_upload."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->latitude."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->longtitude."</td>";
                    $stream .= "</tr>";
                }
            $stream .= "</tbody>";
        $stream  .= "</table>";

        $table1 .= $stream;

        echo $table1;
    break;

    case 'loadtablecrh':
        $table2 = "";

        $stream  .= "<table class='sortable' cellspacing='1' cellpadding='3' border='0' style='width:100%'>";
            $stream .= "<thead>";
                $stream .= "<tr class='rowheader'>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['kodeorg']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['tanggal']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Pagi</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Siang</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Sore</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Malam</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>Catatan</th>";
                $stream .= "</tr>";
            $stream .= "</thead>";
            $stream .= "<tbody>";
                $query  = "SELECT * FROM $dbname.kebun_curahhujan ORDER BY tanggal DESC limit 10";
                $result = fetchData($query, 'OBJECT');
                if (count($result) < 1) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle' colspan='7'>Data Kosong</td>";
                    $stream .= "</tr>";
                }

                foreach ($result as $key => $value) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$utilities['organization']['Name'][$value->kodeorg]."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".tanggalnormal($value->tanggal)."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->pagi."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->siang."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->sore."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->malam."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->catatan."</td>";
                    $stream .= "</tr>";
                }
            $stream .= "</tbody>";
        $stream  .= "</table>";

        $table2 .= $stream;

        echo $table2;
    break;

    case 'loadtablesounding':
        $table2 = "";

        $stream  .= "<table class='sortable' cellspacing='1' cellpadding='3' border='0' style='width:100%'>";
            $stream .= "<thead>";
                $stream .= "<tr class='rowheader'>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['kodeorg']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['kodetangki']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['tanggal']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['suhu']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['tinggi']."</th>";
                    $stream .= "<th style='text-align:center;vertical-align:middle'>".$_SESSION['lang']['volume']."</th>";
                $stream .= "</tr>";
            $stream .= "</thead>";
            $stream .= "<tbody>";
                $query  = "SELECT * FROM $dbname.perhitungan_tangki WHERE kodeorg = '".$_SESSION['empl']['lokasitugas']."' and waktusounding like '".date('Y-m')."%' ORDER BY waktusounding DESC LIMIT 10";
                $result = fetchData($query, 'OBJECT');
                if (count($result) < 1) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle' colspan='7'>Data Kosong</td>";
                    $stream .= "</tr>";
                }

                foreach ($result as $key => $value) {
                    $stream .= "<tr class='rowcontent'>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$utilities['organization']['Name'][$value->kodeorg]."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->kodetangki."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->waktusounding."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->suhu."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".$value->tinggicpo."</td>";
                        $stream .= "<td style='text-align:center;vertical-align:middle'>".number_format($value->volume)."</td>";
                    $stream .= "</tr>";
                }
            $stream .= "</tbody>";
        $stream  .= "</table>";

        $table2 .= $stream;

        echo $table2;
    break;

    case 'previewcanvas1':
        $data   = array();
        $query  = "SELECT * FROM $dbname.perhitungan_tangki WHERE kodeorg = '".$_SESSION['empl']['lokasitugas']."' and waktusounding like '".date('Y-m')."%' AND kodetangki = 'ST01' ORDER BY waktusounding DESC LIMIT 10";
        $result = fetchData($query, 'OBJECT');
        $no     = 0;
        foreach ($result as $key => $value) {
            $data['waktu'][$no]  = substr($value->waktusounding, 11,8);
            $data['volume'][$no] = $value->volume;

            $no++;
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit('Error'.$query);

        echo json_encode($data);
    break;

    default:
    break;
}


function getInisialupplier($supplierid,$kolom='namapenanggungjawab'){
	global $dbname;
    global $owlPDO;
    
	$suppliername='';
    $str="select ".$kolom." from ".$dbname.".log_5supplier where supplierid='".$supplierid."'";
	$res=fetchdata($str);
	$suppliername=$res[0][$kolom];
	
	return $suppliername;    
}
?>
