<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$unit = checkPostGet('unit','');
$notransaksi = checkPostGet('notransaksi','');
$jeniscr = checkPostGet('jeniscr','');
$kodeasset = checkPostGet('kodeasset','');
$jenis = checkPostGet('jenis','');
$jenisket = checkPostGet('jenisket','');
$ket = checkPostGet('ket','');
$nilaibuku = checkPostGet('nilaibuku','');
$akumulasipenyusutan = checkPostGet('akumulasipenyusutan','');
$per['persetujuan1'] = checkPostGet('persetujuan1','');
$per['persetujuan2'] = checkPostGet('persetujuan2','');
$per['persetujuan3'] = checkPostGet('persetujuan3','');
$per['persetujuan4'] = checkPostGet('persetujuan4','');
$method = checkPostGet('method','');
$arrstatus=array('1' => 'Disposal','2' => 'Write-off');

switch ($method) {

    case'getjenisket':

        $optJenisket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".keu_5jenisdisposalasset where jenis='".$jenis."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if ($jenisket==$bar['id']) {
                $optJenisket.="<option value='".$bar['id']."' selected>".$bar['keterangan']."</option>";
            }else{
                $optJenisket.="<option value='".$bar['id']."'>".$bar['keterangan']."</option>";
            }
            
        }

        echo $optJenisket; 
        break;

    case'getasset':

        $optasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".sdm_daftarasset where kodeorg='".$unit."'  and status='1'  and kodeasset not in (select kodeasset from ".$dbname.".keu_disposalasset where statuspersetujuan not in (0,2)) ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if ($kodeasset==$bar['kodeasset']) {
                $optasset.="<option value='".$bar['kodeasset']."' selected>".$bar['kodeasset']." - ".$bar['namasset']."</option>";
            }else{
                $optasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset']." - ".$bar['namasset']."</option>";
            }
            
        }

        echo $optasset; 
        break;

    case'getdata':

        $str="select * from ".$dbname.".sdm_daftarasset where kodeasset='".$kodeasset."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();

        $tgl1=$bar->awalpenyusutan."-01";
        $tgl2=date('Y-m-d');
        $tahun1=substr($tgl1,0,4);
        $bulan1=substr($tgl1,5,2);
        $tahun2=substr($tgl2,0,4);
        $bulan2=substr($tgl2,5,2);

        $sisalbulan=($tahun2*12)+$bulan2 - (($tahun1*12)+$bulan1);
        $akumulasiBulanan=$bar->bulanan*$sisalbulan;
        $nilaibuku=$bar->hargaperolehan-$akumulasiBulanan;

        echo number_format($nilaibuku)."####".number_format($akumulasiBulanan); 
        break;

    case'insert':

        $tahunbulan = $unit."-".date("ymd");

        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_disposalasset where left(notransaksi,11) = '".$tahunbulan."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        $notransaksi=$tahunbulan.addZero($awal,3);
        $jenispersetujuan='DISPO';
        $nilaibuku=str_replace(',', '', $nilaibuku);
        $akumulasipenyusutan=str_replace(',', '', $akumulasipenyusutan);

        $str="insert into ".$dbname.".keu_disposalasset (notransaksi,kodeasset,jenisket,catatan,createdby,updateby,nilaibuku,akumulasipenyusutan)
            values ('".$notransaksi."','".$kodeasset."','".$jenisket."','".$ket."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$nilaibuku."','".$akumulasipenyusutan."')";
		try{
			$owlPDO->exec($str); 

            for ($i=1; $i<5; $i++) { 

                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }


		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;


    case'update':

        $nilaibuku=str_replace(',', '', $nilaibuku);
        $akumulasipenyusutan=str_replace(',', '', $akumulasipenyusutan);

        $str="update ".$dbname.".keu_disposalasset set updateby='".$_SESSION['standard']['userid']."',catatan='".$ket."',kodeasset='".$kodeasset."',jenisket='".$jenisket."',nilaibuku='".$nilaibuku."',akumulasipenyusutan='".$akumulasipenyusutan."',updatetime='".date('Y-m-d H:i:s')."'
             where notransaksi='".$notransaksi."'";
        try{
			$owlPDO->exec($str); 

            for ($i=1; $i<5; $i++) { 

                $str="update ".$dbname.".approval set karyawanid='".$per['persetujuan'.$i]."' where notransaksi='".$notransaksi."' and jenispersetujuan='DISPO' and level='".$i."'";

                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }

		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
            
        break;


    case'loadData':
        $where = "";
        $where = " createdby ='".$_SESSION['standard']['userid']."'";

        if ($notransaksi != '') {
            $where.=" and notransaksi like '%" . $notransaksi . "%'";
        }
        if ($jeniscr != '') {
            $where.=" and jenisket like '" . $jeniscr . "%' ";
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

        $ql2="select count(*) as jmlhrow from " . $dbname . ".keu_disposalasset where ".$where; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl=$query2->fetch()) {
            $jlhbrs=$jsl->jmlhrow;
        }

        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $str="select * from ".$dbname.".keu_disposalasset where ".$where."  limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $no=$maxdisplay;
            while ($bar=$res->fetch()) {

                $strp="select karyawanid,level from ".$dbname.".approval where jenispersetujuan='DISPO' and notransaksi='".$bar['notransaksi']."'";
                $resp=$owlPDO->query($strp) or die(print " Gagal: ".PDOException::getMessage());
                $resp->setFetchMode(PDO::FETCH_ASSOC);
                while($barp=$resp->fetch())
                {
                    $per['persetujuan'.$barp['level']]=$barp['karyawanid'];
                }

                $whr1="karyawanid='".$bar['updateby']."'";
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whr1);
                $whr="id='".$bar['jenisket']."'";
                $optjns = makeOption($dbname, 'keu_5jenisdisposalasset','id,keterangan',$whr);
                $whras="kodeasset='".$bar['kodeasset']."'";
                $nmasset = makeOption($dbname, 'sdm_daftarasset','kodeasset,namasset',$whras);
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
                $tab.="<td align=center>" . $nmasset[$bar['kodeasset']] . "</td>";
                $tab.="<td align=left>" . $arrstatus[substr($bar['jenisket'],0,1)] . "</td>";
                $tab.="<td align=left>" . $optjns[$bar['jenisket']] . "</td>";
                $tab.="<td align=left>" . $bar['catatan'] . "</td>";
                $tab.="<td align=left>" . (isset($nmKar[$bar['updateby']]) ? $nmKar[$bar['updateby']] : '') . "</td>";
                if ($bar['statuspersetujuan']==0){
                    $tab.="<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".substr($bar['notransaksi'],0,4)."','".$bar['kodeasset']."','".substr($bar['jenisket'],0,1)."','".$bar['jenisket']."','".$bar['catatan']."','".$per['persetujuan1']."','".$per['persetujuan2']."','".$per['persetujuan3']."','".$per['persetujuan4']."','".number_format($bar['nilaibuku'])."','".number_format($bar['akumulasipenyusutan'])."');\"></td>
                           <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['notransaksi']. "');\"></td>";
                    $tab.="<td align=center><img src=images/icons/04/16/01.png class=resicon  title='Ajukan' onclick=\"ajukan('".$bar['notransaksi']."');\"></td>";
                    $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('".$bar['notransaksi']."',event);\" ></td>";
                }else{
                    $tab.="<td align=center colspan=4><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('".$bar['notransaksi']."',event);\" ></td>";
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
                <tr><td colspan=16 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
		
        echo $tab."####".$footd;
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".keu_disposalasset where notransaksi='" . $notransaksi . "'";
        try{
			$owlPDO->exec($str); 

            $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "'";
            try{
                $owlPDO->exec($str); 
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }


		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'ajukan':

        $strht = "update ".$dbname.".keu_disposalasset set statuspersetujuan='9' where notransaksi='".$notransaksi."'";             
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

    case'getformkodeasset':
        
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['namaasset']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=fkodeasset></td>";
        $form.= "<td><button class=mybutton onclick=findkodeasset(2)>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=container2></div>";
        echo $form;
        break;  

    case'getdatakodeasset':

        $data="";
        $dt  ="";

        if($_POST['kodeasset']!=''){
            $where.=" and namasset like '%".$_POST['kodeasset']."%'";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['kodeasset']."</td>";
        $data.="<td>".$_SESSION['lang']['namaasset']."</td>";
        $data.="<td>".$_SESSION['lang']['tipeasset']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggalperolehan']."</td>";
        $data.="<td>".$_SESSION['lang']['hargaperolehan']."</td>";
        $data.="<td>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td>";
        $data.="<td>".$_SESSION['lang']['akumulasipenyusutan']."</td>";
        $data.="<td>".$_SESSION['lang']['nilaibuku']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".sdm_daftarasset where kodeorg='".$unit."'  and status='1' and kodeasset not in (select kodeasset from ".$dbname.".keu_disposalasset where statuspersetujuan not in (0,2)) ".$where;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){

            $tgl1=$bar->awalpenyusutan."-01";
            $tgl2=date('Y-m-d');
            $tahun1=substr($tgl1,0,4);
            $bulan1=substr($tgl1,5,2);
            $tahun2=substr($tgl2,0,4);
            $bulan2=substr($tgl2,5,2);
    
            $sisalbulan=($tahun2*12)+$bulan2 - (($tahun1*12)+$bulan1);
            $akumulasiBulanan=$bar->bulanan*$sisalbulan;
            $nilaibuku=$bar->hargaperolehan-$akumulasiBulanan;

            $whr1="kodetipe='".$bar->tipeasset."'";
            $opttipe = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,namatipe',$whr1);

            $data.="<tr class=rowcontent style='cursor:pointer;' onclick=\"setdata('".$bar->kodeasset."','".number_format($nilaibuku)."','".number_format($akumulasiBulanan)."')\">";
            $data.="<td>".$bar->kodeasset."</td>";
            $data.="<td>".$bar->namasset."</td>";
            $data.="<td>".$opttipe[$bar->tipeasset]."</td>";
            $data.="<td>".$bar->tahunperolehan."</td>";
            $data.="<td align=right>".number_format($bar->hargaperolehan)."</td>";
            $data.="<td align=right>".$bar->jlhblnpenyusutan."</td>";
            $data.="<td align=right>".number_format($akumulasiBulanan)."</td>";
            $data.="<td align=right>".number_format($nilaibuku)."</td>";
            $data.="</tr>";
        }
        $data.= "</table></div></fieldset>";

        echo $data;
        break;

    case 'viewdetail':
        $data.="<fieldset><legend>".$_SESSION['lang']['detail']." Asset</legend>";
        $data.="<div style=overflow:auto;width:100%;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['kodeasset']."</td>";
        $data.="<td>".$_SESSION['lang']['namaasset']."</td>";
        $data.="<td>".$_SESSION['lang']['tipeasset']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggalperolehan']."</td>";
        $data.="<td>".$_SESSION['lang']['hargaperolehan']."</td>";
        $data.="<td>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td>";
        $data.="<td>".$_SESSION['lang']['akumulasipenyusutan']."</td>";
        $data.="<td>".$_SESSION['lang']['nilaibuku']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".keu_disposalasset where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $barp=$res->fetch();

            $str="select * from ".$dbname.".sdm_daftarasset where kodeasset='".$barp->kodeasset."' ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $bar=$res->fetch();

            $tgl1=$bar->awalpenyusutan."-01";
            $tgl2=date('Y-m-d');
            $tahun1=substr($tgl1,0,4);
            $bulan1=substr($tgl1,5,2);
            $tahun2=substr($tgl2,0,4);
            $bulan2=substr($tgl2,5,2);
    
            $sisalbulan=($tahun2*12)+$bulan2 - (($tahun1*12)+$bulan1);
            $akumulasiBulanan=$bar->bulanan*$sisalbulan;
            $nilaibuku=$bar->hargaperolehan-$akumulasiBulanan;

            $whr1="kodetipe='".$bar->tipeasset."'";
            $opttipe = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,namatipe',$whr1);

            $data.="<tr class=rowcontent>";
            $data.="<td>".$bar->kodeasset."</td>";
            $data.="<td>".$bar->namasset."</td>";
            $data.="<td>".$opttipe[$bar->tipeasset]."</td>";
            $data.="<td>".$bar->tahunperolehan."</td>";
            $data.="<td align=right>".number_format($bar->hargaperolehan)."</td>";
            $data.="<td align=center>".$bar->jlhblnpenyusutan."</td>";
            $data.="<td align=right>".number_format($akumulasiBulanan)."</td>";
            $data.="<td align=right>".number_format($barp->nilaibuku)."</td>";
            $data.="</tr>";
        $data.= "</table></div></fieldset><br><br>";

        $data.="<table align=left border=0>
                    <tr>
                     <td colspan=3><b>Status Persetujuan</b></td>
                    </tr>
        ";

        $statper = array('0' => $_SESSION['lang']['wait_approve'],'1' => $_SESSION['lang']['disetujui'],'3' => $_SESSION['lang']['ditolak'], );

        $str="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' order by level";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $data.="<tr>
                    <td>".$_SESSION['lang']['persetujuan']." ".$bar['level']."</td>
                    <td> : </td>
                    <td>".$statper[$bar['status']]."</td>
                  </tr>
                  <tr>
                    <td>Komentar ".$bar['level']."</td>
                    <td> : </td>
                    <td>".$bar['komentar']."</td>
                  </tr>";
        }
        $data.="</table>";

        echo $data;
        break;

    default:
}
?>
