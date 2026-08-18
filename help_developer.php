<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('help_developer').'</span>');
?>

<script language=javascript src='js/help_developer.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="ckeditor/ckeditor.js"></script>




<?php
$arrtipe = array('FORM' => 'FORM' , 'FUNCTION' => 'FUNCTION');
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($arrtipe as $val) {
	$opttipe.="<option value=".$val.">".$val."</option>";
}
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo"
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						
						&nbsp".$_SESSION['lang']['judul']." : 
						<input type=text  id=keterangansch nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:150px;\">
						
						<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
						<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
					</td> 
				</tr>
			</table>
			";
echo"</fieldset></td>
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>

<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php echo"
<div id=listData style=display:block>";//buka list data
OPEN_BOX();
	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=contain  style=display:block> 
                    <script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data

echo "<div id=headher style=display:none>";
OPEN_BOX();
echo "
<fieldset>
		<legend>".$_SESSION['lang']['formpermintaan']."</legend>
		<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['judul']."</td> 
			<td>:</td>
			<td>
			<input hidden type=text id=kode class=myinputtext>
			<input type=text id=judul class=myinputtext style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td> 
			<td>:</td>
			<td><select id=tipe style=\"width:205px;\">" . $opttipe . "</select>
			</td>
			
		</tr>
		<tr>
			<td valign=top>".$_SESSION['lang']['keterangan']."</td> 
			<td valign=top>:</td>
			<td colspan=4>
			<textarea class=ckeditor id=keterangan ></textarea>
			</td>
		</tr>
		<tr>
		<td colspan=9 align=center>
			<button id=savehead class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			<button id=savehead class=mybutton onclick=add_new_data()>".$_SESSION['lang']['baru']."</button>
			<input type=hidden id=method value='insert'>
		</td>
		</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";



echo close_body();			
?>
    
