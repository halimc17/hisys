<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
require_once('master_validation.php');


			$tglskrg = date('Y-m-d');
			//$tglskrg = '2021-09-07';
			$arrfp=array();
			$str="select * from ".$dbname.".att_log where scan_date like '".$tglskrg."%' and inoutmode=1";
			$res=fetchdata($str);
			foreach($res as $key=>$val){
				$kary[$val['pin']]=$val['pin'];
				$tglmasuk[$val['pin']]=$val['scan_date'];
			}

			$strx="select * from ".$dbname.".att_log where scan_date like '".$tglskrg."%' and inoutmode=2";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				$tglkeluar[$valx['pin']]=$valx['scan_date'];	
			}


			$OptNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
			$optunit = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');
			$optnik = makeOption($dbname,'datakaryawan','karyawanid,nik');
				echo"<table class=sortable cellspacing=1 border=0>";
				echo "<tr>";
				echo "<td>Data Absen Finger Hari ini</td>";
				echo "</tr>";
				echo "<tr>";
				echo "<td>Tanggal : ".substr($tglskrg,0,10)."</td>";
				echo "</tr>";
				echo"</table><br>";

				echo"<table class=sortable cellspacing=1 border=1>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>No.</td>
					<td style='text-align:center'>".$_SESSION['lang']['kodeorg']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['nik']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tanggalmasuk']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tanggalkeluar']."</td>
				</tr>
				</thead>
				<tbody>";
				if (count(@$kary)==0) {
					echo"<tr class=rowcontent>
						<td style='text-align:center' colspan=6><b>Belum ada Fingger</b></td>

					</tr>";
				}
				else
				{
					foreach($kary as $key1=>$val1){
						$no+=1;
						echo"<tr class=rowcontent>
						<td style='text-align:right'>".$no."</td>
						<td style='text-align:center'>".$optunit[$val1]."</td>
						<td id='tdabsen_".$no."'>".$optnik[$val1]."</td>
						<td>".$OptNama[$val1]."</td>
						<td>".$tglmasuk[$val1]."</td>
						<td>".$tglkeluar[$val1]."</td>
						</tr>";
					}
				}
				
			
			
			echo"</tbody></table></div>";
?>