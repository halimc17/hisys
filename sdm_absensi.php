<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_absensi').'</span>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
    nmTmblDone = '<?php echo $_SESSION['lang']['done'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';
    nmTmblSave = '<?php echo $_SESSION['lang']['save'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';

</script>
<script language="javascript" src="js/sdm_absensi.js?v=<?php echo time(); ?>"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
    <?php
	
    $sGp = "select DISTINCT periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and `sudahproses`=0 order by periode desc";
	$qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
	$qGp->setFetchMode(PDO::FETCH_ASSOC);
    $optPeriode = "";
    while ($rGp = $qGp->fetch()) {
        $optPeriode.="<option value=" . $rGp['periode'] . ">" . substr(tanggalnormal($rGp['periode']), 1, 7) . "</option>";
    }


     #=Ambil kode organisasi
    $sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".getOrgDetail(31).") order by kodeorganisasi asc";
    $rOrg=fetchData($sOrg);
    foreach ($rOrg as $val) {

        $d= substr($val['kodeorganisasi'], 0, 4);
        if($d!=$n){			
            $optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
        }

        if(strlen($val['kodeorganisasi']) == '4' ){
            $captionTambah = ' (UMUM)';
        }else{
            $captionTambah = '';
        }

        $optOrg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']." ".$captionTambah." </option>";		
        
        $n=$d;
        if($d!=$n){			
            $optOrg.="</optgroup>";
        }
    }

    if ($_SESSION['language'] == 'ID') {
        $ket = "Form absensi hari libur berfungsi untuk mencatat absensi
				seluruh karyawan yang masih aktif secara otomatis. <br>
				Setiap hari libur dan hari minggu  harus dicatatkan melalui form ini.";
    } else {
        $ket = "Form holiday attendance serves to record the attendance of all employees<br>
				who are still active automatically. <br>
                Every holiday and day of week should be listed in this form.";
    }

    echo"<table cellspacing=1 border=0>
     <tr>
	 <td  valign=moiddle align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td  valign=moiddle align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td valign=moiddle>
	 <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
	 
    echo"<table border=0>
	<tr>
		<td>".$_SESSION['lang']['unitkerja'] . " </td>
		<td>:</td>
		<td> <select id=kdOrgCari style='width:100px;' ><option value=''></option>" . $optOrg . "</select><!--<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext onclick=\"cariOrg('" . $_SESSION['lang']['find'] . "','<fieldset style=height:80px><legend>" . $_SESSION['lang']['searchdata'] . "</legend>Find<input type=text class=myinputtext id=crOrg><button class=mybutton onclick=findOrg2()>Find</button></fieldset><div id=container></div>','event')\">-->&nbsp;
		</td></tr>
		<tr><td>".$_SESSION['lang']['tanggal'] . " </td>
		<td>:</td>
		<td> <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:95px;' maxlength=10 readonly/></td></tr><tr><td><td><td>
		<button class=mybutton onclick=loadData();>" . $_SESSION['lang']['find'] . "</button></tr></table>";

	$abs = "<option value=''></option>";
	$str="select * from ".$dbname.".sdm_5absensi where status='1' and kodeabsen in ('MG','L','LN')";
	$res = fetchData($str);
	foreach($res as $val){
		$abs.="<option value=".$val['kodeabsen'].">".$val['keterangan']."</option>";	
	}
	
	$tp = "<option value=''>SELURUHNYA</option>";
	$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' and id not in ('4')";
	$res = fetchData($str);
	foreach($res as $val){
		$tp.="<option value=".$val['id'].">".$val['tipe']."</option>";	
	}
	
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where = "";
	} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
	} else {
		$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
	}


	$optorg="<option value=''></option>";
	$unit='';
	$arrUnit = getOrgDetail(1);
	foreach($arrUnit as $key=>$val){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}

		$optorg.="<option value='".$key."'>".$val."</option>";			
		
		$n=$d;
		if($d!=$n){			
			$optorg.="</optgroup>";
		}
	}

    echo"</fieldset></td>
		<td>
			<fieldset><legend>" . $_SESSION['lang']['absenharilibur'] . "</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['kodeorg']."</td><td>:</td>
						<td><select id=kodeorghm onchange=getdivisi(); style=width:100px>".$optorg."</select></td>
						
						<td>".$_SESSION['lang']['divisi']."</td><td>:</td>
						<td><select id=divisihm style=width:100px></select></td>
						
						<td>".$_SESSION['lang']['tipekaryawan']."</td><td>:</td>
						<td><select id=tipekary style=width:100px>".$tp."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td><td>:</td>
						<td><input type=text class=myinputtext id=tgllibur onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 style=width:95px readonly/></td>
						<td>".$_SESSION['lang']['absensi']."</td><td>:</td>
						<td><select id=jlibur style=width:100px>".$abs."</select></td>
						<td colspan=3>
						  <button class=mybutton onclick=saveHariLibur()>" . $_SESSION['lang']['save'] . "</button> 
						</td>
					</tr>
					<tr>
						<td colspan=30 style=font-size:10px>" . $ket . "</td>
					</tr>
				</table>
			 </fieldset>
		 </td>
	 </tr>
	 </table> ";
    ?>
</div>
    <?php
    CLOSE_BOX();
    ?>
<div id="listData">
    <?php OPEN_BOX() ?>
    
        <div id="contain">
            <script>loadData();</script>
        </div>
    
<?php CLOSE_BOX() ?>
</div>



<div id="headher" style="display:none">
    <?php
    OPEN_BOX();
    $optPrd = "";
    for ($x = 0; $x <= 3; $x++) {
        $dte = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
        $optPrd.="<option value=" . date("m-Y", $dte) . ">" . date("m-Y", $dte) . "</option>";
    }
    ?>
    <fieldset style='float:left;'>
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><?php echo $_SESSION['lang']['kodeorg'] ?></td>
                <td>:</td>
                <td>
                    <select id="kdOrg" style="width:150px;" ><option value=""><?php echo $_SESSION['lang']['pilihdata']; ?></option><?php echo $optOrg ?></select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['tanggal'] ?></td>
                <td>:</td>
                <td>
                    <input type="text" class="myinputtext" id="tglAbsen" name="tglAbsen" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/>
                </td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['periode'] ?></td>
                <td>:</td>
                <td>
                    <select id="periode" name="periode" style="width:150px;" ><?php echo $optPeriode; ?></select>
                </td>
            </tr>
				<tr>
					<td></td><td></td>
					<td colspan="3" id="tmbLheader"></td>
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
?>
    <div id="addRow_table">
        <fieldset>
            <legend><?php echo $_SESSION['lang']['detail'] ?></legend>
            <div id="detailIsi">
            </div>
            <table>
                <tr><td id="tombol">

                    </td></tr>
            </table>
        </fieldset>
    </div>
	<div style=clear:both></div>
	<div style=clear:both></div>
	<fieldset style="min-height:280px">
		<legend><?php echo $_SESSION['lang']['datatersimpan'] ?></legend>
		<div >
            <table class='sortable' border='0' cellspacing='1' cellpadding='5' style='width:100%;'>
                <thead>
                    <tr class="rowheader">
                        <th align=center style=width:30px>No</th>
                        <th align=center><?php echo $_SESSION['lang']['nik2'] ?></th>
                        <th align=center><?php echo $_SESSION['lang']['namakaryawan'] ?></th>
                        <th align=center><?php echo str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan']) ?></th>
						<?php
							
							echo"<th align=center >".$_SESSION['lang']['akun']."</th>";
							echo"<th align=center >".$_SESSION['lang']['kegiatan']."</th>";
							echo"<th align=center >".$_SESSION['lang']['alokasi']."</th>";
						?>
                        <th align=center><?php echo $_SESSION['lang']['shift'] ?></th>
                        <th align=center><?php echo $_SESSION['lang']['absensi'] ?></th>
                        <th align=center style=width:50px><?php echo $_SESSION['lang']['jumlahhk'] ?></th>
                        <th align=center style='width:50px'><?php echo $_SESSION['lang']['jamMsk'] ?></th>
                        <th align=center style='width:50px'><?php echo $_SESSION['lang']['jamistirahatdari'] ?></th>
                        <th align=center style='width:50px'><?php echo $_SESSION['lang']['jamistirahatsampai'] ?></th>
                        <th align=center style='width:50px'><?php echo $_SESSION['lang']['jamPlg'] ?></th>
                        <th style='display:none'><?php echo $_SESSION['lang']['pembagiancatu'] ?></th>
                        <th align=center style='width:80px;display:none'><?php echo $_SESSION['lang']['penaltykehadiran'] ?></th>
						<th align=center><?php echo $_SESSION['lang']['premi'] ?></th>
						<th align=center style='width:80px;display:none'>Extra Fooding</th>
                        <th align=center ><?php echo $_SESSION['lang']['keterangan'] ?></th>
                        <th align=center ><?php echo $_SESSION['lang']['noreferensi'] ?></th>
                        <th align=center colspan=3>Action</th>
                    </tr>
                </thead>
                <tbody id="contentDetail">

                </tbody>
            </table>
		</div>
	</fieldset>
<?php
CLOSE_BOX();
?>
</div>
<?php
echo close_body();
?>
