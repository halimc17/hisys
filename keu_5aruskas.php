<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_5aruskas.js?v=<?php echo time(); ?>'></script>

<?php

$optinduk=$optLevel=$optTipeTrans=$optjenis=$optPemilik=$optAksesRek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

### Get Value Enum Tipe Transasksi
$arrTipeTrans=getEnum($dbname,'keu_5aruskas','tipetransaksi');
$strtipe=array ("M"=>"MASUK","K"=>"KELUAR");
foreach ($arrTipeTrans as $kei => $fal) {
    $optTipeTrans.="<option value='".$kei."'>".$kei." - ".$strtipe[$fal]."</option>";
}

### Get Value Enum Akses Rekening
$arrAksesRek=getEnum($dbname,'keu_5aruskas','akses_rekening');
foreach ($arrAksesRek as $kei => $fal) {
    $optAksesRek.="<option value='".$kei."'>".$kei."</option>";
}

### Get Value Enum Level
$arrLevel=getEnum($dbname, 'keu_5aruskas', 'level');
foreach ($arrLevel as $kei => $fal) {
    $optLevel.="<option value='".$kei."'>".$kei."</option>";
}

### Get Value Pemilik
$optOrgpemilik=array('GLOBAL'=>'GLOBAL','HOLDING'=>'HOLDING','UNIT'=>'UNIT','KANWIL'=>'KANWIL');
foreach ($optOrgpemilik as $key => $fal) {
    $optPemilik.="<option value= '".$key."'>".strtoupper($fal). "</option>";
}

#### untuk tipe keluar
$optExpn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrTipExs=array("F"=>"Fixed Expenses","V"=>"Variable Expenses");
foreach($arrTipExs as $row=>$lst){
    $optExpn.="<option value='".$row."'>".$lst."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5aruskas').'</span></br>');
echo"<fieldset style='float:left'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
            <tr>
                <td>".$_SESSION['lang']['tipetransaksi']."</td> 
                <td>:</td>
                <td><select id=tipetrans style=\"width:205px;\">".$optTipeTrans."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['level']."</td> 
                <td>:</td>
                <td><select id=level style=\"width:205px;\" onchange=forminduk()>".$optLevel."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['induk']."</td> 
                <td>:</td>
                <td><select id=indukkas style=\"width:205px;\" disabled>".$optinduk."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nomor']." ".$_SESSION['lang']['induk']." ".$_SESSION['lang']['aruskas']."</td>
                <td>:</td>
                <td><input type=text  id=noarus onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber	style=\"width:200px;\" disabled></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['induk']." ".$_SESSION['lang']['aruskas']."</td>
                <td>:</td>
                <td><input type=text onkeydown=\"upperCaseF(this)\" id=namaarus nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
            </tr>
            <tr>
                <td> Akses ".$_SESSION['lang']['rekening']."</td>
                <td>:</td>
                <td><select id=aksesRek style=\"width:205px;\" >".$optAksesRek."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['pemilik']."</td> 
                <td>:</td>
                <td><select id=pemilik style=\"width:205px;\">".$optPemilik."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['biaya']."</td>
                <td>:</td>
                <td><select id=tpExpns style=\"width:205px;\" disabled>".$optExpn."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['status']."</td>
                <td>:</td>
                <td><input type=checkbox id=status1 checked>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td colspan=3>
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
					<table>
						<tr>
							<td>Status :</td><td>Pemilik :</td>
						</tr>
						<tr>
							<td>&nbsp;- Aktif : Centang CheckBox <input type='checkbox' checked disabled></td>
							<td>&nbsp;- GLOBAL : Seluruh Unit</td>
							<td>&nbsp;- KANWIL : Kantor regional / RO</td>
						</tr>
						<tr>
							<td>&nbsp;- Tidak Aktif : Uncentang CheckBox <input type='checkbox' disabled></td>
							<td>&nbsp;- HOLDING : Kantor Pusat / HO</td>
							<td>&nbsp;- KEBUN : Seluruh Unit Kebun</td>
						</tr>
						<tr>
							<td></td>
							<td>&nbsp;- UNIT : Seluruh Unit Pabrik dan Kebun</td>
						</tr>
					</table>
				</fieldset>
			</td> 
		</tr>
        </table>
	</fieldset>
	<input type=hidden id=method value='insert'>";
CLOSE_BOX();

OPEN_BOX();
//ISI UNTUK DAFTAR 
echo "
        <div id=container style='width:100%; height:550px;overflow:auto;'> 
            <script>loadData(0)</script>
        </div>
    ";
CLOSE_BOX();
echo close_body();                  
?>