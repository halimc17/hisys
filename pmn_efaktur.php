<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pmn_efaktur.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
include('master_mainMenu.php');			
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        $optCariPt.="<option value='".$data['kodeorganisasi']."'>".$data['namaorganisasi']."</option>";
}
$optsup=$optFaktur="";
$ha="SELECT namasupplier,`supplierid`,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE status='1' and kodekelompok='S004' order by namasupplier asc";
$hi=$owlPDO->query($ha) or die(print " Gagal: ".PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while ($hu=$hi->fetch())
{
        $optsup.="<option value=".$hu['supplierid'].">".$hu['namasupplier']."</option>";
}

$optJenis="<option value=''></option>";	

OPEN_BOX('','<span class=judul>'.strtoupper('Faktur Pajak').'</span><br />');

echo"<fieldset style='float:left;'>
	<legend>".$_SESSION['lang']['form']."</legend> 
		<table border=0>
			<tr>
				<td>".$_SESSION['lang']['kodeorganisasi']."</td>
				<td>:</td>
				<td><select id=pt style=\"width:173px;\">".$optOrg."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
                <td>
					<input type=text  style='width:70px' class=myinputtext id=tglawal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('01-m-Y')."' />
					s/d 
					<input type=text  style='width:70px' class=myinputtext id=tglakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' />
				</td>
            </tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button onclick='preview()' class='mybutton' name='preview' id='preview'>Preview</button>
					<button onclick=excel(event,'pmn_slave_efaktur.php') class='mybutton' name='excel' id='excel'>Excel</button>
				</td>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX(); 
echo "<fieldset style='min-height:400px;'>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id=container>
	</div>
</fieldset>";
CLOSE_BOX();
echo close_body();									
?>