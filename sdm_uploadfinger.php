<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_uploadfinger').'</span>');
require_once('lib/zSelect2.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language="javascript" src="js/sdm_uploadfinger.js?v=<?php echo time(); ?>"></script>
<?php

##GET UNIT
$optunit="";
$arrorgdet = getOrgDetail(1);
$no=0;
foreach($arrorgdet as $key=>$val){
	$no++;
	if($no==1){
		$wunit=$key;
	}
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";	
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

##PENCARIAN
echo "<div id='action_list'>
	<table>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
				<img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
			<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['find']."</legend>
					<table>
						<tr>
							<td>Unit</td>
							<td>:</td>
							<td><select class=select2 style=width:200px id='unitsc'>".$optunit."</select></td>
							
							<td style='padding-left:10px'>".$_SESSION['lang']['tanggal']." upload</td>
							<td>:</td>
							<td>
								<input type='text' class='myinputtext' id='tanggalsc' value='' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center' />
							</td>
							<td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table> 
</div>";

CLOSE_BOX();

echo "<div id=\"list_ba\">";
OPEN_BOX();

echo"
	
	<div style='overflow:auto;'>
	<table class='sortable' cellspacing='1' cellpadding=5 border='0' style='width:100%;'>
		<thead>
		<tr class=rowheader>
			<th align='center'>No.</th>
			<th align='center'>".$_SESSION['lang']['unit']."</th>
			<th align='center'>".$_SESSION['lang']['tanggal']." Upload</th>
			<th align='center'>".$_SESSION['lang']['createby']."</th>
			<th align='center'>".$_SESSION['lang']['createtime']."</th>
			<th align='center'>".$_SESSION['lang']['status']."</th>
			<th align='center' colspan=5>Action</th>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loaddata(0)</script>
		</tbody>
	 </table>
	</div>
";

CLOSE_BOX();
echo"</div>";


echo"<div id='form_ba' style='display:none;'>";
OPEN_BOX();


##Tanggal
$as=date("Y-m-d");

echo"<fieldset>
	<legend>".$_SESSION['lang']['header']." <label id=lblmethod>(New)</lbl></legend>
	<input type='hidden' value='' id='idx'/>
	<table cellspacing='1' cellpadding=2 border='0'>

	<tr>
			<td style='vertical-align:top'>No. BA</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<input type=text class=myinputtext disabled id='noba' style=width:195px; onkeypress=\"return tanpa_kutip(event)\" onkeydown=\"upperCaseF(this)\">
			</td>
			<td style='padding-left:20px;vertical-align:top' rowspan=5>
					<fieldset>
					<legend>".$_SESSION['lang']['info']."</legend>
					<table cellspacing='1' border='0'>
						<tr>
							<td>
							=> No. BA Akan otomatis terisi</b>
							<br>=> <b>Form dapat di download disini </b>&nbsp;<a href='fileupload/uploadfinger.xlsx'  title='uploadfinger.xlsx'>Klik disini untuk mendapatkan contoh file</a>
							</td>
						</td>
						</tr>
					</table>
					</fieldset>
				</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:200px id='unit'>".$optunit."</select>
			</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['tanggal']." Upload</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggal' value='".tanggalnormal($as)."' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:195px;text-align:center' disabled/>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['file']." (.xlsx)</td>
			<td>:</td>
			<td>
				<input name='filex' type='file' id='filex' size='25' class='mybutton'>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert' />
				<button class=mybutton onclick=\"simpan()\">".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=\"batal()\">".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";

##div persetujuan##
echo"<div id='persetujuan' style='display:none;'>";
OPEN_BOX();
echo"<div id='persetujuandata'></div>";

CLOSE_BOX();
echo "</div>";
echo close_body();

?>