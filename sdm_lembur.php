<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
 
nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
nmTmblSave='<?php echo $_SESSION['lang']['save']?>';
nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
</script>
<script language="javascript" src="js/sdm_lembur.js?v=<?php echo time(); ?>"></script>

<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_lembur').'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert"  />


<div id="action_list">
<?php
$_SESSION['lembur']=array();

$optPeriode="";
for($x=0;$x<=24;$x++){
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
}

//$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
$optOrg2 = getOrgDetail(1);
$optOrg="";
foreach ($optOrg2 as $key => $nmorg) {
	$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$key."' or induk='".$key."') ORDER BY length(kodeorganisasi),tipe asc,`kodeorganisasi` ASC";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
	
	while($res=$query->fetch()){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$res['kodeorganisasi']."'");
		$d=$induk[$res['kodeorganisasi']];
		if($d!=$n){			
			$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
		
		$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>"; 
		
		$n=$d;
		if($d!=$n){			
			$optOrg.="</optgroup>";
		}
	}
}

$optOrg="";
$idOrg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$sql = "select kodeorganisasi,namaorganisasi 
from " . $dbname . ".organisasi 
where kodeorganisasi='" . $idOrg . "' or induk='" . $_SESSION['empl']['lokasitugas'] . "' and tipe NOT LIKE '%GUDANGTEMP%'
ORDER BY `kodeorganisasi` ASC";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);

while($res=$query->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$res['kodeorganisasi']."'");
	$d=$induk[$res['kodeorganisasi']];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>"; 
	
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

##jabatan
$optjab="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach ($optOrg2 as $key => $nmorg){
	$str="select distinct a.kodejabatan,b.namajabatan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
	where lokasitugas='".$key."' and tipekaryawan not in (0,7,8,9,10) order by namajabatan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optjab.="<option value='".$bar['kodejabatan']."'>".$bar['namajabatan']."</option>";
	}
}

##tipekaryawan
$opttpkar="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where id not in (0,7,8,9,10) and aktif='1' order by tipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$opttpkar.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
}


echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['kodeorganisasi']." : <select style=width:150px  id=kdOrgCr><option value=''></option>".$optOrg."</select>&nbsp;";
			echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
			echo"<button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button>";
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
<div id="contain"><script>loadData();</script></div>
<?php CLOSE_BOX()?>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();
	
?>
<fieldset  style='float:left'>
<legend><?php echo $_SESSION['lang']['header']?></legend>
<table cellspacing="1" border="0">
	<tr>
		<td><?php echo $_SESSION['lang']['unitkerja']?></td>
		<td>:</td>	
		<td>
		<select id="kdOrg" name="kdOrg" style="width:150px;" ><option value=""><?php echo $_SESSION['lang']['pilihdata']; ?></option><?php echo $optOrg;?></select>
		</td>
	</tr>
	<tr>
		<td><?php echo $_SESSION['lang']['jabatan'] ?></td> 
		<td>:</td>
		<td><select id="jabatan" style="width:150px;"><?php echo $optjab ?></select></td>
	</tr> 

	<tr>
		<td><?php echo $_SESSION['lang']['tipekaryawan'] ?></td> 
		<td>:</td>
		<td><select id="tipekar" style="width:150px;"><?php echo $opttpkar ?></select></td>
	</tr> 
	<tr>
		<td><?php echo $_SESSION['lang']['tanggal']?></td>
		<td>:</td>
		<td>
		<input type="text" class="myinputtext" id="tglAbsen" name="tglAbsen" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/>
		</td>
	</tr>
	<tr>
		<td><td><td id="tmbLheader">
		</td>
		</td>
		</td>
	</tr>
</table>
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<div id="detailEntry" style="display:none">
<?php 
OPEN_BOX();
#echo makeElement('btnspl','btn','Add from SPL',array('onclick'=>"searchspl('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';            
?>
<!--<fieldset >
<legend><?php echo $_SESSION['lang']['detail']?></legend>
-->
<br>
<div id="addRow_table">
	<div id="detailIsi"></div>
		<table cellspacing="1" border="0">
			<tr>
				<td id="tombol"></td>
			</tr>
		</table>
</div>
<div id="loaddetail" style="overflow:auto; height:300px;">
<table cellspacing='1' cellpadding='5' border='0' class='sortable' style='width:100%;' >
<thead>
 <tr class="rowheader">
 <th align='center'>No</th>
    <th align='center' ><?php echo $_SESSION['lang']['nik2'] ?></th>
    <th align='center' ><?php echo $_SESSION['lang']['namakaryawan'] ?></th>
    <th align='center' ><?php echo $_SESSION['lang']['jabatan'] ?></th>
    <th align='center' ><?php echo $_SESSION['lang']['divisi'] ?></th>
    <th align='center' ><?php echo $_SESSION['lang']['bagian'] ?></th>
    <th align='center' ><?php echo $_SESSION['lang']['tipekaryawan'] ?></th>
	<?php
		$e="";
		$k="style=display:none;";
		if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
			$e="style=display:none;";
		}
		echo"<th align=center ".$e.">".$_SESSION['lang']['akun']."</th>";
		echo"<th align=center ".$e." ".$k.">".$_SESSION['lang']['alokasi']."</th>";
	?>
 	<th align='center' ><?php echo $_SESSION['lang']['tipelembur'] ?></th>
  	<th align='center' style='width:50px'><?php echo $_SESSION['lang']['jamaktual'] ?></th>
  	
	<th hidden align='center' style='width:100px'><?php echo $_SESSION['lang']['uangmakan'] ?></th>
    <th hidden align='center' style='width:100px'><?php echo $_SESSION['lang']['penggantiantransport'] ?></th>
	 <th  align='center' style='width:50px'><?php echo $_SESSION['lang']['uangkelebihanjam'] ?></th>
	 
	 <th align='center' style='width:50px'><?php echo $_SESSION['lang']['jam'] ?> <?php echo $_SESSION['lang']['mulai'] ?></th>
	 <th align='center' style='width:50px'><?php echo $_SESSION['lang']['jamselesai'] ?></th>
	 <th align='center'><?php echo $_SESSION['lang']['keterangan'] ?></th>
    <th colspan='2' align='center' style='width:40px'>Action</th>
    </tr>
</thead>
<tbody id="contentDetail">

</tbody>
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();
?>

