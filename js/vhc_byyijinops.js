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
	tujuan = 'vhc_slave_byyijinops.php';
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
	tujuan = 'vhc_slave_byyijinops.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containeraju').innerHTML = con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					x.cells[8].innerHTML = '';
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
	width = '720';
	height = '';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function html(notransaksi, kodeorg, tgl) {
	form();
	param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
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
	document.getElementById('tglsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}
function edit(kodeorg, tgl, notransaksi) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('tgl').value = tgl;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(notransaksi, kodeorg, tgl);
}
function editdetail(div, kodekend, tgl, driver, pekerjaan, keterangan, satuan, fisik) {
	document.getElementById('kodekend').value = kodekend;
	document.getElementById('kodekend').disabled = true;
	document.getElementById('driver').value = driver;
	document.getElementById('driver').disabled = true;
	document.getElementById('pekerjaan').value = pekerjaan;
	document.getElementById('pekerjaan').disabled = true;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('satuan').value = satuan;
	document.getElementById('fisik').value = fisik;
	document.getElementById('method').value = 'update';
}
function deletedetail(kodekend, tgl, kodeorg, notransaksi, jenisbiaya) {
	param = 'method=deletedetail' + '&kodekend=' + kodekend + '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&notransaksi=' + notransaksi + '&jenisbiaya=' + jenisbiaya;
	tujuan = 'vhc_slave_byyijinops.php';
	//if(confirm(' Anda yakin ingin menghapus nomor transaksi'))
	// {
	post_response_text(tujuan, param, respog);
	//}
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
function del(kodeorg, tgl, notransaksi) {
	param = 'method=delete' + '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
	if (confirm(' Anda yakin ingin menghapus nomor transaksi')) {
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
					deletefileall(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(notransaksi, kodeorg, tgl, numrow) {
	param = 'method=unposting' + '&notransaksi=' + notransaksi + '&kodeorg=' + kodeorg + '&tgl=' + tgl;
	tujuan = 'vhc_slave_byyijinops.php';
	if (confirm('Anda yakin ingin unposting transaksi nomor ' + notransaksi + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					x.cells[8].innerHTML = '';
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
	tgl = document.getElementById('tgl').value;
	notransaksi = document.getElementById('notransaksi').value;
	if (tgl == '' || kodeorg == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detail';
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
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
	tglsch = document.getElementById('tglsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'vhc_slave_byyijinops.php';
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
	document.getElementById('tgl').disabled = false;
	document.getElementById('tgl').value = '';
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('notransaksi').value = '';
}
function loaddatadetail(notransaksi) {
	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('tgl').disabled = true;
	tgl = document.getElementById('tgl').value;
	kodeorg = document.getElementById('kodeorg').value;
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
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
function getdata() {
	kodekend = document.getElementById('kodekend').value;
	param = 'method=getdata' + '&kodekend=' + kodekend;
	tujuan = 'vhc_slave_byyijinops.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("######");
					document.getElementById('nopol').innerHTML = isdt[0];
					document.getElementById('tahunperolehan').innerHTML = isdt[1];
					document.getElementById('kepemilikan').innerHTML = isdt[2];
					document.getElementById('kodetraksi').innerHTML = isdt[3];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savedetail() {
	kodeorg = document.getElementById('kodeorg').value;
	tgl = document.getElementById('tgl').value;
	kodekend = document.getElementById('kodekend').value;
	biaya = document.getElementById('biaya').value;
	keterangan = document.getElementById('keterangan').value;
	notransaksi = document.getElementById('notransaksi').value;
	jenisbiaya = document.getElementById('jenisbiaya').value;
	method = document.getElementById('method').value;
	if ((kodekend == '' || biaya == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}
	param = 'kodeorg=' + kodeorg + '&kodekend=' + kodekend + '&jenisbiaya=' + jenisbiaya;
	param += '&biaya=' + biaya + '&keterangan=' + keterangan + '&notransaksi=' + notransaksi;
	param += '&tgl=' + tgl;
	param += '&method=' + method;
	tujuan = 'vhc_slave_byyijinops.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
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
	document.getElementById('kodekend').value = '';
	document.getElementById('kodekend').disabled = false;
	document.getElementById('nopol').innerHTML = '';
	document.getElementById('tahunperolehan').innerHTML = '';
	document.getElementById('kepemilikan').innerHTML = '';
	document.getElementById('kodetraksi').innerHTML = '';
	document.getElementById('biaya').value = '';
	document.getElementById('keterangan').value = '';
	document.getElementById('jenisbiaya').value = '';
}
function getnotransaksi() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tgl = document.getElementById('tgl').value;
	document.getElementById('notransaksi').value = '';
	param = 'tgl=' + tgl + '&kodeorg=' + kodeorg + '&method=getnotransaksi';
	tujuan = 'vhc_slave_byyijinops.php';
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
function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}
function showupload(ev) {
	showformupload(ev);
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=showupload&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
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
	var notransaksi = document.getElementById("noppupload").innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "vhc_slave_byyijinops.php?method=submitfile", true);
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
	tujuan = 'vhc_slave_byyijinops.php';
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
					if (document.getElementById('loadfilesview') !== null) {
						document.getElementById('loadfilesview').innerHTML = con.responseText;
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
	param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'vhc_slave_byyijinops.php';
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
function deletefileall(notransaksi) {
	param = 'method=deletefileall&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_byyijinops.php';
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