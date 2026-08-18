<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$tanggalmulai=tanggalsystem(checkPostGet('tanggalmulai',''));
$tanggalakhir=tanggalsystem(checkPostGet('tanggalakhir',''));
$kdBrg=checkPostGet('kdBrg','');
$idPabrik=checkPostGet('idPabrik','');
$proses=checkPostGet('proses','');


if(($tanggalakhir-$tanggalmulai) < 0){
echo " Gagal: Periksa kembali periode tanggal, Tanggal akhir lebih kecil dari tanggal mulai.";
}else{
switch($proses)
{
case'preview':
	if($tanggalmulai=='')
	{
		echo"warning: Tanggal Dari Tidak Boleh Kosong";
		exit();
	}
	if($tanggalakhir=='')
	{
		echo"warning: Tanggal Sampai Tidak Boleh Kosong";
		exit();
	}

	echo" <table class=sortable cellspacing=1 border=0 style='width:1800px;'>
		<thead>
		<tr class=rowheader>
			<td rowspan=2>".$_SESSION['lang']['komoditi']."</td>
			<td style='text-align:center;' colspan=7>".$_SESSION['lang']['kontrak']."</td>
			<td style='text-align:center;' rowspan=2>Term</td>
			<td style='text-align:center;' colspan=5>Invoice</td>
			<td style='text-align:center;' rowspan=2>Total Invoice</td>
			<td style='text-align:center;' rowspan=2>Payment</td>
			<td style='text-align:center;' rowspan=2>Outstanding Payment</td>
			<td style='text-align:center;' rowspan=2>No. Faktur Pajak</td>
			<td style='text-align:center;' rowspan=2>Status Posting</td>
		</tr>
		<tr>
			<td style='text-align:center;'>".$_SESSION['lang']['customer']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['nomor']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['nodo']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['tanggal']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['kg']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['total']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['nomor']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['kg']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['hargaJual']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['ppn']."</td>
		</tr>
		</thead><tbody>
		";
		
		$isidata=array();
		$sql="select b.namabarang,f.namacustomer, a.nokontrak,g.nodo, a.tanggalkontrak, a.kuantitaskontrak, a.hargasatuan, a.kdtermin, 
			c.noinvoice, c.nilaiinvoice, c.nilaippn, c.posting, c.nofakturpajak, d.jumlah, e.nofaktur  
			from ".$dbname.".keu_penagihanht c
			left join ".$dbname.".pmn_kontrakjual a
			on a.nokontrak = c.nokontrak
			left join ".$dbname.".log_5masterbarang b 
			on a.kodebarang = b.kodebarang
			left join ".$dbname.".keu_kasbankdt d
			on c.noinvoice = d.keterangan1
			left join ".$dbname.".pmn_faktur e 
			on a.nokontrak = e.nokontrak and c.noinvoice = e.noinvoice 
			left join ".$dbname.".pmn_4customer f
			on c.kodecustomer = f.kodecustomer 
			left join ".$dbname.".pmn_suratperintahpengiriman g
			on a.nokontrak = g.nokontrak 
			where a.tanggalkontrak between '".$tanggalmulai."' and '".$tanggalakhir."' and 
			c.kodept = '".$idPabrik."'
			";
		if(!empty($kdBrg)) {
			$sql .= " and a.kodebarang = '".$kdBrg."'";
		}
		$sql .= " order by a.nokontrak";
		
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($query);
                    
		if($row>0)
		{
            $gtotalIncHargaJual=$gtotalIncPpn=$gtotalIncKg=$gtotalInc   =$gtotalPayment =0;
			$query->setFetchMode(PDO::FETCH_ASSOC);
			while($res=$query->fetch())
                            {
				$no+=1;
				$item = explode(";",$res['kdtermin']);
				$itemCount = count($item);
				
				$sInv="select * from ".$dbname.".keu_penagihanht where nokontrak='".$res['nokontrak']."'";
				$qInv=$owlPDO->query($sInv) or die(print " Gagal: ".PDOException::getMessage());
                $qInv->setFetchMode(PDO::FETCH_ASSOC);
                $rInv=$qInv->fetch();
                                    
				$sSub="select count(nokontrak) from ".$dbname.".keu_penagihanht where nokontrak = '".$res['nokontrak']."'";
                $qSub=$owlPDO->query($sSub) or die(print " Gagal: ".PDOException::getMessage());
                $qSub->setFetchMode(PDO::FETCH_NUM);
                $cAry=$qSub->fetch();
                                    
                                    
				$rows = $cAry[0];
				
				if($res['posting']==1){
					$hPosting = "Posted";
				}else{
					$hPosting = "Not Posted";
				}
				
				if($res['nilaippn']!=0){
					$hHargaSatuan = $res['hargasatuan']/1.1;
				}else{
					$hHargaSatuan = $res['hargasatuan'];
				}
				
				// print_r($itemCount);
				
				echo"<tr class=rowcontent>
						<td>".$res['namabarang']."</td>
						<td>".$res['namacustomer']."</td>
						<td>".$res['nokontrak']."</td>
						<td>".$res['nodo']."</td>
						<td>".tanggalnormal($res['tanggalkontrak'])."</td>
						<td style='text-align:right;'>".number_format($res['kuantitaskontrak'])."</td>
						<td style='text-align:right;'>".number_format($res['hargasatuan'])."</td>
						<td style='text-align:right;'>".number_format($res['kuantitaskontrak'] * $res['hargasatuan'])."</td>
						<td style='text-align:center;'>".$res['kdtermin']."</td>
						<td>".$res['noinvoice']."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice']/$hHargaSatuan)."</td>
						<td style='text-align:right;'>".number_format($hHargaSatuan)."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice'])."</td>
						<td style='text-align:right;'>".number_format($res['nilaippn'])."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice']+$res['nilaippn'])."</td>
						<td style='text-align:right;'>".number_format($res['jumlah'])."</td>
						<td style='text-align:right;'>".number_format(($res['nilaiinvoice']+$res['nilaippn'])-$res['jumlah'])."</td>
						<td style='text-align:left;'>".$res['nofaktur']."</td>
						<td style='text-align:center;'>".$hPosting."</td>
						</tr>
					";
				
				$totalIncKg[] = $res['nilaiinvoice']/$hHargaSatuan;
				$totalIncHargaJual[] = $res['nilaiinvoice'];
				$totalIncPpn[] = $res['nilaippn'];
				$totalInc[] = $res['nilaiinvoice']+$res['nilaippn'];
				$totalPayment[] = $res['jumlah'];
				
				$gtotalIncKg += ($res['nilaiinvoice']/$hHargaSatuan);
				$gtotalIncHargaJual += $res['nilaiinvoice'];
				$gtotalIncPpn += $res['nilaippn'];
				$gtotalInc += ($res['nilaiinvoice']+$res['nilaippn']);
				$gtotalPayment += $res['jumlah'];
				
				if (count($totalIncKg) == $rows) {
					$sData = "select distinct(a.nokontrak) 
							from ".$dbname.".pmn_kontrakjualdt a
							where a.nokontrak_ref='".$res['nokontrak']."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
					$rowz=owlBaris($qData);
                    if($rowz!=0)
                    {
                        $qData->setFetchMode(PDO::FETCH_ASSOC);
						while($rData=$qData->fetch())
                		{
							if($rData['posting']==1){
								$hPosting2 = "Posted";
							}else{
								$hPosting2 = "Not Posted";
							}
							
							if($rData['nilaippn']!=0){
								$hHargaSatuan2 = $rData['hargasatuan']/1.1;
							}else{
								$hHargaSatuan2 = $rData['hargasatuan'];
							}
							echo"<tr class=rowcontent style='color:green;'>
								<td></td>
								<td>".$rData['nokontrak']."</td>
								<td></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:center;'></td>
								<td></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:left;'></td>
								<td style='text-align:center;'></td>
								</tr>
							";
						}
					}
					echo"<tr class=rowcontent style='font-weight:bold;'>
							<td style='text-align:center'>Sub Total</td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:center'></td>
							<td style='text-align:right'>".number_format(array_sum($totalIncKg))."</td>
							<td style='text-align:right'></td>
							<td style='text-align:right'>".number_format(array_sum($totalIncHargaJual))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalIncPpn))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalInc))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalPayment))."</td>
							<td colspan='3'></td>
						</tr>";
						
					$totalIncKg = array();
					$totalIncHargaJual = array();
					$totalIncPpn = array();
					$totalInc = array();
					$totalPayment = array();
				}
			}
			echo"<tr class=rowcontent style='font-weight:bold;'>
					<td style='text-align:center' colspan=8>Grand Total</td>
					<td style='text-align:right'>".number_format($gtotalIncKg)."</td>
					<td style='text-align:right'></td>
					<td style='text-align:right'>".number_format($gtotalIncHargaJual)."</td>
					<td style='text-align:right'>".number_format($gtotalIncPpn)."</td>
					<td style='text-align:right'>".number_format($gtotalInc)."</td>
					<td style='text-align:right'>".number_format($gtotalPayment)."</td>
					<td colspan='3'></td>
				</tr>";
		}
		else
		{
				echo"<tr class=rowcontent align=center><td colspan=18>Not Found</td></tr>";
		}
		echo"</tbody></table>";
    break;
    
    
    
    
	
    case'getDetail':
    echo"<link rel=stylesheet type=text/css href=style/generic.css>";
    $nokontrak=$_GET['nokontrak'];
    $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
	$qHead=$owlPDO->query($sHed) or die(print " Gagal: ".PDOException::getMessage());
	$qHead->setFetchMode(PDO::FETCH_ASSOC);
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
            <td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td>".$nokontrak."</td>
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
    </table><br />
    <table cellspacing=1 border=0 class=sortable><thead>
    <tr class=data>
    <td>".$_SESSION['lang']['notransaksi']."</td>
    <td>".$_SESSION['lang']['tanggal']."</td>
    <td>".$_SESSION['lang']['nodo']."</td>
    <td>".$_SESSION['lang']['nosipb']."</td>
    <td>".$_SESSION['lang']['beratBersih']."</td>
    <td>".$_SESSION['lang']['kodenopol']."</td>
    <td>".$_SESSION['lang']['sopir']."</td>
    </tr></thead><tbody>
    ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/	

    $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."'";
    $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
    $rCek=owlBaris($qDet);
	$qDet->setFetchMode(PDO::FETCH_ASSOC);
	if($rCek>0)
    {
            while($rDet=$qDet->fetch())
            {
                    echo"<tr class=rowcontent>
                    <td>".$rDet['notransaksi']."</td>
                    <td>".tanggalnormal($rDet['tanggal'])."</td>
                    <td>".$rDet['nodo']."</td>
                    <td>".$rDet['nosipb']."</td>
                    <td align=right>".number_format($rDet['beratbersih'],2)."</td>
                    <td>".$rDet['nokendaraan']."</td>
                    <td>".ucfirst($rDet['supir'])."</td>
                    </tr>";
            }
    }
    else
    {
            echo"<tr><td colspan=7>Not Found</td></tr>";
    }
    echo"</tbody></table></fieldset>";

    break;

	case'excel':
		if($tanggalmulai=='')
		{
			echo"warning: Tanggal Dari Tidak Boleh Kosong";
			exit();
		}
		if($tanggalakhir=='')
		{
			echo"warning: Tanggal Sampai Tidak Boleh Kosong";
			exit();
		}

		$bgcoloraja="bgcolor=#DEDEDE align=center";
		$tab="<table class=sortable cellspacing=1 border=1>
				<thead>
				<thead>
				<tr class=rowheader>
					<td rowspan=2>".$_SESSION['lang']['komoditi']."</td>
					<td style='text-align:center;' colspan=5>".$_SESSION['lang']['kontrak']."</td>
					<td style='text-align:center;' rowspan=2>Term</td>
					<td style='text-align:center;' colspan=5>Invoice</td>
					<td style='text-align:center;' rowspan=2>Total Invoice</td>
					<td style='text-align:center;' rowspan=2>Payment</td>
					<td style='text-align:center;' rowspan=2>Outstanding Payment</td>
					<td style='text-align:center;' rowspan=2>No. Faktur</td>
					<td style='text-align:center;' rowspan=2>Status Posting</td>
				</tr>
				<tr>
					<td style='text-align:center;'>".$_SESSION['lang']['nomor']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['tanggal']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['kg']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['total']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['nomor']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['kg']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['hargaJual']."</td>
					<td style='text-align:center;'>".$_SESSION['lang']['ppn']."</td>
				</tr>
				</thead><tbody>";
				
		$sql="select b.namabarang, a.nokontrak, a.tanggalkontrak, a.kuantitaskontrak, a.hargasatuan, a.kdtermin, 
			c.noinvoice, c.nilaiinvoice, c.nilaippn, c.posting, c.nofakturpajak, d.jumlah, e.nofaktur  
			from ".$dbname.".keu_penagihanht c
			left join ".$dbname.".pmn_kontrakjual a
			on a.nokontrak = c.nokontrak
			left join ".$dbname.".log_5masterbarang b 
			on a.kodebarang = b.kodebarang
			left join ".$dbname.".keu_kasbankdt d
			on c.noinvoice = d.keterangan1
			left join ".$dbname.".pmn_faktur e 
			on a.nokontrak = e.nokontrak and c.noinvoice = e.noinvoice 
			where a.tanggalkontrak between '".$tanggalmulai."' and '".$tanggalakhir."' and 
			c.kodept = '".$idPabrik."'
			";
		if(!empty($kdBrg)) {
			$sql .= " and a.kodebarang = '".$kdBrg."'";
		}
		$sql .= " order by a.nokontrak";
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$row=owlBaris($query);
                    
		
		
		$gtotalIncHargaJual=$gtotalIncPpn=$gtotalInc=$gtotalIncKg  =$gtotalPayment =0;
		if($row>0){
                        
                        
                        
        $query->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$query->fetch())
                {
                                
                                
				$no+=1;
				$item = explode(";",$res['kdtermin']);
				$itemCount = count($item);
				
				$sInv="select * from ".$dbname.".keu_penagihanht where nokontrak='".$res['nokontrak']."'";
                $qInv=$owlPDO->query($sInv) or die(print " Gagal: ".PDOException::getMessage());
                $qInv->setFetchMode(PDO::FETCH_ASSOC);
                $rInv=$qInv->fetch();
				
				$sSub="select count(nokontrak) from ".$dbname.".keu_penagihanht where nokontrak = '".$res['nokontrak']."'";
                $qSub=$owlPDO->query($sSub) or die(print " Gagal: ".PDOException::getMessage());
                $qSub->setFetchMode(PDO::FETCH_NUM);
                $cAry=$qSub->fetch();
				$rows = $cAry[0];
				
				if($res['posting']==1){
					$hPosting = "Posted";
				}else{
					$hPosting = "Not Posted";
				}
				
				if($res['nilaippn']!=0){
					$hHargaSatuan = $res['hargasatuan']/1.1;
				}else{
					$hHargaSatuan = $res['hargasatuan'];
				}
				
				// print_r($itemCount);
				
				$tab.="<tr class=rowcontent>
						<td>".$res['namabarang']."</td>
						<td>".$res['nokontrak']."</td>
						<td>".tanggalnormal($res['tanggalkontrak'])."</td>
						<td style='text-align:right;'>".number_format($res['kuantitaskontrak'])."</td>
						<td style='text-align:right;'>".number_format($res['hargasatuan'])."</td>
						<td style='text-align:right;'>".number_format($res['kuantitaskontrak'] * $res['hargasatuan'])."</td>
						<td style='text-align:center;'>".$res['kdtermin']."</td>
						<td>".$res['noinvoice']."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice']/$hHargaSatuan)."</td>
						<td style='text-align:right;'>".number_format($hHargaSatuan)."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice'])."</td>
						<td style='text-align:right;'>".number_format($res['nilaippn'])."</td>
						<td style='text-align:right;'>".number_format($res['nilaiinvoice']+$res['nilaippn'])."</td>
						<td style='text-align:right;'>".number_format($res['jumlah'])."</td>
						<td style='text-align:right;'>".number_format(($res['nilaiinvoice']+$res['nilaippn'])-$res['jumlah'])."</td>
						<td style='text-align:left;'>".$res['nofaktur']."</td>
						<td style='text-align:center;'>".$hPosting."</td>
						</tr>
					";
					
				$totalIncKg[] = $res['nilaiinvoice']/$hHargaSatuan;
				$totalIncHargaJual[] = $res['nilaiinvoice'];
				$totalIncPpn[] = $res['nilaippn'];
				$totalInc[] = $res['nilaiinvoice']+$res['nilaippn'];
				$totalPayment[] = $res['jumlah'];
				
				$gtotalIncKg += ($res['nilaiinvoice']/$hHargaSatuan);
				$gtotalIncHargaJual += $res['nilaiinvoice'];
				$gtotalIncPpn += $res['nilaippn'];
				$gtotalInc += ($res['nilaiinvoice']+$res['nilaippn']);
				$gtotalPayment += $res['jumlah'];
				
		if (count($totalIncKg) == $rows) {
					$sData = "select distinct(a.nokontrak) 
							from ".$dbname.".pmn_kontrakjualdt a
							where a.nokontrak_ref='".$res['nokontrak']."'";
                                            
					$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
					$rowz=owlBaris($qData);

                    if($rowz!=0)
                    {
                    $qData->setFetchMode(PDO::FETCH_ASSOC);
					while($rData=$qData->fetch())
			            {
							if($rData['posting']==1){
								$hPosting2 = "Posted";
							}else{
								$hPosting2 = "Not Posted";
							}
							
							if($rData['nilaippn']!=0){
								$hHargaSatuan2 = $rData['hargasatuan']/1.1;
							}else{
								$hHargaSatuan2 = $rData['hargasatuan'];
							}
							$tab.="<tr class=rowcontent style='color:green;'>
								<td></td>
								<td>".$rData['nokontrak']."</td>
								<td></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:center;'></td>
								<td></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:right;'></td>
								<td style='text-align:left;'></td>
								<td style='text-align:center;'></td>
								</tr>
							";
						}
					}
					$tab.="<tr class=rowcontent style='font-weight:bold;'>
							<td style='text-align:center' colspan=8>Sub Total</td>
							<td style='text-align:right'>".number_format(array_sum($totalIncKg))."</td>
							<td style='text-align:right'></td>
							<td style='text-align:right'>".number_format(array_sum($totalIncHargaJual))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalIncPpn))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalInc))."</td>
							<td style='text-align:right'>".number_format(array_sum($totalPayment))."</td>
							<td colspan='3'></td>
						</tr>";
						
					$totalIncKg = array();
					$totalIncHargaJual = array();
					$totalIncPpn = array();
					$totalInc = array();
					$totalPayment = array();
				}
			}
			$tab.="<tr class=rowcontent style='font-weight:bold;'>
					<td style='text-align:center' colspan=8>Grand Total</td>
					<td style='text-align:right'>".number_format($gtotalIncKg)."</td>
					<td style='text-align:right'></td>
					<td style='text-align:right'>".number_format($gtotalIncHargaJual)."</td>
					<td style='text-align:right'>".number_format($gtotalIncPpn)."</td>
					<td style='text-align:right'>".number_format($gtotalInc)."</td>
					<td style='text-align:right'>".number_format($gtotalPayment)."</td>
					<td colspan='3'></td>
				</tr>";
		}
		else
		{
				$tab.="<tr class=rowcontent align=center><td colspan=18>Not Found</td></tr>";
		}
			
		$tab.="</tbody></table>";
		$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		$dte=date("Hms");
		$nop_="RekapKontrakInvoicedanFakturPajak_".$dte;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $tab);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
		window.location='tempExcel/".$nop_.".xls.gz';
		</script>";	
	break;
	
    default:
    break;
}
}

?>