<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$param = $_POST;
$proses = $_POST['proses'];

//        $sorg="select distinct kodetimbangan,namacustomer from ".$dbname.".pmn_4customer where kodetimbangan like '1%' order by namacustomer";
        $sorg="select distinct kodetimbangan,namasupplier from ".$dbname.".log_5supplier where kodetimbangan like '1%' order by namasupplier";
        $qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
        $qorg->setFetchMode(PDO::FETCH_ASSOC);
        while($rorg=$qorg->fetch()){
            $kamuscust[$rorg['kodetimbangan']]=$rorg['namasupplier'];
        }        
 
switch($proses) {
    # Daftar Header
    case 'loadData':
//	$where = "afdeling in (select distinct kodetimbangan from ".$dbname.".pmn_4customer where kodetimbangan like '1%' order by namacustomer)";
//	$where = "afdeling in (select distinct kodetimbangan from ".$dbname.".log_5supplier where kodetimbangan like '1%' order by namasupplier)";
	$where = "afdeling != '' and blok = ''";
        
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%><thead><tr align=center>";
//        $tab.="<td>".$_SESSION['lang']['mandor']."</td>";
        $tab.="<td>".$_SESSION['lang']['nmcust']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td>".$_SESSION['lang']['kg']."</td>";
//        $tab.="<td>".$_SESSION['lang']['bjr']."</td>";
        $tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody>";
        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        if(isset($_POST['page2']) and $_POST['page2']!=''){
         $page=$_POST['page2']-1;   
        }
        $offset=$page*$limit;
        
       
        $sdata="select distinct * from ".$dbname.".kebun_taksasi where ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
        //echo $sdata;
        $qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while($rdata=  $qdata->fetch()){
            $tab.="<tr class=rowcontent align=center>";
//            $tab.="<td>".$kamuskaryawan[$rdata['karyawanid']]."</td>";
            $tab.="<td>".$kamuscust[$rdata['afdeling']]."</td>";
            $tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
            $tab.="<td align=right>".$rdata['kg']."</td>";
//            $tab.="<td align=right>".$rdata['bjr']."</td>";
            $tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
            $tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
            $tab.="</tr>";
        }
        $tab.="</tbody><tfoot>";
        $tab.="<tr>";
        $tab.="<td colspan=10 align=center>";
        $tab.="<img src=\"images/skyblue/first.png\" onclick='loadData(0)' style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/prev.png\" onclick='loadData(".($page-1).")'  style='cursor:pointer'>";
        
        $spage="select distinct * from ".$dbname.".kebun_taksasi where ".$where."";
        //echo $spage;
        $qpage=$owlPDO->query($spage) or die(print " Gagal: ".PDOException::getMessage());
        $qpage->setFetchMode(PDO::FETCH_ASSOC);
        $rpage=owlBaris($qpage);
        $tab.="<select id='pages' style='width:50px' onchange='loadData(1.1)'>";
        @$totalPage=ceil($rpage/10);
        for($starAwal=1;$starAwal<=$totalPage;$starAwal++)
        {
            $_POST['page']=='1.1'?$_POST['page']=$_POST['page2']:$_POST['page']=$_POST['page'];
            $tab.="<option value='".$starAwal."' ".($starAwal==$_POST['page']?'selected':'').">".$starAwal."</option>";
        }
        $tab.="</select>";
        $tab.="<img src=\"images/skyblue/next.png\" onclick='loadData(".($page+1).")'  style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/last.png\" onclick='loadData(".intval($totalPage).")'  style='cursor:pointer'>";
        $tab.="</td></tr></tfoot></table>";
	 
	echo $tab;
	break;
        case 'cariData':
//	$where = "afdeling in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING')";
        $where = "afdeling != '' and blok = ''";
		if(!empty($param['sNoTrans'])){
            $tgl=explode("-",$param['sNoTrans']);
            $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
            $where.=" and tanggal like '%".$param['tanggal']."%'";
        }
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%><thead><tr align=center>";
        $tab.="<td>".$_SESSION['lang']['nmcust']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td>".$_SESSION['lang']['kg']."</td>";
        $tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
        $tab.="</tr></thead><tbody>";
        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        if(isset($_POST['page2']) and $_POST['page2']!=''){
         $page=$_POST['page2']-1;   
        }
        $offset=$page*$limit;
		$sdata="select distinct * from ".$dbname.".kebun_taksasi where ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
        //echo $sdata;
        $qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while($rdata=  $qdata->fetch()){
            $tab.="<tr class=rowcontent align=center>";
//            $tab.="<td>".$kamuskaryawan[$rdata['karyawanid']]."</td>";
            $tab.="<td>".$kamuscust[$rdata['afdeling']]."</td>";
            $tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
            $tab.="<td align=right>".$rdata['kg']."</td>";
//            $tab.="<td align=right>".$rdata['bjr']."</td>";
            $tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
            $tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
            $tab.="</tr>";
        }
        $tab.="</tbody><tfoot>";
        $tab.="<tr>";
        $tab.="<td colspan=10 align=center>";
        $tab.="<img src=\"images/skyblue/first.png\" onclick='cariData(0)' style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/prev.png\" onclick='cariData(".($page-1).")'  style='cursor:pointer'>";
        
        $spage="select distinct * from ".$dbname.".kebun_taksasi where ".$where."";
        //echo $spage;
        $qpage=$owlPDO->query($spage) or die(print " Gagal: ".PDOException::getMessage());
        $qpage->setFetchMode(PDO::FETCH_ASSOC);
        $rpage=owlBaris($qpage);
        $tab.="<select id='pages' style='width:50px' onchange='cariData(1.1)'>";
        @$totalPage=ceil($rpage/10);
        for($starAwal=1;$starAwal<=$totalPage;$starAwal++)
        {
            $_POST['page']=='1.1'?$_POST['page']=$_POST['page2']:$_POST['page']=$_POST['page'];
            $tab.="<option value='".$starAwal."' ".($starAwal==$_POST['page']?'selected':'').">".$starAwal."</option>";
        }
        $tab.="</select>";
        $tab.="<img src=\"images/skyblue/next.png\" onclick='cariData(".($page+1).")'  style='cursor:pointer'>";
        $tab.="<img src=\"images/skyblue/last.png\" onclick='cariData(".intval($totalPage).")'  style='cursor:pointer'>";
        $tab.="</td></tr></tfoot></table>";
	# Content
	$cols = "notransaksi,tanggal,kodeorg,kodetangki,kuantitas,suhu";
	echo $tab;
	break;
   case'insert':
       #var ek//$arr="##tanggal##customer##proses##jjgmasak##bjr";
       $param['kg']==''?$param['kg']=0:$param['kg']=$param['kg'];
//       $param['bjr']==''?$param['bjr']=0:$param['bjr']=$param['bjr'];
       
       #end var
       
       $tgl=explode("-",$param['tanggal']);
       $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
       
       $scek2="select distinct * from ".$dbname.".kebun_taksasi where tanggal='".$param['tanggal']."' and afdeling='".$param['customer']."'";
       $qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
       $qcek2->setFetchMode(PDO::FETCH_ASSOC);
       $rcek2=owlBaris($qcek2);
       if($rcek2!=0){
//            exit("error: Data sudah pernah diinput.");
           
            $sins="update ".$dbname.".kebun_taksasi  set `kg`='".$param['kg']."'
             where tanggal='".$param['tanggal']."' and afdeling='".$param['customer']."'";
            try{
                $owlPDO->exec($sins); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
       }else{
            $scek="select distinct * from ".$dbname.".kebun_taksasi 
              where tanggal='".$param['tanggal']."' and afdeling='".$param['customer']."'";
            //exit("error:".$scek);
            $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
            $qcek->setFetchMode(PDO::FETCH_ASSOC);
            $rcek=owlBaris($qcek);
            if($rcek!=0){
            exit("error:Data Sudah Ada");
            }
            $sins="insert into ".$dbname.".kebun_taksasi  
            (`afdeling`,`tanggal`, `kg`)
            values ('".$param['customer']."','".$param['tanggal']."','".$param['kg']."')";
            try{
                $owlPDO->exec($sins); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
       }

   break;
   case'getData':
    $tgl=explode("-",$param['tanggal']);
    $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
    $str="select distinct * from ".$dbname.".kebun_taksasi 
          where tanggal='".$param['tanggal']."' and 
          afdeling='".$param['afdeling']."'";
   //exit("error:".$str);
   $qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
   $qstr->setFetchMode(PDO::FETCH_ASSOC);
   $rts=$qstr->fetch();
   
   echo $rts['afdeling']."###".tanggalnormal($rts['tanggal'])."###".$rts['kg'];
   break;
    case 'delete': 
    $tgl=explode("-",$param['tanggal']);
    $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
	$where = "tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."'";
	$query = "delete from `".$dbname."`.`kebun_taksasi` where ".$where;
        //exit("error:".$query);
    try{
        $owlPDO->exec($query); 
    }catch (PDOException $e){
        echo "DB Error : " . $e->getMessage();
        die();
    }
	 
    break;
    
}
?>