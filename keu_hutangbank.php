<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zMysql.php');
echo open_body();
?>
<script language='JavaScript1.2' src='js/keu_hutangbank.js'></script>
<?
include('master_mainMenu.php');
include('lib/zLib.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['data'].' '.$_SESSION['lang']['hutangbank']).'</span><br>');


//Kode Org
// $where = "`tipe`='HOLDING' and length(kodeorganisasi)=3";

// $str2=$owlPDO->query("select induk,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=3 order by namaorganisasi");

// $str2->setFetchMode(PDO::FETCH_OBJ);
// $optOrg='';
// while($bar=$str2->fetch()){
//   $optOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//    makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',$where);
//     // $optkeg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
// }
$str2=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi  where tipe = 'HOLDING' and length(kodeorganisasi)=4 order by namaorganisasi");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optOrg='';
while($bar=$str2->fetch()){
    $optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

### Get Value Enum Jenis
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>"; //pilih data
$arrjenis=getEnum($dbname,'keu_hutangbank','jenis');
foreach($arrjenis as $kei=>$val)
{
            switch($kei){
              case'LEASING':
                  $val='Leasing';
                  break;
              case'NONLEASING':
                  $val='Hutang Bank';
                  break;
              }
          
	$optjenis.="<option value='".$kei."'>".ucfirst(strtoupper($val))."</option>";
}

### Get Value No AKun
$str4=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where noakun like '2150%'
      order by namaakun");
$str4->setFetchMode(PDO::FETCH_OBJ);
$optakun='';
while($bar=$str4->fetch()){
    $optakun.="<option value='".$bar->noakun."'>".$bar->namaakun."</option>";
}

 echo"<fieldset style=float:left>
      <legend>".$_SESSION['lang']['form']."</legend>
	  <table>
	 

	  <tr>
	      <td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=notrans disabled></td>
	      <td><input type=hidden  id=notrans nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" disabled></td>
	  </tr>

	  <tr>
	        <td>".$_SESSION['lang']['kodeorg']."</td>
	        <td>:</td>
	        <td><select style=width:200px id=kodeorg>".$optOrg."</select></td>
    	</tr>

	   <tr>
            <td>".$_SESSION['lang']['noakun']."</td> 
            <td>:</td>
            <td><select id=noakun style=\"width:200px;\">".$optakun."</select></td>
		</tr>

		<tr>
            <td>".$_SESSION['lang']['jenis']."</td> 
            <td>:</td>
            <td><select onchange = pilihjenis() id=jenis style=\"width:200px;\">".$optjenis."</select></td>
		</tr>

	  <tr>
		  <td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['hutang']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=namahutang onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45></td>
	  </tr>

	  <tr>
		  <td>".$_SESSION['lang']['nilai']." ".$_SESSION['lang']['pokok']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=nilaipokok onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45></td>
	  </tr>

	  <tr>
		  <td>".$_SESSION['lang']['nilai']." ".$_SESSION['lang']['bunga']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=nilaibunga onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45></td>
	  </tr>

	  <tr>
		  <td>".$_SESSION['lang']['jumlahbulan']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=jumlahbulan onkeypress=\return tanpa_kutip(event);\" size=20 maxlength=45></td>
	  </tr>

	  <tr>
		 <td>".$_SESSION['lang']['tanggalmulai']."</td><td>:</td><td><input style=\"width:200px;\" type='text' class='myinputtext' id='tglmulai' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:200px; /></td>
	  </tr>

	  <tr>
		  <td>".$_SESSION['lang']['tanggalselesai']."</td><td>:</td><td><input style=\"width:200px;\" type='text' class='myinputtext' id='tglselesai' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:200px; /></td>
	  </tr>

	  
	  <tr><td><td><td>
	  <input type=hidden id=methodHutang value=insert>
	<button class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>Reset</button>	  
	  </td></td></td></tr></table></fieldset>";
?>
<?
CLOSE_BOX();
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <div id=container> 
            <script>loadData(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>