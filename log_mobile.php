<?php
//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_mobile').'</span>'); ?>
<script language="javascript" src="js/log_mobile.js?v=<?php echo time(); ?>"></script>

<?php 
echo "<div id='action_list'>
	<table>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=\"loadData(0, 'html')\">
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['find']."</legend>
					".$_SESSION['lang']['karyawan']." :
					<select  id=karyawanCari onchange=\"loadData(0, 'html')\">
						<option value=''>-".$_SESSION['lang']['pilih']."-</option>";
						$str 	= "select distinct karyawanid from ".$dbname.".log_user_mobile";
						$res 	= fetchdata($str);

						foreach ($res as $row) {
							$nmKaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', 'karyawanid='.$row['karyawanid']);
							echo "<option value='".$row['karyawanid']."'>[".$row['karyawanid']."] ".$nmKaryawan[$row['karyawanid']]."";
						}
					echo "</select>
					<img id='requester' onclick=z.elSearch('karyawanCari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>&nbsp
					".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dari']." : <input type=text autocomplete=off onchange=\"loadData(0, 'html')\" class=myinputtext id=tgl_dari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
					".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['sampai']." : <input type=text autocomplete=off onchange=\"loadData(0, 'html')\" class=myinputtext id=tgl_sampai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
					<button class=mybutton onclick=\"batal()\">".$_SESSION['lang']['cancel']."</button>
					<button class=mybutton onclick=\"loadData(0, 'pdf')\">".$_SESSION['lang']['pdf']."</button>
					<button class=mybutton onclick=\"loadData(0, 'excel')\">".$_SESSION['lang']['excel']."</button>
				</fieldset>
			</td>
		</tr>
	</table> 
</div>";

CLOSE_BOX();
OPEN_BOX();

echo
"<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	
	<div style='overflow:auto;' id=listData>
		<script>loadData(0, 'html')</script>
	</div>
</fieldset>";

CLOSE_BOX();
echo close_body();
?>