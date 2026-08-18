<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$str="select a.namakaryawan,a.karyawanid,b.tipe,a.subbagian from ".$dbname.".datakaryawan a
     left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id
     where lokasitugas='".$_SESSION['empl']['lokasitugas']."'
     and tipekaryawan=".$_POST['tipekaryawan']." and statuskaryawan != 'Keluar' and 
     (tanggalkeluar>='".$_POST['periode']."-01' or tanggalkeluar='0000-00-00') 
     order by namakaryawan";

$stream="<table class=sortable cellspacing=1 border=0>
	<thead>
	<tr class=rowheader>
	<td>".$_SESSION['lang']['nomor']."</td>
	<td>".$_SESSION['lang']['karyawanid']."</td>    
	<td>".$_SESSION['lang']['namakaryawan']."</td>
	<td>".$_SESSION['lang']['tipekaryawan']."</td>
	<td>".$_SESSION['lang']['subbagian']."</td>    
	<td>".$_SESSION['lang']['biayalistrik']."</td> 
	<td>".$_SESSION['lang']['biayaair']."</td>
	<td>".$_SESSION['lang']['biayaklinik']."</td>    
	<td>".$_SESSION['lang']['biayasosial']."</td>
	<td>".$_SESSION['lang']['manajemenperumahan']."</td> 
	<td>".$_SESSION['lang']['natura']."</td>     
	<td>".$_SESSION['lang']['jms']."</td>    
	<td>".$_SESSION['lang']['save']."</td>
	<td></td>     
	</tr>
	</thead>
	<tbody>";
#ambil data pada table keu_unalocated
$str1="select * from ".$dbname.".keu_byunalocated where periode='".$_POST['periode']."' 
       and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$listrik=Array();
$air=Array();
$klinik=Array();
$sosial=Array();
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($barx=$res1->fetch())
{
    $listrik[$barx->karyawanid]=$barx->listrik;
    $air[$barx->karyawanid]=$barx->air;
    $klinik[$barx->karyawanid]=$barx->klinik;
    $sosial[$barx->karyawanid]=$barx->sosial;
    $perumahan[$barx->karyawanid]=$barx->perumahan;
    $natura[$barx->karyawanid]=$barx->natura;
    $jms[$barx->karyawanid]=$barx->jms;
    $post[$barx->karyawanid]=$barx->posting;
}
$no=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()) {
	$no+=1; 
	$save="<img id=save".$no." title=\"Simpan\" class=\"dellicon\" onclick=\"simpanBy('".$no."','".$_POST['periode']."');\" src=\"images/save.png\">";
	if($no%2!=0)
		$align='left';
	else
		$align='right';
	
	if(isset($post[$bar->karyawanid]) and $post[$bar->karyawanid]==0 and ($listrik[$bar->karyawanid]>0 or $air[$bar->karyawanid]>0 or $klinik[$bar->karyawanid]>0 or $sosial[$bar->karyawanid]>0)) {
		$img="<img class=dellicon onclick=posting('".$no."','".$_POST['periode']."'); id=btn".$no." src='images/skyblue/posting.png'>";
		$style='#DEDEDE';
	} elseif(isset($post[$bar->karyawanid]) and $post[$bar->karyawanid]==1) {
		$img="<img id=btn".$no." src='images/skyblue/posted.png'>";
		$style='#DEDEDE';
		$save='';
	} else {
		$style='#FFFFFF';
		$img='';
	}
	
	if(!isset($listrik[$bar->karyawanid])) $listrik[$bar->karyawanid]=0;
	if(!isset($air[$bar->karyawanid])) $air[$bar->karyawanid]=0;
	if(!isset($klinik[$bar->karyawanid])) $klinik[$bar->karyawanid]=0;
	if(!isset($sosial[$bar->karyawanid])) $sosial[$bar->karyawanid]=0;
	if(!isset($perumahan[$bar->karyawanid])) $perumahan[$bar->karyawanid]=0;
	if(!isset($natura[$bar->karyawanid])) $natura[$bar->karyawanid]=0;
	if(!isset($jms[$bar->karyawanid])) $jms[$bar->karyawanid]=0;
    
	$stream.="<tr class=rowcontent>
			<td>".$no."</td>
			<td id=karid".$no.">".$bar->karyawanid."</td>
			<td id=namakaryawan".$no.">".$bar->namakaryawan."</td>    
			<td>".$bar->tipe."</td>
			<td id=subbagian".$no.">".$bar->subbagian."</td>    
			<td><input type=text id=bylistrik".$no." style='background-color:".$style."' value='".$listrik[$bar->karyawanid]."' class=myinputtextnumber  size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td> 
			<td><input type=text id=byair".$no." style='background-color:".$style."'  value='".$air[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>
			<td><input type=text id=byklinik".$no." style='background-color:".$style."'  value='".$klinik[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>   
			<td><input type=text id=bysosial".$no." style='background-color:".$style."'  value='".$sosial[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>
			<td><input type=text id=perumahan".$no." style='background-color:".$style."'  value='".$perumahan[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>
			<td><input type=text id=natura".$no." disabled style='background-color:".$style."'  value='".$natura[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>
			<td><input type=text id=jms".$no." style='background-color:".$style."'  value='".$jms[$bar->karyawanid]."' class=myinputtextnumber   size=10 maxlength=8 onkeypress=\"return angka_doang(event);\" onchange=\"this.style.backgroundColor='orange';\"></td>    
			<td align=".$align.">
				".$save."
			</td>
			<td align=center  id=cell".$no.">".$img."</td>
		</tr>";
}

$stream.="</tbody>
	<tfoot></tfoot> 
	</table>";
echo $stream;
?>