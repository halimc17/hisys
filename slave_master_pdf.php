<?php
session_start();
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');

# Get Data
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

#====================== Prepare Data
$query = selectQuery($dbname,$table,$column);
$result = fetchData($query);
$header = array();
foreach($result[0] as $key=>$row) {
    if($_SESSION['language']=='EN' && $_SESSION['lang'][strtolower($key)]!==''){
        $header[] = $_SESSION['lang'][strtolower($key)];
    }else{
      $header[] = $key;
    }
}

#====================== Prepare Header PDF
class masterpdf extends FPDF {
    function Header() {
        global $table;
        global $header;
        
        # Panjang, Lebar
        $width = $this->w - $this->lMargin - $this->rMargin;
		$height = 12;
        
        $tableName = explode('_',str_replace(range(0,9),'',$table));
		$tableName = ucwords(implode(' ',$tableName));
		
		$this->SetFont('Arial','B',9);
		$this->Cell($width,$height,'Tabel : '.$tableName,'',1,'L');
        $this->Ln();
        
        # Generate Header
        $this->SetFillColor(220,220,220);
        foreach($header as $hName) {
			if(isset($_SESSION['lang'][$hName])) {
				$this->Cell($width/count($header),$height,$_SESSION['lang'][$hName],'TBLR',0,'L',1);
			} else {
				$this->Cell($width/count($header),$height,ucfirst($hName),'TBLR',0,'L',1);
			}
        }
        $this->Ln();
    }
	
	function Footer() {
		$width = $this->w - $this->lMargin - $this->rMargin;
		
		$this->SetY(-30);
        $this->SetFont('Arial','I',8);
        $this->Cell($width-1,10,'Page '.$this->PageNo(),0,0,'L');
		$this->Cell(1,10,$_SESSION['standard']['username'].'/'.date('Y-m-d H:i:s'),0,0,'R');
	}
}

#====================== Prepare PDF Setting
$pdf = new masterpdf('L','pt','A4');
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$height = 12;
$pdf->SetFont('Arial','',8);
$pdf->AddPage();

# Generate Data
foreach($result as $row) {
    foreach($row as $data) {
        $pdf->Cell($width/count($header),$height,$data,1,0,'L');
    }
    $pdf->Ln();
}

# Print Out
$pdf->Output();
?>