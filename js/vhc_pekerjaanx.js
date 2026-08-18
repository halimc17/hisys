function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	cancel();
}
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('KbnId').disabled = false;
	document.getElementById('KbnId').value = '';
	document.getElementById('jns_vhc').disabled = false;
	document.getElementById('kodetraksi').disabled = false;
	document.getElementById('kde_vhc').disabled = false;
	document.getElementById('tgl_pekerjaan').disabled = false;
	document.getElementById('jns_bbm').disabled = false;
	document.getElementById('jmlh_bbm').disabled = false;
	document.getElementById('mode').value = 'baru';
	document.getElementById('method').value = 'insert_header';
	
}
function createNew() {
	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('KbnId').disabled = true;
	document.getElementById('jns_vhc').disabled = true;
	document.getElementById('kodetraksi').disabled = true;
	document.getElementById('kde_vhc').disabled = true;
	document.getElementById('tgl_pekerjaan').disabled = true;
	document.getElementById('jns_bbm').disabled = true;
	document.getElementById('jmlh_bbm').disabled = true;
}

function get_notransaksi() {
	kdOrg = document.getElementById('KbnId').value;
	param = 'method=get_no_transaksi' + '&kdOrg=' + kdOrg;
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ac = con.responseText.split("####");
					document.getElementById('no_trans').value = ac[0];
					document.getElementById('jns_vhc').innerHTML = ac[1];
					//load_data();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function get_kd(notrans) {
	if (notrans == '') {
		jns_id = document.getElementById('jns_vhc').value;
		traksi_id = document.getElementById('kodetraksi').value;
		strAll = 'jns_id=' + jns_id + '&traksi_id=' + traksi_id + '&method=getKodeVhc';
	} else {
		strAll = 'no_trans=' + notrans;
		strAll += '&method=getKodeVhc';
	}
	param = strAll;
	tujuan = 'vhc_slave_pekerjaanx.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kde_vhc').innerHTML = con.responseText;
					//load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function getUmr() {
	//kdKry
	kdkry = document.getElementById('kode_karyawan').options[document.getElementById('kode_karyawan').selectedIndex].value;
	tanggal = document.getElementById('tgl_pekerjaan').value;
	tahun = tanggal.substr(6, 4);
	param = 'proses=getUmr' + '&kdKry=' + kdkry + '&tahun=' + tahun + '&tglTrans=' + tanggal;
	tujuan = 'vhc_detailPekerjaan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('uphOprt').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function addHeader() {
	//createNew();
	
	no_trans = document.getElementById('no_trans').value;
	jenis_vhc = document.getElementById('jns_vhc').value;
	kdVhc = document.getElementById('kde_vhc').value;
	kodeOrg = document.getElementById('KbnId').value;
	tgl_kerja = document.getElementById('tgl_pekerjaan').value;
	jns_bbm = document.getElementById('jns_bbm').value;
	jmlh = document.getElementById('jmlh_bbm').value;
	method = document.getElementById('method').value;
	mode = document.getElementById('mode').value;
	
	param = 'jns_id=' + jenis_vhc + '&kode_vhc=' + kdVhc + '&tglKerja=' + tgl_kerja + '&kodeOrg=' + kodeOrg;
	param += '&jnsBbm=' + jns_bbm + '&jumlah=' + jmlh + '&method=' + method + '&no_trans=' + no_trans+ '&mode=' + mode;
	
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detailx').style.display = 'block';
					document.getElementById('detail').innerHTML = data[0];
					
					
					load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getSatuan(jns_pekerjan) {
	param = 'jnsPekerjaan=' + jns_pekerjan + '&method=getSatuan'
	tujuan = 'vhc_slave_pekerjaanx.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					dtIsi = con.responseText.split("####");
					document.getElementById('satuan').innerHTML = dtIsi[0];
					document.getElementById('lokasi_kerja').innerHTML = dtIsi[1];
					getBlok(0, 0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getBlok(kdkbn, kdblok) {
	if (document.getElementById('jns_kerja').value == '') {
		alert("Jenis Pekerjaan harus diisi terlebih dahulu!");
		document.getElementById('lokasi_kerja').selectedIndex = 0;
		return false;
	}

	if ((kdkbn == '') && (kdblok == '')) {
		locationKerja = document.getElementById('lokasi_kerja').value;
		jnsPekerjaan = document.getElementById('jns_kerja').value;
		param = 'locationKerja=' + locationKerja + '&jnsPekerjaan=' + jnsPekerjaan + '&method=getBlok';
	} else {
		locationKerja = kdkbn;
		Blok = kdblok;
		jnsPekerjaan = document.getElementById('jns_kerja').value;
		param = 'locationKerja=' + locationKerja + '&jnsPekerjaan=' + jnsPekerjaan + '&Blok=' + Blok + '&method=getBlok';
	}
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
					document.getElementById('old_blok').value = kdblok;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getkegiatan(kelompok){
	jenis_vhc = document.getElementById('jns_vhc').value;
	kode_vhc = document.getElementById('kde_vhc').value;
	param = 'kelompok=' + kelompok + '&method=getkegiatan'
	param += '&jenis_vhc=' + jenis_vhc;
	param += '&kode_vhc=' + kode_vhc;
	tujuan = 'vhc_slave_pekerjaanx.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jns_kerja').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function save_pekerjaan() {
	notrans = document.getElementById('no_trans').value;
	if (notrans == '') {
		alert("Notransaksi wajib terisi.");
		return;
	}
	jns_pekerjan = document.getElementById('jns_kerja').value;
	if (document.getElementById('old_jnskerja').value == '') {
		document.getElementById('old_jnskerja').value = jns_pekerjan;
	}
	kmhm_aw      = document.getElementById('kmhm_awal').value;
	kmhm_ak      = document.getElementById('kmhm_akhir').value;
	satuan       = document.getElementById('stn').value;
	oldkerja     = document.getElementById('old_jnskerja').value;
	locationKerj = document.getElementById('lokasi_kerja').value;
	brtmuatan    = document.getElementById('brt_muatan').value;
	jmlh_rit     = document.getElementById('jmlh_rit').value;
	keterangan   = document.getElementById('ket').value;
	pro          = document.getElementById('proses_pekerjaan').value;
	bya          = document.getElementById('biaya').value;
	Blok         = document.getElementById('blok').value;
	kodesegment  = getValue('kodesegment');

	param = 'no_trans=' + notrans + '&jnsPekerjaan=' + jns_pekerjan + '&locationKerja=' + locationKerj + '&biaya=' + bya;
	param += '&brtmuatan=' + brtmuatan + '&jmlhRit=' + jmlh_rit + '&ket=' + keterangan + '&method=' + pro + '&oldjnsPekerjaan=' + oldkerja;
	param += '&kmhmAwal=' + kmhm_aw + '&kmhmAkhir=' + kmhm_ak + '&satuan=' + satuan + '&kodesegment=' + kodesegment + '&oldSegment=' + getValue('oldSegment');

	if (document.getElementById('oldbrt_muatan').value != '') {
		oldbrt_muatan = document.getElementById('oldbrt_muatan').value;
		param += '&oldbrt_muatan=' + oldbrt_muatan;
	}

	if (document.getElementById('old_lokkerja').value != '') {
		old_lokKerja = document.getElementById('old_lokkerja').value;
		param += '&old_lokKerja=' + old_lokKerja;
	}
	if (document.getElementById('old_blok').value != '') {
		oldBlok = document.getElementById('old_blok').value;
		param += '&oldBlok=' + oldBlok;
	}

	if (Blok != '') {
		param += '&Blok=' + Blok;
	}
	
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isidt = 0;
					if (con.responseText != '') {
						isidt = parseInt(con.responseText);
					}
					document.getElementById('kmhm_awal').disabled = false;
					document.getElementById('kmhm_awal').value = isidt;
					bersih_form_pekerjaan();
					load_data_pekerjaan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function load_data_pekerjaan() {
	no_trans = document.getElementById('no_trans').value;
	param = 'no_trans=' + no_trans;
	param += '&method=load_data_kerjaan';
	
	tujuan = 'vhc_slave_pekerjaanx.php';

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

function bersih_form_pekerjaan() {
	document.getElementById('proses_pekerjaan').value = 'insert_pekerjaan';
	document.getElementById('jns_kerja').value = '';
	document.getElementById('jns_kerja').disabled = false;
	document.getElementById('lokasi_kerja').selectedIndex = 0;
	document.getElementById('lokasi_kerja').disabled = false;
	document.getElementById('brt_muatan').value = 0;
	document.getElementById('jmlh_rit').value = 0;
	document.getElementById('ket').value = '';
	document.getElementById('blok').value = "";
	document.getElementById('blok').selectedIndex = 0;
	document.getElementById('biaya').value = 0;
	document.getElementById('kmhm_akhir').value = 0;
	document.getElementById('stn').selectedIndex = 0;
	document.getElementById('oldbrt_muatan').value = ''
	setValue('kodesegment', '');
	setValue('kodesegment_name', '');
	//getKmAkhir();
}

function getKmAkhir() {
	var kodevhc = getValue('kde_vhc'),
	param = "method=getKmAkhir&kodevhc=" + kodevhc;
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kmhm_awal').value = trim(con.responseText);
					if (parseFloat(con.responseText) > 0) {
						getById('kmhm_awal').disabled = false;
					} else {
						getById('kmhm_awal').disabled = false;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function fillFieldKrj(jnsKrj, lokKrj, brtMuat, jmlhRit, ktr, bya, kmawl, kmakhr, stn, segment, nmSegment) {
	document.getElementById('kelompok').value = 'ALL';
	document.getElementById('jns_kerja').value = jnsKrj;
	document.getElementById('old_jnskerja').value = jnsKrj;
	document.getElementById('brt_muatan').value = brtMuat;
	document.getElementById('jmlh_rit').value = jmlhRit;
	document.getElementById('biaya').value = bya;
	document.getElementById('ket').value = ktr;
	document.getElementById('kmhm_awal').value = kmawl;
	document.getElementById('kmhm_akhir').value = kmakhr;
	document.getElementById('stn').value = stn;
	document.getElementById('proses_pekerjaan').value = 'update_kerja';
	document.getElementById('old_jnskerja').value = jnsKrj;
	document.getElementById('oldbrt_muatan').value = brtMuat;
	setValue('kodesegment', segment);
	setValue('kodesegment_name', nmSegment);



	if (lokKrj.length > 4) {
		if(lokKrj.substr(0,3)=='S20'){
			kd = lokKrj;
			document.getElementById('lokasi_kerja').value = kd;
			document.getElementById('old_lokkerja').value = kd;
			document.getElementById('blok').value = '';
		}else{
			kd = lokKrj;
			document.getElementById('lokasi_kerja').value = kd.substring(0, 4);
			getBlok(kd.substring(0, 4), kd);
			document.getElementById('old_lokkerja').value = kd;
		}
		
	} else {
		document.getElementById('old_lokkerja').value = lokKrj;
		document.getElementById('lokasi_kerja').value = lokKrj;
		getBlok();
	}
}

function delDataKrj(noTrans, jnsKerja, blok, segment, beratmuatan) {
	no_trans = document.getElementById('no_trans').value = noTrans;
	jns_kerja = document.getElementById('jns_kerja').value = jnsKerja;
	param = 'notrans=' + no_trans + '&jnsPekerjaan=' + jns_kerja + '&Blok=' + blok + '&kodesegment=' + segment + '&beratmuatan=' + beratmuatan+ '&proses=deleteKrj';
	
	tujuan = 'vhc_slave_pekerjaanx.php';
	if (confirm("Delete, are you sure?")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					load_data_pekerjaan();
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
	txtTgl = document.getElementById('tgl_cari').value;
	txtCari = document.getElementById('txtCari').value;
	kodevhc_cari = document.getElementById('kodevhc_cari').value;
	statData = document.getElementById('statusInputan').value;
	param = "txtTgl=" + txtTgl + "&txtCari=" + txtCari + '&statData=' + statData + '&kodevhc_cari=' + kodevhc_cari;
	param += "&method=loaddata";
	param += '&page=' + page;
	
	tujuan = 'vhc_slave_pekerjaanx.php';
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

function enter(e) {
	key = getKey(e);
	if (key == 13) {
		cariDataTransaksi();
		return true;
	} else {
		return tanpa_kutip_dan_sepasi(e);
	}
}

function displayList() {
	document.getElementById('mode').value = 'baru';
	document.getElementById('txtCari').value = '';
	document.getElementById('tgl_cari').value = '';
	document.getElementById('kodevhc_cari').value = '';
	document.getElementById('statusInputan').value = '';
	
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailx').style.display = 'none';
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	loaddata(0);
}
function edit(notransaksi,jenisvhc,tanggal,jenisbbm,jlhbbm,kodeorg,kodetraksi,kodevhc){
	document.getElementById('kde_vhc').value = kodevhc;
	document.getElementById('no_trans').value = notransaksi;
	document.getElementById('jns_vhc').value = jenisvhc;
	document.getElementById('kodetraksi').value = kodetraksi;
	document.getElementById('tgl_pekerjaan').value = tanggal;
	document.getElementById('tgl_pekerjaan').disabled = true;
	document.getElementById('jns_bbm').value = jenisbbm;
	document.getElementById('jmlh_bbm').value = jlhbbm;
	document.getElementById('KbnId').disabled = true;
	document.getElementById('KbnId').value = kodeorg;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	document.getElementById('mode').value = 'edit';
	document.getElementById('kde_vhc').disabled = true;
	document.getElementById('kodetraksi').disabled = true;
	document.getElementById('jns_vhc').disabled = true;
	
	addHeader(notransaksi);
	bersih_form_pekerjaan();
}
function formview() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function preview(notransaksi, kode_vhc, tipe) {
	formview();
	param = 'method=preview' + '&kode_vhc=' + kode_vhc + '&tipe=' + tipe + '&no_trans=' + notransaksi;
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(no_trans,tanggal, numRow) {
	param = "no_trans=" + no_trans;
	param += "&tanggal=" + tanggal;
	param += "&method=posting";
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm('Akan dilakukan posting untuk transaksi ' + no_trans +
			'\nData tidak dapat diubah setelah ini. Anda yakin?')) {
		post_response_text('vhc_slave_pekerjaanx.php', param, respon);
	}
}

//==========================================================================================================

function unhideheader() {
	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	document.getElementById('hidebtn').style.display = 'block';
	document.getElementById('unhidebtn').style.display = 'none';
}
function hideheader() {
	document.getElementById('header_trans').style.display = 'none';
	document.getElementById('judul_header').style.display = 'none';
	document.getElementById('hidebtn').style.display = 'none';
	document.getElementById('unhidebtn').style.display = '';
}
function detailExcel(notransaksi, numRow, ev) {
	param = "proses=excel&tipe=PNN" + "&notransaksi=" + notransaksi;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '900', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function detailData(notransaksi, numRow, ev, tipe) {
	param = "proses=html&tipe=" + tipe + "&notransaksi=" + notransaksi;
	title = "Data Detail";
	showDialog1(title, "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '', '', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function detailPDF(notransaksi, numRow, ev) {
	param = "proses=pdf&tipe=PNN" + "&notransaksi=" + notransaksi;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='kebun_slave_operasional_print_detail_panen.php?" + param + "'></iframe>", '900', '400', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function postingData(notransaksi, numRow) {
	param = "notransaksi=" + notransaksi;
	param += "&method=posting";
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					//alert('Posting Berhasil');
					//javascript:location.reload(true);
					x = document.getElementById('tr_' + numRow);
					x.cells[11].innerHTML = '';
					x.cells[12].innerHTML = '';
					x.cells[13].innerHTML = "<img class=\"zImgOffBtn\" title=\"Posted\" src=\"images/skyblue/posted.png\">";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm('Akan dilakukan posting untuk transaksi ' + notransaksi +
			'\nData tidak dapat diubah setelah ini. Anda yakin?')) {
		//post_response_text('kebun_slave_panen_posting.php', param, respon);
		//berhubung tidak ada jurnal upah, dan hanya update flag saja maka kita alihkan kesini saja
		post_response_text('vhc_slave_pekerjaanx.php', param, respon);
	}
}
// function edit(notransaksi, tgl, kodeorg, nobkm, mandor, mandor1, kerani,sts) {
	// document.getElementById('notransaksi').value = notransaksi;
	// document.getElementById('tgl').value = tgl;
	// document.getElementById('kodeorg').value = kodeorg;
	// document.getElementById('nobkm').value = nobkm;
	// document.getElementById('mandor').value = mandor;
	// document.getElementById('mandor1').value = mandor1;
	// document.getElementById('kerani').value = kerani;
	// document.getElementById('status').value = sts;
	
	// document.getElementById('listData').style.display = 'none';
	// document.getElementById('header').style.display = 'block';
	// //document.getElementById('detail').style.display='block';
	// document.getElementById('mode').value = 'edit';
	// addHeader(notransaksi);
// }
function deletedetail(notransaksi, karyawanid, blok, numrow) {
	param = 'method=deletedetail' + '&notransaksi=' + notransaksi + '&karyawanid=' + karyawanid + '&blok=' + blok;
	tujuan = 'vhc_slave_pekerjaanx.php';
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

function clear_operator() {
	document.getElementById('kode_karyawan').value = '';
	document.getElementById('uphOprt').value = 0;
	document.getElementById('prmiOprt').value = 0;
	document.getElementById('pnltyOprt').value = 0;
	document.getElementById('ketOprt').value = "";
	document.getElementById('prosesOpt').value = 'insert_operator';
}
function save_operator() {

	jenisvhc = document.getElementById('jns_vhc').value;

	notrans = document.getElementById('no_trans').value;
	kdKry = document.getElementById('kode_karyawan').options[document.getElementById('kode_karyawan').selectedIndex].value;
	posisi = document.getElementById('posisi').options[document.getElementById('posisi').selectedIndex].value;
	uphoprt = document.getElementById('uphOprt').value;
	prmiOprt = document.getElementById('prmiOprt').value;
	pnltyOprt = document.getElementById('pnltyOprt').value;
	tglTrans = document.getElementById('tgl_pekerjaan').value;
	ketOprt = document.getElementById('ketOprt').value;
	pros = document.getElementById('prosesOpt');

	if (kdKry == '') {
		alert('Nama Karyawan wajib di isi !');
		return;
	}

	param = 'notrans=' + notrans + '&kdKry=' + kdKry + '&posisi=' + posisi + '&jenisvhc=' + jenisvhc;
	param += '&method=' + pros.value + '&pnltyOprt=' + pnltyOprt + '&prmiOprt=' + prmiOprt + '&uphOprt=' + uphoprt + '&tglTrans=' + tglTrans + '&ketOprt=' + ketOprt;
	tujuan = 'vhc_slave_pekerjaanx.php';
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
					//document.getElementById('containPekerja').innerHTML=con.responseText;
					load_data_operator();
					clear_operator();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function load_data_operator() {
	//alert(document.getElementById('no_trans_opt').value);
	if (document.getElementById('no_trans_opt').value != '') {
		no_tans = document.getElementById('no_trans_opt').value;
		param = 'method=load_data_opt';
		param += '&notrans=' + no_tans;
		//alert(param);
		tujuan = 'vhc_slave_pekerjaanx.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						document.getElementById('containOperator').innerHTML = con.responseText;
						//load_data_pekerjaan();+
						//noTrans = document.getElementById('no_trans_opt').value;
						//getKmAkhir();
						//  getKntrk(thn,nokntrak);
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

function getdata(row) {
	row = document.getElementById('jlhbrs').value;
	filterdivisi = document.getElementById('filterdivisi').value;
	tgl = document.getElementById('tgl').value;
	kodeorg=document.getElementById('kodeorg').value;
	param = 'method=getdata' + '&filterdivisi=' + filterdivisi + '&tgl=' + tgl + '&kodeorg=' + kodeorg;
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (row == 0) {
						isdata = con.responseText.split("######");
						document.getElementById('karyawanid').innerHTML = isdata[0];
						document.getElementById('blok').innerHTML = isdata[1];
					} else {
						for (i = 1; i <= row; i++) {
							isdata = con.responseText.split("######");
							document.getElementById('blok' + i).innerHTML = isdata[1];
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
function getnotransaksi() {
	kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tgl = document.getElementById('tgl').value;
	document.getElementById('notransaksi').value = '';
	param = 'tgl=' + tgl + '&kodeorg=' + kodeorg + '&method=getnotransaksi';
	tujuan = 'vhc_slave_pekerjaanx.php';
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

function del(notransaksi, numrow) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_pekerjaanx.php';
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
					loaddata(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function loaddatadetail(notransaksi) {
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('tgl').disabled = true;
	tgl = document.getElementById('tgl').value;
	kodeorg = document.getElementById('kodeorg').value;
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loaddatadetail';
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	tujuan = 'vhc_slave_pekerjaanx.php';
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
function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
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
	tujuan = 'vhc_slave_pekerjaanx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
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
				
