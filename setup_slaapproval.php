<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('setup_slaapproval').'</span><br>');
?>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
	$(this).closest(".select2-container").siblings('select:enabled').select2('open');
});

function showEdit(id,jenisapproval,dariuser,keuser,hari,status){
	document.getElementById('id').value=id;	
	setValue2('jenis',jenisapproval);
	setValue2('dariuser',dariuser);
	setValue2('keuser',keuser);
	setValue2('hari',hari);
	setValue2('status',status);
}
function del(id){
	param  = 'proses=delete';
    param += "&id=" + id;
	if(confirm("Anda yakin ???")){		
		tujuan = 'setup_slave_slaapproval.php';
		post_response_text(tujuan, param, respog);
	}
	

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	id      = document.getElementById('id').value; 
	jenis   = document.getElementById('jenis').value; 
	status  = document.getElementById('status').value; 
	dariuser= document.getElementById('dariuser').value; 
	keuser  = document.getElementById('keuser').value; 
	hari    = document.getElementById('hari').value; 
	
	param  = 'proses=simpan';
    param += "&id=" + id;
    param += "&jenis=" + jenis;
    param += "&status=" + status;
    param += "&dariuser=" + dariuser;
    param += "&keuser=" + keuser;
    param += "&hari=" + hari;
    tujuan = 'setup_slave_slaapproval.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('id').value='';	
					loaddata();
					//location.reload();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loaddata(){
	param  = 'proses=loaddata';
    tujuan = 'setup_slave_slaapproval.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
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

$jenisapprov="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".setup_jenisapproval where status='1' and jenis in ('PR','PO')";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['jenis']!='PR'){
		//$jenisapprov.="<option value='".$bar['jenis']."' disabled>".$bar['jenis']." - ".$bar['nama']."</option>";
	}else{		
		// $jenisapprov.="<option value='".$bar['jenis']."'>".$bar['jenis']." - ".$bar['nama']."</option>";
	}
	$jenisapprov.="<option value='".$bar['jenis']."'>".$bar['jenis']." - ".$bar['nama']."</option>";
}

$nama="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan ='PR' and level >= '1' order by level desc";
$res=fetchData($str);
foreach($res as $bar){
	$nama.="<option value='".$bar['karyawanid']."'>".getNamaKaryawan($bar['karyawanid'])."</option>";
}

echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>Jenis Approval</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=jenis style=min-width:150px>".$jenisapprov."</select></td>
                </tr>
				<tr>
                    <td>Dari User</td>
                    <td>:</td>
                    <td colspan=2><select class=select2 id=dariuser style=min-width:150px>".$nama."</select></td>
                </tr>
				<tr>
                    <td>Ke User</td>
                    <td>:</td>
                     <td colspan=2><select class=select2 id=keuser style=min-width:150px>".$nama."</select></td>
                </tr>
				<tr>
                    <td>Outstanding (Hari)</td>
                    <td>:</td>
                    <td colspan=2><input style=width:95px id=hari class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"></td>
                </tr>
				<tr>
                    <td>Status</td>
                    <td>:</td>
                    <td colspan=2><select style=width:100px class=select2 id=status >
							<option value='1'>Aktif</option>
							<option value='0'>Non Aktif</option>
						</select>
					</td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
					<input hidden id=id>
                    <button onclick=simpan(); class=mybutton>" . $_SESSION['lang']['save'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"
	<div id='printContainer'><script>loaddata();</script></div>
";
CLOSE_BOX();
echo close_body();
?>