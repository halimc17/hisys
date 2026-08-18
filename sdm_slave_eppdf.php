<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$tglevaluasi = checkPostGet('tglevaluasi', '');
$karyawan = checkPostGet('karyawan', '');

$tab='';
$tab.="<table width=100%>
		<tr><td align=center><b><font size=5>FORM PENILAIAN KARYAWAN</font></b></td></tr>";
		
		$str = "SELECT * FROM " . $dbname . ".sdm_evaluasiht where karyawanid='".$karyawan."' and tanggalevaluasi='".$tglevaluasi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$fbatasan=$bar['kekuatan'];
		
		$strx = "SELECT * FROM " . $dbname . ".approval where notransaksi='".$bar['noid']."' and jenispersetujuan='KPI' and level='1'";
		$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		
		
		$nm = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawan."' or karyawanid='".$barx['karyawanid']."'");
		$nik = makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karyawan."'");
		$jab = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karyawan."'");
		$lt = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$karyawan."'");
		$dep = makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karyawan."'");
		$tmk = makeOption($dbname,'datakaryawan','karyawanid,tanggalmasuk',"karyawanid='".$karyawan."'");
		$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$nmdep = makeOption($dbname,'sdm_5departemen','kode,nama');
		
$tab.="<tr><td>
			<table width=100% cellspacing=0 border=1>
				<tr class=rowcontent>
					<td style='background-color:green' align=center>Nama Karyawan :</td>
					<td align=center>".$nm[$bar['karyawanid']]."</td>
					
					<td style='background-color:green' align=center>NIK :</td>
					<td align=center>".$nik[$bar['karyawanid']]."</td>
					
					<td style='background-color:green' align=center>Nama Penilai :</td>
					<td align=center>".@$nm[$barx['karyawanid']]."</td>
				
				</tr>
				<tr class=rowcontent>
					<td style='background-color:green' align=center>Jabatan :</td>
					<td align=center>".$nmjab[$jab[$bar['karyawanid']]]."</td>
					
					<td style='background-color:green' align=center>Unit Kerja / Seksi :</td>
					<td align=center>".$lt[$bar['karyawanid']]."</td>
					
					<td style='background-color:green' align=center>Tgl Review :</td>
					<td align=center>".$bar['tanggalevaluasi']."</td>
				</tr>
				<tr class=rowcontent>
					<td style='background-color:green' align=center>Departement :</td>
					<td align=center>".$nmdep[$dep[$bar['karyawanid']]]."</td>
					
					<td style='background-color:green' align=center>Tgl Efektif Kerja :</td>
					<td align=center>".$tmk[$bar['karyawanid']]."</td>
					
					<td style='background-color:green' align=center>Periode :</td>
					<td align=center>".substr($bar['tanggalevaluasi'],0,7)."</td>
				</tr>
			</table>
		</td></tr>
		
		<tr><td>";
		$str = "SELECT * FROM " . $dbname . ".sdm_nilai_penilaian order by nilai desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			@$kodenilai[$bar['kodenil']]=$bar['kodenil'];
			@$nmkodenilai[$bar['kodenil']]=$bar['nama'];
		}
		$span=count($kodenilai);
$tab.="<table width=100% cellspacing=0 border=1>
				<tr class=rowcontent>
					<td style='background-color:#ffffe6' align=center rowspan=2 colspan=2>No</td>
					<td style='background-color:#ffffe6' align=center rowspan=2>Aspek Penilaian</td>
					<td style='background-color:#ffffe6' align=center colspan=".$span.">Penilaian [N]</td>
					<td style='background-color:#ffffe6' align=center rowspan=2>Comment</td>
				</tr>
				<tr class=rowcontent>";
				foreach($kodenilai as $kode => $val){
					$tab.="<td style='background-color:#ffffe6' align=center>".$nmkodenilai[$val]."</td>";
				}
		$tab.="</tr>";
		
		$str = "select * from " . $dbname . ".sdm_5jeniskriteria order by kode asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$jnskriteria[$bar['kode']]=$bar['kode'];
			$nmjnskriteria[$bar['kode']]=$bar['kriteria'];
		}
		
		$strx = "select * from " . $dbname . ".sdm_5kriteriapenilaian order by kode asc";
		$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx = $resx->fetch()){
			$idjenis[$barx['idjenispenilaian']]=$barx['idjenispenilaian'];
			$kriteria[$barx['kode']][$barx['idjenispenilaian']]=$barx['idjenispenilaian'];
			$nmkriteria[$barx['kode']][$barx['idjenispenilaian']]=$barx['penilaian'];
			$jeniskriteria[$barx['idjenispenilaian']]=$barx['idjenispenilaian'];
		}
			
		$stri = "select * from " . $dbname . ".sdm_evaluasidt where karyawanid='".$karyawan."' and tanggalevaluasi='".$tglevaluasi."'";
		$resi = $owlPDO->query($stri) or die(print " Gagal: " . PDOException::getMessage());
		$resi->setFetchMode(PDO::FETCH_ASSOC);
		while($bari = $resi->fetch()){
			$data[$bari['idjenispenilaian']][$bari['nilai']]=$bari['nilai'];
			$kom[$bari['idjenispenilaian']]=$bari['kom'];
		}
		// echo"<pre>";
		// print_r($data);
		// echo"</pre>";
		
		foreach($jnskriteria as $jnskr){
			$tab.="<tr class=rowcontent>
				<td style='background-color:#ffe6e6' align=left>".$jnskr."</td>
				<td style='background-color:#ffe6e6' align=left colspan=".($span+3).">".$nmjnskriteria[$jnskr]."</td>
			</tr>";
			
			foreach($idjenis as $idjns){
				$kriteria[$jnskr][$idjns] = isset($kriteria[$jnskr][$idjns]) ? $kriteria[$jnskr][$idjns] : '';
				if($kriteria[$jnskr][$idjns]!=''){
					$tab.="<tr class=rowcontent>
					<td align=left></td>
					<td align=left>".$kriteria[$jnskr][$idjns]."</td>
					<td align=left>".$nmkriteria[$jnskr][$idjns]."</td>";
					foreach($kodenilai as $kode => $val){
						$data[$idjns][$val] = isset($data[$idjns][$val]) ? $data[$idjns][$val] : '';
						$kom[$idjns] = isset($kom[$idjns]) ? $kom[$idjns] : '';
						$isi='';
						if($data[$idjns][$val]!=''){
							$isi="x";
						}
						$tab.="<td align=center>".$isi."</td>";
					}
					$tab.="<td align=left>".$kom[$idjns]."</td>";
					$tab.="</tr>";					
				}
			}
		}
		$tab.="<tr></tr>";
		$tab.="</table>
		</td></tr>
		<tr><td>Feed Back Management/Atasan</td></tr>
			<tr><td>
			<table border=1 width=100%>
				<tr style=height:50px><td width=100%>&nbsp;".$fbatasan."</td></tr>
			</table>
		</td></tr>
		</table>";
	 //echo $tab;
	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();
	$dompdf->stream("form survey",array("Attachment"=>0));
?>