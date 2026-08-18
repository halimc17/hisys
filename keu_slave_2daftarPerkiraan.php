<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=$_POST['proses'];

$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satuanbarang=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');

if($proses=='excel')
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
    $ceklis = "<td align=center>&#10004;</td>";
    $uncek = "<td align=center></td>";
}
else
{ 
    $stream = "<table id=mytable class=sortable cellspacing=1 cellpadding=5>";
    $ceklis = "<td align=center><img src=images/done.png class=resicon title='True'></td>";
    $uncek = "<td align=center><img src=images/full-screen.png class=resicon title='False'></td>";
    $uncek = "<td align=center><input type=checkbox disabled></td>";
}
 $stream.="<thead class=rowheader>
                <tr  bgcolor=#CCCCCC class=rowheader>
                    <th align=center>".$_SESSION['lang']['nomorperkiraan']."</th>
                    <th align=center>".$_SESSION['lang']['namaperkiraan']."</th>
                    <th align=center>".$_SESSION['lang']['tipe']."</th>
                    <th align=center>".$_SESSION['lang']['level']."</th>
                    <th align=center>".$_SESSION['lang']['matauang']."</th>
                    <th align=center>".$_SESSION['lang']['tampilkan']."</th>
                    <th align=center>".$_SESSION['lang']['kasbank']."</th>
                    <th align=center>".$_SESSION['lang']['detail']."</th>
                    <th align=center>".$_SESSION['lang']['kasbank']." ".$_SESSION['lang']['detail']."</th>
                    <th align=center>".$_SESSION['lang']['kodekegiatan']."</th>
                    <th align=center>".$_SESSION['lang']['kodeblok']."</th>
                    <th align=center>".$_SESSION['lang']['invoice']." AP</th>
                    <th align=center>".$_SESSION['lang']['kodeasset']."</th>
                    <th align=center>".$_SESSION['lang']['kodesupplier']."</th>
                    <th align=center>".$_SESSION['lang']['jurnalmemo']."</th>
                    <th align=center>".$_SESSION['lang']['nik']."</th>
                    <th align=center>".$_SESSION['lang']['kodevhc']."</th>
                    <th align=center>".$_SESSION['lang']['nodok']."</th>
                    <th align=center>".$_SESSION['lang']['kodecustomer']."</th>
                </tr></thead>";

$sql="select * from ".$dbname.".keu_5akun";				
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $no+=1;
    $stream .= "<tr class=rowcontent>
                <td>".$bar['noakun']."</td>";

    if($_SESSION['language']=='EN')
        $stream .= "<td>".$bar['namaakun1']."</td>";
    else
        $stream .= "<td>".$bar['namaakun']."</td>";

    $stream .= "<td>".$bar['tipeakun']."</td>
                <td>".$bar['level']."</td>
                <td>".$bar['matauang']."</td>
                <td>".$bar['pemilik']."</td>";

    if ($bar['kasbank']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['detail']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kasbankdetail']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodekegiatan']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodeblok']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['tagihan']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodeasset']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodesupplier']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['jurnalmemorial']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['nik']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodevhc']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['nodok']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;
    if ($bar['kodecustomer']==1)
        $stream .= $ceklis;
    else
        $stream .= $uncek;

    $stream.="</tr>";
}




$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
        case 'preview':
                echo $stream;
    break;

######EXCEL	
        case 'excel':
                //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
                $tglSkrg=date("Ymd");
                $nop_="Daftar_perkiraan".$tglSkrg;
                if(strlen($stream)>0)
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
                        if(!fwrite($handle,$stream))
                        {
                                echo "<script language=javascript1.2>
                                parent.window.alert('Can't convert to excel format');
                                </script>";
                                exit;
                        }
                        else
                        {
                                echo "<script language=javascript1.2>
                                window.location='tempExcel/".$nop_.".xls';
                                </script>";
                        }
                        fclose($handle);
                }     
                break;

###############	
#panggil PDFnya
###############

                case'pdf':

                $table = "keu_5akun";



                #====================== Prepare Data
                $query = selectQuery($dbname,$table);
                #print_r ($_GET);
                #exit;
                $result = fetchData($query);
                $header = array();
                foreach($result[0] as $key=>$row) {
                        $header[] = $key;
                }


            #====================== Prepare Header PDF
                        class masterpdf extends FPDF {
                                function Header() {
                                        global $table;
                                        global $header;

                                        # Panjang, Lebar
                                        $width = $this->w - $this->lMargin - $this->rMargin;
                                                        $height = 12;
                                        $this->SetFont('Arial','B',8);
                                                        $this->Cell(20,$height,$_SESSION['org']['namaorganisasi'],'',1,'L');
                                        $this->SetFont('Arial','B',12);
                        #		$this->Cell($width,$height,'Tabel : '.$table,'',1,'L');
                                                        $this->Cell($width,$height,strtoupper($_SESSION['lang']['daftarperkiraan']),'',1,'C');
                                        $this->SetFont('Arial','B',8);
                                                        $this->Cell(420,$height,' ','',0,'R');
                                                        $this->Cell(38,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                                                        $this->Cell(5,$height,':','',0,'L');
                                                        $this->Cell(40,$height,date('d-m-Y H:i'),'',1,'L');
                                                        $this->Cell(420,$height,' ','',0,'R');
                                                        $this->Cell(38,$height,$_SESSION['lang']['page'],'',0,'L');
                                                        $this->Cell(8,$height,':','',0,'L');
                                                        $this->Cell(15,$height,$this->PageNo(),'',1,'L');
                        #        $this->Ln();
                                                        $this->Cell(420,$height,' ','',0,'R');
                                                        $this->Cell(38,$height,'User','',0,'L');
                                                        $this->Cell(8,$height,':','',0,'L');
                                                        $this->Cell(20,$height,$_SESSION['standard']['username'],'',1,'L');
                                        $this->Ln();

                                        # Generate Header
                        #        foreach($header as $hName) {
                        #            $this->Cell($width/count($header),$height,ucfirst($hName),'TBLR',0,'L');
                        #        }
                                        $this->Cell(60,1.5*$height,$_SESSION['lang']['nomorperkiraan'],'TBLR',0,'C');
                                        $this->Cell(260,1.5*$height,$_SESSION['lang']['namaperkiraan'],'TBLR',0,'C');
                                        $this->Cell(38,1.5*$height,$_SESSION['lang']['tipe'],'TBLR',0,'C');
                                        $this->Cell(45,1.5*$height,$_SESSION['lang']['level'],'TBLR',0,'C');
                                        $this->Cell(47,1.5*$height,$_SESSION['lang']['matauang'],'TBLR',0,'C');
                                        $this->Cell(40,1.5*$height,$_SESSION['lang']['tampilkan'],'TBLR',0,'C');        
                                        $this->Cell(40,1.5*$height,$_SESSION['lang']['detail'],'TBLR',0,'C');
                                        $this->Ln();
                                        $this->Ln();
                                }
                        }

                        #====================== Prepare PDF Setting
                        $pdf = new masterpdf('P','pt','A4');
                        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                        $height = 12;
                        $pdf->SetFont('Arial','',8);
                        $pdf->AddPage();
                        foreach($result as $data) {
                                        $pdf->Cell(60,$height,$data['noakun'],'',0,'L');
                                        if($_SESSION['language']=='EN'){
                                                $pdf->Cell(260,$height,$data['namaakun1'],'',0,'L');
                                        }else{
                                                $pdf->Cell(260,$height,$data['namaakun'],'',0,'L');
                                        }
                                        $pdf->Cell(40,$height,$data['tipeakun'],'',0,'C');
                                        $pdf->Cell(40,$height,$data['level'],'',0,'C');
                                        $pdf->Cell(60,$height,$data['matauang'],'',0,'C');
                                        $pdf->Cell(40,$height,$data['pemilik'],'',0,'C');        
                                        if ($data['detail']==1)
                                        {
                                        $pdf->Cell(40,$height,'Y','',0,'C');
                                        }
                                $pdf->Ln();
                        }


                # Print Out
                $pdf->Output();

                        break;	

}
?>