<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('main_cektable').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/main_cektable.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/tablebrowser.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$opttable="<option value=''></option>";
$opttable.="<option value='".$dbname."'>".$dbname."</option>";
if(@$dbnamebga!=''){	
	$opttable.="<option value='".$dbnamebga."'>".$dbnamebga."</option>";
}

$optasc="<option value='asc'>asc</option>";
$optasc.="<option value='desc'>desc</option>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>Database</td>
                    <td>:</td>
                    <td style=\"width:270px;\" colspan=2><select id=db style=\"width:235px;\" onchange=showtable()>" . $opttable . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['user'] . "</td>
                    <td>:</td>
                    <td colspan=2><input type='text' class='myinputtext' style=\"width:230px;\" id='user' value=''></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['password'] . "</td>
                    <td>:</td>
                    <td colspan=2><input type='password' onblur=showtable() class='myinputtext' style=\"width:230px;\" id='pass' value=''></td>
                </tr>
				<tr>
                    <td>Table</td>
                    <td>:</td>
                    <td colspan=2><select id=tabel onchange=showkolom() style=\"width:235px;\"></select>
					<img id='tabel' onclick=z.elSearch('tabel',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
                </tr>
				<tr>
                    <td>Search 1</td>
                    <td>:</td>
                    <td><select id=field style=\"width:110px;\"></select>
					<img id='field' onclick=z.elSearch('field',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                    <td><input type=text class=myinputtext id=txtcari style=\"width:100px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Seluruhnya'></td>
                </tr>
				<tr>
                    <td>Search 2</td>
                    <td>:</td>
                    <td><select id=field2 style=\"width:110px;\"></select>
					<img id='field2' onclick=z.elSearch('field2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                    <td><input type=text class=myinputtext id=txtcari2 style=\"width:100px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Seluruhnya'></td>
                </tr>
				<tr>
                    <td>Search 3</td>
                    <td>:</td>
                    <td><select id=field3 style=\"width:110px;\"></select>
					<img id='field3' onclick=z.elSearch('field3',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                    <td><input type=text class=myinputtext id=txtcari3 style=\"width:100px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Seluruhnya'></td>
                </tr>
				<tr>
                    <td>Search 4</td>
                    <td>:</td>
                    <td><select id=field4 style=\"width:110px;\"></select>
					<img id='field4' onclick=z.elSearch('field4',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                    <td><input type=text class=myinputtext id=txtcari4 style=\"width:100px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Seluruhnya'></td>
                </tr>
				<tr>
                    <td>Search 5</td>
                    <td>:</td>
                    <td><select id=field5 style=\"width:110px;\"></select>
					<img id='field5' onclick=z.elSearch('field5',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                    <td><input type=text class=myinputtext id=txtcari5 style=\"width:100px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Seluruhnya'></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['order']." 1</td>
                    <td>:</td>
                    <td style=\"width:130px;\"><select id=order1 style=\"width:110px;\"></select>
					<img id='order1' onclick=z.elSearch('order1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
					<td><select id=orderby1 style=\"width:105px;\">".$optasc."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['order']." 2</td>
                    <td>:</td>
                    <td><select id=order2 style=\"width:110px;\"></select>
					<img id='order2' onclick=z.elSearch('order2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
					<td><select id=orderby2 style=\"width:105px;\">".$optasc."</select></td>
                </tr>
				<tr>
                    <td>Limit</td>
                    <td>:</td>
					<td colspan=2><input type=text class=myinputtext id=limit style=\"width:105px;\" onkeypress='enterkey(event,tampilkan)' placeholder='Max 1000' value=50></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button class=mybutton onclick=\"tampilkan();\">".$_SESSION['lang']['preview']."</button>
                    </td>
                </tr>
				
            </table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "
<div style=clear:both></div>

<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<div id='printContainer' style='overflow:auto;min-height:450px;width:100%'; >
</div></div>";
CLOSE_BOX();
echo close_body();
?>