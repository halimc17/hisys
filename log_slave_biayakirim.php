<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodebarang=checkPostGet('kodebarang','');
$kodegudang=checkPostGet('kodegudang','');
$nodok=checkPostGet('nodok','');
$nopo=checkPostGet('nopo','');
$kodeorg=checkPostGet('kodeorg','');
$notransaksi=checkPostGet('notransaksi','');
$tanggalinput=tanggalsystem(checkPostGet('tanggalinput',''));
$tanggalposting=tanggalsystem(checkPostGet('tanggalposting',''));
$tanggalpriode=checkPostGet('tanggalpriode','');
$kodetrp=checkPostGet('kodetrp','');
$jumlah=checkPostGet('jumlah','');
$jumlahpesan=checkPostGet('jumlahpesan','');
$biayakirims=checkPostGet('biayakirim','');
$dataarray=checkPostGet('dataarray','');
$method=checkPostGet('method','');
$namaBarangCari=checkPostGet('namaBarangCari','');
$namaDokCari=checkPostGet('namaDokCari','');
$transporter=checkPostGet('transporter','');
$jenis=checkPostGet('jenis','');
$nodoksch=checkPostGet('nodoksch','');
$totalbiayakirim=checkPostGet('totalbiayakirim','');

$nmBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$nmGdng=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch($method)
{
    case'getListBarang':
        echo"	
            <fieldset  style='float:left;' >
            <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                            <tr>
                                <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                                <td colspan=5>: 
                                        <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                        <button class=mybutton onclick=cariListBarang()>cari</button>
                                <td>
                            <tr>
                            </table>

                            <table id=listCariBarang >
                            <thead>
                            <tr class=rowheader>
                                    <td>No</td>
                                    <td>".$_SESSION['lang']['kodebarang']."</td>
                                    <td>".$_SESSION['lang']['namabarang']."</td>
                                    <td>".$_SESSION['lang']['satuan']."</td>
                            </tr></thead>";

                    if($namaBarangCari=='')
                    {}
                    else
                    {
                        $i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where (namabarang like '%".$namaBarangCari."%' or kodebarang like '%".$namaBarangCari."%')";
						$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
						$n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d=$n->fetch())
                        {
                            $no+=1;
                            echo"
                                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."');\">
                                            <td>".$no."</td>
                                            <td>".$d['kodebarang']."</td>
                                            <td>".$nmBrg[$d['kodebarang']]."</td>
                                            <td>".$satBrg[$d['kodebarang']]."</td>
                                    </tr>";
                        }
                    }
                    echo"</table>
            </fieldset>";
	
    break;
    
    case'getListDok':
    $optjenis="<option value='1'>Purchase Order</option>";
	$optjenis.="<option value='2'>Surat Jalan</option>";
        echo"	
            <fieldset  style='float:left;' >
            <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['nodok']."</legend>
                <table cellspacing=1 border=0 class=data>
                	<tr>
						<td colspan=2>".$_SESSION['lang']['jenis']."</td>
						<td colspan=5>: 
						<select style=\"width:100px;\" id=jenis >".$optjenis."</select>
						<td>
					<tr>
					<tr>
						<td colspan=2>".$_SESSION['lang']['nodok']."</td>
						<td colspan=5>: 
								<input type=text id=namaDokCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=cariListDok()>cari</button>
						<td>
					<tr>
					</table>
					
					<table id=listCariDok width=100%>
					<thead>
					<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['nodok']."</td>
					</tr></thead>";
					
					
                    if(!empty($namaDokCari) && $jenis==1) {
                        $i="select nopo, subtotal, kodesupplier,kodeorg from ".$dbname.".log_poht
							where nopo like '%".$namaDokCari."%'
							and nopo not in (select nodok from ".$dbname.".log_biayakirimht) and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'
							order by nopo";
						$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
						$n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d=$n->fetch())
                        {
                            $no+=1;
                            echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataDok('".$d['nopo']."','".$d['subtotal']."','".$d['kodesupplier']."','1','".$d['kodeorg']."');\">
										<td align=center>".$no."</td>
										<td>".$d['nopo']."</td>
										
								</tr>";
                        }
                    }
                    //echo 'jenis :'.$jenis;
                    if(!empty($namaDokCari) && $jenis==2) {
                        $i="select nosj, expeditor,kodept from ".$dbname.".log_suratjalanht
							where nosj like '%".$namaDokCari."%'
							and nosj not in (select nodok from ".$dbname.".log_biayakirimht)
							order by nosj";
						//exit($i);
						$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
						$n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d=$n->fetch())
                        {
                            $no+=1;
                            echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataDok('".$d['nosj']."','0','".$d['expeditor']."','2','".$d['kodept']."');\">
										<td align=center>".$no."</td>
										<td>".$d['nosj']."</td>
										
								</tr>";
                        }
                    }
                    echo"</table>
            </fieldset>";
		break;
	case 'getakunpajak':
		$qakunpajak = "SELECT a.noakun, b.namaakun, c.noaruskas FROM ".$dbname.".log_5supkelompok a
			LEFT JOIN ".$dbname.".keu_5akun b on a.noakun=b.noakun 
			LEFT JOIN ".$dbname.".keu_5aruskas_detail c on a.noakun=c.noakun 
			where a.supplierid = '".$transporter."' and tipe='TRANSPORTIR'";
		$resakunpajak = fetchData($qakunpajak);

		
		echo $resakunpajak[0]['noakun'].'###'.$resakunpajak[0]['namaakun'].'###'.$resakunpajak[0]['noaruskas'];
	break;
	

    case 'insert':
    			$noTrans = str_replace('-','',$tanggalinput)."/".$kodeorg."/LOG/";
				$qTrans = selectQuery($dbname,'log_biayakirimht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
				$resTrans = fetchData($qTrans);
				if(empty($resTrans)) {
						$notransaksibr = $noTrans."00001";
				} else {
						$tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
						$tmpTrans++;
						$notransaksibr = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
				}
            $i="insert into ".$dbname.".log_biayakirimht (notransaksi,nodok,jenis,tanggalinput,tanggalposting,kodetrp,jumlah,updateby)
            values ('".$notransaksibr."','".$nodok."','".$jenis."','".$tanggalinput."','".$tanggalposting."','".$transporter."','".$jumlah."','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($i);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
    break;

    case 'update':
            $i="update ".$dbname.".log_biayakirimht set updateby='".$_SESSION['standard']['userid']."',
				kodetrp='".$transporter."', jumlah='".$jumlah."'
				where notransaksi='".$notransaksi."' and nodok='".$nodok."'";
            try{
				$owlPDO->exec($i);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
    break;
	
		
    case'loadData':
        
		echo"
		<div id=container>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>    
					<td align=center>".$_SESSION['lang']['nodok']."</td>    
					<td align=center>".$_SESSION['lang']['tanggalinput']."</td>
					<td align=center>Tanggal Posting</td>
					<td align=center>".$_SESSION['lang']['transporter']."</td>
					<td align=center>".$_SESSION['lang']['jumlah']."</td>
					<td align=center>".$_SESSION['lang']['updateby']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		
		$sch = "";
        if($nodoksch!='')
        {
            $sch="and a.nodok like '%".$nodoksch."%' ";
        }
		

		
		$ql2="select a.*,b.namasupplier from ".$dbname.".log_biayakirimht a 
			LEFT JOIN ".$dbname.".log_5supplier b ON a.kodetrp=b.supplierid 
			where a.notransaksi like '%".$_SESSION['empl']['kodeorganisasi']."%' 
			".$sch."";
		//exit($ql2);
		$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);
		$i="select a.*,b.namasupplier from ".$dbname.".log_biayakirimht a 
			LEFT JOIN ".$dbname.".log_5supplier b ON a.kodetrp=b.supplierid 
			where a.notransaksi like '%".$_SESSION['empl']['kodeorganisasi']."%' 
			".$sch."  limit ".$offset.",".$limit."";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
		$no=$maxdisplay;
		while($d=$n->fetch())
		{
			$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
							  "karyawanid='".$d['updateby']."'");
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>".$no."</td>";
			echo "<td align=left>".$d['notransaksi']."</td>";
			echo "<td align=left>".$d['nodok']."</td>";
			echo "<td align=right>".tanggalnormal($d['tanggalinput'])."</td>";
			echo "<td align=right>".tanggalnormal($d['tanggalposting'])."</td>";
			echo "<td align=right>".$d['namasupplier']."</td>";
			echo "<td align=right>".number_format($d['jumlah'])."</td>";
			echo "<td align=left>".$nmKar[$d['updateby']]." ".tanggalnormal(substr($d['updatetime'],0,10))." ".substr($d['updatetime'],11,30)."</td>";
			//echo "<td align=left>".$d['updatetime']."</td>";
			echo "<td align=center>";
			if($d['posting']==0) {

				echo "<img id='".$d['notransaksi'].$d['nodok']."_edit' src=images/001_45.png class=resicon  caption='Edit' onclick=\"edit('".$d['notransaksi']."','".$d['nodok']."','".$d['tanggalinput']."','".$d['tanggalposting']."','".$d['kodetrp']."','".$d['jumlah']."');\">
					<img id='".$d['notransaksi'].$d['nodok']."_delete' src=images/delete_32.png class=resicon  caption='Delete' onclick=\"del('".$d['notransaksi']."','".$d['nodok']."','".$d['tanggalinput']."','".$d['tanggalposting']."','".$d['kodetrp']."','".$d['jumlah']."');\">";
					if($d['jenis']==1)
					{
					echo "<img src=images/addplus.png id='".$d['notransaksi'].$d['nodok']."_showdetail' class=resicon caption='Add Detail' onclick=\"showdetail('datapodetail','3','".$d['notransaksi']."','".$d['nodok']."','".$d['jumlah']."');\">";
					}
				echo "<img src=images/hot.png id='".$d['notransaksi'].$d['nodok']."' class=resicon caption='Posting' onclick=\"posting('".$d['notransaksi']."','".$d['nodok']."','".$d['jenis']."','".$d['tanggalposting']."');\">  
					</td>";
			} else {
				echo "<img src=images/buttongreen.png class=resicon>
					</td>";
			}
			echo "</tr>";
		}
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
    break;
    case 'datapodetail':
    	$qpo = "SELECT a.nopo,a.kodebarang, b.namabarang, a.satuan, a.jumlahpesan, a.hargasatuan FROM ".$dbname.".log_podt a
			LEFT JOIN ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where a.nopo='".$nopo."'";//echo $qGudang;
		$respo = fetchData($qpo);
		$qbkht = "SELECT jumlah FROM ".$dbname.".log_biayakirimht 
		 		where notransaksi='".$notransaksi."' and nodok='".$nopo."'";//echo $qGudang;
		$resbkht = fetchData($qbkht);
		$qbkdt = "SELECT biayakirim, kodegudang,kodebarang FROM ".$dbname.".log_biayakirimdt 
		 		where notransaksi='".$notransaksi."'";//echo $qGudang;
		$resbkdt = fetchData($qbkdt);
		$jlhbkdt = count($resbkdt);
		$databkdt = array();
		foreach ($resbkdt as $ky => $vl) {
			$databkdt[$vl['kodebarang']]['biayakirim']=$vl['biayakirim'];
			$databkdt[$vl['kodebarang']]['kodegudang']=$vl['kodegudang'];
		}
		/*print_r($databkdt);
		exit();*/
		$table="<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nopo']."</td>
					<td align=center colspan=8>".$nopo."</td>
			</tr>
			<tr class=rowheader>
					<td align=center>No Urut</td>
					<td align=center>Kode Barang</td>
					<td align=center>Nama Barang</td>
					<td align=center>Satuan</td>
					<td align=center>Jumlah</td>
					<td align=center>Harga Satuan</td>
					<td align=center>Subtotal</td>
					<td align=center>Kode Gudang</td>
					<td align=center>Biaya Kirim</td>
			</tr>
			</thead>
			<tbody>";
		$totaljumlah=0;
		$hargarupiah=0;
		$totalrupiah=0;
		$biayakirim=0;
		foreach($respo as $row=>$value) {
			$totaljumlah+=$value['jumlahpesan'];
			$hargarupiah=($value['hargasatuan']*$value['jumlahpesan']);
			$totalrupiah+=$hargarupiah;
		}
			@$biayakirim=($hargarupiah/$totalrupiah)*$totalbiayakirim;
			

		foreach($respo as $row=>$value) {
			$table.="<tr class=rowcontent>";
			$no+=1;
			$optgudang=array();
			/*$qgudang = "SELECT a.kodegudang,a.kodebarang, b.namaorganisasi FROM ".$dbname.".log_5saldobulanan a
			LEFT JOIN ".$dbname.".organisasi b on a.kodegudang=b.kodeorganisasi where a.kodebarang='".$value['kodebarang']."' group by kodegudang";//echo $qGudang;*/
			$qgudang = "SELECT namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where alokasi='".$_SESSION['empl']['kodeorganisasi']."' and tipe='GUDANG'";
			$resgudang = fetchData($qgudang);
			foreach ($resgudang as $k => $v) {
				$optgudang[$v['kodeorganisasi']]=$v['namaorganisasi'];
			}
			$table.="<td align=right>".$no."</td>
			<td align=left hidden id='notransaksix_".$row."'>".$notransaksi."</td>
			<td align=left id='kodebarang_".$row."'>".$value['kodebarang']."</td>
			<td align=left>".$value['namabarang']."</td>
			<td align=right>".$value['satuan']."</td>
			<td align=right id='jumlahpesan_".$row."'>".$value['jumlahpesan']."</td>
			<td align=right>".number_format($value['hargasatuan'])."</td>
			<td align=right>".number_format($hargarupiah)."</td>";

			
			$table.="<td align=center >".makeElement('kodegudang_'.$row,'select',$databkdt[$v['kodebarang']]['kodegudang'],array('style'=>'100px'),$optgudang)."</td>";

			if(count($databkdt[$v['kodebarang']]['biayakirim'])==0)
			{
			$table.="<td align=center >".makeElement('biayakirim_'.$row,'textnum',$biayakirim,array('onblur'=>'hitungtotal()'))."</td>";
			}
			else
			{
			$table.="<td align=center >".makeElement('biayakirim_'.$row,'textnum',$databkdt[$v['kodebarang']]['biayakirim'],array('onblur'=>'hitungtotal()'))."</td>";
			}
			$table.="</tr>";
		}
		$table.="<tr class=rowcontent>";
		$table.="<td align=center colspan=4>Total</td>
		<td align=center>".makeElement('totaljumlah','textnum',$totaljumlah,array('disabled'=>'disabled'))."</td>
		<td align=center></td>
		<td align=center>".makeElement('totalrupiah','textnum',$totalrupiah,array('disabled'=>'disabled'))."</td>
		<td align=center></td>
		<td align=center hidden>".makeElement('totalbiayakirimx','textnum',$resbkht[0]['jumlah'],array('disabled'=>'disabled'))."</td>
		<td align=center>".makeElement('totalbiayakirim','textnum',$totalbiayakirim,array('disabled'=>'disabled'))."</td>";
		$table.="</tr>";
		$table.="<tr class=rowcontent>";
		if($jlhbkdt==0)
		{
		$table.="<td align=center hidden id=methoddetail>insertdetail</td>";
		}
		else
		{
		$table.="<td align=center hidden id=methoddetail>updatedetail</td>";
		}
		$table.="<td align=right colspan=9>".makeElement('savedetail','button','Simpan',array('onclick'=>'simpandetail()'))."</td>";
		$table.="</tr>";
		$table.="</tbody></table>";
		echo $table;
    break;
    case 'insertdetail':
    		$arraydata=explode('###', $dataarray);

    		foreach ($arraydata as $key => $val) {
    			$arrdatadetail=explode('#%#', $val);

	            $i="insert into ".$dbname.".log_biayakirimdt (notransaksi,kodebarang,kodegudang,jumlahbarang,biayakirim)
	            values ('".$arrdatadetail[0]."','".$arrdatadetail[1]."','".$arrdatadetail[3]."','".$arrdatadetail[2]."','".$arrdatadetail[4]."')";
	            /*echo $i;
	            exit();*/
				try{
					$owlPDO->exec($i);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
    		}
    		
    break;
    case 'updatedetail':
    			$arraydata=explode('###', $dataarray);
 				
    			foreach ($arraydata as $key => $val) {
    			$arrdatadetail=explode('#%#', $val);

	            $i="update ".$dbname.".log_biayakirimdt set kodegudang='".$arrdatadetail[3]."', biayakirim='".$arrdatadetail[4]."' 
	            	where notransaksi='".$arrdatadetail[0]."' and kodebarang='".$arrdatadetail[1]."' ";
	            echo $i;
				try{
					$owlPDO->exec($i);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
    		}
    		
    break;
	case 'delete':
		$i="delete from ".$dbname.".log_biayakirimht where notransaksi='".$notransaksi."' and nodok='".$nodok."'";
		try{
			$owlPDO->exec($i);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
	
	case 'getGudang':
		$qGudang = "SELECT a.kodegudang, b.namaorganisasi FROM ".$dbname.".log_transaksi_vw a
			LEFT JOIN ".$dbname.".organisasi b on a.kodegudang=b.kodeorganisasi
			where a.tipetransaksi=1 and statussaldo=1 and a.nopo='".$nopo."'";//echo $qGudang;
		$resGudang = fetchData($qGudang);
		$optGudang = array();
		foreach($resGudang as $row) {
			$optGudang[$row['kodegudang']] = $row['namaorganisasi'];
		}
		echo json_encode($optGudang);
		break;
	
	case 'posting':
		$kodeJurnal = "EXP01";
		$cr='';
		// Tanggal sekarang
		$tgl = $tanggalpriode;
		$tglEntry = date('Y-m-d');
		
		
		// Get Data
		$qTrans = selectQuery($dbname,'log_biayakirimht','*',"notransaksi='".$notransaksi.
							  "' and nodok='".$nodok."'");
		$resTrans = fetchData($qTrans);
		$data = $resTrans[0];
		/*if($data['posting']==1) {
			exit("Transaksi sudah pernah di posting");
		}*/
		// Get Akun Kredit
			$qAkun = selectQuery($dbname,'log_5supkelompok','noakun',
								 "supplierid='".$data['kodetrp']."' and tipe='TRANSPORTIR' ");
			$resAkun = fetchData($qAkun);
			$akunKredit = $resAkun[0]['noakun'];
		$kodept='';
		if($jenis==1)
		{
			$qPO = selectQuery($dbname,'log_poht','*',"nopo = '".$nodok."'");
			$resPO = fetchData($qPO);
			$kodept = $resPO[0]['kodeorg'];
		}
		else
		{
			$qPO = selectQuery($dbname,'log_suratjalanht','*',"nosj = '".$nodok."'");
			$resPO = fetchData($qPO);
			$kodept = $resPO[0]['kodept'];

			$qAK = selectQuery($dbname,'setup_periodeakuntansi','*',"kodeorg = '".$resPO[0]['kodeorg']."' and periode='".substr($tgl,0,7)."'");
			$resAK = fetchData($qAK);
			$tglPeriod = $resAK[0]['periode'];
			$tutupbuku = $resAK[0]['tutupbuku'];
			
			if($tutupbuku==1) {
				exit("Gudang dengan periode ".$tglPeriod.' telah tutup buku');
			}
		}
		// Get Counter Journal
			$qCounter = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
									"kodekelompok='".$kodeJurnal."' and kodeorg='".
									$kodept."'");
			/*echo $qCounter;
			exit();*/
			$resCounter = fetchData($qCounter);
			if(empty($resCounter)) exit("Warning: Kelompok Jurnal ".$kodeJurnal.
										" untuk PT.".$kodept." belum ada".
										"\nSilahkan hubungi IT dengan melampirkan pesan error ini");
			$counter = $resCounter[0]['nokounter'];
			
			// Create No Jurnal
			//echo $tanggalpriode;
			//exit();
			$nojurnal = str_replace('-','',$tgl)."/".substr($notransaksi,9,3)."/".
				$kodeJurnal."/".str_pad($counter+1,3,'0',STR_PAD_LEFT);

			$dataRes['header'] = array();
			$dataRes['detailDB'] = array();
			$dataRes['detailCR'] = array();
			$dataRes['saldoupdate'] = array();
			// Data Jurnal Header
					$dataRes['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodeJurnal,
					'tanggal'=>$tgl,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>$data['jumlah'],
					'totalkredit'=>($data['jumlah']*-1),
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);

		if($jenis==1)
		{	
			$qTransdet = selectQuery($dbname,'log_biayakirimdt','kodebarang, kodegudang, biayakirim',"notransaksi='".$notransaksi."'");
			$resTransdet = fetchData($qTransdet);
			$jumlahdet = count($resTransdet);
			if($jumlahdet==0)
			{
				exit('Detail Biaya Kirim PO tidak boleh kosong');
			}
			foreach ($resTransdet as $k => $v) {
				$qAK = selectQuery($dbname,'setup_periodeakuntansi','*',"kodeorg = '".$v['kodegudang']."' and periode='".substr($tgl,0,7)."'");
				$resAK = fetchData($qAK);
				$tglPeriod = $resAK[0]['periode'];
				$tutupbuku = $resAK[0]['tutupbuku'];
			
				if($tutupbuku==1) {
					exit("Gudang dengan periode ".$tglPeriod.' telah tutup buku');
				}
				#lakukan pengecekan fisik masih ada atau tidak
			$sCek="select * from ".$dbname.".log_5saldobulanan where kodegudang='".$v['kodegudang']."' and kodebarang='".$v['kodebarang']."' and periode='".substr($tgl,0,7)."'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qCek->fetch();
			
			// Get Header PO
			$akunDebet='';
			$qPO = selectQuery($dbname,'log_poht','*',"nopo = '".$nodok."'");
			$resPO = fetchData($qPO);
			$kodept = $resPO[0]['kodeorg'];

			

			
		

			if(abs($rCek['saldoakhirqty'])==0)
			{
				
				$str="SELECT nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='LG'	and kodeparameter='LOGBK'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();		
				$akunDebet=$bar['nilai'];
				// Data Jurnal Detail - Debet
				$cr=($data['jumlah']*-1);

				$dataRes['detailDB'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tgl,
					'nourut'=>$k+1,
					'noakun'=>$akunDebet,
					'keterangan'=>'Biaya Kirim PO.'.$nodok." Barang ".$v['kodebarang']." Gudang ".$nmGdng[$v['kodegudang']],
					'jumlah'=>$v['biayakirim'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($v['kodegudang'],0,4),
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$v['kodebarang'],
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$data['kodetrp'],
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nodok,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' =>''
				);
			}
			else
			{
				$qBarang = selectQuery($dbname,'log_5klbarang','noakun',
									   "kode='".substr($v['kodebarang'],0,3)."'");
				//exit("Error ".$qBarang);
				$resBarang = fetchData($qBarang);
				$akunDebet = $resBarang[0]['noakun'];

				

				// Data Jurnal Detail - Debet
				$cr=($v['biayakirim']*-1);
				$dataRes['detailDB'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tgl,
					'nourut'=>$k+1,
					'noakun'=>$akunDebet,
					'keterangan'=>'Biaya Kirim PO.'.$nodok." Barang ".$v['kodebarang']." Gudang ".$nmGdng[$v['kodegudang']],
					'jumlah'=>$v['biayakirim'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($v['kodegudang'],0,4),
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$v['kodebarang'],
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$data['kodetrp'],
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nodok,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' =>''
				);


					$nilai = ($rCek['hargarata'] * $rCek['saldoakhirqty']) + $v['biayakirim'];
					$harga = $nilai / $rCek['saldoakhirqty'];
					
					$data = array(
						'hargarata' => $harga,
						'nilaisaldoakhir' => $nilai,
						'qtymasukxharga'=> $v['biayakirim']+$rCek['qtymasukxharga']//update tambahan harga masuk
					);

					$data2 = array(
						'hargalastin' => $harga,
					);
					
					$tmpPo = explode('/',$nodok);
					
					// Update setelah perhitungan
					$querySaldo = updateQuery($dbname,'log_5saldobulanan',$data,
											  "kodeorg='".$tmpPo[5]."' and
											  kodebarang='".$v['kodebarang']."' and
											  kodegudang='".$v['kodegudang']."' and
											  periode='".$tglPeriod."'");
					$querySaldo2 = updateQuery($dbname,'log_5masterbarangdt',$data2,
											  "kodeorg='".$tmpPo[5]."' and
											  kodebarang='".$v['kodebarang']."' and
											  kodegudang='".$v['kodegudang']."'");

					$dataRes['saldoupdate'][]=$querySaldo;
					$dataRes['saldoupdate2'][]=$querySaldo2;

			}
					
				
				
		
			}

			// Data Jurnal Detail - Kredit
			$dataRes['detailCR'] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tgl,
				'nourut'=>$jumlahdet+1,
				'noakun'=>$akunKredit,
				'keterangan'=>'Biaya Kirim PO.'.$nodok." Barang ".$v['kodebarang']." Gudang ".$nmGdng[$v['kodegudang']],
				'jumlah'=>$cr,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($v['kodegudang'],0,4),
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>$v['kodebarang'],
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$data['kodetrp'],
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>$nodok,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
		}
		else
		{
				$str="SELECT nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='LG'	and kodeparameter='LOGBK'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();		
				$akunDebet=$bar['nilai'];

						$dataRes['detailDB'][] = array(
							'nojurnal'=>$nojurnal,
							'tanggal'=>$tgl,
							'nourut'=>1,
							'noakun'=>$akunDebet,
							'keterangan'=>'Biaya Kirim NO Surat Jalan: '.$nodok,
							'jumlah'=>$data['jumlah'],
							'matauang'=>'IDR',
							'kurs'=>'1',
							'kodeorg'=>substr($resPO[0]['kodeorg'],0,4),
							'kodekegiatan'=>'',
							'kodeasset'=>'',
							'kodebarang'=>'',
							'nik'=>'',
							'kodecustomer'=>'',
							'kodesupplier'=>$data['kodetrp'],
							'noreferensi'=>$notransaksi,
							'noaruskas'=>'',
							'kodevhc'=>'',
							'nodok'=>$nodok,
							'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' =>''
						);

					// Data Jurnal Detail - Kredit
						$dataRes['detailCR'] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tgl,
						'nourut'=>2,
						'noakun'=>$akunKredit,
						'keterangan'=>'Biaya Kirim NO Surat Jalan: '.$nodok,
						'jumlah'=>($data['jumlah'] * -1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>substr($resPO[0]['kodeorg'],0,4),
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$data['kodetrp'],
						'noreferensi'=>$notransaksi,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nodok,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
						);

		}

		/*print_r($dataRes);
		exit();*/
		/***********************************************************************
		 * Insert Data
		 */
		/*print_r($dataRes);
		exit("Error : ".$dataRes);
		$errorDB = "";*/
		
		// Query Delete Jurnal
		$delJurnal = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
		
		# Header
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		try{
			$owlPDO->exec($queryH);
		}catch (PDOException $e){
			$errorDB .= "Header :".$e->getMessage()."\n";
		}
		
		# Detail
		if($errorDB=='') {
			foreach($dataRes['detailDB'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{
					$owlPDO->exec($queryD);
				}catch (PDOException $e){
					$errorDB .= "Detail Debet ".$key." :".$e->getMessage()."\n";
				}
			}

		$queryC = insertQuery($dbname,'keu_jurnaldt',$dataRes['detailCR']);
		try{
			$owlPDO->exec($queryC);
		}catch (PDOException $e){
			$errorDB .= "Detail Credit :".$e->getMessage()."\n";
		}


				if($errorDB=='') {
				// Update Flag
				$updBy = updateQuery($dbname,'log_biayakirimht',array('posting'=>1,'postingby'=>$_SESSION['standard']['userid']),
									 "notransaksi='".$notransaksi."' and nodok='".$nodok."'");
				try{
					$owlPDO->exec($updBy);
					
					#=== Update rupiah dan harga rata2 di Saldo bulanan ===
					// Hitung harga rata2
					if($jenis==1){
					foreach($dataRes['saldoupdate'] as $key=>$datasaldoupadate) {
					try{
						$owlPDO->exec($datasaldoupadate);
						echo 'Success';
					}catch (PDOException $e){
						$updRB = updateQuery($dbname,'log_biayakirimht',array('posting'=>0),
									 "nodok='".$nopo."' and kodebarang='".$kodebarang.
									 "' and kodegudang='".$kodegudang."'");
						echo "Error DB: ".$e->getMessage()."\n";
						try{
							$owlPDO->exec($updRB);
						}catch (PDOException $e){
							echo "error : ".$e->getMessage();
						}
						
						try{
							$owlPDO->exec($delJurnal);
						}catch (PDOException $e){
							echo "error : ".$e->getMessage();
						}
						exit;
					}
					}
					foreach($dataRes['saldoupdate2'] as $key=>$datasaldoupadate2) {
					try{
						$owlPDO->exec($datasaldoupadate2);
						echo 'Success';
					}catch (PDOException $e){
						echo "Error DB: ".$e->getMessage()."\n";
						
					}
					}
					}
				}catch (PDOException $e){
					echo "Error DB: ".$e->getMessage()."\n";
					try{
						$owlPDO->exec($delJurnal);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
						exit;
					}
				}
			} else {
				// Rollback, delete jurnal
				echo "Error DB: \n".$errorDB;
				try{
					$owlPDO->exec($delJurnal);
					exit;
				}catch (PDOException $e){
					exit($e->getMessage());
				}
			}
		} else {
			exit("Error DB: ".$errorDB);
		}
		
		// Posting Success
		#=== Add Counter Jurnal ===
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1),
			"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'");
		$errCounter = "";
		try{
			$owlPDO->exec($queryJ);
		}catch (PDOException $e){
			$errCounter.= "Update Counter Parameter Jurnal Error :".$e->getMessage()."\n";
		}
		
		if($errCounter!="") {
			$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter),
				"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."'");
			$errCounter = "";
			try{
				$owlPDO->exec($queryJRB);
			}catch (PDOException $e){
				$errorJRB .= "Rollback Parameter Jurnal Error :".$e->getMessage()."\n";
			}
			echo "DB Error :\n".$errorJRB;
			exit;
		}
		break;
	default:
}
?>
