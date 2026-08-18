<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
?>
<script type="text/javascript" src="js/setup_validasiinput.js?v=<?php echo time(); ?>"></script>
<!--
<script type=text/javascript src=pivottable-master/dist/jquery.min.js></script>
<script type=text/javascript src=pivottable-master/dist/jquery-ui.min.js></script>
<script type=text/javascript src=DataTables/js/jquery.dataTables.min.js></script>
<script type=text/javascript src=DataTables/js/dataTables.responsive.min.js></script>

<link rel=stylesheet type=text/css href=DataTables/css/jquery.dataTables.min.css>
<link rel=stylesheet type=text/css href=DataTables/css/responsive.dataTables.min.css>
-->
<?php
require_once('lib/zSelect2Lite.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?php 
OPEN_BOX('','<span class=judul>'.getMenu('setup_validasiinput').'</span><br>');
CLOSE_BOX();
OPEN_BOX();
?>
<div id="container">
	<script>loadData()</script>
</div>
<?
CLOSE_BOX();
echo close_body();
?>