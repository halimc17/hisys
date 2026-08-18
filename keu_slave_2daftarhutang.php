<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=$_GET['proses'];
$kodeOrg		= empty($_POST['kdOrg'])? (isset($_GET['kdOrg'])? $_GET['kdOrg']: '') : $_POST['kdOrg'];
$kodesupplier 	= empty($_POST['kdsup'])? (isset($_GET['kdsup'])? $_GET['kdsup']: '') : $_POST['kdsup'];//Penambahan Filter persupplier
$tgl1			= empty($_POST['tgl1'])? (isset($_GET['tgl1'])? tanggalsystem($_GET['tgl1']): '') : tanggalsystem($_POST['tgl1']);
$tgl2			= empty($_POST['tgl2'])? (isset($_GET['tgl2'])? tanggalsystem($_GET['tgl2']): '') : tanggalsystem($_POST['tgl2']);
$statTagihan	= empty($_POST['statTagihan'])? (isset($_GET['statTagihan'])? $_GET['statTagihan']: '') : $_POST['statTagihan'];
$periode		= empty($_POST['periode'])? (isset($_GET['periode'])? $_GET['periode']: '') : $_POST['periode'];
$periode2		= empty($_POST['periode2'])? (isset($_GET['periode2'])? $_GET['periode2']: '') : $_POST['periode2'];

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmSupp=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optMatauang=makeOption($dbname, 'log_poht', 'nopo,matauang');

if($periode==''||$periode2=='')
{
    exit("Error:Field Tidak Boleh Kosong");
}

if($_GET['proses']=='excel')
{
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
    $tab="<table>
             <tr><td colspan=5 align=left><font size=3>".strtoupper($_SESSION['lang']['daftarHutang'])."</font></td></tr> 
             <tr><td colspan=5 align=left>".(isset($optNm[$kodeOrg])? $optNm[$kodeOrg]: '')."</td></tr>   
             <tr><td colspan=5>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tagihan']." ".$_SESSION['lang']['dari']." ".$periode." ".$_SESSION['lang']['sampai']." ".$periode2."</td></tr>   
             </table>";
}
else
{ 
    $bg="";
    $brdr=0;
}

//kondisi
$where5=$where2="";

if($kodeOrg!='') {
    $where5=" and kodeorg = '".$kodeOrg."'";
    $where2=" and kodeorg='".$kodeOrg."'";
}

if($kodesupplier!='') {
    $where_supplier =" and a.kodesupplier = '".$kodesupplier."'";
}else{
    $where_supplier = "";
}

if($statTagihan==1) {
    $where=" noinvoice
                IN (select distinct noinvoice from ".$dbname.".aging_sch_vw where (dibayar!=0) ".$where2.")";
} else if($statTagihan==0) {
    $where=" noinvoice 
                IN (select distinct noinvoice from ".$dbname.".aging_sch_vw where (dibayar is null or dibayar=0) ".$where2.")";
} else if($statTagihan==2) {
    $where=" 1=1";
}

if($periode!=''||$periode2!='')
{
    if(strlen($periode)<7)
    {
        if($peridode==$periode2){
                $where.=" and substring(a.tanggal,1,4) between  '".$periode."' and '".$periode2."' ";
        }else{
                exit("error:Harus Di Tahun Yang Sama");
        }
    }
    else
    {
        $where.=" and substring(a.tanggal,1,7)  between '".$periode."' and '".$periode2."'";
    }
}

$sByr="select distinct sum(a.jumlah) as jumlah,b.tanggal,a.notransaksi,a.keterangan1
        from ".$dbname.". keu_kasbankdt a
        left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
        INNER JOIN ".$dbname.".keu_tagihanht c ON a.keterangan1 = c.noinvoice
        where a.keterangan1!='' 
        and a.tipetransaksi='K' and b.posting=1
        group by a.keterangan1,a.tipetransaksi";
$res=$owlPDO->query($sByr) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rByr=$res->fetch()){
    $penambah[$rByr['keterangan1']]=$rByr['jumlah'];
    $ntrKasBank[$rByr['keterangan1']]=$rByr['notransaksi'];
    $tglKasBank[$rByr['keterangan1']]=$rByr['tanggal'];
}
$sByr="select distinct sum(a.jumlah) as jumlah,a.keterangan1  from ".$dbname.". keu_kasbankdt a
      left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
      INNER JOIN ".$dbname.".keu_tagihanht c ON a.keterangan1 = c.noinvoice
      where a.keterangan1!='' 
      and a.tipetransaksi='M'  and b.posting=1 group by a.keterangan1,a.tipetransaksi";
$res=$owlPDO->query($sByr) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rByr2=$res->fetch()){
    $pengurang[$rByr2['keterangan1']]=$rByr2['jumlah'];
}        

//get data klo bukan pdf
if($_GET['proses']!='pdf')
{
    $tab="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
    $tab.="<tr class=rowheader>";
    $tab.="<td align=center ".$bg.">No.</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['pt']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['noinvoice']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tagihan']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['notransaksi']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dibayar']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['nopo']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['matauang']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['namasupplier']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['nilaiinvoice']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['terbayar']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jatuhtempo']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['nofp']."</td>";
    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['dokumen']."</td>";

    $tab.="</tr></thead><tbody>";       
    $sKodeOrg="SELECT a.`noinvoice`,a.`nopo`,a.`tanggal`,a.kodesupplier,a.`jatuhtempo`,a.`nofp`,a.`keterangan`,
                      sum((a.`nilaiinvoice`+a.`nilaippn`)-a.`uangmuka`) as nilvoice,a.`kodeorg`,posting
    FROM      ".$dbname.".`keu_tagihanht` a WHERE ".$where." ".$where5." ".$where_supplier." group by a.noinvoice order by a.tanggal asc";
    $arrDok=array("0"=>$_SESSION['lang']['lengkap'],"1"=>$_SESSION['lang']['belumlengkap']);
    $res=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $numrows=owlBaris($res);
    if($numrows!=0)
    {
        $no=$totInvoice=0;
        while($rKode=$res->fetch())
        {
            $no+=1;
            if(!isset($penambah[$rKode['noinvoice']])) $penambah[$rKode['noinvoice']]=0;
            if(!isset($pengurang[$rKode['noinvoice']])) $pengurang[$rKode['noinvoice']]=0;
            if(!isset($ntrKasBank[$rKode['noinvoice']])) $ntrKasBank[$rKode['noinvoice']]='';
            if(!isset($tglKasBank[$rKode['noinvoice']])) $tglKasBank[$rKode['noinvoice']]='';
            if(!isset($optMatauang[$rKode['nopo']])) $optMatauang[$rKode['nopo']]='';
            $dibayarsmp[$rKode['noinvoice']]=$penambah[$rKode['noinvoice']]-$pengurang[$rKode['noinvoice']];
            
            if($rByr['tanggal']=='')  
            {
                    $rByr['tanggal']="0000-00-00";
            }
            
            if($statTagihan==1){
                if($dibayarsmp[$rKode['noinvoice']]!=0){
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=left>".$rKode['kodeorg']."</td>";
                    $tab.="<td align=left>".$rKode['noinvoice']."</td>";
                    $tab.="<td align=center>".$rKode['tanggal']."</td>";
                    $tab.="<td align=left>".$ntrKasBank[$rKode['noinvoice']]."</td>";
                    $tab.="<td align=center>".$tglKasBank[$rKode['noinvoice']]."</td>";
                    $tab.="<td align=left>".$rKode['nopo']."</td>";
                    $tab.="<td align=left>".$optMatauang[$rKode['nopo']]."</td>";
                    $tab.="<td align=left>".$optNmSupp[$rKode['kodesupplier']]."</td>";
                    $tab.="<td align=right>".number_format($rKode['nilvoice'],2)."</td>";
                    $tab.="<td align=right>".number_format($dibayarsmp[$rKode['noinvoice']],2)."</td>";
                    $tab.="<td align=center>".$rKode['jatuhtempo']."</td>";
                    $tab.="<td align=left>".$rKode['nofp']."</td>";

                    $tab.="<td align=left>".$arrDok[$rKode['posting']]."</td>";
                    $tab.="</tr>";
                    $totInvoice+=$rKode['nilvoice'];    
                }
            }else{
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=left>".$rKode['kodeorg']."</td>";
                $tab.="<td align=left>".$rKode['noinvoice']."</td>";
                $tab.="<td align=center>".$rKode['tanggal']."</td>";
                $tab.="<td align=left>".$ntrKasBank[$rKode['noinvoice']]."</td>";
                $tab.="<td align=center>".$tglKasBank[$rKode['noinvoice']]."</td>";
                $tab.="<td align=left>".$rKode['nopo']."</td>";
                $tab.="<td align=left>".$optMatauang[$rKode['nopo']]."</td>";
                $tab.="<td align=left>".$optNmSupp[$rKode['kodesupplier']]."</td>";
                $tab.="<td align=right>".number_format($rKode['nilvoice'],2)."</td>";
                $tab.="<td align=right>".number_format($dibayarsmp[$rKode['noinvoice']],2)."</td>";
                $tab.="<td align=center>".$rKode['jatuhtempo']."</td>";
                $tab.="<td align=left>".$rKode['nofp']."</td>";
                $tab.="<td align=left>".$arrDok[$rKode['posting']]."</td>";
                $tab.="</tr>";
                $totInvoice+=$rKode['nilvoice'];
            }
        }

        $tab.="</tbody><thead><tr class=rowheader>";
        $tab.="<td  align=right colspan=9>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right >".number_format($totInvoice,2)."</td>";
        // $tab.="<td align=left colspan=7>".kekata($totInvoice)."</td>";
        $tab.="<td align=left colspan=4></td>";
        $tab.="</tr>";
        $tab.="</thead>";

    } else {
        $tab.="<tr class=rowcontent align=center><td colspan=15>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    $tab.="</table>";
}

switch($proses) {
    case'preview':
        echo $tab;
    break;

    case'excel':
        if($periode=='') {
            exit("Error:Field Tidak Boleh Kosong");
        }
        //||$statTagihan==''
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="daftarHutang".$dte;
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }

            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo"<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                    </script>";
                exit;
            } else {
                echo"<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                    </script>";
            }
            fclose($handle);
        }
    break;

    case'pdf':
        class PDF extends FPDF {
            function Header() {

                global $tgl1;
                global $where;
                global $tgl2;
                global $kodeOrg;
                global $statTagihan;
                global $dbname;
                global $optNm;
                global $optNmSupp;

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon', "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $this->SetFont('Arial','B',8);
                $this->Cell($width,$height,$_SESSION['org']['namaorganisasi'],0,1,'R');
                // $path='images/logo.jpg';
                //$this->Image($path,$this->lMargin,$this->tMargin,0,55);
                //$this->SetFont('Arial','B',9);
                //$this->SetFillColor(255,255,255);	
                //$this->SetX(100);   
                //$this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                //$this->SetX(100); 		
                //$this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                //$this->SetX(100); 			
                //$this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                //$this->Line($this->lMargin,$this->tMargin+($height*4),
                //$this->lMargin+$width,$this->tMargin+($height*4));

                $this->SetFont('Arial','B',15);
                $this->Cell($width,$height,$kodeOrg,'',0,'R');
                $this->Ln();
                $this->Ln();
                if(strlen($_GET['periode'])>4)
                {
                    $periode=substr(tanggalnormal($_GET['periode']),1,7);
                    $periode2=substr(tanggalnormal($_GET['periode2']),1,7);
                } else {
                    $periode=$_GET['periode'];
                    $periode2=$_GET['periode2'];
                }
                $this->Cell($width,$height,$_SESSION['lang']['periode']." ".$_SESSION['lang']['tagihan']." ".$_SESSION['lang']['dari']." ".$periode." ".$_SESSION['lang']['sampai']." ".$periode2,'',0,'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial','B',6);
                $this->SetFillColor(220,220,220);
                                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['pt'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['noinvoice'],1,0,'C',1);		
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tagihan'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dibayar'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['notransaksi'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['nopo'],1,0,'C',1);
                $this->Cell(12/100*$width,$height,$_SESSION['lang']['namasupplier'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['nilaiinvoice'],1,0,'C',1);
                $this->Cell(5/100*$width,$height,$_SESSION['lang']['nilaippn'],1,0,'C',1);
                $this->Cell(6/100*$width,$height,$_SESSION['lang']['jatuhtempo'],1,0,'C',1);
                $this->Cell(9/100*$width,$height,$_SESSION['lang']['nofp'],1,1,'C',1);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }

        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 10;

        $pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',6);

        $sKodeOrg="SELECT a.`noinvoice`,a.`nopo`,a.`tanggal`,a.kodesupplier,a.`nilaiinvoice`,a.`nilaippn`,a.`jatuhtempo`,a.`nofp`,a.`keterangan`,a.`matauang`,a.`uangmuka`,a.`kodeorg`
                   FROM ".$dbname.".`keu_tagihanht` a WHERE ".$where." order by a.tanggal asc";
        $res=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $numrows=owlBaris($res);
        if($numrows!=0)
        {
            $no=$totInvoice=0;
            while($rKode=$res->fetch()) {
                $no+=1;
                $sTglByr="SELECT DISTINCT a.tanggal AS tglByr, a.notransaksi FROM ".$dbname.".`keu_kasbankht` a 
                        LEFT JOIN ".$dbname.".keu_kasbankdt b ON a.`notransaksi` = b.`notransaksi`WHERE `keterangan1` = '".$rKode['noinvoice']."'";
                $qTglByr=$owlPDO->query($sTglByr) or die(print " Gagal: ".PDOException::getMessage());
                $qTglByr->setFetchMode(PDO::FETCH_ASSOC);
                $rTglByr=$qTglByr->fetch();
                if($rTglByr['tglByr']=='')
                {
                    $rTglByr['tglByr']="0000-00-00";
                }
                if(!isset($tglKasBank[$rKode['noinvoice']])) $tglKasBank[$rKode['noinvoice']]='0000-00-00';
                if(!isset($ntrKasBank[$rKode['noinvoice']])) $ntrKasBank[$rKode['noinvoice']]='';
                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                $pdf->Cell(15/100*$width,$height,$optNm[$rKode['kodeorg']],1,0,'L',1);		
                $pdf->Cell(10/100*$width,$height,$rKode['noinvoice'],1,0,'L',1);
                $pdf->Cell(7/100*$width,$height,tanggalnormal($rKode['tanggal']),1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,tanggalnormal($tglKasBank[$rKode['noinvoice']]),1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,$ntrKasBank[$rKode['noinvoice']],1,0,'L',1);
                $pdf->Cell(10/100*$width,$height,$rKode['nopo'],1,0,'L',1);
                $pdf->Cell(12/100*$width,$height,$optNmSupp[$rKode['kodesupplier']],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,number_format($rKode['nilaiinvoice'],2),1,0,'R',1);
                $pdf->Cell(5/100*$width,$height,$rKode['nilaippn'],1,0,'R',1);
                $pdf->Cell(6/100*$width,$height,tanggalnormal($rKode['jatuhtempo']),1,0,'C',1);
                $pdf->Cell(9/100*$width,$height,$rKode['nofp'],1,1,'L',1);    
                $totInvoice+=$rKode['nilaiinvoice'];
            }
            $pdf->Cell(72/100*$width,$height,$_SESSION['lang']['total'].": ".kekata($totInvoice),1,0,'L',1);
            $pdf->Cell(28/100*$width,$height,number_format($totInvoice,2),1,1,'L',1);
        } else {
            exit("Error: Data Kosong");
        }
        
        $pdf->Output();

    break;

    default:
    break;
}