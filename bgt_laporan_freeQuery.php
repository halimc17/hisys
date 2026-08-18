<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/bgt_freeQuery.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['budget'].' FREE QUERY').'</span>');
$optOrg="";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
       where length(kodeorganisasi)=4 and tipe='KEBUN' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

//ambil tanub budget
$str="select distinct(tahunbudget) as tahunbudget  
      from ".$dbname.".bgt_budget  order by tahunbudget";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optthn.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}

//ambil kegiatan
$str="select kodekegiatan,namakegiatan,kelompok  
      from ".$dbname.".setup_kegiatan where
      kelompok in('TB','BBT','TBM','TM','PNN')
      order by kelompok asc,namakegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optkeg.="<option value='".$bar->kodekegiatan."'>".$bar->kelompok." - ".$bar->namakegiatan."</option>";
}



echo"<fieldset style='width:500px;'><legend>".$_SESSION['lang']['form']."</legend>";
echo"<table>
     <tr>
          <td>".$_SESSION['lang']['budgetyear']."</td>
          <td><select id='thnbudget'>".$optthn."</select></td>    
     </tr>
     <tr>
          <td>".$_SESSION['lang']['kodeorg']."</td>
          <td><select id='kodeorg'>".$optOrg."</select></td>    
     </tr>
     <tr>
          <td>".$_SESSION['lang']['kegiatan']."</td>
          <td><select id='kegiatan'>".$optkeg."</select></td>    
     </tr>     
</table>
<button class=mybutton onclick=getFreeQuery()>".$_SESSION['lang']['lihat']."</button>";
echo"</fieldset><br>
<fieldset><legend>".$_SESSION['lang']['list']."</legend>
  <div id=container  style='width:1000px,overflow:scroll'></div>  
</fieldset>    
";


CLOSE_BOX();
echo close_body();
?>