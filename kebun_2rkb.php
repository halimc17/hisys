<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2rkb').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper='';
// $optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);

// $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' and induk='".@key($optPT)."' 
// 		order by kodeorganisasi asc ";
// $optorg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $count = 0;
// $firstUnit = "";
// while($bar=$res->fetch()){
// 	if($count==0){
// 		$firstUnit = $bar['kodeorganisasi'];
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// 	$count++;
// }

$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(3) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optPT.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optPT.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optPT.="</optgroup>";
	}
}

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}



$optDiv="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$firstUnit."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . ">".$bar['namaorganisasi']."</option>";
}

$optDiv2="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$firstUnit."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv2.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
    $optDiv2.="<option value=" . $bar['kodeorganisasi'] . ">".$bar['namaorganisasi']."</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".kebun_rkbht order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

// $jns=array('PANEN'=>'Panen TBS','PEMEL_R'=>'Pemeliharaan Rekap','PEMEL_D'=>'Pemeliharaan Detail','ANGKUT'=>'Pengangkutan TBS','UMUM'=>'Umum','SUPPORT'=>'Support','BAHAN'=>'Bahan dan Alat');
$jns=array('PANEN'=>'Panen TBS','PEMEL_R'=>'Pemeliharaan Rekap','PEMEL_D'=>'Pemeliharaan Detail','ANGKUT'=>'Pengangkutan TBS','UMUM'=>'Umum','BAHAN'=>'Bahan dan Alat');
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($jns as $key => $val){
    $optjenis.="<option value=" . $key. ">" . $val. "</option>";
}

$arr = "##pt##kdorg##divisi##periode##jenis";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getEstate_x()  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select onchange=getdivisi_x('UMUM') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=periode style=\"width:164px;\">".$optper."</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['jenis'] . "</td>
                    <td>:</td>
                    <td><select id=jenis style=\"width:164px;\">".$optjenis."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2rkb','" . $arr . "','Container') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2rkb.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
		<div style=clear:both></div>
		<div id='both_report'>
			<div id='head_tableboth' align=right>
				<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='Container' table='sortable' >
					<img title='Full Screen' class='resicon' src='images/full-screen.png'>
				</a>
				<a class='fixheadbtn mybutton' table='sortable' idbothbody='Container' shown='0' >
					<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
				</a>
			</div>
			<div id='Container' style='overflow:auto;height:380px'; ></div>
		</div>
</fieldset>";

CLOSE_BOX();
echo close_body();
?>