<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$tahun = checkPostGet('tahun','');
$unit = checkPostGet('unit','');
$rpnya = checkPostGet('rpnya','');
$method = checkPostGet('method','');

 
$nmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi'); 
switch($method){
	case 'insert':
		try {
		$owlPDO->beginTransaction();
		
		if ($tahun == '' || $unit == '') {
			throw new PDOException("Lengkapi pengisian.");
		}
	
		#= delete
		$str="delete from ".$dbname.".sdm_5gajipokok where tahun='".$tahun."' and kodeorg='".$unit."' and idkomponen='87'";
		$owlPDO->exec($str); 

		#= insert
		$str="insert into ".$dbname.".sdm_5gajipokok (kodeorg,idkomponen,tahun,jumlah,karyawanid)
			  values('".$unit."','87','".$tahun."','".$rpnya."',0)";
		$owlPDO->exec($str); 
		
	
		#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
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
            $page=intval($_POST['page']);
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".sdm_5gajipokok where idkomponen='87' and kodeorg in (".$listOrg.") ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=4>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_5gajipokok where idkomponen='87' and kodeorg in (".$listOrg.") order by tahun desc limit ".$offset.",".$limit."";
		
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td align=center>".$bar['tahun']."</td>
                    <td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
                    <td align=right>".number_format($bar['jumlah'],2)."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['jumlah']."')\">
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