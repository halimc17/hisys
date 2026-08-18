<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2rb').'</span></br>');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/option.js'></script>
<script language=javascript1.2 src="js/zSelect2.js?ver=1.9"></script>

<?
$listOrg = getOrgDetail(3);
$opt="<option value=''>Pilih Data</option>";
foreach($listOrg as $key => $value){
	$opt .= '<option value="'.$key.'">'.$value.'</option>';
}

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$optPeriode="<option value=''>Pilih Data</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
while($bar=$res->fetch())
{
  $optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}

$arr = "##kodept##unit##periode";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
					<tr>
						<td>".$_SESSION['lang']['pt']."</td><td>:</td>
          <td><select class=select2 style=width:150px onchange=getunit() id=kodept>".$opt."</select></td>
					 </tr>	
					 <tr>
					   <td>".$_SESSION['lang']['gudang']."</td><td>:</td>
					   <td><select class=select2 style=width:150px id=unit>".$optDiv."</select></td>
					 </tr>
           <tr>
           <td>".$_SESSION['lang']['periode']."</td><td>:</td>
           <td><select class=select2 style=width:150px id=periode>".$optPeriode."</select></td>
         </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('log_slave_2rb','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'log_slave_2rb.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";


echo"<fieldset style=float:left>
<div>
<legend>Note</legend>
<hr>
<table  cellpadding=5 cellspacing=1 border=0 style='font-weight:bold'>
	<tr>
		<td style='width:20px;background:red'>&nbsp;</td>
		<td style='background-color:#red !important; color:black;border:unset !important'>:</td>
		<td style='background-color:#red !important; color:black;border:unset !important'>Saldo Gudang Kurang Dari Minimum Stok</td>
	</tr>
</table>
</div>
</fieldset> ";

CLOSE_BOX();

OPEN_BOX();
echo "
<div id='printContainer' class='table-scroll' style='overflow:auto;height:450px;' >
</div>";
CLOSE_BOX();
echo close_body();
?>