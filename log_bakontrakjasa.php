<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
$_SESSION['pajak'] = array();
OPEN_BOX('','<span class=judul>'.getMenu('log_bakontrakjasa.php').'</span>');
?>
<script type="text/javascript" src="js/log_bakontrakjasa.js?v=<?php echo time(); ?>" /></script>
<?php
## BEGIN HEADER AND SEARCH ##
echo"<div id='action_list'>
	<table>
		<tr valign=moiddle>
			<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>
				No. ".$_SESSION['lang']['kontrak']." : <input type=text id=sckontrak size=25 maxlength=30 class=myinputtext>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				</fieldset>
			</td>
		</tr>
	</table>
</div>";
CLOSE_BOX();
## END HEADER AND SEARCH ##

OPEN_BOX();
## BEGIN LIST DATA ##
echo"<div id='listdata'>
	<div class='table-scroll'>
	<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
		<thead>
		<tr style='text-align:center;font-weight:bold'>
			<th>".$_SESSION['lang']['nourut']."</th>
			<th>No. ".$_SESSION['lang']['kontrak']."</th>
			<th>".$_SESSION['lang']['tanggal']."</th>
			<th>".$_SESSION['lang']['supplier']."</th>
			<th>".$_SESSION['lang']['deskripsi']."</th>
			<th>".$_SESSION['lang']['jumlahrealisasi']."</th>
			<th>".$_SESSION['lang']['status']."</th>
			<th align='center' colspan=2>Action</th>
		</tr>
		</thead>
		<tbody id='contain'></tbody>
		<tbody id='containft'></tbody>
		<script>loaddata(0);</script>
	</table>
	</div>
</div>";
## END LIST DATA ##

## BEGIN FORM INPUT ##
echo"<div id=forminput style='display:none'>
	<fieldset>
		<div id='showdata'></div>
	</fieldset>
</div>";
## END FORM INPUT ##
CLOSE_BOX();

echo close_body(); 
?>