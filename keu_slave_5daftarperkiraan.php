<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$kodeorg    = checkPostGet('kodeorg','');
$noakun     = checkPostGet('noakun','');
$noakundt   = checkPostGet('noakundt','');
$namaakun   = checkPostGet('namaakun','');
$namaakun1  = checkPostGet('namaakun1','');
$tipeakun   = checkPostGet('tipeakun','');
$level      = checkPostGet('level','');
$matauang   = checkPostGet('matauang','');
$namapemilik= checkPostGet('pemilik','');
$fieldaktif = checkPostGet('fieldaktif','');
$method     = checkPostGet('method','');
$pages      = checkPostGet('page', '');
$txt_search = checkPostGet('txtsearch', '');
$txtNoakun  = checkPostGet('txtNoakun', '');
// bikin baru lagi pake array untuk load data yg checkbox
$strnama = array ("0"=>"Kelompok","1"=>"Detail");
$strnama1 = array ("0"=>"Bukan Kasbank","1"=>"Kasbank");

$kasbankdetail = checkPostGet('kasbankdetail', '');
$kodekegiatan = checkPostGet('kodekegiatan', '');
$kodeblok    = checkPostGet('kodeblok', '');
$tagihan     = checkPostGet('tagihan','0');
$kodeasset   = checkPostGet('kodeasset', '');
$kodesupplier = checkPostGet('kodesupplier', '');
$jurnalmemorial     = checkPostGet('jurnalmemorial','0');
$nik         = checkPostGet('nik', '');
$kodevhc     = checkPostGet('kodevhc', '');
$detail     = checkPostGet('detail','');
$nodok   = checkPostGet('nodok', '');
$kodecustomer= checkPostGet('kodecustomer', '');
$kasbank    = checkPostGet('kasbank','');
$kodeunitdt    = checkPostGet('kodeunitdt','');


$optunit="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	$nmunit[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}
//=$optakun
$str = "SELECT * FROM " . $dbname . ".keu_5akun where noakun='".$noakundt."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$optakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

switch ($method) {
	
	case'savedt':
		$str="insert into ".$dbname.".keu_5akununit (noakun,kodeunit) values ";
        $str.="('".$noakundt."','".$kodeunitdt."')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$str;
            die();
        }
	break;
	
	case'deldt':
		$str="delete from ".$dbname.".keu_5akununit where kodeunit='".$kodeunitdt."' and noakun='".$noakundt."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$str;
            die();
        }
	break;
	
	
	case'viewdetailbaru':
	
        $tab="";
		@$nmdetail=makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay',"nourut='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."'");		
		
        //$tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['input']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['noakun']."</td>
                <td>:</td>
                <td><select disabled id=noakundt style='width:250px;'>".$optakun."</select></td>
            </tr>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select  id=kodeunitdt style='width:250px;'>".$optunit."</select></td>
            </tr>";
       
		$tab.="<td colspan=2></td><td colspan=2><button class=mybutton style='cursor:pointer;' onclick='savedt()'>".$_SESSION['lang']['save']."</button></td></tr>";
        
		$tab.="</table>";	
		// $tab.="</fieldset>";	
		$tab.="<div style=clear:both></div>";	
		
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <th>".$_SESSION['lang']['nomor']."</th>
                <th>".$_SESSION['lang']['kodeorganisasi']."</th>
                <th>".$_SESSION['lang']['namaorganisasi']."</th>
                <th>".$_SESSION['lang']['action']."</th>
            </tr></thead>";
		#= isi data
		$str = "SELECT * FROM " . $dbname . ".keu_5akununit where noakun='".$noakundt."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no+=1;
			$tab.="<tr class=rowcontent>
                <td align=center>".$no."</td>
                <td align=center>".$bar['kodeunit']."</td>
                <td>".$nmunit[$bar['kodeunit']]."</td>
                <td align=center><img src='images/skyblue/delete.png' class='resicon' title='Delete ".$bar['noakun']."' onclick=\"deldt('".$bar['noakun']."','".$bar['kodeunit']."')\"></td>               
            </tr>";
		}
		
		$tab.="</table>";	
		$tab.="</fieldset>";
		
		echo $tab;
		
	break;

    case 'insert':
       $fieldaktif=$kasbankdetail.$tagihan.$jurnalmemorial;
		$fieldaktif.=$kodekegiatan.$kodeasset.$nik.$kodecustomer.$kodesupplier.$kodevhc.$kodeblok.$nodok;
        $input = "insert into " . $dbname . ".keu_5akun
                  (kodeorg,noakun,namaakun,namaakun1,tipeakun,level, matauang,detail,kasbank,fieldaktif,pemilik,jurnalmemorial,kodekegiatan,kodeasset,nik,kodecustomer,kodesupplier,kodevhc,kodeblok,nodok,kasbankdetail,tagihan)
            values ('" . $kodeorg . "','" . $noakun . "','" . $namaakun . "','" . $namaakun1 . "','" . $tipeakun . "','" . $level . "','" . $matauang . "','" . $detail . "','" . $kasbank . "','" . $fieldaktif . "','" . $namapemilik . "','" . $jurnalmemorial . "','" . $kodekegiatan . "','" . $kodeasset . "','" . $nik . "','" . $kodecustomer . "','" . $kodesupplier . "','" . $kodevhc . "','" . $kodeblok . "','" . $nodok . "','" . $kasbankdetail . "','" . $tagihan . "')";
    try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
        break;

    case 'update':
		$fieldaktif=$kasbankdetail.$tagihan.$jurnalmemorial;
    $fieldaktif.=$kodekegiatan.$kodeasset.$nik.$kodecustomer.$kodesupplier.$kodevhc.$kodeblok.$nodok;
    // exit ('error:a');
        $input = "update " . $dbname . ".keu_5akun set noakun='" . $noakun . "',namaakun='" . $namaakun . "',namaakun1='" . $namaakun1 . "',
      tipeakun='" . $tipeakun . "',level='" . $level . "',matauang='" . $matauang . "',detail='" . $detail . "',kasbank='" . $kasbank . "',
      fieldaktif='" . $fieldaktif . "',pemilik='" . $namapemilik . "',jurnalmemorial='" . $jurnalmemorial . "',kodekegiatan='" . $kodekegiatan . "',kodeasset='" . $kodeasset . "',nik='" . $nik . "',kodecustomer='" . $kodecustomer . "',kodesupplier='" . $kodesupplier . "',kodevhc='" . $kodevhc . "',kodeblok='" . $kodeblok . "',nodok='" . $nodok . "',kasbankdetail='" . $kasbankdetail . "',tagihan='" . $tagihan . "'
             where noakun='" . $noakun . "' and kodeorg='" . $kodeorg."'";
              // exit('error:'.$input);
        try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
            // fdfdfdfdfdf
        break;

      case'cariBarangDlmDtBs':
    $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
    $str="select * from ".$dbname.".keu_5akun where namaakun like '%".$txtfind."%'";
    // echo $str;
    // $res=$owlPDO->query($str);
    
    if($res=$owlPDO->query($str)){
      echo "<fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; max-height:300px;\" >
        <table class=sortable cellspacing=1 cellpadding=2  border=0>
          <thead>
          <tr class=rowheader>
            <td class=firsttd align=center>No.</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
            <td align=center>".$_SESSION['lang']['namaakun']."</td>
           
          </tr>
          </thead>
          <tbody>";
          
      $no=0;   
      $res->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$res->fetch()){
        $no+=1;
        
        echo "
       <tr class=rowcontent>
        <td class=firsttd  align=center>".$no."</td>
            <td align=left>".$bar->noakun."</td>
            <td align=left>".$bar->namaakun."</td>
      </tr>";
      }  
         
      echo "</tbody>
        <tfoot>
        </tfoot>
        </table></div></fieldset>";
    }else{
      echo " Gagal,".PDOException::getMessage();
    }
  break;

  case'cariNoAkun':
    $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
    $str="select * from ".$dbname.".keu_5akun where noakun like '%".$txtfind."%'";
    // echo $str;
    // $res=$owlPDO->query($str);
    
    if($res=$owlPDO->query($str)){
      echo "<fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; max-height:300px;\" >
        <table class=sortable cellspacing=1 cellpadding=2  border=0>
          <thead>
          <tr class=rowheader>
            <td class=firsttd align=center>No.</td>
            <td align=center>".$_SESSION['lang']['noakun']."</td>
            <td align=center>".$_SESSION['lang']['namaakun']."</td>
           
          </tr>
          </thead>
          <tbody>";
          
      $no=0;   
      $res->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$res->fetch()){
        $no+=1;
        
        echo "
       <tr class=rowcontent>
        <td class=firsttd  align=center>".$no."</td>
            <td align=left>".$bar->noakun."</td>
            <td align=left>".$bar->namaakun."</td>
      </tr>";
      }  
         
      echo "</tbody>
        <tfoot>
        </tfoot>
        </table></div></fieldset>";
    }else{
      echo " Gagal,".PDOException::getMessage();
    }
  break;

    case'loadData':
    if($txt_search!='')
    {
       $where=" and namaakun LIKE  '%".$txt_search."%'";
    }
    if($txtNoakun!='')
    {
       $where=" and noakun LIKE  '%".$txtNoakun."%'";
    }
	echo"
    <table class=sortable cellpadding=4 cellspacing=1 border=0>
      <thead>
       <tr class=rowheader>
         <th align=center>" . $_SESSION['lang']['noakun'] . "</th>
         <th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
         <!--<th align=center>" . $_SESSION['lang']['namaakun']." (EN)</th> -->
         <th align=center>" . $_SESSION['lang']['tipeakun'] . "</th>
         <th align=center>" . $_SESSION['lang']['level'] . "</th>
         <th align=center>" . $_SESSION['lang']['matauang'] . "</th>
         <th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
         <th align=center>" . $_SESSION['lang']['pemilik'] . "</th>
         <th align=center>" . $_SESSION['lang']['kasbank'] . "</th>
         <th align=center>" . $_SESSION['lang']['detail'] . "</th>
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
         <th align=center>" . $_SESSION['lang']['action'] . "</th>
       </tr>
      </thead>
    <tbody>";
	$tab='';
	$nor=0;
  $nmdetail2=makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay');
	$input = "select * from " . $dbname . ".keu_5akun where noakun<>'' ".$where."";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = @$maxdisplay;
        while ($d = $n->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>";
            echo"<td align=left>" . $d['noakun'] . "</td>";
            echo"<td align=left>" . $d['namaakun'] . "</td>";
            //echo"<td align=left>" . $d['namaakun1'] . "</td>";
            echo"<td align=left>" . $d['tipeakun'] . "</td>";
            echo"<td align=center>" . $d['level'] . "</td>";
            echo"<td align=center>" . $d['matauang'] . "</td>";
            echo"<td align=center>" . $d['kodeorg'] . "</td>";
            echo"<td align=left>" . $d['pemilik'] . "</td>";
            if($d['kasbank'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['detail'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kasbankdetail'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodekegiatan'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodeblok'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['tagihan'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodeasset'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodesupplier'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['jurnalmemorial'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['nik'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodevhc'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['nodok'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            if($d['kodecustomer'] == 1){
             echo"<td align=center><img src=images/done.png class=resicon title='True'></td>";
            } else {
             echo"<td align=center></td>";
            }
            echo"<td align=center nowrap>
				          <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit(
                  '".$d['noakun']."',
                  '".$d['namaakun']."',
                  '".$d['namaakun1']."',
                  '".$d['tipeakun']."',
                  '".$d['kasbank']."',
                  '".$d['level']."',
                  '".$d['matauang']."',
                  '".$d['kodeorg']."',
                  '".$d['detail']."',
                  '".$d['kasbankdetail']."',
                  '".$d['tagihan']."',
                  '".$d['jurnalmemorial']."',
                  '".$d['kodekegiatan']."',
                  '".$d['kodeasset']."',
                  '".$d['nik']."',
                  '".$d['kodecustomer']."',
                  '".$d['kodesupplier']."',
                  '".$d['kodevhc']."',
                  '".$d['kodeblok']."',
                  '".$d['nodok']."',
                  '".$d['pemilik']."');\">
          				<img src='images/skyblue/zoom.png' class='resicon' title='Add Detail ".@$nmdetail2[$d['noakun']]."' onclick=\"viewdetailbaru('".$d['noakun']."')\">
				      </td>";

            echo"</tr>"; 
        }
    echo"</tbody></table>";
	break;
    default:
}
?>
