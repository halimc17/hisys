<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_persetuuanPp').'</span>');
?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_persetujuan.js"></script>
<div id="action_list">

<?php
$optListUsr="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select  distinct a.dibuat,b.namakaryawan,lokasitugas from ".$dbname.".log_prapoht a 
	left join ".$dbname.".datakaryawan b on a.dibuat=b.karyawanid order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    if($bar['namakaryawan']!='')
    {
		$optListUsr.="<option value='".$bar['dibuat']."'>".$bar['namakaryawan']." [".$bar['lokasitugas']."]</option>";
    }
}

$optStatus="";
$optStatus.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Belum Disetujui</option>";
$optStatus.="<option value='1'>Sudah Disetujui</option>";

echo"<table>
	<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	   </td>
	   <td>
		<fieldset><legend>".$_SESSION['lang']['find']."</legend>
        <table>
			<tr>
				<td>No. PR/SR</td>
                <td>:</td>
                <td>
					<input type=text style=width:195px; id=txtsearch size=25 maxlength=30 class=myinputtext>
				</td>
				
				<td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td>
					<input type=text style=width:95px; class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namabarang']."</td>
                <td>:</td>
                <td>
					<input type=text style=width:195px; id=txtnmbrg size=25 maxlength=30 class=myinputtext>
				</td>
				
				<td>".$_SESSION['lang']['dbuat_oleh']."</td>
                <td>:</td>
                <td>
					<select id=pembuatPP style=width:200px;>".$optListUsr."</select>
				</td>
			</tr>
				<td style='display:none;'>".$_SESSION['lang']['status']." Persetujuan</td>
                <td style='display:none;'>:</td>
                <td style='display:none;'>
					<select id=statusSch style=width:100px;>".$optStatus."</select>
				</td>
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
</table> "; 

CLOSE_BOX(); //1 C //2 O

echo"<div id=list_pp_verication>";

OPEN_BOX();

echo"<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div style='overflow:auto; height:420px;'>
		<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>No. PR/SR</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td align=center>PR/SR Detail</td>
				<td colspan='4' align='center'>Verification</td>";

				$countApp = getCountApproval('PR');
                for($i=1;$i<=$countApp;$i++)
				{
					echo"<td align=center>".$_SESSION['lang']['persetujuan'].$i."</td>";
				}
			echo"</tr>
			</thead>
			<tbody id='contain'>
				<script>loaddata(0)</script>
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</div>
</fieldset>";

CLOSE_BOX();

echo"</div>
	<input type='hidden' name='method' id='method'  />
	<input type='hidden' id='no_pp' name='no_pp' />
	<input type='hidden' name='user_login' id='user_login' value='".$_SESSION['standard']['userid']."' />";

close_body();

?>