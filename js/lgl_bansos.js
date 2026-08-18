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
	tujuan = 'lgl_slave_bansos.php';
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
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					x = document.getElementById('tr_' + numrow);
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					x.cells[8].innerHTML = '';
					alert('Sucses');
					closeDialog();
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukandireksi(notransaksi, kodeorg) {

	param = 'method=ajukandireksi' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg;
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					loaddata();
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
	content = "<fieldset><div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Preview";
	showDialog1(title, content, width, height, ev);
}
function previewexcelbansos(notransaksi, kodeorg, periode, tipe) {
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi + '&tipe=' + tipe;
	tujuan = 'lgl_slave_bansos.php' + "?" + param;
	printnopopup(tujuan + '?' + param)
}
function html(notransaksi, kodeorg, periode, tipe) {
	width = '';
	height = '';
	content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
	ev = 'event';
	title = "Preview";
	showDialog1(title, content, width, height, ev);
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi + '&tipe=' + tipe;
	tujuan = 'lgl_slave_bansos.php';
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

function pdf(notransaksi, kodeorg, periode, tipe) {
	param = 'method=pdf' + '&kodeorg=' + kodeorg + '&periode=' + periode + '&notransaksi=' + notransaksi + '&tipe=' + tipe;
	tujuan = 'lgl_slave_bansos.php?' + param;
	title = '';
	width = '1000';
	height = '700';
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog2(title, content, width, height, ev);
}

function displayList() {
	document.getElementById('divsch').value = '';
	document.getElementById('namasrc').value = '';
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalsampai').value = '';
	document.getElementById('kasbanksrc').value = '';
	document.getElementById('ptsrc').value = '';
	document.getElementById('status').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}
function edit(kodeorg, tanggal, notransaksi, kategori, namapemesan, lokasipemesan, tujuan, rekening, tipebayar, atasnama, npwp) {
	document.getElementById('tipebayar').value = tipebayar;
	document.getElementById('atasnama').value = atasnama;
	document.getElementById('npwp').value = npwp;
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('tanggal').value = tanggal;
	document.getElementById('kategori').value = kategori;
	document.getElementById('namapemesan').value = namapemesan;
	document.getElementById('lokasipemesan').value = lokasipemesan;
	document.getElementById('tujuan').value = tujuan;
	document.getElementById('rekening').value = rekening;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(notransaksi, kodeorg, tanggal);
}
function deletedetail(notransaksi, kodeorg, kategori, tujuan, deskripsi, tanggal, satuan) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&kategori=' + kategori + '&tujuan=' + tujuan + '&deskripsi=' + deskripsi + '&tanggal=' + tanggal + '&satuan=' + satuan;
	tujuan = 'lgl_slave_bansos.php';
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
function del(kodeorg, tanggal, notransaksi) {
	param = 'method=delete' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_bansos.php';
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
					loaddata();
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
	tanggal = document.getElementById('tanggal').value;
	// notransaksi = document.getElementById('notransaksi').value;
	kategori = document.getElementById('kategori').value;
	namapemesan = document.getElementById('namapemesan').value;
	lokasipemesan = document.getElementById('lokasipemesan').value;
	tujuan = document.getElementById('tujuan').value;
	rekening = document.getElementById('rekening').value;
	if (tanggal == '' || kodeorg == '' || kategori == '' || namapemesan == '' || lokasipemesan == '' || tujuan == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('tanggal').disabled = true;
	document.getElementById('kategori').disabled = true;
	document.getElementById('lokasipemesan').disabled = true;
	param = 'method=detail';
	param += '&tanggal=' + tanggal + '&kodeorg=' + kodeorg;
	param += '&kategori=' + kategori + '&namapemesan=' + namapemesan + '&lokasipemesan=' + lokasipemesan;
	param += '&tujuan=' + tujuan;
	param += '&rekening=' + rekening;
	tujuan = 'lgl_slave_bansos.php';
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
function loaddata(page, tipe = 'html') {
	divsch = document.getElementById('divsch').value;
	namasrc = document.getElementById('namasrc').value;
	tanggalmulai = document.getElementById('tanggalmulai').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	ptsrc = document.getElementById('ptsrc').value;
	kasbanksrc = document.getElementById('kasbanksrc').value;
	katsrc = document.getElementById('katsrc').value;
	status = document.getElementById('status').value;



	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (namasrc != '') {
		param += '&namasrc=' + namasrc;
	}
	if (status != '') {
		param += '&status=' + status;
	}

	param += '&tanggalsampai=' + tanggalsampai;
	param += '&tanggalmulai=' + tanggalmulai;
	param += '&ptsrc=' + ptsrc;
	param += '&kasbanksrc=' + kasbanksrc;
	param += '&katsrc=' + katsrc;
	param += '&status=' + status;
	param += '&tipe=' + tipe;

	tujuan = 'lgl_slave_bansos.php';
	if (tipe == 'excel') {
		printnopopup(tujuan + '?' + param)
	} else {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("#####");
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
	document.getElementById('tanggal').disabled = false;
	document.getElementById('tanggal').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('notransaksi').value = '';
	document.getElementById('kategori').value = '';
	document.getElementById('lokasipemesan').value = '';
	document.getElementById('namapemesan').value = '';
	document.getElementById('tipebayar').value = '';
	document.getElementById('atasnama').value = '';
	document.getElementById('npwp').value = '';
	document.getElementById('tujuan').value = '';
	document.getElementById('rekening').value = '';
	document.getElementById('kategori').disabled = false;
	document.getElementById('lokasipemesan').disabled = false;
}
function loaddatadetail(notransaksi) {
	tanggal = document.getElementById('tanggal').value;
	kodeorg = document.getElementById('kodeorg').value;
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&tanggal=' + tanggal + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	var components = (parseFloat(number).toFixed(digit)).split(".");
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return components.join(".");
}
function savedetail() {
	kodeorg = document.getElementById('kodeorg').value;
	tanggal = document.getElementById('tanggal').value;
	notransaksi = document.getElementById('notransaksi').value;
	kategori = document.getElementById('kategori').value;
	namapemesan = document.getElementById('namapemesan').value;
	lokasipemesan = document.getElementById('lokasipemesan').value;
	tujuan = document.getElementById('tujuan').value;
	deskripsi = document.getElementById('deskripsi').value;
	satuan = document.getElementById('satuan').value;
	rupiah = document.getElementById('rupiah').value;
	rekening = document.getElementById('rekening').value;
	tipebayar = document.getElementById('tipebayar').value;
	atasnama = document.getElementById('atasnama').value;
	npwp = document.getElementById('npwp').value;
	method = document.getElementById('method').value;
	if (deskripsi == '' || rupiah == '' || satuan == '') {
		alert('Lengkapi Pengisian.');
		return;
	}
	param = 'kodeorg=' + kodeorg;
	param += '&tanggal=' + tanggal;
	param += '&notransaksi=' + notransaksi;
	param += '&kategori=' + kategori;
	param += '&namapemesan=' + namapemesan;
	param += '&lokasipemesan=' + lokasipemesan;
	param += '&tujuan=' + tujuan;
	param += '&deskripsi=' + deskripsi;
	param += '&satuan=' + satuan;
	param += '&rupiah=' + rupiah;
	param += '&rekening=' + rekening;
	param += '&atasnama=' + atasnama;
	param += '&npwp=' + npwp;
	param += '&tipebayar=' + tipebayar;
	param += '&method=' + method;
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value = con.responseText.trim();
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
	document.getElementById('deskripsi').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('rupiah').value = '';
	document.getElementById('method').value = 'insert';
}
function getnotransaksi() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tanggal = document.getElementById('tanggal').value;
	document.getElementById('notransaksi').value = '';
	param = 'tanggal=' + tanggal + '&kodeorg=' + kodeorg + '&method=getnotransaksi';
	tujuan = 'lgl_slave_bansos.php';
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
function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').value;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	if (notransaksi == "") {
		alert("warning : Silahkan isikan detail terlebih dahulu !");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_bansos.php?method=submitfile", true);
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
function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_bansos.php';
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
function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'lgl_slave_bansos.php';

	if (confirm('Apakah anda yakin menghapus?')) {
		post_response_text(tujuan, param, respog);
	}
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
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		width = '';
		height = '';
		content = "<fieldset style=\"width:97%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
		ev = 'event';
		title = "View";
		showDialog2(title, content, width, height, ev);
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_bansos.php';
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
					document.getElementById('contviewx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getrekening() {
	lokasipemesan = document.getElementById('lokasipemesan').options[document.getElementById('lokasipemesan').selectedIndex].value;
	tipebayar = document.getElementById('tipebayar').value;
	param = 'tipebayar=' + tipebayar + '&lokasipemesan=' + lokasipemesan + '&method=getrekening';
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (tipebayar == 'transfer') {
						document.getElementById('rekening').innerHTML = trim(con.responseText);
						document.getElementById('rekening').value = '';
						document.getElementById('atasnama').value = '';
						document.getElementById('rekening').disabled = false;
						document.getElementById('atasnama').disabled = true;
					} else {
						document.getElementById('atasnama').disabled = false;
						document.getElementById('rekening').disabled = true;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getatasnama() {
	rekening = document.getElementById('rekening').value;
	param = 'rekening=' + rekening + '&method=getatasnama';
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('atasnama').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingin(kodeorg, tanggal, notransaksi) {
	let cf = confirm('Apakah yakin diposting?');
	if (cf == false) {
		return false;
	}
	param = 'method=posting' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(kodeorg, tanggal, notransaksi) {
	let cf = confirm('Apakah yakin unposting?');
	if (cf == false) {
		return false;
	}
	param = 'method=unposting' + '&kodeorg=' + kodeorg + '&tanggal=' + tanggal + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_bansos.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}