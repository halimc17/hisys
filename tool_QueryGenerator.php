<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/admin_validation.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/tool_QueryGenerator.js?v=<?php echo time(); ?>'></script>
<style>
.rounded5{
    border-style: solid;
    border-width: 2px;
    border-color:#c1c1c1;
	border-radius: 15px;
    background: #dedede;
    padding: 5px;
	cursor:pointer;
}
.myButtonR{
	  color: #14396A !important;
	  font-size: 11px;
	  font-weight:bold;
	  padding: 1px 12px;
	  -moz-border-radius: 8px;
	  -webkit-border-radius: 8px;
	  border-radius: 8px;
	  border: 1px solid #5B7574;
	  background: #BAEBEE;
	  background: linear-gradient(top,  #E1F2EE,  #CECFCB);
	  background: -ms-linear-gradient(top,  #E1F2EE,  #CECFCB);
	  background: -webkit-gradient(linear, left top, left bottom, from(#E1F2EE), to(#CECFCB));
	  background: -moz-linear-gradient(top,  #E1F2EE,  #CECFCB);
}
.myButtonR:hover{
	  color: #6A6305 !important;
	  background: #468CCF;
	  background: linear-gradient(top,  #CFC5CA,  #43D7EE);
	  background: -ms-linear-gradient(top,  #CFC5CA,  #43D7EE);
	  background: -webkit-gradient(linear, left top, left bottom, from(#CFC5CA), to(#43D7EE));
	  background: -moz-linear-gradient(top,  #CFC5CA,  #43D7EE);
	}
.myButton1{
    border-style: solid;
    border-width: 2px;
    border-color:#FFFF55;
	border-radius: 8px;
    background: #3B8F3A;
    padding: 4px;
	cursor:pointer;
	color:#FFFFFF;
	font-size:11px;
}

.myButton2{
	border-style: solid;
    border-width: 2px;
    border-color:#FFFF55;
	border-radius: 8px;
    background: #3D689F;
    padding: 4px;
	cursor:pointer;
	color:#FFFFFF;
	font-size:11px;
}
.myButton3{
	border-style: solid;
    border-width: 2px;
    border-color:#FFFF55;
	border-radius: 8px;
    background: #724F8C;
    padding: 4px;
	cursor:pointer;
	color:#FFFFFF;
	font-size:11px;
}
.myButton4{
	border-style: solid;
    border-width: 2px;
    border-color:#FFFF55;
	border-radius: 8px;
    background: #9E3016;
    padding: 4px;
	cursor:pointer;
	color:#FFFFFF;
	font-size:11px;
}

.columnList{
	width:700px;
	height:75px;
	padding:10px;
	overflow:auto;
	background-color:#DEDEDE;
	text-align:left;
	z-index:2700;
}
.columnCaption{
  background-color:#ffffff;
  padding:2px;
  color:#000000;
  width:717px;
  overflow:auto;
}
</style>
<?
OPEN_BOX('','<span class=judul>'.getMenu('tool_QueryGenerator').'</span><br>');
echo OPEN_THEME("QUERY GENERATOR:");
$str="show tables from ".$dbname;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_NUM);
		
$tablelist="<select id=tableList1 onchange=getThisField(this.options[this.selectedIndex].value,'table1');><option value=''>Choose table</option>
";
while ($bar = $res->fetch()) {
	if($bar[0]=='user' || $bar[0]=='admin_list'|| $bar[0]=='auth' || $bar[0]=='masetr_lisensi'|| $bar[0]=='menu'){
		continue;
	}
	$tablelist.="<option value='".$bar[0]."'>".$bar[0]."</optiong>";
}
$tablelist.="</select>";

	//<img id="drag1" src="images/OWL_OV.png" draggable="true" ondragstart="drag(event)" width="60" height="69">
	//<img id="drag2" src="images/OWL_OV.png" draggable="true" ondragstart="drag(event)" width="60" height="69">
    echo '<table id=myTable><tr><td valign=top>
	<div class=rounded5> From table : '.$tablelist.'</div>
	</td>
	<td>
	Fields:<a href=# onclick=showById("table1"); title="Maximize">+</a>/<a href=# onclick=hideById("table1"); title="Minimize">-</a><div id=table1 class=rounded5 ondrop="drop(event);generateParameter();" ondragover="allowDrop(event)"></div>
	</td>
	</tr>
	</table>
	<span class=myButtonR id=btReset onclick=reset()>Reset / New</span>
	<span class=myButtonR id=btNew onclick=addNewRow()>Add Join</span>
	<span class=myButtonR id=btConfig onclick=configureColumn()>Column Collector</span>
	<hr/>
	<div id=columnControl class=drag style=display:none>
		<font style="color:#000000;font-size:14px;font-weight:bold;">Column collector (Dragable)</font> :<br>
		<div id="columnList" class=columnList ondrop="drop(event);generateParameter();" ondragover="allowDrop(event)"></div>
		<div style="text-align:left;background-color:#2B4462;color:#ffffff"><br>
			<font style=font-weight:bold>Report Title : </font>
			<input type=text  style=width:638px id=judul onkeypress="return tanpa_kutip(event);" value="Report Title"><br>
			<div style=display:none>Caption on Display : <div id="caption" class=columnCaption></div></div><br>
		</div>	
		<div style=background-color:#B2BABB;>	
			<br><font style=font-weight:bold;color:#000>Parameter and Conditions : </font><br>
			<div id="condition" class=columnCaption></div>	
		</div>
		
		<div style="text-align:right;">
				<button class=myButtonR onclick=previewQuery(event) title="Test configuration on query">Preview</button>
				<button class=myButtonR onclick=configureColumn();>Reset</button>	
				<button class=myButtonR onclick=saveConfig(event,"save"); title="Save Configuration">Save</button>
				<button class=myButtonR onclick=hideById("columnControl"); title="Hide display">Close</button>
		</div>
		
	</div>';
echo CLOSE_THEME();
echo "<div style=clear:both></div>";
echo '<fieldset><legend>List</legend>';
echo "<table class=sortable cellspacing=1 cellpadding=5 border=0>
	  <thead>
			<tr class=rowheader>
				<td style=align:center>No</td>
				<td style=align:center>Report Title</td>
				<td style=align:center>Create Date</td>
				<td style=align:center>Designer</td>
				<td style=align:center>html</td>
				<td style=align:center>excel</td>
				<td style=align:center>pdf</td>				
				<td style=align:center>Status</td>
				<td style=align:center>Asign<br>User</td>
				<td style=align:center>Browse</td>
			</tr>
		</thead>
		<tbody>";
	$tab='';
	$capStatus='';
	$str="select * from ".$dbname.".tool_userdefinedreport order by rnumber";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if($bar->status=='0'){
			$capStatus='Not Published';
		}else if($bar->status=='1'){
			$capStatus='Active';
		}else{
			$capStatus='Deleted';
		}
		$user='';
		if($bar->owner==$_SESSION['standard']['username']){
			$user="<img src='images/orgicon.png' style='cursor:pointer;' class=zImgBtn title='Assign User Access' onclick=userOf(event,'".$bar->rnumber."')>";
			$status="<select id=statusR".$bar->rnumber." onchange=change(this,'status',this.options[this.selectedIndex].value,'".$bar->rnumber."')>
						<option value='".$bar->status."'>".$capStatus."</option>
						<option value='0'>Not Published</option>
						<option value='1'>Active</option>
						<option value='2'>Delete</option>
					</select>";
			#html
			$click='';
			if($bar->html=='1'){
				$click='checked=true';
			}
			$html="<input type=checkbox id=html".$bar->rnumber." ".$click." onclick=change(this,'html','','".$bar->rnumber."')>";
			#excel
			$click='';
			if($bar->speadsheet=='1'){
				$click='checked=true';
			}
			$excel="<input type=checkbox id=xls".$bar->rnumber." ".$click." onclick=change(this,'speadsheet','','".$bar->rnumber."')>";
			#pdf
			$click='';			
			if($bar->pdf=='1'){
				$click='checked=true';
			}
			$pdf="<input type=checkbox id=pdf".$bar->rnumber." ".$click." onclick=change(this,'pdf','','".$bar->rnumber."')>";
			
		}else{
			$status=$capStatus;
			if($bar->html=='1'){
				$html='Yes';
			}
			else{
				$html='No';
			}
			if($bar->speadsheet=='1'){
				$excel='Yes';
			}else{
				$excel='No';
			}
			if($bar->pdf=='1'){
				$pdf='Yes';
			}else{
				$pdf='No';
			}
		}
		$tab.="<tr class=rowcontent>
				<td align=center>".$bar->rnumber."</td>
				<td>".$bar->namalaporan."</td>
				<td>".tanggalnormal($bar->createdate)."</td>
				<td>".$bar->owner."</td>
				<td align=center>".$html."</td>
				<td align=center>".$excel."</td>
				<td align=center>".$pdf."</td>
				<td>".$status."</td>
				<td align=center>".$user."</td>
				<td align=center><img src='images/skyblue/zoom.png' class=zImgBtn style='cursor:pointer;' onclick=browseR(event,'".$bar->rnumber."') title='Try Report'></td>
			   </tr>";	
	}
	echo $tab;
echo "</tbody><tfoot></tfoot></table></legend>";
CLOSE_BOX();
echo close_body();	
?>