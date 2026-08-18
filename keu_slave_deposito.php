<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

$pt = checkPostGet('pt','');
$notransaksi = checkPostGet('notransaksi','');
$notranskasbank = checkPostGet('notranskasbank','');
$jnsdeposito = checkPostGet('tipetransaksi','');
$noakun = checkPostGet('noakun','');
$nobilyet = checkPostGet('nobilyet','');
$nodeposito = checkPostGet('nodeposito','');
$tglvaluta = tanggalsystem(checkPostGet('tglvaluta',''));
$tgltempo = tanggalsystem(checkPostGet('tgltempo',''));
$tglcair = tanggalsystem(checkPostGet('tglcair',''));
$tglterima = tanggalsystem(checkPostGet('tglterima',''));
$jangkawaktu = checkPostGet('jangkawaktu','');
$status = checkPostGet('status','');
$sukubunga = checkPostGet('sukubunga','');
$jumlahdeposito = checkPostGet('jumlahdeposito','');
$jumlahdeposito = str_replace(',', '', $jumlahdeposito);
$jumlahbunga = checkPostGet('jumlahbunga','');
$jumlahbunga = str_replace(',', '', $jumlahbunga);
$jumlahpajak = checkPostGet('jumlahpajak','');
$jumlahpajak = str_replace(',', '', $jumlahpajak);
$jumlahpenalti = checkPostGet('jumlahpenalti','');
$jumlahpenalti = str_replace(',', '', $jumlahpenalti);
$realisasi = checkPostGet('realisasi','');
$method = checkPostGet('method','');
$notranscr = checkPostGet('notranscr', '');
$tipecr = checkPostGet('tipecr', '');
$statusclose = checkPostGet('statusclose', '');
$namafile = checkPostGet('namafile','');
$arrstatusclose=array('0' => 'Open','1' => 'Closed' );
$arrstatus=array('0' => 'Non Roll-Over','1' => 'Roll-Over','2' => 'Closed' );
$arrjenis=array('1' => $_SESSION['lang']['depositoberjangka'].'(Automatic Roll-Over)','2' => $_SESSION['lang']['depositoberjangka'].'(Non Automatic Roll Over)');


switch ($method) {

	case 'getakun':
        $akun=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query("select a.noakun,b.namabank,a.rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where pemilik='".$pt."' order by b.namabank ");
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
            if ($noakun==$bar['noakun']){
                $akun.="<option value='".$bar['noakun']."' selected>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }else{
                $akun.="<option value='".$bar['noakun']."'>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }
            
        }

		echo $akun;
	break;

    case'getBulan':
        if($_POST['tgltempo']==''){
            $_POST['tgltempo']=$_POST['tglvaluta'];
        }

        $jmlhBulan=datediff(tanggalsystem($_POST['tglvaluta']),tanggalsystem($_POST['tgltempo']));
        
        echo $jmlhBulan['months'];
    break;

    case 'loadData':
        $where = "";
        $where.= " unit in (".getOrgDetail(2).")";

        if ($tipecr != '') {
            $where.=" and jnsdeposito='" . $tipecr . "' ";
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
        $str="select * from ".$dbname.".keu_depositoht where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{

            $str="select * from ".$dbname.".keu_5daftarbank";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $nmbank[$bar['kodebank']]=$bar['namabank'];
            }

            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_depositoht where ".$where." order by notransaksi desc limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".substr($bar->notransaksi,0,3)."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                $nmBankDt="";
                $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar->noakun."' order by namabank asc";
                $barak=fetchData($strak);
                if(count($barak)!=0){
                    $dtRek=$barak[0];
                    $nmBankDt=$nmbank[$dtRek['namabank']]." - ".$dtRek['rekening'];
                    
                }

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->notransaksi."</td>
                    <td>".$optpt[substr($bar->notransaksi,0,3)]."</td>
                    <td>".$arrjenis[$bar->jnsdeposito]."</td>
                    <td>".$nmBankDt."</td>
                    <td>".$bar->nobilyet."</td>
                    <td>".$bar->nodeposito."</td>
                    <td>".tanggalnormal($bar->tglvaluta)."</td>
                    <td>".tanggalnormal($bar->tgljatuhtempo)."</td>
                    <td align=center>".$bar->sukubunga." %</td>
                    <td align=right>".number_format($bar->jmlhdeposito)."</td>
                    <td align=center>".$arrstatus[$bar->status]."</td>";
                    if ($bar->posting==0){
                        $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar->unit."','".$bar->notransaksi."','".$bar->noakun."','".$bar->jnsdeposito."','".$bar->nobilyet."','".$bar->nodeposito."','".tanggalnormal($bar->tglvaluta)."','".tanggalnormal($bar->tgljatuhtempo)."','".$bar->jangkawaktu."','".$bar->sukubunga."','".number_format($bar->jmlhdeposito)."','".$bar->status."')\"></td>
                               <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('" . $bar->notransaksi. "');\" ></td>
                               <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar->notransaksi."');\" ></td>";
                    }else{
                        /*if ($bar->statusclose==0){
                           $tab.="<td align=center colspan=3 style='width:50px'><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"viewdetail('".$bar->notransaksi."','');\" >
                                <img src=images/icons/lock.png class=resicon  title='Closed' onclick=\"closed('".$bar->notransaksi."');\" ></td>"; 
                        }else{
                            $tab.="<td align=center colspan=3 style='width:50px'><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"viewdetail('".$bar->notransaksi."','');\" ></td>";
                        } */  
                        $tab.="<td align=center colspan=3 style='width:50px'><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"viewdetail('".$bar->notransaksi."','');\" ></td>";
                    }
                    $tab.="<td><img src=images/addplus.png title='Upload' class=resicon onclick=showupload('".$bar->notransaksi."',event)></td>";
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

	case 'insert':

        if ($pt=='' || $noakun=='' || $jnsdeposito=='' || $nobilyet=='' || $nodeposito=='' || $tglvaluta=='' || $tgltempo=='' || $sukubunga=='' || $jumlahdeposito=='') {
            exit('warning : All field may not empty.');
        }

		if ($jnsdeposito==1){
			$kdtipe='DBR';
		}
		if ($jnsdeposito==2){
			$kdtipe='DBN';
		}
		if ($jnsdeposito==3){
			$kdtipe='SDP';
		}
        if ($jnsdeposito==4){
            $kdtipe='DOC';
        }

        if ($status==1) {
            if ($jangkawaktu==0) {
                exit('warning : '.$arrjenis[$jnsdeposito].' tidak boleh kurang dari 1 bulan.');
            }
        }

        ##menghitung jumlah hari
        $dt1 = strtotime($tglvaluta);
        $dt2 = strtotime($tgltempo);
        $diff = abs($dt2-$dt1);
        $jmlhhari = (($diff/86400));

        #variabel $pt berisi dengan kodeorganisasi tipe holding
        #mengambil kode pt untuk notransaksi kodept-notransaksi
        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $induk=$bar['induk'];

        ##buat notransaksi
		$notransaksi = $induk."-".$kdtipe.date('ymd');
        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_depositoht where left(notransaksi,13) = '".$notransaksi."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        $notransaksi=$notransaksi.addZero($awal,3);

        $str = "insert into ".$dbname.".keu_depositoht (notransaksi,unit,jnsdeposito,noakun,nodeposito,nobilyet,createdby,updateby,tglvaluta,tgljatuhtempo,jangkawaktu,sukubunga,jmlhdeposito,status,jumlahhari)
                values ('".$notransaksi."','".$pt."','".$jnsdeposito."','".$noakun."','".$nodeposito."','".$nobilyet."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$tglvaluta."','".$tgltempo."','".$jangkawaktu."','".$sukubunga."','".$jumlahdeposito."','".$status."','".$jmlhhari."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}

	break;

    case'deldt':

        $strdt = "delete from ".$dbname.".keu_depositoht where notransaksi='".$notransaksi."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'updatedt':

        if ($pt=='' || $noakun=='' || $jnsdeposito=='' || $nobilyet=='' || $nodeposito=='' || $tglvaluta=='' || $tgltempo=='' || $sukubunga=='' || $jumlahdeposito=='') {
            exit('warning : All field may not empty.');
        }

        ##menghitung jumlah hari
        $dt1 = strtotime($tglvaluta);
        $dt2 = strtotime($tgltempo);
        $diff = abs($dt2-$dt1);
        $jmlhhari = (($diff/86400));

        $strht = "update ".$dbname.".keu_depositoht set noakun='".$noakun."',updateby='".$_SESSION['standard']['userid']."', nodeposito='".$nodeposito."',nobilyet='".$nobilyet."',tglvaluta='".$tglvaluta."',tgljatuhtempo='".$tgltempo."', jangkawaktu='".$jangkawaktu."',sukubunga='".$sukubunga."',jmlhdeposito='".$jumlahdeposito."',status='".$status."',jumlahhari='".$jmlhhari."'  where notransaksi='".$notransaksi."'";             
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

    case 'posting':

        #ambil data ht
        $str="select * from ".$dbname.".keu_depositoht where notransaksi='".$notransaksi."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jumlahhariht=$bar['jumlahhari'];
        $tglvaluta=$bar['tglvaluta'];
        $tglHisValuta=$bar['tglvaluta'];
        $tglHisJatuhTempo=$bar['tgljatuhtempo'];
        $sukubunga=$bar['sukubunga'];
        $jmlhdeposito=$bar['jmlhdeposito'];
        $noakun=$bar['noakun'];
        $jangkawaktu=$bar['jangkawaktu'];
        $status=$bar['status'];

        /*#cek apakah bank maybank
        $strbrs="select count(*) as jumlahbaris from ".$dbname.".setup_parameterappl where kodeparameter='DPBM' and nilai like '%".$noakun."%'";
        $resbrs= $owlPDO->query($strbrs) or die(print " Gagal: " . PDOException::getMessage());
        $resbrs->setFetchMode(PDO::FETCH_ASSOC);
        $barbrs= $resbrs->fetch();
        if ($barbrs['jumlahbaris']>0) {
            #jika maybank, jumlah hari pertahun ambil dari parameter appl
            $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='DPHR'";
            $res= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar= $res->fetch();
            $jmlhtahunan=$bar['nilai'];
        }else{*/
            #jika bukan maybank, jumlah hari pertahun ambil dari setup daftar bank
            $str="select jumlah_hari2 from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where noakun='".$noakun."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            $jmlhtahunan=$bar['jumlah_hari2'];
        // }   
        
        $totjumlahhari=0;
        if ($jangkawaktu>0) {
            #jika >=sebulan
            $lstData=array();
            for($blndt=1;$blndt<=$jangkawaktu;$blndt++){
                if($blndt==1){
                    $dt1 = strtotime($tglvaluta);
                    $tgljatuhtempo=date('Y-m-d', strtotime('+1 month', $dt1));
                    $dt2 = strtotime($tgljatuhtempo);
                    $diff = abs($dt2-$dt1);
                    $jmlhhari = (($diff/86400));  
                    @$jumlahbunga=$jmlhdeposito*($sukubunga/100)*($jmlhhari/$jmlhtahunan); 
                    @$jumlahpajak=$jumlahbunga*0.2; 
                    $lstData[$tglvaluta]['jumlahbunga']=$jumlahbunga;
                    $lstData[$tglvaluta]['jumlahpajak']=$jumlahpajak;
                    $lstData[$tglvaluta]['tgljatuhtempo']=$tgljatuhtempo;
                    $lstData[$tglvaluta]['jumlahhari']=$jmlhhari;
                    $tglvaluta=$tgljatuhtempo;
                }else{
                    $dt1 = strtotime($tglvaluta);
                    $tgljatuhtempo=date('Y-m-d', strtotime('+1 month', $dt1));
                    $dt2 = strtotime($tgljatuhtempo);
                    $diff = abs($dt2-$dt1);
                    $jmlhhari = (($diff/86400)); 
                    @$jumlahbunga=$jmlhdeposito*($sukubunga/100)*($jmlhhari/$jmlhtahunan); 
                    @$jumlahpajak=$jumlahbunga*0.2; 
                    $lstData[$tglvaluta]['jumlahbunga']=$jumlahbunga;
                    $lstData[$tglvaluta]['jumlahpajak']=$jumlahpajak;
                    $lstData[$tglvaluta]['tgljatuhtempo']=$tgljatuhtempo;
                    $lstData[$tglvaluta]['jumlahhari']=$jmlhhari;
                    $tglvaluta=$tgljatuhtempo;
                    // $tglvaluta=date('Y-m-d', strtotime('+1 Day', strtotime($tgljatuhtempo)));
                }
                $totjumlahhari+=$jmlhhari;
            }
        }

        #-jika jangka waktu deposito < sebulan
        #-jika tanggal jatuh tempo tidak tepat 1 bulan
        $sisajumlahhari=$jumlahhariht-$totjumlahhari;
        if ($sisajumlahhari>0) {
            $dt1 = strtotime($tglvaluta);
            $dt2 = strtotime($tglHisJatuhTempo);
            $diff = abs($dt2-$dt1);
            $jmlhhari = $diff/86400; 
            $jumlahbunga=$jmlhdeposito*($sukubunga/100)*($jmlhhari/$jmlhtahunan); 
            $jumlahpajak=$jumlahbunga*0.2; 
            $lstData[$tglvaluta]['jumlahbunga']=$jumlahbunga;
            $lstData[$tglvaluta]['jumlahpajak']=$jumlahpajak;
            $lstData[$tglvaluta]['tgljatuhtempo']=$tglHisJatuhTempo;
            $lstData[$tglvaluta]['jumlahhari']=$jmlhhari;
        }


        // $jmlhhari = $jmlhhari+1;
        // echo "<pre>";
        // print_r($lstData);
        // echo "</pre>";
        // exit('warning');

        if(count($lstData)!=0){
            foreach ($lstData as $key => $val) {
                $str = "insert into ".$dbname.".keu_depositodt (notransaksi,tglcair,tglterima,jumlahbunga,jumlahpajak,tglvaluta,tgljatuhtempo)
                        values ('".$notransaksi."','".$val['tgljatuhtempo']."','".$val['tgljatuhtempo']."','".$val['jumlahbunga']."','".$val['jumlahpajak']."','".$key."','".$val['tgljatuhtempo']."')";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }  
            }

            $strht = "update ".$dbname.".keu_depositoht set posting='1' where notransaksi='".$notransaksi."'";
            try{ $owlPDO->exec($strht); }
            catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
            }
        } 
        
    break;

    case 'closed':

        $strht = "update ".$dbname.".keu_depositoht set statusclose='1' where notransaksi='".$notransaksi."'";
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

    case 'viewdetail':

        $strht="select * from ".$dbname.".keu_depositoht where notransaksi='".$notransaksi."'";
        $resht= $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht = $resht->fetch();
        $nobilyet=$barht['nobilyet'];
        $jnsdeposito=$barht['jnsdeposito'];
        $status=$barht['status'];

        $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun=".$barht['noakun']." order by namabank asc";
        $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
        $resak->setFetchMode(PDO::FETCH_ASSOC);
        $barak=$resak->fetch();

        $whrpt="kodeorganisasi='".substr($barht['notransaksi'],0,3)."'";
        $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
        $tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable>
            <thead>
            <tr class=rowcontent>    
                <td rowspan=3><b>".strtoupper($_SESSION['lang']['pt'])."</b></td>
                <td rowspan=3> : </td>
                <td rowspan=3>".$optpt[substr($barht['notransaksi'],0,3)]."</td>
                <td><b>".strtoupper($_SESSION['lang']['namabank'])."</b></td>
                <td> : </td>
                <td>".$barak['namabank']."</td>
                <td><b>".strtoupper($_SESSION['lang']['tipetransaksi'])."</b></td>
                <td> : </td>
                <td>".$arrjenis[$jnsdeposito]."</td>
            </tr>
            <tr class=rowheader>    
                <td><b>".strtoupper($_SESSION['lang']['matauang'])."</b></td>
                <td> : </td>
                <td>IDR</td>
                <td><b>".strtoupper($_SESSION['lang']['nourut'])." BILYET</b></td>
                <td> : </td>
                <td>".$barht['nobilyet']."</td>
            </tr>
            <tr class=rowheader>    
                <td><b>".strtoupper($_SESSION['lang']['nourut'].$_SESSION['lang']['rekening'])."</b></td>
                <td> : </td>
                <td>".$barak['rekening']."</td>
                <td><b>".strtoupper($_SESSION['lang']['nourut'])." DEPOSITO</b></td>
                <td> : </td>
                <td>".$barht['nodeposito']."</td>
            </tr>
            <tr class=rowheader>    
                <td><b>".strtoupper($_SESSION['lang']['tanggalvaluta'])."</b></td>
                <td> : </td>
                <td>".tanggalnormal($barht['tglvaluta'])."</td>
                <td><b>".strtoupper($_SESSION['lang']['tanggaljatuhtempo'])."</b></td>
                <td> : </td>
                <td>".tanggalnormal($barht['tgljatuhtempo'])."</td>
                <td><b>".strtoupper($_SESSION['lang']['jangkawaktu'])."</b></td>
                <td> : </td>
                <td>".$barht['jangkawaktu']." ".$_SESSION['lang']['bulan']."</td>
            </tr>
            <tr class=rowheader>    
                <td><b>".strtoupper($_SESSION['lang']['status'])."</b></td>
                <td> : </td>
                <td>".$arrstatus[$barht['status']]."</td>
                <td><b>".strtoupper($_SESSION['lang']['sukubunga'])."</b></td>
                <td> : </td>
                <td>".$barht['sukubunga']." %/Tahun</td>
                <td><b>".strtoupper($_SESSION['lang']['jumlahdeposito'])."</b></td>
                <td> : </td>
                <td>".number_format($barht['jmlhdeposito'])."</td>
            </tr>
            </thead>
            </table><br><br>";   

        $optstatclose.="<option value='0'>Open</option>";
        $optstatclose.="<option value='1'>Close</option>";

        $tglcair='';
        $tglterima='';
        $str="select * from ".$dbname.".keu_depositodt where notransaksi='".$notransaksi."'";
        $res= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();  
             
        $tab.="<fieldset><legend>".$_SESSION['lang']['detail']."</legend>
                <table border=0 style='float:left;'>";
        $tab.="</tr>  
                <td>".$_SESSION['lang']['tglpencairanbunga']."</td>
                <td>:</td>
                <td><input type=text class=myinputtext id=tglcair onmousemove=setCalendar(this.id) style=width:150px; maxlength=10 disabled/></td>
                <td>".$_SESSION['lang']['tglpenerimaanbunga']."</td>
                <td>:</td>
                <td><input type=text class=myinputtext id=tglterima onmousemove=setCalendar(this.id) style=width:150px; maxlength=10 disabled/></td>
                <td>".$_SESSION['lang']['close']." </td>
                <td>:</td>
                <td><select id=statusclose style=width:150px; disabled>".$optstatclose."</select></td>
             </tr>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['jumlahbunga']."</td>
                <td>:</td>
                <td><input type=text id=jumlahbunga class=myinputtextnumber style=width:150px; disabled ></td>
                <td>".$_SESSION['lang']['jumlahpajak']."</td>
                <td>:</td>
                <td><input type=text id=jumlahpajak class=myinputtextnumber style=width:150px; disabled ></td>
             </tr>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['jumlahpenalti']."</td>
                <td>:</td>
                <td><input type=text id=jumlahpenalti class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('jumlahpenalti',2)\" disabled></td>
                <td>".$_SESSION['lang']['total']."</td>
                <td>:</td>
                <td><input type=text id=total class=myinputtextnumber style=width:150px; disabled ></td>
             </tr>";

        /*$tab.="<tr>
                <td>".$_SESSION['lang']['jumlahpenalti']."</td>
                <td>:</td>
                <td><input type=text id=jumlahpenalti class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event);\" value='".number_format($bar['jumlahpenalti'],2)."' ".$disabled."></td>
                <td>".$_SESSION['lang']['realisasi']."</td>
                <td>:</td>
                <td><input type=text id=realisasi class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event);\" value='".number_format($bar['realisasi'],2)."' disabled></td>
             </tr>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['total']."</td>
                <td>:</td>
                <td><input type=text id=total class=myinputtextnumber style=width:150px; disabled value='".number_format($total,2)."'></td>
                <td>Variance</td>
                <td>:</td>
                <td><input type=text id=variance class=myinputtextnumber style=width:150px; disabled value='".number_format($variance,2)."'></td>
             </tr>";*/
        $tab.="<tr >
                <td></td><td></td>
                <td colspan=2>
                    <input type=hidden id=tglvalutadetail value=''/>
                    <button class=mybutton onclick=getjumlah('".$notransaksi."')>".$_SESSION['lang']['jumlah']."</button>&nbsp;
                    <button class=mybutton onclick=saveDatadt('".$notransaksi."')>".$_SESSION['lang']['save']."</button>&nbsp;
                    <button class=mybutton onclick=clearDatadt('".$status."')>".$_SESSION['lang']['cancel']."</button>
                </td>
             </tr>
             <input type=hidden id=methoddt value=''/>
             </table></fieldset><br<br>";

        $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['notransaksi']." Kasbank</td>";
        $tab.="<td>".$_SESSION['lang']['tglpencairanbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['tglpenerimaanbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahpajak']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahpenalti']."</td>";
        $tab.="<td>".$_SESSION['lang']['total']."</td>";
        $tab.="<td>".$_SESSION['lang']['realisasi']."</td>";
        $tab.="<td>Variance</td>";
        $tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody >";

        $no=0;
        $str = "select * from ".$dbname.".keu_depositodt where notransaksi='".$notransaksi."' order by tglterima desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $no+=1;

            $total=$bar['jumlahbunga']-$bar['jumlahpajak']-$bar['jumlahpenalti'];
            $variance=$total-$bar['realisasi'];

            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['notranskasbank']."</td>
                <td align=center>".tanggalnormal($bar['tglcair'])."</td>
                <td align=center>".tanggalnormal($bar['tglterima'])."</td>
                <td align=right>".number_format($bar['jumlahbunga'],2)."</td>
                <td align=right>".number_format($bar['jumlahpajak'],2)."</td>
                <td align=right>".number_format($bar['jumlahpenalti'],2)."</td>
                <td align=right>".number_format($total,2)."</td>
                <td align=right>".number_format($bar['realisasi'],2)."</td>
                <td align=right>".number_format($variance,2)."</td>";
                if ($bar['posting']==0){
                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdetail('".$bar['notransaksi']."','".tanggalnormal($bar['tglvaluta'])."','".tanggalnormal($bar['tglcair'])."','".tanggalnormal($bar['tglterima'])."','".number_format($bar['jumlahbunga'],2)."','".number_format($bar['jumlahpajak'],2)."','".number_format($bar['jumlahpenalti'],2)."','".number_format($total,2)."','".$bar['statusclosedt']."')\"></td>";
                    //$tab.="<td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldetail('" . $bar['notransaksi']. "','".tanggalnormal($bar['tglvaluta'])."');\" ></td>";
                    $tab.="<td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"postingdetail('".$bar['notransaksi']."','".tanggalnormal($bar['tglvaluta'])."');\" ></td>";
                }else{
                    $tab.="<td align=center colspan=2><img src=images/icons/04/16/02.png class=resicon  title='Posted'></td>";
                }
            $tab.="</tr>";
        }

        $tab.="</tbody>";
        $tab.="</table></fieldset>";

        echo $tab;
        break;

    case 'getjumlah':

        $str="select * from ".$dbname.".keu_depositoht where notransaksi='".$notransaksi."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $sukubunga=$bar['sukubunga'];
        $jmlhdeposito=$bar['jmlhdeposito'];
        $noakun=$bar['noakun'];
        $status=$bar['status'];
        
        /*#cek apakah bank maybank
        $strbrs="select count(*) as jumlahbaris from ".$dbname.".setup_parameterappl where kodeparameter='DPBM' and nilai like '%".$noakun."%'";
        $resbrs= $owlPDO->query($strbrs) or die(print " Gagal: " . PDOException::getMessage());
        $resbrs->setFetchMode(PDO::FETCH_ASSOC);
        $barbrs= $resbrs->fetch();
        if ($barbrs['jumlahbaris']>0) {
            #jika maybank, jumlah hari pertahun ambil dari parameter appl
            $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='DPHR'";
            $res= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar= $res->fetch();
            $jmlhtahunan=$bar['nilai'];
        }else{*/
            #jika bukan maybank, jumlah hari pertahun ambil dari setup daftar bank
            $str="select jumlah_hari2 from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where noakun='".$noakun."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            $jmlhtahunan=$bar['jumlah_hari2'];
        // }

        $dt1 = strtotime($tglvaluta);
        $dt2 = strtotime($tglcair);
        $diff = abs($dt2-$dt1);
        $jmlhhari = $diff/86400; 
        $jumlahbunga=$jmlhdeposito*($sukubunga/100)*($jmlhhari/$jmlhtahunan); 
        $jumlahpajak=$jumlahbunga*0.2; 
        $total=$jumlahbunga-$jumlahpajak-$jumlahpenalti; 

        echo number_format($jumlahbunga,2)."####".number_format($jumlahpajak,2)."####".number_format($total,2);

    break;

    case 'insertdetail':

        $str = "insert into ".$dbname.".keu_depositodt (notransaksi,tglcair,tglterima,jumlahbunga,jumlahpajak,jumlahpenalti,statusclosedt)
                values ('".$notransaksi."','".$tglcair."','".$tglterima."','".$jumlahbunga."','".$jumlahpajak."','".$jumlahpenalti."','".$statusclose."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

    break;

    case 'updatedetail':

        $str="update ".$dbname.".keu_depositodt set tglterima='".$tglterima."',tglcair='".$tglcair."',jumlahbunga='".$jumlahbunga."',jumlahpajak='".$jumlahpajak."',jumlahpenalti='".$jumlahpenalti."',statusclosedt='".$statusclose."' where notransaksi='".$notransaksi."' and tglvaluta='".$tglvaluta."'";
        try{
            $owlPDO->exec($str); 

            if ($statusclose==1) {
                $strclose="update ".$dbname.".keu_depositodt set statusclosedt='".$statusclose."', posting='1' where notransaksi='".$notransaksi."' and tglvaluta>'".$tglvaluta."'";
                try{
                    $owlPDO->exec($strclose); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }

        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

    break;

    case'deldetail':

        $strdt = "delete from ".$dbname.".keu_depositodt where notransaksi='".$notransaksi."' and tglvaluta='".$tglvaluta."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'postingdetail':
        $strht = "update ".$dbname.".keu_depositodt set posting='1' where notransaksi='".$notransaksi."' and tglvaluta='".$tglvaluta."'";             
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

    case 'showupload':
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup'>
            <tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td>
                    <label id='notransupload' style='font-weight:bold'>".$notransaksi."</label>
                </td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' class=mybutton>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=\"submitfile()\">Submit</button>
                </td>
            </tr>
        </table>
        <p />";
        
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
        
        echo $tab;
    break;
    
    case 'submitfile':
        $tgl = date("YmdHis");
        $data = $_POST;
        
        if($data['fileupload']!='')
        {
            if($_FILES['file']['error']==0)
            {
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = $_FILES['file']['tmp_name'];        
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
                {
                    if($_FILES['file']['size'] <= 250000)
                    {
                        $str = "insert into ".$dbname.".listfileupload values ('','".$data['notransaksi']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        try
                        {
                            $owlPDO->exec($str);
                            move_uploaded_file($file_tmpname,"fileupload/deposito/$filename");
                        }
                        catch(PDOException $e)
                        {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                    else
                    {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                }
            }
        }
    break;
    
    case 'loadfiles':
        $no = 0;
        $tab = "";
        $str="select * from ".$dbname.".keu_depositoht where notransaksi = '".$notransaksi."'";
        $resv=fetchData($str);
        foreach($resv as $bar => $barv){
            $close = $barv['close'];    
        }
        
        $str="select * from ".$dbname.".listfileupload where notransaksi = '".$notransaksi."' and status='1'";
        $res=fetchData($str);
        if(empty($res))
        {
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }
        else
        {
            foreach($res as $key=>$val)
            {
                $no++;
                $tab.="<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                    
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.png')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.pdf')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }
                else
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                    $tab.="<td style='text-align:left'>".$val['namafile']."</td>
                        <td align=center>
                        <a href='fileupload/deposito/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($close==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$notransaksi."','".$val['namafile']."');\" >";
                }
                $tab."</td>
                </tr>";
            }   
        }
        echo $tab;
    break;
    
    case 'deletefile':
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
        try
        {
            $owlPDO->exec($str);
            $path = "fileupload/deposito/".$namafile;
            unlink($path);
        }
        catch(PDOException $e)
        {
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
	
	default:
		# code...
		break;
}


?>