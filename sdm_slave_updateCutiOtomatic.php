<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

/**Otomatic KHL 
Update data tanggal masuk dan keluar dengan alasan adanya aturan dari pemerintah
3bulan wajib menjadi staff atau tetap.

Role:
KHL yang aktif, akan update per 3 bulan keluar dan dan Update masuk lagi 
1 bulan berikutnya.
~ Atwal Arifin
**/

// $tglskrg = date("Y-m-d");
// $strx="select * from ".$dbname.".datakaryawan where tipekaryawan = '4' and statuskaryawan ='Aktif' and tanggalmasuk <> '0000-00-00' ";
// $query=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
// $query->setFetchMode(PDO::FETCH_OBJ);   
// while($r=$query->fetch())
// {
// 	$tglkeluar = date('Y-m-d',strtotime($r->tanggalkeluar));
// 	$tglmasuk = date('Y-m-d',strtotime($r->tanggalmasuk));
// 	$tglkeluarx = date('Y-m-d',strtotime('+1 Month',strtotime($tglkeluar)));
// 	if(($tglkeluar<$tglskrg) and ($tglkeluarx<=$tglskrg)){
// 		if($tglkeluar < $tglmasuk){
// 			$tglkeluar = date('Y-m-d',strtotime('+3 Month',strtotime($tglmasuk)));
// 		}
// 		$newtglmasuk = date('Y-m-d',strtotime('+1 Month',strtotime($tglkeluar)));
// 		$newtglkeluar = date('Y-m-d',strtotime('+3 Month',strtotime($newtglmasuk)));
// 		$str="update ".$dbname.".datakaryawan set 
// 				tanggalmasuk='".$newtglmasuk."',
// 				tanggalkeluar='".$newtglkeluar."' where karyawanid='".$r->karyawanid."'";
			
// 		 try {
// 			 $owlPDO->exec($str); 
// 			 // History
// 				 $dataChange	= array(
// 								'tanggalmasuk'=>$tglmasuk,
// 								'tanggalkeluar'=>$tglkeluar,
// 								'tipekaryawan'=>'KHL',
// 								'statuskaryawan'=>'Aktif',
// 								'keterangan'=>'Otomatis KHL Update Tanggal Masuk dan keluar per 3 bulan'
// 								);
						
// 				if(!empty($dataChange)) {
// 					$dataHist = array(
// 							'updatetime' => date('Y-m-d H:i:s'),
// 							'updateby' => '',
// 							'karyawanid' => $r->karyawanid,
// 							'data' => json_encode($dataChange)
// 					);
// 					$qHist = insertQuery($dbname,'hist_datakaryawan',$dataHist);
// 					$owlPDO->exec($qHist);
// 				}
// 			}catch (PDOException $e) {
// 				//print " Gagal  !: " . $e->getMessage() . "\n";
// 			}
// 	}	
// }

/** END */
	
// $tglskrg = date('Y-m-d');
// $tglresetcuti = date('Y')."-01-01";

// if($tglskrg == $tglresetcuti)
// {
// 	$str1 = "select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan where tanggalmasuk<>'0000-00-00' and (COALESCE(ROUND(DATEDIFF('".$tglskrg."',tanggalmasuk)/365.25,3),0)) >= 2 and (tanggalkeluar='0000-00-00' or tanggalkeluar>".date('Ymd').") and tipekaryawan in(0,2,3,4,5,7,8)";
// 	$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
// 	$res->setFetchMode(PDO::FETCH_OBJ);
// 	while($bar1=$res->fetch())
// 	{
// 		$hakcuti = 0;
// 		$kodeorg=$bar1->lokasitugas;
// 		$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$bar1->lokasitugas."'");
		
// 		if($optTipe[$kodeorg]=='HOLDING')
// 		{
// 			$hakcuti = 12;
// 		}
// 		else
// 		{
// 			if(($bar1->tipekaryawan)==0)
// 			{
// 				$hakcuti = 17;
// 			}
// 			else
// 			{
// 				$hakcuti = 12;
// 			}
// 		}
		
// 		$saldo = $hakcuti;
		
// 		$dari = (date('Y')-1)."-01-01";
// 		$sampai = (date('Y')-1)."-12-31";
		
// 		#periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
// 		$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt where karyawanid=".$bar1->karyawanid." and  daritanggal >= '".$dari."' and daritanggal<= '".$sampai."'";
// 		$diambil=0;#sudah diambil diambil tahun ini
// 		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
// 		$resx->setFetchMode(PDO::FETCH_OBJ);   
// 		while($barx=$resx->fetch())
// 		{
// 			$diambil=$barx->diambil;
// 			if($diambil=='')
// 				$diambil=0;
// 		}
		
// 		$saldo=$saldo-$diambil;
		
// 		#================================================================
// 		#maka insert periode baru
// 		$sCek="select * from ".$dbname.".sdm_cutiht where karyawanid='".$bar1->karyawanid."' and periodecuti='".date('Y')."'";
// 		$rCek=fetchData($sCek);
// 		if(count($rCek)!=0)
// 		{
// 			$str="update ".$dbname.".sdm_cutiht set 
// 				dari='".$dari."',
// 				sampai='".$sampai."',
// 				hakcuti=".$hakcuti.",
// 				diambil=".$diambil.",
// 				sisa=".$saldo."
// 			where 
// 				kodeorg='".$bar1->lokasitugas."'
// 				and karyawanid=".$bar1->karyawanid."
// 				and periodecuti='".date('Y')."'";  
// 		   $owlPDO->exec($str); 
// 		}
// 		else
// 		{
// 			$str1="insert into ".$dbname.".sdm_cutiht(kodeorg,`karyawanid`,`periodecuti`,`dari`,`sampai`,`hakcuti`,`sisa`,`diambil`) values('".$bar1->lokasitugas."',".$bar1->karyawanid.",'".date('Y')."','".$dari."','".$sampai."',".$hakcuti.",".$saldo.",0)";
// 			$owlPDO->exec($str1);
// 		} 
// 	}
// }
// else
// {
// 	$str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan where  tanggalmasuk<>'0000-00-00'  and tanggalmasuk<".date('Ymd')." and (COALESCE(ROUND(DATEDIFF('".$tglskrg."',tanggalmasuk)/365.25,3),0)) < 2 and (tanggalkeluar='0000-00-00' or tanggalkeluar>".date('Ymd').") and tipekaryawan in(0,2,3,4,5,7,8)";
// 	$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
// 	$res->setFetchMode(PDO::FETCH_OBJ);
// 	while($bar1=$res->fetch())
// 	{

// 		$awal = date_create($bar1->tanggalmasuk);
// 		$skrg = date_create();
// 		$diffskrg = date_diff($awal,$skrg);
// 		$tpkar = $bar1->tipekaryawan;
		
// 		switch($optTipe[$kodeorg])
// 		{
// 			case'HOLDING':
// 				$dari = '';
// 				$sampai = '';
				
// 				$dari = date('Y-m-d', strtotime('+3 month', strtotime($bar1->tanggalmasuk)));
// 				$sampai = date('Y-m-d');
				
// 				if((($diffskrg->m) < 3) && ($diffskrg->y) < 1)
// 				{
// 					$hakcuti = 0;
// 				}
// 				else
// 				{
// 					if(($diffskrg->y) > 0)
// 					{
// 						$hakcuti = (($diffskrg-y)*12) + (($diffskrg-m));
// 					}
// 					else
// 					{
// 						$hakcuti = ($diffskrg->m);
// 					}
// 				}
// 			break;
			
// 			default:
// 				$dari = '';
// 				$sampai = '';
				
// 				$dari = date('Y-m-d', strtotime('+3 month', strtotime($bar1->tanggalmasuk)));
// 				$sampai = date('Y-m-d');
					
// 				if((($diffskrg->m) < 3) && ($diffskrg->y) < 1)
// 				{
// 					$hakcuti = 0;
// 				}
// 				else
// 				{
// 					if($tpkar==0)
// 					{
// 						if(($diffskrg->y) > 0)
// 						{
// 							$hakcuti = number_format((17 * ($diffskrg->y)) + (($diffskrg->m) * (17/11)));
// 						}
// 						else
// 						{
// 							$hakcuti = number_format((17/11) * ($diffskrg->m));
// 						}
// 					}
// 					else
// 					{
// 						if(($diffskrg->y) > 0)
// 						{
// 							$hakcuti = (($diffskrg-y)*12) + (($diffskrg-m));
// 						}
// 						else
// 						{
// 							$hakcuti = ($diffskrg->m);
// 						}
// 					}
// 				}
// 			break;
// 		}
		
// 		$saldo = $hakcuti;
		
// 		#periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
// 		$strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt where karyawanid=".$bar1->karyawanid." and  daritanggal >='".$dari."' and daritanggal<='".$sampai."'";
// 		$diambil=0;#sudah diambil diambil tahun ini
// 		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
// 		$resx->setFetchMode(PDO::FETCH_OBJ);   
// 		while($barx=$resx->fetch())
// 		{
// 			$diambil=$barx->diambil;
// 			if($diambil=='')
// 				$diambil=0;
// 		}
		
// 		$saldo=$saldo-$diambil;
		
// 		#================================================================
// 		#maka insert periode baru
// 		$sCek="select * from ".$dbname.".sdm_cutiht where karyawanid='".$bar1->karyawanid."' and periodecuti='".date('Y')."'";
// 		$rCek=fetchData($sCek);
// 		if(count($rCek)!=0)
// 		{
// 			$str="update ".$dbname.".sdm_cutiht set 
// 				dari='".$dari."',
// 				sampai='".$sampai."',
// 				hakcuti=".$hakcuti.",
// 				diambil=".$diambil.",
// 				sisa=".$saldo."
// 			where 
// 				kodeorg='".$bar1->lokasitugas."'
// 				and karyawanid=".$bar1->karyawanid."
// 				and periodecuti='".date('Y')."'";  
// 		   $owlPDO->exec($str); 
// 		}
// 		else
// 		{
// 			$str1="insert into ".$dbname.".sdm_cutiht(kodeorg,`karyawanid`,`periodecuti`,`dari`,`sampai`,`hakcuti`,`sisa`,`diambil`) values('".$bar1->lokasitugas."',".$bar1->karyawanid.",'".date('Y')."','".$dari."','".$sampai."',".$hakcuti.",".$saldo.",0)";
// 			$owlPDO->exec($str1);
// 		}
// 	}	
// }

##########################################################################################################################
#komenn

// $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while($bar1=$res->fetch())
// {
	// //=================================
    // //default
    // $x=readTextFile('config/jumlahcuti.lst');
    // if(intval($x)>0)
		// $hakcuti=$x;
	// else
		// $hakcuti=12;  //default
	// //=================================
	
	// $tgl=substr(str_replace("-","",$bar1->tanggalmasuk),4,4);		
    // $dari=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),date('Y'));
    // $dari=date('Ymd',$dari);
    // $sampai=mktime(0,0,0,substr($tgl,0,2),substr($tgl,2,2),date('Y')+1);		
    // $sampai=date('Ymd',$sampai);
	
	// #jika periode masuk masih belum 1tahun maka 0
    // $d=substr(str_replace("-","",$bar1->tanggalmasuk),0,4);
	
	// //Masa kerja
    // $date1 = $bar1->tanggalmasuk;
    // $date2 = date('Y-m-d');
    // $diff = abs(strtotime($date2) - strtotime($date1));
    // $years = floor($diff / (365 * 60 * 60 * 24));
    // $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    // @$blnMasa=$months/12;
	
	// #ambil sisa cuti YBS
    // $str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$bar1->karyawanid." and periodecuti>".(date('Y')-2)." order by periodecuti desc limit 1";
	// $resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $resx->setFetchMode(PDO::FETCH_OBJ);
	// $sisalalu=0;
    // while($barx=$resx->fetch())
	// {
		// $sisalalu=$barx->sisa;
	// }
	
	// #periksa apakah sudah ada pada periode yang sama
	// $str="select * from ".$dbname.".sdm_cutiht where karyawanid=".$bar1->karyawanid." and periodecuti=".date('Y')." order by periodecuti desc limit 1";
	// echo $str."<br>";
	// $resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    // $resy->setFetchMode(PDO::FETCH_OBJ);        
    // $numrows=owlBaris($resy);
	
	// if($numrows>0)
	// {
		// #berarti  saldo saat ini adalah sisalalu
        // #$saldo=$sisalalu;
        // #tidak ada perubahan
	// }
	// else
	// {
		// if($years==0)
		// {
			// $hakcuti=0;//tidak ikut diproses
		// }
		
		// if(($bar1->karyawanid=='0')||($bar1->karyawanid=='7')||($bar1->karyawanid=='8'))
		// {
			// if($years!=0)
			// {
				// $kodeorg=$bar1->lokasitugas;
                // $whrdt="kodeorganisasi='".$kodeorg."'";
                // $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whrdt);
				
				// switch($optTipe[$kodeorg])
				// {
					// case'HOLDING':
						// $kelipantan = 'CTKLPTNHO';
        				// $hakct='CTHKHO';
        				// if(($tipekaryawan!=0)||($tipekaryawan!=7)||($tipekaryawan!=8))
						// {
							// $hakcuti=12;
							// break;
                        // }
						
						// $optKelipatan=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kelipantan."'");
						// $optHakcuti=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$hakct."'");
						
						// if(($years/$optKelipatan[$kelipantan])==1)
						// {
							// $thnMasa=$optKelipatan[$kelipantan]+$blnMasa;//mencari total masa kerja
							// if($thnMasa>=intval($optKelipatan[$kelipantan])&&$thnMasa<intval(($optKelipatan[$kelipantan]+1)))
							// {
								// $hakcuti=$optHakcuti[$hakct];
							// }
						// }
						// else
						// {
							// if(($years%$optKelipatan[$kelipantan])==0)
							// {
								// $thnMasa=$optKelipatan[$kelipantan]+$blnMasa;//mencari total masa kerja  
							// }
							// else
							// {
								// $thnMasa=$years+$blnMasa;//mencari total masa kerja  
							// }
							
							// if($thnMasa>=intval($optKelipatan[$kelipantan])&&$thnMasa<intval(($optKelipatan[$kelipantan]+1)))
							// {
								// $hakcuti=$optHakcuti[$hakct];
							// }
						// }
					// break;
					
					// case'KEBUN':
						// $kelipantan = 'CTKLPTNKB';
        				// $hakct='CTHKKB';
						
						// $optKelipatan=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kelipantan."'");
        				// $optHakcuti=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$hakct."'");
        				// if(($years%$optKelipatan[$kelipantan])==0)
						// {
							// $thnMasa=$optKelipatan[$kelipantan]+$blnMasa;//mencari total masa kerja  
						// }
						// else
						// {
							// $thnMasa=$years+$blnMasa;//mencari total masa kerja  
						// }
						
						// if($thnMasa>=intval($optKelipatan[$kelipantan])&&$thnMasa<intval(($optKelipatan[$kelipantan]+1)))
        				// {
							// $hakcuti=$optHakcuti[$hakct];
						// }   
					// break;
					
					// case'PABRIK':
						// $kelipantan = 'CTKLPTNPB';
        				// $hakct='CTHKPB';
						
						// $optKelipatan=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kelipantan."'");
        				// $optHakcuti=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$hakct."'");
					// break;
					
					// case'KANWIL':
						// $kelipantan = 'CTKLPTNRO';
        				// $hakct='CTHKRO';
						
						// $optKelipatan=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kelipantan."'");
						// $optHakcuti=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$hakct."'");
						
						// if(($years/$optKelipatan[$kelipantan])===1)
						// {
							// $thnMasa=$years+$blnMasa;//mencari total masa kerja
        					// if($thnMasa>=intval($optKelipatan[$kelipantan])&&$thnMasa<intval(($optKelipatan[$kelipantan]+1)))
        					// {
								// $hakcuti=$optHakcuti[$hakct];
							// }
						// }
						// else
						// {
							// if(($years%$optKelipatan[$kelipantan])==0)
							// {
								// $thnMasa=$optKelipatan[$kelipantan]+$blnMasa;//mencari total masa kerja  
							// }
							// else
							// {
								// $thnMasa=$years+$blnMasa;//mencari total masa kerja  
							// }
							
							// if($thnMasa>=intval($optKelipatan[$kelipantan])&&$thnMasa<intval(($optKelipatan[$kelipantan]+1)))
        					// {
								// $hakcuti=$optHakcuti[$hakct];
							// }
						// }
					// break;
				// }
			// }
		// }
	// }
	
	// $saldo=$hakcuti;
	
	// #periksa apakah sudah ada pengambilan cuti sebelum ada header (sebelum cuti baru muncul)
	// $strx="select sum(jumlahcuti) as diambil from ".$dbname.".sdm_cutidt where karyawanid=".$bar1->karyawanid." and  daritanggal >=".$dari." and daritanggal<=".$sampai;
	// $diambil=0;#sudah diambil diambil tahun ini
    // $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
    // $resx->setFetchMode(PDO::FETCH_OBJ);   
    // while($barx=$resx->fetch())
	// {
		// $diambil=$barx->diambil;
        // if($diambil=='')
			// $diambil=0;
	// }
	
	// $saldo=$saldo-$diambil;    

	
	
	
	// #================================================================
    // #maka insert periode baru
	// $sCek="select * from ".$dbname.".sdm_cutiht where karyawanid='".$bar1->karyawanid."' and periodecuti='".date('Y')."'";
    // $rCek=fetchData($sCek);
    // if(count($rCek)!=0)
	// {
		// $str="update ".$dbname.".sdm_cutiht set 
			// dari=".$dari.",
            // sampai=".$sampai.",
            // hakcuti=".$hakcuti.",
            // diambil=".$diambil.",
            // sisa=".$saldo."
		// where 
			// kodeorg='".$bar1->lokasitugas."'
            // and karyawanid=".$bar1->karyawanid."
            // and periodecuti='".date('Y')."'";  
	   // // $owlPDO->exec($str); 
	// }
	// else
	// {
		// $str1="insert into ".$dbname.".sdm_cutiht(kodeorg,`karyawanid`,`periodecuti`,`dari`,`sampai`,`hakcuti`,`sisa`,`diambil`) values('".$bar1->lokasitugas."',".$bar1->karyawanid.",'".date('Y')."',".$dari.",".$sampai.",".$hakcuti.",".$saldo.",0)";
		// // $owlPDO->exec($str1);
	// } 
// }   




##Transaksi Rutin
/*
Jika tipe waktu bulanan, otomatis terbentuk tagihan dan jurnal.
selama pembayaran sewa/asuransi berlangsung.
*/

// Get Data
$qTrans = "SELECT * FROM ".$dbname.".keu_transaksi_rutin WHERE tipewaktu='BULANAN' and posting=1 and right(tanggalposting,2)='".date('d')."' and '".substr($tglskrg,0,7)."' between left(tanggalmulai,7) and left(tanggalselesai,7)";
$data = fetchData($qTrans);
foreach ($data as $key => $bar) {

	//get kodeorg
    $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
    $ressup=$owlPDO->query($sqlkd);
    $ressup->setFetchMode(PDO::FETCH_ASSOC);
    $barsup=$ressup->fetch();
    $induk=$barsup['induk'];

    $tipetransaksi=$bar['tipe_transaksi'];
    if ($bar['tipe_transaksi']=='OTHERS') {
        $tipetransaksi="KOPERASI";
    }

	//get noakun supplier
    $ressup=$owlPDO->query("select noakun from ".$dbname.".log_5supkelompok where supplierid='".$bar['supplierid']."' and tipe='".$tipetransaksi."' ");
    $ressup->setFetchMode(PDO::FETCH_ASSOC);
    $barsup=$ressup->fetch();
    $akunkredit=$barsup['noakun'];
    $totperbulan=$bar['harga_barang']/$bar['tenor'];

    //noaruskas dan ket
    @$datadt=getArusKasket($bar['noakun_debet']);
    @$datadt=explode('##', $datadt);
    $noaruskas=$datadt[0];
    $ket=$datadt[1];

    $noinvoice=date('Ymdhis');
    $tipeinvoice=$bar['jenistipe'];

    #Supplier
    $whrsup="supplierid='".$bar['supplierid']."'";
    $optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
    $keterangan2='pengakuan hutang '.strtolower($bar['tipe_transaksi']).' atas '.$optsup[$bar['supplierid']].'/'.$bar['keterangan'];

    $insht="insert into ".$dbname.".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values 
    ('".$noinvoice."','".$tipeinvoice."','".date('Y-m-d')."','".$bar['notransaksi']."','".$bar['supplierid']."','".$totperbulan."','','".$keterangan2."','".$akunkredit."','IDR','1','1','".$induk."','".$bar['kodeorg']."','".$bar['createdby']."','".$bar['createdby']."')";
    try {
        $owlPDO->exec($insht);

        $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values 
          ('".$noinvoice."','".$bar['noakun_debet']."','".$totperbulan."','','','".$noaruskas."','".$ket."')";
        try{
            $owlPDO->exec($ins);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    } catch (PDOException $e) {
        print " Gagal: " . $e->getMessage() . "\n";
        die();
    }

    $kodejurnal="TGH01";  
    $tgljurnal=str_replace("-", "", $tglskrg);

    # Get Journal Counter
    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
        "kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
    $tmpKonter = fetchData($queryJ);
    $konter = addZero($tmpKonter[0]['nokounter']+1,3);
    # Prep No Jurnal
    $notrans=$tgljurnal."/".$bar['kodeorg']."/".$kodejurnal."/".$konter;
    

    $i = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
    values ('".$notrans."','".$kodejurnal."','".$totperbulan."','".(-1)*($totperbulan)."','".$tgljurnal."','".date('Ymd')."','1','".$noinvoice."','IDR','1')";
    try{
        $owlPDO->exec($i);

        $i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,kodesupplier)
        values ('".$notrans."','".$tgljurnal."','1','".$bar['noakun_debet']."','".$keterangan2."','".$totperbulan."','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$bar['notransaksi']."','".$bar['supplierid']."')";
        try{
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

        $i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,kodesupplier)
        values ('".$notrans."','".$tgljurnal."','2','".$akunkredit."','".$keterangan2."','" .(-1)*($totperbulan). "','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$bar['notransaksi']."','".$bar['supplierid']."')";
        try{
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

        $strht="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
        try{
            $owlPDO->exec($strht);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
        
    } catch (PDOException $e) {
        print " Gagal: " . $e->getMessage() . "\n";
        die();
    }
	
}

#perhitungan absensi
#cek tgl masuk yang kosong,jika kosong dan sudah pernah absensi pertama kali maka insert
$sListTrans="select notransaksi,tgl_masuk,karyawanid,tanggalkembali from ".$dbname.".sdm_pjdinasht where tanggalbuat like '".date("Y")."%' and statuspersetujuan=1 and tgl_masuk='0000-00-00' and posting=0";
$rListTrans=fetchData($sListTrans);
foreach($rListTrans as $row=>$data){
	$sCek="select * from ".$dbname.".sdm_absensidt where tanggal>'".$data['tanggalkembali']."' and karyawanid='".$data['karyawanid']."' order by tanggal asc limit 1";
	$rCek=fetchData($sCek);
	if(count($rCek)!=0){
		$sUpdate="update ".$dbname.".sdm_pjdinasht set tgl_masuk='".$rCek[0]['tanggal']."' where karyawanid='".$data['karyawanid']."' and notransaksi='".$data['notransaksi']."'";
		try{ $owlPDO->exec($sUpdate); }catch (PDOException $e){ echo "Error : ".$sUpdate."__".$e->getMessage(); die(); }
	}
}
$dt=array();
$dttrans=array();
#cek tgl masuk yang gak kosong bandingkan dengan total absensinya
$sListTrans2="select notransaksi,tgl_masuk,karyawanid,tanggalkembali,uangmuka,kodeorg from ".$dbname.".sdm_pjdinasht where tanggalbuat like '".date("Y")."%' and statuspersetujuan=1 and tgl_masuk<>'0000-00-00' and posting=0";
$rListTrans2=fetchData($sListTrans2);
foreach($rListTrans2 as $row=>$data){
	$sCek="select * from ".$dbname.".sdm_absensidt where tanggal between '".$data['tgl_masuk']."' and '".date('Y-m-d')."' and karyawanid='".$data['karyawanid']."'";
	$rCek=fetchData($sCek);
	if(count($rCek)==7){
		$dttrans[$data['notransaksi']]=$data['notransaksi'];
		$dt[$data['notransaksi']]['karyawanid']=$data['karyawanid'];
		$dt[$data['notransaksi']]['uangmuka']=$data['uangmuka'];
		$dt[$data['notransaksi']]['kodeorg']=$data['kodeorg'];
	}
}

$awalan=0;
$str=array();
if(count($dttrans)!=0){

	foreach ($dttrans as $notransaksi){

		if ($dt[$notransaksi]['uangmuka']==0) {
			continue;
		}

		$sCek="select nojurnal from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
		$rCek=fetchData($sCek);
		if(count($rCek)==0){

			//Parameter jurnal noakun debet dan kredit
			$strdt="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='PJPD' and kodeaplikasi='PJDDK'";
			$res=fetchData($strdt);
			$bar=$res[0];
			$noakundebet=$bar['noakundebet'];
			$noakunkredit=$bar['noakunkredit'];

			//Parameter jurnal noakun debet dan kredit
			$strdt="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$dt[$notransaksi]['kodeorg']."' and sudahproses=0 order by periode desc limit 1";
			$res=fetchData($strdt);
			$bar=$res[0];
			$periodegaji=$bar['periode'];

			//Parameter jurnal noakun debet dan kredit
			$strdt="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='IDPOTPD'";
			$res=fetchData($strdt);
			$bar=$res[0];
			$komp=$bar['nilai'];

			//get induk
			$sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$dt[$notransaksi]['kodeorg']."'";
			$ressup=$owlPDO->query($sqlkd);
			$ressup->setFetchMode(PDO::FETCH_ASSOC);
			$barsup=$ressup->fetch();
			$induk=$barsup['induk'];

			//data create jurnal
			$strup="select karyawanid from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='PJDINAS' and level=3";
			$resup=fetchData($strup);
			$barup=$resup[0];
			$create=$barup['karyawanid'];

			$kodejurnal="PJPD";  
			$tgljurnal=date('Ymd');
			$ket1="Jurnal Otomatis Atas PJ Perdin";
			$ket="Jurnal Otomatis Atas Pertanggungjawaban Perjalanan Dinas berdasarkan notransaksi:".$notransaksi;

			# Get Journal Counter
			$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			    "kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
			$tmpKonter = fetchData($queryJ);
			if($awalan==0){
				$konter = addZero($tmpKonter[0]['nokounter']+1,3);
			}else{
				$awalan=1;
				$konter = addZero(intval($konter)+1,3);
			}
			
			# Prep No Jurnal
			$notrans=$tgljurnal."/".$dt[$notransaksi]['kodeorg']."/".$kodejurnal."/".$konter;
			
			//insert potongan gaji
			$str[]="insert into ".$dbname.".sdm_potongandt (kodeorg,tipepotongan,periodegaji,nik,jumlahpotongan,keterangan,hk,updateby) 
					values ('".$dt[$notransaksi]['kodeorg']."','".$komp."','".$periodegaji."','".$dt[$notransaksi]['karyawanid']."','".$dt[$notransaksi]['uangmuka']."','".$ket1."','0','".$create."')";

			//insert jurnalht
			$str[]="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
					values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$notransaksi."','IDR','1')";

			//insert jurnalht debet
			$str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
			values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$dt[$notransaksi]['uangmuka']."','IDR','1','".$dt[$notransaksi]['kodeorg']."','".$notransaksi."','".$notransaksi."')";

			//insert jurnalht kredit
			$str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
			values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($dt[$notransaksi]['uangmuka'])."','IDR','1','".$dt[$notransaksi]['kodeorg']."','".$notransaksi."','".$notransaksi."')";

			//update status posting jadi 2
			$str[]="update ".$dbname.".sdm_pjdinasht set posting=2 where karyawanid='".$dt[$notransaksi]['karyawanid']."' and notransaksi='".$notransaksi."'";

		}

	}

}

// echo"<pre>";
// print_r($str);
// echo"</pre>";
if(count($str)!=0){
	for($i=0; $i<count($str); $i++){
		try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
	}	
	
	//update counter kelompok jurnal
	$strup="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
	try{ $owlPDO->exec($strup); }catch (PDOException $e){ echo "Error : ".$strup."__".$e->getMessage(); die(); }
	
}


##Leasing
/*
Jika tanggal angsuran=tanggal hari ini, otomatis terbentuk kasbank.
*/
// Get Data
$qTrans = "SELECT * FROM ".$dbname.".keu_leasinght a left join ".$dbname.".keu_leasingdt b on a.notransaksi=b.notransaksi WHERE b.tgl_transaksi='".$tglskrg."' and b.statuskasbank=0 and a.posting=1 and tenor_ke!=1 and a.metode_bayar in ('Cheque','Giro','Transfer')";
$data = fetchData($qTrans);
foreach ($data as $key => $bar) {

	$bunga=0;
	$angsuran=0;
	// Get angsuran dan bunga per angsuran
	// $sisatenor=intval($bar['tenor'])-1;
	// $bunga=intval($bar['bunga_pertahun'])/$sisatenor;
	$sisatenor=$bar['tenor']-1;
    $bunga=$bar['bunga_pertahun']/$sisatenor;
	$angsuran=intval($bar['angsuran'])-$bunga;

	// Get Tipe Transaksi (Bank / Kas)
	$tipe = "";
	$notransaksikb = "";
	$jurnalid="TRLE";
    $whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid='BK'";
    $queryAKB = selectQuery($dbname,'keu_5parameterjurnal','jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',$whereAKB);
    $optAKB = fetchData($queryAKB);
    $row = $optAKB[0];
    $kodejurnal=$tipe = $row['jurnalid'];

	// Get Last Transaction
    $keterangan="Pembayaran angsuran ke - ".$bar['tenor_ke']." atas no.kontrak leasing ".$bar['nokontrak_leasing'];
	$tglinput=str_replace('-','',$bar['tgl_transaksi']);
	$bulan=substr($tglinput,4,2);
	$tahun=substr($tglinput,0,4);
    $noTrans = $tglinput."/".$bar['kodeorg']."/".$tipe."/";
    $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
    $resTrans = fetchData($qTrans);
    if(empty($resTrans)) {
        $notransaksikb = $noTrans."00001";
    } else {
        $tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
        $tmpTrans++;
        $notransaksikb = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
    }

    //get noakun bank
    $str1="select noakundebet,noakunkredit,sampaidebet from ".$dbname.".keu_5parameterjurnal where jurnalid='".$jurnalid."'";
    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $qtr->setFetchMode(PDO::FETCH_ASSOC);
    $rtr=$qtr->fetch();
    $noakundebet=$rtr['noakundebet'];
    $sAkunDrNotadbt="select * from ".$dbname.".keu_notadebet_dt where notadebet='".$bar['notadebet']."' and kodesupplierdt='".$bar['supplierid_leasing']."'";
    $rAkunDrNotadbt=fetchData($sAkunDrNotadbt);
    if(count($rAkunDrNotadbt)!=0){
    	$noakundebet=$rAkunDrNotadbt[0]['noakun'];
    }
    
    $sampaidebet=$rtr['sampaidebet'];
    $noakunkredit=$rtr['noakunkredit'];

    //get noaruskas hutang leasing
    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakundebet."'";
    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $qtr->setFetchMode(PDO::FETCH_ASSOC);
    $rtr=$qtr->fetch();
    $noaruskas=$rtr['noaruskas'];

    //get noaruskas bunga
    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$sampaidebet."'";
    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $qtr->setFetchMode(PDO::FETCH_ASSOC);
    $rtr=$qtr->fetch();
    $noaruskasbunga=$rtr['noaruskas'];

    //get keterangan hutang leasing
    $str1="select id_ket,keterangan from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskas."'";
    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $qtr->setFetchMode(PDO::FETCH_ASSOC);
    $rtr=$qtr->fetch();
    $keterangantemp=$rtr['id_ket'];
    $keterangan2=$rtr['keterangan'].' '.numToMonth($bulan,'I','long').' '.$tahun;

    //get keterangan bunga
    $str1="select id_ket,keterangan from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasbunga."'";
    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $qtr->setFetchMode(PDO::FETCH_ASSOC);
    $rtr=$qtr->fetch();
    $keterangantempbunga=$rtr['id_ket'];
    $keterangan2bunga=$rtr['keterangan'].' '.numToMonth($bulan,'I','long').' '.$tahun;

	$insht="insert into ".$dbname.".keu_kasbankht (`notransaksi`,`noakun`,`tanggalinput`,`matauang`,`kurs`,`tipetransaksi`,`jumlah`,`cgttu`,`keterangan`,`yn`,`kodeorg`,`nocek`,`hutangunit`,`pemilikhutang`,`noakunhutang`,`bayarkepada`,`rekening`,`tanggal`,`userid`,`posting`) values 
    ('".$notransaksikb."','".$noakunkredit."','".$tglinput."','IDR','1','K','".$bar['angsuran']."','".$bar['metode_bayar']."','".$keterangan."','0','".$bar['kodeorg']."','".$bar['no_bukucek']."','0','','','','".$bar['norekening_bank']."','".$bar['tgl_transaksi']."','".$bar['createdby']."','1')";
    try {
        $owlPDO->exec($insht);

        $ins="insert into ".$dbname.".keu_kasbankdt (`tanggal`,`kode`,`keterangan1`,`noaruskas`,`noakun`,`matauang`,`kurs`,`keterangan2temp`,`bulan`,`tahun`,`jumlah`,`kodesegment`,`kodekegiatan`,`kodeasset`,`nik`,`kodecustomer`,`kodesupplier`,`kodevhc`,`orgalokasi`,`nodok`,`hutangunit1`,`notransaksi`,`kodeorg`,`noakun2a`,`tipetransaksi`,`keterangan2`) values
          ('".$tglinput."','BK','".$bar['notransaksi']."','".$noaruskas."','".$noakundebet."','IDR','1','".$keterangantemp."','".$bulan."','".$tahun."','".$angsuran."','','','','','','".$bar['supplierid_leasing']."','','0','".$bar['nokontrak_leasing']."','0','".$notransaksikb."','".$bar['kodeorg']."','".$noakunkredit."','K','".$keterangan2."')";
        try{
            $owlPDO->exec($ins);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
        if($bunga!=0){
        	 $ins="insert into ".$dbname.".keu_kasbankdt (`tanggal`,`kode`,`keterangan1`,`noaruskas`,`noakun`,`matauang`,`kurs`,`keterangan2temp`,`bulan`,`tahun`,`jumlah`,`kodesegment`,`kodekegiatan`,`kodeasset`,`nik`,`kodecustomer`,`kodesupplier`,`kodevhc`,`orgalokasi`,`nodok`,`hutangunit1`,`notransaksi`,`kodeorg`,`noakun2a`,`tipetransaksi`,`keterangan2`) values
	          ('".$tglinput."','BK','".$bar['notransaksi']."','".$noaruskasbunga."','".$sampaidebet."','IDR','1','".$keterangantempbunga."','".$bulan."','".$tahun."','".$bunga."','','','','','','".$bar['supplierid_leasing']."','','0','".$bar['nokontrak_leasing']."','0','".$notransaksikb."','".$bar['kodeorg']."','".$noakunkredit."','K','".$keterangan2bunga."')";
	        try{
	            $owlPDO->exec($ins);
	        } catch (PDOException $e) {
	            print " Gagal: " . $e->getMessage() . "\n";
	            die();
	        }
        }
       

    } catch (PDOException $e) {
        print " Gagal: " . $e->getMessage() . "\n";
        die();
    }

    //get induk
	$sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
	$ressup=$owlPDO->query($sqlkd);
	$ressup->setFetchMode(PDO::FETCH_ASSOC);
	$barsup=$ressup->fetch();
	$induk=$barsup['induk'];

	$tgljurnal=$tglinput;
	$ket="Jurnal Otomatis Atas Pembayaran Angsuran Leasing ke - ".$bar['tenor_ke']." atas no.kontrak leasing ".$bar['nokontrak_leasing'];

	# Get Journal Counter
	$awalan=0;
	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
	$tmpKonter = fetchData($queryJ);
	if($awalan==0){
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);
	}else{
		$awalan=1;
		$konter = addZero(intval($konter)+1,3);
	}
	
	# Prep No Jurnal
	$notrans=$tgljurnal."/".$bar['kodeorg']."/".$kodejurnal."/".$konter;

	//insert jurnalht
	$strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
			values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$notransaksikb."','IDR','1')";
	try{

        $owlPDO->exec($strht);

        $str=array();
        //insert jurnalht debet
		$str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
		values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$angsuran."','IDR','1','".$bar['kodeorg']."','".$notransaksikb."','".$bar['notransaksi']."')";
		if($bunga!=0){
			 //insert jurnalht debet
			$str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
			values ('".$notrans."','".$tgljurnal."','2','".$sampaidebet."','".$ket."','".$bunga."','IDR','1','".$bar['kodeorg']."','".$notransaksikb."','".$bar['notransaksi']."')";

		}
       
		//insert jurnalht kredit
		$str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
		values ('".$notrans."','".$tgljurnal."','3','".$noakunkredit."','".$ket."','".-($bar['angsuran'])."','IDR','1','".$bar['kodeorg']."','".$notransaksikb."','".$bar['notransaksi']."')";

		//update statuskasbank jadi 1
		$str[]="update ".$dbname.".keu_leasingdt set statuskasbank=1 where tenor_ke='".$bar['tenor_ke']."' and notransaksi='".$bar['notransaksi']."'";
		if($bar['no_bukucek']!=''){
			//update nocek jadi 1
			$str[]="update ".$dbname.".keu_bukucekdt set status_cek=1,notransaksi='".$notransaksikb."' where nocek='".$bar['no_bukucek']."' and notransaksi='".$bar['notransaksi']."'";	
		}
		

		$str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
	    
		if ($bar['tenor']==$bar['tenor_ke']) {
			//update statuskasbank jadi 1
			$str[]="update ".$dbname.".keu_leasinght set status_leasing=2 where notransaksi='".$bar['notransaksi']."'";
		}

		// echo"<pre>";
		// print_r($str);
		// echo"</pre>";

	    if(count($str)!=0){
			for($i=0; $i<count($str); $i++){
				try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
			}	
		}

    }catch (PDOException $e){
        echo "Gagal : ".$e->getMessage();
        die();
    }

}



?>