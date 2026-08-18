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
		$str="update ".$dbname.".pabrik_5mr_parameter_nilai set standard_nilai='".$param['standarnilai']."', nama='".$param['nama']."', updateby='".$_SESSION['standard']['userid']."'
		       where kode_station='".$param['stationId']."' and kode_nilai='".$param['kdnilai']."'";
       //exit('warning : '.$str);
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
        if ($param['kdnilaidr']!=''){
            if(strlen($param['kdnilaidr'])<7){
                $param['kdnilai']=$param['kdnilaidr'];
                $parameter=$param['kdnilaidr'];
                $i = "select nama from ".$dbname.".pabrik_5mr_roa_parameter where parameter='".$parameter."'";
                $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
                $n->setFetchMode(PDO::FETCH_ASSOC);
                $d = $n->fetch();
                $param['nama']=$d['nama'];
            }else{
                $param['kdnilai']=$param['kdnilaidr'];
                $kd_transaksi=substr($param['kdnilaidr'], 0,4);
                $kode=substr($param['kdnilaidr'], 4,3);
                $i = "select nama from ".$dbname.".pabrik_5mr_bfwt where kd_transaksi='".$kd_transaksi."' and kode='".$kode."' ";
                $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
                $n->setFetchMode(PDO::FETCH_ASSOC);
                $d = $n->fetch();
                $param['nama']=$d['nama'];
            }

        }

		$str="insert into ".$dbname.".pabrik_5mr_parameter_nilai (kode_station,kode_nilai,standard_nilai,nama,updateby)
		      values('".$param['stationId']."','".$param['kdnilai']."','".$param['standarnilai']."','".$param['nama']."','".$_SESSION['standard']['userid']."')";
		//exit('warning : '.$str);
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
        $str="select * from ".$dbname.".pabrik_5mr_parameter_nilai where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=5>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".pabrik_5mr_parameter_nilai where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."' order by kode_station asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->kode_station."</td>
                    <td>".$bar->kode_nilai."</td>
                    <td>".$bar->nama."</td>
                    <td align=center>".$bar->standard_nilai."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode_station."','".$bar->kode_nilai."','".$bar->nama."','".$bar->standard_nilai."')\">
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
                <tr><td colspan=6 align=center>
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