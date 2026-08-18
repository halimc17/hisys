<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
?>
<script src='js/sdm_pengajuantiket.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

$where="";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where.=" and kodeorganisasi like '%HO%'";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where.=" and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
	$where.=" and kodeorganisasi like '%RO%'";
} else {
	$where.=" and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by induk, namaorganisasi asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$d=$bar['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
		$optorgx.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
	}
	if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){		
		$optorg.="<option value=".$bar['kodeorganisasi']." selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$optorgx.="<option value=".$bar['kodeorganisasi']." selected>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}else{
		$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$optorgx.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}

$str = "SELECT *  FROM " . $dbname . ".sdm_5jenisbiayapjdinas where tiket='1' and status='1' order by id";
$res = fetchdata($str);
foreach ($res as $bar){
	if($bar['id']=='3'){
		@$optjenis.="<option value=".$bar['id']." selected>".$bar['keterangan']."</option>";
	}else{		
		@$optjenis.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
	}
}

$asumber=array('1'=>'Karyawan (Cuti)','2'=>'Lainnya (Tamu, dll)');
$optsumber="<option value=''></option>";	
foreach ($asumber as $bar =>$val){
	@$optsumber.="<option value=".$bar.">".$val."</option>";	
}





OPEN_BOX('','<span class=judul>'.getMenu('sdm_pengajuantiket').'</span><br>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>
	 <td align=center style='width:80px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:80px;cursor:pointer;display:none;' onclick=viewlist()>
	   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['preview'] . "'><br>" . $_SESSION['lang']['preview'] . "</td>
	 <td>
	 
	 <td align=center style='width:80px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader ><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td>" . $_SESSION['lang']['kodeorg'] . "</td> 
				<td>:</td>
				<td><select id=kodeorgsch onchange='loaddata()' style=\"width:135px;\">".$optorgx."</select></td>
			
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglsch' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['noreferensi'] . "</td> 
				<td>:</td>
				<td><input type='text' onkeypress='enterkey(event,loaddata)' style='width:130px;' class='myinputtext' id='nopjdsch'>
				</td>
				
				<td>" . $_SESSION['lang']['nama'] . "</td> 
				<td>:</td>
				<td><input type='text' onkeypress='enterkey(event,loaddata)' style='width:130px;' class='myinputtext' id='namasch'>
				</td>
				
				<td>" . $_SESSION['lang']['sumber'] . "</td> 
				<td>:</td>
				<td><select id=sumbersch onchange='loaddata()' style=\"width:135px;\">".$optsumber."</select></td>
				
			</tr>
			<tr>
				<td><td>
				<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button></td>
				</td>
			</tr>
		</table>
	</fieldset>
	</td>
	</tr></table> ";
echo "</div>";
CLOSE_BOX();

echo"<div id=listdata style=display:block>";
OPEN_BOX();
echo "
		<div>    
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr class=rowheader style=height:25px>
						<th align=center width=30px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center>".$_SESSION['lang']['notransaksi']."</th>
						<th align=center>".$_SESSION['lang']['kodeorg']."</th>
						<th align=center>".$_SESSION['lang']['sumber']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>" . $_SESSION['lang']['updateby'] . "</th>
						<th align=center>" . $_SESSION['lang']['status'] . "</th>
						<th align=center colspan='6'>" . $_SESSION['lang']['action'] . "</th>
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

$optsumber="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optsumber.="<option value='1'>Karyawan (Cuti)</option>";
$optsumber.="<option value='2'>".$_SESSION['lang']['lain']." (Tamu, dll)</option>";



# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('','','header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
			</tr> 
			<tr>
				<td>".$_SESSION['lang']['sumber'] . "</td> 
				<td>:</td>
				<td><select onmousemove=hapuswarna(this.id); style=\"width:150px;\" id=sumber>" . $optsumber . "</select></td>

				<td>&nbsp;".$_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id);hapuswarna(this.id); onkeypress=return false; maxlength=10 /></td>
				
			</tr> 
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=previewdata()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

echo "<div id=viewlist style=display:none;>";
OPEN_BOX();
$rows="rowspan=2";	
echo "<fieldset>
		<legend>List Data Pengajuan Tiketing / Perjalanan Dinas</legend>
		<table cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr class=rowheader style=height:25px>
						<td align=center ".$rows." width=20px>No</td>
						<td align=center ".$rows.">".$_SESSION['lang']['nomor']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['kodeorg']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['nama']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['kodegolongan']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['tujuan']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['keterangan']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['tanggal']."</td>
						<td align=center ".$rows." >".$_SESSION['lang']['tgldibutuhkan']."</td>
						<td align=center ".$rows.">".$_SESSION['lang']['action']."</td>
				</thead>
				<tbody id=containview></tbody>
				<tfoot id=footDataview></tfoot>
			</table>
	</fieldset>
	
	";
CLOSE_BOX();
echo"</div>";

# === Form Detail Input Data ===
echo"<div id=detail_cont style=display:none>";
OPEN_BOX();
echo"<div id=detail></div>";
CLOSE_BOX();
echo"</div>";


echo close_body();
?>