<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language=javascript1.2 src='js/bgt_uploadbyykebun.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/bgt_byykebun.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('bgt_uploadbyykebun').'</span>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_dataUpload()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayListUpload()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
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
CLOSE_BOX();
echo "</div>";


OPEN_BOX();
$bulan=range(1,12);

#untuk inputan baru
echo"<div id=contdetail style=display:none; class='table-scroll'>";
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