<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/sdm_5stdwaktuabsen.js'></script>

<?php
for($i=0;$i<24;){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$jm.="<option value=".$i.">".$i."</option>";
	$i++;
}

for($i=0;$i<60;){
	if(strlen($i)<2){
		$i="0".$i;
	}
	$mnt.="<option value=".$i.">".$i."</option>";
	$i++;
}

$optspt=$optsunit=$optsstt="<option value=''>".$_SESSION['lang']['all']."</option>";

## GET PT
$unit="";
$no=0;
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $val){
	$no++;
	if($no==1){
		$unit=$val['kodeorganisasi'];
	}
	$optpt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$optspt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET UNIT
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."'";
$res=fetchdata($str);
foreach($res as $val){
	$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET STATUS
$optstt.="<option value='A'>".$_SESSION['lang']['aktif']."</option>";
$optsstt.="<option value='A'>".$_SESSION['lang']['aktif']."</option>";
$optstt.="<option value='D'>".$_SESSION['lang']['nonaktif']."</option>";
$optsstt.="<option value='D'>".$_SESSION['lang']['nonaktif']."</option>";

OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('sdm_5stdwaktuabsen')).'</span>');
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['kode']."</td> 
			<td>:</td>
			<td colspan=4><input type=text  id=kode onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\" maxlength=5 onkeydown='upperCaseF(this)' disabled> <font color=red>* ".$_SESSION['lang']['otomatis']."</font></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td> 
			<td>:</td>
			<td colspan=4>
				<select id='pt' onchange=\"getunit('')\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td colspan=4>
				<select id='unit'>".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td> 
			<td>:</td>
			<td colspan=4>
				<input id='keterangan' size='40' maxlength='40' onkeyup='upperCaseF(this)' onkeypress='return tanpa_kutip(event);' class='myinputtext' type='text'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jammasuk']."</td> 
			<td>:</td>
			<td>
				<select id='jam10' onchange=\"updatejam()\">".$jm."</select>:<select id='mnt10' onchange=\"updatemnt()\">".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['toleransi']." (+/-)</td> 
			<td>:</td>
			<td>
				<select id='jam11' onchange=\"updatejamt()\">".$jm."</select>:<select id='mnt11' onchange=\"updatemntt()\">".$mnt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jamistirahatdari']."</td> 
			<td>:</td>
			<td>
				<select id='jam20'>".$jm."</select>:<select id='mnt20'>".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['toleransi']." (+/-)</td> 
			<td>:</td>
			<td>
				<select id='jam21'>".$jm."</select>:<select id='mnt21'>".$mnt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jamistirahatsampai']."</td> 
			<td>:</td>
			<td>
				<select id='jam30'>".$jm."</select>:<select id='mnt30'>".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['toleransi']." (+/-)</td> 
			<td>:</td>
			<td>
				<select id='jam31'>".$jm."</select>:<select id='mnt31'>".$mnt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jamkeluar']."</td> 
			<td>:</td>
			<td>
				<select id='jam40'>".$jm."</select>:<select id='mnt40'>".$mnt."</select>
			</td>
			
			<td style='padding-left:20px;'>".$_SESSION['lang']['toleransi']." (+/-)</td> 
			<td>:</td>
			<td>
				<select id='jam41'>".$jm."</select>:<select id='mnt41'>".$mnt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['status']."</td> 
			<td>:</td>
			<td>
				<select id='stt'>".$optstt."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=4>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>
</fieldset><div>";

CLOSE_BOX();
?>



<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
		<b><i>".$_SESSION['lang']['find']."</i></b><br>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id='spt' onchange=\"getunitsearch()\">".$optspt."</select></td>
				
				<td style='padding-left:20px'>".$_SESSION['lang']['unit']."</td> 
				<td>:</td>
				<td>
					<select id='sunit' onchange=\"loaddata()\">".$optsunit."</select>
				</td>
				
				<td style='padding-left:20px'>".$_SESSION['lang']['status']."</td> 
				<td>:</td>
				<td>
					<select id='sstt' onchange=\"loaddata()\">".$optsstt."</select>
				</td>
				
				<td style='padding-left:20px'>
					<button class=mybutton onclick=loaddata()>".$_SESSION['lang']['find']."</button>
				</td>
				<td>
					<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
		<hr>
        <div id=container> 
            <script>loaddata()</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>