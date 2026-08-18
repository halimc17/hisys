<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi = checkPostGet('notransaksi','');
$tipetransaksikasbank = checkPostGet('tipetransaksikasbank','');
$unitpemberi = checkPostGet('unitpemberi','');
$unitpenerima = checkPostGet('unitpenerima','');
$norekpemberi = checkPostGet('norekpemberi','');
$norekpenerima = checkPostGet('norekpenerima','');
$tanggalpemberi = tanggalsystemn(checkPostGet('tanggalpemberi',''));
$totalpemberi = checkPostGet('totalpemberi','');
$totalpenerima = checkPostGet('totalpenerima','');
$method = checkPostGet('method','');
$arrstatus=array('1' => 'Disposal','2' => 'Write-off');

switch ($method) {

    case 'getakun':
        $akunpemberi=$akunpenerima="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select a.noakun,b.namabank,a.rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where pemilik='".$unitpemberi."' order by b.namabank ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            if ($norekpemberi==$bar['noakun']){
                $akunpemberi.="<option value='".$bar['noakun']."' selected>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }else{
                $akunpemberi.="<option value='".$bar['noakun']."'>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }
            
        }

        $res=$owlPDO->query("select a.noakun,b.namabank,a.rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where pemilik='".$unitpenerima."' order by b.namabank ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            if ($norekpenerima==$bar['noakun']){
                $akunpenerima.="<option value='".$bar['noakun']."' selected>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }else{
                $akunpenerima.="<option value='".$bar['noakun']."'>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }
            
        }

        echo $akunpemberi."####".$akunpenerima;
    break;

    case 'getmatauang':
        $res=$owlPDO->query("select matauang from ".$dbname.".keu_5akunbank where noakun='".$norekpemberi."' ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $mupemberi=$bar['matauang'];

        $res=$owlPDO->query("select matauang from ".$dbname.".keu_5akunbank where noakun='".$norekpenerima."' ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $mupenerima=$bar['matauang'];

        echo $mupemberi."####".$mupenerima;
    break;

    case'gettotalpenerima':
        $opttotalpenerima="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".keu_5totalpemberidisposalasset where totalpemberi='".$totalpemberi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if ($totalpenerima==$bar['id']) {
                $opttotalpenerima.="<option value='".$bar['id']."' selected>".$bar['keterangan']."</option>";
            }else{
                $opttotalpenerima.="<option value='".$bar['id']."'>".$bar['keterangan']."</option>";
            }
            
        }

        echo $opttotalpenerima; 
    break;

    case'insert':

        if ($unitpemberi==$unitpenerima) {
            exit('warning : Unit pemberi modal dan unit penerima modal tidak boleh sama.');
        }

        $tahunbulan="MOD".date("Ym");
        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_modal where left(notransaksi,9)='".$tahunbulan."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
        if(intval($rp['nomorurut'])==0){
          $awal=1;
        }else{
          $awal=intval($rp['nomorurut'])+1;
        }
        $notransaksi=$tahunbulan.addZero($awal,3);
        $totalpemberi=str_replace(',', '', $totalpemberi);

        $str="insert into ".$dbname.".keu_modal (notransaksi,unit_pemberimodal,norekening_pemberimodal,tanggal,nilai_modal,unit_penerimamodal,norekening_penerimamodal,createby,updateby)
            values ('".$notransaksi."','".$unitpemberi."','".$norekpemberi."','".$tanggalpemberi."','".$totalpemberi."','".$unitpenerima."','".$norekpenerima."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
    break;

    case'update':
        $totalpemberi=str_replace(',', '', $totalpemberi);

        $str="update ".$dbname.".keu_modal set updateby='".$_SESSION['standard']['userid']."',norekening_penerimamodal='".$norekpenerima."',norekening_pemberimodal='".$norekpemberi."',tanggal='".$tanggalpemberi."',nilai_modal='".$totalpemberi."',updatetime='".date('Y-m-d H:i:s')."'
             where notransaksi='".$notransaksi."'";
        try{
			$owlPDO->exec($str); 
        }catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}            
    break;

    case'loadData':
        $where = "";
        $where = " createby ='".$_SESSION['standard']['userid']."'";

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

        $str="select * from ".$dbname.".keu_5daftarbank";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $nmbank[$bar['kodebank']]=$bar['namabank'];
        }

        $ql2="select count(*) as jmlhrow from " . $dbname . ".keu_modal where ".$where; 
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
            $str="select * from ".$dbname.".keu_modal where ".$where."  limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $no=$maxdisplay;
            while ($bar=$res->fetch()) {

                $nmBankDtterima=$nmBankDtberi="";
                $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar['norekening_penerimamodal']."' order by namabank asc";
                $barak=fetchData($strak);
                if(count($barak)!=0){
                    $dtRek=$barak[0];
                    $nmBankDtterima=$nmbank[$dtRek['namabank']]." - ".$dtRek['rekening'];
                }

                $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar['norekening_pemberimodal']."' order by namabank asc";
                $barak=fetchData($strak);
                if(count($barak)!=0){
                    $dtRek=$barak[0];
                    $nmBankDtberi=$nmbank[$dtRek['namabank']]." - ".$dtRek['rekening'];
                }


                $whr1="karyawanid='".$bar['createby']."'";
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whr1);
                $whr2="kodeorganisasi='".$bar['unit_pemberimodal']."'";
                $nmunitpemberi = makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi',$whr2);
                $whr3="kodeorganisasi='".$bar['unit_penerimamodal']."'";
                $nmunitpenerima = makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi',$whr3);
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center>".$bar['notransaksi']."</td>";
                $tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
                $tab.="<td align=center>".$nmunitpemberi[$bar['unit_pemberimodal']]."</td>";
                $tab.="<td align=center>".$nmBankDtberi."</td>";
                $tab.="<td align=center>".$nmunitpenerima[$bar['unit_penerimamodal']]."</td>";
                $tab.="<td align=center>".$nmBankDtterima."</td>";
                $tab.="<td align=right>".number_format($bar['nilai_modal'])."</td>";
                $tab.="<td align=left>".(isset($nmKar[$bar['createby']]) ? $nmKar[$bar['createby']] : '')."</td>";
                if ($bar['status']==0){
                    $tab.="<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['unit_pemberimodal']."','".$bar['norekening_pemberimodal']."','".tanggalnormal($bar['tanggal'])."','".number_format($bar['nilai_modal'])."','".$bar['unit_penerimamodal']."','".$bar['norekening_penerimamodal']."');\"></td>
                        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['notransaksi']. "');\"></td>
                        <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\" ></td>";
                }else{
                    $tab.="<td align=center colspan=3><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail Pemberi' onclick=\"viewdetail('".$bar['notransaksi']."','K');\"> &nbsp;";
                    $tab.="<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail Penerima' onclick=\"viewdetail('".$bar['notransaksi']."','M');\"></td>";
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
        $str = "delete from " . $dbname . ".keu_modal where notransaksi='" . $notransaksi . "'";
        try{
			$owlPDO->exec($str); 

		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
    break;

    case 'posting':

        $strht = "update ".$dbname.".keu_modal set status='1' where notransaksi='".$notransaksi."'";             
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

        $legend="";
        if ($tipetransaksikasbank=='K') {
            $legend="Pemberi Modal";
        }

        if ($tipetransaksikasbank=='M') {
            $legend="Penerima Modal";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['detail']." ".$legend."</legend>";
        $data.="<div style=overflow:auto;width:100%;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $data.="<td>".$_SESSION['lang']['notransaksi']." Kasbank</td>";
        $data.="<td>".$_SESSION['lang']['keterangan']."</td>";
        $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and tipetransaksi='".$tipetransaksikasbank."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            
            $strket="select keterangan from ".$dbname.".keu_kasbankht where notransaksi='".$bar['notransaksi']."' ";
            $resket=$owlPDO->query($strket) or die(print " Gagal: ".PDOException::getMessage());
            $resket->setFetchMode(PDO::FETCH_ASSOC);
            $barket=$resket->fetch();

            $data.="<tr class=rowcontent>";
            $data.="<td>".$bar['tanggal']."</td>";
            $data.="<td>".$bar['notransaksi']."</td>";
            $data.="<td>".$barket['keterangan']."</td>";
            $data.="<td align=right>".number_format($bar['jumlah'])."</td>";
            $data.="</tr>";
            $total+=$bar['jumlah'];

        }
        $data.="<tr class=rowcontent>";
        $data.="<td align=right colspan=3>".$_SESSION['lang']['total']."</td>";
        $data.="<td align=right>".number_format($total)."</td>";
        $data.="</tr>";
        $data.= "</table></div></fieldset><br><br>";

        echo $data;
    break;

    default:
}
?>
