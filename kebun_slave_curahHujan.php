<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
// error_reporting(1);

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses = checkPostGet('proses', '');
$kdORg = checkPostGet('kdOrg', '');
$daTpagi = checkPostGet('daTpagi', '');
$daTsiang = checkPostGet('daTsiang', '');
$daTsore = checkPostGet('daTsore', '');
$daTmalam = checkPostGet('daTmalam', '');
$note = checkPostGet('note', '');
$daTtgl = tanggalsystemn(checkPostGet('daTtgl', ''));
$lokasi = $_SESSION['empl']['lokasitugas'];
$jampagi      =checkPostGet('jampagi','');
$mntpagi      =checkPostGet('mntpagi','');
$jamsiang     =checkPostGet('jamsiang','');
$mntsiang     =checkPostGet('mntsiang','');
$jamsore      =checkPostGet('jamsore','');
$mntsore      =checkPostGet('mntsore','');


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
		

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_curahhujan where `kodeorg` like  '" . $lokasi . "%' order by `tanggal` desc";
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $str = "select * from " . $dbname . ".kebun_curahhujan where `kodeorg` like '" . $lokasi . "%' order by tanggal desc limit " . $offset . "," . $limit . "";
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
				<td id='kpsits_" . $no . "'>" . tanggalnormal($bar->tanggal) . "</td>
				<td align=right id='strt_" . $no . "'>" . $bar->pagi . "</td>
                <td align=right id='strtjam_" . $no . "'>" . $bar->jampagi . "</td>
                <td align=right id='siang_" . $no . "'>" . $bar->siang . "</td>
                <td align=right id='siangjam_" . $no . "'>" . $bar->jamsiang . "</td>
                <td align=right id='end_" . $no . "'>" . $bar->sore . "</td>
                <td align=right id='endjam_" . $no . "'>" . $bar->jamsore . "</td>
				<td align=right style='display:none' id='mlm_" . $no . "'>" . $bar->malam . "</td>
				<td id='tglex_" . $no . "'>" . $bar->catatan . "</td>
				<td align=center width=30px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "');\">
				</td>
				<td align=center width=30px>
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "');\">
				</td>
				<td align=center width=30px>
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "',event);\"></td>
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
        if (($kdORg == '') || ($daTpagi == '') || ($daTsiang == '') || ($daTsore == '') || ($daTtgl == '--') ) {
            echo"warning:Please Complete The Form";
            exit();
        }
        $tglCek = explode("-", $_POST['daTtgl']);
        $thnSkrng = date("Y");
        $blnSkrng = date("m");

        $sCek = "select kodeorg,tanggal from " . $dbname . ".kebun_curahhujan where kodeorg='" . $kdORg . "' and tanggal='" . $daTtgl . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek < 1) {

            $jam=$daTtgl." ".addZero($jampagi,2).":".addZero($mntpagi,2).":00";
            $jam2=$daTtgl." ".addZero($jamsiang,2).":".addZero($mntsiang,2).":00";
            $jam3=$daTtgl." ".addZero($jamsore,2).":".addZero($mntsore,2).":00";

            $sIns = "insert into " . $dbname . ".kebun_curahhujan (kodeorg, tanggal, pagi, siang, sore, malam, catatan, jampagi, jamsiang, jamsore) values ('" . $kdORg . "','" . $daTtgl . "','" . $daTpagi . "','" . $daTsiang . "','" . $daTsore . "','" . $daTmalam . "','" . ucwords($note) . "','".$jam."','".$jam2."','".$jam3."')";
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
        $sql = "select catatan,pagi,siang,sore,malam,jampagi, jamsiang, jamsore from " . $dbname . ".kebun_curahhujan where kodeorg='" . $kdORg . "' and tanggal='" . $daTtgl . "'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        $res = $query->fetch();
        $jam=substr($res[0]['jampagi'],11,2);
        $minutes = substr($res[0]['jampagi'],14, 2);
        $jam2=substr($res[0]['jamsiang'],11,2);
        $minutes2 = substr($res[0]['jamsiang'],14, 2);
        $jam3=substr($res[0]['jamsore'],11,2);
        $minutes3 = substr($res[0]['jamsore'],14, 2);

        echo $res['catatan'] . "###" . $res['pagi'] . "###" . $res['sore'] . "###" . $res['malam']. "###" . $res['siang']. "###" . $jam. "###" . $jam2. "###" . $jam3 ."###". $minutes ."###". $minutes2 ."###". $minutes3;
        break;
    case'update':
        if (($kdORg == '') || ($daTpagi == '') || ($daTsiang == '') || ($daTsore == '') || ($daTtgl == '--')) {
            echo"warning:Please Complete The Form";
            exit();
        }

        $jam=$daTtgl." ".addZero($jampagi,2).":".addZero($mntpagi,2).":00";
		$jam2=$daTtgl." ".addZero($jamsiang,2).":".addZero($mntsiang,2).":00";
		$jam3=$daTtgl." ".addZero($jamsore,2).":".addZero($mntsore,2).":00";

        $sUpd = "update " . $dbname . ".kebun_curahhujan set  pagi='" . $daTpagi . "',siang='" . $daTsiang . "', sore='" . $daTsore . "', malam='" . $daTmalam . "', catatan='" . $note . "', jampagi='".$jam."', jamsiang='".$jam2."', jamsore='".$jam3."' where  kodeorg='" . $kdORg . "' and tanggal='" . $daTtgl . "'";
        // exit('warning:'.$sUpd);
        try{
			$owlPDO->exec($sUpd); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}

        break;

    case'delData':
        $sDel = "delete from " . $dbname . ".kebun_curahhujan where  kodeorg='" . $kdORg . "' and tanggal='" . $daTtgl . "'";
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

            // if (($kdORg != '') && ($daTtgl != '')) {
                // $where = " kodeorg='" . $kdORg . "' and tanggal='" . $daTtgl . "'";
            // } elseif ($kdORg != '') {
                // $where = " kodeorg='" . $kdORg . "'";
            // } elseif ($daTtgl != '') {
                // $where = " tanggal='" . $daTtgl . "' and kodeorg = '" . $lokasi . "'";
            // } elseif (($kdORg == '') && ($daTtgl == '')) {
                // echo"warning:Please Insert Data";
                // exit();
            // }
			
			if($kdORg!=''){
				$where.=" and kodeorg='" . $kdORg . "'";
			}
			if($daTtgl!=''){
				$where.=" and tanggal='" . $daTtgl . "'";
			}
			
			
			
			//exit("Error:$where");
			
			
            $sCek = "select * from " . $dbname . ".kebun_curahhujan where 1=1 " . $where . "";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			// $rCek=owlBaris($qCek);
			$rCek=$qCek->rowCount();
			
            if ($rCek > 0) {
                $ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_curahhujan where 1=1 " . $where . " order by `tanggal` desc";
				$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_OBJ);
                while ($jsl = $query2->fetch()) {
                    $jlhbrs = $jsl->jmlhrow;
                }
//exit("Error:A");

                $str = "select * from " . $dbname . ".kebun_curahhujan where 1=1 " . $where . " order by tanggal desc limit " . $offset . "," . $limit . "";
				
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
						<td id='kpsits_" . $no . "'>" . tanggalnormal($bar->tanggal) . "</td>
						<td align=right id='strt_" . $no . "'>" . $bar->pagi . "</td>
                        <td align=right id='strtpagi_" . $no . "'>" . $bar->jampagi . "</td>
						<td align=right id='siang_" . $no . "'>" . $bar->siang . "</td>
                        <td align=right id='strtsiang_" . $no . "'>" . $bar->jamsiang . "</td>
						<td align=right id='end_" . $no . "'>" . $bar->sore . "</td>
                        <td align=right id='strtsore_" . $no . "'>" . $bar->jamsore . "</td>
						<td align=right style='display:none' id='mlm_" . $no . "'>" . $bar->malam . "</td>
						<td id='tglex_" . $no . "'>" . $bar->catatan . "</td>
						<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "');\">
                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"printPDF('" . $bar->kodeorg . "','" . tanggalnormal($bar->tanggal) . "',event);\"></td>
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