<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param          = $_POST;
$method         = checkPostGet('method', '');
$notransaksi    = checkPostGet('notransaksi', '');
$tglmulai       = tanggalsystemn(checkPostGet('tglmulai', ''));

switch ($method) {
    case'detail':
		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable >
		<thead><tr class=rowheader>";
		$tab.="<td align=center width=20px>No</td>";
	
			
		$tab.="</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$tab.="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $tab.="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
        // $tab.=CLOSE_BOX();
		echo $tab;	
		
	break;
}
?>	