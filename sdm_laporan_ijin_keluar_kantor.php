<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_laporan_ijin_keluar_kantor.js'></script>
<script>
    tolak="<? echo $_SESSION['lang']['ditolak'];?>";
    </script>
<?php
include('master_mainMenu.php');


//Lokasi Tugas
$optlokasitugas="";
if(trim($_SESSION['org']['tipeinduk'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and length(kodeorganisasi)=4 order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
}
else if(trim($_SESSION['org']['induk']!=''))//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{
     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
	      and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
			$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	}
}

// $strApp = "select distinct(a.karyawanid) as karyawanid, b.namakaryawan from ".$dbname.".sdm_ijin a
		// , ".$dbname.".datakaryawan b where a.karyawanid = b.karyawanid and 
		// (a.persetujuan1='".$_SESSION['standard']['userid']."' or a.persetujuan4='".$_SESSION['standard']['userid']."' or hrd='".$_SESSION['standard']['userid']."')";
// $qryApp=$owlPDO->query($strApp) or die(print " Gagal: ".PDOException::getMessage());
// $qryApp->setFetchMode(PDO::FETCH_ASSOC);

//$optKary='';
$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ($_SESSION['empl']['bagian'] == 'HRA' || $_SESSION['empl']['bagian'] == 'HHRS')){
	
	$sKary="select distinct(a.karyawanid), b.namakaryawan from ".$dbname.".sdm_ijin a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where b.tipekaryawan in(0,1,7,8) and a.hrd ='".$_SESSION['standard']['userid']."' order by b.namakaryawan asc";
	$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
	while($rKary=$qKary->fetch())
	{
		$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
	}
}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA'){
	//$optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optJenis=$optKary;
	$sKary="select distinct(a.karyawanid) from ".$dbname.".sdm_ijin a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where b.tipekaryawan in(0,1,7,8) and a.hrd ='".$_SESSION['standard']['userid']."' and b.kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' order by b.namakaryawan asc";
	
	// $sKary="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in(0,1,7,8) and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."' order by namakaryawan asc";
	$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
	while($rKary=$qKary->fetch())
	{
		$optKary.="<option value='".$rKary['karyawanid']."'>".$rKary['namakaryawan']."</option>";
	}
}else{
	$optKary.="<option value='".$_SESSION['standard']['userid']."'>".$_SESSION['empl']['name']."</option>";
}





// while($resApp=$qryApp->fetch()){
	// // $optKary.="<option value='".$resApp['karyawanid']."'>".$resApp['namakaryawan']."</option>";
// }

$arrStat=array("0" => $_SESSION['lang']['diajukan'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);
$optStat="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrStat as $bar1 => $isi)
{
	$optStat.="<option value=".$bar1.">".$isi."</option>";
}

$str1=$owlPDO->query("select idjenis,jenisijin from ".$dbname.".sdm_5jenisijin 
      order by jenisijin");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optJenis="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$str1->fetch()){
    $optJenis.="<option value='".$bar->idjenis."'>".$bar->jenisijin."</option>";
}

// $optJenis='';
// $optJenis="<option value=''>".$_SESSION['lang']['all']."</option>";
// $arragama=getEnum($dbname,'sdm_ijin','jenisijin');
// foreach($arragama as $kei=>$fal)
// {
// 	if($_SESSION['language']=='ID'){
// 		$optJenis.="<option value='".$kei."'>".$fal."</option>";
// 	}else{
// 		switch($fal){
// 			case 'TERLAMBAT':
// 				$fal='Late for work';
// 				break;
// 			case 'KELUAR':
// 				$fal='Out of Office';
// 				break;         
// 			case 'PULANGAWAL':
// 				$fal='Home early';
// 				break;     
// 			case 'IJINLAIN':
// 				$fal='Other purposes';
// 				break;   
// 			case 'CUTI':
// 				$fal='Leave';
// 				break;       
// 			case 'MELAHIRKAN':
// 				$fal='Maternity';
// 				break;           
// 			default:
// 				$fal='Wedding, Circumcision or Graduation';
// 				break;                              
// 		}
// 		$optJenis.="<option value='".$kei."'>".$fal."</option>";       
// 	}                    
// }

$tglSkrg = date("d-m-Y");
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['cuti']." / ".$_SESSION['lang']['izinkntor']).'</span>');
echo"<fieldset style='width:500px;'><legend>".$_SESSION['lang']['form']."</legend>
	   <table>
		  <tr>
		      <td>".$_SESSION['lang']['periode']."</td>
			  <td>:</td>
			  <td>
					<input type=text class=myinputtext id=periodeawal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly='true' value='".$tglSkrg."' /> s/d 
					<input type=text class=myinputtext id=periodeakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly='true' value='".$tglSkrg."' />
			  </td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['jeniscuti']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=jnsCuti>".$optJenis."</select></td>
		  </tr>
		  <tr>
		      <td>".$_SESSION['lang']['namakaryawan']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=karyidCari>".$optKary."</select></td>
		  </tr>
		  <tr hidden>
		      <td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['persetujuan']." 1</td>
			  <td>:</td>
			  <td><select style=width:200px id=statpp1>".$optStat."</select></td>
		  </tr>
		  <tr hidden>
		      <td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['persetujuan']." 2</td>
			  <td>:</td>
			  <td><select style=width:200px id=statpp2>".$optStat."</select></td>
		  </tr>
		  <tr hidden>
		      <td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['hrd']."</td>
			  <td>:</td>
			  <td><select style=width:200px id=stathrd>".$optStat."</select></td>
		  </tr>
		  <tr>
		      <td colspan=2></td>
			  <td>
				<button class=mybutton onclick=\"loadData(0)\">".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=\"detailExcel(event,'sdm_slave_laporan_ijin_meninggalkan_kantor.php')\">".$_SESSION['lang']['excel']."</button>
			  </td>
		  </tr>	  
	   </table>
	 </fieldset>  
    ";

CLOSE_BOX();
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['list']).'</b>');
echo "<div style='width:100%;height:400px;overflow:auto;'>
		<table class=sortable cellspacing=1 border=0>
             <thead>
                    <tr>
                          <td align=center>No.</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>
                          <td align=center>".$_SESSION['lang']['nama']."</td>
                          <td align=center>".$_SESSION['lang']['keperluan']."</td>
                          <td align=center>".$_SESSION['lang']['jenisijin']."</td>  
                         
                          <td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
                          <td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
                          <td align=center width=70px>".$_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil']."</td>
                          <td align=center>".$_SESSION['lang']['cuti']." ".$_SESSION['lang']['sisa']."</td>
						 <td align=center>".$_SESSION['lang']['persetujuan']." 1</td>    
						   <td align=center>".$_SESSION['lang']['persetujuan']." 2</td>    
                          <td align=center>".$_SESSION['lang']['hrd']."</td> 
                          <td align=center>".$_SESSION['lang']['print']."</td>    
                        </tr>  
                 </thead>
                 <tbody id=container><script>loadData()</script>
                 </tbody>

           </table>
     </div>";
CLOSE_BOX();
close_body();
?>