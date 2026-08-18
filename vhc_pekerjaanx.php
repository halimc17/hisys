<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/vhc_pekerjaanx.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('vhc_pekerjaanx').'</span>','judul_header');
# === Header dan Pencarian data ===

$optStatusLst="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrStatus=array("0"=>"Belum Posting","1"=>"Sudah diposting");
foreach($arrStatus as $lstStatus=>$vwStatus){
    $optStatusLst.="<option value='".$lstStatus."'>".$vwStatus."</option>";
}

echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table border=0>
		 <tr>
			<td>".$_SESSION['lang']['notransaksi']." </td>
			<td><input type=\"text\" id='txtCari' onkeypress='return enter(event);' name='txtCari' style='width:130px' class=myinputtext /></td>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td><input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 /></td>
			<td>".$_SESSION['lang']['kodevhc']."</td>
			<td><input type=text class=myinputtext id=kodevhc_cari onkeypress='return enter(event);' style='width:130px'></td>
			
			<td>".$_SESSION['lang']['status']." <select id=statusInputan>".$optStatusLst."</select></td>
			</tr>";

echo"<tr><td></td><td colspan=5><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td></td></tr></table>";
echo"</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo "<fieldset>
		<legend>".$_SESSION['lang']['list'] . "</legend>
		<div>    
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
					<tr class=rowheader>
						<td align=center width=50px>" . $_SESSION['lang']['nourut'] . "</td>
						<td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center >" . $_SESSION['lang']['jenisvch'] . "</td>
						<td align=center >" . $_SESSION['lang']['kodevhc'] . "</td>
						<td align=center >" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center >" . $_SESSION['lang']['vhc_jenis_bbm'] . "</td>
						<td align=center >" . $_SESSION['lang']['vhc_jumlah_bbm'] . "</td>
						<td align=center >" . $_SESSION['lang']['vhc_kmhm_awal'] . "</td>
						<td align=center >" . $_SESSION['lang']['vhc_kmhm_akhir'] . "</td>
						<td align=center >" . $_SESSION['lang']['jumlah'] . "</td>
						<td align=center >" . $_SESSION['lang']['updateby'] . "</td>
						<td align=center colspan='5'>" . $_SESSION['lang']['action'] . "</td>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>
	 </fieldset>";
CLOSE_BOX();
echo "</div>";

$optOrg2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
$res=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$res->fetch()){
	$optOrg2.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['kodeorganisasi']." - ".$rOrg2['namaorganisasi']."</option>";
}

$optJnsvhc='';
$optJnsvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sjvch="select jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc order by namajenisvhc";
$res=$owlPDO->query($sjvch) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rjvch=$res->fetch()){
	$optJnsvhc.="<option value=".$rjvch['jenisvhc'].">".$rjvch['jenisvhc']."-".$rjvch['namajenisvhc']."</option>";
}

$optvhc='';
$sjvch="select * from ".$dbname.".vhc_5master where status=1  order by kodevhc";
$resx=$owlPDO->query($sjvch) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_ASSOC);
while($res=$resx->fetch()){
	$optvhc.="<option value='".$res['kodevhc']."'>".$res['kodevhc']." ".($res['nopol']!=''?"- ".$res['nopol']:'')." - ".$res['kodetraksi']." ".($res['detailvhc']!=''?"- ".$res['detailvhc']:'')."</option>";
}

$optTraksi='';
$optTraksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$strak="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe = 'TRAKSI' order by namaorganisasi ";
$res=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rtrak=$res->fetch()){
	if(substr($rtrak['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
		$optTraksi.="<option value=".$rtrak['kodeorganisasi']." selected>".$rtrak['kodeorganisasi']."-".$rtrak['namaorganisasi']."</option>";
	}else{		
		$optTraksi.="<option value=".$rtrak['kodeorganisasi'].">".$rtrak['kodeorganisasi']."-".$rtrak['namaorganisasi']."</option>";
	}
}

$optJnsBBMvhc='';
$where=" `kelompokbarang` = '351'";
$sbrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where ".$where."";
$res=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rbrg=$res->fetch()){
	if($rbrg['kodebarang']==351010003){
		$optJnsBBMvhc.="<option value='".$rbrg['kodebarang']."' selected>".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}else{
		$optJnsBBMvhc.="<option value=".$rbrg['kodebarang'].">".$rbrg['kodebarang']."-".$rbrg['namabarang']."</option>";
	}
}

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>".$_SESSION['lang']['kodevhc']."</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input type=text id=no_trans name=no_trans disabled=disabled class=myinputtext style=width:145px; /></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select id=KbnId name=KbnId onchange=\"get_notransaksi()\" style=width:150px>".$optOrg2."</select></td>
				
				<td>&nbsp;".$_SESSION['lang']['kodetraksi']."</td> 
				<td>:</td>
				<td><select id=kodetraksi name=kodetraksi style=width:150px; onchange=\"get_kd('')\">".$optTraksi."</select>
					<img id='kodetraksi' onclick=z.elSearch('kodetraksi',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
					
				<td>&nbsp;".$_SESSION['lang']['jenisvch']."</td> 
				<td>:</td>
				<td><select id=jns_vhc name=jns_vhc style=width:150px; onchange=\"get_kd('')\">".$optJnsvhc."</select>
					<img id='jns_vhc' onclick=z.elSearch('jns_vhc',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				
			</tr> 
			<tr>
				<td>".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text  class=myinputtext style='width:145px;' id=tgl_pekerjaan onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 /></td>
				
				<td>&nbsp;".$_SESSION['lang']['kodevhc']."</td> 
				<td>:</td>
				<td><select id=kde_vhc name=kde_vhc style=width:150px;><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optvhc."</select>
				<img id='kde_vhc' onclick=z.elSearch('kde_vhc',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				
				<td>&nbsp;".$_SESSION['lang']['vhc_jenis_bbm']."</td> 
				<td>:</td>
				<td><select id=jns_bbm name=jns_bbm style=width:150px;>".$optJnsBBMvhc."</select></td>
				
				<td >&nbsp;" . $_SESSION['lang']['jumlah'] . "</td> 
				<td >:</td>
				<td ><input type=text class=myinputtextnumber id=jmlh_bbm name=jmlh_bbm maxlength=60 value=0 onkeypress=\"return angka_doang(event);\" style=width:145px; /></td>";
				
		echo"</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=addHeader()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=method value='insert_header'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
echo"<div id=detailx style=display:none>";
OPEN_BOX();
echo"<div id=detail style=display:none>";
echo"</div>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>