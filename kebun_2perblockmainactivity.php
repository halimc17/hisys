<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2perblockmainactivity').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/kebun_2perblockmainactivity.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrIP=array("I"=>"INTI","P"=>"PLASMA");
$optIP="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrIP as $res => $bar){
	$optIP.="<option value=".$res.">".$bar."</option>";
}

$arr1 = "##pt##kdorg##prd##divisi##tt##ip##kolomhide##barishide";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select id=ip style=\"width:164px;\">" . $optIP . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
				<tr id=tombolshohhide >
                    <td colspan=1 id=judulshowhide>Tampilkan semua</td>
                    <td colspan=1>:</td>
                    <td colspan=4><input type=checkbox id=kolomhide><label>Kolom</label>
									<input type=checkbox id=barishide><label>Baris</label>
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2perblockmainactivity','" . $arr1 . "','printContainer');showdetail(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2perblockmainactivity.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";
echo"<br><div id=info><img src=images/info.png class=zImgBtn>
	<br>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_header.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Click pada header untuk menampilkan detail transaksi.</li>	
	<li onclick=getinfo('images/kebun_2perblockmainactivity_subhead.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Click pada sub header untuk menampilkan detail bulan.</li>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_row.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Click pada baris total untuk menampilkan detail perblok.</li>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_click.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Single click pada baris blok untuk menampilkan memberi warna pada baris.</li>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_click.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Double click pada baris blok untuk menampilkan detail biaya.</li>	
	<li onclick=getinfo('images/kebun_2perblockmainactivity_prd.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Click angka pada kolom Productivity untuk menampilkan data blok.</li>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_profit.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Click angka pada kolom Profitability untuk menampilkan data blok.</li>
	<li onclick=getinfo('images/kebun_2perblockmainactivity_aopytd.jpg'); style=cursor:pointer;color:blue; title=\"show me.\">
		Untuk Average Selling Price (AOP YTD) jika data belum ada anda diminta untuk mengisikan kemudian click update</li>
	<li>Pengisian Average Selling Price (AOP YTD) hanya bisa 1 kali, harap di masukkan angka yg benar.</li>
</div>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='zImgBtn' src='images/full-screen.png'>
		</a>
		<!--<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='zImgBtn' src='images/fix-header.gif'>
		</a>-->
	</div>
	<div id='printContainer' style='overflow:auto;height:380px'; ></div>
</div>
";

echo"<div id='getdetail' style=display:none></div>";
CLOSE_BOX();
echo close_body();
?>