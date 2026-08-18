<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5verifikasibkm.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?

$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$skary="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan WHERE tanggalkeluar='0000-00-00' AND tipekaryawan IN('0','1')
        AND namakaryawan not like 'ADMINISTRATOR%' ORDER BY namakaryawan asc";
$rkary=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
$rkary->setFetchMode(PDO::FETCH_OBJ);
while($bkary=$rkary->fetch()){
	$optkary.="<option value='".$bkary->karyawanid."'>".$bkary->nik." - ".$bkary->namakaryawan."</option>";	
}

$optunit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sunit="SELECT kodeorganisasi, namaorganisasi FROM $dbname.`organisasi` WHERE tipe='KEBUN'";
$runit=$owlPDO->query($sunit) or die(print " Gagal: ".PDOException::getMessage());
$runit->setFetchMode(PDO::FETCH_OBJ);
while($bunit=$runit->fetch()){
	$optunit.="<option value='".$bunit->kodeorganisasi."'>".$bunit->kodeorganisasi." - ".$bunit->namaorganisasi."</option>";	
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5verifikasibkm').'</span>');
echo"<div>
        <fieldset>
            <legend>".$_SESSION['lang']['form']."</legend>
            <table style='display: inline-block;vertical-align:top'>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td><td>:</td>
                    <td><select class=select2 style=width:150px id=unit >".$optunit."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
                    <td><select class=select2 style=width:150px id=karyawanid>".$optkary."</select></td>
                </tr>
                <tr>
                     <td>".$_SESSION['lang']['status']."</td> 
                    <td>:</td>
                    <td>
                        <input type=checkbox id=status checked>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input type=hidden value=insert id=proses>
                        <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                    </td>
                </tr>
            </table>

            <table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
                <tr>
                    <td>
                        <fieldset>
                            <legend>".$_SESSION['lang']['keterangan']."</legend>
                            Status :<br>
                            &nbsp;- Aktif : Centang CheckBox <input type='checkbox' checked disabled><br>
                            &nbsp;- Non Aktif : Uncentang CheckBox <input type='checkbox' disabled>
                        </fieldset>
                    </td> 
                </tr>
	        </table>
        </fieldset>
     </div>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:350px;float:left;><legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>