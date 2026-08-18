<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$kodekriteria = checkPostGet('kodekriteria','');
$kriteria = checkPostGet('kriteria','');
$idnilai = checkPostGet('idnilai','');
$penilaian = checkPostGet('penilaian','');
$method = checkPostGet('method','');

switch($method){
	case 'update':	
		$str="update ".$dbname.".sdm_5kriteriapenilaian set penilaian='".$penilaian."'
		       where idjenispenilaian='".$kodekriteria.$idnilai."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
            $strCount = "select right(idjenispenilaian,3) as nourut from " . $dbname . ".sdm_5kriteriapenilaian where kode='".$kriteria."' order by idjenispenilaian desc limit 1";
            $rData=fetchData($strCount);
            if(intval($rData[0]['nourut'])==0){
                $idnilai=addZero(1,3);
            }else{
                $idnilai=addZero((intval($rData[0]['nourut'])+1),3);
            }
            $idjenispenilaian=$kodekriteria.$idnilai;

            if ($penilaian == '' || $kodekriteria == '') {
                echo 'Gagal : Semua field harus diisi.';
            }else{
        		$str="insert into ".$dbname.".sdm_5kriteriapenilaian (kode,idjenispenilaian,penilaian,updateby)
        		      values('".$kriteria."','".$idjenispenilaian."','".$penilaian."','".$_SESSION['standard']['userid']."')";
        		try{
        			$owlPDO->exec($str); 
        		}
        		catch (PDOException $e){
        			echo " Gagal : ".addslashes($e->getMessage());
        		}
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
        $str="select * from ".$dbname.".sdm_5kriteriapenilaian ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_5kriteriapenilaian order by idjenispenilaian asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whr="kode='".$bar->kode."'";
                $optkriteria=makeOption($dbname,'sdm_5jeniskriteria','kode,kriteria',$whr);
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$optkriteria[$bar->kode]."</td>
                    <td>".$bar->penilaian."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode."','".$bar->kode."','".substr($bar->idjenispenilaian,2,3)."','".$bar->penilaian."')\">
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