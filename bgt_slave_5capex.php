<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$kelbrg = checkPostGet('kelbrg','');
$kdcapex = checkPostGet('kdcapex','');
$method = checkPostGet('method','');

switch($method){
	case 'update':	
		$str="update ".$dbname.".bgt_5capex set kdcapex='".$kdcapex."'
		       where idjeniskdcapex='".$kodekriteria.$kelbrg."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
        $str="select * from ".$dbname.".bgt_5capex where kelbarang='".$kelbrg."' and kodecapex='".$kdcapex."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){    
            if ($kdcapex == '' || $kelbrg == '') {
                echo 'Gagal : Semua field harus diisi.';
            }else{
        		$str="insert into ".$dbname.".bgt_5capex (kelbarang,kodecapex)
        		      values('".$kelbrg."','".$kdcapex."')";
        		try{
        			$owlPDO->exec($str); 
        		}
        		catch (PDOException $e){
        			echo " Gagal : ".addslashes($e->getMessage());
        		}
            }
        }else{
            exit('Warning : data sudah pernah di input');
        }
	break;

	case 'loadData':
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=intval($_POST['page']);
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".bgt_5capex";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".bgt_5capex order by kodecapex asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whr="kode='".$bar->kelbarang."'";
                $kelompok=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok',$whr);
                $whr="kodetipe='".$bar->kodecapex."'";
                $tipeasset=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe',$whr);
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->kodecapex." - ".$tipeasset[$bar->kodecapex]."</td>
                    <td>".$bar->kelbarang." - ".$kelompok[$bar->kelbarang]."</td>
                	</tr>";
            }
            // <td align=center>
            // <img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode."','".$bar->kode."','".substr($bar->idjeniskdcapex,2,3)."','".$bar->kdcapex."')\">
            // </td>
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
                <tr><td colspan=3 align=center>
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