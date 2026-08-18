<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$optNm=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optasset=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$karyawanid = checkPostGet('karyawanid', '');
$notranscr = checkPostGet('notranscr', '');
$tglcr = tanggalsystem(checkPostGet('tglcr', ''));
$tglcarismp = tanggalsystem(checkPostGet('tglcarismp', ''));
// $optstatus=array("0"=>"Belum Menyetujui","1"=>"Disetujui","2"=>"Ditolak");
	 
$optstatus=array("0"=>"","1"=>"Disetujui","2"=>"Dikoreksi","3"=>"Ditolak","9"=>"Proses Pengajuan");	 
switch ($method) {
    case 'loadData':
        $where = "";
        if ($notranscr != '') {
            $where.=" and notransaksi like '%" . $notranscr . "%' ";
        }
        if (($tglcr != '')&&($tglcarismp != '')) {
            $where.=" and tanggal between '".$tglcr."' and  '".$tglcarismp."' ";
        }

        if (($tglcr != '')&&($tglcarismp == '')) {
            $where.=" and tanggal='" . $tglcr . "' ";
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
        $str="select * from ".$dbname.".log_formcapex_ht where 1=1 ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".log_formcapex_ht where 1=1 ".$where." order by tanggal desc, status_pengajuan desc limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".$bar['kodept']."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                #pembuat
                $whrKar2="karyawanid='".$bar['dibuat_oleh']."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                #no.pp
                $whrpp="keterangan='".$bar['notransaksi']."'";
                $optpp=makeOption($dbname,'log_prapoht','keterangan,nopp',$whrpp);

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar['notransaksi']."</td>
                    <td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optpt[$bar['kodept']]."</td>";

				/*
                for($a=1;$a<3;$a++){
                    $tab.="<td align=center>".$optttd[$bar['diperiksa'.$a]]."<br>(".$optstatus[$bar['stat_periksa'.$a]].")</td>";
                } 

                    $tab.="<td align=center>".$optttd[$bar['budget']]."<br>(".$optstatus[$bar['stat_budget']].")</td>";

                    $tab.="<td align=center>".$optttd[$bar['menyetujui1']]."<br>(".$optstatus[$bar['stat_menyetujui1']].")</td>";

                if ($bar['subtotal']<=50000000){
                    $isi="Tidak Ada Persetujuan 2<br> (Budget < Rp. 50.000.000,-)";
                }else{
                    $isi="".$optttd[$bar['menyetujui2']]."<br>(".$optstatus[$bar['stat_menyetujui2']].")";
                }
				*/
				
				// $tab.="<td align=center>".$isi."</td>"; 

				$tab.="<td align=center>".$optstatus[$bar['status_pengajuan']]."</td>";  
				$tab.="<td align=center>".$optpp[$bar['notransaksi']]."</td>";  

                $tab.="<td align=center>";
                $tab.="<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar['notransaksi']. "',event);\" >";
                // if ($bar['status_pengajuan']!=0){
                    // $tab.="&nbsp<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewpdf('" . $bar['notransaksi']. "',event);\">";  
                // }
                
                $tab.="</td>";
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
                <tr><td colspan=11 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case 'viewdetail':
        //get data spdt dan spht
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
        $subtotal=$bar->subtotal;

		
			 $nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		
            #diperiksa1
            $whrKar1="karyawanid='".$bar->diperiksa1."'";
            $optdiperiksa1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #diperiksa2
            $whrKar2="karyawanid='".$bar->diperiksa2."'";
            $optdiperiksa2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #budget 
            $whrKar3="karyawanid='".$bar->budget."'";
            $optbudget=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3); 
            #menyetujui1 
            $whrKar4="karyawanid='".$bar->menyetujui1."'";
            $optmenyetujui1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar4);
            #menyetujui2 
            $whrKar5="karyawanid='".$bar->menyetujui2."'";
            $optmenyetujui2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar5); 
            #namapt
            $whrpt="kodeorganisasi='".$bar->kodept."'";
            $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);    
            
            $tab="<legend><b>DETAIL PURCHASE REQUEST</b></legend><br>";
            $tab.="<table align=left border=0>
            
            <tr>
                <td>No Transaksi</td>
                <td> : </td>
                <td>".$notransaksi."</td>
            </tr>";
			
			$str1="SELECT * from ".$dbname.".approval where notransaksi='".$notransaksi."'";
			//echo $str;
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch()){
				$tab.="<tr>
                <td>".$nmkar[$bar1->karyawanid]."</td>
                <td> : </td>
                <td>".$optstatus[$bar1->status]."</td>
				</tr>";
			}
			
			/*
            tab.="<tr>
                <td>Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optdiperiksa1[$bar->diperiksa1]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa1]."</td>
            </tr>
            <tr>
                <td>Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optdiperiksa2[$bar->diperiksa2]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa2]."</td>
            </tr>
            <tr>
                <td>Budget</td>
                <td> : </td>
                <td>".$optbudget[$bar->budget]."</td>
            </tr>
            <tr>
                <td>Status Budget</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_budget]."</td>
            </tr>
            <tr>
                <td>Persetujuan 1</td>
                <td> : </td>
                <td>".$optmenyetujui1[$bar->menyetujui1]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui1]."</td>
            </tr>";
        if ($subtotal>50000000){
        $tab.="<tr>
                <td>Persetujuan 2</td>
                <td> : </td>
                <td>".$optmenyetujui2[$bar->menyetujui2]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui2]."</td>
            </tr>";
        }
		
		*/
		
        $tab.="<tr colspan=3>
                <td>&nbsp;</td>
            </tr>
            <tr colspan=3>
                <td><b>Detail Barang</b></td>
            </tr>
            <tr >
                <td colspan=3>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']." ETA</td>
                    <td align=center>".$_SESSION['lang']['namabarang']."</td>
                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td align=center>" . $_SESSION['lang']['harga'] . " ".$_SESSION['lang']['satuan']."</td>
                    <td align=center>" . $_SESSION['lang']['total'] . "</td>
                    <td align=center>" . $_SESSION['lang']['catatan'] . "</td>
                    <td align=center>Kode Asset</td>
                    <td align=center>Nama Asset</td>
                </tr>
                </thead>";

                $no = 0;
                $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    //kode asset dan suptipe
                    $sSat="select * from ".$dbname.".log_formcapex_assetcode where kodebarang='".$bar->kodebarang."' and notransaksi='".$notransaksi."'";
                    $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
                    $qSat->setFetchMode(PDO::FETCH_ASSOC);
                    $rSat=$qSat->fetch();
                    $kodeasset=$rSat['kodeasset'];
                    $subtipeasset=$rSat['subtipeasset'];
                    $namaasset=$rSat['namaasset'];

                    //nama suptipe
                    $sSat="select kode from ".$dbname.".project where substr(kode,4,2)='".$kodeasset."' and subtipe='".$subtipeasset."' and nama='".$namaasset."' and keterangan='".$notransaksi."'";
                    // echo $sSat;
                    $qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
                    $qSat->setFetchMode(PDO::FETCH_ASSOC);
                    $rSat=$qSat->fetch();
                    $kodeproject=$rSat['kode'];  

                    $total=($bar->jumlah)*($bar->hargasatuan);
                    $no+=1;
                    $tab.="<tr class=rowcontent>   
                        <td>".$no."</td>
                        <td>".tanggalnormal($bar->tanggal_eta)."</td>
                        <td>".$optNmBrg[$bar->kodebarang]."</td>
                        <td align=center>".$bar->jumlah."</td>
                        <td align=right>".@number_format($bar->hargasatuan)."</td>
                        <td align=right>".@number_format($total)."</td>
                        <td align=justify>".$bar->catatan."</td>
                        <td align=center>".$kodeproject."</td>
                        <td align=center>".$namaasset."</td>
                        </tr>";
            
                }
                $tab.="<tr class=rowcontent>   
                        <td colspan=5 align=right>Subtotal</td>
                        <td align=right>".@number_format($subtotal)."</td>
                        <td colspan=3></td>
                       </tr>
                        </table>
            </td>
            </tr>
            </table>";

        echo $tab;
    break;

}

