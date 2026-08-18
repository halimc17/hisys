<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/kebun_5mutuancak.js'></script>
<?

//get optjenis
$optjenis='';
$arrjenis=getEnum($dbname,'kebun_5jenismutu','jenis');
foreach($arrjenis as $kei)
{
	$optjenis.="<option value='".$kei."'>".$kei."</option>";
} 

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5mutuancak').'</span>');
//Tab 1 jenis mutu
$frm[0]="<fieldset style='width:350px'>
	<legend>".$_SESSION['lang']['form']."</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td><select id='jenis1'>".$optjenis."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
				<td><textarea style='width:200px' id='kriteria1' maxlength=40></textarea></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>:</td>
				<td><input type='text' class=myinputtext style='width:217px' id='satuan1' maxlength=20></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['satuan']." / ".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><input type='text' class=myinputtext style='width:217px' id='satuan199' maxlength=20></td>
			</tr>
			<tr>
				<td colspan=3 style='text-align:center'>
					<input type='hidden' value='' id='idjenis1' />
					<input type='hidden' value='insert1' id='method1' />
					<button class=mybutton onclick=simpan1()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=batal1()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container1> 
			<script>loaddata1(0)</script>
		</div>
	</fieldset>";

//tab Mutu ancak, trans dan buah

//get optpt
$spt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$qpt=$owlPDO->query($spt) or die(print " Gagal: ".PDOException::getMessage());
$qpt->setFetchMode(PDO::FETCH_ASSOC);
$optpt='';
while($rpt=$qpt->fetch())
{
        $optpt.="<option value='".$rpt['kodeorganisasi']."'>".$rpt['namaorganisasi']."</option>";	
}

//get optjenismutu
$kriteria=$optjenismutu="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sjenis="select distinct(jenis) from ".$dbname.".kebun_5jenismutu";
$qjenis=$owlPDO->query($sjenis) or die(print " Gagal: ".PDOException::getMessage());
$qjenis->setFetchMode(PDO::FETCH_ASSOC);
while($rjenis=$qjenis->fetch())
{
        $optjenismutu.="<option value='".$rjenis['jenis']."'>".$rjenis['jenis']."</option>";	
}


$frm[1]="<fieldset style='width:640px'>
	   <legend>".$_SESSION['lang']['form']."</legend>
	   	<table>
			<tr>
                <td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
                <td><select id='pt' name='pt' style='width:150px;'>".$optpt."</select></td>
                <td>&nbsp;</td>
                <td>".$_SESSION['lang']['ket']." ".$_SESSION['lang']['total']." ".$_SESSION['lang']['point']."</td>
				<td>:</td>
                <td style='width:150px;'><input type='text' id='keterangan' class='myinputtext' maxlength='20' size='20' /></td>                     	  
				</td>
			</tr>

            <tr>            	
                <td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td><select id='jenismutu' name='jenismutu' style='width:150px;' onchange='getkriteria(0,0)'>".$optjenismutu."</select></td>
                <td>&nbsp;</td>
                <td>".$_SESSION['lang']['range']." ".$_SESSION['lang']['total']." ".$_SESSION['lang']['point']."</td>
				<td>:</td>
				<td>".makeElement('rangetotaldari','textnum',0,array('style'=>'width:58px','maxlength'=>10))." s/d ".makeElement('rangetotalsampai','textnum',0,array('style'=>'width:58px','maxlength'=>10))."</td>				
				<td>&nbsp;</td>
            </tr>
      
			<tr>
				<td>".$_SESSION['lang']['kriteria']."</td>
				<td>:</td>
                <td><select id='kriteriamutu' name='kriteriamutu' style='width:150px;'>".$kriteria."</select></td>
				<td>&nbsp;</td>
				<td>".$_SESSION['lang']['warna']."</td>
				<td>:</td>
				<td>
					<input disabled type=text class=myinputtext id=kodefill name=kode onkeypress=\"return tanpa_kutip(event);\" style=\"width:128px;\" />
					<img  class=resicon src=images/color_fill.png style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna('fill',event)>
				</td>
            </tr>
			<tr>
                <td>".$_SESSION['lang']['range']."</td>
				<td>:</td>
				<td>".makeElement('rangedari','textnum',0,array('style'=>'width:58px','maxlength'=>10))." s/d ".makeElement('rangesampai','textnum',0,array('style'=>'width:58px','maxlength'=>10))."</td>
	            <td>&nbsp;</td>  
                <td>Sample Warna Fill</td>
				<td>:</td>
				<td id=displaycolorfill style=\"width:94px;\" style=position:relative;top:5px title='".$_SESSION['lang']['find']."' onclick=cariwarna(event)></td>              
			</tr>
			<tr>
	            <td>".$_SESSION['lang']['nilai']."</td>
				<td>:</td>
                <td>".makeElement('nilai','textnum',0,array('style'=>'width:146px','maxlength'=>10))."</td>
			</tr>
			<tr>
				<td><td>
				<td>    
	 				<input type=hidden value=insert id=method>
	 				<button class=mybutton onclick=simpanmutu()>".$_SESSION['lang']['save']."</button>
	 				<button class=mybutton onclick=batalmutu()>".$_SESSION['lang']['cancel']."</button>
     			</td>
     		</tr>
     	</table>
	  </fieldset>

	  <fieldset style='width:640px'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=containermutu> 
			<script></script>
		</div>
	  </fieldset>";

$hfrm[0]=$_SESSION['lang']['jenismutu'];
$hfrm[1]=$_SESSION['lang']['mutuancak'].", ".$_SESSION['lang']['transport']." ".$_SESSION['lang']['dan']." ".$_SESSION['lang']['buah'];
	 
drawTab('FRM',$hfrm,$frm,300,1235);
CLOSE_BOX();
echo close_body('');
?>