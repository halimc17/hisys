function tiketext(nospb){
	param  = 'nospb=' + nospb;
	param += "&proses=tiketext";
	tujuan = 'kebun_slave_save_spbv2.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Tiket",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('800px','60%');
				}

			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prosesData(nospb) {
	noSpb = nospb;
	param = 'noSpb=' + noSpb + '&proses=PostingDataSPB';
	tujuan = 'kebun_slave_3AmbilKgTimbangan.php';
	//alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Posting This Data"))
		post_response_text(tujuan, param, respon);
}

function postingDatanih(nospb) {
	noSpb = nospb;
	param = 'noSpb=' + noSpb + '&proses=postingDatanih';
	tujuan = 'kebun_slave_save_spbv2.php';
	//alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Posting This Data"))
		post_response_text(tujuan, param, respon);
}

function unpostingnih(nospb) {
	noSpb = nospb;
	param = 'noSpb=' + noSpb + '&proses=unpostingnih';
	tujuan = 'kebun_slave_save_spbv2.php';
	//alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Unposting This Data"))
		post_response_text(tujuan, param, respon);
}

function getPks(intex, pks, noSpb) {
	intex = document.getElementById('intex').value;
	param = 'intex=' + intex + '&pks=' + pks;
	param += "&proses=getPks";
	tujuan = 'kebun_slave_save_spbv2.php';

	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					document.getElementById('pks').innerHTML = con.responseText;
					loadfiles(noSpb); 
					loadfilespetani(noSpb);
					loadDetail(intex, pks);
				}

			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function assignGlobal(s1, s2, s3, s4, s5) {
	nmTmblDone = s1;
	nmTmblCancel = s2;
	nmTmblSave = s3;
	nmTmblCancel = s4;
	optIsi = s5;
}

function add_new_data() {
	bersihForm();
	status_inputan = 0;
	//document.getElementById('proses').value='insert';
	param = 'proses=generateNo';
	tujuan = 'kebun_slave_save_spbv2.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					document.getElementById('listSpb').style.display = 'none';
					document.getElementById('headher').style.display = 'block';
					document.getElementById('detailSpb').style.display = 'none';
					//document.getElementById('noSpb').value = con.responseText;
					//document.getElementById('noSpb').disabled=true;
					document.getElementById('ublLheader').innerHTML = '';
					document.getElementById('tmbLheader').innerHTML = '<button class=mybutton id=dtlSpb onclick=detailSpb()>' + nmTmblSave + '</button><button class=mybutton id=cancelSpb onclick=cancelSpb()>' + nmTmblCancel + '</button>';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function bersihForm() {
	document.getElementById('nourut').value = '';
	document.getElementById('tahuntanam').value = '';
	document.getElementById('tgl_ganti').value = '';
	document.getElementById('kodeOrg').value = '';
	document.getElementById('referensimb').value = '';
	document.getElementById('kodeOrg').disabled = false;
	document.getElementById('kodeDiv').disabled = false;
	document.getElementById('period').disabled = false;
	document.getElementById('nourut').disabled = false;
	document.getElementById('kodeDiv').innerHTML = '';
	document.getElementById('mnculSma').disabled = false;
	document.getElementById('mnculSma').checked = false;
	document.getElementById('tahuntanam').disabled = false;
	document.getElementById('referensimb').disabled = false;

	document.getElementById('tgl_ganti').disabled = false;
	document.getElementById('intex').disabled = false;
	document.getElementById('pks').disabled = false;
	document.getElementById('intex').value = '';
	document.getElementById('pks').value = '';
	document.getElementById('contentDetail').innerHTML = '';
	document.getElementById('contentpetani').innerHTML = '';
	document.getElementById('contenttkbm').innerHTML = '';
}
function bersihDetailForm() {
	document.getElementById('blok').value = '';
	document.getElementById('bjr').value = '0';
	document.getElementById('jjng').value = '0';
	document.getElementById('brondln').value = '0';
	document.getElementById('kgwb').value = '0';
	document.getElementById('mtng').value = '0';
	document.getElementById('mnth').value = '0';
	document.getElementById('bsk').value = '0';
	document.getElementById('lwtmtng').value = '0';
	document.getElementById('kegiatan').value = '0';
}
function add_detail() {
	kdDiv = document.getElementById('kodeDiv').options[document.getElementById('kodeDiv').selectedIndex].value;
	periode = document.getElementById('period').options[document.getElementById('period').selectedIndex].value
		noUrut = document.getElementById('nourut').value;
	tgl = document.getElementById('tgl_ganti').value;
	a = periode.split('-');
	var hsl = noUrut + '/' + kdDiv + '/' + a[1] + '/' + a[0];
	//alert(hsl);
	//return;
	document.getElementById('noSpb').value = hsl;
	nospb = document.getElementById('noSpb').value;
	document.getElementById('detail_kode').value = nospb;

	intex = document.getElementById('intex').value;
	pks = document.getElementById('pks').value;
	kerani = document.getElementById('kerani').value;
	kontanan = document.getElementById('kontanan').value;
	tahuntanam = document.getElementById('tahuntanam').value;

	//alert(notran);
	param = 'noSpb=' + nospb + '&kdDiv=' + kdDiv + '&periode=' + periode + '&tgl=' + tgl + '&intex=' + intex + '&pks=' + pks + '&kerani=' + kerani + '&kontanan=' + kontanan + '&tahuntanam=' + tahuntanam;
	param += "&proses=createTable";
	//alert(param);
	tujuan = 'kebun_spb_slave_detailv2.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					//alertify.alert(con.responseText);
					document.getElementById('kodeOrg').disabled = true;
					document.getElementById('kodeDiv').disabled = true;
					document.getElementById('period').disabled = true;
					document.getElementById('tahuntanam').disabled = true;
					document.getElementById('nourut').disabled = true;
					document.getElementById('tmbLheader').innerHTML = '';
					document.getElementById('detailSpb').style.display = 'block';
					document.getElementById('ppDetailTable').innerHTML = con.responseText;
					showTmbl();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function UbahHeader() {
	nospb = document.getElementById('noSpb').value;
	document.getElementById('detail_kode').value = nospb;
	kontanan = document.getElementById('kontanan').value;
	referensimb = document.getElementById('referensimb').value;
	param = 'noSpb=' + nospb + '&kontanan=' + kontanan+ '&referensimb=' + referensimb;
	param += "&proses=UbahHeader";
	tujuan = 'kebun_spb_slave_detailv2.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kodeOrg').disabled = true;
					document.getElementById('kodeDiv').disabled = true;
					document.getElementById('period').disabled = true;
					document.getElementById('tahuntanam').disabled = true;
					document.getElementById('nourut').disabled = true;
					// document.getElementById('tmbLheader').innerHTML = '';
					document.getElementById('ublLheader').innerHTML = '<button class=mybutton id=ublSpb onclick=UbahHeader()>Ubah</button>';
					document.getElementById('detailSpb').style.display = 'block';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function detailSpb() {
	tgl = document.getElementById('tgl_ganti').value;
	kdOrg = document.getElementById('kodeOrg').value;
	NoUrut = document.getElementById('nourut').value;
	kerani = document.getElementById('kerani').value;
	kontanan = document.getElementById('kontanan').value;
	kodeDiv = document.getElementById('kodeDiv').value;
	intex = document.getElementById('intex').value;
	pks = document.getElementById('pks').value;
	tahuntanam = document.getElementById('tahuntanam').value;
	if(kdOrg == ''){
		alert("Kode organisasi wajib diisi."); return;
	}
	if(tahuntanam == ''){
		alert("Tahun Tanam wajib diisi."); return;
	}
	if(kodeDiv == ''){
		alert("Divisi wajib diisi."); return;
	}
	// if(NoUrut == '' || NoUrut == '0000000'){
	// 	alert("Nomor wajib diisi."); return;
	// }
	if(NoUrut == '' || NoUrut == '0000'){
		alert("Nomor wajib diisi."); return;
	}
	if(tgl == ''){
		alert("Tanggal wajib diisi."); return;
	}
	if(intex == ''){
		alert("Status wajib diisi."); return;
	}
	if(pks == ''){
		alert("Pabrik wajib diisi."); return;
	}

	if (tahuntanam.length !== 4){
		alert('Tahun Tanam Wajib 4 digit');
		return false;
	}
	
	document.getElementById('contentDetail').innerHTML = '';
	document.getElementById('contentpetani').innerHTML = '';
	document.getElementById('contenttkbm').innerHTML = '';
	add_detail();
	//tmbhDetail();
}
function getBjr(tphdt='') {
	blk = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	//document.getElementById('oldBlok').value=blk;
	periode = document.getElementById('period');
	periode = periode.options[periode.selectedIndex].value;
	param = 'blok=' + blk + '&proses=amblBjr&periode=' + periode+'&tphdt='+tphdt;
	//alert(param);
	tujuan = 'kebun_slave_save_spbv2.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
						// Success Response
						data = con.responseText.split("####");
						document.getElementById('bjr').value = data[0];
						document.getElementById('tphdt').innerHTML = data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
status_inputan = 0;
function addDetail() {

	crt = document.getElementById('proses');
	//	alert(crt.value);
	var detKode = document.getElementById('detail_kode');
	var rblok = document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
	var rbjr = document.getElementById('bjr');
	var rjjng = document.getElementById('jjng');
	var rbrondln = document.getElementById('brondln');
	var kgwb = document.getElementById('kgwb');
	var rmatang = document.getElementById('mtng');
	var rmentah = document.getElementById('mnth');
	var rbusuk = document.getElementById('bsk');
	var tglpanen = document.getElementById('tglpanen').value;
	var kegiatan = document.getElementById('kegiatan').value;

	var rlwtmatang = document.getElementById('lwtmtng');
	//addSession();
	//var id_user = trim(document.getElementById('user_id').value);
	rtgl = trim(document.getElementById('tgl_ganti').value);
	rnospb = trim(document.getElementById('noSpb').value);
	intex = trim(document.getElementById('intex').value);
	pks = trim(document.getElementById('pks').value);
	kerani = trim(document.getElementById('kerani').value);
	document.getElementById('detail_kode').value = rnospb;
	if (status_inputan == 0) {
		if (confirm('Are You Sure add this detail')) {
			cek_data();
		}
	} else {
		cek_data();
	}

}
function loadDetail(intex, pks) {

	noSpb = document.getElementById('detail_kode').value;
	kdDiv = document.getElementById('kodeOrg').options[document.getElementById('kodeOrg').selectedIndex].value;
	tujuan = 'kebun_spb_slave_detailv2.php';
	param = 'noSpb=' + noSpb + '&kdDiv=' + kdDiv + '&proses=loadDetail';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					//alertify.alert(con.responseText);
					document.getElementById('contentDetail').innerHTML = con.responseText;
					//getPks(intex, pks, noSpb);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editDetail(nospb, blok, tph, sesi, nik, nopnnref, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang, kgwb,tglpanen,kegiatan) { //	alert('test');
	document.getElementById('noreferensidt').value = nopnnref;
	document.getElementById('sesidt').value = sesi;
	document.getElementById('blok').value = blok;
	document.getElementById('bjr').value = bjr;
	document.getElementById('jjng').value = jjg;
	document.getElementById('brondln').value = brondolan;
	document.getElementById('kgwb').value = kgwb;
	document.getElementById('mtng').value = matang;
	document.getElementById('mnth').value = mentah;
	document.getElementById('bsk').value = busuk;
	document.getElementById('lwtmtng').value = lewatmatang;
	document.getElementById('oldBlok').value = blok;
	document.getElementById('oldTph').value = tph;
	document.getElementById('oldPemanen').value = nik;
	document.getElementById('oldQrcode').value = nopnnref;
	document.getElementById('oldSesi').value = sesi;
	document.getElementById('tglpanen').value = tglpanen;
	document.getElementById('oldtglpanen').value = tglpanen;
	document.getElementById('kegiatan').value = kegiatan;
	document.getElementById('proses').value = 'updateData';
	getBlokSma('',nik,blok,tph);
}

function editpetani(nospb, petani, jjgpetani, brdpetani) { //	alert('test');
	if (document.getElementById('jjgpetani') !== null) {
		document.getElementById('petani').value = petani;
		setValue2('petani',petani);
		document.getElementById('jjgpetani').value = jjgpetani;
		document.getElementById('brdpetani').value = brdpetani;
	}else{
		alertify.popup().destroy();
		showpetani('event');		
	}
	// document.getElementById('proses').value = 'updateData';
}

/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
function deleteDetail(id) {
	var detKode = document.getElementById('detail_kode');
	var rblok = document.getElementById('blok_' + id);
	param = "proses=detail_delete";
	param += "&noSpb=" + detKode.value;
	param += "&blok=" + rblok.value;
	//alert(param);
	tujuan = 'kebun_spb_slave_detailv2.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					row = document.getElementById("detail_tr_" + id);
					if (row) {
						row.style.display = "none";
					} else {
						alert("Row undetected");
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm('Are You Sure Delete This Data!!!')) {
		post_response_text(tujuan, param, respon);
	} else {
		return;
	}
}
/* Function addNewRow
 * Fungsi untuk menambah row baru ke dalam table
 * I : id dari tbody tabel
 * P : Persiapan row dalam bentuk HTML
 * O : Tambahan row pada akhir tabel (append)
 */
function addNewRow(body, onDetail) {
	//alert(body);
	var tabBody = document.getElementById(body);
	if (onDetail) {
		var detail = onDetail;

	} else {
		var detail = false;
	}

	// Search Available numRow
	var numRow = 0;
	if (!detail) {
		while (document.getElementById('tr_' + numRow)) {
			numRow++;
		}
	} else {
		while (document.getElementById('detail_tr_' + numRow)) {
			numRow++;
		}
	}

	// Add New Row
	var newRow = document.createElement("tr");
	tabBody.appendChild(newRow);
	if (!detail) {
		newRow.setAttribute("id", "tr_" + numRow);
	} else {
		newRow.setAttribute("id", "detail_tr_" + numRow);
	}
	newRow.setAttribute("class", "rowcontent");

	if (!detail) {
		newRow.innerHTML += "<td><input id='kode_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='matauang_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='simbol_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><input id='kodeiso_" + numRow +
		"' type='text' class='myinputtext' style='width:70px' onkeypress='return tanpa_kutip(event)' value='' /></td><td><img id='add_" + numRow +
		"' title='Tambah' class=zImgBtn onclick=\"addMain('" + numRow + "')\" src='images/plus.png'/>" +
		"&nbsp;<img id='delete_" + numRow + "' />" +
		"&nbsp;<img id='pass_" + numRow + "' />" +
		"</td>";
	} else {
		// Create Row
		newRow.innerHTML += "<td><select id='blok_" + numRow + "' type='text' style='width:150px' onchange='getBjr(" + numRow + ")' />" + optIsi + "</select><input type=hidden id=oldBlok_" + numRow + "  /></td><td>" +
		"<input id='bjr_" + numRow + "' type='text' class='myinputtextnumber' style='width:120px' disabled='disabled' value=''  /></td><td><input id='jjng_" + numRow +
		"' type='text' class='myinputtextnumber' style='width:100px' value='' onkeypress='return angka_doang(event)' maxlength='12' /></td>" + "<td><input id='brondln_" + numRow + "' type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:100px' value='0' maxlength='12' />" + "" + "<td><input id='mnth_" + numRow + "' type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:100px' value='0' maxlength='12' />" + "" + "<td><input id='bsk_" + numRow + "' type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:100px' value='0' maxlength='12' />" + "" + "<td><input id='mtng_" + numRow + "' type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:100px' value='0' maxlength='12' />" + "" + "<td><input id='lwtmtng_" + numRow + "' type='text' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:100px' value='0' maxlength='12' />" + "<td><img id='detail_add_" + numRow + "' title='Tambah' class=zImgBtn onclick=\"addDetail('" + numRow + "')\" src='images/save.png'/>" + "&nbsp;<img id='detail_delete_" + numRow + "' />" + "&nbsp;<img id='detail_pass_" + numRow + "' />" + "</td>";
		/*newRow.innerHTML += "<td><select id='kd_brg_"+numRow+"' style='width:180px' onchange='set_brg("+numRow+")'>"+isi_barang+"</select><input type=hidden id=skd_brg_"+numRow+" name=skd_brg_"+numRow+" /></td><td>"+
		"<select id='sat_"+numRow+"'  style='width:70px'></select></td>"+"<td><input id='jmlh_"+numRow+"' type='text' class='myinputtextnumber' onkeypress='return amgka_doang(event)' style='width:70px' value='' />"+"<td><input id='ket_"+numRow+"' type='text' class='myinputtext' style='width:130px' value='' onkeypress='return tanpa_kutip(event)' />"+"<td><img id='detail_add_"+numRow+
		"' title='Tambah' class=zImgBtn onclick=\"addDetail('"+numRow+"')\" src='images/save.png'/>"+
		"&nbsp;<img id='detail_delete_"+numRow+"' />"+
		"&nbsp;<img id='detail_pass_"+numRow+"' />"+
		"</td>";*/
	}
}
/* Function switchEditAdd
 * Fungsi untuk mengganti image add menjadi edit dan keroconya
 * I : id nomor row
 * P : Image Add menjadi Edit
 * O : Image Edit
 */
function switchEditAdd(id, main) {

	if (main == 'main') {
		var idField = document.getElementById('add_' + id);
		var delImg = document.getElementById('delete_' + id);
		var passImg = document.getElementById('pass_' + id);
		var kode = document.getElementById('kode_' + id);
	} else {
		//alert(id);
		var idField = document.getElementById('detail_add_' + id);
		var delImg = document.getElementById('detail_delete_' + id);
	}
	if (idField) {
		idField.removeAttribute('id');
		idField.removeAttribute('name');
		idField.removeAttribute('onclick');
		idField.removeAttribute('src');
		idField.removeAttribute('title');

		// Set Edit Image Attr
		idField.setAttribute('title', 'Edit');
		if (main == 'main') {
			idField.setAttribute('id', 'edit_' + id);
			idField.setAttribute('name', 'edit_' + id);
			idField.setAttribute('onclick', 'editMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
		} else {
			//alert(id);
			idField.setAttribute('id', 'detail_edit_' + id);
			idField.setAttribute('name', 'detail_edit_' + id);
			idField.setAttribute('onclick', 'editDetail(\'' + id + '\')');
		}
		idField.setAttribute('src', 'images/001_45.png');

		// Set Delete Image Attr
		delImg.setAttribute('class', 'zImgBtn');
		delImg.setAttribute('title', 'Hapus');
		if (main == 'main') {
			delImg.setAttribute('name', 'delete_' + id);
			delImg.setAttribute('onclick', 'deleteMain(\'' + id + '\',\'kode\',\'' + kode.value + '\')');
		} else {
			//alert(id);
			delImg.setAttribute('name', 'detail_delete_' + id);
			delImg.setAttribute('onclick', 'deleteDetail(\'' + id + '\')');
		}
		delImg.setAttribute('src', 'images/delete_32.png');

	} else {
		alert('DOM Definition Error');
	}
}
function showTmbl() {
	document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button><button class=mybutton onclick=reset_data()>" + nmTmblCancel + "</button>";
}
function cek_data() {
	//var detKode = document.getElementById('detail_kode');
	var rblok = document.getElementById('blok');
	var rbjr = document.getElementById('bjr');
	var rjjng = document.getElementById('jjng');
	var rbrondln = document.getElementById('brondln');
	//	var kgwb = document.getElementById('kgwb');
	var rmatang = document.getElementById('mtng');
	var rmentah = document.getElementById('mnth');
	var rbusuk = document.getElementById('bsk');
	var rlwtmatang = document.getElementById('lwtmtng');
	var kagewebe = document.getElementById('kgwb');
	var rkodeOrg = document.getElementById('kodeOrg').options[document.getElementById('kodeOrg').selectedIndex].value;

	//var id_user = trim(document.getElementById('user_id').value);
	rtgl = trim(document.getElementById('tgl_ganti').value);
	rnospb = trim(document.getElementById('noSpb').value);
	intex = trim(document.getElementById('intex').value);
	pks = trim(document.getElementById('pks').value);
	kerani = trim(document.getElementById('kerani').value);
	kontanan = document.getElementById('kontanan').value;
	tahuntanam = document.getElementById('tahuntanam').value;
	tglpanen = document.getElementById('tglpanen').value;
	oldtglpanen = document.getElementById('oldtglpanen').value;
	kegiatan = document.getElementById('kegiatan').value;
	kodeDiv = document.getElementById('kodeDiv').value;
	pros = document.getElementById('proses').value;

	referensimb = document.getElementById('referensimb').value;
	
	noreferensidt = document.getElementById('noreferensidt').value;
	pemanendt = document.getElementById('pemanendt').value;
	tphdt = document.getElementById('tphdt').value;
	sesidt = document.getElementById('sesidt').value;
	
	if (pros != 'updateData') {
		param = "proses=cekData";
	} else {
		oldBlok = document.getElementById('oldBlok').value;
		oldTph = document.getElementById('oldTph').value;
		oldPemanen = document.getElementById('oldPemanen').value;
		oldQrcode = document.getElementById('oldQrcode').value;
		oldSesi = document.getElementById('oldSesi').value;
		param = "proses=" + pros + '&oldBlok=' + oldBlok+ '&oldTph=' + oldTph+ '&oldPemanen=' + oldPemanen+ '&oldQrcode=' + oldQrcode+ '&oldSesi=' + oldSesi;
	}
	param += "&noreferensidt=" + noreferensidt;
	param += "&pemanendt=" + pemanendt;
	param += "&tphdt=" + tphdt;
	param += "&sesidt=" + sesidt;
	param += "&referensimb=" + referensimb;
	
	param += "&noSpb=" + rnospb;
	param += "&kodeDiv=" + kodeDiv;
	param += "&kegiatan=" + kegiatan;
	param += "&blok=" + rblok.value;
	param += "&bjr=" + rbjr.value;
	param += "&jjng=" + rjjng.value;
	param += "&brondolan=" + rbrondln.value;
	param += "&tgl=" + rtgl;
	param += "&intex=" + intex;
	param += "&pks=" + pks;
	param += "&matang=" + rmatang.value;
	param += "&mentah=" + rmentah.value;
	param += "&busuk=" + rbusuk.value;
	param += "&lwtmatang=" + rlwtmatang.value;
	param += "&kdOrg=" + rkodeOrg;
	param += "&kerani=" + kerani;
	param += "&kontanan=" + kontanan;
	param += "&tahuntanam=" + tahuntanam;
	param += "&tglpanen=" + tglpanen;
	param += "&oldtglpanen=" + oldtglpanen;
	param += "&kgwb=" + kagewebe.value;
	tujuan = 'kebun_slave_save_spbv2.php';
	//	alert(param);
	//        return;

	if(tphdt==''){
		alertify.alert('Info','TPH harus dipilih');
		return false;
	}
	if(rblok.value==''){
		alertify.alert('Info','Blok harus dipilih');
		return false;
	}

	// if (rbjr.value == '' || parseFloat(rbjr.value) == 0) {
	// 	alert('BJR is NULL, Mohon diisi BJR terlebih dahulu')
	// } else {
	// 	post_response_text(tujuan, param, respog);
	// }
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//return;
					//var id=con.responseText;
					//id=id-1;
					//switchEditAdd(id,'detail');
					//	addNewRow('detailBody',true);
					status_inputan = 1;
					document.getElementById('proses').value = "cekData";
					bersihDetailForm();
					loadDetail();
					//showTmbl();
					//document.getElementById('contain').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	document.getElementById('listSpb').style.display = 'block';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('detailSpb').style.display = 'none';
	document.getElementById('txtsearch').value = '';
	document.getElementById('tgl_cari').value = '';
	document.getElementById('mnculSma').disabled = false;
	loadData(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num) {
	txtSearch = document.getElementById('txtsearch').value;
	txtDiv = document.getElementById('divsch').value;
	txtTgl = document.getElementById('tgl_cari').value;
	status_spb = document.getElementById('status_spb').value;
	referensisearch = document.getElementById('referensisearch').value;
	status_posting = document.getElementById('status_posting').value;

	param = 'proses=loadNewData';
	param += '&page=' + num;
	param += '&txtSearch=' + txtSearch;
	param += '&txtDiv=' + txtDiv;
	param += '&txtTgl=' + txtTgl;
	param += '&status_spb='+status_spb;
	param += '&referensisearch='+referensisearch;
	param += '&status_posting='+status_posting;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('contain').innerHTML = data[0];
					document.getElementById('footer').innerHTML = data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function donwloadlist() {
	param = 'proses=donwloadmobile';
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingData(noSpb, intex) {
    param = 'proses=postingData' + '&noSpb=' + noSpb + '&intex=' + intex ;
    tujuan = 'kebun_slave_save_spbv2.php';
	if (confirm('Anda yakin, posting data ?')){
		post_response_text(tujuan, param, respog);
	}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function unpostingData(noSpb, intex) {
    param = 'proses=unpostingData' + '&noSpb=' + noSpb + '&intex=' + intex ;
    tujuan = 'kebun_slave_save_spbv2.php';
	if (confirm('Anda yakin, unposting data ?')){
		post_response_text(tujuan, param, respog);
	}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(nospb, tanggal, stat, period, statDt, intex, pks, kerani,tahuntanam,noref) {

	document.getElementById('kerani').value = kerani;
	document.getElementById('referensimb').value = noref;
	document.getElementById('tahuntanam').value = tahuntanam;
	document.getElementById('intex').value = intex;
	intex = document.getElementById('intex').value;

	document.getElementById('pks').value = pks;
	//pks=document.getElementById('pks').value;


	ar = document.getElementById('noSpb').value = nospb;
	ars = ar.split("/");
	document.getElementById('nourut').value = ars[0];

	kdorg = ars[1].substring(0, 4);
	document.getElementById('kodeOrg').value = kdorg;
	periode = ars[3] + '-' + ars[2];
	document.getElementById('period').value = periode;
	document.getElementById('tgl_ganti').value = tanggal;
	nospb = document.getElementById('noSpb').value;

	kdDiv = ars[1];
	tgl = tanggal;
	periode = period;
	Stat = stat;
	param = 'noSpb=' + nospb + '&stat=' + Stat + '&kdDiv=' + kdDiv + '&tgl=' + tgl + '&periode=' + periode;
	param += "&proses=createTable" + '&statusCek=' + statDt + '&intex=' + intex + '&pks=' + pks;
	//	alert(param);
	tujuan = 'kebun_spb_slave_detailv2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// Success Response
					document.getElementById('detail_kode').value = nospb;

					lockForm();
					document.getElementById('proses').value = 'cekData';
					//alertify.alert(con.responseText);

					document.getElementById('listSpb').style.display = 'none';
					document.getElementById('headher').style.display = 'block';
					document.getElementById('detailSpb').style.display = 'block';
					//document.getElementById('dtlSpb').disabled=true;
					//				document.getElementById('cancelSpb').disabled=true;
					var detailDiv = document.getElementById('ppDetailTable');
					detailDiv.innerHTML = con.responseText;
					status_inputan = 1;
					statForm = 1;
					//showTmbl();
					document.getElementById('mnculSma').checked = false;
					if (statDt == 1) {
						document.getElementById('mnculSma').checked = true;
						document.getElementById('mnculSma').disabled = true;
					}

					document.getElementById('tgl_ganti').disabled = true;
					document.getElementById('intex').disabled = true;
					document.getElementById('pks').disabled = true;
					document.getElementById('tahuntanam').disabled = true;
					// document.getElementById('referensimb').disabled = true;

					document.getElementById('tmbLheader').innerHTML = '';
					document.getElementById('ublLheader').innerHTML = '<button class=mybutton id=ublSpb onclick=UbahHeader()>Ubah</button>';
					document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button>";
					//alert(ars[1]);
					getDiv(ars[1], intex, pks);
					//   getPks(intex,pks);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function delData(nosbp) {
	noSpb = nosbp;
	param = 'noSpb=' + noSpb + '&proses=delData';
	tujuan = 'kebun_slave_save_spbv2.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Delete All Data!!!")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}
}
function delDetail(nospb,noreferensidt,tglpanen,karyawanid,blok,tph,sesi) {
	param = 'nospb='+nospb;
	param += '&noreferensidt='+noreferensidt;
	param += '&tglpanen='+tglpanen;
	param += '&karyawanid='+karyawanid;
	param += '&blok='+blok;
	param += '&tph='+tph;
	param += '&sesi='+sesi;
	param += '&proses=delDetail';
	tujuan = 'kebun_slave_save_spbv2.php';
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Anda yakin ingin menghapus data ini!!!")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}

}
function cariSpb() {
	txtSearch = document.getElementById('txtsearch').value;
	txtTgl = document.getElementById('tgl_cari').value;

	param = 'txtSearch=' + txtSearch + '&txtTgl=' + txtTgl + '&proses=cariNospb';
	//alert(param);
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('listSpb').style.display = 'block';
					document.getElementById('headher').style.display = 'none';
					document.getElementById('detailSpb').style.display = 'none';
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariData(num) {
	txtSearch = document.getElementById('txtsearch').value;
	txtTgl = document.getElementById('tgl_cari').value;
	param = 'txtSearch=' + txtSearch + '&txtTgl=' + txtTgl + '&proses=cariNospb';
	param += '&page=' + num;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cariBast(num) {
	param = 'proses=loadNewData';
	param += '&page=' + num;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancelSpb() {
	if (confirm("Are You Sure Want Cancel!!")) {
		document.getElementById('listSpb').style.display = 'block';
		document.getElementById('headher').style.display = 'none';
		document.getElementById('detailSpb').style.display = 'none';
		document.getElementById('tgl_ganti').value = '';
	} else {
		return;
	}
}
statForm = 0;
function frm_aju() {

	if (confirm("Are You Sure Already Done !!")) {
		displayList();
	}
}
function upDate() {
	nospb = document.getElementById('noSpb').value;
	tglSpb = document.getElementById('tgl_ganti').value;
	param = 'noSpb=' + nospb + '&proses=update' + '&tgl=' + tglSpb;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function reset_data() {
	if (statForm == 0) {
		nsbp = document.getElementById('noSpb').value;
		param = 'noSpb=' + nsbp + '&proses=delData';
		tujuan = 'kebun_slave_save_spbv2.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						displayList();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		if (confirm("Are You Sure Want Cancel This Entry !!!")) {
			post_response_text(tujuan, param, respog);
		} else {
			return;
		}
	} else if (statForm == 1) {
		if (confirm("Are You Sure Want Cancel This Action !!!")) {
			displayList();
		}
		document.getElementById('tgl_ganti').value = '';
	}
}
function dataKePDF(ev) {
	pt = document.getElementById('unitOrg');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	//gudang	=gudang.options[gudang.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;
	tujuan = 'kebun_spb_pdf.php';
	judul = 'List Data SPB PDF';
	param = 'pt=' + pt + '&periode=' + periode;
	//alert(param);
	printFile(param, tujuan, judul, ev)
}
function dataKePDFDat(nospb, ev) {
	noSpb = nospb;
	param = 'noSpb=' + noSpb + '&proses=pdf';
	//alert(param);
	tujuan = 'kebun_spbPdf.php';
	judul = 'List Data SPB PDF';

	//alert(param);
	printFile(param, tujuan, judul, ev)
}
function dataKeExcel(ev, tujuan) {
	pt = document.getElementById('unitOrg');
	periode = document.getElementById('periode');
	pt = pt.options[pt.selectedIndex].value;
	//gudang	=gudang.options[gudang.selectedIndex].value;
	periode = periode.options[periode.selectedIndex].value;
	//tujuan='kebun_spb_pdf.php';
	judul = 'Download Daftar SPB';
	param = 'pt=' + pt + '&periode=' + periode;
	//alert(param);
	printFile(param, tujuan, judul, ev)
}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
function getDiv(idn, intex, pks) {
	nsbp = document.getElementById('noSpb').value;
	if (idn == 0) {
		kdOrg = document.getElementById('kodeOrg').value;
		param = 'kdOrg=' + kdOrg + '&proses=getDivData';
		param += '&nospb=' + nsbp;
		tujuan = 'kebun_slave_save_spbv2.php';
	} else {
		kdrrg = idn.substring(0, 4);
		idDiv = idn;
		param = 'kdOrg=' + kdrrg + '&idDiv=' + idDiv + '&proses=getDivData';
		param += '&nospb=' + nsbp;
		tujuan = 'kebun_slave_save_spbv2.php';
	}
	//	alert(param);


	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);


					if (idn == 0) {
						//alert("masuk");
						document.getElementById('kodeDiv').innerHTML = con.responseText;
						getPks(intex, pks)
					} else {
						document.getElementById('kodeDiv').innerHTML = con.responseText;
						getPks(intex, pks);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getkerani() {
	kdOrg = document.getElementById('kodeDiv').options[document.getElementById('kodeDiv').selectedIndex].value;
	param = 'kodeDiv=' + kdOrg + '&proses=getkerani';
	tujuan = 'kebun_slave_save_spbv2.php';
	//	alert(param);


	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {

					//alert("masuk");
					document.getElementById('kerani').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function fillZero() {
	//alert("test");
	str = document.getElementById('nourut').value;
	// while (str.length < 7) {
	// 	str = 0 + str;
	// }
	while (str.length < 4) {
		str = 0 + str;
	}
	document.getElementById('nourut').value = str;
}
function lockForm() {
	document.getElementById('kodeOrg').disabled = true;
	document.getElementById('kodeDiv').disabled = true;
	document.getElementById('period').disabled = true;
	document.getElementById('nourut').disabled = true;
}
function getBlokSma(xz='',nikdt='',blokdt='',tphdt='') {
	if(xz==1){
		document.getElementById('tglpanen').value = '';
		document.getElementById('bjr').value = '0.00';
		document.getElementById('jjng').value = '0';
		document.getElementById('brondln').value = '0';
	}
	pil = document.getElementById('kodeDiv');
	pil = pil.options[pil.selectedIndex].value;
	der = document.getElementById('mnculSma');
	if (der.checked == true) {
		param = 'proses=getBlokSma';
	} else {
		param = 'proses=getBlokNor';
	}
	
	plasma = document.getElementById('blokplasma');
	if (plasma.checked == true) {
		param += '&plasma=1';
	} else {
		param += '&plasma=0';
	}
	
	tglspb = document.getElementById('tgl_ganti').value;
	tglpanen = document.getElementById('tglpanen').value;
	
	param += '&nikdt=' + nikdt;
	param += '&blokdt=' + blokdt;
	param += '&tphdt=' + tphdt;
	param += '&kdAfd=' + pil;
	param += '&tglpanen=' + tglpanen;
	param += '&tglspb=' + tglspb;
	tujuan = 'kebun_spb_slave_detailv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('blok').innerHTML = data[0];
					document.getElementById('pemanendt').innerHTML = data[1];
					if(tphdt!=''){
						getBjr(tphdt);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function searchBrg(title, content, ev) {
	width = '';
	height = '';
	showDialog1(title, content, width, height, ev);
	//alert('asdasd');
}
function findBrg() {
	txt = trim(document.getElementById('no_brg').value);
	dt = trim(document.getElementById('kdafd').value);

	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Too short words');
	} else {
		der = document.getElementById('mnculSma');
		if (der.checked == true) {
			param = 'idCer=1';
		} else {
			param = 'idCer=0';
		}
		param += '&txtfind=' + txt + '&proses=cariBlok';
		param += '&kdAfd=' + dt;
		tujuan = 'kebun_spb_slave_detailv2.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setBlok(blk) {
	l = document.getElementById('blok');

	for (a = 0; a < l.length; a++) {
		if (l.options[a].value == blk) {
			l.options[a].selected = true;
		}
	}
	closeDialog();
	getBjr();
}

function unposting(nospb, kodeorg, numrow) {
	param = 'proses=unposting' + '&nospb=' + nospb + '&kodeorg=' + kodeorg;
	tujuan = 'kebun_slave_save_spbv2.php';
	if (confirm('Anda yakin ingin unposting spb nomor ' + nospb + ' ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alert("Done");
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

/*function addSession(){
if(document.getElementById('detail_kode').value!=''){
nosbp=document.getElementById('detail_kode').value;
param='noSpb='+nosbp+'proses=addSession';
tujuan='kebun_slave_save_spbv2.php';
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alertify.alert(con.responseText);
}
else {
//document.getElementById('kodeDiv').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
post_response_text(tujuan, param, respog);
}
}
 */
/*function cekData()
{
nospb=document.getElementById('noSpb').value;
param='noSpb='+nospb;
param+='&proses=CekData';
tujuan = 'kebun_slave_save_spbv2.php';
post_response_text(tujuan, param, respog);
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alertify.alert(con.responseText);
}
else {
status=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}*/

function showformupload(ev) {
	title = "Add TK BM";
	width = '400';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:380px;height:auto;' ></div></fieldset>";
	showDialog4(title, content, width, height, ev);

	// pos = new Array();
	// pos = getMouseP(ev);

	// document.getElementById('dynamic2').style.top = pos[1]+'px';
	// document.getElementById('dynamic2').style.left = (pos[0] - 300) +'px';
	// document.getElementById('dynamic2').style.display='';
}
function showformuploadpetani(ev) {
	title = "Add Petani";
	width = '600';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUploadPetani style='overflow:auto;width:580px;height:auto;' ></div></fieldset>";
	showDialog4(title, content, width, height, ev);

	// pos = new Array();
	// pos = getMouseP(ev);

	// document.getElementById('dynamic2').style.top = pos[1]+'px';
	// document.getElementById('dynamic2').style.left = (pos[0] - 300) +'px';
	// document.getElementById('dynamic2').style.display='';
}
function changeo() {
	jjgtk = document.getElementById('jjgtk').value;
	brdtk = document.getElementById('brdtk').value;
	if (jjgtk != '') {
		jjgtk = parseFloat(jjgtk);
	}

	if (jjgtk == '' || jjgtk == 0 || jjgtk < 1) {
		document.getElementById('jjgtk').value = 0;
	}

	if (brdtk != '') {
		brdtk = parseFloat(brdtk);
	}
	if (brdtk == '' || brdtk == 0 || brdtk < 1) {
		document.getElementById('brdtk').value = 0;
	}

}

function changeosesitk() {
	sesitk = document.getElementById('sesitk').value;
	
	if (sesitk == '' || sesitk == 0 || sesitk < 1) {
		document.getElementById('sesitk').value = 1;
	}

}

function changeopetani() {
	jjgtk = document.getElementById('jjgpetani').value;
	if (jjgtk != '') {
		jjgtk = parseFloat(jjgtk);
	}
	if (jjgtk == '' || jjgtk == 0 || jjgtk < 1) {
		document.getElementById('jjgpetani').value = 1;
	}

	brdtk = document.getElementById('brdpetani').value;
	if (brdtk != '') {
		brdtk = parseFloat(brdtk);
	}
	if (brdtk == '' || brdtk < 0) {
		document.getElementById('brdpetani').value = 0;
	}
}
function showtkbm(ev) {
	// showformupload(ev);
	notransaksi= document.getElementById('detail_kode').value;
	kdOrg      = document.getElementById('kodeOrg').value;
	idDiv      = document.getElementById('kodeDiv').value;
	param      = 'proses=showtkbm&notransaksi=' + notransaksi + '&kdOrg=' + kdOrg + '&idDiv=' + idDiv;
	tujuan     = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					// document.getElementById('contUpload').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('60%','80%');
					loadfiles(notransaksi);
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showpetani(ev) {
	// showformuploadpetani(ev);
	alertify.popup().destroy();
	notransaksi = document.getElementById('detail_kode').value;
	kdOrg = document.getElementById('kodeOrg').value;
	idDiv = document.getElementById('kodeDiv').value;
	param = 'proses=showpetani&notransaksi=' + notransaksi + '&kdOrg=' + kdOrg + '&idDiv=' + idDiv;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					// document.getElementById('contUploadPetani').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','70%');

					loadfilespetani(notransaksi);
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanbm() {
	notransaksi = document.getElementById("notransaksi").innerHTML;
	tkbm = document.getElementById("tkbm").value;
	jjgtk = document.getElementById("jjgtk").value;
	brdtk = document.getElementById("brdtk").value;
	sesitk = document.getElementById("sesitk").value;
	kendaraantk = document.getElementById("kendaraantk").value;
	tgl = document.getElementById('tgl_tkbm').value;
	kdOrg = document.getElementById('kodeOrg').value;
	kontanan = document.getElementById('kontanan').value;

	if (tkbm == "") {
		alert("warning : Silahkan pilih karyawan.");
		return false;
	}
	if (kendaraantk == "") {
		alert("warning : Silahkan input kegiatan.");
		return false;
	}

	if (tgl == "") {
		alert("warning : Silahkan input tanggal.");
		return false;
	}

	param = 'proses=simpanbm&notransaksi=' + notransaksi + '&tkbm=' + tkbm + '&jjgtk=' + jjgtk + '&sesitk=' + sesitk + '&kendaraantk=' + kendaraantk + '&kdOrg=' + kdOrg + '&tgl=' + tgl+ '&kontanan=' + kontanan+ '&brdtk=' + brdtk;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//=== Success Response
					//alert('Uploaded Success.');
					document.getElementById("tkbm").value = "";
					setValue2('tkbm',null);
					document.getElementById("jjgtk").value = "0";
					document.getElementById("brdtk").value = "0";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpanpetani() {
	notransaksi = document.getElementById("notransaksi").innerHTML;
	petani = document.getElementById("petani").value;
	jjgpetani = document.getElementById("jjgpetani").value;
	brdpetani = document.getElementById("brdpetani").value;

	if (petani == "") {
		alert("warning : Silahkan pilih petani.");
		return false;
	}

	param = 'proses=simpanpetani&notransaksi='+notransaksi+'&petani='+petani+'&jjgpetani='+jjgpetani+'&brdpetani='+brdpetani;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//=== Success Response
					//alert('Uploaded Success.');
					document.getElementById("petani").value = "";
					document.getElementById("jjgpetani").value = "1";
					document.getElementById("brdpetani").value = "0";
					loadfilespetani(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi) {
	param = 'proses=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('contenttkbm') !== null) {
						document.getElementById('contenttkbm').innerHTML = con.responseText;
					}
					if(con.responseText!=''){
						document.getElementById('conttkbm').style.display = "block";
					}else{
						document.getElementById('conttkbm').style.display = "none";
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfilespetani(notransaksi) {
	param = 'proses=loadfilespetani&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					if (document.getElementById('listfilespetani') !== null) {
						document.getElementById('listfilespetani').innerHTML = data[0];
					}
					if (document.getElementById('contentpetani') !== null) {
						document.getElementById('contentpetani').innerHTML = data[0];
					}
					
					if(data[1]=='x'){
						document.getElementById('contpetani').style.display = "none";
					}else{
						document.getElementById('contpetani').style.display = "block";
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletebm(tkbm, kendaraan, tgl, sesi ,notransaksi) {
	param = 'proses=deletebm&notransaksi=' + notransaksi + '&tkbm=' + tkbm + '&tgl=' + tgl+ '&kendaraan=' + kendaraan+ '&sesi=' + sesi;
	tujuan = 'kebun_slave_save_spbv2.php';
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
function deletepetani(petani, notransaksi) {
	param = 'proses=deletepetani&notransaksi=' + notransaksi + '&petani=' + petani;
	tujuan = 'kebun_slave_save_spbv2.php';
	if (confirm('Yakin menghapus?')) {
	post_response_text(tujuan, param, respog);
	}	

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfilespetani(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewdata(noSpb, ev) {
	// title = "";
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Form</legend><div id=prev style='overflow:auto;width:550px;height:auto;' ></div></fieldset>";
	// showDialog2(title, content, width, height, ev);

	param = 'proses=previewdata&noSpb=' + noSpb;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					// document.getElementById('prev').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('60%','80%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function proporsitahuntanam(nospb,noreferensi,ev){
	param = 'proses=proporsitahuntanam&notransaksi=' + nospb+ '&noreferensi=' + noreferensi;
	tujuan = 'kebun_slave_save_spbv2.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function gettahuntanam(nour) {

	blok = document.getElementById('blokkecil_' +nour).value;

	param = 'proses=gettahuntanam';
	param += '&blok=' + blok;
	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('inputtahuntanam_'+nour).value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteproporsijjg(noid,notransaksi){

	param = "proses=deleteproporsijjg";
	param += '&noid=' + noid;
	param += '&notransaksi=' + notransaksi;

	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					proporsitahuntanam(notransaksi,'event');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveproporsi(notransaksi){
	param = "proses=saveproporsi";
	param += '&notransaksi=' + notransaksi;

	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					proporsitahuntanam(notransaksi,'event');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addproporsijjg(nour,notransaksi,tanggal){

	
	inputblokkecil		    = document.getElementById('blokkecil_' +nour).value;
	inputtahuntanam		    = document.getElementById('inputtahuntanam_' +nour).value;
	inputjjgproporsi		= document.getElementById('inputjjgproporsi_'+nour).value;
	inputbrondolanproporsi	= document.getElementById('inputbrondolanproporsi_'+nour).value;

	kodeorg = document.getElementById("inputblok_"+nour).innerHTML; 

	param = "proses=addproporsijjg";
	param += '&notransaksi=' + notransaksi;
	param += '&tanggal=' + tanggal;
	param += '&kodeorg=' + kodeorg;
	param += '&inputblokkecil=' + inputblokkecil;
	param += '&inputjjgproporsi=' + inputjjgproporsi;
	param += '&inputbrondolanproporsi=' + inputbrondolanproporsi;
	param += '&inputtahuntanam=' + inputtahuntanam;

	tujuan = 'kebun_slave_save_spbv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					proporsitahuntanam(notransaksi,'event');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewdata2(noSpb,tipe='html', ev) {
	// title = "";
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Form</legend><div id=prev style='overflow:auto;width:550px;height:auto;' ></div></fieldset>";
	// showDialog2(title, content, width, height, ev);

	param = 'proses=previewdata2&noSpb=' + noSpb + '&tipe=' + tipe;
	tujuan = 'kebun_slave_save_spbv2.php';
	
	if (tipe == 'html') {
		post_response_text(tujuan, param, respon);
    } else {
		tujuan=tujuan+"?"+param;
        printnopopup(tujuan);
    }

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					// document.getElementById('prev').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('60%','80%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}