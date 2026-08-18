<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$tanggal=checkPostGet('tanggal','');
$keterangan=checkPostGet('keterangan','');
$catatan=checkPostGet('catatan','');
$tglcr=tanggalsystem(checkPostGet('tglcr',''));

switch($method)
{
case'insert':
    if($kebun=="GLOBAL"){
		$wherRow = "tanggal='".tanggalsystem($tanggal)."'";
	}else{
		$wherRow = "tanggal='".tanggalsystem($tanggal)."' and (kebun='".$kebun."' or kebun='GLOBAL')";
	}
	
	$str="select * from ".$dbname.".sdm_5harilibur where ".$wherRow."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($res);
	
	if($numRows > 0){
		if($kebun=="GLOBAL"){
			echo"Gagal. Tidak bisa input hari libur secara global.";
		}else{
			echo"Gagal. Kebun dan Tanggal sudah pernah diinput";
		}
	}else{
		$sIns="insert into ".$dbname.".sdm_5harilibur (`tanggal`,`keterangan`,`catatan`,`updateby`,`kebun`) 
        values ('".tanggalsystem($tanggal)."','".$keterangan."','".$catatan."','".$_SESSION['standard']['userid']."','".$kebun."')";
		try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
		}
	}
	break;

	case 'loadData':
		$where = "";
        if ($tglcr != '') {
            $where=" where tanggal='" . $tglcr . "' ";
        }

		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".sdm_5harilibur ".$where;
        //exit('warning : '.$str);
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=6>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="select * from ".$dbname.".sdm_5harilibur ".$where." order by tanggal desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
		        echo"<tr class=rowcontent>
				        <td>".$no."</td>
						<td>".$bar['kebun']."</td>
				        <td align=right>".tanggalnormal($bar['tanggal'])."</td>
				        <td>".$bar['keterangan']."</td>
				        <td>".$bar['catatan']."</td>
				        <td align=center>";
				        if($bar['tanggal']>date('Y-m-d')){
				        echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletehk('".$bar['tanggal']."');\">";
				    	}
				echo"</td>
		        	</tr>";
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
                <tr><td colspan=6 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case'delete':
        $sIns="delete from ".$dbname.".sdm_5harilibur where tanggal = '".$tanggal."'";
        try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
    break;
        default:
        break;
}
?>