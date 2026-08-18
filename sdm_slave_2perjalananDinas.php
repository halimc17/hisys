<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kdPt = checkPostGet('kdOrg','');
$periode = checkPostGet('periode','');
$bagian = checkPostGet('bagId','');
$karyawanId = checkPostGet('karyawanId','');
$stat = checkPostGet('stat','');

$sKd="select kodeorganisasi from ".$dbname.".organisasi where induk='".$kdPt."'";
$qKd=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
$qKd->setFetchMode(PDO::FETCH_ASSOC);
$aro=0;
while($rKd=$qKd->fetch())
{
    $aro+=1;
    if($aro==1)
    {
        $kodear="'".$rKd['kodeorganisasi']."'";
    }
    else
    {
        $kodear.=",'".$rKd['kodeorganisasi']."'";
    }
}

//ambil query untuk data karyawan
		$add="";
        if($kdPt=='')
        {

           exit("Error: Working unit required");
        }
        else
        {
            $where.=" karyawanid in (select distinct karyawanid from ".$dbname.".sdm_pjdinasht 
                      where kodeorg in(".$kodear.")) and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8')";
            $add.=" and kodeorg in (".$kodear.")";
        }
        if($karyawanId!='')
        {
            $where.=" and karyawanid='".$karyawanId."'";
        }
        if($bagian!='')
        {
            $where.=" and bagian='".$bagian."'";
        }
            $sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan,a.lokasitugas from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 
            ".$where." order by namakaryawan asc";    
 //exit("Error".$sGetKary."__".$where);
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
    $resData[$kar['karyawanid']][]=$kar['karyawanid'];
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    $lokTugas[$kar['karyawanid']]=$kar['lokasitugas'];
}  

$optOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,kodeorganisasi');

switch($proses)
{
        case'getKaryawan':
            //exit("Error:masuk");
            if($kdPt=='')
            {
                exit("Error : Working unit required");
            }
            $optKary="<option value=''>".$_SESSION['lang']['all']."</option>";
            $sKary="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where 
                    ".$where." order by namakaryawan asc";
			$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
			$qKary->setFetchMode(PDO::FETCH_ASSOC);
            while($rKary=$qKary->fetch())
            {
                $optKary.="<option value=".$rKary['karyawanid'].">".$rKary['namakaryawan']."</option>";
            }
            echo $optKary;
        break;
        case'preview':
            if($periode!='')
            {
            $add=" and substring(tglpertanggungjawaban,1,7)='".$periode."'";
            }
            if($stat!='')
            {
            $add.=" and lunas='".$stat."'";
            }

            #ambil biaya tiket
            $stro="select notransaksi,bytiket,dibayar from ".$dbname.".sdm_pjdinasht where 1=1 ".$add;
			$reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
			$reso->setFetchMode(PDO::FETCH_OBJ);
            $bytiket=Array();
            while($baro=$reso->fetch())
            {
            $bytiket[$baro->notransaksi]=$baro->bytiket;
            $byum[$baro->notransaksi]=$baro->dibayar;
            }

        if(empty($resData))
        {
        exit("Error: Not found");
        }
        else
        {
        $tab.="
                    <table cellspacing='1' border='0' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td align=center>No</td>
        <td align=center>".$_SESSION['lang']['nama']."</td>
        <td align=center>".$_SESSION['lang']['jabatan']."</td>
        <td align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td align=center>".$_SESSION['lang']['tanggaldinas']."</td>
        <td align=center>".$_SESSION['lang']['tanggalkembali']."</td>

        <td align=center>".$_SESSION['lang']['tanggalRelease']."</td>

        <td align=center>".$_SESSION['lang']['tujuan']." 1</td>
        <td align=center>".$_SESSION['lang']['tujuan']." 2</td>
        <td align=center>".$_SESSION['lang']['tujuan']." 3</td>
        <td align=center>".$_SESSION['lang']['tujuan']." ".$_SESSION['lang']['uangmuka']."</td> 
        <td align=center>".$_SESSION['lang']['uangmuka']."</td>
        <td align=center>".$_SESSION['lang']['sudahdipakai']."</td>
        <td align=center>".$_SESSION['lang']['biaya']." Ticket</td>";
        $tab.="</tr></thead>
        <tbody>";
		$no='';
 foreach($resData as $brsDt =>$rData)
        {$no++;
            // $sPjd="select a.*,sum(b.jumlah) as jmlhPjd,sum(b.jumlahhrd) as jmlhSetuju from ".$dbname.".sdm_pjdinasdt b left join ".$dbname.".sdm_pjdinasht a on a.notransaksi=b.notransaksi
                // where statushrd=1   ".$add." and a.karyawanid='".$rData[0]."' group by notransaksi";
			$sPjd="select a.*,sum(b.jumlah) as jmlhPjd,sum(b.jumlahhrd) as jmlhSetuju from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".sdm_pjdinasdt b on a.notransaksi=b.notransaksi
                where  statushrd=1 ".$add." and a.karyawanid='".$rData[0]."'group by notransaksi";
			$qPjd=$owlPDO->query($sPjd) or die(print " Gagal: ".PDOException::getMessage());
			$qPjd->setFetchMode(PDO::FETCH_ASSOC);
            while($rPjd=$qPjd->fetch())
            {
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$namakar[$rData[0]]."</td>";
            $tab.="<td>".$nmJabatan[$rData[0]]."</td>";
            $tab.="<td>".$rPjd['notransaksi']."</td>";
            $tab.="<td>".tanggalnormal($rPjd['tanggalperjalanan'])."</td>";
            $tab.="<td>".tanggalnormal($rPjd['tanggalkembali'])."</td>";
            $tab.="<td>".tanggalnormal($rPjd['tglpertanggungjawaban'])."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan1']]) ? $optOrg[$rPjd['tujuan1']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan2']]) ? $optOrg[$rPjd['tujuan2']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan3']]) ? $optOrg[$rPjd['tujuan3']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuanlain']]) ? $optOrg[$rPjd['tujuanlain']] : "")."</td>";
            $tab.="<td align=right>".number_format(@$byum[$rPjd['notransaksi']],2)."</td>";
            $tab.="<td align=right>".number_format((@$rPjd['jmlhSetuju']),2)."</td>";
             $tab.="<td align=right>".number_format((@$bytiket[$rPjd['notransaksi']]),2)."</td>";
            $tab.="</tr>";
            @$jmlTot[$rPjd['karyawanid']]+=$rPjd['jmlhSetuju'];
            @$jmlTiket[$rPjd['karyawanid']]+=$bytiket[$rPjd['notransaksi']];
            @$jmlhUm[$rPjd['karyawanid']]+=$byum[$rPjd['notransaksi']];
            }
            if(@$jmlTot[$rData[0]]!='' || @$jmlTot[$rData[0]]!=0)
            {
            $tab.="<tr class=rowcontent style='font-weight:bold;'><td colspan=11>Total ".$namakar[$rData[0]]."</td><td  align=right>".number_format($jmlhUm[$rData[0]],2)."</td><td  align=right>".number_format($jmlTot[$rData[0]],2)."</td><td  align=right>".number_format( $jmlTiket[$rData[0]],2)."</td></tr>";
            }
             @$grandTot+=$jmlTot[$rData[0]];
             @$grandUm+=$jmlhUm[$rData[0]];
             @$grandTi+=$jmlTiket[$rData[0]];
        }
        $tab.="<tr class=rowcontent><td colspan=11>Grand Total </td><td  align=right>".number_format($grandUm,2)."</td><td  align=right>".number_format($grandTot,2)."</td><td  align=right>".number_format($grandTi,2)."</td></tr>";
        $tab.="</tbody></table>";

        echo $tab;
        }
        break;
        case'pdf':
           if($periode!='')
            {
            $add=" and substring(tglpertanggungjawaban,1,7)='".$periode."'";
            }
            if($stat!='')
            {
            $add.=" and lunas='".$stat."'";
            }

            #ambil biaya tiket
            $stro="select notransaksi,bytiket from ".$dbname.".sdm_pjdinasht where 1=1 ".$add;
			$reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
			$reso->setFetchMode(PDO::FETCH_OBJ);
            $bytiket=Array();
            while($baro=$reso->fetch())
            {
            $bytiket[$baro->notransaksi]=$baro->bytiket;
            }

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $period;
                                global $periode;
                                global $kdOrg;
                                global $kdeOrg;
                                global $tgl1;
                                global $tgl2;
                                global $where;
                                global $jmlHari;
                                global $test;
                                global $klmpkAbsn;
                                global $tipeKary;
                                global $resData;
                                global $byum;
                                global $kdPt;

                                $jmlHari=$jmlHari*1.5;
                                $cols=247.5;
                            # Alamat & No Telp
                $arrHead = setheadreport(substr($kdPt,0,4),$kdPt);
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',10);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['lapPjd'],'',0,'L');
                                $this->Ln();
                                $this->Ln();

                $this->SetFont('Arial','B',6);
                $this->SetFillColor(220,220,220);
                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(11/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
                //$this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['notransaksi'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggaldinas'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggalkembali'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tanggalRelease'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tujuan']."1",1,0,'C',1);
                $this->Cell(7/100*$width,$height,$_SESSION['lang']['tujuan']."2",1,0,'C',1);
                $this->Cell(6/100*$width,$height,$_SESSION['lang']['tujuan']."3",1,0,'C',1);
                $this->Cell(13/100*$width,$height,$_SESSION['lang']['tujuan']."  ".$_SESSION['lang']['lain'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['uangmuka'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['sudahdipakai'],1,0,'C',1);
                $this->Cell(7/100*$width,$height,'Ticket',1,1,'C',1);
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
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
                #ambil biaya tiket
            $stro="select notransaksi,bytiket,dibayar from ".$dbname.".sdm_pjdinasht where 1=1 ".$add;
			$reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
			$reso->setFetchMode(PDO::FETCH_OBJ);
            $bytiket=Array();
            while($baro=$reso->fetch())
            {
            $bytiket[$baro->notransaksi]=$baro->bytiket;
            $byum[$baro->notransaksi]=$baro->dibayar;
            }
		if(isset($resData))	
        foreach($resData as $brsDt =>$rData)
        {
            $sPjd="select a.*,sum(b.jumlah) as jmlhPjd,sum(b.jumlahhrd) as jmlhSetuju from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".sdm_pjdinasdt b on a.notransaksi=b.notransaksi
                where  statushrd=1  ".$add." and a.karyawanid='".$rData[0]."'group by notransaksi";
			$qPjd=$owlPDO->query($sPjd) or die(print " Gagal: ".PDOException::getMessage());
			$qPjd->setFetchMode(PDO::FETCH_ASSOC);
            while($rPjd=$qPjd->fetch())
            {
                $no+=1;
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                $pdf->Cell(11/100*$width,$height,$namakar[$rData[0]],1,0,'L',1);		
                //$this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
                $pdf->Cell(10/100*$width,$height,$rPjd['notransaksi'],1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,tanggalnormal($rPjd['tanggalperjalanan']),1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,tanggalnormal($rPjd['tanggalkembali']),1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,tanggalnormal($rPjd['tglpertanggungjawaban']),1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,$rPjd['tujuan1'],1,0,'C',1);
                $pdf->Cell(7/100*$width,$height,$rPjd['tujuan2'],1,0,'C',1);
                $pdf->Cell(6/100*$width,$height,$rPjd['tujuan3'],1,0,'C',1);
                $pdf->Cell(13/100*$width,$height,$rPjd['tujuanlain'],1,0,'L',1);

                $pdf->Cell(8/100*$width,$height,number_format($byum[$rPjd['notransaksi']],2),1,0,'R',1);
                $pdf->Cell(8/100*$width,$height,number_format(($rPjd['jmlhSetuju']),2),1,0,'R',1);
                $pdf->Cell(7/100*$width,$height,number_format(( $bytiket[$rPjd['notransaksi']]),2),1,1,'R',1);

                @$jmlTot[$rPjd['karyawanid']]+=$rPjd['jmlhSetuju'];
                @$jmlhUm[$rPjd['karyawanid']]+=$byum[$rPjd['notransaksi']];
                @$jmlTiket[$rPjd['karyawanid']]+=$bytiket[$rPjd['notransaksi']];                

            }
            if(@$jmlTot[$rData[0]]!=''||@$jmlTot[$rData[0]]!=0)
            {
                $pdf->SetFillColor(220,220,220);
                $pdf->Cell(78/100*$width,$height,"Total ".$namakar[$rData[0]],1,0,'L',1);
                $pdf->Cell(8/100*$width,$height,number_format($jmlhUm[$rData[0]],2),1,0,'R',1);
                $pdf->Cell(8/100*$width,$height,number_format($jmlTot[$rData[0]],2),1,0,'R',1);
                $pdf->Cell(7/100*$width,$height,number_format($jmlTiket[$rData[0]],2),1,1,'R',1);
                $no=0;
            //$tab.="<tr class=rowcontent><td colspan=10 align=right>Total ".$namakar[$rData[0]]."</td><td  align=right>".number_format($jmlTot[$rData[0]],2)."</td></tr>";
            }
            @$grandTot+=$jmlTot[$rData[0]];
             @$grandUm+=$jmlhUm[$rData[0]];
             @$grandTi+=$jmlTiket[$rData[0]];            
        }
        $pdf->Cell(78/100*$width,$height,"Grand Total ",1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($grandUm,2),1,0,'R',1);
        $pdf->Cell(8/100*$width,$height,number_format($grandTot,2),1,0,'R',1);
        $pdf->Cell(7/100*$width,$height,number_format($grandTi,2),1,1,'R',1);
        $pdf->Output();

        break;
        case'excel':
                        if($periode!='')
            {
            $add=" and substring(tglpertanggungjawaban,1,7)='".$periode."'";
            }
            if($stat!='')
            {
            $add.=" and lunas='".$stat."'";
            }

            #ambil biaya tiket
            $stro="select notransaksi,bytiket,dibayar from ".$dbname.".sdm_pjdinasht where 1=1 ".$add;
			$reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
			$reso->setFetchMode(PDO::FETCH_OBJ);
            $bytiket=Array();
            while($baro=$reso->fetch())
            {
                $bytiket[$baro->notransaksi]=$baro->bytiket;
                $byum[$baro->notransaksi]=$baro->dibayar;
            }
        if($periode!='')
        {
            $add=" and substring(tglpertanggungjawaban,1,7)='".$periode."'";
        }
         if(empty($resData))
        {
        exit("Error: Not Found");
        }
        else
        {
        $tab.="
                   <table cellspacing='1' border='1' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggaldinas']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggalkembali']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggalRelease']."</td>

        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tujuan']." 1</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tujuan']." 2</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tujuan']." 3</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tujuan']." ".$_SESSION['lang']['lain']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['uangmuka']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sudahdipakai']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['biaya']." Ticket</td>";
        $tab.="</tr></thead>
        <tbody>";
        foreach($resData as $brsDt =>$rData)
        {
            $sPjd="select a.*,sum(b.jumlah) as jmlhPjd,sum(b.jumlahhrd) as jmlhSetuju from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".sdm_pjdinasdt b on a.notransaksi=b.notransaksi
                where  statushrd=1 ".$add." and a.karyawanid='".$rData[0]."'group by notransaksi";
			$qPjd=$owlPDO->query($sPjd) or die(print " Gagal: ".PDOException::getMessage());
			$qPjd->setFetchMode(PDO::FETCH_ASSOC);
            while($rPjd=$qPjd->fetch())
            {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$namakar[$rData[0]]."</td>";
            $tab.="<td>".$nmJabatan[$rData[0]]."</td>";
            $tab.="<td>".$rPjd['notransaksi']."</td>";
            $tab.="<td>".$rPjd['tanggalperjalanan']."</td>";
            $tab.="<td>".$rPjd['tanggalkembali']."</td>";
            $tab.="<td>".$rPjd['tglpertanggungjawaban']."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan1']]) ? $optOrg[$rPjd['tujuan1']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan2']]) ? $optOrg[$rPjd['tujuan2']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuan3']]) ? $optOrg[$rPjd['tujuan3']] : "")."</td>";
            $tab.="<td>".(isset($optOrg[$rPjd['tujuanlain']]) ? $optOrg[$rPjd['tujuanlain']] : "")."</td>";
            $tab.="<td align=right>".number_format($byum[$rPjd['notransaksi']],2)."</td>";
            $tab.="<td align=right>".number_format(($rPjd['jmlhSetuju']),2)."</td>";
             $tab.="<td align=right>".number_format(($bytiket[$rPjd['notransaksi']]),2)."</td>";
            $tab.="</tr>";
             @$jmlTot[$rPjd['karyawanid']]+=$rPjd['jmlhSetuju'];
            @$jmlTiket[$rPjd['karyawanid']]+=$bytiket[$rPjd['notransaksi']];
            @$jmlhUm[$rPjd['karyawanid']]+=$byum[$rPjd['notransaksi']];
            }
            if(@$jmlTot[$rData[0]]!='' || @$jmlTot[$rData[0]]!=0)
            {
            $tab.="<tr style='font-weight:bold;' bgcolor=#DEDEDE ><td colspan=10>Total ".$namakar[$rData[0]]."</td><td  align=right>".number_format($jmlhUm[$rData[0]],2)."</td><td  align=right>".number_format($jmlTot[$rData[0]],2)."</td><td  align=right>".number_format( $jmlTiket[$rData[0]],2)."</td></tr>";
            }
             @$grandTot+=$jmlTot[$rData[0]];
             @$grandUm+=$jmlhUm[$rData[0]];
             @$grandTi+=$jmlTiket[$rData[0]];
        }
        $tab.="<tr bgcolor=#DEDEDE ><td colspan=10>Grand Total </td><td  align=right>".number_format($grandUm,2)."</td><td  align=right>".number_format($grandTot,2)."</td><td  align=right>".number_format($grandTi,2)."</td></tr>";
        $tab.="</tbody></table>";
        }

                    //echo "warning:".$strx;
                    //=================================================


                    $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	

                    $nop_="rekapPerjalananDinas__".$kdPt;
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
        case'getTgl':
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['empl']['lokasitugas'];
        }
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' and periode='".$tanggal."' ";
		$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl=$qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getKry':
        $optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if(strlen($kdeOrg)>4)
        {
                $where=" subbagian='".$kdeOrg."'";
        }
        else
        {
                $where=" lokasitugas='".$kdeOrg."' and (subbagian='0' or subbagian is null)";
        }
        $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
		$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
		$qKry->setFetchMode(PDO::FETCH_ASSOC);
        while($rKry=$qKry->fetch())
        {
                $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
        }
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        //echo $optPeriode;
        echo $optKry."###".$optPeriode;
        break;
        case'getPeriode':
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdUnit."'";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        echo $optPeriode;
        break;
        default:
        break;
}
?>