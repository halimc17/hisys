<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper("Persetujuan Barang Masuk").'</span>');

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_persetujuan_barangmasuk.js"></script>
<div id="action_list">
<?php
echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=refresh_data()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>Form Pencarian</legend>"; 
                        echo "No. Dokumen:<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>&nbsp;";
                        echo "Tanggal Dokumen:<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
                        echo"<button class=mybutton onclick=refresh_data(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
<?php OPEN_BOX();?>
<fieldset>
<legend>List Persetujuan Barang Masuk</legend>
<div style="overflow:scroll; height:420px;">
	<table class="sortable" cellspacing="1" border="0">
		<thead>
		<tr class=rowheader>
			<td style='text-align:center'>No.</td>
			<td style='text-align:center'>Gudang</td>
			<td style='text-align:center'>No. Dokumen</td> 
			<td style='text-align:center'>Tanggal</td>
			<td style='text-align:center'>No. PO</td>
			<td style='text-align:center'>Supplier</td>
			<td style='text-align:center'>Persetujuan 1</td>
			<td style='text-align:center'>Persetujuan 2</td>
			<td style='text-align:center'>*</td>
		</tr>
         </thead>
         <tbody id="contain">
        <script>refresh_data(0)</script>
         <?php 

         ?>
          </tbody>
         <tfoot id="footData">
         </tfoot>
         </table></div>
</fieldset
><?php
CLOSE_BOX();
?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid']?>" />
<?
echo close_body();
?>