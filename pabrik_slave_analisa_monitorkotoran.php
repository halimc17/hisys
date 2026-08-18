<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$unit = checkPostGet('unit','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$tipe = checkPostGet('tipe','');
$nourut = checkPostGet('nourut','');
$jam = checkPostGet('jam','');
$menit = checkPostGet('menit','');
$kadar = checkPostGet('kadar','');
$method = checkPostGet('method','');

$jam=$jam.':'.$menit;

switch($method){
	case 'insert':
		if ($tanggal == '' || $unit == '' || $tipe == '') {
			exit("Warning:Lengkapi Pengisian");
		}
		
		$expjam = $jam.":".$menit.":00";
	
		#= delete
		$str="delete from ".$dbname.".pabrik_analisa_monitorkotoran where 
			unit='".$unit."' and tanggal='".$tanggal."' and tipe='".$tipe."' and nourut='".$nourut."' and jam='".$expjam."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
		
		if($nourut==''){
			$str="SELECT nourut from ".$dbname.".pabrik_analisa_monitorkotoran  where 
				unit='".$unit."' and tanggal='".$tanggal."' and tipe='".$tipe."' and nourut='".$nourut."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$nourut=$bar['nourut'];
				@$nourut=$nourut+1;
			
		}
			
		#= insert
		$str="insert into ".$dbname.".pabrik_analisa_monitorkotoran (unit,tanggal,tipe,nourut,
				jam,kadar,createdby,createtime)
			  values('".$unit."','".$tanggal."','".$tipe."','".$nourut."',
			  '".$jam."','".$kadar."','".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;

	case 'loadData':
    if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
        $unit = "";
    }else{
        $unit = "where unit='".$_SESSION['empl']['lokasitugas']."'";
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
        $str="select * from ".$dbname.".pabrik_analisa_monitorkotoran ".$unit." order by tanggal desc ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".pabrik_analisa_monitorkotoran ".$unit." order by unit asc,tanggal desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['unit']."'";
                $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
                @$no++;
                $tab.="<tr class=rowcontent>
            
                    <td align=center>".$no."</td>
                    <td>".$optorg[$bar['unit']]."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$bar['tipe']."</td>
                    <td>".$bar['nourut']."</td>
                    <td>".$bar['jam']."</td>
                    <td align=right>".number_format($bar['kadar'],2)."</td>
                    <td align=center width=25px>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield(
						'".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['tipe']."','".$bar['nourut']."',
						'".substr($bar['jam'],0,2)."','".substr($bar['jam'],3,2)."','".$bar['kadar']."')\"></td>
					<td align=center width=25px><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletedata(
						'".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['tipe']."','".$bar['nourut']."')\">
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
                <tr><td colspan=12 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
	
	case'deletedata':
		$str="delete from ".$dbname.".pabrik_analisa_monitorkotoran where unit='".$unit."' and tanggal='".$tanggal."' and tipe='".$tipe."' and nourut='".$nourut."'";
		try{$owlPDO->exec($str);}catch (PDOException $e){echo "Gagal : ".addslashes($e->getMessage());}
	break;

	default:
	   break;					
}


?>