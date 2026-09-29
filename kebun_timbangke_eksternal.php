<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
function add_new_data(){
    document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	document.getElementById('listfiles').innerHTML="";
	cancelData();
}
nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
periodeDefault='<?php echo date('Y-m'); ?>';
</script>
<script language="javascript" src="js/kebun_timbangke_eksternal.js?v=<?php echo time(); ?>"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_timbangke_eksternal').'</span>');
$optTipePot=$optOrg=$opttt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

# Tahun Tanam Search
$sql = selectQuery($dbname,"pabrik_timbangan","tahuntanam","millcode='EXTM'","tahuntanam asc",true);
$res = fetchData($sql);

# PKS
$optPks="<option value=''>Pilih Data</option>";
$optTujuanSrc="<option value=''>Seluruhnya</option>";
$iPks="select distinct b.* from ".$dbname.".pmn_4komoditi a left join ".$dbname.".pmn_4customer b ON a.kodecustomer=b.kodecustomer where a.kodebarang='40000003'  and b.kodecustomer is not null"; 
$nPks=$owlPDO->query($iPks) or die(print " Gagal: ".PDOException::getMessage());
$nPks->setFetchMode(PDO::FETCH_ASSOC);
while($dPks=$nPks->fetch()){	
	$optPks.="<option value='".$dPks['kodecustomer']."'>".$dPks['namacustomer']."</option>";
	$optTujuanSrc.="<option value='".$dPks['kodecustomer']."'>".$dPks['namacustomer']."</option>";
}

# Periode Search (default: bulan berjalan)
$periodeSkrg=date('Y-m');
$listPeriode=array($periodeSkrg);
foreach(fetchData("select distinct left(tanggal,7) as periode from ".$dbname.".pabrik_timbangan where millcode='EXTM' and kodeorg IN (".getOrgDetail(2).") order by periode desc") as $rPer){
	if(!in_array($rPer['periode'],$listPeriode)){
		$listPeriode[]=$rPer['periode'];
	}
}
rsort($listPeriode);
$optPeriodeSrc="<option value=''>Seluruhnya</option>";
foreach($listPeriode as $perSrc){
	$optPeriodeSrc.="<option value='".$perSrc."'".($perSrc==$periodeSkrg?' selected':'').">".$perSrc."</option>";
}

foreach($res as $val):
	if($val['tahuntanam']==''):
		$val['tahuntanam'] = 'Kosong';
	endif;

	$opttt.="<option value='".$val['tahuntanam']."'>".$val['tahuntanam']."</option>";
endforeach;

	/* */
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	 <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	 <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo "<table cellspacing=1 border=0>
		<colgroup>
			<col style='width:85px'><col style='width:10px'><col style='width:170px'><col style='width:22px'>
			<col style='width:85px'><col style='width:10px'><col style='width:170px'><col style='width:22px'>
			<col style='width:85px'><col style='width:10px'><col style='width:170px'><col style='width:22px'>
			<col style='width:85px'><col style='width:10px'><col style='width:170px'>
		</colgroup>
		<tr>
			<td nowrap>".$_SESSION['lang']['nospb']."</td><td>:</td><td><input type=text class='myinputtext' onkeypress='return tanpa_kutip(event)' id=nosbpCr style='width:170px'/></td><td></td>
			<td nowrap>SPB Pabrik</td><td>:</td><td><input type=text class='myinputtext' onkeypress='return tanpa_kutip(event)' id=spbpabriksrc style='width:170px'/></td><td></td>
			<td nowrap>Nama Supir</td><td>:</td><td><input type=text class='myinputtext' onkeypress='return tanpa_kutip(event)' id=supirsrc style='width:170px'/></td><td></td>
			<td nowrap>".$_SESSION['lang']['tahuntanam']."</td><td>:</td><td><select class='select2' id='ttsrc' style='width:170px;'>".preg_replace("/^<option value=''>[^<]*/","<option value=''>Seluruhnya",$opttt)."</select></td>
		</tr>
		<tr>
			<td nowrap>Tujuan Pabrik</td><td>:</td><td><select class='select2' id='pabriktujuansrc' style='width:170px;'>".$optTujuanSrc."</select></td><td></td>
			<td nowrap>Periode</td><td>:</td><td><select class='select2' id='periodesrc' style='width:170px;'>".$optPeriodeSrc."</select></td><td></td>
			<td nowrap>".$_SESSION['lang']['tanggal']."</td><td>:</td>
			<td nowrap><input type=text class=myinputtext id=tgl_cari onmousemove='setCalendar(this.id)' onkeypress='return false;' size=10 maxlength=10 style='width:72px' readonly/> s/d <input type=text class=myinputtext id=tgl_cari_sampai onmousemove='setCalendar(this.id)' onkeypress='return false;' size=10 maxlength=10 style='width:72px' readonly/></td><td></td>
			<td colspan=3></td>
		</tr>
		<tr>
			<td colspan=15 style='padding-top:4px'>
				<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
				<button class=mybutton onclick=loadData('0','excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=dataKePDF(event)>PDF</button>
			</td>
		</tr>
		</table>";
echo"</fieldset></td>
	 </tr>
	 </table> "; 
	 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
<?php OPEN_BOX()?>
<div id="contain"><script>loadData(0);</script></div>
<?php CLOSE_BOX()?>
</div>

<div id="headher" style="display:none">
<?php
OPEN_BOX();
//$optTipePot
$jmMsk=$jmKlr=$mntMsk=$mntKlr="";
for($i=0;$i<24;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   @$jmMsk.="<option value=".$i." ".($i==$jmMasuk[0]?'selected':'').">".$i."</option>";
   @$jmKlr.="<option value=".$i." ".($i==$jmKeluar[0]?'selected':'').">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
        if(strlen($i)<2)
        {
                $i="0".$i;
        }
   @$mntMsk.="<option value=".$i." ".($i==$jmMasuk[1]?'selected':'').">".$i."</option>";
   @$mntKlr.="<option value=".$i." ".($i==$jmKeluar[1]?'selected':'').">".$i."</option>";
   $i++;
}
?>
<fieldset style="float:left">
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<table cellspacing="1" border="0">
<?php
$req = '<font size=2px style=color:red;vertical-align:middle><b>*</b></font>';
$sec = "style='font-weight:bold;padding-top:10px;border-bottom:1px solid #9db3cf;'";
?>
<colgroup><col style="width:190px"><col style="width:10px"><col style="width:170px"><col style="width:30px"><col style="width:190px"><col style="width:10px"><col style="width:170px"></colgroup>

<tr><td colspan="6" <?php echo $sec?>>Data SPB</td><td <?php echo $sec?>><button class=mybutton onclick=spbBelumTimbang() title='SPB External periode berjalan yang belum ada hasil timbang' style='width:155px;padding:2px 0;white-space:nowrap;font-size:11px'>SPB Belum Ditimbang <span id=badgeBelum style='display:none;background:#d9534f;color:#fff;border-radius:9px;padding:0 5px;font-size:10px'></span></button></td></tr>
<tr>
	<td><?php echo $_SESSION['lang']['tanggal']." ".$_SESSION['lang']['timbangan']." ".$req?></td>
	<td>:</td>
	<td><input style="width:150px" type=text class=myinputtext id=tgl onmousemove='setCalendar(this.id)' onkeypress='return false;' onchange='getNosbp()' size=10 maxlength=10 readonly/></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['nospb']." ".$req?></td>
	<td>:</td>
	<td>
		<select class="select2" id="spbId" name="spbId" style="width:155px;"><?php echo $optOrg;?></select>
	</td>
</tr>
<tr>
	<td><?php echo $_SESSION['lang']['nomor']." ".$_SESSION['lang']['ticket']." timbangan kebun (DMA & MHA) ".$req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="tktkebun" name="tktkebun" style="width:150px;" /></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['nomor']." ".$_SESSION['lang']['ticket']." ".$req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="notiket" name="notiket" style="width:150px;" /></td>
</tr>
<tr>
	<td>SPB Pabrik <?php echo $req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="spbpabrik" name="spbpabrik" style="width:150px;"/></td>
	<td></td>
	<td>Tujuan Pabrik <?php echo $req?></td>
	<td>:</td>
	<td><select class="select2" id="pabriktujuan" name="pabriktujuan" style="width:155px;"><?php echo $optPks;?></select></td>
</tr>

<tr><td colspan="7" <?php echo $sec?>>Kendaraan</td></tr>
<tr>
	<td><?php echo $_SESSION['lang']['nopol']." ".$req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" maxlength="20" onkeypress="return tanpa_kutip(event)" onkeydown="upperCaseF(this)" id="kdKend" name="kdKend" style="width:150px;" /></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['supir']." ".$req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" onkeydown="upperCaseF(this)" id="nmSupir" name="nmSupir" style="width:150px;" /></td>
</tr>
<tr>
	<td>Tahun Tanam <?php echo $req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" id="tahuntanam2" name="tahuntanam2" maxlength="4" style="width:150px;"/></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['jumlah']." ".$_SESSION['lang']['jjg']." ".$req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="jmlhJjg" name="jmlhJjg" onkeyup="z.numberFormat('jmlhJjg',2)" style="width:150px;" /></td>
</tr>

<tr><td colspan="7" <?php echo $sec?>>Timbangan</td></tr>
<tr>
	<td><?php echo $_SESSION['lang']['jammasuk']?></td>
	<td>:</td>
	<td><select style="width:70px" id=jmMasuk><?php echo $jmMsk; ?></select> : <select id=mntMasuk style="width:72px"><?php echo $mntMsk; ?></select></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['jamkeluar']?></td>
	<td>:</td>
	<td><select id=jmKeluar style="width:70px"><?php echo $jmKlr; ?></select> : <select id=mntKeluar style="width:72px"><?php echo $mntKlr; ?></select></td>
</tr>
<tr>
	<td><?php echo $_SESSION['lang']['beratMasuk']?> (Kg) <?php echo $req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtMsk" name="brtMsk" onkeyup="z.numberFormat('brtMsk',2);getBersih(0)" onblur="getBersih(0)" style="width:150px;" /></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['beratKeluar']?> (Kg) <?php echo $req?></td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtKlr" name="brtKlr" onkeyup="z.numberFormat('brtKlr',2);getBersih(0)" onblur="getBersih(0)" style="width:150px;" /></td>
</tr>
<tr>
	<td>Buah dikembalikan</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="buahdikembalikan" name="buahdikembalikan" onkeyup="z.numberFormat('buahdikembalikan',2)" style="width:150px;" value="0" /></td>
	<td colspan="4"></td>
</tr>

<tr><td colspan="7" <?php echo $sec?>>Potongan</td></tr>
<tbody id="datapotongan"></tbody>
<tr>
	<td><?php echo $_SESSION['lang']['potongan']?> (Kg)</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" onblur="getBersih(0)" id="potKg" name="potKg" onkeyup="z.numberFormat('potKg',2);getBersih(0)" disabled style="width:150px;" /></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['beratBersih']?> (Kg)</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" disabled onkeypress="return angka_doang(event)" id="brtBrsh" name="brtBrsh" style="width:150px;" /></td>
</tr>

<tr hidden>
	<td><?php echo $_SESSION['lang']['kontrak']?></td>
	<td>:</td>
	<td><input type="text" class="myinputtext" onkeypress="return tanpa_kutip(event)" onclick="searchNosibp('<?php echo $_SESSION['lang']['find']?>','<div id=formPencariandata></div>',event)" id="nokontrak" name="nokontrak" style="width:145px;" /></td>
	<td></td>
	<td><?php echo $_SESSION['lang']['nodo']?></td>
	<td>:</td>
	<td><select id="nodo" name="nodo" style="width:145px;"></select></td>
</tr>
<tr hidden>
	<td><?php echo $_SESSION['lang']['jjgpenalty']?></td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="JjgSortasi" name="JjgSortasi" style="width:80px;" /></td>
</tr>
<tr hidden>
	<td><?php echo $_SESSION['lang']['tanggal']." ".$_SESSION['lang']['timbangan']?> (Pabrik)</td>
	<td>:</td>
	<td><input style="width:80px" type=text class=myinputtext id=tglpks onmousemove='setCalendar(this.id)' onkeypress='return false;' size=10 maxlength=10 readonly/></td>
</tr>
<tr hidden>
	<td><?php echo $_SESSION['lang']['beratMasuk']?> (Kg)(Pabrik)</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtMskpmks" name="brtMskpmks" onblur="getBersih(1)" style="width:80px;" /></td>
</tr>
<tr hidden>
	<td><?php echo $_SESSION['lang']['beratKeluar']?> (Kg)(Pabrik)</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" onkeypress="return angka_doang(event)" id="brtKlrpmks" name="brtKlrpmks" onblur="getBersih(1)" style="width:80px;" /></td>
</tr>
<tr hidden>
	<td><?php echo $_SESSION['lang']['beratBersih']?> (Kg)(Pabrik)</td>
	<td>:</td>
	<td><input type="text" class="myinputtextnumber" disabled onkeypress="return angka_doang(event)" id="brtBrshpmks" name="brtBrshpmks" style="width:80px;" /></td>
</tr>
<tr><td colspan='7'><hr></td></tr>
<tr id="uplFileId"><td></td><td colspan='5'>
<?php
echo"
	<fieldset style=float:left>
		<legend>Upload</legend>
		<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
				<td align='center' width=30px>No.</td>
				<td align='center'>Filename</td>
				<td align='center' width=30px>#</td>
			</tr>
			</thead>
			<tr><td colspan=2>
				<input type='file' class=mybutton name='upload' id='upload' >
			</td>
			<td align='center'><img src=images/plus.png style=width:20px;heigth:20px;cursor:pointer; id=btnsubmit title=\"Add File\" onclick=\"submitfile()\"></td>
			</tr>
			<tbody id='listfiles'>
			</tbody>
		</table>
	</fieldset>";
?>

</td></tr>
<tr><td colspan='7'><hr></td></tr>
<tr>
<td colspan=7 align=center><input type="hidden" id="notrans" value='' /><input type="hidden" id="proses" value='insert' />
    <div id="tombolHeader">
        <button class=mybutton id=dtlAbn onclick=saveData()><?php echo $_SESSION['lang']['save'] ?></button>
        <button class=mybutton id=cancelAbn onclick=cancelData()><?php echo $_SESSION['lang']['cancel']?></button>
    </div>
</td>
</tr>
</table>
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();
?>