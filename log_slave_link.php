<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method=checkPostGet('method','');

$notransaksi=checkPostGet('notransaksi','');
$norph=checkPostGet('norph','');
$nopp=checkPostGet('nopp','');
$kd_brg=checkPostGet('kodebarang','');
$supplierid=checkPostGet('supplierid','');
$kriteriaefil=checkPostGet('kriteriaefil','');
$namafile=checkPostGet('namafile','');


switch ($method)
{
	case'previewlink':
		$formPil=0;
        $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optStock=$optTermPay;
        $optKrm=$optTermPay;
        $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
        $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");
		
		$str="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{

			if($bar['pph22'] > 0){
				$pph_ = $bar['pph22'];
				$judul_pph_ = 'PPh Ps 22';
			}else{
				$pph_ = $bar['pph'];
				$judul_pph_ = 'PPh Ps 23';
			}

			$dtNomor[]=$bar['nourut'];
            $dtSupp[$bar['nourut']]=$bar['supplierid'];
            $dtFranco[$bar['nourut']]=$bar['id_franco'];
            $dtStock[$bar['nourut']]=$bar['stock'];
            $dtCattn[$bar['nourut']]=$bar['catatan'];
            $dtSisbyr[$bar['nourut']]=$bar['sisbayar'];
            $dtSisbyr2[$bar['nourut']]=$bar['sisbayar2'];
            $dttotalongkir[$bar['nourut']]=$bar['ongkir'];
            $dtPpn[$bar['nourut']]=$bar['ppn'];
			// $arrNilaiPPN[$bar['nourut']] = ($bar['ppn']/100) * ($bar['subtotal'] - $bar['nilaidiskon'] + $bar['pbbkb']);
			$arrNilaiPPN[$bar['nourut']] = ($bar['ppn']/100) * ($bar['subtotal'] - $bar['nilaidiskon']);
            $dtPph[$bar['nourut']]= $pph_;
			// $arrNilaiPPH[$bar['nourut']] = ($bar['pph']/100) * ($bar['subtotal'] - $bar['nilaidiskon'] + $bar['pbbkb']);

			//PPH FINAL
			if($bar['pphfinal'] > 0){
				$arrNilaiPPH[$bar['nourut']] = $bar['pphfinal'];
			}else{
				$arrNilaiPPH[$bar['nourut']] = ($pph_/100) * ($bar['subtotal'] - $bar['nilaidiskon']);
			}

            $dtPbbkb[$bar['nourut']]=$bar['pbbkb'];
            $dtSbtotal[$bar['nourut']]=$bar['subtotal'];
            $dtDisknPrsn[$bar['nourut']]=$bar['diskonpersen'];
            $dtNildis[$bar['nourut']]=$bar['nilaidiskon'];
            $dtNilPer[$bar['nourut']]=$bar['nilaipermintaan'];
            $dtMtuang[$bar['nourut']]=$bar['matauang'];
            $dtTglDr[$bar['nourut']]=$bar['tgldari'];
            $dtTglSmp[$bar['nourut']]=$bar['tglsmp'];
            $kurs[$bar['nourut']]=$bar['kurs'];
            $dtCttn[$bar['nourut']]=$bar['catatan'];
			
            $dtdurasipengiriman[$bar['nourut']]=$bar['durasipengiriman'];
            $dtdurasipekerjaan[$bar['nourut']]=$bar['durasipekerjaan'];
            $dtgaransiproduk[$bar['nourut']]=$bar['garansiproduk'];
            $dtposisistok[$bar['nourut']]=$bar['posisistok'];
            $dtasuransi[$bar['nourut']]=$bar['asuransi'];
			
			$style[$bar['nourut']]="";
			if($bar['supplierid']==$supplierid){
				$style[$bar['nourut']] = "background-color:#D6F097";
			}
		}
		

		$str="select distinct kodebarang,jumlah,nomor,harga,merk,spec,nourut,hargaterakhir,nopp,ongkir from ".$dbname.".log_permintaanhargadt where nomor='".$notransaksi."' order by kodebarang";
		//exit('warning'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$str2="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$bar['nopp']."' and kodebarang='".$bar['kodebarang']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2=$res2->fetch();
			$arrKonversi[$bar['kodebarang']]=$bar2['satuankonversi'];
			
			if($bar['harga']=='')
            {
				$bar['harga']=0;
			}
			
			$dtSub[$bar['nourut']][$bar['kodebarang']]=floatval($bar['jumlah'])*floatval($bar['harga']);
            $dtHarga[$bar['nourut']][$bar['kodebarang']]=$bar['harga'];
            $dtMerk[$bar['nourut']][$bar['kodebarang']]=$bar['spec'];
            $dtMrk[$bar['nourut']][$bar['kodebarang']]=$bar['merk'];
            $dtongkir[$bar['nourut']]=$bar['ongkir'];
            $dtJumlah[$bar['nourut']][$bar['kodebarang']]=$bar['jumlah'];
            $arrJmlh[$bar['kodebarang']]=$bar['jumlah'];
            $listBarang[$bar['kodebarang']]=$bar['kodebarang'];
            $lstnopp[$bar['kodebarang']]=$bar['nopp'];
            $dthargaterakhir[$bar['kodebarang']]=$bar['hargaterakhir'];
		}
		
		$tab="<table cellspacing=1 cellpadding=3 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<td rowspan=2 align=center>No.</td>
                <td rowspan=2 align=center width=50px>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=2 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=2 align=center width='30px'>".$_SESSION['lang']['satuan']."</td>
                <td rowspan=2 align=center width='30px'>".$_SESSION['lang']['nopp']."</td>
                <td rowspan=2 align=center width='30px'>Harga Terakhir</td>";
                
			foreach ($dtNomor as $brs)
			{
				$optSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$dtSupp[$brs]."'");
				$tab.="<td colspan=4 align=center>".$optSup[$dtSupp[$brs]]."</td>";
			}
			
			$tab.="</tr><tr>";
			
			foreach ($dtNomor as $brs)
			{
				$tab.="<!--<td  align=center width=55px>".$_SESSION['lang']['merk']."</td>-->
				<td  align=center width=55px>".$_SESSION['lang']['spesifikasi']."</td>
				<td  align=center width=40px>".$_SESSION['lang']['jumlah']."</td>
				<td  align=center width=40px>".$_SESSION['lang']['harga']."</td>
				<td align=center width=40px>".$_SESSION['lang']['subtotal']."</td>";
			}
			
			$tab.="<tr>
			</thead>
			<tbody>";
			
			$totRow=count($dtNomor);
			$totBrg=count($listBarang);
			
			$no=0;
			if(count($listBarang)!=0){
				foreach($listBarang as $brsKdBrg){
					$no+=1;
					$hargasbldiskon = 0;
					$arrNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$brsKdBrg."'");
					$optSat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$brsKdBrg."'");
					if($arrKonversi[$brsKdBrg]=='' || is_null($arrKonversi[$brsKdBrg])){
						$mySatuan = $optSat[$brsKdBrg];
					}else{
						$mySatuan = $arrKonversi[$brsKdBrg];
					}
					
					$str="select a.hargasbldiskon from ".$dbname.".log_podt a 
					left join ".$dbname.".log_poht b on a.nopo=b.nopo
					where a.kodebarang='".$brsKdBrg."' order by b.tanggal desc limit 1";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						$hargasbldiskon = $bar['hargasbldiskon'];
					}
					
	                $tab.="<tr class='rowcontent'>
						<td align=center>".$no."</td>
						<td id='kd_brg_".$no."'>".$brsKdBrg."</td>
						<td colspan=2 title='".$arrNmBrg[$brsKdBrg]."'>".$arrNmBrg[$brsKdBrg]."</td>
						<td align=center>".$mySatuan."</td>
						<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$lstnopp[$brsKdBrg]."','".$brsKdBrg."',event)\">".$lstnopp[$brsKdBrg]."</td>
						<td align=right><label id='hargaterakhir_".$no."'>".($dthargaterakhir[$brsKdBrg]==0?0:number_format($dthargaterakhir[$brsKdBrg],2))."</label></td>";
					
					foreach ($dtNomor as $brs){
						$tab.="<!--<td align=left style='".$style[$brs]."'>".$dtMrk[$brs][$brsKdBrg]."</td>-->
						<td align=left style='".$style[$brs]."'>".$dtMerk[$brs][$brsKdBrg]."</td>
						<td align=right style='".$style[$brs]."'>".number_format($dtJumlah[$brs][$brsKdBrg],2)."</td>
						<td align=right style='".$style[$brs]."'>".number_format($dtHarga[$brs][$brsKdBrg],0)."</td>
						<td align=right style='".$style[$brs]."'>".number_format($dtSub[$brs][$brsKdBrg],0)."</td>";
					}
					$tab.="</tr>";
				}
				
			}

			// list material
			$_SESSION['somaterial']=array();
			$countsupplier=0;
			$str="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$_POST['notransaksi']."' order by supplierid asc";
			$res=fetchdata($str);
			foreach($res as $val){
				$dtNomor2[]=$val['nourut'];
				$countsupplier++;
			}

			$nobaris=0;
			$strx="select * from ".$dbname.".log_somaterial_perbandingan where nodph='".$_POST['notransaksi']."'";
			$resx=fetchdata($strx);
			$tempnamabarang="";
			foreach($resx as $valx){
				if($tempnamabarang!=$valx['namabarang']){
					$nobaris++;
				}
				$newdata = array('no_prmntan'=>$valx['nodph'],'baris'=>$nobaris,'nourut'=>$valx['nourut'],'supplier'=>$valx['supplierid'],'namabarang'=>$valx['namabarang'],'jumlah'=>$valx['jumlah'],'hargasatuan'=>$valx['harga']);
				array_push($_SESSION['somaterial'],$newdata);
				$tempnamabarang=$valx['namabarang'];
			}
			$nobaris=0;
			$tempnamabarang="";
			$totalrows=(count($_SESSION['somaterial'])/$countsupplier);
			$countcell=max($dtNomor2);

			// echo "<pre>";
			// print_r($dtNomor2);

			$tempnamabarang="";
			if(count($_SESSION['somaterial']) > 0){
				$nom = 1;
				$tab.="<tr class='rowcontent'><td></td><td style='font-weight:bold' colspan=6".+($countsupplier*4).">LIST MATERIAL</td></tr>";


				foreach($_SESSION['somaterial'] as $key=>$val){
					$namabarang=$val['namabarang'];
				$baris=$val['baris'];
				if($tempnamabarang!=$namabarang){
					$tab.="<tr class='rowcontent'>";
					$tab.="<td style='text-align:center'>
						".$no++."
					</td>";
					$tab.="<td colspan=3 id='dataSO_".$baris."'>".$val['namabarang']."</td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					
					foreach ($dtNomor2 as $cell){
						foreach($_SESSION['somaterial'] as $keyx=>$valx){
							if($valx['baris'] == $baris && $valx['nourut']==$cell){
								$qty=$valx['jumlah'];
								$harga=$valx['hargasatuan'];
							}
						}
						$totalharga=$qty*$harga;
						$tab.="<td></td>";
						$tab.="<td align=right style='".$style[$brs]."'>".number_format($qty,2)."</td>";
						$tab.="<td align=right style='".$style[$brs]."'>".number_format($harga,0)."</td>";
						$tab.="<td align=right style='".$style[$brs]."'>".number_format($totalharga,0)."</td>";
					}
					$tab.="</tr>";
					$tempnamabarang=$namabarang;
				}
			}
		}
			// end list material
			
			
			####ONGKIR####
			$tab.="<tr class='rowcontent'>
				<td rowspan=7 colspan=4 valign=top align=left >&nbsp</td><td colspan=3>".$_SESSION['lang']['ongkoskirim']."</td>";
				
				foreach ($dtNomor as $brs){
					$tab.="<td align=right colspan=3 style='".$style[$brs]."'>".number_format($dtongkir[$brs],0)."</td>
				<td align=right style='".$style[$brs]."'>".number_format($dttotalongkir[$brs],0)."</td>";
			}
			
			
			####SUBTOTAL####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top align=left >".$_SESSION['lang']['subtotal']."</td>";
				
			foreach ($dtNomor as $brs){
				$tab.="<td align=right colspan=4 id=total_harga_po_".$brs." style='".$style[$brs]."'>".number_format($dtSbtotal[$brs],0)."</td>";
			}
			
			$tab.="</tr>";
			
			####DISKON####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['diskon']."</td>";
				
			foreach ($dtNomor as $brs){
				$tab.="<td align=right colspan=3 style='".$style[$brs]."'>".number_format($dtDisknPrsn[$brs],2)."%</td>
				<td align=right style='".$style[$brs]."'>".number_format($dtNildis[$brs],0)."</td>";
			}
			$tab.="</tr>";
			
			####PPN####
			$tab.="<tr class='rowcontent'><td colspan=3>".$_SESSION['lang']['ppn']."</td>";
			
			foreach ($dtNomor as $brs){
				$tab.="<td align=right colspan=3 style='".$style[$brs]."'>".number_format($dtPpn[$brs],2)."%</td>";
				$tab.="<td align=right colspan=1 style='".$style[$brs]."'>".number_format($arrNilaiPPN[$brs],2)."</td>";
			}
			$tab.="</tr>";

			####PPH####
			$tab.="<tr class='rowcontent'><td colspan=3>".$_SESSION['lang']['pph']."</td>";

			foreach ($dtNomor as $brs){

				// jika PPH Final → persen tidak ditampilkan
				if($arrNilaiPPH[$brs] == $dtPph[$brs]/100 * ($barSubtotal[$brs] ?? 0)){
					// kondisi ini opsional kalau kamu tidak punya flag pphfinal
					$tab.="<td align=right colspan=3 style='".$style[$brs]."'>".number_format($dtPph[$brs],2)."%</td>";
				} else {
					// PPH Final
					$tab.="<td align=right colspan=3 style='".$style[$brs]."'>&nbsp;</td>";
				}

				$tab.="<td align=right colspan=1 style='".$style[$brs]."'>".number_format($arrNilaiPPH[$brs],2)."</td>";
			}

			$tab.="</tr>";

			
			####PBBKB####
			$tab.="<tr class='rowcontent'><td colspan=3>PBBKB</td>";
			
			foreach ($dtNomor as $brs){
				$tab.="<td align=right colspan=4 style='".$style[$brs]."'>".number_format($dtPbbkb[$brs],0)."</td>";
			}
			$tab.="</tr>";
			
			####GRANDTOTAL####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['grnd_total']."</td>";
				
			foreach ($dtNomor as $brs){
				$tab.="<td align=right colspan=4 id=grand_total_".$brs." style='".$style[$brs]."'>".hidezerodecimal($dtNilPer[$brs],2)."</td>";
			}
			$tab.="</tr>";
			
			####NO PERMINTAAN HARGA####
			$tab.="<tr class='rowcontent'>
				<td rowspan=12 colspan=4 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>
				<td colspan=3>No. RPH</td>";	
			foreach ($dtNomor as $brs){
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$_POST['notransaksi']."</td>";
			}
			
			$tab.="</tr>";
			
			####MATA UANG####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['matauang']."</td>";
            foreach ($dtNomor as $brs){
				$optMt="";
                
				$optMt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $str="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					if($dtMtuang[$brs]!=''){
						$optMt.="<option value=".$bar['kode']." ".($dtMtuang[$brs]==$bar['kode']?"selected":" ").">".$bar['kodeiso']."</option>";
					}
					else
					{
						$optMt.="<option value=".$bar['kode'].">".$bar['kodeiso']."</option>";
					}
				}
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$dtMtuang[$brs]."</td>";
			}
			
			$tab.="</tr>";
			
			####KURS####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['kurs']."</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$kurs[$brs]."</td>";
			}
			$tab.="</tr>";
			
			####SYARAT PEMBAYARAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['syaratPem']."</td>";
			foreach ($dtNomor as $brs){
				$str="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$dtSisbyr2[$brs]."' order by keterangan asc";
				$res=fetchdata($str);
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$res[0]['keterangan']." (".$res[0]['jenis'].")</td>";
			}
			$tab.="</tr>";
			
			####STOCK####
			$tab.="<tr class='rowcontent' style='display:none'>
				<td colspan=3>".$_SESSION['lang']['stock']."</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$arrStock[$dtStock[$brs]]."</td>";
			}
			$tab.="</tr>";
			
			####LOKASI PENGIRIMAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['almt_kirim']."</td>";
			foreach ($dtNomor as $brs){
				$optfranco = makeOption($dbname,'setup_franco','id_franco,franco_name',"id_franco='".$dtFranco[$brs]."'");
				$tab.="<td colspan=4 style='".$style[$brs]."'>".$optfranco[$dtFranco[$brs]]."</td>";
			}
			$tab.="</tr>";
			
			####DURASI PENGIRIMAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>Durasi Pengiriman</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtdurasipengiriman[$brs])? $dtdurasipengiriman[$brs]: '')."</td>";
			}
			$tab.="</tr>";
			
			####DURASI PEKERJAAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>Durasi Pekerjaan</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtdurasipekerjaan[$brs])? $dtdurasipekerjaan[$brs]: '')."</td>";
			}
			$tab.="</tr>";
			
			####GARANSI PRODUK/JASA####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>Garansi Produk/Jasa</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtgaransiproduk[$brs])? $dtgaransiproduk[$brs]: '')."</td>";
			}
			
			$tab.="</tr>";
			
			####POSISI STOK BARANG####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>Posisi Stok Barang</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtposisistok[$brs])? $dtposisistok[$brs]: '')."</td>";
			}
			$tab.="</tr>";
			
			####ASURANSI####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>Asuransi</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtasuransi[$brs])? $dtasuransi[$brs]: '')."</td>";
			}
			$tab.="</tr>";
			
			####KETERANGAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>".$_SESSION['lang']['keterangan']."</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td align=justify colspan=4 style='".$style[$brs]."'>".(isset($dtCttn[$brs])? $dtCttn[$brs]: '')."</td>";
			}
			$tab.="</tr>";
			
			####FILEUPLOAD####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>".$_SESSION['lang']['uploaddata']."</td>";
			foreach ($dtNomor as $brs){
				$tab.="<td colspan=4 valign=top>
				<div id='listfiles_".$_POST['notransaksi']."_".$dtSupp[$brs]."'><table>";
				$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$_POST['notransaksi']."' and supplierid='".$dtSupp[$brs]."' and status='1'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$nofiles = 0;
				while($bar=$res->fetch())
				{
					$nofiles++;
					$tab.="<tr style='".$style[$brs]."'>
						<td ><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".substr($bar['namafile'],0,40)."...</a></td>
						<td style='display:none'>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$_POST['notransaksi']."','".$dtSupp[$brs]."','".$bar['namafile']."');\" >
						</td>
					</tr>";
				}
				$tab.="</table></div>";
				$tab.="</td>";
			}
			
			$tab.="</tr>
			</tbody>
		</table>";
		
		echo $tab;		
	break;
	
	case'previewlinkpp':
		
		$formPil=0;
        $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optStock=$optTermPay;
        $optKrm=$optTermPay;
        $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
        $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");
		
		##Get Supplier & Purchaser
		$arrSup = $arrPur = array();

		$str="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$norph."'";
		$ada_d=fetchdata($str);
		if(count($ada_d) > 0){
			$str="select * from ".$dbname.".log_perintaanhargaht where nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nopp='".$notransaksi."' and kodebarang='".$kd_brg."' and norph='".$norph."') and flag='1' and tolakrph='0'";
		}else{
			$str="select * from ".$dbname.".log_perintaanhargaht where nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nopp='".$notransaksi."' and kodebarang='".$kd_brg."' and nomor='".$norph."') and flag='1' and tolakrph='0'";
		}

		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$optNmSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");
			$optNmPur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['purchaser']."'");
			$arrSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrNmSup[$val['purchaser']][$val['supplierid']] = $optNmSup[$val['supplierid']];
			$arrPur[$val['purchaser']] = $val['purchaser'];
			$arrNmKar[$val['purchaser']] = $optNmPur[$val['purchaser']];
			$arrCountSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
		}
		
		##Get Nomor RPH
		$arrKdBrg = array();
		if(count($ada_d) > 0){
			$str="select b.nilaipermintaan,b.pph22,b.ppn,b.nilaipermintaan,b.pph,a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,a.ongkir,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi, b.pphfinal from ".$dbname.".log_permintaanhargadt a
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nopp='".$notransaksi."' and kodebarang='".$kd_brg."' and norph='".$norph."')
			where a.nopp='".$notransaksi."' and a.kodebarang='".$kd_brg."' and b.tolakrph='0' order by b.nourut asc, a.kodebarang";
		}else{
			$str="select b.nilaipermintaan,b.pph22,b.ppn,b.nilaipermintaan,b.pph,a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,a.ongkir,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi, b.pphfinal from ".$dbname.".log_permintaanhargadt a
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nopp='".$notransaksi."' and kodebarang='".$kd_brg."' and nomor='".$norph."')
			where a.nopp='".$notransaksi."' and a.kodebarang='".$kd_brg."' and b.tolakrph='0' order by b.nourut asc, a.kodebarang";
		}
		if($val['pph22'] > 0){
			$pph_ = $val['pph22'];
			$judul_pph_ = 'PPh Ps 22';
		}else{
			$pph_ = $val['pph'];
			$judul_pph_ = 'PPh Ps 23';
		}
		// and b.nomor not in (select nomor from ".$dbname.".log_permintaanhargadt where flag='1' and kodebarang='".$kd_brg."' and nopp='".$notransaksi."')
		$res=fetchdata($str);
		$nourut = 0;
		foreach($res as $key=>$val){
			$nourut++;
			$tglrph = $val['tanggal'];
			$arrKdBrg[$val['kodebarang']] = $val['kodebarang'];
			$arrHarga[$val['kodebarang']] = $hrgtrk;
			
			$arrSupplier2[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrSupplier[$val['supplierid']] = $val['supplierid'];
			
			if($val['supplierid']==$supplierid){
				$style[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']] = "background-color:#D6F097";
			}
			
			$optMrk = makeOption($dbname,'log_5merkbaranght','idmerk,merk',"idmerk='".$val['merk']."'");
			
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['merk'] = $optMrk[$val['merk']];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['spec'] = $val['spec'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['jumlah'] = $val['jumlah'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['harga'] = $val['harga'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['hargadiskon'] = ($val['diskonpersen']==0?$val['harga']:($val['harga'] - $val['nilaidiskon']));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nodph'] = $val['nomor'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nourut'] = $val['nourut'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['diskonpersen'] = $val['diskonpersen'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nilaidiskon'] = ($val['diskonpersen']==0?'0':($val['nilaidiskon']));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pbbkb'] = $val['pbbkb'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['ppn'] = ($val['ppn']/100) * ( ($val['jumlah'] * $val['harga']) - $val['nilaidiskon']);

			//pph final 
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['is_pph_final']
				= ($val['pphfinal'] > 0);

			if($val['pphfinal'] > 0){
				// PPH Final → nominal langsung
				$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pph']
					= $val['pphfinal'];
			}else{
				$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pph'] = ($pph_/100) * ( ($val['jumlah'] * $val['harga']) - $val['nilaidiskon']);
			}

			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['matauang'] = $val['matauang'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['kurs'] = $val['kurs'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tgldari'] = $val['tgldari'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tglsmp'] = $val['tglsmp'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['keterangan'] = $val['catatan'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipengiriman'] = $val['durasipengiriman'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipekerjaan'] = $val['durasipekerjaan'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['garansiproduk'] = $val['garansiproduk'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['posisistok'] = $val['posisistok'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['asuransi'] = $val['asuransi'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nilaipermintaan'] = $val['nilaipermintaan'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['ongkir'] = $val['ongkir'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['totalongkir'] = ($val['ongkir']*$val['jumlah']);
		}
		
		$tab.=getdph($norph,$notransaksi,$kd_brg,$supplierid);
		
		$tab.="<br><hr><br>";
		
		$tab.="<table cellspacing=1 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<td rowspan=3 align=center>No.</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=3 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['satuan']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['nopp']."</td>
                <td rowspan=3 align=center>Harga Terakhir</td>";
				
		foreach($arrPur as $val){
			$tab.="<td colspan='".(count($arrCountSup[$val])*4)."' style='font-weight:bold;text-align:center'>".$arrNmKar[$val]."</td>";
		}
		
		$tab.="</tr></tr>";
		
		foreach($arrPur as $val){
			foreach($arrSupplier as $val2){
				if($val2==$arrSupplier2[$val][$val2])
				{
					$tab.="<td colspan=4 style='text-align:center'>".$arrNmSup[$val][$val2]."</td>";
				}
			}
		}
		$tab.="</tr></tr>";
		foreach($arrPur as $val){
			foreach($arrSup[$val] as $val2){
				$tab.="<!--<td  align=center>".$_SESSION['lang']['merk']."</td>-->
					<td  align=center>".$_SESSION['lang']['spesifikasi']."</td>
					<td  align=center>".$_SESSION['lang']['jumlah']."</td>
					<td  align=center>".$_SESSION['lang']['harga']."</td>
					<td align=center>".$_SESSION['lang']['subtotal']."</td>";
			}
		}
		
		$tab.="<tr>
			</thead>
			<tbody>";
		
		$nobrg = 0;
		foreach($arrKdBrg as $val){
			$str="select * from ".$dbname.".log_prapoht where nopp='".$notransaksi."'";
			$res=fetchData($str);
			$tmpTgl = explode('-',$res[0]['tanggal']);
			$tgldari = "01-12-".$tmpTgl[0];
			$tgldarilalu = "01-01-".($tmpTgl[0]-1);
			$unit=$res[0]['unit'];
			
			## GET HARGA TERAKHIR
			$hrgtrk = 0;
			$tgltrk = "";
			$strx="select hargasatuan,tanggal from ".$dbname.".log_5hargaterakhir where unit='".$unit."' and kodebarang='".$val."' and status='1'";
			$resx=fetchdata($strx);
			if(count($resx) > 0){
				$hrgtrk = $resx[0]['hargasatuan'];
			}
			
			$no++;
			$optNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val."'");
			$optSatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val."'");
			$strx="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$notransaksi."' and kodebarang='".$val."'";
			$resx=fetchdata($strx);
			if($resx[0]['satuankonversi']=='' || is_null($resx[0]['satuankonversi'])){
				$mySatuan = $optSatuan[$val];
			}else{
				$mySatuan = $resx[0]['satuankonversi'];
			}
			$tab.="<tr class='rowcontent'>
				<td align=center>".$no."</td>
				<td id='kd_brg_".$no."' style='color:blue;cursor:pointer' onclick=showdocpakaibarang('".$tgldari."','".tanggalnormal($res[0]['tanggal'])."','".substr($notransaksi,-4)."','".$val."','event') title='Detail Pemakaian Barang'>".$val."</td>
				<td colspan=2 style='color:blue;cursor:pointer' onclick=showdocpembelianterakhir('".$tgldarilalu."','".tanggalnormal($res[0]['tanggal'])."','".substr($notransaksi,-4)."','".$val."','event') title='Detail Pembelian Terakhir'>".$optNmBrg[$val]."</td>
				<td align=center title='".$mySatuan."'>".$mySatuan."</td>
				<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$notransaksi."','".$val."',event)\">".$notransaksi."</td>
				<td align=right><label id='hargaterakhir_".$no."'>".($hrgtrk==''?0:number_format($hrgtrk,2))."</label></td>";
			
			foreach($arrPur as $val2){
				foreach($arrSupplier as $val3)
				{
					if($val3==$arrSupplier2[$val2][$val3])
					{
						$subtotal = ($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['jumlah'] * $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['harga']);
						$tab.="<!--<td align=justify style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['merk']."</td>-->
							<td align=justify style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['spec']."</td>
							<td align=center style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['jumlah']."</td>
							<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['harga'])."</td>
							<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($subtotal)."</td>
						";
						$arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'] = ($subtotal + $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['totalongkir']);
					}
				}
			}
		}		
		$tab.="</tr>";
		
		####ONGKIR####
		$tab.="<tr class='rowcontent'>
			<td rowspan=7 colspan=4 valign=top align=left >&nbsp</td><td colspan=3>".$_SESSION['lang']['ongkoskirim']."</td>";
			
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=3 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".hidezerodecimal($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['ongkir'],2)."</td>";
					$tab.="<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".hidezerodecimal($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['totalongkir'],2)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####SUBTOTAL####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['subtotal']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DISKON####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['diskon']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=3 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['diskonpersen']==0?'0':number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['diskonpersen'],2))."</td>
					<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nilaidiskon'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####PBBKB####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>PBBKB</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pbbkb'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";

		####PPN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>PPN</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['ppn'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";

		$isPphFinalRow = false;

		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					if(!empty($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['is_pph_final'])){
						$isPphFinalRow = true;
					}
				}
			}
		}

		$labelPph = $isPphFinalRow ? 'PPH Final' : 'PPH';

		####PPH####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$labelPph."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pph'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		

		####GRANDTOTAL####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['grnd_total']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					// $grandtotal = $arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'] - $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nilaidiskon'] + $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pbbkb'];
					$grandtotal = $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nilaipermintaan'];
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($grandtotal,0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####NO PERMINTAAN HARGA####
		$tab.="<tr class='rowcontent'>
			<td rowspan=12 colspan=4 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>
			<td colspan=3>No. RPH</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####MATA UANG####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['matauang']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['matauang']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####KURS####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['kurs']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['kurs']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####TANGGAL DARI####
		// $tab.="<tr class='rowcontent'>
			// <td colspan=3>".$_SESSION['lang']['tgldari']."</td>";
		// foreach($arrPur as $val2){
			// foreach($arrSupplier as $val3){
				// if($val3==$arrSupplier2[$val2][$val3]){
					// $tab.="<td align=right colspan=5 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['tgldari']."</td>";
				// }
			// }
		// }
		// $tab.="</tr>";
		
		####TANGGAL SAMPAI####
		// $tab.="<tr class='rowcontent'>
			// <td colspan=3>".$_SESSION['lang']['tglsmp']."</td>";
		// foreach($arrPur as $val2){
			// foreach($arrSupplier as $val3){
				// if($val3==$arrSupplier2[$val2][$val3]){
					// $tab.="<td align=right colspan=5 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['tglsmp']."</td>";
				// }
			// }
		// }
		// $tab.="</tr>";
		
		####SYARAT PEMBAYARAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['syaratPem']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$str="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['syaratbayar']."'";
					$res=fetchData($str);
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$res[0]['keterangan']." (".$res[0]['jenis'].")</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####LOKASI PENGIRIMAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['almt_kirim']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$optfranco = makeOption($dbname,'setup_franco','id_franco,franco_name',"id_franco='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['lokasikirim']."'");
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$optfranco[$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['lokasikirim']]."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DURASI PENGIRIMAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Durasi Pengiriman</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['durasipengiriman']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DURASI PEKERJAAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Durasi Pekerjaan</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['durasipekerjaan']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####GARANSI PRODUK/JASA####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Garansi Produk/Jasa</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['garansiproduk']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####POSISI STOK BARANG####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Posisi Stok Barang</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['posisistok']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####ASURANSI####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Asuransi</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['asuransi']."</td>";
				}
			}
		}
		$tab.="</tr>";

		####KETERANGAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3 valign=top>".$_SESSION['lang']['keterangan']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['keterangan']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####FILEUPLOAD####
		# permintaan dafi jika PO belum di setujui penuh masih bisa upload file
		$nopo = '';
		$str = "select * from ".$dbname.".log_poht where 1=1 and nodph='".$norph."' and kodesupplier='".$supplierid."' and statuspo<='1' and stat_release is null";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nopo = $bar['nopo'];
		}	
		
		
		$tab.="<tr class='rowcontent'>
			<td colspan=3 valign=top>".$_SESSION['lang']['uploaddata']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){					
					$tab.="<td colspan=4 valign=top style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'><table>";
					$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."' and supplierid='".$val3."' and status='1'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$nofiles = 0;
					while($bar=$res->fetch()){
						$nofiles++;
						$delete="";
						if($nopo!=''){
							$delete="&nbsp;<img src=images/application/application_delete.png class=zImgBtn title=Delete onclick=\"deletefiletambahan('".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."','".$val3."','".$bar['namafile']."','".$nofiles."')\";>";
						}
						$tab.="<tr id=baris_".$val3."_".$nofiles.">
							<td colspan=2 nowrap><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".(strlen($bar['namafile'])>50?substr($bar['namafile'],0,50)."...":$bar['namafile'])."</a></td><td align=center nowrap>".$delete."</td>
						</tr>";
					}
					
					if($nopo!=''){
						$tab.="<tr><td align=left><select id=kriteriaefil".$val3." style=width:75px>
													<option value='EPHS'>Penawaran Harga Supplier</option>
													<option value='others'>Others</option>
												</select></td>
									<td align=left><input class=mybutton type='file' name='uploadtambahan".$val3."' id='uploadtambahan".$val3."'></td>
									<td  align=center nowrap><img title=Upload class=zImgBtn onclick=\"submitfiletambahan('".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."','".$val3."','".$notransaksi."','".$kd_brg."','".$norph."','".$supplierid."')\" src=images/plus.png></td>
								</tr>";
						$tab.="<tr><td colspan=3 align=left>
										<progress id='progressBar".$val3."' value='0' max='100' style='width:300px;display:none;'></progress>
										<p id='status".$val3."'></p>
										<p id='loaded_n_total".$val3."'></p>
									</td>
								</tr>";
					}
					
					
					$tab.="</table>";
					$tab.="</td>";
				}
			}
		}	
		$tab.="</tr>
		</table>";
		
		echo $tab;		
	break;
	case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".log_permintaanhargafile values ('".$norph."','".$supplierid."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')"; //exit("error".$str);
					try{
						$owlPDO->exec($str);
						move_uploaded_file($file_tmpname,"fileupload/rph/$filename");
					}catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload harus .jpg .jpeg .png .pdf .xls .xlsx .doc .docx");
				}
			}
		}
	break;
	case 'deletefiletambahan':
		$str="delete from ".$dbname.".log_permintaanhargafile where nomor='".$notransaksi."' and supplierid='".$supplierid."' and namafile='".$namafile."'";
		// exit("error".$str);
		try{
			$owlPDO->exec($str);
			$path = "fileupload/rph/".$namafile;
			unlink($path);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'previewlinkpp3':
		$formPil=0;
        $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optStock=$optTermPay;
        $optKrm=$optTermPay;
        $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
        $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");
		
		##Get Supplier & Purchaser
		$arrSup = $arrPur = array();
		$str="select * from ".$dbname.".log_perintaanhargaht where nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nopp='".$notransaksi."' and kodebarang='".$kd_brg."') and flag='1' and tolakrph='0'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$optNmSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");
			$optNmPur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['purchaser']."'");
			$arrNmSup[$val['purchaser']][$val['supplierid']] = $optNmSup[$val['supplierid']];
			$arrPur[$val['purchaser']] = $val['purchaser'];
			$arrNmKar[$val['purchaser']] = $optNmPur[$val['purchaser']];
		}
		
		##Get Nomor RPH
		$arrKdBrg = array();
		$str="select a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi, b.subtotal, b.pphfinal, b.pph, b.pph22 from ".$dbname.".log_permintaanhargadt a
		left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
		where a.nopp='".$notransaksi."' and a.kodebarang='".$kd_brg."' and b.tolakrph='0' 
		and b.nomor not in (select nomor from ".$dbname.".log_permintaanhargadt where flag='1' and kodebarang='".$kd_brg."' and nopp='".$notransaksi."') order by b.nourut asc";
		$res=fetchdata($str);
		$nourut = 0;
		foreach($res as $key=>$val){
			$nourut++;
			$tglrph = $val['tanggal'];
			$arrKdBrg[$val['kodebarang']] = $val['kodebarang'];
			$arrHarga[$val['kodebarang']] = $hrgtrk;

			if($val['pph22'] > 0){
					$pph_ = $val['pph22'];
				}else{
					$pph_ = $val['pph'];
				}
			
			$arrSupplier2[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrSupplier[$val['supplierid']] = $val['supplierid'];
			
			$arrSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrCountSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			
			$optNmSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");
			$optNmPur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['purchaser']."'");
			$arrNmSup[$val['purchaser']][$val['supplierid']] = $optNmSup[$val['supplierid']];
			
			if($val['supplierid']==$supplierid){
				$style[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']] = "background-color:#D6F097";
			}
			
			$optMrk = makeOption($dbname,'log_5merkbaranght','idmerk,merk',"idmerk='".$val['merk']."'");
			
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['merk'] = $optMrk[$val['merk']];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['spec'] = $val['spec'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['jumlah'] = $val['jumlah'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['harga'] = $val['harga'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['hargadiskon'] = ($val['diskonpersen']==0?$val['harga']:($val['harga'] - $val['nilaidiskon']));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nodph'] = $val['nomor'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nourut'] = $val['nourut'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['diskonpersen'] = $val['diskonpersen'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nilaidiskon'] = ($val['diskonpersen']==0?'0':($val['nilaidiskon']));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pbbkb'] = $val['pbbkb'];

			//pph final 

			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['is_pph_final']
				= ($val['pphfinal'] > 0);

			if($val['pphfinal'] > 0){
				// PPH Final → nominal langsung
				$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pph']
					= $val['pphfinal'];
			}else{
				// PPH normal
				$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pph']
					= ($pph_/100) * ($val['subtotal'] - $val['nilaidiskon']);
			}


			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['matauang'] = $val['matauang'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['kurs'] = $val['kurs'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tgldari'] = $val['tgldari'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tglsmp'] = $val['tglsmp'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['keterangan'] = $val['catatan'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipengiriman'] = $val['durasipengiriman'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipekerjaan'] = $val['durasipekerjaan'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['garansiproduk'] = $val['garansiproduk'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['posisistok'] = $val['posisistok'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['asuransi'] = $val['asuransi'];
		}
		
		$tab="<table cellspacing=1 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<td rowspan=3 align=center>No.</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=3 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['satuan']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['nopp']."</td>
                <td rowspan=3 align=center>Harga Terakhir</td>";
				
		foreach($arrPur as $val){
			$tab.="<td colspan='".(count($arrCountSup[$val])*4)."' style='font-weight:bold;text-align:center'>".$arrNmKar[$val]."</td>";
		}
		
		$tab.="</tr></tr>";
		
		foreach($arrPur as $val){
			foreach($arrSupplier as $val2){
				if($val2==$arrSupplier2[$val][$val2])
				{
					$tab.="<td colspan=4 style='text-align:center'>".$arrNmSup[$val][$val2]."</td>";
				}
			}
		}
		$tab.="</tr></tr>";
		foreach($arrPur as $val){
			foreach($arrSup[$val] as $val2){
				$tab.="<!--<td  align=center>".$_SESSION['lang']['merk']."</td>-->
					<td  align=center>".$_SESSION['lang']['spesifikasi']."</td>
					<td  align=center>".$_SESSION['lang']['jumlah']."</td>
					<td  align=center>".$_SESSION['lang']['harga']."</td>
					<td align=center>".$_SESSION['lang']['subtotal']."</td>";
			}
		}
		
		$tab.="<tr>
			</thead>
			<tbody>";
		
		$nobrg = 0;
		foreach($arrKdBrg as $val){
			$str="select * from ".$dbname.".log_prapoht where nopp='".$notransaksi."'";
			$res=fetchData($str);
			$tmpTgl = explode('-',$res[0]['tanggal']);
			$tgldari = "01-12-".$tmpTgl[0];
			$tgldarilalu = "01-01-".($tmpTgl[0]-1);
			
			##Harga Terakhir
			$strx="select hargasatuan from ".$dbname.".log_po_vw where statuspo>1 and kodebarang='".$val."' and tanggal < '".$tglrph."' order by tanggal desc limit 1";
			$resx=fetchdata($strx);
			$hrgtrk = $resx[0]['hargasatuan'];
			
			$no++;
			$optNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val."'");
			$optSatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val."'");
			$strx="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$notransaksi."' and kodebarang='".$val."'";
			$resx=fetchdata($strx);
			if($resx[0]['satuankonversi']=='' || is_null($resx[0]['satuankonversi'])){
				$mySatuan = $optSatuan[$val];
			}else{
				$mySatuan = $resx[0]['satuankonversi'];
			}
			$tab.="<tr class='rowcontent'>
				<td align=center>".$no."</td>
				<td id='kd_brg_".$no."' style='color:blue;cursor:pointer' onclick=showdocpakaibarang('".$tgldari."','".tanggalnormal($res[0]['tanggal'])."','".substr($notransaksi,-4)."','".$val."','event') title='Detail Pemakaian Barang'>".$val."</td>
				<td colspan=2 style='color:blue;cursor:pointer' onclick=showdocpembelianterakhir('".$tgldarilalu."','".tanggalnormal($res[0]['tanggal'])."','".substr($notransaksi,-4)."','".$val."','event') title='Detail Pembelian Terakhir'>".$optNmBrg[$val]."</td>
				<td align=center title='".$mySatuan."'>".$mySatuan."</td>
				<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$notransaksi."','".$val."',event)\">".$notransaksi."</td>
				<td align=right><label id='hargaterakhir_".$no."'>".($hrgtrk==''?0:number_format($hrgtrk,2))."</label></td>";
			
			foreach($arrPur as $val2){
				foreach($arrSupplier as $val3)
				{
					if($val3==$arrSupplier2[$val2][$val3])
					{
						$subtotal = ($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['jumlah'] * $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['harga']);
						$tab.="<!--<td align=justify style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['merk']."</td>-->
							<td align=justify style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['spec']."</td>
							<td align=center style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['jumlah']."</td>
							<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['harga'])."</td>
							<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($subtotal)."</td>
						";
						$arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'] = $subtotal;
					}
				}
			}
		}		
		$tab.="</tr>";
		
		####SUBTOTAL####
		$tab.="<tr class='rowcontent'>
			<td rowspan=5 colspan=4 valign=top align=left >&nbsp</td><td colspan=3>".$_SESSION['lang']['subtotal']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DISKON####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['diskon']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=3 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['diskonpersen']==0?'0':number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['diskonpersen'],2))."</td>
					<td align=right style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nilaidiskon'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####PBBKB####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>PBBKB</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pbbkb'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";

		####PPH FINAL####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>PPH Final</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pph'],0)."</td>";
				}
			}
		}
		$tab.="</tr>";

		####GRANDTOTAL####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['grnd_total']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$grandtotal = $arrSubtotal[$val2][$notransaksi][$kd_brg][$val3]['subtotal'] - $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nilaidiskon'] + $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pbbkb'] - $arrStatus[$val2][$notransaksi][$kd_brg][$val3]['pph'];
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".number_format($grandtotal,0)."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####NO PERMINTAAN HARGA####
		$tab.="<tr class='rowcontent'>
			<td rowspan=12 colspan=4 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>
			<td colspan=3>No. RPH</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####MATA UANG####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['matauang']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['matauang']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####KURS####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['kurs']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['kurs']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####TANGGAL DARI####
		// $tab.="<tr class='rowcontent'>
			// <td colspan=3>".$_SESSION['lang']['tgldari']."</td>";
		// foreach($arrPur as $val2){
			// foreach($arrSupplier as $val3){
				// if($val3==$arrSupplier2[$val2][$val3]){
					// $tab.="<td align=right colspan=5 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['tgldari']."</td>";
				// }
			// }
		// }
		// $tab.="</tr>";
		
		####TANGGAL SAMPAI####
		// $tab.="<tr class='rowcontent'>
			// <td colspan=3>".$_SESSION['lang']['tglsmp']."</td>";
		// foreach($arrPur as $val2){
			// foreach($arrSupplier as $val3){
				// if($val3==$arrSupplier2[$val2][$val3]){
					// $tab.="<td align=right colspan=5 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['tglsmp']."</td>";
				// }
			// }
		// }
		// $tab.="</tr>";
		
		####SYARAT PEMBAYARAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['syaratPem']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$str="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['syaratbayar']."'";
					$res=fetchData($str);
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$res[0]['keterangan']." (".$res[0]['jenis'].")</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####LOKASI PENGIRIMAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>".$_SESSION['lang']['almt_kirim']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$optfranco = makeOption($dbname,'setup_franco','id_franco,franco_name',"id_franco='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['lokasikirim']."'");
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$optfranco[$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['lokasikirim']]."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DURASI PENGIRIMAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Durasi Pengiriman</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['durasipengiriman']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####DURASI PEKERJAAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Durasi Pekerjaan</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['durasipekerjaan']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####GARANSI PRODUK/JASA####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Garansi Produk/Jasa</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['garansiproduk']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####POSISI STOK BARANG####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Posisi Stok Barang</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['posisistok']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####ASURANSI####
		$tab.="<tr class='rowcontent'>
			<td colspan=3>Asuransi</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['asuransi']."</td>";
				}
			}
		}
		$tab.="</tr>";

		####KETERANGAN####
		$tab.="<tr class='rowcontent'>
			<td colspan=3 valign=top>".$_SESSION['lang']['keterangan']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td align=right colspan=4 style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'>".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['keterangan']."</td>";
				}
			}
		}
		$tab.="</tr>";
		
		####FILEUPLOAD####
		$tab.="<tr class='rowcontent'>
			<td colspan=3 valign=top>".$_SESSION['lang']['uploaddata']."</td>";
		foreach($arrPur as $val2){
			foreach($arrSupplier as $val3){
				if($val3==$arrSupplier2[$val2][$val3]){
					$tab.="<td colspan=4 valign=top style='".$style[$val2][$notransaksi][$kd_brg][$val3]."'><table>";
					$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$arrStatus[$val2][$notransaksi][$kd_brg][$val3]['nodph']."' and supplierid='".$val3."' and status='1'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$nofiles = 0;
					while($bar=$res->fetch())
					{
						$nofiles++;
						$tab.="<tr>
							<td ><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".substr($bar['namafile'],0,40)."...</a></td>
						</tr>";
					}
					$tab.="</table>";
					$tab.="</td>";
				}
			}
		}	
		$tab.="</tr>
		</table>";
		
		echo $tab;	
	break;
	
	case'previewlinkdt':
		$sPP="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qPP=$owlPDO->query($sPP) or die(print " Gagal: ".PDOException::getMessage());
		$qPP->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$qPP->fetch();
		$ket=explode('/',$bar['keterangan']);
		if (@$ket[1]=='FRM') {
			$jnsapp = "CPX";
			$noppx = $bar['keterangan'];
		} else {
			$jnsapp = "PR";
			$noppx = $nopp;
		}
		$req=$bar['requester'];
		$optbagian = makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$req."'");
		$koderorg=substr($nopp,15,4);
		//$countApprove = getCountApproval($jnsapp,$koderorg,$optbagian[$req]);

		$strx="select count(level) as jumlvl from ".$dbname.".approval where notransaksi='".$nopp."'";
		$resx=fetchdata($strx);
		$countApprove=$resx[0]['jumlvl'];
		//echo $countApprove;
		
		echo"<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['tanggal']." PR</td>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
					for($i=1;$i<=$countApprove;$i++)
					{
						echo"<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
					}
					
					echo"<td style='text-align:center'>No.Capex</td>
				</tr>
				</thead>
				<tbody>";
				
				$sPP="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
				$qPP=$owlPDO->query($sPP) or die(print " Gagal: ".PDOException::getMessage());
				$qPP->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$qPP->fetch())
				{
					$ket=explode('/',$bar['keterangan']);
					if (@$ket[1]=='FRM')
					{
						$ketcapex=$bar['keterangan'];
					}
					else
					{
						$ketcapex='-';
					}
					
					$tglpp=$bar['tanggal'];
					$sql="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar['dibuat']."'";
					$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
					$query->setFetchMode(PDO::FETCH_ASSOC);
					$ret=$query->fetch();
					
					echo"<tr class=rowcontent>
						<td>".tanggalnormal($bar['tanggal'])."</td>
						<td>".$ret['namakaryawan']."</td>";
					
					$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
					
					for($i=1;$i<=$countApprove;$i++)
					{
						$arrApp = detailApprove($i,$noppx,$jnsapp);
						
						if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00')
						{
							$tngl='';
						}
						else
						{
							$tngl=tanggalnormal($arrApp['tanggal']);
						}
						
						if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0))
						{
							echo"<td>".$arrApp['nama']."
								<br />".$arrHsl[$arrApp['status']]."
								<br>".$tngl."
							</td>";
						}
						else
						{
							echo"<td>&nbsp;</td>";
						}
					}
					
					echo"<td align=center>".$ketcapex."</td></tr>";
				}
				
			echo"</tbody>
			</table><br />";

		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$nopp."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tab.="<div style='width:auto;overflow:auto;'>
					<table border=0 cellspacing=1 cellpadding=3 class=sortable>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='".(2+$val['level'])."'>Return - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>".$_SESSION['lang']['tanggal']." PR</td>
							<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td>".tanggalnormal($tglpp)."</td>
							<td>".$pembuatpp."</td>";
							for($i=1;$i<=$val['level'];$i++) {
								$strx="select * from ".$dbname.".approval_return where notransaksi='".$nopp."' and level='".$i."' and keterangan='".$val['keterangan']."'";
								$resx=fetchdata($strx);
								$namakaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$resx[0]['karyawanid']."'");
								$tab.="<td>".$namakaryawan[$resx[0]['karyawanid']]."
									<br>	
									".$arrHsl[$resx[0]['status']]."
									<br>	
									".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
								</td>";
							}

						$exdata=explode('##', $val['keterangan']);
						if (count($exdata)>1) {
							$nmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$exdata[0]."'");
							$nmbrgreturn=$nmbarang[$exdata[0]];
							$tab.="</tr>
							<tr class=rowcontent>
								<td colspan='".(2+$val['level'])."'>Barang Return : ".$nmbrgreturn." (".$exdata[0].")</td>
							</tr>
							<tr class=rowcontent>
								<td colspan='".(2+$val['level'])."'>Keterangan Return : ".$exdata[1]."</td>
							</tr>";
						}else{
							$tab.="</tr>
							<tr class=rowcontent>
								<td colspan='".(2+$val['level'])."'>Keterangan Return : ".$val['keterangan']."</td>
							</tr>";
						}
						$tab.="
					</tbody>
					</table>
				</div>";
				$tab.="<br>";
			}
			echo $tab;
		}
			
		echo"<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>No</td>
				<td style='text-align:center'>Chat</td>
				<td style='text-align:center'>".$_SESSION['lang']['kodebarang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['namabarang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['jmlh_disetujui']."</td>
				<td style='text-align:center'>Prioritas</td>
				<td style='text-align:center'>".$_SESSION['lang']['kodevhc']."</td>
				<td style='text-align:center'>KM/HM</td>
				<td style='text-align:center;min-width:80px'>".$_SESSION['lang']['tanggal']." PR/SR</td>
				<td style='text-align:center'>".$_SESSION['lang']['tgldibutuhkan']."</td>   
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['stock']."</td>
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['hargasatuan']."</td>
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['realisasi']."</td>
				<td style='text-align:center' width='50px'>".$_SESSION['lang']['anggaran']."</td>				
				<td style='text-align:center'>".$_SESSION['lang']['nopo']."</td>
				<td style='text-align:center;min-width:80px'>".$_SESSION['lang']['tanggal']." PO/SO</td>
				<td style='text-align:center'>".$_SESSION['lang']['keterangan']."</td>
				<td style='text-align:center;display:none'>SPK</td>
			</tr>
		</thead>";
		
		$sdhi=date('Y-m-d');

		$sCek="select nopp from ".$dbname.".log_prapodt where nopp='".$nopp."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);;
		if($rCek>0)
		{
			echo"
			<tbody>";
			$sDet="select a.*,b.tanggal from ".$dbname.".log_prapodt a left join ".$dbname.".log_prapoht b on a.nopp=b.nopp where a.nopp='".$nopp."'";
			$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
			$qDet->setFetchMode(PDO::FETCH_ASSOC);
			$lokasi=array("Pusat","Lokal");
			$no=0;
			while($res=$qDet->fetch())
			{
				## GET PRIORITAS
				$optprioritas = makeOption($dbname,'log_5prioritas','kode,nama',"kode='".$res['prioritas']."'");
				$prioritas = $optprioritas[$res['prioritas']];
				
				## GET PLAT KENDARAAN
				$optplat = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$res['kodevhc']."'");
				if($optplat[$res['kodevhc']]!=''){
					$noplat = $res['kodevhc']." - ".$optplat[$res['kodevhc']];
				}else{
					$noplat = $res['kodevhc'];
				}
				
				$thnAnggaran=substr($res['tanggal'],0,4);
				$unitAnggaran=substr($nopp,15,4);
				$awalthn=$thnAnggaran."-01-01";
				$sBrg="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
				$rBrg=$qBrg->fetch();

				$sPoDet="select nopo from ".$dbname.".log_podt where nopp='".$res['nopp']."' and kodebarang='".$res['kodebarang']."'";
				$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
				$rCek=owlBaris($qPoDet);
				
				// $sAnggaran="select sum(jumlah) as jmlhAnggaran from ".$dbname.".bgt_budget_detail where 
					// kodebarang='".$res['kodebarang']."' and tahunbudget='".substr($res['tanggal'],0,4)."' and kodeorg like '".substr($nopp,15,4)."%' group by kodebarang";
					// //exit("");
				// $qAnggaran=$owlPDO->query($sAnggaran) or die(print " Gagal: ".PDOException::getMessage());
				// $qAnggaran->setFetchMode(PDO::FETCH_ASSOC);	
				// $rAnggaran=$qAnggaran->fetch();

				$sSdhi="select sum(jumlahpesan) as sdhi from ".$dbname.". log_po_vw 
					where nopp like '%".substr($nopp,15,4)."%' and kodebarang='".$res['kodebarang']."'
					 and substr(tanggal,1,4)='".$thnAnggaran."'";
				$qDhi=$owlPDO->query($sSdhi) or die(print " Gagal: ".PDOException::getMessage());
				$qDhi->setFetchMode(PDO::FETCH_ASSOC);
				$rDphi=$qDhi->fetch();
				
				// Cek Chat
				$strChat="select * from ".$dbname.".log_pp_chat where "
                . " kodebarang='".$res['kodebarang']."' and nopp='".$nopp."'";
				$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($resChat)>0)
				{
					$ingChat="chat1";
				} else {
					$ingChat="chat0";
				}
				
				if($rCek>0)
				{
					//echo"warning:A";
					$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
					$rPoDet=$qPoDet->fetch();
					
					$sPo="select tanggal from ".$dbname.".log_poht where nopo='".$rPoDet['nopo']."'";
					$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
					$qPo->setFetchMode(PDO::FETCH_ASSOC);
					$rPo=$qPo->fetch();

					$Tgl2=$rPo['tanggal'];

					$tgl1=$res['tanggal'];
					$pecah1 = explode("-", $tgl1);
					$date1 = $pecah1[2];
					$month1 = $pecah1[1];
					$year1 = $pecah1[0];
					//$tgl1 = $bar->tanggal;

					$pecah2 = explode("-", $Tgl2);
					$date2 = $pecah2[2];
					$month2 = $pecah2[1];
					$year2 =  $pecah2[0];
					$stat=1;
					$nopo=$rPoDet['nopo'];
					$tglPo=tanggalnormal($rPo['tanggal']);
				}
				else
				{	
					//echo"B";
					$tgl1=$res['tanggal'];
					$pecah1 = explode("-", $tgl1);
					$date1 = $pecah1[2];
					$month1 = $pecah1[1];
					$year1 = $pecah1[0];
					//$tgl1 = $bar->tanggal;
					$tgl1 =$year1.$month1.$date1;
					$Tgl2 = date('Y-m-d');			
					$pecah2 = explode("-", $Tgl2);
					$date2 = $pecah2[2];
					$month2 = $pecah2[1];
					$year2 =  $pecah2[0];	
					$tglPo='';
					$stat=0;	
					$nopo="";				
				}

				$jd1 = GregorianToJD($month1, $date1, $year1);
				$jd2 = GregorianToJD($month2, $date2, $year2);
				$jmlHari= $jd2 - $jd1;

				$no+=1;
				//$tolak=array("0"=>$_SESSION['lang']['disetujui'],"3"=>);
				if($res['status']=='3')
				{
					$stat2=$_SESSION['lang']['ditolak'];
					$jmlHari=0;
					$nopo='';
				}
				else
				{
					$stat2="-";
				}
				
				$tmpTgl = explode('-',$res['tanggal']);
				$tgldari = "01-01-".$tmpTgl[0];
				$tgldarilalu = "01-01-".($tmpTgl[0]-1);
				$prddari = $tmpTgl[0]."-01";
				$prdsampai = $tmpTgl[0]."-".$tmpTgl[1];
				$stylebg = "";
				if($kd_brg==$res['kodebarang']){
					$stylebg = "background-color:#D6F097"; 
				}
				//style='cursor:pointer;' onclick=detailAnggaran('".$res['kodebarang']."','".$thnAnggaran."','".$unitAnggaran."')
				echo"<tr class=rowcontent style=".$stylebg.">
				<td align=center>".$no."</td>
				<td style='text-align:center'>
					<img src='images/".$ingChat.".png' onclick=\"loadPPChat('".$nopp."','".$res['kodebarang']."',event);\" class='resicon'>
				</td>
				<td style=cursor:pointer onclick=showdocpakaibarang('".$tgldari."','".tanggalnormal($res['tanggal'])."','".substr($nopp,-4)."','".$res['kodebarang']."','event') title='Detail Pemakaian Barang'><font color=blue>".$res['kodebarang']."</font></td>
				<td style=cursor:pointer onclick=showdocpembelianterakhir('".$tgldarilalu."','".tanggalnormal($res['tanggal'])."','".substr($nopp,-4)."','".$res['kodebarang']."','event') title='Detail Pembelian Terakhir'><font color=blue>".$rBrg['namabarang']."</font></td>
				<td>".$rBrg['satuan']."</td>
				<td align=center>".hidezerodecimal($res['jumlahpp'],2)."</td>
				<td align=center>".hidezerodecimal($res['jumlah'],2)."</td>
				<td style='text-align:left'>".$res['prioritas']." - ".$prioritas."</td>";
				
				echo"<td>";
					if($res['kodevhc']!=''){
						echo"<table>";
						$nomor=0;
						$arrkdvhc=explode(",",$res['kodevhc']);
						foreach($arrkdvhc as $kodekend){
							$optplat = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$kodekend."'");
							if($optplat[$kodekend]!=''){
								$noplat = $kodekend." - ".$optplat[$kodekend];
							}else{
								$noplat = $kodekend;
							}
							$nomor++;
							echo"<tr>";
							echo"<td nowrap style='text-align:left;cursor:pointer' onclick=showdocsparepart('".$prddari."','".$prdsampai."','".substr($nopp,-4)."','".$kodekend."',event)><font color=blue>".$nomor.". ".$noplat."</font></td>";
							echo"</tr>";
						}
						echo"</table>";
					}
				echo"</td>";	
					
				// <td style='text-align:center;cursor:pointer' onclick=showdocsparepart('".$prddari."','".$prdsampai."','".substr($nopp,-4)."','".$res['kodevhc']."','event')><font color=blue>".$noplat."</font></td>
				echo"<td align=center>".hidezerodecimal($res['kmhm'],2)."</td>
				<td align=center style='min-width:80px;'>".tanggalnormal($res['tanggal'])."</td>
				<td align=center style='min-width:80px;'>".tanggalnormal($res['tgl_sdt'])."</td>   
				<td align=center>".hidezerodecimal($res['stock'],2)."</td>
				<td align=right>".hidezerodecimal($res['hargasatuan'],2)."</td>
				<td align=right>".hidezerodecimal($res['realisasi'],2)."</td>
				<td align=right>".hidezerodecimal($res['anggaran'],2)."</td>

				<td>".$nopo."</td>
				<td>".$tglPo."</td>
				<td>".$res['keterangan']."</td>";
				if($res['spk']=='1'){
					echo"<td style='text-align:center;display:none'>&checkmark;</td>";
				}else{
					echo"<td style='text-align:center;display:none'></td>";
				}
			echo"</tr>";
            }
		echo"</tbody></table></div><br />";
		echo"<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfilesview'>";
		echo"</tbody>
			</table><br />";
			
		echo"<div id=dtFormDetail style=\"overflow:auto; width:500px;height:150px;\">";

		echo"</div>";
		}
		else
		{
				echo"<tbody><tr class=rowcontent style='text-align:center'><td colspan=18>Not Found</td></tr></tbody></table>";
		}
	break;
	
	case 'loadfiles':
		$no = 0;
		$tab = "";
		$str="select * from ".$dbname.".log_prapoht where nopp = '".$nopp."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$close = $barv['close'];	
			$ketcapex=$barv['keterangan'];
		}
		
		$str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopp."' and status='1'";
		$res=fetchData($str);
		if(empty($res))
		{
			$str="select * from ".$dbname.".listfileupload where notransaksi = '".$ketcapex."' and status='1'";
			$res=fetchData($str);
			if(empty($res))
			{
				$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
				
				foreach($res as $key=>$val)
				{
					$no++;
					$tab.="<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.png')
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf')
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}
					elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}
					elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}
					else
					{
						$tab.="<td style='text-align:center'>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
					
					if($val['kriteriaefil']=='others'){
						$kriteriaefil = 'Others';
					}else{
						$optkritefil = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$val['kriteriaefil']."'");
						$kriteriaefil = $optkritefil[$val['kriteriaefil']];
					}
					
					$tab.="<td style='text-align:left'>".$kriteriaefil."</td>
						<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					if($close==0){
						$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
					}
					$tab."	</td>
					</tr>";
				}	

			}

		}
		else
		{
			foreach($res as $key=>$val)
			{
				$no++;
				$tab.="<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.png')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.pdf')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}
				elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}
				elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				if($val['kriteriaefil']=='others'){
						$kriteriaefil = 'Others';
					}else{
						$optkritefil = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$val['kriteriaefil']."'");
						$kriteriaefil = $optkritefil[$val['kriteriaefil']];
					}
				
				$tab.="<td style='text-align:left'>".$kriteriaefil."</td>
				<td style='text-align:left'>".$val['namafile']."</td>
					<td align=center>
						<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($close==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
				}
				$tab."	</td>
				</tr>";
			}	
		}
		echo $tab;
	break;
	
	case'previewlinkpemenang':
		##Get Supplier & Purchaser
		// exit("warning: ".$supplierid." ");
		$arrSup = $arrPur = array();
		$str="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$notransaksi."'";
		$ada_d=fetchdata($str);
		if(count($ada_d) > 0){
			$str="select * from ".$dbname.".log_perintaanhargaht where nomor in (select nomor from ".$dbname.".log_permintaanhargadt where norph='".$notransaksi."') and supplierid='".$supplierid."'";
		}else{
			$str="select * from ".$dbname.".log_perintaanhargaht where nomor = '".$notransaksi."' and supplierid='".$supplierid."'  ";
		}

		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$optNmSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");
			$optNmPur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['purchaser']."'");
			$arrSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrNmSup[$val['purchaser']][$val['supplierid']] = $optNmSup[$val['supplierid']];
			$arrPur[$val['purchaser']] = $val['purchaser'];
			$arrNmKar[$val['purchaser']] = $optNmPur[$val['purchaser']];
			$arrCountSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
		}
		
		##Get Nomor RPH
		$arrKdBrg = array();
		if(count($ada_d) > 0){
			$str="select a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan from ".$dbname.".log_permintaanhargadt a
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
			where a.norph='".$notransaksi."' and b.supplierid='".$supplierid."' and score='1'";
		}else{
			$str="select a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan from ".$dbname.".log_permintaanhargadt a
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
			where a.nomor='".$notransaksi."' and b.supplierid='".$supplierid."' and score='1'";
		}
		// exit("warning: ".$str." ");
		$res=fetchdata($str);
		$nourut = 0;
		foreach($res as $key=>$val){
			$nourut++;
			$arrKdBrg[$val['kodebarang']] = $val['kodebarang'];


			$str1="select * from ".$dbname.".log_prapoht where nopp='".$val['nopp']."'";
			$res1=fetchData($str1);
			$tmpTgl1 = explode('-',$res1[0]['tanggal']);
			$tgldari1 = $tmpTgl1[0]."-01-01";
			$tgldarilalu1 = ($tmpTgl1[0]-1)."-01-01";

			// $unitPP=makeOption($dbname,"log_prapoht","nopp,kodeorg","nopp='".$val['nopp']."'");
			/*$sHrg="select hargasatuan from ".$dbname.".log_po_vw where kodebarang='".$val['kodebarang']."' and kodeorg='".$unitPP[$val['nopp']]."' 
				   and (tanggal between '".$tgldarilalu1."' and '".$tgldari1."') and statuspo>1 order by tanggal desc limit 1";*/
			$sHrg="select hargasatuan from ".$dbname.".log_po_vw where kodebarang='".$val['kodebarang']."' and statuspo>1 
				   and (tanggal between '".$tgldarilalu1."' and '".$res1[0]['tanggal']."') and kodesupplier='".$supplierid."'  order by tanggal desc limit 1";
			$rHrg=fetchData($sHrg);
			// echo $sHrg;

			$arrnopp[$val['kodebarang']] = $val['nopp'];
			$arrHarga[$val['kodebarang']] = $rHrg[0]['hargasatuan'];
			
			$arrSupplier2[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
			$arrSupplier[$val['supplierid']] = $val['supplierid'];
			
			$optMrk = makeOption($dbname,'log_5merkbaranght','idmerk,merk',"idmerk='".$val['merk']."'");
			
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['merk'] = $optMrk[$val['merk']];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['spec'] = $val['spec'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['jumlah'] = $val['jumlah'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['harga'] = $val['harga'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['hargadiskon'] = ($val['diskonpersen']==0?$val['harga']:($val['harga'] - ($val['harga']*($val['diskonpersen']/100))));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nodph'] = $val['nomor'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nourut'] = $val['nourut'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['diskonpersen'] = $val['diskonpersen'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nilaidiskon'] = ($val['diskonpersen']==0?'0':($val['harga']*($val['diskonpersen']/100)));
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pbbkb'] = $val['pbbkb'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['matauang'] = $val['matauang'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['kurs'] = $val['kurs'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tgldari'] = $val['tgldari'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tglsmp'] = $val['tglsmp'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
			$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['keterangan'] = $val['catatan'];
		}

		
		$tab="<table cellspacing=1 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<td rowspan=3 align=center>No.</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=3 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['satuan']."</td>
                <td rowspan=3 align=center>".$_SESSION['lang']['nopp']."</td>
                <td rowspan=3 align=center>Prioritas</td>
                <td rowspan=3 align=center>Harga Terakhir</td>";
				
		foreach($arrPur as $val){
			$tab.="<td colspan='".(count($arrCountSup[$val])*4)."' style='font-weight:bold;text-align:center'>".$arrNmKar[$val]."</td>";
		}
		$tab.="<td rowspan=3 align=center>Action</td>";
		$tab.="</tr></tr>";
		
		foreach($arrPur as $val){
			foreach($arrSup[$val] as $val2){
				$tab.="<td colspan=4 style='text-align:center'>".$arrNmSup[$val][$val2]."</td>";
			}
		}
		$tab.="</tr></tr>";
		foreach($arrPur as $val){
			foreach($arrSup[$val] as $val2){
				$tab.="<!--<td  align=center>".$_SESSION['lang']['merk']."</td>-->
					<td  align=center>".$_SESSION['lang']['spesifikasi']."</td>
					<td  align=center>".$_SESSION['lang']['jumlah']."</td>
					<td  align=center>".$_SESSION['lang']['harga']."</td>
					<td align=center>".$_SESSION['lang']['subtotal']."</td>";
			}
		}
		
		$tab.="<tr>
			</thead>
			<tbody>";
		
		$nobrg = 0;
		foreach($arrKdBrg as $val){
			$optSatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val."'");
			$str="select prioritas,satuankonversi from ".$dbname.".log_prapodt where nopp='".$arrnopp[$val]."' and kodebarang='".$val."'";
			$res=fetchdata($str);
			$prioritas = $res[0]['prioritas'];
			$optprioritas = makeOption($dbname,'log_5prioritas','kode,nama',"kode='".$prioritas."'");
			if($res[0]['satuankonversi']=='' || is_null($res[0]['satuankonversi'])){
				$mySatuan = $optSatuan[$val];
			}else{
				$mySatuan = $res[0]['satuankonversi'];
			}
			
			$str="select tanggal from ".$dbname.".log_prapoht where nopp='".$arrnopp[$val]."'";
			$res=fetchData($str);
			$tmpTgl = explode('-',$res[0]['tanggal']);
			$tgldari = "01-01-".$tmpTgl[0];
			$tgldarilalu = "01-01-".($tmpTgl[0]-1);
			$no++;
			$optNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val."'");
			
			$tab.="<tr class='rowcontent'>
				<td align=center>".$no."</td>
				<td id='kd_brg_".$no."' style='color:blue;cursor:pointer' onclick=showdocpakaibarang('".$tgldari."','".tanggalnormal($res[0]['tanggal'])."','".substr($arrnopp[$val],-4)."','".$val."','event') title='Detail Pemakaian Barang'>".$val."</td>
				<td colspan=2 style='color:blue;cursor:pointer' onclick=showdocpembelianterakhir('".$tgldarilalu."','".tanggalnormal($res[0]['tanggal'])."','".substr($arrnopp[$val],-4)."','".$val."','event') title='Detail Pembelian Terakhir'>".$optNmBrg[$val]."</td>
				<td align=center title='".$mySatuan."'>".$mySatuan."</td>
				<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$arrnopp[$val]."','".$val."',event)\">".$arrnopp[$val]."</td>
				<td align=center>".$prioritas." - ".$optprioritas[$prioritas]."</td>
				<td align=right><label id='hargaterakhir_".$no."'>".($arrHarga[$val]==0?0:number_format($arrHarga[$val],2))."</label></td>";
			
			foreach($arrPur as $val2){
				foreach($arrSupplier as $val3)
				{
					if($val3==$arrSupplier2[$val2][$val3])
					{
						$subtotal = ($arrStatus[$val2][$arrnopp[$val]][$val][$val3]['jumlah'] * $arrStatus[$val2][$arrnopp[$val]][$val][$val3]['harga']);
						$tab.="<!--<td align=justify>".$arrStatus[$val2][$arrnopp[$val]][$val][$val3]['merk']."</td>-->
							<td align=justify>".$arrStatus[$val2][$arrnopp[$val]][$val][$val3]['spec']."</td>
							<td align=center>".$arrStatus[$val2][$arrnopp[$val]][$val][$val3]['jumlah']."</td>
							<td align=right>".number_format($arrStatus[$val2][$arrnopp[$val]][$val][$val3]['harga'])."</td>
							<td align=right>".number_format($subtotal)."</td>
							<td align=center style='cursor:pointer;color:blue' onclick=\"previewlinkpp2('".$notransaksi."','".$arrnopp[$val]."','".$val."','".$val3."','Detail Riwayat Perbandingan Harga',event);\">Detail</td>
						";
					}
				}
			}
		}		
		$tab.="</tr>
		</table>";
		
		echo $tab;
	break;
	
	default;
	break;
}

function getdph($norph,$nopr,$kdbrg,$supplierid){
	global $dbname;
	global $owlPDO;
	
	// $norph="";
	// $str="select norph from ".$dbname.".log_permintaanhargadt where nopp='".$nopr."' and kodebarang='".$kdbrg."'";
	// $res=fetchdata($str);
	// foreach($res as $val){
		// if($val['norph']!=''){
			// $norph=$val['norph'];					
		// }
	// }

	$str="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$norph."'";
	$ada_d=fetchdata($str);
	if(count($ada_d) > 0){
		$norph = $norph;
	}else{
		$str="select nomor from ".$dbname.".log_permintaanhargadt where nomor='".$norph."'";
		$ada_d2=fetchdata($str);

		$norph = $ada_d2[0]['nomor'];
	}


	
	if($norph!=''){
		$tab.="";
		$arrPurchaser = array();
		$arrSupplier = array();
		$arrStatus = array();
		$arrnamasup=array();
		$AllKolom = 0;
		##GET KODEBARANG All
		if(count($ada_d) > 0){
			$str="select kodebarang from ".$dbname.".log_permintaanhargadt where norph='".$norph."'";
		}else{
			$str="select kodebarang from ".$dbname.".log_permintaanhargadt where nomor='".$norph."'";
		}
		$res=fetchdata($str);
		foreach($res as $val){
			$_POST['kdbrg'][] = $val['kodebarang'];
		}
		
		for($i=0;$i<count($_POST['kdbrg']);$i++){
			$str="select * from ".$dbname.".log_listverifikasi where nopp='".$nopr."' and kodebarang='".$_POST['kdbrg'][$i]."' and pemenang='0'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$arrPurchaser[$bar['karyawanid']] = $bar['karyawanid'];
				$arrStatus[$bar['karyawanid']][$bar['nopp']][$bar['kodebarang']]['status'] = $bar['status'];
				$arrStatus[$bar['karyawanid']][$bar['nopp']][$bar['kodebarang']]['skip'] = $bar['skip'];
			}
			
			
			$strx="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$_POST['kdbrg'][$i]."'";
			$resx=fetchdata($strx);
			$arrdtbarang[$_POST['kdbrg'][$i]]['namabarang']=$resx[0]['namabarang'];
			$arrdtbarang[$_POST['kdbrg'][$i]]['satuan']=$resx[0]['satuan'];
			
			$strx="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$nopr."' and kodebarang='".$_POST['kdbrg'][$i]."'";
			$resx=fetchdata($strx);
			if($resx[0]['satuankonversi']=='' || is_null($resx[0]['satuankonversi'])){}else{
				$arrdtbarang[$_POST['kdbrg'][$i]]['satuan']=$resx[0]['satuankonversi'];				
			}
			
			if(count($ada_d) > 0){
				$str="select a.ongkir,a.kodebarang,a.nopp,a.jumlah,a.harga,a.score,a.factor,a.nourut,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,b.flag,b.nilai1s,b.nilai2s,b.nilai3s,b.nilai4s,b.nilai5s,b.nilai1f,b.nilai2f,b.nilai3f,b.nilai4f,b.nilai5f from ".$dbname.".log_permintaanhargadt a 
				left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from ".$dbname.".log_permintaanhargadt where norph='".$norph."')
				where a.kodebarang='".$_POST['kdbrg'][$i]."' and a.nopp='".$nopr."' and b.tolakrph='0' order by b.nourut asc";
			}else{
				$str="select a.ongkir,a.kodebarang,a.nopp,a.jumlah,a.harga,a.score,a.factor,a.nourut,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,b.flag,b.nilai1s,b.nilai2s,b.nilai3s,b.nilai4s,b.nilai5s,b.nilai1f,b.nilai2f,b.nilai3f,b.nilai4f,b.nilai5f from ".$dbname.".log_permintaanhargadt a 
				left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from ".$dbname.".log_permintaanhargadt where nomor='".$norph."')
				where a.kodebarang='".$_POST['kdbrg'][$i]."' and a.nopp='".$nopr."' and b.tolakrph='0' order by b.nourut asc";
			}
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$optnmsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['supplierid']."'");
				$arrnamasup[$bar['supplierid']]=$optnmsup[$bar['supplierid']];
				
				$arrSupplier2[$bar['purchaser']][$bar['supplierid']] = $bar['supplierid'];
				$arrSupplier[$bar['supplierid']] = $bar['supplierid'];
				
				$optMrk = makeOption($dbname,'log_5merkbaranght','idmerk,merk',"idmerk='".$bar['merk']."'");
				
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['merk'] = $optMrk[$bar['merk']];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']]['status'] = $bar['flag'];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['jumlah'] = $bar['jumlah'];
				// $arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['harga'] = ($bar['diskonpersen']==0?$bar['harga']:($bar['harga'] - ($bar['harga']*($bar['diskonpersen']/100))));
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['harga'] = ($bar['diskonpersen']==0?$bar['harga']:($bar['harga'] - ($bar['diskonpersen']*$bar['harga']/100)));
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['nodph'] = $bar['nomor'];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['nourut'] = $bar['nourut'];
				
				$expnopp = explode('/',$bar['nopp']);
				## GET HARGA TERAKHIR
				$hargabarang = 0;
				$tglhargatrk = "";
				$strx="select hargasatuan,tanggal from ".$dbname.".log_5hargaterakhir where unit='".$expnopp[4]."' and kodebarang='".$bar['kodebarang']."' and status='1'";
				$resx=fetchdata($strx);
				if(count($resx) > 0){
					$hargabarang = $resx[0]['hargasatuan'];
					$tglhargatrk = $resx[0]['tanggal'];
				}
				$arrdtbarang[$bar['kodebarang']]['qty']=$bar['jumlah'];
				$arrdtbarang[$bar['kodebarang']]['lastprice']=($resx[0]['hargasatuan']==''?'-':$hargabarang);
				$arrdtbarang[$bar['kodebarang']]['lastpricetgl']=($resx[0]['tanggal']==''?'-':$tglhargatrk);
				$arrdtbarang[$bar['kodebarang']]['factor']=$bar['factor'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['harga']=$bar['harga'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['score']=$bar['score'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['total']=$bar['harga']*$bar['jumlah'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['ongkir']=$bar['ongkir']*$bar['jumlah'];
				
				$arrnilais[$bar['supplierid']]['nilai1s']=$bar['nilai1s'];
				$arrnilais[$bar['supplierid']]['nilai2s']=$bar['nilai2s'];
				$arrnilais[$bar['supplierid']]['nilai3s']=$bar['nilai3s'];
				$arrnilais[$bar['supplierid']]['nilai4s']=$bar['nilai4s'];
				$arrnilais[$bar['supplierid']]['nilai5s']=$bar['nilai5s'];
				$arrongkir[$bar['supplierid']]=$bar['ongkir'];
				$arrtotalongkir[$bar['supplierid']]=$bar['totalongkir'];
				
				$nilai1f=$bar['nilai1f'];
				$nilai2f=$bar['nilai2f'];
				$nilai3f=$bar['nilai3f'];
				$nilai4f=$bar['nilai4f'];
				$nilai5f=$bar['nilai5f'];
			}
		}
		
		$tab.="<fieldset><legend>List Data</legend>";
		$countsup = count($arrSupplier);
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr style='text-align:center;font-weight:bold'>
				<td rowspan=2>Evaluation Parameter</td>
				<td rowspan=2>Description</td>
				<td rowspan=2>Unit</td>
				<td rowspan=2>Uom</td>
				<td colspan='".$countsup."'>Summary of Information</td>
				<td rowspan=2 style='min-width:80px'>LAST PRICE</td>";
			if($countsup > 1){
				$tab.="<td colspan='".$countsup."'>Score (1 - 5, 5 for the Best)</td>
					<td rowspan=2>Weighted Factor</td>
					<td colspan='".$countsup."'>Weighted Score</td>";
			}
			$tab.="</tr>
			<tr style='text-align:center;font-weight:bold'>";
			foreach($arrnamasup as $val){
				$tab.="<td>".$val."</td>";
			}
			if($countsup > 1){
				foreach($arrnamasup as $val){
					$tab.="<td>".$val."</td>";
				}
				foreach($arrnamasup as $val){
					$tab.="<td>".$val."</td>";
				}
			}
			$tab.="<tr>
			</thead>
			<body>";
			
			$arrkodebarang = $_POST['kdbrg'];
			$arrkodebarang = array_unique($arrkodebarang);
			// asort($arrkodebarang);
			
			## PRICE
			$arrsubhasil=array();
			$arrhasil=array();
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+(1*$countsup))."'><b>I. Price :</b></td>";
				
				$no=0;
				foreach($arrkodebarang as $val){
					$no++;
					if($no==1){
						$browspan = (count($arrkodebarang) * 3) + 2;
						foreach($arrSupplier as $valx){
							if($countsup > 1){
								$tab.="<td rowspan='".$browspan."' style='text-align:center'>".$arrnilais[$valx]['nilai1s']."</td>";
							}
						}
						if($countsup > 1){
							$tab.="<td rowspan='".$browspan."' style='text-align:center'>".hidezerodecimal($nilai1f,2)." %</td>";
						}
						foreach($arrSupplier as $valx){
							$hasil=$arrnilais[$valx]['nilai1s'] * ($nilai1f/100);
							// $tab.="<td style='text-align:center'>".$hasil."</td>";
							if($countsup > 1){
								$tab.="<td rowspan='".$browspan."' style='text-align:center'>".$hasil."</td>";
							}
							$arrsubhasil[$valx]=$hasil;
						}
					}
				}
				
			$tab.="</tr>";
			$no=0;
			$arrtotrpsup = array();
			$ongkir=0;
			foreach($arrkodebarang as $val){
				$no++;
				$tab.="<tr class='rowcontent' style='text-align:center'>
					<td>".$no."</td>
					<td style='text-align:left'>".$arrdtbarang[$val]['namabarang']."</td>
					<td>".hidezerodecimal($arrdtbarang[$val]['qty'],2)."</td>
					<td>".$arrdtbarang[$val]['satuan']."</td>";
					foreach($arrSupplier as $valx){
						$tab.="<td style='text-align:center'>".number_format($arrbarang[$valx][$val]['harga'])."</td>";
					}
					$tab.="<td>".hidezerodecimal($arrdtbarang[$val]['lastprice'],2)."</td>";
					
					// $tab.="<td>".hidezerodecimal($nilai1f,2)."</td>";
				$tab.="</tr>";
				$tab.="<tr class='rowcontent' style='text-align:center'>
					<td colspan=4></td>";
					foreach($arrSupplier as $valx){
						$tab.="<td style='text-align:center;font-weight:bold'>".number_format($arrbarang[$valx][$val]['total'])."</td>";
						$arrtotrpsup[$valx]+=($arrbarang[$valx][$val]['total']+$arrbarang[$valx][$val]['ongkir']);
						$ongkir+=$arrtotalongkir[$valx];
					}
					$tab.="<td>".($arrdtbarang[$val]['lastpricetgl']=='-'?'-':tanggalnormal($arrdtbarang[$val]['lastpricetgl']))."</td>";
				$tab.="</tr>";
				
				if(count($arrkodebarang)==$no){
					if($ongkir>0){
						$tab.="<tr class='rowcontent'>";
						$tab.="<td colspan='".(4)."'><b>".$_SESSION['lang']['ongkoskirim']."</b></td>";
						foreach($arrSupplier as $valx){
							$tab.="<td align=center><b>".hidezerodecimal($arrtotalongkir[$valx])."</b></td>";
						}
						$tab.="<td></td>";
						$tab.="</tr>";
					}else{
						$tab.="<tr class='rowcontent'><td colspan='".(5+(1*$countsup))."'>&nbsp;</td></tr>";
					}					
				}else{
					$tab.="<tr class='rowcontent'><td colspan='".(5+(1*$countsup))."'>&nbsp;</td></tr>";					
				}
			}
			
			// ## PPN
			// $tab.="<tr class='rowcontent'>
				// <td colspan='4' style='text-align:right'>PPN&nbsp;</td>";
				// $vhppn=array();
				// foreach($arrSupplier as $val){
					// $str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$val."' and noakun='1170111' limit 1";
					// $res=fetchdata($str);
					// $vpppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);
					// $vhppn[$val] = ($vpppn/100) * $arrtotrpsup[$val];
					// $tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($vhppn[$val])."</td>";
				// }
				
			// $tab.="<td></td>
			// </tr>";
			
			## TOTAL
			$tab.="<tr class='rowcontent' style='background-color:lightgreen'>
				<td colspan='4' style='text-align:center;font-weight:bold'>T O T A L</td>";
				foreach($arrSupplier as $val){
					// $vtothas = $arrtotrpsup[$val] + $vhppn[$val];
					$vtothas = $arrtotrpsup[$val];
					$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($vtothas)."</td>";
				}
				
				$tab.="<td></td>";
			$tab.="</tr>";
			
			if($countsup > 1){
			## Availability
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>II. Availability :</b></td>";
				foreach($arrSupplier as $val){
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai2s']."</td>";
					}
				}
				if($countsup > 1){
					$tab.="<td style='text-align:center'>".$nilai2f." %</td>";
				}
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai2s'] * ($nilai2f/100);
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					}
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Quality/ Performance/ Integrity
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>III. Quality/ Performance/ Integrity :</b></td>";
				foreach($arrSupplier as $val){
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai3s']."</td>";
					}
				}
				if($countsup > 1){
					$tab.="<td style='text-align:center'>".$nilai3f." %</td>";
				}
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai3s'] * ($nilai3f/100);
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					}
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Service
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>IV. Service :</b></td>";
				foreach($arrSupplier as $val){
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai4s']."</td>";
					}
				}
				if($countsup > 1){
					$tab.="<td style='text-align:center'>".$nilai4f." %</td>";
				}
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai4s'] * ($nilai4f/100);
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					}
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Other Concerns (payment scheme, etc.)
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>V. Other Concerns (payment scheme, etc.) :</b></td>";
				foreach($arrSupplier as $val){
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai5s']."</td>";
					}
				}
				if($countsup > 1){
					$tab.="<td style='text-align:center'>".$nilai5f." %</td>";
				}
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai5s'] * ($nilai5f/100);
					if($countsup > 1){
						$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					}
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			$arrwin = array();
			$tab.="<tr class='rowcontent' style='background-color:lightgreen'>";
				if($countsup > 1){
					$tab.="<td colspan='".(5+($countsup*2))."'>&nbsp;</td>";
					$tab.="<td></td>";
				}else{
					$tab.="<td colspan='".(5+($countsup))."'>&nbsp;</td>";
				}
				foreach($arrSupplier as $val){
					$hasil = $arrsubhasil[$val]+$arrhasil[$val];
					if($countsup > 1){
						$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($hasil,2)."</td>";
					}
					$arrwin[$val] = $hasil;
				}
			$tab.="</tr>";
			
			## PEMENANG TENDER
			if($countsup > 1){
				$tab.="<tr class='rowcontent'><td colspan='".(6+(3*$countsup))."'>&nbsp;</td></tr>";
			}else{
				$tab.="<tr class='rowcontent'><td colspan='".(5+($countsup))."'>&nbsp;</td></tr>";
			}
			$no=0;
			arsort($arrwin);
			foreach($arrwin as $key=>$val){
				$no++;
				$tab.="<tr class='rowcontent' style='font-weight:bold'>";
				if($countsup > 1){
					$tab.="<td colspan='".(6+($countsup*2))."' style='text-align:right'>Rekomendasi Tender ".$no."</td>";
					$tab.="<td colspan='".$countsup."'>".$arrnamasup[$key]."</td>";
				}else{
					$tab.="<td colspan='".(4+($countsup))."' style='text-align:right'>Rekomendasi Tender ".$no."</td>";
					$tab.="<td>".$arrnamasup[$key]."</td>";
				}
				$tab.="</tr>";
			}
			}
			
			$tab.="</tbody>
		</table>";
	}else{
		$tab="";
	}
	
	return $tab;
}
?>