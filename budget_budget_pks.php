<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';

?>
<script>
    var plh='';
    plh="<?php echo $_SESSION['lang']['pilihdata'];?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/budget_budget_pks.js?v=1.1"></script>
<?php

//pilihan station
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
	where (tipe='STATION') and induk = '".$_SESSION['empl']['lokasitugas']."'
	order by kodeorganisasi";
$optstation="";
$optstation.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optstation.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

//pilihan pabrik
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
	where (tipe='PABRIK') and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'
	order by kodeorganisasi";
$optpabrik="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
    
//pilihan tahun tutup
$str="select distinct tahunbudget from ".$dbname.".bgt_budget
	where tutup = '0' and kodebudget != 'UMUM' and tipebudget = 'MILL' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'
	order by tahunbudget desc";
//echo $str;
$opttahun="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
$optmesin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

//pilihan kodebudget tab0
$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where kodebudget like 'EXPL%'";
$optkodebudget0="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optkodebudget0.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}

//pilihan kodebudget tab1
$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where kodebudget like 'M%'";
$optmaterial1="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optmaterial1.="<option value='".$bar->kodebudget."'>".$bar->kodebudget." - ".$bar->nama."</option>";
}
$optjenis1="";
$optjenis1.="<option value='consumables'>Consumables</option>";
$optjenis1.="<option value='recurrent'>Recurrent</option>";
$optjenis1.="<option value='nonrecurrent'>Non Recurrent</option>";


//pilihan kodebudget tab2    
$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where kodebudget like 'TOOL%'";
$opttool2="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$opttool2.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}

//pilihan kodebudget tab3    
$str="select kodebudget,nama from ".$dbname.".bgt_kode
				where kodebudget like 'VHC%'";
$optkode3="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
		$optkode3.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}

//pilihan vhc tab3    
$optvhc3="";
  
//atas
OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET '.$_SESSION['lang']['biaya']." ".$_SESSION['lang']['pabrik']).'</span>');

echo"<br /><fieldset style='float:left;width:275px;'><legend>".$_SESSION['lang']['form']."</legend><table cellspacing=1 border=0>
    <tr><td>".$_SESSION['lang']['tipeanggaran']." </td><td>:</td><td>
        <input type=text class=myinputtext id=tipebudget name=tipebudget onkeypress=\"return angka_doang(event);\" maxlength=2 disabled=true style=width:145px; value=\"MILL\"/></td>
        
    </tr>
    <tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
        <input type=text class=myinputtext id=tahunbudget name=tahunbudget onkeypress=\"return angka_doang(event);\" maxlength=4 style=width:145px; /></td>
    
    </tr>
    <tr><td>".$_SESSION['lang']['station']."</td><td>:</td><td colspan=3>
        <select name=station id=station onchange=\"load_mesin();\" style='width:150px;'>".$optstation."</select></td></tr>
    <tr><td>".$_SESSION['lang']['mesin']."</td><td>:</td><td colspan=3>
        <select name=mesin id=mesin style='width:150px;'>".$optmesin."</select>
		&nbsp;<img id='mesin' onclick=z.elSearch('mesin',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
		</td></tr>
    <tr><td colspan=2></td><td colspan=3>
        <button class=mybutton id=simpan name=simpan onclick=prosesSimpan()>".$_SESSION['lang']['save']."</button>
        <button class=mybutton id=baru name=baru onclick=prosesBaru()>".$_SESSION['lang']['baru']."</button>
        <input type=hidden id=tersembunyi name=tersembunyi value=tersembunyi >
    </td></tr></table></fieldset>
    <fieldset style='width:250px;'><legend>".$_SESSION['lang']['tutup']."</legend>
        <table>
        <tr><td>".$_SESSION['lang']['pabrik']." </td><td>:</td><td>
        <select name=pabrik id=pabrik style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optpabrik."</select></td>
        </tr>
        <tr><td>".$_SESSION['lang']['budgetyear']." </td><td>:</td><td>
        <select name=tahuntutup id=tahuntutup style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</opion>".$opttahun."</select></td></tr>
        <tr><td colspan=3 align=center>
        <button class=mybutton id=tutup name=tutup onclick=prosesTutup()>".$_SESSION['lang']['close']."</button></td></tr>
        </table></fieldset>";

//tab0
CLOSE_BOX();
OPEN_BOX();

$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' order by a.noaruskas asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}
$frm[0].="<fieldset id=tab0 disabled=true><legend>".$_SESSION['lang']['eksploitasi']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
    <tr>
		<td>".$_SESSION['lang']['kodeanggaran']."</td>
		<td>:</td>
		<td><select id=kodebudget0 onchange=\"jumlahkan(0);\" name=kodebudget0 style='width:150px;'>
			<option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optkodebudget0."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
		<td><select id='aruskassdm' style='width:150px;'>".$optaruskas."</select></td>
	</tr>
	<tr>
		<td>Jumlah Personil</td><td>:</td><td><input type='text' class='myinputtextnumber' style='width:145px;' id='jmlh_0' onblur='jumlahkan(0)' onkeypress='return angka_doang(event)' value='0' /></td>
	</tr>
    <tr>
		<td>".$_SESSION['lang']['jumlahpertahun']." </td>
		<td>:</td>
		<td><input type=text class=myinputtextnumber id=jumlahpertahun0 name=jumlahpertahun0 onkeypress=\"return angka_doang(event);\" maxlength=20 style=width:145px; /></td>
	</tr>
    <tr>
		<td colspan=2></td>
		<td colspan=3><button class=mybutton id=simpan0 name=simpan0 onclick=simpan0()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=tersembunyi0 name=tersembunyi0 value=tersembunyi ></td>
	</tr></table>";
$frm[0].="</fieldset>";
$frm[0].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>    
<div id=container0></div>
    ";
$frm[0].="</fieldset>";

$optAkunTmbhAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['language']=='EN'){
  $dd='namaakun1 as namaakun';
}else{
  $dd='namaakun as namaakun';
}
      
  $sAkun="SELECT distinct noakun,".$dd." FROM ".$dbname.".`keu_5akun`
          WHERE substr(noakun, 1, 2) IN ('63', '64') and detail=1";
$qAkun=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$qAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$qAkun->fetch()){
  $optAkunTmbhAkun.="<option value='".$rAkun['noakun']."'>".$rAkun['noakun']."- [".$rAkun['namaakun']."]</option>";
}
$frm[1].="<fieldset id=tab1 disabled=true><legend>".$_SESSION['lang']['material']."</legend>";
$frm[1].="<table cellspacing=1 border=0><thead>
    </thead>
	
	
    <tr><td>".$_SESSION['lang']['noakun']."</td><td>:</td><td>
    <select id=anggaranKd  name=anggaranKd onchange=getaruskas('anggaranKd','aruskasmat'); style='width:150px;'>".$optAkunTmbhAkun."</select>
	<img id='anggaranKd' onclick=z.elSearch('anggaranKd',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
	</td></tr>
	
	<tr>
		<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
		<td><select id='aruskasmat' style='width:150px;'>".$optaruskas."</select></td>
	</tr>
	
	
	
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select id=kodebudget1 onchange=\"bersihkan(1);\" name=kodebudget1 style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optmaterial1."</select>
		<img id='kodebudget1' onclick=z.elSearch('kodebudget1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
		
		</td></tr>
    <tr><td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td>
        <input type=text class=myinputtext id=kodebarang1 name=kodebarang1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:145px; disabled=true/>
        <input type=\"image\" id=search1 disabled=true class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;' title=".$_SESSION['lang']['find']." onclick=\"searchBrg(1,'".$_SESSION['lang']['findBrg']."','<fieldset><legend>".$_SESSION['lang']['find']."</legend>Find <input type=text class=myinputtext id=no_brg value=".$kodebarang1."><button class=mybutton onclick=findBrg(1)>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";>    
        <label id=namabarang1></label></td></tr>
    <tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>
        <select id=jenis1 name=jenis1 style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optjenis1."</select></td></tr>
    <tr><td>".$_SESSION['lang']['jumlah']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber onblur=\"jumlahkan1();\" id=jumlah1 name=jumlah1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:145px; disabled=true/>
        <label id=satuan1></td></tr>
    <tr><td>".$_SESSION['lang']['totalharga']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber id=totalharga1 name=totalharga1 onkeypress=\"return false;\" maxlength=10 style=width:145px; /></td></tr>
    <tr><td></td><td></td><td>
        <button class=mybutton id=simpan1 name=simpan1 onclick=simpan1()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=regional1 name=regional1 value=>
    </td></tr></table>";
$frm[1].="</fieldset>";
//box dalam tab1, daftar table
$frm[1].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
<div id=container1></div>    
    ";
$frm[1].="</fieldset>";

$optakun=makeOption($dbname,'bgt_kode','kodebudget,noakun',"kodebudget='PKSM'");	
$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun['PKSM']."' order by a.noaruskas asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optaruskasx.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}
//tab2
$frm[2].="<fieldset id=tab2 disabled=true><legend>".$_SESSION['lang']['pemeliharaan']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <input type=text class=myinputtext id=kodebudget2 name=kodebudget2 value=\"PKSM\" maxlength=10 style=width:150px; disabled=true /></td></tr>
    <tr>
	<tr>
		<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
		<td><select id='aruskasmain' style='width:150px;'>".$optaruskasx."</select></td>
	</tr>
	<td>".$_SESSION['lang']['jumlahpertahun']." </td><td>:</td><td>
        <input type=text class=myinputtext id=jumlahpertahun2 name=jumlahpertahun2 onkeypress=\"return angka_doang(event);\" maxlength=20 style=width:150px; /></td></tr>
    <tr><td></td><td></td><td>
        <button class=mybutton id=simpan2 name=simpan2 onclick=simpan2()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=tersembunyi2 name=tersembunyi2 value=tersembunyi >
    </td></tr></table>";
$frm[2].="</fieldset>";
//box dalam tab0, daftar table
$frm[2].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>    
<div id=container2></div>
    ";
$frm[2].="</fieldset>";

$optakun=makeOption($dbname,'bgt_kode','kodebudget,noakun',"kodebudget='PKSM'");	
$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun['PKSM']."' order by a.noaruskas asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optaruskasx.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
}


//tab3
$frm[3].="<fieldset id=tab3 disabled=true><legend>".$_SESSION['lang']['abkend']."</legend>";
$frm[3].="<table cellspacing=1 border=0><thead>
    </thead>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select id=kodebudget3 name=kodebudget3 onchange=getaruskas('kodebudget3','aruskasvhc','x'); style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optkode3."</select></td></tr>
		
		<tr>
		<td>".$_SESSION['lang']['aruskas']."</td><td>:</td>
		<td><select id='aruskasvhc' style='width:150px;'>".$optaruskasx."</select></td>
	</tr>
	
    <tr><td>".$_SESSION['lang']['kodevhc']."</td><td>:</td><td>
        <select id=kodevhc3 onblur=\"jumlahkan3();\" name=kodevhc3 style='width:150px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optvhc3."</select>
		<img id='kodevhc3' onclick=z.elSearch('kodevhc3',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
		
        </td></tr>
    <tr><td>".$_SESSION['lang']['jmljamkerja']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber id=jumlahjam3 name=jumlahjam3 onblur=\"jumlahkan3();\" onkeypress=\"return angka_doang(event);\" maxlength=15 style=width:145px; /></td></tr>
    <tr><td>".$_SESSION['lang']['satuan']."</td><td>:</td><td>
        <input type=text class=myinputtext id=satuan3 name=satuan3 value=\"jam\" maxlength=15 style=width:145px; disabled=true/></td></tr>
    <tr><td>".$_SESSION['lang']['totalbiaya']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber id=totalbiaya3 name=totalbiaya3 onkeypress=\"return false;\" maxlength=15 style=width:145px; /></td></tr>
    <tr><td></td><td></td><td>
        <button class=mybutton id=simpan3 name=simpan3 onclick=simpan3()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=regional3 name=regional3 value=>
    </td></tr></table>";
$frm[3].="</fieldset>";
//box dalam tab3, daftar table
$frm[3].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
<div id=container3></div>    
    ";
$frm[3].="</fieldset>"; 

//tab4
$frm[4].="<fieldset id=tab4 disabled=true><legend>".$_SESSION['lang']['sebaran']."</legend>";
    
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Okt","11"=>"Nov","12"=>"Des");
$frm[4].="<table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>";
foreach($arrBln as $brsBulan =>$listBln){
	$frm[4].="<td align=center>".$listBln."</td>";
} 
$frm[4].="<td align=center>Action</td>";
$frm[4].="</tr></thead><tbody>";
$frm[4].="<tr class=rowcontent>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss1 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss2 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss3 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss4 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss5 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss6 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss7 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss8 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss9 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss10 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss11 value=1></td>
		<td align=center><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=ss12 value=1></td>
		<td align=center><img src=images/clear.png onclick=bersihkanDonk() class='resicon' style='cursor:pointer' title='bersihkan'></td>
	</tr>
	</tbody>
	</table>
	<span>Isi persen Kalenderisasi diatas kemudian click list kegiatan dibawah</span>
	<hr>
	";    

$namaAkun58=makeOption($dbname,'keu_5akun','noakun,namaakun');
$optNoakunData58="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOptNoakun58="select distinct noakun from ".$dbname.".bgt_budget where tipebudget='MILL' and kodebudget!='UMUM' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by noakun asc";
$qOptNoakun58=$owlPDO->query($sOptNoakun58) or die(print " Gagal: ".PDOException::getMessage());
$qOptNoakun58->setFetchMode(PDO::FETCH_ASSOC);
while($rOptNoakun58=$qOptNoakun58->fetch()){
    $optNoakunData58.="<option value='".$rOptNoakun58['noakun']."'>".$rOptNoakun58['noakun']." - ".$namaAkun58[$rOptNoakun58['noakun']]."</option>";
}
$arropt99=array(''=>$_SESSION['lang']['all'],'1'=>'Yes','2'=>'No');
foreach($arropt99 as $key => $val){
	@$opt99.="<option value='".$key."'>".$val."</option>";
}
$frm[4].="<fieldset style=float:left><legend>Find</legend><table><tr class=rowcontent>
              <td>".$_SESSION['lang']['station']."</td><td><select id=AfdSebaran onchange=load_mesin('sebaran')>".$optstation."</select></td>
              <td>".$_SESSION['lang']['mesin']."</td><td><select id=kdblokSebaran onchange='updateTab4()'>".$optmesin."</select><img id='kdblokSebaran' onclick=z.elSearch('kdblokSebaran',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			  
			  </td>
			  
			  <td>".$_SESSION['lang']['sebaran']."</td><td><select style=width:100px  id=kdsebaranData onchange='updateTab4()'>".$opt99."</select></td>
			  
              <td style=display:none>Goto Page</td><td  style=display:none id='pagingDrop'>&nbsp;<select id='pageSebaran' onchange='updateTab4()'><option value=''></option></select><span id=awalPageSebaran></span> &nbsp;".$_SESSION['lang']['dari']." &nbsp;<span id=totalPageSebaran></span></td>
			  <td colspan=5><button class=mybutton onclick=updateTab4() >".$_SESSION['lang']['preview']."</button></td></tr>
              </table>
			  </fieldset><div style=clear:both></div><hr>";
$frm[4].="<div style=clear:both></div>

			<div id='both_report'>
				<div id='head_tableboth' align=right>
					<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container4' table='sortable' >
						<img title='Full Screen' class='resicon' src='images/full-screen.png'>
					</a>
					<a class='fixheadbtn mybutton' table='sortable' idbothbody='container4' shown='0' >
						<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
					</a>
				</div>
				<div id='container4' style='overflow:auto;min-height:380px'; ></div>
			</div>
			

";
$frm[4].="</fieldset>";

//========================
//tab title
$hfrm[0]=$_SESSION['lang']['sdm'];
$hfrm[1]=$_SESSION['lang']['material'];
$hfrm[2]=$_SESSION['lang']['pemeliharaan'];
$hfrm[3]=$_SESSION['lang']['abkend'];
$hfrm[4]=$_SESSION['lang']['sebaran'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,140,'100%');
//===============================================	

CLOSE_BOX();
echo close_body();
?>