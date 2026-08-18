/**
 * @author repindra.ginting
 */
function setSloc(x) {
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;

	//set value display periode
	tglstart = document.getElementById(gudang + '_start').value;
	tglend = document.getElementById(gudang + '_end').value;
	tglstart = tglstart.substr(6, 2) + "-" + tglstart.substr(4, 2) + "-" + tglstart.substr(0, 4);
	tglend = tglend.substr(6, 2) + "-" + tglend.substr(4, 2) + "-" + tglend.substr(0, 4);
	document.getElementById('displayperiod').innerHTML = tglstart + " - " + tglend;
	if (gudang != '') {
		if (x == 'simpan') {
			document.getElementById('sloc').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			tujuan = 'log_slave_getUnposted.php';
			param = 'gudang=' + gudang;
			post_response_text(tujuan, param, respog);
		} else {
			document.getElementById('sloc').disabled = false;
			// document.getElementById('sloc').options[0].selected = true;
			document.getElementById('btnsloc').disabled = false;
			document.getElementById('unconfirmaedlist').innerHTML = "";
			setValue2('sloc',null);
		}

	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('unconfirmaedlist').innerHTML = con.responseText;
					getDocumentList(gudang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getDocumentList(gudang) {
	param = 'gudang=' + gudang;
	tujuan = 'log_slave_getDaftarDokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariUnconfirmed(num) {
	tex = trim(document.getElementById('txtunpost').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (gudang == '') {
		alert('Storage Location  is obligatory')
	} else {
		param = 'gudang=' + gudang;
		param += '&page=' + num;
		if (tex != '')
			param += '&tex=' + tex;
		tujuan = 'log_slave_getUnposted.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unconfirmaedlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariDokumen(num) {
	tex = trim(document.getElementById('txtbabp').value);
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	if (gudang == '') {
		alert('Storage Location  is obligatory')
	} else {
		param = 'gudang=' + gudang;
		param += '&page=' + num;
		if (tex != '')
			param += '&tex=' + tex;
		tujuan = 'log_slave_getDaftarDokumen.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewDocument(tipe, notransaksi, ev) {
	param = 'notransaksi=' + notransaksi;
	switch (tipe) {
	case 1:
		tujuan = 'log_slave_print_bapb_pdf.php?' + param;
		break;
	case 2:
		tujuan = 'log_slave_print_retur_pdf.php?' + param;
		break;
	case 3:
		tujuan = 'log_slave_print_received_pdf.php?' + param;
		break;
	case 5:
		tujuan = 'log_slave_print_bast_pdf.php?' + param;
		break;
	case 7:
		tujuan = 'log_slave_print_mutasi_pdf.php?' + param;
		break;
	default:
		alert("Unknown document type");
	}

	//display window
	title = notransaksi;
	width = '900';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog2(title, content, width, height, ev);
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');	
}

function previewPosting(tipe, notransaksi, gudang, ev) {
	param = 'notransaksi=' + notransaksi + '&tipe=' + tipe + '&gudang=' + gudang;
	tujuan = 'log_slave_posting_gudang.php';
	//if (confirm('Posting ' + notransaksi + ', Are you sure..?')) {
	post_response_text(tujuan, param, respog);
	// lockScreen('wait');
	//}
	function respog() {
		if (con.readyState == 4) {
			// unlockScreen();
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					title = notransaksi;
					width = 'auto';
					height = 'auto';
					content = con.responseText;
					//alert(content);
					//showDialog1(title, content, width, height, ev);
					alertify.popup().destroy();
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prosesPosting(maxRow, tipetrx, notransaksi) {
	if (confirm('Are you sure?')) {
		doPostingmaterial(maxRow, tipetrx, 1, notransaksi);
	} else {
		alertify.popup().destroy();
		closeDialog();
	}
}

function doPostingmaterial(maxRow, tipetrx, currentRow, notransaksi) {
	tipetransaksi = tipetrx;
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	try {
		tanggal = trim(document.getElementById('tanggal' + currentRow).innerHTML);
	} catch (e) {
		if (currentRow == 1) {
			setPosting(gudang, notransaksi, 1); //tidak ada detail transaksi, bisa saja karena sudah diposting, tetapi flag header belum terupdate
		}
	}
	kodebarang = trim(document.getElementById('kodebarang' + currentRow).innerHTML);
	satuan = trim(document.getElementById('satuan' + currentRow).innerHTML);
	jumlah = trim(document.getElementById('jumlah' + currentRow).innerHTML);
	kodept = trim(document.getElementById('kodept' + currentRow).innerHTML);
	try {
		kodeblok = trim(document.getElementById('kodeblok' + currentRow).innerHTML);
	} catch (e) {
		kodeblok = '';
	}
	gudangx = '';
	untukunit = '';
	untukpt = '';
	supplier = '';
	nopo = '';
	nopp = '';
	hargasatuan = '0';
	kodekegiatan = '';
	kodemesin = '';
	switch (tipetrx) {
	case '3':
		gudangx = trim(document.getElementById('gudangx' + currentRow).innerHTML);
		hargasatuan = trim(document.getElementById('hargasatuan' + currentRow).innerHTML);
		break;
	case '5':
		kodesegment = trim(document.getElementById('kodesegment' + currentRow).innerHTML);
		untukpt = trim(document.getElementById('untukpt' + currentRow).innerHTML);
		untukunit = trim(document.getElementById('untukunit' + currentRow).innerHTML);
		kodekegiatan = trim(document.getElementById('kodekegiatan' + currentRow).innerHTML);
		kodemesin = trim(document.getElementById('kodemesin' + currentRow).innerHTML);
		break;
	case '2':
		untukunit = trim(document.getElementById('untukunit' + currentRow).innerHTML);
		kodekegiatan = trim(document.getElementById('kodekegiatan' + currentRow).innerHTML);
		kodemesin = trim(document.getElementById('kodemesin' + currentRow).innerHTML);
		break;
	case '7':
		gudangx = trim(document.getElementById('gudangx' + currentRow).innerHTML);
		break;
	case '1':
		supplier = trim(document.getElementById('supplier' + currentRow).innerHTML);
		nopo = trim(document.getElementById('nopo' + currentRow).innerHTML);
		hargasatuan = trim(document.getElementById('hargasatuan' + currentRow).innerHTML);
		nopp = trim(document.getElementById('nopp' + currentRow).innerHTML);
		break;
	case '6':
		hargasatuan = trim(document.getElementById('hargasatuan' + currentRow).innerHTML);
		supplier = trim(document.getElementById('supplier' + currentRow).innerHTML);
		nopo = trim(document.getElementById('nopo' + currentRow).innerHTML);
		break;
	case '0':
		kdpabrikasi = trim(document.getElementById('kdpabrikasi' + currentRow).innerHTML);
		hargasatuan = trim(document.getElementById('hargasatuan' + currentRow).innerHTML);
		break;
	}
	//periksa tanggal=====================================================================
	gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
	x = tanggal;
	_start = document.getElementById(gudang + '_start').value;
	_end = document.getElementById(gudang + '_end').value;
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("-", "");
	}
	while (x.lastIndexOf("-") > -1) {
		x = x.replace("/", "");
	}

	curdateY = x.substr(4, 4).toString();
	curdateM = x.substr(2, 2).toString();
	curdateD = x.substr(0, 2).toString();
	curdate = curdateY + curdateM + curdateD;
	curdate = parseInt(curdate);
	if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		alert('Date out of range')
	} else {
		//====================================================================================
		if ((tipetransaksi == 3 || tipetransaksi == 7) && gudangx == '') {
			alert('Data component (Source or Destination) is missing');
		} else if (tipetransaksi == 5 && untukpt == '') {
			alert('Data component (Destination Company) is missing');
		} else {
			document.getElementById('row' + currentRow).style.backgroundColor = 'orange';
			param = 'tipetransaksi=' + tipetransaksi + '&tanggal=' + tanggal;
			param += '&kodebarang=' + kodebarang + '&satuan=' + satuan + '&jumlah=' + jumlah;
			param += '&kodept=' + kodept + '&gudangx=' + gudangx + '&untukpt=' + untukpt;
			param += '&gudang=' + gudang + '&kodeblok=' + kodeblok + '&notransaksi=' + notransaksi;
			param += '&nopo=' + nopo + '&supplier=' + supplier + '&hargasatuan=' + hargasatuan + '&untukunit=' + untukunit;
			param += '&kodekegiatan=' + kodekegiatan + '&kodemesin=' + kodemesin+ '&nopp=' + nopp;
			if (tipetransaksi == 0) {
				param += '&kdpabrikasi=' + kdpabrikasi;
			}
			tujuan = 'log_slave_savePosting.php';
			post_response_text(tujuan, param, respog);
			// lockScreen('wait');
		}
	}
	function respog() {
		if (con.readyState == 4) {

			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currentRow).style.backgroundColor = 'red';
					// unlockScreen();
				} else {
					document.getElementById('row' + currentRow).style.backgroundColor = 'green';
					currentRow += 1;
					if (currentRow > maxRow) {
						setPosting(gudang, notransaksi, 1); //beri flag 1 pada kolom post
					} else {
						doPostingmaterial(maxRow, tipetrx, currentRow, notransaksi);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
				// unlockScreen();
			}
		}
	}
}

function setPosting(gudang, notransaksi, status) {
	param = 'notransaksi=' + notransaksi + '&status=' + status + '&gudang=' + gudang;
	tujuan = 'log_slave_ubahFlagPosting.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {

			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('indukrow' + currentRow).style.backgroundColor = 'red';
					// unlockScreen();
				} else {
					setSloc('simpan');
					// unlockScreen();
					alert('Done');
					alertify.popup().destroy();
					// closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
				// unlockScreen();
			}
		}
	}

}
function perbaikisaldobulanan($x='1', barang, tanggal, gudang) {
	param = 'tanggal=' + tanggal + '&gudang=' + gudang + '&barang=' + barang+'&perbaiki=1';
	tujuan = 'log_slave_ubahFlagPosting.php';
	if (confirm('Apakah Anda Yakin Ingin Memperbaiki Saldo Barang ' + barang + ' ?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('indukrow' + currentRow).style.backgroundColor = 'red';
					// unlockScreen();
				} else {
					setSloc('simpan');
					alert('Done, Silahkan Lakukan Posting Ulang');
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}