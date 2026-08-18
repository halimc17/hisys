<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript1.2 src='js/kebun_5asistensi.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});

	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?php
$optdiv = "<option value=''>&nbsp;</option>";
$optdivisi = "<option value=''>&nbsp;</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)>=4 and tipe in ('KEBUN','AFDELING','BIBITAN') and (kodeorganisasi in (".getOrgDetail(24).") or kodeorganisasi in (".getOrgDetail(20).")) order by induk";
$res = fetchData($str);
foreach($res as $bar){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$nminduk[$bar['kodeorganisasi']];
	if($bar['tipe']=='KEBUN'){		
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$sel="";
		if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
			#$sel="selected";
		}
		
		$optdiv.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){
			$optdiv.="</optgroup>";
		}
		
	}
	if($bar['tipe']=='AFDELING' || $bar['tipe']=='BIBITAN'){
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$sel="";
		if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
			#$sel="selected";
		}
		
		$optdivisi.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){
			$optdivisi.="</optgroup>";
		}
	}
}

if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' ";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		## Ambil divisi
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('KEBUN','AFDELING','BIBITAN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($datadivisix==""){
				$datadivisix.="'".$val['kodeorganisasi']."'";				
			}else{
				$datadivisix.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		## Ambil divisi
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('KEBUN','AFDELING','BIBITAN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($datadivisix==""){
				$datadivisix.="'".$val['kodeorganisasi']."'";				
			}else{
				$datadivisix.=",'".$val['kodeorganisasi']."'";				
			}
		}

	}

	if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' ";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		## Ambil divisi
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('KEBUN','AFDELING','BIBITAN')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($datadivisix==""){
				$datadivisix.="'".$val['kodeorganisasi']."'";				
			}else{
				$datadivisix.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

$optdivtujuan = "<option value=''>&nbsp;</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)>=4 and tipe in ('KEBUN','AFDELING','BIBITAN') and (kodeorganisasi in (".$dataunitx.") or kodeorganisasi in (".$datadivisix.") )order by induk";
$res = fetchData($str);
foreach($res as $bar){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$nminduk[$bar['kodeorganisasi']];
	if($bar['tipe']=='KEBUN'){		
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optdivtujuan.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		
		$optdivtujuan.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){
			$optdivtujuan.="</optgroup>";
		}
	}

	if($bar['tipe']=='AFDELING' || $bar['tipe']=='BIBITAN'){
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optdivtujuan.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$sel="";
		if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
			#$sel="selected";
		}
		
		$optdivtujuan.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){
			$optdivtujuan.="</optgroup>";
		}
	}
}

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5asistensi').'<br></span>');


$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipe=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen','TKBM'=>'BM TBS');
$arrtipe=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen');
foreach($arrtipe as $val => $key){
	$opttipe.="<option value='".$val."'>".$key."</option>";
}

echo"<fieldset style='float:left;'><table border=0>
    <tr>
		<td>" . $_SESSION['lang']['tipetransaksi'] . "</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=tipetrans style=width:300px>".$opttipe."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['dari'] . " " . $_SESSION['lang']['kebun'] . "</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=kodeorgdari style=width:300px onchange=getdivisiasal();>".$optdiv."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['dari'] . " " . $_SESSION['lang']['divisi'] . "</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=divisidari style=width:300px>".$optdivisi."</select>
		</td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
		<td>:</td>
		<td colspan=3>
			<input class=myinputtext style=width:298px readonly placeholder='Kosong = Seluruhnya' id=karyawan onclick=getkaryawan()>
			<input hidden style=width:295px readonly placeholder='Kosong = Seluruhnya' id=karyawantemp value=''>
			<input hidden style=width:295px readonly placeholder='Kosong = Seluruhnya' id=karyawantempsudahtrans value=''>
		</td>
	</tr>
	
	
	<tr>
		<td>" . $_SESSION['lang']['ke'] . " " . $_SESSION['lang']['kebun'] . "</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=kodeorgtujuan style=width:300px onchange=getdivisitujuan();>".$optdivtujuan."</select>
		</td>
	 </tr>
	 <tr>
		<td>" . $_SESSION['lang']['ke'] . " " . $_SESSION['lang']['divisi'] . "</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=divisitujuan style=width:300px>".$optdivtujuan."</select>
		</td>
	</tr>
	
	 <tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' readonly=readonly placeholder=\"Tanggal Dari\"  class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false'; style=width:130px>
		</td>
		<td>s/d</td>
		<td><input type='text' readonly=readonly placeholder=\"Tanggal Sampai\" class='myinputtext' id='tanggalsampai' onmousemove='setCalendar(this.id)' onkeypress='return false';  style=width:135px>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td colspan=3>
			 <input type=hidden id=id>
			 <input type=hidden id=sudahtrans value=''>
			 <input type=hidden id=method value='insert'>
			 <button class=mybutton onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
			 <button class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		
		</td>
	 </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();	 	 
echo"<fieldset style=float:left;display:none;>
		<table>
			<tr>
				<td>" . $_SESSION['lang']['tipetransaksi'] . "</td>
				<td>:</td>
				<td>
					<select class=select2 id=tipetranscari style=width:155px>
						<option value=''>&nbsp;</option>
						<option value='all'>".$_SESSION['lang']['all']."</option>
						<option value='BKM'>BKM Rawat</option>
						<option value='PNN'>BKM Panen</option>
					</select>&nbsp;</td>

				<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
				<td>:</td>
				<td><select class=select2 id=kodeorgcari style=width:155px onchange=getdivisicari('asal');>".$optdiv."</select>&nbsp;</td>

				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td>:</td>
				<td><select class=select2 id=divisicari style=width:155px>".$optdivisi."</select>&nbsp;</td>

				<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>
			</tr>
		</table>
	</fieldset>
	<div style=clear:both></div>";

echo"<div id='container' style=min-height:400px><script>loaddata()</script></div>";		 
CLOSE_BOX();
echo close_body();
?>