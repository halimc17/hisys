<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');

// $kd_bag=$_POST['rkd_bag'];
if((isset($_POST['txtfind3']))!='')
{
    $for=$_POST['for'];
    $nolama2=$_POST['nolama2'];
    $kodebarang_=$_POST['kodebarang_'];
    $gudang=$_POST['gudang'];

    $txtfind=$_POST['txtfind3'];
    if($for == 'supplier'){
        // $str="select * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and tipetransaksi='1'"; //exit('error'.$str);

        // $str="SELECT * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and tipetransaksi='1' and 
        // notransaksi NOT IN (SELECT notransaksireferensi from ".$dbname.".log_transaksiht where kodegudang='".$gudang."' 
        // and tipetransaksi=6 order by notransaksi)"; //exit('error'.$str);
        // $str="SELECT * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and kodegudang='".$gudang."' and tipetransaksi='1' and 
        // notransaksi NOT IN (SELECT notransaksireferensi from ".$dbname.".log_transaksiht where kodegudang='".$gudang."' 
        // and tipetransaksi=6 order by notransaksi)"; //exit('error'.$str);
        $str="SELECT * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and kodegudang='".$gudang."' and tipetransaksi='1' and 
        notransaksi"; //exit('error'.$str);
    }elseif($for == 'kodebarang'){
        $str="select * from ".$dbname.".log_transaksidt where notransaksi = '".$nolama2."' "; //exit('error'.$str);
    }elseif($for == 'blok'){
        $str="select * from ".$dbname.".log_transaksidt where notransaksi = '".$nolama2."' and kodebarang = '".$kodebarang_."' "; //exit('error'.$str);
    }else{
        // $str="select * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and tipetransaksi='5' and 
        // notransaksi NOT IN (SELECT notransaksireferensi from ".$dbname.".log_transaksiht where kodegudang='".$gudang."' 
        // and tipetransaksi=2 order by notransaksi)"; //exit('error'.$str);

        // $str="select * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and kodegudang='".$gudang."' and tipetransaksi='5' and 
        // notransaksi NOT IN (SELECT notransaksireferensi from ".$dbname.".log_transaksiht where kodegudang='".$gudang."' 
        // and tipetransaksi=2 order by notransaksi)"; //exit('error'.$str);
        $str="select * from ".$dbname.".log_transaksiht where notransaksi like '%".$txtfind."%' and kodegudang='".$gudang."' and tipetransaksi='5' and 
        notransaksi"; //exit('error'.$str);
    }
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    if($for == 'kodebarang'){
        $namabarang_ = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
        echo"
        <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; \" >
        <table class=sortable cellspacing=1 cellpadding=1  border=0>
        <thead>
        <tr class=rowheader>
        <td>No.</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['kodebarang']."</td>
        <td>".$_SESSION['lang']['namabarang']."</td>
         </tr>
         </thead>
         <tbody>";
        //			$no=0;
        while($bar=$res->fetch())
        {
            $no+=1;
            echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setKodebarangLama('".$bar->kodebarang."')\" title='Click' >
                        <td align=center>".$no."</td>
                        <td>".$bar->notransaksi."</td>
                        <td>".$bar->kodebarang."</td>
                        <td>".$namabarang_[$bar->kodebarang]."</td>
                            
                            </tr>";
        }
        echo "</tbody>
                        <tfoot>
                        </tfoot>
                        </table></div></fieldset>";

    }elseif($for == 'blok'){
        $nmakendaraan = makeOption($dbname,'vhc_5master','kodevhc,detailvhc');
        echo"
        <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; \" >
        <table class=sortable cellspacing=1 cellpadding=1  border=0>
        <thead>
        <tr class=rowheader>
        <td>No.</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['kodeblok']."</td>
        <td>".$_SESSION['lang']['kendaraan']."</td>
         </tr>
         </thead>
         <tbody>";
        //			$no=0;
        while($bar=$res->fetch())
        {
            $no+=1;
            echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setblokLama('".$bar->kodeblok."','".$bar->kodemesin."')\" title='Click' >
                        <td align=center>".$no."</td>
                        <td>".$bar->notransaksi."</td>
                        <td>".$bar->kodeblok."</td>
                        <td>".$nmakendaraan[$bar->kodemesin]."</td>
                            </tr>";
        }
        echo "</tbody>
                        <tfoot>
                        </tfoot>
                        </table></div></fieldset>";
    }
    else{

        echo"
        <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; \" >
        <table class=sortable cellspacing=1 cellpadding=1  border=0>
        <thead>
        <tr class=rowheader>
        <td>No.</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>Kode Gudang</td>
                    
                    </tr>
                    </thead>
                    <tbody>";
        //			$no=0;
        while($bar=$res->fetch())
        {
            $no+=1;
            echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setNodokLama('".$bar->notransaksi."')\" title='Click' >
                        <td align=center>".$no."</td>
                        <td>".$bar->notransaksi."</td>
                            <td>".$bar->tanggal."</td>
                            <td>".$bar->kodegudang."</td>
                            
                            </tr>";
        }
        echo "</tbody>
                        <tfoot>
                        </tfoot>
                        </table></div></fieldset>";
    }

}

?>