<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_5jumlahfisik.js?v=<? echo time(); ?>'></script>
<?php
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('keu_5jumlahfisik').'</span><br>');
echo"<fieldset>
    <legend>".$_SESSION['lang']['entryForm']."</legend> 
            <table border=0 cellpadding=1 cellspacing=1>
                    <tr>
                        <td>Jumlah Fisik</td>
                        <td>:</td>
                        <td>
                            <input type=text class=myinputtextnumber id=jumlah_fisik onkeypress=\"return angka_doang(event)\" 
                            onkeyup=\"z.numberFormat('jumlah_fisik',2)\" style=\"width:145px;\" value='0.00'>
                        </td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        <td><button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=hapus()>Batal</button></td>
                    </tr>
            </table></fieldset>
			<input type=hidden id=method value='insert'>
            <input type=hidden id='jumlahfisik_old' value=''>";
CLOSE_BOX();
OPEN_BOX();
#ISI UNTUK DAFTAR 
echo "<fieldset >
		<legend>".$_SESSION['lang']['list']."</legend>
        <div id=container style='overflow:auto;height:350px;max-width:auto';> 
            <script>loadData()</script>
        </div>
	 </fieldset>";
CLOSE_BOX();
echo close_body();					
?>