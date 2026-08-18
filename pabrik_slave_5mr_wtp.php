<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$method = checkPostGet('method','');
$param=$_POST;
switch($method){
	case 'update':	
		$str="update ".$dbname.".pabrik_5mr_material_usage set kd_transaksi='".$param['kdTrans']."',station='".$param['stationId']."', kodebarang='".$param['kdBrg']."', updateby='".$_SESSION['standard']['userid']."'
		       where kode_mu='".$param['primId']."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
		$str="insert into ".$dbname.".pabrik_5mr_material_usage (kd_transaksi,station,kodebarang,updateby)
		      values('".$param['kdTrans']."','".$param['stationId']."','".$param['kdBrg']."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".sdm_5jeniskriteria
		where kode='".$kode."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;

	case 'loadData':
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".pabrik_5mr_material_usage where left(station,4)='".$_SESSION['empl']['lokasitugas']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=5>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".pabrik_5mr_material_usage where left(station,4)='".$_SESSION['empl']['lokasitugas']."' order by kode_mu asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;
                $whrBrg="kodebarang='".$bar->kodebarang."'";
                $optNmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrBrg);
                $whrStation="kodeorganisasi='".$bar->station."'";
                $optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrStation);
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->kd_transaksi."</td>
                    <td>".$optNmOrg[$bar->station]."</td>
                    <td>".$optNmBrg[$bar->kodebarang]."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode_mu."','".$bar->kd_transaksi."','".$bar->station."','".$bar->kodebarang."')\">
                    </td>
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
                <tr><td colspan=5 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

	default:
	   break;					
}


?>
