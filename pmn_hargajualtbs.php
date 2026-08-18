<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pmn_hargajualtbs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php
$optunit=$optcustomer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>"; 

## GET PERIODE
// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' AND inti='1'";
// $res=fetchdata($str);
// foreach($res as $val){
//     $optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>"; 
// }

## GET PT
// $optunit='';
$unit='';
$arrUnit = getOrgDetail(23);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	// if($key==$_SESSION['empl']['lokasitugas']){
	// 	$optunit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
	// 	$unit=$key;
	// }else{
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	// }
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

$sql = selectQuery($dbname,"setup_parameterappl","*","kodeaplikasi='TB' and kodeparameter='TBTBS'");
$res = fetchData($sql,"OBJECT")[0];
$tbs = $res->nilai;

$str="select * from ".$dbname.".pmn_4komoditi a left join ".$dbname.".pmn_4customer b on a.kodecustomer=b.kodecustomer where kodebarang='".$tbs."' and inisialcustomer!='NULL' order by namacustomer";
// echo $str;
$res=fetchdata($str);
foreach($res as $val){
    $optcustomer.="<option value='".$val['kodecustomer']."'>".$val['kodecustomer']." - ".$val['namacustomer']."</option>"; 
}


OPEN_BOX('','<span class=judul>'.getMenu('pmn_hargajualtbs').'</span>');
$arrht="###kodeorg###tahuntanam###bjr###kodecustomer###hargadisbun###tanggal###tanggal2###harga###notransaksi";

echo"<fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr hidden>
			<td>".$_SESSION['lang']['notransaksi']."</td> 
			<td>:</td>
			<td coslpan=2><input type=text id=notransaksi onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td coslpan=2><select id=kodeorg style=\"width:205px;\">" . $optunit . "</select></td>
			
			<td>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</td> 
			<td>:</td>
			<td coslpan=2><input type=text id=tahuntanam onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext  style=\"width:200px;\" maxlength=100 ></td>
			
			<td>".$_SESSION['lang']['bjr']." </td> 
			<td>:</td>
			<td coslpan=2><input type=text id=bjr class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['customer']."</td> 
			<td>:</td>
			<td coslpan=2><select id=kodecustomer style=\"width:205px;\">" . $optcustomer . "</select>
			
			<td>".$_SESSION['lang']['harga']."  Disbun</td> 
			<td>:</td>
			<td coslpan=2><input type=text  id=hargadisbun class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</td>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td> 
			<td>:</td>
			<td>
			<input type='text' class='myinputtext' readonly=readonly id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' value='".date('d-m-Y')."'/>
			<input type='text' class='myinputtext' readonly=readonly id='tanggal2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' value='".date('d-m-Y')."'/>
			</td>
			
			<td>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['realisasi']."</td> 
			<td>:</td>
			<td coslpan=2><input type=text id=harga class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		
	
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=saveht('".$arrht."')>".$_SESSION['lang']['save']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>

</fieldset>";
CLOSE_BOX();

OPEN_BOX('','');
// OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br>');

	echo" <table cellpading=1 cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td> 
				<td>:</td>
				<td coslpan=2><select id=kodeorgsch style=\"width:205px;\">" . $optunit . "</select></td>
				<td>".$_SESSION['lang']['customer']."</td> 
				<td>:</td>
				<td coslpan=2><select id=kodecustomersch style=\"width:205px;\">" . $optcustomer . "</select>
				<td>".$_SESSION['lang']['tanggal']."</td> 
			<td>:</td>
			<td>
			<input type='text' class='myinputtext' readonly=readonly id='tanggalsch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;'/>
			<input type='text' class='myinputtext' readonly=readonly id='tanggal2sch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:95px;' />
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>&nbsp;
			<button id=batal class=mybutton onclick=cancelsch()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		</table>
	";

    echo "
    <div class=table-scroll style='height:80vh'>";
           echo " <table cellpadding=5 cellspacing=1 border=0 class=sortable >
            <thead>
                <tr class=rowheader>
					<th  align=center>".$_SESSION['lang']['nourut']."</th>
					<th  align=center>".$_SESSION['lang']['notransaksi']."</th>
					<th  align=center>".$_SESSION['lang']['unit']."</th>
					<th  align=center>".$_SESSION['lang']['customer']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['tahuntanam']."<br>".$_SESSION['lang']['grade']."</th>
                    <th  align=center>".$_SESSION['lang']['bjr']." </th>
                    <th  align=center>".$_SESSION['lang']['harga']." Disbun</th>
                    <th  align=center>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['realisasi']."</th>
					 <th  align=center>".$_SESSION['lang']['dibuatoleh']."</th> 
                    <th  align=center>".$_SESSION['lang']['approval_status']."</th> 
                    <th  align=center colspan=5 style=width:50px>".$_SESSION['lang']['action']." </th> 
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
			<tfoot id=footData></tfoot>
             </table>
	</div>";

	
CLOSE_BOX();
echo close_body();                  
?>