<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$blok = checkPostGet('blok', '');
$per2 = checkPostGet('per2', '');
$kdkeg = checkPostGet('kdkeg', '');
$kdbrg = checkPostGet('kdbrg', '');
$nojurnal = checkPostGet('nojurnal', '');
$notransaksi = checkPostGet('notransaksi', '');



$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}

$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');

switch ($method) {
######PREVIEW
//getdetailjurnal

	case'detailspk':
		$tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=65% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$notransaksi."</td></tr>";
        $tab.="</tbody></table>";
        $tab.="<br />".$titleDetail[0]."<br />";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['kodeorg']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['jjg']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahpenalty']."</td>";
        
        $tab.="<td  align=center>".$_SESSION['lang']['brondolan']."</td>";
        $tab.="<td  align=center>Premi Brondolan</td>";
        
        $tab.="<td  align=center>Premi Kehadiran</td>";
        
        $tab.="<td align=center>".$_SESSION['lang']['premibasis']."</td>";
        $tab.="<td align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
	break;

	case'panen':
		$tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<fieldset><legend>Detail</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=65% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$notransaksi."</td></tr>";
        $tab.="</tbody></table>";
        $tab.="<br /><br />";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['kodeorg']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['jjg']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['luas']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahkerja']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['upahpenalty']."</td>";
        
        $tab.="<td  align=center>".$_SESSION['lang']['brondolan']."</td>";
        $tab.="<td  align=center>Premi Brondolan</td>";
        
        $tab.="<td  align=center>Premi Kehadiran</td>";
        
        $tab.="<td align=center>".$_SESSION['lang']['premibasis']."</td>";
        $tab.="<td align=center>Total ".$_SESSION['lang']['upahpremi']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['rupiahpenalty']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr></thead><tbody>";
        
        
        $isiQuery="a.notransaksi,a.nik,a.kodekegiatan,a.kodeorg as kodeblok,a.tahuntanam,a.hasilkerja,a.hasilkerjakg,
            a.jumlahhk,a.norma,a.outputminimal,a.upahkerja,a.upahpenalty,a.upahpremi,a.premibasis,a.umr,a.statusblok,
            a.pekerjaanpremi,a.penalti1,a.penalti2,a.penalti3,a.penalti4,a.penalti5,a.penalti6,a.penalti7,a.penalti8,
            a.penalti9,a.penalti10,a.rupiahpenalty,a.luaspanen,a.kodesegment,a.brondolan,a.jjgpenalty,b.*";
        $queryhtml="select ".$isiQuery." from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b 
			on a.notransaksi=b.notransaksi where a.notransaksi='".$notransaksi."' and a.kodeorg='".$blok."' ";

		$qData=$owlPDO->query($queryhtml) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($rData=$qData->fetch())
		{
            
              
                $iLibur="select count(*) as libur from ".$dbname.".sdm_5harilibur where tanggal='".$rData['tanggal']."' and "
                        . " keterangan='libur' and kebun in ('GLOBAL','".$rData['kodeorg']."') ";

				$nLibur=$owlPDO->query($iLibur) or die(print " Gagal: ".PDOException::getMessage());
				$nLibur->setFetchMode(PDO::FETCH_ASSOC);
				$dLibur=$nLibur->fetch();
					$libur=$dLibur['libur'];
                    
                    $day = date('D', strtotime($rData['tanggal']));
                    if($day=='Sun')$cekminggu=1; 
                    else $cekminggu=0;
                    $ceklibur=$libur+$cekminggu;
                    
                  
                    
                    if($ceklibur>0)
                    {
                        $jenisPremi='LIBUR';
                    }
                    else
                    {
                        $jenisPremi='KERJA';
                    }
                 
                    
               /* $iTopo=" select * from ".$dbname.".setup_blok where kodeorg='".$rData['kodeblok']."' ";

                $nTopo=$owlPDO->query($iTopo) or die(print " Gagal: ".PDOException::getMessage());
				$nTopo->setFetchMode(PDO::FETCH_ASSOC);
				$dTopo=$nTopo->fetch();*/
                
                $iTopo="select * from ".$dbname.".setup_blok_tahunan where kodeorg = '".$rData['kodeblok']."' and tahun='".str_replace('-', '', $per2)."' ";

				//exit('Error :'.$str);
				$nTopo=$owlPDO->query($iTopo) or die(print " Gagal: ".PDOException::getMessage());
				$nTopo->setFetchMode(PDO::FETCH_ASSOC);
				$numrows=owlBaris($nTopo);
				if($numrows==0){

				$iTopo="select * from ".$dbname.".setup_blok where kodeorg = '".$rData['kodeblok']."' ";

				$nTopo=$owlPDO->query($iTopo) or die(print " Gagal: ".PDOException::getMessage());
				$nTopo->setFetchMode(PDO::FETCH_ASSOC);

				}
				$dTopo=$nTopo->fetch();

                $iBasis="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$_SESSION['org']['kodeorganisasi']."'"
                        . " and jenispremi='".$jenisPremi."' and topografi='".$dTopo['topografi']."'"
                        . " and kelaspohon='".$dTopo['kelaspohon']."' ";

                $nBasis=$owlPDO->query($iBasis) or die(print " Gagal: ".PDOException::getMessage());
				$nBasis->setFetchMode(PDO::FETCH_ASSOC);
				$dBasis=$nBasis->fetch();  
                //echo $iBasis;
                
                
            
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td>".$RnamaKary[$rData['nik']]."</td>";
                $tab.="<td>".$rData['kodeblok']."</td>";
                $tab.="<td align=right>".$rData['hasilkerja']."</td>";
                $tab.="<td align=right>".number_format($rData['luaspanen'],2)."</td>";
                $tab.="<td align=right>".number_format($rData['upahkerja'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['upahpenalty'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['brondolan'],0)."</td>";
                
                $premibrondolan=$dBasis['premibrondolan']*$rData['brondolan'];
                
                $tab.="<td align=right>".number_format($premibrondolan,0)."</td>";
                
                if($ceklibur>0)
                {
                    
                    if($rData['hasilkerja']>=$dBasis['basis'])//cek apakah capai basis / tidak
                    {
                        $premiHadir=$dBasis['premiliburcapaibasis'];
                    }
                    else
                    {
                        $premiHadir=$dBasis['premilibur'];
                    }
                    
                    
                }
                else
                {
                    $premiHadir=$dBasis['premitopografi'];
                }
                
                
                $tab.="<td align=right>".number_format($premiHadir,0)."</td>";
                
                
                $premiBasis=$rData['upahpremi']-$premiHadir-$premibrondolan;
                
                $tab.="<td align=right>".number_format($premiBasis)."</td>";
                
                $tab.="<td align=right>".number_format($rData['upahpremi'],0)."</td>";
                $tab.="<td align=right>".number_format($rData['rupiahpenalty'],0)."</td>";
                $sisa=$rData['upahkerja']-$rData['upahpenalty']+$rData['premibasis']+$rData['upahpremi']-$rData['rupiahpenalty'];
                $tab.="<td align=right>".number_format($sisa,0)."</td>";
                $tab.="</tr>";
                @$totJanjang+=$rData['hasilkerja'];
                @$totUpahKerja+=$rData['upahkerja'];
                @$totUpahKerjapenalty+=$rData['upahpenalty'];
                @$totUpahPremi+=$rData['upahpremi'];
                //$totUpahPremibasis+=$rData['premibasis'];
                @$totUpahDenda+=$rData['rupiahpenalty'];
                @$totLuas+=$rData['luaspanen'];
                @$totSisa+=$sisa;
                @$totBrondolan+=$rData['brondolan'];
                @$totPremiBrondolan+=$premibrondolan;
                
                @$totPremiKehadiran+=$premiHadir;
                
                @$totUpahPremibasis+=$premiBasis;
                
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($totJanjang,0)."</td>";
        $tab.="<td align=right>".number_format($totLuas,2)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerja,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahKerjapenalty,0)."</td>";
        
        $tab.="<td align=right>".number_format($totBrondolan)."</td>";
        $tab.="<td align=right>".number_format($totPremiBrondolan)."</td>";
        $tab.="<td align=right>".number_format($totPremiKehadiran)."</td>";
        
        
        $tab.="<td align=right>".number_format($totUpahPremibasis,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahPremi,0)."</td>";
        $tab.="<td align=right>".number_format($totUpahDenda,0)."</td>";
        $tab.="<td align=right>".number_format($totSisa,0)."</td>";
        $tab.="</tr></tbody></table>";
		echo $tab;
	break;


	 case 'detailjurnal':
		$stream="<link rel=stylesheet type=text/css href=style/generic.css>";
		$stream.="
				<fieldset>
				<legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>
				<table class=sortable cellspacing=1 width=50%>
					<tr class='rowcontent'>
						<td align='left'>".$_SESSION['lang']['unitkerja']."</td>
						<td align='left'>:</td>
						<td align='left'>".substr($blok,0,4)."</td>
					</tr>
					<tr class='rowcontent'>
						<td align='left'>".$_SESSION['lang']['divisi']."</td>
						<td align='left'>:</td>
						<td align='left'>".substr($blok,0,6)."</td>
					</tr>
					<tr  class='rowcontent'>
					<td align='left'>".$_SESSION['lang']['blok']."</td>
						<td align='left'>:</td>
						<td align='left'>".$blok."</td>
					</tr>
					
					
					<tbody>";	
		$stream.="</table>";			
		$stream.="<br>";
		$stream.="
				<table class=sortable cellspacing=1>
					<thead>
					<tr class='rowheader'>
						<td align='center'>NO</td>
						<td align='center'>".$_SESSION['lang']['nojurnal']."</td>
						<td align='center'>".$_SESSION['lang']['tanggal']."</td>
						<td align='center'>".$_SESSION['lang']['debet']."</td>
						<td align='center'>".$_SESSION['lang']['kredit']."</td>
						<td align='center'>".$_SESSION['lang']['keterangan']."</td>
						<td align='center'>".$_SESSION['lang']['kodekegiatan']."</td>
						<td align='center'>".$_SESSION['lang']['noreferensi']."</td>
						<td align='center'>".$_SESSION['lang']['jumlah']."</td>
					</tr>
					</thead>
					<tbody>";	
		
		##ambil akun lawannya
		
		/*
		SELECT * FROM `keu_jurnaldt_vw` WHERE `periode` = '2015-07' AND `noreferensi` = 'ALK_WAS' AND `kodeblok` = 'DUKE02085A'
		*/
		
		if(substr($nojurnal,0,3)=='ALK')
		{
			if(substr($nojurnal,0,7)=='ALK_POT')
			{
				$str="SELECT * FROM ".$dbname.".`keu_jurnaldt_vw` WHERE `noreferensi` = '".$nojurnal."' AND `kodeorg` = '".substr($blok,0,4)."' AND `periode` = '".$per2."' ";
			}
			else
			{			
				$str="select count(noakun) as rowakun,noakun from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$nojurnal."' 
					and periode='".$per2."' and kodeblok='".$blok."'
					group by noakun";	
			}	
		}
		else
		{
			$str="select count(noakun) as rowakun,noakun from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$nojurnal."' 
				group by noakun";
		}
		

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$nox++;
			$akunlawan[$nox]=$bar['noakun'];
		}

		if(substr($nojurnal,0,3)=='ALK')
		{
			$str="select * from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$nojurnal."' 
			 and kodeblok='".$blok."' and (kodekegiatan='".$kdkeg."' or kodekegiatan='') 
			 and noakun='".substr($kdkeg,0,7)."' and periode='".$per2."' ";
		}
		else
		{
			$str="select * from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$nojurnal."' 
				 and kodeblok='".$blok."' and (kodekegiatan='".$kdkeg."' or kodekegiatan='') 
				 and noakun='".substr($kdkeg,0,7)."'  ";	
		}
	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			
			if($akunlawan[1]==$bar['noakun'])
			{
				$isiakunlawan=$akunlawan[2];
			}
			else
			{
				$isiakunlawan=$akunlawan[1];
			}
			
			
			if($bar['jumlah']<0)
			{
				$akunsatu=$isiakunlawan;	
				$akundua=$bar['noakun'];	
				
			}
			else
			{
				$akunsatu=$bar['noakun'];
				$akundua=$isiakunlawan;	
				
			}
			
		
			@$no+=1;
			$stream.="	
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$bar['nojurnal']."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td>".$akunsatu."</td>
					<td>".$akundua."</td>
					<td>".$bar['keterangan']."</td>
					<td>".$bar['kodekegiatan']."</td>
					<td>".$bar['noreferensi']."</td>
					<td align=right>".number_format($bar['jumlah'])."</td>
					
				</tr>";
				@$tjumlah+=$bar['jumlah'];
		}/*<td>".$bar['noakun']."</td>
					<td>".$isiakunlawan."</td>*/
		$stream.="
			<tr class=rowcontent>
				<td colspan=8>TOTAL</td>
				<td align=right>".number_format($tjumlah)."</td>
			</tr>";
		$stream.="</table></fieldset>";
		
		echo $stream;

		break;
		default;


    case 'detailbarang':
	
		$norefg=$wherej=$norefj="";
		if($notransaksi!='')
		{
			$norefg=" and notransaksireferensi='".$notransaksi."' ";
			$norefj=" and noreferensi='".$notransaksi."' ";
		}
		else
		{
			$wherej=" and kodeblok='".$blok."' and kodekegiatan='".$kdkeg."'";
		}
		//echo $wherej;
		
		$stream="<link rel=stylesheet type=text/css href=style/generic.css>";
		$stream.="
				<fieldset>
				<legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>
				<table class=sortable cellspacing=1 width=50%>
					<tr class='rowcontent'>
						<td align='left'>UNIT</td>
						<td align='left'>:</td>
						<td align='left'>".substr($blok,0,4)."</td>
					</tr>
					<tr class='rowcontent'>
						<td align='left'>DIVISI</td>
						<td align='left'>:</td>
						<td align='left'>".substr($blok,0,6)."</td>
					</tr>
					<tr  class='rowcontent'>
					<td align='left'>BLOK</td>
						<td align='left'>:</td>
						<td align='left'>".$blok."</td>
					</tr>
					<tr  class='rowcontent'>
					<td align='left'>PERIODE</td>
						<td align='left'>:</td>
						<td align='left'>".$per2."</td>
					</tr>
					
					<tbody>";	
		$stream.="</table>";			
		$stream.="<br>";
		$stream.="
				
				
				<table class=sortable cellspacing=1>
					<thead>
					<tr class='rowheader'>
						<td align='center'>NO</td>
						<td align='center'>NO TRANSAKSI</td>
						<td align='center'>GUDANG</td>
						<td align='center'>TANGGAL</td>
						<td align='center'>KODE BARANG</td>
						<td align='center'>NAMA BARANG</td>
						<td align='center'>SATUAN</td>
						<td align='center'>JUMLAH</td>
						<td align='center'>HARGA</td>
						<td align='center'>TOTAL</td>
					</tr>
					</thead>
					<tbody>";	
	
		$str="select * from ".$dbname.".log_transaksi_vw where 
		kodekegiatan='".$kdkeg."' and kodebarang='".$kdbrg."' and kodeblok='".$blok."' and tanggal like '".$per2."%' ".$norefg." ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			@$no+=1;
			$stream.="	
				<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$bar['notransaksi']."</td>
					<td>".$bar['kodegudang']."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td>".$bar['kodebarang']."</td>
					<td>".$nmBrg[$bar['kodebarang']]."</td>
					<td>".$satBrg[$bar['kodebarang']]."</td>
					<td align=right>".number_format($bar['jumlah'],2)."</td>
					<td align=right>".number_format($bar['hargarata'])."</td>
					<td align=right>".number_format($bar['hartot'])."</td>
				</tr>";
				@$tjumlah+=$bar['jumlah'];
				@$thartot+=$bar['hartot'];
		}
		$stream.="
			<tr class=rowcontent>
				<td colspan=7>TOTAL</td>
				<td align=right>".number_format($tjumlah,2)."</td>
				<td></td>
				<td align=right>".number_format($thartot)."</td>
			</tr></table>
				";
				
				

						
		$stream.="<br>";
		$stream.="<br>";
		$stream.="
				<table class=sortable cellspacing=1>
					<thead>
					<tr class='rowheader'>
						<td align='center'>NO</td>
						<td align='center'>NO JURNAL</td>
						<td align='center'>TANGGAL</td>
						<td align='center'>DEBET</td>
						<td align='center'>KREDIT</td>
						<td align='center'>KETERANGAN</td>
						
						<td align='center'>NO REFERENSI</td>
						<td align='center'>JUMLAH</td>
					</tr>
					</thead>
					<tbody>";			
					
		$str="select * from ".$dbname.".keu_jurnaldt_vw where kodebarang='".$kdbrg."' 
				  and periode='".$per2."' ".$norefj." ".$wherej." ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			
			
			
			$strv="select count(noakun) as rowakun,noakun from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$bar['nojurnal']."' 
				group by noakun";
			$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_ASSOC);
			while($barv=$resv->fetch())	
			{
				$noxv++;
				$akunlawanv[$noxv]=$barv['noakun'];
			}
			
			
			if($akunlawanv[1]==$bar['noakun'])
			{
				$isiakunlawan=$akunlawanv[2];
			}
			else
			{
				$isiakunlawan=$akunlawanv[1];
			}
			
			
			
			if($bar['jumlah']<0)
			{
				$akunsatu=$isiakunlawan;	
				$akundua=$bar['noakun'];	
				$jumlah=abs($bar['jumlah']);
			}
			else
			{
				$akunsatu=$bar['noakun'];
				$akundua=$isiakunlawan;	
				$jumlah=$bar['jumlah'];
			}
			
			$stream.="	
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$bar['nojurnal']."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td>".$akunsatu."</td>
					<td>".$akundua."</td>
					<td>".$bar['keterangan']."</td>
					
					<td>".$bar['noreferensi']."</td>
					<td align=right>".number_format($jumlah)."</td>
					
				</tr>";
		}			
					
					
					
					
					
					
					
		$stream.="</table></fieldset>";
		
		echo $stream;
	
        break;
}
?>