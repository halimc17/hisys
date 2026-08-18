<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');

echo open_body();
require_once('master_mainMenu.php');


if ($_SESSION['lang']['id']=='ID'){
OPEN_BOX('', '<span class=judul>'.getMenu('kebun_2rekappanenvskegpanen').'</span><br>');
} else {
OPEN_BOX('', '<span class=judul>'.getMenu('kebun_2rekappanenvskegpanen').'</span><br>');
}
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>
function lihatdetail(blok, tgl, tipe,proses, ev) {
	param = 'tipe=' + tipe + '&blok=' + blok + '&tgl=' + tgl + '&proses=' + proses;
	tujuan = 'kebun_slave_2rekappnnvskegiatan_detail.php' + "?" + param;
	width = '700';
	height = '250';
	content = "<fieldset style='height:93%'><iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe></fieldset>"
	showDialog1('Detail Transaksi '+proses+' Blok '+ blok, content, width, height, ev);
}
</script>

<?
$optDiv='';
$optOrg = "<option value=''></option>";
// $sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by kodeorganisasi asc ";
// $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while ($rOrg = $qOrg->fetch()) {
//     $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
// }

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$arr = "##kdUnit##tgl1##tgl2##divisi";
echo"<fieldset style='float:left;height:110px'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdUnit onchange=getdivisi() style=\"width:164px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10'  readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2rekappanenvskegpanen','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2rekappanenvskegpanen.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"
	<fieldset style=float:left;height:110px><legend>Info</legend>
	<li>Rekap Pnn => Kebun - Transaksi - Rekap Panen per Blok</li>
	<li>Keg Pnn => Kebun - Transaksi - Kegiatan Panen</li>
	<li>SPB => Kebun - Transaksi - Surat Pengantar Buah</li>
	<li>Data yang di tampilkan termasuk data yang belum di posting</li>
	<li>Data yang berwarna merah berarti ada selisih</li>
	</fieldset>
	";


CLOSE_BOX();

OPEN_BOX();
echo "
<div style=clear:both></div>

<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<div class='table-scroll'><div id='printContainer' style='height:400px;' ></div>
</div></div>";
CLOSE_BOX();
echo close_body();
?>