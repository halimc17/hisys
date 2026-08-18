<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2alkbyymat').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper=$optDiv='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
#$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT' and kodesejarah=''",'2','0',true);

// $str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }


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

// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

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

$arrIP=array("BBT"=>"Bibitan","TBM"=>"TB dan TBM","TM"=>"TM dan Panen");
$optIP="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrIP as $res => $bar){
	$optIP.="<option value=".$res.">".$bar."</option>";
}



$arr1 = "##pt##kdorg##tglawal##tglakhir##klbyy##akun##keg##divisi##sts##blok";
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
                    <td><select id=divisi onchange=getBlok(this,'blok','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
				<tr hidden>
                    <td>" . $_SESSION['lang']['blok'] . "</td>
                    <td>:</td>
                    <td><select id=blok style=\"width:164px;\">" . $optDiv . "</select>
					<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
                </tr>
				<tr>
                    <td id=idtgl>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
						<input type='text' class='myinputtext' id='tglawal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:77px;' value='".date('01-m-Y')."'  readonly/> 
						
						<input type='text' class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:77px;' value='".date('d-m-Y')."'  readonly/></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['status'] . "</td>
                    <td>:</td>
                    <td><select id=sts onchange=getdata('getklbyy','sts','klbyy',this.value) style=\"width:164px;\">" . $optIP . "</select>
					</td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['kelompokbiaya'] . "</td>
                    <td>:</td>
                    <td><select id=klbyy onchange=getdata('getnoakun','klbyy','akun',this.value) style=\"width:164px;\"><option value=''>".$_SESSION['lang']['all']."</option></select>
					<img id='klbyy' onclick=z.elSearch('klbyy',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['namaakun'] . "</td>
                    <td>:</td>
                    <td><select id=akun style=\"width:164px;\"><option value=''>".$_SESSION['lang']['all']."</option></select>
					<img id='akun' onclick=z.elSearch('akun',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
                </tr>
				<tr hidden>
                    <td>" . $_SESSION['lang']['kegiatan'] . "</td>
                    <td>:</td>
                    <td><select id=keg style=\"width:164px;\"><option value=''>".$_SESSION['lang']['all']."</option></select>
					<img id='keg' onclick=z.elSearch('keg',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2alkbyymat','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2alkbyymat.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
	<div class='table-scroll'><div id='printContainer' style='height:380px'; ></div></div>
</div>
";

CLOSE_BOX();
echo close_body();
?>