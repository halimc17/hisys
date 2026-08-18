<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src=js/zMaster.js></script> 
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src='js/formTable.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/rencana_gis.js'></script>
<link rel=stylesheet type=text/css href='style/fm.css'>

<link rel="stylesheet" href="style/jquery-ui.css" />
  <script src="js/jquery-1.9.1.js"></script>
  <script src="js/jquery-ui.js"></script>

<script>
  $(function() {
    $( "#table-css-border-1 tr th" ).resizable({
      handles: 'e'
    });
  });
  </script>

<div class="menu">
	<div id='btnrestore' class="menu-item">Restore</div>
	<div id='btnrename' class="menu-item">Rename</div>
	<div id='btndelete' class="menu-item">Delete</div>
	<div id='btnreloadframe' class="menu-item">Reload Frame</div>
</div>
<a id="invsdownload2" style="display:none;"></a>
<?php
$path = "fileupload/efilling/";

echo"<input type='hidden' id='id' value=''>
<input type='hidden' id='induk' value=''>
<input type='hidden' id='level' value=''>
<input type='hidden' id='previd' value=''>
<input type='hidden' id='tipefl' value=''>
<table style='width:100%;'>
	<tr>
		<td valign='top' style='width:70%;padding-left:5px;padding-top:10px'>
			<label>Current folder : /</label><label style='font-weight:bold' id='lbldatapath'></label>
		</td>
		<td style='text-align:right'>
			<button id='btnaction1' name='level-up' type='button' value='LevelUp' style='background:url(images/icons/arrow_redo.png) no-repeat;background-color: #E8E8E8;background-position: center center;' title='Level Up' onclick='levelup()'><span>&nbsp;</span></button>
			
			<button id='btnaction2' name='level-up' type='button' value='LevelUp' style='background:url(images/home_64.png) no-repeat;background-color: #E8E8E8;background-position: center center;background-size:16px;' title='Home' onclick='home()'><span>&nbsp;</span></button>
			
			<button id='btnaction3' name='level-up' type='button' value='LevelUp' style='background:url(images/upload-2-xxl.png) no-repeat;background-color: #E8E8E8;background-position: left center;background-size:16px;' title='Upload' onclick=\"upload('','upload',event)\"><span>Upload&nbsp;</span></button>
			
			<button id='btnaction4' name='level-up' type='button' value='LevelUp' style='background:url(images/foldo1.png) no-repeat;background-color: #E8E8E8;background-position: left center;background-size:16px;' title='New Folder' onclick=\"newfolder('','newfolder',event)\"><span>New Folder&nbsp;</span></button>

			<button id='btnaction5' name='level-up' type='button' value='LevelUp' style='background:url(images/zoom.png) no-repeat;background-color: #E8E8E8;background-position: left center;background-size:16px;' title='Open Search' onclick=\"opensearch('Search','opensearch',event)\"><span>Seach&nbsp;</span></button>
		</td>
	</tr>
</table>
<table style='width:100%;'>
	<thead>
    </thead>
    <tbody>
	<tr>
		<td valign='top' style='width:20%;'>";
		OPEN_BOX('','');
		$valplus="none";
		$str="select * from ".$dbname.".filemanager where status='0' order by formaticon desc";
		$res=fetchData($str);
		$countrb = count($res);
		if($countrb > 0)
		{
			$valplus = "none";
		}
		echo"<div style='width:100%;height:505px;overflow-y:auto' class=maincontent id='tabelexplore'>
		<ul>
			<li class='liefil'>
				<table>
					<tr>
						<td style='width:10px'>
							<img title=Expand src='images/plus.gif' style='display:".$valplus."'>
						</td>
						<td>
							<img title=Expand class=arrow src='images/rb.png' height=15px style='cursor:normal'>
						</td>
						<td>
							<label onclick=\"openrb();\" class='linklabel' title='Recycle Bin'>Recycle Bin</label>
							<input type='text' class='csttext' style='display:none'>
						</td>
					</tr>
				</table>
			</li>
		</ul>
		<!--<ul>
			<li class='liefil'>
				<table>
					<tr>
						<td style='width:10px'>
							<img title=Expand id='liplus_1' src='images/plus.gif' style='display:".$valplus."'>
						</td>
						<td>
							<img title=Expand id='imgfolder_1' class='imgfolder_1' src='images/foldc_.png' height=15px style='cursor:normal'>
						</td>
						<td>
							<label onclick=\"opendc('1')\" class='linklabel' title=Expand id='lblfolder_1'>Documents</label>
							<input type='text' id='lblfolder_1' class='csttext' style='display:none'>
						</td>
					</tr>
				</table>
				<ul id='ullevel_1' class='ullevel_1' style='display:none'>
				</ul>
			</li>
		</ul>-->
		<ul id='ulfolder_'><script>loadall()</script></ul>
		</div>";
		CLOSE_BOX();
		echo"</td>
		
		<td valign='top'>";
		OPEN_BOX('','');
		echo"<div style='width:100%;height:505px' id='divfile'>
		<table id='table-css-border-1' class='ui-widget-content' cellspacing='1' border='0' style='width:100%'>
			<thead>
			<tr class=rowheader>
				<th align=center>Name</th>
				<th align=center>Date modified</th>
				<th align=center>Type</th>
				<th align=center>Size</th>
				<th align=center>Created by</th>
				<th align=center>Update by</th>
			</tr>
			</thead>
			<tbody id='tbodyright'></tbody>
		</table>
		</div>";
		echo"<div style='width:100%;height:505px;display:none' id='divrb'>
		<table id='table-css-border-1' class='ui-widget-content' cellspacing='1' border='0' style='width:100%'>
			<thead>
			<tr class=rowheader>
				<th align=center>Name</th>
				<th align=center>Original Location</th>
				<th align=center>Date Deleted</th>
				<th align=center>Size</th>
				<th align=center>Item type</th>
				<th align=center>Date modified</th>
			</tr>
			</thead>
			<tbody id='tbodyrightrb'></tbody>
		</table>
		</div>";
		echo"<div style='width:100%;height:505px;display:none' id='divdc'>
		<table id='table-css-border-1' class='ui-widget-content' cellspacing='1' border='0' style='width:100%'>
			<thead>
			<tr class=rowheader>
				<th align=center>Name</th>
				<th align=center>Date modified</th>
				<th align=center>Type</th>
				<th align=center>Size</th>
				<th align=center>Created by</th>
				<th align=center>Update by</th>
			</tr>
			</thead>
			<tbody id='tbodyrightdc'></tbody>
		</table>
		</div>";
		CLOSE_BOX();
		echo"</td>
	</tr>
	</tbody>
	<tfoot>
	</tfoot>
	</table>";
close_body();
?>