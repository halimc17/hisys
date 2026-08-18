function getstatus() {
	stts = document.getElementById("status");
	if (stts.checked == true) {
	  document.getElementById("lstatus").innerHTML = "Aktif";
	} else {
	  document.getElementById("lstatus").innerHTML = "Non Aktif";
	}
  }
  
  function cekdeskripsi(deskripsi, ev) {
	unit =
	  document.getElementById("unit").options[
		document.getElementById("unit").selectedIndex
	  ].value;
	param =
	  "method=cekdeskripsi" + "&deskripsi=" + deskripsi.value + "&unit=" + unit;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
  
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("listdeskripsi").innerHTML = con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
function excel(ev, tujuan) {
	unitexp = document.getElementById("unitexp").value;
	perexp = document.getElementById("perexp").value;
	if (unitexp == "" || perexp == "") {
	  alert("Lengkapi unit dan periode.");
	  return;
	}
	judul = "Report Ms.Excel";
	param = "method=excel" + "&unitexp=" + unitexp + "&perexp=" + perexp;
	printFile(param, tujuan, judul, ev);
  }
  
  function add_new_data() {
	document.getElementById("header").style.display = "block";
	document.getElementById("detailpajakdanhak").style.display = "none";
	document.getElementById("listData").style.display = "none";
	document.getElementById("nodetail").value = "";
	cleardetail();
	cleardetailpajak();
	cleardetailakta();
  }
  
  
  
  function html(pt, unit, jenis, nohak, id, tipe) {
	param =
	  "method=html" +
	  "&id=" +
	  id +
	  "&tipe=" +
	  tipe +
	  "&pt=" +
	  pt +
	  "&unit=" +
	  unit +
	  "&jenis=" +
	  jenis +
	  "&nohak=" +
	  nohak;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			  alertify.popup().destroy();
			  alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('80%','80%');
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function displayList() {
	document.getElementById("divsch").value = "";
	document.getElementById("lokasisch").value = "";
	document.getElementById("nohaksch").value = "";
	document.getElementById("unitsch").value = "";
	document.getElementById("jenissch").value = "";
	document.getElementById("listData").style.display = "block";
	document.getElementById("header").style.display = "none";
	document.getElementById("detailpajakdanhak").style.display = "none";
	loaddata(0);
  }
  function getunit() {
	pt =
	  document.getElementById("pt").options[
		document.getElementById("pt").selectedIndex
	  ].value;
	param = "pt=" + pt + "&method=getunit";
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("unit").innerHTML = trim(con.responseText);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function edit(
	pt,
	unit,
	jenis,
	nohak,
	nonop,
	lokasi,
	luas,
	masaberlaku,
	nib,
	nosuratukur,
	tglsrtukur,
	pemiliksert,
	ketstatushak,
	nopeta,
	tglterbitsertifikat,
	noceksertifikat,
	tglceksertifikat,
	id
  ) {
	document.getElementById("listData").style.display = "none";
	document.getElementById("header").style.display = "block";
	document.getElementById("detailpajakdanhak").style.display = "block";
	document.getElementById("pt").disabled = true;
	document.getElementById("unit").disabled = true;
	//document.getElementById('nohak').disabled=true;
	document.getElementById("pt").value = pt;
	document.getElementById("unit").value = unit;
	document.getElementById("jenis").value = jenis;
	document.getElementById("nohak").value = nohak;
	document.getElementById("nonop").value = nonop;

	document.getElementById("nohakold").value = nohak;
	document.getElementById("lokasi").value = lokasi;
	document.getElementById("luas").value = luas;
	document.getElementById("masaberlaku").value = masaberlaku;
	document.getElementById("nib").value = nib;
	document.getElementById("nosuratukur").value = nosuratukur;
	document.getElementById("tglsrtukur").value = tglsrtukur;
	document.getElementById("pemiliksert").value = pemiliksert;
	document.getElementById("ketstatushak").value = ketstatushak;
	document.getElementById("nopeta").value = nopeta;
	document.getElementById("tglterbit").value = tglterbitsertifikat;
	document.getElementById("nocek").value = noceksertifikat;
	document.getElementById("tglcek").value = tglceksertifikat;
	document.getElementById("id").value = id;
  
	document.getElementById("method").value = "update";
	getstatus();
	//loadfiles(id,pt, unit, jenis, nohak,'statushak');
	loaddatapajak(id);
  }
  
  function editpajak(
	idpajak,
	thnpajak,
	nospptpbb,
	namawp,
	nilaitanah,
	nilainjoptanah,
	nilaibangunan,
	nilainjopbangunan,
	pbb,
	denda,
	jatuhtempo,
	kurangbayar,
	letakobjekpajak,
	keterangan,
	statusbayar
  ) {
	//document.getElementById('idpajak').value=idpajak;
	document.getElementById("thnpajak").value = thnpajak;
	document.getElementById("nospptpbb").value = nospptpbb;
	document.getElementById("namawp").value = namawp;
	document.getElementById("nilaitanah").value = nilaitanah;
	document.getElementById("nilainjoptanah").value = nilainjoptanah;
	document.getElementById("nilaibangunan").value = nilaibangunan;
	document.getElementById("nilainjopbangunan").value = nilainjopbangunan;
	document.getElementById("pbb").value = pbb;
	document.getElementById("denda").value = denda;
	document.getElementById("jatuhtempo").value = jatuhtempo;
	document.getElementById("kurangbayar").value = kurangbayar;
	document.getElementById("letakobjekpajak").value = letakobjekpajak;
	document.getElementById("keterangan").value = keterangan;
	document.getElementById("statusbayar").value = statusbayar;
  
	document.getElementById("methodpajak").value = "updatepajak";
	loaddatapajak(idpajak);
	/*getstatus();
	  loadfiles(pt,jenis,unit,nohak);
	  loaddatapajak();*/
  }
  
  function del(nodetail) {
	param = "method=delete" + "&nodetail=" + nodetail;
	tujuan = "lgl_slave_sertipikat.php";
	if (confirm("Anda yakin ???")) {
	  post_response_text(tujuan, param, respog);
	}
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			loaddata();
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  function deldetail(id) {
	param = "method=deldetail" + "&id=" + id;
	tujuan = "lgl_slave_sertipikat.php";
	if (confirm("Anda yakin ???")) {
	  post_response_text(tujuan, param, respog);
	}
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			loaddataakta();
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function save() {
	nib = document.getElementById("nib").value;
	nosuratukur = document.getElementById("nosuratukur").value;
	tglsrtukur = document.getElementById("tglsrtukur").value;
	pemiliksert = document.getElementById("pemiliksert").value;
	ketstatushak = document.getElementById("ketstatushak").value;
	nopeta = document.getElementById("nopeta").value;
	tglterbit = document.getElementById("tglterbit").value;
	nocek = document.getElementById("nocek").value;
	nohakold = document.getElementById("nohakold").value;
	tglcek = document.getElementById("tglcek").value;
	pt = document.getElementById("pt").value;
	unit = document.getElementById("unit").value;
	jenis = document.getElementById("jenis").value;
	nohak = document.getElementById("nohak").value;
	nonop = document.getElementById("nonop").value;

	lokasi = document.getElementById("lokasi").value;
	luas = document.getElementById("luas").value;
	masaberlaku = document.getElementById("masaberlaku").value;
	id = pt + unit + jenis + nohak;
  
	method = document.getElementById("method").value;
  
	// if (pt == "" || unit == "" || jenis == "" || nohak == "") {
	//   alertify.alert("Lengkapi Pengisian.");
	//   return;
	// }

	if(pt == "" ){
		alertify.alert("PT Wajib Diisi..");
		return;
	}

	if(unit == "" ){
		alertify.alert("Unit Wajib Diisi..");
		return;
	}

	if(jenis == "" ){
		alertify.alert("Jenis Wajib Diisi..");
		return;
	}

	if(nohak == "" ){
		alertify.alert("No. Hak Wajib Diisi..");
		return;
	}
  
	param = "pt=" + pt;
	param += "&unit=" + unit;
	param += "&jenis=" + jenis;
	param += "&nohak=" + nohak;
	param += "&nonop=" + nonop;
	param += "&nohakold=" + nohakold;
	param += "&lokasi=" + lokasi;
	param += "&luas=" + luas;
	param += "&masaberlaku=" + masaberlaku;
	param += "&nib=" + nib;
	param += "&nosuratukur=" + nosuratukur;
	param += "&tglsrtukur=" + tglsrtukur;
	param += "&pemiliksert=" + pemiliksert;
	param += "&ketstatushak=" + ketstatushak;
	param += "&nopeta=" + nopeta;
	param += "&tglterbit=" + tglterbit;
	param += "&nocek=" + nocek;
	param += "&tglcek=" + tglcek;
	param += "&id=" + id;
	param += "&method=" + method;
	tujuan = "lgl_slave_sertipikat.php";
	// alert(param);
	// return;
  
	post_response_text(tujuan, param, respon);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("pt").disabled = true;
			document.getElementById("unit").disabled = true;
			document.getElementById("jenis").disabled = true;
			//document.getElementById('nohak').disabled=true;
  
			// nodet = pt+unit+jenis+nohak;
			document.getElementById("method").value = "update";
			document.getElementById("nohakold").value = nohak;
			document.getElementById("nodetail").value = con.responseText;
			document.getElementById("idpajak").value = con.responseText;
			document.getElementById("detailpajakdanhak").style.display = "block";
			// document.getElementById("formuploadstatushak").style.display =
			//   "block";
			/*document.getElementById('formuploadpajak').style.display = 'block';
					  document.getElementById('formuploadpengalihanhak').style.display = 'block';*/
  
			loaddata(0);
			//loadfiles(id, pt, unit, jenis, nohak, "statushak");
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function savepajak() {
	/*	document.getElementById('SPPTBB').style.display = 'table-row';
	  document.getElementById('APHK').style.display = 'table';*/
	idpajak = document.getElementById("idpajak").value;
	thnpajak = document.getElementById("thnpajak").value;
	nospptpbb = document.getElementById("nospptpbb").value;
	namawp = document.getElementById("namawp").value;
	nilaitanah = document.getElementById("nilaitanah").value;
	nilainjoptanah = document.getElementById("nilainjoptanah").value;
	nilaibangunan = document.getElementById("nilaibangunan").value;
	nilainjopbangunan = document.getElementById("nilainjopbangunan").value;
	pbb = document.getElementById("pbb").value;
	denda = document.getElementById("denda").value;
	jatuhtempo = document.getElementById("jatuhtempo").value;
	kurangbayar = document.getElementById("kurangbayar").value;
	keterangan = document.getElementById("keterangan").value;
	letakobjekpajak = document.getElementById("letakobjekpajak").value;
	statusbayar = document.getElementById("statusbayar").value;
	method = document.getElementById("methodpajak").value;
  
	if (nospptpbb == "" || pbb == "" || thnpajak == "") {
	  alertify.alert("Lengkapi Pengisian.");
	  return;
	}
	param = "&idpajak=" + idpajak;
	param += "&thnpajak=" + thnpajak;
	param += "&nospptpbb=" + nospptpbb;
	param += "&namawp=" + namawp;
	param += "&nilaitanah=" + nilaitanah;
	param += "&nilainjoptanah=" + nilainjoptanah;
	param += "&nilaibangunan=" + nilaibangunan;
	param += "&nilainjopbangunan=" + nilainjopbangunan;
	param += "&letakobjekpajak=" + letakobjekpajak;
	param += "&keterangan=" + keterangan;
	param += "&pbb=" + pbb;
	param += "&denda=" + denda;
	param += "&jatuhtempo=" + jatuhtempo;
	param += "&kurangbayar=" + kurangbayar;
	param += "&statusbayar=" + statusbayar;
	param += "&method=" + method;
  
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respon);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			//document.getElementById("formuploadpajak").style.display = "block";
			document.getElementById("nodetail").value = idpajak;
			loaddatapajak(idpajak);
			//loadfiles(idpajak, pt, unit, jenis, nohak, "pajak");
			cleardetailpajakx();
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  function loaddatapajak(id) {
	pt = document.getElementById("pt").value;
	unit = document.getElementById("unit").value;
	jenis = document.getElementById("jenis").value;
	nohak = document.getElementById("nohak").value;
	nodet = document.getElementById("id").value;
	idpajak = document.getElementById("idpajak").value = id;
  
	param = "idpajak=" + idpajak;
	param += "&method=loaddatapajak";
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respon);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("loaddatapajak").innerHTML = con.responseText;
			document.getElementById("nodetail").value = idpajak;
			//document.getElementById('listfilespajak').innerHTML = con.responseText;
			loaddataakta(idpajak);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function cleardetailpajak() {
	document.getElementById("idpajak").value = "";
	document.getElementById("thnpajak").value = "";
	document.getElementById("pbb").value = "";
	document.getElementById("denda").value = "";
	document.getElementById("jatuhtempo").value = "";
	document.getElementById("kurangbayar").value = "";
	document.getElementById("statusbayar").value = "";
	document.getElementById("nospptpbb").value = "";
	document.getElementById("namawp").value = "";
	document.getElementById("nilaitanah").value = "";
	document.getElementById("nilainjoptanah").value = "";
	document.getElementById("nilaibangunan").value = "";
	document.getElementById("nilainjopbangunan").value = "";
	document.getElementById("letakobjekpajak").value = "";
	document.getElementById("keterangan").value = "";
	document.getElementById("methodpajak").value = "insertpajak";
  }
  
  function cleardetailpajakx() {
	document.getElementById("thnpajak").value = "";
	document.getElementById("pbb").value = "";
	document.getElementById("denda").value = "";
	document.getElementById("jatuhtempo").value = "";
	document.getElementById("kurangbayar").value = "";
	document.getElementById("statusbayar").value = "";
	document.getElementById("nospptpbb").value = "";
	document.getElementById("namawp").value = "";
	document.getElementById("nilaitanah").value = "";
	document.getElementById("nilainjoptanah").value = "";
	document.getElementById("nilaibangunan").value = "";
	document.getElementById("nilainjopbangunan").value = "";
	document.getElementById("letakobjekpajak").value = "";
	document.getElementById("keterangan").value = "";
	document.getElementById("methodpajak").value = "insertpajak";
  }
  
  function deldetailpajak(id,idpajak) {
	param = "method=deldetailpajak" + "&id=" + id;
	tujuan = "lgl_slave_sertipikat.php";
	if (confirm("Anda yakin ???")) {
	  post_response_text(tujuan, param, respog);
	}
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			loaddatapajak(idpajak);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  function cleardetail() {
	document.getElementById("nib").value = "";
	document.getElementById("nosuratukur").value = "";
	document.getElementById("tglsrtukur").value = "";
	document.getElementById("pemiliksert").value = "";
	document.getElementById("ketstatushak").value = "";
	document.getElementById("pt").value = "";
	document.getElementById("pt").disabled = false;
	document.getElementById("unit").value = "";
	document.getElementById("unit").disabled = false;
	document.getElementById("jenis").value = "";
	document.getElementById("jenis").disabled = false;
	document.getElementById("nohak").value = "";
	document.getElementById("nohak").disabled = false;

	document.getElementById("nonop").value = "";
	document.getElementById("nonop").disabled = false;
	
	
	document.getElementById("lokasi").value = "";
	document.getElementById("luas").value = "";
	document.getElementById("masaberlaku").value = "";
	document.getElementById("method").value = "insert";
	document.getElementById("tomboldetail").disabled = false;
	document.getElementById("nodetail").value = "";
	document.getElementById("nopeta").value = "";
	document.getElementById("tglterbit").value = "";
	document.getElementById("nocek").value = "";
	document.getElementById("tglcek").value = "";
	document.getElementById("id").value = "";
	document.getElementById("loaddatapajak").innerHTML = "";
	document.getElementById("loaddataakta").innerHTML = "";
	cleardetailakta();
	cleardetailpajak();
  }
  
  function cleardetailakta() {
	document.getElementById("jenisakta").value = "";
	document.getElementById("pembuat").value = "";
	document.getElementById("namadetailakta").value = "";
	document.getElementById("namapembeli").value = "";
	document.getElementById("nodetailakta").value = "";
	document.getElementById("tgldetailakta").value = "";
	document.getElementById("nilaidetailakta").value = "";
	document.getElementById("ketdetailakta").value = "";
	document.getElementById("methodakta").value = "insertakta";
  }
  
  function saveakta(id) {
	nodetail = document.getElementById("nodetail").value;
	jenisakta = document.getElementById("jenisakta").value;
	pembuat = document.getElementById("pembuat").value;
	namadetailakta = document.getElementById("namadetailakta").value;
	namapembeli = document.getElementById("namapembeli").value;
	nodetailakta = document.getElementById("nodetailakta").value;
	tgldetailakta = document.getElementById("tgldetailakta").value;
	nilaidetailakta = document.getElementById("nilaidetailakta").value;
	ketdetailakta = document.getElementById("ketdetailakta").value;
	method = document.getElementById("methodakta").value;
  
	if (nodetail == "" || jenisakta == "" || pembuat == "") {
	  alertify.alert("Lengkapi Pengisian.");
	  return;
	}
  
	param = "nodetail=" + nodetail;
	param += "&jenisakta=" + jenisakta;
	param += "&pembuat=" + pembuat;
	param += "&namadetailakta=" + namadetailakta;
	param += "&namapembeli=" + namapembeli;
	param += "&nodetailakta=" + nodetailakta;
	param += "&tgldetailakta=" + tgldetailakta;
	param += "&nilaidetailakta=" + nilaidetailakta;
	param += "&ketdetailakta=" + ketdetailakta;
	param += "&method=" + method;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respon);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			cleardetailakta();
			loaddataakta(nodetail);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function loaddataakta(id) {
	pt = document.getElementById("pt").value;
	unit = document.getElementById("unit").value;
	jenis = document.getElementById("jenis").value;
	nohak = document.getElementById("nohak").value;
	nodet = document.getElementById("id").value;
  
	/*nodetail = document.getElementById('nodetail').value = nodet;*/
	nodetail = id;
	param = "nodetail=" + nodetail;
	param += "&method=loaddataakta";
  
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respon);
	function respon() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("loaddataakta").innerHTML = con.responseText;
			document.getElementById("nodetail").value = nodetail;
			//loadfiles(id, pt, unit, jenis, nohak, "all");
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function getPage() {
	pg = document.getElementById("pages");
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
  }

  function viewexcel(pt, unit, jenis, nohak, id, tipe) {
	ev = "event";
	//param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe;
	param =
	  "method=html" +
	  "&pt=" +
	  pt +
	  "&id=" +
	  id +
	  "&tipe=" +
	  tipe +
	  "&unit=" +
	  unit +
	  "&nohak=" +
	  nohak +
	  "&jenis=" +
	  jenis;
	tujuan = "lgl_slave_sertipikat.php" + "?" + param;
	printnopopup(tujuan);
  }

  function excel_list() {
	divsch = document.getElementById("divsch").value;
	jenissch = document.getElementById("jenissch").value;
	unitsch = document.getElementById("unitsch").value;
	nohaksch = document.getElementById("nohaksch").value;
	lokasisch = document.getElementById("lokasisch").value;
	luassch = document.getElementById("luassch").value;
	thnsertisch = document.getElementById("thnsertisch").value;
	pemiliksertisch = document.getElementById("pemiliksertisch").value;
	param = "method=excel_list";
	if (divsch != "") {
	  param += "&divsch=" + divsch;
	}
	if (jenissch != "") {
	  param += "&jenissch=" + jenissch;
	}
	if (unitsch != "") {
	  param += "&unitsch=" + unitsch;
	}
	if (nohaksch != "") {
	  param += "&nohaksch=" + nohaksch;
	}
	if (lokasisch != "") {
	  param += "&lokasisch=" + lokasisch;
	}
	if (luassch != "") {
	  param += "&luassch=" + luassch;
	}
	if (pemiliksertisch != "") {
	  param += "&pemiliksertisch=" + pemiliksertisch;
	}
	if (thnsertisch != "") {
	  param += "&thnsertisch=" + thnsertisch;
	}

	tujuan = "lgl_slave_sertipikat.php" + "?" + param;
	printnopopup(tujuan);
	
  }

  
  function loaddata(page) {
	divsch = document.getElementById("divsch").value;
	jenissch = document.getElementById("jenissch").value;
	unitsch = document.getElementById("unitsch").value;
	nohaksch = document.getElementById("nohaksch").value;
	lokasisch = document.getElementById("lokasisch").value;
	luassch = document.getElementById("luassch").value;
	thnsertisch = document.getElementById("thnsertisch").value;
	pemiliksertisch = document.getElementById("pemiliksertisch").value;
	param = "method=loaddata&page=" + page;
	if (divsch != "") {
	  param += "&divsch=" + divsch;
	}
	if (jenissch != "") {
	  param += "&jenissch=" + jenissch;
	}
	if (unitsch != "") {
	  param += "&unitsch=" + unitsch;
	}
	if (nohaksch != "") {
	  param += "&nohaksch=" + nohaksch;
	}
	if (lokasisch != "") {
	  param += "&lokasisch=" + lokasisch;
	}
	if (luassch != "") {
	  param += "&luassch=" + luassch;
	}
	if (pemiliksertisch != "") {
	  param += "&pemiliksertisch=" + pemiliksertisch;
	}
	if (thnsertisch != "") {
	  param += "&thnsertisch=" + thnsertisch;
	}
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			isdt = con.responseText.split("####");
			document.getElementById("contain").innerHTML = isdt[0];
			document.getElementById("footData").innerHTML = isdt[1];
		  //   document.getElementById("unitsch").innerHTML = isdt[2];
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function form() {
	width = "";
	height = "";
	content =
	  '<fieldset style="width:97%;"><div id=contview style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
	ev = "event";
	title = "View";
	showDialog5(title, content, width, height, ev);
  }
  
  function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (
	  trim(ext[1]) == "jpg" ||
	  trim(ext[1]) == "jpeg" ||
	  trim(ext[1]) == "png"
	) {
	  form();
	  param = "method=viewfile&namafile=" + namafile;
	  tujuan = "lgl_slave_sertipikat.php";
	  post_response_text(tujuan, param, respog);
	} else {
	  alertify.alert("File tidak dapat di tampilkan, silahkan download untuk melihat isi file.");
	  return;
	}
  
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("contview").innerHTML = con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function showformupload(ev) {
	title = "UPLOAD FILES";
	width = "";
	height = "";
	content =
	  "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
  
	pos = new Array();
	pos = getMouseP(ev);
  
	document.getElementById("dynamic2").style.top = pos[1] + "px";
	document.getElementById("dynamic2").style.left = pos[0] - 500 + "px";
	document.getElementById("dynamic2").style.display = "";
  }
  
  function showupload(ev, id, jenis) {
	  param = "";
	  param += "id=" + id;
	  param += "&jenis=" + jenis;
	  param += "&method=showupload";
	  tujuan = "lgl_slave_sertipikat.php";
	  post_response_text(tujuan, param, respog);
	
	  function respog() {
		  if (con.readyState == 4) {
			  if (con.status == 200) {
				  busy_off();
				  if (!isSaveResponse(con.responseText)) {
					  alert(con.responseText);
				  } else {
					  alertify.popup().destroy();
					  alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('600px','400px');
					  loadfiles(id);
				  }
			  } else {
				  busy_off();
				  error_catch(con.status);
			  }
		  }
	  }
  }
  
  function loadfiles(id) {
	  param = 'method=loadfiles&id='+id;
	  tujuan = 'lgl_slave_sertipikat.php';
	  post_response_text(tujuan, param, respog);
	  function respog() {
		  if (con.readyState == 4) {
			  if (con.status == 200) {
				  busy_off();
				  if (!isSaveResponse(con.responseText)) {
					  alert(con.responseText);
				  } else {
					  document.getElementById('listfiles').innerHTML = con.responseText;
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
	  document.getElementById("progressBar").style.display="block";
	  document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded/1024)) + " KB of " + numberFormat(Math.round(event.total/1024))+" KB";
	  var percent = (event.loaded / event.total) * 100;
	  document.getElementById("progressBar").value = Math.round(percent);
	  document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
  }
  function completeHandler(event) {
	  document.getElementById("progressBar").style.display="none";
	  document.getElementById("status").innerHTML = event.target.responseText;
	  document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
  }
  function errorHandler(event) {
	document.getElementById("status").innerHTML = "Upload Failed";
  }
  function abortHandler(event) {
	document.getElementById("status").innerHTML = "Upload Aborted";
  }
  
  function submitfile() {
	  var id = trim(document.getElementById("noppupload").innerHTML);
	  var kriteriaefil = document.getElementById("kriteriaefil").value;
	  var file = document.getElementById("upload").files[0];
	  var formdata = new FormData();
	  formdata.append("file", file);
	  formdata.append("fileupload", getValue('upload'));
	  formdata.append("id", id);
	  formdata.append("kriteriaefil", kriteriaefil);
	  if (getValue('upload') == "") {
		  alert("warning : Upload file has been empty.");
		  return false;
	  }
	  
	  cekfileupload(getValue('upload'));
	  
	  document.getElementsByClassName("mybutton").disabled=true;
	  busy_on();
	  var con = createXMLHttpRequest();
	  //tambahan progress bar
	  con.upload.addEventListener("progress", progressHandler, false);
	  con.addEventListener("load", completeHandler, false);
	  con.addEventListener("error", errorHandler, false);
	  con.addEventListener("abort", abortHandler, false);
	  //tambahan progress bar -end-
	  con.open("POST", "lgl_slave_sertipikat.php?method=submitfile", true);
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
					  document.getElementsByClassName("mybutton").disabled=false;
					  alertify.set('notifier','position', 'top-right');
					  alertify.set('notifier','delay', 2);
					  alertify.success('Berhasil');
					  document.getElementById("upload").value = "";
					  loadfiles(id);
				  }
			  } else {
				  busy_off();
				  error_catch(con.status);
			  }
		  }
	  }
  }
  
  function deletefile(id, namafile) {
	  param = "method=deletefile";
	  param += "&id=" + id;
	  param += "&namafile=" + namafile;
	  tujuan = 'lgl_slave_sertipikat.php';
	  post_response_text(tujuan, param, respog);
	  function respog() {
		  if (con.readyState == 4) {
			  if (con.status == 200) {
				  busy_off();
				  if (!isSaveResponse(con.responseText)) {
					  alertify.alert(con.responseText);
				  } else {
					  loadfiles(id);
				  }
			  } else {
				  busy_off();
				  error_catch(con.status);
			  }
		  }
	  }
  }
  
  function viewlistfile(pt, jenis, unit, nohak) {
	width = "";
	height = "";
	content =
	  '<fieldset style="width:97%;"><div id=contviewz style="width:100%;height:100%;overflow:auto;"></div></fieldset>';
	ev = "event";
	title = "View";
	showDialog4(title, content, width, height, ev);
  
	param =
	  "method=viewlistfile&jenis=" +
	  jenis +
	  "&pt=" +
	  pt +
	  "&unit=" +
	  unit +
	  "&nohak=" +
	  nohak;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("contviewz").innerHTML = con.responseText;
			//loadfiles(pt,jenis,unit,nohak);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function loadfilespajak(id, pt, unit, jenis, nohak, tipex) {
	param = "method=loadfiles";
	param += "&id=" + id;
	param += "&pt=" + pt;
	param += "&unit=" + unit;
	param += "&jenis=" + jenis;
	param += "&nohak=" + nohak;
	param += "&tipex=" + tipex;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			//document.getElementById("formuploadpajak").style.display = "block";
			//document.getElementById("listfilespajak").innerHTML =
			  //con.responseText;
  
			//loadfilespengalihanhak(id, pt, unit, jenis, nohak, "pengalihanhak");
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function loadfilespengalihanhak(id, pt, unit, jenis, nohak, tipex) {
	param = "method=loadfiles";
	param += "&id=" + id;
	param += "&pt=" + pt;
	param += "&unit=" + unit;
	param += "&jenis=" + jenis;
	param += "&nohak=" + nohak;
	param += "&tipex=" + tipex;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			// document.getElementById("listfilespengalihanhak").innerHTML =
			//   con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function form_ajukan(id, numrow) {
	width = "350";
	height = "";
	content =
	  '<fieldset><legend>Submission Form</legend><div id=containeraju align=center style="width:320px;max-height:150px;overflow:auto;"></div></fieldset>';
	ev = "event";
	title = "";
	showDialog5(title, content, width, height, ev);
  
	param = "method=form_ajukan" + "&id=" + id + "&numrow=" + numrow;
  
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("containeraju").innerHTML = con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function ajukan() {
	kepada = document.getElementById("kepada").value;
	notransaksi = document.getElementById("notran_aju").innerHTML;
	numrow = document.getElementById("numrow").value;
	param = "method=ajukan" + "&notransaksi=" + notransaksi;
	param += "&numrow=" + numrow;
	param += "&kepada=" + kepada;
  
	if (kepada == "") {
	  alertify.alert("Isikan nama penyetuju.");
	  return;
	}
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			alertify.alert("Sucses");
			closeDialog5();
			getPage();
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function getstatuspersetujuan(notransaksi) {
	width = "650";
	height = "";
	content =
	  '<fieldset><legend>Form</legend><div id=contview style="width:630px;max-height:350px;overflow:auto;"></div></fieldset>';
	ev = "event";
	title = "";
	showDialog4(title, content, width, height, ev);
  
	param = "method=getstatuspersetujuan" + "&notransaksi=" + notransaksi;
	tujuan = "lgl_slave_sertipikat.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("contview").innerHTML = con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }
  
  function frm_aju() {
	if (confirm("Process submission ??")) {
	  document.getElementById("listData").style.display = "none";
	  document.getElementById("header").style.display = "none";
	  document.getElementById("detailpajakdanhak").style.display = "none";
	  document.getElementById("persetujuan").style.display = "block";
	  pt = document.getElementById("pt").value;
	  jenis = document.getElementById("jenis").value;
	  nohak = document.getElementById("nohak").value;
  
	  param =
		"pt=" +
		pt +
		"&jenis=" +
		jenis +
		"&nohak=" +
		nohak +
		"&method=formPersetujuan";
	  tujuan = "lgl_slave_sertipikat.php";
	  function respog() {
		if (con.readyState == 4) {
		  if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
			  alertify.alert(con.responseText);
			} else {
			  document.getElementById("persetujuandata").innerHTML =
				con.responseText;
			}
		  } else {
			busy_off();
			error_catch(con.status);
		  }
		}
	  }
	  post_response_text(tujuan, param, respog);
	} else {
	  clear_all_data();
	  displayList();
	}
	//}
  }
  function save_persetujuan() {
	pt = document.getElementById("pt").value;
	jenis = document.getElementById("jenis").value;
	kary = document.getElementById("karywn_id").value;
	nohak = document.getElementById("nohak").value;
  
	if (kary == "") {
	  alertify.alert("Please verify  your selection");
	} else {
	  method = "insert_persetujuan";
	  param =
		"pt=" +
		pt +
		"&jenis=" +
		jenis +
		"&nohak=" +
		nohak +
		"&usr_id=" +
		kary +
		"&method=" +
		method;
  
	  tujuan = "lgl_slave_sertipikat.php";
  
	  function respog() {
		if (con.readyState == 4) {
		  if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
			  alertify.alert(con.responseText);
			} else {
			  //alert(con.responseText);
			  /*document.getElementById('contain').innerHTML=con.responseText;
						  displayList();*/
			  loaddata();
			  document.getElementById("persetujuan").style.display = "none";
			  document.getElementById("listData").style.display = "block";
			  document.getElementById("header").style.display = "none";
			  document.getElementById("detailpajakdanhak").style.display = "none";
			}
		  } else {
			busy_off();
			error_catch(con.status);
		  }
		}
	  }
	  //post_response_text(tujuan, param, respog);
	  var answer = confirm("Are you sure?");
	  if (answer) {
		post_response_text(tujuan, param, respog);
	  } else {
		reset_data_setuju();
	  }
	}
  }
  
  function getdatapdf(namafile){
	  param = 'method=getdatapdf&namafile='+namafile;
	  tujuan = 'lgl_slave_sertipikat.php';
	  post_response_text(tujuan, param, respog);
	  
	  function respog(){
		  if(con.readyState == 4){
			  if(con.status == 200){
				  busy_off();
				  if(!isSaveResponse(con.responseText)){
					  alertify.alert("Info",con.responseText);
				  }else{
					  alertify.popuppdf().destroy();
					  // alertify.popuppdf().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
					  alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='lgl_slave_sertipikat.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false,'maximizable':true,'startMaximized':true}).resizeTo('90%','80%');
				  }
			  }else{
				  busy_off();
				  error_catch(con.status);
			  }
		  }
	  }
  }