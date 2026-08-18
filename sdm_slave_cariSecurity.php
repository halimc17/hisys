<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$txtnama = checkPostGet('txtnama','');

$jab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$gol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');

$str1="select induk,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by kodeorganisasi";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$optorg='<option value=*></option>';
while($bar1=$res1->fetch())
{
	$optorg.="<option value='".$bar1->induk."'>".$bar1->kodeorganisasi."</option>";
}
$str="select karyawanid,nik,kodejabatan,kodegolongan,subbagian,namakaryawan,lokasitugas from ".$dbname.".datakaryawan 
      where namakaryawan like '%".$txtnama."%'
	  and alokasi=0 and tipekaryawan<>0 
          and lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable border=0 cellspacing=1 width=100%>
    <thead>
     <tr class=header>
        <td align=center>".$_SESSION['lang']['nokaryawan']."</td>
        <td align=center>".$_SESSION['lang']['nik']."</td>
        <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
            <td align=center>".$_SESSION['lang']['lokasitugas']."</td>
            <td align=center>".$_SESSION['lang']['subbagian']."</td>
			<td align=center>".$_SESSION['lang']['jabatan']."</td>
            <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
            
            
            <td align=center>".$_SESSION['lang']['detail']."</td>
            <td align=center width=50px>".$_SESSION['lang']['rotasike']."</td>
    </tr>
    <thead>
    <tbody>
    ";
while($bar=$res->fetch())
{
      echo"<tr class=rowcontent>
            <td>".$bar->karyawanid."</td>
                <td align=center>".$bar->nik."</td>
                <td>".$bar->namakaryawan."</td>
                <td align=center>".$bar->lokasitugas."</td>
                <td align=center>".$bar->subbagian."</td>
                <td align=left>".$jab[$bar->kodejabatan]."</td>
                <td align=left>".$gol[$bar->kodegolongan]."</td>
                
                <td align=center><img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewKaryawan('".$bar->karyawanid."','".$bar->namakaryawan."',event);\"></td>
            <td><select id=tujuan".$bar->karyawanid." onchange=setKarTo('".$bar->karyawanid."')>".$optorg."</select> 
            </tr>";
}	 
echo"</tbody></table>"; 
?>