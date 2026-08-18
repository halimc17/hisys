<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language="JavaScript1.2" src="js/sdm_2daftarbpjs.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2daftarbpjs').'</span><br>');
$opttipekaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
$optunit=$optper=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$getakses=getOrgDetail(2);
$whrlokasi="and kodeorganisasi in (".$getakses.")";

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$whrlokasi." order by induk, namaorganisasi asc ";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
	$d=$induk[$bar['kodeorganisasi']];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

$str="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1'";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$opttipekaryawan.="<option value=".$bar['id'].">".$bar['tipe']."</option>";
}

$str="select * from ".$dbname.".setup_parameterappl where kodeparameter in ('HRBPJSKER','HRBPJSKES')";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    if($bar['kodeparameter']=='HRBPJSKER')
    {
       $optjenis.="<option value=".$bar['kodeparameter'].">BPJS KETENAGAKERJAAN (JKK,JKM,JHT,JP)</option>";  
    }
    elseif($bar['kodeparameter']=='HRBPJSKES')
    {
	   $optjenis.="<option value=".$bar['kodeparameter'].">BPJS KESEHATAN</option>";    
    }
}

echo"
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
               
				 <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select class=select2 id=unit style=\"width:200px;\">".$optunit."</select></td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select class=select2 id=periode style=\"width:200px;\">".$optper."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select class=select2 id=tipekaryawan style=\"width:200px;\">".$opttipekaryawan."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['jenis']."</td>
                    <td>:</td>
                    <td><select class=select2 id=jenis style=\"width:200px;\">".$optjenis."</select></td>
                </tr>
				
                <tr>
                    <td><td><td>
                   <button id=preview class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
                    <button id=excel class=mybutton onclick=excel1('event')>".$_SESSION['lang']['excel']."</button>
                    <button id=excel class=mybutton onclick=pdf('event')>".$_SESSION['lang']['pdf']."</button>
                    <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"
<div id='printContainer' style='overflow:auto;height:400px;'; >
</div>";



CLOSE_BOX();
echo close_body();
				
?>