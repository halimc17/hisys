<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$method      = checkPostGet('method', '');
$kodeorg     = checkPostGet('kodeorg', '');
$periode     = checkPostGet('periode', '');
$periodebyr  = checkPostGet('periodebyr', '');
$kud      = checkPostGet('kud', '');
$nilaipph      = checkPostGet('nilaipph', '');
$idkav      = checkPostGet('idkav', '');
$nama      = checkPostGet('nama', '');
$baris      = checkPostGet('baris', '');
$notransaksi      = checkPostGet('notransaksi', '');
$jenistampil      = checkPostGet('jenis', '');

$divsch      = checkPostGet('divsch', '');
$tglsch      = checkPostGet('tglsch', '');
$jab = getPostingJabatan('prosespphpetani');

$kodept=makeOption($dbname,'organisasi','kodeorganisasi,induk',"length(kodeorganisasi)='4'");



$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch ($method) {

	case'getnotrans':
			$whr=" and periode = '".$periode."'";
			$cek2 = "select max(right(notransaksi,3)) as nomor from " . $dbname . ".kebun_3pph_petani where kodeorg = '".$kodeorg."' ".$whr."";
			
			$rcek2 = fetchdata($cek2);
			$nourut=$rcek2[0]['nomor'];

			if ($nourut==0) {
				$nourut=1;
			}
			else
			{
				$nourut=$nourut+1;
			}
			$tahun=substr($periode,0,4);
			$bulan=substr($periode,5,2);

			$nourut = str_pad($nourut, 3, "0", STR_PAD_LEFT);		
			$notransaksi=$kodeorg.'/'.$tahun.$bulan.'/'.$nourut;

			echo $notransaksi;
	break;
	case'insert':
	
	try {
		$owlPDO->beginTransaction();
		$tahun=substr($periode,0,4);
		$bulan=substr($periode,5,2);
		$whr=" and periode = '".$periode."'";
		$cek = "select * from " . $dbname . ".kebun_3pph_petani where kodeorg = '".$kodeorg."' and kode_supplier='".$kud."' ".$whr." and posting='1'";
		$rcek = fetchdata($cek);
		if(count($rcek)>0){			
			throw new PDOException("Transaksi sudah pernah di posting.");
		}
		if($baris=='1'){			
			$str = "delete from " . $dbname . ".kebun_3pph_petani where kodeorg = '".$kodeorg."' and kode_supplier='".$kud."' and notransaksi like '%".$tahun.$bulan."%'";
			$owlPDO->exec($str);	


			
		}

		if ($nilaipph>0) {
			//sd1e/202205/01/pph/001
			$optnm = makeOption($dbname, 'kebun_5kavling', 'no_kavl,nama',"no_kavl='".$idkav."'");
			$datafee = array(
				'notransaksi'   => $notransaksi,
				'kodeorg'       => $kodeorg,
				'periode'       => $periode,
				'tipe'      	=> $periodebyr,
				'kode_supplier' => $kud,
				'no_kavl'       => $idkav,
				'nama_petani'   => $nama,
				'nilai'         => $nilaipph,
				'posting'       => '0',
				'createtime'    => date('Y-m-d H:i:s'),
				'createdby'     => $_SESSION['standard']['userid'],
			);
			
			$colsfee = array();
			foreach($datafee as $key=>$row) {
				$colsfee[] = $key;
			}
			$str = insertQuery($dbname,'kebun_3pph_petani',$datafee,$colsfee);		
			$owlPDO->exec($str);
			
			
		#execute
			$owlPDO->commit();
		}

		
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
		
	case'detail':
		OPEN_BOX();
        echo"
        <div class='table-scroll' style=height:60vh>
        <table border=0 cellpadding=2 cellspacing=1 class=sortable>";
        echo"<thead><tr class=rowcontent>";
        echo"<td align=right><b>No.</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['nama']."</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['total']." Netto</b></td>";
        echo"<td align=center><b>Pph 22</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['nospb']."</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['periode']."</b></td>";
        echo"<td align=center><b>Hamparan</b></td>";
        echo"<td align=center><b>Kavling</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['janjang']."</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['brondolan']."</b></td>";
        echo"<td align=center><b>".$_SESSION['lang']['kg']." Netto</b></td>";
        echo"<td align=center><b>Rp/Kg</b></td>";
        echo"</tr></thead><tbody>";
        $table        ='kebun_tbskud';
        $where="";
        $where.=" and pemilik like '".$kodeorg."%'";
        if($periodebyr=='1'){
        	$tanggal1=$periode."-01";
        	$tanggal2=$periode."-15";
        }elseif($periodebyr=='2'){
        	$tanggal1=$periode."-16";
        	$tanggal2=tglakhir($periode."-01");
        }else{
        	$tanggal1=$periode."-01";
        	$tanggal2=tglakhir($periode."-01");
        }


        $where.=" and tanggaltbs1 between '".$tanggal1."' and '".$tanggal2."'";
        $where.=" and a.supplier='".$kud."'";

       $str = "select tanggaltbs1,tanggaltbs2,supplier,a.nospb,no_hamp,no_kavl,nama,janjang,brondolan,kgwb,kgwbnetto,rpkg from ".$dbname.".".$table." a left join ".$dbname.".kebun_spbpetani b on a.nospb=b.nospb where 1=1 ".$where."   group by nama,nospb order by nama";

        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
        	if (substr($bar['tanggaltbs1'],8,2) <= 15) {
        		$arrdatax[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['nospb'];
        		@$arrtotnet[$bar['supplier']][$bar['nama']]['per1']+=$bar['kgwbnetto']*$bar['rpkg'];

        		$arrperiode[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['tanggaltbs1'].' - '.$bar['tanggaltbs2'];
        		$arrhamp[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['no_hamp'];
        		$arrkavl[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['no_kavl'];
        		$arrjjg[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['janjang'];
        		$arrbrd[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['brondolan'];
        		$arrkgnet[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['kgwbnetto'];
        		$arrrpkg[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['rpkg'];


        		$arrcols[$bar['supplier']][$bar['nama']]['per1']=1;
        		$arrcols2[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=1;
        	}
        	else
        	{
        		
        		@$arrdatax[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['nospb'];
        		@$arrtotnet[$bar['supplier']][$bar['nama']]['per2']+=$bar['kgwbnetto']*$bar['rpkg'];


        		@$arrperiode[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['tanggaltbs1'].' - '.$bar['tanggaltbs2'];
        		@$arrhamp[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['no_hamp'];
        		@$arrkavl[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['no_kavl'];
        		@$arrjjg[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['janjang'];
        		@$arrbrd[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['brondolan'];
        		@$arrkgnet[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['kgwbnetto'];
        		@$arrrpkg[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['rpkg'];


        		@$arrcols[$bar['supplier']][$bar['nama']]['per2']=1;
        		@$arrcols2[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=1;
        	}

        }

        if (@count($arrdatax)>0) {

        		
        	foreach($arrdatax as $supplier => $v){	
        		foreach ($v as $nama => $v2) {
        			foreach ($v2 as $per => $v3) {
        				@$cols[$supplier][$nama]+=$arrcols[$supplier][$nama][$per];
        				foreach ($v3 as $spb => $v4) {
        					@$cols2[$supplier][$nama]+=$arrcols2[$supplier][$nama][$per][$spb];
        					@$cols3[$supplier][$nama][$per]+=$arrcols2[$supplier][$nama][$per][$spb];

        				}

        			}

        		}

        	}


        	foreach($arrdatax as $supplier => $v){					
					
						
					$cellpadding=1;	
		


					$optnpwp=makeOption($dbname,"log_5supnpwp",'supplierid,npwp',"supplierid='".$supplier."'");

					$no=0;
					foreach ($v as $nama => $v2) {
							

							$col=$cols2[$supplier][$nama];
							
							$no+=1;
							$nox=0;

							
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=left rowspan=".$col.">".$no." </td>";
							$tab.="<td align=left rowspan=".$col.">".$nama."</td>";

							//$optnpwp=makeOption($dbname,"kebun_5kavling",'nama,npwp',"nama='".$nama."'");
							
							
						$totnetto=0;
						$totpph=0;
						$totbrd=0;
						$totarrkgnet=0;
						$totarrrpkg=0;
						foreach ($v2 as $per => $v3) {
							$nox+=1;
							$col2=$cols3[$supplier][$nama][$per];
							
							if ($nox==1) {
								$noxx=0;
								// $jum=$arrtotnet[$supplier][$nama]['per1']+$arrtotnet[$supplier][$nama]['per2'];
								$jum=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));
								if ($jum<20000000) {
										$pph[$supplier][$nama][$per]=0;
								}
								if ($arrtotnet[$supplier][$nama]['per1']<20000000) {
										$pph[$supplier][$nama]['per1']=0;
									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
								
								if ($arrtotnet[$supplier][$nama]['per1']>20000000) {
									if ($optnpwp[$supplier]==0) {
										$pph[$supplier][$nama]['per1']=$jum*(0.5/100);
									}
									else
									{
										$pph[$supplier][$nama]['per1']=$jum*(0.25/100);
									}

									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}

								$color='';
								if ($pph[$supplier][$nama][$per]>0) {
									$color="style='background-color:cyan';";
								}

								$nilpph=explode(".",$pph[$supplier][$nama][$per]);
								$pph[$supplier][$nama][$per]=$nilpph[0];
								$tab.="<td align=right rowspan=".$col2.">".number_format($arrtotnet[$supplier][$nama][$per],0)."</td>";
								$tab.="<td align=right rowspan=".$col2." ".$color.">".number_format($pph[$supplier][$nama][$per],0)."</td>";
								$arrAdaPPh[$supplier][$nama]=0;
								if($pph[$supplier][$nama][$per]>0){
									$arrAdaPPh[$supplier][$nama]=1;
								}
								foreach ($v3 as $spb => $v4) {
									$noxx+=1;
									
									if ($noxx==1) {
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}
									else
									{
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}

									$totarrkgnet+=$arrkgnet[$supplier][$nama][$per][$spb];
									$totbrd+=$arrbrd[$supplier][$nama][$per][$spb];
									$totarrrpkg+=$arrrpkg[$supplier][$nama][$per][$spb];
								}
								

								
							}
							else
							{
								$col2=$cols3[$supplier][$nama][$per];

								$noxx=0;
								// $jum=$arrtotnet[$supplier][$nama]['per1']+$arrtotnet[$supplier][$nama]['per2'];
								// $jum=$arrtotnet[$supplier][$nama][$per];
								$jum=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));

								if ($jum<20000000) {
										$pph[$supplier][$nama][$per]=0;
								}
								if ($arrtotnet[$supplier][$nama]['per1']<20000000) {
										$pph[$supplier][$nama]['per1']=0;
									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
								if ($arrtotnet[$supplier][$nama]['per1']>20000000) {
									if ($optnpwp[$supplier]==0) {
										$pph[$supplier][$nama]['per1']=$jum*(0.5/100);
									}
									else
									{
										$pph[$supplier][$nama]['per1']=$jum*(0.25/100);
									}

									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
									$nilpph=explode(".",$pph[$supplier][$nama][$per]);
									$pph[$supplier][$nama][$per]=$nilpph[0];
								$color='';
								if ($pph[$supplier][$nama][$per]>0) {
									$color="style='background-color:cyan';";
								}
								$tab.="<tr class=rowcontent>";
							    $tab.="<td align=right rowspan=".$col2.">".number_format($arrtotnet[$supplier][$nama][$per],0)."</td>";
							    $tab.="<td align=right rowspan=".$col2." ".$color.">".number_format($pph[$supplier][$nama][$per],0)."</td>";
								$arrAdaPPh2[$supplier][$nama]=0;
								if($pph[$supplier][$nama][$per]>0){
									$arrAdaPPh2[$supplier][$nama]=1;
								}
							    $noxx=0;
							    foreach ($v3 as $spb => $v4) {
									$noxx+=1;
									
									if ($noxx==1) {
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}
									else
									{
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}

									$totarrkgnet+=$arrkgnet[$supplier][$nama][$per][$spb];
									$totbrd+=$arrbrd[$supplier][$nama][$per][$spb];
									$totarrrpkg+=$arrrpkg[$supplier][$nama][$per][$spb];
								}
							   
							    $tab.="</tr>";
					
							}

							$totnetto+=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));
							$totpph+=$pph[$supplier][$nama][$per];
							
					

							
						}
						$totpph=0;
						if($totnetto>=20000000) {
							if ($optnpwp[$supplier]==0) {
								$totpph=$totnetto*(0.5/100);
							}
							else
							{
								$totpph=$totnetto*(0.25/100);
							}
						}
						if($totpph>0){
							$nilpph=explode(".",$totpph);
							$totpph=$nilpph[0];
						}

        			@$noxxx+=1;
        				$tab.="<tr id=tr_".$noxxx." class=rowcontent>";
        				$tab.="<td align=center colspan=2><b>Total</b></td>";
        				$tab.="<td align=right><b>".number_format($totnetto,0)."</b></td>";
        				$tab.="<td align=right ><b>".number_format($totpph,0)."</b></td>";
        				$tab.="<td align=right hidden id='nilaipph_".$noxxx."'>".$totpph."</b></td>";
        				$tab.="<td align=right hidden id='id_kav_".$noxxx."'>".$arrkavl[$supplier][$nama][$per][$spb]."</b></td>";
        				$tab.="<td align=right hidden id='nama_".$noxxx."'>".$nama."</b></td>";
        				$tab.="<td align=right colspan=5><b></b></td>";
        				$tab.="<td align=right><b>".number_format($totbrd,0)."</b></td>";
        				$tab.="<td align=right><b>".number_format($totarrkgnet,0)."</b></td>";
							// $tab.="<td align=right><b>".number_format($totarrrpkg,0)."</b></td>";
        				$tab.="<td align=right><b></b></td>";
        				$tab.="</tr>";

        			//}


        			@$gtnetto+=$totnetto;
        			@$gtpph+=$totpph;
        			@$gtbrd+=$totbrd;
        			@$gtkgnet+=$totarrkgnet;
        		}


        		//if ($gtpph>0) {
        			$tab.="<tr class=rowcontent>";
        			$tab.="<td align=center colspan=2><b>Grand Total</b></td>";
        			$tab.="<td align=right><b>".number_format($gtnetto,0)."</b></td>";
        			$tab.="<td align=right ><b>".number_format($gtpph,0)."</b></td>";
        			$tab.="<td align=right colspan=5><b></b></td>";
        			$tab.="<td align=right><b>".number_format($gtbrd,0)."</b></td>";
        			$tab.="<td align=right><b>".number_format($gtkgnet,0)."</b></td>";
        			$tab.="<td align=right><b></b></td>";
        			$tab.="</tr>"; 
        		//}

        	}

        }
        else
        {
        	$tab.="<tr class=rowcontent>";
        	$tab.="<td align=center colspan=12>Data Kosong </td>";
        	$tab.="</tr>";
        	
        }
      

    	echo $tab;
        echo"<input type=hidden id=method value='insert'>
        <input type=hidden id=jumlahrow value=".$no.">
        <td colspan=20 align=right>
        <button id=tomboldetail class=mybutton onclick=\"saveAll(".@$noxxx.")\" >" . $_SESSION['lang']['save'] . "</button>
        </td>
        </tr>		
        </tfoot>
        </table>
        </div
        ";
        CLOSE_BOX();
	break;
	
	
	
	case'loaddata':
        $where = "";
       
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $no = 0;
		$sql = "select * from " . $dbname . ".kebun_3pph_petani a where 1=1 ".$where." group by kodeorg,periode";
		$jlhbrs = count(fetchdata($sql));
        $no = $maxdisplay;
		
		$optkdorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$optnmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
        $wh='';
        if ($divsch!='') {
        	$wh.="and kodeorg ='".$divsch."'";
        }
        if ($tglsch!='') {
        	$wh.="and periode ='".$tglsch."'";
        }
		$str = "select sum(nilai) as nilai, kodeorg,periode,kode_supplier,posting,notransaksi  from " . $dbname . ".kebun_3pph_petani where 1=1 ".$where." ".$wh." group by notransaksi  limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		$sts = array('0'=>'Belum Posting' ,'1'=>'Sudah Posting' );
        foreach($res as $bar){
					
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=center>".$bar['notransaksi']."</td>";
			$tab.="<td align=left>".$bar['kodeorg']." - ".$optkdorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['periode']."</td>";
			$tab.="<td align=left>".$optnmsup[$bar['kode_supplier']]."</td>";
			$tab.="<td align=right>".number_format($bar['nilai'])."</td>";
			$optnokas=makeOption($dbname,'keu_kasbankdt','keterangan1,notransaksi');
			$tab.="<td align=right>".@$optnokas[$bar['notransaksi']]."</td>";
			$tab.="<td align=right>".$sts[$bar['posting']]."</td>";
			#action
			if ($bar['posting']==1) {

				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['notransaksi']."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td width=20px align=center></td>";

			}
			else
			{
				$icon="images/skyblue/posting.png";
					$title="";
					$unpost=" onclick=\"posting('".$bar['notransaksi']."');\" ";

				$tab.="<td width=20px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['notransaksi']."');\" ></td>";
			}


			

				
				$tab.="<td width=20px align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";

				$tab.="<td width=20px align=center><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$bar['notransaksi']."','html');\" ></td>";
		
        }
		
            
		$tab.="</tr>";
			
        $tab.="</table>";
        
		
        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=15 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
        echo $tab . "####" . $footd;
        break;
		
	case'delete':
        $str = "delete from " . $dbname . ".kebun_3pph_petani where notransaksi='" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;	
	case'html':
        
		if($jenistampil=='html'){
			$border=0;
			$vwidth="cellspacing=1 cellpadding=3";
		}elseif($jenistampil=='pdf'){
			$border=1;
			$vwidth="width=100%  cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		$tab.="<table  border='".$border."' ".$vwidth.">";
		$tab.="<tr style='text-align:center'>";
		$tab.="<td>Notransaksi</td>";
		$tab.="<td>:</td>";
		$tab.="<td>".$notransaksi."</td>";
		$tab.="</tr>";
		$tab.="</table><br>";

		$tab.="<table class=sortable  border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['nama']." Petani</th>
				<th>".$_SESSION['lang']['nilai']." Pph</th>
				
			</tr></thead>";
			
			
		$str="select * from ".$dbname.".kebun_3pph_petani where notransaksi='" . $notransaksi . "' order by nama_petani asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td>".$no."</td>";
			$tab.="<td>".$val['nama_petani']."</td>";
			$tab.="<td align=right>".number_format($val['nilai'],0)."</td>";
			$tab.="</tr>";

			@$tot+=$val['nilai'];
			
		}

			$tab.="<tr class=rowcontent>";
			$tab.="<th align=center colspan=2>Total</th>";
			$tab.="<th align=right>".number_format($tot,0)."</th>";
			$tab.="</tr>";
			
		$tab.="</tbody>
			</table>";
        
		
		if($jenistampil=='html'){			
			echo $tab;
		}
		
	break;
	
	case'posting':
	try {
	$owlPDO->beginTransaction();
		
        $query = updateQuery($dbname,'kebun_3pph_petani',array('posting'=>'1'),
        	"notransaksi = '" . $notransaksi . "'");
        $owlPDO->exec($query);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	break;
	case'unposting':
	try {
	$owlPDO->beginTransaction();
		
		$cek = "select * from " . $dbname . ".keu_kasbankdt where keterangan1 = '".$notransaksi."'";
		$rcek = fetchdata($cek);
		if(count($rcek)>0){			
			throw new PDOException("Transaksi sudah Ada Di kasbank.");
		}
		else
		{
			$updaterekap = array(
				'posting' => '0'
			);

			$where = "notransaksi='".$notransaksi."'";
			$str = updateQuery($dbname,'kebun_3pph_petani',$updaterekap,$where);
			$owlPDO->exec($str);


			#execute
			$owlPDO->commit();
		}

		
		
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	break;
		

}
?>	