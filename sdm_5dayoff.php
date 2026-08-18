<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_5dayoff.js'></script>

<?
$arr="##periode##pt##unit##karyawan##jumlah";

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('cuti dayoff').'</span>');
// ambil periode -10 +3
$today = getdate();
$bulan = $today['mon'];
$tahun = $today['year'];
function tanggalan($minus){
    global $bulan;
    global $tahun;
    global $optperiode;
    $bulanan = $bulan+$minus;
    $tahunan = $tahun;
    if($bulanan<1){
        $bulanan=12+$bulanan; $tahunan=$tahun-1;
    }
    if($bulanan>24){
        $bulanan=$bulanan-24; $tahunan=$tahun+2;
    }else
    if($bulanan>12){
        $bulanan=$bulanan-12; $tahunan=$tahun+1;
    }
    if(strlen($bulanan)==1)$bulanan='0'.$bulanan;
    $optperiode.="<option value='".$tahunan."-".$bulanan."'>".$tahunan."-".$bulanan."</option>";
}
for ($i = -3; $i < 18; $i++)tanggalan($i);

$sOrg="select distinct kodeorganisasi as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
    @$optOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
    @$optcrOrg.="<option value='".$rOrg['kodeorganisasi']."'>".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
echo"<fieldset>
     <legend>Cuti Dayoff</legend>
	 <table>
	  <tr>
	   <td>".$_SESSION['lang']['pt']."</td>
	   <td><select onchange=\"getunit();\" id=pt style='width:100px'><option value=''>".$optOrg."</select></td>
	 </tr>
	  <tr>
	   <td>".$_SESSION['lang']['unit']."</td>
	   <td><select onchange=\"getkar();\" id=unit style='width:100px'><option value=''></select></td>
	 </tr>
	  <tr>
	   <td>".$_SESSION['lang']['karyawan']."</td>
	   <td><select id=karyawan style='width:100px'><option value=''>".$optperiode."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['periode']."</td>
	   <td><select  id=periode style='width:100px'><option value=''>".$optperiode."</select></td>
	 </tr>	
	 <tr>
	   <td>".$_SESSION['lang']['jumlah']."</td>
	   <td><input type=text class=myinputtextnumber id=jumlah name=jumlah style=\"width:100px;\" maxlength=3 /></td>
	 </tr>
	 
	 </table>
         <input type=hidden value=insert id=method>
         <button class=mybutton onclick=savehk('sdm_slave_5dayoff','".$arr."')>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=oldtahunbudget name=oldtahunbudget />";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 cellpadding=7 width=100% border=0>
     <thead>
	  <tr align = center class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['pt']."</td>
	   <td>".$_SESSION['lang']['unit']."</td>
	   <td>".$_SESSION['lang']['karyawan']."</td>
	   <td>".$_SESSION['lang']['periode']."</td>
	   <td>".$_SESSION['lang']['jumlah']."</td>
	   <td>".$_SESSION['lang']['createby']."</td>
	   <td>".$_SESSION['lang']['createtime']."</td>
	   <td>".$_SESSION['lang']['action']."</td>
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