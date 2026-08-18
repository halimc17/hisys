<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

// require_once('dompdf/autoload.inc.php');
// use Dompdf\Dompdf;

$notrans_cek=checkPostGet('notrans_cek','');
$nocekpil=checkPostGet('nocekpil','');
$tglangsuran=tanggalsystem(checkPostGet('tglangsuran',''));
$unit=checkPostGet('unit','');
$notransaksi=checkPostGet('notransaksi','');
$notadebet=checkPostGet('notadebet','');
$norekening_bank=checkPostGet('rekening','');
$supplierid_asuransi=checkPostGet('namaasuransi','');
$supplierid_leasing=checkPostGet('namavendor','');
$nokontrak_asuransi=checkPostGet('kontrakasuransi','');
$nokontrak_leasing=checkPostGet('kontrakvendor','');
$tgl_efektif=tanggalsystem(checkPostGet('tglefektif',''));
$tgl_pelunasan=tanggalsystem(checkPostGet('tgllunas',''));
$status_leasing=checkPostGet('statuskontrak','');
$kuantitas=checkPostGet('kuantitas','');
$nopol=checkPostGet('nopol','');
$harga_barang=checkPostGet('hargabarang','');
$status=checkPostGet('status','');
$uang_muka=checkPostGet('uangmuka','');
$utang_pokok=checkPostGet('utangpokok','');
$suku_bungapertahun=checkPostGet('sukubunga','');
$bunga_pertahun=checkPostGet('bunga','');
$tenor=checkPostGet('tenor','');
$totalkredit=checkPostGet('totalkredit','');
$angsuran=checkPostGet('angsuran','');
$metode_bayar=checkPostGet('metbayar','');
$bayar_pertama=checkPostGet('pembayaran','');
$administrasi=checkPostGet('administrasi','');
$survey=checkPostGet('survey','');
$asuransi=checkPostGet('asuransi','');
$fidusia=checkPostGet('fidusia','');
$provisi=checkPostGet('provisi','');
$notaris=checkPostGet('notaris','');
$denda_terlambatan=checkPostGet('denda','');
$method=checkPostGet('method','');
$notranscr=checkPostGet('notranscr', '');
$tipecr=checkPostGet('tipecr', '');
$arrstatus=array('1'=>$_SESSION['lang']['aktif'],'2'=>$_SESSION['lang']['lunas']);
switch ($method){

	case 'getreknopol':

        $optakun=$optnopol="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select b.namabank,a.noakun,a.rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where pemilik='".$unit."' order by b.namabank ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            if ($norekening_bank==$bar['noakun']){
               $optakun.="<option value='".$bar['noakun']."' selected>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }else{
               $optakun.="<option value='".$bar['noakun']."'>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }
        }

        #option no.polisi
        $str=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$unit."'");
        $str->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$str->fetch();
        $induk=$bar['induk'];

		$res=$owlPDO->query("select nopol,kodevhc from ".$dbname.".vhc_5master where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$induk."')");
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){
            if ($bar['nopol']=='') {
                $bar['nopol']=$bar['kodevhc'];
            }

            if ($nopol==$bar['nopol']){
                $optnopol.="<option value='".$bar['nopol']."' selected>".$bar['nopol']."</option>";
            }else{
                $optnopol.="<option value='".$bar['nopol']."'>".$bar['nopol']."</option>";
            }
        }

		echo $optakun."####".$optnopol;

	break;

    case'getBulan':

        if($_POST['tgllunas']==''){
            $_POST['tgllunas']=$_POST['tglefektif'];
        }
		$date1 = date("Y-m-d",strtotime($_POST['tglefektif']));
		$date2 = date("Y-m-d",strtotime($_POST['tgllunas']));
		if(date("Y-m",strtotime($_POST['tglefektif'])) >= date("Y-m",strtotime($_POST['tgllunas']))){
			exit("WARNING : Tanggal Pelunasan harus diatas tanggal efektif!");
		}
        $jmlhBulan = datediff($date1,$date2);
        $jmlhBulan['months_total']+=1;
        
        echo $jmlhBulan['months_total'];
        
    break;

    case'getdatadebet':

        $tab="";
        $tab.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $tab.="<div style=overflow:auto;width:auto;height:200px;>";
        $tab.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $tab.="<thead>";
        $tab.="<tr align=center>";
        $tab.="<td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['notransaksi']."</td>";
        $tab.="<td>".$_SESSION['lang']['namasupplier']."</td>";
        $tab.="<td>".$_SESSION['lang']['hargabarang']."</td>";
        $tab.="<td>".$_SESSION['lang']['uangmuka']."</td>";
        $tab.="<td>".$_SESSION['lang']['utangpokok']."</td>";
        $tab.="</tr></thead>";

        //get noakun bank
        $str1="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='TRLE'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $noakundebet=$rtr['noakundebet'];

        $strpa="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TRLE'";
        $respa=$owlPDO->query($strpa) or die(print " Gagal: ".PDOException::getMessage());
        $respa->setFetchMode(PDO::FETCH_ASSOC);
        $barpa=$respa->fetch();
        $akunpa=explode(',', $barpa['nilai']);
        $noakundebet="'".$akunpa[0]."','".$akunpa[1]."'";

        $no = 0;
        $str="select * from ".$dbname.".keu_notadebet_ht a left join keu_notadebet_dt b on a.notadebet=b.notadebet where unit='".$unit."' and b.noakun in (".$noakundebet.") and kodesupplierdt!='' and a.notadebet not in (select notadebet from ".$dbname.".keu_leasinght where posting=1)";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {

            $whrsup="supplierid='".$bar['kodesupplier']."'";
            $optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);

            //get nopo
            $str1="select nopo from ".$dbname.".keu_tagihanht where noinvoice='".$bar['noinvoice_referensi']."'";
            $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $qtr->setFetchMode(PDO::FETCH_ASSOC);
            $rtr=$qtr->fetch();
            $nopo=$rtr['nopo'];

            //get nilaipo
            $str1="select nilaipo from ".$dbname.".log_poht where nopo='".$nopo."'";
            $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $qtr->setFetchMode(PDO::FETCH_ASSOC);
            $rtr=$qtr->fetch();
            $nilaipo=$rtr['nilaipo'];

            // get nopo
            $str1="select nilai from ".$dbname.".keu_tagihandt where noinvoice='".$bar['noinvoice_referensi']."' and left(noakun,5)='11802'";
            $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $qtr->setFetchMode(PDO::FETCH_ASSOC);
            $rtr=$qtr->fetch();
            $uangmuka=(-1)*$rtr['nilai'];

            $no+=1;
            $tab.="<tr class=rowcontent style='cursor:pointer;' onclick=setdata('".$bar['notadebet']."','".number_format($nilaipo)."','".number_format($uangmuka)."','".number_format($bar['nilaiinvoice'])."')>   
                <td align=center>".$no."</td>
                <td>".$bar['notadebet']."</td>
                <td>".$optsup[$bar['kodesupplier']]."</td>
                <td align=right>".number_format($nilaipo)."</td>
                <td align=right>".number_format($uangmuka)."</td>
                <td align=right>".number_format($bar['nilaiinvoice'])."</td>
                </tr>";
        }

        $tab.="</table></div></fieldset>";

        echo $tab;
        
    break;

	case 'insertht':

        if ($unit=='' ||$norekening_bank=='' ||$supplierid_asuransi=='' ||$nokontrak_asuransi=='' ||$supplierid_leasing=='' ||$nokontrak_leasing=='' ||$status_leasing=='' ||$kuantitas=='' ||$tgl_efektif=='' ||$tgl_pelunasan=='' ||$harga_barang==0 ||$uang_muka==0 ||$utang_pokok==0 ||$suku_bungapertahun==0 ||$bunga_pertahun==0 ||$tenor==0 ||$totalkredit==0 ||$angsuran==0 ||$metode_bayar=='' ||$bayar_pertama==0 ||$administrasi==0 ||$denda_terlambatan==0 ||$metode_bayar=='' ) {
            exit('warning : Field was empty.');
        }

		$tahunbulan="LE".date("Ym");
        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_leasinght where left(notransaksi,8)='".$tahunbulan."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
        if(intval($rp['nomorurut'])==0){
          $awal=1;
        }else{
          $awal=intval($rp['nomorurut'])+1;
        }
        $notransaksi=$tahunbulan.addZero($awal,3);

        $harga_barang=str_replace(',', '', $harga_barang);
        $uang_muka=str_replace(',', '', $uang_muka);
        $utang_pokok=str_replace(',', '', $utang_pokok);
        $bunga_pertahun=str_replace(',', '', $bunga_pertahun);
        $totalkredit=str_replace(',', '', $totalkredit);
        $angsuran=str_replace(',', '', $angsuran);
        $bayar_pertama=str_replace(',', '', $bayar_pertama);
        $administrasi=str_replace(',', '', $administrasi);
        $survey=str_replace(',', '', $survey);
        $asuransi=str_replace(',', '', $asuransi);
        $fidusia=str_replace(',', '', $fidusia);
        $provisi=str_replace(',', '', $provisi);
        $notaris=str_replace(',', '', $notaris);
        $denda_terlambatan=str_replace(',', '', $denda_terlambatan);

        $str="insert into ".$dbname.".keu_leasinght (notransaksi,notadebet,kodeorg,norekening_bank,supplierid_asuransi,nokontrak_asuransi,supplierid_leasing,createdby,updateby,tgl_efektif,tgl_pelunasan,nokontrak_leasing,status_leasing,kodevhc,kuantitas,harga_barang,uang_muka,utang_pokok,suku_bungapertahun,bunga_pertahun,tenor,totalkredit,angsuran,metode_bayar,bayar_pertama,administrasi,survey,asuransi,fidusia,provisi,notaris,denda_terlambatan)
                values ('".$notransaksi."','".$notadebet."','".$unit."','".$norekening_bank."','".$supplierid_asuransi."','".$nokontrak_asuransi."','".$supplierid_leasing."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$tgl_efektif."','".$tgl_pelunasan."','".$nokontrak_leasing."','".$status_leasing."','".$nopol."','".$kuantitas."','".$harga_barang."','".$uang_muka."','".$utang_pokok."','".$suku_bungapertahun."','".$bunga_pertahun."','".$tenor."','".$totalkredit."','".$angsuran."','".$metode_bayar."','".$bayar_pertama."','".$administrasi."','".$survey."','".$asuransi."','".$fidusia."','".$provisi."','".$notaris."','".$denda_terlambatan."')";

		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}

	break;

    case'deleteht':

        $strdt="delete from ".$dbname.".keu_leasinght where notransaksi='".$notransaksi."'";
        try {
            $owlPDO->exec($strdt);

            $strcek="delete from ".$dbname.".keu_bukucekdt where notransaksi='".$notransaksi."'";
            try {
                $owlPDO->exec($strcek);
            } catch (PDOException $e) {
                print " Gagal: " . $e->getMessage() . "\n";
                die();
            }

        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'updateht':

        if ($unit=='' ||$norekening_bank=='' ||$supplierid_asuransi=='' ||$nokontrak_asuransi=='' ||$supplierid_leasing=='' ||$nokontrak_leasing=='' ||$status_leasing=='' ||$kuantitas=='' ||$tgl_efektif=='' ||$tgl_pelunasan=='' ||$harga_barang==0 ||$uang_muka==0 ||$utang_pokok==0 ||$suku_bungapertahun==0 ||$bunga_pertahun==0 ||$tenor==0 ||$totalkredit==0 ||$angsuran==0 ||$metode_bayar=='' ||$bayar_pertama==0 ||$administrasi==0 ||$denda_terlambatan==0 ||$metode_bayar=='' ) {
            exit('warning : Field was empty.');
        }

        $harga_barang=str_replace(',', '', $harga_barang);
        $uang_muka=str_replace(',', '', $uang_muka);
        $utang_pokok=str_replace(',', '', $utang_pokok);
        $bunga_pertahun=str_replace(',', '', $bunga_pertahun);
        $totalkredit=str_replace(',', '', $totalkredit);
        $angsuran=str_replace(',', '', $angsuran);
        $bayar_pertama=str_replace(',', '', $bayar_pertama);
        $administrasi=str_replace(',', '', $administrasi);
        $survey=str_replace(',', '', $survey);
        $asuransi=str_replace(',', '', $asuransi);
        $fidusia=str_replace(',', '', $fidusia);
        $provisi=str_replace(',', '', $provisi);
        $notaris=str_replace(',', '', $notaris);
        $denda_terlambatan=str_replace(',', '', $denda_terlambatan);

        $strht="update ".$dbname.".keu_leasinght set kodeorg='".$unit."',notadebet='".$notadebet."',norekening_bank='".$norekening_bank."',supplierid_asuransi='".$supplierid_asuransi."',updateby='".$_SESSION['standard']['userid']."', nokontrak_asuransi='".$nokontrak_asuransi."',supplierid_leasing='".$supplierid_leasing."',tgl_efektif='".$tgl_efektif."',tgl_pelunasan='".$tgl_pelunasan."', nokontrak_leasing='".$nokontrak_leasing."',status_leasing='".$status_leasing."',kodevhc='".$nopol."',kuantitas='".$kuantitas."',harga_barang='".$harga_barang."',uang_muka='".$uang_muka."',utang_pokok='".$utang_pokok."',suku_bungapertahun='".$suku_bungapertahun."',bunga_pertahun='".$bunga_pertahun."',tenor='".$tenor."',totalkredit='".$totalkredit."',angsuran='".$angsuran."',metode_bayar='".$metode_bayar."',bayar_pertama='".$bayar_pertama."',administrasi='".$administrasi."',survey='".$survey."',asuransi='".$asuransi."',fidusia='".$fidusia."',provisi='".$provisi."',notaris='".$notaris."',denda_terlambatan='".$denda_terlambatan."'  where notransaksi='".$notransaksi."'";    
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'postinght':

        /*$qTrans="SELECT * FROM ".$dbname.".keu_leasinght a left join ".$dbname.".keu_leasingdt b on a.notransaksi=b.notransaksi WHERE b.notransaksi='".$notransaksi."' and tenor_ke=1";
        $data=fetchData($qTrans);
        $bar=$data[0];

        $bunga=0;
        $angsuran=0;
        //Get angsuran dan bunga per angsuran
        $bunga=$bar['bunga_pertahun']/$bar['tenor'];
        $angsuran=$bar['angsuran']-$bunga;

        //get induk
        $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
        $ressup=$owlPDO->query($sqlkd);
        $ressup->setFetchMode(PDO::FETCH_ASSOC);
        $barsup=$ressup->fetch();
        $induk=$barsup['induk'];

        $kodejurnal="LE01";
        $tglinput=str_replace('-','',$bar['tgl_transaksi']);
        $tgljurnal=$tglinput;
        $ket="Jurnal Otomatis Atas Pembayaran Angsuran Leasing ke - ".$bar['tenor_ke']." atas no.kontrak leasing ".$bar['nokontrak_leasing'];

        //get noakundebet
        $sqlkd="select noakun from ".$dbname.".keu_notadebet_dt where notadebet='".$bar['notadebet']."'";
        $ressup=$owlPDO->query($sqlkd);
        $ressup->setFetchMode(PDO::FETCH_ASSOC);
        $barsup=$ressup->fetch();
        $noakundebet=$barsup['noakun'];

        //get noakun bank
        $str1="select noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $noakunkredit=$rtr['noakunkredit'];

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
                values ('".$notrans."','".$kodejurnal."','".$angsuran."','".-($angsuran)."','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
        try{

            $owlPDO->exec($strht);
            $str=array();
            //insert jurnalht debet
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$angsuran."','IDR','1','".$bar['kodeorg']."','".$bar['notransaksi']."','".$bar['nokontrak_leasing']."')";

            //insert jurnalht kredit
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($angsuran)."','IDR','1','".$bar['kodeorg']."','".$bar['notransaksi']."','".$bar['nokontrak_leasing']."')";

            $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";

            $str[]="update ".$dbname.".keu_leasinght set posting='1' where notransaksi='".$notransaksi."'";

            if(count($str)!=0){
                for($i=0; $i<count($str); $i++){
                    try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                }   
            }

        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }*/

        $str="update ".$dbname.".keu_leasinght set posting='1' where notransaksi='".$notransaksi."'";
        try{ 
            $owlPDO->exec($str); 
        }catch (PDOException $e){
         echo "Error : ".$str."__".$e->getMessage(); die(); 
        }

    break;

	case 'loadData':

        $where=" 1=1 ";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $where.="";
             $where.= " and kodeorg in (".getOrgDetail(2).")";
        }else{
            $where.=" and (kodeorg='".$_SESSION['empl']['lokasitugas']."'";
             $where.= " or kodeorg in (".getOrgDetail(2)."))";
        }

        if ($notransaksi != '') {
            $where.=" and notransaksi like '%".$notransaksi."%' ";
        }

        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);

        $str="select * from ".$dbname.".keu_leasinght where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{

            $tab="";
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_leasinght where ".$where." order by notransaksi desc limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){

                $whrpt="kodeorganisasi='".$bar->kodeorg."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);

                $nmBankDt="";
                $strak="select b.namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where noakun='".$bar->norekening_bank."'";
                $barak=fetchData($strak);
                $dtRek=$barak[0];
                $nmBankDt=$dtRek['namabank']." - ".$bar->norekening_bank;

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->notransaksi."</td>
                    <td>".$optpt[$bar->kodeorg]."</td>
                    <td>".$nmBankDt."</td>
                    <td>".tanggalnormal($bar->tgl_efektif)."</td>
                    <td>".tanggalnormal($bar->tgl_pelunasan)."</td>
                    <td align=center>".$arrstatus[$bar->status_leasing]."</td>";
                    if ($bar->posting==0){
                        $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"updateht('".$bar->notransaksi."','".$bar->notadebet."','".$bar->kodeorg."','".$bar->norekening_bank."','".$bar->supplierid_asuransi."','".$bar->nokontrak_asuransi."','".$bar->supplierid_leasing."','".$bar->nokontrak_leasing."','".tanggalnormal($bar->tgl_efektif)."','".tanggalnormal($bar->tgl_pelunasan)."','".$bar->status_leasing."','".$bar->kodevhc."','".$bar->kuantitas."','".number_format($bar->harga_barang)."','".number_format($bar->uang_muka)."','".number_format($bar->utang_pokok)."','".$bar->suku_bungapertahun."','".number_format($bar->bunga_pertahun)."','".number_format($bar->tenor)."','".number_format($bar->totalkredit)."','".number_format($bar->angsuran)."','".$bar->metode_bayar."','".number_format($bar->bayar_pertama)."','".number_format($bar->administrasi)."','".number_format($bar->survey)."','".number_format($bar->asuransi)."','".number_format($bar->fidusia)."','".number_format($bar->provisi)."','".number_format($bar->notaris)."','".number_format($bar->denda_terlambatan)."')\"></td>
                               <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteht('".$bar->notransaksi."');\" ></td>
                               <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"postinght('".$bar->notransaksi."');\" ></td>
                            <td><img src='images/addplus.png' class=resicon title='Add Detail' onclick=adddetail('".$bar->notransaksi."')>";
                            $tab.="&nbsp;<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"viewdetail('".$bar->notransaksi."');\">&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30' title='PDF' onclick=\"makepdfx('".$bar->notransaksi."');\"></td>"; 
                    }else{
                        $tab.="<td align=center colspan=4 style='width:50px'><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"viewdetail('".$bar->notransaksi."');\"> <img src=images/pdf.jpg class=resicon class=zImgBtn height='30' title='PDF' onclick=\"makepdfx('".$bar->notransaksi."');\"></td>"; 
                    }

                $tab.="</tr>";
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                $sel=($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=15 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;

    break;

    case 'adddetail':

        //get tenor
        $optnocek=$optbukucek=$opttenor="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select tenor,metode_bayar,norekening_bank from ".$dbname.".keu_leasinght where notransaksi='".$notransaksi."'";
        $qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $tenor=$rtr['tenor'];
        $metode_bayar=$rtr['metode_bayar'];
        $norekening_bank=$rtr['norekening_bank'];
        $jmlhangsuran=$tenor;

        for ($i=1; $i<=$jmlhangsuran ; $i++) { 

            $str = "select tenor_ke from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."' and tenor_ke='".$i."' order by tenor_ke asc";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            if($bar['tenor_ke']!=''){
                continue;
            }

            $opttenor.="<option value='".$i."'>".$i."</option>";
        }

        //get bukucek
        $str="select * from ".$dbname.".keu_bukucekht where noakun='".$norekening_bank."' and status='1' and tipe_buku='".$metode_bayar."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $optbukucek.="<option value='".$bar['notrans_cek']."'>".$bar['nocek_awal']." - ".$bar['nocek_akhir']."</option>";
        }

        $tab="";
        $tab="
        <fieldset><legend>".$_SESSION['lang']['detail']."</legend>
            <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['tanggalangsuran']."</td>
                <td align=center>Tenor Ke-</td>
                <td align=center>Buku ".$metode_bayar."</td>
                <td align=center>".$_SESSION['lang']['nourut']." Buku ".$metode_bayar."</td>
                <td align=center>".$_SESSION['lang']['action']."</td>
            </tr>
            </thead>
            <tbody>
            <tr class=rowcontent>
                <td style='vertical-align:top;'>				
				<input type=text class=myinputtext id=tglangsuran onmousemove=setCalendar(this.id) style=width:150px; maxlength=10 readonly/>
				</td>
                <td><select id=tenor_ke style='width:150px'>".$opttenor."</select></td>
                <td><select id=notrans_cek style='width:150px' onchange='getdetailnocek()'>".$optbukucek."</select></td>
                <td><select id=nocekpil style='width:150px'>".$optnocek."</select></td>
                <td align=center>
                <input type=hidden id=methoddt value='insertdt'>
                <input type=hidden id=notransaksidt value='".$notransaksi."'>
                    <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=savedetail() src='images/save.png'/>
                </td>
            </tr>
            </tbody>
            </table><br><br>";

        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['tgltransaksi']."</td>";
        $tab.="<td>".$_SESSION['lang']['tenor']."</td>";
        $tab.="<td>".$_SESSION['lang']['nourut']." Buku ".$metode_bayar."</td>";
        $tab.="<td>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody >";

        $no=0;
        $str="select * from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."' order by tenor_ke asc";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){

            $no+=1;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".tanggalnormal($bar['tgl_transaksi'])."</td>
                <td align=center>".$bar['tenor_ke']."</td>
                <td align=center>".$bar['no_bukucek']."</td>";
            if ($bar['statuskasbank']==0){
                $isi="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletedt('".$bar['notransaksi']."','".$bar['tenor_ke']."','".$bar['no_bukucek']."');\" >";
            }else{
                $isi="";
            }
            $tab.="<td width=10px align=center>".$isi."</td>";
            $tab.="</tr>";
        }

        $tab.="</tbody>";
        $tab.="</table>";

        $tab.="</fieldset>";

        echo $tab;
        
    break;

    case'getdetailnocek':

        $strht="select * from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."'";
        $resht= $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht = $resht->fetch();
        $nocek_awal=$barht['nocek_awal'];
        $tipe_buku=$barht['tipe_buku'];
        $jumlahangka=strlen($nocek_awal);

        $str = "select count(notrans_cek) as jumlahdt from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' order by nocek desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $jumlahdt=$bar['jumlahdt'];

        if($tipe_buku=='PO'){
            $angkaawal=intval($barht['nocek_awal']);
            $angkaakhir=intval($barht['nocek_akhir']);
        }else{
            $angkaawal=preg_replace("/[^0-9]/",'',$barht['nocek_awal']);
            $angkaakhir=preg_replace("/[^0-9]/",'',$barht['nocek_akhir']);
        }
        $sisa=$angkaakhir-$angkaawal-$jumlahdt+1;
        $selisih=$angkaakhir-$angkaawal+1;

        $nocek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        for ($i=1; $i <=$selisih ; $i++) { 

            if($tipe_buku=='PO'){
                $nocek_awal=addZero($nocek_awal,$jumlahangka);
            }

            $str = "select nocek from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' and nocek='".$nocek_awal."' order by nocek asc";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            if($bar['nocek']!=''){
                $nocek_awal++;
                continue;
            }

            $nocek.="<option value='".$nocek_awal."'>".$nocek_awal."</option>";

            $nocek_awal++;
        }

        echo $nocek;

    break;

    case 'insertdt':

        if ($tglangsuran=='' || $tenor=='') {
            exit('warning : field was empty.');
        }

        $statuskasbank=1;
        if ($tenor!=1) {
            if ($nocekpil=='') {
                exit('warning : field was empty.');
            }
            $statuskasbank=0;
        }

        $str="insert into ".$dbname.".keu_leasingdt (notransaksi,tgl_transaksi,tenor_ke,no_bukucek,statuskasbank)
                values ('".$notransaksi."','".$tglangsuran."','".$tenor."','".$nocekpil."','".$statuskasbank."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

        if ($tenor!=1) {
            $sDet="insert into ".$dbname.".keu_bukucekdt (notrans_cek,notransaksi,nocek,status_cek) 
                values ('".$notrans_cek."','".$notransaksi."','".$nocekpil."', '0')";
            try{ 
                $owlPDO->exec($sDet); 
            }
                catch (PDOException $e){
                echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
            }
        }

    break;

    case'deletedt':

        $strdt="delete from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."' and tenor_ke='".$tenor."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

        $strdt="delete from ".$dbname.".keu_bukucekdt where notransaksi='".$notransaksi."' and nocek='".$nocekpil."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
    break;


    case 'viewdetail':
        $strtenor="select count(*) as sisatenor from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."'  ";
        $restenor=$owlPDO->query($strtenor) or die(print " Gagal: " . PDOException::getMessage());
        $restenor->setFetchMode(PDO::FETCH_ASSOC);
        $bartenor=$restenor->fetch();


        $strht="select * from ".$dbname.".keu_leasinght where notransaksi='".$notransaksi."'";
        $resht=$owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht=$resht->fetch();
        $supplierid_leasing=$barht['supplierid_leasing'];
        $lessor=$barht['supplierid_leasing'];
        $notadebet=$barht['notadebet'];
        $norekening_bank=$barht['norekening_bank'];

        $strnd="select * from ".$dbname.".keu_notadebet_vw where notadebet='".$notadebet."'";
        $resnd=$owlPDO->query($strnd) or die(print " Gagal: " . PDOException::getMessage());
        $resnd->setFetchMode(PDO::FETCH_ASSOC);
        $barnd=$resnd->fetch();

        $noinvoice=$barnd['noinvoice_referensi'];
        $vendor=$barnd['kodesupplierdt'];

        $strth="select nopo from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
        $resth=$owlPDO->query($strth) or die(print " Gagal: " . PDOException::getMessage());
        $resth->setFetchMode(PDO::FETCH_ASSOC);
        $barth=$resth->fetch();

        $nopo=$barth['nopo'];

        $strpo="select * from ".$dbname.".log_po_vw where nopo='".$nopo."'";
        $respo=$owlPDO->query($strpo) or die(print " Gagal: " . PDOException::getMessage());
        $respo->setFetchMode(PDO::FETCH_ASSOC);
        $barpo=$respo->fetch();

        $optsupx=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$lessor."' or supplierid='".$vendor."'");

        $tab.="<table cellpading=1 cellspacing=0 border=0  align=left>";
        $tab.="<tr align=left><td>";
        $tab.="<table cellpading=1 cellspacing=0 border=0>";
        $tab.="<tr align=left><td> <b>Lessor</b> </td>";
        $tab.="<td> <b>: </td>";
        $tab.="<td> <b>".$optsupx[$lessor]."</b> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <b>Vendor</b> </td>";
        $tab.="<td> <b>: </td>";
        $tab.="<td> <b>".$optsupx[$vendor]."</b> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <b>PO.".$nopo."</b> </td>";
        $tab.="<td> <b>:</b> </td>";
        $tab.="<td> <b>".$barpo['jumlahpesan']." ".$barpo['unit']." ".$barpo['namabarang']."</b> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";


        $tab.="<tr align=left><td></td></tr>";

        $tab.="<tr align=left><td>";
        $tab.="<table cellpading=1 cellspacing=0 border=1 align=left width=100%>";
        $tab.="<tr align=left><td> Nomor Kontrak Vendor </td>";
        $tab.="<td align=right>".$barht['nokontrak_leasing']." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Nama Asuransi </td>";
        $tab.="<td align=left>".$barht['supplierid_asuransi']." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Nomor Kontrak Asuransi </td>";
        $tab.="<td align=left>".$barht['nokontrak_asuransi']." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Tingkat Suku Bunga (per Tahun) </td>";
        $tab.="<td align=right>".number_format($barht['suku_bungapertahun'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Denda Keterlambatan </td>";
        $tab.="<td align=right>".number_format($barht['denda_terlambatan'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Jumlah Angsuran </td>";
        $tab.="<td align=right>".($barht['tenor'])." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Tanggal Efektif </td>";
        $tab.="<td align=right>".tanggalnormal($barht['tgl_efektif'])." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> Tanggal Pelunasan </td>";
        $tab.="<td align=right>".tanggalnormal($barht['tgl_pelunasan'])." </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";

        

        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100%>";
        $tab.="<tr align=left><td   style='border:1px solid black;'> Rincian Harga  </td>";
        $tab.="<td style='border:1px solid black;width:50px;' align=center> DPP </td>";
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barpo['subtotal'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>  </td>";
        $tab.="<td style='border:1px solid black;width:50px;' align=center> PPN </td>";
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barpo['ppn'],2)." </td>";
        $tab.="</tr>";
        $totalharga=$barpo['subtotal']+$barpo['ppn'];
        $tab.="<tr align=left><td colspan=2 style='border:0;'> <b>Total Harga </b> </td>";
        $tab.="<td  style='border:0;' align=right><font><b> ".number_format($totalharga,2)."</b> </font></td>";
        $tab.="</tr>";
        $tab.="</table></td></tr>";

        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100%>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Uang Muka </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['uang_muka'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Administrasi </td>";
      
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['administrasi'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Fidusia </td>";
    
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['fidusia'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Survey </td>";
     
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['survey'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Provisi </td>";
       
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['provisi'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Notaris </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['notaris'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Asuransi </td>";

        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['asuransi'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Angsuran Ke-1 </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['angsuran'],2)." </td>";
        $tab.="</tr>";

        $totaluangmuka=$barht['administrasi']+$barht['fidusia']+$barht['survey']+$barht['provisi']+$barht['notaris']+$barht['asuransi']+$barht['angsuran']+$barht['uang_muka'];
        $tab.="<tr align=left><td style='border:0;'> <b>Total Pembayaran dimuka </b> </td>";
        $tab.="<td  style='border:0;' align=right><b> ".number_format($totaluangmuka,2)." </b></td>";
        $tab.="</tr>";
        $tab.="</table></td></tr>";


        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<tr align=left><td>";
        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100% >";
        $tab.="<tr align=left><td style='border:1px solid black;'>Nilai Kredit </td>";

        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['utang_pokok'],2)." </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'>Nilai Bunga </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> ".number_format($barht['bunga_pertahun'],2)." </td>";
        $tab.="</tr>";

        //$tenor=$barht['tenor']-1;
        $totalnilai=$barht['utang_pokok']+$barht['bunga_pertahun'];
        $tab.="<tr align=left><td style='border:0;'> <b>Total Kredit </b> </td>";
        $tab.="<td  style='border:0;' align=right><b> ".number_format($totalnilai,2)." </b></td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:0;'> <b>Sisa Tenor : ".($bartenor['sisatenor']-1)."</b> </td>";
       
        $tab.="<td  style='border:0;' align=right><b> ".number_format($totalnilai/$barht['tenor'],2)." </b></td>";
        $tab.="</tr>";
        $tab.="</table> </td></tr>";


        $tab.="<tr align=left><td></td></tr>";

        $tab.="<tr align=left><td>";
        $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggalangsuran']."</td>";
        $tab.="<td>".$_SESSION['lang']['nourut']." ".$barht['metode_bayar']."</td>";
        $tab.="<td>".$_SESSION['lang']['tenor']."</td>";
        $tab.="<td>".$_SESSION['lang']['angsuran']."</td>";
        $tab.="<td>Bunga</td>";
        $tab.="<td>".$_SESSION['lang']['pembayaran']." ".$_SESSION['lang']['pokok']."</td>";
        $tab.="<td>Status Bayar</td>";
        $tab.="<td>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['kasbank']."</td>";
        $tab.="</tr></thead><tbody >";
        
        $no=0;
        $str="select * from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."' order by tenor_ke ";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $no+=1;

            $statbyr = array('0' =>$_SESSION['lang']['belumbayar'] , '1' =>$_SESSION['lang']['dibayar']);
            $str1="select notransaksi from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and bulan='".substr($bar['tgl_transaksi'],5,2)."' and tahun='".substr($bar['tgl_transaksi'],0,4)."'";
            $res1=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $bar1=$res1->fetch();

            // $bunga=0;
            $angsuran=0;
            //Get angsuran dan bunga per angsuran
            $sisatenor=$barht['tenor']-1;
            $bunga=$barht['bunga_pertahun']/$sisatenor;
            $angsuran=$barht['angsuran']-$bunga;

            if ($bar['tenor_ke']==1) {
                $bar['no_bukucek']='Transfer';
                $bar['statuskasbank']=1;
                // $bunga=0;
            }

            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".tanggalnormal($bar['tgl_transaksi'])."</td>
                <td>".$bar['no_bukucek']."</td>
                <td align=center>".$bar['tenor_ke']."</td>
                <td align=right>".number_format($angsuran,2)."</td>
                <td align=right>".number_format($bunga,2)."</td>
                <td align=right>".number_format($barht['angsuran'],2)."</td>
                <td align=right>".$statbyr[$bar['statuskasbank']]."</td>
                <td align=right>".$bar1['notransaksi']."</td>";
            $tab.="</tr>";
        }

        $tab.="</tbody>";
        $tab.="</table></fieldset></td></tr></table>";

        echo $tab;

    break;

    case 'makepdfx':
        $strtenor="select count(*) as sisatenor from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."'  ";
        $restenor=$owlPDO->query($strtenor) or die(print " Gagal: " . PDOException::getMessage());
        $restenor->setFetchMode(PDO::FETCH_ASSOC);
        $bartenor=$restenor->fetch();

        
        $strht="select * from ".$dbname.".keu_leasinght where notransaksi='".$notransaksi."'";
        $resht=$owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht=$resht->fetch();
        $supplierid_leasing=$barht['supplierid_leasing'];
        $lessor=$barht['supplierid_leasing'];
        $notadebet=$barht['notadebet'];
        $norekening_bank=$barht['norekening_bank'];

        $strnd="select * from ".$dbname.".keu_notadebet_vw where notadebet='".$notadebet."'";
        $resnd=$owlPDO->query($strnd) or die(print " Gagal: " . PDOException::getMessage());
        $resnd->setFetchMode(PDO::FETCH_ASSOC);
        $barnd=$resnd->fetch();

        $noinvoice=$barnd['noinvoice_referensi'];
        $vendor=$barnd['kodesupplierdt'];

        $strth="select nopo from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
        $resth=$owlPDO->query($strth) or die(print " Gagal: " . PDOException::getMessage());
        $resth->setFetchMode(PDO::FETCH_ASSOC);
        $barth=$resth->fetch();

        $nopo=$barth['nopo'];

        $strpo="select * from ".$dbname.".log_po_vw where nopo='".$nopo."'";
        $respo=$owlPDO->query($strpo) or die(print " Gagal: " . PDOException::getMessage());
        $respo->setFetchMode(PDO::FETCH_ASSOC);
        $barpo=$respo->fetch();

        $optsupx=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$lessor."' or supplierid='".$vendor."'");

        $tab.="<table cellpading=1 cellspacing=0 border=0 >";
        $tab.="<tr align=left><td> <font size='10pt'><b>Lessor</b></font> </td>";
        $tab.="<td> <font size='10pt'><b>:</font> </td>";
        $tab.="<td> <font size='10pt'><b>".$optsupx[$lessor]."</b></font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'><b>Vendor</b></font> </td>";
        $tab.="<td> <font size='10pt'><b>:</font> </td>";
        $tab.="<td> <font size='10pt'><b>".$optsupx[$vendor]."</b></font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'><b>PO.".$nopo."</b></font> </td>";
        $tab.="<td> <font size='10pt'><b>:</b></font> </td>";
        $tab.="<td> <font size='10pt'><b>".$barpo['jumlahpesan']." ".$barpo['unit']." ".$barpo['namabarang']."</b></font> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";


        $tab.="<table cellpading=1 cellspacing=0 border=1 width=100% >";
        $tab.="<tr align=left><td> <font size='10pt'>Nomor Kontrak Vendor</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".$barht['nokontrak_leasing']."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Nama Asuransi</font> </td>";
        $tab.="<td align=left> <font size='10pt' align=left>".$barht['supplierid_asuransi']."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Nomor Kontrak Asuransi</font> </td>";
        $tab.="<td align=left> <font size='10pt' align=left>".$barht['nokontrak_asuransi']."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Tingkat Suku Bunga (per Tahun)</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".number_format($barht['suku_bungapertahun'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Denda Keterlambatan</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".number_format($barht['denda_terlambatan'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Jumlah Angsuran</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".($barht['tenor'])."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Tanggal Efektif</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".tanggalnormal($barht['tgl_efektif'])."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td> <font size='10pt'>Tanggal Pelunasan</font> </td>";
        $tab.="<td align=right> <font size='10pt' align=right>".tanggalnormal($barht['tgl_pelunasan'])."</font> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";


        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100%>";
        $tab.="<tr align=left><td   style='border:1px solid black;'> <font size='10pt'>Rincian Harga </font> </td>";
        $tab.="<td style='border:1px solid black;width:50px;' align=right> <font size='10pt'>DPP</font> </td>";
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barpo['subtotal'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'> <font size='10pt'></font> </td>";
        $tab.="<td style='border:1px solid black;width:50px;' align=right> <font size='10pt'>PPN</font> </td>";
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barpo['ppn'],2)."</font> </td>";
        $tab.="</tr>";
        $totalharga=$barpo['subtotal']+$barpo['ppn'];
        $tab.="<tr align=left><td colspan=2 style='border:0;'> <font size='10pt'><b>Total Harga </b></font> </td>";
        $tab.="<td  style='border:0;' align=right> <font size='10pt'><b>".number_format($totalharga,2)."</b></font> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";

        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100%>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Uang Muka</font> </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['uang_muka'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Administrasi</font> </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['administrasi'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Fidusia</font> </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['fidusia'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Survey</font> </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['survey'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Provisi</font> </td>";
       
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['provisi'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Notaris</font> </td>";
       
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['notaris'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Asuransi</font> </td>";
       
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['asuransi'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Angsuran Ke-1</font> </td>";
     
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['angsuran'],2)."</font> </td>";
        $tab.="</tr>";

        $totaluangmuka=$barht['administrasi']+$barht['fidusia']+$barht['survey']+$barht['provisi']+$barht['notaris']+$barht['asuransi']+$barht['angsuran']+$barht['uang_muka'];
        $tab.="<tr align=left><td style='border:0;'> <font size='10pt'><b>Total Pembayaran dimuka </b></font> </td>";
        $tab.="<td  style='border:0;' align=right> <font size='10pt'><b>".number_format($totaluangmuka,2)."</b></font> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";

        $tab.="<table cellpading=1 cellspacing=0 border=0 width=100%>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Nilai Kredit</font> </td>";
      
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['utang_pokok'],2)."</font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:1px solid black;'><font size='10pt'>Nilai Bunga</font> </td>";
        
        $tab.="<td style='border:1px solid black;' align=right> <font size='10pt'>".number_format($barht['bunga_pertahun'],2)."</font> </td>";
        $tab.="</tr>";

        //$tenor=$barht['tenor']-1;
        $totalnilai=$barht['utang_pokok']+$barht['bunga_pertahun'];
        $tab.="<tr align=left><td style='border:0;'> <font size='10pt'><b>Total Kredit </b></font> </td>";
        $tab.="<td  style='border:0;' align=right> <font size='10pt'><b>".number_format($totalnilai,2)."</b></font> </td>";
        $tab.="</tr>";
        $tab.="<tr align=left><td style='border:0;'> <font size='10pt'><b>Sisa Tenor : ".($bartenor['sisatenor']-1)."</b></font> </td>";
        $tab.="<td  style='border:0;' align=right> <font size='10pt'><b>".number_format($totalnilai/$barht['tenor'],2)."</b></font> </td>";
        $tab.="</tr>";
        $tab.="</table> <br><br>";

        $tab.="<table cellpading=1 cellspacing=0 border=1 >";
        $tab.="<thead>";
        $tab.="<tr align=center><td> <font size='10pt'>".$_SESSION['lang']['nourut']." </font> </font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['tanggalangsuran']."</font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['nourut']." ".$barht['metode_bayar']."</font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['tenor']."</font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['angsuran']."</font> </td>";
        $tab.="<td> <font size='10pt'>Bunga</font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['pembayaran']." ".$_SESSION['lang']['pokok']."</font> </td>";
        $tab.="<td> <font size='10pt'>Status Bayar</font> </td>";
        $tab.="<td> <font size='10pt'>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['kasbank']."</font> </td>";
        $tab.="</tr></thead><tbody >";

        $no=0;

        $str="select * from ".$dbname.".keu_leasingdt where notransaksi='".$notransaksi."' order by tenor_ke ";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        
        while ($bar=$res->fetch()) {
            $no+=1;

            $statbyr = array('0' =>$_SESSION['lang']['belumbayar'] , '1' =>$_SESSION['lang']['dibayar']);
            $str1="select notransaksi from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and bulan='".substr($bar['tgl_transaksi'],5,2)."' and tahun='".substr($bar['tgl_transaksi'],0,4)."'";
            $res1=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $bar1=$res1->fetch();

            $bunga=0;
            $angsuran=0;
            //Get angsuran dan bunga per angsuran
            //$bunga=$barht['bunga_pertahun']/$barht['tenor'];
            $sisatenor=$barht['tenor']-1;
            $bunga=$barht['bunga_pertahun']/$sisatenor;
            $angsuran=$barht['angsuran']-$bunga;

            if ($bar['tenor_ke']==1) {
                $bar['no_bukucek']='Transfer';
                $bar['statuskasbank']=1;
                $bunga=0;
            }

            $tab.="<tr >
                <td style='text-align:center;'>".$no."</font> </td>
                <td width=65px> <font size='10pt'>".tanggalnormal($bar['tgl_transaksi'])."</font> </td>
                <td align=center width=70px> <font size='10pt'>".$bar['no_bukucek']."</font> </td>
                <td align=center> <font size='10pt'>".$bar['tenor_ke']."</font> </td>
                <td align=right> <font size='10pt'>".number_format($angsuran,2)."</font> </td>
                <td align=right> <font size='10pt'>".number_format($bunga,2)."</font> </td>
                <td align=right> <font size='10pt'>".number_format($barht['angsuran'],2)."</font> </td>
                <td align=center> <font size='10pt'>".$statbyr[$bar['statuskasbank']]."</font> </td>
                <td align=center> <font size='10pt'>".$bar1['notransaksi']."</font> </td>";
            $tab.="</tr>";
        }

        $tab.="</tbody>";
        $tab.="</table>";
        //echo $tab;
            $dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));

    break;

    // case 'updatedt':

    //     $str="update ".$dbname.".keu_depositodt set tglcair='".$tglcair."',tglterima='".$tglterima."',jumlahbunga='".$jumlahbunga."',jumlahpajak='".$jumlahpajak."',jumlahpenalti='".$jumlahpenalti."',realisasi='".$realisasi."' where notransaksi='".$notransaksi."' and notranskasbank='".$notranskasbank."'";
    //     try{
    //         $owlPDO->exec($str); 
    //     }catch(PDOException $e){
    //         echo " Gagal," . addslashes($e->getMessage());
    //     }

    // break;

    // case'deletedt':

    //     $strdt="delete from ".$dbname.".keu_depositodt where notransaksi='".$notransaksi."' and notranskasbank='".$notranskasbank."'";
    //     try {
    //         $owlPDO->exec($strdt);
    //     } catch (PDOException $e) {
    //         print " Gagal: " . $e->getMessage() . "\n";
    //         die();
    //     }
    // break;

    // case 'postingdt':

    //     $strht="update ".$dbname.".keu_depositodt set posting='1' where notransaksi='".$notransaksi."' and notranskasbank='".$notranskasbank."'";             
    //     try
    //     {
    //         $owlPDO->exec($strht);
    //     }
    //     catch (PDOException $e)
    //     {
    //         print " Gagal  !: " . $e->getMessage() . "\n";
    //         die();
    //     }

    // break;
	
	default:
		# code...
	break;
}


?>