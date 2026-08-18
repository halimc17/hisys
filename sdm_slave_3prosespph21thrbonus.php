<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses        =checkPostGet('proses','');
$per           =checkPostGet('per','');
$unit          =checkPostGet('unit','');
$karyawanid    =checkPostGet('karyawanid','');
$tipe          =checkPostGet('tipe','');
$pph21regular  =checkPostGet('pph21regular','');
$pph21irregular=checkPostGet('pph21irregular','');
$tjpph21x      =checkPostGet('tjpph21x','');
$istjpph21     =checkPostGet('istjpph21','');
$nilaitjpph21  =checkPostGet('nilaitjpph21','');
$baris         =checkPostGet('baris','');
$tipepph         =checkPostGet('tipepph','');

$pph21x= str_replace(',','', checkPostGet('pph21x',''));
$pph21desemberx= str_replace(',','', checkPostGet('pph21desemberx',''));

##Bulan
$bulan12 = substr($per, 5, 2);

switch($proses){
    case'prosespph':
		try {
		$owlPDO->beginTransaction();
		
		$str="select distinct(sudahproses) from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$aktifperiodegaji=$bar['sudahproses'];

		if($aktifperiodegaji==0){
			throw new PDOException('Periode gaji '.$per.' belum ditutup.');
		}
		
		$str="select distinct(tutupbuku) from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$aktifperiodeacct=$bar['tutupbuku'];
		if($aktifperiodeacct==1){
			throw new PDOException('Periode akutansi '.$per.' sudah ditutup.');
		}

		if($tipepph == 'THR'){
			$komponen  = '123';
		}else{
			$komponen  = '124';
        }		
		
		if($baris=='1'){			
			$str = "delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='".$komponen."' and periodegaji='".$per."'";
			$owlPDO->exec($str);
		}

		if($pph21x != 0){
			$str = "insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('".$karyawanid."','".$unit."','".$komponen."','".$pph21x."','1','0','".$per."') ";
			$owlPDO->exec($str);
		}
		
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
	break;
    case 'preview':

        if($tipe!='excel') {
            if($tipepph == ''){
                exit("Warning  : Tipe PPh wajib diisi!");
            }
        }

		$str="select level,percent,upto,penambah from ".$dbname.".sdm_ho_pph21_kontribusi order by level";
		$urut=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$pphtarif[$urut]    =$bar['upto'];
			$pphpenambah[$urut] =$bar['penambah'];
			$pphpercent[$urut]  =$bar['percent'];    
			$urut+=1;  
		}    

		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> '' and tipekaryawan !=0 "; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$dakarbulanan=1;
		}

		if($dakarbulanan==1){
			$strawal = "select * from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$per."' and statuspajak <> ''  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0"; 
		}else{
			$strawal="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0 ";
		}		


        ## Komponen tunjangan tetap
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KOMTJTETAP'";
		$res=fetchdata($str);
		$komponentjtetap = $res[0]['nilai'];

        $hasData = false;

		## DT Tunjangan tetap
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen in (".$komponentjtetap.")  and periodegaji = '".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['plus']==1){
				$dttjtetap[$bar['idkomponen']]=$bar['idkomponen'];	
				$rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
                $hasData = true;
			}
		}

        if(!$hasData){
            exit("Warning : Data gaji kosong, silahkan proses gaji terlebih dahulu periode ".$per." ");
        }

        $count_dttjtetap = count($dttjtetap);

		## DT Komplus
		$str="select a.*,plus from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id 
			where 1=1 and idkomponen in (26,28) and periodegaji = '".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['plus']==1){
                if($tipepph == "THR"){
                    if($bar['idkomponen'] == '28') {
                        $dt_thrbonus[$bar['idkomponen']]=$bar['idkomponen'];	
                        $rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
                    }
                }else{
                    if($bar['idkomponen'] == '26') {
                        $dt_thrbonus[$bar['idkomponen']]=$bar['idkomponen'];	
                        $rupiah[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
                    }
                }
			}
		}

		## Make Option
		$nmgolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
		$optKategori=  makeOption($dbname, 'sdm_5tarifpph21', 'status,kategori');

		$resawal=$owlPDO->query($strawal) or die(print " Gagal: ".PDOException::getMessage());
		$resawal->setFetchMode(PDO::FETCH_ASSOC);
		while($barawal=$resawal->fetch()){
			$dtkarid[$barawal['karyawanid']]		=$barawal['karyawanid'];
			$nik[$barawal['karyawanid']]			=$barawal['nik'];
			$nmkar[$barawal['karyawanid']]			=$barawal['namakaryawan'];
			$golongan[$barawal['karyawanid']]		=$barawal['kodegolongan'];
			$statuspajak[$barawal['karyawanid']]	=$barawal['statuspajak'];
			$bagian[$barawal['karyawanid']]			=$barawal['bagian'];
			$jabatan[$barawal['karyawanid']]		=$barawal['kodejabatan'];	
			$npwp[$barawal['karyawanid']]		    =$barawal['npwp'];	
			$nokeluarga[$barawal['karyawanid']]		=$barawal['no_keluarga'];	
			$tipekaryawan[$barawal['karyawanid']]	=$barawal['tipekaryawan'];	
			$statuskaryawan[$barawal['karyawanid']]	=$barawal['statuskaryawan'];	
			$kota[$barawal['karyawanid']]			=$barawal['kota'];	
			$ptkpnya[$barawal['karyawanid']]		=$ptkpdata[$barawal['statuspajak']];	
		}

		if ($proses == 'excel') {
			$stream.= "<table class=sortable cellspacing=1 border=1>";
		} else {
			$stream.= "<div class='table-scroll'><table class=sortable cellspacing=1 cellpadding=5 style='width:100%;'>";
		}

		$style_upper = "style='text-transform: uppercase;'";

		$stream.="<thead>";
			$stream.="<tr class=rowcontent>";
				$stream.="<th align=center ".$style_upper." rowspan=3>NO</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3> NIK </th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['namakaryawan']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>NPWP</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['statuspajak']."</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['jabatan']."</th>";

                $stream.="<th align=center ".$style_upper." colspan=".$count_dttjtetap." >GAJI / UPAH</th>";
				$stream.="<th align=center ".$style_upper." rowspan=3>".$_SESSION['lang']['total']." GAJI 1 BLN</th>";

                foreach ($dt_thrbonus as $komplus){
                    $stream.="<th ".$style_upper." align=center rowspan=3>".$optNmKomponen[$komplus]."</th>";
                }

				$stream.="<th align=center ".$style_upper." colspan=4>PERHITUNGAN PPH 21 THR/BONUS</th>";
				$stream.="<th align=center ".$style_upper." colspan=4>PERHITUNGAN PPH 21 NON THR/BONUS</th>";

				$stream.="<th align=center ".$style_upper." rowspan=3>PPH21 THR/BONUS</th>";
                
                $stream.="<tr class=rowcontent>";

                    foreach ($dttjtetap as $komplus){
                        $stream.="<th ".$style_upper." align=center rowspan=2>".$optNmKomponen[$komplus]."</th>";
                    }

                    $stream.="<th ".$style_upper." align=center rowspan=2>GAJI BRUTO</th>";
                    $stream.="<th ".$style_upper." align=center rowspan=2>TER</th>";
                    $stream.="<th ".$style_upper." align=center rowspan=2>TARIF EFEKTIF RATA RATA</th>";
                    $stream.="<th align=center ".$style_upper." rowspan=2>PPH 21 GAJI 1 BLN + THR/BONUS</th>";

                    $stream.="<th ".$style_upper." align=center rowspan=2>GAJI 1 BLN</th>";
                    $stream.="<th ".$style_upper." align=center rowspan=2>TER</th>";
                    $stream.="<th ".$style_upper." align=center rowspan=2>TARIF EFEKTIF RATA RATA</th>";
                    $stream.="<th align=center ".$style_upper." rowspan=2>PPH 21 GAJI 1 BLN PPH21</th>";
			$stream.="</tr>";	
			$stream .= "<tr class=rowcontent>";
			$stream .= "</tr>";

		$stream.="</thead>";

		foreach ($dtkarid as $karid){	
			@$no++;
			$stream.="<tr class=rowcontent id=row".$no.">";
			
			if($tipe != 'excel') {
				$stream.="<td hidden id=karyawanid".$no.">".$karid."</td>";
			}
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$nik[$karid]."</td>";
				$stream.="<td>".$nmkar[$karid]."</td>";
				$stream.="<td>".$npwp[$karid]."</td>";
				$stream.="<td align=center>".$statuspajak[$karid]."</td>";
				$stream.="<td>".$nmjabatan[$jabatan[$karid]]."</td>";

                foreach ($dttjtetap as $komplus){
					$stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
					@$ttjtetap[$karid]+=$rupiah[$karid][$komplus];
					@$gttjtetap+=$rupiah[$karid][$komplus];
				}


                $stream.="<td align=right >".number_format($ttjtetap[$karid],0)."</td>";

                foreach ($dt_thrbonus as $komplus){
                    $stream.="<td align=right >".number_format($rupiah[$karid][$komplus],0)."</td>";
                    @$ttjthrbonsu[$karid]+=$rupiah[$karid][$komplus];
					@$gttjthrbonus+=$rupiah[$karid][$komplus];
                }

				## PPH21 THR/BONUS
				@$gajiBruto[$karid] = $ttjtetap[$karid] + $ttjthrbonsu[$karid];
				@$gtgajiBruto = $gttdtkompus + $gttjthrbonus;
				$stream.="<td align=right >".number_format($gajiBruto[$karid],0)."</td>";
				$stream.="<td align=center >".$optKategori[$statuspajak[$karid]]."</td>";

				$str = "select * from ".$dbname.".sdm_5tarifpph21 where status = '".$statuspajak[$karid]."' and kategori = '".$optKategori[$statuspajak[$karid]]."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$tarifData = $res->fetchAll();

				foreach ($tarifData as $bar) {
					if ($gajiBruto[$karid]  >= $bar['minim'] && $gajiBruto[$karid]  <= $bar['maxim']) {
						$tarif_ter[$karid] = $bar['tarif'];
						break; // Keluar dari loop jika sudah menemukan tarif yang sesuai
					}
				}

				$stream.="<td align=center>".number_format($tarif_ter[$karid],2)."</td>";
				## Tarif PPh21
				@$tarifpph21[$karid] = $gajiBruto[$karid] * $tarif_ter[$karid] /100;
				@$gttarifpph21 += $gajiBruto[$karid] * $tarif_ter[$karid] /100;
				$stream.="<td align=right>".number_format($tarifpph21[$karid],0)."</td>";

                ## PPH21 NON THR/BONUS
                $stream.="<td align=right >".number_format($ttjtetap[$karid],0)."</td>";
                $stream.="<td align=center>".$optKategori[$statuspajak[$karid]]."</td>";
                foreach ($tarifData as $bar) {
					if ($ttjtetap[$karid]  >= $bar['minim'] && $ttjtetap[$karid]  <= $bar['maxim']) {
						$tarif_ter_nonthr[$karid] = $bar['tarif'];
						break; // Keluar dari loop jika sudah menemukan tarif yang sesuai
					}
				}

                $stream.="<td align=center>".number_format($tarif_ter_nonthr[$karid],2)."</td>";
                @$tarifpph21_nonbonusthr[$karid] = $ttjtetap[$karid] * $tarif_ter_nonthr[$karid] /100;
				@$gttarifpph21_nonbonusthr += $ttjtetap[$karid] * $tarif_ter_nonthr[$karid] /100;
				$stream.="<td align=right >".number_format($tarifpph21_nonbonusthr[$karid],0)."</td>";

                @$finalPPh21[$karid] = $tarifpph21[$karid] - $tarifpph21_nonbonusthr[$karid];
				$stream.="<td align=right id='pph21x".$no."'>".number_format($finalPPh21[$karid],0)."</td>";
                $gtpph21+=$finalPPh21[$karid];
      

			$stream.="</tr>";	
		}

			$stream.="<tr class=rowcontent>";
                $colspanGt1 = $count_dttjtetap + 6;
                $colspanGt2 = $count_dtkomplus;
                $colspanGt3 = $count_dtbpjsper;
                $colspanGt4 = $count_dtbpjskar;
                $stream.="<td align=center colspan=".$colspanGt1."><b>TOTAL</b></td>";
                $stream.="<td align=center><b>".number_format($gttjtetap,0)."</b></td>";
                $stream.="<td align=center><b>".number_format($gttjthrbonus,0)."</b></td>";
                $stream.="<td align=center colspan=8></td>";
                $stream.="<td align=center><b>".number_format($gtpph21,0)."</b></td>";
			$stream.="</tr>";	
		$stream.="<tbody></table></div>";


		if($tipe!='excel') {
            $stream.="<button class=mybutton onclick=prosespph(".$no.");>".$_SESSION['lang']['proses']."</button>";
			echo $stream."####".$no;
		}
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_pph_pasal21_thrbonus";
			if(strlen($stream)>0)
			{
					if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/'.$file);
							}
							}	
							closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$stream)){
							echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
							exit;
					}else{
							echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
							</script>";
					}
					fclose($handle);
			}    
		}
    break;
}
?>