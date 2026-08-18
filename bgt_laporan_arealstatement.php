<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
// dz, sep 26 2011
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src="js/bgt_laporan_arealstatement.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
//OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanbukubesar']).'</b>');


        $str="select distinct tahunbudget from ".$dbname.".bgt_blok
                  order by tahunbudget desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        $opttahun="";
        while($bar=$res->fetch())
        {
                $opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";

        }


//if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
//{   
        //=================ambil KEBUN;  
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
              where tipe='KEBUN'
                  order by namaorganisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        $optkebun="";
        while($bar=$res->fetch())
        {
                $optkebun.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";

        }
		
		$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach(getOrgDetail(23) as $key => $val){
			$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optkebun.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optkebun.="<option value=".$key.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optkebun.="</optgroup>";
			}
		}
//}
//else
//{
//        $optkebun="";
//         $optkebun.="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";
//   
//}
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_arealstatement').'</span><br>');
?>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td>:</td><td><select class='select2' id=tahun style='width:200px;' onchange=hideById('printPanel')><?php echo $opttahun; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kodeorganisasi']?></label></td><td>:</td><td><select class='select2' id=kebun style='width:200px;' onchange=hideById('printPanel')><?php echo $optkebun; ?></select></td></tr>
<tr><td colspan=2></td><td colspan=3><button class=mybutton onclick=getAreal()><?php echo $_SESSION['lang']['preview'] ?></button></td></tr>

<!--<tr height="20"><td colspan="2">&nbsp;</td></tr>-->

<!--<tr><td colspan="2"><button onclick="zPreview('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>-->

</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX();
echo"<span id=printPanel style='display:none;'>
     <!--<img onclick=arealKeExcel(event,'bgt_slave_laporan_arealstatement_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
     <img onclick=arealKePDF(event,'bgt_slave_laporan_arealstatement_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>-->
	 <div id=container style='width:100%;height:359px;overflow:auto;'>

     </div>";
CLOSE_BOX();
close_body();
?>