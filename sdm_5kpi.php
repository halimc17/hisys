<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5kpi').'</span><br>');
?>
<script language=javascript src='js/sdm_5kpi.js?v=<?php echo time(); ?>'></script>
<?
CLOSE_BOX();
OPEN_BOX();
//echo"<div id='output' style=min-height:100px><script>loaddata()</script></div>";
echo"<table width=100%>
		<tr><td>
				<button onclick=\"loaddata2('0','excel')\" class=\"dt-button buttons-print\" >Excel</button>
				<button onclick=newdata('new') class=\"dt-button buttons-print\">New</button>";
				//if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and ($_SESSION['empl']['bagian']=='HCM' or $_SESSION['empl']['bagian']=='SDM')){
					//echo " <button onclick=uploaddata() class=\"dt-button buttons-print\">Upload</button> ";
				//}
				echo "
			</td>
			<td align=right>
				<div id='mytable_filter' class='dataTables_filter'>
					<label>
						Search:<input onkeypress='enterkey(event,loaddata2)' onkeyup=loaddata2(); id=cari type='search' style=height:30px placeholder='Search'>
					</label>
				</div>
			</td>
		</tr>
	</table>";
echo"<div class='table-scroll' style=height:70vh>
	<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tahun']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['unit']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kpi']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['bobot']." (%)</th>
				<th rowspan=2 style='text-align:center;'>Target</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenis']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['induk']."</th>
				<th rowspan=2 style='text-align:center;'>Tipe Penilaian</th>
				<th rowspan=2 style='text-align:center;'>Skala Penilaian</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updatetime']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody id='output'><script>loaddata2(0)</script></tbody>
		<tfoot id=footData></tfoot>
	</table>
	</div>
	";
CLOSE_BOX();
echo close_body();
?>