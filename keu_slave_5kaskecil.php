<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$unit = checkPostGet('unit','');
$noakun = checkPostGet('noakun','');
$noakun2 = checkPostGet('noakun2','');
$rekening = checkPostGet('rekening','');
$periode = checkPostGet('periode','');
$tanggalmulai=tanggalsystemn(checkPostGet('tanggalmulai',''));
$tanggalselesai=tanggalsystemn(checkPostGet('tanggalselesai',''));
$tanggaltopup=tanggalsystemn(checkPostGet('tanggaltopup',''));
$plafon = checkPostGet('plafon','');
$saldoberjalan = checkPostGet('saldoberjalan','');
$method = checkPostGet('method','');
$batasbawah = checkPostGet('batasbawah','');

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

switch($method){

    case 'getbank':
        $optbank.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

        $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $rekpinjam=$bar['nilai'];

        if ($noakun2=='1110101' || $noakun2=='2140101') {
            $whrrek="";
            if ($noakun2=='1110101') {
                $whrrek=" and noakun not in (".$rekpinjam.")";
            }else{
                $whrrek=" and noakun in (".$rekpinjam.")";
            }

            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."' ".$whrrek;
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                if ($rekening==$bar['noakun']) {
                    $optbank.="<option value='".$bar['noakun']."' selected>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
                }else{
                    $optbank.="<option value='".$bar['noakun']."' >".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
                }
                
            }
        }

        echo $optbank;
        
    break;

	case 'insert':
		if ($periode == '' || $unit == '' || $noakun == '') {
			exit('warning : Semua field harus diisi.');
		}

        if (substr($noakun2,0,5)=='11101' && $rekening=='') {
            exit('warning : No.rekening harus diisi.');
        }
	   
		#= delete
		$str="delete from ".$dbname.".keu_5kaskecil where unit='".$unit."' and noakun='".$noakun."' and periode='".$periode."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
			
		#= insert
		$str="insert into ".$dbname.".keu_5kaskecil (unit,noakun,noakun2,periode,
				tanggalmulai,tanggalselesai,tanggaltopup,
				plafon,saldoberjalan,createdby,createtime,batasbawah,rekening)
			  values('".$unit."','".$noakun."','".$noakun2."','".$periode."',
			  '".$tanggalmulai."','".$tanggalselesai."','".$tanggaltopup."',
			  '".$plafon."','".$saldoberjalan."','".$_SESSION['standard']['userid']."','".date('Ymd')."',
              '".$batasbawah."','".$rekening."')";
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
        $str="select * from ".$dbname.".keu_5kaskecil ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_5kaskecil order by periode desc, unit asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['unit']."'";
                $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);

                $sql="select namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar['rekening']."'";
                $scek2=$owlPDO->query($sql);
                $scek2->setFetchMode(PDO::FETCH_ASSOC);
                $rcek2=$scek2->fetch();
                $kodebank=$rcek2['namabank'];
                $norek=$rcek2['rekening'];

                $sql="select namabank from ".$dbname.".keu_5daftarbank where kodebank='".$kodebank."'";
                $scek2=$owlPDO->query($sql);
                $scek2->setFetchMode(PDO::FETCH_ASSOC);
                $rcek2=$scek2->fetch();
                $namabank=$rcek2['namabank'];

                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$optorg[$bar['unit']]."</td>
                    <td>".$nmakun[$bar['noakun2']]."</td>
                    <td>".$bar['periode']."</td>
                    <td align='center'>".$norek."</td>
                    <td>".tanggalnormal($bar['tanggalmulai'])."</td>
                    <td>".tanggalnormal($bar['tanggalselesai'])."</td>
                    <td>".tanggalnormal($bar['tanggaltopup'])."</td>
                    <td align=right>".number_format($bar['plafon'])."</td>
                    <td align=right>".number_format($bar['saldoberjalan'])."</td>
                    <td align=right>".number_format($bar['batasbawah'],2)."</td>";
                if ($bar['close']==0) {
                    $tab.="<td align=center>
                        <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield(
                        '".$bar['unit']."','".$bar['noakun']."','".$bar['noakun2']."','".$bar['periode']."',
                        '".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."','".tanggalnormal($bar['tanggaltopup'])."',
                        '".number_format($bar['plafon'])."','".number_format($bar['saldoberjalan'])."','".number_format($bar['batasbawah'])."','".$bar['rekening']."')\">
                    </td>";
                }else{
                    $tab.="<td align=center></td>";
                }
                
                $tab.="</tr>";
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

	default:
	   break;					
}


?>