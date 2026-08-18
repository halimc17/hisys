<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript1.2 src='js/vhc_service.js?v=<?php echo time(); ?>'></script>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?php
$svhc="select * from ".$dbname.".vhc_5master where status=1  order by kodevhc"; //echo $svhc;
$res=$owlPDO->query($svhc) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rvhc=$res->fetch())
{
    $optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']." [ ".$rvhc['detailvhc']." ] [ ".@$rvhc['nopol']." ] [ ".$rvhc['tahunperolehan']." ]</option>";
}

$svhc2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('WORKSHOP','MAINTENANCE') and induk = '".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc"; //echo $svhc;
$res=$owlPDO->query($svhc2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rvhc2=$res->fetch())
{
    $optOrg.="<option value='".$rvhc2['kodeorganisasi']."'>".$rvhc2['kodeorganisasi']." - ".$rvhc2['namaorganisasi']."</option>";
}


$optOrgTr=$karypemohon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$svhc23="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' and induk = '".$_SESSION['empl']['lokasitugas']."' 
                 order by namaorganisasi asc"; //echo $svhc;
$res=$owlPDO->query($svhc23) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rvhc23=$res->fetch())
{
    $optOrgTr.="<option value='".$rvhc23['kodeorganisasi']."'>".$rvhc23['kodeorganisasi']." - ".$rvhc23['namaorganisasi']."</option>";
}


$arropttipe = getEnum($dbname,'vhc_penggantianht','tipeperbaikan');
$opttipeperbaikan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($arropttipe as $value) {
    $opttipeperbaikan.="<option value='".$value."'>".$value."</option>";
}

$arr=array(""=>$_SESSION['lang']['pilihdata'],"internal"=>"Internal","external"=>"External");
// $optex="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arr as $brs1 => $isi1)
{
	$optex.="<option value=".$brs1.">".$isi1."</option>";
}


## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
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

	$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
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

	$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
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
	$whereKary=" and a.lokasitugas IN (".$dataunitx.") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
}

$iKar="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
	left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
	where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and statuskaryawan != 'Keluar' ".$whereKary." ";
$rKary=fetchData($iKar);
foreach ($rKary as $val) {
	$karypemohon .= "<option value='" . $val['karyawanid'] . "'>".$val['nik']." - ".$val['namakaryawan']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('vhc_service').'</span>');
                    echo"<div id=action_list>";//buka div
                    echo"<table>
                         <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 
		<td>
		<fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
            echo $_SESSION['lang']['notransaksi']." : <input type=text class=myinputtext id=schTran />";
			echo "  ".$_SESSION['lang']['tanggal']."(".$_SESSION['lang']['masuk']."): <input type=text class=myinputtext id=schTgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
			// echo "".$_SESSION['lang']['noreferensi'].": <input type=text class=myinputtext id=schRef />";
			echo"<button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>";
			echo"</fieldset>
		</td>
        </tr>
		
	 </table> "; 
echo "</div>";//tutup div
CLOSE_BOX();


echo"<div id=listData style=display:block>";//buka list data
OPEN_BOX();
	echo "
	
		<div id=contain  style=display:block> 
                    <script>loadData()</script>
		</div>
	";
CLOSE_BOX();
echo "</div>";//tutup list data

echo "<div id=headher style=display:none;>";//buka diff
OPEN_BOX();//<td><select id=kdorg disabled style=\"width:150px;\"><option  value='".$kdor."'>".$nmor."</option></select></td>
echo "
<fieldset style='float:left'>
<legend>Header</legend>
<table cellspacing=1>
        <tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
            <td>:</td>
            <td colspan=4><input type=text disabled id=trans_no name=trans_no class=myinputtext style=width:250px;/></td>
			
			<td align=left style='padding-left:10px'>".$_SESSION['lang']['nopengajuan']." <font style='font-size:8px;color:blue;font-style:italic;'>
			<font style='font-size:10px;color:blue;'>(1)</font> (Opsional)</font>
			</td>
            <td>:</td>
            <td colspan=4><input type=text id=nopengajuan name=nopengajuan class=myinputtext style=width:205px;  onclick='popupnopengajuan()' readonly/></td>
		</tr>
		<tr>
            <td>".$_SESSION['lang']['workshop']."
				<font style='font-size:10px;color:blue;'>(2)</font>
			</td>
            <td>:</td>
            <td colspan=4>
				<select class='select2' style='width:250px' id='codeOrg'>".$optOrg."</select>
			</td>
		
            <td style='padding-left:10px'> ".$_SESSION['lang']['kodetraksi']."
				<font style='font-size:10px;color:blue;'>(3)</font>
			</td>
            <td>:</td>
            <td colspan=4>
				<select class='select2' style='width:210px' id='kdTraksi' onchange=getKdVhc(0,0)>".$optOrgTr."</select>
			</td>
		</tr>
       
		<tr>        
			
			<td>".$_SESSION['lang']['kodevhc']."
				<font style='font-size:10px;color:blue;'>(4)</font>
			</td>
            <td>:</td>
            <td colspan=4>
				<select class='select2' style='width:250px' id='vhc_code' onchange=getKm()>".$optVhc."</select>
			</td>

            <td style='padding-left:10px'>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pemohon']."
				<font style='font-size:10px;color:blue;'>(5)</font>
			</td>
            <td>:</td>
			<td colspan=4><select class='select2' style='width:210px' id='nmpemohon'>" . $karypemohon . "</select></td>
        </tr>
		
        <tr>
            <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['masuk']."
				<font style='font-size:10px;color:blue;'>(6)</font>
			</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tgl_ganti name=tgl_ganti onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=width:62px; readonly/></td>
        
            <td>".$_SESSION['lang']['tanggal'].". ".$_SESSION['lang']['keluar']."
				<font style='font-size:10px;color:blue;'>(7)</font>
			</td>
            <td>:</td>
            <td><input type=text class=myinputtext id=tgl_keluar name=tgl_keluar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=width:63px; readonly/></td>
			
			<td style='padding-left:10px'>Internal / External&nbsp;
				<font style='font-size:10px;color:blue;'>(8)</font></td>
            <td>:</td>
            <td colspan=4><select id=external class=select2 style=width:65px;>".$optex."</select>&nbsp;".$_SESSION['lang']['downtime']."&nbsp;<input type=text class=myinputtextnumber id=dwnTime name=dwnTime value=0 onclick=\"this.select()\" maxlength=10 style=width:20px; /> ".$_SESSION['lang']['jam']."</td>
        </tr>
        
        <tr>
            <td>KM / HM ".$_SESSION['lang']['masuk']."
				<font style='font-size:10px;color:blue;'>(9)</font>
			</td>
            <td>:</td>
            <td ><input type=text class=myinputtextnumber value=0 id=kmmasuk name=kmawal onkeypress=return angka_doang(event);  value=0  maxlength=10 style=width:62px; /></td>
        
            <td>KM / HM ".$_SESSION['lang']['keluar']."
				<font style='font-size:10px;color:blue;'>(10)</font>
			</td>
            <td>:</td>
            <td ><input type=text class=myinputtextnumber id=kmkeluar name=kmakhir onkeypress=return angka_doang(event);  value=0  maxlength=10 style=width:63px; /></td>
        
            <td valign=top style='padding-left:10px'>".$_SESSION['lang']['tipeperbaikan']."
				<font style='font-size:10px;color:blue;'>(11)</font>
			</td>
            <td valign=top>:</td>
            <td colspan=4>
				<select class='select2' style='width:210px' id='tipeperbaikan'>".$opttipeperbaikan."</select>
			</td>
        </tr>

        <tr>
            <td valign=top> ".$_SESSION['lang']['descDamage']."
				<font style='font-size:10px;color:blue;'>(12)</font>
			</td>
            <td valign=top>:</td>
            <td colspan=7><textarea name=textarea id=descDmg style=width:600px; rows=2 onkeypress=return tanpa_kutip(event);></textarea></td>
        </tr>        

        <tr>
            <td valign=top>".$_SESSION['lang']['alasan']."
				<font style='font-size:10px;color:blue;'>(13)</font>
			</td>
            <td valign=top>:</td>
            <td colspan=7><textarea name=textarea id=terlambat style=width:600px; rows=2 onkeypress=return tanpa_kutip(event);></textarea></td>
        </tr>
		
		<tr>
			<td></td>
			<td colspan=3>
				<button id=savehead class=mybutton onclick=\"saveHeader()\">".$_SESSION['lang']['save']."</button>
				<button id=batal class=mybutton onclick=cancelHead()>".$_SESSION['lang']['cancel']."</button>
			</td>
			<input type=hidden id=proses value='insert'>
			
		</tr>
	
</table>
</fieldset>";// <button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
CLOSE_BOX();
echo"</div>";

echo"<div id=detailEntry style=display:none>";
OPEN_BOX();

echo "<div id=detailisi></div>";

CLOSE_BOX();
echo "</div>";
?>

<!-- <script>
	getSelect2();
</script> -->

<?
echo close_body();
?>