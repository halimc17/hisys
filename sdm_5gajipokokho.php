<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
require_once('lib/admin_validation.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5gajipokokho.js'></script>
<?

$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optkmpn="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTp="select id,name from ".$dbname.".sdm_ho_component where type='basic' order by name";
$qTp=$owlPDO->query($sTp) or die(print " Gagal: ".PDOException::getMessage());
$qTp->setFetchMode(PDO::FETCH_ASSOC);
while($rTp=$qTp->fetch()){
    $optTipe.="<option value='".$rTp['id']."'>".$rTp['name']."</option>";
    $optkmpn.="<option value='".$rTp['id']."'>".$rTp['name']."</option>";
}

$optTipe2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

#jabatan
$optJbtn=$optGol="<option value=''>".$_SESSION['lang']['all']."</option>";
$sjbtn="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$rjbtn=fetchData($sjbtn);
foreach($rjbtn as $row=>$lstJbtn){
	$optJbtn.="<option value='".$lstJbtn['kodejabatan']."'>".$lstJbtn['namajabatan']."</option>";
}


##golongan
$i="select * from ".$dbname.".sdm_5golongan order by kodegolongan asc ";
$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while($d=$n->fetch()){
	$optGol.="<option value='".$d['kodegolongan']."'>".$d['kodegolongan']." => ".$d['namagolongan']."</option>";
}





	
    $i="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by kodeorganisasi asc";

$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while($d=$n->fetch()){
	$optUnit.="<option value='".$d['kodeorganisasi']."'>".$d['kodeorganisasi']." - ".$d['namaorganisasi']."</option>";
}


$optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>";
//$i="select * from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and  tipe!='HOLDING' ";
$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while($d=$n->fetch())
{
	$optUnit2.="<option value='".$d['kodeorganisasi']."'>".$d['kodeorganisasi']." - ".$d['namaorganisasi']."</option>";
}


$optTipe3="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttpkar="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTp2="select distinct tipekaryawan,tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id 
		where a.alokasi = '1' order by b.no";
$qTp2=$owlPDO->query($sTp2) or die(print " Gagal: ".PDOException::getMessage());
$qTp2->setFetchMode(PDO::FETCH_ASSOC);
while($rTp=$qTp2->fetch()){
    $optTipe3.="<option value='".$rTp['tipekaryawan']."'>".$rTp['tipe']."</option>";
    $opttpkar.="<option value='".$rTp['tipekaryawan']."'>".$rTp['tipe']."</option>";
}
$arrd=array("0"=>"Per Orang / Per Person","1"=>$_SESSION['lang']['all']);
$optTipe5="";
foreach($arrd as $rwdd=>$lstarr){
     
     $optTipe5.="<option value='".$rwdd."'>".$lstarr."</option>";
}
$arr="##thn##pilInp##karyawanId##idKomponen##jmlhDt##method##tpKary##kdUnit##golongan##jabatan";
include('master_mainMenu.php');
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	OPEN_BOX('','<span class=judul>ANDA TIDAK DI IZINKAN MEMBUKA MENU INI !</span><br>');
	EXIT('ERROR !');
}else{	
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_5gajipokokho').'</span><br>');
}

echo"<table border=0><td><fieldset style='float:left;height:150px'>
     <legend><b>Form</b></legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['tahun']."</td><td>:</td>
	   <td><input type=text class=myinputtextnumber id=thn name=thn  style=width:145px; onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" maxlength='4' value='".date('Y')."'></td>

	   <td>".$_SESSION['lang']['unitkerja']."</td><td>:</td>
		<td><select onchange=getKar() id=kdUnit style=width:150px;>".$optUnit2."</select></td>
	 </tr>
	 
           <tr>
	   <td>".$_SESSION['lang']['tipekaryawan']." </td><td>:</td>
	    <td><select id=tpKary onchange=getKar() style=width:150px;>".$optTipe3."</select></td>
	
	   <td>".$_SESSION['lang']['kodegolongan']." </td><td>:</td>
	    <td><select id=golongan onchange=getKar() style=width:150px;>".$optGol."</select>
			</td>
	 </tr>	
	 <tr>
	   <td>".$_SESSION['lang']['kodejabatan']." </td><td>:</td>
	    <td><select id=jabatan onchange=getKar() style=width:150px;>".$optJbtn."</select>
		<img id='jabatan' onclick=z.elSearch('jabatan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>

	   <td>".$_SESSION['lang']['pilih']." </td><td>:</td>
	    <td><select id=pilInp style=width:150px;>".$optTipe5."</select></td>
	 </tr>	
	 

	      <tr>
	   <td>".$_SESSION['lang']['idkomponen']." </td><td>:</td>
	    <td><select id=idKomponen  style=width:150px;>".$optTipe."</select></td>

	   <td>".$_SESSION['lang']['namakaryawan']." </td><td>:</td>
	   <td><select id=karyawanId style=width:150px;>".$optTipe2."</select>
			<img id='karyawanId' onclick=z.elSearch('karyawanId',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
	 </tr>	 
     
         <tr>
	   <td>".$_SESSION['lang']['jumlah']."</td><td>:</td>
	   <td><input type=text class=myinputtextnumber id=jmlhDt name=jmlhDt onkeypress=\"return angka_doang(event);\" style=\"width:145px;\" maxlength='8' /></td>
	 </tr>	
	 
	 
	  <tr>
	   <td><input type=hidden class=myinputtext id=karyPdf name=karyPdf  style=\"width:150px;\"></td>
	 </tr>	
	 
	 <tr><td colspan=2><td>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco('sdm_slave_5gajipokokho','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </td></td></tr>
	 </table>
	 </fieldset></td>";
$opttahun1="";
for($x=-2;$x<=8;$x++)
{
    $opttahun1.="<option value='".(date('Y')+$x)."'>".(date('Y')+$x)."</option>";
}
echo"<td valign=top><fieldset style='float:left;height:150px'><legend>Copy</legend>";
	
echo"<table border=0 style='display: inline-block;vertical-align:top'>
	<tr><td>".$_SESSION['lang']['unitkerja']."</td><td>:</td>
		<td colspan=4><select id=kdUnit2 style=width:200px>".$optUnit."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['dari']." ".$_SESSION['lang']['tahun']."</td><td>:</td>
		<td><select id=tahun1>".$opttahun1."</select>
			Ke ".$_SESSION['lang']['tahun']." :
			<select id=tahun2>".$opttahun1."</select></td>
	</tr><tr>
		<td  colspan=2></td><td colspan=4><button onclick=copyTahun() class=mybutton>".$_SESSION['lang']['proses']."</button></center></td>
	</tr><tr>
		<td colspan=6><hr></td>
	</tr><tr>
		<td colspan=6>ID : Copy gaji pokok dari konfigurasi gaji tahun tertentu ke tahun tertentu</td>
	</tr><tr>
		<td colspan=6>EN : Copy basic salary from previous year to this year</td>
    </tr></table></fieldset></td></table>";
CLOSE_BOX();
OPEN_BOX();
$opttahun="";
for($x=2;$x>=-10;$x--)
{
    if((date('Y')+$x)==date('Y'))
    $opttahun.="<option value='".(date('Y')+$x)."' selected>".(date('Y')+$x)."</option>";
     else    
    $opttahun.="<option value='".(date('Y')+$x)."'>".(date('Y')+$x)."</option>";
}//onchange=loadGaji(this.options[this.selectedIndex].value)

//FORM PENCARIAN
echo"<fieldset  style=float:left;>
	<legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['data']."</legend>
	<table border=0>
		<tr>
			<td rowspan=3 width=100px align=center><img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."' onclick=displatList() ><br><span align=center>".$_SESSION['lang']['list']."</span></td>
			<td>".$_SESSION['lang']['tahun']."</td>
			<td>
				<select id=opttahun style='width:100px;''>".$opttahun."</select>
			</td>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>
				<input type=text style='width:150px'; class=myinputtext id=nmKar name=nmKar  onkeypress=\"enterkey(event,loadData);\" style=\"width:150px;\" />
			</td>
			<td>".$_SESSION['lang']['unitkerja']."</td>
			<td>
				<select  id=kdUnitCr style='width:150px;'>".$optUnit2."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipekaryawan']."</td>
			<td>
				<select id=tpKaryCr style=width:100px;>".$opttpkar."</select>
			</td>
			<td>".$_SESSION['lang']['idkomponen']."</td>
			<td>
				<select id=idKomponenCr style=width:155px;>".$optkmpn."</select>
			</td>
			<td>".$_SESSION['lang']['kodejabatan']."</td>
			<td>
				<select id=idjabatan style=width:150px;>".$optJbtn."</select>
			</td>
		</tr>
		<tr>
			<td>Jumlah</td>
			<td><input type=checkbox id=showhide onchange='loadData()'/> Show / Hide</td>
			<td colspan=2 style='text-align:center'>
				<button onclick=loadData() class=mybutton >".$_SESSION['lang']['find']."</button>  
			</td>
		</tr>
	</table>
</fieldset>";

##LIST DATA
echo"<fieldset style=float:left;>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id=container></div>";
echo"<script>loadData()</script>";
echo"</fieldset>";
CLOSE_BOX();
echo close_body();
?>
