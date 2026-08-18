<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
$method=$_POST['method'];


########cara hitung tanggal kemarin###############

        
$kodeorg             =checkPostGet('kodeorg','');
$tanggal             =tanggalsystemn(checkPostGet('tanggal',''));

#tgl kmrn
$tglKmrn             = strtotime('-1 day',strtotime($tanggal));
$tglKmrn             = date('Y-m-d', $tglKmrn);

$tglbesok            = strtotime('-1 day',strtotime($tanggal));
$tglbesok            = date('Y-m-d', $tglbesok);

$tglKmrnnyalagi      = strtotime('-2 day',strtotime($tanggal));
$tglKmrnnyalagi      = date('Y-m-d', $tglKmrnnyalagi);
$kodeorg             =checkPostGet('kodeorg','');

$sisatbskemarin      =checkPostGet('sisatbskemarin','');
$tbsmasuk            =checkPostGet('tbsmasuk','');
$tbsdiolah           =checkPostGet('tbsdiolah','');
$sisahariini         =checkPostGet('sisahariini','');

$oer                 =checkPostGet('oer','');
$kadarair            =checkPostGet('kadarair','');
$ffa                 =checkPostGet('ffa','');
$dirt                =checkPostGet('dirt','');
$loadinggudang       =checkPostGet('loadinggudang','');
$oerpk               =checkPostGet('oerpk','');
$kadarairpk          =checkPostGet('kadarairpk','');
$ffapk               =checkPostGet('ffapk','');
$dirtpk              =checkPostGet('dirtpk','');
$cpoonsistem         =checkPostGet('cpoonsistem','');

$usbbefore           =checkPostGet('usbbefore','');
$usbafter            =checkPostGet('usbafter','');
$oildiluted          =checkPostGet('oildiluted','');
$oilin               =checkPostGet('oilin','');
$oilinheavy          =checkPostGet('oilinheavy','');
$caco                =checkPostGet('caco','');

//cpo loses
$fruitineb           =checkPostGet('fruitineb','');
$ebstalk             =checkPostGet('ebstalk','');
$fibre               =checkPostGet('fibre','');
$nut                 =checkPostGet('nut','');
$effluent            =checkPostGet('effluent','');
$soliddecanter       =checkPostGet('soliddecanter','');


//kernel loses
$fruitinebker        =checkPostGet('fruitinebker','');
$cyclone             =checkPostGet('cyclone','');
$claybath            =checkPostGet('claybath','');
$ltds                =checkPostGet('ltds','');
$usbcpo              =checkPostGet('usbcpo','');//digunakan utk field Dobi
$usbpk               =checkPostGet('usbpk','');
$hydrocyclone        =checkPostGet('hydrocyclone','');//digunakan utk field Centrifuge


$lorirestanhi        =checkPostGet('lorirestanhi','');
$cangkang            =checkPostGet('cangkang','');
$condensatesterilizer=checkPostGet('condensatesterilizer','');

$sisatbskemarinnetto =checkPostGet('sisatbskemarinnetto','');
$tbsmasuknetto       =checkPostGet('tbsmasuknetto','');
$tbsdiolahnetto      =checkPostGet('tbsdiolahnetto','');
$sisanetto           =checkPostGet('sisanetto','');
$keterangan          =checkPostGet('keterangan','');


$tglcr1              =tanggalsystem(checkPostGet('tglcr1',''));
$tglcr2              =tanggalsystem(checkPostGet('tglcr2',''));

$jab = getPostingJabatan('pabrik');

switch($method){
	
	
	case'getlog':
	  
		$str="SELECT distinct(statasiun) as statasiun  FROM ".$dbname.".pabrik_rawatmesinht where 
				tanggal like '".substr($tanggal,0,7)."%' and pabrik='".$kodeorg."' order by statasiun asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$liststasiun[$bar['statasiun']]=$bar['statasiun'];
		}

		$str="SELECT * FROM ".$dbname.".pabrik_rawatmesinht where tanggal like '".substr($tanggal,0,7)."%' 
				and pabrik='".$kodeorg."' order by statasiun,mesin asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listmesin[$bar['statasiun']][$bar['mesin']][$bar['notransaksi']] = $bar;
		}

		$str="select * from ".$dbname.".pabrik_rawatmesindt 
				where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where 
				tanggal like '".substr($tanggal,0,7)."%' 
				and pabrik='".$kodeorg."') group by notransaksi,kodebarang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listbarang[$bar['kodebarang']]=$bar['kodebarang'];
			$barang[$bar['notransaksi']][]=$bar['kodebarang'];
			$satuanbarang[$bar['notransaksi']][]=$bar['satuan'];
			$jumlahbarang[$bar['notransaksi']][]=$bar['jumlah'];
		}

		if(is_array($listmesin)) {
			foreach ($listmesin as $stasiun=>$row) {
				foreach($row as $mesin=>$row2) {
					foreach($row2 as $notransaksi=>$list) {
						$listmesin[$stasiun][$mesin][$notransaksi]['barang'] =isset($barang[$notransaksi])?$barang[$notransaksi]:'';
					}
				}
			}
		} else {
			$listmesin='';
		}

		$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
		$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

		$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
			"pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");
			
		$stream="<table cellspacing='1' border=0 class='sortable' cellpadding=5>";
		$stream.="<thead><tr class=rowcontent>";
						$stream.="<th align=center><b>No</th>";
						$stream.="<th align=center style='width:70px;'><b>".$_SESSION['lang']['tanggal']."</th>";
						$stream.="<th align=center><b>".$_SESSION['lang']['uraiankerusakan']."</th>";
						$stream.="<th align=center><b>Spareparts Replaced</th>";
						$stream.="<th align=center><b>Status</th>";
						$stream.="</tr>";
		$stream.="</thead>";
		if(is_array($listmesin)){
			foreach ($listmesin as $stasiun=>$row){
				$stream.="<tr class=rowcontent>";
				if(in_array($stasiun, $liststasiun)) {
					$stream.="<td align=left colspan=2><b>STATION</b></td>";
					$stream.="<td align=left colspan=3><b>".$stasiun." - ".$nmOrg[$stasiun]."</td>";
					$stream.="</tr>"; 
					foreach($row as $mesin=>$row2) {
						$mesin=isset($mesin)?$mesin:'';
						$nmOrg[$mesin]=isset($nmOrg[$mesin])?$nmOrg[$mesin]:'';
						$stream.="<tr class=rowcontent>";
						$stream.="<td align=right colspan=2><b>MESIN</b></td>";
						$stream.="<td align=left colspan=3><b>".$mesin." - ".$nmOrg[$mesin]."</b></td>";
						$stream.="</tr>";
						$no=0;
						foreach($row2 as $notransaksi=>$list) {
							$no+=1;
							$i=0;
							$rowspan = @count($list['barang']);
							$stream.="<tr class=rowcontent>";
							$stream.="<td rowspan='".$rowspan."' align=center>".$no."</td>";
							$stream.="<td rowspan='".$rowspan."'>".tanggalnormal($list['tanggal'])."</td>";
							$stream.="<td rowspan='".$rowspan."'>".$list['kegiatan']."</td>";
							
							if(empty($list['barang'])) {
								$stream.="<td rowspan='".$rowspan."'></td>";
								$stream.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
							} else {
								foreach ($list['barang'] as $brg) {
									if($i>0) {
										$stream.="<tr class=rowcontent>";
									}
									$stream.="<td>".@$nmBrg[$brg]."</td>";
									$i++;
									if($i==1){
										$stream.="<td rowspan='".$rowspan."'>".$list['statusketuntasan']."</td>";
									}
								}
							}
							$stream.="</tr>";
						}
					}
				}
			}
		}
		$stream.="</tbody></table>";
		echo $stream;	
	break;
	
	
	
	case'delete':
		$str="delete from ".$dbname.".pabrik_produksi 
			       where kodeorg='".$kodeorg."' 
				   and tanggal='".$tanggal."'";   
				   // exit("Error:$str");
		try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}		   
				   
	break;
	
	case'insert':
	
        $sql="select * from ".$dbname.".pabrik_pengolahan where kodeorg='".$kodeorg."' and tanggal='".$tanggal."'";
        $res=fetchdata($sql);
        if ($res) {
            if ($res[0]['posting']=='0') {
                exit("Warningsystem: Transaksi pengolahan pada tanggal ".tanggalnormal($tanggal)." belum diposting.");
            }
        }else{
            exit("Warningsystem: Belum ada transaksi pengolahan pada tanggal ".tanggalnormal($tanggal));
        }
    
        $hitungminus=$sisatbskemarin+$tbsmasuk-$tbsdiolah;
    
        if($hitungminus<0){
            exit("Warning:Jumlah saldo akhir tbs dibawah 0 (".number_format($hitungminus).")");
        }

        $notransaksi=$kodeorg."PRD".str_replace("-", "", $tanggal);
	
		$str="insert into ".$dbname.".pabrik_produksi
                   (notransaksi,kodeorg,tanggal,sisatbskemarin,
				    tbsmasuk,tbsdiolah,sisahariini,
				    oer,cpoonsistem,ffa,kadarair,kadarkotoran,
					oerpk,ffapk,kadarairpk,kadarkotoranpk,
					karyawanid,fruitineb, ebstalk, fibre, nut, 
                                        effluent, soliddecanter, fruitinebker, cyclone, 
                                        ltds, claybath, usbbefore, usbafter, oildiluted, oilin, 
                                        oilinheavy, caco,dobi,usbpk,hydrocyclone,
										lorirestanhi,cangkang,condensatesterilizer,
										tbsmasuknetto,tbsdiolahnetto,
										sisatbskemarinnetto,sisahariininetto,keterangan,
										createby,createtime,updateby,loadinggudang)
					values('".$notransaksi."','".$kodeorg."','".$tanggal."','".$sisatbskemarin."',
					'".$tbsmasuk."','".$tbsdiolah."','".$sisahariini."',
					'".$oer."','".$cpoonsistem."','".$ffa."','".$kadarair."','".$dirt."',
					'".$oerpk."','".$ffapk."','".$kadarairpk."','".$dirtpk."',
					'".$_SESSION['standard']['userid']."','".$fruitineb."','".$ebstalk."',
                                        '".$fibre."','".$nut."','".$effluent."','".$soliddecanter."','".$fruitinebker."','".$cyclone."',
                                        '".$ltds."','".$claybath."','".$usbbefore."','".$usbafter."',
                                        '".$oildiluted."','".$oilin."','".$oilinheavy."','".$caco."','".$usbcpo."','".$usbpk."','".$hydrocyclone."',
										'".$lorirestanhi."','".$cangkang."','".$condensatesterilizer."',
										'".$tbsmasuknetto."','".$tbsdiolahnetto."',
										'".$sisatbskemarinnetto."','".$sisanetto."','".$keterangan."',
										'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".$loadinggudang."')";
										
		try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}								
	break;
	
	
	case'update':
		$str="update ".$dbname.".pabrik_produksi set			
				kodeorg='".$kodeorg."',tanggal='".$tanggal."',sisatbskemarin='".$sisatbskemarin."',
				tbsmasuk='".$tbsmasuk."',tbsdiolah='".$tbsdiolah."',sisahariini='".$sisahariini."',
				oer='".$oer."',cpoonsistem='".$cpoonsistem."',ffa='".$ffa."',kadarair='".$kadarair."',kadarkotoran='".$dirt."',
				oerpk='".$oerpk."',ffapk='".$ffapk."',kadarairpk='".$kadarairpk."',kadarkotoranpk='".$dirtpk."',
				karyawanid='".$_SESSION['standard']['userid']."',fruitineb='".$fruitineb."', ebstalk='".$ebstalk."',
				fibre='".$fibre."', nut='".$nut."',effluent='".$effluent."', soliddecanter='".$soliddecanter."', fruitinebker='".$fruitinebker."', 
				cyclone='".$cyclone."', 
				ltds='".$ltds."', claybath='".$claybath."', usbbefore='".$usbbefore."', usbafter='".$usbafter."', 
				oildiluted='".$oildiluted."', oilin='".$oilin."', 
				oilinheavy='".$oilinheavy."', caco='".$caco."',dobi='".$usbcpo."',usbpk='".$usbpk."',hydrocyclone='".$hydrocyclone."',
				lorirestanhi='".$lorirestanhi."',cangkang='".$cangkang."',condensatesterilizer='".$condensatesterilizer."',
				tbsmasuknetto='".$tbsmasuknetto."',tbsdiolahnetto='".$tbsdiolahnetto."',
				sisatbskemarinnetto='".$sisatbskemarinnetto."',sisahariininetto='".$sisanetto."',keterangan='".$keterangan."',
				updateby='" . $_SESSION['standard']['userid'] . "',loadinggudang='".$loadinggudang."'
				
			where kodeorg='".$kodeorg."' and tanggal='".$tanggal."'";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
	break;		
	case 'posting':
        $notransaksi    =checkPostGet('notransaksi','');
        $str="update ".$dbname.".pabrik_produksi set posting='1' where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
	
	case 'loaddata':

		echo "
			<table class=sortable cellspacing=1 border=0 width=100% cellpadding=5>
			<thead>
			  <tr class=rowheader>
			   <th rowspan=2 align=center>No</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['notransaksi']."</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['unit']."</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['saldoawal']."<br>".$_SESSION['lang']['tbs']."  (Kg)</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['tbsmasuk']."<br>(Kg)</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['tbsdiolah']."<br>(Kg)</th>
			   <th rowspan=2 align=center>".$_SESSION['lang']['saldoakhir']."<br>".$_SESSION['lang']['tbs']."  (Kg)</th>
			   
			   <th colspan=5 align=center>".$_SESSION['lang']['cpo']."
			   </th>
			   <th colspan=6 align=center>".$_SESSION['lang']['kernel']."
			   </th>
			   
			   <th rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</th>	   
			   <th rowspan=2 align=center>".$_SESSION['lang']['updateby']."</th>	   
			   <th rowspan=2 colspan=4 align=center>".$_SESSION['lang']['action']."</th>	   
			  </tr>  
			  <tr class=rowheader> 
			  
			   
			   <th align=center>".$_SESSION['lang']['cpo']." (Kg)</th>
			   <th align=center>".$_SESSION['lang']['oer']." (%) Bruto</th>
			   <th align=center>(FFa)(%)</th>
			   <th align=center>".$_SESSION['lang']['kotoran']." (%)</th>
			   <th align=center>".$_SESSION['lang']['kadarair']." (%)</th>
						
			   
			   <th align=center>".$_SESSION['lang']['kernel']." (Kg)</th>
			   <th align=center>Loading Gudang (Kg)</th>
			   <th align=center>".$_SESSION['lang']['oerpk']." (%) Bruto</th>
			   <th align=center>(FFa) (%)</th>
			   <th align=center>".$_SESSION['lang']['kadarair']." (%)</th>
			   <th align=center>".$_SESSION['lang']['kotoran']." (%)</th>
						   
			  </tr>
			</thead>
			<tbody>";
	
		if($tglcr1=='--'){
			$tglcr1="";
		}
		if($tglcr2=='--'){
			$tglcr1="";
		}
		
		if($tglcr1!='' and $tglcr2!=''){
			$where=" and tanggal between ".$tglcr1." and ".$tglcr2." ";
		}
		
		$limit=10;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
			$page=0;
		}
		$offset=floatval($page)*$limit;
		$maxdisplay=(floatval($page)*$limit);
		$no = $maxdisplay;
		
		$str="select count(*) as jmlhrow from ".$dbname.".pabrik_produksi where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar=$res->fetch();
			$jlhbrs= $bar->jmlhrow;
		
		
		
		$str="select * from ".$dbname.".pabrik_produksi  where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
			".$where." order by `tanggal` desc limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$tCpoLoses=$bar->fruitineb+$bar->ebstalk+$bar->fibre+$bar->nut+$bar->effluent;
			$tKernelLoses=($bar->fruitinebker+$bar->cyclone+$bar->ltds+$bar->claybath);
			$drcl='';
			$no++;
			echo"<tr class=rowcontent >
			<td ".$drcl." align=center>".$no."</td>
			<td ".$drcl.">".$bar->notransaksi."</td>
			<td ".$drcl.">".$bar->kodeorg."</td>
			<td ".$drcl." style='width:75px;text-align:center;'>".tanggalnormal($bar->tanggal)."</td>
			<td ".$drcl." align=right>".number_format($bar->sisatbskemarin,0,'.',',')."</td>
			<td ".$drcl." align=right>".number_format($bar->tbsmasuk,0,'.',',')."</td>
			<td ".$drcl." align=right>".number_format($bar->tbsdiolah,0,'.',',')."</td>
			<td ".$drcl." align=right>".number_format($bar->sisahariini,0,'.',',')."</td>
			<td ".$drcl." align=right>".number_format($bar->oer,2,'.',',')."</td>
			<td ".$drcl." align=right>".(@number_format(((($bar->oer+$bar->cpoonsistem)/$bar->tbsdiolah)*100),2,'.',','))."</td>
			<td ".$drcl." align=right>".$bar->ffa."</td>
			<td ".$drcl." align=right>".$bar->kadarkotoran."</td>
			<td ".$drcl." align=right>".$bar->kadarair."</td>
			<td ".$drcl." align=right hidden>".$bar->dobi."</td>
			<td ".$drcl." align=right>".number_format($bar->oerpk,2,'.',',')."</td>
			<td ".$drcl." align=right>".number_format($bar->loadinggudang,2,'.',',')."</td>
			<td ".$drcl." align=right>".(@number_format(@$bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
			<td ".$drcl." align=right>".$bar->ffapk."</td>
			<td ".$drcl." align=right>".$bar->kadarkotoranpk."</td>
			<td ".$drcl." align=right>".$bar->kadarairpk."</td>
			<td ".$drcl." align=left>".nl2br($bar->keterangan)."</td>
			<td ".$drcl." align=center>".getNamaKaryawan($bar->updateby)."</td>
	    ";
		#ambil periode akuntansi
		$sAkuntansi="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
				and periode='".substr($bar->tanggal,0,7)."' ";
		$rAkuntansi=$owlPDO->query($sAkuntansi) or die(print " Gagal: ".PDOException::getMessage());
		$rAkuntansi->setFetchMode(PDO::FETCH_ASSOC);
		$bakuntansi=$rAkuntansi->fetch();
		$tutupbuku=$bakuntansi['tutupbuku'];
		
			if($tutupbuku==0){
                if($bar->posting == 0){
                    echo"<td align=center style='width:25px;'><img src=images/application/application_edit.png class=resicon  caption='Edit'onclick=\"fillField('".$bar->kodeorg."','".$bar->tanggal."','".number_format($bar->sisatbskemarin)."'
                        ,'".number_format($bar->tbsmasuk)."','".number_format($bar->tbsdiolah)."','".number_format($bar->sisahariini)."','".number_format($bar->oer)."','".$bar->ffa."','".$bar->kadarkotoran."','".$bar->kadarair."','".number_format($bar->oerpk)."',
                        '".$bar->ffapk."','".$bar->kadarkotoranpk."','".$bar->kadarairpk."','".$bar->dobi."',
                        '".$bar->usbbefore."','".$bar->usbafter."','".$bar->oildiluted."','".$bar->oilin."','".$bar->oilinheavy."','".$bar->caco."',
                        '".$bar->hydrocyclone."','".$bar->fruitineb."','".$bar->ebstalk."','".$bar->fibre."','".$bar->nut."','".$bar->effluent."','".$bar->soliddecanter."',
                        '".$bar->fruitinebker."','".$bar->cyclone."','".$bar->ltds."','".$bar->claybath."','".$bar->lorirestanhi."','".$bar->cangkang."','".$bar->condensatesterilizer."',
                        '".number_format($bar->tbsmasuknetto)."','".number_format($bar->tbsdiolahnetto)."','".number_format($bar->sisatbskemarinnetto)."','".number_format($bar->sisahariininetto)."',
                        '".str_replace("\n",'<br />',$bar->keterangan)."','".$bar->cpoonsistem."','".number_format($bar->loadinggudang)."');\"></td>	
                        
                        <td align=center style='width:25px;'><img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"del('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".(isset($bar->kodebarang)? $bar->kodebarang: '')."');\"></td>";
                        #POSTING
                        echo "<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"postingproduksi('".$bar->notransaksi."');\" title='Posting'></td>";
                }else if ($bar->posting==1){
                    echo"<td align=center style='width:25px;'></td>";
				    echo"<td align=center style='width:25px;'></td>";
                    # UNCOMMENT SETELAH SETUP JABATAN BUAT POSTING
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
                        $icon="images/icons/04/16/04.png";
                        $title="Unclose / Unposting";
                        $unpost=" onclick=\"unposting('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\" ";
                    }
                    else{ 
                        $icon="images/icons/04/16/02.png";
                        $title="Closed / Posted";
                        $unpost='';
                    }
                    echo "<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
                }
			}else{
				echo"<td align=center style='width:25px;'></td>";
				echo"<td align=center style='width:25px;'></td>";			
			}
			echo"<td align=center style='width:25px;'><img src=images/skyblue/zoom.png class=resicon  title='delete' onclick=\"getlog('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."');\"></td>";
			echo"</tr>";	
		}
		 echo"
            <tr class=rowheader><td colspan=24 align=center>
            ".((floatval($page)*$limit)+1)." to ".((floatval($page)+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loaddata(".(floatval($page)-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loaddata(".(floatval($page)+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
         echo"</tbody></table>";
	break;
	
    case'getCpo':
	
        #ambil produksi terakhir
        $str="select tanggal from ".$dbname.".pabrik_produksi where oer != '0' and tanggal < '".$tanggal."' order by tanggal desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $tgltrkhirprod=$bar['tanggal'];
        $tglKmrn = $tgltrkhirprod;//kemarin itu = tanggal terakhir produksi dari tgl yg dipilih 
        // $tglKmrn = tglkemarin($tanggal);//tglkemarin ambil dari tgl dipilih bukan dari terakhir yg ada di produksi bermasalah kalau mau edit tgl backdate
        #stock  kemarin
        $str="select sum(kuantitas) as stok from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tglKmrn."' and kodeorg='".$kodeorg."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='CPO')";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $stokkemarin=$bar['stok'];
			
		#kirim  hi
		$str="select sum(beratbersih) as stok from ".$dbname.".pabrik_timbangan_vw where"
                . " kodebarang='40000001' and millcode='".$kodeorg."' and  tanggal='".$tanggal."' and wbcond='Normal'"; 	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
             $kirimhi=$bar['stok'];	

        $str="select sum(beratbersih) as stok from ".$dbname.".pabrik_timbangan_vw where"
                . " kodebarang='40000001' and millcode='".$kodeorg."' and  tanggal='".$tanggal."' and wbcond='Return'"; 	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $returhi=$bar['stok'];	 
			 
		#stok  hr ini
        $str="select sum(kuantitas) as stok from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tanggal."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='CPO') and kodeorg='".$kodeorg."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $stokhi=$bar['stok'];	 
		
		#pemasaran - ba pengiriman
		$str="select jumlah from ".$dbname.".pmn_bapengiriman where unit='".$kodeorg."' and tanggal='".$tanggal."' and kodebarang='40000001' "; 	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
             $kirimponton=$bar['jumlah'];

             // Sri Rahayu via VA group KSP - OWL Project, tanpa kirim ponton (2021-09-23)
		// $kg=$stokhi-$stokkemarin+$returhi+$kirimhi+$kirimponton;
        if($stokhi == ''||$stokhi==0){//kemungkinan ga ada produksi
            $kg =0;
        }else{
            $kg=($stokhi-$stokkemarin)+$returhi+$kirimhi;
        }
		// exit('error');
		// exit("Error:".$stokhi.",".$stokkemarin.",".$returhi.",".$kirimhi);
        
        echo $kg;
    break;


    case'getKernel':
	
        #ambil produksi terakhir
        $str="select tanggal from ".$dbname.".pabrik_produksi where oerpk != '0' and tanggal < '".$tanggal."' order by tanggal desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $tgltrkhirprod=$bar['tanggal'];
        $tglKmrn = $tgltrkhirprod;//kemarin itu = tanggal terakhir produksi dari tgl yg dipilih
        // $tglKmrn = tglkemarin($tanggal);//tglkemarin ambil dari tgl dipilih bukan dari terakhir yg ada di produksi bermasalah kalau mau edit tgl backdate
    	#sounding  hr ini
        $str="select sum(kernelquantity) as stok from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tanggal."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='KER') and kodeorg='".$kodeorg."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $stokhi=$bar['stok'];

        #stock  kemarin
        $str="select sum(kernelquantity) as stok from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tglKmrn."' and kodeorg='".$kodeorg."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='KER')";
        //exit('warning:'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $stokkemarin=$bar['stok'];

        #retur  hi
		$str="select sum(jumlah) as stok from ".$dbname.".pabrik_pembersihantangki where"
                . " kodebarang='40000002' and kodeorg='".$kodeorg."' and  left(tanggal,10)='".$tanggal."' and tipe='Return'  "; 	
                // . " kodebarang='40000002' and kodeorg='".$kodeorg."' and  left(tanggal,10)='".$tanggal."' and tipe='Return'  "; 	
				// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
             $returhi=$bar['stok'];	
            
			
		#kirim  hi
		$str="select sum(beratbersih) as stok from ".$dbname.".pabrik_timbangan_vw where"
                . " kodebarang='40000002' and millcode='".$kodeorg."' and  tanggal='".$tanggal."' and wbcond !='langsirgudang' "; 	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
             $kirimhi=$bar['stok']-$loadinggudang;	

        $dataAdjust = array();
        $adjustmen = fetchdata("SELECT kodebarang,jumlah,tipe FROM pabrik_bakoreksistok  WHERE left(tanggal,10)  = '{$tanggal}' AND unit = '{$kodeorg}'");
        foreach($adjustmen as $adval){
            if (!isset($dataAdjust[$adval['kodebarang']])) {
                $dataAdjust[$adval['kodebarang']] = 0;
            }
            if ($adval['tipe'] == 'OUT') {
                $dataAdjust[$adval['kodebarang']] -= $adval['jumlah'];
            } else {
                $dataAdjust[$adval['kodebarang']] += $adval['jumlah'];
            }
        }
        if($stokhi == ''||$stokhi==0){//kemungkinan ga ada produksi
            $kg =0;
        }else{
            $kg=($stokhi+$dataAdjust['40000002']+$kirimhi)-$stokkemarin+$returhi;
        }
        
        echo $kg;
    break;
    
	break;


    case'getData':
        ##bentuk tanggal kemarin
    
        #ambil sisa tbs kemarin
        $str="select sisahariini,sisahariininetto from ".$dbname.".pabrik_produksi where kodeorg='".$kodeorg."' and "
                . " tanggal='".$tglKmrn."' ";
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $tbsKmrn=$bar['sisahariini'];
            $tbsKmrnnetto=$bar['sisahariininetto'];
            
        #ambil timbangan hari ini
        $str="select sum(beratbersih) as beratbersih,sum(kgpotsortasi) as kgpotsortasi,sum(beratbersih-kgpotsortasi) as beratnormal 
				from ".$dbname.".pabrik_timbangan_vw where millcode='".$kodeorg."' and 
				tanggal='".$tanggal."' and kodebarang='40000003'"; 
				// tanggal='".$tanggal."' and kodebarang='40000003' and left(notiket,1)!='x' "; 
				// exit("Error:".$str);
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $tbsHr=$bar['beratbersih'];
            $tbsHrnetto=$bar['beratnormal'];
		
		#tbs olah hari ini
		# ambil rata2 lori / kg dgn rumus : total tbs (tbshi(pabrik_rimbangan)+restankmrn(pabrik_produksi) / total lori (lorihi+lorirestan (pabrik_pengolahan));
		#= ithaca di 0kan olahnya (bisa manual)
		$tbsolah=0;
		$tbsolahnetto=0;
		          
        if($tbsKmrn!=''){
			$tbsKmrn=$tbsKmrn;
            $tbsKmrn=number_format($tbsKmrn,2);
			$tbsKmrn=str_replace(',','',$tbsKmrn);
		} else {
            $tbsKmrn=0;
		}
		
		if($tbsHr!=''){
            $tbsHr=$tbsHr;
			$tbsHr=str_replace(',','',$tbsHr);
		} else {
            $tbsHr=0;
		}
		
		if(is_nan($tbsolah)){
            $tbsolah=0;
		} else {
            $tbsolah=$tbsolah;
            $tbsolah=number_format($tbsolah,2);
			$tbsolah=str_replace(',','',$tbsolah);
		}
		
		
		if($tbsKmrnnetto!=''){
            $tbsKmrnnetto=$tbsKmrnnetto;
			$tbsKmrnnetto=number_format($tbsKmrnnetto,2);
			$tbsKmrnnetto=str_replace(',','',$tbsKmrnnetto);
		} else {
            $tbsKmrnnetto=0;
		}
		
		if($tbsHrnetto!=''){
            $tbsHrnetto=$tbsHrnetto;
			$tbsHrnetto=str_replace(',','',$tbsHrnetto);
        }else {
            $tbsHrnetto=0;
		}
		
		if(is_nan($tbsolahnetto)){
            $tbsolahnetto=0;
		}else{
            $tbsolahnetto=$tbsolahnetto;
            $tbsolahnetto=number_format($tbsolahnetto,2);
			$tbsolahnetto=str_replace(',','',$tbsolahnetto);
        }
		// $tbsKmrn=6000;
        // echo $tbsKmrn."###".$tbsHr."###".floatval($tbsolah)."###".$tbsKmrnnetto."###".$tbsHrnetto."###".floatval($tbsolahnetto);
        echo number_format($tbsKmrn)."###".number_format($tbsHr)."###".floatval($tbsolah)."###".$tbsKmrnnetto."###".$tbsHrnetto."###".floatval($tbsolahnetto);
            // exit("Error:");
       
    break;
    
    case'unposting':
		$str="update ".$dbname.".pabrik_produksi set posting='0', updateby='" . $_SESSION['standard']['userid'] . "'
		where kodeorg='".$kodeorg."' and tanggal='".$tanggal."'";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
	break;
	
    
    case'getDetailPP':
        $str="select * from ".$dbname.".pabrik_produksi
      where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$_POST['tgl']."'";

        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rdata=$res->fetch();
        
        
        $tCpoLoses=$rdata['fruitineb']+$rdata['ebstalk']+$rdata['fibre']+$rdata['nut']+$rdata['effluent'];
		//+$rdata['soliddecanter']+$rdata['hydrocyclone']
        $tKernelLoses=$rdata['fruitinebker']+$rdata['cyclone']+$rdata['ltds']+$rdata['claybath'];
                             
        
        echo "<fieldset style='width:700px;'>
                <legend>".$_SESSION['lang']['data'].":</legend>
                        <table><tr><td>

                        <table>
                           <tr>
                             <td>
                                    ".$_SESSION['lang']['kodeorganisasi']."
                                 </td>
                             <td>".$rdata['kodeorg']."
                                 </td>
                           </tr>
                           <tr> 
                                 <td>".$_SESSION['lang']['tanggal']."</td>
                                 <td>".tanggalnormal($rdata['tanggal'])."
                                 </td>	
                             <td>		 
                         </tr>
                           <tr>
                             <td>
                                    ".$_SESSION['lang']['sisatbskemarin']."
                                 </td>
                             <td>".number_format($rdata['sisatbskemarin'],0)."
                                 </td>
                           </tr>
                           <tr> 
                             <td>
                                    ".$_SESSION['lang']['tbsmasuk']."
                                 </td>
                                 <td>
                                    ".number_format($rdata['tbsmasuk'],0)."
                                 </td>	 		 
                         </tr>		
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['tbsdiolah']."
                                 </td>
                             <td>
                                    ".number_format($rdata['tbsdiolah'],0)."
                                 </td>		 
                         </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['sisa']."
                                 </td>
                                 <td>   ".number_format($rdata['sisahariini'],0)."
                                 </td>		 
                         </tr>	";
                       echo" <tr>
                             <td>% USB Before Collector
                                 </td>
                             <td>".$rdata['usbbefore']." %
                                 </td>		 
                         </tr>	  
                          <tr>
                             <td>% USB After Collector
                                 </td>
                             <td>".$rdata['usbafter']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil Diluted Crude Oil
                                 </td>
                             <td>".$rdata['oildiluted']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil in underflow (CST)
                                 </td>
                             <td>".$rdata['oilin']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil in Heavy Phase - S/D
                                 </td>
                             <td>".$rdata['oilinheavy']." % 
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>CaCO3
                                 </td>
                             <td>".$rdata['caco']." KG
                                 </td>		 
                         </tr>	";
                  echo"</table>	  
                  </td>
                  <td valign=top>  
                <table>
                        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['cpo']."</legend>
                         <table>
                         <tr><td>".$_SESSION['lang']['cpo']."(Kg)
                                 </td>
                                 <td>
                                   ".number_format($rdata['oer'])."
                                 </td>
                          </tr>
                          <tr><td>".$_SESSION['lang']['oer']."
                                 </td>
                                 <td>
                                   ".(@number_format($rdata['oer']/$rdata['tbsdiolah']*100,2,'.',','))." %
                                 </td>
                          </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kotoran']."
                                 </td>
                             <td>
                                  ".$rdata['kadarkotoran']."%
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kadarair']."
                                 </td>
                                 <td>
                                   ".$rdata['kadarair']."%.
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    FFa
                                 </td>
                             <td>
                                  ".$rdata['ffa']." %. 
                                 </td>			 
                         </tr>
                         <tr>
                             <td>
                                    Dobi
                                 </td>
                             <td>
                                  ".$rdata['dobi']." %. 
                                 </td>           
                         </tr>		   	   
                        </table>
                        </fieldset>

                        </td>
                        </tr>

        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['cpo']." Loses</legend>
                         <table>
                         <tr><td>Centrifuge
                                 </td>
                                 <td>".$rdata['hydrocyclone']." %
                                 </td>
                          </tr>
                         <tr><td>Fruit In Empty Bunch
                                 </td>
                                 <td>
                                    ".$rdata['fruitineb']." KG
                                 </td>
                          </tr>
                         <tr>
                             <td>Empty Bunch Stalk 
                                 </td>
                             <td>".$rdata['ebstalk']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>Fibre From Press Cake
                                 </td>
                                 <td>".$rdata['fibre']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>Nut From Press Cake
                                 </td>
                             <td>".$rdata['nut']." %
                                 </td>			 
                         </tr>	
                          <tr>
                             <td>Effluent
                                 </td>
                             <td>".$rdata['effluent']." %
                                 </td>			 
                         </tr>	
                           <tr>
                             <td>Solid Decanter
                                 </td>
                             <td>".$rdata['soliddecanter']." %
                                 </td>			 
                         </tr>	
                          <tr>
                             <td><b>Total</b>
                                 </td>
                             <td><b>".$tCpoLoses." %</b>
                                 </td>			 
                         </tr>
                         



                        </table>
                        </fieldset>

                        </td>
                        </tr>
                        </table>	
            </td>
                <td valign=top>
                <table>
                        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['kernel']."</legend>
                         <table>
                         <tr><td>
                                    ".$_SESSION['lang']['kernel']."(Kg)
                                 </td>
                                 <td>
                                    ".number_format($rdata['oerpk'])." Kg.
                                 </td>
                          </tr>
                          <tr><td>
                                    ".$_SESSION['lang']['oerpk']."
                                 </td>
                                 <td>
                                    ".(@number_format($rdata['oerpk']/$rdata['tbsdiolah']*100,2,'.',','))." %
                                 </td>
                          </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kotoran']."
                                 </td>
                             <td>".$rdata['kadarkotoranpk']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kadarair']."
                                 </td>
                                 <td>".$rdata['kadarairpk']." %. 
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    Broken
                                 </td>
                             <td>".$rdata['ffapk']." %.
                                 </td>			 
                         </tr>	

                        </table>
                        </fieldset>

                        </td>
                        </tr>
                        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['kernel']." Loses</legend>
                         <table>
                         <tr style=display:none><td>USB

                                 </td>
                                 <td>".$rdata['usbpk']." %
                                 </td>
                          </tr>
                         <tr><td>Fruit In Empty Bunch

                                 </td>
                                 <td>".$rdata['fruitinebker']." KG
                                 </td>
                          </tr>
                         <tr>
                             <td>Fibre Cyclone
                                 </td>
                             <td>".$rdata['cyclone']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>LTDS
                                 </td>
                                 <td>".$rdata['ltds']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>Claybath
                                 </td>
                             <td>".$rdata['claybath']." %
                                 </td>			 
                         </tr>
                         
                            <tr>
                             <td><b>Total</b>
                                 </td>
                             <td><b>".$tKernelLoses." %</b>
                                 </td>			 
                         </tr>

                        </table>
                        </fieldset>

                        </td>
                        </tr>
                        </table>	


                </td>
                </tr>	  

                </table>	
                  </fieldset>
                 ";
        
    break;
}
?>