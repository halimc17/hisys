<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/terbilang.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$tglInv = "";
$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$nmPt=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//=============

//create Header
class PDF extends FPDF {
        function Header() {
                global $conn;
                global $dbname;
                global $userid;
                global $column;
                global $optnmakun;
                global $optnmcust;
                global $bar;
                global $nmPt;
                global $tglInv;
                global $owlPDO;
                $test=explode(',',$_GET['column']);
                $notransaksi=$test[0];
                $kodevhc = count($test)>1? $test[1]: '';
                $str=$owlPDO->query("select * from ".$dbname.".".$_GET['table']."  where noinvoice='".$column."'");
                $str->setFetchMode(PDO::FETCH_OBJ);
                $bar=$str->fetch();
                $posting=$bar->posting;	
                $pt=$bar->kodept;
                $tglInv = $bar->tanggal;


                // if($pt=='CKS')
                // {
                //     $path='images/logo_cks.jpg';  
                // }
                // else if($pt=='MHS')
                // {
                //     $path='images/logo_mhs.jpg';     
                // }
                // else if($pt=='SMA')
                // {
                //     $path='images/logo_sma.jpg';     
                // }
                //                 else if($pt=='MEA')
                // {
                //     $path='images/logo_mea.jpg';     
                // }
                // else
                // {
                //     $path='images/logo.jpg';  
                // }

                $arrHead = setheadreport($bar->kodeorg);
                $path=$arrHead['logo'];
                $this->Image($path,15,5,20);

                //$path2='images/Quality_ISO_9001.jpg';
                //$this->Image($path2,160,5,40);

                $this->Ln(20);
                $this->SetFont('Arial','B',8);
                // $this->Cell(60,5,$nmPt[$bar->kodept],0,1,'L');
                $this->Cell(60,5,$arrHead['nama'],0,1,'L');

                //logo_sma.png


                /*$this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);	
                $this->SetX(55);   
                 $this->Cell(60,5,$namapt,0,1,'L');	 
                $this->SetX(55); 		
                $this->Cell(60,5,$alamatpt,0,1,'L');	
                $this->SetX(55); 			
                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                */

                $this->Ln();
                $this->SetFont('Arial','B',12);
                $this->SetY(35);
                $this->Cell(190,5,'INVOICE',0,1,'C');	

                $this->SetFont('Arial','',6); 
                $this->SetY(27);
                $this->SetX(163);
       // $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
                //$this->Line(10,27,200,27);	
                $this->Ln(20);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            //$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
}

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();


$height='5';

$iCust=$owlPDO->query("select * from ".$dbname.".pmn_4customer where kodecustomer='".$bar->kodecustomer."' ");
$iCust->setFetchMode(PDO::FETCH_ASSOC);
$dCust=$iCust->fetch();

$pdf->SetFont('Arial','',8); 
$pdf->Ln();
$awalYKop=$pdf->GetY();
$pdf->Cell(80,$height,$dCust['namacustomer'],'TLR',1,'L'); 

//echo $akhirYKop;
$pdf->MultiCell(80, $height,$dCust['alamat'], 'RL', 'L');
$pdf->Cell(80,$height,$dCust['telepon'],'LR',1,'L'); 
$pdf->Cell(80,$height,'Attn. Finance Dept.','BLR',1,'L'); 

$akhirYKop=$pdf->GetY();

$selisihYKop=$akhirYKop-$awalYKop;

$heightKopKanan=$selisihYKop/3;

$pdf->SetXY(115, $awalYKop);
$pdf->Cell(30,$heightKopKanan,'No. Invoice',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,$bar->noinvoice,0,1,'L');  

$pdf->SetX(115);
$pdf->Cell(30,$heightKopKanan,'Tanggal',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,tanggalnormal($bar->tanggal),0,1,'L');    

$pdf->SetX(115);
$pdf->Cell(30,$heightKopKanan,'No. Kontrak',0,0,'L'); 
$pdf->Cell(5,$heightKopKanan,':',0,0,'L');
$pdf->Cell(30,$heightKopKanan,$bar->nokontrak,0,1,'L'); 

$pdf->Ln(20);

$pdf->Cell(15,$height,'No',1,0,'C'); 
$pdf->Cell(125,$height,'Deskripsi',1,0,'C'); 
$pdf->Cell(50,$height,'Jumlah (Rp)',1,1,'C'); 



$iKontrak=$owlPDO->query(" select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$bar->nokontrak."' ");
$iKontrak->setFetchMode(PDO::FETCH_ASSOC);
$dKontrak=  $iKontrak->fetch();

$namaKomoditi= makeOption($dbname,'log_5masterbarang', 'kodebarang,namabarang','kelompokbarang=400');
$simbolMataUang=makeOption($dbname,'setup_matauang', 'kode,simbol');

$iTermin=$owlPDO->query("select * from ".$dbname.".pmn_5terminbayar where kode='".$dKontrak['kdtermin']."' ");
$iTermin->setFetchMode(PDO::FETCH_ASSOC);
$dTermin=  $iTermin->fetch();
    $terminSatu=$dTermin['satu'];

$sPem=$owlPDO->query("select * from ".$dbname.".keu_penagihanht where tanggal<'".$tglInv."' and nokontrak='".$bar->nokontrak."'");
$sPem->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($sPem);
if($numrows>0){
        $termin=$dTermin['dua'];
}else{
        $termin=$dTermin['satu'];
}

if($dKontrak['ppn']==0)
{  
    $rpPpn="";
    $dKontrak['hargasatuan']=$dKontrak['hargasatuan'];
}
else
{ 
    $dKontrak['hargasatuan']=$dKontrak['hargasatuan']/1.1;
}

if($dCust['statusberikat']==0)
{

    $rpPpn=$bar->nilaippn;
    $isiPpn="     PPN 10%";
}
else
{
    $isiPpn="     PPN 10%";
    $rpPpn="0";
    $isiBerikat="     ".$dCust['keteranganberikat'];
}

$totalKontrak=$dKontrak['hargasatuan']*$dKontrak['kuantitaskontrak'];

$persenpembayaran=$totalKontrak>0?$bar->nilaiinvoice/$totalKontrak*100:0;

$a=$termin;
$b=$dKontrak['kuantitaskontrak'];

$c=round($dKontrak['hargasatuan'],2);

$totalHarga=$bar->nilaiinvoice;


$iBnyk=$owlPDO->query("select nilaiinvoice,kuantitas from ".$dbname.".keu_penagihanht where nokontrak='".$bar->nokontrak."' and noinvoice='".$bar->noinvoice."' ");
$iBnyk->setFetchMode(PDO::FETCH_ASSOC);
$dBnyk= $iBnyk->fetch();
$totalInv=$dBnyk['nilaiinvoice'];
$keterangan="Pembayaran (".$termin."%)";
$isideskripsi="     ".$keterangan."   untuk ".ucwords(strtolower($namaKomoditi[$bar->kodebarang]))."  ";
$isideskripsi.=" ".number_format($dBnyk['kuantitas'],2)." KG ";
$isideskripsi2="     @ ".$simbolMataUang[$dKontrak['matauang']]." ".number_format($dKontrak['hargasatuan'],2)." ";


$pdf->Cell(15,$height,'1','LR',0,'C'); 
$pdf->Cell(125,$height,$isideskripsi,'LR',0,'L'); 
$pdf->Cell(40,$height,  number_format($totalHarga,0),'L',0,'R'); 
$pdf->Cell(10,$height,'','R',1,'R');

$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,$isideskripsi2,'RL',0,'L'); 
$pdf->Cell(40,$height,'','L',0,'C'); 
$pdf->Cell(10,$height,'','R',1,'R');


$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,$isiPpn,'RL',0,'L'); 
$pdf->Cell(40,$height,  number_format($rpPpn),'L',0,'R'); 
$pdf->Cell(10,$height,'','R',1,'R');


@$pdf->Cell(15,$height,'','RL',0,'C'); 
@$pdf->Cell(125,$height,$isiBerikat,'RL',0,'L'); 
@$pdf->Cell(40,$height,'','L',0,'L'); 
@$pdf->Cell(10,$height,'','R',1,'R');


$optFaktur=  makeOption($dbname, 'pmn_faktur', 'noinvoice,nofaktur');

$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'','RL',0,'L'); 
$pdf->Cell(40,$height,'','L',0,'R'); 
$pdf->Cell(10,$height,'','R',1,'R');

$subTot=$rpPpn+$totalHarga;


    $pdf->Cell(15,$height,'','RL',0,'C'); 
    $pdf->Cell(125,$height,'','RL',0,'L'); 
    $pdf->Cell(40,$height,'','L',0,'R'); 
    $pdf->Cell(10,$height,'','R',1,'R');

$pdf->SetFont('Arial','B',8); 

$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'Sub Total :     ','LR',0,'R'); 

if($bar->matauang=='IDR')
{
    $pdf->Cell(40,$height,number_format($subTot,0),'T',0,'R'); 
}
else
{
    $pdf->Cell(40,$height,number_format($subTot,2),'T',0,'R');    
}
$pdf->Cell(10,$height,'','RT',1,'R');

    $pdf->Cell(15,$height,'','RL',0,'C'); 
    $pdf->Cell(125,$height,'','RL',0,'L'); 
    $pdf->Cell(40,$height,'','L',0,'R'); 
    $pdf->Cell(10,$height,'','R',1,'R');


$totalKalimKurangV=$bar->rupiah1+$bar->rupiah2+$bar->rupiah3+$bar->rupiah4+$bar->rupiah5+$bar->rupiah6+$bar->rupiah7;
$totalKlaimTambah=$bar->rupiah8;

$totalKlaim=$totalKalimKurangV-$totalKlaimTambah;

$absKlaim=abs($totalKlaim);

if($absKlaim!=0)
{
$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(4.5,$height,'','L',0,'L'); 
$pdf->SetFont('Arial','BU',9);
$pdf->Cell(120.5,$height,'Klaim','R',0,'L'); 
$pdf->SetFont('Arial','',8); 
$pdf->Cell(40,$height,'','L',0,'R'); 
$pdf->Cell(10,$height,'','R',1,'R');
}

for($i=1;$i<=7;$i++)
{
    $rupiah="rupiah$i";
    $keterangan="keterangan$i";  

        if($bar->$rupiah!=0)
        {
            if($i<=6)
            {
                $pdf->Cell(15,$height,'','RL',0,'C'); 
                $pdf->Cell(125,$height,'     Klaim mutu '.$bar->$keterangan.' di atas standar','RL',0,'L'); 
                $pdf->Cell(40,$height,"(".number_format($bar->$rupiah).")",'L',0,'R'); 
                $pdf->Cell(10,$height,'','R',1,'R');
            }
            else
            {
                $pdf->Cell(15,$height,'','RL',0,'C'); 
                $pdf->Cell(125,$height,'     Klaim kesusutan antara timbangan pabrik dengan sounding kapal','RL',0,'L'); 
                $pdf->Cell(40,$height,number_format($bar->$rupiah),'L',0,'R'); 
                $pdf->Cell(10,$height,'','R',1,'R');
            }
    }
    @$totalKlaimPengurang+=$bar->$rupiah;
}

//echo $totalKlaim;
if($absKlaim!=0)
{
$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'     Klaim kelebihan timbangan','RL',0,'L'); 
$pdf->Cell(40,$height,number_format($bar->rupiah8),'L',0,'R'); 
$pdf->Cell(10,$height,'','R',1,'R');
}
$totalKlaim=$totalKlaimPengurang-$bar->rupiah8;
if($dKontrak['ppn']!=0){
    $ppnKlaim=10/100*$totalKlaim;
}
if($absKlaim!=0)
{
$txtTotal = ($totalKlaim<0)? number_format($totalKlaim*(-1)): "(".number_format($totalKlaim).")";
$pdf->SetFont('Arial','B',8); 
$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'                    Total Klaim :     ','LR',0,'L'); 
$pdf->Cell(40,$height,  $txtTotal,'T',0,'R'); 
$pdf->Cell(10,$height,'','RT',1,'R');
}

if($absKlaim!=0)
{
$txtPpn = ($ppnKlaim<0)? number_format($ppnKlaim*(-1)): "(".number_format($ppnKlaim).")";
$pdf->SetFont('Arial','B',8); 
$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'                    PPN Klaim(10%) :     ','LR',0,'L'); 
$pdf->Cell(40,$height,  $txtPpn,B,0,'R'); 
$pdf->Cell(10,$height,'',RB,1,'R');
}

@$subTotKlaim=$totalKlaim+$ppnKlaim;

if($absKlaim!=0)
{
$txtSub = ($subTotKlaim<0)? number_format($subTotKlaim*(-1)): "(".number_format($subTotKlaim).")";
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(15,$height,'','RL',0,'C'); 
$pdf->Cell(125,$height,'Sub Total :     ','LR',0,'R'); 
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(40,$height,$txtSub,'T',0,'R'); 
$pdf->Cell(10,$height,'','RT',1,'R');
}

$pdf->Cell(15,2*$height,'','RL',0,'C'); 
$pdf->Cell(125,2*$height,'','RL',0,'L'); 
$pdf->Cell(40,2*$height,'','LB',0,'R'); 
$pdf->Cell(10,2*$height,'','RB',1,'R');

$pdf->Cell(15,0.2*$height,'','RL',0,'C'); 
$pdf->Cell(125,0.2*$height,'','RL',0,'L'); 
$pdf->Cell(40,0.2*$height,'','LB',0,'R'); 
$pdf->Cell(10,0.2*$height,'','RB',1,'R');

$grandTotal=$subTot-$subTotKlaim;

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(15,1.5*$height,'','RLB',0,'C'); 
$pdf->Cell(125,1.5*$height,'Total :     ','LRB',0,'R'); 
$pdf->Cell(40,1.5*$height,number_format($grandTotal),'BL',0,'R'); 
$pdf->Cell(10,1.5*$height,'','RB',1,'R');

$pdf->Ln();
$pdf->SetFont('Arial','',8); 

$akhirYTerbilang=$pdf->GetY();

$pdf->Cell(30,$height,'Dalam huruf :',0,0,'L'); 

if($bar->matauang=='IDR')
{
$pdf->MultiCell(100, $height,  terbilang(round($grandTotal,0),'').' Rupiah',0,'J');
}
else
{
    $pdf->MultiCell(100, $height,  terbilang(round($grandTotal,2),'').' ',0,'J');
}
$pdf->SetFont('Arial','B',8); 
$pdf->SetXY(150, $akhirYTerbilang);
$pdf->Cell(30,$height,'Hormat Kami,',0,1,'L'); 

$pdf->SetX(150);
$pdf->Cell(50,2*$height,$nmPt[$bar->kodept],0,1,'C'); 


$iAkunBank=$owlPDO->query(" select * from ".$dbname.".keu_5akunbank where pemilik='".$bar->kodept."' ");
$iAkunBank->setFetchMode(PDO::FETCH_ASSOC);
$dAkunBank=  $iAkunBank->fetch();
$pdf->Cell(45,$height,'Mohon Ditransfer ke Acc.',0,0,'L'); 
$nmPtS=explode(".",$nmPt[$bar->kodept]);
@$nmPtBawah=$nmPtS[0]." ".ucwords(strtolower($nmPtS[1]));
$pdf->Cell(90,$height,$nmPtBawah.' Acc: '.$dAkunBank['rekening'],1,1,'C'); 
$pdf->SetFont('Arial','',8); 
$pdf->Cell(45,1.5*$height,'Pada :',0,0,'L'); 
$pdf->Cell(90,1.5*$height,$dAkunBank['namabank'],1,1,'C'); 
$nmTtd=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jbTtd=  makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');
$pdf->Ln();
$pdf->SetX(150);
$pdf->Cell(50,$height,$nmTtd[$bar->ttd],0,1,'C'); 
$pdf->SetX(150);
$pdf->SetFont('Arial','B',8); 
@$pdf->Cell(50,$height,$jbTtd[$bar->ttd],'T',1,'C'); 
$pdf->Output();
?>