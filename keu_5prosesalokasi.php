<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/keu_5prosesalokasi.js?v=<?php echo time(); ?>'></script>


<?php

// $optunit="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optunit="";
$sql = "SELECT kodeorganisasi,namaorganisasi,induk FROM " . $dbname . ".organisasi where tipe='PT' ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

// $str="select * from ".$dbname.".organisasi where kodeorganisasi in (select induk from organisasi where tipe = 'KEBUN')";
// $res=fetchdata($str);
// $optLp="";
// $optLp="<option value='ALOKASI BIAYA UMUM'>ALOKASI BIAYA UMUM</option>";
// $optLp="<option value='ALOKASI PENYUSUTAN'>ALOKASI PENYUSUTAN</option>";
// $optLp="<option value='ALOKASI BEBAN BUNGA'>ALOKASI BEBAN BUNGA</option>";
// foreach ($res as $key => $val) {
// 	$optpt.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
// }

OPEN_BOX('','<span class=judul>'.getMenu('keu_5prosesalokasi').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
             <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
               <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
             <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
               <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
             <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
        echo "<table border=0><tr><td>".$_SESSION['lang']['namalaporan']."</td><td>:</td><td><input type=text id='namalaporan' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>";
        echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
        echo"</table>";
        echo"</fieldset></td>
     </tr>
     </table>";

CLOSE_BOX();
?>
<!--Form Add Data-->


<div id="header" style='display:none'>
    <?php OPEN_BOX(); ?>
    <fieldset style="width:1100px;float:left">
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table border="0" cellspacing="0">
            <tr>
                <td><?php echo $_SESSION['lang']['ho']?></td>
                <td>:</td>
                <td><select id="kodeorg" style='width:250px;'><?php echo $optunit; ?></select> </td>
				
				 <td><?php echo $_SESSION['lang']['namalaporan']?></td>
                <td>:</td>
                <td><input type="text" id="nmLaporan" class="myinputtext" style='width:245px;' /></td>
				
				 <td><?php echo $_SESSION['lang']['keterangan']?></td>
                <td>:</td>
                <td><input type="text" id="ketDt1" class="myinputtext" style='width:245px;' /></td>
            </tr>
         
            <!-- <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 2"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt2" class="myinputtext" style='width:250px;' /></td>
            </tr>   
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 3"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt3" class="myinputtext" style='width:250px;' /></td>
            </tr> 
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 4"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt4" class="myinputtext" style='width:250px;' /></td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 5"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt5" class="myinputtext" style='width:250px;' /></td>
            </tr>  
            <tr>
                <td><?php echo $_SESSION['lang']['keterangan']." 6"?></td>
                <td>:</td>
                <td><input type="text" id="ketDt6" class="myinputtext" style='width:250px;' /></td>
            </tr>   -->
            <tr>
                <td><td><td>
                <input type="hidden" id="method" value="insertht" />
                <button class=mybutton id="dtl_ajuan" onclick="saveData()"><?php echo $_SESSION['lang']['save']?></button>
                </td>
            </tr>
        </table>
    </fieldset>
	<?
		/*
		$tab.="<fieldset style=float:left><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']."</legend>";
        $tab.="<li>Tipe :</li>";
        $tab.="<ol>Header : Untuk <b>Header</b> berupa judul</ol>";
        $tab.="<ol>Detail : Untuk <b>Detail</b> berupa rincian dari header, dan di-isikan isi dari nomor akun pendukungnya dengan menekan tombol <img src='images/skyblue/zoom.png' class='resicon'></ol>";
        $tab.="<ol>Total : Untuk <b>Total</b> berupa total dari detail</ol>";
		$tab.="<li>No Urut : Urutan didalam laporan, harap membuat spare nourut dengan total, agar jika ada penambahan dapat ditempatkan ber-urutan</li>";
		$tab.="<li>Keterangan : Nama laporan</li>";
		$tab.="<li>Total : Jika tipe-nya adalah <b>total</b>, maka kolom ini berisikan <b>No. Urut</b> yang akan dijumlahkan, dan diberi tanda pemisah berupa <b>, (koma)</b></li>";
		// $tab.="<li>Colspan : Berupa merge kolom, untuk membentuk laporan sub-total dengan grand-total, sub-total isikan 2, grand total isikan 0</li>";
        
		$tab.="</fieldset>";
		*/
		// $tab.="<div style=clear:both></div>";
		// $tab.="<hr>";
		// echo $tab;
	?>
   
<div id="detail" style='display:none'>

</div>
 <?php  CLOSE_BOX(); ?>
</div>






<div id=listdata style='display:block'>
<?
OPEN_BOX();
echo"<fieldset>
        <legend><b>".$_SESSION['lang']['list']."</legend>
            <table class=sortable cellspacing=1 cellspacing=1 border=0 width=100%>
            <thead>
            <tr class=rowheader>    
                <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
                <td align=center>" . $_SESSION['lang']['namalaporan'] . "</td>
                <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                <td align=center>" . $_SESSION['lang']['action'] . "</td>
            </thead>
            <tbody  id=contain>";

    echo"<tfoot id='footData'>
          </tfoot></tbody></table></fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?><script type="text/javascript">loadData(0);</script>