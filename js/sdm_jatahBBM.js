/**
 * @author repindra.ginting
 */
function getrupiah(){
	harga = document.getElementById("harga").value;
	jlhbbm = document.getElementById("jlhbbm").value;
	rupiah = parseFloat(harga)*parseFloat(jlhbbm);
	document.getElementById('totalharga').value=rupiah;
	
}
 function saveJatah(karid) {
	val = document.getElementById(karid).value;
	if (val == '')
		alert('Value is empty');
	else if (val == 0)
		alert('Value is 0');
	else {
		param = 'val=' + val + '&karyawanid=' + karid;
		tujuan = 'sdm_slaveSaveJatahBBM.php';
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
					document.getElementById(karid).style.backgroundColor = '#E8F4F4';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getNotransaksi(periode) {
	param = 'periode=' + periode;
	tujuan = 'sdm_slave_getBBMNumber.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('notransaksi').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveBBM() {
	periode = document.getElementById('periode');
	karyawanid = document.getElementById('karyawanid');
	pt = document.getElementById('pt');
	periode = periode.options[periode.selectedIndex].value;
	karyawanid = karyawanid.options[karyawanid.selectedIndex].value;
	pt = pt.options[pt.selectedIndex].value;
	notransaksi = document.getElementById('notransaksi').value;
	keterangan = document.getElementById('keterangan').value;
	bytransport = remove_comma(document.getElementById('bytransport'));
	byperawatan = remove_comma(document.getElementById('byperawatan'));
	bytoll = remove_comma(document.getElementById('bytoll'));
	harga = remove_comma(document.getElementById('harga'));
	bylain = remove_comma(document.getElementById('bylain'));
	total = remove_comma(document.getElementById('total'));
	method = document.getElementById('method').value;
	//=====================================
	if (periode == '' || notransaksi == '' || harga=='') {
		alert('Transaction number is obligatory');
	}
	//else if(total=='' || parseFloat(total)==0.00)
	//{
	//alert('Please Enter Cost');
	//}
	else {
		param = 'periode=' + periode + '&karyawanid=' + karyawanid + '&pt=' + pt;
		param += '&notransaksi=' + notransaksi + '&keterangan=' + keterangan;
		param += '&bytransport=' + bytransport + '&byperawatan=' + byperawatan;
		param += '&bytoll=' + bytoll + '&bylain=' + bylain + '&total=' + total;
		param += '&method=' + method;
		if (confirm('Saving..?')) {
			tujuan = 'sdm_slave_penggantianBBM.php';
			post_response_text(tujuan, param, respog);
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
					document.getElementById('container').innerHTML = con.responseText;
					document.getElementById('savebtn').disabled = true;
					document.getElementById('periode').disabled = true;
					loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatadetail(notransaksi) {
	method = "loaddetail";
	param = 'notransaksi=' + notransaksi;
	param += '&method=' + method;
	tujuan = 'sdm_slave_saveJlhBBM.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('containerSolar').innerHTML = con.responseText;
					document.getElementById('savebtn').disabled = true;
					document.getElementById('periode').disabled = true;
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteBBM(notransaksi) {
	periode = document.getElementById('periox').options[document.getElementById('periox').selectedIndex].value;
	param = 'method=delete&notransaksi=' + notransaksi + '&periode=' + periode;
	if (confirm('Deleting ' + notransaksi + ', are you sure..?')) {
		tujuan = 'sdm_slave_penggantianBBM.php';
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
					document.getElementById('savebtn').disabled = true;
					document.getElementById('periode').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function previewBBM(notransaksi, ev) {
	tujuan = 'sdm_laporanPenggantianTransport_pdf.php';
	title = 'Report PDF';
	param = tujuan + '?notransaksi=' + notransaksi;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + param + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
function previewBBMPeriode(ev) {
	periode = document.getElementById('periox').options[document.getElementById('periox').selectedIndex].value;
	tujuan = 'sdm_laporanPenggantianTransportPeriode_pdf.php';
	title = 'Report PDF';
	param = tujuan + '?periode=' + periode;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + param + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
function calculateTotal() {
	bytransport = remove_comma(document.getElementById('bytransport'));
	byperawatan = remove_comma(document.getElementById('byperawatan'));
	bytoll = remove_comma(document.getElementById('bytoll'));
	bylain = remove_comma(document.getElementById('bylain'));
	total = parseFloat(bytransport) + parseFloat(byperawatan) + parseFloat(bytoll) + parseFloat(bylain);
	document.getElementById('total').value = total;
	change_number(document.getElementById('total'));
}
function cancelBBM() {
	document.getElementById('notransaksi').value = '';
	document.getElementById('periode').value = '';
	document.getElementById('periode').disabled = false;
	document.getElementById('savebtn').disabled = false;
	document.getElementById('keterangan').value = '';
	document.getElementById('bytransport').value = 0;
	document.getElementById('byperawatan').value = 0;
	document.getElementById('bytoll').value = 0;
	document.getElementById('bylain').value = 0;
	document.getElementById('total').value = 0;
	//getNotransaksi(periode);
	document.getElementById('containerSolar').innerHTML = '';
}
function getData(periode) {
	param = 'periode=' + periode;
	tujuan = 'sdm_slave_penggantianBBM.php';
	post_response_text(tujuan, param, respog);
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
function saveLitre() {
	notransaksi = document.getElementById('notransaksi').value;
	tanggal = document.getElementById('tanggal').value;
	kmawal = document.getElementById('kmawal').value;
	kmakhir = document.getElementById('kmakhir').value;
	totalharga = document.getElementById('totalharga').value;
	jlhbbm = document.getElementById('jlhbbm').value;
	if (jlhbbm == '')
		jlhbbm = 0;
	if (totalharga == '')
		totalharga = 0;
	if (tanggal.length != 10 || jlhbbm == 0) {
		alert('Date,price and volume are obligatory');
	} else {
		param = 'notransaksi=' + notransaksi + '&tanggal=' + tanggal + '&jlhbbm=' + jlhbbm + '&kmawal=' + kmawal + '&kmakhir=' + kmakhir;
		param += '&method=insert&totalharga=' + totalharga;
		tujuan = 'sdm_slave_saveJlhBBM.php';
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
					curr_total = parseFloat(remove_comma_var(document.getElementById('total').value));
					totalharga = parseFloat(totalharga);
					document.getElementById('total').value = curr_total + totalharga;
					change_number(document.getElementById('total'));
					document.getElementById('containerSolar').innerHTML = con.responseText;
					document.getElementById('tanggal').value = '';
					document.getElementById('jlhbbm').value = 0;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteSolar(notransaksi, tanggal, idcell) {
	param = 'method=delete&notransaksi=' + notransaksi + '&tanggal=' + tanggal;
	nilaicell = parseFloat(remove_comma_var(document.getElementById(idcell).innerHTML));
	tujuan = 'sdm_slave_saveJlhBBM.php';
	if (confirm('Deleting are you sure..?')) {
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
					curr_total = parseFloat(remove_comma_var(document.getElementById('total').value));
					document.getElementById('total').value = curr_total - nilaicell;
					change_number(document.getElementById('total'));
					document.getElementById('containerSolar').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveBBMClaim(no, notransaksi) {
	bayar = remove_comma(document.getElementById('bayar' + no));
	tglbayar = remove_comma(document.getElementById('tglbayar' + no));
	if (notransaksi == '' || bayar == '' || tglbayar.length != 10) {
		alert('Data incomplete');
	} else if (bayar == 0.00) {
		alert('Payment can not be 0');
	} else {
		param = 'notransaksi=' + notransaksi + '&bayar=' + bayar + '&tglbayar=' + tglbayar;
		tujuan = 'sdm_simpanPembayaranBBM.php';
		var conf = confirm('Saving payment ' + notransaksi + ', Are you sure..?');
		if (conf == true) {
			post_response_text(tujuan, param, respog);
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					document.getElementById('bayar' + no).style.backgroundColor = 'red';
					alert(con.responseText);
				} else {
					document.getElementById('bayar' + no).style.backgroundColor = '#C3DAF9';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editBBM(notransaksi, periode, alokasi, namakaryawan, totalklaim, bbm, keterangan) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('periode').value = periode;
	document.getElementById('pt').value = alokasi;
	document.getElementById('karyawanid').value = namakaryawan;
	//document.getElementById('totalklaim').value = totalklaim;
	//document.getElementById('bbm').value = bbm;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('method').value = 'update';
	// document.getElementById('div').value = div;
	// document.getElementById('tgl').value = tgl;
	// document.getElementById('listData').style.display = 'none';
	// document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	//detail(div, tgl);
	//param='notransaksi='+notransaksi,'periode='+periode,'alokasi='+alokasi,'namakaryawan='+namakaryawan,'totalklaim='+totalklaim,'bbm='+bbm,'keterangan='+keterangan;
	//tujuan='sdm_slave_editBBM.php';
	//alert(periode);
	tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 2, 'skyblue'); // MEMBUKA TAB FORM
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
		alert("warning : Silahkan isikan detail transaksi terlebih dahulu !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "sdm_slave_uploader.php?method=submitfile", true);
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
					document.getElementById('btnsubmit').disabled=false;
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
	tujuan = 'sdm_slave_uploader.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('containerupload') !== null) {
						document.getElementById('containerupload').innerHTML = con.responseText;
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
	tujuan = 'sdm_slave_uploader.php';
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
