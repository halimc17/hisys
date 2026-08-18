function getsupplier() {

	param = "";
	unit = document.getElementById('unit').value;
	method = 'getsupplier';

	if (unit == '') {
		alert('Unit tidak boleh kosong');
		document.getElementById('unit').value = '';
		return false;
	}

	param += 'unit=' + unit + '&method=' + method;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					isdt = con.responseText.split("####");
					document.getElementById('divisi').innerHTML = isdt[0];
					document.getElementById('unitinv').innerHTML = isdt[1];

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showupload(notransaksi, ev) {
	param = 'method=showupload';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup("Upload Files", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('55%', '75%');
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savefile(notransaksi) {
	var fileup = document.getElementById('fileupload').files[0];
	var formdata = new FormData();
	formdata.append("fileup", fileup);
	formdata.append("notransaksi", notransaksi);
	formdata.append("fileupload", getValue('fileupload'));
	var con = createXMLHttpRequest();
	con.open("POST", "kebun_tbsexternal_slave.php?method=savefile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
					document.getElementById('fileupload').value = '';
				} else {

					//=== Success Response
					// alert('Uploaded');
					document.getElementById('fileupload').value = '';
					alertify.alert('Informasi', 'Uploaded');
					loadfiles(notransaksi);
					// loaddata();
					// valSplit = con.responseText.split("####");
					// document.getElementById('container').innerHTML = valSplit[0];
					//document.getElementById('container').innerHTML = con.responseText;
					// document.getElementById('id').value = valSplit[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(notransaksi) {
	param = 'method=loadfiles';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
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

function deletefile(notransaksi, namafile) {
	param = 'method=deletefile';
	param += '&notransaksi=' + notransaksi;
	param += '&namafile=' + namafile;
	tujuan = 'kebun_tbsexternal_slave.php';
	alertify.confirm("Informasi", "Anda yakin???",
		function () {
			post_response_text(tujuan, param, respog);
		},
		function () {
			return;
		}
	);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					document.getElementById('fileupload').value = '';
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewimage(file, format) {
	title = "Preview";
	alertify.popup2(title, "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + file + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '90%');
}

function saveht() {
	param = '';
	tanggal = document.getElementById('tanggal').value;
	unit = document.getElementById('unit').value;
	divisi = document.getElementById('divisi').value;
	tanggaltbs1 = document.getElementById('tanggaltbs1').value;
	tanggaltbs2 = document.getElementById('tanggaltbs2').value;

	if (tanggal == '') {
		alert('Tanggal tidak boleh kosong'); return;
	}
	if (unit == '') {
		alert('unit tidak boleh kosong'); return;
	}
	if (divisi == '') {
		alert('Assignment tidak boleh kosong'); return;
	}
	if (divisi == '') {
		alert('Unit Tagihan tidak boleh kosong'); return;
	}

	method = 'notransaksi';
	param += '&unit=' + unit + '&tanggal=' + tanggal;
	param += '&tanggaltbs1=' + tanggaltbs1 + '&tanggaltbs2=' + tanggaltbs2;
	param += '&divisi=' + divisi;
	param += '&method=' + method;
	// alert(param);
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('noafiliasi').value = ar[1];
					// document.getElementById('notransaksi').value=con.responseText;

					document.getElementById('saveht').disabled = true;
					document.getElementById('detail').style.display = 'block';

					document.getElementById('unit').disabled = true;
					document.getElementById('divisi').disabled = true;
					document.getElementById('tanggal').disabled = true;
					document.getElementById('tanggaltbs1').disabled = true;
					document.getElementById('tanggaltbs2').disabled = true;
					document.getElementById('keteranganht').disabled = true;




					document.getElementById('saveht').disabled = false;
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function hitungtotalrp(no, nomax) {
	kgnetto = document.getElementById('kgnetto' + no).innerHTML;
	rpkg = document.getElementById('rpkg' + no).value;
	kgnetto = remove_comma_var(kgnetto);
	rpkg = remove_comma_var(rpkg);
	totalrp = parseFloat(kgnetto) * parseFloat(rpkg);
	document.getElementById('totalrp' + no).value = numberFormat(totalrp, 2);
	hitunggrandtotal(nomax);
}



function hitunggrandtotal(nomax) {
	ttotalrp = 0;
	for (i = 1; i <= nomax; i++) {
		totalrp = document.getElementById('totalrp' + i).value;
		totalrp = remove_comma_var(totalrp);
		ttotalrp = parseFloat(ttotalrp) + parseFloat(totalrp);
	}
	// alert(ttotalrp);
	document.getElementById('ttotalrp').innerHTML = numberFormat(ttotalrp, 2);
}

//#= afiliasi
function hitungtotalrpafiliasi(no, nomax) {
	kgnetto = document.getElementById('kgnettoafiliasi' + no).innerHTML;
	rpkg = document.getElementById('rpkgafiliasi' + no).value;
	kgnetto = remove_comma_var(kgnetto);
	rpkg = remove_comma_var(rpkg);
	totalrp = parseFloat(kgnetto) * parseFloat(rpkg);
	document.getElementById('totalrpafiliasi' + no).value = numberFormat(totalrp, 2);
	hitunggrandtotalafiliasi(nomax);
}



function hitunggrandtotalafiliasi(nomax) {
	ttotalrp = 0;
	for (i = 1; i <= nomax; i++) {
		totalrp = document.getElementById('totalrpafiliasi' + i).value;
		totalrp = remove_comma_var(totalrp);
		ttotalrp = parseFloat(ttotalrp) + parseFloat(totalrp);
	}
	// alert(ttotalrp);
	document.getElementById('ttotalrpafiliasi').innerHTML = numberFormat(ttotalrp, 2);
}




function cancelht() {
	document.getElementById('unit').disabled = false;
	document.getElementById('divisi').disabled = false;
	document.getElementById('tanggal').disabled = false;
	document.getElementById('tanggaltbs1').disabled = false;
	document.getElementById('tanggaltbs2').disabled = false;
	document.getElementById('keteranganht').disabled = false;
	document.getElementById('saveht').disabled = false;
	document.getElementById('notransaksi').value = '';
	document.getElementById('noafiliasi').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('divisi').value = '';
	// document.getElementById('tanggal').value='';
	// document.getElementById('tanggaltbs1').value='';
	// document.getElementById('tanggaltbs2').value='';
	document.getElementById('keteranganht').value = '';
	document.getElementById('detail').style.display = 'none';
}

function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('method').value = 'update';
					// alert(con.responseText.split);
					ar = con.responseText.split("###");

					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('unit').value = ar[1];

					setTimeout(() => {
						getsupplier()

						setTimeout(() => {
							document.getElementById('divisi').value = ar[2];
							document.getElementById('tanggal').value = ar[3];
							document.getElementById('tanggaltbs1').value = ar[4];
							document.getElementById('tanggaltbs2').value = ar[5];
							document.getElementById('keteranganht').value = ar[6];
							document.getElementById('noafiliasi').value = ar[7];


							document.getElementById('notransaksi').disabled = true;
							document.getElementById('unit').disabled = true;
							document.getElementById('divisi').disabled = true;
							document.getElementById('tanggal').disabled = true;
							document.getElementById('tanggaltbs1').disabled = true;
							document.getElementById('tanggaltbs2').disabled = true;
							document.getElementById('keteranganht').disabled = true;
							document.getElementById('saveht').disabled = true;

							document.getElementById('listdata').style.display = 'none';
							document.getElementById('header').style.display = 'block';
							document.getElementById('detail').style.display = 'block';
							loaddatadt();
						}, 500);

					}, 200);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('notransaksisch').value = '';
	document.getElementById('tanggalmulaisch').value = '';
	document.getElementById('tanggalselesaisch').value = '';
	loaddata(0);
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}



function loaddata(num) {
	notransaksisch = document.getElementById('notransaksisch').value;
	tanggalmulaisch = document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch = document.getElementById('tanggalselesaisch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch;
	param += '&tanggalmulaisch=' + tanggalmulaisch + '&tanggalselesaisch=' + tanggalselesaisch;
	tujuan = 'kebun_tbsexternal_slave.php';
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


function newdata() {
	cancelht();
	document.getElementById('header').style.display = 'block';
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	// document.getElementById('detailhead').style.display='none';
}


function postingData(notransaksi, kdunit, page) {
	content = "<div id=formpost style=\"height:100%;width:100%;\"></div>";
	title = 'Ajukan Persetujuan';
	height = '';
	width = '';
	showDialog4(title, content, width, height, 'event');
	formajukan(notransaksi, kdunit, page);
}

function formajukan(notransaksi, kdunit, page) {
	method = 'postingData';
	param = '';
	param += '&notransaksi=' + notransaksi + '&kdunit=' + kdunit + '&page=' + page;
	param += '&method=' + method;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formpost').innerHTML = con.responseText;
					// loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanApproval(notransaksi, maxaproval, page) {
	strper = '';
	for (i = 1; i <= maxaproval; i++) {
		strper += '&persetujuan[' + i + ']=' + document.getElementById('persetujuan' + i).value;

		if (document.getElementById('persetujuan' + i).value == '') {
			alert('Mohon Isi Persetujuan ' + i);
			return;
		}
	}
	param = "notransaksi=" + notransaksi + "&maxaproval=" + maxaproval + "&method=persetujuan";
	param += strper;

	if (confirm('Posting ' + notransaksi + '\nThis transaction will released. are you sure?')) {
		post_response_text('kebun_tbsexternal_slave.php', param, respon);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog4();
					loaddata(page - 1);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cekapproval(notransaksi) {
	content = "<div id=formget  style=\"height:100%;width:100%;\"></div>";
	title = 'Menunggu Approval';
	height = '';
	width = '330';
	//showDialog4(title,content,width,height,'event');	
	formapproval(notransaksi);
}

function formapproval(notransaksi) {
	method = 'cekapproval';
	param = '';
	param += '&notransaksi=' + notransaksi;
	param += '&method=' + method;
	post_response_text(tujuan, param, respon);
	tujuan = 'kebun_tbsexternal_slave.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('formget').innerHTML=con.responseText;
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('30%', '40%');
					// loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function posting(notransaksi) {
	param = 'method=posting' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deleteht(notransaksi) {
	param = 'method=deleteht';
	param += '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


/********************************************** pdf *********************************/
/********************************************** pdf *********************************/

function pdf(notransaksi) {
	param = 'method=pdf' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	tujuan = tujuan + '?' + param;
	// content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	// width = '820';
	// height = '500';
	// title = "";
	// showDialog5(title, content, width, height, 'event');

	alertify.popuppdf("", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}

function pdf2(notransaksi) {
	param = 'method=pdf2' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsexternal_slave.php';
	tujuan = tujuan + '?' + param;
	// content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	// width = '820';
	// height = '500';
	// title = "";
	// showDialog5(title, content, width, height, 'event');

	alertify.popuppdf("", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}
/********************************************** EXCEL *********************************/
/********************************************** EXCEL *********************************/

function excel(notransaksi) {
	param = 'notransaksi=' + notransaksi + '&method=pdf&type=excel';
	tujuan = 'kebun_tbsexternal_slave.php';
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul)
}

function printFile(param, tujuan, title) {
	tujuan = tujuan + "?" + param;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height);
}

/********************************************** detail *********************************/
/********************************************** detail *********************************/

maxf = 0
sekarang = 1;
function savedt(maxRow) {
	maxf = maxRow;
	loopsave(1, maxRow);
}



function loopsave(currRow, maxRow) {
	param = "";
	notransaksi = trim(document.getElementById('notransaksi').value);
	unit = trim(document.getElementById('unit').value);
	divisi = trim(document.getElementById('divisi').value);
	tanggal = trim(document.getElementById('tanggal').value);
	tanggaltbs1 = trim(document.getElementById('tanggaltbs1').value);
	tanggaltbs2 = trim(document.getElementById('tanggaltbs2').value);
	keteranganht = trim(document.getElementById('keteranganht').value);
	noafiliasi = trim(document.getElementById('noafiliasi').value);
	persenpph = trim(document.getElementById('persenpph').value);
	persenppn = trim(document.getElementById('persenppn').value);
	rp_pph = trim(document.getElementById('rp_pph').innerHTML);
	rp_ppn = trim(document.getElementById('rp_ppn').innerHTML);
	rpgrandtotal = trim(document.getElementById('rpgrandtotal').innerHTML);

	tanggalpks = trim(document.getElementById('tanggalpks' + currRow).innerHTML);
	nospb = trim(document.getElementById('nospb' + currRow).innerHTML);
	kdblok = trim(document.getElementById('kdblok' + currRow).innerHTML);
	notiket = trim(document.getElementById('notiket' + currRow).innerHTML);
	bjr = trim(document.getElementById('bjr' + currRow).innerHTML);
	tahuntanam = trim(document.getElementById('tahuntanam' + currRow).innerHTML);
	kgbruto = trim(document.getElementById('kgbruto' + currRow).innerHTML);
	kgpotongan = trim(document.getElementById('kgpotongan' + currRow).innerHTML);
	kgnetto = trim(document.getElementById('kgnetto' + currRow).innerHTML);
	rpkg = trim(document.getElementById('rpkg' + currRow).value);
	totalrp = trim(document.getElementById('totalrp' + currRow).value);
	unitinv = trim(document.getElementById('unitinv').value);



	bjr = remove_comma_var(bjr);
	kgbruto = remove_comma_var(kgbruto);
	kgpotongans = remove_comma_var(kgpotongan);
	kgnetto = remove_comma_var(kgnetto);
	rpkg = remove_comma_var(rpkg);
	totalrp = remove_comma_var(totalrp);
	rp_pph = remove_comma_var(rp_pph);
	rp_ppn = remove_comma_var(rp_ppn);
	rpgrandtotal = remove_comma_var(rpgrandtotal);


	if (unit == '' || tanggal == '') {
		alert("Data tidak lengkap"); return;
	} else {
		param += '&method=savedt' + '&unit=' + unit + '&divisi=' + divisi + '&tanggal=' + tanggal + '&notransaksi=' + notransaksi;
		param += '&kodeblok=' + kdblok;
		param += '&tanggaltbs1=' + tanggaltbs1 + '&tanggaltbs2=' + tanggaltbs2 + '&keteranganht=' + keteranganht;
		param += '&tanggalpks=' + tanggalpks + '&nospb=' + nospb;
		param += '&notiket=' + notiket + '&bjr=' + bjr + '&tahuntanam=' + tahuntanam;
		param += '&kgbruto=' + kgbruto + '&kgpotongan=' + kgpotongan + '&kgnetto=' + kgnetto;
		param += '&rpkg=' + rpkg + '&totalrp=' + totalrp + '&noafiliasi=' + noafiliasi + '&unitinv=' + unitinv;
		param += '&persenppn=' + persenppn + '&persenpph=' + persenpph + '&rp_ppn=' + rp_ppn + '&rp_pph=' + rp_pph + '&rpgrandtotal=' + rpgrandtotal;
		console.log("rpph = " + rp_pph);
		console.log("rppn = " + rp_ppn);
		console.log("rpgrandtotal = " + rpgrandtotal);

		// alert(param);return;
		tujuan = 'kebun_tbsexternal_slave.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row' + currRow).style.backgroundColor = '';
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
		// document.getElementById('row'+currRow).style.backgroundColor='';
		// document.getElementById('row'+currRow).style.display='none';
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alert('Done');
						loaddatadt();
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loaddatadt() {
	notransaksi = document.getElementById('notransaksi').value;
	unit = document.getElementById('unit').value;
	tanggaltbs1 = document.getElementById('tanggaltbs1').value;
	tanggaltbs2 = document.getElementById('tanggaltbs2').value;
	divisi = document.getElementById('divisi').value;
	persenppn = document.getElementById('persenppn').value;
	persenpph = document.getElementById('persenpph').value;
	param = 'method=loaddatadt';
	param += '&notransaksi=' + notransaksi + '&unit=' + unit;
	param += '&tanggaltbs1=' + tanggaltbs1 + '&tanggaltbs2=' + tanggaltbs2 + '&divisi=' + divisi + '&persenpph=' + persenpph + '&persenppn=' + persenppn;
	tujuan = 'kebun_tbsexternal_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}













