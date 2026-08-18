function showalllist2(pg) {
	document.getElementById('txtsearch').value = '';
	document.getElementById('tgl_cari').selectedIndex = 0;
	document.getElementById('purId').selectedIndex = 0;
	document.getElementById('unitIdCr').selectedIndex = 0;
	document.getElementById('klmpkBrg').selectedIndex = 0;
	document.getElementById('kdBarangCari').selectedIndex = 0;
	document.getElementById('statPP').selectedIndex = 0;
	document.getElementById('jenis').selectedIndex = 0;
	loaddata(pg);
}

function getPagex() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(pg) {
	document.getElementById('contload').style.display = "block";
	document.getElementById('containtool').style.display = "none";
					
	crnopp = document.getElementById('txtsearch').value;
	crperiode = trim(document.getElementById('tgl_cari').options[document.getElementById('tgl_cari').selectedIndex].value);
	crpurchaser = trim(document.getElementById('purId').options[document.getElementById('purId').selectedIndex].value);
	crunit = trim(document.getElementById('unitIdCr').options[document.getElementById('unitIdCr').selectedIndex].value);
	crkelbarang = trim(document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value);
	crbarang = trim(document.getElementById('kdBarangCari').value);
	crstatus = trim(document.getElementById('statPP').options[document.getElementById('statPP').selectedIndex].value);
	crjenis = trim(document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value);
	crstrategis = trim(document.getElementById('crstrategis').options[document.getElementById('crstrategis').selectedIndex].value);

	//Umar
	crkontrak  = trim(document.getElementById('kontrakIdCr').options[document.getElementById('kontrakIdCr').selectedIndex].value);
	crkontraka = trim(document.getElementById('kontrakaIdCr').value);
	crkategori = trim(document.getElementById('crkategori').value);
	//End Umar

	param = 'method=loaddata&crnopp=' + crnopp + '&crperiode=' + crperiode + '&crpurchaser=' + crpurchaser + '&crunit=' + crunit + '&crkelbarang=' + crkelbarang + '&crbarang=' + crbarang + '&crstatus=' + crstatus + '&crjenis=' + crjenis + '&page=' + pg +'&crstrategis='+crstrategis;
	param += '&crkontrak=' + crkontrak;
	param += '&crkontraka=' + crkontraka;
	param += '&crkategori=' + crkategori;
	tujuan = 'log_slave_verivikasipp.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					closeDialog();
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function getDataPP(ppno, nourut) {
	title = "List Item PP";
	formListPP(title);
	nopp = ppno;

	param = 'method=listVerivikasiPP' + '&nopp=' + nopp + '&nourut=' + nourut;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					document.getElementById('saveAll').disabled = false;
					document.getElementById('purId2').disabled = false;
					document.getElementById('lokId').disabled = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formListPP(title, wdth, heig) {
	width = '';
	height = '';
	if (wdth != '') {
		width = wdth;
	}
	if (heig != '') {
		height = heig;
	}

	content = "<div id=container></div>";
	ev = 'event';
	showDialog4(title, content, width, height, ev);
}

function formReturn(nopp, kdbrg, nmbrag) {
	title = "Detail Data :" + nopp;
	var width = 380;
	var height = 180;
	formListPP(title, width, height);

	param = 'method=ReturnlistPP' + '&nopp=' + nopp;
	param += '&kdbrg=' + kdbrg + '&nmbrag=' + nmbrag;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadPPChat(nopp, kodebarang, ev) {
	title = "Chat:" + nopp + " - " + kodebarang;
	content = "<iframe frameborder=0 style='width:510px;height:290px;' src='log_slaveChatPP.php?nopp=" + nopp + "&kodebarang=" + kodebarang + "'></iframe>";
	width = '';
	height = '';
	showDialog2(title, content, width, height, ev);
}

function getSatuanKonversi(satuan, kdBrgSatuan, nourut) {
	jmlh_realisai = document.getElementById('realisasi_' + nourut).value;
	hjmlh_realisai = document.getElementById('hrealisasi_' + nourut).value;
	nopp = document.getElementById('nopp_' + nourut).innerHTML;
	param = 'method=getValueKonversi' + '&satuan=' + satuan + '&kdBrgSatuan=' + kdBrgSatuan + '&jmlh_realisai=' + jmlh_realisai + '&nourut=' + nourut + '&hjmlh_realisai=' + hjmlh_realisai + '&nopp=' + nopp;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hasil = con.responseText;
					document.getElementById('realisasi_' + nourut).value = hasil;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addpurchase(nopp, kodebarang, nourut) {
	purchaser = trim(document.getElementById('purchase_name_' + nourut).options[document.getElementById('purchase_name_' + nourut).selectedIndex].value);
	realisasi = document.getElementById('realisasi_' + nourut).value;
	param = 'purchaser=' + purchaser + '&nopp=' + nopp + '&kodebarang=' + kodebarang + '&nourut=' + nourut + '&realisasi=' + realisasi + '&method=addpurchase';

	post_response_text('log_slave_verivikasipp.php', param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadpurchaser(nopp, kodebarang, nourut);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addkontrak(nopp, kodebarang, nourut) {
	nokontrak = trim(document.getElementById('nokontrak_' + nourut).options[document.getElementById('nokontrak_' + nourut).selectedIndex].value);
	param = 'nokontrak='+nokontrak+'&nopp='+nopp+'&kodebarang='+kodebarang+'&nourut='+nourut+'&method=addkontrak';

	post_response_text('log_slave_verivikasipp.php', param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadpurchaser(nopp, kodebarang, nourut);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addpurchase2(nopp) {
	purchaser = trim(document.getElementById('purId2_2').options[document.getElementById('purId2_2').selectedIndex].value);
	param = 'purchaser=' + purchaser + '&nopp=' + nopp + '&method=addpurchase2';

	post_response_text('log_slave_verivikasipp.php', param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadpurchaser2(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadpurchaser2(nopp) {
	param = 'nopp=' + nopp + '&method=loadpurchaser2';
	post_response_text('log_slave_verivikasipp.php', param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listpurchaser2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadpurchaser(nopp, kodebarang, nourut){
	maxbaris = 0;
	row = document.getElementsByName('nopp[]');
	kode = document.getElementsByName('kodebarang[]');
	for(i=0; i < row.length; i++){
		pp = row[i].innerHTML;
		if(pp==nopp){
			maxbaris = parseFloat(maxbaris) + parseFloat(1);
			document.getElementById('tr_'+(i+1)).style.backgroundColor="#c1f7f7";
			document.getElementById('flag'+(i+1)).value="1";
		}else{
			document.getElementById('tr_'+(i+1)).style.backgroundColor="";
		}
	}
	
	bariske=1;
	loadpurchaserLoop(nopp, kodebarang, nourut, bariske, maxbaris);
}

function loadpurchaserLoop(nopp, kodebarang, nourut, bariske, maxbaris) {
	if(bariske!=1){		
		row = document.getElementsByName('nopp[]');
		kode = document.getElementsByName('kodebarang[]');
		flag = document.getElementsByName('flag[]');
		nomor = document.getElementsByName('nomorurut[]');
		for(i=0; i < row.length; i++){
			pp = row[i].innerHTML;
			if(pp==nopp){
				flg = flag[i].value;
				if(flg==1){
					kodebarang = kode[i].innerHTML;
					nourut = nomor[i].value;
				}
			}
		}
	}
	
	param = 'nopp=' + nopp + '&kodebarang=' + kodebarang + '&nourut=' + nourut + '&method=loadpurchaser';
	post_response_text('log_slave_verivikasipp.php', param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listpurchaser_' + nourut).innerHTML = con.responseText;
					document.getElementById('tmbldrop'+nourut).style.display="none";
					document.getElementById('flag'+nourut).value="";
					if(bariske <= maxbaris){
						bariske = parseFloat(bariske) + parseFloat(1);
						loadpurchaserLoop(nopp, kodebarang, nourut, bariske, maxbaris);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletepurchaser(nopp, kodebarang, karyawanid, nourut) {
	param = 'nopp=' + nopp + '&kodebarang=' + kodebarang + '&purchaser=' + karyawanid + '&nourut=' + nourut + '&method=deletepurchaser';

	if (confirm('Are you sure delete this item?')) {
		post_response_text('log_slave_verivikasipp.php', param, respon);
	}

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadpurchaser(nopp, kodebarang, nourut);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletepurchaser2(nopp, karyawanid) {
	param = 'nopp=' + nopp + '&purchaser=' + karyawanid + '&method=deletepurchaser2';

	if (confirm('Are you sure delete this item?')) {
		post_response_text('log_slave_verivikasipp.php', param, respon);
	}

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadpurchaser2(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getBarangCari() {
	klmpKbrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
	param = 'method=loadBarang' + '&klmpKbrg=' + klmpKbrg;
	tujuan = 'log_slave_verivikasipp.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kdBarangCari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function displayTools() {
	param = 'method=loadTools';
	tujuan = 'log_slave_verivikasipp.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contload').style.display = "none";
					document.getElementById('containtool').style.display = "block";
					document.getElementById('containtool').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailPo(x) {
	kodeorg = document.getElementById('kodeOrg_' + x).innerHTML;
	param = 'method=loadPPDetail' + '&kodeorg=' + kodeorg + '&brsKe=' + x;
	tujuan = 'log_slave_verivikasipp.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('dataPO_' + x).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function closeList(b) {
	document.getElementById('dataPO_' + b).innerHTML = '';
}

function getDataPP2(ppno) {
	formListPP(ppno);
	nopp = ppno;

	param = 'method=listVerivikasiPP2' + '&nopp=' + nopp;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					document.getElementById('saveAll2').disabled = false;
					document.getElementById('purId2_2').disabled = false;
					document.getElementById('lokId_2').disabled = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	closeDialog4();
}

function balikin(nopp, kdbrg) {
	ket = document.getElementById('ket').value;
	pg = parseFloat(trim(document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value)) - 1;
	if (ket == '') {
		alert("Keterangan Harus Di Isi");
		return;
	}

	tujuan = 'log_slave_verivikasipp.php';
	param = "nopp=" + nopp + '&ket=' + ket + "&method=balikin" + "&kodebarang=" + kdbrg;

	if (confirm("Anda yakin akan reject PR "+nopp+" ??"))
		post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					closeDialog4();
					loaddata(pg);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savepilih(totRow) {
	nopp = document.getElementById('ppno').value;
	pg = parseFloat(trim(document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value)) - 1;

	var allData = '';
	var countcheck = 0;
	for (dwc = 1; dwc <= totRow; dwc++) {
		if (document.getElementById('pilih_' + dwc).checked == true) {
			allData += "&kdBrg[" + dwc + "]=" + document.getElementById('kdbrg_' + dwc).value;
			allData += "&realisasi[" + dwc + "]=" + document.getElementById('jmlh_2_' + dwc).innerHTML;
			allData += "&pilih[" + dwc + "]=1";
			countcheck = countcheck + 1;
		}
	}

	if (countcheck <= 0) {
		alert("Checked box obligatory.");
		return false;
	}

	param = 'method=savepilih' + '&nopp=' + nopp + '&totRow=' + totRow;
	param += allData;
	tujuan = 'log_slave_verivikasipp.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Saved');
					closeDialog4();
					loaddata(pg);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveSemua2(x) {
	pg = parseFloat(trim(document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value)) - 1;
	document.getElementById('saveAll2').disabled = true;
	document.getElementById('purId2_2').disabled = true;
	document.getElementById('lokId_2').disabled = true;
	nopp = document.getElementById('ppno').value;
	purchaser = document.getElementById('purId2_2').options[document.getElementById('purId2_2').selectedIndex].value;
	lokal = document.getElementById('lokId_2').options[document.getElementById('lokId_2').selectedIndex].value;
	totlBrg = document.getElementById('totalBrg_2').innerHTML;
	kd_brg = document.getElementById('kdBrg_2_' + x).innerHTML;
	jmlh_realisai = document.getElementById('jmlh_2_' + x).innerHTML;
	param = 'method=insertPurchaser' + '&nopp=' + nopp + '&purchase=' + purchaser + '&lokal=' + lokal;
	param += '&kodebarang=' + kd_brg + '&realisasi=' + jmlh_realisai;

	tujuan = 'log_slave_verivikasipp.php';
	if (x == 1 && confirm('Proceed?')) {
		post_response_text(tujuan, param, respog);
	} else {
		post_response_text(tujuan, param, respog);
	}
	document.getElementById('rew_' + x).style.backgroundColor = 'orange';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rew_' + x).style.backgroundColor = 'red';
				} else {
					b = x;
					row = x + 1;
					x = row;

					if (x <= totlBrg) {
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						saveSemua2(x);
					} else {
						alert('Saved');
						closeDialog4();
						loaddata(pg);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function displaySummary() {
	//summForm();
	param = 'method=getSummary';
	tujuan = 'log_slave_save_verivikasi.php'

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					//document.getElementById('container').innerHTML = con.responseText;
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();

					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function summForm() {
	//closeDialog();
	width = '';
	height = '';
	content = "<div id=container style='overflow:auto;max-width:800px;max-height:350px;'></div>";
	ev = 'event';
	title = "Summary";
	showDialog2(title, content, width, height, ev);
}

function AddPur(id) {
	nopp = document.getElementById('nopp_' + id).innerHTML;
	kdbrg = document.getElementById('kd_brg_' + id).innerHTML;
	satuan = document.getElementById('satuan_' + id).options[document.getElementById('satuan_' + id).selectedIndex].value;
	purchase = document.getElementById('purchase_name_' + id).options[document.getElementById('purchase_name_' + id).selectedIndex].value;
	jmlh_realisai = document.getElementById('realisasi_' + id).value;
	met = document.getElementById('method').value;
	met = 'insert_detail_pp';
	document.getElementById('lokalpusat_' + id);
	lokal = document.getElementById('lokalpusat_' + id);
	if (lokal.checked == true) {
		lokal.value = 1;
	} else {
		lokal.value = 0;
	}
	param = 'nopp=' + nopp + '&kdbrg=' + kdbrg + '&purchase=' + purchase + '&jmlh_realisai=' + jmlh_realisai + '&satuan=' + satuan;
	param += '&method=' + met + '&lokal=' + lokal.value;
	tujuan = 'log_slave_save_verivikasi.php';

	//alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					document.getElementById('lokalpusat_' + id).disabled = true;
					document.getElementById('purchase_name_' + id).disabled = true;
					document.getElementById('realisasi_' + id).disabled = true;
					document.getElementById('satuan_' + id).disabled = true;

					if (purchase != '') {
						document.getElementById('balikinbutton_' + id).disabled = true;
					}
					document.getElementById('contain').value = con.responseText;
					//alert(con.reponseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function EditPur(id) {
	a = confirm('Are you sure want to edit');
	if (a) {
		document.getElementById('lokalpusat_' + id).disabled = false;
		document.getElementById('purchase_name_' + id).disabled = false;
		document.getElementById('realisasi_' + id).disabled = false;
		document.getElementById('satuan_' + id).disabled = false;
		document.getElementById('balikinbutton_' + id).disabled = false;
	} else {
		return;
	}

}
function searchBrg(id, title, content, ev) {
	width = '700';
	height = '400';
	showDialog1(title, content, width, height, ev);
	//alert('asdasd');
}
function findBrg() {
	txt = trim(document.getElementById('no_brg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&method=cariBarang';
		tujuan = 'log_slave_save_verivikasi.php';
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
function setBrg(kdBrng, num) {
	nmr = document.getElementById('nomor').value;
	notrans_ = document.getElementById('notrans_' + nmr).value;
	kdbrg = document.getElementById('kdbrg_' + nmr).value;
	keteranganubah = document.getElementById('keteranganubah' + num).value;
	kdBrang = kdBrng;
	tujuan = 'log_slave_save_verivikasi.php';
	param = "nopp=" + notrans_ + "&kdbrg=" + kdbrg + "&method=updateDtbarang" + "&kdBrgBaru=" + kdBrang + "&keteranganubah=" + keteranganubah;
	//alert(param);
	if (confirm("Anda yakin mengubah barang ini"))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					displayList();
					closeDialog();

					//document.getElementById('container').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function EditBrg(id) {
	searchBrg(id);
}
function cariNopp() {
	txtSearch = trim(document.getElementById('txtsearch').value);
	tglCari = trim(document.getElementById('tgl_cari').value);
	pur = document.getElementById('purId').options[document.getElementById('purId').selectedIndex].value;
	unitIdCr = document.getElementById('unitIdCr').options[document.getElementById('unitIdCr').selectedIndex].value;
	klmpKbrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
	kdBarangCari = document.getElementById('kdBarangCari').options[document.getElementById('kdBarangCari').selectedIndex].value;
	stat = document.getElementById('statPP').options[document.getElementById('statPP').selectedIndex].value;
	jenis = document.getElementById('jenis').value;
	met = document.getElementById('method');
	met = met.value = 'cari_pp';
	met = trim(met);
	param = 'txtSearch=' + txtSearch + '&tglCari=' + tglCari + '&method=' + met + '&userid=' + pur + '&unitIdCr=' + unitIdCr + '&klmpKbrg=' + klmpKbrg;
	param += '&kdBarangCari=' + kdBarangCari + '&statPP=' + stat + '&jenis=' + jenis;

	tujuan = 'log_slave_save_verivikasi.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contain').innerHTML = con.responseText;
					closeDialog4();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function cariBast(num) {
	txtSearch = trim(document.getElementById('txtsearch').value);
	tglCari = trim(document.getElementById('tgl_cari').value);
	pur = document.getElementById('purId').options[document.getElementById('purId').selectedIndex].value;
	unitIdCr = document.getElementById('unitIdCr').options[document.getElementById('unitIdCr').selectedIndex].value;
	klmpKbrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
	kdBarangCari = document.getElementById('kdBarangCari').options[document.getElementById('kdBarangCari').selectedIndex].value;
	stat = document.getElementById('statPP').options[document.getElementById('statPP').selectedIndex].value;
	met = document.getElementById('method');
	met = met.value = 'cari_pp';
	met = trim(met);
	param = 'txtSearch=' + txtSearch + '&tglCari=' + tglCari + '&method=' + met + '&userid=' + pur + '&unitIdCr=' + unitIdCr + '&klmpKbrg=' + klmpKbrg;
	param += '&kdBarangCari=' + kdBarangCari + '&statPP=' + stat;

	//param='method=refresh_data';
	param += '&page=' + num;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariData(num) {

	param = 'method=refresh_data';
	param += '&page=' + num;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					closeDialog();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '';
	height = '';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}

function dataKeExcel(ev) {
	txtSearch = trim(document.getElementById('txtsearch').value);
	tglCari = trim(document.getElementById('tgl_cari').value);
	pur = document.getElementById('purId').options[document.getElementById('purId').selectedIndex].value;
	unitIdCr = document.getElementById('unitIdCr').options[document.getElementById('unitIdCr').selectedIndex].value;
	klmpKbrg = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
	kdBarangCari = document.getElementById('kdBarangCari').options[document.getElementById('kdBarangCari').selectedIndex].value;
	stat = document.getElementById('statPP').options[document.getElementById('statPP').selectedIndex].value;
	met = document.getElementById('method');
	met = met.value = 'excelData';
	met = trim(met);
	param = 'txtSearch=' + txtSearch + '&tglCari=' + tglCari + '&method=' + met + '&userid=' + pur + '&unitIdCr=' + unitIdCr + '&klmpKbrg=' + klmpKbrg;
	param += '&kdBarangCari=' + kdBarangCari + '&statPP=' + stat;

	tujuan = 'log_slave_save_verivikasi.php';
	//alert(param);
	//param='nopp='+nopp+'&tglSdt='+tglSdt+'&statPP='+statPP;
	judul = 'PR List Spreadsheet';
	//alert(param);
	//printFile(param,tujuan,judul,ev)
	printFile(param, tujuan, judul, ev)
}

function Summary() {}
function ajukanForm(pp) {
	agree();
	met = document.getElementById('method').value;
	met = 'getForm';
	param = 'method=' + met + '&nopp=' + pp;
	tujuan = 'log_slave_save_verivikasi.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					/*alert(con.responseText);
					return;*/

					document.getElementById('container').innerHTML = con.responseText;
					//										//return con.responseText;
					//
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}

function agree() {
	width = '350';
	height = '380';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<div id=container></div>";
	ev = 'event';
	title = "Submission Form";
	showDialog1(title, content, width, height, ev);
	//get_data_pp();
}
function get_data_po(rnopo) {
	agree();
	met = document.getElementById('method').value;
	met = 'getFormTolak';
	param = 'method=' + met + '&nopo=' + rnopo;
	tujuan = 'log_slave_release_po.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					/*alert(con.responseText);
					return;*/

					document.getElementById('container').innerHTML = con.responseText;
					//										//return con.responseText;
					//
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}
function forwardPP() {
	//kolom=document.getElementById('kolom').value;
	nik = document.getElementById('user_id').value;
	cmnt_hsl = document.getElementById('comment_fr').value;
	rnopp = document.getElementById('nopp').value;
	met = document.getElementById('method');
	if (cmnt_hsl == '') {
		alert('Please write a note');
		return;
	}
	document.getElementById('Ajukan').disabled = true;
	//document.getElementById('Tutup').disabled=true;
	met = met.value = 'insertFwrdpp';
	param = 'userid=' + nik + '&cm_hasil=' + cmnt_hsl + '&method=' + met + '&nopp=' + rnopp;
	tujuan = 'log_slave_save_verivikasi.php';
	/*alert(param);
	return;*/
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					displayList();
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
function rejected_pp_proses() {
	rnopp = trim(document.getElementById('rnopp').value);
	met = document.getElementById('method');
	met = met.value = 'rejected_pp_ex';
	comment = trim(document.getElementById('cmnt_tolak').value);
	klm = document.getElementById('kolom').value;
	usrid = document.getElementById('user_id').value;
	if (comment == '') {
		alert('Please leave a reason');
	} else {
		param = 'nopp=' + rnopp + '&method=' + met + '&comment=' + comment + '&kolom=' + klm + '&userid=' + usrid;
		tujuan = 'log_slave_save_verivikasi.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						//document.getElementById('contain').innerHTML=con.responseText;
						closeDialog();
						displayList();
						//alert('Berhasil');
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	}
}
function reject_some_pp() {
	//closeDialog();
	width = '850';
	height = '450';
	content = "<div id=container></div>";
	ev = 'event';
	title = "Form Penolakan";
	showDialog1(title, content, width, height, ev);
}
function rejected_some_proses(nopp, klm) {
	reject_some_pp();
	//met=document.getElementById('method').value;
	nop = nopp
		kolom = klm;
	met = 'get_form_rejected_some';
	param = 'method=' + met + '&nopp=' + nop + '&kolom=' + kolom;
	//alert(param);
	tujuan = 'log_slave_save_verivikasi.php';
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
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function rejected_some(id, no, kolom) {
	rnopp = id;
	kode_brg = document.getElementById('kdBrg_' + no).innerHTML;
	user_login = document.getElementById('user_id').value;
	alsn = document.getElementById('alsnDtolak_' + no).value;
	//kolom=document.getElementById('kolom').value;
	/*alert(nopp);
	return;*/
	met = 'rejected_some_input';
	param = 'nopp=' + rnopp + '&kd_brg=' + kode_brg + '&method=' + met + '&userid=' + user_login + '&kolom=' + kolom + '&alsnDtolk=' + alsn;
	tujuan = 'log_slave_save_verivikasi.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('contain').innerHTML=con.responseText;
					//alert('Berhasil');
					rejected_some_proses(id, kolom);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
function rejected_some_done() {
	//alert(kolom);
	closeDialog();
	displayList();
	//alert(kolom);
}

function summForm2() {
	//closeDialog();
	width = '';
	height = '';
	content = "<div id=container2 style='overflow:auto;max-width:800px;max-height:350px;'></div>";
	ev = 'event';
	title = "Detail Summary";
	showDialog5(title, content, width, height, ev);
}
function detailData(krywnId, period) {
	summForm2();
	userid = krywnId;
	prd = period;
	param = 'method=detailSum' + '&userid=' + userid + '&periode=' + prd;
	tujuan = 'log_slave_save_verivikasi.php'
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container2').innerHTML = con.responseText;
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
function getSumData() {
	prd = document.getElementById('period').options[document.getElementById('period').selectedIndex].value;
	param = 'method=getSummary' + '&periode=' + prd;
	tujuan = 'log_slave_save_verivikasi.php'
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					// ar = con.responseText.split("###");
					// document.getElementById('isiContain').innerHTML = ar[0];
					// document.getElementById('tglPeriode').innerHTML = ar[1];
					//return con.responseText;
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}




function detailExcel(pur, pt, prd, ev) {
	met = document.getElementById('method');
	usr = pur;
	kdpt = pt;
	period = prd;
	met = met.value = 'dataDetail';
	met = trim(met);
	param = 'method=' + met + '&userid=' + usr + '&kodeorg=' + kdpt + '&periode=' + period;
	// alert(param);
	tujuan = 'log_slave_save_verivikasi.php';
	judul = 'List Permintaan Barang';
	printFile(param, tujuan, judul, ev)
}

function ajukanForm(pp) {
	agree();
	met = document.getElementById('method').value;
	met = 'getForm';
	param = 'method=' + met + '&nopp=' + pp;
	tujuan = 'log_slave_save_verivikasi.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					/*alert(con.responseText);
					return;*/

					document.getElementById('container').innerHTML = con.responseText;
					//										//return con.responseText;
					//
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}

function agree() {
	width = '350';
	height = '380';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<div id=container></div>";
	ev = 'event';
	title = "Submission Form";
	showDialog1(title, content, width, height, ev);
	//get_data_pp();
}
function get_data_po(rnopo) {
	agree();
	met = document.getElementById('method').value;
	met = 'getFormTolak';
	param = 'method=' + met + '&nopo=' + rnopo;
	tujuan = 'log_slave_release_po.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					/*alert(con.responseText);
					return;*/

					document.getElementById('container').innerHTML = con.responseText;
					//										//return con.responseText;
					//
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}
function forwardPP() {
	//kolom=document.getElementById('kolom').value;
	nik = document.getElementById('user_id').value;
	cmnt_hsl = document.getElementById('comment_fr').value;
	rnopp = document.getElementById('nopp').value;
	met = document.getElementById('method');
	if (cmnt_hsl == '') {
		alert('Please leave a note');
		return;
	}
	document.getElementById('Ajukan').disabled = true;
	//document.getElementById('Tutup').disabled=true;
	met = met.value = 'insertFwrdpp';
	param = 'userid=' + nik + '&cm_hasil=' + cmnt_hsl + '&method=' + met + '&nopp=' + rnopp;
	tujuan = 'log_slave_save_verivikasi.php';
	/*alert(param);
	return;*/
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					displayList();
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
function rejected_pp_proses() {
	rnopp = trim(document.getElementById('rnopp').value);
	met = document.getElementById('method');
	met = met.value = 'rejected_pp_ex';
	comment = trim(document.getElementById('cmnt_tolak').value);
	klm = document.getElementById('kolom').value;
	usrid = document.getElementById('user_id').value;
	if (comment == '') {
		alert('Please loave a note');
	} else {
		param = 'nopp=' + rnopp + '&method=' + met + '&comment=' + comment + '&kolom=' + klm + '&userid=' + usrid;
		tujuan = 'log_slave_save_verivikasi.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						//document.getElementById('contain').innerHTML=con.responseText;
						closeDialog();
						displayList();
						//alert('Berhasil');
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respog);
	}
}
function reject_some_pp() {
	//closeDialog();
	width = '850';
	height = '450';
	content = "<div id=container></div>";
	ev = 'event';
	title = "Form Penolakan";
	showDialog1(title, content, width, height, ev);
}
function rejected_some_proses(nopp, klm) {
	reject_some_pp();
	//met=document.getElementById('method').value;
	nop = nopp
		kolom = klm;
	met = 'get_form_rejected_some';
	param = 'method=' + met + '&nopp=' + nop + '&kolom=' + kolom;
	//alert(param);
	tujuan = 'log_slave_save_verivikasi.php';
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
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function rejected_some(id, no, kolom) {
	rnopp = id;
	kode_brg = document.getElementById('kdBrg_' + no).innerHTML;
	user_login = document.getElementById('user_id').value;
	alsn = document.getElementById('alsnDtolak_' + no).value;
	//kolom=document.getElementById('kolom').value;
	/*alert(nopp);
	return;*/
	met = 'rejected_some_input';
	param = 'nopp=' + rnopp + '&kd_brg=' + kode_brg + '&method=' + met + '&userid=' + user_login + '&kolom=' + kolom + '&alsnDtolk=' + alsn;
	tujuan = 'log_slave_save_verivikasi.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('contain').innerHTML=con.responseText;
					//alert('Berhasil');
					rejected_some_proses(id, kolom);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
function rejected_some_done() {
	//alert(kolom);
	closeDialog();
	displayList();
	//alert(kolom);
}

function summForm() {
	//closeDialog();
	width = '';
	height = '';
	content = "<div id=container style='overflow:auto;max-width:1000px;max-height:480px;'></div>";
	ev = 'event';
	title = "Summary";
	showDialog1(title, content, width, height, ev);
}
function summForm2() {
	//closeDialog();
	width = '';
	height = '';
	content = "<div id=container2 style='overflow:auto;max-width:1000px;max-height:280px;'></div>";
	ev = 'event';
	title = "Detail Summary";
	showDialog2(title, content, width, height, ev);
}
function detailData(krywnId, period) {
	summForm2();
	userid = krywnId;
	prd = period;
	param = 'method=detailSum' + '&userid=' + userid + '&periode=' + prd;
	tujuan = 'log_slave_save_verivikasi.php'
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container2').innerHTML = con.responseText;
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}
/*
function getSumData() {
	prd = document.getElementById('period').options[document.getElementById('period').selectedIndex].value;
	param = 'method=getSummar' + '&periode=' + prd;
	tujuan = 'log_slave_save_verivikasi.php'
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					ar = con.responseText.split("###");
					document.getElementById('isiContain').innerHTML = ar[0];
					document.getElementById('tglPeriode').innerHTML = ar[1];
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);

}
*/
function detailExcel2(pur, prd, ev) {
	met = document.getElementById('method');
	usr = pur;
	period = prd;
	met = met.value = 'dataDetailEx';
	met = trim(met);
	param = 'method=' + met + '&userid=' + usr + '&periode=' + period;
	// alert(param);
	tujuan = 'log_slave_save_verivikasi.php';
	judul = 'List User';
	printFile(param, tujuan, judul, ev);
}

function getlokId(purchase, nourut) {
	param = 'method=getlokId' + '&purchase=' + purchase + '&nourut=' + nourut;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hasil = con.responseText;
					if (hasil == 0) { // HO
						document.getElementById('lokId').value = '0';
						document.getElementById('lokalpusat_' + nourut).checked = false;
					}
					if (hasil == 1) { // Local
						document.getElementById('lokId').value = '1';
						document.getElementById('lokalpusat_' + nourut).checked = true;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	document.getElementById('purchase_name_' + nourut).value = purchase;
}

function getlokalpusat(purchase, nourut) {
	param = 'method=getlokId' + '&purchase=' + purchase + '&nourut=' + nourut;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hasil = con.responseText;
					if (hasil == 0) { // HO
						//                        document.getElementById('lokId').value='0';
						document.getElementById('lokalpusat_' + nourut).checked = false;
					}
					if (hasil == 1) { // Local
						//                        document.getElementById('lokId').value='1';
						document.getElementById('lokalpusat_' + nourut).checked = true;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	//    document.getElementById('purchase_name_'+nourut).value=purchase;
}

function getDataPP5(ppno) {
	formListPP();
	nopp = ppno;
	param = 'method=listAddPP' + '&nopp=' + nopp;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
					//                          document.getElementById('saveAll').disabled=false;
					//                          document.getElementById('purId2').disabled=false;
					//                          document.getElementById('lokId').disabled=false;
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBarang() {
	document.getElementById('listDataPP').style.display = 'none';
	document.getElementById('cariBarang').style.display = 'block';
	document.getElementById('no_brg').value = '';
	document.getElementById('container5').innerHTML = '';
}
function cariBarangGet() {
	txt = trim(document.getElementById('no_brg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&method=cariBarang' + '&pil=2';
		tujuan = 'log_slave_save_verivikasi.php';
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
					document.getElementById('container5').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setBrg2(kdbarang, nmbarang, sat) {
	document.getElementById('nmBarang').value = nmbarang;
	document.getElementById('kdBarang').value = kdbarang;
	document.getElementById('satuanForm').value = sat;
	document.getElementById('listDataPP').style.display = 'block';
	document.getElementById('cariBarang').style.display = 'none';
}
function saveSemua(x) {
	document.getElementById('saveAll').disabled = true;
	document.getElementById('purId2').disabled = true;
	document.getElementById('lokId').disabled = true;
	nopp = document.getElementById('ppno').value;
	purchaser = document.getElementById('purId2').options[document.getElementById('purId2').selectedIndex].value;
	lokal = document.getElementById('lokId').options[document.getElementById('lokId').selectedIndex].value;
	//lokal=document.getElementById('lokId').value;
	totlBrg = document.getElementById('totalBrg').innerHTML;
	kd_brg = document.getElementById('kdBrg_' + x).innerHTML;
	jmlh_realisai = document.getElementById('jmlh_' + x).innerHTML;
	param = 'method=insertPurchaser' + '&nopp=' + nopp + '&purchase=' + purchaser + '&lokal=' + lokal;
	param += '&kdbrg=' + kd_brg + '&jmlh_realisai=' + jmlh_realisai;
	//alert(param);
	tujuan = 'log_slave_save_verivikasi.php';
	if (x == 1 && confirm('Proceed ?'))
		post_response_text(tujuan, param, respog);
	else
		post_response_text(tujuan, param, respog);
	document.getElementById('rew_' + x).style.backgroundColor = 'orange';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('rew_' + x).style.backgroundColor = 'red';
				} else {
					// alert(con.responseText);
					//document.getElementById('container').innerHTML=con.responseText;
					//return con.responseText;
					b = x;
					row = x + 1;
					x = row;
					if (x <= totlBrg) {
						document.getElementById('rew_' + b).style.backgroundColor = 'green';
						saveSemua(x);
					} else {
						nummr = document.getElementById('halPage').value;
						document.getElementById('txtsearch').value = '';
						document.getElementById('tgl_cari').value = '';
						document.getElementById('statPP').checked = false;
						cariData(nummr);
						//displayList();
						cancel();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function tambahBarang() {
	nopp = document.getElementById('noppAja').innerHTML;
	tglSdt = document.getElementById('tglSdt').innerHTML;
	kd_brg = document.getElementById('kdBarang').value;
	jmlh_realisai = document.getElementById('jmlhBrg').value;
	param = 'method=addBarangTopp' + '&nopp=' + nopp + '&tglSdt=' + tglSdt;
	param += '&kdbrg=' + kd_brg + '&jmlh_realisai=' + jmlh_realisai;
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//document.getElementById('container5').innerHTML=con.responseText;
					if (con.responseText == 1) {
						alert('Done');
						displayList();
						document.getElementById('nmBarang').value = '';
						document.getElementById('satuanForm').value = '';
						document.getElementById('jmlhBrg').value = '';
						document.getElementById('kdBarang').value = '';

					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function searchBrgCari(title, content, ev) {
	klmpk = document.getElementById('klmpkBrg').options[document.getElementById('klmpkBrg').selectedIndex].value;
	if (klmpk == '') {
		alert("Material group required!!");
		return;
	}
	idKlmpk = "<input type='hidden' id='idKlmpk' value='" + klmpk + "' />"
		content = content + idKlmpk;
	width = '';
	height = '';
	showDialog2(title, content, width, height, ev);
	//findBrg();
	//alert('asdasd');
}
function findBrg2() {
	klmpKbrg = document.getElementById('idKlmpk').value;
	nmBrg = document.getElementById('nmBrg').value;

	param = 'klmpKbrg=' + klmpKbrg + '&nmBrg=' + nmBrg + '&method=getBarang';
	tujuan = 'log_slave_save_verivikasi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//	alert(con.responseText);
					document.getElementById('containerBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function setData(kdbrg, namaBarang, sat) {
	ldata = document.getElementById('kdBarangCari');
	for (adr = 0; adr < ldata.length; adr++) {
		if (ldata.options[adr].value == kdbrg) {
			ldata.options[adr].selected = true;
		}
	}

	closeDialog();
}

function showdocpakaibarang(prddari, prdsampai, kdorg, barang, ev) {
	width = '';
	height = '';
	content = "<fieldset style='height:96%;width:97%';><legend>Pemakaian Material periode " + prddari + " s/d " + prdsampai + "</legend><div id=detailpakaibarang  style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
	ev = 'event';
	title = "Detail";
	showDialog2(title, content, width, height, ev);
	param = 'proses=preview' + '&tgl1=' + prddari + '&tgl2=' + prdsampai + '&unit=' + kdorg + '&barang=' + barang;
	tujuan = 'log_slave_2pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailpakaibarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showdocpembelian(tahun, kdorg, barang, ev) {
	width = '';
	height = '';
	content = "<fieldset style='height:96%;width:97%';><legend>Pembelian periode " + tahun + "</legend><div id=detailpembelian  style='overflow:auto;max-height:400px;max-width:900px';></div></fieldset>";
	ev = 'event';
	title = "Detail";
	showDialog2(title, content, width, height, ev);
	param = 'method=showdocpembelian' + '&tahunprd=' + tahun + '&kodeorg=' + kdorg + '&kodebarang=' + barang;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailpembelian').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanstrategies(nourut, nopp, kodebarang) {
	chkstrg = document.getElementById('strategies_'+nourut);
	
	if(chkstrg.checked==true){
		strategies='1';
	}else{
		strategies='0';
	}
	
	param = 'method=simpanstrategies&nopp='+nopp+'&kodebarang='+kodebarang+'&strategies='+strategies;
	tujuan = 'log_slave_verivikasipp.php';
	post_response_text(tujuan, param, respog);

	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//Umar
function checkAll(check){
	let anak = document.querySelectorAll('.checkChild');

	for (let i = anak.length - 1; i >= 0; i--) {
		anak[i].checked = false;

		if (check == true) {
			anak[i].checked = true;
		}
	}
}

function formReturnBatch() {
	let param  = 'method=ReturnlistPPBatch';
	let tujuan = 'log_slave_verivikasipp.php';

	post_response_text(tujuan, param, function(){
		if (con.readyState == 4) {
			busy_off();
			if (con.status == 200) {
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				error_catch(con.status);
			}
		}
	});
}

function returnBatchLoop(current, max){
	let anak 		= document.querySelectorAll('.checkChild');
	// let keterangan 	= document.querySelectorAll('.checkChildDesc');
	let nopp 		= anak[current].getAttribute('nopp');
	let kodebarang  = anak[current].getAttribute('kodebarang');
	let keterangan 	= document.getElementById('keterangan#'+kodebarang).value;

	if (anak[current].checked == true) {
		// if (current == 0) {
			if (confirm('Are you sure ?')) {
				returnBatch(nopp, kodebarang, keterangan, current, max);
			}
			// else {
				// returnBatch(nopp, kodebarang, keterangan[current], current, max);
			// }
		// }
	} else {
		current = current + 1;
		if (current < max) {
			returnBatchLoop(current, max);
		}
	}
}

function returnBatch(nopp, kdbrg, ket, current, max) {
	let pg 		= parseFloat(trim(document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value)) - 1;
	let tujuan 	= 'log_slave_verivikasipp.php';
	let param 	= "nopp=" + nopp + '&ket=' + ket + "&method=balikin" + "&kodebarang=" + kdbrg;

	post_response_text(tujuan, param, function(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					current = current + 1;
					if (current < max) {
						returnBatchLoop(current, max);
					} else {
						alertify.popup().destroy();
						loaddata(pg);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	});
}

function getDropData(nopp){
	//nopp = document.getElementById('nopp').value;
	let tujuan = 'log_slave_verivikasipp.php';
	let param  = "nopp=" + nopp;
		param  += "&method=getDropData"; 

	post_response_text(tujuan, param, function(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('detailDrop').style.display = '';
					//document.getElementById('detailContainerDrop').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','50%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	});
}

function viewKontrakDetail(nokontrak){
	let param  = '&proses=viewdetail';
		param  += '&nokontrak=' + nokontrak;
		param  += '&tipe=html';
	let tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, function(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	});
}
//End Umar