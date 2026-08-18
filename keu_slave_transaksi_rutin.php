<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$unit = checkPostGet('unit','');
$notransaksi = checkPostGet('notransaksi','');
$tipetransaksi = checkPostGet('tipetransaksi','');
$jenistransaksi = checkPostGet('jenistransaksi','');
$jenistipe = checkPostGet('jenistipe','');
$pihakketiga = checkPostGet('pihakketiga','');
$noakun = checkPostGet('noakun','');
$totrup = checkPostGet('totrup','');
$totbln = checkPostGet('totbln','');
$rpperbulan = checkPostGet('rpperbulan','');
$keterangan = checkPostGet('keterangan','');
$kredit = checkPostGet('kredit','');
$debit = checkPostGet('debit','');
$kodevhc = checkPostGet('kodevhc','');
$nodokumen = checkPostGet('nodokumen','');
$noso = checkPostGet('noso','');
$tipewaktu = checkPostGet('tipewaktu','');
$tglmulai = tanggalsystem(checkPostGet('tglmulai',''));
$tglselesai = tanggalsystem(checkPostGet('tglselesai',''));
$method = checkPostGet('method','');
$notranscr = checkPostGet('notranscr', '');
$tipecr = checkPostGet('tipecr', '');
$keterangancr = checkPostGet('keterangancr', '');
$nodokumencr = checkPostGet('nodokumencr', '');
$tglposting = tanggalsystem(checkPostGet('tglposting', ''));
$tanggalstop = tanggalsystem(checkPostGet('tanggalstop', ''));

$optjenistransaksi=array('tagihan'=>'Tagihan','prepaid'=>'Prepaid');

switch ($method) {
	
	 case'savestop':
		$str = "update " . $dbname . ".keu_transaksi_rutin set posting='2',updateby='".$_SESSION['standard']['userid']."',tanggalstop='".$tanggalstop."' where notransaksi='" . $notransaksi . "'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
	break;
	
	case'getstop':
		$tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
			$tab.="<table>";
			$tab.="<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input type=text id=notransaksistop disabled value='".$notransaksi."' size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tanggalstop onmousemove=setCalendar(this.id) onkeypress=return false; style=width:150px; maxlength=10 /></td>
					";
			$tab.="</tr>";
			$tab.="<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=savestop()>".$_SESSION['lang']['save']."</button></td>";
			$tab.="</tr>";
		$tab.="<table></fieldset>";
		echo $tab;
	break;
	
	case'editht':
		$str = "select * from ".$dbname.".keu_transaksi_rutin  where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		echo 
		$res[0]['notransaksi']."###".
		$res[0]['kodeorg']."###".
		$res[0]['tipe_transaksi']."###".
		$res[0]['jenistransaksi']."###".
		$res[0]['jenistipe']."###".
		$res[0]['tipewaktu']."###".
		$res[0]['noso']."###".
		$res[0]['noakun']."###".
		tanggalnormal($res[0]['tanggalmulai'])."###".
		number_format($res[0]['harga_barang'],2)."###".
		number_format($res[0]['harga_barang']/$res[0]['tenor'],2)."###".
		$res[0]['noakun_kredit']."###".
		$res[0]['kodevhc']."###".
		$res[0]['noso']."###".
		$res[0]['supplierid']."###".
		tanggalnormal($res[0]['tanggalselesai'])."###".
		$res[0]['tenor']."###".
		$res[0]['keterangan']."###".
		$res[0]['noakun_debet'];
	break;
	
	case'getoption':
	
		
		$optvhc=$optnoso="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
		$whereorg="kodeorganisasi='".$unit."'";
        $optorg = makeOption($dbname,'organisasi','kodeorganisasi,induk',$whereorg);
		
		#= so
		$str="select nopo from ".$dbname.".log_poht where tipepo='SO' and kodeorg='".$optorg[$unit]."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($noso==$bar['nopo']){
				$optnoso.="<option value='".$bar['nopo']."' selected>".$bar['nopo']."</option>";
			}else{
				$optnoso.="<option value='".$bar['nopo']."'>".$bar['nopo']."</option>";
			}
		}

		#= kodekendaraan
		$str="select * from ".$dbname.".vhc_5master where status=1 and substr(kodetraksi,1,4)='".$unit."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($kodevhc==$bar['kodevhc']){
				$optvhc.="<option value='".$bar['kodevhc']."' selected>[".substr($bar['kodetraksi'],0,4)."] [".$bar['nopol']."] ".$bar['detailvhc']."</option>";
			}else{
				$optvhc.="<option value='".$bar['kodevhc']."'>[".substr($bar['kodetraksi'],0,4)."] [".$bar['nopol']."] ".$bar['detailvhc']."</option>";
			}
		}
		
		echo $optnoso."###".$optvhc;

	break;
	
	
	

	case 'gettotbulan':
		$timeStart = strtotime($tglmulai);
        $timeEnd = strtotime($tglselesai);
        // Menambah bulan ini + semua bulan pada tahun sebelumnya
        $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
        // menghitung selisih bulan
        $numBulan += date("m",$timeEnd)-date("m",$timeStart);

		
		$numBulan=$numBulan+1; // karna tanggal pertama dihitung bulan pertama mulai jurnal

        if ($numBulan<0) {
            $numBulan=0;
        }

        echo $numBulan;

	break;

	case 'insert':
		
		$timeStart = strtotime($tglmulai);
        $timeEnd = strtotime($tglselesai);
        // Menambah bulan ini + semua bulan pada tahun sebelumnya
        $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
        // menghitung selisih bulan
        $numBulan += date("m",$timeEnd)-date("m",$timeStart);
        $totrup=str_replace(',', '', $totrup);
		
		
		$numBulan=$numBulan+1; // karna tanggal pertama dihitung bulan pertama mulai jurnal

        if ($numBulan!=$totbln){
            exit('Warning : Total bulan tidak sesuai dengan tanggal yang diinputkan.');
        }
		/*
		if ($tipetransaksi=='ASURANSI'){
			$kdtipe='AI';
		}
		if ($tipetransaksi=='SEWA'){
			$kdtipe='SW';
		}
        if ($tipetransaksi=='OTHERS'){
            $kdtipe='OT';
        }
		*/
		$tipetransaksi=='OTHERS';
		$kdtipe='OT';
        
		$tahunbulan = $kdtipe.date("Ym");

        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_transaksi_rutin where left(notransaksi,8) = '".$tahunbulan."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        $notransaksi=$tahunbulan.addZero($awal,3);

        $str = "insert into " . $dbname . ".keu_transaksi_rutin (notransaksi,kodeorg,tipe_transaksi,jenistransaksi,noakun,supplierid,tanggalmulai,tanggalselesai, harga_barang,noakun_debet,noakun_kredit,createdby,keterangan,updateby,tenor,jenistipe,kodevhc,nodokumen,noso,tipewaktu)
            values ('" . $notransaksi . "','" . $unit . "','" . $tipetransaksi. "','" . $jenistransaksi. "','" . $noakun . "','" . $pihakketiga . "','" . $tglmulai. "','" . $tglselesai . "','" . $totrup . "','" . $debit. "','" . $kredit. "','".$_SESSION['standard']['userid']."','" . $keterangan. "','".$_SESSION['standard']['userid']."','" . $totbln. "','" . $jenistipe. "','" . $kodevhc. "','".$nodokumen."','" . $noso. "','" . $tipewaktu. "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}

	break;

	case 'loadData':
        $where = "";
        // $where = " updateby ='".$_SESSION['standard']['userid']."'";

        $where.= " kodeorg in (".getOrgDetail(2).")";

        if ($notranscr != '') {
            $where.=" and notransaksi like '%" . $notranscr . "%' ";
        }
		 if ($keterangancr != '') {
            $where.=" and keterangan like '%" . $keterangancr . "%' ";
        }
		 if ($nodokumencr != '') {
            $where.=" and nodokumen like '%" . $nodokumencr . "%' ";
        }
        // if ($tipecr != '') {
            // $where.=" and tipe_transaksi='" . $tipecr . "' ";
        // }
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_transaksi_rutin where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=14>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_transaksi_rutin where ".$where." order by notransaksi desc limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".$bar->kodeorg."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                #pembuat
                $whrKar2="karyawanid='".$bar->dibuat_oleh."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                #Supplier
                $whrsup="supplierid='".$bar->supplierid."'";
                $optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
                #namaakunbank
                $whrno="noakun='".$bar->noakun."'";
                $optno=makeOption($dbname,'keu_5akunbank','noakun,namabank',$whrno);
				$optNmBank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank',"kodebank='".$optno[$bar->noakun]."'");
                #jenistipe
                $whrjns="kode='".$bar->jenistipe."'";
                $optjns=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrjns);
				#= coa
				$strdt="select * from ".$dbname.".keu_5akun where noakun in ('".$bar->noakun_debet."','".$bar->noakun_kredit."')";
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$nmakun[$bardt['noakun']]=$bardt['namaakun'];
				}
/*
    <td>".$optjenistransaksi[$bar->jenistransaksi]."</td>
                    <td>".$optjns[$bar->jenistipe]."</td>
                    <td>".$bar->tipewaktu."</td>
*/
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->notransaksi."</td>
                    <td>".$optpt[$bar->kodeorg]."</td>
                
                    <td>".$bar->supplierid." (".$optsup[$bar->supplierid].")</td>
                    <td>".$bar->noakun_debet." ".$nmakun[$bar->noakun_debet]."</td>
                    <td>".$bar->noakun_kredit." ".$nmakun[$bar->noakun_kredit]."</td>
                    <td>".tanggalnormal($bar->tanggalmulai)."</td>
                    <td>".tanggalnormal($bar->tanggalselesai)."</td>
                    <td align=right>".number_format($bar->harga_barang)."</td>
                    <td align=right>".number_format($bar->tenor)."</td>
                    <td align=right>".number_format($bar->harga_barang/$bar->tenor,2)."</td>
                    <td align=center>".$bar->keterangan."</td>";
                    // $colspan="colspan=''";
                    if ($bar->posting==0){
                        $tab.="<td align=center><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht('".$bar->notransaksi."','".$bar->kodeorg."','".$bar->tipe_transaksi."')\"></td>
                               <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delht('" . $bar->notransaksi. "');\"></td>";
                        $tab.="<td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar->notransaksi."');\"></td>";
                    }else  if ($bar->posting==2){
                        $tab.="<td align=center colspan=3>Stop ".tanggalnormal($bar->tanggalstop)."</td>";
                    }else{
                        $tab.="<td align=center colspan=2><img src=images/icons/04/16/02.png class=resicon  title='Posted'\">";
						 $tab.="<td align=center><img src=images/stop1.png class=resicon  title='Stop' onclick=\"getstop('".$bar->notransaksi."');\"></td>";
                    }
                $tab.="</tr>";
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=16 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case'delht':

            $strht = "delete from " . $dbname . ".keu_transaksi_rutin where notransaksi='" . $notransaksi . "'";
            try {
                $owlPDO->exec($strht);
            } catch (PDOException $e) {
                print " Gagal: " . $e->getMessage() . "\n";
                die();
            }

    break;
        
    case'posting':
	
		$strht = "update " . $dbname . ".keu_transaksi_rutin set posting='1', tanggalposting='".date('Y-m-d')."' where notransaksi='" . $notransaksi . "'";
	
        try{
            $owlPDO->exec($strht);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
		
		/*
        $strht = "update " . $dbname . ".keu_transaksi_rutin set posting='1', tanggalposting='".$tglposting."' where notransaksi='" . $notransaksi . "'";
        try {
            $owlPDO->exec($strht);

            $str="SELECT * from ".$dbname.".keu_transaksi_rutin where notransaksi='" . $notransaksi . "'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();

            //get kodeorg
            $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
            $ressup=$owlPDO->query($sqlkd);
            $ressup->setFetchMode(PDO::FETCH_ASSOC);
            $barsup=$ressup->fetch();
            $induk=$barsup['induk'];

			if($bar['jenistransaksi']=='tagihan'){
				
				if ($bar['tipewaktu']=='TAHUNAN') {
					//get noakun debet kredit tahunan
					$ressup=$owlPDO->query("select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='TR".substr($notransaksi,0,2)."'");
					$ressup->setFetchMode(PDO::FETCH_ASSOC);
					$barsup=$ressup->fetch();
					$kodejurnal=$barsup['jurnalid'];
					$akdebet=$barsup['noakundebet'];
					$akkredit=$barsup['noakunkredit'];
					$tgljurnal=$tglposting;
					$whrsup="supplierid='".$bar['supplierid']."'";
					$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
					$keterangan2='pengakuan hutang '.strtolower($bar['tipe_transaksi']).' atas '.$optsup[$bar['supplierid']].'/'.$bar['keterangan'];
				  
					# Get Journal Counter
					$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter']+1,3);
					# Prep No Jurnal
					$notrans=$tgljurnal."/".$bar['kodeorg']."/".$kodejurnal."/".$konter;

					$i = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
					values ('".$notrans."','".$kodejurnal."','".$bar['harga_barang']."','".(-1)*($bar['harga_barang'])."','".$tgljurnal."','".date('Ymd')."','1','".$notransaksi."','IDR','1')";
					try{
						$owlPDO->exec($i);

						$i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodesupplier)
						values ('".$notrans."','".$tgljurnal."','1','".$akdebet."','".$keterangan2."','".$bar['harga_barang']."','IDR','1','".$bar['kodeorg']."','".$notransaksi."','".$bar['supplierid']."')";
						try{
							$owlPDO->exec($i);
						} catch (PDOException $e) {
							print " Gagal: " . $e->getMessage() . "\n";
							die();
						}

						$i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodesupplier)
						values ('".$notrans."','".$tgljurnal."','2','".$akkredit."','".$keterangan2."','" .(-1)*($bar['harga_barang']). "','IDR','1','".$bar['kodeorg']."','".$notransaksi."','".$bar['supplierid']."')";
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

				if ($bar['tipewaktu']=='BULANAN') {

					$tipetransaksi=$bar['tipe_transaksi'];
					if ($bar['tipe_transaksi']=='OTHERS') {
						$tipetransaksi="KOPERASI";
					}

					//get noakun supplier
					$ressup=$owlPDO->query("select noakun from ".$dbname.".log_5supkelompok where supplierid='".$bar['supplierid']."' and tipe like '%".$tipetransaksi."%' ");
					$ressup->setFetchMode(PDO::FETCH_ASSOC);
					$barsup=$ressup->fetch();
					$akunkredit=$barsup['noakun'];
					@$totperbulan=$bar['harga_barang']/$bar['tenor'];

					$noinvoice=date('Ymdhis');
					$tipeinvoice=$bar['jenistipe'];
	// exit("Error:A");
					//noaruskas dan ket
					@$datadt=getArusKasket($bar['noakun_debet'],'','');
					@$datadt=explode('##', $datadt);
					$noaruskas=$datadt[0];
					$ket=$datadt[1];
					
					
					
					
					#Supplier
					$whrsup="supplierid='".$bar['supplierid']."'";
					$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
					$keterangan2='pengakuan hutang '.strtolower($bar['tipe_transaksi']).' atas '.$optsup[$bar['supplierid']].'/'.$bar['keterangan'];

					$insht="insert into ".$dbname.".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values 
					('".$noinvoice."','".$tipeinvoice."','".date('Y-m-d')."','".$notransaksi."','".$bar['supplierid']."','".$totperbulan."','','".$keterangan2."','".$akunkredit."','IDR','1','1','".$induk."','".$bar['kodeorg']."','".$bar['createdby']."','".$bar['createdby']."')";
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
					$tgljurnal=$tglposting;

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

						$i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,kodesupplier,kodevhc)
						values ('".$notrans."','".$tgljurnal."','1','".$bar['noakun_debet']."','".$keterangan2."','".$totperbulan."','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$notransaksi."','".$bar['supplierid']."','".$bar['kodevhc']."')";
						try{
							$owlPDO->exec($i);
						} catch (PDOException $e) {
							print " Gagal: " . $e->getMessage() . "\n";
							die();
						}

						$i = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,kodesupplier,kodevhc)
						values ('".$notrans."','".$tgljurnal."','2','".$akunkredit."','".$keterangan2."','" .(-1)*($totperbulan). "','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$notransaksi."','".$bar['supplierid']."','".$bar['kodevhc']."')";
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
			}			

        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
		*/

    break;

    case'update':

        $timeStart = strtotime($tglmulai);
        $timeEnd = strtotime($tglselesai);
        // Menambah bulan ini + semua bulan pada tahun sebelumnya
        $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
        // menghitung selisih bulan
        $numBulan += date("m",$timeEnd)-date("m",$timeStart);
        $totrup=str_replace(',', '', $totrup);

		$numBulan=$numBulan+1; // karna tanggal pertama dihitung bulan pertama mulai jurnal

        if ($numBulan!=$totbln){
            exit('Warning : Total bulan tidak sesuai dengan tanggal yang diinputkan.');
        }

        $strht="update ".$dbname.".keu_transaksi_rutin set noakun='".$noakun."', supplierid='".$pihakketiga."', harga_barang='".$totrup."', tenor='".$totbln."', keterangan='".$keterangan."', noakun_kredit='".$kredit."', noakun_debet='".$debit."', tanggalmulai='".$tglmulai."', tanggalselesai='".$tglselesai."', jenistipe='".$jenistipe."', kodevhc='".$kodevhc."', nodokumen='".$nodokumen."', noso='".$noso."', tipewaktu='".$tipewaktu."', updateby='".$_SESSION['standard']['userid']."',updatetime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
        // exit("Error:$strht");
		try{
            $owlPDO->exec($strht);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
    break;

    case'getformposting':
    
    $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                <tr  class=rowcontent>
                    <td>".$_SESSION['lang']['tanggal']."</td> 
                    <td>:</td>
                    <td><input type=text class=myinputtext readonly  id=tglposting onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" value=''/>
                    <button class=mybutton onclick=posting('".$notransaksi."')>Simpan</button></td>
                </tr>  
            </table>";
            
    echo $tab;
    
    break;
	
	
	case 'getformdt':

        $strht="select * from ".$dbname.".keu_transaksi_rutin where notransaksi='".$notransaksi."'";
        $resht=$owlPDO->query($strht);
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht=$resht->fetch();

        $whereorg="kodeorganisasi='".$unit."'";
        $optorg = makeOption($dbname,'organisasi','kodeorganisasi,induk',$whereorg);
	

        $optnoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query("select * from ".$dbname.".keu_5akunbank where (pemilik='".$unit."' or pemilik='".$optorg[$unit]."')");
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
		    $optNmBank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank',"kodebank='".$bar['namabank']."'");
            if($bar['noakun']==$barht['noakun']){  
                $optnoakun.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." (".$optNmBank[$bar['namabank']]." - ".$bar['rekening']." )</option>";
            }else{
                $optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." (".$optNmBank[$bar['namabank']]." - ".$bar['rekening']." )</option>";
            }
		}
		/*
        $klsuppplier=$tipetransaksi;
        if($tipetransaksi=="OTHERS"){
            // $klsuppplier="";
			$wh='';
        }else{
			$wh=" and tipe='".$klsuppplier."' ";
		}
		$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query("select b.supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a 
		left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where  1=1 ".$wh." and b.status=1");
		*/
		
		$klsuppplier=$tipetransaksi;
        if($tipetransaksi=="OTHERS"){
            // $klsuppplier="";
			$wh='';
        }else{
			$wh=" and tipe='".$klsuppplier."' ";
		}
		$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query("select b.supplierid, b.namasupplier from ".$dbname.".log_5supkelompok a 
		left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where b.status=1");
		
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
		    
            if($bar['supplierid']==$barht['supplierid']){  
                $optsup.="<option value='".$bar['supplierid']."' selected>".$bar['supplierid']." ".$bar['namasupplier']."</option>";
            }else{
                $optsup.="<option value='".$bar['supplierid']."'>".$bar['supplierid']." ".$bar['namasupplier']."</option>";
            }
		}

        $optak="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optak2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if($barht['noakun_kredit']==""){
            $barht['noakun_kredit']=1180102;
        }
        $res=$owlPDO->query("select noakun, namaakun from ".$dbname.".keu_5akun where length(noakun)=7 
		and (noakun like '7%' or noakun like '8%' or noakun like '9%' or noakun like '116%' 
		or noakun like '118%' or (noakun like '41102%' and noakun!='4110299')) order by noakun asc");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if($bar['noakun']==$barht['noakun_debet']){  
                $optak.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }else{
                $optak.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }
            if($bar['noakun']==$barht['noakun_kredit']){  
                $optak2.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }else{
                $optak2.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }
        }

        $optjnstipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select kode,namajenis from ".$dbname.".keu_5jenistagihan where transaksirutin=1 and namajenis like '".$tipetransaksi."%'");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if($bar['kode']==$barht['jenistipe']){  
                $optjnstipe.="<option value='".$bar['kode']."' selected>".$bar['kode']." - ".$bar['namajenis']."</option>";
            }else{
                $optjnstipe.="<option value='".$bar['kode']."'>".$bar['kode']." - ".$bar['namajenis']."</option>";
            }
        }

        $opttipewaktu=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $arrtipe=getEnum($dbname,'keu_transaksi_rutin','tipewaktu');
        foreach($arrtipe as $kei=>$fal){
            if($fal==$barht['tipewaktu']){  
                $opttipewaktu.="<option value='".$kei."' selected>".$fal."</option>";
            }else{
                $opttipewaktu.="<option value='".$kei."'>".$fal."</option>";
            }
        }

        $optvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select kodevhc,detailvhc,nopol from ".$dbname.".vhc_5master where kodeorg='".$unit."'");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if($bar['kodevhc']==$barht['kodevhc']){  
                $optvhc.="<option value='".$bar['kodevhc']."' selected>".$bar['kodevhc']." - ".$bar['detailvhc']." ".($bar['nopol']!=''?' - '.$bar['nopol']:'')."</option>";
            }else{
                $optvhc.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc']." - ".$bar['detailvhc']." ".($bar['nopol']!=''?' - '.$bar['nopol']:'')."</option>";
            }
        }

		$optnoso="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query("select distinct nopo from ".$dbname.".log_po_vw where left(kodebarang,1)=8 and kodeorg='".$optorg[$unit]."'");
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            if($bar['nopo']==$barht['noso']){  
                $optnoso.="<option value='".$bar['nopo']."' selected>".$bar['nopo']."</option>";
            }else{
                $optnoso.="<option value='".$bar['nopo']."'>".$bar['nopo']."</option>";
            }
        }

        if ($tipetransaksi=='ASURANSI') {
            $idjurnal='TRAI';
        }else{
            $idjurnal='TRSW';
        }

        $res=$owlPDO->query("select distinct noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='".$idjurnal."'");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $kredit=$bar['noakundebet'];

        if ($barht['tanggalmulai']!=0000-00-00) {
            $tanggalmulai=tanggalnormal($barht['tanggalmulai']);
        }else{
            $tanggalmulai='';
        }

        if ($barht['tanggalselesai']!=0000-00-00) {
            $tanggalselesai=tanggalnormal($barht['tanggalselesai']);
        }else{
            $tanggalselesai='';
        }

        if ($barht['noakun_kredit']=='') {
            $barht['noakun_kredit']=$kredit;
        }

        if ($barht['harga_barang']!=''){
            @$rpperbulan=$barht['harga_barang']/$barht['tenor'];
        }
        
    	$formdt ="<table border=0 style=width:100%;>";
        $formdt.="<tr><td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['tipe']."</td><td>:</td><td><select id=jenistipe style=width:153px;>".$optjnstipe."</select><img src='images/obl.png' title='Obligatory'></td></tr>";
        $formdt.="<tr><td>".$_SESSION['lang']['tipewaktu']."</td><td>:</td><td><select id=tipewaktu style=width:153px;>".$opttipewaktu."</select><img src='images/obl.png' title='Obligatory'></td>
                 <td>".$_SESSION['lang']['kodevhc']."</td><td>:</td><td><select id=kodevhc style=width:153px;>".$optvhc."</select></td></tr>";
        $formdt.="<tr><td>".$_SESSION['lang']['nodok']."</td><td>:</td><td><input type=text id=nodokumen value='".$barht['nodokumen']."' class=myinputtext style=width:150px; placeholder='No.Polis/No.Kontrak' ></td>
                 <td>".$_SESSION['lang']['noso']."</td><td>:</td><td><select id=noso style=width:153px;>".$optnoso."</select></td></tr>";
        $formdt.="<tr><td>No. Akun Bank</td><td>:</td><td><select id=noakun style=width:153px;>".$optnoakun."</select><img src='images/obl.png' title='Obligatory'></td>
				 <td>Pihak Ketiga</td><td>:</td><td><select id=pihakketiga style=width:153px;>".$optsup."</select><img id='pihakketiga' onclick=z.elSearch('pihakketiga',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'><img src='images/obl.png' title='Obligatory'></td></tr>";
        $formdt.="<tr><td>".$_SESSION['lang']['tanggalmulai']."</td><td>:</td><td><input type=text class=myinputtext id=tglmulai value='".$tanggalmulai."' onmousemove=setCalendar(this.id) onkeypress=return false; onchange=gettotbulan(); style=width:150px; maxlength=10 /><img src='images/obl.png' title='Obligatory'></td>
                 <td>".$_SESSION['lang']['tanggalselesai']."</td><td>:</td><td><input type=text class=myinputtext id=tglselesai value='".$tanggalselesai."' onmousemove=setCalendar(this.id) onkeypress=return false; onchange=gettotbulan(); style=width:150px; maxlength=10 /><img src='images/obl.png' title='Obligatory'></td></tr>";
		$formdt.="<tr><td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['rupiah']."</td><td>:</td><td><input type=text id=totrup value='".$barht['harga_barang']."' class=myinputtextnumber onkeyup=\"z.numberFormat('totrup',2); return getrpperbulan()\" style=width:150px; onkeypress=\"return angka_doang(event);\" ><img src='images/obl.png' title='Obligatory'></td>
				 <td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['bulan']."</td><td>:</td><td><input type=text id=totbln value='".$barht['tenor']."' class=myinputtextnumber  style=width:150px; onkeypress=\"return angka_doang(event);\" onkeyup='getrpperbulan()' disabled><img src='images/obl.png' title='Obligatory'></td></tr>";
		$formdt.="<tr><td>Rp/".$_SESSION['lang']['bulan']."</td><td>:</td><td><input type=text id=rpperbulan class=myinputtextnumber value='".$rpperbulan."' style=width:150px; onkeypress=\"return angka_doang(event);\" disabled><img src='images/obl.png' title='Obligatory'></td>
				 <td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text id=keterangan value='".$barht['keterangan']."' class=myinputtext style=width:150px; ><img src='images/obl.png' title='Obligatory'></td></tr>";
		$formdt.="<tr><td>".$_SESSION['lang']['akun']." ".$_SESSION['lang']['kredit']."</td><td>:</td><td><!--<input type=text id=kredit value='".$barht['noakun_kredit']."' class=myinputtext style=width:150px; disabled>--><select id=kredit style=width:153px;>".$optak2."</select><img id='kredit' onclick=z.elSearch('kredit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'><img src='images/obl.png' title='Obligatory'></td>
				 <td>".$_SESSION['lang']['akun']." ".$_SESSION['lang']['debet']."</td><td>:</td><td><select id=debit style=width:153px;>".$optak."</select><img id='debit' onclick=z.elSearch('debit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:1px;'><img src='images/obl.png' title='Obligatory' style='position:relative;top:3px;left:1px;'>
                 </td></tr>";
		$formdt.="<tr><td><td><td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
		         <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button></td></tr>";
		$formdt.="</table>";

        echo $formdt;

	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	default:
		# code...
		break;
}


?>