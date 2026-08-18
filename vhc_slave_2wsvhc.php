<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$ws=checkPostGet('ws','');
$jenis=checkPostGet('jenis','');
$vhc=checkPostGet('vhc','');
$proses = checkPostGet('proses', '');
$per = checkPostGet('per', '');

$thn=substr($per,0,4);
$tgl1=$thn.'-01-01';




switch ($proses) {

    case'getvhc':
        $opt="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select * from ".$dbname.".vhc_5master where jenisvhc='".$jenis."' and kodeorg='".substr($ws,0,4)."' ";
      
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$e="";
			if(getNopol($bar['kodevhc'])!=''){
				$e.= " - ".getNopol($bar['kodevhc']);
			}
			if(getNopol($bar['kodevhc'],'d')!=''){
				$e.= " - ".getNopol($bar['kodevhc'],'d');
			}
            $opt.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc'].$e."</option>";
        }
        echo $opt;
        break;


######PREVIEW
    default:
        if($ws=='' || $per=='')
        {
            echo"Warning: Lengkapi pengisian"; 
            exit;
        }
		
		$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgl2=$bar['tanggalsampai'];



			
		$where="";
        if($jenis!='')
        {
            $where.=" and b.jenisvhc='".$jenis."'";
        }
        if($vhc!='')
        {
            $where.=" and b.kodevhc='".$vhc."'";
        }

		######################################
		############# prepare data ###########
		######################################


		#bi
		$str="select a.kodeorg,a.kodevhc,a.tanggal,a.downtime,b.jenisvhc,b.kelompokvhc,b.detailvhc "
				. " from ".$dbname.".vhc_penggantianht a left join ".$dbname.".vhc_5master b"
				. " on a.kodevhc=b.kodevhc"
				. " where a.kodeorg='".$ws."' and a.tanggal like '".$per."%' ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kelompokvhc[$bar['kelompokvhc']]=$bar['kelompokvhc'];
			$jenisvhc[$bar['jenisvhc']]=$bar['jenisvhc'];
			$listjenisvhc[$bar['kelompokvhc']][$bar['jenisvhc']]=$bar['jenisvhc'];
			$kodevhc[$bar['kodevhc']]=$bar['kodevhc'];
			$listkodevhc[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]=$bar['kodevhc'];
			@$jam[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]+=$bar['downtime'];
		}

		#sdbi
		$str="select a.kodeorg,a.kodevhc,a.tanggal,a.downtime,b.jenisvhc,b.kelompokvhc,b.detailvhc "
				. " from ".$dbname.".vhc_penggantianht a left join ".$dbname.".vhc_5master b"
				. " on a.kodevhc=b.kodevhc"
				. " where a.kodeorg='".$ws."' and a.tanggal between '".$tgl1."' and '".$tgl2."' ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kelompokvhc[$bar['kelompokvhc']]=$bar['kelompokvhc'];
			$jenisvhc[$bar['jenisvhc']]=$bar['jenisvhc'];
			$listjenisvhc[$bar['kelompokvhc']][$bar['jenisvhc']]=$bar['jenisvhc'];
			$kodevhc[$bar['kodevhc']]=$bar['kodevhc'];
			$listkodevhc[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]=$bar['kodevhc'];
			@$jamsd[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]+=$bar['downtime'];
		}



		#sdbijurnal
		$str="select a.kodevhc,a.tanggal,b.jenisvhc,b.kelompokvhc,b.detailvhc,a.jumlah "
				. " from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".vhc_5master b"
				. " on a.kodevhc=b.kodevhc"
				. " where a.kodeorg='".substr($ws,0,4)."' and a.tanggal between '".$tgl1."' "
				. "and '".$tgl2."' and noreferensi='ALK_BY_WS' and jumlah>0 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kelompokvhc[$bar['kelompokvhc']]=$bar['kelompokvhc'];
			$jenisvhc[$bar['jenisvhc']]=$bar['jenisvhc'];
			$listjenisvhc[$bar['kelompokvhc']][$bar['jenisvhc']]=$bar['jenisvhc'];
			$kodevhc[$bar['kodevhc']]=$bar['kodevhc'];
			$listkodevhc[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]=$bar['kodevhc'];
			@$jumlahsd[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]+=$bar['jumlah'];
		}

		#jurnal
		$str="select a.kodevhc,a.tanggal,b.jenisvhc,b.kelompokvhc,b.detailvhc,a.jumlah "
				. " from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".vhc_5master b"
				. " on a.kodevhc=b.kodevhc"
				. " where a.kodeorg='".substr($ws,0,4)."' and a.tanggal like '".$per."%'"
				. " and noreferensi='ALK_BY_WS' and jumlah>0 ".$where." ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kelompokvhc[$bar['kelompokvhc']]=$bar['kelompokvhc'];
			$jenisvhc[$bar['jenisvhc']]=$bar['jenisvhc'];
			$listjenisvhc[$bar['kelompokvhc']][$bar['jenisvhc']]=$bar['jenisvhc'];
			$kodevhc[$bar['kodevhc']]=$bar['kodevhc'];
			$listkodevhc[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]=$bar['kodevhc'];
			@$jumlah[$bar['kelompokvhc']][$bar['jenisvhc']][$bar['kodevhc']]+=$bar['jumlah'];
		}




		if ($proses == 'excel') {
			$stream = "<table class=sortable cellspacing=1 border=1>";
		} else {
			$stream = "<table class=sortable cellspacing=1 cellpadding=5>";
		}//style=width:63%
		$stream.="
			<thead>
				<tr class=rowheader>
					<th align=center  rowspan='2' style=width:50px>" . $_SESSION['lang']['namakelompok'] . "</th>
					<th align=center rowspan='2' style=width:50px>" . $_SESSION['lang']['jenisvch'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['namajenisvhc'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['kodevhc'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['detail'] . "</th>
					<th align=center colspan='2'>" . $_SESSION['lang']['bi'] . "</th>
					<th align=center colspan='2'>" . $_SESSION['lang']['sbi'] . "</th>
				</tr>
				<tr>
					<th align=center>" . $_SESSION['lang']['jam'] . "</th>
					<th align=center>" . $_SESSION['lang']['biaya'] . "</th>
					<th align=center>" . $_SESSION['lang']['jam'] . "</th>
					<th align=center>" . $_SESSION['lang']['biaya'] . "</th>
				</tr>
			</thead>
		 <tbody>";

		$nmjenis=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');

		foreach($kelompokvhc as $kelompok){
			foreach($jenisvhc as $jenis){
				if(@$listjenisvhc[$kelompok][$jenis]!=''){
					foreach($kodevhc as $vhc){
						if(@$listkodevhc[$kelompok][$jenis][$vhc]!=''){
							$nopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$vhc."'");
							$detail=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$vhc."'");
							$stream.="<tr class=rowcontent style='cursor:pointer' title='Click for Detail.' onclick=\"detail('".$vhc."','".$per."','html','event');\">";
							$stream.="<td align=center>".$kelompok."</td>";
							$stream.="<td align=center>".$jenis."</td>";
							$stream.="<td>".$nmjenis[$jenis]."</td>";
							$stream.="<td>".$vhc."</td>";
							$stream.="<td>".$nopol[$vhc]."</td>";
							$stream.="<td>".$detail[$vhc]."</td>";
							$stream.="<td align=right>".@number_format($jam[$kelompok][$jenis][$vhc],2)."</td>";
							$stream.="<td align=right>".@number_format($jumlah[$kelompok][$jenis][$vhc],2)."</td>";
							$stream.="<td align=right>".@number_format($jamsd[$kelompok][$jenis][$vhc],2)."</td>";
							$stream.="<td align=right>".@number_format($jumlahsd[$kelompok][$jenis][$vhc],2)."</td>";
							$stream.="</tr>";
							
							@$stjenisjam[$kelompok][$jenis]+=$jam[$kelompok][$jenis][$vhc];
							@$stjenisjamsd[$kelompok][$jenis]+=$jamsd[$kelompok][$jenis][$vhc];
							
							@$stjenisjumlah[$kelompok][$jenis]+=$jumlah[$kelompok][$jenis][$vhc];
									@$stjenisjumlahsd[$kelompok][$jenis]+=$jumlahsd[$kelompok][$jenis][$vhc];
							
						}
					}
					$stream.="
						<tr  bgcolor=#80FFFE>
							<td style=background-color:#80FFFE colspan=6>".$_SESSION['lang']['subtotal']." ".$jenis."</td>
							<td style=background-color:#80FFFE align=right>".@number_format($stjenisjam[$kelompok][$jenis],2)."</td>
							<td style=background-color:#80FFFE align=right>".@number_format($stjenisjumlah[$kelompok][$jenis],2)."</td>
							<td style=background-color:#80FFFE align=right>".@number_format($stjenisjamsd[$kelompok][$jenis],2)."</td>
							<td style=background-color:#80FFFE align=right>".@number_format($stjenisjumlahsd[$kelompok][$jenis],2)."</td>
						</tr>";
					@$stkelompokjam[$kelompok]+=$stjenisjam[$kelompok][$jenis];
					@$stkelompokjamsd[$kelompok]+=$stjenisjamsd[$kelompok][$jenis];
					@$stkelompokjumlah[$kelompok]+=$stjenisjumlah[$kelompok][$jenis];
					@$stkelompokjumlahsd[$kelompok]+=$stjenisjumlahsd[$kelompok][$jenis];
					
				}
			
			}
			$stream.="
				<tr bgcolor=#48D1CC>
					<td style=background-color:#48D1CC align=left colspan=6>".$_SESSION['lang']['subtotal']."  ".$kelompok."</td>
					<td style=background-color:#48D1CC align=right>".@number_format($stkelompokjam[$kelompok],2)."</td>
					<td style=background-color:#48D1CC align=right>".@number_format($stkelompokjumlah[$kelompok],2)."</td>
					<td style=background-color:#48D1CC align=right>".@number_format($stkelompokjamsd[$kelompok],2)."</td>
					<td style=background-color:#48D1CC align=right>".@number_format($stkelompokjumlahsd[$kelompok],2)."</td>
				</tr>";
					@$gtjam+=$stkelompokjam[$kelompok];
					@$gtjamsd+=$stkelompokjamsd[$kelompok];
					@$gtjumlah+=$stkelompokjumlah[$kelompok];
					@$gtjumlahsd+=$stkelompokjumlahsd[$kelompok];
		}
		$stream.="
				<tr bgcolor=#009999>
					<td style=background-color:#009999 align=left colspan=6>".$_SESSION['lang']['grnd_total']."</td>
					<td style=background-color:#009999 align=right>".@number_format($gtjam,2)."</td>
					<td style=background-color:#009999 align=right>".@number_format($gtjumlah,2)."</td>   
					<td style=background-color:#009999 align=right>".@number_format($gtjamsd,2)."</td>
				   <td style=background-color:#009999 align=right>".@number_format($gtjumlahsd,2)."</td>
				</tr>";


		$stream.="
		 </tbody>
			 </table>";
			 
        
		if ($proses == 'excel') {
			$tglSkrg = date("Ymd");
			// $nop_ = "laporan_rekap_panen_per_blok" . $kdorg;
			$nop_ = "laporan_kerjaworkshopperkendaraan_".checkPostGet('ws','')."_".checkPostGet('jenis','')."_".checkPostGet('vhc','')."_".checkPostGet('per','');
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
		} else {
			echo $stream;
		}
	break;	
}
?>