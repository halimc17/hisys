<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
echo"<div id=dataAtas>";
OPEN_BOX('','<span class=judul>'.strtoupper('PURCHASE ORDER (Local on site)').'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_po_lokal.js" /></script>
<div id="action_list">
<?php
// <!--td align=center style='width:100px;cursor:pointer;' onclick=show_list_pp()>
          // <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>-->
echo"<table>
     <tr valign=moiddle>

         <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
                        echo $_SESSION['lang']['nopo']." : <input type=text id=txtsearch size=20 maxlength=30 class=myinputtext>";
                        echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
                        echo"<button class=mybutton onclick=cariNopo()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>";
echo"<td><fieldset><legend>List Job</legend><div id=notifikasiKerja>";
echo"<script>loadNotifikasi()</script>";
echo"</div>
</fieldset></td>";
echo"</tr>
         </table></div> "; 
?>
</div>
<?php
CLOSE_BOX(); echo "</div>"; //1 C
echo "<div id=\"list_po\">";
OPEN_BOX(); //2 O
?>
<!--<img src="images/pdf.jpg" onclick="masterPDF('log_poht','','','log_listpo',event)" width="20" height="20" />-->
<fieldset>
    <legend><?php echo $_SESSION['lang']['listpo']?></legend>
    <div id="contain">
    <script>load_new_data()</script>
    </div>

</fieldset>
<?php CLOSE_BOX();?>
</div>



<?php
echo "<div id=list_pp name=list_pp style=display:none;>";
  OPEN_BOX();
   $optPt='';
        $sql3="select * from ".$dbname.".organisasi where tipe='PT'";
		$query3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
		$query3->setFetchMode(PDO::FETCH_OBJ);
        while($res3=$query3->fetch())
        {
            $optPt.="<option value='".$res3->kodeorganisasi."'>".$res3->namaorganisasi."</option>";
        }
$frm[0]=" 
    <fieldset>
        <legend>".$_SESSION['lang']['list_pp']."</legend>
     <table cellspacing=1 border=0>
         <tr>
         <td>Please Select Company</td>
         <td>:</td>
         <td><select id=kode_pt name=kode_pt onchange=cek_pp_pt()>
         <option value=''></option>".$optPt."</select></td></tr>
     <br />
         <input type=hidden id=proses name=proses value=insert />
    <table cellspacing=1 border=0 id=list_pp_table class=sortable>
        <thead>
        <tr class=rowheader>
            <td>No.</td>
            <td align=center width=75px>".$_SESSION['lang']['tglPelimpahan']."</td>
            <td align=center>".$_SESSION['lang']['nopp']."</td>
            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center width=50px>".$_SESSION['lang']['jmlhDiminta']."</td>
            <td align=center>".$_SESSION['lang']['tgldibutuhkan']."</td>
            <td align=center width=50px>".$_SESSION['lang']['jmlh_brg_blm_po']."</td>
            <td align=center width=50px>".$_SESSION['lang']['jmlhPesan']."</td>
            <td align=center>Action</td>
        </tr>
        </thead>

            <tbody id=container_pp>		

            <tr><td colspan=9 align=center><button name=proses id=proses onclick=process()>".$_SESSION['lang']['proses']."</button></td></tr>
        </tbody>
    </table>
        <input type=hidden id=user_id name=user_id value=".$_SESSION['standard']['userid']." />
        </table>
        </fieldset>";
		/*
        $str="select * from ".$dbname.".log_perintaanhargaht where 1=1 and purchaser='".$_SESSION['standard']['userid']."' and
				flag='1' and nomor not in (select nodph from ".$dbname.".log_poht where nodph !='')";
				*/
		 $str="select distinct(a.nomor) as nomor from ".$dbname.".log_perintaanhargaht 
			a left join ".$dbname.".log_permintaanhargadt b
			on a.nomor=b.nomor
			where 1=1 and a.purchaser='".$_SESSION['standard']['userid']."' and
			b.flag='1' and a.nomor not in (select nodph from ".$dbname.".log_poht where nodph !='')";		
				
		$query5=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$query5->setFetchMode(PDO::FETCH_OBJ);
		$optRPH=$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        while($res5=$query5->fetch()){
            $optRPH.="<option value='".$res5->nomor."'>".$res5->nomor."</option>";
        }
		
		
$frm[1]=" 
    <fieldset>
        <legend>".$_SESSION['lang']['form']."</legend>
     
    <table cellspacing=1 border=0>
		</tr>
			<td>No ".$_SESSION['lang']['bandingHarga']."</td>
			<td>:</td>
			<td>
			<select id=nodph style='width:200px'  onchange=getsuprph()>".$optRPH."</select>
			<img id='nodph' onclick=z.elSearch('nodph',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>	 
		</tr>
		</tr>
			<td>".$_SESSION['lang']['namasupplier']."</td>
			<td>:</td>
			<td>
			<select id=suprph style='width:200px'>".$optsup."</select>
			</td>	 
		</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=adddph()>".$_SESSION['lang']['proses']."</button></td>	 
		</tr>
		
        </table>
        </fieldset>";		
		
	
		$hfrm[0]=$_SESSION['lang']['daftarbarang'];
		$hfrm[1]=$_SESSION['lang']['bandingHarga'];
		drawTab('FRM',$hfrm,$frm,200,1000);
?>

<?php
CLOSE_BOX();
?>
</div>
<div id="form_po" style="display:none;">
    <?php 

    OPEN_BOX();
	$isiOpt= array(1=>'Cash',2=>'Transfer',3=>'Giro',4=>'Cheque');
	$optTermpay="";
	foreach($isiOpt as $ter => $OptIsi)
	{
		$optTermpay.="<option value='".$ter."'>".$OptIsi."</option>";
	}
    $optSupplier='';
	$snmkary="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
	$qnmkary=$owlPDO->query($snmkary) or die(print " Gagal: ".PDOException::getMessage());
	$qnmkary->setFetchMode(PDO::FETCH_ASSOC);
	$rnmkary=$qnmkary->fetch();
	
	$nmkelsup=makeOption($dbname,'log_5klsupplier','kode,kelompok');
	
    $sql="select namasupplier,supplierid,kodekelompok from ".$dbname.".log_5supplier  where kodekelompok in ('S001','S002') 
	and status=1 order by namasupplier asc";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
    while($res=$query->fetch())
    {
       $optSupplier.="<option value='".$res['supplierid']."'>".$res['namasupplier']."     [".$nmkelsup[$res['kodekelompok']]."]</option>";
    }
        $sMt="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
        $qMt=$owlPDO->query($sMt) or die(print " Gagal: ".PDOException::getMessage());
		$qMt->setFetchMode(PDO::FETCH_ASSOC);
		$optMt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        while($rMt=$qMt->fetch())
        {
            $optMt.="<option value='".$rMt['kode']."' ".($rMt['kode']=='IDR'?"selected":"").">".$rMt['kodeiso']."</option>";
        }
        
		$user_id=$_SESSION['standard']['userid'];
		$klq="select namakaryawan,karyawanid,bagian,lokasitugas from ".$dbname.".`datakaryawan` where
            (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and
            karyawanid!='".$user_id."' and lokasitugas!='' and
            tanggalkeluar='0000-00-00' order by namakaryawan asc"; 
		$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
        $optPur="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        while($rst=$qry->fetch())
        {
                $sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
				$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
				$qBag->setFetchMode(PDO::FETCH_ASSOC);
                $rBag=$qBag->fetch();
                $optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
        }
        
		$sKrm="select id_franco,franco_name from ".$dbname.".setup_franco where status=0 order by franco_name asc";
        $qKrm=$owlPDO->query($sKrm) or die(print " Gagal: ".PDOException::getMessage());
		$qKrm->setFetchMode(PDO::FETCH_ASSOC);
		$optKrm="";
        while($rKrm=$qKrm->fetch())
        {
			$optKrm.="<option value=".$rKrm['id_franco'].">".$rKrm['franco_name']."</option>";
        }
		
		$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar order by keterangan asc";
		$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
		$qSyp->setFetchMode(PDO::FETCH_ASSOC);
		$optSyp="";
        while($rSyp=$qSyp->fetch())
        {
			$optSyp.="<option value=".$rSyp['kode'].">".$rSyp['keterangan']." (".$rSyp['jenis'].")</option>";
        }
    ?>
    <fieldset>
        <legend><?php echo $_SESSION['lang']['form_po']?></legend>
    <fieldset>
	<table cellspacing="1" border="0">
        <tr>
            <td><?php echo $_SESSION['lang']['nopo']?></td>
            <td>:</td>
            <td><input type="text" name="no_po" id="no_po" class="myinputtext" style="width:150px;" disabled="disabled" /></td>

            <td><?php echo $_SESSION['lang']['tanggal']?></td>
            <td>:</td>
            <td colspan="4"><input type="text" name="tgl_po" id="tgl_po" class="myinputtext" value="<?php echo date("d-m-Y");?>"  readonly="readonly" style="width:150px;" /></td>
        
        <tr>
            <td><?php echo $_SESSION['lang']['npwp']?></td>
            <td>:</td>
            <td><select id="npwporg" name="npwporg" style="width:155px;"><?php echo $optnpwp?></select></td>
        
            <?
            $optbank="<option value=''></option>";
            echo"
                <td>".$_SESSION['lang']['norekeningbank']."</td>
                <td>:</td>
                <td  colspan=4><select id=bank_acc style=\"width:155px;\">".$optbank."</select></td>
            ";
            ?>
        </tr>
		
		
		</tr>
         <tr>
            <td><?php echo $_SESSION['lang']['namasupplier']?></td>
            <td>:</td>
            <td>
                        <select id="supplier_id" name="supplier_id" onchange="get_supplier()" style="width:155px;" >
                        <option value=""></option>
                        <?php echo $optSupplier; ?>
                        </select>
                <img src="images/zoom.png" class="resicon" title='<?php echo $_SESSION['lang']['findRkn']; ?>' onclick="searchSupplier('<?php echo $_SESSION['lang']['findRkn']; ?>','<fieldset><?php echo $_SESSION['lang']['find']; ?>&nbsp;<input type=text class=myinputtext id=nmSupplier><button class=mybutton onclick=findSupplier()><?php echo $_SESSION['lang']['find']; ?></button></fieldset><div id=containerSupplier style=overflow=auto;height=380;width=485></div>',event);"></td>
            <td><?php echo $_SESSION['lang']['npwp']?></td>
            <td>:</td>
            <td><input type="text" id="npwp_sup" name="npwp_sup" class="myinputtext" onkeypress="return angka_doang(event)" style="width:150px;" disabled="disabled"></td>
       
            <!--
				<td><?php echo $_SESSION['lang']['norekeningbank']?></td>
                        <td>:</td>
                        <td colspan="4"><input type="text" id="bank_acc" name="bank_acc" class="myinputtext" onkeypress="return angka_doang(event)" style="width:150px;" disabled="disabled"></td>
				-->
				
				<?
				// $optbank="<option value=''></option>";
				// echo"
				// 	<td>".$_SESSION['lang']['norekeningbank']."</td>
				// 	<td>:</td>
				// 	<td colspan=4><select id=bank_acc style=\"width:155px;\">".$optbank."</select></td>
				// ";
				?>
				
		</tr>
                <tr>
            
            <td><?php echo $_SESSION['lang']['matauang']?></td>
                        <td>:</td>
                        <td><select  style="width:155px;" id="mtUang" name="mtUang" style="width:150px;"><?php echo $optMt?></select></td>
        
            <td><?php echo $_SESSION['lang']['kurs']?></td>
                        <td>:</td>
                        <td><input type="text" class="myinputtext" id="Kurs" name="Kurs" style="width:150px;" onkeypress="return angka_doang(event)" value="1"  /></td>
        </tr>
          <tr>
            <td><?php echo $_SESSION['lang']['tandatangan']?> 1</td>
                        <td>:</td>
                        <td><select id="persetujuan_id" name="persetujuan_id" style="width:155px;" ><?php echo $optPur?></select></td>
        
            <td><?php echo $_SESSION['lang']['tandatangan']?> 2</td>
                        <td>:</td>
                        <td colspan="4"><select id="persetujuan_id2" name="persetujuan_id2" style="width:155px;" ><?php echo $optPur?></select></td>
        </tr>
           <tr  style="display:none;">
            <td><?php echo $_SESSION['lang']['ongkoskirim']?></td>
                        <td>:</td>
                        <td><input type="text" id="ongKirim" class="myinputtextnumber" style="width:150px" onkeypress="return angka_doang(event)"  onblur="disNum()" onfocus="nor_num()" value="0" /></td>
        </tr>
        </table></fieldset>

                        <fieldset style="min-width:95%">
                                <legend><?php echo $_SESSION['lang']['daftarbarang']?></legend>
                <table cellspacing="1" border="0" id="detail_content_table" name="detail_content_table">
                    <tbody id="detail_content" name="detail_content">
                        <tr><td><table id='ppDetailTable'>
                        </table>

                                <table cellspacing='1' border='0'>
        <tr style="display:none;">
            <td><? echo $_SESSION['lang']['tgl_kirim'] ?></td>
                        <td>:</td>
                        <td><input type="text" class="myinputtext" id="tgl_krm" name="tgl_krm" onmousemove="setCalendar(this.id)" onkeypress="return false";   maxlength="10"  style="width:200px;" value="0000-00-00" /></td>
        </tr>
		
		<?php
		echo"
			<tr hidden>
			<td>Internal Memo</td>
			<td>:</td>
			<td><input name=fileupload type=file id=fileupload title='file hanya : JPG,JPEG,PNG,PDF' class=mybutton style=width:160px>
			<img  title='hapus file terpilih' class=zImgBtn onclick=clearfile() src=images/delete_32.png>
			</td>
			</tr>
		";
		
		$str="select *  from ".$dbname.".log_5delivtime";  
		$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optjenis.="<option value='".$bar['kode']."'>".$bar['nama']."</option>";
		}
		
		
		echo"
			<tr>
			<td>".$_SESSION['lang']['waktupenyerahan']."</td>
			<td>:</td>
			<td><select id=delivtime style=\"width:200px;\">".$optjenis."</select></td>
			</tr>
		";
		
		?>
		
                  <tr>
            <td><? echo $_SESSION['lang']['almt_kirim'] ?></td>
                        <td>:</td>
                        <td><select style="width:200px" id='tmpt_krm' name='tmpt_krm1'><? echo $optKrm ?></select>
                        <!--<input type='text'  id='tmpt_krm' name='tmpt_krm' maxlength='45' class='myinputtext' onkeypress='return tanpa_kutip(event);' style=width:200px />--></td>
        </tr>
         <tr>
            <td><? echo $_SESSION['lang']['syaratPem'] ?></td>
                        <td>:</td>
                        <td>
						<select style="width:200px" id='term_pay' name='term_pay'><? echo $optSyp ?></select>
						<!-- <input type='text' id='term_pay' name='term_pay' class='myinputtext' onkeypress='return tanpa_kutip(event);' style="width:200px"   /> -->
						</td>
        </tr>
                 <tr>
            <td valign="top"><? echo $_SESSION['lang']['keterangan'] ?></td>
                        <td valign="top">:</td>
                        <td><textarea style="width:180px" id='ketUraian' name='ketUraian' onkeypress='return tanpa_kutip(event);' maxlength=30></textarea></td>
        </tr>
                <tr>
            <td><? echo $_SESSION['lang']['purchaser'] ?></td>
                        <td>:</td>
                        <td><input style="width:195px" type='text' id='purchaser_id' name='purchaser_id' class='myinputtext' disabled='disabled' value='<? echo $_SESSION['empl']['name'] ?>'  style='width:200px;' /></td>
        </tr></table>

                        </td></tr>

                    </tbody>
                </table>
                        </fieldset>


        <table cellspacing="1" border="0">
        <tr>
            <td colspan="3">
				<input type='hidden' id="btncancel">
                <button id='btnSaveHeader' class="mybutton" onclick="save_headher()"><?php echo $_SESSION['lang']['save']?></button>
                <button class="mybutton" onclick="cancel_headher()"><?php echo $_SESSION['lang']['cancel']?></button>
                                <!--<button class="mybutton"  ><?php echo $_SESSION['lang']['done']?></button>-->
            </td>
        </tr> 

    </table>
        </fieldset>
    <?php CLOSE_BOX(); ?>
</div>

<?
echo close_body();
?>