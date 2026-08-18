<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_2taxplan.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Tax Planning').'</span><br>');

$opt_pt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opt_unit="<option value=''>".$_SESSION['lang']['all']."</option>";
$s_pt="select * from ".$dbname.".organisasi where tipe='PT' order by kodeorganisasi asc";
$res=$owlPDO->query($s_pt) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($r_pt=$res->fetch())
{
     if($r_pt['kodeorganisasi']==$_SESSION['empl']['kodeorganisasi']){
        $opt_pt.="<option value='".$r_pt['kodeorganisasi']."'>".$r_pt['namaorganisasi']."</option>";
     }
}

echo"<fieldset  style=float:left>
    <legend>Form</legend>
    ".$_SESSION['lang']['pt']." : <select id='pt' style=width:200px; onchange=load_unit()>$opt_pt</select>
    <select id='unit' style=width:200px; >$opt_unit</select>
    ".$_SESSION['lang']['tgldari']." : <input type=\"text\" class=\"myinputtext\" id=\"tanggaldari\" name=\"tanggaldari\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:75px;\" />
    ".$_SESSION['lang']['tglsmp']." : <input type=\"text\" class=\"myinputtext\" id=\"tanggalsampai\" name=\"tanggalsampai\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:75px;\" />
    <button class=mybutton onclick=getTax()>".$_SESSION['lang']['proses']."</button>
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');

echo"<fieldset>
	 <legend>Result :</legend>
	 <span id=printPanel style='display:none;'>
        <img onclick=taxKeExcel(event,'keu_slave_2taxplan.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
        <!--<img onclick=prestasiKePDF(event,'it_slave_2prestasi.php') title='PDF' class=resicon src=images/pdf.jpg>-->
    </span>    
    <div id=container style='width:100%;height:430px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>