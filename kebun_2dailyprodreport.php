<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2dailyprodreport').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script>
function preview() {
	unit= document.getElementById('unit').value;
	tanggal= document.getElementById('tanggal').value;
	
	validate([
        ["unit","Regional tidak boleh kosong."],
        ["tanggal","Tanggal tidak boleh kosong."]
    ]);
	
	param  = '';
	param += '&unit=' + unit;
	param += '&tanggal=' + tanggal;
	param += '&method=preview';
	
	tujuan = 'kebun_slave_2dailyprodreport.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById("printContainer").innerHTML=con.responseText;
					document.getElementById("tombolkirim").style.display='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function kirim(sumber) {
	unit= document.getElementById('unit').value;
	tanggal= document.getElementById('tanggal').value;
	
	validate([
        ["unit","Regional tidak boleh kosong."],
        ["tanggal","Tanggal tidak boleh kosong."]
    ]);
	
	param  = '';
	param += '&unit=' + unit;
	param += '&tanggal=' + tanggal;
	param += '&sumber=' + sumber;
	param += '&method=kirim';
	
	alertify.popup("Kirim ???","<div style='align:center'><label>Kirim laporan (.pdf) melalui :<br></label><button style='height:30px;width:150px;' class=mybutton onclick=kirim('telegram')>Telegram</button><button style='height:30px;width:150px;' class=mybutton onclick=kirim('email')>E-Mail</button></div>").set({'resizable':false,'maximizable':false}); 
	if(sumber!=undefined){
		alertify.popup().destroy();
		tujuan = 'kebun_slave_2dailyprodreport.php';
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						alertify.popup2(sumber,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('800px','80%'); 
						listcari();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}

function listcari() {
	unit   = document.getElementById('unit').value;
	tanggal= document.getElementById('tanggal').value;
	nama   = document.getElementById('nama').value;
	lokasi = document.getElementById('lokasi').value;
	jabatan= document.getElementById('jabatan').value;
	sumber = document.getElementById('sumber').value;
	
	
	param  = '';
	param += '&unit=' + unit;
	param += '&tanggal=' + tanggal;
	param += '&nama=' + nama;
	param += '&lokasi=' + lokasi;
	param += '&jabatan=' + jabatan;
	param += '&sumber=' + sumber;
	param += '&method=listcari';
	
	tujuan = 'kebun_slave_2dailyprodreport.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById("listcari").innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clickall(){
	e = document.getElementsByName("check[]");
	h = document.getElementById('checkall');
	for(i=0;i<e.length;i++){
		if(h.checked==true){
			e[i].checked=true;
		}else{
			e[i].checked=false;
		}
	}
}

function kirimkan(){
	i = document.getElementsByName("mail[]");
	e = document.getElementsByName("check[]");
	param="";
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){			
			param+="&email["+n+"]="+i[n].innerHTML;
		}
	}
	if(param==""){		
		alertify.alert("Silahkan check terlebih dahulu"); return;
	}
	unit   = document.getElementById('unit').value;
	tanggal= document.getElementById('tanggal').value;
	sumber = document.getElementById('sumber').value;
	
	validate([
        ["unit","Regional tidak boleh kosong."],
        ["tanggal","Tanggal tidak boleh kosong."]
    ]);
	
	param += '&unit=' + unit;
	param += '&tanggal=' + tanggal;
	param += '&method=preview';
	param += '&sumber='+ sumber;
	
	tujuan = 'kebun_slave_2dailyprodreport.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById("printContainer").innerHTML=con.responseText;
					alertify.alert(sumber+" sudah dikirimkan.");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

</script>

<?

//$optreg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct subregional from ".$dbname.".bgt_regional_assignment where subregional not in ('JAKARTA','PONTIANAK')";
$res=fetchdata($str);
foreach($res as $bar){
    $optreg.="<option value=" . $bar['subregional'] . ">" . $bar['subregional'] . "</option>";
}
$optreg.="<option value='KSP-GROUP'>KSP-GROUP</option>";


$arr1 = "##unit##tanggal";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['regional'] . "</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:164px;\">" .$optreg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
					<td><input type=text class=myinputtext style='width:160px;' id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 value=".date("d-m-Y")." readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview() class=mybutton>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=kirim() id=tombolkirim style=display:none; class=mybutton>" . $_SESSION['lang']['kirim'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=printContainer style='width:100%;height:500px'></div>";
CLOSE_BOX();
echo close_body();
?>