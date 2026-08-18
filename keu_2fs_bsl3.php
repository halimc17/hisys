<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2fs_bsl3').'</span>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<script language=javascript src='js/keu_2fs_bsl3.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$opttahun=$optbulan='';
$opttahun1=$optbulan1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select distinct(substr(periode,1,4)) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $opttahun.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
    $opttahun1.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrBulan=array('01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Okt','11'=>'Nop','12'=>'Des',);
foreach($arrBulan as $key => $val){
    $optbulan.="<option value=" . $key.">" . $val. "</option>";
	$optbulan1.="<option value=" . $key.">" . $val. "</option>";
}

$frm[0]='';
$frm[1]='';
$arr1 = "##pt##kdorg##tahun1##tahun2##tahun3##tahun4##bulan1##bulan2##bulan3##bulan4##tahunsd1##tahunsd2##tahunsd3##tahunsd4##bulansd1##bulansd2##bulansd3##bulansd4";
$arr1.="##tahunytd1##bulanytd1##tahunsdytd1##bulansdytd1";
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getEstate()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun1 onchange=getbulanytd(this.id,'tahunsd1') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan1 onchange=getbulanytd(this.id,'bulansd1') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd1 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd1 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun2 onchange=getbulanytd(this.id,'tahunsd2') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan2 onchange=getbulanytd(this.id,'bulansd2') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd2 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd2 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun3 onchange=getbulanytd(this.id,'tahunsd3') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan3 onchange=getbulanytd(this.id,'bulansd3') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd3 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd3 style=\"width:50px;\">" . $optbulan . "</select>
						
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun4 onchange=getbulanytd(this.id,'tahunsd4') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan4 onchange=getbulanytd(this.id,'bulansd4') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd4 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd4 style=\"width:50px;\">" . $optbulan . "</select>
						
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd1 onchange=getbulanytd(this.id,'tahunsdytd1') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd1 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd1 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd1 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_bsl3','" . $arr1 . "','container_bsl3') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_bsl3.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_bsl3','" . $arr1 . "','container_bsl3')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>
<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_bsl3' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_bsl3' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_bsl3' style='overflow:auto;height:350px'; ></div>
</div>
";

$arr2 = "##pt2##kdorg2##tahun21##tahun22##tahun23##bulan21##bulan22##bulan23##tahunsd21##tahunsd22##tahunsd23##bulansd21##bulansd22##bulansd23##tahun24##bulan24##tahunsd24##bulansd24";
$arr2.="##tahunytd2##bulanytd2##tahunsdytd2##bulansdytd2";
$frm[1]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt2 onchange=getEstate2()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg2 style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun21 onchange=getbulanytd(this.id,'tahunsd21') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan21 onchange=getbulanytd(this.id,'bulansd21') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd21 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd21 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun22 onchange=getbulanytd(this.id,'tahunsd22') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan22 onchange=getbulanytd(this.id,'bulansd22') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd22 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd22 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun23 onchange=getbulanytd(this.id,'tahunsd23') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan23 onchange=getbulanytd(this.id,'bulansd23') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd23 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd23 style=\"width:50px;\">" . $optbulan . "</select>
						
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun24 onchange=getbulanytd(this.id,'tahunsd24') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan24 onchange=getbulanytd(this.id,'bulansd24') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd24 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd24 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd2 onchange=getbulanytd(this.id,'tahunsdytd2') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd2 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd2 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd2 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_bsl4','" . $arr2 . "','container_bsl4') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_bsl4.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_bsl4','" . $arr2 . "','container_bsl4')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_bsl4' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_bsl4' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_bsl4' style='overflow:auto;height:350px'; ></div>
</div>
";


$arr3="##pt3##kdorg3##tahun30##tahun31##tahun32##tahun33##bulan30##bulan31##bulan32##bulan33";
$arr3.="##tahunsd31##tahunsd32##tahunsd33##bulansd31##bulansd32##bulansd33##tahun34##bulan34##tahunsd34##bulansd34";
$arr3.="##tahunytd3##bulanytd3##tahunsdytd3##bulansdytd3";
$frm[2]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt3 onchange=getEstate3()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg3 style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr style=display:none>
                    <td>" . $_SESSION['lang']['periode'] . " </td>
                    <td>:</td>
                    <td><select id=tahun30 style=\"width:50px;\">" . $opttahun1 . "</select>
                        <select id=bulan30 style=\"width:50px;\">" . $optbulan1 . "</select>
                    </td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun31 onchange=getbulanytd(this.id,'tahunsd31') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan31 onchange=getbulanytd(this.id,'bulansd31') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd31 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd31 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun32 onchange=getbulanytd(this.id,'tahunsd32') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan32 onchange=getbulanytd(this.id,'bulansd32') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd32 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd32 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun33 onchange=getbulanytd(this.id,'tahunsd33') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan33 onchange=getbulanytd(this.id,'bulansd33') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd33 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd33 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun34 onchange=getbulanytd(this.id,'tahunsd34') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan34 onchange=getbulanytd(this.id,'bulansd34') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd34 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd34 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd3 onchange=getbulanytd(this.id,'tahunsdytd3') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd3 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd3 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd3 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_pl','" . $arr3 . "','container_pl') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_pl.php','" . $arr3 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_pl','" . $arr3 . "','container_pl')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_pl' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_pl' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_pl' style='overflow:auto;height:350px'; ></div>
</div>
";


$arr4="##pt4##kdorg4##tahun40##tahun41##tahun42##tahun43##bulan40##bulan41##bulan42##bulan43";
$arr4.="##tahunsd41##tahunsd42##tahunsd43##bulansd41##bulansd42##bulansd43##tahun44##bulan44##tahunsd44##bulansd44";
$arr4.="##tahunytd4##bulanytd4##tahunsdytd4##bulansdytd4";
$frm[3]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt4 onchange=getEstate4()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg4 style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr style=display:none>
                    <td>" . $_SESSION['lang']['periode'] . " </td>
                    <td>:</td>
                    <td><select id=tahun40 style=\"width:50px;\">" . $opttahun1 . "</select>
                        <select id=bulan40 style=\"width:50px;\">" . $optbulan1 . "</select>
                    </td>
                </tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun41 onchange=getbulanytd(this.id,'tahunsd41') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan41 onchange=getbulanytd(this.id,'bulansd41') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd41 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd41 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun42 onchange=getbulanytd(this.id,'tahunsd42') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan42 onchange=getbulanytd(this.id,'bulansd42') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd42 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd42 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun43 onchange=getbulanytd(this.id,'tahunsd43') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan43 onchange=getbulanytd(this.id,'bulansd43') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd43 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd43 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun44 onchange=getbulanytd(this.id,'tahunsd44') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan44 onchange=getbulanytd(this.id,'bulansd44') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd44 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd44 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd4 onchange=getbulanytd(this.id,'tahunsdytd4') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd4 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd4 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd4 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_cogs','" . $arr4 . "','container_cogs') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_cogs.php','" . $arr4 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_cogs','" . $arr4 . "','container_cogs')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_cogs' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_cogs' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_cogs' style='overflow:auto;height:350px'; ></div>
</div>
";


$arr5="##pt5##kdorg5##tahun50##tahun51##tahun52##tahun53##bulan50##bulan51##bulan52##bulan53";
$arr5.="##tahunsd51##tahunsd52##tahunsd53##bulansd51##bulansd52##bulansd53##tahun54##bulan54##tahunsd54##bulansd54";
$arr5.="##tahunytd5##bulanytd5##tahunsdytd5##bulansdytd5";
$frm[4]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt5 onchange=getEstate5()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg5 style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr style=display:none>
                    <td>" . $_SESSION['lang']['periode'] . " </td>
                    <td>:</td>
                    <td><select id=tahun50 style=\"width:80px;\">" . $opttahun1 . "</select>
                        <select id=bulan50 style=\"width:80px;\">" . $optbulan1 . "</select>
                    </td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun51 onchange=getbulanytd(this.id,'tahunsd51') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan51 onchange=getbulanytd(this.id,'bulansd51') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd51 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd51 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun52 onchange=getbulanytd(this.id,'tahunsd52') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan52 onchange=getbulanytd(this.id,'bulansd52') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd52 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd52 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun53 onchange=getbulanytd(this.id,'tahunsd53') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan53 onchange=getbulanytd(this.id,'bulansd53') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd53 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd53 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun54 onchange=getbulanytd(this.id,'tahunsd54') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan54 onchange=getbulanytd(this.id,'bulansd54') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd54 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd54 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd5 onchange=getbulanytd(this.id,'tahunsdytd5') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd5 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd5 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd5 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_ga','" . $arr5 . "','container_ga') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_ga.php','" . $arr5 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_ga','" . $arr5 . "','container_ga')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_ga' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_ga' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_ga' style='overflow:auto;height:350px'; ></div>
</div>
";


$arr6="##pt6##kdorg6##tahun60##tahun61##tahun62##tahun63##bulan60##bulan61##bulan62##bulan63";
$arr6.="##tahunsd61##tahunsd62##tahunsd63##bulansd61##bulansd62##bulansd63##tahun64##bulan64##tahunsd64##bulansd64";
$arr6.="##tahunytd6##bulanytd6##tahunsdytd6##bulansdytd6";

$frm[5]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt6 onchange=getEstate6()  style=\"width:232px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg6 style=\"width:232px;\">" . $optorg . "</select></td>
                </tr>
                <tr style=display:none>
                    <td>" . $_SESSION['lang']['periode'] . " </td>
                    <td>:</td>
                    <td><select id=tahun60 style=\"width:50px;\">" . $opttahun1 . "</select>
                        <select id=bulan60 style=\"width:50px;\">" . $optbulan1 . "</select>
                    </td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . " I</td>
                    <td>:</td>
                    <td><select id=tahun61 onchange=getbulanytd(this.id,'tahunsd61') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan61 onchange=getbulanytd(this.id,'bulansd61') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd61 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd61 style=\"width:50px;\">" . $optbulan . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " II</td>
                    <td>:</td>
                    <td><select id=tahun62 onchange=getbulanytd(this.id,'tahunsd62') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan62 onchange=getbulanytd(this.id,'bulansd62') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd62 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd62 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " III</td>
                    <td>:</td>
                    <td><select id=tahun63 onchange=getbulanytd(this.id,'tahunsd63') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan63 onchange=getbulanytd(this.id,'bulansd63') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd63 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd63 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " IV</td>
                    <td>:</td>
                    <td><select id=tahun64 onchange=getbulanytd(this.id,'tahunsd64') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulan64 onchange=getbulanytd(this.id,'bulansd64') style=\"width:50px;\">" . $optbulan . "</select> s/d 
						<select id=tahunsd64 style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansd64 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . " YTD</td>
                    <td>:</td>
                    <td><select id=tahunytd6 onchange=getbulanytd(this.id,'tahunsdytd6') style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulanytd6 disabled style=\"width:50px;\"><option value='01'>Jan</option></select> s/d 
						<select id=tahunsdytd6 disabled style=\"width:50px;\">" . $opttahun . "</select>
						<select id=bulansdytd6 style=\"width:50px;\">" . $optbulan . "</select>
						</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('keu_slave_2fs_no','" . $arr6 . "','container_no') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'keu_slave_2fs_no.php','" . $arr6 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<button onclick=\"zPdf('keu_slave_2fs_no','" . $arr6 . "','container_no')\" class=mybutton>" . $_SESSION['lang']['pdf'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>

<div style=clear:both></div>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container_no' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='container_no' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<hr>
	<div id='container_no' style='overflow:auto;height:350px'; ></div>
</div>
";



$hfrm[0]='BSL3';
$hfrm[1]='BSL4';
$hfrm[2]='PL';
$hfrm[3]='COGS';
$hfrm[4]='GenAdm';
$hfrm[5]='NonOpr';

#draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');	

CLOSE_BOX();
echo close_body();
?>