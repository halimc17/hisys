<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/bgt_prdpabrik.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(13) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}

$optthnpost="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tahunbudget from ".$dbname.".bgt_produksi_pks_vw order by tahunbudget desc";
$res = fetchdata($str);
foreach($res as $bar){
    @$optthn.="<option value='".$bar['tahunbudget']."'>".$bar['tahunbudget']."</option>";
    $optthnpost.="<option value='".$bar['tahunbudget']."'>".$bar['tahunbudget']."</option>";
}

$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
$datatipe = array('0'=>'Belum disebarkan','1'=>'Sudah disebarkan');
foreach($datatipe as $d => $v){
	$optgol.="<option value=".$d.">".$v."</option>";
}
$optip="<option value=''>".$_SESSION['lang']['all']."</option>";
$datatipe = array('I'=>'INTI','P'=>'PLASMA');
foreach($datatipe as $d => $v){
	$optip.="<option value=".$d.">".$v."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('bgt_prdpabrik').'</span>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:75px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=add_sebaran()>
			<img class=delliconBig src=images/archive.png title='".$_SESSION['lang']['posting']."'><br>".$_SESSION['lang']['posting']."
		</td>
		<td valign=middle>
			<div id=formcari>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunsch onchange=loaddata(0); style=\"width:150px;\">" . $optthn . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgsch onchange=loaddata(0); style=\"width:150px;\">".$optorgsch."</select></td>
							
							<td>" . $_SESSION['lang']['sebaran'] . "</td>
							<td>:</td>
							<td><select id=sebaransch onchange=loaddata(0); style=\"width:150px;\">" . $optgol . "</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton id=btnprev onclick=loaddata(0)>" . $_SESSION['lang']['preview'] . "</button>
								<button class=mybutton id=btnexcel onclick=loadexcel(0)>" . $_SESSION['lang']['excel'] . "</button>
								<button class=mybutton id=btncari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id=formcariposting style=display:none>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select id=tahunpostsch onchange=showposting(0); style=\"width:150px;\">" . $optthnpost . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select id=kodeorgpostsch onchange=showposting(0); style=\"width:150px;\">".$optorgsch."</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton onclick=showposting(0)>" . $_SESSION['lang']['preview'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
		</td>
		</tr></table>";

echo "</div>";
CLOSE_BOX();

echo"<div id=inputdata style=display:none>";
OPEN_BOX();

$optorg="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";
echo"
	<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['budgetyear'] . "</td>
				<td>:</td>
				<td><input type=text class=myinputtextnumber id=tahun maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:145px;></td>
				
				<td>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td>:</td>
				<td><select id=kodeorg style=\"width:150px;\">".$optorg."</select></td>
			</tr>
			
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=adddata()>" . $_SESSION['lang']['preview'] . "</button>
					<button class=mybutton onclick=bataladd()>" . $_SESSION['lang']['cancel'] . "</button>
				</td><td colspan=3>	
					<button class=mybutton id=formuploaddt style=display:none;width:240px;align:center;background-color:transparent;border:1px dotted; onclick=formupload()>&nbsp;</button>
					
					<label  id='filechooser' style=cursor:pointer;color:blue;display:none>Click disini<input id='csv' type='file' style='display:none'/></label>
					<textarea style='display:none' placeholder='atau ketik atau drag atau paste CSV text disini' style='width: 300px;' id='textarea'></textarea>
				</td>
			</tr>
		</table>
	</fieldset>
	";
CLOSE_BOX();
echo "</div>";



$bulan=range(1,12);

$optjns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$datatipe = array('1'=>'Internal','2'=>'Afiliasi','0'=>'External');
foreach($datatipe as $d => $v){
	$optjns.="<option value=".$d.">".$v."</option>";
}

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".organisasi where tipe ='KEBUN' order by induk asc";
$res = fetchdata($str);
foreach($res as $bar){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
    $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

$str="select * from ".$dbname.".log_5supplier order by namasupplier  asc";
$res = fetchdata($str);
$optunit.="<optgroup label='EXTERNAL'>";
foreach($res as $bar){
    $optunit.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}
$optunit.="</optgroup>";

$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			
if($tipeorg[$_SESSION['empl']['lokasitugas']]=='BULKING'){
	$disable="disabled";
}else{
	$disable="";
}
#untuk inputan baru
echo"<div id=contdetail style=display:none;>";
OPEN_BOX();
	echo"<table class='sortable' cellspacing=1 cellpadding=2 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>
			<th align=center colspan=2>".$_SESSION['lang']['sumber']."</th>
			<th align=center>".$_SESSION['lang']['tbs']." (Kg)</th>
			<th align=center>OER<br>(%)</th>
			<th align=center>KER<br>(%)</th>
			";
			foreach($bulan as $bln){				
				echo"<th align=center width=40px>".numToMonth($bln,'E','short')."<br>(%)</th>";
			}
	echo"</tr>
	</thead>
	<tbody>
		<tr class='rowcontent'>
		<td style='text-align:center'>01</td>
		<td><select class=select2 id=jenis onchange=getunit(); style=\"width:100px;\">".$optjns."</select></td>
		<td><select class=select2 id=kodeunit onchange=gettbskebun(); style=\"width:160px;\">".$optunit."</select></td>
		<td><img id='kodeunit' onclick=z.elSearch('kodeunit',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
		<td><input type=number min='0' onkeypress=\"return isNumberKey2(event);\" class=myinputtextnumber style='width:90px' id=kgtbs onkeypress='return angka_doang(event)'></td>
		<td><input type=number min='0' ".$disable." class=myinputtextnumber style='width:55px' id=oerpersen onkeypress='return angka_doang(event)'></td>
		<td><input type=number min='0' ".$disable." class=myinputtextnumber style='width:55px' id=kerpersen onkeypress='return angka_doang(event)'></td>";
		foreach($bulan as $bln){				
			echo"<td align=center><input type=text onkeypress=\"return isNumberKey2(event);\" class=myinputtextnumber style='width:55px' onkeyup='calcTotalMonth({$bln})' id=calc-".$bln." ></td>";
		}
		echo"</tr>";
		echo"<tr class='rowcontent'>";
		echo"<td style='text-align:center' colspan='7'></td>";
			foreach($bulan as $bln){
				echo"<td align=center>
						<input type=text class=myinputtextnumber style='width:55px;' id=kg".$bln." readonly disabled>
					</td>";
			}
		echo"</tr>";
		echo"<tr class='rowcontent'>";
		echo"<td colspan=19 align=center><button style=width:100px class=mybutton onclick=simpan(); title=\"Save\">Save</button></td>";
		echo"</tr>";
	echo"</tbody>
	</table>";
CLOSE_BOX();	
echo"</div>";


OPEN_BOX();
#cont posting
echo"<div id=contposting style=display:none;>
	<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['tbs']."<br>(Kg)</th>
			<th align=center>".$_SESSION['lang']['oer']."<br>(%)</th>
			<th align=center>KER<br>(%)</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>";
			foreach($bulan as $bln){				
				echo"<th align=center>".numToMonth($bln,'E','short')."</th>";
			}
		echo"<th width=30px align=center>Action</th>
		</tr>
	</thead>
	<tbody id=contpostingdata></tbody>
	</table></div>";
echo"</div>";

#list data
echo"<div id=listData style=display:block>";
echo"<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>
			<th align=center>".$_SESSION['lang']['sumber']."</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['tbs']."<br>(Kg)</th>
			<th align=center>".$_SESSION['lang']['oer']."<br>(%)</th>
			<th align=center>KER<br>(%)</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>";
			foreach($bulan as $bln){				
				echo"<th align=center>".numToMonth($bln,'E','short')."</th>";
			}
		echo"<th align=center colspan=2>Action</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table>";
	
echo "</div>";
echo "</div>";

CLOSE_BOX();
echo close_body();
?>