<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi"," length(kodeorganisasi)='4'",2,true);


switch($proses) {
    case 'loadData' :
    
    
    $limit = 20;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $offset = $page * $limit;

    $qcount ="select a.notransaksi, a.unit, b.namaorganisasi, a.tanggal from ".$dbname.".lgl_pengajuanspkht a 
                Left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
                WHERE a.unit='".$data['unit']."' and a.tanggal='".tanggalsystem($data['tanggal'])."' and a.statuspersetujuan='1'";
    $rcount = fetchData($qcount);
    $jlhbrs = count($rcount);

    $totalPage = ceil($jlhbrs/$limit);
    $optPage = array();
    $totalPage<1 ? $totalPage=1 : null;
    for($i=1;$i<=$totalPage;$i++) {
        $optPage[$i-1] = $i;
    }

     $queryAll ="select a.notransaksi, a.unit, b.namaorganisasi, a.kategori, a.jenis from ".$dbname.".lgl_pengajuanspkht a 
                Left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
                WHERE a.statuspersetujuan='1'   
                order by a.tanggal desc limit " . $offset . "," . $limit . "";
    $resAll = fetchData($queryAll);
    //exit($queryAll);
    $header = array("No urut","No Transaksi","Nama Unit","Kategori","Jenis");
    $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    $table .="<legend>List Progress SPK</legend>";
    $table .= "<table id='listData' class='sortable' cellspacing='1' border='0' >";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td>".$head."</td>";
    }
    $table .= "<td colspan=3 align=center>*</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody id='bodyList'>";
    foreach ($resAll as $key => $row) {
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        foreach($row as $col=>$dat) 
        {
            if($col=='unit')
            {
                $table .= "<td hidden id='unit_".$key."'>".$dat."</td>";
            }
            elseif($col=='notransaksi')
            {
                $table .= "<td id='nourut' align=center>".($key+1)."</td>";
                $table .= "<td id='notransaksi_".$key."'>".$dat."</td>";
            }
            else
            {
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";   
            }
        }
           
            $table .= "<td id='edit_".$key."'>";
                    $table .= "<img src='images/application/application_edit.png' ";
                    $table .= "class=resicon  caption='Edit' onclick=edit('".$row['notransaksi']."')></td>";
                    $table .= "<td><a href='#' onclick=dataKeExcel(event,'lgl_slave_progressSpk.php','".$row['notransaksi']."')><img  src=images/excel.jpg class=resicon title='MS.Excel'></a></td>";
                    $table .="<td align=center><img src=images/zoom.png class=resicon title='View' onclick=\"html('".$row['notransaksi']."','html');\"></td>";
            $table .= "</tr>";

    }

    $table .= "</tbody>";
    $table .="<tfoot><td colspan=9 align=center>
    <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast(0);>&nbsp;
    <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast(" . ($page - 1) . ");>&nbsp;
    ".makeElement('pages','select',$page,array('style'=>'width:50px',
        'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
    <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast(" . ($page + 1) . ");>&nbsp;
    <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast(".($totalPage-1).");>
    </td>
    </tfoot>";
    $table .= "</table>";
    $table .= "</fieldset>";

    echo $table;
    break;
    case 'checkdata' :
    $str = selectQuery($dbname,"lgl_progressspk","notransaksi","notransaksi='".$data['notransaksi']."'");
    $rsStr = fetchData($str);
    $jlhstr = count($rsStr);

    echo $jlhstr;
    break;
    case 'loadData2' :
    
    $queryAll ="select a.notransaksi, b.namaorganisasi, a.kategori, a.jenis from ".$dbname.".lgl_pengajuanspkht a 
                Left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
                WHERE a.notransaksi='".$data['notransaksi']."'   
                order by a.notransaksi desc";
    $resAll = fetchData($queryAll);

    $header = array("No urut","Nomor Transaksi","Nama Unit","Kategori","Jenis");
    $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    $table .="<legend>Detail</legend>";
    $table .= "<table cellspacing='1' border='0' class='sortable'>";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td rowspan=2>".$head."</td>";
    }
    $table.= "<td align=center colspan=5>Tanggal Progress Persetujuan Draft SPK</td>";
    $table.= "<td align=center colspan=2>Tanggal Pengiriman/Penerimaan SPK</td>";
    $table.= "<td align=center rowspan=2 >Tanggal TTD SPK Direksi/Kuasa Direksi</td>";
    $table.= "<td align=center rowspan=2 >Pengiriman SPK Kepada Supplier</td>";
    $table.= "<td align=center rowspan=2 >Keterangan</td>";
    $table .= "<td align=center rowspan=2>*</td>";
    $table.= "</tr>";      
    $table.= "<tr>";
    $table.= "<td align=center>VPO</td>";
    $table.= "<td align=center>PO/MILL/CIVIL</td>";
    $table.= "<td align=center>ACCOUNTING</td>";
    $table.= "<td align=center>FINANCE</td>";
    $table.= "<td align=center>DIREKSI</td>";
    $table.= "<td align=center>Pengiriman Draft SPK Kepada Supplier</td>";
    $table.= "<td align=center>Penerimaan SPK Dari Supplier</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody>";
    foreach ($resAll as $key => $row) {
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        $table .= "<td  id='nourut_".$key."'>".($key+1)."</td>"; 
        foreach($row as $col=>$dat) 
        {
            $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
        }
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('vpo','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('PMC','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('acc','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('fin','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('dri','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tpengiriman','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tpenerimaan','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tglTTDSPK','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('pengirimanSPK','tanggal','',array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('keterangan','text','',array('style'=>'width:100px'))."</td>";
        $table .= "<td id='add_".$key."'>";
        $table .= "<img src='images/save.png' ";
        $table .= "class=resicon  caption='Save' onclick='saveDataDetail(".$key.")'></td>";
        $table .= "</tr>";
    }
   
    $table .= "</tbody>";
    $table .= "</table>";
    $table .= "</fieldset>";

    echo $table;
    break;

    case 'loadData3' :
    
    $queryAll ="select a.notransaksi, b.namaorganisasi, a.kategori, a.jenis from ".$dbname.".lgl_pengajuanspkht a 
                Left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
                WHERE a.notransaksi='".$data['notransaksi']."'   
                order by a.notransaksi desc";
    $resAll = fetchData($queryAll);

    $header = array("No urut","Nomor Transaksi","Nama Unit","Kategori","Jenis");
    $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    $table .="<legend>Detail</legend>";
    $table .= "<table cellspacing='1' border='0' class='sortable'>";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td rowspan=2>".$head."</td>";
    }
    $table.= "<td align=center colspan=5>Tanggal Progress Persetujuan Draft SPK</td>";
    $table.= "<td align=center colspan=2>Tanggal Pengiriman/Penerimaan SPK</td>";
    $table.= "<td align=center rowspan=2 >Tanggal TTD SPK Direksi/Kuasa Direksi</td>";
    $table.= "<td align=center rowspan=2 >Pengiriman SPK Kepada Supplier</td>";
    $table.= "<td align=center rowspan=2 >Keterangan</td>";
    $table .= "<td align=center rowspan=2>*</td>";
    $table.= "</tr>";      
    $table.= "<tr>";
    $table.= "<td align=center>VPO</td>";
    $table.= "<td align=center>PO/MILL/CIVIL</td>";
    $table.= "<td align=center>ACCOUNTING</td>";
    $table.= "<td align=center>FINANCE</td>";
    $table.= "<td align=center>DIREKSI</td>";
    $table.= "<td align=center>Pengiriman Draft SPK Kepada Supplier</td>";
    $table.= "<td align=center>Penerimaan SPK Dari Supplier</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody>";
    foreach ($resAll as $key => $row) {
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        $table .= "<td  id='nourut_".$key."'>".($key+1)."</td>"; 
        foreach($row as $col=>$dat) 
        {
            $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
        }
        $str = selectQuery($dbname,"lgl_progressspk","*","notransaksi='".$data['notransaksi']."'");
        $rsStr = fetchData($str);
        /*exit($str);*/
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('vpo','tanggal',tanggalnormal($rsStr[0]['tpVPO']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('PMC','tanggal',tanggalnormal($rsStr[0]['tpPOMILLCIVIL']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('acc','tanggal',tanggalnormal($rsStr[0]['tpAccounting']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('fin','tanggal',tanggalnormal($rsStr[0]['tpFinance']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('dri','tanggal',tanggalnormal($rsStr[0]['tpDireksi']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tpengiriman','tanggal',tanggalnormal($rsStr[0]['tPengiriman']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tpenerimaan','tanggal',tanggalnormal($rsStr[0]['tPenerimaan']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('tglTTDSPK','tanggal',tanggalnormal($rsStr[0]['tglTTDSPK']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('pengirimanSPK','tanggal',tanggalnormal($rsStr[0]['pengirimanSPK']),array('style'=>'width:100px'))."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".makeElement('keterangan','text',$rsStr[0]['keterangan'],array('style'=>'width:100px'))."</td>";
        
        $table .= "<td id='add_".$key."'>";
        $table .= "<img src='images/save.png' ";
        $table .= "class=resicon  caption='Save' onclick='updateData(".$key.")'></td>";
        $table .= "</tr>";
    }
   
    $table .= "</tbody>";
    $table .= "</table>";
    $table .= "</fieldset>";

    echo $table;
    break;

     case 'html' :
    
    $queryAll ="select a.notransaksi, b.namaorganisasi, a.kategori, a.jenis from ".$dbname.".lgl_pengajuanspkht a 
                Left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
                WHERE a.notransaksi='".$data['notransaksi']."'   
                order by a.notransaksi desc";
    $resAll = fetchData($queryAll);

    $header = array("No urut","Nomor Transaksi","Nama Unit","Kategori","Jenis");
    $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    $table .="<legend>Progress SPK</legend>";
    $table .= "<table cellspacing='1' border='0' class='sortable'>";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td rowspan=2>".$head."</td>";
    }
    $table.= "<td align=center colspan=5>Tanggal Progress Persetujuan Draft SPK</td>";
    $table.= "<td align=center colspan=2>Tanggal Pengiriman/Penerimaan SPK</td>";
    $table.= "<td align=center rowspan=2 >Tanggal TTD SPK Direksi/Kuasa Direksi</td>";
    $table.= "<td align=center rowspan=2 >Pengiriman SPK Kepada Supplier</td>";
    $table.= "<td align=center rowspan=2 >Keterangan</td>";
    $table.= "</tr>";      
    $table.= "<tr>";
    $table.= "<td align=center>VPO</td>";
    $table.= "<td align=center>PO/MILL/CIVIL</td>";
    $table.= "<td align=center>ACCOUNTING</td>";
    $table.= "<td align=center>FINANCE</td>";
    $table.= "<td align=center>DIREKSI</td>";
    $table.= "<td align=center>Pengiriman Draft SPK Kepada Supplier</td>";
    $table.= "<td align=center>Penerimaan SPK Dari Supplier</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody>";
    foreach ($resAll as $key => $row) {
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        $table .= "<td  id='nourut_".$key."'>".($key+1)."</td>"; 
        foreach($row as $col=>$dat) 
        {
            $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
        }
        $str = selectQuery($dbname,"lgl_progressspk","*","notransaksi='".$data['notransaksi']."'");
        $rsStr = fetchData($str);
        /*exit($str);*/
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tpVPO'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tpPOMILLCIVIL'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tpAccounting'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tpFinance'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tpDireksi'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tPengiriman'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tPenerimaan'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['tglTTDSPK'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".tanggalnormal($rsStr[0]['pengirimanSPK'])."</td>";
        $table.= "<td align=center style='vertical-align:middle'>".$rsStr[0]['keterangan']."</td>";
        
        $table .= "</tr>";
    }
   
    $table .= "</tbody>";
    $table .= "</table>";
    $table .= "</fieldset>";

    echo $table;
    break;

    case 'savedata':
    $data['tpVPO']=tanggalsystem($data['tpVPO']);
    $data['tpPOMILLCIVIL']=tanggalsystem($data['tpPOMILLCIVIL']);
    $data['tpAccounting']=tanggalsystem($data['tpAccounting']);
    $data['tpFinance']=tanggalsystem($data['tpFinance']);
    $data['tpDireksi']=tanggalsystem($data['tpDireksi']);
    $data['tPengiriman']=tanggalsystem($data['tPengiriman']);
    $data['tPenerimaan']=tanggalsystem($data['tPenerimaan']);
    $data['tglTTDSPK']=tanggalsystem($data['tglTTDSPK']);
    $data['pengirimanSPK']=tanggalsystem($data['pengirimanSPK']);
    $query = insertQuery($dbname,'lgl_progressspk',$data);
    try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
       

    break;

    case 'updateData':
    $data['tpVPO']=tanggalsystem($data['tpVPO']);
    $data['tpPOMILLCIVIL']=tanggalsystem($data['tpPOMILLCIVIL']);
    $data['tpAccounting']=tanggalsystem($data['tpAccounting']);
    $data['tpFinance']=tanggalsystem($data['tpFinance']);
    $data['tpDireksi']=tanggalsystem($data['tpDireksi']);
    $data['tPengiriman']=tanggalsystem($data['tPengiriman']);
    $data['tPenerimaan']=tanggalsystem($data['tPenerimaan']);
    $data['tglTTDSPK']=tanggalsystem($data['tglTTDSPK']);
    $data['pengirimanSPK']=tanggalsystem($data['pengirimanSPK']);

    unset($data['notransaksi']);
    $query = updateQuery($dbname,'lgl_progressspk',$data,"notransaksi='".$_POST['notransaksi']."'");
    try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
       

    break;


     case 'deletehd':
   
            $qdetIns = deleteQuery($dbname,"lgl_progressspk","notransaksi='".$data['notransaksi']."' and unit='".$data['unit']."'");
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }


    break;
    case 'excel':
        $notransaksi = checkPostGet('notransaksi', '');

        $sqlht = selectQuery($dbname,"lgl_progressspk","*","notransaksi='".$notransaksi."'","notransaksi asc");
        $resht = fetchdata($sqlht);


        @$no+=1;
        $stream.= "<table cellspacing='1' border='1'>";
        $stream.= "<tr>";
            $stream.= "<td align=center rowspan=2 >No</td>";
            $stream.= "<td align=center rowspan=2 >No Transaksi</td>";
            $stream.= "<td align=center rowspan=2 >Unit</td>";
            $stream.= "<td align=center colspan=5>Tanggal Progress Persetujuan Draft SPK</td>";
            $stream.= "<td align=center colspan=2>Tanggal Pengiriman/Penerimaan SPK</td>";
            $stream.= "<td align=center rowspan=2 >Tanggal TTD SPK Direksi/Kuasa Direksi</td>";
            $stream.= "<td align=center rowspan=2 >Pengiriman SPK Kepada Supplier</td>";
            $stream.= "<td align=center rowspan=2 >Keterangan</td>";
            $stream.= "</tr>";      
            $stream.= "<tr>";
            $stream.= "<td align=center>VPO</td>";
            $stream.= "<td align=center>PO/MILL/CIVIL</td>";
            $stream.= "<td align=center>ACCOUNTING</td>";
            $stream.= "<td align=center>FINANCE</td>";
            $stream.= "<td align=center>DIREKSI</td>";
            $stream.= "<td align=center>Pengiriman Draft SPK Kepada Supplier</td>";
            $stream.= "<td align=center>Penerimaan SPK Dari Supplier</td>";
            $stream.= "</tr>"; 
            $stream.= "<tr>";
            $stream.= "<td align=center style='vertical-align:middle'>".$no."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['notransaksi']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$optOrg[$resht[0]['unit']]."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tpVPO']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tpPOMILLCIVIL']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tpAccounting']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tpFinance']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tpDireksi']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tPengiriman']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tPenerimaan']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['tglTTDSPK']."</td>";
            $stream.= "<td align=center style='vertical-align:middle'>".$resht[0]['keterangan']."</td>";
            $stream.= "</tr>";
                

                      
        
        
        $stream.= "</table>";
        
        $tglSkrg = date("Ymd");
        $nop_ = "ProgresSPK_";
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
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
        
    break;

    
}