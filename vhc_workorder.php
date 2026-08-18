<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/vhc_workorder.js?v=<?php echo time(); ?>'></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc_workorder').'</span>');

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$d=getNamaOrg($key,'induk');
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		$optorgx.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgx.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}

$arrjenis=['newasset'=>'Asset Baru','pemelasset'=>'Perbaikan Asset','nonasset'=>'Non Asset'];
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optjenisx="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrjenis as $key => $val){
	$optjenis.="<option value=".$key.">".$val."</option>";
	$optjenisx.="<option value=".$key.">".$val."</option>";
}

echo "<table>
   		<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	  			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
	  		</td>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	  			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	  		</td>
	 		<td>

			<fieldset>
 			<legend id=legend>Find</legend>
	 			<table>
	 				<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td>
					  	<td>:</td>
					  	<td><input class=myinputtext id=scnotransaksi onkeypress='enterkey(event,loaddata)' style='width:150px;'></td>

	 					<td>".$_SESSION['lang']['namaasset']."</td>
	 					<td>:</td>
					  	<td><input class=myinputtext id=scnamaasset onkeypress='enterkey(event,loaddata)' style='width:145px;'></td>
					  	
						<td>".$_SESSION['lang']['keterangan']."</td>
					  	<td>:</td>
					  	<td><input class=myinputtext id=scket onkeypress='enterkey(event,loaddata)' style='width:145px;'></td>
					
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kodeorg']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='sckodeorg' class='select2' style='width:155px;'>
					  			".$optorgx."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['jenis']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scjenis' class='select2' style='width:150px;'>
					  			".$optjenisx."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['posting']."</td>
	 					<td>:</td>
					  	<td>
							<select onchange=loaddata(); id='scpost' class='select2' style='width:150px;'>
					  			<option value=''>".$_SESSION['lang']['all']."</option>
					  			<option value='0'>Belum Posting</option>
					  			<option value='1'>Posting</option>
					  		</select>
					  	</td>
					
					</tr>
					<tr>
				  		<td></td><td></td>
				  		<td colspan=20>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
				  		</td>
					</tr>
				</table>
			</fieldset>
			</td>
		</tr>
	 </table>"; 
CLOSE_BOX();

echo "<div id=header style=display:none>";
OPEN_BOX();
$optsubkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".sdm_5tipeasset where kodetipe in ('BG','IL','IS','PA','PK','PR') order by kodetipe";
$res = fetchdata($str);
foreach($res as $bar){
	$optkelasset.="<option value='".$bar['kodetipe']."'>".$bar['kodetipe']." - ".$bar['namatipe']."</option>";
}

echo "<table style=margin-top:10px>";
	echo "<tr>";
		echo "<td valign=top>";
			echo "<fieldset><legend>Header</legend>";
			echo "<table id=tableht border=0>";
			echo "<tr>
					<td style=width:90px>".$_SESSION['lang']['notransaksi']."</td>
				  	<td>:</td>
				  	<td>
				  		<input name=ht[] id=notransaksi class=myinputtext style='width:200px;' disabled>
				  	</td>
					
					<td style=width:100px>".$_SESSION['lang']['kodeorg']."</td>
				  	<td>:</td>
				  	<td>
				  		<select name=ht[] id='kodeorg' onchange=gettipelokasi(); class='select2' style='width:205px;'>".$optorg."</select>
				  	</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
				  	<td>:</td>
				  	<td><input name=ht[] type=text id=tanggal class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:200px; value=".date("d-m-Y")."></td>
					
					<td>".$_SESSION['lang']['jenis']."</td>
				  	<td>:</td>
				  	<td>
				  		<select name=ht[] id='jenis' class='select2' onchange=getdetailcont(); style='width:205px;'>".$optjenis."</select>
				  	</td>
				</tr>
				<tr>
					<td>Nama Asset</td>
					<td>:</td>
					<td>
						<div id='divkodeasset' style=display:none;>
							<select name=ht[] id='kodeasset' onchange=getdetailasset(); class='select2' style='width:205px;'>".$optnamaasset."</select></div>
						<input name=ht[] id=namaasset class=myinputtext style='width:200px;display:none;'>
					</td>

					<td>Keterangan</td>
					<td>:</td>
					<td>
						<input name=ht[] id=keterangan class=myinputtext style='width:200px;'>
					</td>					
				</tr>
				<tr name=contnewasset[] style=display:none;>
					<td>Kelompok Asset</td>
					<td>:</td>
					<td>
						<select name=ht[] id='kelompokasset' onchange=getsubklasset(); class='select2' style='width:205px;'>".$optkelasset."</select>
					</td>
					
					<td>Sub Kel Asset</td>
					<td>:</td>
					<td>
						<select name=ht[] id='subkelasset' onchange=getkodeasset(); class='select2' style='width:205px;'>".$optsubkelasset."</select>
					</td>
				</tr>
				<tr name=contnewasset[] style=display:none;>
					<td>Tipe Lokasi</td>
					<td>:</td>
					<td>
						<select name=ht[] id='tipelokasi' ".$disabled." class='select2' style='width:205px;'>".$optipelok."</select>
					</td>
					
					<td>Jumlah</td>
					<td>:</td>
					<td nowrap>
						<input name=ht[] id=jumlah class=myinputtextnumber onkeypress='return angka_doang(event)' style='width:92px;'>
						<select name=ht[] id='satuan' class='select2' style='width:105px;'><option value='UNIT' selected>UNIT</option></select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggalmulai']."</td>
				  	<td>:</td>
				  	<td nowrap>
						<input name=ht[] type=text placeholder='Tanggal Dari' id=tanggaldari class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:90px;>s/d<input name=ht[] placeholder='Tanggal Sampai' type=text id=tanggalsampai class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:90px;>
						</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=method value=insert>
						<input type=hidden id=id value=''>
						<button id=simpanht class=mybutton onclick=\"simpan();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"reset();\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>";
			echo "</table>";
			echo "</fieldset>";
		echo "</td>";
	echo "</tr>";
echo "</table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=detail style=display:none>";
OPEN_BOX('','<span class=judul></span>');
echo "<div id=container></div>";
CLOSE_BOX();
echo"</div>";

#= buat data tersimpan
echo"<div id=loadpreview>";
OPEN_BOX('','<span class=judul></span>');
echo "<div id=listdata style=height:65vh><script>loaddata(0);</script></div>";
CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan
echo close_body();
?>