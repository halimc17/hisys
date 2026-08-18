<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');

echo open_body();
?>

<script language=javascript1.2 src=js/sdm_5Regionalbbm.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Plafond Regional BBM').'</span><br>');
$str="select distinct regional from ".$dbname.".regional_pt order by regional asc";
  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optRegion="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$numrows=owlBaris($res);
while($bar=$res->fetch())
{
  if($bar->regional==''){}
  else
  $optRegion.="<option value='".$bar->regional."'>".$bar->regional."</option>";
}
$strx="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode asc";
      //print_r($str2);
    $res2=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_OBJ);
    $optPriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $numrows=owlBaris($res2);
    while($bar=$res2->fetch())
    {
      if(strlen($bar->periode)<6){}
      else
      $optPriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
    }

//print_r($_SESSION['empl']['kodeorganisasi']);
echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
         <table>
         <tr>
           <td>".$_SESSION['lang']['regional']."</td>
           <td><select style=width:100px id=regional >".$optRegion."</select></td>
         </tr>
         <tr>
            <td>".$_SESSION['lang']['periode']." (Bulan-Tahun)</td>
            <td id=periodecontain><select style=width:100px id=periode onchange=\"getharga(this.value)\">".$optPriode."</select></td>
         </tr>
          <tr>
           <td>".$_SESSION['lang']['harga']."</td>
           <td><input type=text class=myinputtextnumber id=harga value=0 size=15 maxlength=15 onkeypress=\"return angka_doang(event);\"></td>
         </tr>
          <tr> <td><td>
         <input type=hidden value=insert id=proses>
         <button class=mybutton onclick=saveRegionalbbm()>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelRegionalbbm()>".$_SESSION['lang']['cancel']."</button>
         </tr>
		 </table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();
?>

<?php
echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['list']."</legend>
	 <table class=sortable cellspacing=1 border=0>
     <thead>
          <tr class=rowheader>
           <td align=center>No</td>
           <td align=center>".$_SESSION['lang']['regional']."</td>
           <td align=center>".$_SESSION['lang']['periode']."</td>
           <td align=center>".$_SESSION['lang']['harga']."</td>
           <td align=center>Action</td>
          </tr>
         </thead>
         <tbody id=container>
         <script>loadData()</script>
         </tbody>
     <tfoot>
         </tfoot>
         </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>