<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');


### Get Value Enum Status Internal/Eksternal
$optStatusIntEks = '';
$arrStatusIntEks = getEnum($dbname, 'pmn_4customer', 'statusinteks');
foreach ($arrStatusIntEks as $kei => $fal) {
    $optStatusIntEks.="<option value='" . $kei . "'>" . ucfirst(strtolower($fal)) . "</option>";
}

$optJual='';
$arrX = array('franco' => 'Franco', 'loco' => 'Loco', 'fob' => 'FOB');
$optJual.="<option value='loco'>Loco</option>";
$optJual.="<option value='franco'>Franco</option>";
$optJual.="<option value='fob'>FOB</option>";

$arrbyr=array("0"=>"Dibayar sendiri","1"=>"Dipungut pihak lain");
$optcrbyr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrbyr as $brs1 => $isi1)
{
    $optcrbyr.="<option value=".$brs1.">".$isi1."</option>";
}

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$optjenishasil=$optpph="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$strq = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='PPHP'";
$resq = $owlPDO->query($strq) or die(print " Gagal: " . PDOException::getMessage());
$resq->setFetchMode(PDO::FETCH_ASSOC);
$barq = $resq->fetch();
$nilai=explode(',',$barq['nilai']);
foreach($nilai as $key => $val){
    //@$optpph.="<option value='" . $val . "'>" . $val . " - " . $nmakun[$val] . "</option>";
}


$crbyr = '';
$arrcrbyr = array(
			'1'=>'Dibayar sendiri',
			'2'=>'Dipungut pihak lain'
			);

$jnsp = '';
$jnsp = array(
			'1'=>'Badan Usaha Industri Semen',
			'2'=>'Badan Usaha Industri Rokok',
			'3'=>'Badan Usaha Industri Kertas',
			'4'=>'Badan Usaha Industri Baja',
			'5'=>'Badan Usaha Industri Otomotif',
			'6'=>'Pembelian Barang Oleh Bendaharawan',
			'7'=>'Nilai Impor Bank Devisa/Ditjen Bea dan Cukai',
			'8'=>'Hasil Lelang',
			'9'=>'Penjualan Migas Oleh Pertamina',
			'10'=>'Pembelian Barang Keperluan Industri Dalam Sektor Perhutanan',
			'11'=>'Pembelian Barang Keperluan Industri Dalam Sektor Perkebunan',
			'12'=>'Pembelian Barang Keperluan Industri Dalam Sektor Pertanian',
			'13'=>'Pembelian Barang Keperluan Industri Dalam Sektor Perikanan'
			);

$nmakun='';
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');


// $str = "select * from " . $dbname . ".pmn_5pajak order by id";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar = $res->fetch()) {
// 	$optpph.="<option value='" . $bar['id'] . "'>" . $nmakun[$bar['jenispph']] . " - ".$arrcrbyr[$bar['carapembayaran']]." - ".$jnsp[$bar['jenispenghasilan']]."</option>";
// }


$str = "select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 and substr(fieldaktif,7,1)='1' order by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optpph.="<option value='" . $bar['noakun'] . "'>" .$bar['noakun']. " - ".$bar['namaakun']."</option>";
}

$str = "select idpenghasilan,namapenghasilan from ".$dbname.".pmn_5jenispenghasilan order by idpenghasilan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenishasil.="<option value='".$bar['idpenghasilan']."'>".$bar['idpenghasilan']." - ".$bar['namapenghasilan']."</option>";
}
//<script language="javascript" src="js/pmn_5customer.js?ver=1.6"></script>
?>
<script language=javascript src='js/pmn_5customer.js?v=<?php echo time(); ?>'></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('pmn_5customer').'</span>');
?>
<fieldset >
    <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
    <table cellpadding="1" cellspacing="1" border="0">
        <tr>
            <td style='vertical-align:top;'><font color='red'>*</font><?php echo $_SESSION['lang']['komoditi'] ?></td>
            <td style='vertical-align:top;'>:</td>
            <td colspan=20>
                <table width="95%"><tr><td>
					<?
					$str = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang=400";
					$qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$qry->setFetchMode(PDO::FETCH_ASSOC);
					while ($res = $qry->fetch()) 
					{
						echo "<li style='float:left;width:200px;list-style-type:none'><input type='checkbox' id='chkKomoditi' name='chkKomoditi[]' value='" . $res['kodebarang'] . "' />" . $res['namabarang'] . "</li>";
					}
					?>
				</td></tr></table>
            </td>
        </tr>
		
		<tr>
            <td width="120px"><font color='red'>*</font><?php echo $_SESSION['lang']['kodecustomer'] ?></td>
            <td>:</td>
            <td style="width:10px" colspan=20>
				<input style="width:170px"  type="text" class="myinputtext" id="kode_cus" onkeypress="return tanpa_kutip(event);" disabled />&nbsp;<font color='green'><i>* Automatically Generated</i></font>
			</td>
        </tr>
		<tr>
            <td width="120px"><font color='red'>*</font>Inisial Customer</td>
            <td>:</td>
            <td style="width:10px" colspan=20>
				<input style="width:170px"  type="text" class="myinputtext" id="inisial_cus" onkeypress="return tanpa_kutip(event);" />
			</td>
        </tr>
		
        <tr style='display:none;'>
            <td><?php echo $_SESSION['lang']['klmpkcust'] ?></td>
            <td>:</td>
            <td><input type="hidden" id="klcustomer_code"  />
                <input type="text" id="nama_group" class="myinputtext" disabled="disabled"/> 
                <img src=images/search.png class=dellicon title=<?php echo $_SESSION['lang']['find'] ?> onclick="searchGruop('<?php echo $_SESSION['lang']['findgroup'] ?>','<fieldset><legend><?php echo $_SESSION['lang']['findgroup'] ?></legend>Find<input type=text class=myinputtext id=group_name><button class=mybutton onclick=findGroup()>Find</button></fieldset><div id=container_cari></div>',event)";></td>
        </tr>
		
        <tr style='display:none;'>
            <td><?php echo $_SESSION['lang']['akun'] ?></td>
            <td>:</td>
            <td>
                <input type="hidden" id="akun_cust"  /><input type="text" id="nama_akun" class="myinputtext" disabled="disabled"/> <img src=images/search.png class=dellicon title=<?php echo $_SESSION['lang']['find'] ?> onclick="searchAkun('<?php echo $_SESSION['lang']['findnoakun'] ?>','<fieldset><legend><?php echo $_SESSION['lang']['findnoakun'] ?></legend>Find<input type=text class=myinputtext id=no_akun><button class=mybutton onclick=findAkun()>Find</button></fieldset><div id=container_cari_akun></div>',event)";>
                <!--<input type="text" class="myinputtext" id="no_akun" onkeypress="return tanpa_kutip(event);"  />-->
            </td>
        </tr>
		
        <tr>
            <td style='vertical-align:top'><font color='red'>*</font><?php echo $_SESSION['lang']['nmcust'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top;width:30px'>
				<input style="width:170px" type="text" class="myinputtext" id="cust_nm" onkeypress="return tanpa_kutip(event);" onblur="generatekode(this.value)" />
			</td>

            <td rowspan=3 style='vertical-align:top'><font color='red'>*</font><?php echo $_SESSION['lang']['alamat'] ?></td>
            <td rowspan=3 width="5px" style='vertical-align:top'>:</td>
            <td rowspan=3 colspan='20' style='vertical-align:top'>
            <textarea style="width:400px;" rows="2" id='almt' onkeypress='return tanpa_kutip(event);'></textarea>
            </td>
        </tr>
		<tr>
			<td ><font color='red'>*</font><?php echo $_SESSION['lang']['telepon'] ?></td>
            <td>:</td>
            <td>
                <input style="width:170px" type="text" class="myinputtext" id='tlp_cust' onkeypress='return tanpa_kutip(event);' ></input>
            </td>
		</tr>
		<tr>
			<td><font color='red'>*</font><?php echo $_SESSION['lang']['kota'] ?></td>
            <td style="width:5px" >:</td>
            <td>
                <input type="text" style="width:170px;" class="myinputtext" id="kta" onkeypress="return tanpa_kutip(event);"  />
            </td>
		</tr>
        
        <tr>
            <td style='vertical-align:top'><?php echo $_SESSION['lang']['npwp'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top'>
                <input style="width:170px"  type="text" class="myinputtext" id="npwp_no" onkeypress="return tanpa_kutip(event);"  />
            </td>
			
            <td rowspan=2 style='vertical-align:top;width:80px'><?php echo $_SESSION['lang']['alamat'] . " " . $_SESSION['lang']['npwp'] ?></td>
            <td rowspan=2 width="5px" style='vertical-align:top'>:</td>
            <td rowspan=2 colspan='20' style='vertical-align:top'>
                <textarea style="width:400px;" rows="2" id='npwp_alamat' onkeypress='return tanpa_kutip(event);' ></textarea>
            </td>

        </tr>
		
		<tr>
			<td>Upload File NPWP</td>
			<td>:</td>
			<td>
				<input type='file' name='upload' id='upload'>
            </td>
		</tr>
		<tr>
			<td>Upload File Legalitas</td>
			<td>:</td>
			<td>
				<input type='file' name='uplegalitas' id='uplegalitas'>
            </td>
		</tr>
		
        <tr style="display:none;">
            <td><?php echo $_SESSION['lang']['jenispph'] ?></td><td>:</td>
            <td ><select id="jenispph"  style="width:173px;"><?php echo $optpph ?></select></td>
            <td ><?php echo $_SESSION['lang']['pph']."&nbsp; [%]" ?></td><td>:</td>
            <td><input type="text" id="pphpersen" placeholder="%" class="myinputtextnumber" style="width:63px;" onkeypress="return angka_doang(event)"/></td>
        </tr>

        <tr>
            <td ><font color='red'>*</font><?php echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['penandatangan'] ?></td>
            <td >:</td>
            <td >
                <input  style="width:170px" type="text" class="myinputtext" id="penandatangan" onkeypress="return tanpa_kutip(event);"  />
            </td>
            <td ><font color='red'>*</font><?php echo $_SESSION['lang']['jabatan'] ?></td>
            <td >:</td>
            <td colspan=5>
                <input type="text" style="width:185px" class="myinputtext" id="jabatan" onkeypress="return tanpa_kutip(event);"  />
            </td>
        </tr>
		
        <tr>
            <td style="vertical-align:top;"><font color='red'>*</font><?php echo $_SESSION['lang']['kntprson'] ?></td>
            <td style="vertical-align:top;">:</td>
            <td colspan=20>
                <script>loadKontakPerson()</script>
                <div id="listKontakPerson"></div>
                <input type="hidden" class="myinputtext" id="kntk_person" onkeypress="return tanpa_kutip(event);"  />
            </td>
        
        </tr>

        <tr style="display:none;">
            <td ><?php echo $_SESSION['lang']['carapembayaran'] ?></td><td >:</td>
            <td ><select id="carabayar"  style="width:172px;"><?php echo $optcrbyr ?></select></td>
            <td ><?php echo $_SESSION['lang']['jenispenghasilan'] ?></td><td >:</td>
            <td ><select id="jenispenghasilan"  style="width:172px;"><?php echo $optjenishasil ?></select></td>
   
        </tr>

        <tr>
         
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['berikat'] ?></td>
            <td style="vertical-align:top;">:</td>
            <td style="vertical-align:top; width:100px;">
                <input type="checkbox" id="chkBerikat" onclick="checkChkBerikat()"  />
            </td>
			
			
        </tr>
        

        <tr style="display:none;">    
		
			   <td style='vertical-align:top' width='150px'>Status <?php echo $_SESSION['lang']['eksternal'] . " / " . $_SESSION['lang']['internal'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top'>
                <select style="width:170px" id=statusinteks><? echo $optStatusIntEks ?></select>
            </td>
			
		
			<td style="vertical-align:top; width:1px;"><?php echo $_SESSION['lang']['bebas'] ?></td>
            <td style="vertical-align:top; width:1px;">:</td>
            <td style="vertical-align:top; width:1px;">
                <input type="checkbox" id="statusbebas" onclick="checkChkBebas()" />
            </td>
		
			<td style="display:none;width:40px;padding-left:15px"><?php echo $_SESSION['lang']['plafon'] ?></td>
            <td style="display:none">:</td>
            <td style="display:none;width:40px">
                <input style="width:50px" type="text" class="myinputtextnumber" id="plafon_cus" onkeypress="return angka_doang(event);" value="0" />
            </td>
        
            <td style="display:none;width:50px"><?php echo $_SESSION['lang']['nilaihutang'] ?></td>
            <td style="display:none">:</td>
            <td style="display:none;width:50px" >
                <input style="width:50px" type="text" class="myinputtextnumber" id="n_hutang" onkeypress="return angka_doang(event);" value="0" />
            </td>
			
			<td style="display:none;width:50px" align='right'><?php echo $_SESSION['lang']['toleransipenyusutan'] ?></td>
            <td style="display:none;width:5px">:</td>
            <td style="display:none;width:50px">
                <input style="width:50px"  type="text" class="myinputtext" id="toleransipenyusutan" onkeypress="return tanpa_kutip(event);" />
            </td>
			
			 <textarea style="display:none;width:5px" id='ketBerikat' placeholder='Keterangan Berikat..' onkeypress='return tanpa_kutip(event);' style="width:375px;height:50px"></textarea>
		
		</tr>
		
		<tr style="display:none;">
            <td style='vertical-align:top'> <?php echo $_SESSION['lang']['tempatpenyerahan']; ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top'>
                <select style="width:170px" id=penjualan><? echo $optJual ?></select>
            </td>
			
			<!-- <td style="vertical-align:top;width:80px"><?php echo $_SESSION['lang']['statusberikat'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top' colspan=20>
                <textarea id='ketBerikat' onkeypress='return tanpa_kutip(event);' disabled="true" style="width:200px;height:20px"></textarea>
            </td> -->
        </tr>
		<tr style="display:none">
            <td style='vertical-align:top'><?php echo $_SESSION['lang']['noseripajak'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td>
                <input type="text" class="myinputtext" id="seri_no" onkeypress="return tanpa_kutip(event);"  />
                <input type="hidden" value="insert" id="method" />
            </td>
        </tr>
		
        <tr>
            <td><td></td>
            <td align="left" colspan=20>
                <button class=mybutton onclick=simpanPlgn()><?php echo $_SESSION['lang']['save'] ?></button>
                <button class=mybutton onclick=batalPlgn()><?php echo $_SESSION['lang']['cancel'] ?></button>
            </td>
        </tr>
		
    </table>
</fieldset>
<?php
CLOSE_BOX();
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br><br>');
?>
<!--<fieldset><legend><b><?php echo $_SESSION['lang']['list'] ?></b></legend>-->
	<table>
	   <tr>
            <td style='vertical-align:top'><?php echo $_SESSION['lang']['nmcust'] ?></td>
            <td style='vertical-align:top'>:</td>
            <td style='vertical-align:top;'>
				<input style="width:170px" type="text" class="myinputtext" id="namacustomersch" onkeypress="return tanpa_kutip(event);"/>
				<button class=mybutton onclick=loaddata()><?php echo $_SESSION['lang']['find'] ?></button>
			</td>
	</table>		
	

    <div class='table-scroll'>
	
    <table class="sortable" cellspacing="1" border="0">
        <thead>
            <tr class=rowheader>
                <th align=center>No.</th>
                <th align=center style='width:150px'><?php echo $_SESSION['lang']['komoditi'] ?></th>
                <th align=center><?php echo $_SESSION['lang']['kodecustomer']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['nmcust']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['alamat']; ?></th>
                <th align=center>Inisial Customer</th>
                <th align=center><?php echo $_SESSION['lang']['kota']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['telepon']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['npwp']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['alamat']." ".$_SESSION['lang']['npwp']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['penandatangan']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['jabatan']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['kntprson'] . "(" . $_SESSION['lang']['email'] . ")"; ?></th>
                <th align=center>Status <?php echo $_SESSION['lang']['eksternal'] . "/" . $_SESSION['lang']['internal']; ?></th>
                <th align=center><?php echo $_SESSION['lang']['kawasan']."<br>".$_SESSION['lang']['berikat']; ?></th>
                <th align=center hidden><?php echo $_SESSION['lang']['kawasan']."<br>".$_SESSION['lang']['bebas']; ?></th>
                <th  align=center colspan="6">Action</th>
            </tr>
        </thead>
        <tbody id="container">
			
        </tbody>
        <tfoot>
        </tfoot>
    </table>
   </div> 
<!--</fieldset>-->
<?
CLOSE_BOX();
echo close_body();
?>