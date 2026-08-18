<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

   $tahunplafon         =checkPostGet('tahunplafon','');
   $periode		=checkPostGet('periode','');
   $jenisbiaya          =checkPostGet('jenisbiaya','');
   $karyawanid          =checkPostGet('karyawanid','');
   $method		=checkPostGet('method','');
   $ygberobat           =checkPostGet('ygberobat','');
   $rs		=checkPostGet('rs','');
   $diagnosa            =checkPostGet('diagnosa','');
   $klaim		=checkPostGet('klaim','');
   $notransaksi         =checkPostGet('notransaksi','');
   $hariistirahat       =checkPostGet('hariistirahat','');
   $tanggal		=checkPostGet('tanggal','');
   $keterangan          =checkPostGet('keterangan','');		   
   $byrs		=checkPostGet('byrs','');
   $byadmin	=checkPostGet('byadmin','');
   $bydr		=checkPostGet('bydr','');
   $byobat		=checkPostGet('byobat','');
   $total		=checkPostGet('total','');
   $bylab		=checkPostGet('bylab','');
   $bebanperusahaan	=checkPostGet('bebanperusahaan','');
   $bebankaryawan	=checkPostGet('bebankaryawan','');
   $bebanjamsostek	=checkPostGet('bebanjamsostek','');   
   $notransaksi	=checkPostGet('notransaksi','');
   $tanggalkwitansi	=checkPostGet('tanggalkwitansi','');
   $tanggalpengajuan	=checkPostGet('tanggalpengajuan','');
   // cari tipekar
	$namaBiaya = makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama');
	
    $query = "SELECT tipekaryawan, lokasitugas
        FROM ".$dbname.".`datakaryawan` a
        WHERE a.`karyawanid` = '".$karyawanid."'";
    $qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
    $qDetail->setFetchMode(PDO::FETCH_ASSOC);
    while($rDetail=$qDetail->fetch())
    {
        $tipekaryawan=$rDetail['tipekaryawan'];
        $lokasitugas=$rDetail['lokasitugas'];
    }  

   if(!isset($_POST['tahunplafon'])){
        $tahunplafon=date('Y');
   }
   
   $kodeorg=substr($_SESSION['empl']['lokasitugas'],0,4);
  if($method=='insert')
  {
        $str="insert into ".$dbname.".sdm_pengobatanht (	
                  `notransaksi`, `kodeorg`, `karyawanid`,
                  `tahunplafon`, `tanggalkwitansi`, `tanggalpengajuan`, `kodebiaya`, `keterangan`,
                  `rs`, `updateby`, `jasars`,  `jasadr`,
                  `jasalab`, `byobat`, `bypendaftaran`,
                  `ygsakit`, `jlhbayar`, `tanggalbayar`,
                  `totalklaim`, `jlhhariistirahat`,
                  `klaimoleh`, `periode`, `tanggal`, `diagnosa`,
                                          `bebanperusahaan`, `bebankaryawan`, `bebanjamsostek`)
                  values(
                  '".$notransaksi."','".$kodeorg."',".$karyawanid.",
                   ".$tahunplafon.",".tanggalsystem($tanggalkwitansi).",".tanggalsystem($tanggalpengajuan).",'".$jenisbiaya."','".$keterangan."',
                    '".$rs."',".$_SESSION['standard']['userid'].",
                        ".$byrs.",".$bydr.",'".$bylab."','".$byobat."','".$byadmin."',
                        '".$ygberobat."',0,'0000-00-00',
                        ".$total.",".$hariistirahat.",
                        ".$klaim.",'".$periode."','".tanggalsystem($tanggal)."',
                        '".$diagnosa."',".$bebanperusahaan.",".$bebankaryawan.",".$bebanjamsostek."			
                  )";	 // exit("Error:$str");
  }
  else if($method=='del')
  {
        $str="delete from ".$dbname.".sdm_pengobatanht where notransaksi='".$notransaksi."'";
  }
  else if($method=='update')
  {
    $str="update ".$dbname.".sdm_pengobatanht set karyawanid='".$karyawanid."',tanggalkwitansi='".tanggalsystem($tanggalkwitansi)."',tanggalpengajuan='".tanggalsystem($tanggalpengajuan)."',kodebiaya='".$jenisbiaya."',
          keterangan='".$keterangan."',rs='".$rs."',updateby='".$_SESSION['standard']['userid']."',jasars='".$byrs."',
          jasadr='".$bydr."',jasalab='".$bylab."',byobat='".$byobat."',bypendaftaran='".$byadmin."',
          ygsakit='".$ygberobat."',totalklaim='".$total."',jlhhariistirahat='".$hariistirahat."',
          klaimoleh='".$klaim."',periode='".$periode."',tanggal='".tanggalsystem($tanggal)."',diagnosa='".$diagnosa."',
          bebanperusahaan='".$bebanperusahaan."',bebankaryawan='".$bebankaryawan."',bebanjamsostek='".$bebanjamsostek."'
          where notransaksi='".$notransaksi."'";
  }
  else if($method=='posting')
  {
	  //get data
	  
	 // 
	$str="select noakun from ".$dbname.".log_5klsupplier where kode='S006'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$noakuncredit=$bar['noakun'];
		
	$str="select * from ".$dbname.".sdm_pengobatanht where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	
	
	if($tipekaryawan>='1' && $tipekaryawan<='6'){
		$kodejurnal='OBT02';
	}else{
		$kodejurnal='OBT01';
	}
	
	$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
	
	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	"kodeorg='".$kdpt[$bar['kodeorg']]."' and kodekelompok='".$kodejurnal."'");
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter']+1,3);
	
	$queryparameter = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',
	"jurnalid='".$kodejurnal."'");
	$tmpakun = fetchData($queryparameter);
	
	
	# Prep No Jurnal
	$nojurnal = str_replace('-','',$bar['tanggalbayar'])."/".$bar['kodeorg']."/".
	$kodejurnal."/".$konter;
	
		 $ht[] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodejurnal,
			'tanggal'=>$bar['tanggalbayar'],
			'tanggalentry'=>date('Y-m-d'),
			'posting'=>'0',
			'totaldebet'=>$bar['jlhbayar'],
			'totalkredit'=>$bar['jlhbayar']*-1,
			'amountkoreksi'=>'0',
			'noreferensi'=>$bar['notransaksi'],
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
  
 
		// Default Segment
		$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		
		//db
		$dt[] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$bar['tanggalbayar'],
			'nourut'=>'1',
			'noakun'=>$tmpakun[0]['noakundebet'],
			'keterangan'=>'Pembayaran klaim rumah sakit no.transaksi '.$bar['notransaksi'],
			'jumlah'=>$bar['jlhbayar'],
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$bar['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>$bar['karyawanid'],
			'kodecustomer'=>'',
			'kodesupplier'=>$bar['rs'],
			'noreferensi'=>$bar['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$bar['keterangan'],
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
		
		$dt[] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$bar['tanggalbayar'],
			'nourut'=>'2',
			'noakun'=>$noakuncredit,
			'keterangan'=>'Pembayaran klaim rumah sakit no.transaksi '.$bar['notransaksi'],
			'jumlah'=>$bar['jlhbayar']*-1,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$bar['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>$bar['karyawanid'],
			'kodecustomer'=>'',
			'kodesupplier'=>$bar['rs'],
			'noreferensi'=>$bar['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$bar['keterangan'],
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $defSegment
		);
	
	  
	#insert ht 
	$queryht = insertQuery($dbname,'keu_jurnalht',$ht);
	try{$owlPDO->exec($queryht); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; }  
	  
	#insert dt 
	$querydt = insertQuery($dbname,'keu_jurnaldt',$dt);
	try{$owlPDO->exec($querydt); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; } 

	#update nokounter
	$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
        "kodeorg='".$kdpt[$bar['kodeorg']]."' and kodekelompok='".$kodejurnal."'");
    $errCounter = "";
    try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
	
	#update flag posting
	$queryJ = updateQuery($dbname,'sdm_pengobatanht',array('postingjurnal'=>1),
        "notransaksi='".$bar['notransaksi']."'");
    $errCounter = "";
    try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
	
	$str="";
	
  }
  else 
  {
        $str="";
  }

  if($str!=''){
      try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
  }

// if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
    // $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag, a.notransaksi as notransaksi,
          // a.karyawanid as karyawanid,a.kodebiaya as kodebiaya,a.keterangan as keterangan,
          // c.lokasitugas as lokasitugas,a.tahunplafon as thnplafon,a.periode as periode,
          // b.namasupplier as rs,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
          // a.bypendaftaran as byadmin,a.ygsakit as ygsakit,a.tanggal as tanggal,a.totalklaim as totalklaim,
          // a.jlhhariistirahat as istirahat,a.bebankaryawan as bebankaryawan,a.bebanjamsostek as bebanjamsostek,
          // a.bebanperusahaan as bebanperusahaan,a.diagnosa as diagnosa,a.klaimoleh as klaim
          // from ".$dbname.".sdm_pengobatanht a left join
          // ".$dbname.".log_5supplier b on a.rs=b.supplierid 
          // left join ".$dbname.".datakaryawan c
          // on a.karyawanid=c.karyawanid
          // left join ".$dbname.".sdm_5diagnosa d
          // on a.diagnosa=d.id
          // where a.periode='".$tahunplafon."'
          // and (c.tipekaryawan in ('0','7','8') or c.alokasi=1)
          // order by a.updatetime desc, a.tanggal desc";
// //                and (c.tanggalkeluar = '0000-00-00' or c.tanggalkeluar>= '".date("Y-m-d")."') 
// }
// else{
	
	if(substr($_SESSION['empl']['lokasitugas'],2,2)=='RO') {
		$orgin="select kodeorganisasi from ".$dbname.".organisasi where 
				length(kodeorganisasi)=4 and induk='".$_SESSION['org']['kodeorganisasi']."'";
	}else if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO'){
		$orgin="select kodeunit from ".$dbname.".bgt_regional_assignment
		where regional='".$_SESSION['empl']['regional']."'";
	} else {
		$orgin="select kodeorganisasi from ".$dbname.".organisasi where 
				length(kodeorganisasi)=4 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
				$karid=" and a.karyawanid='".$_SESSION['standard']['userid']."'";
	}
	
    $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag, a.notransaksi as notransaksi,
          a.karyawanid as karyawanid,a.kodebiaya as kodebiaya,a.keterangan as keterangan,
          c.lokasitugas as lokasitugas,a.tahunplafon as thnplafon,a.periode as periode,
          b.namasupplier as rs,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
          a.bypendaftaran as byadmin,a.ygsakit as ygsakit,a.tanggal as tanggal,a.totalklaim as totalklaim,
          a.jlhhariistirahat as istirahat,a.bebankaryawan as bebankaryawan,a.bebanjamsostek as bebanjamsostek,
          a.bebanperusahaan as bebanperusahaan,a.diagnosa as diagnosa,a.klaimoleh as klaim
          from ".$dbname.".sdm_pengobatanht a left join
          ".$dbname.".log_5supplier b on a.rs=b.supplierid 
          left join ".$dbname.".datakaryawan c
          on a.karyawanid=c.karyawanid
          left join ".$dbname.".sdm_5diagnosa d
          on a.diagnosa=d.id
          where a.periode='".$tahunplafon."' 
         and a.kodeorg in (".$orgin.") ".$karid."
          order by a.updatetime desc, a.tanggal desc";
//                and (c.tanggalkeluar = '0000-00-00' or c.tanggalkeluar>= '".date("Y-m-d")."')
//}
     // echo $str;
	 $stream='';
     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
     $res->setFetchMode(PDO::FETCH_OBJ);
      $no=0;
      $regional = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional');
      $golonganKar = makeOption($dbname,'datakaryawan','karyawanid,kodegolongan');
      while($bar=$res->fetch())
      {
		  
                    $sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$bar->kodebiaya."' and kodegolongan='".$golonganKar[$bar->karyawanid]."' and regional = '".$regional[$bar->lokasitugas]."'";
                    $qPlaf=$owlPDO->query($sPlaf) or die(print " Gagal: ".PDOException::getMessage());
                    $qPlaf->setFetchMode(PDO::FETCH_ASSOC);
                    $qPlafnumrows=owlBaris($qPlaf);
                    $rPlaf=$qPlaf->fetch();
                    if($rPlaf['satuan']==4){
                            $vWhere = " and tahunplafon between '".(($bar->thnplafon)-2)."' and '".$bar->thnplafon."'";
                    }else{
                            $vWhere = " and tahunplafon='".$bar->thnplafon."'";
                    } 

                    $sPlaf2="select sum(jlhbayar) as jlhbayar, sum(bebanperusahaan) as bebanperusahaan, kodebiaya from ".$dbname.".sdm_pengobatanht
                                      where karyawanid='".$bar->karyawanid."' and kodebiaya='".$bar->kodebiaya."' ".$vWhere." 
                                      group by kodebiaya";
                    $qPlaf2=$owlPDO->query($sPlaf2) or die(print " Gagal: ".PDOException::getMessage());
                    $qPlaf2->setFetchMode(PDO::FETCH_ASSOC);
                    $rPlaf2=$qPlaf2->fetch();

                    $gaji="select * from ".$dbname.".sdm_5gajipokok where karyawanid = ".$bar->karyawanid." and tahun like ".$bar->thnplafon."";
                    $hasil=$owlPDO->query($gaji) or die(print " Gagal: ".PDOException::getMessage());
                    $hasil->setFetchMode(PDO::FETCH_ASSOC);
                    $row=$hasil->fetch();
                    $jumlahgaji=$row['jumlah'];

                    if($bar->kodebiaya=='RWJLN'){
                            $hasilPlaf=$jumlahgaji-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
                    }else if($bar->kodebiaya=='RWINP'){
                            $hasilPlaf=$rPlaf['rupiah'];
                    }else if($rPlaf['satuan']==4){
                            $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
                    }else if($rPlaf['satuan']==3){
                            $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
                    }else{
                            if($qPlafnumrows <= 0){
                                    $hasilPlaf='0';
                            }else{
                                    if($rPlaf2['jlhbayar'] >= $rPlaf['rupiah']){
                                            $hasilPlaf='0';
                                    }else{
                                            $hasilPlaf=($rPlaf['rupiah'])-($rPlaf2['bebanperusahaan']-$bar->bebanperusahaan);
                                    }
                            }
                    }

               $no+=1;
               echo"<tr class=rowcontent>
               <td align=center>";
                    $sPlaf="select * from ".$dbname.".sdm_pengobatanplafond where kodejenisbiaya='".$bar->kodebiaya."' and kodegolongan='".$golonganKar[$bar->karyawanid]."' and regional = '".$regional[$bar->lokasitugas]."'";
                    $qPlaf=$owlPDO->query($sPlaf) or die(print " Gagal: ".PDOException::getMessage());
                    $qPlaf->setFetchMode(PDO::FETCH_ASSOC);
                    $rPlaf=$qPlaf->fetch();
               if($bar->posting==0)
               {
                   $ket=rawurlencode($bar->keterangan);
                   echo"<img src=images/edit.png title='edit' class=resicon onclick=\"editPengobatan('".$bar->notransaksi."','".$bar->karyawanid."','".$bar->kodebiaya."','".$bar->lokasitugas."','".$bar->thnplafon."','".$bar->periode."','".$bar->rs."','".$bar->byrs."','".$bar->bydr."','".$bar->bylab."','".$bar->byobat."','".$bar->byadmin."','".$bar->ygsakit."','".$bar->diagnosa."','".tanggalnormal($bar->tanggal)."','".$bar->totalklaim."','".$bar->istirahat."','".$bar->bebankaryawan."','".$bar->bebanjamsostek."','".$bar->bebanperusahaan."','".$bar->klaim."','".$ket."','".tanggalnormal($bar->tanggalkwitansi)."','".tanggalnormal($bar->tanggalpengajuan)."','".number_format($hasilPlaf,2)."','".$rPlaf['satuan']."')\">";
                   echo"&nbsp<img src=images/close.png title='delete' class=resicon onclick=deletePengobatan('".$bar->notransaksi."')>";
               }
                 echo"&nbsp<img src=images/zoom.png title='View' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)>";

               echo"</td><td align=center>".$no."</td>
                      <td>".$bar->notransaksi."</td>
                      <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
                      <td>".tanggalnormal($bar->tanggal)."</td>
                      <td>".$bar->namakaryawan."</td>
                      <td>".$bar->namasupplier."</td>
                      <td>".$namaBiaya[$bar->kodebiaya]."</td>
                      <td align=right>".number_format($bar->byrs,2,'.',',')."</td>
                      <td align=right>".number_format($bar->byadmin,2,'.',',')."</td>
                      <td align=right>".number_format($bar->bylab,2,'.',',')."</td>
                      <td align=right>".number_format($bar->byobat,2,'.',',')."</td>
                      <td align=right>".number_format($bar->bydr,2,'.',',')."</td>
                       <td align=right>".number_format($bar->bebanperusahaan,2,'.',',')."</td>
                      <td align=right>".number_format($bar->bebankaryawan,2,'.',',')."</td>
                      <td align=right>".number_format($bar->bebanjamsostek,2,'.',',')."</td>                                         
                      <td>".$bar->ketdiag."</td>
                    <td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>
                    <td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
                      <td>".$bar->keterangan."</td>
                    </tr>";  	
      }			  			
?>