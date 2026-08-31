<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('lib/zSelect2Lite.php');
?>

<script language=javascript1.2 src='js/alokasiIDC.js?v=<?= time(); ?>'></script>
<?
include('master_mainMenu.php');

#unit dropdown untuk filter, diambil dari unit yang pernah punya jurnal IDC (scope sama dengan daftar)
#pakai h.kodejurnal='IDC' (terindex) + join kodeorg dari detail, JANGAN nojurnal like '%/IDC/%' (full-scan jutaan baris)
$optUnitFilter="<option value=''>".$_SESSION['lang']['all']."</option>";
$uStr=$owlPDO->query("select distinct d.kodeorg from ".$dbname.".keu_jurnalht h
        inner join ".$dbname.".keu_jurnaldt d on d.nojurnal=h.nojurnal and d.nourut=1
        where h.kodejurnal='IDC' and d.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')");
$uStr->setFetchMode(PDO::FETCH_ASSOC);
while($uBar=$uStr->fetch()){
    $optUnitFilter.="<option value='".$uBar['kodeorg']."'>".$uBar['kodeorg']."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['input'].' '.$_SESSION['lang']['alokasiidc']).'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:90px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:90px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>
	    <table cellpadding=4>
	        <tr>
	            <td>".$_SESSION['lang']['notransaksi']."</td>
	            <td><input type=text class=myinputtext id=notransaksisch style='width:180px'></td>
	            <td>".$_SESSION['lang']['tanggal']."</td>
	            <td>
	                <input type=text class=myinputtext id=tglmulaisch readonly onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 style='width:90px'>
	                s/d
	                <input type=text class=myinputtext id=tglselesaisch readonly onmousemove=setCalendar(this.id) onkeypress='return false;' maxlength=10 style='width:90px'>
	            </td>
	            <td>".$_SESSION['lang']['unit']."</td>
	            <td><select class='select2' id=unitsch style='width:130px'>".$optUnitFilter."</select></td>
	            <td><button class=mybutton onclick=loaddataIDC()>".$_SESSION['lang']['find']."</button></td>
	        </tr>
	    </table>
	 </fieldset></td>
     </tr>
     </table>";
CLOSE_BOX();

echo"<div id=header style=display:none>";
OPEN_BOX('','');
echo"<fieldset><legend>".$_SESSION['lang']['form']."</legend>
	<table cellpadding=4>
		<tr>
			<td width=80px>".$_SESSION['lang']['tanggal']."</td>
            <td>
				<input style='width:200px' class='myinputtext' id='tanggal' size='26' onmousemove='setCalendar(this.id)' maxlength='10' onkeypress='return false;' type='text' onblur=ambilBuktiKas(this.value)>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nojurnal']."</td>
			<td>
				<select class='select2' id=nokas onchange=ambilTipeAlokasi() style='width:200px'></select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td id='detailjurnal'></td>
		</tr>
		<tr>
			<td>Status Blok</td>
			<td>
				<select class='select2' id=stblok onchange=ambilAlokasi() style='width:200px'></select>
			</td>
		</tr>
		<tr>
			<td>Tahun Tanam</td>
			<td>
				<select class='select2' id=thntnm style='width:200px'></select>
			</td>
		</tr>
		<tr>
			<td style='vertical-align:top'>".$_SESSION['lang']['alokasibiaya']."</td>
            <td>
				<table>
					<tr>
						<td style='vertical-align:top'>
							<select class='select2' id=alokasi onchange=getAfd() style='width:197px'></select>
						</td>
						<td id='listafd'>
						</td>
					</tr>
				</table>
                <!--<select id=afdeling onchange=ambilBlok(this.options[this.selectedIndex].value)></select>-->
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<button class=mybutton onclick=ambilBlok()>".$_SESSION['lang']['proses']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=space></div>";
CLOSE_BOX();
echo"</div>";

echo"<div id=listdata style=display:block>";
OPEN_BOX('','');

echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
          <button onclick=exportListExcel()>Excel</button>
          <table id=tblListIDC class=sortable border=0 cellspacing=1 cellpadding=3>
             <thead>
              <tr class=rowheader>
             <td>".$_SESSION['lang']['nomor']."</td>
             <td>".$_SESSION['lang']['nojurnal']."</td>
             <td>".$_SESSION['lang']['unit']."</td>
              <td>".$_SESSION['lang']['tanggal']."</td>
              <td>".$_SESSION['lang']['dibuat']."</td>
              <td>".$_SESSION['lang']['noreferensi']."</td>
              <td>".$_SESSION['lang']['keterangan']."</td>
			  <td>".$_SESSION['lang']['jumlah']."</td>
              <td>".$_SESSION['lang']['action']."</td>
             </tr>
             </thead>
             <tbody id=containIDC>
                <script>loaddataIDC()</script>
             </tbody>
             <tfoot></tfoot>
             </table>
          </fieldset>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>