<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$kodekelompok = checkPostGet('kodekelompok','');
$keterangan = checkPostGet('keterangan','');
$status = checkPostGet('status','');
$method = checkPostGet('method','');

 
 
switch($method){
	case 'insert':
		if ($kodekelompok == '' || $keterangan == '') {
			echo 'Gagal : Lengkapi pengisian.';exit("Warning");
		}
		#= insert
		$str="insert into ".$dbname.".sdm_5kodebpjs (kelompokbpjs,keterangan,createdby,createtime)
			  values('".$kodekelompok."','".$keterangan."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
		
	break;
    case 'update':
        if ($kodekelompok == '' || $keterangan == '') {
            echo 'Gagal : Lengkapi pengisian.';exit("Warning");
        }
        #= update
        $str="update ".$dbname.".sdm_5kodebpjs set  keterangan='".$keterangan."',status='".$status."',updateby='".$_SESSION['standard']['userid']."' where kelompokbpjs='".$kodekelompok."'";
        try{
            $owlPDO->exec($str); 
        }
        catch (PDOException $e){
            echo " Gagal : ".addslashes($e->getMessage());
        }
        
    break;
	case 'loadData':
        $lstUnit=getOrgDetail(1);
        $dtMul=0;
        $listOrg="";
        foreach($lstUnit as $row=>$isiDt){
            if(substr($row,0,5)=='Pilih'){
                continue;
            }
            if($dtMul==0){
                $listOrg="'".$row."'";
                $dtMul=1;
            }else{
                $listOrg.=",'".$row."'";
            }
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
        $str="select * from ".$dbname.".sdm_5kodebpjs order by kelompokbpjs";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=4>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_5kodebpjs order by kelompokbpjs limit ".$offset.",".$limit." ";
		    $stt=array("0"=>$_SESSION['lang']['aktif'],"1"=>$_SESSION['lang']['tidakaktif']);
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td>".$bar['kelompokbpjs']."</td>
                    <td>".$bar['keterangan']."</td>
                    <td>".$stt[$bar['status']]."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['kelompokbpjs']."','".$bar['keterangan']."','".$bar['status']."')\">
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
                <tr><td colspan=4 align=center>
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