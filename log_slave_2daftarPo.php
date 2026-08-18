<?php
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
$method = $_POST['method'];
if($method==''){
	$method = $_GET['proses'];
}
switch ($method) {
    case 'list_new_data':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		 
		$where = "";
		if($_POST['txtSearch']!=''){
			$where.=" and nopo LIKE  '%" . $_POST['txtSearch'] . "%'";
		}
		//exit($_POST['tgl_carismp']);
		if($_POST['tgl_carismp']==''){
			$where.=" and tanggal LIKE '" . tanggalsystemn($_POST['tglCari']) . "'";
		}else if(($_POST['tglCari']!='')&&($_POST['tgl_carismp']!='')){
			//exit('11');
			if(tanggalsystemn($_POST['tglCari'])>tanggalsystemn($_POST['tgl_carismp'])){
				exit('warning: Cek kembali tanggal PO yang diinputkan');
			}
			$where.=" and tanggal between '".tanggalsystemn($_POST['tglCari'])."' and '".tanggalsystemn($_POST['tgl_carismp'])."'";
		}
		if($_POST['lokPus']!=''){
			$where.= " and lokalpusat='".$_POST['lokPus']."'";
		}
		if($_POST['statr']!=''){
			if($_POST['statr']==1){
				$where.=" and stat_release='1' and (keteranganclose like '%,Tutup By System%' or keteranganclose is null or keteranganclose = '') and (keterangan='' or keterangan is null)";	
			}
			if($_POST['statr']==2){
				$where.=" and (stat_release=0 or stat_release is null or stat_release='') and (closed='0' or closed is null or closed='')";	
			}
			if($_POST['statr']==3){
				$where.=" and (closed='1') and (keteranganclose like '%,tanggal tutup : %')";	
			}
			if($_POST['statr']==4){
				$where.=" and (closed='1') and (keterangan like '%,tanggal tutup : %')";	
			}
		}
		
        if(($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')) {
            $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
			$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
			$qPt->setFetchMode(PDO::FETCH_ASSOC);
            $rPt = $qPt->fetch();
            // $where.= " and kodeorg='" . $rPt['induk'] . "' and lokalpusat=1";
        }

		//$sql2 = "SELECT count(*) as jmlhrow FROM " . $dbname . ".log_poht where statuspo>1   " . $addTmbh . " " . $where . " order by tanggal desc ";
        $sql2 = "SELECT count(*) as jmlhrow FROM " . $dbname . ".log_poht where 1=1 " . $where . " order by tanggal desc ";
        //exit ('ERROR'.$sql2);
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		$no=$offset;	
        $strx = "SELECT * FROM " . $dbname . ".log_poht where 1=1 " . $where . " order by tanggal desc limit " . $offset . "," . $limit . "";
		echo $strx;
        // exit('warning : '.$strx);
		$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$kodeunit = $bar['kodeunit'];
			$spr = "select * from  " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeunit . "' and induk='" . $kodeorg . "'";
			//exit($spr);
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rep->fetch();
			$no+=1;
			if ($bar['stat_release'] == 1)
				$st = "Release";
			else{
				if($bar['closed']=='1'){
					if(strpos($bar['keteranganclose'], ",tanggal tutup : ")){
						$st = "Become Out Standing";
					}
					if(strpos($bar['keterangan'], ",tanggal tutup : ")){
						$st = "Cancel";
					}
				}else{
					$st = "Unrelease";
				}
			}
		/*else
		{
			$st=@$stPo[$bar['statuspo']];
		}*/


				//$st = $_SESSION['lang']['un_release_po'];
			echo"<tr class=rowcontent id='tr_" . $no . "'>
					  <td align=center>" . $no . "</td>
					  <td id=td_" . $no . ">" . $bar['nopo'] . "</td>
					  <td>" . tanggalnormal($bar['tanggal']) . "</td>
					  <td>" . $bas->namaorganisasi . "</td>
					  <td>" . @$st . "</td>";
			$sql = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['persetujuan1'] . "'";
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$yrs = $query->fetch();
			echo"<td align=center>" . $yrs['namakaryawan'] . "</td>";
			echo"<td>
			  <button class=mybutton onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po_tanpaharga',event)\">".$_SESSION['lang']['print']."</button>
			</td></tr>";
		}
		echo"<tr><td colspan=7 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
		break;
		case 'excel':
		$_POST=$_GET;
		$where = "";
		if($_POST['txtSearch']!=''){
			$where.=" and nopo LIKE  '%" . $_POST['txtSearch'] . "%'";
		}
		if($_POST['tglCari']!=''){
			$where.=" and tanggal LIKE '" . tanggalsystemn($_POST['tglCari']) . "'";
		}else if(($_POST['tglCari']!='')&&($_POST['tgl_carismp']!='')){
			if(tanggalsystemn($_POST['tglCari'])>tanggalsystemn($_POST['tgl_carismp'])){
				exit('warning: Cek kembali tanggal PO yang diinputkan');
			}
			$where.=" and tanggal between '".tanggalsystemn($_POST['tglCari'])."' and '".tanggalsystemn($_POST['tgl_carismp'])."'";
		}
		if($_POST['lokPus']!=''){
			$where.= " and lokalpusat='".$_POST['lokPus']."'";
		}
		if($_POST['statr']!=''){
			if($_POST['statr']==0){
				$where.= " and (stat_release is null or stat_release='".$_POST['statr']."')";	
			}else{
				$where.= " and stat_release='".$_POST['statr']."'";
			}
			
		}
        if(($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')) {
            $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
			$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
			$qPt->setFetchMode(PDO::FETCH_ASSOC);
            $rPt = $qPt->fetch();
            $where.= " and kodeorg='" . $rPt['induk'] . "' and lokalpusat=1";
        }
        $strx = "SELECT * FROM " . $dbname . ".log_poht where 1=1 " . $where . " order by tanggal desc";
        $rdata=fetchdata($strx);
        $stream="<table class=\"sortable\" cellspacing=\"1\" border=\"1\">
			  <thead><tr class=rowheader>
			  <td bgcolor=#CCCCCC  align=center>No.</td>
			  <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['nopo']."</td>
			  <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['tgl_po']."</td> 
			  <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['namaorganisasi']."</td>
			  <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['status']."</td>
			  <td bgcolor=#CCCCCC  align=center>".$_SESSION['lang']['tandatangan']."</td>
			  </tr></thead><tbody>";
	    $arrStat=array("0"=>$_SESSION['lang']['un_release_po'],"1"=>$_SESSION['lang']['release_po']);
        foreach($rdata as $row=>$bar){
        	$no+=1;
        	$kodeorg = $bar['kodeorg'];
			$spr = "select namaorganisasi from  " . $dbname . ".organisasi where  kodeorganisasi='".$kodeorg."'";
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rep->fetch();
        	$stream.="<tr>";
        	$stream.="<td align=center>" . $no . "</td>
					  <td>" . $bar['nopo'] . "</td>
					  <td>" . $bar['tanggal'] . "</td>
					  <td>" . $bas->namaorganisasi . "</td>
					  <td>" . $arrStat[intval($bar['stat_release'])] . "</td>";
			$sql = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['persetujuan1'] . "'";
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$yrs = $query->fetch();
			$stream.="<td align=center>" . $yrs['namakaryawan'] . "</td>";
        	$stream.="</tr>";
        }
        $stream.="</tbody></table>";
        $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "daftar_po_".$tglSkrg;
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
        break;

/*		
    case 'loadData':
        $addTmbh = "";
        if (($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')) {
            $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
			$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
			$qPt->setFetchMode(PDO::FETCH_ASSOC);
            $rPt = $qPt->fetch();
            $addTmbh = " and kodeorg='" . $rPt['induk'] . "'";
        }


        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);

        $sql2 = "select count(*) as jmlhrow from " . $dbname . ".log_poht where stat_release=1 " . $addTmbh . "  ORDER BY nopo DESC";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
        // stat_release='1'
        $str = "SELECT * FROM " . $dbname . ".log_poht where stat_release=1  " . $addTmbh . "  ORDER BY tanggal DESC limit " . $offset . "," . $limit . "";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		
		$no = 0;
		$no=$maxdisplay;
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$spr = "select * from  " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorg . "' or induk='" . $kodeorg . "'";
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rep->fetch();
			$no+=1;
			if ($bar['stat_release'] == 1)
				$st = $_SESSION['lang']['release_po'];
			else
				$st = $_SESSION['lang']['un_release_po'];
			echo"<tr class=rowcontent id='tr_" . $no . "'>
			  <td align=center>" . $no . "</td>
			  <td id=td_" . $no . ">" . $bar['nopo'] . "</td>
			  <td>" . tanggalnormal($bar['tanggal']) . "</td>
			  <td>" . $bas->namaorganisasi . "</td>
			  <td>" . $st . "</td>";
			$sql = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['persetujuan1'] . "'";
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$yrs = $query->fetch();
			echo"<td align=center>" . $yrs['namakaryawan'] . "</td>";
			?>
			<td>			
				<button class=mybutton onclick="masterPDF('log_poht', '<?php echo $bar['nopo'] ?>', '', 'log_slave_print_detail_po', event);" ><? echo $_SESSION['lang']['print'] ?>
				</button>
			</td>

			<?php
			echo"</tr>";
		} echo"<tr><td colspan=8 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
			<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
        break;
*/
    default:
        break;
}
?>