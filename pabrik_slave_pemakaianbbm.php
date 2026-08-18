<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$unit = checkPostGet('unit','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$nilai = checkPostGet('nilai','');
$station = checkPostGet('station','');
$mesin = checkPostGet('mesin','');
$method = checkPostGet('method','');
$salak = checkPostGet('salak','');
$keluar = checkPostGet('keluar','');
$masuk = checkPostGet('masuk','');
$sawal = checkPostGet('sawal','');
$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


#tgl kmrn
$tglKmrn = strtotime('-1 day',strtotime($tgl));
$tglKmrn = date('Y-m-d', $tglKmrn);

$tglbesok = strtotime('-1 day',strtotime($tgl));
$tglbesok = date('Y-m-d', $tglbesok);

switch($method){
	
	case'getdata':
		#=sawal
		$str="select salak from ".$dbname.".pabrik_pemakaianbbm where tanggal='".$tglKmrn."' and mesin='".$mesin."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$sawal=$bar['salak'];
			if($sawal==''){
				$sawal=0;
			}
		#=masuk
		$str="select sum(jumlah) as jumlah from ".$dbname.".pabrik_pemakaianbbm where tanggal='".$tgl."' 
			and kodeblok='".$mesin."' and tipetransaksi='5'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$masuk=$bar['jumlah'];
			if($sawal==''){
				$masuk=0;
			}
		echo $sawal."##".$masuk;
			
	break;
	
	
	case 'insert':
            if ($mesin == '' || $tgl=='' || $salak<=0 || $salak=='') {
                echo 'Gagal : Mesin / tanggal masih kosong.';
            }else{
        		$str="insert into ".$dbname.".pabrik_pemakaianbbm (unit,station,mesin,tanggal,sawal,masuk,keluar,salak,updateby)
        		      values('".$unit."','".$station."','".$mesin."','".$tgl."',
					  '".$sawal."','".$masuk."','".$keluar."','".$salak."','".$_SESSION['standard']['userid']."')";
        		try{
        			$owlPDO->exec($str); 
        		}
        		catch (PDOException $e){
        			echo " Gagal : ".addslashes($e->getMessage());
        		}
            }
	break;
	
	
	case'getmesin':
        $optMesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optMesin.="<option value='Others'>Others</option>";
        $str="select * from ".$dbname.".organisasi where induk='".$station."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if($mesin==$bar['kodeorganisasi'])
            {$select="selected=selected";}
            else
            {$select="";}
            $optMesin.="<option ".$select." value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
        }
        echo $optMesin;
    break;
	
	
	case 'update':	
		$str="update ".$dbname.".pabrik_pemakaianbbm set sawal='".$sawal."',masuk='".$masuk."',keluar='".$keluar."',keluar='".$keluar."'
		       where mesin='".$mesin."' and tanggal='".$tgl."'";
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
        $str="select * from ".$dbname.".pabrik_pemakaianbbm where unit='".$_SESSION['empl']['lokasitugas']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".pabrik_pemakaianbbm where unit='".$_SESSION['empl']['lokasitugas']."' 
			order by unit asc limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optorg[$bar['station']]."</td>
                    <td>".$optorg[$bar['mesin']]."</td>
                    <td align=right>".$bar['sawal']."</td>
                    <td align=right>".$bar['masuk']."</td>
                    <td align=right>".$bar['keluar']."</td>
                    <td align=right>".$bar['salak']."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['unit']."','".$bar['station']."','".$bar['mesin']."',
						'".tanggalnormal($bar['tanggal'])."','".$bar['sawal']."','".$bar['masuk']."','".$bar['keluar']."','".$bar['salak']."')\">
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
	   break;					
}


?>