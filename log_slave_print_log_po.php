<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');


$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

//=============

//create Header
class PDF extends FPDF {
    function Header() {
        global $conn;
        global $dbname;
        global $userid;
        global $posted;
        global $tanggal;
        global $norek_sup;
        global $npwp_sup;
        global $nm_kary;
        global $nm_pt;
        global $nmSupplier;
        global $almtSupplier;
        global $tlpSupplier;
        global $faxSupplier;
        global $nopo;
        global $tglPo;
        global $kdBank;
        global $an;
        global $optNmkry;
        global $kota;
        global $cp;
        $optNmkry=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		
        $str="select kodeorg,kodesupplier,purchaser,nopo,tanggal from ".$dbname.".log_poht  where nopo='".$_GET['column']."'";
        //echo $str;exit();
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
		
        //ambil nama pt
        if($bar->kodeorg=='')
        {
            $bar->kodeorg=$_SESSION['org']['kodeorganisasi']; 
        }
        $str1="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch())
        {
           
            if((substr($bar1->namaorganisasi,0,8)=='KOPERASI')||(substr($bar1->namaorganisasi,0,8)=='koperasi'))
            {
                $scek="select distinct regional from ".$dbname.".bgt_regional_assignment 
                       where kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar->kodeorg."')";
                //exit("Error:".$scek);
                $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $qcek->setFetchMode(PDO::FETCH_OBJ);
                $rcek=$qcek->fetch();
                if($rcek['regional']=='KALTIM')
                {
                    $bar->kodeorg='DPA';
                }
                if($rcek['regional']=='SUMSEL'||$rcek['regional']=='LAMPUNG')
                {
                    $bar->kodeorg='PMO';
                }
                $str1="select namaorganisasi,alamat,wilayahkota,telepon from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_OBJ);
                $bar1=$res1->fetch();
            }
            $namapt=$bar1->namaorganisasi;
            $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
            $telp=$bar1->telepon;				 
        } 
        $sNpwp="select npwp,alamatnpwp from ".$dbname.".setup_org_npwp where kodeorg='".$bar->kodeorg."'";
        $qNpwp=$owlPDO->query($sNpwp) or die(print " Gagal: ".PDOException::getMessage());
        $qNpwp->setFetchMode(PDO::FETCH_ASSOC);
        $rNpwp=$qNpwp->fetch();
        

        $sql="select * from ".$dbname.".log_5supplier where supplierid='".$bar->kodesupplier."'"; //echo $sql;
        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_OBJ);
        $res=$query->fetch();

        $sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->purchaser."'";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        $res2=$query2->fetch();


        $sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
        $query3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
        $query3->setFetchMode(PDO::FETCH_OBJ);
        $res3=$query3->fetch();
        
        $norek_sup=	isset($res->rekening)? $res->rekening: '';
        $kdBank=	isset($res->bank)? $res->bank: '';
        $npwp_sup=	isset($res->npwp)? $res->npwp: '';
        $an=		isset($res->an)? $res->an: '';
        $nm_kary=	isset($res2->namakaryawan)? $res2->namakaryawan: '';
        $nm_pt=		isset($res3->namaorganisasi)? $res3->namaorganisasi: '';
        //data PO
        $nopo=		isset($bar->nopo)? $bar->nopo: '';
        $tglPo=		isset($bar->tanggal)? tanggalnormal($bar->tanggal): '';
        //data supplier
        $nmSupplier=	isset($res->namasupplier)? $res->namasupplier: '';
        $almtSupplier=	isset($res->alamat)? $res->alamat: '';
        $tlpSupplier=	isset($res->telepon)? $res->telepon: '';
        $faxSupplier=	isset($res->fax)? $res->fax: '';
        $kota=			isset($res->kota)? $res->kota: '';
        $cp=			isset($res->kontakperson)? $res->kontakperson: '';
		
        $this->SetMargins(15,10,0);
        $path='images/logo.jpg';
        $this->Image($path,15,5,0,30);	
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(255,255,255);	
        $this->SetX(55);   
        $this->Cell(60,5,$namapt,0,1,'L');	 
        $this->SetX(55); 		
        $this->Cell(60,5,$alamatpt,0,1,'L');	
        $this->SetX(55); 			
        $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
        $this->SetFont('Arial','B',7);
        $this->SetX(55); 			
        $this->Cell(60,5,"NPWP: ".$rNpwp['npwp'],0,1,'L');	
        $this->SetX(55); 			
        $this->Cell(60,5,$_SESSION['lang']['alamat']." NPWP: ".$rNpwp['alamatnpwp'],0,1,'L');	
        $this->SetFont('Arial','B',9);
        $this->Line(15,35,205,35);	
        $this->SetX(155);
        $this->Cell(30,10,"No. PO: ".$nopo,0,1,'L');
    }

    function Footer()
    {
		global $nopo;
		
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        $this->SetFont('Arial','',6); 	
        //$this->SetY(27);
        $this->SetX(163);
        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');
    }

}

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();

// kepada yth
$pdf->SetFont('Arial','B',8);	
if($_SESSION['language']=='EN'){
    $pdf->Cell(30,4,"TO :",0,0,'L');     
}else{                
    $pdf->Cell(30,4,"KEPADA YTH :",0,0,'L'); 
}

$pdf->Ln();
$arte="";
 
$pdf->Cell(35,4,$_SESSION['lang']['nm_perusahaan'],0,0,'L'); 
$pdf->Cell(40,4,": ".$nmSupplier.$arte,0,1,'L'); 	
if($cp!='')
{
    $pdf->Cell(35,4,$_SESSION['lang']['cperson'],0,0,'L'); 
    $pdf->Cell(40,4,": ".$cp,0,1,'L'); 
}
$pdf->Cell(35,4,$_SESSION['lang']['alamat'],0,0,'L'); 
$pdf->Cell(40,4,": ".$almtSupplier,0,1,'L'); 		  
$pdf->Cell(35,4,$_SESSION['lang']['telp'],0,0,'L'); 
$pdf->Cell(40,4,": ".$tlpSupplier,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['fax'],0,0,'L'); 
$pdf->Cell(40,4,": ".$faxSupplier,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['namabank'],0,0,'L'); 
$pdf->Cell(40,4,": ".$kdBank." ".$kdBank,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['norekeningbank'],0,0,'L'); 
$pdf->Cell(40,4,": ".$an." ".$norek_sup,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['npwp'],0,0,'L'); 
$pdf->Cell(40,4,": ".$npwp_sup,0,1,'L'); 
$pdf->Cell(35,4,$_SESSION['lang']['kota'],0,0,'L'); 
$pdf->Cell(40,4,": ".$kota,0,1,'L'); 

//title
$pdf->SetFont('Arial','U',12);
$ar=round($pdf->GetY());
$pdf->SetY($ar+5);
$pdf->Cell(190,5,strtoupper("Purchase Order"),0,1,'C');		
$pdf->SetY($ar+12);

//no po + tanggal po
$pdf->SetFont('Arial','',8);		
$pdf->Cell(10,4,"",0,0,'L'); 
$pdf->Cell(20,4," ",0,0,'L'); 
$pdf->SetX(163);
$pdf->Cell(20,4,$_SESSION['lang']['tanggal'],0,0,'L'); 
$pdf->Cell(20,4,": ".$tglPo,0,0,'L'); 
$pdf->SetY($ar+17);

//title
$pdf->SetFont('Arial','B',8);	
$pdf->SetFillColor(220,220,220);
$pdf->Cell(8,5,'No',1,0,'L',1);
$pdf->Cell(12,5,$_SESSION['lang']['kodeabs'],1,0,'C',1);	
$pdf->Cell(60,5,$_SESSION['lang']['namabarang'],1,0,'C',1);
$pdf->Cell(12,5,$_SESSION['lang']['nopp'],1,0,'C',1);	
$pdf->Cell(12,5,$_SESSION['lang']['untukunit'],1,0,'C',1);		
$pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
$pdf->Cell(14,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
$pdf->Cell(29,5,$_SESSION['lang']['hargasatuan'],1,0,'C',1);
$pdf->Cell(26,5,'Total',1,1,'C',1);

$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',8);
		
$str="select a.*,b.kodesupplier,b.subtotal,b.diskonpersen,b.tanggal,b.nilaidiskon,b.ppn,b.nilaipo,b.tanggalkirim,b.lokasipengiriman,b.uraian,b.matauang from ".$dbname.".log_podt a inner join ".$dbname.".log_poht b on a.nopo=b.nopo  where a.nopo='".$_GET['column']."'";
$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$re->setFetchMode(PDO::FETCH_OBJ);
$no=0;$i=0;
while($bar=$re->fetch()){
    $no+=1;

    $kodebarang=$bar->kodebarang;
    $jumlah=floatval($bar->jumlahpesan);
    $harga_sat=$bar->hargasbldiskon;
    $total=$jumlah*$harga_sat;
    $unit=substr($bar->nopp,15,4);
    $namabarang='';
    $nopp=substr($bar->nopp,0,3);
    $strv="select b.spesifikasi from  ".$dbname.".log_5photobarang b  where b.kodebarang='".$bar->kodebarang."'"; //echo $strv;exit();	
    $resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
    $resv->setFetchMode(PDO::FETCH_OBJ);
    $barv=$resv->fetch();

    if(!empty($barv->spesifikasi))
    {
        $spek=$barv->spesifikasi."\n";
    }
    else
    {
        $spek="";
    }

    $sSat="select satuan,namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
    $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
    $qSat->setFetchMode(PDO::FETCH_ASSOC);
    $rSat=$qSat->fetch();
    $satuan=$rSat['satuan'];
    $namabarang=$rSat['namabarang'];

    $i++;

    if($no!=1)
    {
        $pdf->SetY($akhirY);
    }
    $akhirY=$pdf->GetY();
    if($akhirY>=260){
        $pdf->AddPage();
        $akhirY=$pdf->GetY();;
    }    
    //no
    $pdf->Cell(8,4,$no,0,0,'L',0);
    $pdf->SetX($pdf->GetX());
    $posisiY=round($pdf->GetY());
    
    $pdf->Cell(12,5,substr($bar->kodebarang,0,3),0,0,'C',0);
    //nama barang
    $pdf->MultiCell(60,5," ".$namabarang."\n".$spek.$bar->catatan,0,'J',0);
    $akhirY=$pdf->GetY();

    //naik lagi kursornya
    $pdf->SetY($posisiY);
    $pdf->SetX($pdf->GetX()+82);

    //no pp + pt + jumlah + satuan + harga + total
    $pdf->Cell(12,5,$nopp,0,0,'C',0);
    $pdf->Cell(12,5,$unit,0,0,'C',0);
    $pdf->Cell(14,5,number_format($jumlah,2,'.',','),0,0,'R',0);
    $pdf->Cell(14,5,$bar->satuan,0,0,'C',0);
    if($bar->matauang=='IDR'){
        $pdf->Cell(29,5,$bar->matauang." ".number_format($harga_sat,2,'.',','),0,0,'R',0);
        $pdf->Cell(26,5,number_format($total,2,'.',','),0,1,'R',0);
    }
    else{
        $pdf->Cell(29,5,$bar->matauang." ".number_format($harga_sat,3,'.',','),0,0,'R',0);
        $pdf->Cell(26,5,number_format($total,3,'.',','),0,1,'R',0);
    }    	
    $desiomal=2;

    
////    if($i==15)
//    {
//        $i=0;
//        $akhirY=$akhirY-20;
//        $akhirY=$pdf->GetY()-$akhirY;
//        $akhirY=$akhirY+35;
//        //$pdf->SetY($posisiY+25);
////        $pdf->AddPage();
//    }
}
$akhirSubtot=$pdf->GetY();
$pdf->SetY($akhirY);
$slopoht="select * from ".$dbname.".log_poht where nopo='".$_GET['column']."'";
$qlopoht=$owlPDO->query($slopoht) or die(print " Gagal: ".PDOException::getMessage());
$qlopoht->setFetchMode(PDO::FETCH_OBJ);
$rlopoht=$qlopoht->fetch();
$sb_tot=$rlopoht->subtotal;
$nil_diskon=$rlopoht->nilaidiskon;
$npbbkb=$rlopoht->pbbkb;
$npph=$rlopoht->pph;
$nppn=$rlopoht->ppn;
$stat_release=$rlopoht->stat_release ;
$user_release=$rlopoht->useridreleasae;
$gr_total=(($sb_tot-$nil_diskon)+$npbbkb)+$nppn-$npph;

$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$rlopoht->syaratbayar."'";
$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
$qSyp->setFetchMode(PDO::FETCH_OBJ);
$rSyp=$qSyp->fetch();

                	
if($akhirSubtot>=240){
    $pdf->AddPage();
    $akhirY=$pdf->GetY();
}
$pdf->MultiCell(134,4, $_SESSION['lang']['keterangan'].":"."\n".$rlopoht->uraian,'T',1,'J',0);
$pdf->SetY($akhirY);
$pdf->SetX($pdf->GetX()+134);
$pdf->Cell(29,5,$_SESSION['lang']['subtotal'],'T',0,'L',1);
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,number_format($rlopoht->subtotal,2,'.',','),'T',1,'R',1);
}
else{
    $pdf->Cell(26,5,number_format($rlopoht->subtotal,3,'.',','),'T',1,'R',1);
}

$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+134);
$pdf->Cell(29,5,'Diskon',0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,number_format($rlopoht->nilaidiskon,$desiomal,'.',','),0,1,'R',1);
}
else{	
    $pdf->Cell(26,5,number_format($rlopoht->nilaidiskon,3,'.',','),0,1,'R',1);
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+134);
$pdf->Cell(29,5,'PBBKB',0,0,'L',1);
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,number_format($rlopoht->pbbkb,$desiomal,'.',','),0,1,'R',1);
}
else{
    $pdf->Cell(26,5,number_format($rlopoht->pbbkb,3,'.',','),0,1,'R',1);    
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+134);
$pdf->Cell(29,5,'PPn',0,0,'L',1);
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,number_format($rlopoht->ppn,$desiomal,'.',','),0,1,'R',1);
}
else{
    $pdf->Cell(26,5,number_format($rlopoht->ppn,3,'.',','),0,1,'R',1);    
}
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+134);
$pdf->Cell(29,5,'PPh',0,0,'L',1);
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,number_format($rlopoht->pph,$desiomal,'.',','),0,1,'R',1);
}
else{
    $pdf->Cell(26,5,number_format($rlopoht->pph,3,'.',','),0,1,'R',1);    
}
$pdf->SetFont('Arial','B',8);
$pdf->SetY($pdf->GetY());
$pdf->SetX($pdf->GetX()+134);

$pdf->Cell(29,5,$_SESSION['lang']['grnd_total'],0,0,'L',1);	
if($rlopoht->matauang=='IDR'){
    $pdf->Cell(26,5,$rlopoht->matauang." ".number_format($gr_total,$desiomal,'.',','),0,1,'R',1);	
}
else{
    $pdf->Cell(26,5,$rlopoht->matauang." ".number_format($gr_total,3,'.',','),0,1,'R',1);
}
if(strlen($rlopoht->uraian)>350)//616
{
    $tmbhBrs=70;                // jul 12, 2013 diganti 80 -> 70
    $tmbhBrs2=95;              // jul 12, 2013 diganti 105 -> 95
    $tmbhBrs3=65;               // jul 12, 2013 diganti 75 -> 65
    $tmbhBrs5=125;              // jul 12, 2013 diganti 135 -> 125
}
else
{
    $tmbhBrs=35;                // jul 12, 2013 diganti 45 -> 35
    $tmbhBrs2=55;               // jul 12, 2013 diganti 65 -> 55
    $tmbhBrs3=45;               // jul 12, 2013 diganti 55 -> 45
    $tmbhBrs5=85;               // jul 12, 2013 diganti 95 -> 85
}
# kalo terlalu ke bawah, pindah halaman aja                
if(($akhirY+$tmbhBrs5)>=275){   // tadinya if($akhirY)>=175)
    $akhirY=0;                  // jul 12, 2013 diganti lagi 250 -> 300
    $pdf->AddPage();
}
# $dz

$pdf->SetY($akhirY+$tmbhBrs);
$pdf->SetFont('Arial','',8);
$pdf->Cell(35,4,$_SESSION['lang']['syaratPem'],0,0,'L'); 
$pdf->Cell(40,4,": ".$rSyp->keterangan." (".$rSyp->jenis.")",0,1,'L'); 

// $pdf->Cell(35,4,$_SESSION['lang']['tgl_kirim'],0,0,'L'); 
// $pdf->Cell(40,4,": ".tanggalnormald($rlopoht->tanggalkirim),0,1,'L'); 		

if((is_null($rlopoht->idFranco))||($rlopoht->idFranco=='')||($rlopoht->idFranco==0))
{
    $pdf->Cell(35,4,$_SESSION['lang']['almt_kirim'],0,0,'L'); 
    $pdf->Cell(40,4,": ".$rlopoht->lokasipengiriman,0,1,'L'); 		
}
else
{
    $sFr="select * from ".$dbname.".setup_franco where id_franco='".$rlopoht->idFranco."'";
    $qFr=$owlPDO->query($sFr) or die(print " Gagal: ".PDOException::getMessage());
    $qFr->setFetchMode(PDO::FETCH_ASSOC);
    $rFr=$qFr->fetch();

    $pdf->Cell(35,4,$_SESSION['lang']['almt_kirim'],0,0,'L'); 
    $pdf->Cell(40,4,": ".$rFr['alamat'],0,1,'L'); 		
    $pdf->Cell(35,4,"Kontak Person",0,0,'L'); 
    $pdf->Cell(40,4,": ".$rFr['contact'],0,1,'L'); 	
    $pdf->Cell(35,4,"Telp / Handphone No.",0,0,'L'); 
    $pdf->Cell(40,4,": ".$rFr['handphone'],0,1,'L'); 	
}

$sPo="select persetujuan1,persetujuan2 from ".$dbname.".log_poht where nopo='".$nopo."'";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
$rPo=$qPo->fetch();


$pdf->SetY($akhirY+$tmbhBrs2);
$pdf->Cell(185,4,$nm_pt,0,0,'R'); 

$pdf->SetY($akhirY+$tmbhBrs2);
$pdf->Ln();

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,$_SESSION['lang']['dbuat_oleh'],1,0,'C',1);	
$pdf->Cell(40,5,$_SESSION['lang']['mengetahui'],1,0,'C',1);
$pdf->Cell(40,5,$_SESSION['lang']['menyetujui'],1,1,'C',1);

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,'',1,0,'C',1);	
$pdf->Cell(40,5,'',1,0,'C',1);
$pdf->Cell(40,5,'',1,1,'C',1);

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);
$pdf->Cell(40,5,'','LR',1,'C',1);

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);
$pdf->Cell(40,5,'','LR',1,'C',1);

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);	
$pdf->Cell(40,5,'','LR',0,'C',1);
$pdf->Cell(40,5,'','LR',1,'C',1);

$pdf->SetFont('Arial','B',8);	
$pdf->Cell(65,5,'',0,0,'C',1);	
$pdf->Cell(40,5,strtoupper($nm_kary),1,0,'C',1);	
$pdf->Cell(40,5,isset($optNmkry[$rPo['persetujuan1']])? strtoupper($optNmkry[$rPo['persetujuan1']]): '',1,0,'C',1);
$pdf->Cell(40,5,isset($optNmkry[$rPo['persetujuan2']])? strtoupper($optNmkry[$rPo['persetujuan2']]): '',1,1,'C',1);

$akrhr=$tmbhBrs5+10;
$pdf->SetY($akhirY+$akrhr);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,4,strtoupper($_SESSION['lang']['fyiGudang2']),0,0,'L',0);

$pdf->Output();
?>
