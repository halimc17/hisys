function getsubtipeasset(){
	aset = document.getElementById('aset').options[document.getElementById('aset').selectedIndex].value;
	param = 'method=getsubtipeasset&aset='+aset;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('sub').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addfile() {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "vhc_slave_capex.php?method=submitfile", true);
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
					loadfiles('ht');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function appshowcapex(notransaksi, suppid, ev) {
	width = '920';
	height = '';
	content = "<div id=containercbxx style=\"width:920px;max-height:700px;overflow:auto\"></div>";
	ev = 'event';
	title = "View Capex Bangunan";
	showDialog2(title, content, width, height, ev);
	param = 'method=appshowcapex&kode=' + notransaksi + '&suppid=' + suppid;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercbxx').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(htdt) {
	param = 'method=loadfiles&htdt=' + htdt;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = con.responseText;
					if (htdt == 'ht') {
						document.getElementById('addfile').style.display = 'block';
						document.getElementById('upload').disabled = false;
					} else {
						document.getElementById('addfile').style.display = 'none';
						document.getElementById('upload').style.display = true;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(namafile) {
	kode=document.getElementById('kode').value;
	param = 'method=deletefile&namafile=' + namafile+ '&kode=' + kode;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles('ht');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batal() {
	// var d = new Date();
	// var curr_date = d.getDate();
	// var curr_month = d.getMonth() + 1; //Months are zero based
	// var curr_year = d.getFullYear();
	// alert(curr_date.length);
	// if(curr_date.length==1)
	// {
	// curr_date='0'+curr_date;
	// }
	d1 = getdatenow(1);
	tipebatal = document.getElementById('method').value;
	document.getElementById('unit').selectedIndex = 0;
	document.getElementById('aset').selectedIndex = 0;
	document.getElementById('sub').selectedIndex = 0;
	document.getElementById('jenis').value = 'AK';
	document.getElementById('jenisbiaya').selectedIndex = 0;
	document.getElementById('nama').value = '';
	document.getElementById('tanggalmulai').value = d1;
	document.getElementById('tanggalselesai').value = d1;
	document.getElementById('method').value = 'insert';
	document.getElementById('kode').value = '';
	document.getElementById('tablepersetujuan').innerHTML = '';
	document.getElementById('pekerjaan').selectedIndex = 0;
	document.getElementById('statusbg').selectedIndex = 0;
	document.getElementById('tipebg').selectedIndex = 0;
	document.getElementById('unit').disabled = false;
	document.getElementById('aset').disabled = false;
	document.getElementById('sub').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('tanggalmulai').disabled = false;
	document.getElementById('tanggalselesai').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('jenisbiaya').disabled = false;
	document.getElementById('pekerjaan').disabled = false;
	document.getElementById('statusbg').disabled = false;
	document.getElementById('tipebg').disabled = false;
	document.getElementById('btnsimpan').disabled = false;
	document.getElementById('addfile').style.display = 'block';
	document.getElementById('upload').disabled = false;
	param = 'method=batal&tipebatal=' + tipebatal;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerupload').innerHTML = "";
					document.getElementById('detailInput').style.display = 'none';
					document.getElementById('dataDisimpan').style.display = 'block';
					getsubtipeasset();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getjbiaya(jenisbiaya, kode, htdt) {
	unit = document.getElementById('unit').value;
	pekerjaan = document.getElementById('pekerjaan').value;
	param = 'method=getjbiaya' + '&unit=' + unit + '&jenisbiaya=' + jenisbiaya + '&pekerjaan=' + pekerjaan;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jenisbiaya').innerHTML = con.responseText;
					loadpersetujuan(kode, htdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadpersetujuan(notransaksi, htdt) {
	unit = document.getElementById('unit').value;
	param = 'method=loadpersetujuan' + '&unit=' + unit + '&kode=' + notransaksi + '&htdt=' + htdt;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tablepersetujuan').innerHTML = con.responseText;
					loadfiles(htdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpan() {
	aset = document.getElementById('aset').options[document.getElementById('aset').selectedIndex].value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	sub = document.getElementById('sub').options[document.getElementById('sub').selectedIndex].value;
	jenis = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	jenisbiaya = document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value;
	nama = trim(document.getElementById('nama').value);
	tanggalmulai = trim(document.getElementById('tanggalmulai').value);
	tanggalselesai = trim(document.getElementById('tanggalselesai').value);
	pekerjaan = document.getElementById('pekerjaan').options[document.getElementById('pekerjaan').selectedIndex].value;
	statusbg = document.getElementById('statusbg').options[document.getElementById('statusbg').selectedIndex].value;
	tipebg = document.getElementById('tipebg').options[document.getElementById('tipebg').selectedIndex].value;
	method = document.getElementById('method').value;
	kode = document.getElementById('kode').value;
	var tbl = document.getElementById("tablepersetujuan");
	var row = parseFloat(tbl.rows.length) + 1;
	strUrl = '';
	for (i = 1; i < row; i++) {
		strUrl += '&persetujuan[' + i + ']=' + document.getElementById('persetujuan' + i).options[document.getElementById('persetujuan' + i).selectedIndex].value;
	}
	param = 'aset=' + aset + '&unit=' + unit + '&sub=' + sub + '&jenis=' + jenis + '&jenisbiaya=' + jenisbiaya;
	param += '&nama=' + nama + '&tanggalmulai=' + tanggalmulai + '&tanggalselesai=' + tanggalselesai + '&kode=' + kode;
	param += '&pekerjaan=' + pekerjaan + '&statusbg=' + statusbg + '&tipebg=' + tipebg;
	param += '&method=' + method;
	param += strUrl;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadData(pg) {
	namacr = document.getElementById('namacr').value;
	unitcr = document.getElementById('unitcr').value;
	kodecr = document.getElementById('kodecr').value;
	param = 'method=loadData';
	param += '&page=' + pg;
	if (namacr != '') {
		param += '&namacr=' + namacr;
	}
	if (unitcr != '') {
		param += '&unitcr=' + unitcr;
	}
	if (kodecr != '') {
		param += '&kodecr=' + kodecr;
	}
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					batal();
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
function hapus(kode) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	param = 'kode=' + kode + '&method=delete';
	if (confirm('Delete/Hapus ' + kode + '?')) {
		tujuan = 'vhc_slave_capex.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done.');
					loadData(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function postIni(kode) {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	param = 'method=postingData' + '&kode=' + kode + '&unit=' + unit;
	tujuan = 'vhc_slave_capex.php';
	if (confirm("Anda Yakin Ingin Ajukan Kode :" + kode)) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadData(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function fillField(aset, unit, sub, jenis, jenisbiaya, nama, tanggalmulai, tanggalselesai, pekerjaan, statusbg, tipebg, kode, method, htdt) {
	document.getElementById('aset').value = aset;
	document.getElementById('unit').value = unit;
	document.getElementById('sub').value = sub;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('method').value = method;
	document.getElementById('kode').value = kode;
	document.getElementById('pekerjaan').value = pekerjaan;
	document.getElementById('statusbg').value = statusbg;
	document.getElementById('tipebg').value = tipebg;
	document.getElementById('jenis').value = jenis;
	document.getElementById('nama').value = nama;
	document.getElementById('unit').disabled = true;
	document.getElementById('aset').disabled = true;
	document.getElementById('jenis').disabled = true;
	document.getElementById('sub').disabled = true;
	document.getElementById('tanggalmulai').disabled = true;
	document.getElementById('tanggalselesai').disabled = true;
	document.getElementById('nama').disabled = true;
	param = 'method=fillField' + '&kode=' + kode;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getjbiaya(jenisbiaya, kode, htdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detailForm(aset, unit, sub, jenis, jenisbiaya, nama, tanggalmulai, tanggalselesai, pekerjaan, statusbg, tipebg, kode, method, htdt) {
	// document.getElementById('sub').innerHTML="<option value='"+ sub +"'>"+ namasub +"</option>"
	// document.getElementById('unit').value=unit;
	// document.getElementById('aset').value=aset;
	// document.getElementById('jenis').value=jenis;
	// document.getElementById('nama').value=nama;
	// document.getElementById('tanggalmulai').value=tanggalmulai;
	// document.getElementById('tanggalselesai').value=tanggalselesai;
	document.getElementById('method').value = 'insertdt';
	// document.getElementById('kode').value=kode;
	document.getElementById('kdProj').value = kode;
	// document.getElementById('unit').disabled=true;
	// document.getElementById('aset').disabled=true;
	// document.getElementById('jenis').disabled=true;
	// document.getElementById('tanggalselesai').disabled=true;
	// document.getElementById('tanggalmulai').disabled=true;
	// document.getElementById('nama').disabled=true;
	// document.getElementById('sub').value=sub;
	// document.getElementById('sub').disabled=true;
	param = 'method=' + method + '&kode=' + kode;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailInput').style.display = 'block';
					document.getElementById('dataDisimpan').style.display = 'none';
					document.getElementById('printDat').innerHTML = con.responseText;
					fillField(aset, unit, sub, jenis, jenisbiaya, nama, tanggalmulai, tanggalselesai, pekerjaan, statusbg, tipebg, kode, 'insertdt', htdt);
					document.getElementById('jenisbiaya').disabled = true;
					document.getElementById('pekerjaan').disabled = true;
					document.getElementById('statusbg').disabled = true;
					document.getElementById('tipebg').disabled = true;
					document.getElementById('btnsimpan').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cekdeskripsi(deskripsi, ev) {
	sub = document.getElementById('sub').options[document.getElementById('sub').selectedIndex].value;
	param = 'method=cekdeskripsi' + '&deskripsiKeg=' + deskripsi.value + '&sub=' + sub;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdeskripsi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addDetail() {
	kd = document.getElementById('kdProj').value;
	nmKeg = document.getElementById('namaKeg').value;
	tglMul = document.getElementById('tanggalMulai').value;
	tglSmp = document.getElementById('tanggalSampai').value;
	knci = document.getElementById('kegId').value;
	met = document.getElementById('method').value;
	satKeg = document.getElementById('satKeg').value;
	volKeg = document.getElementById('volKeg').value;
	hargaKeg = document.getElementById('hargaKeg').value;
	hkKeg = document.getElementById('hkKeg').value;
	rupiahhkKeg = document.getElementById('rupiahhkKeg').value;
	bobotKeg = document.getElementById('bobotKeg').value;
	deskripsiKeg = document.getElementById('deskripsiKeg').value;
	param = 'kode=' + kd + '&nmKeg=' + nmKeg + '&tglMul=' + tglMul + '&tglSmp=' + tglSmp;
	param += '&index=' + knci + '&method=' + met;
	param += '&satKeg=' + satKeg + '&volKeg=' + volKeg + '&hargaKeg=' + hargaKeg + '&hkKeg=' + hkKeg + '&rupiahhkKeg=' + rupiahhkKeg + '&bobotKeg=' + bobotKeg + '&deskripsiKeg=' + deskripsiKeg;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalKeg();
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batalKeg() {
	document.getElementById('deskripsiKeg').value = "";
	document.getElementById('namaKeg').value = "";
	document.getElementById('satKeg').selectedIndex = 0;
	document.getElementById('volKeg').value = '';
	document.getElementById('hargaKeg').value = '';
	document.getElementById('hkKeg').value = '';
	document.getElementById('rupiahhkKeg').value = '';
	document.getElementById('bobotKeg').value = '';
	document.getElementById('method').value = 'insertdt';
}
function loadDetail() {
	kd = document.getElementById('kdProj').value;
	param = 'method=detail' + '&kode=' + kd;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('printDat').innerHTML = con.responseText;
					document.getElementById('method').value = 'insertdt';
					document.getElementById('namaKeg').value = '';
					// document.getElementById('tanggalMulai').value=date('d-m-Y');
					// document.getElementById('tanggalSampai').value=date('d-m-Y');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editDet(tanggalmulai, tanggalselesai, method, dekripsi, kode, knci, nmkeg, satKeg, volKeg, hargaKeg, hkKeg, rupiahhkKeg, bobotKeg) {
	document.getElementById('kdProj').value = kode;
	document.getElementById('deskripsiKeg').value = dekripsi;
	document.getElementById('namaKeg').value = nmkeg;
	document.getElementById('tanggalMulai').value = tanggalmulai;
	document.getElementById('tanggalSampai').value = tanggalselesai;
	document.getElementById('kegId').value = knci;
	//document.getElementById('satKeg').value=satKeg;
	setValue('satKeg', satKeg);
	document.getElementById('volKeg').value = volKeg;
	document.getElementById('hargaKeg').value = hargaKeg;
	document.getElementById('hkKeg').value = hkKeg;
	document.getElementById('rupiahhkKeg').value = rupiahhkKeg;
	document.getElementById('bobotKeg').value = bobotKeg;
	document.getElementById('method').value = method;
}
function hapusData(kode) {
	param = 'index=' + kode + '&method=hpsDetail';
	if (confirm('Delete/Hapus Detail ?')) {
		tujuan = 'vhc_slave_capex.php';
		post_response_text(tujuan, param, respog);
	}
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
}
function tambahBarang(kegiatan, kodeproject, title, ev) {
	content = "<div id=formBarang style=\"height:450px;width:800px;overflow:scroll;\"></div>";
	title = 'Pengajuan Capex bangunan : ' + kodeproject;
	width = '800';
	height = '450';
	showDialog1(title, content, width, height, ev);
	getListBarang(kegiatan, kodeproject);
}
function getListBarang(kegiatan, kodeproject) {
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param = 'method=getListBarang' + '&kegiatan=' + kegiatan + '&kodeproject=' + kodeproject + '&unit=' + unit;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariListBarang(kegiatan, kodeproject) {
	namaBarangCari = document.getElementById('namaBarangCari').value;
	param = 'method=getListBarang' + '&namaBarangCari=' + namaBarangCari + '&kegiatan=' + kegiatan + '&kodeproject=' + kodeproject;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancelFormBarang(kegiatan, kodeproject) {
	document.getElementById('kodeBarangForm').value = '';
	document.getElementById('namaBarangForm').value = '';
	document.getElementById('jumlahBarangForm').value = '';
	document.getElementById('methodmat').value = 'saveFormBarang';
	getListBarang(kegiatan, kodeproject);
}
function saveFormBarang(kegiatan, kodeproject) {
	kodeproject = document.getElementById('kodeproject').value;
	kodekegiatan = document.getElementById('kodekegiatan').value;
	kodeBarangForm = document.getElementById('kodeBarangForm').value;
	jumlahBarangForm = document.getElementById('jumlahBarangForm').value;
	hargaBarangForm = document.getElementById('hargaBarangForm').value;
	method = document.getElementById('methodmat').value;
	param = 'method=' + method + '&kodeproject=' + kodeproject + '&kodekegiatan=' + kodekegiatan + '&kodeBarangForm=' + kodeBarangForm + '&jumlahBarangForm=' + jumlahBarangForm + '&hargaBarangForm=' + hargaBarangForm;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancelFormBarang(kegiatan, kodeproject);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editMaterial(kodebarang, namabarang, satuan, jumlah, harga) {
	document.getElementById('kodeBarangForm').value = kodebarang;
	document.getElementById('namaBarangForm').value = namabarang;
	document.getElementById('satuanBarangForm').value = satuan;
	document.getElementById('jumlahBarangForm').value = jumlah;
	document.getElementById('hargaBarangForm').value = harga;
	document.getElementById('methodmat').value = 'editFormBarang';
}
function delMaterial(kodeproject, kegiatan, kodebarang) {
	param = 'method=deleteMaterial' + '&kodeproject=' + kodeproject + '&kegiatan=' + kegiatan + '&kodebarang=' + kodebarang;
	tujuan = 'vhc_slave_capex.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancelFormBarang(kegiatan, kodeproject);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function printpdf(kode, ev) {
	param = "method=printpdf&kode=" + kode;
	showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:395px' src='vhc_slave_capex.php?" + param + "'></iframe>", '', '', ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}
function doneSlsi() {
	//waktu=date('d-m-Y');
	document.getElementById('unit').value = '';
	document.getElementById('aset').value = '';
	document.getElementById('jenis').value = '';
	document.getElementById('jenisbiaya').value = '';
	document.getElementById('nama').value = '';
	document.getElementById('method').value = 'insert';
	document.getElementById('kode').value = '';
	document.getElementById('kdProj').value = '';
	document.getElementById('unit').disabled = false;
	document.getElementById('aset').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('tanggalselesai').disabled = false;
	document.getElementById('tanggalmulai').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('detailInput').style.display = 'none';
	document.getElementById('dataDisimpan').style.display = 'block';
	document.getElementById('printDat').innerHTML = '';
	//document.getElementById('tanggalmulai').value=waktu;
	//document.getElementById('tanggalselesai').value=waktu;
}
//excel timeframe
function timeFrame(ev, kode) {
	param = 'method=timeFrame' + '&kode=' + kode;
	//alert(param);
	tujuan = 'vhc_slave_capex.php';
	judul = 'Time Frame ' + kode;
	printFile(param, tujuan, judul, ev)
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '600';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
}
function excelMaterial(ev, kode) {
	param = 'method=excelMaterial' + '&kode=' + kode;
	//alert(param);
	tujuan = 'vhc_slave_capex.php';
	judul = 'Material ' + kode;
	printFile(param, tujuan, judul, ev)
}
////////////////////
//BUKA MATERIAL
////////////////////
function moveDataBarang(kodebarang, namabarang, satuanbarang, hargabarang) {
	document.getElementById('kodeBarangForm').value = kodebarang;
	document.getElementById('namaBarangForm').value = namabarang;
	document.getElementById('satuanBarangForm').value = satuanbarang;
	document.getElementById('hargaBarangForm').value = hargabarang;
	//document.getElementById('').innerHTML=con.responseText;
	document.getElementById('listCariBarang').style.display = 'none';
}
//TUTUP MATERIAL
////////////////////