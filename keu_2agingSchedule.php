<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_2agingSchedule.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2agingSchedule').'</span><br>');

if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
{
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT'  order by namaorganisasi";

$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Pusat</option>";
$optStatus.="<option value='1'>Lokal</option>";

}//and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'
else
{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'  order by namaorganisasi";

@$optStatus.="<option value='1'>Lokal</option>";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        @$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}

$optsupkontran="<option value=''>".$_SESSION['lang']['all']."</option>";
$optsupkontran.="<option value='S'>Supllier</option>";
$optsupkontran.="<option value='K'>".$_SESSION['lang']['kontraktor']."</option>";
$optsupkontran.="<option value='T'>Transportir</option>";
//=================ambil gudang;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
                or tipe='HOLDING')  and induk!=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
$optper="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch())
{
#	$optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}
$optStatus2=$optunit=$optjenis=$optSupplier="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select distinct kodesupplier from ".$dbname.".keu_tagihanht order by kodeorg asc ";  
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
    $whrby="supplierid='".$rOrg['kodesupplier']."'";
    $optSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrby);
    $optSupplier.="<option value=".$rOrg['kodesupplier'].">".@$optSup[$rOrg['kodesupplier']]."</option>";
}
$arrExcep="'upd','pjd','p22','p21','p23'";
$optNamaOrganisasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optnmjenis=makeOption($dbname, 'keu_5jenistagihan', 'kode,namajenis',"kode not in (".$arrExcep.")");
$optSupplier.="</select><img id='kodesupplier' onclick=z.elSearch('kodesupplier',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
$str="select distinct(tipeinvoice) as tipeinvoice  from ".$dbname.".keu_tagihanht where tipeinvoice!=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $nmjenis=$optnmjenis[$bar['tipeinvoice']];
    if($nmjenis==''){
        $nmjenis=$bar['tipeinvoice'];
    }
    $optjenis.="<option value='".$bar['tipeinvoice']."'>".$nmjenis."</option>";
}
$str="select distinct(unit) as unit  from ".$dbname.".keu_tagihanht where tipeinvoice!=''";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optunit.="<option value='".$bar['unit']."'>".$optNamaOrganisasi[$bar['unit']]."</option>";
}

$arrOpt=array("2"=>"Sudah Terbayar","3"=>"Outstanding");
$optStatus2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOpt as $listBrs =>$dtStat)
{
    $optStatus2.="<option value='".$listBrs."'>".$dtStat."</option>";
}


echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>

     <table>
        <tr>
            <td>".$_SESSION['lang']['pt']."</td>
            <td>:</td>
            <td><select id=pt style='width:200px;' >".$optpt."</select></td>
			
			 <td>".$_SESSION['lang']['jenis']."</td>
            <td>:</td>
            <td><select style=width:200px  id='jenis' name='jenis'>".$optjenis."</select></td> 
        </tr>
		
		 <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select style=width:200px  id='unit' name='unit'>".$optunit."</select><img id='unit' onclick=z.elSearch('unit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td> 
			
			<td>".$_SESSION['lang']['status']."</td>
            <td>:</td>
            <td><select style=width:200px  id='status' name='status'>".$optStatus2."</select></td> 
        </tr>
		
       
        <tr>
            <td>".$_SESSION['lang']['supplier']." / ".$_SESSION['lang']['kontraktor']." / Transportir</td>
            <td>:</td>
            <td><select style='width:200px;' id=supkontran>".$optsupkontran."</select></td> 
			
			 <td>".$_SESSION['lang']['noinvoice']."</td>
            <td>:</td>
            <td><input type=text id=noinvoicesch class=myinputtext onkeypress='return tanpa_kutip(event)' style=\"width:200px\" placeholder=\"No. Invoice boleh kosong\"  />
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['supplier']."</td>
            <td>:</td>
            <td><select style='width:200px;' id=kodesupplier>".$optSupplier."</select></td> 
			
			 <td>".$_SESSION['lang']['nopo']."</td>
            <td>:</td>
            <td><input type=text id=nopodt class=myinputtext onkeypress='return tanpa_kutip(event)' style=\"width:200px\" placeholder=\"No. PO boleh kosong\"  />
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type=\"text\" value=\"01-".$tanggalpivot=date('m-Y')."\" class=\"myinputtext\" id=\"tanggalpivot\" name=\"tanggalpivot\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:85px;\" readonly/>
                s/d <input type=\"text\" value=\"".$tanggalpivot=date('d-m-Y')."\" class=\"myinputtext\" id=\"tanggalpivot2\" name=\"tanggalpivot2\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:85px;\" readonly/>
            </td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggal']." Jatuh Tempo</td>
            <td>:</td>
            <td><input type=\"text\" value=\"".$tanggaljt."\" class=\"myinputtext\" id=\"tanggaljt\" name=\"tanggaljt\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:75px;\" readonly/>
            </td>
        </tr>
      
        <tr>
            <td><td>
			<td><button class=mybutton onclick=getUsiaHutang('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=getUsiaHutang('excel')>".$_SESSION['lang']['excel']."</button><button class=mybutton onclick=cancel('')>".$_SESSION['lang']['cancel']."</button></td>
            <td><select id=gudang hidden style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select></td>
        </tr>
		
		 <tr style=display:none;>
            <td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['po']."</td>
            <td>:</td>
            <td><select style='width:200px;' id=statuspo>".$optStatus."</select></td>
        </tr>
     </table>


         </fieldset>";
echo"<fieldset hidden>
<div id='containerxtra' style='overflow:auto; height:225px; max-width:800px;'>";
echo " </fieldset>";    

CLOSE_BOX();

OPEN_BOX('','');
echo"
	<span id=printPanel style='display:none;'></span>
    
	<div  class='table-scroll' style='width:100%;height:400px;overflow:auto;' id=container></div>";
    
CLOSE_BOX();

/*
OPEN_BOX('','');

echo"
<span id=printPanel style='display:none;'>
      <img onclick=fisikKeExcel(event,'keu_laporanUsiaHutang_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
          <img onclick=fisikKePDF(event,'keu_laporanUsiaHutang_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
          </span>
<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='container' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='container' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<fieldset style='clear:both;'><legend><b>Print Area</b></legend>
<div id='container' style='overflow:auto; height:325px; max-width:1150px;'>
</div></fieldset></div>";


CLOSE_BOX();
*/
close_body();
?>