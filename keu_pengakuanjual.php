<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script type="text/javascript" src="js/keu_pengakuanjual.js?v=1.1" /></script>
<?php 
OPEN_BOX('','<span class=judul>'.getMenu('keu_pengakuanjual').'</span><br>');
        $sListPabrik = "select kodeorganisasi as millcode, namaorganisasi as namaorganisasi 
                        from ".$dbname.".organisasi where tipe='PABRIK'";
        $rListPabrik = fetchData($sListPabrik);
        $optListPabrik[''] = $_SESSION['lang']['all'];
        foreach($rListPabrik as $key=>$row) {
                $optListPabrik[$row['millcode']] = $row['millcode']." - ".$row['namaorganisasi'];
        }
        $row['millcode']='EXTM';
        $row['namaorganisasi']='Pabrik Luar';
        $optListPabrik[$row['millcode']] = $row['millcode']." - ".$row['namaorganisasi'];
        $optPt[''] = $_SESSION['lang']['all'];
        $optListBrg[0] = $_SESSION['lang']['all'];
        $sBrg="select distinct(a.kodebarang) as millcode, b.namabarang as namaorganisasi 
                                        from ".$dbname.".pabrik_timbangan a
                                        left join ".$dbname.".log_5masterbarang b
                                        on a.kodebarang = b.kodebarang";
        $rBrgPabrik = fetchData($sBrg);
        foreach($rBrgPabrik as $key=>$row) {
                $optListBrg[$row['millcode']] = $row['millcode']." - ".$row['namaorganisasi'];
        }
        $optStat=array();
        $arrStat=array("0"=>$_SESSION['lang']['belumposting'],"1"=>$_SESSION['lang']['all']);
        foreach($arrStat as $row=>$lstPros){
                $optStat[$row]=$lstPros;
        }
?>
<fieldset style='margin-top:10px;float:left'>
        <legend style='font-weight:bold'>Form</legend>
        <table border=0>
                <tr>
                        <td><?php echo $_SESSION['lang']['tanggal']?></td><td>:</td>
                        <td><?php echo makeElement('tanggal','period',date('d-m-Y'),array('style'=>'width:75px'))?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['status']?></td><td>:</td>
                        <td><?php echo makeElement('status','select','',array('style'=>'width:182px'),$optStat) ?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['NoKontrak']?></td><td>:</td>
                        <td><?php echo makeElement('nokontrak','text','',array('style'=>'width:178px'))?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['pabrik']?></td><td>:</td>
                        <td><?php echo makeElement('pabrik','select','',array('style'=>'width:182px','onchange'=>'getPtkntrk()'),$optListPabrik) ?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['komoditi']?></td><td>:</td>
                        <td><?php echo makeElement('komoditi','select','',array('style'=>'width:182px'),$optListBrg) ?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['harga']?></td><td>:</td>
                        <td><?php echo makeElement('hargaall','textnum','',array('style'=>'width:182px;','disabled'=>'disabled','onchange'=>'putHarga()')) ?></td>
                </tr>
                <tr>
                        <td><?php echo $_SESSION['lang']['pt']?></td><td>:</td>
                        <td><?php echo makeElement('kdpt','select','',array('style'=>'width:182px'),$optPt) ?></td>
                </tr>
                <tr>
                        <td><td><td>
                        <?php echo makeElement('btnList','btn',
                                $_SESSION['lang']['list'],array('onclick'=>"list('preview')"))?>
                        </td>
                </tr>
        </table>
</fieldset>
<?php 
CLOSE_BOX();
OPEN_BOX();?>
<fieldset>
        <legend style='font-weight:bold'><?php echo $_SESSION['lang']['list']?></legend>
        <div id='containerList'style='overflow:auto;height:400px;100%';></div>
</fieldset>
<?php CLOSE_BOX();
echo close_body();
?>