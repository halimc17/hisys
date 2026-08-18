<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5printqrcode').'</span>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/kebun_5printqrcode.js?v=1.8'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$where='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where.="";
} else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$where.=" and induk ='".$_SESSION['empl']['kodeorganisasi']."' and tipe != 'HOLDING'";
}else{
	$where.=" and kodeorganisasi ='".$_SESSION['empl']['lokasitugas']."'";
}

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4'  order by induk asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4' and tipe='KEBUN' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optunitx="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optunitx.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optunitx.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunitx.="</optgroup>";
	}
}

$str = "select * from " . $dbname . ".datakaryawan where tanggalkeluar='' or tanggalkeluar='0000-00-00' order  by namakaryawan asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch()) {
    $opttipe.="<option value=" . $bar['karyawanid'] . ">[" . $bar['nik'] . "] - ".$bar['namakaryawan']."</option>";
}

$str = "select * from " . $dbname . ".kebun_5tph order by kode asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$opttph="<option value=''>".$_SESSION['lang']['all']."</option>";
$blok="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
    $opttph.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . "</option>";
}

$frm[0]='';
$frm[1]='';
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=unit class=select2 style=\"width:164px;\" onchange=getdivisi()>" . $optunit . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi class=select2 style=\"width:164px;\" onchange=getmandor()><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                </tr>
				<tr>
                    <td>Kemandoran</td>
                    <td>:</td>
                    <td><select id=mandor class=select2 style=\"width:164px;\" onchange=getkar()><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['karyawan'] . "</td>
                    <td>:</td>
                    <td><select id=karyawan class=select2  style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button class=mybutton onclick=karyawanx('html')>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=karyawanx('pdf')>".$_SESSION['lang']['pdf']."</button>
				</tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
					<button class=mybutton onclick=generateqrcode('karyawanx')>Generate</button>
					<button class=mybutton onclick=batal('karyawanx')>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
            </table>
</fieldset><div style=clear:both></div>";
$frm[0].="<fieldset style='clear:both;min-height:47vh'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer'>
</div></fieldset>";

$arrkertas=array('A4','A5','A6','A3','Ledger','Letter','Legal','Executive');
foreach($arrkertas as $val){
	@$optker.="<option value=".$val.">".$val."</option>";
}

$frm[1].="
		<table>
		<tr><td>
			<fieldset style='float:left;'>
			<legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td width=75px>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td colspan=3><select class=select2 id=unittph style=\"width:164px;\" onchange=getdivisitph()>" . $optunitx . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td colspan=3><select class=select2 id=divisitph style=\"width:164px;\" onchange=getblok()><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['blok'] . "</td>
                    <td>:</td>
                    <td colspan=3><select class=select2 id=blok onchange=gettph() style=\"width:164px;\">
						".$blok."
						</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tph'] . "</td>
                    <td>:</td>
                    <td colspan=3><select class=select2 id=tph style=\"width:164px;\">
						".$opttph."
						</select></td>
                </tr>
                <tr>
                    <td colspan=6 align=center>
					<button class=mybutton onclick=generateqrcode('tphx')>Generate</button>
                    <button class=mybutton onclick=tphx('html')>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=tphx('pdf')>".$_SESSION['lang']['pdf']."</button>
				
					<button class=mybutton onclick=batal('tphx')>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
			</table>	
			</fieldset>
		</td>
		<td>
				<fieldset style=float:left><legend>PDF 2</legend><table>
					<tr>
						<td  width=67px>Lebar</td>
						<td>:</td>
						<td style=\"width:50px;\"><input type=text class=myinputtext id=lebar style=\"width:50px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Lebar (px)'></td>
						<td style=\"width:50px;\">Tinggi</td>
						<td><input type=text class=myinputtext id=tinggi style=\"width:50px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Tinggi (px)'>
						</td>
					</tr>
					<tr>
						<td>Orientation</td>
						<td>:</td>
						<td colspan=3><select id=orientation style=\"width:164px;\">
							<option value='portrait'>Portrait</option>
							<option value='landscape'>Landscape</option>
							</select></td>
					</tr>
					<tr>
						<td>Jlh Kolom</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=max style=\"width:50px;\"></td>
						<td style=\"width:50px;\">Kertas</td>
						<td><select id=ukkertas style=\"width:54px;\">".$optker."</select>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td colspan=6>
						<button class=mybutton onclick=tphx2('pdf')>".$_SESSION['lang']['pdf']." 2</button>
					</tr>
				</table>
				</fieldset>
				
			</td>
		</tr>
		</table>		
	
            
<div style=clear:both></div>";
$frm[1].="<fieldset style='clear:both;min-height:47vh'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerBarang'>
</div></fieldset>";

$hfrm[0]=$_SESSION['lang']['karyawan'];
$hfrm[1]=$_SESSION['lang']['tph'];

drawTab('FRM',$hfrm,$frm,300,'100%');	
CLOSE_BOX();
echo close_body();
?>