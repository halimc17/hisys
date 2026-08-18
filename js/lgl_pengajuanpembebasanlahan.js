function detailPDF(notransaksi,noakun,tipetransaksi,kodeorg) {
    ev = 'event';
    param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    
    showDialog5('Print PDF',"<iframe frameborder=0 style='width:930px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'950','400',ev);
    var dialog = document.getElementById('dynamic5');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

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

function form_ajukan(notransaksi, kodeorg, numrow) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);

	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&numrow=' + numrow;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan() {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;

	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[8].innerHTML = '';
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
					alert('Sucses');
					closeDialog();
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
	content = "<fieldset><div id=containerd style=\"width:1300px;max-height:500px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog2(title, content, width, height, ev);
}

function html(notransaksi, kodeorg, periode) {
	form();
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
					loadfiles(notransaksi);
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
	document.getElementById('periodesch').value = '';
	document.getElementById('notrsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}

function edit(kodeorg, periode, notransaksi) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('periode').value = periode;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(notransaksi, kodeorg, periode);
}

function deletedetail(notransaksi, kodeorg, periode, jenis, nama, nosppt) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&periode=' + periode + '&jenis=' + jenis + '&nama=' + nama + '&nosppt=' + nosppt;

	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
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
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(kodeorg, periode, notransaksi) {
	param = 'method=delete' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
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
					document.getElementById('contain').innerHTML = con.responseText;
					//deletefileall(notransaksi);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	notransaksi = document.getElementById('notransaksi').value;

	if (periode == '' || kodeorg == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detail';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
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
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	divsch = document.getElementById('divsch').value;
	periodesch = document.getElementById('periodesch').value;
	notrsch = document.getElementById('notrsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (periodesch != '') {
		param += '&periodesch=' + periodesch;
	}
	if (notrsch != '') {
		param += '&notrsch=' + notrsch;
	}

	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
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
	document.getElementById('periode').disabled = false;
	document.getElementById('periode').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('notransaksi').value = '';

}

function loaddatadetail(notransaksi) {
	//document.getElementById('tomboldetail').disabled=true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('periode').disabled = true;
	periode = document.getElementById('periode').value;
	kodeorg = document.getElementById('kodeorg').value;
	notransaksi = document.getElementById('notransaksi').value;

	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;

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

	/*document.getElementById('dynamic2').style.top = pos[1]+'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) +'px';
	document.getElementById('dynamic2').style.display='';*/
}

function showupload(ev, notransaksi) {
	param = "";
	param += "notransaksi=" + notransaksi;
	param += '&method=showupload';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
  
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('600px','400px');
					loadfiles(notransaksi);
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
	var notransaksi = document.getElementById('notrupload').innerHTML;
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kriteria", getValue('kriteria'));
	formdata.append("notransaksi", notransaksi);

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_pengajuanpembebasanlahan.php?method=submitfile", true);
	busy_on();
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
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changekriteria(id) {
	kriteriax = getValue('kriteriax_'+id);
	param = 'method=changekriteria&kriteriax='+kriteriax+'&id='+id;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	console.log(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					console.log(con.responseText);
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

function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;

	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdata() {
	kodeorg = document.getElementById('kodeorg').value;
	jenis = document.getElementById('jenis').value;
	param = 'method=getdata' + '&kodeorg=' + kodeorg + '&jenis=' + jenis;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('nama').innerHTML = con.responseText;
					if (jenis == 'fee' || jenis == 'admin' || jenis=='PANJAR') {
						document.getElementById('nosppt').value = '';
						document.getElementById('jlhsppt').value = '';
						document.getElementById('luastantum').value = ''
						document.getElementById('rupiah').value = '';
						document.getElementById('rupiahx').value = '';
						document.getElementById('rpsppt').value = '';
						document.getElementById('nosppt').disabled = true;
						document.getElementById('jlhsppt').disabled = true;
						document.getElementById('luastantum').disabled = true;
						document.getElementById('rupiahx').disabled = true;
						document.getElementById('rpsppt').disabled = true;

						document.getElementById('lokasi').disabled = false;
						document.getElementById('luaslahan').disabled = true;
					} else {
						document.getElementById('nosppt').value = '';
						document.getElementById('jlhsppt').value = '';
						document.getElementById('luastantum').value = '';
						document.getElementById('rupiah').value = '';
						document.getElementById('rupiahx').value = '';
						document.getElementById('nosppt').value = '';
						document.getElementById('rpsppt').value = '';
						document.getElementById('luaslahan').value = '';
						document.getElementById('luastantum').value = '';
						document.getElementById('nosppt').disabled = false;
						document.getElementById('jlhsppt').disabled = false;
						document.getElementById('luastantum').disabled = false;
						document.getElementById('rupiahx').disabled = false;
						document.getElementById('rpsppt').disabled = false;

						document.getElementById('lokasi').disabled = true;
						document.getElementById('nosppt').disabled = true;
						document.getElementById('luaslahan').disabled = true;
					}
					document.getElementById('lokasi').value = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getlokasi() {
	kodeorg = document.getElementById('kodeorg').value;
	jenis = document.getElementById('jenis').value;
	nama = document.getElementById('nama').value;
	param = 'method=getlokasi' + '&kodeorg=' + kodeorg + '&jenis=' + jenis + '&idlahan=' + nama;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	if (jenis == 'GRLTT' || jenis == 'SHM') {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (jenis == 'GRLTT' || jenis == 'SHM') {
						data = con.responseText.split('###');
						document.getElementById('luaslahan').value = trim(data[2]);
						document.getElementById('luaslahan').disabled = true;
						document.getElementById('luastantum').value = trim(data[2]);
						document.getElementById('nosppt').value = trim(data[1]);
						document.getElementById('nosppt').disabled = true;
						document.getElementById('lokasi').value = trim(data[0]);
						document.getElementById('lokasi').disabled = true;
						hitungrp();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function samatantum() {
	luaslahan = document.getElementById('luaslahan').value;
	jenis = document.getElementById('jenis').value;
	if (jenis == 'GRLTT' || jenis == 'SHM') {
		document.getElementById('luastantum').value = luaslahan;
	} else {
		document.getElementById('luastantum').value = '';
	}
}
function hitungrp() {
	jenis = document.getElementById('jenis').value;
	luaslahan = document.getElementById('luaslahan').value;
	luastantum = document.getElementById('luastantum').value;
	harga = document.getElementById('harga').value;
	hargatantum = document.getElementById('hargatantum').value;
	rpsppt = document.getElementById('rpsppt').value;

	luaslahan = remove_comma_var(luaslahan);
	luastantum = remove_comma_var(luastantum);
	harga = remove_comma_var(harga);
	hargatantum = remove_comma_var(hargatantum);
	rpsppt = remove_comma_var(rpsppt);

	rp = parseFloat(luaslahan) * parseFloat(harga);
	rp2 = parseFloat(luastantum) * parseFloat(hargatantum);
	if (isNaN(rp) == true) {
		rp = 0;
	}
	if (isNaN(rp2) == true) {
		rp2 = 0;
	}
	if (rpsppt == '') {
		rpsppt = 0;
	}

	total = parseFloat(rp) + parseFloat(rp2) + parseFloat(rpsppt);
	console.log(total);
	if (isNaN(total) == true) {
		total = 0;
	}
	if (jenis == 'GRLTT' || jenis == 'SHM') {
		document.getElementById('rupiahx').value = numberFormat(rp, 2);
		document.getElementById('rupiahtantum').value = numberFormat(rp2, 2);
	} else {
		document.getElementById('rupiahx').value = '';
		document.getElementById('rpsppt').value = '';
	}
	document.getElementById('totalrp').value = numberFormat(total, 2);
	document.getElementById('rupiah').value = numberFormat(rp, 2);
}

function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	var components = (parseFloat(number).toFixed(digit)).split(".");
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return components.join(".");
}

function savedetail() {
	kodeorg = document.getElementById('kodeorg').value;
	periode = document.getElementById('periode').value;
	notransaksi = document.getElementById('notransaksi').value;
	jenis = document.getElementById('jenis').value;
	nama = document.getElementById('nama').value;
	nosppt = document.getElementById('nosppt').value;
	jlhsppt = document.getElementById('jlhsppt').value;
	lokasi = document.getElementById('lokasi').value;
	luaslahan = document.getElementById('luaslahan').value;
	luastantum = document.getElementById('luastantum').value;
	harga = document.getElementById('harga').value;
	hargatantum = document.getElementById('hargatantum').value;
	rupiah = document.getElementById('rupiah').value;
	rupiahtantum = document.getElementById('rupiahtantum').value;
	rpsppt = document.getElementById('rpsppt').value;
	totalrp = document.getElementById('totalrp').value;
	method = document.getElementById('method').value;

	if ((kodeorg == '' || periode == '' || jenis == '' || nama == '' || totalrp == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}

	param = 'kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis;
	param += '&nama=' + nama;
	param += '&nosppt=' + nosppt;
	param += '&jlhsppt=' + jlhsppt;
	param += '&lokasi=' + lokasi;
	param += '&luaslahan=' + luaslahan;
	param += '&luastantum=' + luastantum;
	param += '&harga=' + harga;
	param += '&hargatantum=' + hargatantum;
	param += '&rupiah=' + rupiah;
	param += '&rupiahtantum=' + rupiahtantum;
	param += '&rpsppt=' + rpsppt;
	param += '&totalrp=' + totalrp;
	param += '&method=' + method;

	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					console.log(con.responseText);
					cleardetail();
					loaddatadetail(notransaksi);
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
	document.getElementById('nama').value = '';
	document.getElementById('nosppt').value = '';
	document.getElementById('nosppt').disabled = false;
	document.getElementById('jlhsppt').value = '';
	document.getElementById('jlhsppt').disabled = false;
	document.getElementById('lokasi').value = '';
	document.getElementById('luaslahan').value = '';
	document.getElementById('luastantum').value = '';
	document.getElementById('luastantum').disabled = false;
	document.getElementById('harga').value = '';
	document.getElementById('hargatantum').value = '';
	document.getElementById('rupiah').value = '';
	document.getElementById('rupiahx').value = '';
	document.getElementById('rupiahx').disabled = false;
	document.getElementById('rupiahtantum').value = '';
	document.getElementById('rupiahtantum').disabled = false;
	document.getElementById('rpsppt').value = '';
	document.getElementById('rpsppt').disabled = false;
	document.getElementById('totalrp').value = '';
}

function getnotransaksi() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	periode = document.getElementById('periode').value;
	document.getElementById('notransaksi').value = '';
	param = 'periode=' + periode + '&kodeorg=' + kodeorg + '&method=getnotransaksi';

	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getdaftarmasy(idlahan) {
	title = "View";
	width = '950px';
	height = '';
	ev = 'event';
	content = "<div id=containerview style='overflow:auto;width:930px;height:auto;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getdaftarmasy';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getstatuslahan(idlahan) {
	title = "View";
	width = '1350px';
	height = '';
	ev = 'event';
	content = "<div id=containerview style='overflow:auto;width:1230px;height:auto;vertical-align:top;'></div>";
	showDialog5(title, content, width, height, ev);
	
	param = "";
	param += "idlahan=" + idlahan;
	param += '&method=getstatuslahan';
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('containerview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukanbayar(notransaksi,kodeorg,periode,jenis,nama,nosppt,numrow) {
	param = 'method=ajukanbayar' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis + '&nama=' + nama + '&nosppt=' + nosppt;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	
	var date = new Date();
	var day = date.getDate();
	var month = date.getMonth()+1;
	var yy = date.getFullYear();
	tglhi = yy+"-"+tambahNol(month)+"-"+tambahNol(day);
	
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
					x = document.getElementById('trd_' + numrow);
					x.cells[17].innerHTML = tglhi;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalpemby(notransaksi,kodeorg,periode,jenis,nama,nosppt,numrow) {
	param = 'method=batalpemby' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi;
	param += '&jenis=' + jenis + '&nama=' + nama + '&nosppt=' + nosppt;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	
	var date = new Date();
	var day = date.getDate();
	var month = date.getMonth()+1;
	var yy = date.getFullYear();
	tglhi = yy+"-"+tambahNol(month)+"-"+tambahNol(day);
	
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
					x = document.getElementById('trd_' + numrow);
					x.cells[14].innerHTML = tglhi;
					x.cells[14].style.backgroundColor = 'red';

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function tambahNol(x){
   y=(x>9)?x:'0'+x;
   return y;
}

function getdatapdf(namafile){
	param = 'method=getdatapdf&namafile='+namafile;
	tujuan = 'lgl_slave_pengajuanpembebasanlahan.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alertify.alert("Info",con.responseText);
				}else{
					alertify.popuppdf().destroy();
					// alertify.popuppdf().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='lgl_slave_pengajuanpembebasanlahan.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false,'maximizable':true,'startMaximized':true}).resizeTo('90%','80%');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}