<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/tool_admin.js?v='<?php echo time();?>></script>
<?

$arr="##listTransaksi##pilUn_1##unitId##method";
include('master_mainMenu.php');

##Jenis Transaksi##
$opt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $pil=array("1"=>$_SESSION['lang']['kasbank'],"2"=>'Kasir',"3"=>$_SESSION['lang']['kontrak'],"4"=>$_SESSION['lang']['tbm']."/".$_SESSION['lang']['tm']."/".$_SESSION['lang']['panen'],"5"=>$_SESSION['lang']['traksi'],"6"=>"Tagihan","7"=>"Penerimaan TBS","8"=>"Penerimaan TBS Ramp","10"=>"Penagihan","12"=>$_SESSION['lang']['notadebet'],"11"=>$_SESSION['lang']['kaskecil']." Top Up","13"=>$_SESSION['lang']['kaskecil']." ".$_SESSION['lang']['pengeluaran'],"14"=>"Project");
$pil=array("1"=>$_SESSION['lang']['kasbank'],"2"=>'Kasir',"3"=>$_SESSION['lang']['kontrak'],"4"=>$_SESSION['lang']['tbm']."/".$_SESSION['lang']['tm']."/".$_SESSION['lang']['panen'],"5"=>$_SESSION['lang']['traksi'],"6"=>"Tagihan","7"=>"Penerimaan TBS","10"=>"Penagihan","14"=>"Project","15"=>"Pembayaran TBS");
foreach($pil as $dtl=>$vw)
{
	$opt.="<option value='".$dtl."'>".$vw."</option>";
}

##Periode Akutansi##
$optUnit2=$optUnit=$optPrd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optPrd.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}

##List Unit##
$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)=4 order by namaorganisasi asc";
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit=$qUnit->fetch())
{
    $optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
    if(substr($rUnit['kodeorganisasi'],3,1)=='E')
    {
    $optUnit2.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
    }
}

##Form Unposting##
// $frm[0]="<table>
// 	<tr>
// 		<td valign=top>
// 			<fieldset style=width:350px;>
// 			<legend>Unposting</legend>
// 			<table>
// 				<tr>
// 					<td>".$_SESSION['lang']['notransaksi']."</td>
// 					<td><textarea id=listTransaksi name=listTransaksi></textarea></td>
// 				</tr>
// 				<tr>
// 					<td>".$_SESSION['lang']['unit']."</td>
// 					<td>
// 						<select id=unitId style=width:150px>".$optUnit."</select>
// 					</td>
// 				</tr>
// 				<tr>
// 					<td>".$_SESSION['lang']['jenis']."</td>
// 					<td>
// 						<select id=pilUn_1 style=width:150px onchange=getInfo()>".$opt."</select>
// 					</td>
// 				</tr>
// 			</table>
// 			<button class=mybutton id=tmblDt onclick=saveFranco('tool_slave_admin','".$arr."')>".$_SESSION['lang']['proses']."</button>
// 			</fieldset>
// 			<input type=hidden id=method value=getData />";
			
// $arrpjl="##tanggalpenjualan##nokontrak##nodo##methodpenjualan";			
// $frm[0].="<fieldset><legend>Unposting Pengakuan Penjualan</legend>
// 	<table>
// 	<tr>
// 		<td>".$_SESSION['lang']['tanggal']."</td>
// 		<td><input type=text class=myinputtext id=tanggalpenjualan onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly /></td>
// 	</tr>
// 	<tr>
// 		<td>".$_SESSION['lang']['NoKontrak']."</td>
// 		<td><input type='text' class='myinputtext' id='nokontrak' size='15' maxlength='150' style=\"width:200px;\"/></td></td>
// 	</tr>
//     <tr>
// 		<td>".$_SESSION['lang']['nodo']."</td>
// 		<td>
// 			<input type='text' class='myinputtext' id='nodo' size='15' maxlength='150' style=\"width:200px;\"/></td>
// 		</td>
// 	</tr>
// </table>
// <button class=mybutton id=tmblDtPjl onclick=savePjl('tool_slave_admin','".$arrpjl."')>".$_SESSION['lang']['proses']."</button>
// </fieldset><input type=hidden id=methodpenjualan value=getDataPenjualan />";
			

// if($_SESSION['empl']['bagian']=="IT")
// {
// 	$frm[0].="<fieldset style=width:350px;float:left;>
// 		<legend>".$_SESSION['lang']['ganti']." ".$_SESSION['lang']['blok']."</legend>
// 		<table>
// 			<tr>
// 				<td>".$_SESSION['lang']['kebun']."</td>
//                 <td><select id=kebuncoy style=width:150px onchange=getBlok(this.options[this.selectedIndex].value)>".$optUnit2."</select></td>
// 			</tr>
//             <tr>
// 				<td>".$_SESSION['lang']['bloklm']."</td>
// 				<td>
// 					<select id=bloklama style=width:150px onchange=updateBlokBaru(this.options[this.selectedIndex].value)><option value=''></option></select>
// 					<img id='bloklama' onclick=z.elSearch('bloklama',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
// 				</td>
// 			</tr>
// 			<tr>
// 				<td>".$_SESSION['lang']['blokbr']."</td>
// 				<td>
// 					<input type='text' class='myinputtext' id='blokbaru' size='10' maxlength='10' style=\"width:100px;\"/></td>
// 			</tr>
// 		</table>
// 		<button class=mybutton id=tombolganti onclick=gantiblok('tool_slave_admin')>".$_SESSION['lang']['proses']."</button>
// 		</fieldset><input type=hidden id=method2 value=blokganti />";
// }

// ##LIST GUDANG##
// $str="select  kodeorganisasi from ".$dbname.".organisasi where tipe not like '%GUDANG%'  and length(kodeorganisasi)=4 order by kodeorganisasi";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// $optOpenClose1="<option value=''></option>";
// while($bar=$res->fetch())
// {
//     $optOpenClose1.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi."</option>";
// }
// $frm[0].="<table>
// <br>
// 	<fieldset>
// 	<legend>Open/Close Periode For Accounting</legend>
// 		<select id=openclose><option value='OPEN'>Open</option><option value='CLOSE'>Close</option></select>
//         Unit:<select id=unitopenclose onchange=getPeriode(this.options[this.selectedIndex].value)>".$optOpenClose1."</select>
//         From<span id=periodeopenclose></span>
// </table>
// <button class=mybutton onclick=prosesDong() id=buttonDong style='display:none;'>Proses!</button>
// </fieldset>";

// $frm[0].="<br><table>
// <br>
// 	<fieldset>
// 	<legend>Open/Close Periode For Closing Bank</legend>
// 		<select id=openclosebank><option value='OPEN'>Open</option><option value='CLOSE'>Close</option></select>
//         Unit:<select id=unitopenclosebank onchange=getPeriodebank(this.options[this.selectedIndex].value)>".$optOpenClose1."</select>
//         From<span id=periodeopenclosebank></span>
// </table>
// <button class=mybutton onclick=prosesbank() id=buttonbank style='display:none;'>Proses</button>
// </fieldset>";

// ##Tipe Transaksi Gudang##
// $optj="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $optjen=array("1"=>$_SESSION['lang']['penerimaanbarang'],"3"=>$_SESSION['lang']['terimamutasi'],"5"=>$_SESSION['lang']['pengeluaranbarang'],"7"=>$_SESSION['lang']['mutasi']);
// foreach($optjen as $dtl=>$vw)
// {
//     $optj.="<option value='".$dtl."'>".$vw."</option>";
// }
// $arr2="##listTransaksi2##pilUn_5##method3";
// if($_SESSION['lang']=='ID')
// {
//     $aks='getInfo2()';
// }
// else
// {
//     $aks='';
// }
    
// $frm[0].="<fieldset style='display:none'><legend>Unposting Warehouse</legend>
// 	<table>
// 	<tr>
// 		<td>".$_SESSION['lang']['notransaksi']."</td>
// 		<td><textarea id=listTransaksi2 name=listTransaksi2></textarea></td>
// 	</tr>
//     <tr>
// 		<td>".$_SESSION['lang']['jenis']."</td>
// 		<td>
// 			<select id=pilUn_5 style=width:150px onchange=".$aks.">".$optj."</select>
// 		</td>
// 	</tr>
// </table>
// <button class=mybutton id=tmblDt onclick=saveFranco2('tool_slave_admin','".$arr2."')>".$_SESSION['lang']['proses']."</button>
// </fieldset><input type=hidden id=method3 value=getData2 />";

// $frm[0].="</td>
// 	<td valign=top>
// 		<fieldset style=width:350px;><legend>".$_SESSION['lang']['info']."</legend><div id=infoTip style=align:justify><script>getInfo()</script>";
// $frm[0].="</div></fieldset></td></tr></table>";

// $frm[0].="<table><tr></td>";
// $frm[0].="<div id=listData style=display:none>";
 OPEN_BOX('','<span class=judul>'.strtoupper('Upload Tool').'</span>');
// $frm[0].="<fieldset style=width:450px;><legend>".$_SESSION['lang']['list']."</legend><div id=container>";
	
// $frm[0].="</div></fieldset></td></tr></table>";

#===========================================================================================
$frm[0]='';
//if($_SESSION['empl']['bagian']=="IT" || $_SESSION['empl']['bagian']=="BOD"){
    
    $frm[0].="<fieldset><legend>Choose data type:</legend>
                      <span>Data type:<select id=udatatype onclick=getFormUplaod(this.options[this.selectedIndex].value)>
                                                    <option value=''>Please choose..</option>
                                                    <option value='GAPOK'>GAPOK:Basic Salary</option>
                                                    <option value='PTKP'>PTKP:Tax Status</option>
                                                    <option value='RAPEL'>RAPEL:Gaji</option>
                                                    </select>
                     </fieldset>                               
                    <fieldset><legend>Form</legend>
                     <div id=uForm style='display:none'>
                     	
                                         <span id=sample></span><br><br>
                                         (File type support only CSV).
                                        <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>	
                                        <input type=hidden name=jenisdata id=jenisdata value=''>
                                        <input type=hidden name=MAX_FILE_SIZE value=1024000>
                                        File:<input name=filex type=file id=filex size=25 class=mybutton>
                                        Field separated by<select name=pemisah>
                                        <option value=','>, (comma)</option>
                                        <option value=';'>; (semicolon)</option>
                                        <option value=':'>: (two dots)</option>
                                        <option value='/'>/ (slash)</option>
                                        </select>
                                        <input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
                                    </form>
 
                                    <iframe frameborder=0 width=800px height=200px name=frame>
                                    </iframe>
                     </div>
                    </fieldset>";
// }
// else{
//    $frm[1]='Not authorized'; 
// }
#============================================================================================
//$hfrm[0]='Unposting';
$hfrm[0]='Upload';
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,900);

CLOSE_BOX();
echo"</div>";
echo close_body();
?>