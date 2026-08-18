<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$karyawanid = checkPostGet('karyawanid', '');
$jenis = checkPostGet('jenis', '');
$nilai = checkPostGet('nilai', '');
$method = checkPostGet('method', '');

switch ($method) {
    case 'loaddata':
        getContainer();
        break;

    case 'insert':
        if ($jenis == '' || $nilai == ''||$unit == '' || $periode == ''|| $karyawanid == '') {
            echo "Gagal : Semua field harus diisi.";
            exit();
        }
        if($jenis=='PPH21')
        {
            echo "Gagal : Untuk PPH 21 tidak bisa di input dari transaksi ini, silahkan proses pph 21.";
            exit();
        }
        $str = "select * from " . $dbname . ".sdm_pph21 wher karyawanid ='" . $karyawanid . "' and kodeorg ='" . $unit . "' and periode ='" . $periode . "' and tipepph ='" . $jenis . "' ";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numRows=owlBaris($qry);
        if ($numRows >= 1) {
            echo "Warning: Kode training sudah pernah terdaftar sebelumnya.";
        } else {
            $strIns = "insert into " . $dbname . ".sdm_pph21 (kodeorg,periode,tipepph,karyawanid,nilai) 
				values ('" . $unit . "','" . $periode . "','" . $jenis . "','" . $karyawanid . "','" . $nilai . "')";
            try{
				$owlPDO->exec($strIns); 
				getContainer();
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
        }
        break;

    case 'gantix':
        $optper=$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' group by periode order by periode desc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
        }

        $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' order by namakaryawan asc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optkary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
        }

        echo $optper.'###'.$optkary;
        break;

    case 'edit':
        if ($jenis == '' || $nilai == ''||$unit == '' || $periode == ''|| $karyawanid == '') {
            echo "Gagal : Semua field harus diisi.";
            exit();
        }

        if($jenis=='PPH21')
        {
            $str = "update " . $dbname . ".sdm_gaji set jumlah='" . $nilai . "' where karyawanid ='" . $karyawanid . "' and kodeorg ='" . $unit . "' and periodegaji ='" . $periode . "' and idkomponen='42' ";
            try{
                $owlPDO->exec($str); 
                getContainer();
            }
            catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
        }
        else
        {
            $str = "update " . $dbname . ".sdm_pph21 set nilai='" . $nilai . "' where karyawanid ='" . $karyawanid . "' and kodeorg ='" . $unit . "' and periode ='" . $periode . "' and tipepph ='" . $jenis . "' ";
            try{
    			$owlPDO->exec($str); 
    			getContainer();
    		}
    		catch (PDOException $e){
    			echo "DB Error : " . $e->getMessage();
    			die();
    		}

        }
        break;

}

function getContainer() {
    global $conn;
    global $dbname;
    global $owlPDO;


        $optnma=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
        $optorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');


        $str = "select count(*) as jlhbrs from  
                (
                select kodeorg,periode,karyawanid,tipepph,nilai from  " . $dbname . ".sdm_pph21 
                union 
                select kodeorg,periodegaji as periode,karyawanid,'PPH21' as tipepph,jumlah as nilai from  " . $dbname . ".sdm_gaji where idkomponen='42'
                )d ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $jlhbrs = $bar->jlhbrs;
        }
        //==================
        $limit=20;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }


        $offset = $page * $limit;


        $str = "select kodeorg,periode,karyawanid,tipepph,nilai from  
                (
                select kodeorg,periode,karyawanid,tipepph,nilai from  " . $dbname . ".sdm_pph21 
                union 
                select kodeorg,periodegaji as periode,karyawanid,'PPH21' as tipepph,jumlah as nilai from  " . $dbname . ".sdm_gaji where idkomponen='42'
                )d order by periode desc,karyawanid asc limit " . $offset . ",20";
	    $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	    $qry->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while ($res = $qry->fetch()) {
        $no+=1;
        $opt = '';
        $bg = "class=rowcontent";
        echo"<tr " . $bg . ">
					<td style='text-align:right;'>" . $no . "</td>
                    <td>" . $optorg[$res->kodeorg] . "</td>
					<td>" . $res->periode . "</td>
					<td>" . $optnma[$res->karyawanid] . "</td>
                    <td>" . $res->tipepph . "</td>
                    <td>" . $res->nilai . "</td>";
        echo"<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('" . $res->kodeorg . "','" . $res->periode . "','" . $res->karyawanid . "','" . $res->tipepph . "','" . $res->nilai . "')\"></td>
				</tr>";
        }
        echo"<tr><td colspan=7 align=center>
       " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
       <br>
       <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
       <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
       </td>
       </tr>";
}
?>