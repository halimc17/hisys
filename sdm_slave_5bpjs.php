<?
//error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$lokasibpjs = checkPostGet('lokasibpjs','');
$jenisbpjs = checkPostGet('jenisbpjs','');
$jenisbpjsplus = checkPostGet('jenisbpjsplus','');
$bebankaryawan = checkPostGet('bebankaryawan','');
$bebanperusahaan = checkPostGet('bebanperusahaan','');
$bebankaryawantpdiskon = checkPostGet('bebankaryawantpdiskon','');
$bebanperusahaantpdiskon = checkPostGet('bebanperusahaantpdiskon','');
$maxgaji = checkPostGet('maxgaji','');
$method = checkPostGet('method','');

#= option unit
$str = "select * from ".$dbname.".sdm_ho_component ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenis[$bar['id']]=$bar['name'];
}

switch($method){
	case 'insert':
		if ($lokasibpjs == '' || $jenisbpjs == '' || $jenisbpjsplus == '') {
			echo 'Gagal : Lengkapi pengisian.';exit("Warning");
		}
	
		#= delete
		$str="delete from ".$dbname.".sdm_5bpjs where lokasibpjs='".$lokasibpjs."' and jenisbpjs='".$jenisbpjs."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	    $arrNm=array("3"=>"JKK","44"=>"Kesehatan","61"=>"JKM","67"=>"JHT","81"=>"JP");
		#= insert
		$str="insert into ".$dbname.".sdm_5bpjs (lokasibpjs,jenisbpjs,jenisbpjsplus,bebankaryawan,bebanperusahaan,maxgaji,namabpjs,bebankaryawantpdiskon,bebanperusahaantpdiskon)
			  values('".$lokasibpjs."','".$jenisbpjs."','".$jenisbpjsplus."','".$bebankaryawan."','".$bebanperusahaan."','".$maxgaji."','".$arrNm[$jenisbpjs]."','".$bebankaryawantpdiskon."','".$bebanperusahaantpdiskon."')";
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
        $str="select * from ".$dbname.".sdm_5bpjs ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center colspan=8>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_5bpjs limit ".$offset.",".$limit."";
		
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar['lokasibpjs']."</td>
                    <td>".$optjenis[$bar['jenisbpjsplus']]."</td>
                    <td>".$optjenis[$bar['jenisbpjs']]."</td>
                    <td align=right>".number_format($bar['bebankaryawan'],2)."</td>
                    <td align=right>".number_format($bar['bebanperusahaan'],2)."</td>
                    <td hidden align=right>".number_format($bar['bebankaryawantpdiskon'],2)."</td>
                    <td hidden align=right>".number_format($bar['bebanperusahaantpdiskon'],2)."</td>
                    <td align=right>".number_format($bar['maxgaji'],2)."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield(
						'".$bar['lokasibpjs']."','".$bar['jenisbpjs']."','".$bar['jenisbpjsplus']."','".$bar['bebankaryawan']."','".$bar['bebanperusahaan']."','".$bar['maxgaji']."','".$bar['bebankaryawantpdiskon']."','".$bar['bebanperusahaantpdiskon']."')\">
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
                <tr><td colspan=10 align=center>
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