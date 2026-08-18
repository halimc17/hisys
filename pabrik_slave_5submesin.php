<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$msnId = checkPostGet('msnId', '');
$divId = checkPostGet('divId', '');
$sbMesinCode = checkPostGet('sbMesinCode', '');
$sbMesinNama = checkPostGet('sbMesinNama', '');
$sbMesinCd2 = checkPostGet('sbMesinCd2', '');
$stat = checkPostGet('stat', '');
$kdSbMsn=$sbMesinCode.$sbMesinCd2;
$optDtstat=array("0"=>$_SESSION['lang']['aktif'],"1"=>$_SESSION['lang']['nonaktif']);
switch ($proses) {
    case'getMesin':
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='STENGINE' and kodeorganisasi like '".$divId."%' order by kodeorganisasi asc";
        $optOrg="<option value=''></option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){
            if($msnId==$bar->kodeorganisasi){
                $optOrg.="<option value='".$bar->kodeorganisasi."' selected>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
            }else{
                $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";    
            }
            
        }
        echo $optOrg;
    break;
    case 'insert':
        if(strlen($kdSbMsn)<13){
            $strCount = "select right(subkodemesin,3) as nourut from " . $dbname . ".pabrik_5submesin where kodemesin='".$msnId."' order by subkodemesin desc limit 1";
            $rData=fetchData($strCount);
            if(intval($rData[0]['nourut'])==0){
                $sbMesinCd2=addZero(1,3);
            }else{
                $sbMesinCd2=addZero((intval($rData[0]['nourut'])+1),3);
            }
            $kdSbMsn=$kdSbMsn.$sbMesinCd2;
        }
        if ($divId == '' || $msnId == '' || $sbMesinCode == '' || $sbMesinNama == ''||$sbMesinCd2=='') {
            echo 'Gagal : Semua field harus diisi.';
        } else {
            $strCount = "select * from " . $dbname . ".pabrik_5submesin where kodemesin='".$msnId."' and subkodemesin='".$kdSbMsn."'";
            $qryCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
            $numRows = owlBaris($qryCount);
            if ($numRows >= 1) {
                echo "Gagal : Item ini sudah ada didatabase.";
            } else {
                $str = "insert into " . $dbname . ".pabrik_5submesin (kodemesin,subkodemesin,namasubmesin,updateby,status) values ('".$msnId."','".$kdSbMsn."','".$sbMesinNama."','".$_SESSION['standard']['userid']."','".$stat."')";
                try
                {
                    $owlPDO->exec($str);
                    loadData();
                }
                catch (PDOException $e)
                {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        }
        break;

    case 'update':
        $str = "update ".$dbname.".pabrik_5submesin set namasubmesin='".$sbMesinNama."', updateby='" . $_SESSION['standard']['userid'] . "',status='".$stat."' where kodemesin='".$msnId."' and subkodemesin='".$kdSbMsn."'";
        try {
            $owlPDO->exec($str);
            loadData();
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loadData':
        loadData();
    break;

    case 'delete':
        $str = "delete from " . $dbname . ".pabrik_5submesin where  kodemesin='".$msnId."' and subkodemesin='".$sbMesinCode."'";
        try {
            $owlPDO->exec($str);
            loadData();
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'pdf':
        $no=0;
        class masterpdf extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $owlPDO;
                global $optDtstat;

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, $height, $_SESSION['org']['namaorganisasi'], '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);

                $this->Cell($width, $height, strtoupper($_SESSION['lang']['daftar']." ".$_SESSION['lang']['submesin']), '', 1, 'C');
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(415, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(5, $height, ':', '', 0, 'L');
                $this->Cell(40, $height, date('d-m-Y H:i'), '', 1, 'L');
                $this->Cell(415, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(8, $height, ':', '', 0, 'L');
                $this->Cell(15, $height, $this->PageNo(), '', 1, 'L');

                $this->Cell(100, $height, '', '', 0, 'L');
                $this->Cell(315, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['user'], '', 0, 'L');
                $this->Cell(8, $height, ':', '', 0, 'L');
                $this->Cell(20, $height, $_SESSION['standard']['username'], '', 1, 'L');
                $this->Ln();

                $this->Cell(20, 1.5 * $height, $_SESSION['lang']['nourut'], 'TBLR', 0, 'C');
                $this->Cell(120, 1.5 * $height, $_SESSION['lang']['station'], 'TBR', 0, 'C');
                $this->Cell(150, 1.5 * $height, $_SESSION['lang']['mesin'], 'TBR', 0, 'C');
                $this->Cell(70, 1.5 * $height, $_SESSION['lang']['kode']." ".$_SESSION['lang']['submesin'], 'TBR', 0, 'C');
                $this->Cell(160, 1.5 * $height, $_SESSION['lang']['nama']." ".$_SESSION['lang']['submesin'], 'TBR', 0, 'C');
                $this->Cell(30, 1.5 * $height, $_SESSION['lang']['status'], 'TBR', 1, 'C');
            }

        }

        #====================== Prepare PDF Setting
        $pdf = new masterpdf('P', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 14;
        $pdf->SetFont('Arial', '', 8);
        $pdf->AddPage();
        
        # Generate Data
        $kodeorg = $_SESSION['empl']['lokasitugas'];
        $str = "select * from ".$dbname.".pabrik_5submesin where kodemesin like  '".$kodeorg."%'";
        $result = fetchData($str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $numRows = owlBaris($res);
        if ($numRows == 0) {
            $pdf->Cell(380, $height, $_SESSION['lang']['errdatanotexist'], 'BRL', 0, 'C');
        } else {
            foreach ($result as $data) {
                $whrst="kodeorganisasi='".substr($data['kodemesin'],0,6)."'";
                $optSt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrst);
                $whrst2="kodeorganisasi='".$data['kodemesin']."'";
                $optSt2=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrst2);

                $pdf->Cell(20, 1.5 * $height, ($no+=1), 'TBLR', 0, 'C');
                $pdf->Cell(120, 1.5 * $height, $optSt[substr($data['kodemesin'],0,6)], 'TBR', 0, 'L');
                $pdf->Cell(150, 1.5 * $height, $optSt2[$data['kodemesin']], 'TBR', 0, 'L');
                $pdf->Cell(70, 1.5 * $height, substr($data['subkodemesin'],10,3), 'TBR', 0, 'L');
                $pdf->Cell(160, 1.5 * $height, $data['namasubmesin'], 'TBR', 0, 'L');
                $pdf->Cell(30, 1.5 * $height, $optDtstat[$data['status']], 'TBR', 1, 'C');
            }
        }

        # Print Out
        $pdf->Output();
        break;

    default:
        break;
}

function loadData() {
    global $conn;
    global $dbname;
    global $kodeorg;
    global $owlPDO;
    global $optDtstat;

    $kodeorg = $_SESSION['empl']['lokasitugas'];
    
    $str = "select * from ".$dbname.".pabrik_5submesin where kodemesin like '".$kodeorg."%'";
    $qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $numRows = owlBaris($qry);
    if ($numRows == 0) {
        echo"<tr class=rowcontent>
					<td colspan='8' style='text-align:center;'>" . $_SESSION['lang']['errdatanotexist'] . "</td>
				</tr>";
    } else {
        $no = 0;
        $qry->setFetchMode(PDO::FETCH_OBJ);
        while ($res = $qry->fetch()) {
            $no+=1;
            $whrst="kodeorganisasi='".substr($res->kodemesin,0,6)."'";
            $optSt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrst);
            $whrst2="kodeorganisasi='".$res->kodemesin."'";
            $optSt2=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrst2);
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$res->updateby."'");
            echo"<tr class=rowcontent>
                        <td style='text-align:center;'>" . $no . "</td>
                        <td>".$optSt[substr($res->kodemesin,0,6)]."</td>
                        <td>".$optSt2[$res->kodemesin]."</td>
                        <td>".$res->subkodemesin."</td>
                        <td>".$res->namasubmesin."</td>
                        <td>".$optDtstat[$res->status]."</td>
                        <td>".$nmKar[$res->updateby]."</td>
                        <td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".substr($res->kodemesin,0,6)."','".$res->kodemesin."','".substr($res->subkodemesin,10,3)."','".$res->namasubmesin."','".$res->status."')\"></td>
                        
                    </tr>";
        }
    }
}
//<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('" . $res->kodemesin . "','" . $res->subkodemesin . "')\"></td>
?>