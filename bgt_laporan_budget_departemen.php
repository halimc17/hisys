<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
// dz, sep 26 2011
?>
<script language=javascript1.2 src="js/bgt_laporan_budget_departemen.js"></script>
<?
include('master_mainMenu.php');
//OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanbukubesar']).'</b>');
OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET: '.$_SESSION['lang']['budgetdepartemen']).'</span>');

        $str="select distinct tahunbudget from ".$dbname.".bgt_dept
                  order by tahunbudget desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $opttahun="";
        while($bar=$res->fetch())
        {
                $opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
        }
        $str="select * from ".$dbname.".sdm_5departemen
                  order by kode";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);        
        $optdepartemen="";
        while($bar=$res->fetch())
        {
                $optdepartemen.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
        }

?>
<br><fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['budgetdepartemen']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><?php echo $_SESSION['lang']['budgetyear']?></td><td><select id='tahun' style='width:200px;' onchange="hideById('printPanel');"><?php echo $opttahun; ?></select></td></tr>
<tr><td><?php echo $_SESSION['lang']['departemen']?></td><td><select id='departemen' style='width:200px;' onchange="hideById('printPanel');"><?php echo $optdepartemen; ?></select></td></tr>
<tr><td></td><td><button class=mybutton onclick=getBudget()><?php echo $_SESSION['lang']['proses'] ?></button></td></tr>

</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX('','Result:');
//	 <img onclick=arealKePDF(event,'bgt_laporan_arealstatement_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
echo"<span id=printPanel style='display:none;'>
     <img onclick=budgetKeExcel(event,'bgt_slave_laporan_budget_departemen_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=budgetKePDF(event,'bgt_slave_laporan_budget_departemen_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>    
	 <div id=container style='width:100%;height:359px;overflow:scroll;'>

     </div>";
CLOSE_BOX();
close_body();
?>