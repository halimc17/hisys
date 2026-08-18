<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    require_once('config/connection.php');
    include_once('lib/zLib.php');
	
	
	$method=checkPostGet('method','');
	$nmsupplier=checkPostGet('nmsupplier','');
	$statusreal=checkPostGet('statusreal','');
	$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
    
	switch($method) {
             
	case 'list_new_data':
        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$txt_search='';
        $txt_tgl='';
        if(!empty($_POST['txtSearch'])) {
		$txt_search=$_POST['txtSearch'];
		}
		if(!empty($_POST['tglCari'])) {
                    $txt_tgl=tanggalsystem($_POST['tglCari']);
                    $txt_tgl_t=substr($txt_tgl,0,4);
                    $txt_tgl_b=substr($txt_tgl,4,2);
                    $txt_tgl_tg=substr($txt_tgl,6,2);
                    $txt_tglr=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
        }
		$listOrg = getOrgDetail(4);
		$where = " and kodeorg in (".$listOrg.")";
        if($txt_search!='') {
                    $where.=" and nopo LIKE  '%".$txt_search."%'";
            }
        if($nmsupplier!='') {
                    $where.=" and kodesupplier in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$nmsupplier."%')";
            }
            if($txt_tgl!='') {
                    $where.=" and tanggal LIKE '%".$txt_tglr."%'";
            }
			
		if(!empty($statusreal)){
			if($statusreal=='1'){
				##RELEASE
				$where.=" and statuspo in ('2','3') and stat_release='1' and closed='0'";
			}
			if($statusreal=='2'){
				##UNRELEASE
				$where.=" and statuspo in ('0','1')";
			}
			if($statusreal=='3'){
				##BECOME OUT STANDING
				$where.=" and statuspo in ('2','3') and closed='1' and keteranganclose like '%,tanggal tutup : %'";	
			}
			if($statusreal=='4'){
				##CLOSE
				$where.=" and statuspo in ('2','3') and closed='1' and (keterangan like '%,tanggal tutup : %' or keteranganclose like '%Tutup By System%')";
			}
			if($statusreal=='5'){
				##CENCEL
				$where.=" and statuspo in ('4') and closed='1' and (keteranganclose like '%,tanggal cancel : %')";
			}
		}
      
        $strx="SELECT * FROM ".$dbname.".log_poht where nopo!=''  ".$where." order by tanggal desc limit ".$offset.",".$limit."";

	$sql2="SELECT count(*) as jmlhrow FROM ".$dbname.".log_poht where nopo!='' ".$where." order by tanggal desc ";	 
	$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
        
        
        
         if($jlhbrs<1)
        {
           // echo"data kosong";
        }
        
	$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	
	$no=0;
	$no=$maxdisplay;
	$data =  array();
	$data['tbody'] = array();
	$data['tfoot'] = array();
    while($bar=$res->fetch()) {
		$strx="select a.nomor from ".$dbname.".log_permintaanhargadt a left join ".$dbname.".log_perintaanhargaht b 
		on a.nomor=b.nomor where a.norph='".$bar['nodph']."' and b.supplierid='".$bar['kodesupplier']."'";
		$resx=fetchData($strx);
		$nomordph = $resx[0]['nomor'];
		
		$body['no'] = "";
		$body['nopo'] = "";
		$body['tanggal'] = "";
		$body['namaorganisasi'] = "";
		$body['vendor'] = "";
		$body['nomordph'] = "";
		$body['st'] = "";
		$body['tipepo'] = "";
		$body['print'] = "";
		$body['gudang'] = "";
		
		$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$bar['kodeorg']."'";
		$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
		$rep->setFetchMode(PDO::FETCH_OBJ);
		$bas=$rep->fetch();
		
		$no+=1;
		
		##STATUS PO
		$postatus="";
		if($bar['statuspo']=='0'){
			$postatus="Unrelease";
		}else if($bar['statuspo']=='1'){
			$postatus="Unrelease";
		}else if($bar['statuspo']=='2'){
			if($bar['closed']=='0'){
				$postatus="Release";
			}else{
				if(strpos($bar['keteranganclose'], ",tanggal tutup : ")){
					$postatus = "Become Out Standing";
				}
				if(strpos($bar['keterangan'], ",tanggal tutup : ")){
					$postatus = "Close";
				}
				if(strpos($bar['keteranganclose'], "Tutup By System")){
					$postatus = "Close";
				}
			}
		}else if($bar['statuspo']=='3'){
			if($bar['closed']=='0'){
				$postatus="Release";
			}else{
				if(strpos($bar['keteranganclose'], ",tanggal tutup : ")){
					$postatus = "Become Out Standing";
				}
				if(strpos($bar['keterangan'], ",tanggal tutup : ")){
					$postatus = "Close";
				}
				if(strpos($bar['keteranganclose'], "Tutup By System")){
					$postatus = "Close";
				}
			}
		}else if($bar['statuspo']=='4'){
			$postatus = "Cancel";
		}
			// if($bar['stat_release']==1)
				// $st=$_SESSION['lang']['release_po'];
			// else
				// $st=$_SESSION['lang']['un_release_po'];
		$notransaksi='';	
		$strgd="select notransaksi from ".$dbname.".log_transaksiht where nopo='".$bar['nopo']."'";
		$resgd=fetchData($strgd);
		foreach($resgd as $bargd){
			$notransaksi.="".$bargd['notransaksi']."<br>";
		}	
		
		$strgd="select notransaksi from ".$dbname.".log_noninventory where nopo='".$bar['nopo']."'";
		$resgd=fetchData($strgd);
		foreach($resgd as $bargd){
			$notransaksi.="".$bargd['notransaksi']."<br>";
		}
			
		$body['no'] = $no;
		$body['nopo'] = $bar['nopo'];
		$body['tanggal'] = tanggalnormal($bar['nopo']);
		$body['tanggal'] = tanggalnormal($bar['tanggal']);
		$body['namaorganisasi'] = $bas->namaorganisasi;
		$body['vendor'] = $namasupplier[$bar['kodesupplier']];
		$body['kodesupplier'] = $bar['kodesupplier'];
		$body['nomordph'] = $bar['nodph'];
		$body['tipepo'] = $bar['tipepo'];
		$body['st'] = $postatus;
		$body['gudang'] = $notransaksi;
		
		
		
			
					$sql="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['persetujuan1']."'";
					$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
					$query->setFetchMode(PDO::FETCH_ASSOC);
					$yrs=$query->fetch();
					
		//$body['print'] = "<button class=mybutton onclick=masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event)>".$_SESSION['lang']['print']."</button>";
		$body['print'] = $_SESSION['lang']['print'];
		$data['tbody'][] = $body;
		}
		
		$data['tfoot']['page']		= $page;
		$data['tfoot']['limit']		= $limit;
		$data['tfoot']['jlhbrs']	= $jlhbrs;
		$data['tfoot']['pref']		= $_SESSION['lang']['pref'];
		$data['tfoot']['lanjut']	= $_SESSION['lang']['lanjut'];
		$data['tfoot']['nopp']		= $bar['nopp'];
		$data['tfoot']['nopp_no']	= "nopp_".$no;
		
		/*
		$data['tfoot']['page']	= (($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
                                <br />
                                <button class=mybutton onclick=cariPage(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                                <button class=mybutton onclick=cariPage(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
								<input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />
								";
		*/						
			// create Json - author: Atwal

		echo json_encode($data);
		break;
	default:
	break;
	}
?>