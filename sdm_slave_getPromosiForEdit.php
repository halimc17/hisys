<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid = checkPostGet('karid','');
$notransaksi = checkPostGet('notransaksi','');

$optGaji = makeOption($dbname,"sdm_ho_component","id,name");

$str="select * from ".$dbname.".sdm_riwayatjabatan where karyawanid='".$karyawanid ."' and nomorsk='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	echo"<?xml version='1.0' ?>
		<karyawan>
			<karyawanid>".($bar->karyawanid!=""?$bar->karyawanid:"*")."</karyawanid>
			<nomorsk>".($bar->nomorsk!=""?$bar->nomorsk:"*")."</nomorsk>
			<tanggalsk>".($bar->tanggalsk!=""?tanggalnormal($bar->tanggalsk):"*")."</tanggalsk>
			<tanggalpengajuan>".($bar->tanggalpengajuan!=""?tanggalnormal($bar->tanggalpengajuan):"*")."</tanggalpengajuan>
			<mulaiberlaku>".($bar->mulaiberlaku!=""?tanggalnormal($bar->mulaiberlaku):"*")."</mulaiberlaku>
			<darikodeorg>".($bar->darikodeorg!=""?$bar->darikodeorg:"*")."</darikodeorg>
			<darisubbagian>".($bar->darisubbagian!=""?$bar->darisubbagian:"*")."</darisubbagian>
			<darikodejabatan>".($bar->darikodejabatan!=""?$bar->darikodejabatan:"*")."</darikodejabatan>
			<daritipe>".($bar->daritipe!=""?$bar->daritipe:"*")."</daritipe>
			<tipesk>".($bar->tipesk!=""?$bar->tipesk:"*")."</tipesk>
			<darikodegolongan>".($bar->darikodegolongan!=""?$bar->darikodegolongan:"*")."</darikodegolongan>
			<bagian>".($bar->bagian!=""?$bar->bagian:"*")."</bagian>     
            <kebagian>".($bar->kebagian!=""?$bar->kebagian:"*")."</kebagian>                             
			<kekodeorg>".($bar->kekodeorg!=""?$bar->kekodeorg:"*")."</kekodeorg>              
			<kesubbagian>".($bar->kesubbagian!=""?$bar->kesubbagian:"*")."</kesubbagian>
			<kekodejabatan>".($bar->kekodejabatan!=""?$bar->kekodejabatan:"*")."</kekodejabatan>
			<ketipekaryawan>".($bar->ketipekaryawan!=""?$bar->ketipekaryawan:"*")."</ketipekaryawan>
			<kekodegolongan>".($bar->kekodegolongan!=""?$bar->kekodegolongan:"*")."</kekodegolongan>
			<ttd1>".($bar->ttd1!=""?$bar->ttd1:"*")."</ttd1>
			<ttd2>".($bar->ttd2!=""?$bar->ttd2:"*")."</ttd2>
			<ttd3>".($bar->ttd3!=""?$bar->ttd3:"*")."</ttd3>
			<ttd4>".($bar->ttd4!=""?$bar->ttd4:"*")."</ttd4>
			<ttd5>".($bar->ttd5!=""?$bar->ttd5:"*")."</ttd5>
			<namadireksi>".($bar->namadireksi!=""?$bar->namadireksi:"*")."</namadireksi>			
			<tembusan1>".($bar->tembusan1!=""?$bar->tembusan1:"*")."</tembusan1>
			<tembusan2>".($bar->tembusan2!=""?$bar->tembusan2:"*")."</tembusan2>
			<tembusan3>".($bar->tembusan3!=""?$bar->tembusan3:"*")."</tembusan3>
			<tembusan4>".($bar->tembusan4!=""?$bar->tembusan4:"*")."</tembusan4>
			<tembusan5>".($bar->tembusan5!=""?$bar->tembusan5:"*")."</tembusan5>
			<updatetime>".($bar->updatetime!=""?$bar->updatetime:"*")."</updatetime>
			<updateby>".($bar->updateby!=""?$bar->updateby:"*")."</updateby>
		    <paragraf1>".($bar->pg1!=""?$bar->pg1:"*")."</paragraf1>
            <paragraf2>".($bar->pg2!=""?$bar->pg2:"*")."</paragraf2>    
            <paragraf3>".($bar->pg3!=""?$bar->pg3:"*")."</paragraf3>    
            <paragraf4>".($bar->pg4!=""?$bar->pg4:"*")."</paragraf4>    
            <paragraf5>".($bar->pg5!=""?$bar->pg5:"*")."</paragraf5>    
            <paragraf6>".($bar->pg6!=""?$bar->pg6:"*")."</paragraf6>    
            <menimbang>".($bar->menimbang!=""?$bar->menimbang:"*")."</menimbang>    
            <mengingat>".($bar->mengingat!=""?$bar->mengingat:"*")."</mengingat>    
            <namajabatan>".($bar->namajabatan!=""?$bar->namajabatan:"*")."</namajabatan>";

            $str="select * from ".$dbname.".sdm_riwayatjabatan_gaji where karyawanid='".$karyawanid ."' and nomorsk='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$jabx=' ';
			$jab=' ';
			$gabx=' ';
			$gab=' ';
			while($bar=$res->fetch()){
				if($bar->status == 'O'){
					if($jabx==''){
						$jabx=$bar->idkomponen;
					}else{
						$jabx.='###'.$bar->idkomponen;
					}
					$jab.="###tr hidden ***";
					$jab.="###td hidden id='oldid_".$bar->idkomponen."'***".$bar->idkomponen."###/td***###td id='oldkomponen_".$bar->idkomponen."'***".$optGaji[$bar->idkomponen]."###/td***###td*** : ###/td***
						###td***###input id=oldkomponenjml_".$bar->idkomponen." type=text class=myinputtextnumber  value='".number_format($bar->rupiah,2)."' size=15 maxlength=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this) *** ###/td***";
					$jab.="###/tr***";
				}else{
					if($gabx==''){
						$gabx=$bar->idkomponen;
						$gab.="###tr hidden id='trid_".$bar->idkomponen."'***";
						$gab.="###td hidden id='newid_".$bar->idkomponen."'***".$bar->idkomponen."###/td***###td id='newkomponen_".$bar->idkomponen."'***".$optGaji[$bar->idkomponen]."###/td***###td*** : ###/td***
							###td id='newkomponenjml_".$bar->idkomponen."'***".number_format($bar->rupiah,2)."  ###/td***###td***###img src=images/minus.gif class=resicon  title='Del' onclick=hapusKomponen('".$bar->idkomponen."')***###/td***";
						$gab.="###/tr***";
					}else{
						$gabx.='###'.$bar->idkomponen;
						$gab.="###tr hidden id='trid_".$bar->idkomponen."'***";
						$gab.="###td hidden id='newid_".$bar->idkomponen."'***".$bar->idkomponen."###/td***###td id='newkomponen_".$bar->idkomponen."'***".$optGaji[$bar->idkomponen]."###/td***###td*** : ###/td***
							###td id='newkomponenjml_".$bar->idkomponen."'***".number_format($bar->rupiah,2)." ###/td***###td***###img src=images/minus.gif class=resicon  title='Del' onclick=hapusKomponen('ZZZ".$bar->idkomponen."')***###/td***";
						$gab.="###/tr***";
					}
				}
			}
			
			echo "<olddataid>".$jabx."</olddataid>";
			echo "<olddata>".$jab."</olddata>";
			echo "<newdataid>".$gabx."</newdataid>";
			echo "<newdata>".$gab."</newdata>";
			echo "<tanggungjawab>".($bar->tanggungjawab!=""?$bar->tanggungjawab:"*")."</tanggungjawab>";

		echo "</karyawan>";	
		
}
?>