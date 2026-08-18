<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/lgl_5kategoribansos.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>KATEGORI CSR</span>');
$optAkunKeg = makeOption($dbname,'setup_klpkegiatan',"noakun,noakun");
$whereAkun = "";

if($_SESSION['language']=='EN'){
    $dd='namaakun1';
}else{
    $dd='namaakun';
}
$str="select noakun,".$dd." as namakegiatan from ".$dbname.".keu_5akun where detail=1 and left(noakun,1)>=7 ";
if(!empty($whereAkun)) {
	$str .= " and (".$whereAkun.")";
}
$str.="order by noakun";
$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." ".$bar->namakegiatan."</option>";
}

$strSat="select satuan from ".$dbname.".setup_satuan";
$optSatuan="";
$optTipe=$optSatuan=$optkel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$resSat=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
$resSat->setFetchMode(PDO::FETCH_OBJ);
while($barSat=$resSat->fetch()){
    $optSatuan.="<option value='".$barSat->satuan."'>".$barSat->satuan."</option>";
}
$arragama=getEnum($dbname,'lgl_kategoribansos','jenis');
foreach($arragama as $kei=>$fal){
	$optTipe.="<option value='".$kei."'>".$fal."</option>";
}

echo"<fieldset style='width:665px;'><table border=0>
     <tr>
		<td>".$_SESSION['lang']['kode']."</td>
		<td>:</td>
		<td><input style='width:75px' disabled type=text  id=kodekegiatan class=myinputtext></td>
				
		<td  style='width:90px;'>".$_SESSION['lang']['nama']."</td>
		<td>:</td>
		<td><input style='width:300px;' type=text  maxlength=200 id=namakegiatan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
	 </tr>
       
	 <tr>
		<td>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>
		<td><select style='width:79px' id=tipe>".$optTipe."</select></td>

		<td style=display:none>".$_SESSION['lang']['satuan']."</td>
		<td style=display:none>:</td>
		<td style=display:none><select style='width:79px' id=satuan>".$optSatuan."</select></td>
		
		<td>".$_SESSION['lang']['noakun']."</td>
		<td>:</td>
		<td><select id=noakun onchange=getKode() style='width:305px;'>".$optakun."</select>
		<img id='noakun' onclick=z.elSearch('noakun',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
	 </tr>        
     <tr>
		<td colspan=2>&nbsp;</td>
		<td colspan=3>
		<input type=hidden id=method value='insert'>
		<button class=mybutton onclick=simpanKegiatan()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=cancelKegiatan()>".$_SESSION['lang']['cancel']."</button></td>
	 </tr>
	 </table></fieldset>";
echo open_theme($_SESSION['lang']['daftarkegiatan']);
echo "<div id=container style=height:400px;overflow:auto;width:665px>";
	$str1="select * from ".$dbname.".lgl_kategoribansos order by nama";
	echo"<table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>
		 <tr class=rowheader><td align=center style='width:70px;'>".$_SESSION['lang']['kode']."</td>
                     <td align=center style='width:350px;'>".$_SESSION['lang']['nama']."</td>
                     <td align=center style='width:50px;display:none'>".$_SESSION['lang']['satuan']."</td>
                     <td align=center style='width:70px;'>".$_SESSION['lang']['noakun']."</td>
                     <td align=center style='width:70px;'>".$_SESSION['lang']['tipe']."</td>
                     <td align=center style='width:30px;'>Action</td></tr>
		 </thead>
		 <tbody>";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
		echo"<tr class=rowcontent>
				<td align=center>".$bar1->kode."</td>
				<td>".$bar1->nama."</td>
				<td align=center style=display:none></td>
				<td align=center>".$bar1->akun."</td>
				<td align=center>".$bar1->jenis."</td>    
				<td style='text-align:center'><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->nama."','','".$bar1->akun."','".$bar1->jenis."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>