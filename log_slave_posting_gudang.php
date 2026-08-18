<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
if(isTransactionPeriod()) {//check if transaction period is normal
	$gudang=$_POST['gudang'];
	$notransaksi=$_POST['notransaksi'];
	$tipetransaksi=$_POST['tipe'];
	$statussaldo=$_POST['statussaldo'];
	if($statussaldo == 'ya'){
		$whereStatussaldo = "";
	}else{
		$whereStatussaldo = "and statussaldo=0";
	}
	//================================
	echo "<div  style='max-width:100%;overflow:auto;'> 
		   <table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>
		   <thead>";
	$num=0;
	switch($tipetransaksi) {
		//== Penerimaan dari Supplier ==========================================
		case 1:
			$str="select a.kodebarang,a.satuan,a.jumlah,a.hargasatuan,b.tanggal,b.kodept,
				b.tipetransaksi,c.namasupplier,b.nopo,b.idsupplier,a.hargasatuan,a.nopp 
				from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				left join ".$dbname.".log_5supplier c
				on b.idsupplier=c.supplierid    
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";
			echo"<tr class=rowheader>
                <th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th style=display:none align=center>".$_SESSION['lang']['hargasatuan']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['supplier']."</th>
				<th align=center>".$_SESSION['lang']['nopo']."</th>
				<th align=center>".$_SESSION['lang']['nopp']."</th>
				<th hidden align=center>".$_SESSION['lang']['hargasatuan']."</th>    
				</tr>
				</thead>
				<tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
            $no=0;
            while($bar=$res->fetch()) {
                $no+=1;
                //=======ambil namabarang
                $strc="select namabarang from ".$dbname.".log_5masterbarang where
                        kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
                $namabarang="";
                while($barc=$resc->fetch())
                {
                        $namabarang=$barc->namabarang;
                }	    
                echo"<tr class=rowcontent id=row".$no.">
                    <td align=center>".$no."</td>
                    <td align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
                    <td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
                    <td id=kodebarang".$no." >".$bar->kodebarang."</td>
                    <td>".$namabarang."</td>
                    <td id=satuan".$no." >".$bar->satuan."</td>
                    <td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
                    <td style=display:none id=harga".$no." align=right>".$bar->hargasatuan."</td>
                    <td id=kodept".$no." >".$bar->kodept."</td>
                    <td id=supplier".$no.">".$bar->idsupplier."</td>
                    <td id=nopo".$no.">".$bar->nopo."</td>
                    <td id=nopp".$no.">".$bar->nopp."</td>
                    <td hidden id=hargasatuan".$no.">".$bar->hargasatuan."</td>    
                    </tr>";										
            }
            break;
		case 0:
			$str="select a.kodebarang,a.satuan,a.jumlah,a.hargasatuan,b.tanggal,b.kodept,
				b.tipetransaksi,a.hargasatuan,b.notransaksireferensi
				from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";
			echo"<tr class=rowheader>
                <th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['noreferensi']."</th>
				<th align=center hidden>".$_SESSION['lang']['hargasatuan']."</th>    
				</tr>
				</thead>
				<tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
            $no=0;
            while($bar=$res->fetch()) {
                $no+=1;
                //=======ambil namabarang
                $strc="select namabarang from ".$dbname.".log_5masterbarang where
                        kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
                $namabarang="";
                while($barc=$resc->fetch())
                {
                        $namabarang=$barc->namabarang;
                }	    
                echo"<tr class=rowcontent id=row".$no.">
                    <td align=center>".$no."</td>
                    <td align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
                    <td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
                    <td id=kodebarang".$no." >".$bar->kodebarang."</td>
                    <td>".$namabarang."</td>
                    <td id=satuan".$no." >".$bar->satuan."</td>
                    <td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
                    <td id=kodept".$no." >".$bar->kodept."</td>
                    <td id=kdpabrikasi".$no.">".$bar->notransaksireferensi."</td>
                    <td hidden align=right id=hargasatuan".$no.">".$bar->hargasatuan."</td>    
                    </tr>";										
            }
            break;
		//== Retur ke Gudang ===================================================
		case 2:
			$str="select a.kodebarang,a.satuan,a.jumlah,b.tanggal,b.kodept,b.tipetransaksi,a.kodeblok,a.kodekegiatan,b.untukunit,a.kodemesin
                from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";  
				// echo $str;
			echo"<tr class=rowheader>
                <th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['untukunit']."</th>                                
				<th align=center>".$_SESSION['lang']['kodekegiatan']."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>                          
				<th align=center>".$_SESSION['lang']['kodeblok']."</th>                     
				</tr>
				</thead>
				<tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
            $no=0;
            while($bar=$res->fetch()) {
                $no+=1;
                //=======ambil namabarang
                $strc="select namabarang from ".$dbname.".log_5masterbarang where
                        kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
                $namabarang="";
                while($barc=$resc->fetch())
                {
                    $namabarang=$barc->namabarang;
                }	    
                echo"<tr class=rowcontent id=row".$no.">
                    <td align=center>".$no."</td>
                    <td  align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
                    <td align=center id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
                    <td id=kodebarang".$no." >".$bar->kodebarang."</td>
                    <td>".$namabarang."</td>
                    <td id=satuan".$no." >".$bar->satuan."</td>
                    <td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
                    <td id=kodept".$no." >".$bar->kodept."</td>
                    <td id=untukunit".$no.">".$bar->untukunit."</td>                                
                    <td id=kodekegiatan".$no.">".$bar->kodekegiatan."</td>      
                    <td id=kodemesin".$no.">".$bar->kodemesin."</td>                         
                    <td id=kodeblok".$no.">".$bar->kodeblok."</td>
                    </tr>";										
            }
            break;
		
		//== Penerimaan Mutasi Barang ==========================================
		case 3:
			$str="select a.kodebarang,a.satuan,a.jumlah,b.gudangx,a.kodeblok,a.hargasatuan,
				b.tanggal,b.kodept,b.tipetransaksi
				from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";
			
            echo"<tr class=rowheader>
				<th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['sumber']."</th>
				<th align=center>".$_SESSION['lang']['kodeblok']."</th>
				<th hidden align=center>".$_SESSION['lang']['hargasatuan']."</th>    
				</tr>
				</thead>
				<tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
			$no=0;
			while($bar=$res->fetch())
			{
				$no+=1;
				//=======ambil namabarang
				$strc="select namabarang from ".$dbname.".log_5masterbarang where
						kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
				$namabarang="";
				while($barc=$resc->fetch())
				{
						$namabarang=$barc->namabarang;
				}	    
				echo"<tr class=rowcontent id=row".$no.">
					<td align=center>".$no."</td>
					<td align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
					<td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
					<td id=kodebarang".$no." >".$bar->kodebarang."</td>
					<td>".$namabarang."</td>
					<td id=satuan".$no." >".$bar->satuan."</td>
					<td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
					<td id=kodept".$no." >".$bar->kodept."</td>
					<td id=gudangx".$no." >".$bar->gudangx."</td>
					<td id=kodeblok".$no.">".$bar->kodeblok."</td>
					<td hidden id=hargasatuan".$no.">".$bar->hargasatuan."</td>    
					</tr>";										
			}		 	
            break;
		
		//== Pemakaian Barang ==================================================
        case 5:
            // $str="select a.kodebarang,a.satuan,a.jumlah,b.untukpt,a.kodeblok,a.kodesegment,b.untukunit,a.kodekegiatan,c.namakegiatan,a.kodemesin,
			// 	b.tanggal,b.kodept,b.tipetransaksi
			// 	from ".$dbname.".setup_kegiatan c,".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
			// 	on a.notransaksi=b.notransaksi
			// 	where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." and c.kodekegiatan=a.kodekegiatan";  
            $str_cek="select a.kodebarang,a.satuan,a.jumlah,b.untukpt,a.kodeblok,a.kodesegment,b.untukunit,a.kodekegiatan,c.namakegiatan,a.kodemesin,
				b.tanggal,b.kodept,b.tipetransaksi
				from ".$dbname.".setup_kegiatan c,".$dbname.".log_transaksidt_detail a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." and c.kodekegiatan=a.kodekegiatan order by waktutransaksi asc";  
				// echo $str;
            echo"<tr class=rowheader>
                <th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['pt']."</th>    
				<th align=center>".$_SESSION['lang']['untukunit']."</th>                                
				<th align=center>".$_SESSION['lang']['kodeblok']."</th>
				<th align=center style='display:none'>".$_SESSION['lang']['segment']."</th>
				<th align=center>".$_SESSION['lang']['kodekegiatan']."</th> 
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>     
				<th align=center style='background-color:aqua;color:black;font-weight:bold'>Perbaiki Saldo <br><span style='color:red'>(Dilakukan Jika Posting Bermasalah)</span> </th>     
				</tr>
				</thead>
				<tbody>";
			$res_cek=$owlPDO->query($str_cek) or die(print " Gagal: ".PDOException::getMessage());
			$res_cek->setFetchMode(PDO::FETCH_OBJ);
			$num_cek=owlBaris($res_cek);
			if($num_cek > 0){
				$str="select a.kodebarang,a.satuan,a.jumlah,b.untukpt,a.kodeblok,a.kodesegment,b.untukunit,a.kodekegiatan,c.namakegiatan,a.kodemesin,
				b.tanggal,b.kodept,b.tipetransaksi
				from ".$dbname.".setup_kegiatan c,".$dbname.".log_transaksidt_detail a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." and c.kodekegiatan=a.kodekegiatan";  
			}else{
				$str="select a.kodebarang,a.satuan,a.jumlah,b.untukpt,a.kodeblok,a.kodesegment,b.untukunit,a.kodekegiatan,c.namakegiatan,a.kodemesin,
				b.tanggal,b.kodept,b.tipetransaksi
				from ".$dbname.".setup_kegiatan c,".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." and c.kodekegiatan=a.kodekegiatan";  
			}
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
			$no=0;
			while($bar=$res->fetch())
			{
				$no+=1;
				//=======ambil namabarang
				$strc="select namabarang from ".$dbname.".log_5masterbarang where
						kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
				$namabarang="";
				while($barc=$resc->fetch())
				{
						$namabarang=$barc->namabarang;
				}	    
				echo"<tr class=rowcontent id=row".$no.">
					<td align=center>".$no."</td>
					<td  align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
					<td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
					<td id=kodebarang".$no." >".$bar->kodebarang."</td>
					<td>".$namabarang."</td>
					<td id=satuan".$no." >".$bar->satuan."</td>
					<td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
					<td id=kodept".$no." >".$bar->kodept."</td>
					<td id=untukpt".$no." >".$bar->untukpt."</td>
					<td id=untukunit".$no.">".$bar->untukunit."</td>                                
					<td id=kodeblok".$no.">".$bar->kodeblok."</td>
					<td id=kodesegment".$no." style='display:none'>".$bar->kodesegment."</td>
					<td id=kodekegiatan".$no." style='display:none'>".$bar->kodekegiatan."</td>                                
					<td>".$bar->kodekegiatan."-".$bar->namakegiatan."</td>                                
					<td id=kodemesin".$no.">".$bar->kodemesin."</td>
                    <td align=center><button onclick=\"perbaikisaldobulanan('perbaiki','".$bar->kodebarang."','".$bar->tanggal."', '".$gudang."'); this.disabled=true;\" class=mybutton>".$_SESSION['lang']['proses']."</button></td>    
					</tr>";										
			}
            break;
		
		//== Retur ke Supplier =================================================
		case 6:
			$str="select a.kodebarang,a.satuan,a.jumlah,a.hargasatuan,b.tanggal,b.kodept,b.tipetransaksi,c.namasupplier,b.nopo,b.idsupplier,a.hargasatuan
				from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				left join ".$dbname.".log_5supplier c
				on b.idsupplier=c.supplierid    
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";       
			echo"<tr class=rowheader>
                <th align=center>No</th>
                    <th align=center>".$_SESSION['lang']['tipe']."</th>			   
                    <th align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th align=center>".$_SESSION['lang']['kodebarang']."</th>
                    <th align=center>".$_SESSION['lang']['namabarang']."</th>
                    <th align=center>".$_SESSION['lang']['satuan']."</th>
                    <th align=center>".$_SESSION['lang']['kuantitas']."</th>
                    <th align=center>".$_SESSION['lang']['hargasatuan']."</th>
                    <th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
                    <th align=center>".$_SESSION['lang']['supplier']."</th>
                    <th align=center>".$_SESSION['lang']['nopo']."</th>
                    <th hidden align=center>".$_SESSION['lang']['hargasatuan']."</th>    
                    </tr>
                    </thead>
                    <tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
            $no=0;
            while($bar=$res->fetch())
            {
                $no+=1;
                //=======ambil namabarang
                $strc="select namabarang from ".$dbname.".log_5masterbarang where
                        kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
                $namabarang="";
                while($barc=$resc->fetch())
                {
                        $namabarang=$barc->namabarang;
                }	    
                echo"<tr class=rowcontent id=row".$no.">
                    <td align=center>".$no."</td>
                    <td align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
                    <td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
                    <td id=kodebarang".$no." >".$bar->kodebarang."</td>
                    <td>".$namabarang."</td>
                    <td id=satuan".$no." >".$bar->satuan."</td>
                    <td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
                    <td  id=harga".$no." align=right>".$bar->hargasatuan."</td>
                    <td id=kodept".$no." >".$bar->kodept."</td>
                    <td id=supplier".$no.">".$bar->idsupplier."</td>
                    <td id=nopo".$no.">".$bar->nopo."</td>
                    <td hidden id=hargasatuan".$no.">".$bar->hargasatuan."</td>                         
                    </tr>";										
            }                            
			break;
		
		//== Pengeluaran Mutasi ================================================
        case 7:
			$str="select a.kodebarang,a.satuan,a.jumlah,b.gudangx,a.kodeblok,
				b.tanggal,b.kodept,b.tipetransaksi
				from ".$dbname.".log_transaksidt a left join  ".$dbname.".log_transaksiht b 
				on a.notransaksi=b.notransaksi
				where a.notransaksi='".$notransaksi."' and b.kodegudang='".$gudang."' ".$whereStatussaldo." order by waktutransaksi asc";       

            echo"<tr class=rowheader>
                <th align=center>No</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>			   
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."</th>
				<th align=center>".$_SESSION['lang']['ptpemilikbarang']."</th>
				<th align=center>".$_SESSION['lang']['tujuan']."</th>
				<th align=center>".$_SESSION['lang']['kodeblok']."</th>
				</tr>
				</thead>
				<tbody>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=owlBaris($res);
			$no=0;
			while($bar=$res->fetch())
			{
				$no+=1;
				//=======ambil namabarang
				$strc="select namabarang from ".$dbname.".log_5masterbarang where
						kodebarang='".$bar->kodebarang."'";
				$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
				$namabarang="";
				while($barc=$resc->fetch())
				{
						$namabarang=$barc->namabarang;
				}	    
				echo"<tr class=rowcontent id=row".$no.">
					<td align=center>".$no."</td>
					<td align=center id=tipe".$no." title=\"1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi\">".$bar->tipetransaksi."</td>
					<td id=tanggal".$no." >".tanggalnormal($bar->tanggal)."</td>
					<td id=kodebarang".$no." >".$bar->kodebarang."</td>
					<td>".$namabarang."</td>
					<td id=satuan".$no." >".$bar->satuan."</td>
					<td  id=jumlah".$no." align=right>".$bar->jumlah."</td>
					<td id=kodept".$no." >".$bar->kodept."</td>
					<td id=gudangx".$no." >".$bar->gudangx."</td>
					<td id=kodeblok".$no.">".$bar->kodeblok."</td>
					</tr>";										
			}			
            break;
        default:
			echo" Error: Unknown transaction type"; 				
	}
	echo"</tbody><tfoot></tfoot></table>
	<center>
		<button onclick=\"prosesPosting(".$no.",'".$tipetransaksi."','".$notransaksi."'); this.disabled=true;\" class=mybutton>".$_SESSION['lang']['posting']."</button>
		<button onclick=closeDialog() class=mybutton>".$_SESSION['lang']['cancel']."</button>
	</center>
	</div>";
} else {
	echo " Error: Transaction Period missing";
}