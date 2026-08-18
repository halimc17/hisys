<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	$org_code=$_POST['code_org'];
	$code_block=$_POST['kode_blok'];
	$no_rmh=$_POST['rmh_no'];
	$method=$_POST['method'];
	
	
	
	switch($method)
	{
	case'get_blok':
	$optOrg='';
	$sql="select blok from ".$dbname.".sdm_perumahanht where kodeorg='".$org_code."' group by blok";
	$qes=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$qes->setFetchMode(PDO::FETCH_ASSOC);
	$optOrg.="<option value=></option>";
	while($res=$qes->fetch())
	{
		$optOrg.="<option value=".$res['blok'].">".$res['blok']."</option>";
		
	}	
	echo $optOrg;
	break;

	case'get_asset':
	$optAset='';
	$saset="select kodeasset,namasset from ".$dbname.".sdm_daftarasset where tipeasset='PR' and posisiasset='".$org_code."' and kodeasset not in (select kodeasset from ".$dbname.".sdm_perumahandt)";
	$qaset=$owlPDO->query($saset) or die(print " Gagal: ".PDOException::getMessage());
	$qaset->setFetchMode(PDO::FETCH_ASSOC);
	$optAset.="<option value=></option>";
	while($raset=$qaset->fetch())
	{
		$optAset.="<option value=".$raset['kodeasset'].">".$raset['namasset']."</option>";
	}

	echo $optAset;
	break;
        case'get_blok_penghuni':
	$optOrg='';
	$sql="select blok from ".$dbname.".sdm_perumahanht where kodeorg='".$org_code."' group by blok";
	$qes=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$qes->setFetchMode(PDO::FETCH_ASSOC);
	$optOrg.="<option value=></option>";
	while($res=$qes->fetch())
	{
		$optOrg.="<option value=".$res['blok'].">".$res['blok']."</option>";
		
	}
	
	$skary="select karyawanid,namakaryawan,lokasitugas,subbagian 
                from ".$dbname.".datakaryawan 
                where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') 
                and lokasitugas = '".$org_code."'";
		$qkary=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
		$qkary->setFetchMode(PDO::FETCH_ASSOC);
        while($rkary=$qkary->fetch())
        {
                if(($rkary['subbagian']=='0')||(is_null($rkary['subbagian'])))
                {
                        $rkary['lokasitugas']=$rkary['lokasitugas'];
                }
                else
                {
                        $rkary['lokasitugas']=$rkary['subbagian'];
                }
                $optKary.="<option value=".$rkary['karyawanid'].">".$rkary['namakaryawan']."&nbsp;[".$rkary['karyawanid']."]&nbsp;[".$rkary['lokasitugas']."]</option>";
        }
	echo $optOrg."###".$optKary;
	break;	
	case'get_normh':
	$optNormh='';
	//$optNormh.="<option value=></option>";
	//echo"warning:".$no_rmh."---".$code_block;
	if(($no_rmh!=0)&&($code_block!=0))	{
		$where.=" kodeorg='".$org_code."' and blok='".$code_block."' and norumah='".$no_rmh."'";
	}
	elseif($code_block!='')	{
		$where.=" kodeorg='".$org_code."' and blok='".$code_block."'";
	}
	$sql="select norumah from ".$dbname.".sdm_perumahanht where".$where;
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
	while($res=$query->fetch())	{		
		$optNormh.="<option value=".$res['norumah'].">".substr($res['norumah'],6)."</option>";
	}
               
	echo $optNormh;
	break;	
		default:
		break;
	}
?>