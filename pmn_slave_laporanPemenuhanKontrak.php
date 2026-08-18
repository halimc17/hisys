<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$nokontrak = checkPostGet('notrans','');
$periode=checkPostGet('periode','');
$kdBrg=checkPostGet('kdBrg','');
$kdBrg2=checkPostGet('kdBrg2','');
$thn = checkPostGet('thn','');
$kdBrg3 = checkPostGet('kdBrg3','');
$pt = checkPostGet('pt','');
$pt2 = checkPostGet('pt2','');
$pt3 = checkPostGet('pt3','');
$tgl_dr = checkPostGet('tgl_dr','');
$tgl_samp = checkPostGet('tgl_samp','');
$matauang = checkPostGet('matauang','');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$total1=$total2=$total3=$total4=0;
$total1e=$total2e=$total3e=$total4e=0;
$total1p=$total2p=$total3p=$total4p=0;

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$indukOrg=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$whrNotran="";
$whrNotranA="";
if($kdBrg!='40000003'){
    #filter untuk data timbangan yang duplikasi dari keuangan>Transaksi>Pengakuan Jual
    #jika TBS maka tidak di tambahkan,kodebarang untuk TBS=40000003
    $whrNotran=" and char_length(notransaksi)<8";
    $whrNotranA=" and char_length(a.notransaksi)<8";
}
switch($proses)
{
        case'preview':
		
		if($periode==''){
			exit("Warning : Periode harus dipilih");
		}
		// <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        echo"<div class='table-scroll'  style='width:100%;height:340px;overflow:scroll;'>
        <table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>
        <th rowspan=3>".$_SESSION['lang']['nourut']."</th>
        <th rowspan=3>".$_SESSION['lang']['kodept']."</th>
        <th colspan=11>".$_SESSION['lang']['kontrak']."</th>
        <th colspan=4>".$_SESSION['lang']['sales']."</th>
        <th colspan=3>Outstanding</th>
        <th rowspan=3>".$_SESSION['lang']['matauang']."</th>
        </tr><tr class=rowheader>
        <th rowspan=2>".$_SESSION['lang']['NoKontrak']."</th>
        <th rowspan=2>".$_SESSION['lang']['komoditi']."</th>
        <th rowspan=2>".$_SESSION['lang']['tglKontrak']."</th>
        <th rowspan=2>".$_SESSION['lang']['Pembeli']."</th>
        <th rowspan=2>".$_SESSION['lang']['tipe']."</th>
        <th rowspan=2>".$_SESSION['lang']['franco']."</th>
        <th rowspan=2>".$_SESSION['lang']['mutu']."</th>
        <th rowspan=2>".$_SESSION['lang']['estimasiPengiriman']."</th>
        <th>".$_SESSION['lang']['volume']."</th>
        <th>".$_SESSION['lang']['harga']."</th>
        <th>".$_SESSION['lang']['nilai']."</th>
        <th rowspan=2>".$_SESSION['lang']['tanggal']." BL</th>
        <th>".$_SESSION['lang']['volume']."</th>
        <th>".$_SESSION['lang']['harga']."</th>
        <th>".$_SESSION['lang']['nilai']."</th>
        <th>".$_SESSION['lang']['volume']."</th>
        <th>".$_SESSION['lang']['harga']."</th>
        <th>".$_SESSION['lang']['nilai']."</th>
        </tr><tr class=rowheader>
        <th>".$_SESSION['lang']['kg']."</th>
        <th>".$_SESSION['lang']['rpperkg']."</th>
        <th>".$_SESSION['lang']['rp']."</th>
        <th>".$_SESSION['lang']['kg']."</th>
        <th>".$_SESSION['lang']['rpperkg']."</th>
        <th>".$_SESSION['lang']['rp']."</th>
        <th>".$_SESSION['lang']['kg']."</th>
        <th>".$_SESSION['lang']['rpperkg']."</th>
        <th>".$_SESSION['lang']['rp']."</th>
        </tr></thead><tbody>
        ";
        if($kdBrg!='')
        {
                $where=" and kodebarang='".$kdBrg."'";
        }
		if($pt!=''){
			$where.=" and kodept like '%".$pt."%'";
		}
                
                
        // $sql="select * from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%' ".$where." order by tanggalkontrak desc";
	// pak berlino minta tanggal asc 20211222 via WA pak asep
        // $sql="select * from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%' ".$where." order by tanggalkontrak asc";
        // 20211230 berlino/yan yang berkaitan dengan laporan sales harus ambil dari tanggalbl bast (tidak peduli kontrak kapan)
        $sql="select * from ".$dbname.".pmn_kontrakjual_vw where (nokontrak in (
        	select nokontrak from ".$dbname.".pmn_bast where tanggalbl like '".$periode."%'
    	) or tanggalkontrak like '".$periode."%') ".$where." order by tanggalkontrak asc";
        
      
        //exit("Error".$sql);
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $row=owlBaris($query);
                
        $nourut=0;
	    if($row<=0){
			echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    while($res=$query->fetch())
                    {
						$nourut++;
						$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
						$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
						$qBrg->setFetchMode(PDO::FETCH_ASSOC);
						$rBrg=$qBrg->fetch();
											
											
						$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
						$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
						$qCust->setFetchMode(PDO::FETCH_ASSOC);
						$rCust=$qCust->fetch();
										
						$sTimb="select sum(jumlah) as jumlahTotal  from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' ";					
						$qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
						$qTimb->setFetchMode(PDO::FETCH_ASSOC);
						$rTimb=$qTimb->fetch();
						
						$strfranco="select franco_name  from ".$dbname.".pmn_5franco where id_franco='".$res['franco']."' ";					
						$resfranco=$owlPDO->query($strfranco) or die(print " Gagal: ".PDOException::getMessage());
						$resfranco->setFetchMode(PDO::FETCH_ASSOC);
						$barfranco=$resfranco->fetch();
						
						$tanggalbl=$temptanggalbl='';
						$strbl="select tanggalbl,nokontrak,sum(jumlah) as jumlah from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' group by tanggalbl";
						$resbl=fetchdata($strbl);
						foreach($resbl as $barbl){
							if($temptanggalbl!=$barbl['tanggalbl']){
								// $tanggalbl.=tanggalnormal($barbl['tanggalbl']).' ('.hidezerodecimal($barbl['jumlah'],2).')<br>';
								$tanggalbl.=tanggalnormal($barbl['tanggalbl'])	;
							}
							$temptanggalbl=$barbl['tanggalbl'];
						}

							// if ($res['kodebarang']=='40000002') {
								// $data="Kadar Air : ".$res['moist'].", Kadar Kotoran : ".$res['dirt']."";
							// }else if ($komoditi[$nokontrak]=='40000001'){
								// $data="FFA : ".$res['ffa'].", M&I : ".$res['mdani']."";
							// }
						$data='';	
						if($res['moist']!=0){
							$data.="Kadar Air : ".hidezerodecimal($res['moist'],3)."; ";
						}			
						if($res['dirt']!=0){
							$data.="Kadar Kotoran : ".hidezerodecimal($res['dirt'],3)."; ";
						}
						if($res['ffa']!=0){
							$data.="FFA : ".hidezerodecimal($res['ffa'],2)."; ";
						}
						if($res['mdani']!=0){
							$data.="M and I : ".hidezerodecimal($res['mdani'],3)."; ";
						}						
					
					
					$salesvalue=$res['hargasatuan']*$rTimb['jumlahTotal'];
						
					$arr="nokontrak"."##".$res['nokontrak'];
					// $sisaBarang=$res['kuantitaskontrak']-$rTimb['jumlahTotal'];
					$sisaBarang=$rTimb['jumlahTotal']-$res['kuantitaskontrak'];
					//<td align=right>".hidezerodecimal($rTimb['jumlahKgpem'])."</td>
					// echo"<tr class=rowcontent onclick=\"zDetail(event,'pmn_slave_laporanPemenuhanKontrak.php','".$arr."')\">
					@$hargasales=fixnan($salesvalue/$rTimb['jumlahTotal']);
					$hargaoutstanding=$res['hargasatuan'];
					$nilaioutstanding=$hargaoutstanding*$sisaBarang;
					echo"<tr class=rowcontent>
					<td align=center>".$nourut."</td>
					<td>".$res['kodept']."</td>
					<td>".$res['nokontrak']."</td>
					<td>".$rBrg['namabarang']."</td>
					<td>".tanggalnormal($res['tanggalkontrak'])."</td>
					<td>".$rCust['namacustomer']."</td>
					<td>".$res['tipepenjualan']."</td>
					<td>".$barfranco['franco_name']."</td>
					<td>".$data."</td>
					<td>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td align=right>".hidezerodecimal($res['kuantitaskontrak'])."</td>
					<td align=right>".hidezerodecimal($res['hargasatuan'])."</td>
					<td align=right>".hidezerodecimal($res['nilaikontrak'])."</td>
					<td align=left>".$tanggalbl."</td>
					<td align=right>".hidezerodecimal($rTimb['jumlahTotal'])."</td>
					<td align=right>".hidezerodecimal($hargasales)."</td>
					<td align=right>".hidezerodecimal($salesvalue)."</td>
					<td align=right>".hidezerodecimal($sisaBarang)."</td>
					<td align=right>".hidezerodecimal($hargaoutstanding)."</td>
					<td align=right>".hidezerodecimal($nilaioutstanding)."</td>
                    <td align=center>".$res['matauang']."</td>
					</tr>
					";
					$total1+=$res['kuantitaskontrak'];
					$total2+=$rTimb['jumlahTotal'];
					@$total3+=$rTimb['jumlahKgpem'];
					@$totalnilaikontrak+=$res['nilaikontrak'];
					$total4+=$sisaBarang;                
					$totalsalesvalue+=$salesvalue;
					$totalnilaioutstanding+=$nilaioutstanding;                
			}
			@$totalhargasales=fixnan($totalsalesvalue/$total2);
			@$totalhargaoutstanding=fixnan($totalnilaioutstanding/$total4);
			echo"<tr class=rowcontent>
			<td colspan=10>".$_SESSION['lang']['total']."</td>
			<td align=right>".hidezerodecimal($total1)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalnilaikontrak)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($total2)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalsalesvalue)."</td>
			<td align=right>".hidezerodecimal($total4)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalnilaioutstanding)."</td>
			<td align=right></td>
			</tr>";/*<td align=right>".hidezerodecimal($total3)."</td>*/
		}
        echo"</tbody></table></div>";
        break;
		
        case'preview2':
        if($thn==''){
			exit("Warning: Periode harus dipilih.");
		}
        echo"<div class='table-scroll'>
        <table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>
        <th>".$_SESSION['lang']['kodept']."</th>
        <th>".$_SESSION['lang']['NoKontrak']."</th>
        <th>".$_SESSION['lang']['komoditi']."</th>
        <th>".$_SESSION['lang']['tglKontrak']."</th>
        <th>".$_SESSION['lang']['Pembeli']."</th>
        <th>".$_SESSION['lang']['estimasiPengiriman']."</th>
        <th>Kuantitas (Kg)</th>
        <th>".$_SESSION['lang']['tanggal']." BL</th>
        <th>Delivery (Kg)</th>
        <th>".$_SESSION['lang']['sisa']." (Kg)</th>
        <th>".$_SESSION['lang']['matauang']."</th>
        </tr></thead><tbody>
        ";//  <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>

        // $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang  from ".$dbname.".pmn_kontrakjual_vw
        //       where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak desc";
	// pak berlino minta tanggal asc 20211222 via WA pak asep
        // $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang  from ".$dbname.".pmn_kontrakjual_vw
        //       where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak asc";
        // 20211230 berlino/yan yang berkaitan dengan laporan sales harus ambil dari tanggalbl bast (tidak peduli kontrak kapan)
        $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang  from ".$dbname.".pmn_kontrakjual_vw
            	where kodebarang like '%".$kdBrg2."%' and (nokontrak in (
              		select nokontrak from ".$dbname.".pmn_bast where tanggalbl like '".$thn."%'
          		) or tanggalkontrak like '".$thn."%' ) and kodept like '%".$pt2."%' order by tanggalkontrak asc";
        
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $row=owlBaris($query);
            
        if($row<=0){
		echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch())
            {
				$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                $rBrg=$qBrg->fetch();

				$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                $rCust=$qCust->fetch();
				
				
				/*
				// Get No Kontrak Internal
				$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
										"nokontrak = '".$res['nokontrak']."' or
										nokontrakinternal = '".$res['nokontrak']."'");
				$resKontrak = fetchData($qKontrak);
				if(empty($resKontrak)) {
					$optKontrak = array($res['nokontrak']);
				} else {
					$optKontrak = array();
				}
				$optSipb = array();
				foreach($resKontrak as $row) {
					$optKontrak[] = $row['nokontrak'];
					if(!empty($row['nokontrakinternal'])) {
						$optKontrak[] = $row['nokontrakinternal'];
					}
					$optSipb[] = $row['nodo'];
				}
				
				$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
				if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
                $qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
                $qTimb->setFetchMode(PDO::FETCH_ASSOC);
                $rTimb=$qTimb->fetch();
				*/
				
				
				$tanggalbl=$temptanggalbl='';
				$strbl="select tanggalbl,nokontrak,sum(jumlah) as jumlah from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' group by tanggalbl";
				$resbl=fetchdata($strbl);
				foreach($resbl as $barbl){
					if($temptanggalbl!=$barbl['tanggalbl']){
						// $tanggalbl.=tanggalnormal($barbl['tanggalbl']).' ('.hidezerodecimal($barbl['jumlah'],2).')<br>';
						$tanggalbl.=tanggalnormal($barbl['tanggalbl']);
					}
					$temptanggalbl=$barbl['tanggalbl'];
				}
				
				
				
				
				$sTimb="select sum(jumlah) as jumlahTotal  from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' ";					
				$qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
				$qTimb->setFetchMode(PDO::FETCH_ASSOC);
				$rTimb=$qTimb->fetch();
                                
				$arr="nokontrak"."##".$res['nokontrak'];
				$sisaBarang=$rTimb['jumlahTotal']-$res['kuantitaskontrak'];
				if($rTimb['jumlahTotal']<$res['kuantitaskontrak'])
				{
					echo"<tr class=rowcontent \">
					<td >".$res['kodept']."</td>
					<td >".$res['nokontrak']."</td>
					<td>".$rBrg['namabarang']."</td>
					<td>".tanggalnormal($res['tanggalkontrak'])."</td>
					<td>".$rCust['namacustomer']."</td>
					<td>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td align=right>".hidezerodecimal($res['kuantitaskontrak'],2)."</td>
					<td>".$tanggalbl."</td>
					<td align=right>".hidezerodecimal($rTimb['jumlahTotal'],2)."</td>
					<td align=right>".hidezerodecimal($sisaBarang,2)."</td>
                    <td align=center style=\"cursor:pointer;\">".$res['matauang']."</td>
					</tr>";
					@$tkuantitaskontrak+=$res['kuantitaskontrak'];
					@$tjumlahTotal+=$rTimb['jumlahTotal'];
					@$tsisaBarang+=$sisaBarang;
					//<td align=right>".hidezerodecimal($rTimb['jumlahKgpem'],2)."</td>
				}
			}
			#= bentuk total
			echo"<tr class=rowcontent \">
			<td colspan=6>".$_SESSION['lang']['total']."</td>
			
			<td align=right>".hidezerodecimal($tkuantitaskontrak,2)."</td>
			<td></td>
			<td align=right>".hidezerodecimal($tjumlahTotal,2)."</td>
			<td align=right>".hidezerodecimal($tsisaBarang,2)."</td>
			<td align=center style=\"cursor:pointer;\"></td>
			</tr>";
		}
        echo"</tbody></table></div>";
        break;
        
		case'preview3':
		if($tgl_dr==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
		if($tgl_samp==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
        echo"
        <div class='table-scroll' id=cetakdHtml >
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>No</td>
        <th>".$_SESSION['lang']['kodept']."</th>
        <th>".$_SESSION['lang']['notransaksi']."</th>
        <th>".$_SESSION['lang']['tanggal']."</th>
        <th>".$_SESSION['lang']['kodebarang']."</th>
        <th>".$_SESSION['lang']['nodo']."</th>
        <th>".$_SESSION['lang']['nosipb']."</th>         
        <th>".$_SESSION['lang']['sopir']."</th>
        <th>".$_SESSION['lang']['kendaraan']."</th>               
        <th>".$_SESSION['lang']['beratBersih']."</th>
        <th>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</th>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual_vw a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/
            $tgl1=explode("-",$_POST['tgl_dr']);
            $tangglAwl=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
            $tgl2=explode("-",$_POST['tgl_samp']);
            $tangglSmp=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
			//$hPT = $namaOrg[$indukOrg[$pt3]];

        $sDet="select a.notransaksi,a.tanggal,a.nodo,a.nosipb,a.beratbersih,a.nokendaraan,a.supir,a.kgpembeli,a.kodebarang, b.kodeorganisasi
              from ".$dbname.".pabrik_timbangan a 
			  left join ".$dbname.".organisasi b
			  on a.millcode = b.kodeorganisasi
			  where substr(a.tanggal,1,10) between '".$tangglAwl."' and '".$tangglSmp."' and a.kodebarang like '%".$kdBrg3."%'
                  and a.nokontrak !='' and b.induk like '%".$pt3."%'  ".$whrNotranA."
              order by tanggal asc";
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
        $rCek=owlBaris($qDet);  
        if($rCek>0)
        {         
                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$qDet->fetch())
                {
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['kodeorganisasi']."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$optNmBrg[$rDet['kodebarang']]."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>			
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td align=right>".hidezerodecimal($rDet['beratbersih'],2)."</td>
                        <td align=right>".hidezerodecimal($rDet['kgpembeli'],2)."</td>
                        </tr>";
						setIt($subtot['total'],0);
						setIt($subtotKga['totalKg'],0);
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                echo"<tr class=rowcontent><td colspan='9'>Total</td><td align=right>".hidezerodecimal($subtot['total'],2)."</td><td align=right>".hidezerodecimal($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        echo"</tbody></table></div></fieldset>";
        break;
		
		case'excel':
    $namaOrg['']="KSP Agro";   
                        $stream="
                        <table>
                        <tr><td colspan=18 align=center>".strtoupper($_SESSION['lang']['laporanPemenuhanKontrak'])."</td></tr>
                        <tr><td colspan=2>".$_SESSION['lang']['perusahaan']."</td><td style='text-align:left'>: ".$namaOrg[$pt]."</td></tr>
                        <tr><td colspan=2>".$_SESSION['lang']['periode']."</td><td style='text-align:left'>: ".$periode."</td></tr>
                        <tr><td colspan=2></td><td></td></tr>
                        </table>
                        <table border=1>
                        <tr class=rowheader>
					        <th rowspan=3>".$_SESSION['lang']['nourut']."</th>
					        <th rowspan=3>".$_SESSION['lang']['kodept']."</th>
					        <th colspan=11>".$_SESSION['lang']['kontrak']."</th>
					        <th colspan=4>".$_SESSION['lang']['sales']."</th>
					        <th colspan=3>Outstanding</th>
					        <th rowspan=3>".$_SESSION['lang']['matauang']."</th>
					        </tr><tr class=rowheader>
					        <th rowspan=2>".$_SESSION['lang']['NoKontrak']."</th>
					        <th rowspan=2>".$_SESSION['lang']['komoditi']."</th>
					        <th rowspan=2>".$_SESSION['lang']['tglKontrak']."</th>
					        <th rowspan=2>".$_SESSION['lang']['Pembeli']."</th>
					        <th rowspan=2>".$_SESSION['lang']['tipe']."</th>
					        <th rowspan=2>".$_SESSION['lang']['franco']."</th>
					        <th rowspan=2>".$_SESSION['lang']['mutu']."</th>
					        <th rowspan=2>".$_SESSION['lang']['estimasiPengiriman']."</th>
					        <th>".$_SESSION['lang']['volume']."</th>
					        <th>".$_SESSION['lang']['harga']."</th>
					        <th>".$_SESSION['lang']['nilai']."</th>
					        <th rowspan=2>".$_SESSION['lang']['tanggal']." BL</th>
					        <th>".$_SESSION['lang']['volume']."</th>
					        <th>".$_SESSION['lang']['harga']."</th>
					        <th>".$_SESSION['lang']['nilai']."</th>
					        <th>".$_SESSION['lang']['volume']."</th>
					        <th>".$_SESSION['lang']['harga']."</th>
					        <th>".$_SESSION['lang']['nilai']."</th>
					        </tr><tr class=rowheader>
					        <th>".$_SESSION['lang']['kg']."</th>
					        <th>".$_SESSION['lang']['rpperkg']."</th>
					        <th>".$_SESSION['lang']['rp']."</th>
					        <th>".$_SESSION['lang']['kg']."</th>
					        <th>".$_SESSION['lang']['rpperkg']."</th>
					        <th>".$_SESSION['lang']['rp']."</th>
					        <th>".$_SESSION['lang']['kg']."</th>
					        <th>".$_SESSION['lang']['rpperkg']."</th>
					        <th>".$_SESSION['lang']['rp']."</th>
					    </tr>";
                        
                        
                        
                        if($periode=='')
                        {
                            exit("Warning : Periode harus dipilih");
                        }
                        if($kdBrg!='')
                        {
                            $where=" and kodebarang='".$kdBrg."'";
                        }
                        if($pt!=''){
                            $where.=" and kodept like '%".$pt."%'";
                        }
                
         $nourut=0;     
        $sql="select * from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%' ".$where." order by tanggalkontrak desc";
	// pak berlino minta tanggal asc 20211222 via WA pak asep
        // $sql="select * from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%' ".$where." order by tanggalkontrak asc";
        // 20211230 berlino/yan yang berkaitan dengan laporan sales harus ambil dari tanggalbl bast (tidak peduli kontrak kapan)
        $sql="select * from ".$dbname.".pmn_kontrakjual_vw where (nokontrak in (
        	select nokontrak from ".$dbname.".pmn_bast where tanggalbl like '".$periode."%'
    	) or tanggalkontrak like '".$periode."%') ".$where." order by tanggalkontrak asc";
        
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($query);
                if($row<=0){
			 $stream.="<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
                $query->setFetchMode(PDO::FETCH_ASSOC);
                while($res=$query->fetch())
                {
					$nourut++;
                    $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
						$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
						$qBrg->setFetchMode(PDO::FETCH_ASSOC);
						$rBrg=$qBrg->fetch();
											
											
						$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
						$qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
						$qCust->setFetchMode(PDO::FETCH_ASSOC);
						$rCust=$qCust->fetch();
										
						$sTimb="select sum(jumlah) as jumlahTotal  from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' ";					
						$qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
						$qTimb->setFetchMode(PDO::FETCH_ASSOC);
						$rTimb=$qTimb->fetch();
						
						$strfranco="select franco_name  from ".$dbname.".pmn_5franco where id_franco='".$res['franco']."' ";					
						$resfranco=$owlPDO->query($strfranco) or die(print " Gagal: ".PDOException::getMessage());
						$resfranco->setFetchMode(PDO::FETCH_ASSOC);
						$barfranco=$resfranco->fetch();
						
						
						$tanggalbl=$temptanggalbl='';
						$strbl="select tanggalbl,nokontrak,sum(jumlah) as jumlah from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' group by tanggalbl";
						$resbl=fetchdata($strbl);
						foreach($resbl as $barbl){
							if($temptanggalbl!=$barbl['tanggalbl']){
								// $tanggalbl.=tanggalnormal($barbl['tanggalbl']).' ('.hidezerodecimal($barbl['jumlah'],2).')<br>';
								$tanggalbl.=($barbl['tanggalbl']);
							}
							$temptanggalbl=$barbl['tanggalbl'];
						}
						
							// if ($res['kodebarang']=='40000002') {
								// $data="Kadar Air : ".$res['moist'].", Kadar Kotoran : ".$res['dirt']."";
							// }else if ($komoditi[$nokontrak]=='40000001'){
								// $data="FFA : ".$res['ffa'].", M&I : ".$res['mdani']."";
							// }
						$data='';	
						if($res['moist']!=0){
							$data.="Kadar Air : ".hidezerodecimal($res['moist'],3)."; ";
						}			
						if($res['dirt']!=0){
							$data.="Kadar Kotoran : ".hidezerodecimal($res['dirt'],3)."; ";
						}
						if($res['ffa']!=0){
							$data.="FFA : ".hidezerodecimal($res['ffa'],2)."; ";
						}
						if($res['mdani']!=0){
							$data.="M and I : ".hidezerodecimal($res['mdani'],3)."; ";
						}						
					
					$salesvalue=$res['hargasatuan']*$rTimb['jumlahTotal'];
					// $sisaBarang=$res['kuantitaskontrak']-$rTimb['jumlahTotal'];
					$sisaBarang=$rTimb['jumlahTotal']-$res['kuantitaskontrak'];
					//<td align=right style=\"cursor:pointer;\">".hidezerodecimal($rTimb['jumlahKgpem'])."</td>
					// echo"<tr class=rowcontent onclick=\"zDetail(event,'pmn_slave_laporanPemenuhanKontrak.php','".$arr."')\" style=\"cursor:pointer;\">
					@$hargasales=fixnan($salesvalue/$rTimb['jumlahTotal']);
					$hargaoutstanding=$res['hargasatuan'];
					$nilaioutstanding=$hargaoutstanding*$sisaBarang;

					 $stream.="<tr class=rowcontent>
					<td>".$nourut."</td>
					<td>".$res['kodept']."</td>
					<td>".$res['nokontrak']."</td>
					<td>".$rBrg['namabarang']."</td>
					<td>".($res['tanggalkontrak'])."</td>
					<td>".$rCust['namacustomer']."</td>
					<td>".$res['tipepenjualan']."</td>
					<td>".$barfranco['franco_name']."</td>
					<td>".$data."</td>
					<td>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td align=right>".hidezerodecimal($res['kuantitaskontrak'])."</td>
					<td align=right>".hidezerodecimal($res['hargasatuan'])."</td>
					<td align=right>".hidezerodecimal($res['nilaikontrak'])."</td>
					<td align=left>".$tanggalbl."</td>
					<td align=right>".hidezerodecimal($rTimb['jumlahTotal'])."</td>
					<td align=right>".hidezerodecimal($hargasales)."</td>
					<td align=right>".hidezerodecimal($salesvalue)."</td>
					<td align=right>".hidezerodecimal($sisaBarang)."</td>
					<td align=right>".hidezerodecimal($hargaoutstanding)."</td>
					<td align=right>".hidezerodecimal($nilaioutstanding)."</td>
                    <td align=center>".$res['matauang']."</td>
					</tr>
					";
					$total1+=$res['kuantitaskontrak'];
					$total2+=$rTimb['jumlahTotal'];
					@$total3+=$rTimb['jumlahKgpem'];
					@$totalnilaikontrak+=$res['nilaikontrak'];
					$total4+=$sisaBarang;                
					$totalsalesvalue+=$salesvalue;
					$totalnilaioutstanding+=$nilaioutstanding;                
			}
			@$totalhargasales=fixnan($totalsalesvalue/$total2);
			@$totalhargaoutstanding=fixnan($totalnilaioutstanding/$total4);
			 $stream.="<tr class=rowcontent>
			<td colspan=10>".$_SESSION['lang']['total']."</td>
			<td align=right>".hidezerodecimal($total1)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalnilaikontrak)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($total2)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalsalesvalue)."</td>
			<td align=right>".hidezerodecimal($total4)."</td>
			<td align=right></td>
			<td align=right>".hidezerodecimal($totalnilaioutstanding)."</td>
			<td align=right></td>
			</tr>";/*<td align=right>".hidezerodecimal($total3)."</td>*/
		}
    
                    
                    //echo "warning:".$strx;
                    //=================================================
                    $stream.="</table>";
                                            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                    $nop_="PemenuhanKontrak_".$pt._.$periode;
                    if(strlen($stream)>0)
                    {
                    if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                    @unlink('tempExcel/'.$file);
                    }
                    }	
                    closedir($handle);
                    }
                    $handle=fopen("tempExcel/".$nop_.".xls",'w');
                    if(!fwrite($handle,$stream))
                    {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                    }
                    else
                    {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
                    }
                    fclose($handle);
                    }
        break;
		
		case'excel2':
        $kdBrg2=$_GET['kdBrg2'];
        if($thn=='')
        {
            exit("Warning: tahun harus dipilih.");
        }
		

                        $stream="
                        <table>
                        <tr><td colspan=11 align=center>Unfulfilled sales contract</td></tr>
                        <tr><td colspan=2>".$_SESSION['lang']['perusahaan']."</td><td> : ".$namaOrg[$pt2]."</td></tr>
                        <tr><td colspan=2>".$_SESSION['lang']['periode']."</td><td> : ".$thn."</td></tr>
                        <tr><td colspan=3></td><td></td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center>No.</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodept']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['NoKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['komoditi']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tglKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['Pembeli']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['estimasiPengiriman']."</td>
                                <td bgcolor=#DEDEDE align=center>Kuantitas (Kg)</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']." BL</td>
                                <td bgcolor=#DEDEDE align=center>Delivery (Kg)</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sisa']." (Kg)</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['matauang']."</td>
                        </tr>";

                        // $strx="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang from ".$dbname.".pmn_kontrakjual_vw
                        //       where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak desc";
	// pak berlino minta tanggal asc 20211222 via WA pak asep
                        $strx="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang from ".$dbname.".pmn_kontrakjual_vw
                              where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak asc";

                        $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                        $row=owlBaris($resx);
                        if($row<=0)
                        {
							$stream.="	<tr class=rowcontent>
							<td colspan=11 align=center>Not Found</td></tr>
							";
                        }
                        else
                        {
                            $no=0;
                                $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                                $resx->setFetchMode(PDO::FETCH_ASSOC); 
                                while($barx=$resx->fetch())
                                {
                                
                                $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$barx['koderekanan']."'";
                                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                                $rCust=$qCust->fetch();
                                
                                
                                // Get No Kontrak Internal
								/*
									$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
															"nokontrak = '".$barx['nokontrak']."' or
															nokontrakinternal = '".$barx['nokontrak']."'");
									$resKontrak = fetchData($qKontrak);
									if(empty($resKontrak)) {
										$optKontrak = array($barx['nokontrak']);
									} else {
										$optKontrak = array();
									}
									$optSipb = array();
									foreach($resKontrak as $row) {
										$optKontrak[] = $row['nokontrak'];
										if(!empty($row['nokontrakinternal'])) {
											$optKontrak[] = $row['nokontrakinternal'];
										}
										$optSipb[] = $row['nodo'];
									}
									
									
									$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
									if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}

									$qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
									$qTimb->setFetchMode(PDO::FETCH_ASSOC);
									$rTimb=$qTimb->fetch();
								*/
				
						
						$tanggalbl=$temptanggalbl='';
						$strbl="select tanggalbl,nokontrak,sum(jumlah) as jumlah from ".$dbname.".pmn_bast where nokontrak='".$barx['nokontrak']."' group by tanggalbl";
						$resbl=fetchdata($strbl);
						foreach($resbl as $barbl){
							if($temptanggalbl!=$barbl['tanggalbl']){
								// $tanggalbl.=tanggalnormal($barbl['tanggalbl']).' ('.hidezerodecimal($barbl['jumlah'],2).')<br>';
								$tanggalbl.=($barbl['tanggalbl']);
							}
							$temptanggalbl=$barbl['tanggalbl'];
						}
							
						$sTimb="select sum(jumlah) as jumlahTotal  from ".$dbname.".pmn_bast where nokontrak='".$barx['nokontrak']."' ";					
						$qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
						$qTimb->setFetchMode(PDO::FETCH_ASSOC);
						$rTimb=$qTimb->fetch();
										
						$sisaBarang=$rTimb['jumlahTotal']-$barx['kuantitaskontrak'];
								
						$sisaData=$rTimb['jumlahTotal']-$barx['kuantitaskontrak'];
						   
							if($rTimb['jumlahTotal']<$barx['kuantitaskontrak'])
							{
								$no++;
								$stream.="	<tr class=rowcontent>
								<td>".$no."</td>
								<td>".$barx['kodept']."</td>
								<td>".$barx['nokontrak']."</td>
								<td>".$optNmBrg[$barx['kodebarang']]."</td>
								<td>".$barx['tanggalkontrak']."</td>
								<td>".$rCust['namacustomer']."</td>
								<td>".tanggalnormal($barx['tanggalkirim'])." s.d. ".tanggalnormal($barx['sdtanggal'])."</td>
								<td>".hidezerodecimal($barx['kuantitaskontrak'],0)."</td>
								<td>".$tanggalbl."</td>
								<td>".hidezerodecimal($rTimb['jumlahTotal'],0)."</td>
								<td>".hidezerodecimal($sisaData,0)."</td>
								<td>".$barx['matauang']."</td>
								</tr>";
								@$tkuantitaskontrak+=$barx['kuantitaskontrak'];
								@$tjumlahTotal+=$rTimb['jumlahTotal'];
								@$tsisaBarang+=$sisaData;
							}
						}
						#= bentuk total
							$stream.="<tr class=rowcontent \">
							<td colspan=7>".$_SESSION['lang']['total']."</td>
							
							<td align=right>".hidezerodecimal($tkuantitaskontrak,2)."</td>
							<td></td>
							<td align=right>".hidezerodecimal($tjumlahTotal,2)."</td>
							<td align=right>".hidezerodecimal($tsisaBarang,2)."</td>
							<td align=center></td>
							</tr>";
					}

                     
                        //=================================================
                        $stream.="</table>";
                                                $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];

                        $nop_="KontrakBlmTpenuhi";
                        if(strlen($stream)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$stream))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
		
        case'pdf':
		if($periode==''){
			exit("Warning : Periode harus dipilih");
		}
		$periode=$_GET['periode'];
        $kdBrg=$_GET['kdBrg'];
         class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $periode;
                                global $kdBrg;
                                global $namaOrg;
                                global $pt;
                                global $owlPDO;


                                $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%'";
                                $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                                $query->setFetchMode(PDO::FETCH_ASSOC);
                                $res=$query->fetch();
                                

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$res['kodept']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',9);
                                $this->Cell((20/100*$width)-5,$height,strtoupper($_SESSION['lang']['laporanPemenuhanKontrak']),'',0,'L');
                                $this->Ln();
                                $this->SetFont('Arial','',8);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['perusahaan'],'',0,'L');
                                $this->Cell(5,$height,':','',0,'L');
                                //$this->Cell(45/100*$width,$height,$namaOrg[$pt],'',0,'L');
                                $this->Cell(45/100*$width,$height,$pt,'',0,'L');
								$this->Ln();
                                $this->SetFont('Arial','',8);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                                $this->Cell(5,$height,':','',0,'L');
                                $this->Cell(45/100*$width,$height,$periode,'',0,'L');



                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','U',7);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['laporanPemenuhanKontrak']),0,1,'C');	
                $this->Ln();	

                $this->SetFont('Arial','B',5);
                $this->SetFillColor(220,220,220);


                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(9/100*$width,$height,$_SESSION['lang']['NoKontrak'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['komoditi'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tglKontrak'],1,0,'C',1);
                $this->Cell(18/100*$width,$height,$_SESSION['lang']['Pembeli'],1,0,'C',1);
                $this->Cell(13/100*$width,$height,$_SESSION['lang']['estimasiPengiriman'],1,0,'C',1);
                $this->Cell(9/100*$width,$height,$_SESSION['lang']['jmlhBrg']." (KG)",1,0,'C',1);
                $this->Cell(9/100*$width,$height,$_SESSION['lang']['pemenuhan']." (KG)",1,0,'C',1);
                $this->Cell(11/100*$width,$height,$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli'],1,0,'C',1);
                $this->Cell(9/100*$width,$height,$_SESSION['lang']['sisa']." (KG)",1,1,'C',1);

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }//indra
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',5);
                if($kdBrg!='')
                {
                        $where=" and kodebarang='".$kdBrg."'";
                }
				if($pt!='')
                {
                        $where.=" and kodept like '%".$pt."%'";
                }
                $sDet="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak like '%".$periode."%' ".$where."";
                $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
                $row=owlBaris($qDet);
                if($row<=0){
					$pdf->Cell(99/100*$width,$height,$_SESSION['lang']['datanotfound'],1,0,'C',1);
				}else{
                    $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$qDet->fetch())
                    {
					$no+=1;
					$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rDet['kodebarang']."'";
                    $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rBrg=$qBrg->fetch();

					$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rDet['koderekanan']."'";
                    $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                    $qCust->setFetchMode(PDO::FETCH_ASSOC);
                    $rCust=$qCust->fetch();
					
					// Get No Kontrak Internal
					$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
											"nokontrak = '".$rDet['nokontrak']."' or
											nokontrakinternal = '".$rDet['nokontrak']."'");
					$resKontrak = fetchData($qKontrak);
					if(empty($resKontrak)) {
						$optKontrak = array($rDet['nokontrak']);
					} else {
						$optKontrak = array();
					}
					$optSipb = array();
					foreach($resKontrak as $row) {
						$optKontrak[] = $row['nokontrak'];
						if(!empty($row['nokontrakinternal'])) {
							$optKontrak[] = $row['nokontrakinternal'];
						}
						$optSipb[] = $row['nodo'];
					}
					
					$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
					if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
	                $qTimb=$owlPDO->query($sTimb) or die(print " Gagal: ".PDOException::getMessage());
	                $qTimb->setFetchMode(PDO::FETCH_ASSOC);
	                $rTimb=$qTimb->fetch();
					$sisaData=$rDet['kuantitaskontrak']-$rTimb['jumlahTotal'];
					$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
					$pdf->Cell(9/100*$width,$height,$rDet['nokontrak'],1,0,'L',1);
					$pdf->Cell(10/100*$width,$height,$rBrg['namabarang'],1,0,'L',1);
					$pdf->Cell(8/100*$width,$height,tanggalnormal($rDet['tanggalkontrak']),1,0,'L',1);
					$pdf->Cell(18/100*$width,$height,substr($rCust['namacustomer'],0,50),1,0,'L',1);		
					$pdf->Cell(13/100*$width,$height,tanggalnormal($rDet['tanggalkirim'])."-".tanggalnormal($rDet['sdtanggal']),1,0,'C',1);		
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($rDet['kuantitaskontrak']),1,0,'R',1);
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($rTimb['jumlahTotal']),1,0,'R',1);
					$pdf->Cell(11/100*$width,$height,hidezerodecimal($rTimb['jumlahKgpem']),1,0,'R',1);
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($sisaData),1,1,'R',1);
					$total1p+=$rDet['kuantitaskontrak'];
					$total2p+=$rTimb['jumlahTotal'];
					$total3p+=$rTimb['jumlahKgpem'];
					$total4p+=$sisaData;
			}
					$pdf->Cell(61/100*$width,$height,$_SESSION['lang']['total'],1,0,'R',1);
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($total1p),1,0,'R',1);
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($total2p),1,0,'R',1);
					$pdf->Cell(11/100*$width,$height,hidezerodecimal($total3p),1,0,'R',1);
					$pdf->Cell(9/100*$width,$height,hidezerodecimal($total4p),1,1,'R',1);
				}
        $pdf->Output();
        break;
        
        
        
        
        case'getDetail':
            
        $drr="##no_kontrak";
        echo"<script language=javascript src=js/generic.js></script><script language=javascript src=js/zTools.js></script>
        <script language=javascript src=js/pmn_laporanPemenuhanKontrak.js></script>";
        echo"<link rel=stylesheet type=text/css href=style/generic.css>";
        $nokontrak=$_GET['nokontrak'];
        $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual_vw a where a.nokontrak='".$nokontrak."'";
        $qHead=$owlPDO->query($sHed) or die(print " Gagal: ".PDOException::getMessage());
        $qHead->setFetchMode(PDO::FETCH_ASSOC);
        $rHead=$qHead->fetch();

        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
        $rBrg=$qBrg->fetch();
        
        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
        $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
        $qCust->setFetchMode(PDO::FETCH_ASSOC);
        $rCust=$qCust->fetch();
        echo"<fieldset><legend>".$_SESSION['lang']['detailPengiriman']."</legend>
        <table cellspacing=1 border=0 class=myinputtext>
        <tr>
                <td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td id='no_kontrak' value='".$nokontrak."'>".$nokontrak."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['tglKontrak']."</td><td>:</td><td>".tanggalnormal($rHead['tanggalkontrak'])."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['komoditi']."</td><td>:</td><td>".$rBrg['namabarang']."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['Pembeli']."</td><td>:</td><td>".$rCust['namacustomer']."</td>
        </tr>
        <tr><td><button onclick=\"zPdfDetail('pmn_slave_laporanPemenuhanKontrak','".$drr."','printPdf')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>
        <button onclick=\"zBack()\" class=\"mybutton\" name=\"preview\" id=\"preview\">HTML</button>
        <button onclick=\"detailExcel('".$nokontrak."','pmn_slave_laporanPemenuhanKontrak.php','printExcel','event')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
        </table><br />";
        echo"<div id=cetakdPdf style=\"display:none;\">
        <fieldset><legend>".$_SESSION['lang']['print']."</legend>
        <div id=\"printPdf\">
        </div>
        </fieldset>
        </div>
        ";
        echo"
        <div id=cetakdHtml >
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>No</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>".$_SESSION['lang']['nosipb']."</td>
        <td>".$_SESSION['lang']['kendaraan']."</td>            
        <td>".$_SESSION['lang']['sopir']."</td>
        <td>".$_SESSION['lang']['beratBersih']."</td>
        <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
		// Get No Kontrak Internal
		$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
								"nokontrak = '".$nokontrak."' or
								nokontrakinternal = '".$nokontrak."'");
		$resKontrak = fetchData($qKontrak);
		if(empty($resKontrak)) {
			$optKontrak = array($nokontrak);
		} else {
			$optKontrak = array();
		}
		$optSipb = array();
		foreach($resKontrak as $row) {
			$optKontrak[] = $row['nokontrak'];
			if(!empty($row['nokontrakinternal'])) {
				$optKontrak[] = $row['nokontrakinternal'];
			}
			$optSipb[] = $row['nodo'];
		}
		
		$sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
		if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}
                $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
                $rCek=owlBaris($qDet);
                if($rCek>0)
                {
                    $qDet->setFetchMode(PDO::FETCH_ASSOC);
                    $subtot['total']=$subtotKga['totalKg']=0;
                    while($rDet=$qDet->fetch())
                    {
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".hidezerodecimal($rDet['beratbersih'],2)."</td>
                        <td align=right>".hidezerodecimal($rDet['kgpembeli'],2)."</td>
                        </tr>";
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                echo"<tr class=rowcontent><td colspan='7'>Total</td><td align=right>".hidezerodecimal($subtot['total'],2)."</td><td align=right>".hidezerodecimal($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                echo"<tr><td colspan=7>Not Found</td></tr>";
        }
        echo"</tbody></table></div></fieldset>";

        break;
        case'getExcel':
            
            
           
            
            $tab.="
        <table cellspacing=1 border=1 class=sortable><thead>
        <tr class=data>
        <td  bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nodo']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nosipb']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kendaraan']."</td>            
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sopir']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
		// Get No Kontrak Internal
		$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
								"nokontrak = '".$nokontrak."' or
								nokontrakinternal = '".$nokontrak."'");
		$resKontrak = fetchData($qKontrak);
		if(empty($resKontrak)) {
			$optKontrak = array($nokontrak);
		} else {
			$optKontrak = array();
		}
		$optSipb = array();
		foreach($resKontrak as $row) {
			$optKontrak[] = $row['nokontrak'];
			if(!empty($row['nokontrakinternal'])) {
				$optKontrak[] = $row['nokontrakinternal'];
			}
			$optSipb[] = $row['nodo'];
		}		
	
        $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."'  ".$whrNotran."";
    if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}   
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
        $rCek=owlBaris($qDet);
        if($rCek>0)
        {
                $subtot['total']=$subtotKga['totalKg']=0;
                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$qDet->fetch())
                {
                        $no+=1;
                        $tab.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".hidezerodecimal($rDet['beratbersih'],2)."</td>
                        <td align=right>".hidezerodecimal($rDet['kgpembeli'],2)."</td>
                        </tr>";
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                $tab.="<tr class=rowcontent><td colspan='7'>Total</td><td align=right>".hidezerodecimal($subtot['total'],2)."</td><td align=right>".hidezerodecimal($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                $tab.="<tr><td colspan=7>Not Found</td></tr>";
        }
        $tab.="</tbody>";
                        $tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                        $nop_="ContractFullfillmentDetail";
                        if(strlen($tab)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$tab))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
        
        case'excel3':
		if($tgl_dr==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
		if($tgl_samp==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
        $tab.="
        <table cellspacing=1 border=1 class=sortable><thead>
        <tr class=data>
        <td bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodept']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodebarang']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nodo']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nosipb']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kendaraan']."</td>            
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sopir']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual_vw a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/
            $tgl1=explode("-",$_GET['tgl_dr']);
            $tangglAwl=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
            $tgl2=explode("-",$_GET['tgl_samp']);
            $tangglSmp=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];

        $sDet="select a.notransaksi,a.tanggal,a.nodo,a.nosipb,a.beratbersih,a.nokendaraan,a.supir,a.kgpembeli,a.kodebarang, b.kodeorganisasi
              from ".$dbname.".pabrik_timbangan a 
			  left join ".$dbname.".organisasi b
			  on a.millcode = b.kodeorganisasi 
			  where substr(a.tanggal,1,10) between '".$tangglAwl."' and '".$tangglSmp."' and a.kodebarang like '%".$kdBrg3."%'
                  and a.nokontrak !='' and b.induk like '%".$pt3."%'  ".$whrNotranA."
              order by a.tanggal asc";
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
        $rCek=owlBaris($qDet);  
        if($rCek>0)
        {
                $subtot['total']=$subtotKga['totalKg']=0;
                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$qDet->fetch())
                {
                        $no+=1;
                        $tab.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['kodeorganisasi']."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$optNmBrg[$rDet['kodebarang']]."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".hidezerodecimal($rDet['beratbersih'],0)."</td>
                        <td align=right>".hidezerodecimal($rDet['kgpembeli'],0)."</td>
                        </tr>";
						setIt($subtot['total'],0);
						setIt($subtotKga['totalKg'],0);
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                $tab.="<tr class=rowcontent><td colspan='9'>Total</td><td align=right>".hidezerodecimal($subtot['total'],0)."</td><td align=right>".hidezerodecimal($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                $tab.="<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        $tab.="</tbody></table>";
        $nop_="rangePengiriman";
                        if(strlen($tab)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$tab))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
        case'detailpdf':
        $no_kontrak=$_GET['no_kontrak'];
        class PDF extends FPDF
        { 
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $no_kontrak;
                global $owlPDO;


                $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual_vw where nokontrak='".$no_kontrak."'";
                $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $res=$query->fetch();
                
                $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual_vw a where a.nokontrak='".$no_kontrak."'";
                $qHead=$owlPDO->query($sHed) or die(print " Gagal: ".PDOException::getMessage());
                $qHead->setFetchMode(PDO::FETCH_ASSOC);
                $rHead=$qHead->fetch();
                
                $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                $rBrg=$qBrg->fetch();
                
                $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                $rCust=$qCust->fetch();

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$res['kodept']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin-20,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','U',9);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['detailPengiriman']),0,1,'C');	
                $this->Ln();	

                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);
                    $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                    $this->Cell(10/100*$width,$height,$_SESSION['lang']['notransaksi'],1,0,'C',1);
                    $this->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                    $this->Cell(12/100*$width,$height,$_SESSION['lang']['nodo'],1,0,'C',1);
                    $this->Cell(16/100*$width,$height,$_SESSION['lang']['nosipb'],1,0,'C',1);
                    $this->Cell(11/100*$width,$height,$_SESSION['lang']['kendaraan'],1,0,'C',1);
                    $this->Cell(11/100*$width,$height,$_SESSION['lang']['sopir'],1,0,'C',1);
                    $this->Cell(12/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);
                    $this->Cell(16/100*$width,$height,$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli'],1,1,'C',1);

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
				
				$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
										"nokontrak = '".$no_kontrak."' or
										nokontrakinternal = '".$no_kontrak."'");
				$resKontrak = fetchData($qKontrak);
				if(empty($resKontrak)) {
					$optKontrak = array($no_kontrak);
				} else {
					$optKontrak = array();
				}
				$optSipb = array();
				foreach($resKontrak as $row) {
					$optKontrak[] = $row['nokontrak'];
					if(!empty($row['nokontrakinternal'])) {
						$optKontrak[] = $row['nokontrakinternal'];
					}
					$optSipb[] = $row['nodo'];
                                }
					
                $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak='".$no_kontrak."'  ".$whrNotran."";
 				if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}
                $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                $subtot=$subtot2=0;
                while($rDet=$qDet->fetch())
                {
                        $no+=1;

                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        $pdf->Cell(10/100*$width,$height,$rDet['notransaksi'],1,0,'L',1);	
                        $pdf->Cell(10/100*$width,$height,tanggalnormal($rDet['tanggal']),1,0,'L',1);	
                        $pdf->Cell(12/100*$width,$height,$rDet['nodo'],1,0,'L',1);		
                        $pdf->Cell(16/100*$width,$height,$rDet['nosipb'],1,0,'L',1);						
                        $pdf->Cell(11/100*$width,$height,$rDet['nokendaraan'],1,0,'L',1);		
                        $pdf->Cell(11/100*$width,$height,ucfirst($rDet['supir']),1,0,'L',1);		
                        $pdf->Cell(12/100*$width,$height,hidezerodecimal($rDet['beratbersih'],2),1,0,'R',1);
                        $pdf->Cell(16/100*$width,$height,hidezerodecimal($rDet['kgpembeli'],2),1,1,'R',1);
                        $subtot+=$rDet['beratbersih'];
                        $subtot2+=$rDet['kgpembeli'];
                }
                $pdf->Cell(73/100*$width,$height,"Total",1,0,'R',1);			
                $pdf->Cell(12/100*$width,$height,hidezerodecimal($subtot,2),1,0,'R',1);
                $pdf->Cell(16/100*$width,$height,hidezerodecimal($subtot2,2),1,1,'R',1);
        $pdf->Output();
        break;
        default:
        break;
}
?>