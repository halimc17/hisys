function gethargabaris(no) {
	// komoditi=document.getElementById('komoditi');
	// komoditi=komoditi.options[komoditi.selectedIndex].value;
	if (document.getElementById('hargatbs_' + no).disabled == false) {
		hargatbs = document.getElementById('hargatbs_' + no).value;
		netto = document.getElementById('beratbersih_' + no).value;
		netto = remove_comma_var(netto);
		totHarga = parseFloat(netto) * parseFloat(hargatbs);
		document.getElementById('totharga_' + no).innerHTML = totHarga;
	}
}

function list(tpDisplay, ev) {

	if (getValue('pabrik') == 'EXTM') {
		if (getValue('komoditi') == '0') {
			alert('komoditi Tidak Boleh Kosong');
			return;
		}
		if (getValue('komoditi') == '40000003') {
			hrgdt = document.getElementById('hargaall').value;
			if ((hrgdt == '') || (parseInt(hrgdt) == 0)) {
				alert("Harga harus terisi");
				return;
			}
		}
	}

	if (tpDisplay == 'preview') {
		var param = 'tanggal1=' + getValue('tanggal_from') + '&tanggal2=' + getValue('tanggal_until') + '&pabrik=' + getValue('pabrik') + '&komoditi=' + getValue('komoditi') + '&status=' + getValue('status') + '&tpDisplay=' + tpDisplay,
		tujuan = 'keu_slave_pengakuanjual.php?proses=list';
		param += "&nokontrak=" + getValue('nokontrak') + "&kdpt=" + getValue('kdpt');
		post_response_text(tujuan, param, respog);
	}

	if (tpDisplay == 'excel') {
		tujuan = 'keu_slave_pengakuanjual.php';
		var param = 'tanggal1=' + getValue('tanggal_from') + '&tanggal2=' + getValue('tanggal_until') + '&pabrik=' + getValue('pabrik') + '&komoditi=' + getValue('komoditi') + '&status=' + getValue('status') + '&tpDisplay=' + tpDisplay;
		param += "&nokontrak=" + getValue('nokontrak') + "&kdpt=" + getValue('kdpt') + "&proses=list";
		judul = 'Report Ms.Excel';
		printFile(param, tujuan, judul, ev);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (tpDisplay == 'preview') {
						getById('containerList').innerHTML = con.responseText;
						if (getValue('komoditi') == '40000003') {
							getHarga();
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showDetail(noTiket, millCode, ev) {
	title = "Detail Nokontrak";
	content = "<fieldset><legend>" + noTiket + "</legend><div id=contDetail style='overflow:auto; width:380px; height:100%;' ></div></fieldset>";
	width = '400px';
	height = '';
	showDialog1(title, content, width, height, ev);
}

function getFormKgBeli(noTiket, millCode, ev) {
	var isidt = noTiket.split("##");
	var dataTiket = "Tanggal : " + getValue('tanggal_' + noTiket) + ",No Kontark :" + isidt[1];
	showDetail(dataTiket, millCode, ev);
	param = 'notransaksi=' + noTiket + '&millcode=' + millCode;
	tujuan = 'keu_slave_pengakuanjual.php?proses=getFormKgBeli';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getById('contDetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function saveKgPembeli(rowdt) {
	var listdata;
	for (dtawal = 1; dtawal <= rowdt; dtawal++) {
		if (dtawal == 1) {
			listdata = '&notiket[]=' + getValue('notiket_' + dtawal);
			listdata += '&tglPembeli[]=' + getValue('tanggal_' + dtawal);
			listdata += '&kgPembeli[]=' + getValue('kgPembeli_' + dtawal);
		} else {
			listdata += '&notiket[]=' + getValue('notiket_' + dtawal);
			listdata += '&tglPembeli[]=' + getValue('tanggal_' + dtawal);
			listdata += '&kgPembeli[]=' + getValue('kgPembeli_' + dtawal);
		}

	}
	param = 'totRow=' + rowdt;
	param += listdata;
	tujuan = 'keu_slave_pengakuanjual.php?proses=updtAll';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					list();
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function pilKontrak(obj, noTiket, millCode, rw, noke, nosipb, event) {
	if (rw == 0) {
		post(obj, noTiket, millCode, rw, noke, nosipb); //jika tidak ada detail dari nokontrak langsung terposting
	} else {
		showDetail(noTiket, millCode, event); //memunculkan pilihan kontrak
		var param = 'proses=getForm';
		param += '&notiket=' + noTiket + '&millcode=' + millCode + '&tanggal=' + getValue('tanggal_' + noTiket) + '&obc=' + obj + '&rw=' + rw,
		tujuan = 'keu_slave_pengakuanjual.php?proses=pilKontrak';
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						getById('contDetail').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}

function post(obj, noTiket, millCode, rw, noke, nosipb) {
	var param = 'tanggal=' + getValue('tanggal_' + noTiket) + '&millcode=' + millCode + '&nosipb=' + nosipb + '&rowKntrk=' + rw + '&nokontrak=' + getValue('nokontrak_' + noke) + '&notransaksi=' + getValue('notransaksi_' + noke) + '&kodebarang=' + getValue('kdBarang_' + noke),
	tujuan = 'keu_slave_pengakuanjual.php?proses=post';
	kdBrg = document.getElementById('kdBarang_' + noke);
	if (kdBrg.value == '40000003') {
		hrgtransport = getValue('hargatransportir_' + noke);
		hrgtbs = getValue('hargatbs_' + noke);
		brttbs = getValue('beratbersih_' + noke);
		param += '&hargasatuantbs=' + hrgtbs + '&beratbersihtbs=' + brttbs + '&hargatransportir=' + hrgtransport;
	} else {
		param += '&qty=' + getValue('qty_' + noke) + '&hrgsatuan=' + getValue('hrgsatuan_' + noke);
	}

	if ((getValue('tanggal_' + noTiket)) == '') {
		alert("Warning: Tanggal Pengakuan belum diisi");
		return;
	}
	if (rw != 0) {
		if (getValue('nokontrakDt') == '') {
			alert("Warning: No kontrak harus di pilih");
			return;
		}
		btn = document.getElementById('imgPost_' + noTiket);
		param += '&nokontrakDt=' + getValue('nokontrakDt');
	} else {
		btn = obj;
	}
	//alert(param);
	if (confirm("Anda akan melakukan pengakuan atas No. Kontrak : " + getValue('nokontrak_' + noke) + ", Tanggal : " + getValue('tanggal_' + noTiket) + "\nAnda yakin?"))
		post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					var fieldTgl = getById('tanggal_' + noTiket);
					fieldTgl.disabled = true;
					btn.removeAttribute('onclick');
					btn.setAttribute('src', 'images/skyblue/posted.png');
					if (rw != 0) {
						closeDialog();
					}
					// getById('containerList').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPtkntrk() {
	var param = 'tanggal1=' + getValue('tanggal_from') + '&tanggal2=' + getValue('tanggal_until') + '&pabrik=' + getValue('pabrik'),
	tujuan = 'keu_slave_pengakuanjual.php?proses=getPt';
	if ((getValue('tanggal_from') == '') || (getValue('tanggal_until') == '')) {
		alert("Warning: Tanggal harus lengkap!!");
		return;
	}
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					dataisi = con.responseText.split("####");
					getById('kdpt').innerHTML = dataisi[0];
					getById('komoditi').innerHTML = dataisi[1];
					document.getElementById('hargaall').disabled = true;
					document.getElementById('hargaall').value = '';
					if (getValue('pabrik') == 'EXTM') {
						document.getElementById('hargaall').disabled = false;
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getExcel(ev, tujuan, tpDisplay) {
	if (getValue('pabrik') == 'EXTM') {
		if (getValue('komoditi') == '0') {
			alert('komoditi Tidak Boleh Kosong');
			return;
		}
		if (getValue('komoditi') == '40000003') {
			hrgdt = document.getElementById('hargaall').value;
			if ((hrgdt == '') || (parseInt(hrgdt) == 0)) {
				alert("Harga harus terisi");
				return;
			}
		}
	}
	hrg = document.getElementById('hargaall').value;
	var param = 'tanggal1=' + getValue('tanggal_from') + '&tanggal2=' + getValue('tanggal_until') + '&pabrik=' + getValue('pabrik') + '&komoditi=' + getValue('komoditi');
	param += "&nokontrak=" + getValue('nokontrak') + "&kdpt=" + getValue('kdpt') + "&proses=listExcel" + "&hargasatuantbs=" + hrg + "&status=" + getValue('status');
	//list(tpDisplay,ev);
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev);
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '450';
	height = '450';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>";
	showDialog1(title, content, width, height, ev);
}
function getHarga() {
	komoditi = document.getElementById('komoditi');
	komoditi = komoditi.options[komoditi.selectedIndex].value;
	///document.getElementById('hargaall').value='';
	if (komoditi == '40000003') {
		document.getElementById('hargaall').disabled = false;
		putHarga();
	} else {
		document.getElementById('hargaall').disabled = true;
		if (document.getElementById('containerList').innerHTML != '') {
			list();
		}
	}
}
function putHarga() {
	var hargaall;
	var hargaall2;
	var netto;
	var totHarga;
	var ppn;
	hargaall = document.getElementById('hargaall').value;
	if (hargaall != '') {
		// var els = document.getElementById('containdata').getElementsByClassName('myinputtextnumber');
		rowdt = document.getElementById('els').value;
		// for(var i=1;i<=els.length;i++){
		for (var i = 1; i <= rowdt; i++) {
			ppn = document.getElementById('ppn_' + i).value;
			hargaall2 = hargaall;
			// if(ppn=='1'){
			//     hargaall2=parseFloat(hargaall)/1.1;
			// }
			if (document.getElementById('hargatbs_' + i).disabled == false) {
				document.getElementById('hargatbs_' + i).value = hargaall2;
				netto = document.getElementById('beratbersih_' + i).value;
				netto = remove_comma_var(netto);
				totHarga = parseFloat(netto) * parseFloat(hargaall2);
				document.getElementById('totharga_' + i).innerHTML = numberFormat(totHarga);
			}

		}
	}
}
function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}