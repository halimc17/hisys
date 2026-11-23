// Dropdown memakai select2: nilai yang diisi lewat kode harus menyegarkan tampilan select2 tanpa memicu onchange bawaan
function refreshSel2(id) {
	var el = document.getElementById(id);
	if (el && window.jQuery && jQuery(el).data('select2')) {
		var h = el.onchange;
		el.onchange = null;
		jQuery(el).trigger('change.select2');
		el.onchange = h;
	}
}
function setSel2(id, nilai) {
	var el = document.getElementById(id);
	if (el) {
		el.value = nilai;
		refreshSel2(id);
	}
}


function viewbiaya(kode) {
    param = 'kode='+kode+'&method=viewbiaya';
    tujuan = 'vhc_slave_project.php';
    title = 'Detail Project ' + kode;
    tujuan = tujuan + "?" + param;
    // width = 500;
    // height = 300;
    // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+" style=background:#E8F4F4></iframe>";
    // showDialog5(title, content, width, height, 'event');
    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}




function showhide(e){
	// naik menjadi aset: tampilkan akun aset (debet) dan akun CIP (kredit) yang akan dipakai; alokasi biaya: pilih akun biaya
	var asset = (e == 'asset');
	document.getElementById('rowakun').style.display = asset ? 'none' : '';
	document.getElementById('rowkary').style.display = asset ? 'none' : '';
	var a1 = document.getElementById('rowakunasset1');
	var a2 = document.getElementById('rowakunasset2');
	if (a1) {
		a1.style.display = asset ? '' : 'none';
	}
	if (a2) {
		a2.style.display = asset ? '' : 'none';
	}
}
function displayList() {
	document.getElementById('formdetailinput').style.display = 'block';
	document.getElementById('dataDisimpan').style.display = 'block';
	document.getElementById('detailInputAK').style.display = 'none';
	document.getElementById('forminput').style.display = 'none';
	document.getElementById('detailInput').style.display = 'none';
	loadData(0);
}

function add_new_data(){
	document.getElementById('dataDisimpan').style.display = 'none';
	document.getElementById('detailInputAK').style.display = 'none';
	document.getElementById('formdetailinput').style.display = 'none';
	document.getElementById('forminput').style.display = 'block';
	batal();
}

function searchnocapex(title, ev) {
	content = "<div style='width:100%;'>";
	content += "<fieldset><div id=divpic style='overflow:auto;max-height:317px;'></div></fieldset>";
	width = 'auto';
	height = 'auto';
	showDialog1('Search Capex', content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = (pos[0]) + 'px';
	param = 'method=searchnocapex';
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divpic').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function caricapex() {
	crcapex = document.getElementById('crcapex').value;
	param = 'method=caricapex&crcapex=' + crcapex;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listnorequest').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showdetail(kodecapex, kodeorg, aset, subtipeaset, jenisbiaya, tipebg, pekerjaan, nama, tanggalmulai, tanggalselesai) {
	closeDialog();
	document.getElementById('kodecapex').value = kodecapex;
	setSel2('unit', kodeorg);
	setSel2('aset', aset);
	setSel2('tipebg', tipebg);
	setSel2('pekerjaan', pekerjaan);
	document.getElementById('nama').value = nama;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;

	getSub(subtipeaset, jenisbiaya,aset);

	document.getElementById('unit').disabled = true;
	document.getElementById('aset').disabled = true;
	document.getElementById('sub').disabled = true;
	document.getElementById('jenisbiaya').disabled = true;
	document.getElementById('tipebg').disabled = true;
	document.getElementById('pekerjaan').disabled = true;
	document.getElementById('nama').disabled = true;
	document.getElementById('tanggalmulai').disabled = true;
	document.getElementById('tanggalselesai').disabled = true;

	document.getElementById('posisiasset').disabled = true;
	document.getElementById('tipelokasiasset').disabled = true;
	document.getElementById('nomesin').disabled = true;
	document.getElementById('norangka').disabled = true;
	document.getElementById('keterangan').disabled = true;
	// param = 'method=showdetail&norequest=' + norequest + '&gudang=' + gudang + '&pemilikbarang=' + pemilikbarang;
	// tujuan = 'log_slave_getpic.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
	// if (con.readyState == 4) {
	// if (con.status == 200) {
	// busy_off();
	// if (!isSaveResponse(con.responseText)) {
	// alert(con.responseText);
	// } else {
	// document.getElementById('listnorequest').innerHTML = con.responseText;
	// }
	// } else {
	// busy_off();
	// error_catch(con.status);
	// }
	// }
	// }
}

function getSub(sub, jenisbiaya,aset) {
	aset = document.getElementById('aset').value;
	param = 'method=getSub' + '&aset=' + aset + '&sub=' + sub;

	//alert(param);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);

				} else {
					//alert(con.responseText);
					document.getElementById('sub').innerHTML = con.responseText; refreshSel2('sub');
					getjbiaya(jenisbiaya,aset);

				}
			} else {
				busy_off();
				error_catch(con.status);

			}
		}
	}
}

function getjbiaya(jenisbiaya,aset) {

	unit = document.getElementById('unit').value;
	param = 'method=getjbiaya' + '&unit=' + unit + '&jenisbiaya=' + jenisbiaya;

	//alert(param);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);

				} else {
					//alert(con.responseText);
					document.getElementById('jenisbiaya').innerHTML = con.responseText; refreshSel2('jenisbiaya');
					// if(aset=='KD' || aset=='AB')
					// {
					// 	loadDetailAK();
					// }
					// else
					// {
					// 	loadDetail();
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);

			}
		}
	}
}

///


function simpan() {
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	aset = document.getElementById('aset').options[document.getElementById('aset').selectedIndex].value;
	jenis = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	tipebg = document.getElementById('tipebg').options[document.getElementById('tipebg').selectedIndex].value;
	pekerjaan = document.getElementById('pekerjaan').options[document.getElementById('pekerjaan').selectedIndex].value;
	jenisbiaya = document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value;
	sub = document.getElementById('sub').options[document.getElementById('sub').selectedIndex].value;
	satuan = trim(document.getElementById('satuan').value);
	nama = trim(document.getElementById('nama').value);
	jumlah = trim(document.getElementById('jumlah').value);
	tanggalmulai = trim(document.getElementById('tanggalmulai').value);
	tanggalselesai = trim(document.getElementById('tanggalselesai').value);
	method = document.getElementById('method').value;
	kode = document.getElementById('kode').value;
	kodecapex = document.getElementById('kodecapex').value;
	posisiasset = document.getElementById('posisiasset').value;
	tipelokasiasset = document.getElementById('tipelokasiasset').value;
	nomesin = document.getElementById('nomesin').value;
	norangka = document.getElementById('norangka').value;
	tipemodel = document.getElementById('tipemodel').value;
	keterangan = document.getElementById('keterangan').value;
	dgnapprvl = document.getElementById('dgnapprvl');
	aprv1 = document.getElementById('aprv1').value;
	aprv2 = document.getElementById('aprv2').value;
	aprv3 = document.getElementById('aprv3').value;
	aprv4 = document.getElementById('aprv4').value;

	if (unit == '') {
		alert('Please fill UNIT');
		return;
	}
	if (aset == '') {
		alert('Please fill ASSET');
		return;
	}
	if (nama == '') {
		alert('Please fill NAMA');
		return;
	}
	if (sub == '') {
		alert('Please fill SUB ASSET');
		return;
	}
	if (jenisbiaya == '') {
		alert('Please fill JENIS BIAYA');
		return;
	}
	if (tanggalmulai == '') {
		alert('Please fill TANGGAL MULAI');
		return;
	}
	if (tanggalselesai == '') {
		alert('Please fill TANGGAL SELESAI');
		return;
	}
	if (tipebg == '') {
		alert('Please fill TIPE/TYPE');
		return;
	}
	if (pekerjaan == '') {
		alert('Please fill PEKERJAAN');
		return;
	}
	if (satuan == '') {
		alert('Please fill SATUAN');
		return;
	}
	if (jumlah == '' || jumlah == 0) {
		alert('Please fill JUMLAH');
		return;
	}
	if (dgnapprvl.checked == true){
		dgnapprvl = 1; 
		// return false; 
	}else{
		dgnapprvl = 0;
	}
	// if (posisiasset == '') {
		// alert('Please fill POSISI ASSET');
		// return;
	// }
	// if (tipelokasiasset == '') {
		// alert('Please fill TIPE LOKASI');
		// return;
	// }
	// if (nomesin == '') {
		// alert('Please fill NO MESIN');
		// return;
	// }
	// if (norangka == '') {
		// alert('Please fill NO RANGKA');
		// return;
	// }

	// form ini hanya untuk simpan/ubah proyek; saat mode Rincian (insertDetail dst.) simpan dilakukan lewat tombol di bagian Rincian
	if (method != 'insert' && method != 'update') {
		alert('Mode Rincian sedang aktif. Simpan rincian lewat ikon simpan di bagian Rincian, lalu tekan Selesai.');
		return;
	}
	var e = encodeURIComponent;
	param = 'unit=' + e(unit) + '&aset=' + e(aset) + '&jenis=' + e(jenis) + '&jenisbiaya=' + e(jenisbiaya) + '&tipebg=' + e(tipebg) + '&pekerjaan=' + e(pekerjaan);
	param += '&nama=' + e(nama) + '&tanggalmulai=' + e(tanggalmulai) + '&tanggalselesai=' + e(tanggalselesai) + '&kode=' + e(kode) + '&sub=' + e(sub);
	param += '&kodecapex=' + e(kodecapex);
	param += '&satuan=' + e(satuan);
	param += '&jumlah=' + e(jumlah);
	param += '&posisiasset=' + e(posisiasset);
	param += '&tipelokasiasset=' + e(tipelokasiasset);
	param += '&nomesin=' + e(nomesin);
	param += '&norangka=' + e(norangka);
	param += '&tipemodel=' + e(tipemodel);
	param += '&keterangan=' + e(keterangan);
	param += '&dgnapprvl=' + dgnapprvl;
	param += '&aprv1=' + e(aprv1);
	param += '&aprv2=' + e(aprv2);
	param += '&aprv3=' + e(aprv3);
	param += '&aprv4=' + e(aprv4);
	//  param+='&kelompok='+kelompok+'&nilai='+nilai;
	param += '&method=' + method;
	if (confirm('Save/Simpan?')) {
		tujuan = 'vhc_slave_project.php';
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
					alert('Done.');
					//document.getElementById('container').innerHTML=con.responseText;
					document.getElementById('formdetailinput').style.display = 'block';
					document.getElementById('dataDisimpan').style.display = 'block';
					document.getElementById('detailInputAK').style.display = 'none';
					document.getElementById('forminput').style.display = 'none';
					document.getElementById('detailInput').style.display = 'none';
					loadData(0);
					//batal();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batal() {
	/*var d = new Date();
	var curr_date = d.getDate();


	var curr_month = d.getMonth() + 1; //Months are zero based
	var curr_year = d.getFullYear();
	d1=curr_date + "-" + curr_month + "-" + curr_year;*/

	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1; //Months are zero based
	var curr_year = d.getFullYear();
	if (curr_date.length == 1) {
		curr_date = '0' + curr_date;
	}

	d1 = curr_date + "-" + curr_month + "-" + curr_year;

	document.getElementById('kodecapex').value = '';
	setSel2('unit', '');
	setSel2('aset', '');
	setSel2('jenisbiaya', '');
	setSel2('jenis', 'AK');
	document.getElementById('nama').value = '';
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalselesai').value = '';
	document.getElementById('method').value = 'insert';
	document.getElementById('kode').value = '';
	setSel2('sub', '');
	setSel2('tipebg', '');
	setSel2('pekerjaan', '');
	setSel2('satuan', '');
	document.getElementById('jumlah').value = '';
	document.getElementById('keterangan').value = '';
	setSel2('posisiasset', '');
	setSel2('tipelokasiasset', '');
	document.getElementById('nomesin').value = '';
	document.getElementById('norangka').value = '';
	document.getElementById('tipemodel').value = '';
	for (var ap = 1; ap <= 4; ap++) {
		setSel2('aprv' + ap, '');
		document.getElementById('aprv' + ap).disabled = false;
	}
	document.getElementById('dgnapprvl').disabled = false;

	document.getElementById('unit').disabled = false;
	document.getElementById('aset').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('sub').disabled = false;
	document.getElementById('tanggalmulai').disabled = false;
	document.getElementById('tanggalselesai').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('tipebg').disabled = false;
	document.getElementById('pekerjaan').disabled = false;
	document.getElementById('satuan').disabled = false;
	document.getElementById('jenisbiaya').disabled = false;
	document.getElementById('dgnapprvl').checked = false;
	document.getElementById('formapproval').hidden = true; 

	document.getElementById('imgsearch').style.display = '';
	document.getElementById('imgdelete').style.display = '';

	document.getElementById('detailInput').style.display = 'none';
	document.getElementById('detailInputAK').style.display = 'none';
	document.getElementById('dataDisimpan').style.display = 'none';

	document.getElementById('kdProj').value='';
	document.getElementById('kdProjx').value='';
}

function fillField(unit, aset, jenis, nama, tanggalmulai, tanggalselesai, method, kode, sub, jenisbiaya, tipebg, pekerjaan, kodecapex, satuan, jumlah, keterangan, posisiasset, tipelokasiasset, nomesin, norangka, tipemodel,dgnapprvl,statuspersetujuan,kryApr1,kryApr2,kryApr3,kryApr4){
	document.getElementById('kodecapex').value = kode;
	setSel2('unit', unit);
	setSel2('aset', aset);
	setSel2('jenis', jenis);
	setSel2('jenisbiaya', jenisbiaya);
	document.getElementById('nama').value = nama;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	setSel2('tipebg', tipebg);
	setSel2('pekerjaan', pekerjaan);
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('method').value = method;
	document.getElementById('kode').value = kode;
	setSel2('satuan', satuan);
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('keterangan').value = keterangan;
	setSel2('posisiasset', posisiasset);
	setSel2('tipelokasiasset', tipelokasiasset);
	document.getElementById('nomesin').value = nomesin;
	document.getElementById('norangka').value = norangka;
	document.getElementById('tipemodel').value = tipemodel;
	
	changetipelokasi(tipelokasiasset);

	// if (kodecapex != '') {
		// document.getElementById('jenisbiaya').disabled = true;
	// }
	if (dgnapprvl == '1'){
		document.getElementById('dgnapprvl').checked = true;
	}else{
		document.getElementById('dgnapprvl').checked = false;
	}
	
	// Persetujuan hanya tampil bila proyek memakai approval; terkunci bila sudah disetujui
	var aprvKunci = (statuspersetujuan == 1);
	document.getElementById('formapproval').hidden = (dgnapprvl != '1');
	document.getElementById('dgnapprvl').disabled = aprvKunci;
	document.getElementById('aprv1').disabled = aprvKunci;
	document.getElementById('aprv2').disabled = aprvKunci;
	document.getElementById('aprv3').disabled = aprvKunci;
	document.getElementById('aprv4').disabled = aprvKunci;
	setSel2('aprv1', kryApr1);
	setSel2('aprv2', kryApr2);
	setSel2('aprv3', kryApr3);
	setSel2('aprv4', kryApr4);
	
	document.getElementById('jenisbiaya').disabled = true;
	document.getElementById('unit').disabled = true;
	document.getElementById('aset').disabled = true;
	document.getElementById('jenis').disabled = true;
	document.getElementById('tipebg').disabled = true;
	document.getElementById('pekerjaan').disabled = true;

	document.getElementById('sub').disabled = true;
	document.getElementById('tanggalmulai').disabled = true;
	document.getElementById('tanggalselesai').disabled = true;
	document.getElementById('nama').disabled = true;
	document.getElementById('satuan').disabled = true;

	document.getElementById('imgsearch').style.display = 'none';
	document.getElementById('imgdelete').style.display = 'none';

	getSub(sub, jenisbiaya);
	document.getElementById('dataDisimpan').style.display = 'none';
	document.getElementById('detailInputAK').style.display = 'block';
	document.getElementById('forminput').style.display = 'block';
	document.getElementById('formdetailinput').style.display = 'none';
}

function detailForm(unit, aset, jenis, nama, tanggalmulai, tanggalselesai, method, kode, sub, jenisbiaya, tipebg, pekerjaan, kodecapex,satuan,jumlah, keterangan, posisiasset, tipelokasiasset, nomesin, norangka, tipemodel) {
	document.getElementById('kodecapex').value = kode;
	setSel2('unit', unit);
	setSel2('aset', aset);
	setSel2('jenis', jenis);
	setSel2('jenisbiaya', jenisbiaya);
	document.getElementById('nama').value = nama;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	setSel2('tipebg', tipebg);
	setSel2('pekerjaan', pekerjaan);
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('method').value = method;
	document.getElementById('kode').value = kode;
	setSel2('satuan', satuan);
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('keterangan').value = keterangan;
	setSel2('posisiasset', posisiasset);
	setSel2('tipelokasiasset', tipelokasiasset);
	document.getElementById('nomesin').value = nomesin;
	document.getElementById('norangka').value = norangka;
	document.getElementById('tipemodel').value = tipemodel || '';
	document.getElementById('formapproval').hidden = true;

	// if (kodecapex != '') {
		// document.getElementById('jenisbiaya').disabled = true;
	// }

	document.getElementById('unit').disabled = true;
	document.getElementById('aset').disabled = true;
	document.getElementById('jenis').disabled = true;
	document.getElementById('tipebg').disabled = true;
	document.getElementById('pekerjaan').disabled = true;
	document.getElementById('jenisbiaya').disabled = true;

	document.getElementById('sub').disabled = true;
	document.getElementById('tanggalmulai').disabled = true;
	document.getElementById('tanggalselesai').disabled = true;
	document.getElementById('nama').disabled = true;

	document.getElementById('imgsearch').style.display = 'none';
	document.getElementById('imgdelete').style.display = 'none';

	if(aset=='KD' || aset=='AB')
	{
		document.getElementById('kdProj').value=kode;
		method='detailAK';
	}
	else
	{
		document.getElementById('kdProjx').value=kode;
		method='detail';
	}
	
	param = 'method=' + method + '&kode=' + kode;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('forminput').style.display = 'block';
					//alert(jenis);
					if(aset=='KD' || aset=='AB'){
						document.getElementById('detailInputAK').style.display = 'block';
						document.getElementById('method').value='insertDetailAK';
						document.getElementById('printDat').innerHTML = con.responseText;
					}else{
						document.getElementById('detailInput').style.display = 'block';
						document.getElementById('printDatx').innerHTML = con.responseText;
						document.getElementById('method').value='insertDetail';
					}
					//alert(document.getElementById('method').value);
					document.getElementById('dataDisimpan').style.display = 'none';

					var x2 = document.getElementsByName("excapex2");
					var x3 = document.getElementsByName("excapex3");
					var i;
					if (kodecapex != '') {
						var fsInput = document.getElementById(aset=='KD' || aset=='AB' ? 'excapex1' : 'excapex1x');
						fsInput.style.display = 'none';
						for (i = 0; i < x2.length; i++) {
							x2[i].style.display = "none";
							x3[i].style.display = "none";
						}
					} else {
						var fsInput = document.getElementById(aset=='KD' || aset=='AB' ? 'excapex1' : 'excapex1x');
						fsInput.style.display = '';
						for (i = 0; i < x2.length; i++) {
							x2[i].style.display = "";
							x3[i].style.display = "";
						}
					}
					getSub(sub, jenisbiaya,aset);
					loadDetail(tipelokasiasset);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function doneSlsi() {
	//waktu=date('d-m-Y');
	setSel2('unit', '');
	setSel2('aset', '');
	setSel2('jenis', '');
	setSel2('jenisbiaya', '');
	document.getElementById('nama').value = '';
	document.getElementById('method').value = 'insert';
	document.getElementById('kode').value = '';
	document.getElementById('kdProj').value = '';
	document.getElementById('kdProjx').value = '';
	document.getElementById('unit').disabled = false;
	document.getElementById('aset').disabled = false;
	document.getElementById('jenis').disabled = false;
	document.getElementById('tanggalselesai').disabled = false;
	document.getElementById('tanggalmulai').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('detailInput').style.display = 'none';
	document.getElementById('detailInputAK').style.display = 'none';
	document.getElementById('dataDisimpan').style.display = 'block';
	document.getElementById('printDat').innerHTML = '';
	document.getElementById('printDatx').innerHTML = '';
	//document.getElementById('tanggalmulai').value=waktu;
	//document.getElementById('tanggalselesai').value=waktu;
}
function editDet(tanggalmulai, tanggalselesai, method, kode, knci, deskripsi, nmkeg, satKeg, volKeg, bobotKeg) {
	document.getElementById('kdProjx').value = kode;
	document.getElementById('deskripsi').value = deskripsi;
	document.getElementById('namaKeg').value = nmkeg;
	document.getElementById('tanggalMulai').value = tanggalmulai;
	document.getElementById('tanggalSampai').value = tanggalselesai;
	document.getElementById('kegId').value = knci;
	setSel2('satKeg', satKeg);
	document.getElementById('volKeg').value = volKeg;
	document.getElementById('bobotKeg').value = bobotKeg;
	document.getElementById('method').value = method;
}

function editDetAK(nomesin, norangka, method, kode, tahunproduksi, tahunprolehan,keg) {
	document.getElementById('kdProjx').value = kode;
	document.getElementById('nomesin').value = nomesin;
	document.getElementById('norangka').value = norangka;
	document.getElementById('tahunproduksi').value = tahunproduksi;
	document.getElementById('tahunprolehan').value = tahunprolehan;
	document.getElementById('kegiatanx').innerHTML = keg;
	document.getElementById('method').value = method;
}

function hapus(kode) {
	document.getElementById('method').value = 'hapus';
	param = 'kode=' + kode + '&method=delete';
	if (confirm('Delete/Hapus ' + kode + '?')) {
		tujuan = 'vhc_slave_project.php';
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
					alert('Done.');
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// satu sumber filter List Data untuk tampil, Excel, dan PDF
function getFilterParamCIP() {
	var id = ['namacr', 'unitcr', 'kodecr', 'jeniscr', 'asetcr', 'pekerjaancr', 'statuscr', 'tahuncr'];
	var param = '';
	for (var i = 0; i < id.length; i++) {
		var el = document.getElementById(id[i]);
		var v = el ? el.value.trim() : '';
		if (v != '') {
			param += '&' + id[i] + '=' + encodeURIComponent(v);
		}
	}
	return param;
}

// ikon List: kembalikan semua filter ke awal
function resetFilterCIP() {
	var teks = ['namacr', 'kodecr'];
	for (var i = 0; i < teks.length; i++) {
		document.getElementById(teks[i]).value = '';
	}
	var pil = ['unitcr', 'jeniscr', 'asetcr', 'pekerjaancr', 'statuscr', 'tahuncr'];
	for (var j = 0; j < pil.length; j++) {
		setSel2(pil[j], '');
	}
}

function tampilTarikanCIP(judul, tujuan) {
	alertify
		.popup(judul, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>")
		.set({ resizable: true, maximizable: true, overflow: false })
		.resizeTo('80%', '70%');
}

function exportExcelCIP() {
	tampilTarikanCIP('Report Ms.Excel', 'vhc_slave_project.php?method=excellist' + getFilterParamCIP());
}

function exportPdfCIP() {
	tampilTarikanCIP('Report PDF', 'vhc_slave_project.php?method=pdflist' + getFilterParamCIP());
}

function loadData(num) {
	param = 'method=loadData';
	param += '&page=' + num;
	param += getFilterParamCIP();
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					// document.getElementById('container').innerHTML=con.responseText;
					isdt = con.responseText.split("####");
					document.getElementById('container').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
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

//excel timeframe
function timeFrame(ev, kode) {
	param = 'method=timeFrame' + '&kode=' + kode;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
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
	tujuan = 'vhc_slave_project.php';
	judul = 'Material ' + kode;
	printFile(param, tujuan, judul, ev)
}
function postIni(kd, unit, jnsbiaya,posisiasset) {
	if(unit!=posisiasset){
		alert('Unit asset dan posisi asset tidak sama, unit : '+unit+' ; posisi : '+posisiasset+' ');return;
	}
	//content+="<div id=formCariBarang></div>";
	title = 'Posting ' + kd;
	height = '110px';
	width = '1000px';
	content = "<fieldset><div id=formpost style=width:600px></div></fieldset>";
	if (confirm("Anda Yakin Ingin Memposting Kode :" + kd)) {
		//showDialog2(title, content, width, height, 'event');
		getformPost(kd, unit, jnsbiaya);
	}

} 

function approval(kd, unit, jnsbiaya) { 
	//content+="<div id=formCariBarang></div>";
	title = 'Posting ' + kd;
	height = '110px';
	width = '500px';
	content = "<fieldset><div id=formpost style=width:200px></div></fieldset>";
	// if (confirm("Ajukan Approval Kode :" + kd)) {
		//showDialog2(title, content, width, height, 'event');
		getformApproval(kd, unit, jnsbiaya);
	// }

}
function savePosting(kd, unit, jnsbiaya) {
	tgl = document.getElementById('tglpost').value;
	if(tgl==''){
		alert ("Tanggal wajib di isi !"); return;
	}
	alokasi = document.getElementById('alokasi').value;
	if(alokasi==''){
		alert ("Alokasi wajib di isi !"); return;
	}
	noakun = document.getElementById('noakun').value;
	namaaset = document.getElementById('namaasetposting').value;
	if(alokasi=='biaya' && noakun==''){
		alert ("Noakun wajib di isi !"); return;
	}
	
	tipemodel = document.getElementById('tipemodelposting').value;
	nomesin = document.getElementById('nomesinposting').value;
	norangka = document.getElementById('norangkaposting').value;
	karyawanid = document.getElementById('karyawanid').value;
	
	
	param = 'method=postingData' + '&kode=' + kd + '&unit=' + unit + '&tglpost=' + tgl + '&jnsbiaya=' + jnsbiaya;
	param += '&alokasi=' + alokasi + '&noakun=' + noakun + '&namaaset=' + namaaset;
	param += '&tipemodel=' + tipemodel + '&nomesin=' + nomesin + '&norangka=' + norangka+ '&karyawanid=' + karyawanid;
	
	tujuan = 'vhc_slave_project.php';
	if(alokasi=='biaya'){
		if (confirm("Project akan di biayakan ke akun "+noakun+", lanjutkan ???")) {
			post_response_text(tujuan, param, respog);
		}
	}else{		
		if (confirm("Project akan di buatkan Assetnya dengan tanggal perolehan : " + tgl +", lanjutkan ???")) {
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
					// closeDialog();
					alertify.popup().destroy();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editApproval(kodeproject) {
	aprv1 = document.getElementById('aprv1').value; 
	aprv2 = document.getElementById('aprv2').value; 

	// if(aprv1=='' || aprv2==''){
	// 	alert ("Persetujuan 1 & 2  Wajib di isi !!"); 
	// 	return false;
	// }  
	aprv3 = document.getElementById('aprv3').value; 
	aprv4 = document.getElementById('aprv4').value;  
	  
	param = 'method=editApproval'+ '&kodeproject=' + kodeproject + '&aprv1=' + aprv1+ '&aprv2=' + aprv2+ '&aprv3=' + aprv3+ '&aprv4=' + aprv4; 
	alert(param);
	return false;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					// closeDialog();
					alertify.popup().destroy();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		} 
	}
} 
function saveApproval(kodeproject) {
 
	param = 'method=saveApproval'+ '&kodeproject=' + kodeproject; 
	// alert(param);
	// return false;
	tujuan = 'vhc_slave_project.php';
	if(confirm("Anda Yakin Ingin Mengajukan Approval :" + kodeproject)){			
		post_response_text(tujuan, param, respog);
	}else{
		return;
	} 
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// if (confirm("Anda Yakin Ingin Mengajukan Approval :" + kodeproject)) {
					// 	//showDialog2(title, content, width, height, 'event');
					// 	getformPost(kodeproject);
					// }
					
					//alert(con.responseText);
					// closeDialog();
					alertify.popup().destroy();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		} 
	}
} 
// function saveApproval(kodeproject) {
// 	aprv1 = document.getElementById('aprv1').value; 
// 	aprv2 = document.getElementById('aprv2').value; 

// 	if(aprv1=='' || aprv2==''){
// 		alert ("Persetujuan 1 & 2  Wajib di isi !!"); 
// 		return false;
// 	}  
// 	aprv3 = document.getElementById('aprv3').value; 
// 	aprv4 = document.getElementById('aprv4').value;  
	  
// 	param = 'method=saveApproval'+ '&kodeproject=' + kodeproject + '&aprv1=' + aprv1+ '&aprv2=' + aprv2+ '&aprv3=' + aprv3+ '&aprv4=' + aprv4; 
// 	// alert(param);
// 	// return false;
// 	tujuan = 'vhc_slave_project.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					// closeDialog();
// 					alertify.popup().destroy();
// 					loadData();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		} 
// 	}
// } 
function getformPost(kd, unit, jnsbiaya) {
	var param = "kodeproject=" + kd + "&kodeorg=" + unit + "&jnsbiaya=" + jnsbiaya;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('formpost').innerHTML = con.responseText;
					alertify.popup().destroy();
					// alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','400px');
					
					// alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					alertify.popup("Posting",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					
					getSelect2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text('vhc_slave_project.php?method=showform', param, respon);
}

function getformApproval(kd, unit, jnsbiaya) {
	var param = "kodeproject=" + kd + "&kodeorg=" + unit + "&jnsbiaya=" + jnsbiaya;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('formpost').innerHTML = con.responseText;
					// alertify.popup().destroy();
					// alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('700px','400px');
					
					// alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					alertify.popup("Approval",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('60%','70%');
					
					getSelect2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text('vhc_slave_project.php?method=showformApproval', param, respon);
}
// dd-mm-yyyy -> yyyymmdd (angka) untuk membandingkan tanggal
function tanggalJs(t) {
	var p = String(t).split('-');
	return p.length == 3 ? parseInt(p[2] + p[1] + p[0], 10) : 0;
}

// kosongkan input rincian kegiatan dan kembali ke mode tambah (juga dipakai tombol Batal saat mengubah)
function batalKeg() {
	var d = new Date();
	var hariIni = ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();
	setSel2('satKeg', '');
	document.getElementById('deskripsi').value = '';
	document.getElementById('namaKeg').value = '';
	document.getElementById('volKeg').value = '';
	document.getElementById('bobotKeg').value = '';
	document.getElementById('tanggalMulai').value = hariIni;
	document.getElementById('tanggalSampai').value = hariIni;
	document.getElementById('kegId').value = '';
	document.getElementById('method').value = 'insertDetail';
}

function batalKegAK() {
	document.getElementById('norangka').value = '';
	document.getElementById('nomesin').value = '';
	document.getElementById('tahunproduksi').value = '';
	document.getElementById('tahunprolehan').value = '';
}


function addDetail() {
	kd = document.getElementById('kdProjx').value;
	deskripsi = document.getElementById('deskripsi').value;
	nmKeg = document.getElementById('namaKeg').value;
	tglMul = document.getElementById('tanggalMulai').value;
	tglSmp = document.getElementById('tanggalSampai').value;
	knci = document.getElementById('kegId').value;
	met = document.getElementById('method').value;
	satKeg = document.getElementById('satKeg').value;
	volKeg = document.getElementById('volKeg').value;
	bobotKeg = document.getElementById('bobotKeg').value;

	if (met != 'updatedet') {
		met = 'insertDetail';
	}
	// validasi (diulang di server)
	if (trim(nmKeg) == '') {
		alert('Please fill NAMA KEGIATAN');
		return;
	}
	if (satKeg == '') {
		alert('Please fill SATUAN');
		return;
	}
	if (trim(volKeg) == '' || isNaN(volKeg) || parseFloat(volKeg) <= 0) {
		alert('VOLUME harus berupa angka lebih dari 0');
		return;
	}
	if (trim(bobotKeg) == '' || isNaN(bobotKeg) || parseFloat(bobotKeg) < 0 || parseFloat(bobotKeg) > 100) {
		alert('BOBOT harus berupa angka 0 sampai 100');
		return;
	}
	if (tglMul == '' || tglSmp == '') {
		alert('Please fill TANGGAL MULAI dan TANGGAL SAMPAI');
		return;
	}
	if (tanggalJs(tglSmp) < tanggalJs(tglMul)) {
		alert('Tanggal Sampai tidak boleh lebih kecil dari Tanggal Mulai');
		return;
	}
	param = '&kode=' + encodeURIComponent(kd) + '&deskripsi=' + encodeURIComponent(deskripsi) + '&nmKeg=' + encodeURIComponent(nmKeg) + '&tglMul=' + encodeURIComponent(tglMul) + '&tglSmp=' + encodeURIComponent(tglSmp);
	param += '&index=' + encodeURIComponent(knci) + '&method=' + met;
	param += '&satKeg=' + encodeURIComponent(satKeg) + '&volKeg=' + encodeURIComponent(volKeg) + '&bobotKeg=' + encodeURIComponent(bobotKeg);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					// document.getElementById('container').innerHTML=con.responseText;
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

function addDetailAK() {
	kd = document.getElementById('kdProj').value;
	nomesin = document.getElementById('nomesin').value;
	norangka = document.getElementById('norangka').value;
	tahunproduksi = document.getElementById('tahunproduksi').value;
	tahunprolehan = document.getElementById('tahunprolehan').value;
	kegiatanx = document.getElementById('kegiatanx').innerHTML;
	//alert(kegiatanx);
	met = document.getElementById('method').value;
	//alert(met);
	param = '&kode=' + encodeURIComponent(kd) + '&nomesin=' + encodeURIComponent(nomesin) + '&norangka=' + encodeURIComponent(norangka) + '&tahunproduksi=' + encodeURIComponent(tahunproduksi) + '&tahunprolehan=' + encodeURIComponent(tahunprolehan);
	param += '&kegiatanx=' + encodeURIComponent(kegiatanx) + '&method=' + met;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					// document.getElementById('container').innerHTML=con.responseText;
					batalKegAK();
					loadDetailAK();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadDetail(tipelokasiasset) {
	kd = document.getElementById('kdProjx').value;
	param = 'method=detail' + '&kode=' + kd;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('printDatx').innerHTML = con.responseText;
					document.getElementById('method').value = 'insertDetail';
					document.getElementById('deskripsi').value = '';
					document.getElementById('namaKeg').value = '';
					changetipelokasi(tipelokasiasset);
					//document.getElementById('tanggalMulai').value = date('d-m-Y');
					//document.getElementById('tanggalSampai').value = date('d-m-Y');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadDetailAK() {
	kd = document.getElementById('kdProj').value;
	param = 'method=detailAK' + '&kode=' + encodeURIComponent(kd);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printDat').innerHTML = con.responseText;
					document.getElementById('method').value = 'insertDetailAK';
					document.getElementById('deskripsi').value = '';
					document.getElementById('namaKeg').value = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hapusData(kode) {
	param = 'index=' + kode + '&method=hpsDetail';
	if (confirm('Delete/Hapus Detail ?')) {
		tujuan = 'vhc_slave_project.php';
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
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hapusDataAK(kode) {
	param = 'index=' + encodeURIComponent(kode) + '&method=hpsDetailAK';
	if (confirm('Delete/Hapus Detail ?')) {
		tujuan = 'vhc_slave_project.php';
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
					loadDetailAK();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

////////////////////
//BUKA MATERIAL
////////////////////

function saveFormBarang(kegiatan, kodeproject) {

	//alert('MASUK');
	kodeproject = document.getElementById('kodeproject').value;
	kodekegiatan = document.getElementById('kodekegiatan').value;
	kodeBarangForm = document.getElementById('kodeBarangForm').value;
	jumlahBarangForm = document.getElementById('jumlahBarangForm').value;
	method = document.getElementById('method').value;

	//param='kodeproject='+kodeproject+'&kodekegiatan='+kodekegiatan+'&kodeBarangForm='+kodeBarangForm+'&jumlahBarangForm='+jumlahBarangForm+'&method='+saveFormBarang;
	param = 'method=saveFormBarang' + '&kodeproject=' + kodeproject + '&kodekegiatan=' + kodekegiatan + '&kodeBarangForm=' + kodeBarangForm + '&jumlahBarangForm=' + jumlahBarangForm;

	tujuan = 'vhc_slave_project.php';

	//alert(tujuan);
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText
					cancelFormBarang(kegiatan, kodeproject);

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

	//content+="<div id=formCariBarang></div>";

	title = 'Project : ' + kodeproject;

	width = '800';
	height = '450';
	showDialog1(title, content, width, height, ev);
	getListBarang(kegiatan, kodeproject);
}

function moveDataBarang(kodebarang, namabarang, satuanbarang) {
	document.getElementById('kodeBarangForm').value = kodebarang;
	document.getElementById('namaBarangForm').value = namabarang;
	document.getElementById('satuanBarangForm').value = satuanbarang;

	//document.getElementById('').innerHTML=con.responseText;
	document.getElementById('listCariBarang').style.display = 'none';

}

function cariListBarang(kegiatan, kodeproject) {
	//alert('MASUK');
	namaBarangCari = document.getElementById('namaBarangCari').value;
	//alert(kegiatan);
	param = 'method=getListBarang' + '&namaBarangCari=' + namaBarangCari + '&kegiatan=' + kegiatan + '&kodeproject=' + kodeproject;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function delMaterial(kodeproject, kegiatan, kodebarang) {
	param = 'method=deleteMaterial' + '&kodeproject=' + kodeproject + '&kegiatan=' + kegiatan + '&kodebarang=' + kodebarang;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
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

function cancelFormBarang(kegiatan, kodeproject) {

	//document.getElementById('kodekegiatan').value=kodek
	//kodeproject


	document.getElementById('kodeBarangForm').value = '';
	document.getElementById('namaBarangForm').value = '';
	document.getElementById('jumlahBarangForm').value = '';
	getListBarang(kegiatan, kodeproject);
	//document.getElementById('listCariBarang').style.display='none';
}

function getListBarang(kegiatan, kodeproject) {
	param = 'method=getListBarang' + '&kegiatan=' + kegiatan + '&kodeproject=' + kodeproject;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

////////////////////
//TUTUP MATERIAL
////////////////////
/*
function viewasset(kode) {
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contviewasset style=\"width:100%;height:100%;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=viewasset' + '&kode=' + kode;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewasset').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
*/



function viewasset(kode) {
    param = 'method=viewasset' + '&kode=' + kode;
	tujuan = 'vhc_slave_project.php';
    title = 'Detail Asset ' + kode;
    tujuan = tujuan + "?" + param;
    // width = 500;
    // height = 300;
    // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+" style=background:#E8F4F4></iframe>";
    // showDialog5(title, content, width, height, 'event');
    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function changetipelokasi(lokasi) {
	posisiasset = document.getElementById('posisiasset').value;
	param = 'method=changetipelokasi' + '&posisiasset=' + posisiasset+ '&lokasi=' + lokasi;
		//alert(param);
		tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipelokasiasset').innerHTML = con.responseText; refreshSel2('tipelokasiasset');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cekapproval() {
	dgnapprvl = document.getElementById('dgnapprvl');
	formapproval = document.getElementById('formapproval');
	if (dgnapprvl.checked == true){ 
		document.getElementById('formapproval').hidden = false; 
	}  else {
		document.getElementById('formapproval').hidden = true; 

	}
}


// Upload File
function showupload(notransaksi) {
	ev = "event";
	//showformupload(ev);
	param = "method=showupload&notransaksi=" + notransaksi;
  
	tujuan = "vhc_slave_project.php";
	post_response_text(tujuan, param, respog);
  
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			//document.getElementById('contUpload').innerHTML=con.responseText;
			alertify.popup().destroy();
			alertify
			  .popup("Upload", con.responseText)
			  .set({ resizable: true, overflow: false })
			  .resizeTo("400px", "400px");
  
			loadfiles(notransaksi);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  // fungsi untuk progress bar
  function progressHandler(event) {
	document.getElementById("progressBar").style.display = "block";
	document.getElementById("loaded_n_total").innerHTML =
	  "Uploaded " +
	  numberFormat(Math.round(event.loaded / 1024)) +
	  " KB of " +
	  numberFormat(Math.round(event.total / 1024)) +
	  " KB";
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML =
	  Math.round(percent) + "% uploaded... please wait";
  }
  function completeHandler(event) {
	document.getElementById("progressBar").style.display = "none";
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
  }
  function errorHandler(event) {
	document.getElementById("status").innerHTML = "Upload Failed";
  }
  function abortHandler(event) {
	document.getElementById("status").innerHTML = "Upload Aborted";
  }
  
	function cekfileupload(namafile, ext=''){
		if(ext==''){
			var ext=['.jpeg','.jpg','.png','.pdf','.xls','.xlsx','.doc','.docx'];
		}
		var val = 0;
		for(i=0;i<ext.length;i++){		
			namafile=namafile.toLowerCase();
			if (namafile.lastIndexOf(ext[i]) > -1){
				val++;
			}
		}
		if (val==0){
			alertify.alert("Format file harus "+ext);
			throw Error('Stop!');
		}
	}

  function submitfile(notransaksi) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("fileupload", getValue("upload"));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue("upload") == "") {
	  alertify.alert("Upload file has been empty.");
	  return false;
	}
	if (notransaksi == "") {
	  alertify.alert("Nomor transaksi tidak ditemukan.");
	  return false;
	}
  
	cekfileupload(getValue("upload"));
  
	var con = createXMLHttpRequest();
	document.getElementById("btnsubmit").style.display = "none";
	//tambahan progress bar
	con.upload.addEventListener("progress", progressHandler, false);
	con.addEventListener("load", completeHandler, false);
	con.addEventListener("error", errorHandler, false);
	con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "vhc_slave_project.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			//=== Success Response
			alertify.alert("Uploaded Success.");
			document.getElementById("btnsubmit").style.display = "";
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
	param = "method=loadfiles&notransaksi=" + notransaksi;
	tujuan = "vhc_slave_project.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			if (document.getElementById("listfiles") !== null) {
			  document.getElementById("listfiles").innerHTML = con.responseText;
			}
			if (document.getElementById("loadfilesdetail") !== null) {
			  document.getElementById("loadfilesdetail").innerHTML =
				con.responseText;
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
	tujuan = "vhc_slave_project.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
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