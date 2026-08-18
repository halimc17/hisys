<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$unit = checkPostGet('unit','');
$periode = checkPostGet('periode','');
$tipe = checkPostGet('tipe','');
$noakun = checkPostGet('noakun','');
$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$jumlahtax = checkPostGet('jumlahtax','');
$npwp = checkPostGet('npwp','');
$viewtipe = checkPostGet('viewtipe','');
$tglposting = tanggalsystemn(checkPostGet('tglposting',''));
$tanggaldari = tanggalsystemn(checkPostGet('tanggaldari',''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai',''));
$createTime=date("Y-m-d H:i:s");
$status = array(1=>'Unpaid',2=>'SPT',3=>'Nihil');
$kodejurnal="VIVO";
$tipe = checkPostGet('tipe','');
$path   = "fileupload/vatin_vatout/";

#akun Vin Vout
$sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='VIVO'";
$rappl=fetchData($sappl);
$noakunvv=$rappl[0]['nilai'];
$noakunvv=explode(',', $noakunvv);
$noakunIn=$noakunvv[0];
$noakunOut=$noakunvv[1];

#nama akun Vin
$wheredz=" noakun='".$noakunIn."' ";
$optnamain=makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredz);

#nama akun Vout
$wheredz=" noakun='".$noakunOut."' ";
$optnamaout=makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredz);


$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
// echo $str;exit();
$res=fetchdata($str);
foreach($res as $bar){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
}


$str="select * from ".$dbname.".log_5supplier where status='1'";
$res=fetchdata($str);
foreach($res as $bar){
	$namasupcust[$bar['supplierid']]=$bar['namasupplier'];
}

$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$namasupcust[$bar['kodecustomer']]=$bar['namacustomer'];
}



switch ($method) {

    case 'getperiode':
	    $arrnpwp=$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	    $str = "select distinct periode from ".$dbname.".keu_jurnaldt_vw where kodeorg='".$unit."' order by periode desc limit 12 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
	        if ($periode==$bar['periode']){
	            $optperiode.="<option value='".$bar['periode']."' selected>".$bar['periode']."</option>";
	        }else{
	            $optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
	        }
	    }

        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $induk=$bar['induk'];

        # Options
        $str="select npwp from ".$dbname.".setup_org_npwp where kodeorg='".$induk."' and status=1";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            if($npwp==$bar['npwp']){ 
                $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
            }else{
                if ($bar['defaults']=1) {
                    $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
                }else{
                    $arrnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
                }
            }
        }

        echo $optperiode."####".$arrnpwp;

    break;

    case 'getunit':
        $arrnpwp=$arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $lstUnit=getOrgDetail(1);
        $dtMul=0;
        $listOrg='';
        foreach($lstUnit as $row=>$isiDt){
            if(substr($row,0,5)=='Pilih'){
                continue;
            }
            if($dtMul==0){
                $listOrg="'".$row."'";
                $dtMul=1;
            }else{
                $listOrg.=",'".$row."'";
            }
        }

        # Options
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kdpt']."' and kodeorganisasi in (".$listOrg.")";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            if($param['kodeunit']==$bar['kodeorganisasi']){ 
                $arrUnit.="<option value='".$bar['kodeorganisasi']."' selected>".$bar['namaorganisasi']."</option>";
            }else{
                $arrUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";    
            }
            
        }

        # Options
        $str="select npwp from ".$dbname.".setup_org_npwp where kodeorg='".$param['kdpt']."' and status=1";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            if($param['npwp']==$bar['npwp']){ 
                $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
            }else{
                if ($bar['defaults']=1) {
                    $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
                }else{
                    $arrnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
                }
            }
        }

        echo $arrUnit."####".$arrnpwp;
    break;

    case 'showlistdata':
        $arrStat=array("1"=>"","3"=>"Nihil");
        $optstatus="<option value='1'></option>";
        $optstatus.="<option value=3>Nihil</option>";
        $periode1=date('Y-m', strtotime('-3 month', strtotime($periode)));

        switch ($tipe) {
            case '1':
                $lang=$_SESSION['lang']['supplier'];
                $noakun=$noakunIn;
                $namaakun=$optnamain[$noakunIn];
                $str="select a.tanggal as tglGL,a.noreferensi as noinv,a.kodeorg as kodeorg,a.noakun as coagl,b.tanggal as tglinv,a.kodesupplier,b.tanggalnofp  as tglnsfp,
				b.nofp as nsfp,b.historynofp as hisnsfp,b.historytanggalfp as historytanggalfp,a.jumlah,b.nopo as nodok
				from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".keu_tagihanht b on a.noreferensi=b.noinvoice 
				where a.noakun ='".$noakun."' and a.jumlah>0 and b.kodeorg='".$kodept[$unit]."' and
				b.tanggal between '".$tanggaldari."' and '".$tanggalsampai."' and b.npwp='".$npwp."' order by b.nofp asc";
				
				/*
				   $str="select a.tanggal as tglGL,a.noreferensi as noinv,a.noakun as coagl,b.tanggal as tglinv,a.kodesupplier,b.tanggalnofp  as tglnsfp,
				b.nofp as nsfp,b.historynofp as hisnsfp,b.historytanggalfp as historytanggalfp,a.jumlah 
				from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".keu_tagihanht b on a.noreferensi=b.noinvoice 
				where a.noakun ='".$noakun."' and a.jumlah>0 and a.kodeorg='".$unit."' and
				b.tanggal between '".$tanggaldari."' and '".$tanggalsampai."' and b.npwp='".$npwp."' order by b.nofp asc";
				*/
				// echo $str;
			break;

            case '2':
                #pengecekan jika data sudah tersimpan tidak ditampilankan kembali
                $scek="select nofakturawal,nofakturakhir from ".$dbname.".keu_fakturpajakht where npwp='".$npwp."'";
                $sres=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $sres->setFetchMode(PDO::FETCH_ASSOC);
                $sbar=$sres->fetch();
                $noawal=$sbar['nofakturawal'];
                $noakhir=$sbar['nofakturakhir'];

                $lang=$_SESSION['lang']['customer'];
                $noakun=$noakunOut;
                $namaakun=$optnamaout[$noakunOut];
                //select distinct a.tanggal as tglGL,b.noinvoice as noinv,a.noakun as coagl,b.tanggal as tglinv,b.kodecustomer as kodesupplier,b.tanggal as tglnsfp,b.nofakturpajak as nsfp,(a.jumlah*(-1)) as jumlah,c.berikat from keu_jurnaldt_vw a left join keu_penagihanht b on a.noreferensi=b.noinvoice left join pmn_kontrakjual c on b.nokontrak=c.nokontrak where a.noakun='2130601' and a.noreferensi in (select noinvoice from keu_penagihanht where kodeorg='APHO' and tanggal between '2018-04-01' and '2018-05-28')
                $str="select distinct a.tanggal as tglGL,a.kodeorg as kodeorg,b.noinvoice as noinv,a.noakun as coagl,b.tanggal as tglinv,
					b.kodecustomer as kodesupplier,b.tanggal  as tglnsfp,b.nofakturpajak as nsfp,(a.jumlah*(-1)) as jumlah,
					c.berikat,b.nilaiinvoice as nilinv,b.nokontrak as nodok from ".$dbname.".keu_jurnaldt_vw a 
                    left join ".$dbname.".keu_penagihanht b on a.noreferensi=b.noinvoice 
                    left join ".$dbname.".pmn_kontrakjual c on b.nokontrak=c.nokontrak 
                    where a.noakun='".$noakun."' and a.noreferensi in (select noinvoice from ".$dbname.".keu_penagihanht where 
					kodept='".$kodept[$unit]."' and tanggal between '".$tanggaldari."' and '".$tanggalsampai."') order by b.nofakturpajak asc ";
					
				/*
				 $str="select distinct a.tanggal as tglGL,b.noinvoice as noinv,a.noakun as coagl,b.tanggal as tglinv,
					b.kodecustomer as kodesupplier,b.tanggal  as tglnsfp,b.nofakturpajak as nsfp,(a.jumlah*(-1)) as jumlah,
					c.berikat,b.nilaiinvoice as nilinv from ".$dbname.".keu_jurnaldt_vw a 
                    left join ".$dbname.".keu_penagihanht b on a.noreferensi=b.noinvoice 
                    left join ".$dbname.".pmn_kontrakjual c on b.nokontrak=c.nokontrak 
                    where a.noakun='".$noakun."' and a.noreferensi in (select noinvoice from ".$dbname.".keu_penagihanht where 
					kodeorg='".$unit."' and tanggal between '".$tanggaldari."' and '".$tanggalsampai."') order by b.nofakturpajak asc ";
				*/	
				break;
        }
       
    	$data.="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['detail']."</b></legend>";
        $data.="<div id='printContainer' style='overflow-x:hidden;height:400px;'>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<button class=mybutton onclick=adddetail('".$periode."','".$unit."','".$noakun."','".$npwp."')>".$_SESSION['lang']['addtodetail']."</button>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['nourut']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggalinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['unit']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggal'].". GL</td>";
        $data.="<td>COA. GL</td>";
        $data.="<td>Tgl. NSFP</td>";
        $data.="<td>Exp. Date NSFP</td>";
        $data.="<td>No. NSFP</td>";
        $data.="<td>".$_SESSION['lang']['nodok']."</td>";
        $data.="<td colspan=2>".$lang."</td>";
        $data.="<td>DPP</td>";
        $data.="<td>".$_SESSION['lang']['ppn']."</td>";
        $data.="<td>".$_SESSION['lang']['jenispajak']."</td>";
        $data.="<td>".$_SESSION['lang']['total']."</td>";
        $data.="<td>".$_SESSION['lang']['status']."</td>";
        $data.="<td><input type='checkbox' id='btnall' onclick='checkAll()'></td>";
        $data.="</tr></thead>";

        $no=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

            #pengecekan jika data sudah tersimpan tidak ditampilankan kembali
			$scek="select * from ".$dbname.".tax_vatin_vatout where noinvoice='".$bar['noinv']."'";
			$rcek=fetchData($scek);
			if(count($rcek)==1){
				continue;
			}
            $dpp=0;
            #pengecekan untuk vat out, pada kontrak berikat atau tidak
            $status='1';
            if($tipe==2){
                $bar['tglnsfp']=$bar['tglinv'];
                if (intval($bar['berikat'])==1) {
                    $status=3;
                }else{
                    $status=1;    
                }
            } 
            

            
			$total=0;
            if(intval($bar['berikat'])==1){
                $dpp=$bar['nilinv'];
            }else{
                $dpp=$bar['jumlah']*10;
            }
			 
			$total=$dpp+$bar['jumlah'];
			$ekstglnsfp=date('Y-m-d', strtotime('+3 month', strtotime($bar['tglnsfp'])));
            #untuk sementara dipasang, agar yang terlihat data yang dah oke
            if(is_null($bar['tglinv'])){
                continue;
            }
            if($tipe!=2){
                if($bar['historytanggalfp']!='0000-00-00'){
                    $bar['tglnsfp']=$bar['historytanggalfp'];
                }
                if($bar['hisnsfp']!=''){
                    $bar['nsfp']=$bar['hisnsfp'];
                }
            }

            if ($bar['nsfp']=='') {
                continue;
            }

			$no++;
		    $brt="style=cursor:pointer";
		    $data.="<tr ".$brt." class=rowcontent>";
		    $data.="<td style=cursor:pointer>".$no."</td>";
		    $data.="<td style=cursor:pointer align=left id='tglinvoice_".$no."'>".tanggalnormal($bar['tglinv'])."</td>";
		    $data.="<td style=cursor:pointer id='noinvoice_".$no."'>".$bar['noinv']."</td>";
		    $data.="<td>".$bar['kodeorg']."</td>";
		    $data.="<td style=cursor:pointer align=left id='tglgL_".$no."'>".tanggalnormal($bar['tglGL'])."</td>";
		    $data.="<td style=cursor:pointer id='coagl_".$no."'>".$bar['coagl']."</td>";
		    $data.="<td style=cursor:pointer align=left id='tglnsfp_".$no."'>".tanggalnormal($bar['tglnsfp'])."</td>";
		    $data.="<td style=cursor:pointer align=left id='ekstglnsfp_".$no."'>".tanggalnormal($ekstglnsfp)."</td>";
		    $data.="<td style=cursor:pointer id='nsfp_".$no."' onclick=checkfp('".$bar['nsfp']."')>".$bar['nsfp']."</td>";
		    $data.="<td style=cursor:pointer>".$bar['nodok']."</td>";
		    $data.="<td style=cursor:pointer id='kodesupplier_".$no."'>".$bar['kodesupplier']."</td>";
		    $data.="<td style=cursor:pointer>".$namasupcust[$bar['kodesupplier']]."</td>";
		    $data.="<td style=cursor:pointer align=right id='dpp_".$no."'>".$dpp."</td>";
		    $data.="<td style=cursor:pointer align=right id='ppn_".$no."'>".$bar['jumlah']."</td>";
		    $data.="<td style=cursor:pointer id='jnspajak_".$no."'>".$namaakun."</td>";
		    $data.="<td style=cursor:pointer align=right id='total_".$no."'>".$total."</td>";
		    if ($tipe==1) {
		    	$data.="<td style=cursor:pointer><select id='status_".$no."' style=width:150px; >".$optstatus."</select></td>";
		    }
            if ($tipe==2){
                $data.="<td style=cursor:pointer><input type=hidden id='status_".$no."' class=myinputtext readonly style=width:150px; value='".$status."'><input type=text id='statusVwData_".$no."' class=myinputtext readonly style=width:150px; value='".$arrStat[$status]."'></td>";
            }
		    $data.="<td style=cursor:pointer><input type='checkbox' id='no_".$no."'></td></tr>";

		}	
		$data.="<input type=hidden id=totrow value=".$no."></tbody></table>";

		$data.="</table></div></fieldset>";
		echo $data;
		
    break;
    case 'showlistdata2':
        $addWhr='';
        // $optstatus="<option value='1'></option>";
        // $optstatus.="<option value=3>Nihil</option>";
        $arrStatus=array("1"=>"","3"=>"Nihil");
        $periode1=date('Y-m', strtotime('-3 month', strtotime($periode)));
        if($_POST['noinvoice']!=''){
            $addWhr.=" and noinvoice='".$_POST['noinvoice']."'";
        }
        if($_POST['supplierId']!=''){
            $addWhr.=" and kodesupplier='".$_POST['supplierId']."'";
        }
        switch ($tipe) {
            case '3':
                $lang=$_SESSION['lang']['supplier'];
                $noakun=$noakunIn;
                $namaakun=$optnamain[$noakunIn];
                $str="select tglgl as tglGL,noinvoice as noinv,noakun as coagl,tglinvoice as tglinv,kodesupplier, tgl_fakturpajak as tglnsfp, nofakturpajak as nsfp,nilai as jumlah,status from ".$dbname.".tax_vatin_vatout where noakun='".$noakun."' and npwp='".$npwp."' and unit='".$unit."' and periode between '".$periode1."' and '".$periode."' and posting=1 ".$addWhr;
                // exit('warning : '.$str);
            break;

            case '5':
                $lang=$_SESSION['lang']['customer'];
                $noakun=$noakunOut;
                $namaakun=$optnamaout[$noakunOut];
                $str="select tglgl as tglGL,noinvoice as noinv,noakun as coagl,tglinvoice as tglinv,kodesupplier,
                      tgl_fakturpajak as tglnsfp,nofakturpajak as nsfp,nilai as jumlah,status from ".$dbname.".tax_vatin_vatout where noakun='".$noakun."' and unit='".$unit."' and npwp='".$npwp."' and periode between '".$periode1."' and '".$periode."' and posting=1 ".$addWhr;
            break;
        }

        $data.="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['detail']."</b></legend>";
        $data.="<div id='printContainer' style='overflow-x:hidden;height:400px;'>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<button class=mybutton onclick=adddetail('".$periode."','".$unit."','".$noakun."','".$npwp."')>".$_SESSION['lang']['addtodetail']."</button>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['nourut']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggalinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggal'].". GL</td>";
        $data.="<td>COA. GL</td>";
        $data.="<td>Tgl. NSFP</td>";
        $data.="<td>Exp. Date NSFP</td>";
        $data.="<td>No. NSFP</td>";
        $data.="<td>".$lang."</td>";
        $data.="<td>DPP</td>";
        $data.="<td>".$_SESSION['lang']['ppn']."</td>";
        $data.="<td>".$_SESSION['lang']['jenispajak']."</td>";
        $data.="<td>".$_SESSION['lang']['total']."</td>";
        $data.="<td><input type='checkbox' id='btnall' onclick='checkAll()'></td>";
        $data.="</tr></thead>";

        $no=0;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){

            #pengecekan untuk vat out, pada kontrak berikat atau tidak
            $status='';
            if ($tipe==2) {
                if ($bar['berikat']==1) {
                    $status=3;
                    $bar['tglnsfp']=$bar['tglinv'];
                }else{
                    $status=1;
                }
            }


            $dpp=0;
            $total=0;
            $dpp=$bar['jumlah']*10;
            $total=$dpp+$bar['jumlah'];
            $ekstglnsfp=date('Y-m-d', strtotime('+3 month', strtotime($bar['tglnsfp'])));
            #untuk sementara dipasang, agar yang terlihat data yang dah oke
            if(is_null($bar['tglinv'])){
                continue;
            }

            $no++;
            $brt="style=cursor:pointer";
            $data.="<tr ".$brt." class=rowcontent>";
            $data.="<td style=cursor:pointer>".$no."</td>";
            $data.="<td style=cursor:pointer align=left id='tglinvoice_".$no."'>".tanggalnormal($bar['tglinv'])."</td>";
            $data.="<td style=cursor:pointer id='noinvoice_".$no."'>".$bar['noinv']."</td>";
            $data.="<td style=cursor:pointer align=left id='tglgL_".$no."'>".tanggalnormal($bar['tglGL'])."</td>";
            $data.="<td style=cursor:pointer id='coagl_".$no."'>".$bar['coagl']."</td>";
            $data.="<td style=cursor:pointer align=left id='tglnsfp_".$no."'>".tanggalnormal($bar['tglnsfp'])."</td>";
            $data.="<td style=cursor:pointer align=left id='ekstglnsfp_".$no."'>".tanggalnormal($ekstglnsfp)."</td>";
            $data.="<td style=cursor:pointer id='nsfp_".$no."' onclick=checkfp('".$bar['nsfp']."')>".$bar['nsfp']."</td>";
            $data.="<td style=cursor:pointer id='kodesupplier_".$no."'>".$bar['kodesupplier']."</td>";
            $data.="<td style=cursor:pointer align=right id='dpp_".$no."'>".$dpp."</td>";
            $data.="<td style=cursor:pointer align=right id='ppn_".$no."'>".$bar['jumlah']."</td>";
            $data.="<td style=cursor:pointer id='jnspajak_".$no."'>".$namaakun."</td>";
            $data.="<td style=cursor:pointer align=right id='total_".$no."'>".$total."</td>";
            $data.="<input type=hidden id='status_".$no."' value='4'>";
            $data.="<td style=cursor:pointer><input type='checkbox' id='no_".$no."'></td></tr>";

        }   
        $data.="<input type=hidden id=totrow value=".$no."></tbody></table>";

        $data.="</table></div></fieldset>";
        echo $data;
        
    break;

    case'adddetail':

        #pembentukan notransaksi
        $notransaksi=str_replace('-', '', $periode).$unit;

        $scek="select * from ".$dbname.".tax_vatin_vatout where notransaksi='".$notransaksi."' and posting=1";
        $rcek=fetchData($scek);
        if(count($rcek)>0){
            exit('Warning : Pada periode '.$periode.' unit '.$unit.' sudah ada transaksi yang di posting.');
        }

        #insert data
        $sDet="insert into ".$dbname.".tax_vatin_vatout (notransaksi,periode,unit,npwp,noinvoice,tglinvoice,tglgl,noakun,nofakturpajak,tgl_fakturpajak,kodesupplier,nilai,createdby,updateby,createtime,status) values ";
        for($arDt=0;$arDt<$_POST['totrow'];$arDt++){

        	#delete data
			$sdel="delete from ".$dbname.".tax_vatin_vatout where notransaksi='".$notransaksi."' and noinvoice='".$_POST['noinvoice'][$arDt]."'";
			try{
				$owlPDO->exec($sdel); 
			}catch (PDOException $e) {
				print " error: code 1125\n: " . $e->getMessage() . "<br/>"; die(); 
			}

			#insert data
            if($arDt==0){
                $sDet.=" ('".$notransaksi."','".$periode."','".$unit."','".$npwp."','".$_POST['noinvoice'][$arDt]."','".tanggalsystem($_POST['tglinvoice'][$arDt])."','".tanggalsystem($_POST['tglgL'][$arDt])."','".$noakun."','".$_POST['nsfp'][$arDt]."','".tanggalsystem($_POST['tglnsfp'][$arDt])."','".$_POST['kodesupplier'][$arDt]."','".str_replace(',', '', $_POST['ppn'][$arDt])."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$createTime."','".$_POST['status'][$arDt]."')";
            }else{
                $sDet.=",('".$notransaksi."','".$periode."','".$unit."','".$npwp."','".$_POST['noinvoice'][$arDt]."','".tanggalsystem($_POST['tglinvoice'][$arDt])."','".tanggalsystem($_POST['tglgL'][$arDt])."','".$noakun."','".$_POST['nsfp'][$arDt]."','".tanggalsystem($_POST['tglnsfp'][$arDt])."','".$_POST['kodesupplier'][$arDt]."','".str_replace(',', '', $_POST['ppn'][$arDt])."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$createTime."','".$_POST['status'][$arDt]."')";
            }

        }try{ 

            $owlPDO->exec($sDet);

        }catch (PDOException $e){

            echo " Gagal ".addslashes($e->getMessage()."__".$sDet);

        }

    break;

    case'loadData':
        $where = "";
        // $where = " createdby ='".$_SESSION['standard']['userid']."'";
        $where.= " unit in (".getOrgDetail(2).")";

        if ($notransaksi != '') {
            $where.=" and notransaksi like '%" . $notransaksi . "%'";
        }

        $limit=20;
        $page=0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2="select count(*) as jmlhrow from " . $dbname . ".tax_vatin_vatout where ".$where." group by notransaksi,noakun"; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl=$query2->fetch()) {
            $jlhbrs=$jsl->jmlhrow;
        }

        #query untuk display
        $arrIsiDt=array();
        $sData="select sum(nilai) as nilai,notransaksi,noakun,unit,npwp from ".$dbname.".tax_vatin_vatout where ".$where." and status!='3' group by notransaksi,npwp,noakun";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
        	$arrIsiDt[$val['notransaksi'].$val['npwp'].$val['noakun']]=$val['nilai'];
        }

        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $str="select distinct notransaksi,periode,unit,posting,npwp from ".$dbname.".tax_vatin_vatout where ".$where." limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $no=$maxdisplay;
            while ($bar=$res->fetch()) {

            	$selisih=$arrIsiDt[$bar['notransaksi'].$bar['npwp'].$noakunOut]-$arrIsiDt[$bar['notransaksi'].$bar['npwp'].$noakunIn];

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center>".$bar['notransaksi']."</td>";
                $tab.="<td align=center>".$bar['periode']."</td>";
                $tab.="<td align=center>".$bar['unit']."</td>";
                $tab.="<td align=center>".$bar['npwp']."</td>";
                $tab.="<td align=right title='Detail Vat in' style=cursor:pointer onclick=\"viewdetail('".$bar['notransaksi']."','".$bar['npwp']."','".$noakunIn."');\">".number_format($arrIsiDt[$bar['notransaksi'].$bar['npwp'].$noakunIn])."</td>";
                $tab.="<td align=right title='Detail Vat Out' style=cursor:pointer onclick=\"viewdetail('".$bar['notransaksi']."','".$bar['npwp']."','".$noakunOut."');\">".number_format($arrIsiDt[$bar['notransaksi'].$bar['npwp'].$noakunOut])."</td>";
                $tab.="<td align=right>".number_format($selisih)."</td>";
                if ($bar['posting']==0){
                    $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['notransaksi']."','".$bar['npwp']."');\"></td>
                        <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting ".$bar['notransaksi']."' onclick=\"formposting('".$bar['notransaksi']."');\" ></td>";
                }else{
                    $tab.="<td align=center colspan=2><img src=images/icons/04/16/02.png class=resicon  title='Posted ".$bar['notransaksi']."'></td>";
                }
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
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
		
        echo $tab."####".$footd;
    break;

    case 'delete':
        $str = "delete from " . $dbname . ".tax_vatin_vatout where notransaksi='" . $notransaksi . "' and npwp='".$npwp."'";
        try{
            $owlPDO->exec($str); 

        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case 'formposting':  

        #ambil selisih tax in - tax out
        $selisih=0;
        $arrIsiDt=array();
        $tahun=substr($notransaksi,0,4);
        $sData="select sum(nilai) as nilai,notransaksi,noakun from ".$dbname.".tax_vatin_vatout where status!='3' and notransaksi='".$notransaksi."' group by notransaksi,noakun";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
            $arrIsiDt[$val['notransaksi'].$val['noakun']]=$val['nilai'];
        }
        $selisih=$arrIsiDt[$notransaksi.$noakunOut]-$arrIsiDt[$notransaksi.$noakunIn];

        #ambil noakun tax recoverable
        $str1="select sampaidebet from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $noakun=$rtr['sampaidebet'];

        #ambil sawal tax recoverable
        $str1="select awal01 as sawal from ".$dbname.".keu_saldobulanan where noakun='".$noakun."' and kodeorg='".substr($notransaksi,6,4)."' and periode='".$tahun."01'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $sawal=$rtr['sawal'];

        #ambil jumlah tax recoverable
        $str1="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where noakun='".$noakun."' and kodeorg='".substr($notransaksi,6,4)."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $jumlah=$sawal+$rtr['jumlah'];

        #mencari tax recoverable yang digunakan
        $selisihtax=$selisih-$jumlah;
        if ($selisihtax<=0) {
            $taxre=$selisih;
        }else{
            $taxre=$jumlah;
        }

        $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
        $tab.="<tr class=rowcontent>
                <td>".$_SESSION['lang']['notransaksi']."</td> 
                <td>:</td>
                <td><input type=text class=myinputtext  style=\"width:200px;\" value='".$notransaksi."' disabled/></td>
              </tr>
              <tr class=rowcontent>
                <td>".$_SESSION['lang']['tanggal']."</td> 
                <td>:</td>
                <td><input type=text class=myinputtext readonly  id=tglposting onmousemove=setCalendar(this.id) onkeypress=return false; style=\"width:200px;\"/></td>
              </tr>";

        if ($selisih>0 && $jumlah>0) {
            $tab.="<tr class=rowcontent>
                <td>".$_SESSION['lang']['jumlah']." dipakai (tax recoverable)</td> 
                <td>:</td>
                <td><input type=text class=myinputtextnumber style=\"width:200px;\" value='".number_format($taxre,3)."' placeholder='Jumlah tax recoverable=".$jumlah."' id=jumlahtax onkeyup=\"z.numberFormat('jumlahtax',2);\" /></td>
              </tr>";
        }else{
            $tab.="<tr class=rowcontent style=display:none;>
                <td>".$_SESSION['lang']['jumlah']."</td> 
                <td>:</td>
                <td><input type=text class=myinputtextnumber style=\"width:200px;\" placeholder='Jumlah tax recoverable=".$jumlah."' id=jumlahtax onkeyup=\"z.numberFormat('jumlahtax',2);\" /></td>
              </tr>";
        }      
        
        $tab.="<tr class=rowcontent>
                <td></td><td></td>
                <td><button class=mybutton onclick=posting('".$notransaksi."')>Simpan</button></td>
              </tr>
        </table>";
                
        echo $tab;
    
    break;

    case 'posting':

        $jumlahtax=floatval(str_replace(',', '', $jumlahtax));

        #query untuk display
        $arrIsiDt=array();
        $arrIsiDtNhl=array();
        $sData="select sum(nilai) as nilai,notransaksi,noakun from ".$dbname.".tax_vatin_vatout where status!='3' and notransaksi='".$notransaksi."' and posting=0 group by notransaksi,noakun";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
            $arrIsiDt[$val['notransaksi'].$val['noakun']]=$val['nilai'];
        }

        $sData="select sum(nilai) as nilai,notransaksi,noakun from ".$dbname.".tax_vatin_vatout where status='3' and notransaksi='".$notransaksi."' and posting=0 group by notransaksi,noakun";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
            $arrIsiDtNhl[$val['notransaksi'].$val['noakun']]=$val['nilai'];
        }

        $qTrans="select distinct notransaksi,periode,unit,status,noakun from ".$dbname.".tax_vatin_vatout where notransaksi='".$notransaksi."' and posting=0";
        $data=fetchData($qTrans);
        $bar=$data[0];

        #get induk
        $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['unit']."'";
        $ressup=$owlPDO->query($sqlkd);
        $ressup->setFetchMode(PDO::FETCH_ASSOC);
        $barsup=$ressup->fetch();
        $induk=$barsup['induk'];

        $selisih=0;
        $noakundebet="";
        $noakunkredit="";
        $kodejurnal="VIVO";
        $tgljurnal=str_replace('-','',$tglposting);
        $ket="Jurnal Otomatis periode : ".$bar['periode']." unit : ".$bar['unit'];
        $selisih=$arrIsiDt[$bar['notransaksi'].$noakunOut]-$arrIsiDt[$bar['notransaksi'].$noakunIn];

        #get noakun bank
        $str1="select noakundebet,noakunkredit,sampaidebet,sampaikredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $noakundebet=$rtr['noakundebet'];
        $noakunkredit=$rtr['noakunkredit'];
        $sampaidebet=$rtr['sampaidebet'];
        $sampaikredit=$rtr['sampaikredit'];

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
        $notrans=$tgljurnal."/".$bar['unit']."/".$kodejurnal."/".$konter;

        if ($bar['status']!=4 && $jumlahtax==0) {
            
            if ($selisih<0) {
                $noakun3=$sampaidebet;
            }
            if ($selisih>0) {
                $noakun3=$sampaikredit;
            }
            
            //insert jurnalht
            $strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
            try{

                $owlPDO->exec($strht);
                $str=array();
                //insert jurnaldt debet
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$arrIsiDt[$bar['notransaksi'].$noakunOut]."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //insert jurnaldt kredit
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($arrIsiDt[$bar['notransaksi'].$noakunIn])."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //insert jurnaldt selisih
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','3','".$noakun3."','".$ket."','".-($selisih)."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //update kelompokjurnal
                $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";

                if(count($str)!=0){
                    for($i=0; $i<count($str); $i++){
                        try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                    }   
                }

            }catch (PDOException $e){
                echo "Gagal : ".$e->getMessage();
                die();
            }
        }

        if ($arrIsiDtNhl[$bar['notransaksi'].$noakunIn]!=0) {
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
            $notrans=$tgljurnal."/".$bar['unit']."/".$kodejurnal."/".$konter;
            
            //insert jurnalht
            $strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
            try{

                $owlPDO->exec($strht);

                $str=array();
                //insert jurnaldt debet
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','1','9299901','".$ket."','".$arrIsiDtNhl[$bar['notransaksi'].$noakunIn]."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //insert jurnaldt kredit
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($arrIsiDtNhl[$bar['notransaksi'].$noakunIn])."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //update kelompokjurnal
                $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";

                if(count($str)!=0){
                    for($i=0; $i<count($str); $i++){
                        try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                    }   
                }

            }catch (PDOException $e){
                echo "Gagal : ".$e->getMessage();
                die();
            }
        }

        if ($bar['status']!=4 && $jumlahtax>0) {

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
            $notrans=$tgljurnal."/".$bar['unit']."/".$kodejurnal."/".$konter;
            $sisahutang=$selisih-$jumlahtax;

            //insert jurnalht
            $strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
            try{

                $owlPDO->exec($strht);
                $str=array();
                //insert jurnaldt debet PPN Keluaran
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$arrIsiDt[$bar['notransaksi'].$noakunOut]."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //insert jurnaldt kredit PPN Masukan
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($arrIsiDt[$bar['notransaksi'].$noakunIn])."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                if ($sisahutang>0) {
                    //insert jurnaldt kredit hutang pajak
                    $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                    values ('".$notrans."','".$tgljurnal."','3','".$sampaikredit."','".$ket."','".(-1)*($sisahutang)."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";
                }
                
                //insert jurnaldt kredit tax recoverable
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','4','".$sampaidebet."','".$ket."','".-($jumlahtax)."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //update kelompokjurnal
                $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";

                if(count($str)!=0){
                    for($i=0; $i<count($str); $i++){
                        try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                    }   
                }

            }catch (PDOException $e){
                echo "Gagal : ".$e->getMessage();
                die();
            }
        }

        if ($bar['status']==4) {

            $sData="select sum(nilai) as nilai,notransaksi,noakun from ".$dbname.".tax_vatin_vatout where status='4' and notransaksi='".$notransaksi."' and noakun='".$bar['noakun']."'";
            $rData=fetchData($sData);
            $jumlah=$rData[0]['nilai'];
            
            if ($bar['noakun']==$noakunIn) {
                $akun1=$noakunkredit;
                $akun2=$sampaidebet;
            }else{
                $akun1=$sampaidebet;
                $akun2=$noakundebet;
            }
            
            //insert jurnalht
            $strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
            try{

                $owlPDO->exec($strht);

                $str=array();
                //insert jurnaldt debet
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','1','".$akun1."','".$ket."','".$jumlah."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //insert jurnaldt kredit
                $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
                values ('".$notrans."','".$tgljurnal."','2','".$akun2."','".$ket."','".-($jumlah)."','IDR','1','".$bar['unit']."','".$bar['notransaksi']."','".$bar['noinvoice']."')";

                //update kelompokjurnal
                $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";

                if(count($str)!=0){
                    for($i=0; $i<count($str); $i++){
                        try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                    }   
                }

            }catch (PDOException $e){
                echo "Gagal : ".$e->getMessage();
                die();
            }
        }

        //update posting tax
        $strpost="update ".$dbname.".tax_vatin_vatout set posting='1',tgl_posting='".$tglposting."' where notransaksi='".$notransaksi."'";
        try{ 
            $owlPDO->exec($strpost); 
        }catch (PDOException $e){
            echo "Error : ".$e->getMessage(); 
            die(); 
        }  

    break;

    case 'viewdetail':

        $legend="";
        if ($noakun==$noakunIn) {
            $legend="Vat In";
            $lang=$_SESSION['lang']['supplier'];
            $namaakun=$optnamain[$noakunIn];
			$arrmodul = getmodulefil('VATIN');
			$tipe='vatin';
			$str="select * from ".$dbname.".log_5supplier where status=1";
			$res=fetchdata($str);
			foreach($res as $bar){
				$nmsupcust[$bar['supplierid']]=$bar['namasupplier'];
			}
        }

        if ($noakun==$noakunOut) {
            $legend="Vat Out";
            $lang=$_SESSION['lang']['customer'];
            $namaakun=$optnamaout[$noakunOut];
			$arrmodul = getmodulefil('VATOUT');
			$tipe='vatout';
			$str="select * from ".$dbname.".pmn_4customer";
			$res=fetchdata($str);
			foreach($res as $bar){
				$nmsupcust[$bar['kodecustomer']]=$bar['namacustomer'];
			}
        }
	
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}	

        if ($viewtipe=='excel') {
            $border="border=1";
        }else{
            $border='';
			$data .="<fieldset style='float:left;'>
			<legend>Upload File</legend>
			<table class=sortable cellspacing=1 cellpadding=5 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['kriteria']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody id=containerupload></tbody>
				<tbody>
				<tr>
					<td></td>
					<td>
						<select id='kriteriaefil'>". $optkriteria."</select>
					</td>
					<td>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td style='text-align:center'>
						<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"addfile('".$notransaksi."','".$noakun."','".$npwp."','".$tipe."');\">
					</td>
				</tr>
				</tbody>
			</table>
			</fieldset>
			<div style='clear:both'></div>";
        }
	
        $data.="<fieldset><legend>".$_SESSION['lang']['detail']." ".$legend."</legend>";
		
		if ($viewtipe=='html'){
			$data.="<div style='float:left;margin-bottom:5px;'>";
			if ($noakun==$noakunIn) {
                $data.="<img  title='Export to CSV PPN Masukan' onclick=dataKeExcel(event,'".$notransaksi."','".$noakun."','pajak_slave_vatinvatout.php') src=images/excel.jpg class=resicon>&nbsp;
				<img  title='Export to CSV PPN Masukan Lain-lain' onclick=dataKeExcelLain(event,'".$notransaksi."','".$noakun."','pajak_slave_vatinvatout.php') src=images/excel.jpg class=resicon>&nbsp;";
            }
            if ($noakun==$noakunOut) {
                $data.="<img  title='Export to CSV PPN Keluaran' onclick=dataKeExcel(event,'".$notransaksi."','".$noakunOut."','pajak_slave_vatinvatout.php') src=images/excel.jpg class=resicon>";
            }
            $data.="&nbsp;<img title='Detail Excel' onclick=dataexceldetail(event,'".$notransaksi."','".$noakun."','".$npwp."','pajak_slave_vatinvatout.php') src=images/excel.jpg class=resicon>
			</div>";
		}
		
        $data.="<div style=overflow:auto;width:100%;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable ".$border." >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['nourut']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggalinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['invoice']."</td>";
        $data.="<td>".$_SESSION['lang']['nodok']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggal'].". GL</td>";
        $data.="<td>COA. GL</td>";
        $data.="<td>Tgl. NSFP</td>";
        $data.="<td>Exp. Date NSFP</td>";
        $data.="<td>No. NSFP</td>";
        $data.="<td>".$lang."</td>";
        $data.="<td>DPP</td>";
        $data.="<td>".$_SESSION['lang']['ppn']."</td>";
        $data.="<td>".$_SESSION['lang']['jenispajak']."</td>";
        $data.="<td>".$_SESSION['lang']['total']."</td>";
        $data.="<td>Kas Negara</td>";
        $data.="<td></td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".tax_vatin_vatout where notransaksi='".$notransaksi."' and noakun='".$noakun."' and npwp='".$npwp."'  order by nofakturpajak asc";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
			
			
			if($tipe=='vatin'){
				#= query ke tagihan
				#= keu_tagihanht
				$strdata="select * from ".$dbname.".keu_tagihanht where noinvoice='".$bar['noinvoice']."'";
				$resdata=fetchdata($strdata);
				foreach($resdata as $bardata){
					$nodok=$bardata['nopo'];
					$keterangantagihan=$bardata['keterangan'];
				}
			}
			
			
			if($tipe=='vatout'){
				#= query ke tagihan
				#= keu_tagihanht
				$strdata="select * from ".$dbname.".keu_penagihanht where noinvoice='".$bar['noinvoice']."'";
				$resdata=fetchdata($strdata);
				foreach($resdata as $bardata){
					$nodok=$bardata['nokontrak'];
					$keterangantagihan='';
				}
			}

        	$dpp=0;
        	$total=0;
			$dpp=$bar['nilai']*10;
			$total=$dpp+$bar['nilai'];
			$ekstglnsfp=date('Y-m-d', strtotime('+3 month', strtotime($bar['tgl_fakturpajak'])));

        	$no+=1;
            $data.="<tr class=rowcontent>";
            $data.="<td>".$no."</td>";
            if ($viewtipe=='excel') {
                $data.="<td>".$bar['tglinvoice']."</td>";
            }else{
                $data.="<td>".tanggalnormal($bar['tglinvoice'])."</td>";    
            }
            
            $data.="<td id='noinvoicedt_".$no."'>".$bar['noinvoice']."</td>";
            $data.="<td>".$nodok."</td>";
            $data.="<td>".$keterangantagihan."</td>";
            if ($viewtipe=='excel') {
                $data.="<td>".$bar['tglgl']."</td>";
            }else{
                $data.="<td>".tanggalnormal($bar['tglgl'])."</td>";
            }
            $data.="<td>".$bar['noakun']."</td>";
            if ($viewtipe=='excel') {
                $data.="<td>".$bar['tgl_fakturpajak']."</td>";
                $data.="<td>".$ekstglnsfp."</td>";
            }else{
                $data.="<td>".tanggalnormal($bar['tgl_fakturpajak'])."</td>";
                $data.="<td>".tanggalnormal($ekstglnsfp)."</td>";
            }
            $data.="<td>".$bar['nofakturpajak']."</td>";
            // $data.="<td>".$nodok."</td>";
            $data.="<td>".$nmsupcust[$bar['kodesupplier']]."</td>";
            $data.="<td align=right>".number_format($dpp)."</td>";
            $data.="<td align=right>".number_format($bar['nilai'])."</td>";    
            $data.="<td>".$namaakun."</td>";
            $data.="<td align=right>".number_format($total)."</td>";
            $data.="<td >".$status[$bar['status']]."</td>";
            $data.="<input type='hidden' id='statusdt_".$no."' value='".$bar['status']."'>";
            $data.="<td style=cursor:pointer><input type='checkbox' id='nodt_".$no."'></td></tr>";
            $data.="</tr>";
            $totalDpp+=$dpp;
            $totalPPn+=$bar['nilai'];
        }
        
        $data.="<input type=hidden id=totrowdt value=".$no.">";
        $data.="<tr class=rowcontent>";
        $data.="<td colspan=11 align=right>".$_SESSION['lang']['total']."</td>";
        $data.="<td align=right>".number_format($totalDpp)."</td>";
        $data.="<td align=right>".number_format($totalPPn)."</td>";    
        $data.="<td colspan=4>&nbsp;</td>";
        $data.="</tr>";
        $data.= "</table></div></fieldset>";
        $data.="<p align=right><button class=mybutton onclick=removedetail('".$notransaksi."','".$noakun."')>".$_SESSION['lang']['delete']."</button></p>";

        if ($viewtipe=='excel') { 
            $sNet="select a.unit,b.induk from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi 
                    where notransaksi='".$notransaksi."'";
            $rNet=fetchData($sNet);
            $tglSkrg = date("Ymd");
            $nop_ = "Detail_".$rNet[0]['induk']."_".$legend;
            if (strlen($data) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $data)) {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/" . $nop_ . ".xls';
                    </script>";
                }
                fclose($handle);
            }
        }else{
            echo $data."####".$tipe;
        }

    break;

    case'removedetail':

        for($arDt=0;$arDt<$_POST['totrow'];$arDt++){

            #delete data
            $sdel="delete from ".$dbname.".tax_vatin_vatout where notransaksi='".$notransaksi."' and noakun='".$noakun."' and noinvoice='".$_POST['noinvoice'][$arDt]."' and status='".$_POST['status'][$arDt]."'";
            // exit('warning : '.$sdel);
            try{
                $owlPDO->exec($sdel); 
            }catch (PDOException $e) {
                print " error: code 1125\n: " . $e->getMessage() . "<br/>"; die(); 
            }

        }

    break;

    case'ppnmasukanexcel':
    $sData="select a.noinvoice as noinvoice,right(a.nofakturpajak,15) as seripajak,left(a.nofakturpajak,3) as faktur,a.tgl_fakturpajak as tgl_fakturpajak,a.nofakturpajak as fakturpajakasli,b.historynofp as nofp_ganti,
            b.tanggalnofp as historytanggalfp,b.jenistransaksi, left(a.periode,4) as tahunpajak,right(a.periode,2) as masapajak,b.tipeinvoice,b.nilaiinvoice,a.nilai as ppn,a.kodesupplier as supplierid,a.periode
            from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where notransaksi='".$_GET['notransaksi']."' and  a.noakun='".$_GET['noakun']."' order by a.nofakturpajak ";
    // echo $sData;
    // exit('warning ');
    $rData=fetchData($sData);
    foreach ($rData as $key => $val) {
        // if(strlen($val['fakturpajakasli'])==19){
            $lstData[$val['noinvoice']]['jenistransaksi']=$val['jenistransaksi'];
           $fakturpajak=str_replace(".","", $val['seripajak']);
           $fakturpajak=str_replace("-","", $fakturpajak);
           $fakturpajak=str_replace("/","", $fakturpajak);
           $lstData[$val['noinvoice']]['nofp']=$fakturpajak;
           $lstData[$val['noinvoice']]['masapajak']=$val['masapajak'];
           $lstData[$val['noinvoice']]['tahunpajak']=$val['tahunpajak'];
           $tglfaktur=tanggalnormal($val['tgl_fakturpajak']);
           $tglfaktur=str_replace("-","/",$tglfaktur);
           $lstData[$val['noinvoice']]['tgl_fakturpajak']=$tglfaktur;
           $periodemasa=str_replace("-","", $val['periode']);
           $sNpwp="select * from ".$dbname.".log_5supnpwp where supplierid='".$val['supplierid']."'";
           $rNpwp=fetchData($sNpwp);
           $data=$rNpwp[0];
           $data['npwp']=str_replace(".", "", $data['npwp']);
           $data['npwp']=str_replace("-", "", $data['npwp']);
           $lstData[$val['noinvoice']]['npwp']=$data['npwp'];
           $lstData[$val['noinvoice']]['nama']=$val['nama_npwp'];
           $lstData[$val['noinvoice']]['alamat']=$val['alamat_lengkap'];
           $lstData[$val['noinvoice']]['nilaidpp']=$val['ppn']*10;
           $lstData[$val['noinvoice']]['nilaippn']=$val['ppn'];
           $lstData[$val['noinvoice']]['faktur']=$val['faktur'];
        // }
    }
    $sNet="select a.unit,b.induk from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi 
           where notransaksi='".$_GET['notransaksi']."'";
    $rNet=fetchData($sNet);
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=masapajak_ppnmasukan_".$rNet[0]['induk']."_".$periodemasa.".csv");
        echo"FM,KD_JENIS_TRANSAKSI,FG_PENGGANTI,NOMOR_FAKTUR,MASA_PAJAK,TAHUN_PAJAK,TANGGAL_FAKTUR,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,IS_CREDITABLE\n";
        foreach($lstData as $row=>$isiDt){
            $jnsTrans=substr($isiDt['faktur'],0,2);
            $fgganti=substr($isiDt['faktur'],-1,1);
            echo "FM,"."'".$jnsTrans.","."'".$fgganti.","."'".$isiDt['nofp'].",".$isiDt['masapajak'].",".$isiDt['tahunpajak'].",".$isiDt['tgl_fakturpajak'].",".$isiDt['npwp'].",".$isiDt['nama'].",".$isiDt['alamat'].",".$isiDt['nilaidpp'].",".$isiDt['nilaippn'].",0,1\n";
        }
    exit();
    break;
    case'ppnmasukanexcellain':
    $sData="select a.noinvoice as noinvoice,right(a.nofakturpajak,15) as seripajak,left(a.nofakturpajak,3) as faktur,a.tgl_fakturpajak as tgl_fakturpajak,a.nofakturpajak as fakturpajakasli,b.historynofp as nofp_ganti,
            b.tanggalnofp as historytanggalfp,b.jenistransaksi, left(a.periode,4) as tahunpajak,right(a.periode,2) as masapajak,b.tipeinvoice,b.nilaiinvoice,a.nilai as ppn,a.kodesupplier as supplierid,a.periode
            from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where notransaksi='".$_GET['notransaksi']."' and  a.noakun='".$_GET['noakun']."'  order by a.nofakturpajak ";
    $rData=fetchData($sData);
    foreach ($rData as $key => $val) {
        if(strlen($val['fakturpajakasli'])!=19){
            $lstData[$val['noinvoice']]['jenistransaksi']=$val['jenistransaksi'];
            $fakturpajak=$val['seripajak'];
           if(($val['nofp_ganti']!='')||(!is_null($val['nofp_ganti']))){
                $fakturpajak=$val['nofp_ganti'];
                $val['tgl_fakturpajak']=$val['tgl_fakturpajak'];
           }
           $lstData[$val['noinvoice']]['nofp']=$fakturpajak;
           $lstData[$val['noinvoice']]['masapajak']=$val['masapajak'];
           $lstData[$val['noinvoice']]['tahunpajak']=$val['tahunpajak'];
           $tglfaktur=tanggalnormal($val['historytanggalfp']);
           $tglfaktur=str_replace("-","/",$tglfaktur);
           $lstData[$val['noinvoice']]['tgl_fakturpajak']=$tglfaktur;
           $periodemasa=str_replace("-","", $val['periode']);
           $sNpwp="select * from ".$dbname.".log_5supnpwp where supplierid='".$val['supplierid']."'";
           $rNpwp=fetchData($sNpwp);
           $data=$rNpwp[0];
           $data['npwp']=str_replace(".", "", $data['npwp']);
           $data['npwp']=str_replace("-", "", $data['npwp']);
           $lstData[$val['noinvoice']]['npwp']=$data['npwp'];
           $lstData[$val['noinvoice']]['nama']=$val['nama_npwp'];
           $lstData[$val['noinvoice']]['alamat']=$val['alamat_lengkap'];
           $lstData[$val['noinvoice']]['nilaidpp']=$val['ppn']*10;
           $lstData[$val['noinvoice']]['nilaippn']=$val['ppn'];
           $lstData[$val['noinvoice']]['faktur']=$val['faktur'];
           $lstData[$val['noinvoice']]['nofp_ganti']=$val['nofp_ganti'];
        }
    }
    $sNet="select a.unit,b.induk from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi 
           where notransaksi='".$_GET['notransaksi']."'";
    $rNet=fetchData($sNet);
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=masapajak_ppnmasukan_lainlain_".$rNet[0]['induk']."_".$periodemasa.".csv");
        echo"DK_DM,JENIS_TRANSAKSI,JENIS_DOKUMEN,KD_JNS_TRANSAKSI,FG_PENGGANTI,NOMOR_DOK_LAIN_GANTI,NOMOR_DOK_LAIN,TANGGAL_DOK_LAIN,MASA_PAJAK,TAHUN_PAJAK,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,KETERANGAN,FAPR,TGL_APPROVAL\n";
        foreach($lstData as $row=>$isiDt){
            $dtgnt=0;
            if($isiDt['nofp_ganti']!=''){
                $dtgnt=1;
            }
            echo"DM,2,5,1,".$dtgnt.",".$isiDt['nofp'].",".$isiDt['nofp'].",".$isiDt['tgl_fakturpajak'].",".$isiDt['masapajak'].",".$isiDt['masapajak'].",".$isiDt['npwp'].",".$isiDt['nama'].",".$isiDt['alamat'].",".$isiDt['nilaidpp'].",".$isiDt['nilaippn'].",0,KETERANGAN,FAPR,TGL_APPROVAL\n";
        }
    exit();
    break;
    case'ppnkeluaranexcel':
        $arrCust=array();
        $sData="select * from ".$dbname.".pmn_4customer ";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
                $arrCust[$val['kodecustomer']]['namacust']=$val['namacustomer'];
                $val['npwp']=str_replace(".", "", $val['npwp']);
                $val['npwp']=str_replace("-", "", $val['npwp']);
                $arrCust[$val['kodecustomer']]['npwp']=$val['npwp'];
                $arrCust[$val['kodecustomer']]['alamatcust']=$val['alamatnpwp'];
        }
        $sData="select a.noinvoice as noinvoice,a.noinvoice as noreferensi,a.nofakturpajak as nofakturpajak,a.tgl_fakturpajak as tgl_fakturpajak,b.nokontrak as nokontrak,
            a.npwp as npwp, left(a.periode,4) as tahunpajak,right(a.periode,2) as masapajak,b.tipeinvoice,b.nilaiinvoice,a.nilai as ppn,a.kodesupplier as supplierid,a.periode,b.kodecustomer as customerid,b.npwp as npwpcust
            from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".keu_penagihanht b on a.noinvoice=b.noinvoice where notransaksi='".$_GET['notransaksi']."' and  a.noakun='".$_GET['noakun']."'  order by a.nofakturpajak ";
        $rData=fetchData($sData);
        foreach ($rData as $key => $val) {
            $val['nofakturpajak']=str_replace(".", "", $val['nofakturpajak']);
            $val['nofakturpajak']=str_replace("-", "", $val['nofakturpajak']);
            $lstData[$val['noinvoice']]['nofakturpajak']=$val['nofakturpajak'];
            $val['tgl_fakturpajak']=str_replace("-","/", $val['tgl_fakturpajak']);
            $lstData[$val['noinvoice']]['tgl_fakturpajak']=$val['tgl_fakturpajak'];
            $snpwp="select * from ".$dbname.".setup_org_npwp where npwp='".$val['npwp']."' ";
            $rnpwp=fetchData($snpwp);
            $optNm=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$rnpwp[0]['kodeorg']."'");
            $val['npwp']=str_replace(".", "", $val['npwp']);
            $val['npwp']=str_replace("-", "", $val['npwp']);
            $lstData[$val['noinvoice']]['npwp']=$val['npwp'];
            $lstData[$val['noinvoice']]['alamat']=$rnpwp[0]['alamatnpwp'];
            $lstData[$val['noinvoice']]['namanpwp']=$optNm[$rnpwp[0]['kodeorg']];
            $lstData[$val['noinvoice']]['ppn']=$val['nilai'];
            $lstData[$val['noinvoice']]['dpp']=$val['nilai']*10;
            $lstData[$val['noinvoice']]['customerid']=$val['customerid'];

            $sKontrak="select kodebarang,hargasatuan,kuantitaskontrak     from ".$dbname.".pmn_kontrakjual where nokontrak='".$val['nokontrak']."'";
            $rKontrak=fetchData($sKontrak);
            $lstData[$val['noinvoice']]['kodebarang']=$rKontrak[0]['kodebarang'];
            $lstData[$val['noinvoice']]['hargasatuan']=$rKontrak[0]['hargasatuan'];
            $lstData[$val['noinvoice']]['kuantitaskontrak']=$rKontrak[0]['kuantitaskontrak'];
        }
        $sNet="select a.unit,b.induk from ".$dbname.".tax_vatin_vatout a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi 
           where notransaksi='".$_GET['notransaksi']."'";
        $rNet=fetchData($sNet);
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=masapajak_ppnkeluaran_".$rNet[0]['induk']."_".$periodemasa.".csv");
        echo"FK,KD_JENIS_TRANSAKSI,FG_PENGGANTI,NOMOR_FAKTUR,MASA_PAJAK,TAHUN_PAJAK,TANGGAL_FAKTUR,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,ID_KETERANGAN_TAMBAHAN,FG_UANG_MUKA,UANG_MUKA_DPP,UANG_MUKA_PPN,UANG_MUKA_PPNBM,REFERENSI\n";
        echo"LT,NPWP,NAMA,JALAN, BLOK,,NOMOR, RT,RW,KECAMATAN, KELURAHAN,KABUPATEN,PROPINSI,KODE_POS,NOMOR_TELEPON\n";
        echo"OF,KODE_OBJEK,NAMA,HARGA_SATUAN,JUMLAH_BARANG,HARGA_TOTAL,DISKON,DPP,PPN,TARIF_PPNBM,PPNBM\n";
        foreach($lstData as $rw=>$lst){
            $sinisial="select kodebarang,namabarang,inisial from ".$dbname.".log_5masterbarang where kodebarang='".$lst['kodebarang']."'";
            $rinisial=fetchData($sinisial);
            echo"FK,1,NAMA,".$val['nofakturpajak'].",".$val['masapajak'].",".$val['tahunpajak'].",".$val['tgl_fakturpajak'].",".$arrCust[$val['customerid']]['npwp'].",".$arrCust[$val['customerid']]['namacust'].",".$arrCust[$val['customerid']]['alamatcust'].",".$val['dpp'].",".$val['ppn'].",0,0,0,0,0,0,".$val['noreferensi']."\n";
            echo"FAPR,".$lst['namanpwp'].",".$lst['alamat'].",'', '','', '','','', '','','','',''\n";
            echo"OF,".$rinisial[0]['inisial'].",".$rinisial[0]['namabarang'].",".$lst['hargasatuan'].",".$lst['kuantitaskontrak'].",".($lst['hargasatuan']*$lst['kuantitaskontrak']).",0,".($lst['hargasatuan']*$lst['kuantitaskontrak']).",".(($lst['hargasatuan']*$lst['kuantitaskontrak'])*0.1).",0,0\n";
        }
        exit();
    break;
	
	case'submitfile':
		$data=$_POST;
		$tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 250000){
                        $str = "insert into ".$dbname.".listfileupload values ('','".$notransaksi."','".$filename."','".$filetype."','".$data['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
                        try{
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                            file_put_contents($path.$filename,$file_tmpname);
                        }
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }else{
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus .jpg atau .jpeg");
                }
            }
        }
	break;
	
	case'loadfiles':
		$data=$_POST;
		$tab="";
		$no=0;
		// $str="select * from ".$dbname.".listfileupload where 
				// notransaksi='".$notransaksi."' and noakun='".$noakun."' and npwp='".$npwp."' and tipe='".$tipe."'";
        // $res=fetchData($str);
		$str="select * from ".$dbname.".listfileupload where 
				notransaksi='".$notransaksi."'";
        $res=fetchData($str);
		
		if(count($res) <= 0){
			$tab.="<tr class='rowcontent'><td colspan='4' style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td style='text-align:center'>".getcriterianame($val['kriteriaefil'])."</td>";
				$tab.="<td style='text-align:left;'>".$val['namafile']."</td>";
				
				$tab.="<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>";
				
				$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$notransaksi."','".$val['namafile']."');\" >";
				
				$tab."  </td>
				</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		$data=$_POST;
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$data['namafile']."'";
        try{
            $owlPDO->exec($str);
            $pathx = $path.$data['namafile'];
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
}
?>
