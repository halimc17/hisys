<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/log_spkv2.js?v=1.3'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('log_spkv2').'</span>');
echo"<div id=action_list>
	<table>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=addnewdata()>
				<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
			<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend> 
				<table>
					<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td> 
						<td>:</td>
						<td>
							<input id='snotrans' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" value=''>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
</div>";
CLOSE_BOX();

echo"<div id=listdata style=display:block>";
OPEN_BOX();
echo"<div>    
	<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
		<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['kodeorg']."</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>No Pengajuan</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['subunit']."</td>
			<td align=center>".$_SESSION['lang']['koderekanan']."</td>
			<td align=center>".$_SESSION['lang']['nilaikontrak']."</td>
			<td align=center>".$_SESSION['lang']['dari']."</td>
			<td align=center>".$_SESSION['lang']['sampai']."</td>
			<td align=center>".$_SESSION['lang']['jumlahrealisasi']."</td>
			<td align=center>".$_SESSION['lang']['status']."</td>
			<td align=center colspan='4'>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=contain> 
			<script>loaddata(0)</script>
		</tbody>
		</table>
	</div>";
CLOSE_BOX();
echo "</div>";

echo "<div id=header style=display:none>";
OPEN_BOX();
echo "<fieldset><legend>Pencarian Pengajuan SPK</legend>
	<table>
		<tr>
			<td>No. SPK</td>
			<td>:</td>
			<td>
				<input id=nospk class=myinputtext onkeypress=\"return_tanpa_kutip(event);\" style=\"width:195px;\">
			</td>
		</tr>
		<tr>
			<td>No</td>
			<td>:</td>
			<td>
				<input id=notransaksi class=myinputtext onkeypress=\"return_tanpa_kutip(event);\" disabled style=\"width:195px;\">
				<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"popuppencarian('Cari No. Pengajuan SPK','<fieldset>Find<input type=text class=myinputtext id=snopengajuan onkeypress=enterkey(event,carinopengajuan)><button class=mybutton onclick=carinopengajuan()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor>',event)\">
			</td>
		</tr>
	</table>
</fieldset>
<fieldset id='fieldresult' style='display:none'><legend>Result</legend><div id='divresult'></div></fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>