<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src='js/sdm_3ctbs.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

##tipekaryawan
$opttpkar="<option value=''>".$_SESSION['lang']['all']."</option>";


$str="select * from ".$dbname.".sdm_5tipekaryawan where id !='4' and aktif='1' order by id";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$opttpkar.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
}

##jenis jenis izin
$optJns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select idjenis,jenisijin from ".$dbname.".sdm_5jenisijin where statuspotongan in ('1','2') and status ='1' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optJns.="<option value='".$bar['idjenis']."'>".$bar['jenisijin']."</option>";
}

##periode
$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct periode from ".$dbname.". sdm_5periodegaji where sudahproses=0 and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPer.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

##karyawan
$iKar="select namakaryawan,karyawanid,nik,subbagian,lokasitugas from ".$dbname.".datakaryawan   order by namakaryawan";
$nKar=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
$nKar->setFetchMode(PDO::FETCH_ASSOC);
$optKar="<option value=''>Pilih Data</option>";
while($dKar=$nKar->fetch()){
	$optKar.="<option value='".$dKar['karyawanid']."'>".$dKar['nik']." - ".$dKar['namakaryawan']."</option>";
}

// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
// 	$where = "";
// }else {
// 	$where = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."'";
// }

// $optOrg="";
// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by kodeorganisasi asc ";
// $res=fetchdata($str);
// foreach($res as $bar){
// 	$optOrg.="<optgroup label='".$bar['namaorganisasi']."'>";
// 	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }


$optOrg="<option value=''>Pilih Data</option>";
foreach(getOrgDetail(1) as $key => $val){
	$d=getNamaOrg($key,'induk');
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	if($key==$_SESSION['empl']['lokasitugas']){
		$optOrg.="<option value=".$key." selected>".$key." - ".$val."</option>";
	}else{		
		$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	}
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

?>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('sdm_3ctbs').'</span>');


echo"<table>
     <tr valign=middle>";
echo"<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>";
echo"<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset id=formpencarianheader><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
                <input type='text' style='width:100px;' class='myinputtext' id='tanggalxsch' value=''  onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
			</td>
		</tr>";
echo"<tr>
		<td colspan=2></td>
		<td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
			<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>";

echo"</fieldset></table><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo"<fieldset style=min-height:400px><legend><b>".$_SESSION['lang']['list']."</b></legend>
	<div>    
		<table cellpadding=5 cellspacing=1 border=0 style='width:100%;' class=sortable>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kodeorg']."</td>
				<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['dibuat']."</td>
				<td align=center colspan=5>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
			<tbody id=container> 
				<script>loadData(0)</script>
			</tbody>
			<tfoot id=footData>
			</tfoot>
		 </table>
		 </div>
		 
</div></fieldset>"; 
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style=display:none>";
OPEN_BOX();


echo"<fieldset><legend><b>Form</b></legend>
<table border=0 style='display: inline-block;vertical-align:top'>
	<input hidden id=stsawal value=''>
	<input hidden id=methodheader value='insertheader'>
    <tr>
		<td>".$_SESSION['lang']['kodeorg']."</td> 
		<td>:</td>
		<td><select id=org style=\"width:150px;\">".$optOrg."</select></td>

		<td>".$_SESSION['lang']['jenis']."</td> 
		<td>:</td>
		<td><select id=kom style=\"width:150px;\">".$optJns."</select></td>
	</tr> 

	<tr>
		<td>".$_SESSION['lang']['tipekaryawan']."</td> 
		<td>:</td>
		<td><select id=tipekar style=\"width:150px;\">".$opttpkar."</select></td>

		<td>".$_SESSION['lang']['tanggal']."</td> 
		<td>:</td>
		<td><input type='text' style='width:100px;' class='myinputtext' id='tanggalx' value=''  onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' /></td>


	</tr>
	<tr>
		<td>".$_SESSION['lang']['keterangan']."</td> 
		<td>:</td>
		<td colspan='4'><input type='text' style='width:350px;' class='myinputtext' id='keterangan' value=''  onkeypress=\"return_tanpa_kutip(event);\" /></td>
	</tr> 
	<tr>
		<td><td><td>
		<button class=mybutton id=saveHeader onclick=saveHeader()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton id=cancelHeader  onclick=cancelHeader()>".$_SESSION['lang']['cancel']."</button>	
		</td>
	</tr> 
	";
echo"</table>";
echo"</fieldset>";

echo"<div id='displayinsert' style=display:none></div>";
#echo"</div>";
echo"<div id='inputdetail' style=display:none>
		<fieldset><legend><b>Form</b></legend>
	<table border=0 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td> 
			<td>:</td>
			<td><select id=kar style=\"width:150px;\"></select>
				<img id='kar' onclick=z.elSearch('kar',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				</td>
			
		</tr>	
		<tr>	
			<td></td><td><input id=saveDetail value='saveDetail' hidden></td>
			<td><button class=mybutton onclick=saveDetail()>".$_SESSION['lang']['save']."</button></td>
		</tr> 
		</table></fieldset>
	</div>";

echo"<div id='loaddatadetail' style=display:none></div>";
echo"</div>";
CLOSE_BOX();
echo close_body();		
?>