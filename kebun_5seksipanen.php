<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/kebun_5seksipanen.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION','PABRIK','KANWIL','HOLDING') 
              and length(kodeorganisasi)=4 order by kodeorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$optAll='';
    while($bar=$res->fetch())
    {
        $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }
}
else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL')
	{
    $wheredt="regional='".$_SESSION['empl']['regional']."'";
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION','PABRIK','KANWIL')
                   and length(kodeorganisasi)=4
          and (kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where ".$wheredt.")
          or induk in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where ".$wheredt.")) 
          order by kodeorganisasi asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
        }
	}
else if(trim($_SESSION['empl']['tipelokasitugas'])=='KEBUN')//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{

    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
        and kodeorganisasi  like '".$_SESSION['empl']['lokasitugas']."%' order by kodeorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
    }
}


$arrSeksi=array('A'=>'Seksi A','B'=>'Seksi B','C'=>'Seksi C','D'=>'Seksi D','E'=>'Seksi E','F'=>'Seksi F','G'=>'Seksi H','I'=>'Seksi I','J'=>'Seksi J','K'=>'Seksi K','L'=>'Seksi L','M'=>'Seksi M');
$optseksi="<option value=''></option>";
foreach($arrSeksi as $key=>$value)
{
	$optseksi.="<option value='".$key."'>".$key." - ".$value."</option>";
}
?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Harvest Section').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Seksi Panen').'</span>');
	
}
// style=\"width:550px;float:left\" 
echo "<br>";

echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kodeorg']."</td>
					<td>:</td>
					<td><select id=kodeorg onchange=getdivisi() style=\"width:175px;\" >".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi onchange=getblok() style=\"width:175px;\" >".$optdivisi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>:</td>
					<td><select id=blok onchange=gettahuntanam() style=\"width:175px;\" >".$optblok."</select>
					<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				
				</tr>
				
				<tr>
					<td width=100>".$_SESSION['lang']['seksi']."</td>
					<td>:</td>
					<td><select id=seksi style=\"width:175px;\" >".$optseksi."</select></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['tahuntanam']."</td>
					<td>:</td>
					<td >&nbsp;<span id=tahuntanam></span></td>
				</tr>				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
		

CLOSE_BOX();
OPEN_BOX();
// fieldset style=\"width:1000px;\" 
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>