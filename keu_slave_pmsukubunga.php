<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$kodebank = checkPostGet('kodebank','');
$notransaksi = checkPostGet('notransaksi','');
$periode = tanggalsystemn(checkPostGet('periode',''));
$periodelama = tanggalsystemn(checkPostGet('periodelama',''));
$nilai = checkPostGet('nilai','');
$method = checkPostGet('method','');
$daftarbank=makeOption($dbname, 'keu_5daftarbank','kodebank,namabank');
$kirim="";
if($_POST['kirim']!=''){
	$kirim=$_POST['kirim'];
}
switch($method){
	default:
	OPEN_BOX('','<span class=judul>'.getMenu('keu_pmsukubunga').'</span>');
	$optper=$optunit=$optkas =$optkas2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

	#= option bank
	$disabled="";
	$optbank ="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str = "select kodebank,namabank from ".$dbname.".keu_5daftarbank ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if($bar['kodebank']==$kodebank){
			$disabled="disabled";
			$optbank.="<option value='".$bar['kodebank']."' selected>".$bar['namabank']."</option>";
		}else{

			$optbank.="<option value='".$bar['kodebank']."'>".$bar['namabank']."</option>";	
		}
		
	}


	for($x=0;$x<=12;$x++){
		$dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
		$optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
	}

	echo"
	<br><fieldset>
		<legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellspacing=0>
		<tr>	
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td> : </td>
			<td><input type=text class=myinputtext id=notransaksipm value='".$notransaksi."' ".$disabled." style=width:148px></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namabank']."</td><td> : </td>
			<td><select style=width:150px id=pmssukubungakodebank ".$disabled.">".$optbank."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td> : </td>
			<td>
			<input type=text class=myinputtext id=pmssukubungaperiode onmousemove=setCalendar(this.id) style=width:148px  onkeypress=return false;  size=10 maxlength=10 readonly/>
			</td>
		</tr>
		<tr>	
			<td>".$_SESSION['lang']['nilai']."</td>
			<td> : </td>
			<td><input type=text class=myinputtextnumber id=pmssukubunganilai value=0  style=width:145px maxlength=8 onkeypress='return angka_doang(event)' ></td>
		</tr>

	  <input type=hidden id=sumberform value='".$kirim."'>
	  <input type=hidden id=pmssukubungamethod value='insert'>
	  <input type=hidden id=sukubungaperiodelama value=''>
	  <tr>
		<td><td>
		<td><button class=mybutton onclick=bungasimpan()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=bungacancel()>".$_SESSION['lang']['cancel']."</button></td>
	  </tr>
		 
	</table></fieldset>";

	echo "
	<br/>
	<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 cellspacing=1 border=0>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					
					<td align=center>".$_SESSION['lang']['namabank']."</td>
					<td align=center>".$_SESSION['lang']['nilai']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>
			<tbody id=pmssukubungacontainer></tbody>
			<tfoot id='pmssukubungafootData'>
			</tfoot>
		</table>
	</fieldset>";
	CLOSE_BOX();
	break;	
	case 'insert':
		if ($kodebank == '' || $periode == '' || $nilai == '') {
			echo 'Gagal : Semua field harus diisi.';exit("Warning");
		}
	
		#= delete
		$str="delete from ".$dbname.".keu_pmsukubunga where notransaksipm='".$notransaksi."' and kodebank='".$kodebank."' and periode='".$periodelama."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
			
		#= insert
		$str="insert into ".$dbname.".keu_pmsukubunga (kodebank,periode,nilai,notransaksipm,createdby,createtime,updateby)
			  values('".$kodebank."','".$periode."','".$nilai."','".$notransaksi."','".$_SESSION['standard']['userid']."','".date('Ymd')."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
		if($_POST['sumberform']=='daripinjaman'){
			echo $_POST['nilai'];
			//exit('warning');
		}
		
	break;

	case 'loadData':
		$footd="";
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
			if(!is_numeric($page)){
				$page=0;
			}
            if($page<0){
				$page=0;
			}	
        }

        $whr="";
        if ($kodebank!='') {
        	$whr=" and kodebank='".$kodebank."' ";
        }

        if ($notransaksi!='') {
        	$whr=" and notransaksipm='".$notransaksi."' ";
        }

        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_pmsukubunga where 1=1 ".$whr;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_pmsukubunga where 1=1 ".$whr." order by periode desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                //$whr="kodeorganisasi='".$bar['unit']."'";
                //$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".tanggalnormal($bar['periode'])."</td>
                    <td>".$daftarbank[$bar['kodebank']]."</td>
                    <td align=right>".number_format($bar['nilai'],2)."</td>
                    <td align=center>
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"bungafillfield(
						'".tanggalnormal($bar['periode'])."','".$bar['kodebank']."','".$bar['nilai']."')\">
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
                <button class=mybutton onclick=bungaloadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"bungaloadData(this.value)\">".$isiRow."</select>
                <button class=mybutton onclick=bungaloadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;				
}


?>