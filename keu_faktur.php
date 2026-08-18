<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_faktur.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zMaster.js"></script>

<?php

$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPt="select * from ".$dbname.".organisasi where tipe='PT' ";
$nPt=$owlPDO->query($iPt) or die(print " Gagal: ".PDOException::getMessage());
$nPt->setFetchMode(PDO::FETCH_ASSOC);
while($dPt=  $nPt->fetch())
{
    @$optPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
    @$optCariPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('keu_faktur').'</span></br>');
echo"<fieldset>";
    echo"<legend>Form</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr hidden>
                    <td hidden>".$_SESSION['lang']['id']." Bayar</td> 
                    <td hidden>:</td>
                    <td hidden><input type=text maxlength=5 id=id nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=pt onchange=getnpwp() style=\"width:204px;\">".$optPt."</select>
						<input id=ptlama style='display:none'></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['npwp']."</td>
                    <td>:</td>
                    <td><select id=npwp style=\"width:204px;\">".$optnpwp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nofakturawal']."</td> 
                    <td>:</td>
                    <td><input type=faktur onblur=getpakhir() id=fakturawal nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength='15' value='002-19.' placeholder='xxx-xx.xxxxxxxx'>
						<input id=fakturawallama style='display:none'></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['nofakturakhir']."</td> 
                    <td>:</td>
                    <td><input type=faktur onblur=getjumlah() id=fakturakhir nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength='15' value='002-19.' placeholder='xxx-xx.xxxxxxxx'>
					<input id=fakturakhirlama style='display:none'></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['jumlah']."</td> 
                    <td>:</td>
                    <td><input type=jumlah onkeyup=getpakhir() id=jumlah nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber style=\"width:75px;\" maxlength='15'></td>
                </tr>
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>";

CLOSE_BOX();

OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <div>
                        <table>
                                <tr>
                                        <td>".$_SESSION['lang']['perusahaan']."</td>
                                        <td><select id=cariPt>".$optCariPt."</select></td>
                                        <td>".$_SESSION['lang']['status']."</td>
                                        <td><select id=cariStatus >
										<option value=''>".$_SESSION['lang']['all']."</option>
										<option value='1'>".$_SESSION['lang']['aktif']."</option>
										<option value='0'>".$_SESSION['lang']['tidakaktif']."</option>
								</select>
									</td>
									<td><button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button>
										<button class=mybutton onclick=resetcari()>".$_SESSION['lang']['cancel']."</button>
									</td>
                                </tr>
                        </table>
                </div>
                <div id=container> 
                        <script>loadData()</script>
                </div>
        </fieldset>";
CLOSE_BOX();
echo close_body();					
?>