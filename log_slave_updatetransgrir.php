<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$method = checkPostGet('method', '');

if($param['kodeorg']=='' or $param['tahun']==''){
	exit("Warning : Kode Organisasi dan Tahun harus diisi.");
}

switch ($method) {
	case'preview':
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=2950px>
			<thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['nopo'] . "</td>    
            <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
            <td align=center>" . $_SESSION['lang']['barang'] . "</td>
            <td align=center>Jlh PO</td>
            <td align=center></td>
        </tr>
		</thead>
			";
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=5 style=background-color:#c3ffb8;font-weight:bold; align=center>Daftar PO</td>";
		$tab.="<td>
				<table>
					<td align=center colspan=6 style=background-color:#adefff;width:470px;font-weight:bold>Penerimaan Barang</td>
					<td align=center colspan=1 style=background-color:#adffe2;width:490px;font-weight:bold>Jurnal Penerimaan Barang</td>
					<td align=center style=background-color:#fdff99;width:460px;font-weight:bold>Tagihan (AP)</td>
					<td align=center colspan=7 style=background-color:#edd1ff;width:670px;font-weight:bold>Kas Bank</td>
					<td align=center colspan=5 style=background-color:#adffe2;width:410px;font-weight:bold>Jurnal Kas dan Bank</td>
				</table>
				</td>
				";
		$tab.="</tr>";
		$where="";
		
		$where.=" and b.kodeunit ='".$param['kodeorg']."'";
		$where.=" and b.tanggal like '".$param['tahun']."%'";
		
		
		$str="select * from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where a.nopo in (select nopo from ".$dbname.".log_transaksiht where tipetransaksi='1') ".$where." order by tanggal asc";
		$res = fetchData($str);
		foreach($res as $val){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center valign=top width=40px>" . $no . "</td>";
			$tab.="<td align=left valign=top width=170px>" . $val['nopo'] . "</td>";
			$tab.="<td align=left valign=top width=70px>" . $val['tanggal'] . "</td>";
			$tab.="<td align=left valign=top width=70px>" . $val['kodebarang'] . "</td>";
			$tab.="<td align=right valign=top width=50px>" . $val['jumlahpesan'] . "</td>";
			#penerimaan barang
			$tab.="<td valign=top>";
				$tab.="<table>";
				$s1="select * from ".$dbname.".log_transaksi_vw where tipetransaksi='1' and nopo='".$val['nopo']."' and kodebarang='".$val['kodebarang']."'";
				$r1 = fetchData($s1);
				$n1=0;
				foreach($r1 as $v1){
					$n1++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center style=background-color:#adefff valign=top width=30px>" . $n1 . "</td>";
					$tab.="<td align=left style=background-color:#adefff valign=top width=160px>" . $v1['notransaksi'] . "</td>";
					$tab.="<td align=left style=background-color:#adefff valign=top width=70px>" . $v1['tanggal'] . "</td>";
					$tab.="<td align=left style=background-color:#adefff valign=top width=70px>" . $v1['kodebarang'] . "</td>";
					$tab.="<td align=right style=background-color:#adefff valign=top width=70px>" . $v1['jumlah'] . "</td>";
					$tab.="<td align=left style=background-color:#adefff valign=top width=50px>" . ($v1['post']==1?"Post":"Unpost") . "</td>";
					#jurnal
					$tab.="<td valign=top>";
						$tab.="<table>";
						$s2="select * from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$v1['notransaksi']."' and kodebarang ='".$v1['kodebarang']."'";
						$r2 = fetchData($s2);
						$n2=0;
						foreach($r2 as $v2){
							$n2++;
							$tab.="<tr class=rowcontent style=background-color:#adffe2>";
							$tab.="<td align=center valign=top width=30px>" . $n2 . "</td>";
							$tab.="<td align=left valign=top width=160px>" . $v2['nojurnal'] . "</td>";
							$tab.="<td align=left valign=top width=70px>" . $v2['tanggal'] . "</td>";
							$tab.="<td align=left valign=top width=70px>" . $v2['kodebarang'] . "</td>";
							$tab.="<td align=right valign=top width=60px>" . $v2['noakun'] . "</td>";
							$tab.="<td align=right valign=top width=80px>" . round($v2['jumlah']) . "</td>";
							$tab.="</tr>";
						}
						$tab.="</table>";
					$tab.="</td>";			
					#jurnal
					
					#tagihan
					$tab.="<td valign=top>";
						$tab.="<table>";
						$s3="select * from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice where 
						a.noinvoice in (select a.noinvoice from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice where a.nopo ='".$val['nopo']."' and (a.notransaksi_gr='".$v1['notransaksi']."' or b.notransaksi='".$v1['notransaksi']."'))";
						$r3 = fetchData($s3);
						$n3=0;
						foreach($r3 as $v3){
							$n3++;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center style=background-color:#fdff99 valign=top width=30px>" . $n3 . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=100px>" . $v3['noinvoice'] . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=20px>" . $v3['tipeinvoice'] . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=70px>" . $v3['tanggal'] . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=70px>" . $v3['noakun'] . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=70px>" . $v3['nilai'] . "</td>";
							$tab.="<td align=left style=background-color:#fdff99 valign=top width=70px>" . ($v3['posting']==1?"Post":"Unpost")  . "</td>";
							#KAS BANK
							$tab.="<td valign=top>";
								$tab.="<table>";
								$s4="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 ='".$v3['noinvoice']."'";
								$r4 = fetchData($s4);
								$n4=0;
								foreach($r4 as $v4){
									$n4++;
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center style=background-color:#edd1ff valign=top width=30px>" . $n4 . "</td>";
									$tab.="<td align=left style=background-color:#edd1ff valign=top width=170px>" . $v4['notransaksi'] . "</td>";
									$tab.="<td align=left style=background-color:#edd1ff valign=top width=170px>" . $v4['novoucher'] . "</td>";
									$tab.="<td align=left style=background-color:#edd1ff valign=top width=70px>" . $v4['noakun'] . "</td>";
									$tab.="<td align=left style=background-color:#edd1ff valign=top width=70px>" . $v4['tanggal'] . "</td>";
									$tab.="<td align=right style=background-color:#edd1ff valign=top width=70px>" . round($v4['jumlah']) . "</td>";
									$tab.="<td align=left style=background-color:#edd1ff valign=top width=70px>" . ($v4['posting']==1?"Post":"Unpost")  . "</td>";
									#jurnal kas bank
									$tab.="<td valign=top>";
										$tab.="<table>";
										$s5="select * from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$v4['notransaksi']."' and kodejurnal in ('BK','KK')";
										$r5 = fetchData($s5);
										$n5=0;
										foreach($r5 as $v5){
											$n5++;
											$tab.="<tr class=rowcontent style=background-color:#adffe2>";
											$tab.="<td align=center valign=top width=30px>" . $n5 . "</td>";
											$tab.="<td align=left valign=top width=160px>" . $v5['nojurnal'] . "</td>";
											$tab.="<td align=left valign=top width=70px>" . $v5['tanggal'] . "</td>";
											$tab.="<td align=right valign=top width=60px>" . $v5['noakun'] . "</td>";
											$tab.="<td align=right valign=top width=80px>" . round($v5['jumlah']) . "</td>";
											$tab.="</tr>";
										}
										$tab.="</table>";
									$tab.="</td>";			
									#jurnal kas bank
									
									
									$tab.="</tr>";
								}
								$tab.="</table>";
							$tab.="</td>";	
							#KAS BANK
							
							
							$tab.="</tr>";
						}
						$tab.="</table>";
					$tab.="</td>";	
					#tagihan
					
					$tab.="</tr>";
				}
				$tab.="</table>";
			$tab.="</td>";
			#penerimaan barang





			
			$tab.="</tr>";
		}
		
		$tab.="</table>";
		
		if($param['jenis']!='excel'){
			echo $tab;
		}else{
		
			$nop = "Angkut TBS.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("Angkut TBS", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
	break;
}
?>	