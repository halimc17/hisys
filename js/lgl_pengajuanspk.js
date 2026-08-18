function loaddatapajak(notransaksi) {
	param = 'method=loaddatapajak';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contpajak').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatanopol(notransaksi) {
	param = 'method=loaddatanopol';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contnopol').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletenopol(no,notransaksi) {
	param = 'no=' + no+ '&method=deletenopol';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contnopol').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addnopol(sumber) {
	nopol = document.getElementById('nopol').value;
	notransaksi = document.getElementById('notransaksi').value;
	supir = document.getElementById('supir').value;
	param = 'method=addnopol';
	param += '&nopol=' + nopol;
	param += '&supir=' + supir;
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contnopol').innerHTML = trim(con.responseText);
					document.getElementById('nopol').value='';
					document.getElementById('supir').value='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function editnopol(nopol,supir,stat) {
	document.getElementById('nopol2').disabled=true;
	document.getElementById('nopol2').value=nopol;
	document.getElementById('supir2').value=supir;
	document.getElementById('status2').value=stat;
}

function addnopol2(notransaksi) {
	nopol = document.getElementById('nopol2').value;
	supir = document.getElementById('supir2').value;
	stat = document.getElementById('status2').value;
	param = 'method=addnopol2';
	param += '&nopol=' + nopol;
	param += '&supir=' + supir;
	param += '&notransaksi=' + notransaksi;
	param += '&stat=' + stat;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					html(notransaksi,'html');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function deletepajak(no,notransaksi) {
	param = 'no=' + no+ '&method=deletepajak';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contpajak').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function addpajak() {
	jenispajak = document.getElementById('jenispajak').value;
	nilaipajak = document.getElementById('nilaipajak').value;
	notransaksi = document.getElementById('notransaksi').value;
	param = 'jenispajak=' + jenispajak + '&nilaipajak=' + nilaipajak + '&method=addpajak';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contpajak').innerHTML = trim(con.responseText);
					setValue2('jenispajak','');
					document.getElementById('nilaipajak').value='';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getjenis(){
	kategori=getValue('kategori');
	param = 'kategori=' + kategori+ '&method=getjenis';
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jenis').innerHTML = trim(con.responseText);
					getJenisSup();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showhide(){
	if(document.getElementById('coba').type=="text"){
		document.getElementById('coba').type="password";
	}else{
		document.getElementById('coba').type="text";
	}
}


/* 
function settermin() {
	nilai = document.getElementById('persentermin').value;
	rptermin = document.getElementById('rptermin').value;
	terminke = document.getElementById('terminke').value;
	ket = document.getElementById('kettermin').value;
	baru = document.getElementById('conttermin');
	if (nilai == '' || terminke == '' || ket == '') {
		return false;
	}
	if (nilai > 100) {
		alert('Persen termin terlalu besar !');
		return;
	}
	if (terminke == '' || nilai == '' || terminke == '0' || nilai == '0') {
		alert('Termin Ke ? dan Persen Termin wajib diisi !');
		return;
	}
	var cont = baru.getElementsByTagName('INPUT');
	var i = cont.length + 1;
	if (terminke != i) {
		alert('Termin Ke salah, seharusnya ke = ' + i);
		return;
	}
	if (cont.length != '') {
		n = x = w = y = 0;
		for (j = 1; j <= cont.length; j++) {
			var n = document.getElementById('kettermindt####' + j).innerHTML;
			w = n.split("#####"); // alert(w[1]);
			x = parseFloat(x) + parseFloat(w[1]);
		}
		y = parseFloat(x) + parseFloat(nilai);
		if (y > 100) {
			document.getElementById('rptermin').value='';
			alert('Persen termin melebihi 100%, total persen termin = ' + y + '%');
			return;
		}
	}
	baru.innerHTML += "<label id=\"kettermindt####" + i + "\" style=\"display:none\"/>" + terminke + "#####" + nilai + "#####" + ket + "#####" + rptermin + "</label>";
	baru.innerHTML += "<input id=\"termindt####" + i + "\" readonly title="+ket+" onclick=\"delpjkdantermin(this.id,'termin')\" class='myinputtext' value=\"Ke: " + terminke + " = " + nilai + "%, Rp = "+rptermin+"\" style=\"display: inline-block;width:90%;cursor:pointer\"/>";
	document.getElementById('terminke').value = i + 1;
	document.getElementById('persentermin').value = '';
	document.getElementById('rptermin').value = '';
	document.getElementById('kettermin').value = '';
	document.getElementById('kettermin').disabled = true;
}
function delpjkdantermin(id, tipe) {
	idx = id.split("####");
	if (tipe == 'pajak') {
		document.getElementById('pajak####' + idx[1]).remove();
		document.getElementById('pajakx####' + idx[1]).remove();
	}
	if (tipe == 'termin') {
		baru = document.getElementById('conttermin');
		var cont = baru.getElementsByTagName('INPUT');
		var i = cont.length;
		if (i != idx[1]) {
			alert('Hapus termin ke ' + i + ' terlebih dahulu !');
			return false;
		}
		document.getElementById('kettermindt####' + idx[1]).remove();
		document.getElementById('termindt####' + idx[1]).remove();
		document.getElementById('terminke').value = idx[1];
	}
}
function setpajak(nilai) {
	jns = document.getElementById('jenispajak').value;
	baru = document.getElementById('contpajak');
	if (nilai > 50) {
		alert('Persen pajak terlalu besar !');
		return;
	}
	var cont = baru.getElementsByTagName('INPUT');
	var i = cont.length + 1;
	if (document.getElementsByName(jns) !== null) {
		var ele = document.getElementsByName(jns);
		var elementsCount = ele.length;
		for (var x = 0; x < ele.length; x++) {
			ele[0].parentNode.removeChild(ele[0]);
		}
		var elek = document.getElementsByName(jns + jns);
		var elementsCount = elek.length;
		for (var x = 0; x < elek.length; x++) {
			elek[0].parentNode.removeChild(elek[0]);
		}
	}
	baru.innerHTML += "<label id=\"pajak####" + i + "\" name=\"" + jns + "\" style=\"display:none\"/>" + jns + "#####" + nilai + "</label>";
	baru.innerHTML += "<input id=\"pajakx####" + i + "\" name=\"" + jns + jns + "\" readonly title="+jns+"  onclick=\"delpjkdantermin(this.id,'pajak')\" class='myinputtextnumber' value=\"" + jns + " => " + nilai + " %\" style=\"display: inline-block;width:47%;cursor:pointer\"/>";
	document.getElementById('jenispajak').value = '';
	document.getElementById('nilaipajak').value = '';
	document.getElementById('nilaipajak').disabled = true;
}
function enablevalue(val, id) {
	if (id == 'jenispajak' && val != '') {
		document.getElementById('nilaipajak').disabled = false;
		document.getElementById('nilaipajak').value = '';
	} else if (id == 'jenispajak' && val == '') {
		document.getElementById('nilaipajak').disabled = true;
		document.getElementById('nilaipajak').value = '';
	}
	if(id == 'rptermin' && val != '') {
		rp1 = parseFloat(remove_comma_var(document.getElementById('rupiah_1').value));
		rp2 = parseFloat(remove_comma_var(document.getElementById('rupiah_2').value));
		rp3 = parseFloat(remove_comma_var(document.getElementById('rupiah_3').value));
		if (isNaN(rp1) == true) {rp1 = 0;}
		if (isNaN(rp2) == true) {rp2 = 0;}
		if (isNaN(rp3) == true) {rp3 = 0;}
		ttlrp = rp1+rp2+rp3;
		rptermin = document.getElementById('rptermin').value;
		nilai = (rptermin/ttlrp*100);
		document.getElementById('persentermin').value=nilai;
		document.getElementById('kettermin').disabled = false;
		document.getElementById('kettermin').value = 'Ket : ' + nilai + '% dari nilai pekerjaan setelah ' + nilai + '% pekerjaan selesai';
	}else if (id == 'persentermin' && val != '') {
		nilai = document.getElementById('persentermin').value;
		rp1 = parseFloat(remove_comma_var(document.getElementById('rupiah_1').value));
		rp2 = parseFloat(remove_comma_var(document.getElementById('rupiah_2').value));
		rp3 = parseFloat(remove_comma_var(document.getElementById('rupiah_3').value));
		if (isNaN(rp1) == true) {rp1 = 0;}
		if (isNaN(rp2) == true) {rp2 = 0;}
		if (isNaN(rp3) == true) {rp3 = 0;}
		ttlrp = rp1+rp2+rp3;
		rpt = ((nilai/100)*ttlrp);
		document.getElementById('rptermin').value=rpt;
		document.getElementById('kettermin').disabled = false;
		document.getElementById('kettermin').value = 'Ket : ' + nilai + '% dari nilai pekerjaan setelah ' + nilai + '% pekerjaan selesai';
	} else if (id == 'persentermin' && val == '') {
		document.getElementById('kettermin').disabled = true;
		document.getElementById('kettermin').value = '';
	}
}
function delrp(id) {
	//document.getElementById(id).style.display = 'none';
	//document.getElementById(id).value = '';
	//document.getElementById('ket' + id).value = '';
}
function setrupiah(ket) {
	nilai = document.getElementById('rupiah').value;
	if (nilai == '' || ket == '') {
		alert('Nilai dan Keterangan wajib diisi !');
		return;
	}
	document.getElementById('rupiah').value = '';
	document.getElementById('ketnilai').value = '';
	if (document.getElementById('rupiah_1').value == '') {
		document.getElementById('rupiah_1').value = nilai;
		document.getElementById('ketrupiah_1').value = ket;
		document.getElementById('rupiah_1').style.display = 'block';
		document.getElementById('rupiah_1').style.display = 'inline-block';
		document.getElementById('rupiah_1').title = ket;
	} else if (document.getElementById('rupiah_2').value == '') {
		document.getElementById('rupiah_2').value = nilai;
		document.getElementById('ketrupiah_2').value = ket;
		document.getElementById('rupiah_2').style.display = 'block';
		document.getElementById('rupiah_2').style.display = 'inline-block';
		document.getElementById('rupiah_2').title = ket;
	} else if (document.getElementById('rupiah_3').value == '') {
		document.getElementById('rupiah_3').value = nilai;
		document.getElementById('ketrupiah_3').value = ket;
		document.getElementById('rupiah_3').style.display = 'block';
		document.getElementById('rupiah_3').style.display = 'inline-block';
		document.getElementById('rupiah_3').title = ket;
	} else {
		alert('Nilai Maksimal 3 !');
		return;
	}
}
 */
function jumlahhari() {
	tanggaldari = document.getElementById('tanggaldari').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	param = 'tanggaldari=' + tanggaldari + '&tanggalsampai=' + tanggalsampai + '&method=jumlahhari';
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jangkawaktu').value = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getnotransaksi() {
	kategori = document.getElementById('kategori').options[document.getElementById('kategori').selectedIndex].value;
	jenis = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	tanggalsurat = document.getElementById('tanggalsurat').value;
	document.getElementById('notransaksi').value = '';
	param = 'tanggalsurat=' + tanggalsurat + '&method=getnotransaksi';
	param += '&unit=' + unit + '&pt=' + pt;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(kategori=='' || jenis=='' || pt=='' || unit=='' || tanggalsurat=='')
					{
						document.getElementById('notransaksi').value = '';
					}
					else
					{
						document.getElementById('notransaksi').value = trim(con.responseText);
					}
					getsubunit();
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
function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cleardetail();
	setTimeout(() => {
		getnopoltipeangkut();
	}, 500);
}
function viewexcel(notransaksi, tipe) {
	ev = 'event';
	param = 'method=html' + '&tipe=' + tipe + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
function html(notransaksi, tipe) {
	// width = '';
	// height = '';
	// content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "View";
	// showDialog5(title, content, width, height, ev);
	param = 'method=html' +'&tipe=' + tipe + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	if(tipe == 'pdf'){
		alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}else{
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	setValue2('divsch','');
	setValue2('unitsch','');
	setValue2('jenissch','');
	setValue2('nohaksch','');
	setValue2('projectsch','');
	setValue2('koderekanansch','');
	document.getElementById('statussch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	loaddata();
	setTimeout(() => {
		getnopoltipeangkut();
	}, 400);
	
}
function getunit() {
	pt = document.getElementById('pt').value;
	kategori = document.getElementById('kategori').value;
	jenis = document.getElementById('jenis').value;
	if (jenis == 'PROJECT') {
		document.getElementById('labeldivisi').innerHTML = "Sub Tipe Asset &nbsp;<font size=2px style='color:red;vertical-align:middle'><b>*</b></font>";
	} else if (jenis == 'PR') {
		document.getElementById('labeldivisi').innerHTML = "No PR / SR &nbsp;<font size=2px style='color:red;vertical-align:middle'><b>*</b></font>";
	} else if (jenis == 'PABRIK') {
		document.getElementById('labeldivisi').innerHTML = "Station &nbsp;<font size=2px style='color:red;vertical-align:middle'><b>*</b></font>";
	} else {
		document.getElementById('labeldivisi').innerHTML = "Divisi &nbsp;<font size=2px style='color:red;vertical-align:middle'><b>*</b></font>";
	}
	param = 'pt=' + pt + '&method=getunit';
	param += '&kategori=' + kategori + '&jenis=' + jenis;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unit').innerHTML = trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdivisi() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	jenis = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	param = 'pt=' + pt + '&method=getdivisi';
	param += '&unit=' + unit + '&jenis=' + jenis;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
					getnotransaksi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getsubunit() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	divisi = document.getElementById('divisi').value;
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	jenis = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	notrans=document.getElementById('notransaksi').value;
	param = 'pt=' + pt + '&method=getsubunit';
	param += '&divisi=' + divisi + '&jenis=' + jenis+ '&unit=' + unit+'&notransaksi='+notrans;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {					
					opt = con.responseText.split('####');
					document.getElementById('subunit').innerHTML = trim(opt[0]);
					document.getElementById('kegiatan').innerHTML = trim(opt[1]);

					if (jenis == 'PROJECT') {
						if(trim(opt[7]) == 'x' || trim(opt[7]) == ''){
							if(opt[2].trim() != ''){
								document.getElementById('project').value = trim(opt[2]);
							}
							document.getElementById('project').disabled = true;
							// document.getElementById('tanggaldari').value = trim(opt[3]);
							// document.getElementById('tanggaldari').disabled = true;
							// document.getElementById('tanggalsampai').value = trim(opt[4]);
							// document.getElementById('tanggalsampai').disabled = true;
							document.getElementById('imgkoderekanan').style.display = '';
							document.getElementById('detail').style.display = 'block';
							jumlahhari();
						}else{
							//document.getElementById('rupiah_1').style.display = 'none';
							setValue2('rupiah_1','');
							setValue2('ketrupiah_1','');
							document.getElementById('rupiah_2').style.display = 'none';
							setValue2('rupiah_2','');
							setValue2('ketrupiah_2','');
							document.getElementById('rupiah_2').style.display = 'none';
							setValue2('rupiah_2','');
							setValue2('ketrupiah_2','');
							document.getElementById('project').value = trim(opt[2]);
							document.getElementById('project').disabled = true;
							// document.getElementById('tanggaldari').value = trim(opt[3]);
							// document.getElementById('tanggaldari').disabled = true;
							// document.getElementById('tanggalsampai').value = trim(opt[4]);
							// document.getElementById('tanggalsampai').disabled = true;
							jumlahhari();
							setValue2('rupiah',trim(opt[5]));
							document.getElementById('rupiah').disabled = true;
							document.getElementById('ketnilai').disabled = true;
							setrupiah("Total Biaya");
							document.getElementById('rupiah_1').disabled = true;
							setValue2('koderekanan',trim(opt[6]));
							document.getElementById('koderekanan').disabled = true;
							document.getElementById('imgkoderekanan').style.display = 'none';
							document.getElementById('detail').style.display = 'none';
						}
					} else {
						document.getElementById('detail').style.display = '';
						document.getElementById('project').disabled = false;
						getJenisSup();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getsatuan(e) {
	pt      = document.getElementById('pt').value;
	divisi  = document.getElementById('divisi').value;
	jenis   = document.getElementById('jenis').value;
	kegiatan= document.getElementById('kegiatan').value;
	subunit = document.getElementById('subunit').value;
	param = 'pt=' + pt + '&method=getsatuan';
	param += '&divisi=' + divisi + '&jenis=' + jenis + '&kegiatan=' + kegiatan;
	param += '&subunit=' + subunit;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('satuan').innerHTML = trim(data[0]);
					if(jenis=='SEWA.HM'){
						document.getElementById('kegiatan').innerHTML = trim(data[1]);						
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function fillfield(notransaksi) {
	param = 'notransaksi=' + notransaksi + '&method=fillfield';
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					hsl = JSON.parse(con.responseText);
					document.getElementById('listData').style.display = 'none';
					document.getElementById('header').style.display = 'block';
					setValue2('kategori',hsl.kategori);
					document.getElementById('kategori').disabled=true;
					setValue2('jenis',hsl.jenis);
					document.getElementById('jenis').disabled=true;
					setValue2('tanggalsurat',hsl.tanggal);
					// document.getElementById('tanggalsurat').disabled=true;
					setValue2('pt',hsl.pt);
					document.getElementById('pt').disabled=true;
					setValue2('unit',hsl.unit);
					document.getElementById('unit').disabled=true;
					setValue2('divisi',hsl.divisi);
					document.getElementById('divisi').disabled=true;
					setValue2('bagian',hsl.bagian);
					setValue2('project',hsl.project);
					setValue2('koderekanan',hsl.koderekanan);
					setValue2('perjanjianinduk',hsl.perjanjianinduk);
					setValue2('perjanjianperubahan',hsl.perjanjianperubahan);
					setValue2('retensi',hsl.retensi);
					setValue2('denda',hsl.denda);
					setValue2('tanggaldari',hsl.tanggaldari);
					setValue2('tanggalsampai',hsl.tanggalsampai);
					setValue2('jangkawaktu',hsl.jangkawaktu);
					setValue2('garansi',hsl.garansi);
					setValue2('spesifikasi',hsl.spesifikasi);
					setValue2('jnsSupplierId',hsl.jenissupplier);
					if(hsl.pendukung =='1'){
						document.getElementById('pendukung').checked=true;
					}else{
						document.getElementById('pendukung').checked=false;
					}
					// document.getElementById('rupiah_1').value=data[21];
					// Get Rupiah tambahkan echo baru di slave
					document.getElementById('rupiah_1').value=hsl.totalrupiah;
					document.getElementById('contpajak').innerHTML=hsl.pajak;
					document.getElementById('contnopol').innerHTML=hsl.nopol;
						
					if(hsl.jenis=='PROJECT'){
						// document.getElementById('tabledetail').style.display = 'none';
						// document.getElementById('detail').style.display = 'none';
					// }else if(){
						// document.getElementById('jlhhm').value = 
					}else if (hsl.jenis=='ANGKUTTBS'){
						document.getElementById('tabledetail').style.display = 'none';
						document.getElementById('detail').style.display = 'none';
					}else{
						document.getElementById('tabledetail').style.display = '';
						document.getElementById('detail').style.display = '';
					}
					loadfiles(notransaksi);								
					setTimeout(function(){
						getrekanan(notransaksi,hsl.koderekanan,hsl.project, hsl.jenis)
						setTimeout(function(){
							setValue2('notransaksiold',hsl.notransaksiold);
							setValue2('jenissuplier',hsl.jenissuplier);
						}, 900);
					}, 200);
					
					document.getElementById('notransaksi').value = hsl.notransaksi;
					document.getElementById('jlhhm').value = hsl.jlhhm;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



// function edit(notransaksi,kategori,jenis,tanggal,pt,unit,divisi,bagian,project,koderekanan,perjanjianinduk,perjanjianperubahan,retensi,denda,tanggaldari,tanggalsampai,jangkawaktu,garansi,spesifikasi,jnsSupplier,pendukung,nopol,tipeangkut) {
	// getnopoltipeangkut();
	// document.getElementById('listData').style.display = 'none';
	// document.getElementById('header').style.display = 'block';
	// document.getElementById('notransaksi').value=notransaksi;
	// document.getElementById('kategori').value=kategori;
	// document.getElementById('kategori').disabled=true;
	// document.getElementById('jenis').value=jenis;
	// document.getElementById('jenis').disabled=true;
	// document.getElementById('tanggalsurat').value=tanggal;
	// document.getElementById('tanggalsurat').disabled=true;
	// document.getElementById('pt').value=pt;
	// document.getElementById('pt').disabled=true;
	// document.getElementById('unit').value=unit;
	// document.getElementById('unit').disabled=true;
	// document.getElementById('divisi').value=divisi;
	// document.getElementById('divisi').disabled=true;
	// document.getElementById('bagian').value=bagian;
	// document.getElementById('project').value=project;
	// document.getElementById('koderekanan').value=koderekanan;
	// document.getElementById('perjanjianinduk').value=perjanjianinduk;
	// document.getElementById('perjanjianperubahan').value=perjanjianperubahan;
	// document.getElementById('retensi').value=retensi;
	// document.getElementById('denda').value=denda;
	// document.getElementById('tanggaldari').value=tanggaldari;
	// document.getElementById('tanggalsampai').value=tanggalsampai;
	// document.getElementById('jangkawaktu').value=jangkawaktu;
	// document.getElementById('garansi').value=garansi;
	// document.getElementById('spesifikasi').value=spesifikasi;
	// document.getElementById('jnsSupplierId').value=jnsSupplier;
	// document.getElementById('nopol').value=nopol;
	// document.getElementById('tipeangkut').value=tipeangkut;
	// if(pendukung=='1'){
		// document.getElementById('pendukung').checked=true;
	// }else{
		// document.getElementById('pendukung').checked=false;
	// }
	// editdetail(notransaksi);
// }
// function editdetail(notransaksi) {
	// param = 'notransaksi=' + notransaksi;
	// param += '&method=editdetail';
	// tujuan = 'lgl_slave_pengajuanspk.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// hasil = con.responseText;
					// hasil = hasil.split("########");
					// rupiah = hasil[0].split("#$#");
					// if(rupiah!=''){
						// cont = rupiah.length - 1;
						// for (i = 0; i <= cont; i++) {
							// norp = rupiah[i].split("##");
							// document.getElementById('rupiah_' + (i + 1)).value = norp[2];
							// document.getElementById('ketrupiah_' + (i + 1)).value = norp[3];
							// document.getElementById('rupiah_' + (i + 1)).style.display = 'block';
							// document.getElementById('rupiah_' + (i + 1)).style.display = 'inline-block';
							// document.getElementById('rupiah_' + (i + 1)).title = norp[3];
						// }
					// }
					// pajak = hasil[1].split("#$$#");
					// if(pajak!=''){
						// cont = pajak.length - 1;
						// for (i = 0; i <= cont; i++) {
							// nopajak = pajak[i].split("####");
							// baru = document.getElementById('contpajak');
							// baru.innerHTML += "<label id=\"pajak####" + (i + 1) + "\" name=\"" + nopajak[1] + "\" style=\"display:none\"/>" + nopajak[1] + "#####" + nopajak[2] + "</label>";
							// baru.innerHTML += "<input id=\"pajakx####" + (i + 1) + "\" name=\"" + nopajak[1] + nopajak[1] + "\" readonly title="+nopajak[1]+"  onclick=\"delpjkdantermin(this.id,'pajak')\" class='myinputtextnumber' value=\"" + nopajak[1] + " => " + nopajak[2] + " %\" style=\"display: inline-block;width:47%;cursor:pointer\"/>";
						// }
					// }
					// termin = hasil[2].split("#$$$#");
					// if(termin!=''){
						// cont = termin.length - 1;
						// for (i = 0; i <= cont; i++) {
							// notermin = termin[i].split("######");
							// baru = document.getElementById('conttermin');
							// baru.innerHTML += "<label id=\"kettermindt####" + (i + 1) + "\" style=\"display:none\"/>" + notermin[1] + "#####" + notermin[2] + "#####" + notermin[3] + "</label>";
							// baru.innerHTML += "<input id=\"termindt####" + (i + 1) + "\" readonly title=\""+ notermin[3] +"\" onclick=\"delpjkdantermin(this.id,'termin')\" class='myinputtext' value=\"Ke: " + notermin[1] + " = " + notermin[2] + "%, Rp = "+notermin[4]+"\" style=\"display: inline-block;width:90%;cursor:pointer\"/>";
							// document.getElementById('terminke').value = (i + 2);
						// }
					// }
					// loadfiles(notransaksi);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }
function cleardetail() {
	document.getElementById('conttermin').innerHTML='';
	document.getElementById('contpajak').innerHTML='';
	document.getElementById('terminke').value = 1;
	document.getElementById('rupiah_1').value = '';
	document.getElementById('contnopol').innerHTML='';
	document.getElementById('notransaksi').value='';
	setValue2('kategori',null);
	document.getElementById('kategori').disabled=false;
	setValue2('jenis',null);
	document.getElementById('jenis').disabled=false;
	document.getElementById('tanggalsurat').value='';
	document.getElementById('tanggalsurat').disabled=false;
	setValue2('pt',null);
	document.getElementById('pt').disabled=false;
	setValue2('unit',null);
	document.getElementById('unit').disabled=false;
	setValue2('divisi',null);
	document.getElementById('divisi').disabled=false;
	document.getElementById('bagian').value='';
	document.getElementById('project').value='';
	document.getElementById('project').disabled=false;
	document.getElementById('koderekanan').value='';
	document.getElementById('koderekanan').disabled=false;
	document.getElementById('perjanjianinduk').value='';
	document.getElementById('perjanjianperubahan').value='';
	document.getElementById('retensi').value='';
	document.getElementById('denda').value='';
	document.getElementById('tanggaldari').value='';
	document.getElementById('tanggaldari').disabled=false;
	document.getElementById('tanggalsampai').value='';
	document.getElementById('tanggalsampai').disabled=false;
	document.getElementById('jangkawaktu').value='';
	document.getElementById('garansi').value='';
	document.getElementById('spesifikasi').value='';
	document.getElementById('jnsSupplierId').value='';
	document.getElementById('notransaksiold').innerHTML='';
	document.getElementById('jlhhm').value='';
	document.getElementById('pendukung').checked = false;
	document.getElementById('tabledetail').style.display = '';
	document.getElementById('jenissupplier').innerHTML="<option value=''></option>";
	loaddatadetail();
}
function del(notransaksi) {
	param = 'method=delete';
	param += "&notransaksi=" + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
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
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savedetail() {
	notransaksi = document.getElementById('notransaksi').value;
	subunit = document.getElementById('subunit').value;
	kegiatan = document.getElementById('kegiatan').value;
	satuan = document.getElementById('satuan').value;
	volume = document.getElementById('volume').value;
	total = document.getElementById('total').value;
	hk = document.getElementById('hk').value;
	method = document.getElementById('methoddetail').value;
	if (subunit == '' || kegiatan == '' || notransaksi == ''|| satuan == ''|| volume == ''||total=='') {
		alert('Lengkapi Pengisian.');
	return;
	} 
	param = '&notransaksi=' + notransaksi;
	param += '&subunit=' + subunit;
	param += '&kegiatan=' + kegiatan;
	param += '&satuan=' + satuan;
	param += '&volume=' + volume;
	param += '&total=' + total;
	param += '&hk=' + hk;
	param += '&method=' + method;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rupiah_1').value = con.responseText;
					loaddatadetail();
					cleardetaildt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// function delterdanpjk(){
	// i = document.getElementById('conttermin');
	// var r = i.getElementsByTagName('INPUT');
	// n = r.length;
	// if (n != '') {
		// for (j = 1; j <= n; j++) {
			// document.getElementById('kettermindt####' + j).remove();
			// document.getElementById('termindt####' + j).remove();	
		// }
	// }	
	
	// n = 0;
	// q = document.getElementById('contpajak');
	// var w = q.getElementsByTagName('INPUT');
	// n = w.length;
	// if (n != '') {
		// for (j = 1; j <= n; j++) {
			// document.getElementById('pajak####' + j).remove();
			// document.getElementById('pajakx####' + j).remove();
		// }
	// }	
	
	
	
	// editdetail(notransaksi);
// }

function loaddatadetail() {
	notransaksi = document.getElementById('notransaksi').value;
	param = 'method=loaddatadetail&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
					getsubunit();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deldetail(notransaksi,subunit,kegiatan) {
	param = '&notransaksi=' + notransaksi;
	param += '&subunit=' + subunit;
	param += '&kegiatan=' + kegiatan;
	param += '&method=deldetail';
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('rupiah_1').value = con.responseText;
					loaddatadetail();
					delterdanpjk();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getrupiah(){
	rpsat = document.getElementById('rppersat').value;
	volume = document.getElementById('volume').value;
	rpsat = remove_comma_var(rpsat);
	volume = remove_comma_var(volume);
	
	total = parseFloat(rpsat)*parseFloat(volume);
	document.getElementById('total').value=numberFormat(total,0);
	
}
function save() {
	notransaksi        = document.getElementById('notransaksi').value;
	kategori           = document.getElementById('kategori').value;
	jenis              = document.getElementById('jenis').value;
	tanggal            = document.getElementById('tanggalsurat').value;
	pt                 = document.getElementById('pt').value;
	unit               = document.getElementById('unit').value;
	divisi             = document.getElementById('divisi').value;
	bagian             = document.getElementById('bagian').value;
	project            = document.getElementById('project').value;
	koderekanan        = document.getElementById('koderekanan').value;
	perjanjianinduk    = document.getElementById('perjanjianinduk').value;
	perjanjianperubahan= document.getElementById('perjanjianperubahan').value;
	retensi            = document.getElementById('retensi').value;
	denda              = document.getElementById('denda').value;
	tanggaldari        = document.getElementById('tanggaldari').value;
	tanggalsampai      = document.getElementById('tanggalsampai').value;
	jangkawaktu        = document.getElementById('jangkawaktu').value;
	garansi            = document.getElementById('garansi').value;
	spesifikasi        = document.getElementById('spesifikasi').value;
	jenissupplier      = document.getElementById('jenissupplier').value;
	method             = document.getElementById('method').value;
	rupiahttl          =document.getElementById('rupiah_1').value;
	
	pendukung = document.getElementById('pendukung');   
	if(pendukung.checked==true){
		pendukung=1;
	}else{
		pendukung=0;
	}
	
	if (pt == '' || unit == '' || tanggal == '') {
		alertify.alert('Informasi','Kode PT, Kode Unit dan Tanggal wajib diisi.');return
	}

	if(project == ''){
		alertify.alert('Informasi','Project wajib diisi.');return
	}

	if(koderekanan == ''){
		alertify.alert('Informasi','Kode Rekanan wajib diisi.');return
	}
	
	param = 'pt=' + pt;
	param += '&notransaksi=' + notransaksi;
	param += '&kategori=' + kategori;
	param += '&jenis=' + jenis;
	param += '&tanggal=' + tanggal;
	param += '&unit=' + unit;
	param += '&divisi=' + divisi;
	param += '&bagian=' + bagian;
	param += '&project=' + project;
	param += '&koderekanan=' + koderekanan;
	param += '&perjanjianinduk=' + perjanjianinduk;
	param += '&perjanjianperubahan=' + perjanjianperubahan;
	param += '&retensi=' + retensi;
	param += '&denda=' + denda;
	param += '&tanggaldari=' + tanggaldari;
	param += '&tanggalsampai=' + tanggalsampai;
	param += '&jangkawaktu=' + jangkawaktu;
	param += '&garansi=' + garansi;
	param += '&spesifikasi=' + spesifikasi;
	param += '&jenissupplier=' + jenissupplier;
	param += '&pendukung=' + pendukung;
	param += '&rupiahttl=' + rupiahttl;
	param += '&method=' + method;param += '&notransaksiold=' + getValue('notransaksiold');if(jenis =='SEWA.HM'){param += '&jlhhm=' + remove_comma_var(getValue('jlhhm'));}
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					
					if(con.responseText == undefined || con.responseText == "0") {
						document.getElementById("rupiah_1").value = 0;
					}

					document.getElementById('pt').disabled = true;
					document.getElementById('unit').disabled = true;
					document.getElementById('notransaksi').disabled = true;
					document.getElementById('divisi').disabled = true;
					if(jenis=='PROJECT'){
						document.getElementById('detail').style.display = 'none';
					}else if (jenis=='ANGKUTTBS'){
						document.getElementById('detail').style.display = 'none';
					}else{
						document.getElementById('detail').style.display = '';
					}
					loaddatadetail();
					alert("Data sudah disimpan");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


// max = 0
// function saveAll(jenis) {
	// if (jenis == 'rupiah') {
		// maxRow = 1;
		// max = maxRow;
		// loopsave(1, maxRow, jenis);
	// } else 
	// if (jenis == 'pajak') {
		// var baru = document.getElementById('contpajak');
		// var cont = baru.getElementsByTagName('INPUT');
		// var i = cont.length;
		// maxRow = i;
		// max = maxRow;
		// loopsave(1, maxRow, jenis);
	// } else if (jenis == 'termin') {
		// var baru = document.getElementById('conttermin');
		// var cont = baru.getElementsByTagName('INPUT');
		// var i = cont.length;
		// maxRow = i;
		// max = maxRow;
		// loopsave(1, maxRow, jenis);
	// }
// }
// function loopsave(currRow, maxRow, jenis) {
	// notransaksi = document.getElementById('notransaksi').value;
	// jenisx = document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	// param = 'notransaksi=' + notransaksi;
	// if (jenis == 'rupiah') {
		// rupiah = document.getElementById('rupiah_' + currRow).value;
		// ketrupiah = document.getElementById('ketrupiah_' + currRow).value;
		// param += '&rupiah=' + rupiah;
		// param += '&keterangan=' + ketrupiah;
		// param += '&nourut=' + currRow;
		// param += "&jenis=" + jenis;
	// }
	// if (jenis == 'pajak') {
		// if(document.getElementById('pajak####' + currRow)!=null){
			// rp = document.getElementById('pajak####' + currRow).innerHTML;
			// rp = rp.split('#####');
			// noakun = rp[0];
			// rupiah = rp[1];
			// param += '&rupiah=' + rupiah;
			// param += '&nourut=' + noakun;
			// param += "&jenis=" + jenis;
		// }
	// }
	// if (jenis == 'termin') {
		// if(document.getElementById('kettermindt####' + currRow)!=null){
			// rp = document.getElementById('kettermindt####' + currRow).innerHTML;
			// rp = rp.split('#####');
			// nourut = rp[0];
			// rupiah = rp[1];
			// keterangan = rp[2];
			// rptermin = rp[3];
			// param += '&rupiah=' + rupiah;
			// param += '&nourut=' + nourut;
			// param += '&keterangan=' + keterangan;
			// param += '&rptermin=' + rptermin;
			// param += "&jenis=" + jenis;
		// }
	// }
	// param += "&row=" + currRow;
	// param += "&method=insertdt";
	// tujuan = 'lgl_slave_pengajuanspk.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText + '\nJenis : ' + jenis);
					// unlockScreen();
				// } else {
					// currRow += 1;
					// if (currRow > maxRow) {
						// if (jenis == 'rupiah') {
							// saveAll(jenis = 'pajak');
						// } else if (jenis == 'pajak') {
							// saveAll(jenis = 'termin');
						// } else{
							// document.getElementById('pt').disabled = true;
							// document.getElementById('unit').disabled = true;
							// document.getElementById('notransaksi').disabled = true;
							// document.getElementById('divisi').disabled = true;
							// if(jenisx=='PROJECT'){
								// document.getElementById('tabledetail').style.display = 'none';
							// }else if (jenisx=='ANGKUTTBS'){
								// document.getElementById('detail').style.display = 'none';
							// }else{
								// document.getElementById('tabledetail').style.display = '';
							// }
							// loaddatadetail();
							// alert("Data sudah disimpan");
						// }
					// } else {
						// loopsave(currRow, maxRow, jenis);
					// }
						// //document.getElementById('detail').style.display = 'block';
						// //loaddatadetail();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(paged) {
	divsch = document.getElementById('divsch').value;
	jenissch = document.getElementById('jenissch').value;
	unitsch = document.getElementById('unitsch').value;
	nohaksch = document.getElementById('nohaksch').value;
	projectsch = document.getElementById('projectsch').value;
	koderekanansch = document.getElementById('koderekanansch').value;
	statussch = document.getElementById('statussch').value;
	param = 'method=loaddata&page=' + paged;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (jenissch != '') {
		param += '&jenissch=' + jenissch;
	}
	if (unitsch != '') {
		param += '&unitsch=' + unitsch;
	}
	if (nohaksch != '') {
		param += '&nohaksch=' + nohaksch;
	}
	if (projectsch != '') {
		param += '&projectsch=' + projectsch;
	}
	if (koderekanansch != '') {
		param += '&koderekanansch=' + koderekanansch;
	}
	if (statussch != '') {
		param += '&statussch=' + statussch;
	}
	tujuan = 'lgl_slave_pengajuanspk.php';
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
					// cleardetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'method=viewfile&namafile=' + namafile;
		tujuan = 'lgl_slave_pengajuanspk.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}
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

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev,notrans,jenisupload){
	var notransaksi = document.getElementById('notransaksi').value;

	//Untuk upload SPK Final, tombol pada list data
	if (jenisupload=='1') {
		notransaksi=notrans;
	}

	if (notransaksi == "") {
		alert("warning : Silahkan isikan detail pengajuan terlebih dahulu !");
		return false;
	}
	showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan='lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi,jenisupload);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile(notrans,jenisupload) {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').value;
	var formdata = new FormData();

	if (jenisupload=='1') {
		notransaksi=notrans;
	}

	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("jenisupload", jenisupload);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}

	if (notransaksi == "") {
		alert("warning : Silahkan isikan detail pengajuan terlebih dahulu !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "lgl_slave_pengajuanspk.php?method=submitfile", true);
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
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function viewlistfile(notransaksi) {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog4(title, content, width, height, ev);
	param = 'method=viewlistfile&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contviewz').innerHTML = con.responseText;
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(notransaksi,jenisupload) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan = 'lgl_slave_pengajuanspk.php';
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
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(notransaksi, namafile,jenisupload) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,jenisupload);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_ajukan_spk(notransaksi, unit, numrow, rupiah) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow + '&rupiahttl=' + rupiah;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan() {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdetail2(notransaksi,subunit,kegiatan,satuan,volume,total,hk){
	setValue2('subunit',subunit);
	document.getElementById('subunit').disabled=true;
	document.getElementById('kegiatan').disabled=true;
	getsatuan();
	setTimeout(() => {
		setValue2('kegiatan',kegiatan);
	}, 400);
	document.getElementById('satuan').value=satuan;
	document.getElementById('satuan').disabled=true;
	document.getElementById('volume').value=volume;
	document.getElementById('volume').disabled=true;
	document.getElementById('total').value=numberFormat(total,0);
	document.getElementById('hk').value=hk;
	if(total > 0){
		document.getElementById('rppersat').value=numberFormat((parseFloat(total)/parseFloat(volume)),0);
	}else{
		document.getElementById('rppersat').value="0.00";
	}
	document.getElementById('methoddetail').value='editdetail2';
}

function cleardetaildt(){
	
	document.getElementById('subunit').disabled=false;
	//document.getElementById('kegiatan').value='';
	document.getElementById('kegiatan').disabled=false;
	document.getElementById('satuan').value='';
	document.getElementById('satuan').disabled=false;
	document.getElementById('volume').value='';
	document.getElementById('volume').disabled=false;
	document.getElementById('rppersat').value='';
	document.getElementById('total').value='';
	document.getElementById('methoddetail').value='insertdetail';
}
function getJenisSup(){
	//alert("masuk");
	suppid = document.getElementById('koderekanan');
	suppid=suppid.options[suppid.selectedIndex].value;	
	jnsid2=document.getElementById('jnsSupplierId').value;
	notransaksi=document.getElementById('notransaksi').value;
	jenis=document.getElementById('jenis').value;
	param = 'method=getJenisSup' + '&koderekanan=' + suppid+'&notransaksi='+notransaksi +'&jenis='+jenis ;	
	 
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					dt = con.responseText.split('##');
					document.getElementById('notransaksiold').innerHTML = dt[1];
					document.getElementById('jenissupplier').innerHTML=dt[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_tutup(notransaksi, unit, numrow) {
	width = '300';
	height = '';
	content = "<fieldset><legend>Alasan Close</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "";
	showDialog1(title, content, width, height, ev);
	param = 'method=form_tutup' + '&notransaksi=' + notransaksi + '&unit=' + unit + '&numrow=' + numrow;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraju').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tutup() {
	notransaksi=document.getElementById('notran_tutup').value;
	unit=document.getElementById('unit_tutup').value;
	numrow=document.getElementById('numrow_tutup').value;
	alasanclose=document.getElementById('alasanclose').value;
	
	
	param = 'method=tutup';
	param += '&notransaksi=' + notransaksi;
	param += '&unit=' + unit;
	param += '&alasanclose=' + alasanclose;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnopoltipeangkut(){
	jenis = document.getElementById('jenis').value;
	if(jenis != 'ANGKUTTBS'){
		document.getElementById('nopol').disabled = true;
		document.getElementById('supir').disabled = true;
		document.getElementById('tmblnopol').disabled = true;
		document.getElementById('rupiah_1').disabled = true;
		document.getElementById('nopol').value = '';
		document.getElementById('supir').value = '';
		document.getElementById('contnopol').innerHTML = '';
	}else{
		document.getElementById('supir').disabled = false;
		document.getElementById('nopol').disabled = false;
		document.getElementById('tmblnopol').disabled = false;
		document.getElementById('rupiah_1').disabled = true;
	}
	
	if(jenis == 'PROJECT'){
		document.getElementById('lblperbandingan').style.display = ''; 
		document.getElementById('lbljumlahhm').style.display = 'none';
		document.getElementById('koderekanan').disabled = true;
	}else if(jenis == 'SEWA.HM'){
		document.getElementById('lbljumlahhm').style.display = '';
		document.getElementById('koderekanan').disabled = false;
		document.getElementById('lblperbandingan').style.display = 'none'; 
	}else{
		setValue2('koderekanan','');
		document.getElementById('koderekanan').disabled = false;
		document.getElementById('lblperbandingan').style.display = 'none'; 
		document.getElementById('lbljumlahhm').style.display = 'none';
	}
}




function viewdetailbapp(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe){
	param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin;
    width = '';
    height = '';
    content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    title = "View";
    showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contviewx').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function showhidedetail(rows,total){
	for(var i = 1; i <= total; i++) {
		key = document.getElementById('tr_dt2_'+rows+'_'+i).style.display;
		if(key=='none'){
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='';
		}else{
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='none';
		}
    }
}

function viewdetail(notransaksi,kodeorg,tipeview,ev){
	width = '';
	height = '';
	content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	title = "";
	showDialog1(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contRekap').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getapprovaldetail(nopengajuan,kodeorg,ev) {
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Detail Approval</legend><div id=contapp style=\"overflow:auto;width:100%;\"></div></fieldset>";
	title = "";
	showDialog4(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic4').style.top = pos[1] + 'px';
	// document.getElementById('dynamic4').style.left = (pos[0]-width) + 'px';
	document.getElementById('dynamic4').style.display = '';
	param = 'method=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contapp').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function carinoperbandingan(title, content, ev) {
	jenis=document.getElementById('jenis').value;
	tanggalsurat=document.getElementById('tanggalsurat').value;
	if(jenis == 'PROJECT'){
		param = 'method=carinoperbandingan&jenis=' + jenis + '&tanggalsurat='+tanggalsurat;
		tujuan = 'lgl_slave_pengajuanspk.php';
		post_response_text(tujuan + '?' + '', param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi",con.responseText);
					} else {
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','40%'); 
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}else{
		alertify.alert('Informasi','Jenis bukan project');
	}
}

function getrekanan(notransaksi,supp, namaproject, tipe){
	document.getElementById('noperbandingan').value = notransaksi
	document.getElementById('project').value = namaproject;
	if(tipe == undefined){
		setValue2('koderekanan',supp);
	}
	
	alertify.popup().destroy();
}