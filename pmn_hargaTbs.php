<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/pmn_hargaTbs.js'></script>
<?
//$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";

$arr="##pabrik##tanggal##supplier##hargab##hargas##hargak##proses";
include('master_mainMenu.php');
OPEN_BOX();
$optpabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optsupplier=$optpabrik;

$spabrik="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
    where `tipe` = 'PABRIK' order by kodeorganisasi";
$qpabrik=$owlPDO->query($spabrik) or die(print " Gagal: ".PDOException::getMessage());
$qpabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rpabrik=$qpabrik->fetch())
{
    $optpabrik.="<option value='".$rpabrik['kodeorganisasi']."'>".$rpabrik['namaorganisasi']."</option>";
}

$arrukuran=getEnum($dbname,'pabrik_timbangan','kriteriabuah');
foreach($arrukuran as $kei=>$fal)
{
    switch($kei)
    {
                                case 'S':
                                         $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
                                break;
                                case 'M':                            
                                        $_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
                                break;
                                case 'L':
                                        $_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
                                break;		
    }
    $optukuran.="<option value='".$kei."'>".$fal."</option>";
} 

$ssupplier="select distinct kodetimbangan,namasupplier from ".$dbname.".log_5supplier 
    where kodetimbangan IS NOT NULL and kodetimbangan like '1%' order by namasupplier";
$qsupplier=$owlPDO->query($ssupplier) or die(print " Gagal: ".PDOException::getMessage());
$qsupplier->setFetchMode(PDO::FETCH_ASSOC);
while($rsupplier=$qsupplier->fetch())
{
    $optsupplier.="<option value='".$rsupplier['kodetimbangan']."'>".$rsupplier['namasupplier']." [".$rsupplier['kodetimbangan']."]</option>";
}

echo"<fieldset style=width:350px>
     <legend>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['tbs']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['pabrik']."</td>
	   <td><select id=pabrik style=\"width:150px;\">".$optpabrik."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['supplier']."</td>
	   <td><select id=supplier style=\"width:150px;\">".$optsupplier."</select></td>
	 </tr>	
          <tr>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['besar'].")</td>
	   <td><input type=text class=myinputtextnumber id=hargab onchange=\"inputharga();\" onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	 </tr>	
          <tr>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['sedang'].")</td>
	   <td><input type=text class=myinputtextnumber id=hargas onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	 </tr>	
          <tr>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['kecil'].")</td>
	   <td><input type=text class=myinputtextnumber id=hargak onkeypress=\"return angka_doang(event);\" style=\"width:150px;\"  /> </td>
	 </tr>	
	 </table>
	 <input type=hidden value=insert id=proses>
	 <button class=mybutton onclick=saveFranco('pmn_slave_hargaTbs','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();

echo"<fieldset  style=width:650px><legend>".$_SESSION['lang']['list']."</legend>";
echo"<table cellpadding=1 cellspacing=1 border=0><tr><td>".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10  />";
echo"&nbsp;".$_SESSION['lang']['supplier']." : <select id=carisupplier style=\"width:150px;\">".$optsupplier."</select>";
echo"&nbsp;".$_SESSION['lang']['pabrik']." : <select id=caripabrik style=\"width:150px;\">".$optpabrik."</select><button class=mybutton onclick=cariTransaksi()>".$_SESSION['lang']['find']."</button></td></tr></table>";
echo"
    <table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['pabrik']."</td>
	   <td>".$_SESSION['lang']['supplier']."</td>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['besar'].")</td>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['sedang'].")</td>
	   <td>".$_SESSION['lang']['harga']."/kg (".$_SESSION['lang']['kecil'].")</td>
	   <td>Action</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";

echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>