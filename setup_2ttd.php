<? //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>

<script language=javascript src='js/setup_2ttd.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
// $optunit = $optmenu=$optkaryawan= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

// $arrunit=array();
// $arrunit=getOrgDetail(1);
// foreach($arrunit as $val=>$nama){
    // $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
	// $dtunit[$val]=$val;
// } 




// $str = "select * FROM ".$dbname.".auth where namauser='".$_SESSION['standard']['username']."' and status=1";
// $res = fetchdata($str);
// foreach ($res as $bar) {
   // $arrmenu[$bar['menuid']]=$bar['menuid'];
// }

// $str = "select * FROM ".$dbname.".menu where id in ('".implode("','",$arrmenu)."') and class='click' and action!='' ";
// $res = fetchdata($str);
// foreach ($res as $bar) {
    // $optmenu .= "<option value='".$bar['id']."'>".$bar['id']." - ".$bar['caption']."</option>";
// }

// $str = "select * FROM ".$dbname.".datakaryawan where lokasitugas in ('".implode("','",$dtunit)."') order by namakaryawan asc";
// $res = fetchdata($str);
// foreach ($res as $bar) {
    // $optkaryawan .= "<option value='".$bar['karyawanid']."'>".$bar['nik']." - ".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
// }

// $str = "select * FROM ".$dbname.".datakaryawan where tipekaryawan=0 order by namakaryawan asc";
// $res = fetchdata($str);
// foreach ($res as $bar) {
    // $optkaryawan .= "<option value='".$bar['karyawanid']."'>".$bar['nik']." - ".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
// }

OPEN_BOX('', '<span class=judul>' . getMenu('setup_2ttd') . '</span>');

//print_r($_SESSION['empl']['regional']);

  
    // echo "<fieldset>";
    // echo "<legend>" . $_SESSION['lang']['form'] . "</legend>";
    // echo "<table border=0 cellpadding=1 cellspacing=1>
            // <tr>
                // <td>" . $_SESSION['lang']['menu'] . "</td>
                // <td>:</td>
                // <td><select id=menuid style=\"width:125px;\">" . $optmenu . "</select><img id=menuid onclick=z.elSearch('menuid',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>&nbsp;</td>  
				
				// <td>" . $_SESSION['lang']['judul'] . "</td>
                // <td>:</td>
                // <td><input type=text id=judul size=50 class=myinputtext style=\"width:120px;\">&nbsp;</td>
				
					// <td>" . $_SESSION['lang']['jabatan'] . "</td>
                // <td>:</td>
                // <td><input type=text id=jabatan size=50 class=myinputtext style=\"width:120px;\">&nbsp;</td>
			// </tr>
            // <tr>
				// <td>" . $_SESSION['lang']['unit'] . "</td>
                // <td>:</td>
                // <td><select id=kodeunit  style=\"width:125px;\">" . $optunit . "</select>&nbsp;</td>
			
				// <td>" . $_SESSION['lang']['namakaryawan'] . "</td>
                // <td>:</td>
                // <td><select id=karyawanid  style=\"width:125px;\">" . $optkaryawan . "</select><img id=karyawanid onclick=z.elSearch('karyawanid',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>&nbsp;</td>
			
			
				
            // </tr>
            // <tr>
                // <td colspan=2></td>
                // <td>
                    // <button class=mybutton onclick=simpan()>Simpan</button>
                    // <button class=mybutton onclick=cancel()>Hapus</button>&nbsp;
                // </td>
            // </tr>

        // </table>
    // </fieldset>
    // <input type=hidden id=method value='insert'><input type=hidden id=id>";


CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo"<fieldset style=display:none>
        <legend>".$_SESSION['lang']['find']."</legend>
        <table>
            <tr>
                <td>" . $_SESSION['lang']['menu'] . "</td>
                <td>:</td>
                <td><select id=menuidsch style=\"width:125px;\">" . $optmenu . "</select><img id=menuidsch onclick=z.elSearch('menuidsch',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>&nbsp;</td>  
				
				<td>" . $_SESSION['lang']['unit'] . "</td>
                <td>:</td>
                <td><select id=kodeunitsch style=\"width:125px;\">" . $optunit . "</select>&nbsp;</td>

                <td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
            </tr>
        </table>
    </fieldset>";
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "
		<div id=container style='min-height:400px'>
			<script>loaddata(0)</script>
		</div>
	";
CLOSE_BOX();
echo close_body();
?><? //@Copy nangkoelframework
    ?>