<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script type='text/javascript' src='pivottable-master/dist/jquery.min.js'></script>
<script type='text/javascript' src='pivottable-master/dist/jquery-ui.min.js'></script>
<script type='text/javascript' src='pivottable-master/dist/papaparse.min.js'></script>
<script language=javascript1.2 src='js/sdm_5shiftanggota.js?v=<?php echo time(); ?>'></script>
	
<script type='text/javascript' src='lib/select2/js/select2.js'></script>
<link href='lib/select2/css/select2.css' rel="stylesheet">
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<style>
 html{}
	.whiteborder {border-color: white;}
	.greyborder {border-color: lightgrey;border: 3px dotted;}
</style>
<script type="text/javascript">
	$(function(){
		var parseAndPivot = function(f) {
			Papa.parse(f, {
				skipEmptyLines: true,
				error: function(e){ alert(e) },
				complete: function(parsed){
					param     = 'data=' + JSON.stringify(parsed.data);
					kodeorg   = document.getElementById('kodeorg').value;
					subbagian = document.getElementById('subbagian').value;
					departemen= document.getElementById('departemen').value;
					periode   = document.getElementById('periode').value;
					
					validate([
						["kodeorg","Kode organisasi tidak boleh kosong"],
						["periode","Periode tidak boleh kosong"]
					]);
					
					param += '&method=loaddatadetail';
					param += '&periode=' + periode + '&kodeorg=' + kodeorg;
					param += '&subbagian=' + subbagian + '&departemen=' + departemen;
					param += '&sumber=upload';
					
					tujuan = 'sdm_slave_5shiftanggota.php';
					post_response_text(tujuan, param, respog);
					function respog() {
						if (con.readyState == 4) {
							if (con.status == 200) {
								busy_off();
								if (!isSaveResponse(con.responseText)) {
									alertify.alert(con.responseText);
								} else {
									document.getElementById('contdetail').style.display = 'block';
									$("#detail").html(con.responseText);
								}
							} else {
								busy_off();
								error_catch(con.status);
							}
						}
					}
				}
			});
		};

		$("#csv").bind("change", function(event){
			var name = event.target.files[0].name;
			var file_ext = name.substr(name.lastIndexOf('.')+1,name.length);
			if (file_ext.toLowerCase()!='csv'){
				alert("File harus .csv");
                throw Error('Stop!');
            }
			parseAndPivot(event.target.files[0]);
		});

		$("#textarea").bind("input change", function(){
			parseAndPivot($("#textarea").val());
		});

		var dragging = function(evt) {
			evt.stopPropagation();
			evt.preventDefault();
			evt.originalEvent.dataTransfer.dropEffect = 'copy';
			$("html").removeClass("whiteborder").addClass("greyborder");
		};

		var endDrag = function(evt) {
			evt.stopPropagation();
			evt.preventDefault();
			evt.originalEvent.dataTransfer.dropEffect = 'copy';
			$("html").removeClass("greyborder").addClass("whiteborder");
		};

		var dropped = function(evt) {
			evt.stopPropagation();
			evt.preventDefault();
			$("html").removeClass("greyborder").addClass("whiteborder");
			var name = evt.originalEvent.dataTransfer.files[0].name;
			var file_ext = name.substr(name.lastIndexOf('.')+1,name.length);
			if (file_ext.toLowerCase()!='csv'){
				alert("File harus .csv");
                throw Error('Stop!');
            }
			parseAndPivot(evt.originalEvent.dataTransfer.files[0]);
		};

		$("html")
			.on("dragover", dragging)
			.on("dragend", endDrag)
			.on("dragexit", endDrag)
			.on("dragleave", endDrag)
			.on("drop", dropped);
	 });
</script>
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
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgx.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}

$where="";
$optdept="<option value=''>".$_SESSION['lang']['all']."</option>";
$opsubb = "<option value=''>".$_SESSION['lang']['all']."</option>";
$opsubb.="<option value='UMUM'>UMUM</option>";	
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 ".$where."";
$res = fetchData($str);
foreach($res as $key => $val){
	$opsubb.="<option value=".$val['kodeorganisasi']." ".$n.">".$val['namaorganisasi']."</option>";	
}

$optjab = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5jabatan where aktif=1";
$res = fetchData($str);
foreach($res as $key => $val){
	$optjab.="<option value=".$val['kodejabatan'].">".$val['namajabatan']."</option>";	
}

$periode="<option value=''>&nbsp;</option>";
$periodex="<option value=''>Pilih Data</option>";
for($x=-2;$x<20;$x++){
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$periode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
	$periodex.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5shiftanggota').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td>
					<td>:</td>
					<td><input style=\"width:146px;\" type=text id=tahunsch class=myinputtext /></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class='select2' id=kodeorgsch onchange=loaddata(0); style=\"width:150px;\">" . $optorgx . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>
				</tr>
			</table>
		</fieldset>
	</td>
	<td>
		<fieldset><legend>Copy</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['dari'] . "</td>
					<td>:</td>
					<td><select class='select2' style=\"width:150px;\" id=periodedari>" . $periodex . "</select></td>
					
					<td>" . $_SESSION['lang']['ke'] . "</td>
					<td>:</td>
					<td><select class='select2' style=\"width:150px;\" id=periodeke>" . $periodex . "</select></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class='select2' id=kodeorgcopy style=\"width:150px;\">" . $optorg . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=copy(0)>Copy</button></td>
				</tr>
			</table>
		</fieldset>	
	</td>	
		
     </tr></table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=listData style=display:block>";
OPEN_BOX();
echo "
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
			<th align=center>" . $_SESSION['lang']['unit'] . "</th>
			<th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
			<th align=center>" . $_SESSION['lang']['subbagian'] . "</th>
			<th align=center>" . $_SESSION['lang']['periode'] . "</th>
			<th align=center>" . $_SESSION['lang']['updateby'] . "</th>
			<th align=center colspan='5'>" . $_SESSION['lang']['action'] . "</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table>
";
CLOSE_BOX();
echo "</div>";


echo "<div id=header style=display:none>";
OPEN_BOX();
echo "<fieldset style=float:left>
	<legend>Input</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:200px;\" id=kodeorg onchange=getsubbagian('kodeorg','');>" . $optorg . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['subbagian'] . "</td>
			<td>:</td>
			<td><select style=\"width:200px;\" id=subbagian onchange=getsubbagian('subbagian',this.value);>" . $opsubb . "</select></td>
		</tr>
		<tr hidden>
			<td>" . $_SESSION['lang']['departemen'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:200px;\" id=departemen>" . $optdept . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['jabatan'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:200px;\" id=jabatan>" . $optjab . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['periode'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:200px;\" id=periode>" . $periode . "</select></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button id=tomboldetail class=mybutton onclick=loaddatadetail()>" . $_SESSION['lang']['preview'] . "</button>
				
				<button class=mybutton id=formuploaddt onclick=formupload()>Template</button>
				
				<button class=mybutton><label  id='filechooser' style=cursor:pointer;color:blue;>Upload<input id='csv' type='file' style='display:none'/></label></button>
				<textarea style='display:none' placeholder='atau ketik atau drag atau paste CSV text disini' style='width: 300px;' id='textarea'></textarea>
			</td>
			<input type=hidden id=method value='insert'>
		</tr>
	</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

echo"<div id=contdetail style=display:none>";
OPEN_BOX();
echo"<div id=detail class='table-scroll' style=height:60vh></div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>