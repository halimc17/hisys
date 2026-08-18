<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$proses=checkPostGet('proses','');
$kdBrg=checkPostGet('kdBrg','');
$custId=checkPostGet('custId','');
$noKontrak=checkPostGet('noKontrak','');
$optNma=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$txtSearch=checkPostGet('txtSearch','');
$txtTgl=tanggalsystem(checkPostGet('txtTgl',''));
$noTiket=checkPostGet('noTiket','');
$kodeVhc=checkPostGet('kodeVhc','');
$brtCust=checkPostGet('brtCust','');
$txtKntrk=checkPostGet('txtKntrk','');
$nodo=checkPostGet('nodo','');

    switch($proses){
		
		case'getnodo':
			$opt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str=" select distinct nodo from ".$dbname.".pmn_suratperintahpengiriman where nokontrak='".$noKontrak."' ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()) {
                if($nodo!='') {
                   $opt.="<option value='".$bar['nodo']."' ".($bar['nodo']==$nodo?"selected":"").">".$bar['nodo']."</option>";
                } else {
                    $opt.="<option value='".$bar['nodo']."'>".$bar['nodo']."</option>";
                }
            }
            echo $opt;
		
		break;
		
        case'getCustomer':
            $optCust="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sCust=" select distinct koderekanan from ".$dbname.".pmn_kontrakjual where kodebarang=".$kdBrg." ";
            $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
            $qCust->setFetchMode(PDO::FETCH_ASSOC);
            while($rCust=$qCust->fetch())
            {
                if($custId!='')
                {
                   $optCust.="<option value='".$rCust['koderekanan']."' ".($rCust['koderekanan']==$custId?"selected":"").">".$optNma[$rCust['koderekanan']]."</option>";
                }
                else
                {
                    $optCust.="<option value='".$rCust['koderekanan']."'>".$optNma[$rCust['koderekanan']]."</option>";
                }
            }
            echo $optCust;
        break;
        case'getKontrak':
            $optCust="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sCust=" select distinct nokontrak from ".$dbname.".pmn_kontrakjual where koderekanan='".$custId."' order by tanggalkontrak desc";
            $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
            $qCust->setFetchMode(PDO::FETCH_ASSOC);
            while($rCust=$qCust->fetch())
            {
                if($custId!='')
                {
                   $optCust.="<option value='".$rCust['nokontrak']."' ".($rCust['nokontrak']==$noKontrak?"selected":"").">".$rCust['nokontrak']."</option>";
                }
                else
                {
                    $optCust.="<option value='".$rCust['nokontrak']."'>".$optNma[$rCust['nokontrak']]."</option>";
                }
            }
            echo $optCust;
        break;
        case'getForm':
        if($noKontrak=='')
        {
            exit("Error: Contract number required");
        }
        $tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<th align=center>No.</th>";        
        $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";    
        $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";    
        $tab.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";    
        $tab.="<th align=center width=100px>".$_SESSION['lang']['beratBersih']." PKS (KG)</th>";    
        $tab.="<th align=center style=width:75px>".$_SESSION['lang']['beratBersih']." ".substr($_SESSION['lang']['kodecustomer'],5)."</th>";    
        $tab.="<th align=center>".$_SESSION['lang']['action']."</th>";     
        $tab.="</tr></thead><tbody>";
        $sData="select notransaksi,tanggal,beratbersih,nokendaraan,nokontrak,kgpembeli from ".$dbname.".pabrik_timbangan 
		where nokontrak='".$noKontrak."' and nosipb='".$nodo."'";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);
        if($row>0) {
            while($rData=$qData->fetch()) {
                $no+=1;
                $tab.="<tr class=rowcontent id=baris_".$no.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center id=notiket_".$no.">".$rData['notransaksi']."</td>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td id=kendaran_".$no.">".$rData['nokendaraan']."</td>";
                $tab.="<td align=right >".number_format($rData['beratbersih'],2)."</td>";
                $tab.="<td><input style=width:75px type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='brtCust_".$no."' value='".$rData['kgpembeli']."' /></td>";
                $tab.="<td align=center><button class=mybutton id=simTmbl2_".$no." onclick=saveForm('".$rData['notransaksi']."','".$rData['nokendaraan']."','".$rData['nokontrak']."','".$no."')>".$_SESSION['lang']['save']."</button></td>";
                $tab.="</tr>";
            }
            $tab.="<thead><tr><td colspan=7 align=center><button class=mybutton id=simTmbl_".$no." onclick=saveAll(1)>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr>";
        }
        else
        {
         $tab.="<tr><td colspan=7 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";   
         $tab.="<thead><tr><td colspan=7 align=center><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr></thead>";   
        }
        $tab.="</tbody></table><input type='hidden' id=nokontrak2 value='".$noKontrak."' /><input type='hidden' id=jmlhRow value='".$no."' />";
        echo $tab;
        break;


        case'loadData':
		$where="";
		if($txtSearch!=''){
			$where.=" and notransaksi like '%".$txtSearch."%'";
		}
		if($txtTgl!=''){
			$thn=substr($txtTgl,0,4);
			$bln=substr($txtTgl,4,2);
			$tgl=substr($txtTgl,6,2);
			$txtTgl=$thn."-".$bln."-".$tgl;
			$where.=" and substr(tanggal,1,10)='".$txtTgl."'";
		}
		if($txtKntrk!=''){
			$where.=" and nokontrak like '%".$txtKntrk."%'";
		}
		
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
			$page=0;
        }
        $offset=$page*$limit;
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$vOrg = " and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		}else{
				$vOrg = " and millcode = '".$_SESSION['empl']['lokasitugas']."'";
		}

        $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$vOrg." ".$where." order by tanggal desc";// echo $ql2;
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch()){
            $jlhbrs= $jsl->jmlhrow;
        }


        $tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<th align=center>No.</th>";        
        $tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";  
        $tab.="<th align=center>".$_SESSION['lang']['NoKontrak']." (WB)</th>";  
        $tab.="<th align=center>".$_SESSION['lang']['NoKontrak']." (Sales)</th>";  
        $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";    
        $tab.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";    
        $tab.="<th align=center width=100px>".$_SESSION['lang']['beratBersih']." Internal (KG)</th>";    
        $tab.="<th align=center width=100px>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</th>";    
        $tab.="<th align=center colspan=2>".$_SESSION['lang']['action']."</th>";     
        $tab.="</tr></thead><tbody>";
        $sData="select notransaksi,tanggal,beratbersih,nokendaraan,nokontrak,kodebarang,kgpembeli,nosipb from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$vOrg." ".$where." order by tanggal desc limit ".$offset.",".$limit."";

        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);

        if($row>0){
            while($rData=$qData->fetch()){
                $sCust=" select distinct nokontrak,koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$rData['nokontrak']."' ";
                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                $rCust=$qCust->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent id=baris_".$no.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td id=notiket_".$no.">".$rData['notransaksi']."</td>";
                $tab.="<td id=nokontrak_".$no.">".$rData['nokontrak']."</td>";
				$tab.="<td id=nokontrak_".$no.">".$rCust['nokontrak']."</td>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td id=kendaran_".$no.">".$rData['nokendaraan']."</td>";
                $tab.="<td align=right>".number_format($rData['beratbersih'],2)."</td>";
                $tab.="<td align=right>".number_format($rData['kgpembeli'],2)."</td>";
                $tab.="<td align='center' width=25px>
                    <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rData['kodebarang']."','".$rCust['koderekanan']."','".$rCust['nokontrak']."','".$rData['nosipb']."');\"></td>";
                    
				$tab.="<td align='center' width=25px><img src=images/application/application_link.png class=resicon  title='Loco ".$rCust['nokontrak']."' onclick=\"locoData('".$rData['kodebarang']."','".$rCust['koderekanan']."','".$rCust['nokontrak']."','".$rData['nosipb']."');\">  </td>";
                $tab.="</tr>";
            }
        }else{
         $tab.="<tr class=rowcontent><td colspan=10 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";   

        }
        $tab.="
        </tr><tr class=rowheader><td colspan=10 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr></tbody></table>";
        echo $tab;
        break;

        case'cariTransaksi':
                                // print_r($_SESSION['empl']);
            if($txtSearch!=''){
                $where.=" and notransaksi like '%".$txtSearch."%'";
            }
            if($txtTgl!=''){
                $thn=substr($txtTgl,0,4);
                $bln=substr($txtTgl,4,2);
                $tgl=substr($txtTgl,6,2);
                $txtTgl=$thn."-".$bln."-".$tgl;
                $where.=" and substr(tanggal,1,10)='".$txtTgl."'";
            }
            if($txtKntrk!=''){
                $where.=" and nokontrak like '%".$txtKntrk."%'";
            }
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;

		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$vOrg = " and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		}else{
				$vOrg = " and millcode = '".$_SESSION['empl']['lokasitugas']."'";
		}

        $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$where." ".$vOrg." order by tanggal desc"; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch())
        {
            $jlhbrs= $jsl->jmlhrow;
        }


        $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<td align=center>No.</td>";        
        $tab.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";  
        $tab.="<td align=center>".$_SESSION['lang']['NoKontrak']." WB</td>";  
        $tab.="<td align=center>".$_SESSION['lang']['NoKontrak']." Sales</td>";  
        $tab.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['kodevhc']."</td>";    
        $tab.="<td align=center width=100px>".$_SESSION['lang']['beratBersih']." PKS (KG)</td>";    
        $tab.="<td align=center width=100px>".$_SESSION['lang']['beratBersih']." ".substr($_SESSION['lang']['kodecustomer'],5)."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['action']."</td>";     
        $tab.="</tr></thead><tbody>";
        $sData="select notransaksi,tanggal,beratbersih,nokendaraan,nokontrak,kodebarang,kgpembeli,nosipb from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$where." ".$vOrg." order by tanggal desc limit ".$offset.",".$limit."";
		
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);
        if($row>0)
        {
            while($rData=$qData->fetch())
            {
                $sCust=" select distinct nokontrak,koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$rData['nokontrak']."' ";
                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                $rCust=$qCust->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td id=notiket_".$no.">".$rData['notransaksi']."</td>";
				$tab.="<td id=nokontrak_".$no.">".$rData['nokontrak']."</td>";
				$tab.="<td id=nokontrak_".$no.">".$rCust['nokontrak']."</td>";
                $tab.="<td>".tanggalnormal($rData['tanggal'])."</td>";
                $tab.="<td id=kendaran_".$no.">".$rData['nokendaraan']."</td>";
                $tab.="<td align=right>".number_format($rData['beratbersih'],2)."</td>";
                $tab.="<td align=right>".number_format($rData['kgpembeli'],2)."</td>";
                $tab.="<td align='center'>
                    <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rData['kodebarang']."','".$rCust['koderekanan']."','".$rData['nokontrak']."','".$rData['nosipb']."');\">
                    &nbsp;
                    <img src=images/application/application_link.png class=resicon  title='Loco ".$rData['nokontrak']."' onclick=\"locoData('".$rData['kodebarang']."','".$rCust['koderekanan']."','".$rData['nokontrak']."','".$rData['nosipb']."');\">
                    </td>";
                $tab.="</tr>";
            }

        }
        else
        {
         $tab.="<tr class=rowcontent><td colspan=9 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";   

        }
        $tab.="
        </tr><tr class=rowheader><td colspan=9 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=cariBastTransaksi(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBastTransaksi(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr></tbody></table>";
        echo $tab;
        
        break;
        case'updateTimAll':
        $sUpdate="update ".$dbname.".pabrik_timbangan set kgpembeli='".$brtCust."' where notransaksi='".$noTiket."'";
        try
        {
            $owlPDO->exec($sUpdate);
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
            
        break;
        
        
        
        case'updateKgTimbangan':
        $sData="select notransaksi,tanggal,beratbersih,nokendaraan,nokontrak,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak='".$noKontrak."' and nosipb='".$nodo."'";
       $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);
        if($row>0)
        {
            while($rData=$qData->fetch())
            {
                  $sUpdate="update ".$dbname.".pabrik_timbangan set kgpembeli='".$rData['beratbersih']."' where notransaksi='".$rData['notransaksi']."'";
                   try
                    {
                        $owlPDO->exec($sUpdate);
                    }
                    catch (PDOException $e) 
                    {
                        print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                        die(); 
                    }
            }
        }
        else
        {
            exit("Error: ".$_SESSION['lang']['dataempty']);
        }
        break;
        default:
        break;
    }
?>