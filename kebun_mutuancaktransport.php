<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/kebun_mutuancaktransport.js'></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_mutuancaktransport').'</span>');

##deklarasi untuk option##

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and induk='" . $_SESSION['empl']['lokasitugas'] . "' ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}


$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN'  ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}


$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

//Form Buat Baru, List Data dan Pencarian Data
echo"<div id=action_list>";
echo"<table>
     	<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()><img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
			<td align=center style='width:100px;cursor:pointer;' onclick=displayList()><img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 		<td>
	        <fieldset>
	        	<legend>" . $_SESSION['lang']['find'] . "</legend>
	     		<table>
	                <tr>
	                    <td>" . $_SESSION['lang']['divisi'] . "</td> 
	                    <td>:</td>
	                    <td><select id=divsch  style=\"width:100px;\">" . $optorg . "</select></td>
	                </tr>
	                <tr>
	                    <td>" . $_SESSION['lang']['tanggal'] . "</td> 
	                    <td>:</td>
	                    <td><input type=text class=myinputtext  id=tglsch onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:95px;\" /></td>
	            	</tr>
					<tr>
						<td><td>
						<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>
						</td>
					</tr>
				</table>
			</fieldset>
			</td>
  
			<td>       
	        <fieldset>
	        	<legend>" . $_SESSION['lang']['print'] . "</legend> 		
	         	<table>
	                <tr>
	                    <td>" . $_SESSION['lang']['unit'] . "</td> 
	                    <td>:</td>
	                    <td><select id=unitexp  style=\"width:100px;\">" . $optunit . "</select></td>
	                </tr>
	                <tr>
	                    <td>" . $_SESSION['lang']['periode'] . "</td> 
	                    <td>:</td>
	                    <td><select id=perexp  style=\"width:100px;\">" . $optper . "</select></td>
	                </tr>
					<tr>
						<td><td>
						<td><button class=mybutton onclick=excel(event,'kebun_slave_mutuancaktransport.php')>" . $_SESSION['lang']['excel'] . "</button></td>
						</td>
					</tr>
				</table>
			</fieldset>
			</td>
	    </tr>
	</table> ";
CLOSE_BOX();
echo "</div>";


// List Data (Menu Tampilan)
echo"
<div id=listData style=display:block>"; //buka list data
OPEN_BOX(); 

##Get Kriteria Mutu
$arrKriteria = array();
$str = "select idjenis, jenis, kriteria, satuan from ".$dbname.".kebun_5jenismutu where jenis!='Mutu Buah' order by jenis asc, idjenis asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) 
{
	$arrKriteria[$bar['idjenis']]['kriteria'] = $bar['kriteria'];
	$arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
	$arrKriteria[$bar['idjenis']]['jenis'] = $bar['jenis'];
	$arrJenis[$bar['jenis']] = $bar['jenis'];
	$countKriteria[$bar['jenis']] += 1;
	$arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
}

echo "<fieldset>
      	<legend>" . $_SESSION['lang']['list'] . "</legend>
            <div>    
            	<table border=0 cellpadding=1 cellspacing=1 class=sortable>
			        <thead>
			        <tr class=rowheader>    
			        	<td align=center rowspan='2' >" . $_SESSION['lang']['nourut'] . "</td>
			            <td align=center rowspan='2' style=width:70px>" . $_SESSION['lang']['tanggal'] . "</td>
			            <td align=center rowspan='2' style=width:60px>" . $_SESSION['lang']['divisi'] . "</td>
			            <td align=center rowspan='2' style=\"width:125px;\">" . $_SESSION['lang']['kemandoran'] . "</td>
						<td align=center rowspan='2' style=width:50px>" . $_SESSION['lang']['pokok'] . "<br>".$_SESSION['lang']['sample']."</td>
			            <td align=center rowspan='2' style=width:50px>" . $_SESSION['lang']['pokok'] . " yg di ".$_SESSION['lang']['panen']."</td>
			            <td align=center rowspan='2' style=width:50px>" . $_SESSION['lang']['jjg'] . " ".$_SESSION['lang']['panen']."</td>";
					$tempjenis = "";
					if(isset($arrJenis))
					foreach($arrJenis as $key)
					{
						if($tempjenis=='' or $tempjenis==$key)
						{
							echo "<td align=center colspan='".$countKriteria[$key]."'>".$key."</td>";
						}
						else
						{
							echo "<td align=center colspan='".(($countKriteria[$key])+1)."'>".$key."</td>";
						}
						$tempjenis=$key;
					}
			        echo"<td align=center rowspan='2' colspan=4>" . $_SESSION['lang']['action'] . "</td>
			        </tr>
			        <tr>";
					$tempjenis = "";
					if(isset($arrKriteria))
			        foreach($arrKriteria as $key=>$val)
					{
						if($tempjenis=='' or $tempjenis==$val['jenis'])
						{
							echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
						}
						else
						{
							echo "<td align=center>Jumlah<br>TPH</td>";
							echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
						}
						$tempjenis = $val['jenis'];
					}
			        echo"</tr>
			        </thead>
		            <tbody id=contain> 
		                <script>loaddata(0)</script>
		            </tbody>
		            <tfoot id=footData>
		            </tfoot>
		        </table>
             </div>
	</fieldset>";
CLOSE_BOX();
echo "</div>"; //tutup list data

//Form Input Header
echo "<div id=header style=display:none>"; 
OPEN_BOX();
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
    		<tr>
	            <td>" . $_SESSION['lang']['tanggal'] . "</td> 
	            <td>:</td>
	            <td><input type=text style=\"width:130px;\" class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>
    		</tr>
    		<tr>
            	<td>" . $_SESSION['lang']['divisi'] . "</td> 
            	<td>:</td>
            	<td><select style=\"width:133px;\" id=divisi>" . $optorg . "</select></td>
    		</tr> 
			<tr>
	            <td colspan=2></td>
	            <td><button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
	            	<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button></td>
            		<input type=hidden id=method value='insert'>
			</tr>
		</table>
	  </fieldset>";
CLOSE_BOX();
echo"</div>";

//Div Form Detail, Form setelah Form Header
echo"<div id=detail style=display:none>";
OPEN_BOX();

/*
  echo"
  <fieldset style='float:left;'>
  <script>detail()</script>
  </fieldset>";
 */

CLOSE_BOX();
echo"</div>";


echo close_body('');
?>