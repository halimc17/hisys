<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/vhc.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');

$optklvhc="<option value=''></option>";
$arrklvhc=getEnum($dbname,'vhc_5master','kelompokvhc');
foreach($arrklvhc as $kei=>$fal){
	switch($kei){
		case 'AB':
			 $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
		break;
		case 'KD':                            
			$_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
		break;
		case 'MS':
			$_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
		break;
	}
	$optklvhc.="<option value='".$kei."'>".$fal."</option>";
} 
//ambil jenis mesin/kendaraan
$str="select * from ".$dbname.".vhc_5jenisvhc order  by namajenisvhc";
$optjnsvhc="<option value=''></option>";;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
  	$optjnsvhc.="<option value='".$bar->jenisvhc."'>".$bar->jenisvhc." - ".$bar->namajenisvhc."</option>";
}	 

//=================ambil master barang untuk aset kendaraan (905)
$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='911' order by namabarang";
$optbarang="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar->kodebarang."'>".$bar->kodebarang." - ".$bar->namabarang."</option>";	
}
#ambil traksi
if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL')){
  $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' order by namaorganisasi";
}else{
  $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi";
}
  
$opttraksi='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $opttraksi.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
}  
  
//ambil kode organisasi selain blok dan afdeling
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe  in('KANWIL','HOLDING','KEBUN','PABRIK','TRAKSI','BULKING','TC','RND') 
and length(kodeorganisasi)=4 order  by kodeorganisasi,namaorganisasi";
$optorg="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
  	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}	 
    
$optkepemilikan=" <option value=1>".$_SESSION['lang']['miliksendiri']."</option>
                  <option value=0>".$_SESSION['lang']['sewa']."</option>";
$optAsset = "<option value=''></option>";

OPEN_BOX('','<span class=judul>'.getMenu('vhc_5mastervhc').'</span>');
echo"<fieldset><table border=0 ><legend>".$_SESSION['lang']['entryForm']."</legend>
    <tr><td style=width:160px>".$_SESSION['lang']['kodekelompok']."</td>
		<td  style=width:200px ><select style=width:200px; id=kelompokvhc onchange=loadJenis(this.options[this.selectedIndex].value)>".$optklvhc."</select></td>
		
		<td style=width:160px>".$_SESSION['lang']['jenkendabmes']."</td>
		<td><select style=width:160px onchange=loaddata(); id=jenisvhc >".$optjnsvhc."</select></td>
        
		<td>".$_SESSION['lang']['tahunperolehan']."</td>
		<td><input style=width:100px type=text id=tahunperolehan size=4 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4></td>
		
		<td style=display:none; colspan=2 rowspan=7 style='vertical-align:top'>
			<div id='divimage' style='padding-top:5px;padding-left:5px;border:1px solid grey;text-align:center'>
				<img src='images/question.png' style='width:120px;height:120px;'>
			</div>
		</td>

	</tr>
    <tr><td style=width:160px>".$_SESSION['lang']['kodeorganisasi']." (Owner)</td>
		<td style=width:200px ><select  style=width:200px; id=kodeorg onchange=getList();>".$optorg."</select></td>
        
		<td style=width:160px>".$_SESSION['lang']['kodevhc']."</td>
		<td><input style=width:156px type=text disabled placeholder='auto generate' id=kodevhc size=12 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20></td>
		
		<td >".$_SESSION['lang']['tahunproduksi']."</td>
		<td><input style=width:100px type=text id=tahunproduksi size=4 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=4></td>
        

	</tr>
    <tr><td style=width:160px>".$_SESSION['lang']['namabarang']."</td>
		<td style=width:220px ><select  style=width:200px; id=kodebarang style='width:200px'>".$optbarang."</select>
		<img id='kodebarang_find' onclick=\"z.elSearch('kodebarang',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'></td>
        
		<td style=width:160px>".$_SESSION['lang']['nopol']."</td>
		<td><input style=width:156px type=text  id=nopol size=12 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20></td>
        
		<td>".$_SESSION['lang']['warna']."</td>
		<td><input style=width:100px type=text id=warna onkeypress=\"return tanpa_kutip_dan_sepasi(event);\" class=myinputtext maxlength=20></td>
		
	</tr>
    <tr><td style=width:160px>".$_SESSION['lang']['kodeasset']."</td>
		<td style=width:160px><select style=width:200px; id=kodeasset>".$optAsset."</select>
		<img id='kodeasset_find' onclick=\"z.elSearch('kodeasset',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'></td>
		
        <td style=width:160px>".$_SESSION['lang']['beratkosong']." (Kg)</td>
		<td><input style=width:156px type=text id=beratkosong size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5></td>
        
		<td>".$_SESSION['lang']['tglakhirstnk']."</td><td>
		<input type=text class=myinputtext id=tglakhirstnk name=tglakhirstnk onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>
		
	</tr>
    <tr><td style=width:160px>".$_SESSION['lang']['nomorrangka']." / ".$_SESSION['lang']['noseri']."</td>
		<td><input style=width:195px;  type=text id=nomorrangka size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td>
		
        <td style=width:160px>".$_SESSION['lang']['nomormesin']."</td>
		<td><input style=width:156px type=text id=nomormesin size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td>
		
		<td>".$_SESSION['lang']['tglakhirkir']."</td>
		<td><input type=text class=myinputtext id=tglakhirkir name=tglakhirkir onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>

	</tr>
    <tr>
    	<td valign=top  style=width:160px rowspan=3>".$_SESSION['lang']['tmbhDetail']."</td>
		<td rowspan=3><textarea  id=detailvhc cols=23 rows=2 onkeypress=\"return tanpa_kutip(event);\" maxlength=255></textarea></td>

    	<td style=width:160px>".$_SESSION['lang']['nobpkb']."</td>
		<td><input style=width:156px type=text id=nobpkb size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=20></td>
		
		
		<td>".$_SESSION['lang']['tglakhirasuransi']."</td>
		<td><input type=text class=myinputtext id=tglakhirasuransi name=tglakhirasuransi onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>
		
		<td hidden>".$_SESSION['lang']['tglakhirijinbongkar']."</td>
		<td hidden><input type=text class=myinputtext id=tglakhirijinbm name=tglakhirijinbm onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>
    </tr>
    <tr>

        <td  style=width:160px valign=top>".$_SESSION['lang']['kepemilikan']."</td>
		<td valign=top><select  style=width:160px id=kepemilikan>".$optkepemilikan."</select></td>

		<td>".$_SESSION['lang']['tglakhirleasing']."</td>
		<td><input type=text class=myinputtext id=tglakhirleasing name=tglakhirleasing onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>
		
		<td hidden>".$_SESSION['lang']['tglakhirijinangkut']."</td>
		<td hidden><input type=text class=myinputtext id=tglakhirijinang name=tglakhirijinang onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\";   maxlength=10  style=width:100px; /></td>   

	</tr>
    <tr>
        <td style=width:160px>".$_SESSION['lang']['kodetraksi']."</td>
		<td><select  style=width:160px; id=kodetraksi>".$opttraksi."</select></td>

 
    </tr>
    <tr><td><td>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanMasterVhc()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelMasterVhc()>".$_SESSION['lang']['cancel']."</button>
	 </table></fieldset>";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str1="select * from ".$dbname.".vhc_5master order by status desc,kodeorg,kodevhc asc";
}else{
    $str1="select * from ".$dbname.".vhc_5master where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%' order by status desc,kodeorg,kodevhc asc";
}
CLOSE_BOX();
OPEN_BOX();
echo"
    <img onclick=dataKeExcel(event,'vhc_slave_save_vhc_excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
    <div class='table-scroll' style='height:320px;overflow:auto;'>";
echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
			<th align=center>No</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodeorganisasi'])."</th>		 
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodekelompok'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['jenkendabmes'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodevhc'])."</th>		
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['nopol'])."</th>		
            <th align=center>".$_SESSION['lang']['kodeasset']."</th>		
            <th align=center>".$_SESSION['lang']['namabarang']."</th>		
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['tahunperolehan'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['nomormesin'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['detail'])."</th>	   
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['kepemilikan'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['kodetraksi'])."</th>
			<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['updateby'])."</th>
            <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['action'])."</th></tr>
		 </thead>
		 <tbody id=container><script>loaddata()</script>";
echo"	 
	 </tbody>
	 <tfoot></tfoot>
	 </table>";
echo "</div>";

CLOSE_BOX();
echo close_body();
?>