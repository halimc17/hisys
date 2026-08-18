<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses','');
if(!empty($_POST)){$param=$_POST;}else{$param=$_GET;}

switch ($proses){
	case 'detail':
		$tab = "<table>";
		$tab.= "<tr>";
			$tab.= "<td>".$_SESSION['lang']['namablok']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".getNamaOrg($param['blok'])."</td>";
			$tab.= "<td width=50px></td>";
			
			$tab.= "<td>".$_SESSION['lang']['luas']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".getBlok($param['blok'],'luasareaproduktif')." Ha</td>";
		$tab.= "</tr>";
		
		$tab.= "<tr>";
			$tab.= "<td>".$_SESSION['lang']['tahuntanam']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".getBlok($param['blok'],'tahuntanam')."</td>";
			$tab.= "<td></td>";
			
			$tab.= "<td>".$_SESSION['lang']['pokok']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".getBlok($param['blok'],'jumlahpokok')."</td>";
		$tab.= "</tr>";
		$tab.= "<tr>";
			$tab.= "<td>".$_SESSION['lang']['jenisbibit']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".getBlok($param['blok'],'jenisbibit')."</td>";
			$tab.= "<td></td>";
			
			$tab.= "<td>".$_SESSION['lang']['sph']."</td>";
			$tab.= "<td>:</td>";
			$tab.= "<td>".hidezerodecimal(getBlok($param['blok'],'jumlahpokok')/getBlok($param['blok'],'luasareaproduktif'),2)."</td>";
		$tab.= "</tr>";
		
		$tab.= "</table>";
		$tab.= "<table class=sortable cellpadding=5 cellspacing=1 border=0>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center >".$_SESSION['lang']['nourut']."</th>
					<th align=center >".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['panen']."</th>
					<th align=center >".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kirim']."</th>
					<th align=center >".$_SESSION['lang']['restan']."<br>(Hari)</th>
					<th align=center >".$_SESSION['lang']['nospb']."</th>
					<th align=center >".$_SESSION['lang']['nopol']."</th>
					<th align=center >".$_SESSION['lang']['noTiket']."</th>
					<th align=center >".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kirim']."</th>
					<th align=center >".$_SESSION['lang']['kgwb']."</th>
					<th align=center >".$_SESSION['lang']['mandor']."</th>
					<th align=center >".$_SESSION['lang']['pemanen']."</th>
					<th align=center >".$_SESSION['lang']['jjg']."</th>
					<th align=center >".$_SESSION['lang']['kg']."</th>
					<th align=center >".$_SESSION['lang']['keterangan']."</th>
				</tr>";
			$tab.="</tr>
			</thead>
		 <tbody>";
		 
		$str = "select *  from " . $dbnamerpt . ".kebun_spb_vw where 1=1 and blok='".$param['blok']."' and tanggal like '".$param['periode']."%' order by tanggalpanen asc, nospb asc";
		$res = fetchdatarpt($str);
		foreach($res as $bar){
			$sql = "select *  from " . $dbnamerpt . ".kebun_3premipemanen where 1=1 and blok='".$param['blok']."' and tanggalpanen = '".$bar['tanggalpanen']."' and nospb = '".$bar['nospb']."' order by tanggalpanen asc";
			$req = fetchdatarpt($sql);
			if(empty($req)){				
				$sql = "select nospb, b.tanggal as tanggalpanen from " . $dbnamerpt . ".kebun_prestasi a left join " . $dbnamerpt . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and a.kodeorg='".$param['blok']."' and b.tanggal = '".$bar['tanggalpanen']."' and a.nospb = '".$bar['nospb']."' order by tanggal asc";
				$req = fetchdatarpt($sql);
			}
			foreach($req as $val){
				$rowspan[$bar['nospb']][$bar['tanggalpanen']]++;
			}
		}
		$baris="";
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent name=baris[]>";
			$tab.="<td align=center style=vertical-align:top; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$no."</td>";
			$tab.="<td style=vertical-align:top;text-align:center rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$bar['tanggalpanen']."</td>";
			$tab.="<td style=vertical-align:top;text-align:center rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$bar['tanggal']."</td>";
			
			$diff      = (strtotime($bar['tanggal'])-strtotime($bar['tanggalpanen']));
			$hari      = floor($diff/(60*60*24));
					
			
			$tab.="<td style=vertical-align:top;text-align:center; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$hari."</td>";
			$tab.="<td style=vertical-align:top; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$bar['nospb']."</td>";
			$tab.="<td style=vertical-align:top; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$bar['nokendaraan']."</td>";
			$tab.="<td style=vertical-align:top; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".$bar['notiket']."</td>";
			$tab.="<td style=vertical-align:top;text-align:right; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".hidezerodecimal($bar['jjg'])."</td>";
			$tab.="<td style=vertical-align:top;text-align:right; rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">".hidezerodecimal($bar['kgwb'])."</td>";
			
			$totaljjgwb+=$bar['jjg'];
			$totalkgwb+=$bar['kgwb'];
			
			$baris=0;$totaljjg=$totalkg=0;
			$sql = "select *  from " . $dbnamerpt . ".kebun_3premipemanen where 1=1 and blok='".$param['blok']."' and tanggalpanen = '".$bar['tanggalpanen']."' and nospb = '".$bar['nospb']."' order by tanggalpanen asc";
			$req = fetchdatarpt($sql);
			if(empty($req)){				
				$sql = "select nikmandor as mandor, a.nik as karyawanid, hasilkerja as jjgpanen, 'Premi panen belum diproses' as ket  from " . $dbnamerpt . ".kebun_prestasi a left join " . $dbnamerpt . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 and a.kodeorg='".$param['blok']."' and b.tanggal = '".$bar['tanggalpanen']."' and a.nospb = '".$bar['nospb']."' order by tanggal asc";
				$req = fetchdatarpt($sql);
			}
			if(!empty($req)){				
				foreach($req as $val){
					$baris++;
					$tab.="<td style=vertical-align:top;>".getKary($val['mandor'])."</td>";
					$tab.="<td style=vertical-align:top;>".getKary($val['karyawanid'])."</td>";
					$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($val['jjgpanen'])."</td>";
					$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($val['kgwb'])."</td>";
					$tab.="<td style=vertical-align:top;>".$val['ket']."</td>";
					$totaljjg+=$val['jjgpanen'];
					$totalkg+=$val['kgwb'];
					$gtotaljjg+=$val['jjgpanen'];
					$gtotalkg+=$val['kgwb'];
					if($baris<$rowspan[$bar['nospb']][$bar['tanggalpanen']]){
						$tab.="</tr><tr class=rowcontent $n>";			
					}
				}
			}else{
				$tab.="<td style=vertical-align:top;text-align:right; colspan=7 rowspan=".$rowspan[$bar['nospb']][$bar['tanggalpanen']].">BKM pemanen belum diinput.</td>";		
			}
			
			$tab.="<tr class=rowcontent name=baris[]>";
			$tab.="<td style=vertical-align:top;text-align:right; colspan=11>Sub Total</td>";		
			$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($totaljjg)."</td>";		
			$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($totalkg)."</td>";		
			$tab.="<td style=vertical-align:top;text-align:right;></td>";		
			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent name=baris[]>";
		$tab.="<td style=vertical-align:top;text-align:right; colspan=7>Grand Total</td>";		
		$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($totaljjgwb)."</td>";		
		$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($totalkgwb)."</td>";		
		$tab.="<td style=vertical-align:top;text-align:right; colspan=2></td>";		
		$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($gtotaljjg)."</td>";		
		$tab.="<td style=vertical-align:top;text-align:right;>".hidezerodecimal($gtotalkg)."</td>";		
		$tab.="<td style=vertical-align:top;text-align:right;></td>";		
		$tab.="</tr>";
		$tab.="</tbody>";
		$tab.="</table>";
		$tab.="<br><label>Kg Pemanen akan lebih kecil dari Kg PKS dikarenakan yang dibayar ke pemanen Kg PKS di kurangi Kg Brondolan dan Kg Sortasi.</label>";
		
		// echo"<pre>";
		// print_r($rowspan);
		// echo"</pre>";
		
		echo $tab;
	break;
}

?>