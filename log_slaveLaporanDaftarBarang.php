<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = $_POST['proses'];
$kelbrg = $_POST['kelbrg'];
$subklbarang = $_POST['subklbarang'];
$gdg = $_POST['gdg'];
$txtfind = $_POST['txtcari'];


switch($proses){
	case'getsubkelompok':
		## Pengambilan sub kelompok barang dari table sub kelompok barang
		$optsubkelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str = "select kode, namasubkelompok from ".$dbname.".log_5subklbarang where kelompok='".$kelbrg."' order by kode asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optsubkelompok.="<option value='".$val['kode']."'>".$val['namasubkelompok']. " [ ".$val['kode']." ] </option>";
		}
		
		echo $optsubkelompok;
	break;
	
	case'preview':
		$str = "select * from ".$dbname.".log_5klbarang order by kelompok asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$nmkl[$val['kode']]=$val['kelompok'];
		}

		$str = "select * from ".$dbname.".log_5subklbarang order by namasubkelompok asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$nmsubkl[$val['kode']]=$val['namasubkelompok'];
		}

		echo"<table class=sortable cellspacing=1 border=0 cellpadding=5 style='width:100%'>
		<thead>
			<tr class=rowheader style='text-align:center'>
				<th>No.</th>
				<th align=center>" . str_replace(" ", "<br>", $_SESSION['lang']['kodekelompok']) . "</th>
				<th align=center>" . $_SESSION['lang']['namakelompok'] . "</th>
				<th align=center>" . str_replace(" ", "<br>", $_SESSION['lang']['subkelompokbarang']) . "</th>
				<th align=center>" . $_SESSION['lang']['namasubkelompokbarang']. "</th>
				<th>" . $_SESSION['lang']['materialcode'] . "</th>
				<th>" . $_SESSION['lang']['materialname'] . "</th>
				<th>" . $_SESSION['lang']['satuan'] . "</th>
				<th align=center style='display:none'>" . str_replace(" ", "<br>", $_SESSION['lang']['minstok']) . "</th>
				<th align=center style='display:none'>" . str_replace(" ", "<br>", $_SESSION['lang']['nokartubin']) . "</th>
				<th>" . $_SESSION['lang']['konversi'] . "</th>	  
				<th style='display:none'>" . $_SESSION['lang']['tglmaxin'] . "</th>
				<th style='display:none'>" . $_SESSION['lang']['tglmaxout'] . "</th>
				<th>" . $_SESSION['lang']['status'] . "</th>
				<th>BIN CARD</th>	  
			</tr>  
		</thead><tbody>";

		$str = "select a.kelompokbarang,a.kodebarang,a.namabarang,a.satuan,a.konversi,a.inactive,b.nokartubin,b.minstok from " . $dbname . ".log_5masterbarang a
		left join " . $dbname . ".log_5kartubin b on a.kodebarang=b.kodebarang and b.kodegudang='" . $gdg . "'
		where (a.namabarang like '%" . $txtfind . "%' or a.kodebarang like '%" . $txtfind . "%')
			and a.kelompokbarang like '%" . $kelbrg . "%' and left(a.kodebarang,5) like '".$subklbarang."%' 
		order by a.kodebarang asc";

		$strin = "select min(a.tanggal) as tgl,a.kodebarang from " . $dbname . ".log_transaksi_vw a 
		left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang 
		where a.kodegudang ='" . $gdg . "' and tipetransaksi in(1,3) and (b.namabarang 
		like '%" . $txtfind . "%' or a.kodebarang like '%" . $txtfind . "%') and kelompokbarang like '%" . $kelbrg . "%' group by namabarang order by a.kodebarang asc";

		$strout = "select max(a.tanggal) as tgl,a.kodebarang from " . $dbname . ".log_transaksi_vw a 
		left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang 
		where a.kodegudang ='" . $gdg . "' and tipetransaksi in(5,7) and (b.namabarang 
		like '%" . $txtfind . "%' or a.kodebarang like '%" . $txtfind . "%') and kelompokbarang like '%" . $kelbrg . "%' 
		group by namabarang order by a.kodebarang asc";
		$resin=$owlPDO->query($strin) or die(print " Gagal: ".PDOException::getMessage());
		$resin->setFetchMode(PDO::FETCH_OBJ);
		$in = array();
		while ($barin = $resin->fetch()) {
			$in[$barin->kodebarang] = tanggalnormal($barin->tgl);
		}

		$resout=$owlPDO->query($strout) or die(print " Gagal: ".PDOException::getMessage());
		$resout->setFetchMode(PDO::FETCH_OBJ);
		$out = array();
		while ($barout = $resout->fetch()) {
			$out[$barout->kodebarang] = tanggalnormal($barout->tgl);
		}

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no = 0;
		while ($bar = $res->fetch()) {
			$stru = "select * from " . $dbname . ".log_5photobarang where kodebarang='" . $bar->kodebarang . "'";
			$resx=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($resx);
			if ($numrows > 0) {
				$adx = "<img src=images/zoom.png class=resicon height=16px title='View detail'  onclick=viewDetailbarang('" . $bar->kodebarang . "',event)> <img src=images/tool.png class=resicon height=16px title='Edit Detail'  onclick=editDetailbarang('" . $bar->kodebarang . "',event)>";
			} else {
				$adx = "<img src=images/tool.png class=resicon height=16px title='Edit Detail' onclick=editDetailbarang('" . $bar->kodebarang . "',event)>";
			}

			$no+=1;
			echo"<tr class=rowcontent>
				<td align='right'>" . $no . "</td>
				<td align='center'>" . $bar->kelompokbarang . "</td>
				<td align='left'>" . $nmkl[$bar->kelompokbarang] . "</td>
				<td align='center'>".substr($bar->kodebarang,0,5)."</td>
				<td align='left'>".$nmsubkl[substr($bar->kodebarang,0,5)]."</td>
				<td align='center'>" . $bar->kodebarang . "</td>
				<td>" . $bar->namabarang . "</td>
				<td align='center'>" . $bar->satuan . "</td>
				<td align=right  style='display:none'>" . $bar->minstok . "</td>
				<td  style='display:none'>" . $bar->nokartubin . "</td>
				<td align='center'>".($bar->konversi=='0'?'Tidak':'Ya')."</td>
				<td style='display:none'>" . (isset($in[$bar->kodebarang]) ? $in[$bar->kodebarang] : '') . "</td>
				<td style='display:none'>" . (isset($out[$bar->kodebarang]) ? $out[$bar->kodebarang] : '') . "</td>    
				<td align='center'>".($bar->inactive=='0'?'Aktif':'Non-Aktif')."</td>
				<td align='center'><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('log_5masterbarang','".$bar->kodebarang."','','log_slave_print_log_bincard',event);\"></td>
			</tr>";
		}
		echo"</tbody>
			</table>
		</div>";
	break;
}

