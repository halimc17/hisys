<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


///$arr="##thnBudget##kdGol##ungSaku##ungMkn##htel##method";
$method = checkPostGet('method','');
$thnBudget = checkPostGet('thnBudget','');
$kdGol = checkPostGet('kdGol','');
$ungSaku = checkPostGet('ungSaku','');
$ungMkn = checkPostGet('ungMkn','');
$htel = checkPostGet('htel','');
$optGol = makeOption($dbname, "sdm_5golongan", "kodegolongan,namagolongan");

$where = " tahunbudget='" . $thnBudget . "' and golongan='" . $kdGol . "' ";

switch ($method) {
    case'insert':
        if (($thnBudget == '') || ($kdGol == '')) {
            echo"warning:Field tidak boleh kosong";
            exit();
        }
        $sCek = "select tahunbudget from " . $dbname . ".sdm_5sakupjd where " . $where . "";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning:Data sudah ada";
            exit();
        } else {
            $ungSaku == '' ? $ungSaku = 0 : $ungSaku = $ungSaku;
            $ungMkn == '' ? $ungMkn = 0 : $ungMkn = $ungMkn;
            $htel == '' ? $htel = 0 : $htel = $htel;


            $sIns = "insert into " . $dbname . ".sdm_5sakupjd (tahunbudget, golongan, uangsaku, uangmakan, hotel) values 
                        ('" . $thnBudget . "','" . $kdGol . "','" . $ungSaku . "','" . $ungMkn . "','" . $htel . "')";
			try{
				$owlPDO->exec($sIns); 
			}
			catch (PDOException $e){
				echo"Gagal" . $e->getMessage();
				die();
			}
        }
        break;
		
    case'loadData':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $no = 0;
		$addKond='';
        if ($thnBudget != '') {
            $addKond.=" and tahunbudget='" . $thnBudget . "'";
        }
        if ($kdGol != '') {
            $addKond.=" and golongan='" . $kdGol . "'";
        }

        $sql2 = "SELECT count(*) as jmlhrow FROM " . $dbname . ".sdm_5sakupjd where tahunbudget!='' " . $addKond . " order by tahunbudget desc ";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        if ($jlhbrs != 0) {
            $str = "select * from " . $dbname . ".sdm_5sakupjd where tahunbudget!='' " . $addKond . " order by tahunbudget desc limit " . $offset . "," . $limit . "";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $no+=1;
                echo"<tr class=rowcontent>
		<td>" . $no . "</td>
		<td>" . $bar['tahunbudget'] . "</td>
		<td>" . $optGol[$bar['golongan']] . "</td>
		<td align=right>" . number_format($bar['uangsaku'], 2) . "</td>
                <td align=right>" . number_format($bar['uangmakan'], 2) . "</td>
		<td align=right>" . number_format($bar['hotel'], 2) . "</td>                
		<td>
			  <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['tahunbudget'] . "','" . $bar['golongan'] . "');\"> 
			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar['tahunbudget'] . "','" . $bar['golongan'] . "');\">
		  </td>
		</tr>";
            }
            echo"
                <tr><td colspan=8 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                <button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        } else {
            echo"<tr class=rowcontent><td colspan=8>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        break;
    case'update':
        if (($thnBudget == '') || ($kdGol == '')) {
            echo"warning:Field tidak boleh kosong";
            exit();
        } else {
            $ungSaku == '' ? $ungSaku = 0 : $ungSaku = $ungSaku;
            $ungMkn == '' ? $ungMkn = 0 : $ungMkn = $ungMkn;
            $htel == '' ? $htel = 0 : $htel = $htel;

            $sUpd = "update " . $dbname . ".sdm_5sakupjd set `uangsaku`='" . $ungSaku . "',`uangmakan`='" . $ungMkn . "',`hotel`='" . $htel . "' where " . $where . "";
			try{
				$owlPDO->exec($sUpd); 
			}
			catch (PDOException $e){
				echo"Gagal" . $e->getMessage();
				die();
			}
        }
        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".sdm_5sakupjd  where " . $where . "";
		try{
			$owlPDO->exec($sDel); 
		}
		catch (PDOException $e){
			echo"Gagal" . $e->getMessage();
			die();
		}
        break;
    case'getData':
        $sDt = "select * from " . $dbname . ".sdm_5sakupjd where " . $where . "";
		$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDet = $qDt->fetch();
        //tahunbudget, golongan, tujuan, ticket, taxiboat, airporttax, visa, bylain
        echo $rDet['tahunbudget'] . "###" . $rDet['golongan'] . "###" . $rDet['uangsaku'] . "###" . $rDet['uangmakan'] . "###" . $rDet['hotel'];
        break;
    default:
        break;
}
?>