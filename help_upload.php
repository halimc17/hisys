<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('help_upload').'</span>');
?>

<script language="javascript" src="js/help_upload.js"></script>

<?php
$_SESSION['helpupload'] = array();

## GET MODUL
$optscmenu.="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".menu where type='master' order by urut asc";
$res=fetchdata($str);
foreach($res as $val){
	$optscmenu.="<option value='".$val['id']."'>".$val['caption']."</option>";
	$optmenu.="<option value='".$val['id']."'>".$val['caption']."</option>";
}

echo"<table cellspacing=1 border=0>
	<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/newfile.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td>
		<fieldset>
			<legend>" . $_SESSION['lang']['find']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['modul']."</td>
					<td>:</td>
					<td>
						<select id='scmodul' onchange='loaddata(0)'>".$optscmenu."</select>
					</td>
					
					<td style='padding-left:10px;'>".$_SESSION['lang']['judul']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='scjudul' onkeypress='return tanpa_kutip(event)' style='width:200px;'' />
					</td>
					
					<td style='padding-left:10px;'>
						<button class=mybutton onclick=\"loaddata(0)\">".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</fieldset>
		</td>
	</tr>
</table>";
CLOSE_BOX();

## BEGIN LIST DATA ##
echo"<div id='listData'>";
OPEN_BOX();

echo"<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<table cellspacing='1' cellpadding='3' border='0' class='sortable'>
		<thead>
        <tr class='rowheader'>
			<td align='center'>".$_SESSION['lang']['nourut']."</td>
            <td align='center'>".$_SESSION['lang']['modul']."</td>
			<td align='center'>".$_SESSION['lang']['judul']."</td>
			<td align='center'>".$_SESSION['lang']['langname']."</td> 
            <td align='center'>".$_SESSION['lang']['status']."</td>
            <td align='center'>".$_SESSION['lang']['updateby']."</td>
            <td align='center' colspan='2'>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id='contain'><script>loaddata(0)</script></tbody>
        </table>
    </fieldset>";

CLOSE_BOX();
echo"</div>";
## END LIST DATA ##

## BEGIN INPUT FORM ##
echo"<div id='headher' style='display:none'>";
OPEN_BOX();

## GET BAHASA
$str="select * from ".$dbname.".namabahasa order by code";
$res=fetchdata($str);
foreach($res as $val){
	if($val['code']=='ID'){
		$optbahasa.="<option value='".$val['code']."' selected>".$val['name']."</option>";		
	}else{
		$optbahasa.="<option value='".$val['code']."'>".$val['name']."</option>";		
	}
}

echo"<fieldset style='float:left'>
	<legend>".$_SESSION['lang']['entryForm']."</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td style='vertical-align:top'>
			<table cellspacing='1' border='0'>
				<tr>
					<td>".$_SESSION['lang']['modul']."</td>
					<td>:</td>
					<td>
						<select id='modul'>".$optmenu."</select>
						<img id=imgmodul onclick=z.elSearch('modul',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['judul']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='judul' onkeypress='return tanpa_kutip(event)' style='width:350px;'' />
					</td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['upload']."</td>
					<td style='vertical-align:top'>:</td>
					<td style='vertical-align:top'>
					<table class=sortable  cellspacing=1 cellpadding=3>
						<thead>
						<tr class='rowheader'>
							<td>".$_SESSION['lang']['langname']."</td>
							<td>".$_SESSION['lang']['file']."</td>
							<td>".$_SESSION['lang']['action']."</td>
						</tr>
						</thead>
						<tbody id='containerupload'></tbody>
						<tbody>
						<tr class='rowcontent' style='text-align:center'>
							<td>
								<select id='bahasa'>".$optbahasa."</select>
							</td>
							<td>
								<input type='file' name='upload' id='upload' class=mybutton>
							</td>
							<td>
								<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"submitfile();\">
							</td>
						</tr>
						</tbody>
					</table>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align:center'>
				<input type=hidden id=modulold value=''  />
				<input type=hidden id=judulold value=''  />
				<input type=hidden id=method value=insert  />
				<button class=mybutton onclick=simpan()>" .$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>". $_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo "</div>";
## END INPUT FORM ##


echo close_body();
?>