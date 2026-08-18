function getkebunscr() {
	kodept3 = document.getElementById('ptscr');
	kodept3 = kodept3.options[kodept3.selectedIndex].value;

	param = 'proses=getkebun3&kodept=' + kodept3 + '&status=cari';
	tujuan = 'umm_slave_uploadpeta.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unitscr').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function getkebun3() {
	kodept3 = document.getElementById('kodept3');
	kodept3 = kodept3.options[kodept3.selectedIndex].value;

	param = 'proses=getkebun3&kodept=' + kodept3;
	tujuan = 'umm_slave_uploadpeta.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kebun3').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function batal() {
	document.getElementById('id').value = "";
	document.getElementById('namapeta').value = "";
	document.getElementById("upload").value = "";
	document.getElementById("method").value = "insert";
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	ptscr = document.getElementById('ptscr').value;
	unitscr = document.getElementById('unitscr').value;
	tipepetascr = document.getElementById('tipepetascr').value;
	statusscr = document.getElementById('statusscr').value;
	namapetascr = document.getElementById('namapetascr').value;
	namafilescr = document.getElementById('namafilescr').value;
	revisiscr = document.getElementById('revisiscr').value;
	tglscr = document.getElementById('tglscr').value;
	
	param = "page=" + page;
	param += "&ptscr=" + ptscr;
	param += "&unitscr=" + unitscr;
	param += "&tipepetascr=" + tipepetascr;
	param += "&statusscr=" + statusscr;
	param += "&namapetascr=" + namapetascr;
	param += "&namafilescr=" + namafilescr;
	param += "&revisiscr=" + revisiscr;
	param += "&tglscr=" + tglscr;
	post_response_text('umm_slave_uploadpeta.php?proses=loaddata', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					valSplit = con.responseText.split("####");
					document.getElementById('container').innerHTML = valSplit[0];
					document.getElementById('footData').innerHTML = valSplit[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function aktif(id,stts) {
	pg = document.getElementById('pages').value;
	page = parseFloat(pg) - 1;
	param = "id=" + id;
	param += "&status=" + stts;
	post_response_text('umm_slave_uploadpeta.php?proses=aktif', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(id) {
	param = "id=" + id;
	post_response_text('umm_slave_uploadpeta.php?proses=del', param, respon);
	function respon() {
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

function batalscr(){
	document.getElementById('ptscr').value='';
	document.getElementById('unitscr').value='';
	document.getElementById('tipepetascr').value='';
	document.getElementById('statusscr').value='';
	document.getElementById('namapetascr').value='';
	document.getElementById('namafilescr').value='';
	document.getElementById('revisiscr').value='';
	document.getElementById('tglscr').value='';
}

function simpan() {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload').value);
	formdata.append("kodept", document.getElementById('kodept3').value);
	formdata.append("kebun", document.getElementById('kebun3').value);
	formdata.append("status", document.getElementById('status').value);
	formdata.append("namapeta", document.getElementById('namapeta').value);
	formdata.append("tipepeta", document.getElementById('tipepeta').value);
	formdata.append("id", document.getElementById('id').value);
	formdata.append("revisi", document.getElementById('revisi').value);
	formdata.append("method", document.getElementById('method').value);
	
	document.getElementById('tblsimpan').style.display='none';
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "umm_slave_uploadpeta.php?proses=simpan", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('tblsimpan').style.display='';
				} else {
					//=== Success Response
					document.getElementById('tblsimpan').style.display='';
					loaddata(0);
					batalscr();
					batal();
					// alert(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showDetail(nosvg, ev) {
	title = nosvg;
	content = "<div id='svgimg' style='width:780px;height:380px;padding:5px;'></div>";
	width = '800';
	height = '400';
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = '200px';
	document.getElementById('dynamic1').style.left = (pos[0] - 10 - width) + 'px';
	document.getElementById('dynamic1').style.display = '';
}

function deldata(idsvg) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;

	param = "idsvg=" + idsvg;

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	if (confirm('Are you sure delete this item : ' + idsvg + '?'))
		post_response_text('umm_slave_uploadpeta.php?proses=deldata', param, respon);
}


function isifile(namafile, ev) {
	param = 'proses=isifile&nodok=' + nodok + '&namafile=' + namafile;
	title = "Data Detail";
	showDialog4(title, "<iframe frameborder=0 style='width:795px;height:395px'" +
		" src='umm_slave_uploadpeta.php?" + param + "'></iframe>", '800', '400', ev);
	var dialog = document.getElementById('dynamic4');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}