<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$pbrkId=$_POST['pbrkId'];
$statId=$_POST['statId'];
$msnId=checkPostGet('msnId','');
$periode=$_POST['periode'];
$kdBrg=checkPostGet('kdBrg','');
$optNmMsn=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        switch($proses)
        {
                case'getData':
                if($periode=='0')
                {
                        $sql="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a left join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
                where a.pabrik='".$pbrkId."' and a.statasiun='".$statId."'  order by a.tanggal asc";//echo"warning:".$sql;
                }
                elseif($periode!='0')
                {
                        $sql="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a left join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
                where a.pabrik='".$pbrkId."' and a.statasiun='".$statId."' and tanggal like '%".$periode."%' order by a.tanggal asc";			
                }
                $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_ASSOC);
                echo"<div style=\"width:100%; height:300px; overflow:scroll;\">";
                echo"<table cellspacing=1 border=0 width=1500px>
                <thead><tr class=rowheader>
                        <td>No</td>
                        <td>".$_SESSION['lang']['notransaksi']."</td>
                        <td>".$_SESSION['lang']['tanggal']."</td>
                        <td>".$_SESSION['lang']['kegiatan']."</td>
                        <td>".$_SESSION['lang']['jammulai']."</td>
                        <td>".$_SESSION['lang']['jamselesai']."</td>
                        <td>".$_SESSION['lang']['mesin']."</td>
                        <td>".$_SESSION['lang']['nmmesin']."</td>
                        <td>".$_SESSION['lang']['kodebarang']."</td>
                        <td>".$_SESSION['lang']['namabarang']."</td>
                        <td>".$_SESSION['lang']['satuan']."</td>
                        <td>".$_SESSION['lang']['jumlah']."</td>
                        <td>".$_SESSION['lang']['keterangan']."</td></tr></thead><tbody>";
                while($res=$query->fetch())
                {
                        $sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$res['notransaksi']."</td>
                        <td>".tanggalnormal($res['tanggal'])."</td>
                        <td>".$res['kegiatan']."</td>
                        <td>".tanggalnormald($res['jammulai'])."</td>
                        <td>".tanggalnormald($res['jamselesai'])."</td>
                        <td>".$res['mesin']."</td>
                        <td>".$optNmMsn[$res['mesin']]."</td>
                        <td>".$res['kodebarang']."</td>
                        <td>".$rBrg['namabarang']."</td>
                        <td>".$res['satuan']."</td>
                        <td>".$res['jumlah']."</td>
                        <td>".$res['keterangan']."</td>
                        </tr>";
                }
                echo"</tbody></table></div>";
                break;

                case'get_result_cari':	
                $sql="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a inner join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
                where a.pabrik='".$pbrkId."' and tanggal like '%".$periode."%' and b.kodebarang='".$kdBrg."' order by a.tanggal asc";//echo"warning:".$sql;
                $query=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_ASSOC);
                echo"<div style=\"width:850px; height:300px; overflow:auto;\">";
                echo"<table cellspacing=1 border=0>
                <thead><tr class=rowheader>
                        <td>No</td>
                        <td>".$_SESSION['lang']['notransaksi']."</td>
                        <td>".$_SESSION['lang']['tanggal']."</td>
                        <td>".$_SESSION['lang']['mesin']."</td>
                        </thead><tbody>";
                while($res=$query->fetch()){
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$res['notransaksi']."</td>
                        <td>".tanggalnormal($res['tanggal'])."</td>
                        <td>".$res['mesin']."</td>
                        </tr>";
                }
                echo"</tbody></table></div>";
                break;

                case'GetDataMsn':

                $sql="select a.*,b.* from ".$dbname.".pabrik_rawatmesinht a inner join ".$dbname.".pabrik_rawatmesindt b on a.notransaksi=b.notransaksi 
                where a.pabrik='".$pbrkId."' and tanggal like '%".$periode."%' and a.mesin='".$msnId."' order by a.tanggal asc";//echo"warning:".$sql;
                $query=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $query->setFetchMode(PDO::FETCH_ASSOC);
                echo"<div style=\"width:850px; height:300px; overflow:auto;\">";
                echo"<table cellspacing=1 border=0>
                <thead><tr class=rowheader>
                        <td>No</td>
                        <td>".$_SESSION['lang']['notransaksi']."</td>
                        <td>".$_SESSION['lang']['kodebarang']."</td>
                        <td>".$_SESSION['lang']['namabarang']."</td>
                        <td>".$_SESSION['lang']['satuan']."</td>
                        <td>".$_SESSION['lang']['jumlah']."</td>
                        <td>".$_SESSION['lang']['keterangan']."</td></tr></thead><tbody>";
                while($res=$query->fetch()){
                        $sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                        $rBrg=$qBrg->fetch();
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$res['notransaksi']."</td>
                        <td>".$res['kodebarang']."</td>
                        <td>".$rBrg['namabarang']."</td>
                        <td>".$res['satuan']."</td>
                        <td>".$res['jumlah']."</td>
                        <td>".$res['keterangan']."</td>
                        </tr>";
                }
                echo"</tbody></table></div>";
                break;
                default:
                break;
        }

?>