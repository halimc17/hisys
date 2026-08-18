<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pmn_billofloading').'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/pmn_billofloading.js?v=<?php echo time(); ?>'></script>

<div id="action_list">
    <?php

$frm[0]='';
$frm[1]=''; 

/*

<script language="javascript" src="js/pmn_billofloading.js"></script>
*/

# ambil list PT
$optkontrak=$pt=$cust = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select distinct nokontrak,kodept,koderekanan from " . $dbname . ".pmn_kontrakjual where posting=1 order by nokontrak asc";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optpt=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$rOrg['kodept']."'");
    $optcusto=makeOption($dbname,"pmn_4customer","kodecustomer,namacustomer","kodecustomer='".$rOrg['koderekanan']."'");
    $optkontrak.="<option value=" . $rOrg['nokontrak'] . ">" . $rOrg['nokontrak'] . "</option>";
    @$pt.="<option value=" . $rOrg['kodept'] . ">" . $optpt[$rOrg['kodept']] . "</option>";
    @$cust.="<option value=" . $rOrg['koderekanan'] . ">" . $optcusto[$rOrg['koderekanan']] . "</option>";
}

   #ambil list supplier
$str="select * from ".$dbname.".log_5supplier where status=1";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    @$optSupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']." (".$bar['supplierid'].")</option>";
}

   #ambil list kodebarang
$str="select * from ".$dbname.".log_5masterbarang where left(kodebarang,1) in ('3','8','9') order by kodebarang";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    @$optbrg.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." (".$bar['namabarang'].")</option>";
}

   #ambil list Status
$status= array('1' =>'Aktif' ,'0' =>'Tidak Aktif' );
foreach ($status as $sts=>$val) {
    @$optstatus.="<option value='".$sts."'>".$val."</option>";
}


    echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/newfile.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
		echo"<table>";
			echo"<tr>";
				echo"<td>No. BAST</td>";
				echo"<td>:</td>";
				echo"<td><input type='text' class='myinputtext' id='bilcari' name='bil' onkeypress='return tanpa_kutip(event)' style='width:200px;' maxlength='45' /></td>";
				echo"<td>".$_SESSION['lang']['tanggal']." BL</td>";
				echo"<td>:</td>";
				echo"<td><input type=text class=myinputtext id=tglcari onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:200px;'  size=10 maxlength=10 readonly/></td>";
			echo"</tr>";
			echo"<tr>";
				echo"<td>".$_SESSION['lang']['NoKontrak']."</td>";
				echo"<td>:</td>";
				echo"<td><input type='text' class='myinputtext' id='kontrakcari' name='kontrakcari' onkeypress='return tanpa_kutip(event)' style='width:200px;'' maxlength='45' /></td>";
				echo"<td>".$_SESSION['lang']['tanggal']." BAST</td>";
				echo"<td>:</td>";
				echo"<td><input type=text class=myinputtext id=tglbastcari onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:200px;'  size=10 maxlength=10 readonly/></td>";
			echo"</tr>";
		echo"</table>";
    echo"<button class=mybutton onclick=\"loadData(0)\">" . $_SESSION['lang']['find'] . "</button>";
    echo"</fieldset></td>
	
	 </tr>
	 </table> ";
    ?>
</div>
    <?php
    CLOSE_BOX();
    ?>
<div id="listData">
<?php OPEN_BOX() ?>
    <fieldset>
        <legend><?php echo $_SESSION['lang']['list'] ?></legend>

        <table cellspacing="1" cellpadding='3' border="0" class="sortable" width=100%>
            <thead>
                <tr class="rowheader">
                    <td align='center'>No.</td>
                    <td align='center'><?php echo $_SESSION['lang']['pt'] ?></td>
                    <td align='center'><?php echo $_SESSION['lang']['customer']; ?></td>     
                    <td align='center'><?php echo $_SESSION['lang']['kontrak']; ?></td> 
                    <td align='center'>Kg</td>  
                    <td align='center'>Nobl</td>    
                    <td align='center'><?php echo $_SESSION['lang']['tanggal']; ?></td> 
                    <td align='center'>Created Time</td>   
                    <td align='center'>Action</td>
                </tr>
            </thead>
            <tbody id="contain"><script>loadData(0)</script></tbody>
        </table>
    </fieldset>

                <?php CLOSE_BOX() ?>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();

?>
<?php
    $frm[0].="<fieldset style='float:left'>
        <legend>".$_SESSION['lang']['entryForm']."</legend>
        <table cellspacing='1' border='0'>
            <tr>
                <td>".$_SESSION['lang']['kontrak']."</td>
                <td>:</td>
                <td>
                    <select id='kontrak' name='kontrak' style='width:200px;' onchange='getpt()'>".$optkontrak."</select>
                    <img id=kontrak onclick=z.elSearch('kontrak',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
				 </td>
				 
				<td>FFA (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=ffa name=ffa  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>    
            </tr>

            <tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select disabled id='pt' name='pt' style='width:200px;'>".$pt."</select></td>                  

				<td>Moisture (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=moisture name=moisture  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>    	
            </tr>
			<tr>
				<td>".$_SESSION['lang']['customer']."</td>
				<td>:</td>
				<td><select disabled id='cust'  name='cust' style='width:200px;'>".$cust."</select></td>  
				
				<td>Dirt (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=dirt name=dirt  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>
            </tr>
            <tr>
                <td>". $_SESSION['lang']['tanggal']." BL</td>
                <td>:</td>
                <td>
                    <input type=text class='myinputtext' id='tgl'  onmousemove='setCalendar(this.id)' onkeypress='return false;' size='10' maxlength='10' style='width:200px;' readonly/>
                </td>
				
				<td>Dobi (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=dobi name=dobi  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>   
            </tr>
			 <tr>
                <td>". $_SESSION['lang']['tanggal']." BAST</td>
                <td>:</td>
                <td>
                    <input type=text class='myinputtext' onchange='getnobl()' id='tglbast'  onmousemove='setCalendar(this.id)' onkeypress='return false;' size='10' maxlength='10' style='width:200px;' readonly/>
                </td>
				
				<td>Broken (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=broken name=broken  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>
            </tr>
			<tr>
                <td>No. BAST</td>
                <td>:</td>
                <td ><input disabled type=text class=myinputtext id=bil name=bil  value=0  onkeypress=return tanpa_kutip(event) style=width:200px; maxlength=100 /></td> 
				
				<td>Impurities (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=impurities name=impurities  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>
            </tr>
			<tr>
                <td>Kg</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=kg name=kg  value=0  onkeyup=\"z.numberFormat('kg');\" onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>    
				
				<td>M & I (%)</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=mdani name=mdani  value=0  onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td> 
			</tr>
			<tr>
                <td>".$_SESSION['lang']['kota']."</td>
                <td>:</td>
                <td ><input  type=text class=myinputtext id=kota name=kota onkeypress=return tanpa_kutip(event) style=width:200px; maxlength=100 /></td>   
				
				<td>Rp/Kg Claim</td>
                <td>:</td>
                <td><input type=text class=myinputtextnumber id=rpkgclaim name=rpkgclaim value=0 onkeypress=return_angka_doang(event) style=width:200px; maxlength=45 /></td>    				
            </tr>
			<tr>
				<td><td><td id=tmbLheader>
				<button class=mybutton id=dtlAbn onclick=saveData()>" .$_SESSION['lang']['save']."</button><button class=mybutton id=cancelAbn onclick=bersih()>". $_SESSION['lang']['cancel']."</button><input type=hidden id=proses name=proses value=insert  />
				</td></td></td>
            </tr>
        </table>
    </fieldset>";
	$emodul='BASTSALES';
     @$arrmodul = getmodulefil($emodul);
        foreach($arrmodul as $key=>$val){
            @$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
        }

    $frm[1].="<fieldset style='float:left'>
        <legend>".$_SESSION['lang']['entryForm']."</legend>
        <table cellspacing='1' border='0'>
            <tr>
                <td >Kriteria</td>
                <td>:</td>
                <td>
                    <select id='kriteriaefil'>". $optkriteria."</select>
                </td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' class=mybutton>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=\"submitfile()\">Submit</button>
                     <button class=mybutton onclick=\"selesai()\">Selesai</button>
                </td>
                
            </tr>
        </table>
    ";

   
    $frm[1].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['nourut']."</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
		</fieldset>";


?>
<?php

//========================
$hfrm[0]='Entry Data';
$hfrm[1]='Upload File';
// $hfrm[2]=$_SESSION['lang']['seedcard'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1150);
//========================
CLOSE_BOX();
?>
</div>
<?php
echo close_body();
?>