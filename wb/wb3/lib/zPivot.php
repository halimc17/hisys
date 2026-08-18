<?
require_once('lib/zJspdf.php');
#Pivot Table
echo"<script src=pivottable-master/dist/plotly-basic-latest.min.js></script>";
echo"<script type=text/javascript src=pivottable-master/dist/jquery.min.js></script>";
echo"<script type=text/javascript src=pivottable-master/dist/jquery-ui.min.js></script>";
echo"<link rel=stylesheet type=text/css href=pivottable-master/dist/c3.min.css>";

#echo"<script type=text/javascript src=pivottable-master/dist/papaparse.min.js></script>";
#echo"<script type=text/javascript src=pivottable-master/dist/d3.min.js></script>";

echo"<script type=text/javascript src=pivottable-master/dist/c3.min.js></script>";
echo"<script type=text/javascript src=pivottable-master/dist/jquery.ui.touch-punch.min.js></script>";
echo"<link rel=stylesheet type=text/css href=pivottable-master/dist/pivot.css>";
echo"<script type=text/javascript src=pivottable-master/dist/pivot.js></script>";
echo"<script type=text/javascript src=pivottable-master/dist/c3_renderers.js></script>";
echo"<script type=text/javascript src=pivottable-master/dist/plotly_renderers.js></script>";
echo"<script type=text/javascript src=pivottable-master/subtotal/dist/subtotal.js></script>";
echo"<link rel=stylesheet type=text/css href=pivottable-master/subtotal/dist/subtotal.css>";

#echo"<script type=text/javascript src=pivottable-master/dist/nrecopivottableext.js></script>";
#echo"<link rel=stylesheet type=text/css href=pivottable-master/dist/nrecopivottableext.css>";

echo"<link rel=stylesheet type=text/css href=DataTables/css/buttons.dataTables.min.css>";

/* 

echo"
	<style>
		body {font-family: Verdana;}
		.c3-line, .c3-focused {stroke-width: 3px !important;}
		.c3-bar {stroke: white !important; stroke-width: 1;}
		.c3 text { font-size: 12px; color: grey;}
		.tick line {stroke: white;}
		.c3-axis path {stroke: grey;}
		.c3-circle { opacity: 1 !important; }
		.c3-xgrid-focus {visibility: hidden !important;}
	</style>";

#Datatables

#echo"<script type=text/javascript src=DataTables/js/jquery-3.5.1.js></script>";
echo"<script type=text/javascript src=DataTables/js/jquery.dataTables.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.buttons.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/jszip.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/buttons.print.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/buttons.html5.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/pdfmake.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/vfs_fonts.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.fixedHeader.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.responsive.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.colReorder.min.js></script>";
#echo"<script type=text/javascript src=DataTables/js/dataTables.rowReorder.min.js></script>";
#echo"<script type=text/javascript src=DataTables/js/dataTables.keyTable.min.js></script>";
#echo"<script type=text/javascript src=DataTables/js/dataTables.scroller.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.rowGroup.min.js></script>";
echo"<script type=text/javascript src=DataTables/js/dataTables.fixedColumns.min.js></script>";

#echo"<script type=text/javascript src=DataTables/js/dataTables.select.min.js></script>";
#echo"<script type=text/javascript src=DataTables/js/dataTables.searchPanes.min.js></script>";

echo"<link rel=stylesheet type=text/css href=DataTables/css/buttons.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/jquery.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/fixedHeader.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/responsive.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/colReorder.dataTables.min.css>";
#echo"<link rel=stylesheet type=text/css href=DataTables/css/rowReorder.dataTables.min.css>";
#echo"<link rel=stylesheet type=text/css href=DataTables/css/keyTable.dataTables.min.css>";
#echo"<link rel=stylesheet type=text/css href=DataTables/css/scroller.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/rowGroup.dataTables.min.css>";
echo"<link rel=stylesheet type=text/css href=DataTables/css/fixedColumns.dataTables.min.css>";
 */
#echo"<link rel=stylesheet type=text/css href=DataTables/css/select.dataTables.min.css>";
#echo"<link rel=stylesheet type=text/css href=DataTables/css/searchPanes.dataTables.min.css>";
?>