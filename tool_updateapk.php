<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>DOWNLOAD</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/tool_updateapk.js?v=1.1'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?
$sOrg = "select * from " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$admin = fetchdata($sOrg);
if(count($admin)>0){
	$i=" style=display:''";
	$d="style='overflow:auto;height:250px;max-width:100%';";
	$u="style='overflow:auto;height:250px;max-width:100%';";
	$x="style=min-height:200px";
}else{
	$x="style=min-height:500px";
	$d="style='overflow:auto;height:550px;max-width:100%';";
	$i=" style=display:none";
}

$strR = "select * from " . $dbname . ".data_version";
$resR = fetchdata($strR);
$strB = "select * from " . $dbname . ".data_versionlog  order by id desc limit 1";
$resB = fetchdata($strB);
$y='';
if($resB[0]['appversion']==$resR[0]['appversion']){
	$y=" hidden";
}
echo"<fieldset>
        <legend>Form</legend>
			<div id='printContainer' ".$d." >
            <table border=0 cellpadding=1 cellspacing=1 class=sortable>
				<tr class=rowheader>
                    <td align=center>No</td>
                    <td align=center>Nama Aplikasi</td>
                    <td align=center width=75px>Nama Versi</td>
                    <td align=center width=75px>Versi</td>
                    <td align=center>Tanggal Upload</td>
                    <td align=center width=100px>Status</td>
                    <td align=center>Tanggal Release</td>
                    <td align=center ".$i.">Release</td>
					<td align=center>Link</td>
                    <td align=center>Keterangan</td>
                </tr>";
			# last update / last release
			$sOrg = "select a.*,b.releaseby,b.tanggalrelease from " . $dbname . ".data_version a left join " . $dbname . ".data_versionlog b on a.appversion=b.appversion order by tanggalrelease desc limit 1";
			$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
			$no='';
			while ($bar = $qOrg->fetch()) {
				$versi=$bar['appversion'];
				$no++;
				$link2=explode("/",$bar['urlapp']);
				$v=$bar['appversion'];
				echo"<tr class=rowcontent>";
				echo"<td align=center>".$no."</td>";
				echo"<td align=center>OWL Mobile</td>";
				echo"<td align=center>".$bar['appversion_name']."</td>";
				echo"<td align=center>".$bar['appversion']."</td>";
				if($bar['tanggalrelease']=='0000-00-00 00:00:00'){$sts='Unrealese'; $tglreal=''; $tbl="disabled";}else{$sts='Release'; $tglreal=$bar['tanggalrelease']; $tbl="";}
				echo"<td align=center>".$bar['updatetime']."</td>";
				echo"<td align=center>".$sts."</td>";
				echo"<td align=center>".$tglreal."</td>";
				echo"<td align=center>".$bar['releaseby']."</td>";
				
				#echo"<td align=center><a href=\"".$link2[0]."//192.168.7.90/".$link2[3]."/".$link2[4]."/".$link2[5]."/".$link2[6]."\" download><button class=mybutton>Download</button></a></td>";
				echo"<td align=center><a href='".$bar['urlapp']."' download><button class=mybutton>Download</button></a></td>";
				echo"<td align=center></td>";
			}
			
			
			#beta test
			/* $sOrg = "select * from " . $dbname . ".data_versionlog where tanggalrelease='0000-00-00 00:00:00' and releaseby='' and appversion>'".$v."' order by id asc";
			$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $qOrg->fetch()) {
				$versi=$bar['appversion'];
				$no++;
				
				$link4[0]="http://192.168.7.90/owl/android/update/harvest-app-";
				$link4[1]=".apk";
				
				echo"<tr ".$i." ".$y." class=rowcontent>";
				echo"<td align=center>".$no."</td>";
				echo"<td align=center>OWL Mobile Harvestingx</td>";
				echo"<td align=center>".$bar['appversion_name']."</td>";
				echo"<td align=center>".$bar['appversion']."</td>";
				if($bar['tanggalrelease']=='0000-00-00 00:00:00'){$sts='Unrealese'; $tglreal=''; $tbl="disabled";}else{$sts='Release'; $tglreal=$bar['tanggalrelease']; $tbl="";}
				echo"<td align=center>".$bar['updatetime']."</td>";
				echo"<td align=center>".$sts."</td>";
				echo"<td align=center>".$tglreal."</td>";
				echo"<td align=center><button class=mybutton onclick=release('".$bar['appversion']."','".$bar['appversion_name']."')>Release</button></td>";
				
				echo"<td align=center><a href=\"".$link4[0]."".$bar['appversion']."".$link4[1]."\" download><button class=mybutton>Download</button></a></td>";
				echo"<td align=center><a href=\"".$bar['urlapp']."\" download><button class=mybutton>Download</button></a></td>";
				echo"<td align=center></td>";
			} */
			
			
			
			
			
			
        echo"</table>";
		$str = "select * from " . $dbname . ".data_versionlog order by id desc";
		$res = fetchdata($str);
		#echo"<fieldset ".$x."><legend>Update Log</legend>";
		foreach($res as $bar){
			if(strlen($bar['appversion'])==5){
				$versi=substr($bar['appversion'],0,1).".".substr($bar['appversion'],2,1).".".substr($bar['appversion'],4,1);
			}else{
				$versi=substr($bar['appversion'],0,1).".".substr($bar['appversion'],2,1).".".substr($bar['appversion'],4,1).".".substr($bar['appversion'],6,1);
			}
			echo"<div><b>Versi ".$versi."</b> ".$bar['updatetime']." :<br>".str_replace('####','<br>',$bar['updatelog'])."<br><br></div>";
		}
		#echo"</fieldset>";
		echo"</div>
</fieldset>";
CLOSE_BOX();

echo"<div ".$i.">";
OPEN_BOX('','<span class=judul>UPLOAD</span>');
echo"<fieldset>
        <legend>Form</legend>
			<div id='printContainer' ".$u." >";
		echo"<table border=0 >
			
			<tr>
				<td>Nama Versi</td>
				<td>:</td>
				<td>
					<input type=text placeholder='1.2.5' style=width:170px id=namaversi class=myinputtext onkeypress='return tanpa_kutip(event)' maxlength=5 class=myinputtext/>
				</td>
			</tr>
			
			<tr>
				<td>Versi</td>
				<td>:</td>
				<td>
					<input type=text placeholder='10205' style=width:170px id=versi class=myinputtext onkeypress='return tanpa_kutip(event)' maxlength=5 class=myinputtext/>
				</td>
			</tr>
			<tr>
				<td>File</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td valign=top>Update Log</td>
				<td valign=top>:</td>
				<td>
					<textarea rows='8' maxlength='1024' id='updatelog' type='text' onkeypress='return tanpa_kutip(event)' style='width:1000px;'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>";
		
        echo"</table>
			</div>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>