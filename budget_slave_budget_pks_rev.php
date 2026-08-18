<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$kodebgt=checkPostGet('kodebgt','');
$jmlh=checkPostGet('jmlh','');
$thnBudget=checkPostGet('thnBudget','');

$akun=checkPostGet('akun','');


$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmkode=makeOption($dbname,'bgt_kode','kodebudget,nama');


$cekapa=$_POST['cekapa'];
if($cekapa=='')$cekapa=$_GET['cekapa'];
if($proses=='jumlahkanhk'){
	
	$sHk="select distinct * from ".$dbname.".bgt_hk where tahunbudget='".$thnBudget."' and unit = '".substr($_SESSION['empl']['lokasitugas'],0,4)."'";
	$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
	$qHk->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$qHk->fetch()){
		$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
		$thke=$bar['harisetahun']-$thrlb;
		$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
		$tothke=$thke-($bar['jlhcuti']+$tsim);
		
		$hke=$tothke;
	}
	
	$str="select * from ".$dbname.".bgt_upah where tahunbudget = '".$thnBudget."' and kodeorg='".$_SESSION['empl']['lokasitugas']."' and golongan='".$kodebgt."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $rpperhari = $bar->jumlah;
    }
	
	$rupiah = $jmlh * $hke * $rpperhari;
	$optakun=makeOption($dbname,'bgt_kode','kodebudget,noakun');
	
	$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun[$kodebgt]."' order by a.noaruskas asc"; 
	$res=fetchdata($str);
	foreach($res as $bar){
		$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
	}
	
	
	
	echo $rupiah."###".$optaruskas;
}

if($proses=='getaruskas'){
	if($akun=='x'){		
		$optakun=makeOption($dbname,'bgt_kode','kodebudget,noakun');
		$kodebgt=$optakun[$kodebgt];
		
	}
	$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$kodebgt."' order by a.noaruskas asc"; #exit("error".$str);
	$res=fetchdata($str);
	foreach($res as $bar){
		$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
	}
	echo $optaruskas;
}



//cek mesin untuk station
if($cekapa=='station'){
    $station=$_POST['station'];
    $str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
        where induk = '".$station."'";
    $lempar="";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$lempar="<option value=''>".$_SESSION['lang']['all']."</option>";
    while($bar=$res->fetch()){
        $lempar.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
    echo $lempar;        
}

//cek mesin untuk station
if($cekapa=='updatetahuntutup'){
    $str="select distinct tahunbudget from ".$dbname.".bgt_budget
        where tutup = '0' and kodebudget != 'UMUM' and tipebudget = 'MILL' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'
        order by tahunbudget desc";
    $lempar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $lempar.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
    }
    echo $lempar;        
}

//tampilkan data tab0
if($cekapa=='tab0'){
    $tipebudget=$_POST['tipebudget'];
    $tahunbudget=$_POST['tahunbudget'];
    $mesin=$_POST['mesin'];
    $hkef='';
    $hkef.="<table id=container9 class=sortable cellspacing=1 border=0>
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['kodeorg']."</td>
            <td align=center>".$_SESSION['lang']['tipeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['aruskas']."</td>
			
            <td align=center>".$_SESSION['lang']['totalbiaya']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
       </tr>  
     </thead>
     <tbody>";
    $str="select * from ".$dbname.".bgt_budget
        where kodebudget like 'EXPL%' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg like '".$mesin."%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=1;
    while($bar=$res->fetch()){
	$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
    $hkef.="<tr class=rowcontent>
            <td align=center>".$bar->kunci."</td>
            <td align=center>".$bar->tahunbudget."</td>
            <td align=left>".$bar->kodeorg." - ".$nmorg[$bar->kodeorg]."</td>
            <td align=center>".$bar->tipebudget."</td>
            <td align=left>".$bar->kodebudget." - ".$nmkode[$bar->kodebudget]."</td>
			<td align='left'>".$bar->aruskas." - ".$nmaruskas[$bar->aruskas]."</td>
            <td align=right>".number_format($bar->rupiah)."</td>";
            if($bar->tutup==0){
				$hkef.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editsdm('".$bar->kunci."','".$bar->kodeorg."','".$bar->kodebudget."','".$bar->aruskas."','".$nmaruskas[$bar->aruskas]."','".$bar->rupiah."');\" ></td>";
				
				$hkef.="<td align=center style=width:20px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(0,".$bar->kunci.")\" title=\"Hapus\"></td>";
			}else{				
				$hkef.="<td align=center>&nbsp;</td>";
				$hkef.="<td align=center>&nbsp;</td>";
			}
       $hkef.="
       </tr>";
    $no+=1;
    }
    echo $hkef;        


    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//tampilkan data kodevhc tab3
if($cekapa=='kendaraan'){
    $tipebudget=$_POST['tipebudget'];
    $tahunbudget=$_POST['tahunbudget'];
    $mesin=$_POST['mesin'];
    $hkef='';
	
	$kodept = $_SESSION['empl']['kodeorganisasi'];
	
    #$str="select * from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi where tahunbudget = '".$tahunbudget."' and kodetraksi in (select kodeorganisasi from ".$dbname.".organisasi where alokasi='".$kodept."') "; //exit("error".$str);
	
	$str="select * from ".$dbname.".bgt_vhc_jam where tahunbudget = '".$tahunbudget."' and unitalokasi='".$_SESSION['empl']['lokasitugas']."'"; #exit("error".$str);
	
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $hkef="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar= $res->fetch()){
		$nmpol = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar->kodevhc."'");
		if($nmpol[$bar->kodevhc]!=''){			
			$hkef.="<option value='".$bar->kodevhc."'>".$bar->kodevhc." - ".$nmpol[$bar->kodevhc]."</option>";
		}else{
			$hkef.="<option value='".$bar->kodevhc."'>".$bar->kodevhc."</option>";
		}
		
    }
    echo $hkef;           
}

//delete row, all tab berdasarkan kunci
if($cekapa=='delete0'){
    $kunci=$_POST['kunci'];
    $str="delete from ".$dbname.".bgt_budget  where kunci='".$kunci."'";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    $str2="delete from ".$dbname.".bgt_distribusi  where kunci='".$kunci."'";
    try{$owlPDO->exec($str2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
}

//cek regional berdasarkan kodews(4) vs bgt_regional_assignment
if($cekapa=='regional'){
    $mesin=$_POST['mesin'];
    $kodeorg=substr($mesin,0,4);
    $str="select * from ".$dbname.".bgt_regional_assignment where kodeunit = '".$kodeorg."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $hkef='';
    while($bar= $res->fetch())
    {
        $hkef=$bar->regional;
    }
    echo $hkef;        
}

//harga barang tab1 dan tab2
if($cekapa=='barang'){
    $kodebarang1=$_POST['kodebarang1'];
    $tahunbudget=$_POST['tahunbudget'];
    $regional=$_POST['regional'];
    $str="select * from ".$dbname.".bgt_masterbarang
        where closed=1 and kodebarang = '".$kodebarang1."' and regional ='".$regional."' and tahunbudget ='".$tahunbudget."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $hkef='';
    while($bar= $res->fetch())
    {
        $hkef=$bar->hargasatuan;
    }
    echo $hkef;        
}

//tampilkan data tab1
if($cekapa=='tab1'){
    $tipebudget=$_POST['tipebudget'];
    $tahunbudget=$_POST['tahunbudget'];
    $mesin=$_POST['mesin'];
//kamus namabarang
    $strJ="select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang";
    $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
    while($barJ=$resJ->fetch())
    {
        $barang[$barJ->kodebarang]=$barJ->namabarang;
        $satuan[$barJ->kodebarang]=$barJ->satuan;
    }

    $hkef='';
    $hkef.="<table id=container8 class=sortable cellspacing=1 border=0>
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['kodeorg']."</td>
            <!--<td align=center>".$_SESSION['lang']['tipeanggaran']."</td>-->
            <td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['jenis']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['totalbiaya']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
       </tr>  
     </thead>
     <tbody>";
    $str="select * from ".$dbname.".bgt_budget where kodebudget like 'M%' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg like '".$mesin."%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=1;
    while($bar= $res->fetch()){
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
    $hkef.="<tr class=rowcontent>
            <td align=center>".$bar->kunci."</td>
            <td align=center>".$bar->tahunbudget."</td>
            <td align=left>".$bar->kodeorg." - ".$nmorg[$bar->kodeorg]."</td>
            <!--<td align=center>".$bar->tipebudget."</td>-->
            <td align=left>".$bar->kodebudget." - ".$nmkode[$bar->kodebudget]."</td>
			<td align='left'>".$bar->aruskas." - ".$nmaruskas[$bar->aruskas]."</td>
            <td align=right>".$bar->kodebarang."</td>
            <td align=left>".$barang[$bar->kodebarang]."</td>
            <td align=center>".$bar->keterangan."</td>
            <td align=right>".number_format($bar->jumlah)."</td>
            <td align=left>".$satuan[$bar->kodebarang]."</td>
            <td align=right>".number_format($bar->rupiah)."</td>";
            if($bar->tutup==0){
				$hkef.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editmat('".$bar->kunci."','".$bar->kodeorg."','".$bar->kodebudget."','".$bar->aruskas."','".$nmaruskas[$bar->aruskas]."','".$bar->noakun."','".$bar->rupiah."','".$bar->kodebarang."','".$barang[$bar->kodebarang]."','".$bar->keterangan."','".$bar->jumlah."','".$satuan[$bar->kodebarang]."');\" ></td>";
				
				$hkef.="<td align=center style=width:20px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(1,".$bar->kunci.")\" title=\"Hapus\">
                </td>";
			}else{
				$hkef.="<td align=center>&nbsp;</td>";
				$hkef.="<td align=center>&nbsp;</td>";
			}
			
       $hkef.="
       </tr>";
    $no+=1;
    }
    echo $hkef;        

    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//tampilkan data tab2
if($cekapa=='tab2'){
    $tipebudget=$_POST['tipebudget'];
    $tahunbudget=$_POST['tahunbudget'];
    $mesin=$_POST['mesin'];
    $hkef='';
    $hkef.="<table id=container7 class=sortable cellspacing=1 border=0 >
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['kodeorg']."</td>
            <!--<td align=center>".$_SESSION['lang']['tipeanggaran']."</td>-->
            <td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
			<td align=center>".$_SESSION['lang']['aruskas']."</td>
            <td align=center>".$_SESSION['lang']['totalbiaya']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
       </tr>  
     </thead>
     <tbody>";
    $str="select * from ".$dbname.".bgt_budget where kodebudget in ('PKSM','SERVICE') and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg like '".$mesin."%' order by kodebudget";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=1;
    while($bar= $res->fetch())
    {
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
    $hkef.="<tr class=rowcontent>
            <td align=center>".$bar->kunci."</td>
            <td align=center>".$bar->tahunbudget."</td>
            <td align=left>".$bar->kodeorg." - ".$nmorg[$bar->kodeorg]."</td>
            <!--<td align=center>".$bar->tipebudget."</td>-->
            <td align=left>".$bar->kodebudget." - ".$nmkode[$bar->kodebudget]."</td>
			<td align='left'>".$bar->aruskas." - ".$nmaruskas[$bar->aruskas]."</td>
            <td align=right>".number_format($bar->rupiah)."</td>";
            if($bar->tutup==0){
				
				$hkef.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editmain('".$bar->kunci."','".$bar->kodeorg."','".$bar->kodebudget."','".$bar->aruskas."','".$nmaruskas[$bar->aruskas]."','".$bar->jumlah."','".$bar->rupiah."');\" ></td>";
			
				$hkef.="<td align=center style=width:20px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(2,".$bar->kunci.")\" title=\"Hapus\"></td>";
			}else{				
				$hkef.="<td align=center>&nbsp;</td>";
				$hkef.="<td align=center>&nbsp;</td>";
			}
       $hkef.="
       </tr>";
    $no+=1;
    }
    echo $hkef;        


    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//harga barang tab1 dan tab2
if($cekapa=='vhc'){
    $kodevhc3=$_POST['kodevhc3'];
    $tahunbudget=$_POST['tahunbudget'];
//    $regional=$_POST['regional'];
    $str="select * from ".$dbname.".bgt_biaya_ken_per_jam
        where kodevhc = '".$kodevhc3."' and tahunbudget ='".$tahunbudget."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $hkef='0';
    while($bar= $res->fetch())
    {
        $hkef=$bar->rpperjam;
    }
    echo $hkef;        
}

//tampilkan data tab3
if($cekapa=='tab3'){
    $tipebudget=$_POST['tipebudget'];
    $tahunbudget=$_POST['tahunbudget'];
    $mesin=$_POST['mesin'];
//kamus namaakun
    $strJ="select * from ".$dbname.".keu_5akun where tipeakun='Biaya' and detail=1";
    $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
    while($barJ=$resJ->fetch())
    {
        $akun[$barJ->noakun]=$barJ->namaakun;
    }

    $hkef='';
    $hkef.="<table id=container6 class=sortable cellspacing=1 border=0>
     <thead>
        <tr>
            <td align=center>".$_SESSION['lang']['index']."</td>
            <td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center>".$_SESSION['lang']['kodeorg']."</td>
            <td align=center>".$_SESSION['lang']['tipeanggaran']."</td>
            <td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
			<!--<td align=center>".$_SESSION['lang']['aruskas']."</td>-->
            <td align=center>".$_SESSION['lang']['kodevhc']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['totalbiaya']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
       </tr>  
     </thead>
     <tbody>";
    $str="select * from ".$dbname.".bgt_budget
        where kodebudget like 'VHC%' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg like '".$mesin."%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=1;
    while($bar= $res->fetch()){
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
	$nmpol = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar->kodevhc."'");
    $hkef.="<tr class=rowcontent>
            <td align=center>".$bar->kunci."</td>
            <td align=center>".$bar->tahunbudget."</td>
            <td align=left>".$bar->kodeorg." - ".$nmorg[$bar->kodeorg]."</td>
            <td align=center>".$bar->tipebudget."</td>
            <td align=left>".$bar->kodebudget." - ".$nmkode[$bar->kodebudget]."</td>
			<!--<td align='left'>".$bar->aruskas." - ".$nmaruskas[$bar->aruskas]."</td>-->
            <td align=left>".$bar->kodevhc." - ".$nmpol[$bar->kodevhc]."</td>
            <td align=right>".$bar->volume."</td>
            <td align=left>".$bar->satuanv."</td>
            <td align=right>".number_format($bar->rupiah)."</td>";
            if($bar->tutup==0){
				$hkef.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editvhc('".$bar->kunci."','".$bar->kodeorg."','".$bar->kodebudget."','".$bar->kodevhc."','".$bar->volume."','".$bar->rupiah."');\" ></td>";

				$hkef.="<td align=center style=width:20px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(3,".$bar->kunci.")\" title=\"Hapus\"></td>";
			}else{				
				$hkef.="<td align=center>&nbsp;</td>";
				$hkef.="<td align=center>&nbsp;</td>";
			}
       $hkef.="
       </tr>";
    $no+=1;
    }
    echo $hkef;        


    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

if($cekapa=='sebarDoong'){
    $var1=$_POST['var1'];
    $var2=$_POST['var2'];
    $var3=$_POST['var3'];
    $var4=$_POST['var4'];
    $var5=$_POST['var5'];
    $var6=$_POST['var6'];
    $var7=$_POST['var7'];
    $var8=$_POST['var8'];
    $var9=$_POST['var9'];
    $var10=$_POST['var10'];
    $var11=$_POST['var11'];
    $var12=$_POST['var12'];
    $rupiah=$_POST['rupe'];
    $fis=$_POST['fis'];
    $kunci=$_POST['kunci'];
    $str="delete from ".$dbname.".bgt_distribusi where kunci=".$kunci;
    $owlPDO->exec($str);
    $str="insert into ".$dbname.".bgt_distribusi (kunci, rp01, fis01, rp02, fis02, rp03, fis03, rp04, fis04, rp05, fis05, rp06, fis06, rp07, fis07, rp08, fis08, rp09, fis09, rp10, fis10, rp11, fis11, rp12, fis12, updateby)
        values(".$kunci.",  
           ".$var1*$rupiah.",
           ".$var1*$fis.",
           ".$var2*$rupiah.",
           ".$var2*$fis.",
           ".$var3*$rupiah.",
           ".$var3*$fis.",
           ".$var4*$rupiah.",
           ".$var4*$fis.",
           ".$var5*$rupiah.",
           ".$var5*$fis.",
           ".$var6*$rupiah.",
           ".$var6*$fis.",
           ".$var7*$rupiah.",
           ".$var7*$fis.",
           ".$var8*$rupiah.",
           ".$var8*$fis.",
           ".$var9*$rupiah.",
           ".$var9*$fis.",
           ".$var10*$rupiah.",
           ".$var10*$fis.",
           ".$var11*$rupiah.",
           ".$var11*$fis.",
           ".$var12*$rupiah.",
           ".$var12*$fis.",
           ".$_SESSION['standard']['userid'].");";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 
}
 
//tampilkan data tab4
if($cekapa=='tab4'){

$tipebudget=$_POST['tipebudget'];
$tahunbudget=$_POST['tahunbudget'];
$mesin=$_POST['mesin'];

$stationsrc= checkPostGet('stationsrc', '');
$mesinsrc  = checkPostGet('mesinsrc', '');
$sebarsrc  = checkPostGet('sebarsrc', '');
$limit     = checkPostGet('limit', '');
if($limit=='0' or $limit==''){
	$limit=25;
}

if($limit<25){
	exit("Warning : Limit terlalu kecil, minimal : 25.");
}

if($limit>5000){
	exit("Warning : Limit terlalu kecil, maksimal : 5000.");
}

//kamus namabarang
$strJ="select kodebarang, namabarang from ".$dbname.".log_5masterbarang";
$resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
$resJ->setFetchMode(PDO::FETCH_OBJ);
while($barJ=$resJ->fetch()){
	$barang[$barJ->kodebarang]=$barJ->namabarang;
}

    
$hkef.="<table id=container6 class=sortable cellspacing=1 border=0 width=100%>
 <thead>
	<tr>
		<td></td>
		<td align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
		<td align=center>".$_SESSION['lang']['mesin']."</td>
		<td align=center>".$_SESSION['lang']['kodeanggaran']."</td>
		<td align=center>".$_SESSION['lang']['aruskas']."</td>
		<td align=center>".$_SESSION['lang']['kodevhc']."</td>
		<td align=center>".$_SESSION['lang']['kodebarang']."</td>
		<td align=center>".$_SESSION['lang']['namabarang']."</td>
		<td align=center>".substr($_SESSION['lang']['jan'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['peb'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['mar'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['apr'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['mei'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['jun'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['jul'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['agt'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['sep'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['okt'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['nov'],0,3)."</td>
		<td align=center>".substr($_SESSION['lang']['dec'],0,3)."</td>
		<td align=center>".$_SESSION['lang']['totalbiaya']."</td>
		<td align=center>".$_SESSION['lang']['action']."</td>
   </tr>  
 </thead>
 <tbody>";
		
$where='';
if($stationsrc!=''){
	$where.=" and b.kodeorg like '".$stationsrc."%'";
}
if($mesinsrc!=''){
	$where.=" and b.kodeorg like '".$mesinsrc."%'";
}

if($sebarsrc=='1'){
	$where.=" and a.kunci in (select kunci from ".$dbname.".bgt_distribusi) ";
}elseif($sebarsrc=='2'){
	$where.=" and a.kunci not in (select kunci from ".$dbname.".bgt_distribusi) ";
}

$sLoad="select a.*, b.tutup from ".$dbname.".bgt_budget_detail a
        left join ".$dbname.".bgt_budget b on a.kunci=b.kunci
        where a.tahunbudget=".$tahunbudget." and a.tipebudget='".$tipebudget."' 
		and a.kodeorg like '".substr($_SESSION['empl']['lokasitugas'],0,4)."%'  
		".$where."
		and (a.kodebudget like 'EXPL%' or a.kodebudget like 'M%' or a.kodebudget like 'TOOL%' or a.kodebudget like 'VHC%' or a.kodebudget = 'PKSM' or a.kodebudget='SERVICE')";
$jlhbrs=count(fetchdata($sLoad));

//$limit = 50;
$page = 0;
$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
if (isset($_POST['page'])) {
	$page = $_POST['page'];
	if ($page < 0)
		$page = 0;
}

$offset = $page * $limit;
$maxdisplay = ($page * $limit);
$nox = 0;
$nox = $maxdisplay;		
		
		
		
$str="select a.*, b.tutup from ".$dbname.".bgt_budget_detail a
        left join ".$dbname.".bgt_budget b on a.kunci=b.kunci
        where a.tahunbudget=".$tahunbudget." and a.tipebudget='".$tipebudget."' 
		and a.kodeorg like '".substr($_SESSION['empl']['lokasitugas'],0,4)."%'  
		".$where."
		and (a.kodebudget like 'EXPL%' or a.kodebudget like 'M%' or a.kodebudget like 'TOOL%' or a.kodebudget like 'VHC%' or a.kodebudget = 'PKSM') limit ".$offset.",".$limit."";
$numrowsx = count(fetchdata($str));
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

if($numrowsx>50){
	$ttlbrs=50;
}else{
	$ttlbrs=$numrowsx;
}

$no=0;
$hkef.="
        <tr class=rowcontent>
			<td colspan=22><button class=mybutton onclick=sebarkanall(".$numrowsx.") title=\"Sebarkan Seluruhnya\">Sebarkan Seluruhnya</button></td>
		</tr>";
while($bar= $res->fetch()){
$no+=1;
$nox+=1;	
$hkef.="
		<tr class=rowcontent style='cursor:pointer;' id=baris".$no.">";
		$hkef.="<td style=display:none>
			<input id='kunci".$no."' value=".$bar->kunci." />
			<input id='rupiah".$no."' value=".$bar->rupiah." />
			<input id='jlh".$no."' value=".$bar->jumlah." />
	
		</td>";
		$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
		$hkef.="<td><input id=chkboxsebar".$no." type=checkbox onclick=sebarkanBoo(".$no."); title='Sebarkan sesuai proporsi diatas'></td>
		<td align=center>".$nox."</td>
		<td align=left>".$bar->kodeorg." - ".$nmorg[$bar->kodeorg]."</td>
		<td align=center>".$bar->kodebudget."</td>
		<td align='left'>".$nmaruskas[$bar->aruskas]."</td>
		<td align=center>".$bar->kodevhc."</td>
		<td align=right>".$bar->kodebarang."</td>
		<td align=left>".$barang[$bar->kodebarang]."</td>
		<td align=right>".number_format($bar->rp01)."</td>
		<td align=right>".number_format($bar->rp02)."</td>
		<td align=right>".number_format($bar->rp03)."</td>
		<td align=right>".number_format($bar->rp04)."</td>
		<td align=right>".number_format($bar->rp05)."</td>
		<td align=right>".number_format($bar->rp06)."</td>
		<td align=right>".number_format($bar->rp07)."</td>
		<td align=right>".number_format($bar->rp08)."</td>
		<td align=right>".number_format($bar->rp09)."</td>
		<td align=right>".number_format($bar->rp10)."</td>
		<td align=right>".number_format($bar->rp11)."</td>
		<td align=right>".number_format($bar->rp12)."</td>
		<td align=right>".number_format($bar->rupiah)."</td>";
		if($bar->tutup==0)
		$hkef.="
		<td align=center>
			<input type=\"image\" id=search4 src='images/skyblue/zoom.png' class=resicon title=".$_SESSION['lang']['sebaran']." onclick=\"sebaran(".$bar->kunci.",event)\";>
		</td>";
		else
		$hkef.="<td align=center>&nbsp;</td>";
       $hkef.="
       </tr>";
    }
	
	@$totrows = ceil($jlhbrs / $limit);
	if ($totrows == 0) {
		$totrows = 1;
	}
	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++) {
		$sel = ($page == $er - 1) ? 'selected' : '';
		$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
	}
	$hkef.="
				 <tr><td colspan=21 align=center>";

	if ($page == '0') {
		$hkef.="<button class=mybutton disabled=true>Prev</button>";
	} else {
		$hkef.="<button class=mybutton onclick=updateTab4(" . ($page - 1) . ");>Prev</button>";
	}

	$hkef.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

	if (($page + 1) == $totrows) {
		$hkef.="<button class=mybutton disabled=true>Next</button>";
	} else {
		$hkef.="<button class=mybutton onclick=updateTab4(" . ($page + 1) . ");>Next</button>";
	}
	$hkef.="</td>
		</tr>";
	
	
	
    echo $hkef;        


    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//tampilkan data tab4
if($cekapa=='sebaran4'){
    $kunci=$_GET['kunci'];
//kamus namabarang
    $strJ="select kodebarang, namabarang from ".$dbname.".log_5masterbarang";
    $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
    while($barJ=$resJ->fetch()){
        $barang[$barJ->kodebarang]=$barJ->namabarang;
    }
    
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	?>
	<script language=javascript1.2 src="js/generic.js"></script>
	<script language=javascript1.2 src="js/budget_budget_pks.js"></script>
	<link rel=stylesheet type='text/css' href='style/generic.css'>
	<?
    
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $gen='generic.css';
	}else if($theme=='red'){
	  $gen='genericRed.css';  
	}else{
	  $gen='genericGray.css';  
	} 
	echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
	"; 
    $hkef='';

    $hkef.="<table id=container5 class=sortable cellspacing=1 border=0 width=100%>
     <thead>";
     $hkef.="</thead>
     <tbody>";
    $str="select * from ".$dbname.".bgt_budget_detail where kunci = '".$kunci."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=1;
    while($bar=$res->fetch())
    {
        $rp01=$bar->rp01; $pr01=($rp01*100/($bar->rupiah));
        $rp02=$bar->rp02; $pr02=($rp02*100/($bar->rupiah));
        $rp03=$bar->rp03; $pr03=($rp03*100/($bar->rupiah));
        $rp04=$bar->rp04; $pr04=($rp04*100/($bar->rupiah));
        $rp05=$bar->rp05; $pr05=($rp05*100/($bar->rupiah));
        $rp06=$bar->rp06; $pr06=($rp06*100/($bar->rupiah));
        $rp07=$bar->rp07; $pr07=($rp07*100/($bar->rupiah));
        $rp08=$bar->rp08; $pr08=($rp08*100/($bar->rupiah));
        $rp09=$bar->rp09; $pr09=($rp09*100/($bar->rupiah));
        $rp10=$bar->rp10; $pr10=($rp10*100/($bar->rupiah));
        $rp11=$bar->rp11; $pr11=($rp11*100/($bar->rupiah));
        $rp12=$bar->rp12; $pr12=($rp12*100/($bar->rupiah));

        $fis01=$bar->fis01; 
        $fis02=$bar->fis02; 
        $fis03=$bar->fis03; 
        $fis04=$bar->fis04; 
        $fis05=$bar->fis05; 
        $fis06=$bar->fis06; 
        $fis07=$bar->fis07; 
        $fis08=$bar->fis08; 
        $fis09=$bar->fis09; 
        $fis10=$bar->fis10; 
        $fis11=$bar->fis11; 
        $fis12=$bar->fis12; 
        if($rp01==0&&$rp02==0&&$rp03==0&&$rp04==0&&$rp05==0&&$rp06==0&&$rp07==0&&$rp08==0&&$rp09==0&&$rp10==0&&$rp11==0&&$rp12==0){
            $rp01=floor(($bar->rupiah*100)/12)*0.01; $pr01=floor(100*100/12);
            $rp02=floor(($bar->rupiah*100)/12)*0.01; $pr02=floor(100*100/12)*0.01;
            $rp03=floor(($bar->rupiah*100)/12)*0.01; $pr03=floor(100*100/12)*0.01;
            $rp04=floor(($bar->rupiah*100)/12)*0.01; $pr04=floor(100*100/12)*0.01;
            $rp05=floor(($bar->rupiah*100)/12)*0.01; $pr05=floor(100*100/12)*0.01;
            $rp06=floor(($bar->rupiah*100)/12)*0.01; $pr06=floor(100*100/12)*0.01;
            $rp07=floor(($bar->rupiah*100)/12)*0.01; $pr07=floor(100*100/12)*0.01;
            $rp08=floor(($bar->rupiah*100)/12)*0.01; $pr08=floor(100*100/12)*0.01;
            $rp09=floor(($bar->rupiah*100)/12)*0.01; $pr09=floor(100*100/12)*0.01;
            $rp10=floor(($bar->rupiah*100)/12)*0.01; $pr10=floor(100*100/12)*0.01;
            $rp11=floor(($bar->rupiah*100)/12)*0.01; $pr11=floor(100*100/12)*0.01;
            $rp12=floor(($bar->rupiah*100)/12)*0.01; $pr12=floor(100*100/12)*0.01;
            $fis01=floor(($bar->jumlah*100)/12)*0.01; 
            $fis02=floor(($bar->jumlah*100)/12)*0.01; 
            $fis03=floor(($bar->jumlah*100)/12)*0.01; 
            $fis04=floor(($bar->jumlah*100)/12)*0.01; 
            $fis05=floor(($bar->jumlah*100)/12)*0.01; 
            $fis06=floor(($bar->jumlah*100)/12)*0.01; 
            $fis07=floor(($bar->jumlah*100)/12)*0.01; 
            $fis08=floor(($bar->jumlah*100)/12)*0.01; 
            $fis09=floor(($bar->jumlah*100)/12)*0.01; 
            $fis10=floor(($bar->jumlah*100)/12)*0.01; 
            $fis11=floor(($bar->jumlah*100)/12)*0.01; 
            $fis12=floor(($bar->jumlah*100)/12)*0.01; 
        }
//        echo "Error: ".$bar->kunci;
        $rp01=0.01*round($rp01*100);
        $rp02=0.01*round($rp02*100);
        $rp03=0.01*round($rp03*100);
        $rp04=0.01*round($rp04*100);
        $rp05=0.01*round($rp05*100);
        $rp06=0.01*round($rp06*100);
        $rp07=0.01*round($rp07*100);
        $rp08=0.01*round($rp08*100);
        $rp09=0.01*round($rp09*100);
        $rp10=0.01*round($rp10*100);
        $rp11=0.01*round($rp11*100);
        $rp12=0.01*round($rp12*100);
        $fis01=0.01*round($fis01*100);
        $fis02=0.01*round($fis02*100);
        $fis03=0.01*round($fis03*100);
        $fis04=0.01*round($fis04*100);
        $fis05=0.01*round($fis05*100);
        $fis06=0.01*round($fis06*100);
        $fis07=0.01*round($fis07*100);
        $fis08=0.01*round($fis08*100);
        $fis09=0.01*round($fis09*100);
        $fis10=0.01*round($fis10*100);
        $fis11=0.01*round($fis11*100);
        $fis12=0.01*round($fis12*100);
        $prall=$pr01+$pr02+$pr03+$pr04+$pr05+$pr06+$pr07+$pr08+$pr09+$pr10+$pr11+$pr12;
        $sifall=$sif01+$sif02+$sif03+$sif04+$sif05+$sif06+$sif07+$sif08+$sif09+$sif10+$sif11+$sif12;

    $hkef.="
            <tr class=rowcontent><td colspan=3 align=center>".$_SESSION['lang']['index']."</td><td align=center>".$kunci."</td></tr>
            <tr class=rowcontent><td colspan=3 align=center>".$_SESSION['lang']['mesin']."</td><td align=center>".$bar->kodeorg."</td></tr>
            <tr class=rowcontent><td colspan=3 align=center>".$_SESSION['lang']['kodeanggaran']."</td><td align=center>".$bar->kodebudget."</td></tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['jan'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=jan4per name=jan4per onblur=\"kalikan4(1,".$bar->rupiah.");\" value=\"".$pr01."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=jan4 name=jan4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp01."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=jan4fis name=jan4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis01."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['peb'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=feb4per name=feb4per onblur=\"kalikan4(2,".$bar->rupiah.");\" value=\"".$pr02."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=feb4 name=feb4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp02."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=feb4fis name=feb4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis02."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['mar'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=mar4per name=mar4per onblur=\"kalikan4(3,".$bar->rupiah.");\" value=\"".$pr03."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=mar4 name=mar4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp03."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=mar4fis name=mar4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis03."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['apr'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=apr4per name=apr4per onblur=\"kalikan4(4,".$bar->rupiah.");\" value=\"".$pr04."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=apr4 name=apr4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp04."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=apr4fis name=apr4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis04."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['mei'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=may4per name=may4per onblur=\"kalikan4(5,".$bar->rupiah.");\" value=\"".$pr05."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=may4 name=may4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp05."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=may4fis name=may4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis05."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['jun'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=jun4per name=jun4per onblur=\"kalikan4(6,".$bar->rupiah.");\" value=\"".$pr06."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=jun4 name=jun4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp06."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=jun4fis name=jun4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis06."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['jul'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=jul4per name=jul4per onblur=\"kalikan4(7,".$bar->rupiah.");\" value=\"".$pr07."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=jul4 name=jul4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp07."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=jul4fis name=jul4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis07."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['agt'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=aug4per name=aug4per onblur=\"kalikan4(8,".$bar->rupiah.");\" value=\"".$pr08."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=aug4 name=aug4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp08."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=aug4fis name=aug4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis08."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['sep'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=sep4per name=sep4per onblur=\"kalikan4(9,".$bar->rupiah.");\" value=\"".$pr09."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=sep4 name=sep4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp09."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=sep4fis name=sep4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis09."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['okt'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=oct4per name=oct4per onblur=\"kalikan4(10,".$bar->rupiah.");\" value=\"".$pr10."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=oct4 name=oct4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp10."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=oct4fis name=oct4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis10."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['nov'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=nov4per name=nov4per onblur=\"kalikan4(11,".$bar->rupiah.");\" value=\"".$pr11."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=nov4 name=nov4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp11."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=nov4fis name=nov4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis11."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".substr($_SESSION['lang']['dec'],0,3)."</td>
                <td align=center><input type=text class=myinputtextnumber id=dec4per name=dec4per onblur=\"kalikan4(12,".$bar->rupiah.");\" value=\"".$pr12."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:30px; />%</td>
                <td align=center><input type=text class=myinputtextnumber id=dec4 name=dec4 onblur=\"jumlahkan7(".$bar->rupiah.");\" value=\"".$rp12."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
                <td align=center><input type=text class=myinputtextnumber id=dec4fis name=dec4fis onblur=\"jumlahkan7a(".$bar->jumlah.");\" value=\"".$fis12."\" onkeypress=\"return angka_doangsamaminus(event);\" maxlength=12 style=width:90px; /></td>
            </tr>
            <tr class=rowcontent>
                <td align=center>".$_SESSION['lang']['total']."</td>
                <td align=center><input type=text class=myinputtextnumber id=all4per name=all4per value=\"".$prall."\" disabled=true maxlength=10 style=width:30px; />%</td>
                <td align=center><input type=hidden id=hiddenrupiah name=hiddenrupiah value=\"".$bar->rupiah."\">".number_format($bar->rupiah,2)."</td>
                <td align=center><input type=hidden id=hiddenjumlah name=hiddenjumlah value=\"".$bar->jumlah."\">".number_format($bar->jumlah,2)."</td>
            </tr>
            <tr class=rowcontent><td align=center colspan=2>".$_SESSION['lang']['action']."</td><td align=center  colspan=2>
                <input type=hidden id=total4 name=total4 value=\"".$bar->rupiah."\">
                <input type=hidden id=total4fis name=total4fis value=\"".$bar->jumlah."\">
                <input type=hidden id=progress name=progress value=\"\">    
                <input type=\"image\" id=search4 src=images/save.png class=resicon title=".$_SESSION['lang']['save']." onclick=\"simpansebaran(".$bar->kunci.",event)\";>
            
                <input type=\"image\" id=sapu4 src=images/clear.png class=resicon title=".$_SESSION['lang']['clear']." onclick=\"sapusebaran(event)\";>
            </td></tr><tr>
       </tr><tr>
       <tr><td align=center colspan=15><button class=mybutton id=tutup4 name=tutup4 onclick=parent.closeDialog()>".$_SESSION['lang']['close']."</button></td>
       </tr>";
    $no+=1;
    }
    echo $hkef;        
 

    echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
    echo " Nol-kan semua persentase untuk mengisi rupiah.";
}
?>