<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$proses=checkPostGet('proses','');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$gudang=checkPostGet('gudang','');
$periode=checkPostGet('periode','');
$klbrg=checkPostGet('klbrg','');
$stok=checkPostGet('stok','');

$kdorg=checkPostGet('kdorg','');
$kdgudang=checkPostGet('kdgudang','');
$kdbarang=checkPostGet('kdbarang','');
$kuantitas=checkPostGet('kuantitas','');
$per=checkPostGet('per','');




    switch($proses){


    case'deletestok':
        #update flag
        $str = "delete from " . $dbname . ".log_stokopname where kodeorg='" .$unit. "' and gudang='".$gudang."' and periode='".$periode."' ";
        // echo $str;
        // exit();
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    

    case'posting':
        #update flag
        $str = "update " . $dbname . ".log_stokopname set posting='1' where  kodeorg='" .$unit. "' and gudang='".$gudang."' and periode='".$periode."' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    

         case 'getklbrg':
            $optklbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select distinct(b.kelompokbarang), c.kelompok from ".$dbname.".log_5saldobulanan a 
            left join log_5masterbarang b on b.kodebarang=a.kodebarang
            left join log_5klbarang c on c.kode=b.kelompokbarang
                where  kodegudang= '".$gudang."' and periode='".$periode."' and saldoakhirqty > 0 ";

            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch()){    
                $optklbrg.="<option value=".$res['kelompokbarang'].">".$res['kelompokbarang']."-".$res['kelompok']."</option>";
            }
            if($gudang=='')$optklbrg="<option value=''></option>";
            echo $optklbrg;
            break;


        case 'getperiode':
            $optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select kodeorg, periode from ".$dbname.".setup_periodeakuntansi 
                where kodeorg='".$gudang."' and tutupbuku='1' and periode not in (select periode from ".$dbname.".log_stokopname where posting='1' )";
            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch()){    
                $optperiode.="<option value=".$res['periode'].">".$res['periode']."</option>";
            }
            if($gudang=='')$optperiode="<option value=''></option>";
            echo $optperiode;
            break;
		
		

        case 'getkebun':
            $optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where induk='".$pt."' and tipe in ('KEBUN','PABRIK')";
            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch()){    
                $optkebun.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>";
            }
            if($pt=='')$optkebun="<option value=''></option>";
            echo $optkebun;
            break;

            case 'getgudang':
            $optgdg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where induk='".$unit."' and tipe='GUDANG'";
            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch()){    
                $optgdg.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>";
            }
            if($unit=='')$optgdg="<option value=''></option>";
            echo $optgdg;
            break;
		
       

        case'getForm':

        $where="";
        if($klbrg!=''){
            $where="and b.kelompokbarang='".$klbrg."'";
        }
        
        $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<td align=center>No.</td>";        
        $tab.="<td align=center>".$_SESSION['lang']['unit']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['gudang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['kodebarang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['namabarang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['periode']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['kuantitas']."</td>";    

        $tab.="<td align=center>".$_SESSION['lang']['action']."</td>";     
        $tab.="</tr></thead><tbody>";
         $sData="select a.kodegudang, d.induk as unit, a.kodebarang, b.kelompokbarang, c.kelompok, b.namabarang, a.saldoakhirqty from ".$dbname.".log_5saldobulanan a 
            left join log_5masterbarang b on b.kodebarang=a.kodebarang
            left join log_5klbarang c on c.kode=b.kelompokbarang
            left join organisasi d on d.kodeorganisasi=a.kodegudang
                where a.periode='".$periode."' and a.saldoakhirqty > 0 and a.kodebarang not in (select kodebarang from ".$dbname.".log_stokopname where gudang = '".$gudang."' and periode='".$periode."' ) and a.kodegudang= '".$gudang."' ".$where." ";
                // echo $sData;
                // exit();
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);
        if($row>0) {
            while($rData=$qData->fetch()) {
                $no+=1;
                $tab.="<tr class=rowcontent id=baris_".$no.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center id=kdorg".$no.">".$rData['unit']."</td>";
                $tab.="<td align=center id=kdgdg".$no.">".$rData['kodegudang']."</td>";
                $tab.="<td align=center id=kdbrg".$no.">".$rData['kodebarang']."</td>";
                $tab.="<td id=nmbrg".$no.">".$rData['namabarang']."</td>";
                $tab.="<td id=periode".$no.">".$periode."</td>";
                $tab.="<td id=stoksistem".$no." hidden>".$rData['saldoakhirqty']."</td>";
                $tab.="<td><input style=width:75px type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='kuantitas_".$no."' value='0' /></td>";
                $tab.="<td align=center><button class=mybutton id=simTmbl2_".$no." onclick=saveForm('".$rData['unit']."','".$rData['kodegudang']."','".$rData['kodebarang']."','".$periode."','".$rData['saldoakhirqty']."','".$no."')>".$_SESSION['lang']['save']."</button></td>";
                $tab.="</tr>";
            }
            $tab.="<thead><tr><td colspan=8 align=center><button class=mybutton id=simTmbl_".$no." onclick=saveAll(1)>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr>";
        }
        else
        {
         $tab.="<tr><td colspan=8 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";   
         $tab.="<thead><tr><td colspan=8 align=center><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr></thead>";   
        }
        $tab.="</tbody></table><input type='hidden' id=nokontrak2 value='".$noKontrak."' /><input type='hidden' id=jmlhRow value='".$no."' />";
        echo $tab;
        break;

        case'getForm2':

        $sDataCek="select * from ".$dbname.".log_stokopname a
        left join log_5masterbarang b on b.kodebarang=a.kodebarang
        where gudang='".$gudang."' and periode='".$periode."' ";
        $qDataCek=$owlPDO->query($sDataCek) or die(print " Gagal: ".PDOException::getMessage());
        $qDataCek->setFetchMode(PDO::FETCH_ASSOC);
        $rDataCek=$qDataCek->fetch();
        $hide3="";
        if($rDataCek['posting']==1){
            $hide3="hidden";
        }
        
        $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<td align=center>No.</td>";        
        $tab.="<td align=center>".$_SESSION['lang']['unit']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['gudang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['kodebarang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['namabarang']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['periode']."</td>";    
        $tab.="<td align=center>".$_SESSION['lang']['kuantitas']."</td>";    

        $tab.="<td ".$hide3." align=center>".$_SESSION['lang']['action']."</td>";     
        $tab.="</tr></thead><tbody>";

        $sData="select * from ".$dbname.".log_stokopname a
        left join log_5masterbarang b on b.kodebarang=a.kodebarang
        where gudang='".$gudang."' and periode='".$periode."' ";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);
        if($row>0) {
            while($rData=$qData->fetch()) {
                
                $hide="";
                if($rData['posting']==1){
                    $hide="hidden";
                    $disabled="disabled";
                }

                $no+=1;
                $tab.="<tr class=rowcontent id=baris_".$no.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center id=kdorg".$no.">".$rData['kodeorg']."</td>";
                $tab.="<td align=center id=kdgdg".$no.">".$rData['gudang']."</td>";
                $tab.="<td align=center id=kdbrg".$no.">".$rData['kodebarang']."</td>";
                $tab.="<td id=nmbrg".$no.">".$rData['namabarang']."</td>";
                $tab.="<td id=periode".$no.">".$periode."</td>";
                // $tab.="<td align=right >".number_format($rData['saldoakhirqty'],2)."</td>";
                $tab.="<td><input ".$disabled." style=width:75px type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' id='kuantitas_".$no."' value='".$rData['stok']."' /></td>";
                $tab.="<td ".$hide." align=center><button class=mybutton id=simTmbl2_".$no." onclick=saveForm('".$rData['unit']."','".$rData['kodegudang']."','".$rData['kodebarang']."','".$periode."','".$no."')>".$_SESSION['lang']['save']."</button></td>";
                $tab.="</tr>";
            }
            $tab.="<thead><tr><td ".$hide." colspan=8 align=center><button class=mybutton id=simTmbl_".$no." onclick=saveAll(1)>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr>";
        }
        else
        {
         $tab.="<tr><td colspan=8 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";   
         $tab.="<thead><tr><td colspan=8 align=center><button class=mybutton id=dtlForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button></td></tr></thead>";   
        }
        $tab.="</tbody></table><input type='hidden' id=nokontrak2 value='".$noKontrak."' /><input type='hidden' id=jmlhRow value='".$no."' />";
        echo $tab;
        break;


        case'loadData':
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
                        

        $ql2="select count(*) as jmlhrow from ".$dbname.".log_stokopname ";// echo $ql2;
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch())
        {
            $jlhbrs= $jsl->jmlhrow;
        }

         


        $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead><tr class=rowheader>";
        $tab.="<td align=center>No.</td>";        
        $tab.="<td align=center>".$_SESSION['lang']['kodeorg']."</td>";  
        $tab.="<td align=center>".$_SESSION['lang']['gudang']."</td>";  
        $tab.="<td align=center>".$_SESSION['lang']['periode']."</td>";  
     
         
        $tab.="<td align=center colspan=2>".$_SESSION['lang']['action']."</td>";     
        $tab.="</tr></thead><tbody>";

        $sData="select * from ".$dbname.".log_stokopname group by gudang,periode limit ".$offset.",".$limit."";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qData);

        if($row>0)
        {
            while($rData=$qData->fetch())
            {
                $hide="";
                $post="<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
                            onclick=\"posting('" . $rData['kodeorg'] . "','".$rData['gudang']."','".$rData['periode']."');\" >";
                if($rData['posting']==1){
                    $hide="hidden";
                    $post="<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting');\" >";
                }

                $hide2="";
                if($rData['posting']==0){
                    $hide2="hidden";
                }

                $no+=1;
                $tab.="<tr class=rowcontent id=baris_".$no.">";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td id=notiket_".$no.">".$rData['kodeorg']."</td>";
                $tab.="<td id=nokontrak_".$no.">".$rData['gudang']."</td>";
				$tab.="<td id=nokontrak_".$no.">".$rData['periode']."</td>";
                $tab.="
                <td align='center'>
                    <img ".$hide." src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rData['kodeorg']."','".$rData['gudang']."','".$rData['periode']."');\">
                    
                    <img ".$hide." src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delStok('".$rData['kodeorg']."','".$rData['gudang']."','".$rData['periode']."');\">

                      <img ".$hide2." src=images/zoom.png class=resicon  title='Edit' onclick=\"fillField('".$rData['kodeorg']."','".$rData['gudang']."','".$rData['periode']."');\">

                    
                     ".$post."</td>";
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
        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr></tbody></table>";
        echo $tab;
        break;

        case'cariTransaksi':
                                // print_r($_SESSION['empl']);
            if($txtSearch!='')
            {
                $where.=" and notransaksi like '%".$txtSearch."%'";
            }
            if($txtTgl!='')
            {
                $thn=substr($txtTgl,0,4);
                $bln=substr($txtTgl,4,2);
                $tgl=substr($txtTgl,6,2);
                $txtTgl=$thn."-".$bln."-".$tgl;
                $where.=" and substr(tanggal,1,10)='".$txtTgl."'";
            }
            if($txtKntrk!='')
            {
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

        $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_timbangan where nokontrak!='' ".$where." ".$vOrg." order by tanggal desc";// echo $ql2;
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
        case'insert':

        $sInsert="insert into ".$dbname.".log_stokopname (`kodeorg`,`gudang`,`kodebarang`,`stok`,`stok_sistem`,`periode`,`posting`,`createby`)
        values
        ('".$kdorg."', '".$kdgudang."', '".$kdbarang."','".$kuantitas."','".$stok."','".$per."','0','".$_SESSION['standard']['userid']."' ) ";
        // echo $sInsert;
        // exit();
        try
        {
            $owlPDO->exec($sInsert);
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