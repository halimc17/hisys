<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zPivottable.php');

OPEN_BOX('','<span class=judul>'.getMenu('user_customreport').'</span><br>');
?>
<script language=javascript src='js/user_customreport.js?v=<?php echo time(); ?>'></script>
<!--
<script src="pivottable-master/dist/plotly-basic-latest.min.js"></script>
<script type="text/javascript" src="pivottable-master/dist/jquery.min.js"></script>
<script type="text/javascript" src="pivottable-master/dist/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="pivottable-master/dist/c3.min.css">
-->

<!--
<script type="text/javascript" src="pivottable-master/dist/papaparse.min.js"></script>
<script type="text/javascript" src="pivottable-master/dist/d3.min.js"></script>
-->
<!--
<script type="text/javascript" src="pivottable-master/dist/c3.min.js"></script>
<script type="text/javascript" src="pivottable-master/dist/jquery.ui.touch-punch.min.js"></script>
<link rel="stylesheet" type="text/css" href="pivottable-master/dist/pivot.css">
<script type="text/javascript" src="pivottable-master/dist/pivot.js"></script>
<script type="text/javascript" src="pivottable-master/dist/c3_renderers.js"></script>
<script type="text/javascript" src="pivottable-master/dist/plotly_renderers.js"></script>
-->

<!--
	<script type="text/javascript" src="tablejstoexcel/jquery-3.5.1.js"></script>
-->
<!--
<script type="text/javascript" src="tablejstoexcel/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/jszip.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/buttons.print.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/buttons.html5.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/pdfmake.min.js"></script>
<script type="text/javascript" src="tablejstoexcel/vfs_fonts.js"></script>

<link rel="stylesheet" type="text/css" href="tablejstoexcel/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="tablejstoexcel/jquery.dataTables.min.css">
-->
<!--
<script type="text/javascript" src="tablejstoexcel/jquery.dataTables.js"></script>
<link rel="stylesheet" type="text/css" href="tablejstoexcel/jquery.dataTables.css">
-->
<!--
<style>
	body {font-family: Verdana;}
	.c3-line, .c3-focused {stroke-width: 3px !important;}
	.c3-bar {stroke: white !important; stroke-width: 1;}
	.c3 text { font-size: 12px; color: grey;}
	.tick line {stroke: white;}
	.c3-axis path {stroke: grey;}
	.c3-circle { opacity: 1 !important; }
	.c3-xgrid-focus {visibility: hidden !important;}
</style>
-->
<?
$optrpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select a.* from ".$dbname.".tool_userdefinedreport a left join ".$dbname.".tool_userdefinedreport_user b on a.rnumber=b.rnumber where b.username='".$_SESSION['standard']['username']."' and a.status=1 order by rnumber"; 
$res = fetchData($str);
foreach($res as $val){
	$optrpt.="<option value=".$val['rnumber'].">".$val['namalaporan']."</option>";
}

echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>Jenis Laporan</td> 
					<td>:</td>
					<td><select id=jenislaporan onchange=getfilter(this.value); style=\"width:150px;\">".$optrpt."</select>
                </tr>
				<tr id=filterlap style=display:none;>
					<td colspan=3 id=contfilterlap></td> 
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=pivot('pivot'); class=dt-button buttons-print>Pivot</button>
					<button onclick=\"excel('excel')\" class=\"dt-button buttons-print\">Data</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none>
		<button onclick=\"exportTableToExcel('pvtTable')\" class=\"dt-button buttons-print\" >Excel</button>
		<button onclick='ExportPdf()' class=\"dt-button buttons-print\">PDF</button>
	</div>";
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>