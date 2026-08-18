<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2btl').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/zComment.js?ver=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zComment.css>
<script>
function jurnalv1KeExcel(ev, tujuan,ptV,gudangV, tanggal1V,tanggal2V,akundariV,akunsampaiV,regional) {
	param = 'pt=' + ptV + '&gudang=' + gudangV + '&tanggal1=' + tanggal1V + '&tanggal2=' + tanggal2V + '&akundari=' + akundariV + '&akunsampai=' + akunsampaiV;
	param += '&regional=' + regional;

	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}

function detailjurnal(ptV,gudangV, tanggal1V,tanggal2V,akundariV,akunsampaiV,regional){
    width = '';
    height = '';
    content = "<fieldset><img onclick=\"jurnalv1KeExcel(event,'keu_laporanBukuBesarv1_Excel.php','"+ptV+"','"+gudangV+"','"+tanggal1V+"','"+tanggal2V+"','"+akundariV+"','"+akunsampaiV+"','"+regional+"')\" src=images/excel.jpg class=resicon title='MS.Excel'>";
	content += "<div id=container style=\"max-height:500px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail Jurnal";
    //showDialog1(title, content, width, height, ev); 
	
	param = 'pt=' + ptV + '&gudang=' + gudangV + '&tanggal1=' + tanggal1V + '&tanggal2=' + tanggal2V + '&akundari=' + akundariV + '&akunsampai=' + akunsampaiV;
	param += '&regional=' + regional;
	param += '&tipelaporan=html';
	//alert(param);
	tujuan = 'keu_slave_2bukubesarv1.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd style=\"max-height:450px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}

function getdetail(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	// form();
	param  = 'method=html';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	tujuan = 'kebun_slave_2analisabyytm_popup.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdetailexcel(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	ev='event';
	param  = 'method=excel';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	
	showDialog1('Report Ms.Excel', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_2analisabyytm_popup.php?" + param + "'></iframe>", '900', '400', ev);
}
function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
</script>
<?
// $wh='';
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
// 	$wh='';
// } elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
//     $wh=" and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
// } else {
// 	$wh=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// }

// $sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' ".$wh." and inti ='1' order by induk, kodeorganisasi asc ";
// $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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


$sOrg = "select distinct periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optper="";
while ($rOrg = $qOrg->fetch()) {
    $optper.="<option value=" . $rOrg['periode'] . ">" . $rOrg['periode'] . "</option>";
}

$sOrg = "select distinct noakun, namaakun from " . $dbname . ".keu_5akun where length(noakun) = 5 and substr(noakun,1,3) in ('126','621','611') and namaakun not like '%NON AKTIF%'";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$klpbyy="<option value=''></option>";
while ($rOrg = $qOrg->fetch()) {
    $klpbyy.="<option value=" . $rOrg['noakun'] . ">" . $rOrg['noakun'] . " - " . $rOrg['namaakun'] . "</option>";
}

$arr = "##kdorg##periode2";
echo"<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=kdorg style=\"width:160px;\">" . $optOrg . "</select></td>
                </tr>
				
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=periode2 style=\"width:160px;\">" . $optper . "</select></td>
                </tr>
				
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2btl','" . $arr . "','printContainer');showheader(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2btl.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' class='table-scroll' style=height:73vh></div>";


// OPEN_BOX();
// echo "<div style=clear:both></div>
		// <div id='both_report'>
			// <div id='head_tableboth' align=right>
				// <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
					// <img title='Full Screen' class='resicon' src='images/full-screen.png'>
				// </a>
				// <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
					// <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
				// </a>
			// </div>
		// <div id='printContainer' style='height:450px; >
		// </div></div>";
CLOSE_BOX();
echo close_body();
?>