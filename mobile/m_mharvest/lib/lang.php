<?php
//== Create Variable Language fo Javascript //
$bahasaForJSON = array();
if(isset($_SESSION['lang'])){
	
	$dataarrLang = $_SESSION['lang'];
	$d = array();
	foreach($dataarrLang as $k => $v){
		$key = preg_replace('/\s+/','',$k);
		$d[$key] = preg_replace('/\s+/',' ',$v);
	}
	$bahasaForJSON = $d;
}
// END Var Javascript Language //
?>
<script>
var bahasa = language('<?php echo json_encode($bahasaForJSON); ?>');
</script>