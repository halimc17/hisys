// JavaScript Document

function updtjam() {
	Jam = document.getElementById('jam').value;
	jammulai = document.getElementById('jam_mulai').value;

	param = 'Jam=' + Jam + '&proses=updtjam';
	param += "&jammulai=" + jammulai;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('jam_selesai').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function add_new_data() {
	document.getElementById('headher').style.display = "block";
	document.getElementById('listData').style.display = "none";
	document.getElementById('detailEntry').style.display = "none";
	unlockForm();
	document.getElementById('contentDetail').innerHTML = '';
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('detailEntry').style.display = 'none';
	document.getElementById('kdOrgCr').value = '';
	document.getElementById('tgl_cari').value = '';
	loadData(0);
}

function cancelAbsn() {
	document.getElementById('kdOrg').value = '';
	document.getElementById('tglAbsen').value = '';
}

function cariOrg(title, content, ev) {
	width = '500';
	height = '400';
	showDialog1(title, content, width, height, ev);
}
function previewDetail(notrans, ev) {
	showDetail(notrans, ev);
	param = 'no=' + notrans + '&proses=prevprog';
	tujuan = 'sdm_slave_splembur.php';
	//alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contDetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showDetail(notrans, ev) {
	title = "Progress Persetujuan";
	width = '';
	height = '';
	content = "<fieldset><div id=contDetail style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog5(title, content, width, height, ev);
}

function findOrg() {
	txt = trim(document.getElementById('fnOrg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&proses=cariOrg';
		tujuan = 'sdm_slave_splembur.php';
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setOrg(kdOrg, nmOrg) {
	document.getElementById('kdOrg').value = kdOrg;
	document.getElementById('nmOrg').value = nmOrg;
	closeDialog();
}

function findOrg2() {
	txt = trim(document.getElementById('crOrg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&proses=cariOrg2';
		tujuan = 'sdm_slave_splembur.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setOrg2(kdOrg, nmOrg) {
	document.getElementById('kdOrg').value = kdOrg;
	document.getElementById('txtsearch').value = nmOrg;
	closeDialog();
}

function detailAbsn() {
	kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tgl = document.getElementById('tglAbsen').value;
	persetujuan1 = document.getElementById('persetujuan1').value;
	persetujuan2 = document.getElementById('persetujuan2').value;
	if ((kdorg == '') || (tgl == '') || (persetujuan1 == '') || (persetujuan2 == '')) {
		alert("Data are obligatory");
		return;
	}

	tujuan = 'sdm_slave_splembur.php';
	param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&proses=cekHeader' + '&persetujuan1=' + persetujuan1 + '&persetujuan2=' + persetujuan2;
	//alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					numrow = data[0];
					document.getElementById('notransaksi').value = data[1];
					add_detail(numrow);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function add_detail(numrow) {
	if (numrow > 0) {
		alert("Tanggal yang dipilih adalah hari libur.");
	}
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	param = 'kdorg=' + kdorg + '&tgl=' + tgl;
	param += "&proses=add_detail";
	tujuan = 'sdm_slave_splembur.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailEntry').style.display = 'block';
					document.getElementById('detailIsi').innerHTML = con.responseText;
					document.getElementById('loaddetail').style.display = 'none';
					lockForm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}

function lockForm() {
	document.getElementById('kdOrg').disabled = true;
	document.getElementById('tglAbsen').disabled = true;
	document.getElementById('persetujuan1').disabled = true;
	document.getElementById('persetujuan2').disabled = true;
}

function unlockForm() {
	document.getElementById('persetujuan1').disabled = false;
	document.getElementById('persetujuan2').disabled = false;
	document.getElementById('kdOrg').disabled = false;
	document.getElementById('tglAbsen').disabled = false;
	document.getElementById('butsave').disabled = false;
	document.getElementById('persetujuan1').value = '';
	document.getElementById('persetujuan2').value = '';
	document.getElementById('kdOrg').value = '';
	document.getElementById('tglAbsen').value = '';
	document.getElementById('notransaksi').value = '';
}

function editDetail(krywn, tplmbr, jmaktl, ungmkn, ungtrans, unglbhjm, jammulai, jamselesai, ket) {
	//if (confirm("Anda yakin ingin mengedit")) {
		document.getElementById('krywnId').value = krywn;
		document.getElementById('krywnId').disabled = true;
		document.getElementById('tpLmbr').value = tplmbr;
		document.getElementById('uang_mkn').value = ungmkn;
		document.getElementById('uang_trnsprt').value = ungtrans;
		document.getElementById('uang_lbhjm').value = unglbhjm;
		document.getElementById('jam_mulai').value = jammulai;
		document.getElementById('jam_selesai').value = jamselesai;
		document.getElementById('keterangan').value = ket;
		document.getElementById('proses').value = "updateDetail";
		getLembur(tplmbr, jmaktl);
	//}
}

/* Function addNewRow
 * Fungsi untuk menambah row baru ke dalam table
 * I : id dari tbody tabel
 * P : Persiapan row dalam bentuk HTML
 * O : Tambahan row pada akhir tabel (append)
 */
function addNewRow(body, onDetail) {

	var tabBody = document.getElementById(body);
	if (onDetail) {
		var detail = onDetail;
	} else {
		var detail = false;
	}

	// Search Available numRow
	var numRow = 0;
	if (!detail) {
		while (document.getElementById('tr_' + numRow)) {
			numRow++;
		}
	} else {
		while (document.getElementById('detail_tr_' + numRow)) {
			numRow++;
		}
	}

	// Add New Row
	var newRow = document.createElement("tr");
	tabBody.appendChild(newRow);
	if (!detail) {
		newRow.setAttribute("id", "tr_" + numRow);
	} else {
		newRow.setAttribute("id", "detail_tr_" + numRow);
	}
	newRow.setAttribute("class", "rowcontent");

	if (!detail) {
		newRow.innerHTML += "<td><input id='kode_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='matauang_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='simbol_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='kodeiso_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><img id='add_" + numRow +
		"' title='Tambah' class=zImgBtn onclick=\"addMain('" + numRow + "')\" src='images/plus.png'/>" +
		"&nbsp;<img id='delete_" + numRow + "' />" +
		"&nbsp;<img id='pass_" + numRow + "' />" +
		"</td>";
	} else {
		// Create Row
		newRow.innerHTML += "<td><select id='krywnId_" + numRow + "' type='text' style='width:150px' />" + optIsi + "</select></td><td>" + "<select id='tpLmbr_" + numRow + "' />" + optLmbr + "</select></td>" + "<td><select id='jmId_" + numRow + "' type='text' />" + optJm + "</select>:<select id='mntId_" + numRow + "' type='text' />" + optMnt + "</select></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_mkn_" + numRow + " id=uang_mkn_" + numRow + "></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_trnsprt_" + numRow + " id=uang_trnsprt_" + numRow + "></td>" + "<td><input type='text' onfocus=\"normal_number_1('" + numRow + "')\" onblur=\"chngeFormat('" + numRow + "')\" maxlength='10' onkeypress='return angka_doang(event)' style='width: 100px;' value='0' class='myinputtextnumber' name=uang_lbhjm_" + numRow + " id=uang_lbhjm_" + numRow + "></td>" + "<td><img id='detail_add_" + numRow + "' title='Tambah' class=zImgBtn onclick=\"addDetail('" + numRow + "')\" src='images/save.png'/>" + "&nbsp;<img id='detail_delete_" + numRow + "' />" + "&nbsp;<img id='detail_pass_" + numRow + "' />" + "</td>";
	}
}

/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id, main) {

	if (main == 'main') {
		var idField = document.getElementById('add_' + id);
		var delImg = document.getElementById('delete_' + id);
		var passImg = document.getElementById('pass_' + id);
		var kode = document.getElementById('kode_' + id);
	} else {
		//alert(id);
		var idField = document.getElementById('detail_add_' + id);
		var delImg = document.getElementById('detail_delete_' + id);
	}
	if (idField) {
		idField.removeAttribute('id');
		idField.removeAttribute('name');
		idField.removeAttribute('onclick');
		idField.removeAttribute('src');
		idField.removeAttribute('title');

		// Set Edit Image Attr
		idField.setAttribute('title', 'Edit');
		if (main == 'main') {
			idField.setAttribute('id', 'edit_' + id);
			idField.setAttribute('name', 'edit_' + id);
			idField.setAttribute('onclick', 'editMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
		} else {
			//alert(id);
			idField.setAttribute('id', 'detail_edit_' + id);
			idField.setAttribute('name', 'detail_edit_' + id);
			idField.setAttribute('onclick', 'editDetail(\'' + id + '\')');
		}
		idField.setAttribute('src', 'images/001_45.png');

		// Set Delete Image Attr
		delImg.setAttribute('class', 'zImgBtn');
		delImg.setAttribute('title', 'Hapus');
		if (main == 'main') {
			delImg.setAttribute('name', 'delete_' + id);
			delImg.setAttribute('onclick', 'deleteMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
		} else {
			//alert(id);
			delImg.setAttribute('name', 'detail_delete_' + id);
			delImg.setAttribute('onclick', 'deleteDetail(\'' + id + '\')');
			document.getElementById('krywnId_' + id).disabled = true;
		}
		delImg.setAttribute('src', 'images/delete_32.png');

	} else {
		alert('DOM Definition Error');
	}
}

statFrm = 0;
function cek_data() {
	notransaksi = document.getElementById('notransaksi').value;
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	persetujuan1 = document.getElementById('persetujuan1').value;
	persetujuan2 = document.getElementById('persetujuan2').value;
	var rkrywn = document.getElementById('krywnId').options[document.getElementById('krywnId').selectedIndex].value;
	var rtpLmbr = document.getElementById('tpLmbr').options[document.getElementById('tpLmbr').selectedIndex].value;
	var rungMkn = document.getElementById('uang_mkn').value;
	var jam = document.getElementById('jam').options[document.getElementById('jam').selectedIndex].value;
	var rungTrans = document.getElementById('uang_trnsprt').value;
	var rungLbhjm = document.getElementById('uang_lbhjm').value;
	var jammulai = document.getElementById('jam_mulai').value;
	var jamselesai = document.getElementById('jam_selesai').value;
	var ket = document.getElementById('keterangan').value;
	pros = document.getElementById('proses').value;

	if (pros != "updateDetail") {
		param = "proses=cekData";
	} else {
		param = "proses=updateDetail";
	}
	param += "&notransaksi=" + notransaksi;
	param += "&kdorg=" + kdorg;
	param += "&tgl=" + tgl;
	param += "&persetujuan1=" + persetujuan1;
	param += "&persetujuan2=" + persetujuan2;
	param += "&tpLmbr=" + rtpLmbr;
	param += "&krywnId=" + rkrywn;
	param += "&ungTrans=" + rungTrans;
	param += "&ungLbhjm=" + rungLbhjm;
	param += "&ungMkn=" + rungMkn;
	param += "&Jam=" + jam;
	param += "&jammulai=" + jammulai;
	param += "&jamselesai=" + jamselesai;
	param += "&ket=" + ket;

	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (con.responseText != '') {
						alert(con.responseText);
					}
					bersihFormDet();
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedt() {
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	persetujuan1 = document.getElementById('persetujuan1').value;
	persetujuan2 = document.getElementById('persetujuan2').value;
	notransaksi = document.getElementById('notransaksi').value;
	totRow = document.getElementById('totrows').value;
	var allData = '';
	for (dwc = 0; dwc < totRow; dwc++) {
		allData += "&kar[" + dwc + "]=" + document.getElementById('kar_' + dwc).value;
		allData += "&tpLembur[" + dwc + "]=" + document.getElementById('tpLembur_' + dwc).value;
		allData += "&jamlmbr[" + dwc + "]=" + document.getElementById('jamlmbr_' + dwc).value;
		allData += "&uang_lbh[" + dwc + "]=" + document.getElementById('uang_lbh_' + dwc).value;
		allData += "&jam_mulai[" + dwc + "]=" + document.getElementById('jam_mulai_' + dwc).value;
		allData += "&jam_selesai[" + dwc + "]=" + document.getElementById('jam_selesai_' + dwc).value;
		allData += "&keterangan[" + dwc + "]=" + document.getElementById('keterangan_' + dwc).value;
	}

	param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&proses=savedt' + '&totRow=' + totRow + '&persetujuan1=' + persetujuan1 + '&persetujuan2=' + persetujuan2 + '&notransaksi=' + notransaksi;
	param += allData;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bersihFormDet() {
	document.getElementById('krywnId').value = '';
	document.getElementById('krywnId').disabled = false;
	document.getElementById('tpLmbr').value = '';
	document.getElementById('uang_mkn').value = '0';
	document.getElementById('uang_trnsprt').value = '0';
	document.getElementById('uang_lbhjm').value = '0';
	document.getElementById('jam').value = '';
	document.getElementById('proses').value = "";
	document.getElementById('jam_mulai').value = "00:00";
	document.getElementById('jam_selesai').value = "00:00";
	document.getElementById('keterangan').value = "";
}

function delDetail(notransaksi, kdorg, tgl, krywn) {
	param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&proses=delDetail' + '&krywnId=' + krywn + '&notransaksi=' + notransaksi;
	tujuan = 'sdm_slave_splembur.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Deleting, are you sure..?"))
		post_response_text(tujuan, param, respog);
}

function loadData(num) {
	kdOrgCr = document.getElementById('kdOrgCr').value;
	tgl_cari = document.getElementById('tgl_cari').value;

	param = 'proses=loadData';
	param += '&page=' + num;

	if (kdOrgCr != '') {
		param += '&kdorg=' + kdOrgCr;
	}

	if (tgl_cari != '') {
		param += '&tgl=' + tgl_cari;
	}

	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('continerlist').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
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
	loadData(paged);
}

function loadDetail() {
	tgl = document.getElementById('tglAbsen').value;
	notransaksi = document.getElementById('notransaksi').value;
	kdrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	param = 'tgl=' + tgl + '&kdorg=' + kdrg + '&notransaksi=' + notransaksi + '&proses=loadDetail';
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('butsave').disabled = true;
					document.getElementById('loaddetail').style.display = 'block';
					document.getElementById('contentDetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillField(notransaksi, kdorg, tgl, persetujuan1, persetujuan2) {
	document.getElementById('kdOrg').value = kdorg;
	document.getElementById('tglAbsen').value = tgl;
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('persetujuan1').value = persetujuan1;
	document.getElementById('persetujuan2').value = persetujuan2;
	param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&persetujuan1=' + persetujuan1 + '&persetujuan2=' + persetujuan2;
	param += "&proses=createTable";
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					lockForm();
					document.getElementById('listData').style.display = 'none';
					document.getElementById('headher').style.display = 'block';
					document.getElementById('detailEntry').style.display = 'block';
					document.getElementById('detailIsi').innerHTML = con.responseText;
					status_inputan = 1;
					statFrm = 1;
					// // showTmbl();
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delData(notransaksi, kdorg, tgl) {
	param = 'kdorg=' + kdorg + '&notransaksi=' + notransaksi + '&tgl=' + tgl + '&proses=delData';
	tujuan = 'sdm_slave_splembur.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Deleteing, are you sure..?"))
		post_response_text(tujuan, param, respog);
}

function delDataAll(kdorg, tgl) {
	param = 'kdorg=' + kdorg + '&tgl=' + tgl + '&proses=delData';
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function reset_data() {
	if (statFrm == 0) {
		if (confirm("Canceling, are you sure..?")) {
			kdorg = document.getElementById('kdOrg').value;
			tgl = document.getElementById('tglAbsen').value;
			delDataAll(kdorg, tgl);
		}
	}

}

function normal_number_1() {
	satu = document.getElementById('uang_mkn');
	satu.value = remove_comma(satu);
}

function normal_number_2() {
	dua = document.getElementById('uang_trnsprt');
	dua.value = remove_comma(dua);
}

function normal_number_3() {
	tiga = document.getElementById('uang_lbhjm');
	tiga.value = remove_comma(tiga);
}

function chngeFormat() {
	if (document.getElementById('uang_mkn').value != 0) {
		sat = document.getElementById('uang_mkn');
		change_number(sat);
	}
	if (document.getElementById('uang_trnsprt').value != 0) {
		dua = document.getElementById('uang_trnsprt');
		change_number(dua);
	}
	if (document.getElementById('uang_lbhjm').value != 0) {
		tiga = document.getElementById('uang_lbhjm');
		change_number(tiga);
	}
}

function getLembur(tplmbr, basisjam) {
	if ((tplmbr == '') && (basisjam == '')) {
		tipeLembur = document.getElementById('tpLmbr').options[document.getElementById('tpLmbr').selectedIndex].value;
		param = 'tpLembur=' + tipeLembur + '&proses=getBasis';
	} else {
		tipeLembur = tplmbr;
		bsisJam = basisjam;
		param = 'tpLembur=' + tipeLembur + '&proses=getBasis' + '&basisJam=' + bsisJam;
	}
	krywnId = document.getElementById('krywnId').value;
	kdorg = document.getElementById('kdOrg').value;
	param += '&kdorg=' + kdorg + '&krywnId=' + krywnId;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jam').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getUangLem() {
	basis = document.getElementById('jam').options[document.getElementById('jam').selectedIndex].value;
	idKry = document.getElementById('krywnId').options[document.getElementById('krywnId').selectedIndex].value;
	kodeOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tpeLmbr = document.getElementById('tpLmbr').options[document.getElementById('tpLmbr').selectedIndex].value;
	tanggal = document.getElementById('tglAbsen').value;
	tahun = tanggal.substr(6, 4);
	param = 'basisJam=' + basis + '&proses=getUang' + '&krywnId=' + idKry + '&kodeOrg=' + kodeOrg + '&tpLmbr=' + tpeLmbr + '&tahun=' + tahun + '&tgl=' + tanggal;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('uang_lbhjm').value = con.responseText;
					updtjam();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getLemburulang(tplmbr, basisjam, no,maxrow) {
	sumber = document.getElementById('tpLembur_'+ no).value;
	if ((tplmbr == '') && (basisjam == '')) {
		tipeLembur = document.getElementById('tpLembur_' + no).options[document.getElementById('tpLembur_' + no).selectedIndex].value;
		param = 'tpLembur=' + tipeLembur + '&proses=getBasis';
	} else {
		tipeLembur = tplmbr;
		bsisJam = basisjam;
		param = 'tpLembur=' + tipeLembur + '&proses=getBasis' + '&basisJam=' + bsisJam;
	}
	krywnId = document.getElementById('kar_' + no).value;
	kdorg = document.getElementById('kdOrg').value;
	param += '&kdorg=' + kdorg;
	param += '&krywnId=' + krywnId;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {					
					document.getElementById('jamlmbr_' + no).innerHTML = con.responseText;
					document.getElementById('uang_lbh_' + no).value = '';
					document.getElementById('jam_mulai_' + no).value = '00:00';
					document.getElementById('jam_selesai_' + no).value = '00:00';
					no += 1;
					if ((no > maxrow) || (maxrow == undefined)) {
						
					} else {
						document.getElementById('tpLembur_' + no).value=sumber;
						getLemburulang(tplmbr, basisjam,no, maxrow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function copiall(tplmbr, basisjam,no){
	totrows = document.getElementById('totrows').value;
	totrows = totrows-1;
	sumber = document.getElementById('tpLembur_'+ no).value;
	
	for (i = no; i <= totrows; i++) {
		getLemburulang(tplmbr, basisjam, i);
		document.getElementById('tpLembur_' + i).value=sumber;
	}
	
}

function getUangLemulang(no) {
	basis = document.getElementById('jamlmbr_' + no).options[document.getElementById('jamlmbr_' + no).selectedIndex].value;
	idKry = document.getElementById('kar_' + no).value;
	kodeOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tpeLmbr = document.getElementById('tpLembur_' + no).options[document.getElementById('tpLembur_' + no).selectedIndex].value;
	tanggal = document.getElementById('tglAbsen').value;
	tahun = tanggal.substr(6, 4);
	param = 'basisJam=' + basis + '&proses=getUang' + '&krywnId=' + idKry + '&kodeOrg=' + kodeOrg + '&tpLmbr=' + tpeLmbr + '&tahun=' + tahun + '&tgl=' + tanggal;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('uang_lbh_' + no).value = con.responseText;
					document.getElementById('jam_mulai_' + no).value = '00:00';
					document.getElementById('jam_selesai_' + no).value = '00:00';
					updtjamulang(no);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updtjamulang(no) {

	Jam = document.getElementById('jamlmbr_' + no).value;
	jammulai = document.getElementById('jam_mulai_' + no).value;

	param = 'Jam=' + Jam + '&proses=updtjam';
	param += "&jammulai=" + jammulai;
	tujuan = 'sdm_slave_splembur.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('jam_selesai_' + no).value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(notransaksi, kdorg, tgl) {
	param = 'kdorg=' + kdorg + '&notransaksi=' + notransaksi + '&tgl=' + tgl + '&proses=ajukan';
	tujuan = 'sdm_slave_splembur.php';
	if (confirm('Apakah anda yakin ini mengajukan SPL ini ? '))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}