<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/formReport.php');

echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src=js/zMaster.js></script> 
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript>
function lempar(dest, title) {
	param = 'judul=' + title;
	tujuan = dest + '.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('formcontainer').innerHTML = con.responseText;
					document.getElementById('reportcontainer').innerHTML = '';
					document.getElementById('isiJdlBawah').innerHTML = title;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ubah(obj) {
	if (obj.style.backgroundColor == 'darkgreen') {
		obj.style.backgroundColor = '#FFFFFF';
		obj.style.color = '#000000';
		obj.style.fontWeight = 'normal';
	} else {
		obj.style.backgroundColor = 'darkgreen';
		obj.style.color = '#FFFFFF';
		obj.style.fontWeight = 'bolder';
	}
}
function getAfd(obj) {
	unt = obj.options[obj.selectedIndex].value;
	param = 'unit=' + unt;
	//alert(param);
	tujuan = 'lbm_slave_sampul.php';
	post_response_text(tujuan + '?proses=getAfdl', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('afdId').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function lihatDetail(kodept, periode, namakendaraan, gaji, lembur, bbm, sukucadang, reparasi, asuransi, pajak, penyusutan, hmkm, ev) {
	param = 'kodept=' + kodept + '&periode=' + periode + '&namakendaraan=' + namakendaraan + '&gaji=' + gaji;
	param += '&lembur=' + lembur + '&bbm=' + bbm + '&sukucadang=' + sukucadang + '&reparasi=' + reparasi;
	param += '&asuransi=' + asuransi + '&pajak=' + pajak + '&penyusutan=' + penyusutan + '&hmkm=' + hmkm;
	tujuan = 'lbm_slave_transit_kendaraan_detail.php' + "?" + param;
	width = '700';
	height = '400';

	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1('Detail Biaya Alokasi Kendaraan', content, width, height, ev);
}

function detailKeExcel(ev, tujuan) {
	width = '700';
	height = '400';

	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1('Detail Biaya Alokasi Kendaraan', content, width, height, ev);
}

function details(id, child, parent) {
	for (i = 1; i < child + 1; i++) {
		var row = document.getElementById('child_' + id + '_' + i);
		if (row !== null) {
			if (row.style.display == '') {
				row.style.display = 'none';
			} else {
				row.style.display = '';
			}
		}
	}

	var count = 0;
	for (j = 1; j < parent + 1; j++) {
		for (i = 1; i < child + 1; i++) {
			var row = document.getElementById('child_' + j + '_' + i);
			if (row !== null) {
				if (row.style.display == '') {
					count = count + 1;
				}
			}
		}
	}

	if (count == 0) {
		document.getElementById('titAfd').style.display = "none";
		document.getElementById('titBlok').style.display = "none";
		document.getElementById('totRows').colSpan = 2;

		for (j = 1; j < parent + 1; j++) {
			var row = document.getElementById('bodyAfd' + j);
			if (row !== null) {
				row.style.display = 'none';
			}
		}
	} else {
		document.getElementById('titAfd').style.display = "";
		document.getElementById('titBlok').style.display = "";
		document.getElementById('totRows').colSpan = 4;

		for (j = 1; j < parent + 1; j++) {
			var row = document.getElementById('bodyAfd' + j);
			if (row !== null) {
				row.style.display = '';
			}
		}
	}
}

function detailrow(nourut, totalkeg) {
	for (i = 1; i <= totalkeg; i++) {
		var row = document.getElementById('listkegiatan' + nourut + i);
		if (row !== null) {
			if (row.style.display == '') {
				row.style.display = 'none';
			} else {
				row.style.display = '';
			}
		}
	}
}

function detailrowlima(nourut, totalkeg) {
	for (i = 1; i <= totalkeg; i++) {
		var row = document.getElementById('listakunlina' + nourut + i);
		if (row !== null) {
			if (row.style.display == '') {
				row.style.display = 'none';
			} else {
				row.style.display = '';
			}
		}
	}
}


function detailcomment(file, unit, per, afd, val, tipe, ev) {

	per = document.getElementById('periode').value;
	unit = document.getElementById('unit').value;
	afd = document.getElementById('afdId').value;
	param = 'method=detailcomment' + '&file=' + file + '&unit=' + unit + '&per=' + per + '&afd=' + afd + '&val=' + val + '&tipe=' + tipe;
	title = "Data Detail";
	showDialog2(title, "<iframe frameborder=0 style='width:795px;height:395px'" +
		" src='lbm_slave_comment.php?" + param + "'></iframe>", '800', '400', ev);
	var dialog = document.getElementById('dynamic2');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function isifile(doc, ev) {
	param = 'method=isifile' + '&doc=' + doc;
	title = "Data Detail";
	showDialog4(title, "<iframe frameborder=0 style='width:795px;height:395px'" +
		" src='lbm_slave_comment.php?" + param + "'></iframe>", '800', '400', ev);
	var dialog = document.getElementById('dynamic4');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

/*
function comment(file,unit,per,afd,val,ev){
if(afd==''){
afd='afd';
}
content = "<div>";
content += "<fieldset>text :<input type=text id=text class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25>";
content += "<button class=mybutton onclick=savecomment("+file+","+unit+","+per+","+afd+","+val+")>Find</button>";
content += " </fieldset>";
content += "<div id=listsupspk style=\"height:270px;width:500px;overflow:scroll;\"></div></div>";
title = ' Kontraktor :';
width = '510';
height = '310';
showDialog2(title, content, width, height, ev);
}
 */

function showpopup(file, unit, per, afd, val, no,isi) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
	param = 'method=showpopup' + '&file=' + file + '&unit=' + unit + '&per=' + per + '&afd=' + afd + '&val=' + val+ '&no=' + no+ '&isi=' + isi;
	tujuan = 'lbm_slave_comment.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('contviewx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function savecomment(file, unit, per, afd, val, no) {
	text = document.getElementById('textx' + no).value;
	if(text==''){
		alert("Penjelasan tidak boleh kosong !!!"); return;
	}
	param = 'method=savecomment' + '&file=' + file + '&unit=' + unit + '&per=' + per + '&afd=' + afd + '&val=' + val + '&text=' + text;
	tujuan = 'lbm_slave_comment.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					alert(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deletecomment(file, unit, per, afd, val, tipe, ev) {

	param = 'method=deletecomment' + '&file=' + file + '&unit=' + unit + '&per=' + per + '&afd=' + afd + '&val=' + val;
	tujuan = 'lbm_slave_comment.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					alert(con.responseText);
					detailcomment(file, unit, per, afd, val, tipe, ev);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(file, unit, per, afd, val, doc, tipe, ev) {
	param = 'method=deletefile' + '&file=' + file + '&unit=' + unit + '&per=' + per + '&afd=' + afd + '&val=' + val + '&doc=' + doc
		tujuan = 'lbm_slave_comment.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					alert(con.responseText);
					detailcomment(file, unit, per, afd, val, tipe, ev);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savefile(file, unit, per, afd, val, no) {
	var fileup = document.getElementById("fileuploadx" + no).files[0];
	var formdata = new FormData();

	formdata.append("fileup", fileup);
	formdata.append("file", file);
	formdata.append("unit", unit);
	formdata.append("per", per);
	formdata.append("afd", afd);
	formdata.append("val", val);
	formdata.append("fileupload", getValue('fileuploadx'+ no));
	var con = createXMLHttpRequest();
	con.open("POST", "lbm_slave_comment.php?method=savefile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Uploaded');
					alert(con.responseText);
					document.getElementById('fileupload' + no).value = '';
					detailcomment(file, unit, per, afd, val, tipe, ev);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function excel2(ev, per, unit, div, noakun, tipe) {
	param = 'method=excel2';
	param += '&per=' + per + '&unit=' + unit + '&div=' + div + '&noakun=' + noakun + '&tipe=' + tipe;
	tujuan = 'lbm_detail_lv2.php';
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}

function html2(noakun, tipe) {
	per = document.getElementById('periode').value;
	unit = document.getElementById('unit').value;
	div = document.getElementById('afdId').value;
	param = 'method=html2';
	param += '&per=' + per + '&unit=' + unit + '&div=' + div + '&noakun=' + noakun + '&tipe=' + tipe;
	tujuan = 'lbm_detail_lv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('reportcontainer').style.display = 'none';
					document.getElementById('html2').style.display = 'block';
					document.getElementById('html2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function kehtml1() {
	document.getElementById('reportcontainer').style.display = 'block';
	document.getElementById('html2').style.display = 'none';
}
</script>
<script src="js/formReport.js"></script>
<script src="js/biReport.js"></script>
<script src="js/kebun_2accreport.js"></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?
//echo "qwe";
//exit;

echo"<table>
     <thead>
     </thead>
        <tbody>
        <tr>
            <td valign='top'>";
            OPEN_BOX('','LBM');
            echo"<fieldset><legend>".$_SESSION['lang']['navigasi']."</legend>
                 <div id='navcontainer' style='width:200px;height:740px;overflow:auto;background-color:#FFFFFF;'>";
                if($_SESSION['language']=='ID'){
                  $x=readCountry('config/lbm.lst');
                }
                else{
                   $x=readCountry('config/lbm_en.lst'); 
                }
                foreach($x as $bar=>$val)
                 {                    
                     echo "<a onmouseover=ubah(this) onmouseout=ubah(this) style='font-size:10px;cursor:pointer;' onclick=\"lempar('".$val[1]."','".$val[2]."');\" title='".$val[2]."'>".$val[0]."</a><br>";               
                 }
                echo"</div>
                    </fieldset>";
            CLOSE_BOX();   
            
        echo"</td><td width=100%>";
            OPEN_BOX('','');
            echo"<fieldset><legend>".$_SESSION['lang']['form']."</legend>
                 <div id='formcontainer' style='width:100%;height:150px;overflow:auto'></div> 
                 </fieldset>";            
            CLOSE_BOX();  
            OPEN_BOX('','');
			
            echo"<fieldset><legend>".$_SESSION['lang']['list']." <span id=isiJdlBawah></span></legend>
                <div id='reportcontainer' style='width:100%;height:550px;overflow:auto;background-color:#FFFFFF;'></div> 
				<div id='html2' style='width:100%;height:550px;overflow:auto;background-color:#FFFFFF;display:none'></div>
				<div id='html3' style='width:100%;height:550px;overflow:auto;background-color:#FFFFFF;display:none'></div>
				  
                 </fieldset>";            
            CLOSE_BOX();              
        echo"</td>
        </tr>
        </tbody>
     <tfoot>
     </tfoot>
     </table>";
echo close_body();
?>