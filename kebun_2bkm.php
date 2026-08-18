<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function changediv(unit) {
	param = 'unit='+unit.value;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('kebun_slave_2rekappnnblok_detail.php?proses=changediv', param, respon);
}
function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}


function preview() {
	kodeorg = trim(document.getElementById('kdorg').value);
	divisi  = trim(document.getElementById('divisi').value);
	tgl1    = trim(document.getElementById('tgl1').value);
	tgl2    = trim(document.getElementById('tgl2').value);

    param = 'method=preview';
    param += '&kodeorg=' + kodeorg;
    param += '&divisi=' + divisi;
    param += '&tgl1=' + tgl1;
    param += '&tgl2=' + tgl2;
    tujuan = 'kebun_slave_2bkm.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
					document.getElementById('filterlap').style.display='none';
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							ordering: false,
							fixedHeader: true,
							paging: true,
							"iDisplayLength": 10,
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: [{
									text: 'Show',
									action: function () {
										newdata();
									}
								},'csv', 'excel', 'print'
							]
						});
					} );
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function newdata(){
	e = document.getElementById('filterlap');
	if(e.style.display=='block'){
		e.style.display='none';
	}else{
		e.style.display='block';
	}
}

function showupload(notransaksi){
	param='method=loadfiles&notransaksi='+notransaksi;
	
	tujuan='kebun_slave_2bkm.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
					alertify.popuppdf("File",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('30%','40%');
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function detailData(notransaksi,numRow,ev,tipe,jenis){
    param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
	title="Data Detail";
	if(tipe=='BKM'){		
		alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}else{
		alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detail_panen.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}
}
</script>

<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2bkm').'</span><br>');

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}

$optDiv="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
	$i="";
	if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
		$i="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$i.">".$bar['namaorganisasi']."</option>";
}


$arr = "##kdorg##tgl1##tgl2##divisi";
echo"<div id=filterlap style=display:block;><fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:175px;\" onchange='changediv(this)'>" . $optOrg . "</select></td>
                </tr>
				<tr style=display:none>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi style=\"width:175px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'  readonly>
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'  readonly></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview('".$arr."') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset></div>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='output' style='min-height:400px;max-width:100%'; ></div>";
CLOSE_BOX();
echo close_body();
?>