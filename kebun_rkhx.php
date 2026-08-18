<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_rkhx.js?v=<?php echo time(); ?>'></script>
<!--<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>-->
<?php
if(@$_SESSION['org']['period']['start']==''){
	exit("Warning : Periode akutansi belum ada.");
}
$divisi=$asst=$org=$jabatan=$mdr1x="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$divisis=$jabatand=$karysch=$orgsdet=$divisisdet=$tipekaryh=$periodeh="<option value=''>".$_SESSION['lang']['all']."</option>";
$dataarray=array();
$dataarray=getOrgDetail(27);
$datadivisi='';
foreach ($dataarray as $key => $value) {
	if($datadivisi==''){
		$datadivisi="'".$value."'";
	}else{
		$datadivisi.=",'".$value."'";
	}
}

$where="";
$where=" and kodeorganisasi in (".$datadivisi.")";
$sql = "SELECT * FROM " . $dbname . ".organisasi where 1=1 ".$where." and length(kodeorganisasi)='6' order by induk, tipe";
$res = fetchdata($sql);
foreach($res as $bar){
	if($bar['tipe']=='AFDELING' or $bar['tipe']=='BIBITAN'){		
		if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){					
			$divisi.="<option value=".$bar['kodeorganisasi']." selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			$divisis.="<option value=".$bar['kodeorganisasi']." selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}else{		
			$divisi.="<option value=".$bar['kodeorganisasi'].">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			$divisis.="<option value=".$bar['kodeorganisasi'].">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
	}
	
}
$whereKary=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > '".$_SESSION['org']['period']['start']."')";
if($_SESSION['empl']['subbagian']!=''){
	//$whereKary.=" and a.subbagian='".$_SESSION['empl']['subbagian']."'";
}

$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tipe='RKH'";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['kolom']=='mandor'){
		$mdr=$bar['jabatan'];
	}
	if($bar['kolom']=='mandor1'){
		$mdr1=$bar['jabatan'];
	}
	if($bar['kolom']=='kerani'){
		$krn=$bar['jabatan'];
	}
	if($bar['kolom']=='asst'){
		$asstx=$bar['jabatan'];
	}
}

if($asstx!=''){
	$whereKary.=" and a.kodejabatan in (".$asstx.")";
}else{
	$whereKary.=" and b.namajabatan like '%ass%'";
}

$whereMdr1 = "";
if ($md1 != '') {
	$whereMdr1 .= " and a.kodejabatan in(".$mdr1.")";
} else {
	$whereMdr1 .= " and a.kodejabatan in('6')";
}


$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereKary." and (b.namajabatan not like '%traksi%' or b.namajabatan not like '%workshop%') order by a.namakaryawan asc";	
$res = fetchdata($qAsst);
foreach($res as $row){
	if($row['subbagian']!=''){
		$div="[".$row['subbagian']."] ";
	}else{
		$div="[".$row['lokasitugas']."] ";
	}
	$asst.="<option value=".$row['karyawanid'].">".$row['namakaryawan']."</option>";
}

$qMdr1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a ".
	"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whereMdr1." and (b.namajabatan not like '%traksi%' or b.namajabatan not like '%workshop%') order by a.namakaryawan asc";	
$res = fetchdata($qMdr1);
foreach($res as $row){
	$mdr1x.="<option value=".$row['karyawanid'].">".$row['namakaryawan']."</option>";
}

$_SESSION['addbrg']=array();
OPEN_BOX('','<span class=judul>'.getMenu('kebun_rkhx').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table border=0>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td><input style=\"width:150px;\" class=myinputtext id=notr ></td>
				
				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td>:</td>
				<td><select style=\"width:150px;\" onchange=loaddata(); id=divisis>" . $divisis. "</select></td>
			
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><input type='text' readonly=readonly  style='width:130px;' class='myinputtext' id='tglh' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
				
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset>";
echo"</tr></table> ";
	
CLOSE_BOX();
echo "</div>";
echo"<div id=listData style=display:block>";

OPEN_BOX();
	echo "
	
	<!--<div class='table-scroll'>-->
	<table class='sortable' cellspacing=1 cellpadding=5 border=0>
            <thead>
                <tr style=height:25px>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['asisten'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nikmandor1'] . "</th>
                    <th align=center rowspan=2>Permintaan<br>Barang</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['dibuat'] . "</th>
                    <th align=center rowspan=2 colspan=5>" . $_SESSION['lang']['action'] . "</th>
				</tr>
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
             </div>
	";
CLOSE_BOX();
echo "</div>";

$sql = "SELECT distinct periode FROM " . $dbname . ".sdm_5periodegaji where sudahproses='0' order by periode";
$res = fetchdata($sql);
$periode="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach($res as $bar){
    $periode.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

echo "<div id=header style=display:none>";
OPEN_BOX();
echo "<fieldset>
	<legend>Input</legend>
	<table>
		<tr>
			<td>" . $_SESSION['lang']['notransaksi'] . "</td>
			<td>:</td>
			<td><input style=\"width:150px;\" class=myinputtext id=notransaksi disabled></td>
			
			<td>" . $_SESSION['lang']['divisi'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onmousemove=hapuswarna(this.id); id=divisi>" . $divisi . "</select></td>
		
			<td>" . $_SESSION['lang']['nikasisten'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onmousemove=hapuswarna(this.id); id=asst>" . $asst . "</select></td>
			
			<td>" . $_SESSION['lang']['nikmandor1'] . "</td>
			<td>:</td>
			<td><select style=\"width:150px;\" onmousemove=hapuswarna(this.id); id=mandor1>" . $mdr1x . "</select></td>
		
			<td>" . $_SESSION['lang']['tanggal'] . "</td> 
			<td>:</td>
			<td><input type='text' readonly=readonly  style='width:100px;' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
			</td>
		</tr>
		<tr>
			<td></td><td></td>
			<td colspan=5>
				<input type=hidden id=pupukold><input type=hidden id=aplold><input type=hidden id=bulanold><input type=hidden id=tahunold>
				<input hidden id=method value='insert'>
				<input hidden id=methoddetail value='insert'>
				<button id=tomboldetailhtml class=mybutton onclick=previewdata()>" . $_SESSION['lang']['preview'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
</table>
</fieldset>
";
CLOSE_BOX();


echo "<div id=contpreview style=display:none>";
OPEN_BOX();
echo"<div id=contview></div>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>