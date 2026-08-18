<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
$param = $_POST;

#periksa periode gudang
$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodegudang']."' and tutupbuku=0";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$res = $res->fetch();

#ambil harga terakhir berdasarkan periode
$sHrg = "select * from ".$dbname.".log_5saldobulanan where kodegudang='".$param['kodegudang']."' and kodebarang = '".$param['kodebarang']."' and periode='".$res['periode']."'";
$qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
$qHrg->setFetchMode(PDO::FETCH_ASSOC);
$rHrg = $qHrg->fetch();

is_null($rHrg['hargarata'])?$rHrg['hargarata']=0:$rHrg['hargarata']=$rHrg['hargarata'];

echo $rHrg['hargarata'];