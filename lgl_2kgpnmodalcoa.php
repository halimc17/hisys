<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/lgl_2kgpnmodalcoa.js?v=<?=time();?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('lgl_2kgpnmodalcoa').'</span>');

#------------------------------------------------------------
# Pilihan akun: hanya akun daun (level>=5) dari bagan akun HHO
# Akun yang sudah terdaftar diberi tanda [MT]/[MK]
#------------------------------------------------------------
if($_SESSION['language']=='EN'){
    $dd='namaakun1';
}else{
    $dd='namaakun';
}
$str="select a.noakun,a.".$dd." as namaakun,ifnull(c.jenis,0) as jenis
      from ".$dbname.".keu_5akun a
      left join ".$dbname.".lgl_kgpnmodal_5coa c on c.noakun=a.noakun
      where a.kodeorg='HHO' and a.level>=5
      order by a.noakun";
$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $tanda='';
    if($bar->jenis==1){
        $tanda=' [MT]';
    }elseif($bar->jenis==2){
        $tanda=' [MK]';
    }
    $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." ".$bar->namaakun.$tanda."</option>";
}

$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optJenis.="<option value='1'>1 - Modal Tetap</option>";
$optJenis.="<option value='2'>2 - Modal Kerja</option>";

$optJenisSrc="<option value=''>".$_SESSION['lang']['all']."</option>";
$optJenisSrc.="<option value='1'>Modal Tetap</option>";
$optJenisSrc.="<option value='2'>Modal Kerja</option>";

$optStatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optStatus.="<option value='1'>Aktif</option>";
$optStatus.="<option value='0'>Nonaktif</option>";

$optStatusSrc="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatusSrc.="<option value='1'>Aktif</option>";
$optStatusSrc.="<option value='0'>Nonaktif</option>";

echo"<fieldset style='width:665px;'><table border=0>
     <tr>
		<td>".$_SESSION['lang']['noakun']."</td>
		<td>:</td>
		<td colspan=4><select id=noakun style='width:470px;'>".$optakun."</select>
		<img id='cariakun' onclick=z.elSearch('noakun',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
	 </tr>
	 <tr>
		<td>Jenis</td>
		<td>:</td>
		<td><select style='width:200px' id=jenis>".$optJenis."</select></td>

		<td style='width:70px;'>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select style='width:170px' id=aktif>".$optStatus."</select></td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>
		<td colspan=4><input style='width:470px;' type=text maxlength=60 id=keterangan class=myinputtext onkeypress=\"return tanpa_kutip(event);\"></td>
	 </tr>
     <tr>
		<td colspan=2>&nbsp;</td>
		<td colspan=4>
		<input type=hidden id=method value='insert'>
		<button class=mybutton onclick=simpanCoa()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=cancelCoa()>".$_SESSION['lang']['cancel']."</button>
		<button class=mybutton onclick=hapusCoa()>".$_SESSION['lang']['delete']."</button></td>
	 </tr>
	 </table></fieldset>";

echo"<fieldset style='width:665px;margin-top:4px;'><table border=0>
     <tr>
		<td>Jenis</td>
		<td>:</td>
		<td><select style='width:130px' id=jenissrc>".$optJenisSrc."</select></td>

		<td style='width:70px;'>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select style='width:100px' id=aktifsrc>".$optStatusSrc."</select></td>
	 </tr>
	 <tr>
		<td>".$_SESSION['lang']['nama']."</td>
		<td>:</td>
		<td><input style='width:250px;' type=text id=carisrc class=myinputtext onkeypress=\"if(event.keyCode==13){loaddata()}\"></td>

		<td colspan=3><button class=mybutton onclick=loaddata()>".$_SESSION['lang']['find']."</button></td>
	 </tr>
	 </table></fieldset>";

echo open_theme($_SESSION['lang']['daftar'].' COA Kegiatan Penanaman Modal');
echo "<div id=container style=height:400px;overflow:auto;width:665px>";
echo "<script>loaddata()</script>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>
