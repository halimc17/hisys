<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/terbilang.php');
require('lib/fpdf.php');
// require('lib/htmlparser.inc');
// require('lib/htmltofpdf.php');

$method = checkPostGet('method','');
$keterangan = htmlspecialchars(checkPostGet('keterangan',''));
// $keterangan = checkPostGet('keterangan','');
$judul = checkPostGet('judul','');
$tipe = checkPostGet('tipe','');
$kode = checkPostGet('kode','');
$keterangansch = checkPostGet('keterangansch','');



switch ($method) {	
	case 'insert':
		$str = "insert into ".$dbname.".help_developer 
				(`judul`, `keterangan`, `tipe`)
				values 
				('".$judul."','".$keterangan."','".$tipe."')";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
		
	break;

    case 'update':
		 $str = "update ".$dbname.".help_developer set judul='".$judul."', keterangan='".$keterangan."', tipe='".$tipe."' where kode = '".$kode."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'delete':
		$str = "delete from ".$dbname.".help_developer where kode = '".$kode."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	case 'getketerangan':
		$str="select * from ".$dbname.".help_developer where kode='".$kode."' ";
		$res=fetchData($str);
		$judul = $res[0]['judul'];
		$tipe = $res[0]['tipe'];
		// $keterangan = $res[0]['keterangan'];
		$keterangan = htmlspecialchars_decode($res[0]['keterangan']);

		echo $judul."####".$tipe."####".$keterangan;
		
		break;
	
    case'loaddata':
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where="";
		
		if($keterangansch!=''){ 
			$where.=" and judul LIKE  '%".$keterangansch."%'";
		}

		 $ql2 = "select count(*) as jmlhrow from " . $dbname . ".help_developer
				where 0=0 ".$where." "; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0 style=min-width:900px>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>Judul</td>
				<td align=center>Tipe</td>
				<td align=center>Materi</td>
				<td align=center colspan=3>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".help_developer
				where 0=0 ".$where." LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['judul']."</td>";
            $tab.="<td>".$bar['tipe']."</td>";
            $tab.="<td>".htmlspecialchars_decode($bar['keterangan'])."</td>";			
				$tab.="<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' 
							onclick=\"edit('".$bar['kode']."');\"></td>";
				$tab.="<td align=center>
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
						onclick=\"del('" . $bar['kode'] . "');\" ></td>";

				
				$tab.="<td align=center><img src=images/zoom.png class=resicon  caption='PDF' onclick=\"viewpdf('".$bar['kode']."');\"></td>";
		
			
			

            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=14 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

	case 'viewpdf':
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
		$gen='generic.css';
	}else if($theme=='red'){
		$gen='genericRed.css';  
	}else{
		$gen='genericGray.css';  
	}  
	echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";


	$str = "select * from " . $dbname . ".help_developer where kode='" . $kode . "'";
	$res = fetchData($str);
	$judul = $res[0]['judul'];
	$keterangan = $res[0]['keterangan'];
	$tipe = $res[0]['tipe'];

	$tab="
	<fieldset>
	<table class=sortable cellpadding=1 cellspacing=1 border=0 style=min-width:800px>
	<tr class=rowcontent>
	<td>Help Developer</td>
	</tr>
	<tr>
	<td>".$judul."</td>
	</tr>
	<tr>
	<td>".$tipe."</td>
	</tr>
	<tr>
	<td>".htmlspecialchars_decode($keterangan)."</td>
	</tr>
	</table>
	</fieldset>
	";

	echo $tab;
		
		
		
		// class PDF extends FPDF{}
		
		// $pdf=new FPDF('P','mm','A4');
		// $pdf->SetAutoPageBreak(false);
		// $pdf->AddPage();
			
		
		// $height=10;
		// $pdf->SetTextColor(0,0,0);
		
		// $pdf->SetFont('Times','B','9.5');
		// $pdf->SetY(35);
		// $pdf->SetX(30);
		// $pdf->Cell(100,$height,$nmorg[$kodept],0,1,'L');

		// $pdf->SetFont('Times','B','14');
		// $pdf->SetY(40);
		// $pdf->SetX(95);
		// $pdf->Cell(100,$height,"HELP DEVELOPER",0,1,'L');

		// $pdf->SetFont('Times','B','10');
		// $pdf->SetY(60);
		// $pdf->SetX(30);
		// $pdf->Cell(100,$height,"TIPE :",0,1,'L');
		
		// $pdf->SetFont('Times','','10');
		// $pdf->SetY(60);
		// $pdf->SetX(50);
		// $pdf->Cell(100,$height,$tipe,0,1,'L');
				
		// $pdf->SetFont('Times','B','10');
		// $pdf->SetY(70);
		// $pdf->SetX(30);
		// $pdf->Cell(100,$height,"JUDUL : ",0,1,'L');

		// $pdf->SetFont('Times','','10');
		// $pdf->SetY(70);
		// $pdf->SetX(50);
		// $pdf->Cell(100,$height,$judul,0,1,'L');

		// $pdf->SetFont('Times','B','10');
		// $pdf->SetY(80);
		// $pdf->SetX(30);
		// $pdf->Cell(100,$height,"URAIAN :",0,1,'L');

		// $pdf->SetFont('Times','','10');
		// $pdf->SetY(80);
		// $pdf->SetX(50);
		// $pdf->MultiCell(100,$height,htmlspecialchars_decode($keterangan),0,'L');

		
		
		
		
		
		// $pdf->Output();
		
		break;
	


}
?>
