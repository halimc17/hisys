function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	if (unitexp == '' || perexp == '') {
		alert('Lengkapi unit dan periode.');
		return;
	}
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printFile(param, tujuan, judul, ev);
}

function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function viewexcel(pt, tipe) {
	ev = 'event';
	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe;
	tujuan = 'lgl_slave_dokumenlegal.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function html(pt, tipe) {
	width = '';
	height = '';
	content = "<fieldset><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog1(title, content, width, height, ev);

	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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

function displayList() {
	document.getElementById('divsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}

function edit(pt) {
	document.getElementById('pt').value = pt;
	document.getElementById('pt').disabled = true;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	detail();
}

function editdetail(pt, jenis, kodeijin, noijin, tanggal, tanggalsampai, dikeluarkan, kedudukan, jenisusaha, penanggungjawab, keterangan, tgldaftarulang,tgljatuhtempo) {

	document.getElementById('pt').value = pt;
	document.getElementById('jenis').value = jenis;
	document.getElementById('jenis').disabled = true;
	document.getElementById('kodeijin').value = kodeijin;
	document.getElementById('kodeijin').disabled = true;
	document.getElementById('noijin').value = noijin;
	document.getElementById('noijin').disabled = true;
	document.getElementById('tanggal').value = tanggal;
	document.getElementById('tanggalsampai').value = tanggalsampai;
	document.getElementById('dikeluarkan').value = dikeluarkan;
	document.getElementById('kedudukan').value = kedudukan;
	document.getElementById('jenisusaha').value = jenisusaha;
	document.getElementById('penanggungjawab').value = penanggungjawab;
	document.getElementById('tgldaftarulang').value = tgldaftarulang;
	document.getElementById('tgljatuhtempo').value = tgljatuhtempo;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('method').value = 'update';
}

function deletedetail(pt, jenis, kodeijin, noijin) {
	param = 'method=deletedetail';
	param += "&jenis=" + jenis;
	param += "&pt=" + pt;
	param += "&kodeijin=" + kodeijin;
	param += "&noijin=" + noijin;
	tujuan = 'lgl_slave_dokumenlegal.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldisabled() {
	alert('Silahkan hapus detail terlebih dahulu !');
	return;
}
function del(pt) {
	param = 'method=delete' + '&pt=' + pt;
	tujuan = 'lgl_slave_dokumenlegal.php';
	if (confirm('Anda yakin ???')) {
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

function detail() {
	pt = document.getElementById('pt').value;

	if (pt == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detail';
	param += '&pt=' + pt;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail() {
	document.getElementById('pt').disabled = true;
	pt = document.getElementById('pt').value;

	param = 'method=loaddatadetail';
	param += '&pt=' + pt;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('loaddatadetail') != undefined) {
						document.getElementById('loaddatadetail').innerHTML = con.responseText;
					}
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail() {
	pt = document.getElementById('pt').value;
	jenis = document.getElementById('jenis').value;
	kodeijin = document.getElementById('kodeijin').value;
	noijin = document.getElementById('noijin').value;
	tanggal = document.getElementById('tanggal').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	dikeluarkan = document.getElementById('dikeluarkan').value;
	kedudukan = document.getElementById('kedudukan').value;
	jenisusaha = document.getElementById('jenisusaha').value;
	penanggungjawab = document.getElementById('penanggungjawab').value;
	tgldaftarulang = document.getElementById('tgldaftarulang').value;
	tgljatuhtempo = document.getElementById('tgljatuhtempo').value;

	keterangan = document.getElementById('keterangan').value;
	method = document.getElementById('method').value;

	if ((pt == '' || jenis == '' || kodeijin == '' || noijin == '' || tanggal == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}

	param = 'pt=' + pt;
	param += '&jenis=' + jenis;
	param += '&kodeijin=' + kodeijin;
	param += '&noijin=' + noijin;
	param += '&tanggal=' + tanggal;
	param += '&tanggalsampai=' + tanggalsampai;
	param += '&dikeluarkan=' + dikeluarkan;
	param += '&kedudukan=' + kedudukan;
	param += '&jenisusaha=' + jenisusaha;
	param += '&penanggungjawab=' + penanggungjawab;
	param += '&tgldaftarulang=' + tgldaftarulang;
	param += '&tgljatuhtempo=' + tgljatuhtempo;
	param += '&keterangan=' + keterangan;
	param += '&method=' + method;

	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetail();
					loaddatadetail(pt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetail() {
	document.getElementById('jenis').value = '';
	document.getElementById('jenis').disabled = false;
	document.getElementById('kodeijin').value = '';
	document.getElementById('kodeijin').disabled = false;
	document.getElementById('noijin').value = '';
	document.getElementById('noijin').disabled = false;
	document.getElementById('tanggal').value = '';
	document.getElementById('tanggalsampai').value = '';
	document.getElementById('dikeluarkan').value = '';
	document.getElementById('kedudukan').value = '';
	document.getElementById('jenisusaha').value = '';
	document.getElementById('penanggungjawab').value = '';
	document.getElementById('tgldaftarulang').value = '';
	document.getElementById('tgljatuhtempo').value = '';
	document.getElementById('keterangan').value = '';
	document.getElementById('method').value = 'insert';
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function batal(){
	document.getElementById('divsch').value='';
	document.getElementById('namaijinsrc').value='';
	document.getElementById('noijinsrc').value='';
	document.getElementById('tglsdsrc').value='';
	document.getElementById('tglakhirsrc').value='';
	document.getElementById('dikeluarkansrc').value='';
	document.getElementById('kedudukansrc').value='';
	document.getElementById('kegusahasrc').value='';
	document.getElementById('tggjwbsrc').value='';
	document.getElementById('ketsrc').value='';
	loaddata(0);
}


function loaddata(page) {
	divsch = document.getElementById('divsch').value;
	namaijinsrc = document.getElementById('namaijinsrc').value;
	noijinsrc = document.getElementById('noijinsrc').value;
	tglsdsrc = document.getElementById('tglsdsrc').value;
	tglakhirsrc = document.getElementById('tglakhirsrc').value;
	dikeluarkansrc = document.getElementById('dikeluarkansrc').value;
	kedudukansrc = document.getElementById('kedudukansrc').value;
	kegusahasrc = document.getElementById('kegusahasrc').value;
	tggjwbsrc = document.getElementById('tggjwbsrc').value;
	ketsrc = document.getElementById('ketsrc').value;

	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	param += '&namaijinsrc=' + namaijinsrc;
	param += '&noijinsrc=' + noijinsrc;
	param += '&tglsdsrc=' + tglsdsrc;
	param += '&tglakhirsrc=' + tglakhirsrc;
	param += '&dikeluarkansrc=' + dikeluarkansrc;
	param += '&kedudukansrc=' + kedudukansrc;
	param += '&kegusahasrc=' + kegusahasrc;
	param += '&tggjwbsrc=' + tggjwbsrc;
	param += '&ketsrc=' + ketsrc;

	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('pt').disabled = false;
	document.getElementById('pt').value = '';
}

function getjenispt() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&method=getjenispt';

	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jenis').value = trim(con.responseText);
					// if(trim(con.responseText)!=''){
					// document.getElementById('jenis').disabled=true;
					// }else{
					// document.getElementById('jenis').disabled=false;
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}

function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_dokumenlegal.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, pt, jenis, kodeijin, noijin) {
	showformupload(ev);
	param = "";
	param += "pt=" + pt;
	param += "&kodeijin=" + kodeijin;
	param += "&noijin=" + noijin;
	param += "&jenis=" + jenis;
	param += '&method=showupload';
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(pt, jenis, kodeijin, noijin);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var pt = document.getElementById('ptupload').innerHTML;
	var jenis = document.getElementById('xxx').innerHTML;
	var kodeijin = document.getElementById('yyy').innerHTML;
	var noijin = document.getElementById('iii').innerHTML;
	var formdata = new FormData();
	formdata.append("kodeijin", kodeijin);
	formdata.append("noijin", noijin);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("pt", pt);
	formdata.append("jenis", jenis);

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_dokumenlegal.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(pt, jenis, kodeijin, noijin);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewlistfile(pt, jenis, kodeijin, noijin) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog4(title, content, width, height, ev);

	param = 'method=viewlistfile&jenis=' + jenis + '&pt=' + pt + '&kodeijin=' + kodeijin + '&noijin=' + noijin;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(pt, jenis, kodeijin, noijin);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(pt, jenis, kodeijin, noijin) {
	param = 'method=loadfiles&jenis=' + jenis + '&pt=' + pt + '&kodeijin=' + kodeijin + '&noijin=' + noijin;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(jenis, pt, kodeijin, noijin, namafile) {
	param = "method=deletefile";
	param += "&jenis=" + jenis;
	param += "&pt=" + pt;
	param += "&kodeijin=" + kodeijin;
	param += "&noijin=" + noijin;
	param += "&namafile=" + namafile;

	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(pt, jenis, kodeijin, noijin);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefileall(jenis, pt, xxx, yyy, iii) {
	param = 'method=deletefileall&notransaksi=' + notransaksi;
	param += "&jenisupload=" + jenis;
	param += "&pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&namafile=" + namafile;
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('File sudah di hapus');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnama() {
	jenis = trim(document.getElementById('jenis').value);

	param = 'jenis=' + jenis + '&method=getnama';
	tujuan = 'lgl_slave_dokumenlegal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kodeijin').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}