function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {
	idcari = document.getElementById('idcari').value;
	pemilikcari = document.getElementById('pemilikcari').value;
	persilcari = document.getElementById('persilcari').value;
	spptcari = document.getElementById('spptcari').value;
	param = 'method=loaddata';
	param += "&page=" + page;
	param += "&idcari=" + idcari;
	param += "&pemilikcari=" + pemilikcari;
	param += "&persilcari=" + persilcari;
	param += "&spptcari=" + spptcari;
	tujuan = 'pad_slave_save_pembebasan.php';
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

function changekriteria(id) {
	kriteriax = getValue('kriteriax_' + id);
	param = 'method=changekriteria&kriteriax=' + kriteriax + '&id=' + id;
	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	console.log(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalcari() {
	document.getElementById('idcari').value = '';
	document.getElementById('pemilikcari').value = '';
	document.getElementById('persilcari').value = '';
	document.getElementById('spptcari').value = '';
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	/*document.getElementById('dynamic2').style.top = pos[1]+'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) +'px';
	document.getElementById('dynamic2').style.display='';*/
}

function showupload(ev, jenis, pt, iii, xxx, yyy) {
	showformupload(ev);
	param = "";
	param += "pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&jenisupload=" + jenis;
	param += '&method=showupload';
	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	console.log(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile(jenis) {
	var file = document.getElementById("upload").files[0];
	var pt = document.getElementById('ptupload').innerHTML;
	var xxx = document.getElementById('xxx').innerHTML;
	var yyy = document.getElementById('yyy').innerHTML;
	var formdata = new FormData();
	formdata.append("xxx", xxx);
	formdata.append("yyy", yyy);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("pt", pt);
	formdata.append("jenisupload", jenis);
	formdata.append("kriteria", getValue('kriteria'));

	if (jenis == 'statuslahan') {
		var iii = document.getElementById('iii');
		if (typeof iii == 'undefined') {
			formdata.append("iii", iii.value);
		}
	}

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "pad_slave_save_pembebasan.php?method=submitfile", true);
	busy_on();
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
					document.getElementById("upload").value = "";
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewlistfile(jenis, pt, xxx, yyy, iii) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog2(title, content, width, height, ev);

	param = 'method=viewlistfile&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy + '&iii=' + iii;
	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(jenis, pt, xxx, yyy, iii);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(jenis, pt, xxx, yyy, iii) {
	param = 'method=loadfiles&jenisupload=' + jenis + '&pt=' + pt + '&xxx=' + xxx + '&yyy=' + yyy + '&iii=' + iii;
	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(jenis, pt, xxx, yyy, iii, namafile) {
	param = "method=deletefile";
	param += "&jenisupload=" + jenis;
	param += "&pt=" + pt;
	param += "&xxx=" + xxx;
	param += "&yyy=" + yyy;
	param += "&iii=" + iii;
	param += "&namafile=" + namafile;

	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(jenis, pt, xxx, yyy, iii);
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
	width = '900';
	height = '500';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function ptintPDF(idlahan, pemilik, ev) {
	method = 'pdf';
	param = 'idlahan=' + idlahan + '&pemilik=' + pemilik + '&method=' + method;
	tujuan = 'pad_slave_save_pembebasan.php';
	judul = 'Report PDF';
	printFile(param, tujuan, judul, ev)
}

function simpanJabatan() {
	mid = document.getElementById('mid').value;
	unit = document.getElementById('unit');
	unit = unit.options[unit.selectedIndex].value;
	pemilik = document.getElementById('pemilik');
	pemilik = pemilik.options[pemilik.selectedIndex].value;
	lokasi = document.getElementById('lokasi').value;
	luaslahan = remove_comma_var(document.getElementById('luaslahan').value);
	luasinti = remove_comma_var(document.getElementById('luasinti').value);
	luasplasma = remove_comma_var(document.getElementById('luasplasma').value);
	jmlsppt = document.getElementById('jmlsppt').value;

	bisaditanam = document.getElementById('bisaditanam').value;
	blok = document.getElementById('blok');
	blok = blok.options[blok.selectedIndex].value;
	shm = document.getElementById('shm').value;
	batastimur = document.getElementById('batastimur').value;
	batasbarat = document.getElementById('batasbarat').value;
	batasutara = document.getElementById('batasutara').value;
	batasselatan = document.getElementById('batasselatan').value;
	koordinatulx = document.getElementById('koordinatulx').value;
	koordinatuly = document.getElementById('koordinatuly').value;
	koordinatlrx = document.getElementById('koordinatlrx').value;
	koordinatlry = document.getElementById('koordinatlry').value;
	keterangan = document.getElementById('ket').value;
	alamat = document.getElementById('alamat').value;
	statuskawasan = document.getElementById('statuskawasan');
	statuskawasan = statuskawasan.options[statuskawasan.selectedIndex].value;
	met = document.getElementById('method').value;

	if (mid > 0) {
		met = "update";
	}

	if (luaslahan < bisaditanam) {
		alert('Luas harus lebih besar atau sama dengan luas dapat ditanam');
	} else if (lokasi == '') {
		alert('Mohon diisi keterangan lokasi');
	} else {
		param = 'mid=' + mid + '&unit=' + unit + '&pemilik=' + pemilik + '&statuskawasan=' + statuskawasan;
		param += '&lokasi=' + lokasi + '&luaslahan=' + luaslahan + '&luasinti=' + luasinti + '&luasplasma=' + luasplasma + '&bisaditanam=' + bisaditanam;
		param += '&blok=' + blok + '&batastimur=' + batastimur + '&batasbarat=' + batasbarat;
		param += '&batasutara=' + batasutara + '&batasselatan=' + batasselatan + '&koordinatulx=' + koordinatulx + '&koordinatuly=' + koordinatuly + '&koordinatlrx=' + koordinatlrx + '&koordinatlry=' + koordinatlry;
		param += '&keterangan=' + keterangan + '&jmlsppt=' + jmlsppt;
		param += '&shm=' + shm + '&method=' + met;
		param += '&alamat=' + alamat;
		tujuan = 'pad_slave_save_pembebasan.php';
		console.log(statuskawasan);

		post_response_text(tujuan, param, respog);
		//alert(shm);
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
					cancelJabatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function fillFieldarray(e) {
	var data = e.getAttribute("data-edit");
	//alert(data);
	var dataArr = JSON.parse(data);

	if (document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value != dataArr.unit) {
		alert('Pilih unit terlebih dahulu');
	} else {
		document.getElementById('mid').value = dataArr.idlahan;
		x = document.getElementById('pemilik');
		for (y = 0; y < x.length; y++) {
			if (x.options[y].value == dataArr.pemilik)
				x.options[y].selected = true;
		}
		document.getElementById('lokasi').value = dataArr.lokasi;
		document.getElementById('luas').value = dataArr.luas;
		document.getElementById('jmlsppt').value = dataArr.jmlsppt;
		document.getElementById('bisaditanam').value = dataArr.luasdapatditanam;
		x = document.getElementById('blok');
		for (y = 0; y < x.length; y++) {
			if (x.options[y].value == dataArr.kodeblok)
				x.options[y].selected = true;
		}
		document.getElementById('batastimur').value = dataArr.batastimur;
		document.getElementById('batasbarat').value = dataArr.batasbarat;
		document.getElementById('batasutara').value = dataArr.batasutara;
		document.getElementById('batasselatan').value = dataArr.batasselatan;
		document.getElementById('koordinatulx').value = dataArr.koordinatulx;
		document.getElementById('koordinatuly').value = dataArr.koordinatuly;
		document.getElementById('koordinatlrx').value = dataArr.koordinatlrx;
		document.getElementById('koordinatlry').value = dataArr.koordinatlry;

		document.getElementById('keterangan').value = dataArr.keterangan;

		/*
		x=document.getElementById('statuspermintaandana');
		for(y=0;y<x.length;y++){
		if(x.options[y].value==dataArr.statuspermintaandana)
		x.options[y].selected=true;
		}
		x=document.getElementById('statuspermbayaran');
		for(y=0;y<x.length;y++){
		if(x.options[y].value==dataArr.statuspermbayaran)
		x.options[y].selected=true;
		}
		x=document.getElementById('statuskades');
		for(y=0;y<x.length;y++){
		if(x.options[y].value==dataArr.statuskades)
		x.options[y].selected=true;
		}
		x=document.getElementById('statuscamat');
		for(y=0;y<x.length;y++){
		if(x.options[y].value==dataArr.statuscamat)
		x.options[y].selected=true;
		}
		document.getElementById('nosurat').value=dataArr.nosurat;


		if(tanggalpengajuan=='00-00-0000')
		tanggalpengajuan='';
		document.getElementById('tanggalpermintaan').value=dataArr.tanggalpengajuan;
		if(tanggalbayar=='00-00-0000')
		tanggalbayar='';
		document.getElementById('tanggalbayar').value=dataArr.tanggalbayar;
		if(tanggalkades=='00-00-0000')
		tanggalkades='';
		document.getElementById('tanggalkades').value=dataArr.tanggalkades;
		if(tanggalcamat=='00-00-0000')
		tanggalcamat='';
		document.getElementById('tanggalcamat').value=dataArr.tanggalcamat;
		 */
	}
	document.getElementById('method').value = 'update';
}

function fillField(idlahan, pemilik, unit, lokasi, luas, luasinti, luasplasma, batastimur, batasbarat, batasutara, batasselatan,
	luasdapatditanam, koordinatulx, koordinatuly, koordinatlrx, koordinatlry,
	kodeblok, keterangan, noshm, jmlsppt, alamat)

//function fillField(idlahan,pemilik,unit,lokasi,luas,batastimur,batasbarat,batasutara,batasselatan,luasdapatditanam,koordinatulx,koordinatuly,koordinatlrx,koordinatlry,rptanaman,rptanah,biayasppt,statuspermintaandana,statuspermbayaran,kodeblok,statuskades,statuscamat,tanggalpengajuan,tanggalbayar,tanggalkades,tanggalcamat,biayakades,biayacamat,biayamatrai,keterangan,nosurat,totalbiaya)
{

	document.getElementById('unit').value = unit
	document.getElementById('mid').value = idlahan;

	document.getElementById('lokasi').value = lokasi;
	document.getElementById('luaslahan').value = numberFormat(luas);
	document.getElementById('luasinti').value = numberFormat(luasinti);
	document.getElementById('luasplasma').value = numberFormat(luasplasma);
	document.getElementById('shm').value = noshm;
	document.getElementById('bisaditanam').value = luasdapatditanam;
	document.getElementById('jmlsppt').value = jmlsppt;
	document.getElementById('blok').value = kodeblok;

	document.getElementById('batastimur').value = batastimur;
	document.getElementById('batasbarat').value = batasbarat;
	document.getElementById('batasutara').value = batasutara;
	document.getElementById('batasselatan').value = batasselatan;

	document.getElementById('koordinatulx').value = koordinatulx;
	document.getElementById('koordinatuly').value = koordinatuly;
	document.getElementById('koordinatlrx').value = koordinatlrx;
	document.getElementById('koordinatlry').value = koordinatlry;

	document.getElementById('ket').value = keterangan;
	document.getElementById('alamat').value = alamat;

	document.getElementById('method').value = 'update';
	updatePemilik(unit, pemilik);
}

function cancelJabatan() {
	document.getElementById('mid').value = '';
	x = document.getElementById('pemilik');
	x.options[0].selected = true;
	document.getElementById('lokasi').value = '';
	document.getElementById('luaslahan').value = 0;
	document.getElementById('luasinti').value = 0;
	document.getElementById('luasplasma').value = 0;
	document.getElementById('bisaditanam').value = 0;
	document.getElementById('jmlsppt').value = '';

	x = document.getElementById('blok');
	x.options[0].selected = true;
	document.getElementById('batastimur').value = '';
	document.getElementById('batasbarat').value = '';
	document.getElementById('batasutara').value = '';
	document.getElementById('batasselatan').value = '';
	document.getElementById('koordinatulx').value = '';
	document.getElementById('koordinatuly').value = '';
	document.getElementById('koordinatlrx').value = '';
	document.getElementById('koordinatlry').value = '';

	document.getElementById('shm').value = '';

	document.getElementById('ket').value = '';
	document.getElementById('alamat').value = '';
	document.getElementById('method').value = 'insert';
	batalcari();
	loaddata();
}

function hitungtotalahan() {
	lahaninti = parseFloat((document.getElementById('luasinti').value));
	// alert(lahaninti);
	lahanplalsma = parseFloat((document.getElementById('luasplasma').value));
	totallahan = lahaninti + lahanplalsma;

	document.getElementById('luaslahan').value = (totallahan);
}
function updatePemilik(unit, pemilik) {
	//console.log(unit); return false;
	param = 'unit=' + unit + '&pemilik=' + pemilik + '&method=getPemilik';

	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('pemilik').innerHTML = con.responseText;
					updateBlok(unit);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function updateBlok(unit) {
	param = 'unit=' + unit + '&method=getBlok';
	tujuan = 'pad_slave_save_pembebasan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
					//loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changeTanggal(objid, value) {
	if (value == '0')
		document.getElementById(objid).value = '';
}

function deleteData(idlahan, unit) {
	param = 'mid=' + idlahan + '&unit=' + unit + '&method=delete';
	tujuan = 'pad_slave_save_pembebasan.php';

	if (confirm('Deleting id:' + idlahan + ', Are you sure..?')) {
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
					// document.getElementById('container').innerHTML=con.responseText;
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function postingData(idlahan, unit) {
	param = 'mid=' + idlahan + '&unit=' + unit + '&method=posting';
	tujuan = 'pad_slave_save_pembebasan.php';

	if (confirm('Posting id:' + idlahan + ' will commited for good,  Are you sure..?')) {
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
					// document.getElementById('container').innerHTML=con.responseText;
					loaddata();
					cancelJabatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unpostingData(idlahan, unit) {
	param = 'mid=' + idlahan + '&unit=' + unit + '&method=unposting';
	tujuan = 'pad_slave_save_pembebasan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
					cancelJabatan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function showDetail(unit, idlahan, pemilik) {

param = 'unit=' + unit + '&idlahan=' + idlahan + '&pemilik=' + pemilik;
tujuan = 'pad_slave_save_pembebasan.php';
function respog() {
	if (con.readyState == 4) {
		if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
				alert(con.responseText);
			} else {
				alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
			}
		} else {
			busy_off();
			error_catch(con.status);
		}
	}
}
	post_response_text(tujuan + '?' + 'method=showDetail', param, respog);
}


function simpanDetail() {

unit = document.getElementById('unitx').value;
idlahan = document.getElementById('idlahanx').value;
pemilik = document.getElementById('pemilikx').value;
tglpembebasan = document.getElementById('tglpembebasan').value;
penyelesaian = document.getElementById('penyelesaian').value;
tglsengketa = document.getElementById('tglsengketa').value;
deskripsi = document.getElementById('deskripsi').value;
kategori = document.getElementById('kategori').value;
catatan = document.getElementById('catatan').value;

param =  'unit=' + unit+  '&idlahan=' + idlahan+  '&pemilik=' + pemilik + '&tglpembebasan=' + tglpembebasan + '&penyelesaian=' + penyelesaian+ '&tglsengketa=' + tglsengketa + '&deskripsi=' + deskripsi+ '&kategori=' + kategori + '&catatan=' + catatan + '&method=simpanDetail';
tujuan = 'pad_slave_save_pembebasan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Berhasil disimpan.');
					showDetail(unit,idlahan,pemilik);
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function hapusDetail(noid,idlahan,unit,pemilik) {


param =  'noid=' + noid + '&method=hapusDetail';
tujuan = 'pad_slave_save_pembebasan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Berhasil dihapus.');
					showDetail(unit,idlahan,pemilik);
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

