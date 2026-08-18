
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
	if(e=='asset'){
		document.getElementById('rowakun').style.display = 'none';
		document.getElementById('rowkary').style.display = 'none';
	}else{
		document.getElementById('rowakun').style.display = '';
		document.getElementById('rowkary').style.display = '';
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
	document.getElementById('unit').value = kodeorg;
	document.getElementById('aset').value = aset;
	document.getElementById('tipebg').value = tipebg;
	document.getElementById('pekerjaan').value = pekerjaan;
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
					document.getElementById('sub').innerHTML = con.responseText;
					if (sub != '') {
						getjbiaya(jenisbiaya,aset);
					}

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
					document.getElementById('jenisbiaya').innerHTML = con.responseText;
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

	param = 'unit=' + unit + '&aset=' + aset + '&jenis=' + jenis + '&jenisbiaya=' + jenisbiaya + '&tipebg=' + tipebg + '&pekerjaan=' + pekerjaan;
	param += '&nama=' + nama + '&tanggalmulai=' + tanggalmulai + '&tanggalselesai=' + tanggalselesai + '&kode=' + kode + '&sub=' + sub;
	param += '&kodecapex=' + kodecapex;
	param += '&satuan=' + satuan;
	param += '&jumlah=' + jumlah;
	param += '&posisiasset=' + posisiasset;
	param += '&tipelokasiasset=' + tipelokasiasset;
	param += '&nomesin=' + nomesin;
	param += '&norangka=' + norangka;
	param += '&tipemodel=' + tipemodel;
	param += '&keterangan=' + keterangan;
	param += '&dgnapprvl=' + dgnapprvl;
	param += '&aprv1=' + aprv1;
	param += '&aprv2=' + aprv2;
	param += '&aprv3=' + aprv3;
	param += '&aprv4=' + aprv4;
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
	document.getElementById('unit').value = '';
	document.getElementById('aset').value = '';
	document.getElementById('jenisbiaya').value = '';
	document.getElementById('jenis').value = 'AK';
	document.getElementById('nama').value = '';
	document.getElementById('tanggalmulai').value = '';
	document.getElementById('tanggalselesai').value = '';
	document.getElementById('method').value = 'insert';
	document.getElementById('kode').value = '';
	document.getElementById('sub').value = '';
	document.getElementById('tipebg').value = '';
	document.getElementById('pekerjaan').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('jumlah').value = '';
	document.getElementById('keterangan').value = '';
	document.getElementById('posisiasset').value = '';
	document.getElementById('tipelokasiasset').value = '';
	document.getElementById('nomesin').value = '';
	document.getElementById('norangka').value = '';

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
	document.getElementById('unit').value = unit;
	document.getElementById('aset').value = aset;
	document.getElementById('jenis').value = jenis;
	document.getElementById('jenisbiaya').value = jenisbiaya;
	document.getElementById('nama').value = nama;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('tipebg').value = tipebg;
	document.getElementById('pekerjaan').value = pekerjaan;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('method').value = method;
	document.getElementById('kode').value = kode;
	document.getElementById('satuan').value = satuan;
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('posisiasset').value = posisiasset;
	document.getElementById('tipelokasiasset').value = tipelokasiasset;
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
	
	if (statuspersetujuan == 1){
		document.getElementById('dgnapprvl').disabled = true;
		document.getElementById('aprv1').disabled  = true;
		document.getElementById('aprv2').disabled  = true;
		document.getElementById('aprv3').disabled  = true;
		document.getElementById('aprv4').disabled  = true;
		
	}else{
		document.getElementById('formapproval').hidden = false;  
		document.getElementById('dgnapprvl').disabled = false;
		document.getElementById('aprv1').disabled  = false;
		document.getElementById('aprv2').disabled  = false;
		document.getElementById('aprv3').disabled  = false;
		document.getElementById('aprv4').disabled  = false;
	}
	document.getElementById('aprv1').value = kryApr1;
	document.getElementById('aprv2').value = kryApr2;
	document.getElementById('aprv3').value = kryApr3;
	document.getElementById('aprv4').value = kryApr4;
	
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

function detailForm(unit, aset, jenis, nama, tanggalmulai, tanggalselesai, method, kode, sub, jenisbiaya, tipebg, pekerjaan, kodecapex,satuan,jumlah, keterangan, posisiasset, tipelokasiasset, nomesin, norangka) {
	document.getElementById('kodecapex').value = kode;
	document.getElementById('unit').value = unit;
	document.getElementById('aset').value = aset;
	document.getElementById('jenis').value = jenis;
	document.getElementById('jenisbiaya').value = jenisbiaya;
	document.getElementById('nama').value = nama;
	document.getElementById('tanggalmulai').value = tanggalmulai;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('tipebg').value = tipebg;
	document.getElementById('pekerjaan').value = pekerjaan;
	document.getElementById('tanggalselesai').value = tanggalselesai;
	document.getElementById('method').value = method;
	document.getElementById('kode').value = kode;
	document.getElementById('satuan').value = satuan;
	document.getElementById('jumlah').value = jumlah;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('posisiasset').value = posisiasset;
	document.getElementById('tipelokasiasset').value = tipelokasiasset;
	document.getElementById('nomesin').value = nomesin;
	document.getElementById('norangka').value = norangka;

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
						document.getElementById('excapex1').style.display = 'none';
						for (i = 0; i < x2.length; i++) {
							x2[i].style.display = "none";
							x3[i].style.display = "none";
						}
					} else {
						document.getElementById('excapex1').style.display = '';
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
	document.getElementById('unit').value = '';
	document.getElementById('aset').value = '';
	document.getElementById('jenis').value = '';
	document.getElementById('jenisbiaya').value = '';
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
	document.getElementById('kdProj').value = kode;
	document.getElementById('deskripsi').value = deskripsi;
	document.getElementById('namaKeg').value = nmkeg;
	document.getElementById('tanggalMulai').value = tanggalmulai;
	document.getElementById('tanggalSampai').value = tanggalselesai;
	document.getElementById('kegId').value = knci;
	//document.getElementById('satKeg').value=satKeg;
	setValue('satKeg', satKeg);
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
					//document.getElementById('container').innerHTML=con.responseText;
					loadData();
					batal();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadData(num) {
	namacr = document.getElementById('namacr').value;
	unitcr = document.getElementById('unitcr').value;
	kodecr = document.getElementById('kodecr').value;

	param = 'method=loadData';
	param += '&page=' + num;

	if (namacr != '') {
		param += '&namacr=' + namacr;
	}
	if (unitcr != '') {
		param += '&unitcr=' + unitcr;
	}
	if (kodecr != '') {
		param += '&kodecr=' + kodecr;
	}
	// alert(param);
	// return;
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
function batalKeg() {
	document.getElementById('satKeg').selectedIndex = 0;
	document.getElementById('volKeg').value = '';
	document.getElementById('bobotKeg').value = '';
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

	param = '&kode=' + kd + '&deskripsi=' + deskripsi + '&nmKeg=' + nmKeg + '&tglMul=' + tglMul + '&tglSmp=' + tglSmp;
	param += '&index=' + knci + '&method=insertDetail';
	param += '&satKeg=' + satKeg + '&volKeg=' + volKeg + '&bobotKeg=' + bobotKeg;
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
					//batalKeg();
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
	param = '&kode=' + kd + '&nomesin=' + nomesin + '&norangka=' + norangka + '&tahunproduksi=' + tahunproduksi + '&tahunprolehan=' + tahunprolehan;
	param += '&kegiatanx=' + kegiatanx + '&method=' + met;
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
	param = 'method=detailak' + '&kode=' + kd;
	tujuan = 'vhc_slave_project.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert(con.responseText);
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
	param = 'index=' + kode + '&method=hpsDetailAK';
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
					document.getElementById('tipelokasiasset').innerHTML = con.responseText;
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