<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/keu_tagihan_unpost.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
OPEN_BOX();
echo "<div align='center'><h3>Invoice</h3></div>";
echo "<table width=100%><tr><td valign=top width=30%><fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>
    <label>".$_SESSION['lang']['noinvoice']."</label>
    <input id=noinvoice class=myinputtext type=text value='' onkeypress='return angka_doang(event);' name=noinvoice>
    <button id=sFind class=mybutton onclick=loadData() name=sFind>".$_SESSION['lang']['find']."</button>
    </fieldset></td>";
echo "<td width=70% valign=top><fieldset><legend><b>".$_SESSION['lang']['info']."</b></legend>
    <label>Unposting Tagihan hanya bisa dilakukan bila Tagihan belum dibayar (icon <img src=images/application/application_edit.png class=resicon title='Unposting'>)</label>
    </fieldset></td></tr></table>";
CLOSE_BOX();

# List
OPEN_BOX();

echo" <table class=sortable cellspacing=1 border=0 style='width:100%;'>
<thead>
<tr class=rowheader>
<td>".$_SESSION['lang']['noinvoice']."</td>
<td>".$_SESSION['lang']['perusahaan']."</td>
<td>".$_SESSION['lang']['tanggal']."</td>
<td>".$_SESSION['lang']['updateby']."</td>
<td>".$_SESSION['lang']['nopo']."</td>
<td>".$_SESSION['lang']['namasupplier']."</td>
<td>".$_SESSION['lang']['keterangan']."</td>
<td>".$_SESSION['lang']['subtotal']."</td>
<td>".$_SESSION['lang']['posted']."</td>
<td>".$_SESSION['lang']['action']."</td>
<tr class=rowheader>
</thead>
<tbody id=container><script>loadData()</script>
</tbody>
</table>
";

CLOSE_BOX();
echo close_body();
?>