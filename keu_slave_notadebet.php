<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

$proses=checkPostGet('proses','');
$notadebet=checkPostGet('notadebet','');
$tipe=checkPostGet('tipe','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$revisi=checkPostGet('revisi','');
$kodeorg=checkPostGet('kodeorg','');
$unit=checkPostGet('unit','');
$tipeinvoice=checkPostGet('tipeinvoice','');
$notahutang=checkPostGet('notahutang','');
$supplier=checkPostGet('supplier','');
$nilaiinvoice=checkPostGet('nilaiinvoice','');
$keterangan=checkPostGet('keterangan','');
$notadebet=checkPostGet('notadebet','');
$noakun=checkPostGet('noakun','');
$noakundtold=checkPostGet('noakundtold','');
$matauang=checkPostGet('matauang','');
$kurs=checkPostGet('kurs','');
$nilai=checkPostGet('nilai','');
$kodevhc=checkPostGet('kodevhc','');
$kmhm=checkPostGet('kmhm','');
$kodeasset=checkPostGet('kodeasset','');
$tipesupplier=checkPostGet('tipesupplier','');
$noreferensi_transaksi=checkPostGet('noreferensi_transaksi','');
$param =$_POST;

switch ($proses) {
	
	
	
	case'getnilai':
		// exit("Error:".$kmhm._.$kodevhc._.$unit);
		
		#= ambil rp/kmhm ditable setup_varianharga
		$str = "select rupiah from ".$dbname.".setup_varianharga 
			where kodevhc='".$kodevhc."' and unit='".$unit."' and tanggal<='".$tanggal."' order by tanggal desc limit 1 ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargasatuan=$bar['rupiah'];
			
		$nilai=$hargasatuan*$kmhm;	
		$nilai=number_format($nilai,2);
		
		
	echo $nilai;
		
	
	break;

    case'getunit':
        # Options
        $arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and kodeorganisasi in (".getOrgDetail(2).")";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) 
        {
            $arrUnit .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }
        
        echo $arrUnit;
    break;
	
	
	case'gettipesupplier':
        # Options
		// exit("Error:A");
        $opttipesupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select a.tipe,b.kode from ".$dbname.".log_5supkelompok a 
				left join ".$dbname.".log_5klsupplier b on a.tipe=b.tipe where a.supplierid = '".$supplier."' and status='1'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
			$select='';
			if($tipesupplier==$bar['tipe']){
				$select="selected";
			}
            $opttipesupplier .= "<option value='".$bar['tipe']."' ".$select.">".$bar['tipe']." ".$bar['kode']."</option>";
        }
        echo $opttipesupplier;
    break;
	
	case'getakunht':
        # Options
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		// exit("Error:$noakun");
        $optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".log_5klsupplier  where tipe = '".$tipesupplier."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
			$select='';
			if($noakun==$bar['noakun']){
				$select="selected";
			}
            $optakun .= "<option value='".$bar['noakun']."' ".$select.">".$bar['noakun']." ".$nmakun[$bar['noakun']]."</option>";
        }
		$select='';
		if($noakun=='1130101'){
			$select="selected";
		}
		$optakun .= "<option value='1130101' ".$select.">1130101 ".$nmakun['1130101']."</option>";
		
        
        echo $optakun;
    break;

    case 'getakun':
        $optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
      
            // $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='APMK'";
            // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            // $res->setFetchMode(PDO::FETCH_ASSOC);
            // $bar=$res->fetch();
            // $akun=$bar['nilai'];

            // $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and (substring(fieldaktif,-2,1)='1'  or noakun='".$akun."') order by noakun";
     

        // if ($kodevhc!=''){
            // $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and noakun='4110299' order by noakun";
        // }

        // if ($supplier!='') {

            // $strpa="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TRLE'";
            // $respa=$owlPDO->query($strpa) or die(print " Gagal: ".PDOException::getMessage());
            // $respa->setFetchMode(PDO::FETCH_ASSOC);
            // $barpa=$respa->fetch();
            // $akunpa=explode(',', $barpa['nilai']);
            // $arrakleasing="'".$akunpa[0]."','".$akunpa[1]."'";

            // $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and noakun in (".$arrakleasing.") order by noakun";
        // }
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // while($bar=$res->fetch())
        // {
            // if ($noakun==$bar['noakun']){
                // $optakun.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            // }else{
                // $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            // }
            
        // }
		
		
		$str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            if ($noakun==$bar['noakun']){
                $optakun.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }else{
                $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
            }
            
        }
        echo $optakun;
    break;

    case'getformnodo':
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['noinvoice']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=fnodo></td>";
        $form.= "<td><button class=mybutton onclick=findnodo()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=container2></div>";
        echo $form;
    break;  

    case'getdatanodo':
        $data="";
        $dt  ="";

        if($param['nodo']!=''){
            $where.=" and noinvoice like '%".$param['nodo']."%'";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['jenis']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $data.="<td>".$_SESSION['lang']['nopo']."</td>";
        $data.="<td>".$_SESSION['lang']['kodesupplier']."</td>";
        $data.="<td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        //and tipeinvoice='".$tipeinvoice."' kodeorg='".$kodeorg."' and 
        $str="select * from ".$dbname.".keu_tagihanht where unit='".$unit."' and tipeinvoice!='um' ".$where;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){

            $opjns=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',"kode='".$bar->tipeinvoice."'"); 
            $optsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar->kodesupplier."'"); 

            if ($bar->nilaiinvoice>0) {
                $data.="<tr class=rowcontent style='cursor:pointer;' 
						onclick=\"setdata('".$bar->noinvoice."','".$bar->tipeinvoice."','".$bar->kodesupplier."','".$bar->noakun."',
						'".$bar->matauang."','".$bar->kurs."','".$bar->jenissupplier."')\">";
                $data.="<td>".$bar->noinvoice."</td>";
                $data.="<td>".$opjns[$bar->tipeinvoice]."</td>";
                $data.="<td>".tanggalnormal($bar->tanggal)."</td>";
                $data.="<td>".$bar->nopo."</td>";
                $data.="<td>".$optsupp[$bar->kodesupplier]."</td>";
                $data.="<td align='right'>".number_format($bar->nilaiinvoice)."</td>";
                $data.="</tr>";  
            }
            
        }
        $data.= "</table></div></fieldset>";
        echo $data;
    break;

    case 'showdetail':

        $optSupplier=$optakun=$optasset=$optvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".log_5supplier where status=1";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $optSupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']." (".$bar['supplierid'].")</option>";
        }


        $str="select kodevhc,detailvhc from ".$dbname.".vhc_5master where kodeorg='".$unit."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optvhc.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc']." - ".$bar['detailvhc']."</option>";
        }


        $str="select kodeasset,namasset from ".$dbname.".sdm_daftarasset where kodeorg='".$unit."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset']." - ".$bar['namasset']."</option>";
        }


        $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='APMK'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $akun=$bar['nilai'];

        // $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and substring(fieldaktif,-2,1)='1' order by noakun";
        // if ($tipeinvoice=='k') {
            $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and (substring(fieldaktif,-2,1)='1'  or noakun='".$akun."') order by noakun";
        // }
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
        }

        OPEN_BOX();
        echo"<fieldset style='width:598px;'>";
        echo"<legend>".$_SESSION['lang']['detail']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>";
            // if ($tipeinvoice=='k') {
            echo"<tr>
                    <td>".$_SESSION['lang']['notransaksi']."</td> 
                    <td>:</td>
                    <td >
                        <input type=text id=kodeasset class=myinputtext style=width:197px; placeholder='click..' readonly title='".$_SESSION['lang']['find']."' onclick=\"searchgudang('".$_SESSION['lang']['find']."','<div id=formPencariangudang></div>',event)\">
                    </td>
                </tr>";
            // }
            echo"<tr>
                    <td>".$_SESSION['lang']['supplier']."</td> 
                    <td>:</td>
                    <td >
                        <select id=supplierdt style=width:200px; onchange='getakun()'>".$optSupplier."</select>
                        <img id='supplierdt' onclick=z.elSearch('supplierdt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
                    </td>
                </tr>";
            echo"<tr>
                    <td>".$_SESSION['lang']['kodevhc']."</td> 
                    <td>:</td>
                    <td><select id=kodevhc  style=width:200px; onchange='getakun()'>".$optvhc."</select></td>
                </tr>
				 <tr>
                    <td valign=top>".$_SESSION['lang']['kmhm']."</td> 
                    <td valign=top>:</td>
                    <td valign=top>
                        <input type=text onkeypress=\"return angka_doang (event);\" onkeyup=\"z.numberFormat('kmhm',2);\" class=myinputtextnumber onblur=getnilai(); id=kmhm style=width:197px; >
                    </td> 
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namaasset']."</td> 
                    <td>:</td>
                    <td >
                        <select id=kodeasset style=width:200px;>".$optasset."</select>
                        <img src=\"images/onebit_02.png\" style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."' onclick=\"searchkodeasset('".$_SESSION['lang']['find']."','<div id=formPencarianasset></div>',event)\">
                    </td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['noakun']."</td> 
                    <td>:</td>
                    <td>
                        <select id=noakundt style=width:200px;>".$optakun."</select><img src='images/obl.png' title='Obligatory'>
                    </td>
                </tr>
                <tr>
                    <td valign=top>".$_SESSION['lang']['nilai']."</td> 
                    <td valign=top>:</td>
                    <td valign=top>
                        <input type=text onkeypress=\"return angka_doang (event);\" onkeyup=\"z.numberFormat('nilai',2);\" class=myinputtextnumber id=nilai style=width:197px; ><img src='images/obl.png' title='Obligatory'>
                    </td> 
                </tr>
                <tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=saveDetail()>Simpan</button>
                        <button class=mybutton onclick=cleardetail()>Hapus</button>
                        <input type=hidden id=prosesdt value='insertdt'>
                        <input type=hidden id=noakundtold>
                    </td>
                </tr>
            </table></fieldset>";

            echo"
            <br>
            <fieldset>
            <legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</legend>
            <table class=sortable cellspacing=1 cellspacing=1 border=0 style='width:100%;'>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>" . $_SESSION['lang']['notransaksi'] . " referensi</td>
                <td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
                <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
                <td align=center>" . $_SESSION['lang']['kmhm'] . "</td>
                <td align=center>" . $_SESSION['lang']['namaasset'] . "</td>
                <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                <td align=center>" . $_SESSION['lang']['nilai'] . "</td>
                <td align=center>" . $_SESSION['lang']['nodok'] . "</td>
                <td align=center colspan=2>" . $_SESSION['lang']['action'] . "</td>
            </tr>
            </thead><tbody>";
            $no=0;
            $colspan=2;
            $str="SELECT * from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
            $res=fetchData($str);
            foreach($res as $row=>$bar){
                #pembuat
                $whrsup="supplierid='".$bar['kodesupplierdt']."'";
                $optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
                $whrKar2="kodevhc='".$bar['kodevhc']."'";
                $optjenis=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',$whrKar2);
                $whrak="noakun='".$bar['noakun']."'";
                $optak=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrak);
                $whrnama="kodeasset='".$bar['kodeasset']."'";
                $optnama=makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whrnama);
                $no+=1;
                echo"<tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$bar['noreferensi_transaksi']."</td>
                    <td>".$optsup[$bar['kodesupplierdt']]."</td>
                    <td>".$optjenis[$bar['kodevhc']]."</td>
                    <td align=right>".number_format($bar['kmhm'],2)."</td>
                    <td>".$optnama[$bar['kodeasset']]."</td>
                    <td>".$optak[$bar['noakun']]."</td>
                    <td align=right>".number_format($bar['nilai'])."</td>
                    <td>".$bar['noreferensi_transaksi']."</td>";
                $colspan="colspan=2";
                if ($bar['noreferensi_transaksi']=='') {
                    echo"<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdetail('".$bar['kodevhc']."','".$bar['kodeasset']."','".$bar['noakun']."','".number_format($bar['nilai'])."','".$bar['kodesupplierdt']."','".$bar['kmhm']."')\"></td>";
                    $colspan="";
                }
                echo"<td ".$colspan." ><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"deldetail('" . $bar['noakun']. "','".$bar['noreferensi_transaksi']."');\" ></td>";  
                echo"</tr>";
                
            }
            echo"</tbody></table></fieldset>";

        CLOSE_BOX();
    break;

    case'getformkodeasset':
        
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['namaasset']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=fkodeasset></td>";
        $form.= "<td><button class=mybutton onclick=findkodeasset()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=containerasset></div>";
        echo $form;
    break;  

    case'getdatakodeasset':

        $data="";
        $dt  ="";

        if($_POST['kodeasset']!=''){
            $where.=" and kodeasset like '%".$_POST['kodeasset']."%'";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['kodeasset']."</td>";
        $data.="<td>".$_SESSION['lang']['namaasset']."</td>";
        $data.="<td>".$_SESSION['lang']['tipeasset']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".sdm_daftarasset where kodeorg='".$unit."'  and status='1' and kodeasset not in (select kodeasset from ".$dbname.".keu_disposalasset where statuspersetujuan not in (0,2)) ".$where;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){

            $whr1="kodetipe='".$bar->tipeasset."'";
            $opttipe = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,namatipe',$whr1);

            $data.="<tr class=rowcontent style='cursor:pointer;' onclick=\"setdataasset('".$bar->kodeasset."')\">";
            $data.="<td>".$bar->kodeasset."</td>";
            $data.="<td>".$bar->namasset."</td>";
            $data.="<td>".$opttipe[$bar->tipeasset]."</td>";
            $data.="</tr>";
        }
        $data.= "</table></div></fieldset>";

        echo $data;
    break;

    case'getformgudang':
        
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['notransaksi']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=transgudang></td>";
        $form.= "<td><button class=mybutton onclick=findgudang()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=containergudang></div>";
        echo $form;
    break;  

    case'getdatagudang':

        $data="";
        $dt  ="";

        if($_POST['transgudang']!=''){
            $where.=" and notransaksi like '%".$_POST['transgudang']."%'";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:auto;height:200px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr>";
        $data.="<td colspan=5 ><button class=mybutton onclick=adddetail()>".$_SESSION['lang']['addtodetail']."</button></td>";
        $data.="</tr>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
        $data.="<td align=right>".$_SESSION['lang']['nilai']."</td>";
        $data.="<td align=right>".$_SESSION['lang']['persen']."</td>";
        $data.="<td align=right>".$_SESSION['lang']['total']."</td>";
        $data.="<td><input type=checkbox class=myinputtext title='".$_SESSION['lang']['all']."' id='btnall' onclick='checkAll()'></td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="SELECT notransaksi,hartot,left(kodekegiatan,7) as noakun,untukunit,tanggal,kodebarang FROM ".$dbname.".`log_transaksi_vw` 
					WHERE `kodekegiatan` LIKE '1160202%' and kodeblok='".$supplier."' ".$where." ";
        // exit('warning : '.$str);and notransaksi not in (select noreferensi_transaksi from ".$dbname.".keu_notadebet_dt)
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){

            $str1="select noreferensi_transaksi from ".$dbname.".keu_notadebet_dt a 
			left join ".$dbname.".keu_notadebet_ht b on a.notadebet=b.notadebet 
			where b.kodesupplier='".$supplier."' and noreferensi_transaksi='".$bar->notransaksi."'";
            $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);
            $bar1=$res1->fetch();
            if ($bar->notransaksi==$bar1->noreferensi_transaksi) {
                continue;
            }
			
			
			#= ambil data kenaikan harga
			// $str2="select noreferensi_transaksi from ".$dbname.".setup_varianharga a left join ".$dbname.".keu_notadebet_ht b on a.notadebet=b.notadebet where b.kodesupplier='".$supplier."' and noreferensi_transaksi='".$bar->notransaksi."'";
            // $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
            // $res2->setFetchMode(PDO::FETCH_OBJ);
            // $bar2=$res2->fetch();
			
			$str2 = "select persen from ".$dbname.".setup_varianharga 
				where unit='".$bar->untukunit."' and tanggal<='".$bar->tanggal."' 
				and tipe='inv' and kelompokbarang='".substr($bar->kodebarang,0,3)."' order by tanggal desc limit 1 ";
			$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2=$res2->fetch();
			$persen=$bar2['persen'];
			
			$hartot=$bar->hartot+($bar->hartot*$persen/100);
			
            $data.="<tr class=rowcontent>";
            $data.="<td id='nogudang_".$no."'>".$bar->notransaksi."</td>";
            $data.="<td align=right>".number_format($bar->hartot)."</td>";
            $data.="<td align=right>".number_format($persen,2)."</td>";
            $data.="<td id='hartot_".$no."' align=right>".number_format($hartot)."</td>";
            $data.="<td align=center><input type=checkbox class=myinputtext id='no_".$no."'></td>";
            $data.="</tr>";
            $data.="<input type=hidden id='noakunkeg' value='".$bar->noakun."'>";
            $no+=1;
        }
        $data.="<input type=hidden id='totrow' value='".$no."'>";
        $data.= "</table></div></fieldset>";

        echo $data;
    break;

    case 'insert':

        //get unit tagihan
		if($notahutang!=''){
			$sqlht=$owlPDO->query("select unit from ".$dbname.".keu_tagihanht where noinvoice='".$notahutang."'");
			$sqlht->setFetchMode(PDO::FETCH_ASSOC);
			$tght=$sqlht->fetch();
			$unittagihan=$tght['unit'];

			//check apakah akun R/K nya ada 
			
			if ($unit!=$unittagihan){
				$strcaco="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis='intra'";
				$rescaco=fetchData($strcaco);
				$akunrkpiutang=$rescaco[0]['akunpiutang'];
		
				$strcaco2="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$unittagihan."' and jenis='intra'";
				$rescaco2=fetchData($strcaco2);
				$akunrkhutang=$rescaco2[0]['akunhutang'];

				if ($akunrkpiutang=='') {
					exit('Warning : Akun R/K untuk unit '.$unit.' belum ada. Silahkan daftarkan di menu keuangan>setup>akun intra/interco');
				}

				if ($akunrkhutang=='') {
					exit('Warning : Akun R/K untuk unit '.$unittagihan.' belum ada. Silahkan daftarkan di menu keuangan>setup>akun intra/interco');
				}
			}
		}
        $nilaiinvoice=str_replace(',', '', $nilaiinvoice);

        // if ($tanggal=='' || $kodeorg=='' || $unit=='' || $notahutang=='' || $tipeinvoice=='' || $supplier=='' || $noakun=='' || $kurs=='' || $nilaiinvoice==0 ) {
        if ($tanggal=='' || $kodeorg=='' || $unit=='' || $supplier=='' || $noakun=='' || $kurs=='') {
            exit('warning : Field was empty.');
        }

        $tahunbulan = $param['kodeorg']."-NB".date('ymd');

        $query="select right(notadebet,3) as nomorurut from ".$dbname.".keu_notadebet_ht where left(notadebet,12) = '".$tahunbulan."' order by right(notadebet,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }

        $notadebet=$tahunbulan.addZero($awal,3);

        $str="insert into ".$dbname.".keu_notadebet_ht (notadebet,tipe,tanggal,revisi,kodeorg,unit,tipeinvoice,noinvoice_referensi,kodesupplier,nilaiinvoice,keterangan,matauang,kurs,noakun,createdby,updateby,tipesupplier)
                values ('".$notadebet."','".$tipe."','".$tanggal."','".$revisi."','".$kodeorg."','".$unit."','".$tipeinvoice."','".$notahutang."','".$supplier."','".$nilaiinvoice."','".$keterangan."','".$matauang."','".$kurs."','".$noakun."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$tipesupplier."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

        echo $notadebet;

    break;

    case'deldt':

        $strdt = "delete from ".$dbname.".keu_notadebet_ht where notadebet='".$notadebet."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'update':

        $nilaiinvoice=str_replace(',', '', $nilaiinvoice);

        if ($tanggal=='' || $kodeorg=='' || $unit=='' || $supplier=='' || $noakun=='' || $kurs=='') {
            exit('warning : Field was empty.');
        }

        $strht = "update ".$dbname.".keu_notadebet_ht 
			set tipe='".$tipe."',tanggal='".$tanggal."',
			revisi='".$revisi."',keterangan='".$keterangan."',
			tipesupplier='".$tipesupplier."',noakun='".$noakun."',
			updateby='".$_SESSION['standard']['userid']."', nilaiinvoice='".$nilaiinvoice."'  where notadebet='".$notadebet."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        echo $notadebet;

        break;

    case 'posting':

        //get noakun debet kredit tahunan
        $resht=$owlPDO->query("select * from ".$dbname.".keu_notadebet_ht where notadebet='".$notadebet."'");
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$resht->fetch();
        $kodejurnal="NOTAD";
        $akdebet=$bar['noakun'];
        $tgljurnal=str_replace('-', '', $bar['tanggal']);
        $induk=$bar['kodeorg'];
        $unit=$unittagihan=$bar['unit'];
        $unit1=$bar['unit'];
        $kodesupplier=$bar['kodesupplier'];
        $noinvoice=$bar['noinvoice_referensi'];
        $whrsup="supplierid='".$kodesupplier."'";
        $optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
        $keterangan2='Nota debet atas No.invoice : '.$bar['noinvoice_referensi'].'; '.$optsup[$kodesupplier].'/'.$bar['keterangan'];
		
        //get unit tagihan
		if($noinvoice!=''){
			$sqlht=$owlPDO->query("select unit from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'");
			$sqlht->setFetchMode(PDO::FETCH_ASSOC);
			$tght=$sqlht->fetch();
			$unittagihan=$tght['unit'];
		}

        //check apakah unit tagihan = unit nota debet
        //jika tidak sama muncul jurnal R/K
        if ($unit!=$unittagihan){
            $strcaco="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis='intra'";
            $rescaco=fetchData($strcaco);
            $akunrkpiutang=$rescaco[0]['akunpiutang'];
    
            $strcaco2="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$unittagihan."' and jenis='intra'";
            $rescaco2=fetchData($strcaco2);
            $akunrkhutang=$rescaco2[0]['akunhutang'];

            $unit1=$unittagihan;
        }

        # Get Journal Counter
        $queryJ=selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
        $tmpKonter=fetchData($queryJ);
        $konter=addZero($tmpKonter[0]['nokounter']+1,3);
        # Prep No Jurnal
        $notrans=$tgljurnal."/".$unit."/".$kodejurnal."/".$konter;

        $tmpJml = 0;
        $strdet="select * from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
        $dataD=fetchData($strdet);
        foreach($dataD as $row) {
            if($row['nilai']>0)
            $tmpJml += $row['nilai'];
        }
        $selisih = abs($tmpJml - $bar['nilaiinvoice']);
        if($selisih > 0.01) {
            echo "Warning : Jumlah Header dan Detail Tidak Balance\n";
            echo "Header:".number_format($bar['nilaiinvoice'])."\n";
            echo "Detail:".number_format($tmpJml)."\n";
            echo "Posting Gagal";
            exit;
        }

        $nourut=1;
        $errorDB="";
        $strht = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
        values ('".$notrans."','".$kodejurnal."','".$bar['nilaiinvoice']."','".(-1)*($bar['nilaiinvoice'])."','".$tgljurnal."','".date('Ymd')."','1','".$notadebet."','IDR','1')";
        try{
            $owlPDO->exec($strht);

            $strdt[] = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodesupplier)
            values ('".$notrans."','".$tgljurnal."','".$nourut."','".$akdebet."','".$keterangan2."','".$bar['nilaiinvoice']."','IDR','1','".$unit1."','".$noinvoice."','".$notadebet."','".$kodesupplier."')";
            $nourut++;

            if ($unit!=$unittagihan){

                $strdt[] = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodesupplier)
                values ('".$notrans."','".$tgljurnal."','".$nourut."','".$akunrkpiutang."','".$keterangan2."','".(-1)*($bar['nilaiinvoice'])."','IDR','1','".$unit1."','".$noinvoice."','".$notadebet."','".$kodesupplier."')";
                $nourut++;

                $strdt[] = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodesupplier)
                values ('".$notrans."','".$tgljurnal."','".$nourut."','".$akunrkhutang."','".$keterangan2."','".$bar['nilaiinvoice']."','IDR','1','".$unit."','".$noinvoice."','".$notadebet."','".$kodesupplier."')";
                $nourut++;

            }

            foreach($dataD as $row){

                $strdt[]= "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodesupplier)
                values ('".$notrans."','".$tgljurnal."','".$nourut."','".$row['noakun']."','".$keterangan2."','" .(-1)*($row['nilai']). "','IDR','1','".$unit."','".$row['noreferensi_transaksi']."','".$notadebet."','".$kodesupplier."')";
                $nourut++;

            }

            if (count($strdt)!=0) {
                for($i=0; $i<count($strdt); $i++){
                    try{
                        $owlPDO->exec($strdt[$i]);  
                    }catch (PDOException $e) {
                        $errorDB .= "Detail: ".$strdt[$i]." ". $e->getMessage() ; 
                    }
                }
            }
            

            if ($errorDB=="") {
                $strkj="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
                try{
                    $owlPDO->exec($strkj);
                }catch (PDOException $e){
                    echo "Gagal : ".$e->getMessage();
                    die();
                }
            }
            
            
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

        $strnd="update ".$dbname.".keu_notadebet_ht set posting='1',postingby='".$_SESSION['standard']['userid']."' where notadebet='".$notadebet."'";             
        try
        {
            $owlPDO->exec($strnd);

        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'loadData':
        $where=" 1=1 ";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $where.="";
        }
    
        $where.= " and unit in (".getOrgDetail(2).")";

        if ($notadebet!='') {
            $where.=" and notadebet like '%".$notadebet."%' ";
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

        $str="select * from ".$dbname.".keu_notadebet_ht where ".$where;
        $res=fetchData($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=14>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_notadebet_ht where ".$where." order by tanggal desc limit ".$offset.",".$limit."";
            $tab="";
            $res=fetchData($str);
            foreach($res as $row=>$bar){
                #pembuat
                $whrKar2="kode='".$bar['tipeinvoice']."'";
                $optjenis=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',$whrKar2);
                $whrsup="supplierid='".$bar['kodesupplier']."'";
                $optSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);
                $whrKar3="karyawanid='".$bar['postingby']."'";
                $optPosting=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);
                
                $colspan='colspan=4';
                $tab.="<tr class=rowcontent>
                    <td>".$bar['notadebet']."</td>
                    <td>".$bar['kodeorg']."</td>
                    <td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optjenis[$bar['tipeinvoice']]."</td>
                    <td>".$bar['noinvoice_referensi']."</td>
                    <td>".$optSup[$bar['kodesupplier']]."</td>
                    <td>".$bar['keterangan']."</td>
                    <td align=right>".number_format($bar['nilaiinvoice'])."</td>
                    <td>".$optPosting[$bar['postingby']]."</td>";
                if ($bar['posting']==0){
                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar['notadebet']."','".$bar['tipe']."','".tanggalnormal($bar['tanggal'])."','".$bar['revisi']."','".$bar['kodeorg']."','".$bar['unit']."','".$bar['tipeinvoice']."','".$bar['noinvoice_referensi']."','".$bar['kodesupplier']."','".number_format($bar['nilaiinvoice'])."','".$bar['keterangan']."','".$bar['noakun']."','".$bar['matauang']."','".$bar['kurs']."','".$bar['tipesupplier']."')\"></td>
                           <td><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"deldt('" . $bar['notadebet']. "');\" ></td>
                           <td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('".$bar['notadebet']."');\" ></td>";
                }else{
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted' ></td>";   
                }
                $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('".$bar['notadebet']."',event);\" ></td>";   
                $tab.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('" . $bar['notadebet']. "',event);\" ></td>";
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
                <tr><td colspan=14  valign=top align=center>
                <img src=\"images/skyblue/first.png\"  onclick=loadData(1);>
                <img src=\"images/skyblue/prev.png\"  onclick=loadData(".($page-1).");>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <img src=\"images/skyblue/next.png\"  onclick=loadData(".($page+1).");>
                <img src=\"images/skyblue/last.png\"  onclick=loadData(".($totrows-1).");>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case 'viewdetail':

        $tab.="<fieldset><legend>".$notadebet."</legend>";
        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['noakun']."</td>";
        $tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
        $tab.="<td>".$_SESSION['lang']['debet']."</td>";
        $tab.="<td>".$_SESSION['lang']['kredit']."</td>";
        $tab.="</tr></thead><tbody >";

        $no=1;
        $str="select * from ".$dbname.".keu_notadebet_ht where notadebet='".$notadebet."'";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();


        //get unit tagihan
        $sqlht=$owlPDO->query("select unit from ".$dbname.".keu_tagihanht where noinvoice='".$bar['noinvoice_referensi']."'");
        $sqlht->setFetchMode(PDO::FETCH_ASSOC);
        $tght=$sqlht->fetch();
        $unittagihan=$tght['unit'];

        //check apakah unit tagihan = unit nota debet
        //jika tidak sama muncul jurnal R/K
        if ($unit!=$unittagihan){
            $strcaco="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$bar['unit']."' and jenis='intra'";
            $rescaco=fetchData($strcaco);
            $akunrkpiutang=$rescaco[0]['akunpiutang'];
    
            $strcaco2="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$unittagihan."' and jenis='intra'";
            $rescaco2=fetchData($strcaco2);
            $akunrkhutang=$rescaco2[0]['akunhutang'];
        }

        $whrno="noakun='".$bar['noakun']."'";
        $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
        $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['noakun']."</td>
                <td>".$optnmakun[$bar['noakun']]."</td>
                <td align=right>".number_format($bar['nilaiinvoice'],2)."</td>
                <td align=right>".number_format(0,2)."</td>";
                $debet=$bar['nilaiinvoice'];
        $tab.="</tr>";
        $no+=1;

        if ($unit!=$unittagihan){
            $whrno="noakun='".$akunrkpiutang."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$akunrkpiutang."</td>
                <td>".$optnmakun[$akunrkpiutang]."</td>
                <td align=right>".number_format(0,2)."</td>
                <td align=right>".number_format($bar['nilaiinvoice'],2)."</td>";
                $debet=$bar['nilaiinvoice'];
            $tab.="</tr>";
            $no+=1;

            $whrno="noakun='".$akunrkhutang."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$akunrkhutang."</td>
                <td>".$optnmakun[$akunrkhutang]."</td>
                <td align=right>".number_format($bar['nilaiinvoice'],2)."</td>
                <td align=right>".number_format(0,2)."</td>";
                $debet=$bar['nilaiinvoice'];
            $tab.="</tr>";
        }

        $str="select * from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $no+=1;

            $whrno="noakun='".$bar['noakun']."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);

            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['noakun']."</td>
                <td>".$optnmakun[$bar['noakun']]."</td>
                <td align=right>".number_format(0,2)."</td>
                <td align=right>".number_format($bar['nilai'],2)."</td>";
                $kredit+=$bar['nilai'];
            $tab.="</tr>";
        }
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($debet,2)."</td>";
            $tab.="<td align=right>".number_format($kredit,2)."</td>";
            $tab.="</tr>";

        $tab.="</tbody>";
        $tab.="</table></fieldset>";

        echo $tab;
    break;

    case 'insertdt':

        $nilai=str_replace(',', '', $nilai);

        if ($noakun=='' || $nilai=='') {
            exit('warning : Field was empty.');
        }

        $str="insert into ".$dbname.".keu_notadebet_dt (notadebet,noakun,nilai,kodevhc,kodeasset,kodesupplierdt,kmhm)
                values ('".$notadebet."','".$noakun."','".$nilai."','".$kodevhc."','".$kodeasset."','".$supplier."','".$kmhm."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
		
		
		
		#= ambil total nilai
		$str="SELECT sum(nilai) as nilai from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilai=$bar['nilai'];
			
			
		#= update ht	
		$str="update ".$dbname.".keu_notadebet_ht set nilaiinvoice='".$nilai."' where notadebet='".$notadebet."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		

    break;

    case'adddetail':

        $sDet="insert into ".$dbname.".keu_notadebet_dt values ";
        for($arDt=0;$arDt<$_POST['totrow'];$arDt++){
            if($arDt==0){
                $sDet.=" ('".$notadebet."','".$noakun."','".str_replace(',', '', $_POST['hartot'][$arDt])."','".$_POST['nogudang'][$arDt]."','','','','')";
            }else{
                $sDet.=",('".$notadebet."','".$noakun."','".str_replace(',', '', $_POST['hartot'][$arDt])."','".$_POST['nogudang'][$arDt]."','','','','')";
            }
        }
        try{ 
            $owlPDO->exec($sDet); 
        }
        catch (PDOException $e){
        echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
        }
		
		#= ambil total nilai
		$str="SELECT sum(nilai) as nilai from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilai=$bar['nilai'];
			
			
		#= update ht	
		$str="update ".$dbname.".keu_notadebet_ht set nilaiinvoice='".$nilai."' where notadebet='".$notadebet."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		

    break;

    case 'updatedt':

        $nilai=str_replace(',', '', $nilai);

        if ($noakun=='' || $nilai=='') {
            exit('warning : Field was empty.');
        }

        $strdt = "update ".$dbname.".keu_notadebet_dt set 
			kodeasset='".$kodeasset."',kodevhc='".$kodevhc."',noakun='".$noakun."',nilai='".$nilai."',
			kodesupplierdt='".$supplier."',kmhm='".$kmhm."' where  notadebet='".$notadebet."' and noakun='".$noakundtold."'";             
            try
            {
                $owlPDO->exec($strdt);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
			
			
		#= ambil total nilai
		$str="SELECT sum(nilai) as nilai from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilai=$bar['nilai'];
			
			
		#= update ht	
		$str="update ".$dbname.".keu_notadebet_ht set nilaiinvoice='".$nilai."' where notadebet='".$notadebet."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
			
			

    break;

    case'deldetail':

        $whr=" notadebet='".$notadebet."' and noakun='".$noakun."'";
        if ($tipeinvoice=='k') {
            $whr=" notadebet='".$notadebet."' and noakun='".$noakun."' and noreferensi_transaksi='".$noreferensi_transaksi."'";
        }

        $strdt = "delete from ".$dbname.".keu_notadebet_dt where ".$whr;
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
		
		
		#= ambil total nilai
		$str="SELECT sum(nilai) as nilai from ".$dbname.".keu_notadebet_dt where notadebet='".$notadebet."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilai=$bar['nilai'];
			
			
		#= update ht	
		$str="update ".$dbname.".keu_notadebet_ht set nilaiinvoice='".$nilai."' where notadebet='".$notadebet."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		
		

    break;
    
    default:
        # code...
    break;
}


?>