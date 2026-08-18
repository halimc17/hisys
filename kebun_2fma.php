<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2fma').'</span><br>');
?>
<script language="javascript" src="js/zComment.js?ver=<?php echo time(); ?>"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type=text/css href=style/zComment.css>
<script>
	function getdetail(iddesc,head,region,prd){
		param = '';
		param += '&iddesc=' + iddesc;
		param += "&head=" + head;
		param += "&region=" + region;
		param += "&prd=" + prd;
		
		tujuan = 'kebun_slave_2fmapopup.php';
		post_response_text(tujuan, param, respog);

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
	}
	function showheader(){
		if(document.getElementById('tableheader').style.display=="none"){		
			document.getElementById('tableheader').style.display="block";
			document.getElementById('showhead').innerHTML="Hide Filter";
			document.getElementById('tombolexport').style.display="none";
		}else{
			document.getElementById('tableheader').style.display="none";
			document.getElementById('tombolexport').style.display="block";
			document.getElementById('showhead').innerHTML="Show Filter";
		}	
	}
</script>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".bgt_regional_assignment";
$res = fetchdata($str);
foreach($res as $bar){
	$myregional="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeunit']){
		$myregional=$bar['subregional'];
	}
	if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN'){		
		$datareg[$bar['subregional']]=$bar['subregional'];
	}
}
foreach($datareg as $region){
	$s="";
	if($myregional==$region){
		$s="selected";
	}
    $optPT.="<option value=" . $region . " ".$s.">".$region."</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$d=substr($bar['periode'],0,4);
	if($d!=$n){			
		$optper.="<optgroup label='".$d."'>";
	}
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
	$n=$d;
	if($d!=$n){			
		$optper.="</optgroup>";
	}
}


$arr1 = "##region##prd##penyusutan";
echo"<div id=tableheader>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['regional'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=region style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td>Depresiasi</td>
                    <td>:</td>
                    <td><select class=select2 id=penyusutan style=\"width:164px;\"><option value='0'>Exclude</option><option value='1'>Include</option></select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2fma','" . $arr1 . "','printContainer');showheader(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2fma.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";
echo"</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' style=height:73vh class='table-scroll'></div>";
echo"<div id='getdetail' style=display:none></div>";
CLOSE_BOX();
echo close_body();
?>