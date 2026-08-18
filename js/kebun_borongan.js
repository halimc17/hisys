function getLaporanJurnal(pt,periode,gudang,nojurnal) {
	width = '';
	height = '';
	content = ""
		 + "<div id=contviewx style=\"width:100%;max-height:500px;overflow:auto;\">"
		 + "</div>";
	ev = 'event';
	title = "Jurnal";
	showDialog4(title, content, width, height, ev);
	
	periode1=periode;
	regional=ref=kdKel=ket='';
	param = 'pt=' + pt + '&gudang=' + gudang + '&periode=' + periode + '&periode1=' + periode1 + '&revisi=0&regional=' + regional;
	param += '&kdKel=' + kdKel + '&ref=' + ref + '&ket=' + ket + '&nojurnal=' + nojurnal;
	param += '&method=getLaporanJurnal';
	tujuan = 'keu_laporanJurnal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					var data = "";
					e=document.getElementById('contviewx');
					data+="<table class='sortable' cellpading=1 cellspacing=1 border=0>";
					data+="<thead><tr class=rowheader>";
					data+="<th align=center>No</th>";
					data+="<th align=center>No Jurnal</th>";
					data+="<th align=center>No Voucher</th>";
					data+="<th align=center>Tanggal</th>";
					data+="<th align=center>Organisasi</th>";
					data+="<th align=center>No Akun</th>";
					data+="<th align=center>Nama Akun</th>";
					data+="<th align=center>Keterangan</th>";
					data+="<th align=center>Debet</th>";
					data+="<th align=center>Kredit</th>";
					data+="<th align=center>No Referensi</th>";
					data+="<th align=center>Blok</th>";
					data+="<th align=center>TT</th>";
					data+="<th align=center>Rev</th>";
					data+="</tr></thead>";
					data+=con.responseText;
					data+="</table>";
					e.innerHTML = data;
				   
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formpostingData(notransaksi, nopengajuan, no) {
	width = '';
	height = '';
	content = ""
		 + "<div id=contview style=\"width:100%;max-height:500px;overflow:auto;\">"
		 + "</div>";
	ev = 'event';
	title = "Posting";
	showDialog5(title, content, width, height, ev);
	param = 'method=formpostingData&notransaksi=' + notransaksi;
	param += '&nopengajuan=' + nopengajuan;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

sekarang = 1;
function postingall(maxRow) {
	tanggal = document.getElementById('tglpost').value;
	if (tanggal == '') {
		alert("Tanggal wajib diisi !!!");
		return;
	}

	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Akan dilakukan posting, data tidak dapat diubah setelah ini. Anda yakin?")) {
		postingData(1, maxRow);
	}
}

function postingData(currRow, maxRow) {
	notransaksi = document.getElementById('notr_' + currRow).innerHTML;
	tanggal = document.getElementById('tglpost').value;

	param = "notransaksi=" + notransaksi;
	param += "&tanggal=" + tanggal;
	param += '&method=posting';
	document.getElementById('tombolpost').disabled = true;
	document.getElementById('tglpost').disabled = true;
	post_response_text('kebun_slave_borongan_posting.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
				} else {
					if (currRow != undefined) {
						document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
					}
					currRow += 1;
					sekarang = currRow;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						pages = document.getElementById('pages').value;
						loaddata(pages-1);
						closeDialog5();
						document.getElementById('tombolpost').disabled = false;
						document.getElementById('tglpost').disabled = false;
					} else {
						postingData(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function htmlborrekap(nopengajuan, kodeorg, no, ev, tipe) {
	width = '1000px';
	height = '500px';
	content = "<fieldset style=\"width:995px;height:500px\">"
		 + "<div id=contview1 style=\"width:100%;max-height:500px;overflow:auto;\">"
		 + "</div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog2(title, content, width, height, ev);
	proses = 'preview';

	param = 'method=rekap&notransaksi=' + nopengajuan;
	param += '&kodeorg=' + kodeorg;
	param += '&proses=' + proses;
	tujuan = 'kebun_slave_borongan_approval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview1').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_ajukan(notransaksi, divisi, nopengajuan, numrow) {
	width = '';
	height = '';
	content = "<div id=containeraju align=left style=\"width:100%;max-height:600px;overflow:auto;\"></div>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&numrow=' + numrow + '&divisi=' + divisi + '&nopengajuan=' + nopengajuan;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan(no) {
	kepada = document.getElementById('kepada').value;
	nopengajuan = document.getElementById('nopengajuan').innerHTML;
	tglpengajuan = document.getElementById('tglpengajuan').innerHTML;
	notran = e = '';
	for (i = 1; i <= no; i++) {
		aju = document.getElementById('ajukan' + i);
		if (aju.checked == true) {
			notr = document.getElementById('notr_' + i).innerHTML;
			if (notr != '') {
				e++;
				if (e == 1) {
					notran += "'" + notr + "'";
				} else {
					notran += ",'" + notr + "'";
				}
			}
		}
	}
	if (notran == '') {
		alert("Silahkan pilih / checked salah satu atau lebih !!!");
		return false;
	}
	param = 'method=ajukan' + '&notransaksi=' + notran + '&kepada=' + kepada;
	param += '&nopengajuan=' + nopengajuan + '&tglpengajuan=' + tglpengajuan;
	if (kepada == '') {
		alert('Isikan nama penyetuju !!!');
		return;
	}
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Sucses');
					closeDialog();
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailData(notransaksi, numRow, ev, tipe, jenis) {
	param = "method=html&tipe=" + tipe + "&notransaksi=" + notransaksi + "&jenis=" + jenis;
	title = "Data Detail";
	showDialog5(title, "<iframe frameborder=0 style='width:995px;height:490px'" +
		" src='kebun_slave_borongan.php?" + param + "'></iframe>", '1000', '500', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function edit(notransaksi, tgl, kodeorg, divisi, noborong, palaborong, tipetransaksi) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('tgl').value = tgl;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('divisi').value = divisi;
	document.getElementById('noborong').value = noborong;
	document.getElementById('palaborong').value = palaborong;
	document.getElementById('statusblok').value = tipetransaksi;
	document.getElementById('mode').value = 'edit';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	simpanheader();
	//addHeader(notransaksi);
}

function simpanheader() {
	kodeorg = document.getElementById('kodeorg').value;
	palaborong = document.getElementById('palaborong').value;
	statusblok = document.getElementById('statusblok').value;
	divisi = document.getElementById('divisi').value;
	noborong = document.getElementById('noborong').value;
	tgl = document.getElementById('tgl').value;
	stsawal = document.getElementById('stsawal').value;
	mode = document.getElementById('mode').value;

	if (tgl == '' || kodeorg == '') {
		alert('Tanggal dan atau Kode Organisasi harus di isi !');
		return;
	}
	if (mode == 'baru') {
		document.getElementById('tomboldetail').disabled = true;
	} else {
		document.getElementById('tomboldetail').disabled = false;
	}
	param = 'method=simpanheader';
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&palaborong=' + palaborong + '&divisi=' + divisi + '&noborong=' + noborong + '&stsawal=' + stsawal + '&mode=' + mode + '&statusblok=' + statusblok;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (mode == 'baru') {
						document.getElementById('notransaksi').value = trim(con.responseText);
					}
					addHeader();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addHeader() {
	notransaksi = document.getElementById('notransaksi').value;
	kodeorg = document.getElementById('kodeorg').value;
	palaborong = document.getElementById('palaborong').value;
	statusblok = document.getElementById('statusblok').value;
	divisi = document.getElementById('divisi').value;
	noborong = document.getElementById('noborong').value;
	tgl = document.getElementById('tgl').value;
	stsawal = document.getElementById('stsawal').value;
	mode = document.getElementById('mode').value;

	if (tgl == '' || kodeorg == '') {
		alert('Tanggal dan atau Kode Organisasi harus di isi !');
		return;
	}

	param = 'method=detail';
	param += '&tgl=' + tgl + '&kodeorg=' + kodeorg + '&palaborong=' + palaborong + '&divisi=' + divisi + '&noborong=' + noborong + '&stsawal=' + stsawal + '&mode=' + mode + '&statusblok=' + statusblok+'&notransaksi='+notransaksi;
	tujuan = 'kebun_slave_borongan.php';
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
					loaddataprestasi();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettotalabsensi(){
	prestasi = remove_comma(document.getElementById('prestasiabs'));
	hargasatabs = remove_comma(document.getElementById('hargasatabs'));
	hasil = parseFloat(prestasi) * parseFloat(hargasatabs);
	document.getElementById('rupiahabs').value = hasil;
	z.numberFormat('rupiahabs',2);
}

function getdetailblok() {
	kodeorg = document.getElementById('kodeorg').value;
	notransaksi = document.getElementById('notransaksi').value;
	statusblok = document.getElementById('statusblok').value;
	divisi = document.getElementById('divisi').value;
	blok = document.getElementById('blok').value;
	kegiatan = document.getElementById('kegiatan').value;

	param = 'method=getdetailblok';
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	param += '&kodeorg=' + kodeorg + '&tgl=' + tgl + '&notransaksi=' + notransaksi;
	param += '&divisi=' + divisi + '&statusblok=' + statusblok;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('tt').value = data[0];
					document.getElementById('luas').value = data[1];
					document.getElementById('pkk').value = data[2];
					document.getElementById('satuan').value = data[3];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveprestasi() {
	notransaksi = document.getElementById('notransaksi').value;
	blok = document.getElementById('blok').value;
	kegiatan = document.getElementById('kegiatan').value;
	method = document.getElementById('methodprestasi').value;
	tt = document.getElementById('tt').value;
	prestasi = document.getElementById('prestasi').value;
	hargasat = document.getElementById('hargasat').value;

	param = '';
	param += '&method=' + method;
	param += '&notransaksi=' + notransaksi;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	param += '&tt=' + tt;
	param += '&prestasi=' + prestasi;
	param += '&hargasat=' + hargasat;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('hargasatabs').value = hargasat;
					loaddataprestasi();
					clearprestasi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editabs(notransaksi, karyawanid, prestasi, rupiah, numrow) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('karyawanid').value = karyawanid;
	document.getElementById('karyawanid').disabled = true;
	document.getElementById('prestasiabs').value = prestasi;
	document.getElementById('rupiahabs').value = rupiah;
	document.getElementById('methodabsensi').value = 'updateabsensi';
}
function editpres(notransaksi, blok, kegiatan, tt, luas, pkk, satuan, prestasi, hargasat, numrow) {
	document.getElementById('notransaksi').value = notransaksi;
	document.getElementById('blok').value = blok;
	document.getElementById('blok').disabled = true;
	document.getElementById('kegiatan').value = kegiatan;
	document.getElementById('kegiatan').disabled = true;
	document.getElementById('luas').value = luas;
	document.getElementById('tt').value = tt;
	document.getElementById('pkk').value = pkk;
	document.getElementById('satuan').value = satuan;
	document.getElementById('hargasat').value = hargasat;
	document.getElementById('prestasi').value = prestasi;
	document.getElementById('methodprestasi').value = 'updateprestasi';
}
function delpres(notransaksi, kegiatan, blok) {
	param = '';
	param += '&method=delpres';
	param += '&notransaksi=' + notransaksi;
	param += '&blok=' + blok;
	param += '&kegiatan=' + kegiatan;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('hargasatabs').value = 0;
					loaddataprestasi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddataprestasi() {
	notransaksi = document.getElementById('notransaksi').value;

	param = '';
	param += '&method=loaddataprestasi';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddataprestasi').innerHTML = con.responseText;
					loaddatadetailabsensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveabsensi() {
	notransaksi = document.getElementById('notransaksi').value;
	karyawanid = document.getElementById('karyawanid').value;
	prestasi = document.getElementById('prestasiabs').value;
	method = document.getElementById('methodabsensi').value;
	rupiah = document.getElementById('rupiahabs').value;

	param = '';
	param += '&method=' + method;
	param += '&notransaksi=' + notransaksi;
	param += '&karyawanid=' + karyawanid;
	param += '&rupiah=' + rupiah;
	param += '&prestasi=' + prestasi;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetailabsensi();
					clearabsensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loaddatadetailabsensi() {
	notransaksi = document.getElementById('notransaksi').value;

	param = '';
	param += '&method=loaddatadetailabsensi';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('datadetailabsensi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delabs(notransaksi, karyawanid) {
	param = '';
	param += '&method=delabs';
	param += '&notransaksi=' + notransaksi;
	param += '&karyawanid=' + karyawanid;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetailabsensi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function clearprestasi() {
	document.getElementById('prestasi').value = '';
	document.getElementById('blok').value = '';
	document.getElementById('kegiatan').value = '';
	document.getElementById('tt').value = '';
	document.getElementById('luas').value = '';
	document.getElementById('pkk').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('hargasat').value = '';
	document.getElementById('blok').disabled = false;
	document.getElementById('kegiatan').disabled = false;
	document.getElementById('methodprestasi').value = 'insertprestasi';
}
function clearabsensi() {
	document.getElementById('karyawanid').value = '';
	document.getElementById('prestasiabs').value = '';
	document.getElementById('rupiahabs').value = '';
	document.getElementById('karyawanid').disabled = false;
	document.getElementById('methodabsensi').value = 'insertabsensi';
}

function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function del(notransaksi, numrow) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;

	param = 'method=delete' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_borongan.php';
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

function displayList() {
	document.getElementById('notransaksisch').value = '';
	document.getElementById('tglsch').value = '';
	document.getElementById('divsch').value = '';
	document.getElementById('postingsrc').value = '';
	document.getElementById('statussch').value = '';
	document.getElementById('kepalaborongansch').value = '';
	document.getElementById('nomorborongansch').value = '';
	document.getElementById('mode').value = 'baru';

	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';

	document.getElementById('header_trans').style.display = 'block';
	document.getElementById('judul_header').style.display = 'block';
	//document.getElementById('hidebtn').style.display='block';
	//document.getElementById('unhidebtn').style.display='none';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	notransaksisch = document.getElementById('notransaksisch').value;
	divsch = document.getElementById('divsch').value;
	statussch = document.getElementById('statussch').value;
	postingsrc = document.getElementById('postingsrc').value;
	tglsch = document.getElementById('tglsch').value;
	kepalaborongansch = document.getElementById('kepalaborongansch').value;
	nomorborongansch = document.getElementById('nomorborongansch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (notransaksisch != '') {
		param += '&notransaksisch=' + notransaksisch;
	}
	if (statussch != '') {
		param += '&statussch=' + statussch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	if (postingsrc != '') {
		param += '&postingsrc=' + postingsrc;
	}
	if (kepalaborongansch != '') {
		param += '&kepalaborongansch=' + kepalaborongansch;
	}
	if (nomorborongansch != '') {
		param += '&nomorborongansch=' + nomorborongansch;
	}

	tujuan = 'kebun_slave_borongan.php';
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
	document.getElementById('palaborong').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('noborong').value = '';
	document.getElementById('mode').value = 'baru';
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
	tujuan = 'kebun_slave_borongan.php';
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

function addfile(nopengajuan){
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("nopengajuan", nopengajuan);
	formdata.append("fileupload", getValue('upload'));
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "kebun_slave_borongan.php?method=submitfile", true);
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
					document.getElementById("upload").value = "";
					loadfiles(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(nopengajuan) {
	param = 'method=loadfiles&nopengajuan=' + nopengajuan;
	tujuan = 'kebun_slave_borongan.php';
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

function deletefile(namafile,kriteriaefil) {
	param = 'method=deletefile&namafile=' + namafile+ '&kriteriaefil=' + kriteriaefil;
	tujuan = 'kebun_slave_borongan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}