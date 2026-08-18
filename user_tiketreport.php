<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('user_tiketreport').'</span><br>');

$s="select id, caption, caption2, caption3 from ".$dbname.".menu where action='user_tiketreport'";
$r=fetchdata($s);
$idmenu = $r[0]['id'];

//<script language="javascript" src="js/zSelect2.js?ver=4.9"></script>
?>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2.help').select2({
			dropdownAutoWidth:false
		});
	});
	function loaddataticketsupport999999999(idmenu,curentpage) {
		//cari= trim(document.getElementById('cari').value);

		param = 'method=loaddata';
		//param += '&cari=' + cari;
		tujuan = 'user_tiketreport_slave.php';
		post_response_text(tujuan, param, respog);

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						document.getElementById('outputticketsupport999999999').innerHTML = con.responseText;
						$(document).ready(function() {
							var table = $('#mytable').DataTable({
								// supaya tidak ada overflow horisontal
								//responsive: true,
								// fixedColumns:   {
									// leftColumns: 1,
									// rightColumns: 2
								// },
								ordering: false,
								fixedHeader: true,
								// pake paging atau tidak
								paging: true,
								// columnDefs: [
									// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
								// ],
								// drag kolom
								//colReorder: true,
								// jumlah per page
								"iDisplayLength": 10,
								// tinggi / height
								scrollY: '65vh',
								scrollX: true,
								scrollCollapse: true,
								dom: 'Blfrtip',
								//select: true,
								
								language: {
									searchBuilder: {
										title: 'Filter',
										button: 'Filter'
									}
								},
								buttons: ['searchBuilder', 'excel', 'print',{
										text: 'New',
										action: function () {
											tambahreporthelppopup(idmenu);
										}
									}
								]
							});
							
							//double click untuk freeze column
							$(table.table().container()).on('dblclick', 'td', function () {
								var row = table.column(this);
									new $.fn.dataTable.FixedColumns(table, {
											leftColumns: row.index()+1
											//   rightColumns: 1
										}); 
								//console.log('Row Index = ' + row.index());
							});
							
							//right click untuk freeze column
							$(table.table().container()).on('dblclick', 'th', function () {
								var row = table.column(this);
									new $.fn.dataTable.FixedColumns(table, {
											leftColumns: row.index()+1
										}); 
								//console.log('Row Index = ' + row.index());
							});
						} );
						$('select[name*="mytable_length"]').attr("style", "height:30px;");
						$(document).ready(function() {
							$('.select2.help').select2({
								dropdownAutoWidth:true
							});
						});
						
						if(curentpage>0){						
							var table = $('#mytable').DataTable();
								table.page(parseFloat(curentpage)).draw( false );
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	
	window.onload=setInterval(function(){
		var table = $('#mytable').DataTable();
		var info = table.page.info();
		var curentpage = info.page;
		loaddataticketsupport999999999(idmenu,curentpage)
	},60000);
</script>
<?
CLOSE_BOX();
OPEN_BOX();
echo"<input style=display:none; id='tempidmenusupport999999999' value=".$idmenu.">";
echo"<div id='outputticketsupport999999999' style=min-height:80vh><script>loaddataticketsupport999999999(".$idmenu.")</script></div>";
CLOSE_BOX();
echo close_body();
?>

<script type="text/javascript">
	idmenu = document.getElementById('tempidmenusupport999999999').value;
	setInterval( function () {
		loaddataticketsupport999999999(idmenu);
	}, 600000); // reload per 10 menit
</script>