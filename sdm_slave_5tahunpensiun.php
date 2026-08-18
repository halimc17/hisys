<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tahun=checkPostGet('tahun','');
$usiapensiun=checkPostGet('usiapensiun','');
$method=checkPostGet('method','');

switch ($method) {

    case 'insert':

        $str = "select tahun from " . $dbname . ".sdm_5tahunpensiun  where tahun='".$tahun."'";
        $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $d = $n->fetch();
        $jmlh=$d['tahun'];

        if ($jmlh>0){
            exit('warning : Data already exist.');
        }

        $str="insert into ".$dbname.".sdm_5tahunpensiun (tahun,usiapensiun,updateby,createdby)
            values ('" . $tahun . "','" . $usiapensiun . "','" . $_SESSION['standard']['userid'] . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':
        $str = "update " . $dbname . ".sdm_5tahunpensiun set updateby='" . $_SESSION['standard']['userid'] . "',usiapensiun='" . $usiapensiun . "'
             where tahun='" . $tahun . "'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
            
        break;

    case'loadData':

        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5tahunpensiun"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=8>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $str = "select * from " . $dbname . ".sdm_5tahunpensiun  limit " . $offset . "," . $limit . "";
            $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $n->setFetchMode(PDO::FETCH_ASSOC);
            $no = $maxdisplay;
            while ($d = $n->fetch()) {
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td align=center>" . $d['tahun'] . "</td>";
                $tab.="<td align=center>" . $d['usiapensiun'] . "</td>";
                $tab.="<td align=center>" . (isset($nmKar[$d['createdby']]) ? $nmKar[$d['createdby']] : '') . "</td>";
                $tab.="<td align=center>" . $d['cretaetime'] . "</td>";
                $tab.="<td align=center>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
                $tab.="<td align=center>" . $d['updatetime'] . "</td>";
                $tab.="<td align=center>
                        <img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('" . $d['tahun'] . "',"
                ."'".$d['usiapensiun']."');\">
                                </td>";
                $tab.="</tr>"; 
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=8 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        
        echo $tab."####".$footd;
    break;

    default:
}
?>
