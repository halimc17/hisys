<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
error_reporting(1);

$proses = checkPostGet('proses', '');
$kdORg = checkPostGet('kdOrg', '');
$daTpagi = checkPostGet('daTpagi', '');
$daTsiang = checkPostGet('daTsiang', '');
$daTsore = checkPostGet('daTsore', '');
$daTmalam = checkPostGet('daTmalam', '');
$note = checkPostGet('note', '');
$daTtgl = checkPostGet('daTtgl', '');
$lokasi = $_SESSION['empl']['lokasitugas'];


switch ($proses) {
    case'LoadData':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_defisitair where `kodeorg` like  '" . $lokasi . "%' order by `periode` desc";
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $str = "select * from " . $dbname . ".kebun_defisitair where `kodeorg` like '" . $lokasi . "%' order by periode desc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);

        $no = 0;
		$no=$maxdisplay;
        while ($bar = $res->fetch()) {
            $spr = "select namaorganisasi from  " . $dbname . ".organisasi where  kodeorganisasi='" . $bar->kodeorg . "'";
            $rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rep->fetch();
            $no+=1;

            //echo $minute_selesai; exit();
            echo"<tr class=rowcontent id='tr_" . $no . "'>
				<td align=center>" . $no . "</td>
				<td id='nmorg_" . $no . "'>" . $bas->namaorganisasi . "</td>
				<td id='kpsits_" . $no . "'>" . $bar->periode . "</td>
				<td align=right id='strt_" . $no . "'>" . $bar->mm . "</td>
				<td hidden align=right id='siang_" . $no . "'>" . $bar->siang . "</td>
				<td hidden align=right id='end_" . $no . "'>" . $bar->sore . "</td>
				<td hidden align=right style='display:none' id='mlm_" . $no . "'>" . $bar->malam . "</td>
				<td id='tglex_" . $no . "'>" . $bar->catatan . "</td>
				<td align=center width=30px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodeorg . "','" . $bar->periode . "');\">
				</td>
				<td align=center width=30px>
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . $bar->kodeorg . "','" . $bar->periode . "');\">
				</td>
				<td hidden align=center width=30px>
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('" . $bar->kodeorg . "','" . $bar->periode . "',event);\"></td>
			</tr>";
        }
        echo"
		<tr><td colspan=9 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";

        break;

    case'insert':
        if (($kdORg == '') || ($daTpagi == '')) {
            echo"warning:Please Complete The Form";
            exit();
        }
        // $tglCek = explode("-", $_POST['daTtgl']);
        // $thnSkrng = date("Y");
        // $blnSkrng = date("m");

        $sCek = "select kodeorg,periode from " . $dbname . ".kebun_defisitair where kodeorg='" . $kdORg . "' and periode='" . $daTtgl . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek < 1) {
            $sIns = "insert into " . $dbname . ".kebun_defisitair (kodeorg,periode,mm,catatan) values ('" . $kdORg . "','" . $daTtgl . "','" . $daTpagi . "','" . ucwords($note) . "')";
            try{
				$owlPDO->exec($sIns); 
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
			}
        }
        else {
            echo"warning:Data Already Entry";
            exit();
        }
        break;

    case'showData':
        $sql = "select catatan,mm from " . $dbname . ".kebun_defisitair where kodeorg='" . $kdORg . "' and periode='" . $daTtgl . "'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        $res = $query->fetch();
        echo $res['catatan'] . "###" . $res['mm'];

    break;
    case'update':
        if ($daTpagi == '') {
            echo"warning:Please Complete The Form";
            exit();
        }
        $sUpd = "update " . $dbname . ".kebun_defisitair set  mm='" . $daTpagi . "', catatan='" . $note . "' where  kodeorg='" . $kdORg . "' and periode='" . $daTtgl . "'";
        try{
			$owlPDO->exec($sUpd); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}

        break;

    case'delData':
        $sDel = "delete from " . $dbname . ".kebun_defisitair where  kodeorg='" . $kdORg . "' and periode ='" . $daTtgl . "'";
        try{
			$owlPDO->exec($sDel); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
        break;

    case'CekData':
        if (!preg_match("/e$/i", $lokasi)) {
            echo"warning:You Not In Estate";
            exit();
        }
        break;

    case'cariData':
        if (preg_match("/e$/i", $lokasi)) {
            $limit = 20;
            $page = 0;
            if (isset($_POST['page'])) {
                $page = $_POST['page'];
                if ($page < 0)
                    $page = 0;
            }
            $offset = $page * $limit;
			$maxdisplay=($page*$limit);

			
			if($kdORg!=''){
				$where.=" and kodeorg='" . $kdORg . "'";
			}
			if($daTtgl!=''){
				$where.=" and periode='" . $daTtgl . "'";
			}
			
			
			
			//exit("Error:$where");
			
			
            $sCek = "select * from " . $dbname . ".kebun_defisitair where 1=1 " . $where . "";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			// $rCek=owlBaris($qCek);
			$rCek=$qCek->rowCount();
			
            if ($rCek > 0) {
                $ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_defisitair where 1=1 " . $where . " order by `periode` desc";
				$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_OBJ);
                while ($jsl = $query2->fetch()) {
                    $jlhbrs = $jsl->jmlhrow;
                }

                $str = "select * from " . $dbname . ".kebun_defisitair where 1=1 " . $where . " order by periode desc limit " . $offset . "," . $limit . "";
				
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				
				$no = 0;
				$no=$maxdisplay;
				while ($bar = $res->fetch()) {
					$spr = "select * from  " . $dbname . ".organisasi where  kodeorganisasi='" . $bar->kodeorg . "'";
					$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
					$rep->setFetchMode(PDO::FETCH_OBJ);
					$bas = $rep->fetch();
					$no+=1;

					//echo $minute_selesai; exit();
					echo"<tr class=rowcontent id='tr_" . $no . "'>
						<td align=center>" . $no . "</td>
						<td id='nmorg_" . $no . "'>" . $bas->namaorganisasi . "</td>
						<td id='kpsits_" . $no . "'>" . $bar->periode . "</td>
						<td align=right id='strt_" . $no . "'>" . $bar->mm . "</td>
						<td hidden align=right id='siang_" . $no . "'>" . $bar->siang . "</td>
						<td hidden align=right id='end_" . $no . "'>" . $bar->sore . "</td>
						<td hidden align=right style='display:none' id='mlm_" . $no . "'>" . $bar->malam . "</td>
						<td id='tglex_" . $no . "'>" . $bar->catatan . "</td>
						<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodeorg . "','" . $bar->periode . "');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . $bar->kodeorg . "','" . $bar->periode . "');\">
					</tr>";
				}
				echo"
				<tr class=rowheader><td colspan=7 align=center>
				" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
				<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
				<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
				</td>
				</tr>";
            } else {
                echo"<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
            }
        } else {
            echo"warning:You Not In Estate";
            exit();
        }

        break;
    default:
        break;
}
?>