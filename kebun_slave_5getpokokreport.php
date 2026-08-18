<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$idlaporan=checkPostGet('idlaporan','');
$namalaporan=checkPostGet('namalaporan','');
$kegiatan=checkPostGet('kegiatan','');
$status=checkPostGet('status','');
$ispokok=checkPostGet('ispokok','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		$namalaporanx = trim($namalaporan);
		$namalaporanx = preg_replace('/\s+/', ' ', $namalaporan);

		if($namalaporanx == "")
		{
			exit("Warning : Semua field harus diisi.");
		}

		$str="select * from ".$dbname.".kebun_5getpokokreport where namalaporan='".$namalaporanx."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			exit("Warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.");
		}else{
			$sCek = "SELECT MAX(idlaporan) as idlaporan FROM $dbname.kebun_5getpokokreport";
			$rCek = fetchData($sCek);
			if ($rCek[0]['idlaporan'] == 0 || $rCek[0]['idlaporan'] == null) {
				$no = 1;
			} else {
				$no = ($rCek[0]['idlaporan']+1);
			}
			// echo "<br>".$no;
			$strIns="insert into ".$dbname.".kebun_5getpokokreport (`idlaporan`,`namalaporan`,`status`,`createby`,`createtime`,`updateby`,`updatetime`) 
            values ('".$no."','".$namalaporanx."','".$status."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."',
            '".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
			try{
				$owlPDO->exec($strIns); 
				loadlist();
			}catch (PDOException $e){
				echo"Gagal:Db Error".$strIns."__".$e->getMessage();
				die();
			}
		}
	break;
	
	case'edit':
		$strEdt="update ".$dbname.".kebun_5getpokokreport set status='".$status."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date("Y-m-d H:i:s")."'
        where idlaporan='".$idlaporan."' and namalaporan='".$namalaporan."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5getpokokreport where idlaporan='".$idlaporan."' and status='".$status."'";
		try{
			$owlPDO->exec($str); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$str."__".$e->getMessage();
			die();
		}
	break;

	case 'detailView':
		$optKeg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sKeg="SELECT kodekegiatan, namakegiatan, kelompok FROM $dbname.`setup_kegiatan` WHERE kelompok IN ('TM','TBM','TB') ORDER BY namakegiatan asc, kodekegiatan asc";
		$rKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
		$rKeg->setFetchMode(PDO::FETCH_OBJ);
		while($bKeg=$rKeg->fetch()){
			$optKeg.="<option value='".$bKeg->kodekegiatan."'>".$bKeg->kodekegiatan." - ".$bKeg->namakegiatan." (".$bKeg->kelompok.")</option>";	
		}
		
		$tab="";
		$tab.="<b style='font-size:14px;margin-left:5px;'>DETAIL LAPORAN</b> <br><br>";
		$tab.="<fieldset style='display:inline-block;'>";
			$tab.="<table style='display: inline-block;vertical-align:top'>";	
				$tab.="<tr>";
					$tab.="<td>".$_SESSION['lang']['namakegiatan']."</td>";
					$tab.="<td>:</td>";
					$tab.="<td>
						<select style='width:150px;' class='select2' id='kegiatan'>".$optKeg."</select>
					</td>";
				$tab.="</tr>";

				$tab.="<tr>";
					$tab.="<td>Gunakan Pokok</td>";
					$tab.="<td>:</td>";
					$tab.="<td><input type=checkbox id='ispokok' checked></td>";
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td>
						<input type='hidden' value='".$idlaporan."' id='idlap_dt'>
						<input type='hidden' value='insert_dt' id='proses_dt'>
						<button class=mybutton onclick=\"simpan_dt()\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"cancel_dt()\">".$_SESSION['lang']['cancel']."</button>
					</td>";
            	$tab.="</tr>";
			$tab.="</table>";
		$tab.="</fieldset>";

			$tab.="<table style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['keterangan']."</legend>
							Gunakan Pokok :<br>
							&nbsp;- Ya : Centang CheckBox <input type='checkbox' checked disabled><br>
							&nbsp;- Tidak : Uncentang CheckBox <input type='checkbox' disabled>
						</fieldset>
					</td> 
				</tr>
			</table>";
		
		$tab.="<br><br>";
		echo $tab;

		echo "<div id=contDetailList>";
		echo "</div>";
	break;

	case 'loadDetail':
		$tab="";
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100>";
			$tab.="<thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>";
				$tab.="<th style='text-align:center;'>".$_SESSION['lang']['kodekegiatan']."</th>";
				$tab.="<th style='text-align:center;'>".$_SESSION['lang']['namakegiatan']."</th>";
				$tab.="<th style='text-align:center;'>".$_SESSION['lang']['satuan']."</th>";
				$tab.="<th style='text-align:center;'>".$_SESSION['lang']['kelompok']."</th>";
				$tab.="<th style='text-align:center;'>Gunakan<br>Pokok</th>";
				$tab.="<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>";
				$tab.="</tr>";
			$tab.="</thead>";
			$tab.="<tbody>";
				$nox=0;
				$arrPokok = array ("0"=>"Tidak","1"=>"Ya");
				$stdt = "SELECT * FROM $dbname.kebun_5getpokokreport_dt WHERE idlaporan='".$idlaporan."'";
				$rtdt = fetchData($stdt);
				if (count($rtdt) > 0) {
					foreach ($rtdt as $bdt) {
						$nox++;
						$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:center;'>".$nox."</td>";
						$tab.="<td style='text-align:center;'>".$bdt['kodekegiatan']."</td>";
						$tab.="<td style='text-align:center;'>".getNamaKeg($bdt['kodekegiatan'])."</td>";
						$tab.="<td style='text-align:center;'>".getNamaKeg($bdt['kodekegiatan'],'satuan')."</td>";
						$tab.="<td style='text-align:center;'>".getNamaKeg($bdt['kodekegiatan'],'kelompok')."</td>";
						$tab.="<td style='text-align:center;'>".$arrPokok[$bdt['ispokok']]."</td>";
						$tab.="<td style='text-align:center;'>
							<img src='images/skyblue/edit.png' class='resicon' title='Edit Detail' onclick=\"fillfield_dt('".$bdt['idlaporan']."','".$bdt['kodekegiatan']."','".$bdt['ispokok']."')\">
						</td>";
						$tab.="<td style='text-align:center;'>
							<img src='images/skyblue/delete.png' class='resicon' title='Delete Detail' onclick=\"delete_dt('".$bdt['idlaporan']."','".$bdt['kodekegiatan']."','".$bdt['ispokok']."')\">
						</td>";
						$tab.="</tr>";
					}
				} else {
					$tab.="<tr class=rowcontent>";
						$tab.="<td style='text-align:center;' colspan=6>".$_SESSION['lang']['errdatanotexist']."</td>";
					$tab.="</tr>";
				}
				
			$tab.="</tbody>";
		$tab.="</table>";

		echo $tab;
	break;

	case'insert_dt':
		if($kegiatan == "")
		{
			exit("Warning : Semua field harus diisi.");
		}

		$str="select * from ".$dbname.".kebun_5getpokokreport_dt where idlaporan='".$idlaporan."' AND kodekegiatan='".$kegiatan."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			exit("Warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.");
		}else{
			$strInsdt="insert into ".$dbname.".kebun_5getpokokreport_dt (`idlaporan`,`kodekegiatan`,`ispokok`,`createby`,`createtime`,`updateby`,`updatetime`) 
            values ('".$idlaporan."','".$kegiatan."','".$ispokok."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."',
            '".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
			try{
				$owlPDO->exec($strInsdt);
			}catch (PDOException $e){
				echo"Gagal:Db Error".$strInsdt."__".$e->getMessage();
				die();
			}
		}
	break;

	case'delete_dt':
		$strdt="delete from ".$dbname.".kebun_5getpokokreport_dt where idlaporan='".$idlaporan."' and kodekegiatan='".$kegiatan."' and ispokok='".$ispokok."'";
		try{
			$owlPDO->exec($strdt); 
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strdt."__".$e->getMessage();
			die();
		}
	break;

	case'edit_dt':
		$strEdt="update ".$dbname.".kebun_5getpokokreport_dt set ispokok='".$ispokok."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date("Y-m-d H:i:s")."'
        where idlaporan='".$idlaporan."' and kodekegiatan='".$kegiatan."'";
		try{
			$owlPDO->exec($strEdt);
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
}

function loadlist(){
	global $owlPDO;
	global $dbname;
	global $pages;
	
	echo"<div id=container>
		<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
			   <td style='text-align:center;'>".$_SESSION['lang']['nourut']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['namalaporan']."</td>
			   <td style='text-align:center;'>Status<br>Laporan</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['updateby']."</td>
			   <td colspan='3' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody>";
	
		$limit=15;
		$page=0;
		if(isset($pages)){
			$page=$pages;
			if($page<0){
				$page=0;
			}
		}
		// print_r($pages);
		$no=0;
		$offset=$page*$limit;
		$maxdisplay = ($page * $limit);
		$no = $maxdisplay;
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5getpokokreport";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}

		// $arrPokok = array ("0"=>"Tidak","1"=>"Ya");
		$arrstatus = array("1" => "Aktif","0"=>"Non Aktif");
		
		$strList="select * from ".$dbname.".kebun_5getpokokreport order by namalaporan asc limit ".$offset.",".$limit."";
		$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
		$qrtList->setFetchMode(PDO::FETCH_ASSOC);
		while($rowList=$qrtList->fetch()){
			$no+=1;
			echo"<tr class='rowcontent'>
				<td>".$no."</td>
				<td>".$rowList['namalaporan']."</td>
				<td style='text-align:center;'>".$arrstatus[$rowList['status']]."</td>
				<td style='text-align:center;'>".getNamaKaryawan($rowList['updateby'])."</td>
				<td style='text-align:center;'><img src='images/skyblue/zoom.png' class='resicon' title='View Detail' onclick=\"detailView('".$rowList['idlaporan']."','".$rowList['status']."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList['namalaporan']."','".$rowList['idlaporan']."','".$rowList['status']."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList['idlaporan']."','".$rowList['status']."')\"></td>
			</tr>";
		}

		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}

		echo"<tr class=rowheader><td colspan=6 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />";

			if ($page == '0') {
				echo"<button class=mybutton disabled>".$_SESSION['lang']['pref']."</button>";
			} else {
				echo"<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}

			if (($page+1) == $totrows) {
				echo"<button class=mybutton disabled>".$_SESSION['lang']['lanjut']."</button>";
			} else {
				echo"<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
			
			echo"</td>
		</tr>";
	echo"</tbody></table></div>";
}
?>