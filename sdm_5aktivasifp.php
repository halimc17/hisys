<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript1.2 src='js/sdm_5aktivasifp.js?ver=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5aktivasifp').'</span><br>');

$arrUnit = getOrgDetail(2);


$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)=4 and kodeorganisasi in ($arrUnit) order by induk";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$key = $bar['kodeorganisasi'];
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optorg.="<optgroup label='".getNamaOrg($d)."'>";
	}
    $optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){
		$optorg.="</optgroup>";
	}
}


$nmjenis=array('transaksi'=>'Sumber Transaksi','subbagian'=>'Subbagian Data Karyawan');
// $optjns="<option value=''>Seluruhnya</option>";
$arrdata=getEnum($dbname,'sdm_5aktivasifp','tipevalidasi');
foreach($arrdata as $val){	
	$optjns.="<option value=".$val.">".$nmjenis[$val]."</option>";
}


$optdetail="<option value=''>&nbsp;</option>";
$optdetail.="<optgroup label='Sumber Transaksi'>";
$optdetail.="<option value='BKM'>BKM - Pemeliharaan</option>";
$optdetail.="<option value='PNN'>BKM - Panen</option>";
$optdetail.="<option value='TRK'>TRK - Pekerjaan</option>";
$optdetail.="<option value='SDM'>SDM - Absensi</option>";
$optdetail.="</optgroup>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe not like '%GUDANGTEMP%' order by induk";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$key = $bar['kodeorganisasi'];
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optdetail.="<optgroup label='".getNamaOrg($d)."'>";
	}
    $optdetail.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){
		$optdetail.="</optgroup>";
	}
}



echo"<fieldset style='float:left'><legend>Form</legend><table>
		<tr><td>".$_SESSION['lang']['unitkerja']."</td><td>:</td><td><select class=select2x onchange=getdetail(); id=kodeorg style=\"width:200px;\" >".$optorg."</select></td></tr>
		<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:195px;'   readonly/> 
        </td>
		</tr>
        <tr>
			<td>Jenis Validasi</td><td>:</td>
			<td><select disabled class=select2x onchange=getjenisval(); id=jenisval style=\"width:200px;\" >".$optjns."</select></td>
		</tr>
		<tr>
			<td>Detail Validasi</td><td>:</td>
			<td>
				<input type='text' class='myinputtext' id='detailval' onclick=getjenisval(); style='width:195px;' readonly/> 
			</td>
		</tr>
        <tr>
			<td>".$_SESSION['lang']['aktif']."</td><td>:</td><td><input type=checkbox id=tutup>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td></tr>
         <tr><td colspan=2></td><td>
         <input type=hidden id=method value='insert'>
         <button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelJ()>".$_SESSION['lang']['cancel']."</button>
         </td></tr></table>
		 </fieldset>";
		 echo"";
CLOSE_BOX();
OPEN_BOX();
echo "<div style=height:50vh>";
// echo open_theme($_SESSION['lang']['list']);
$nmjenis=array(''=>'Seluruhnya','transaksi'=>'Sumber Transaksi','subbagian'=>'Subbagian Data Karyawan');
$nmorg['BKM']='BKM - Pemeliharaan';
$nmorg['PNN']='PNN - Panen';
$nmorg['TRK']='TRK - Pekerjaan';
$nmorg['SDM']='SDM - Absensi';
$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)<=6 order by induk";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

        $str1="select a.*,case status when '1' then '".$_SESSION['lang']['yes']."'
                 when '0' then '".$_SESSION['lang']['no']."' end as statustampil from ".$dbname.".sdm_5aktivasifp a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where 1=1 and a.kodeorg in ($arrUnit) order by induk, kodeorg";
        echo"<table class=sortable cellspacing=1 border=0 cellpadding=5 style='width:100%;'>
             <thead>
                 <tr class=rowheader>
						<th>No</th>
						<th width=50px>".$_SESSION['lang']['kodeorg']."</th>
						<th>".$_SESSION['lang']['namaorganisasi']."</th>
                        <th>".$_SESSION['lang']['aktif']."</th>
                        <th>".$_SESSION['lang']['tanggal']."</th>
                        <th>Jenis Validasi</th>
                        <th>Detail Validasi</th>
                        <th>".$_SESSION['lang']['updateby']."</th>
                        <th>".$_SESSION['lang']['tanggalupdate']."</th>
                        <th>".$_SESSION['lang']['action']."</th>
					</tr>
                 </thead>
                 <tbody id=container>"; 
            $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar1=$res->fetch()){
				$key = $bar1->kodeorg;
				$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
				$d=$induk[$key];
				if($d!=$n){			
					echo"<tr class=rowcontent>
						<td colspan=10><b>".getNamaOrg($d)."</b></td>
					</tr>";
				}
				
				$no++;
                echo"<tr class=rowcontent style=vertical-align:top;>
					<td align=center>".$no."</td>
					<td align=center>".$bar1->kodeorg."</td>
					<td align=left>".getNamaOrg($bar1->kodeorg)."</td>
					<td align=center>".$bar1->statustampil."</td>
					<td align=center>".$bar1->tanggal."</td>
					<td align=left>".$nmjenis[$bar1->tipevalidasi]."</td>";
					
					$explx=explode(",",$bar1->detailvalidasi);
					$isidata="";
					if($bar1->detailvalidasi!=''){						
						foreach($explx as $kodex){
							$nomor++;
							$isidata.=$nomor.". ".$kodex." - ".$nmorg[$kodex]."<br>";
						}
					}else{
						$isidata="Seluruhnya";						
					}
				echo"<td align=left>".$isidata."</td>
					<td align=center>".getKary($bar1->updateby)."</td>
					<td align=center>".$bar1->lastupdate."</td>
					<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->status."','".tanggalnormal($bar1->tanggal)."','".$bar1->tipevalidasi."','".$bar1->detailvalidasi."','".getNamaOrg($bar1->kodeorg,'tipe')."');\"></td></tr>";
				$n=$d;	
			}	 
			echo"	 
                 </tbody>
                 <tfoot>
                 </tfoot>
                 </table>";
// echo close_theme();
echo "</div>";
CLOSE_BOX();
echo close_body();
?>