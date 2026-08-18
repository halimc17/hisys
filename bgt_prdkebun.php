<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/bgt_prdkebun.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>

<script type='text/javascript' src='pivottable-master/dist/jquery.min.js'></script>
<script type='text/javascript' src='pivottable-master/dist/jquery-ui.min.js'></script>
<script type='text/javascript' src='pivottable-master/dist/papaparse.min.js'></script>
	
<script type='text/javascript' src='lib/select2/js/select2.js'></script>
<link href='lib/select2/css/select2.css' rel="stylesheet">
<style>
 html{}
	.whiteborder {border-color: white;}
	.greyborder {border-color: lightgrey;border: 3px dotted;}
</style>

<script type="text/javascript">
	$(function(){
		var parseAndPivot = function(f) {
			//$("#output").html("<p align='center' style='color:grey;'>(processing...)</p>")
			Papa.parse(f, {
				skipEmptyLines: true,
				error: function(e){ alert(e) },
				complete: function(parsed){
					param  = 'data=' + JSON.stringify(parsed.data);
					tahun  = document.getElementById('tahun').value;
					kodeorg= document.getElementById('kodeorg').value;
					divisi = document.getElementById('divisi').value;
					tt     = document.getElementById('tt').value;
					
					
					param += '&method=adddata';
					param += '&tahun=' + tahun + '&kodeorg=' + kodeorg;
					param += '&divisi=' + divisi + '&tt=' + tt;
					param += '&sumber=upload';
					
					tujuan = 'bgt_slave_prdkebun.php';
					post_response_text(tujuan, param, respog);
					function respog() {
						if (con.readyState == 4) {
							if (con.status == 200) {
								busy_off();
								if (!isSaveResponse(con.responseText)) {
									alertify.alert(con.responseText);
								} else {
									$("#continputdata").html(con.responseText);
									leftFixedTable();
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
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
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

$optdiv=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdivsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(19) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optdivsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optdiv.="<option value=".$key.">".$key." - ".$val."</option>";
	$optdivsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optdiv.="</optgroup>";
		$optdivsch.="</optgroup>";
	}	
}

$opttt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct thntnm from ".$dbname.".bgt_blok order by thntnm asc";
$res = fetchdata($str);
foreach($res as $bar){
    $opttt.="<option value='".$bar['thntnm']."'>".$bar['thntnm']."</option>";
}

$optthnpost="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tahunbudget from ".$dbname.".bgt_produksi_kebun order by tahunbudget desc";
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

$optblok="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct kodeblok from ".$dbname.".bgt_blok where substr(kodeblok,1,4) in (".getOrgDetail(2).")  and closed='1' and statusblok in ('TM','TBM') order by kodeblok asc";
$res = fetchdata($str);
foreach($res as $bar){
    $optblok.="<option value='".$bar['kodeblok']."'>".$bar['kodeblok']."</option>";
}

$arrJenis=array("moderat"=>"Moderat", "optimis"=>"Optimis", "pesimis"=>"Pesimis");
foreach($arrJenis as $key => $val) {
	$optjenis .= "<option value='".$key."'>".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('bgt_prdkebun').'</span>');
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
		<td align=center style='width:70px;height:10px!important;cursor:pointer;' onclick=showformupload()>
			<img class=delliconBig src=images/edit.png title='Upload Excel'><br>Upload Excel</td>
		<td>
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
							<td><select class=select2 id=kodeorgsch onchange=loaddata(0); onblur=\"getAfdThnTnm(this,'divisisch,ttsch','','".$_SESSION['lang']['all']."');\" style=\"width:150px;\">".$optorgsch."</select></td>
							
							<td>" . $_SESSION['lang']['divisi'] . "</td>
							<td>:</td>
							<td><select class=select2 id=divisisch onchange=loaddata(0); onblur=getThnTnm(this,'ttsch','".$_SESSION['lang']['all']."'); style=\"width:150px;\">".$optdivsch."</select></td>
							
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['tahuntanam']."</td>
							<td>:</td>
							<td><select class=select2 id=ttsch onchange=loaddata(0); style=\"width:150px;\">" . $opttt . "</select></td>
							
							<td>" . $_SESSION['lang']['sebaran'] . "</td>
							<td>:</td>
							<td><select class=select2 id=sebaransch onchange=loaddata(0); style=\"width:150px;\">" . $optgol . "</select></td>
							
							<td>" . $_SESSION['lang']['intiplasma'] . "</td>
							<td>:</td>
							<td><select class=select2 id=ipsch onchange=loaddata(0); style=\"width:150px;\">" . $optip . "</select></td>
							
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
							<td><select class=select2 id=tahunpostsch onchange=showposting(0); style=\"width:150px;\">" . $optthnpost . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select class=select2 id=kodeorgpostsch onchange=showposting(0); style=\"width:150px;\">".$optorgsch."</select></td>
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
echo"
	<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['budgetyear'] . "</td>
				<td><font style=font-size:10px;color:blue;>(1)</font>&nbsp;:</td>
				<td><input type=text class=myinputtextnumber id=tahun maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:145px;></td>
				
				<td>" . $_SESSION['lang']['tahuntanam']."</td>
				<td><font style=font-size:10px;color:blue;>(4)</font>&nbsp;:</td>
				<td><select class=select2 id=tt style=\"width:150px;\" onchange=getblok(this);>" . $opttt . "</select></td>
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td><font style=font-size:10px;color:blue;>(2)</font>&nbsp;:</td>
				<td><select class=select2 id=kodeorg onchange=\"get_div_tt_blok(this,'divisi,tt,blok','".$_SESSION['lang']['all']."');showbutton();\" style=\"width:150px;\">".$optorg."</select></td>
				
				
				<td>" . $_SESSION['lang']['blok'] . "</td>
				<td><font style=font-size:10px;color:blue;>(5)</font>&nbsp;:</td>
				<td><select class=select2 id=blok style=\"width:150px;\">".$optblok."</select>
					
				</td>
				
			</tr>
			<tr>	
				<td>" . $_SESSION['lang']['divisi'] . "</td>
				<td><font style=font-size:10px;color:blue;>(3)</font>&nbsp;:</td>
				<td><select class=select2 id=divisi onchange=\"get_tt_blok(this,'tt,blok','".$_SESSION['lang']['all']."');\" style=\"width:150px;\">".$optdivsch."</select></td>

				<td hidden>" . $_SESSION['lang']['jenis'] . "</td>
				<td hidden><font style=font-size:10px;color:blue;>(6)</font>&nbsp;:</td>
				<td hidden><select class=select2 id=jenisbudget style=\"width:150px;\">".$optjenis."</select>
					
				</td>
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


OPEN_BOX();
$bulan=range(1,12);

#untuk inputan baru
echo"<div id=contdetail style=display:none;>
	<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center>".$_SESSION['lang']['divisi']."</th>
			<th align=centers style='width:50px'>".$_SESSION['lang']['tahuntanam']."</th>
			<th align=center>".$_SESSION['lang']['blok']."</th>
			<th align=center>".$_SESSION['lang']['luas']."</th>
			<th align=center>".$_SESSION['lang']['pokok']."</th>
			<th align=center>".$_SESSION['lang']['sph']."</th>
			<th align=center width=50px>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jjg']."</th>
			<th align=center width=55px>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']."</th>
			<th align=center width=55px>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']." Bruto (Grading)</th>
			<th align=center>BJR</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>
			";
			foreach($bulan as $bln){				
				echo"<th align=center width=40px >".numToMonth($bln,'E','short')."</th>";
			}
		echo"<th width=50px align=center>Action</th>
		</tr>
	</thead>
	<tbody id=continputdata></tbody>
	</table></div>";
echo"</div>";

#cont posting
echo"<div id=contposting style=display:none;>
	<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=centers style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['luas']."</th>
			<th align=center>".$_SESSION['lang']['pokok']."</th>
			<th align=center>".$_SESSION['lang']['sph']."</th>
			<th align=center>Ton / Ha</th>
			<th align=center>Jjg / Pkk</th>
			<th align=center width=50px>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jjg']."</th>
			<th align=center width=55px>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']."</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']." Bruto <br/> Grading</th>
			<th align=center>BJR</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>
			";
			foreach($bulan as $bln){				
				echo"<th align=center width=40px >".numToMonth($bln,'E','short')."</th>";
			}
		echo"<th width=30px align=center colspan=2>Action</th>
		</tr>
	</thead>
	<tbody id=contpostingdata></tbody>
	</table></div>";
echo"</div>";

#list data
echo"<div id=listData style=display:block>";
echo"<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0 >
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<!--<th align=center>".$_SESSION['lang']['kodeorg']."</th>-->
			<th align=center>".$_SESSION['lang']['divisi']."</th>
			<th align=centers style='width:50px'>".$_SESSION['lang']['tahuntanam']."</th>
			<th align=center>".$_SESSION['lang']['blok']."</th>
			<th align=center>".$_SESSION['lang']['luas']."</th>
			<th align=center>".$_SESSION['lang']['pokok']."</th>
			<th align=center>".$_SESSION['lang']['sph']."</th>
			<th align=center>Ton / Ha</th>
			<th align=center>Jjg / Pkk</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jjg']."</th>
			<th align=center>BJR</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']."</th>
			<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['kg']." Bruto <br/> Grading</th>
			<th align=center>".$_SESSION['lang']['jenis']."</th>";
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

#= Form Upload Excel =#
echo"<div id=inputdataex style=display:none>";
OPEN_BOX();
echo"
	<fieldset style=float:left;margin-right:15px;><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>" . $_SESSION['lang']['file'] . "</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=fileSelected('') style=width:84px;color:blue;>Preview</button>
					<!--<button class=mybutton id=formuploaddt onclick=formupload() style=width:60px;color:red;>Download Template</button>-->
				</td>
			</tr>
		</table>
	</fieldset>
	";

echo"
	<fieldset style=width:200px;height:60px;><legend>Template Download</legend>
		<table border=0>
			<tr>
				<td></td>
				<td></td>
				<td>
					<a href='tempExcel/tempbgtproduksi.xlsx' class=mybutton id=formuploaddt style=width:100px;>Download Template</a>
				</td>
			</tr>
		</table>
	</fieldset>
	";
CLOSE_BOX();
echo "</div>";


OPEN_BOX();
$bulan=range(1,12);

#untuk inputan baru
echo"<div id=contdetailex style=display:none; class='table-scroll'>";
echo"</div>";

#list data
echo"<div id=listData style=display:block>";
echo"<div id=contain>
			<script>loaddataupload(0)</script>";
echo "</div>";
echo "</div>";

CLOSE_BOX();

echo close_body();
?>