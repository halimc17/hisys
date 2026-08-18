

function gettipesupplier() {
	param = "";
	kodeunit = document.getElementById('kodeunitharga').value;
	param += 'kodeunit=' + kodeunit + '&method=gettipesupplier';
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipeharga').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function gettipesupplier2() {
	param = "";
	kodeunit = document.getElementById('kodeunitharga2copy').value;
	param += 'kodeunit=' + kodeunit + '&method=gettipesupplier';
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipeharga2copy').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function gettipesuppliercopy() {
	param = "";
	kodeunit = document.getElementById('kodeunithargacopy').value;
	param += 'kodeunit=' + kodeunit + '&method=gettipesupplier';
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipehargacopy').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function hapushargatbs(notransaksi, kodeunit, tahuntanam, tanggal, tanggal2) {
	param = "";
	//$tab.="&nbsp; <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"hapushargatbs('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".$bar['tanggal']."','".$bar['tanggal2']."')\" >";          
	param += 'notransaksi=' + notransaksi + '&method=hapushargatbs';
	if (confirm('Hapus data unit ' + kodeunit + ' tahun tanam ' + tahuntanam + ' untuk tanggal ' + tanggal + ' s/d ' + tanggal2 + ' ?')) {
		post_response_text('pmn_hargabelitbs_slave.php', param, respon);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddataharga();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function postinght(notransaksi) {
	param = "";
	param += 'notransaksi=' + notransaksi + '&method=postinght';
	if (confirm('Posting data ?')) {
		post_response_text('pmn_hargabelitbs_slave.php', param, respon);
	}
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddataharga();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalprosescopy() {
	document.getElementById('tipehargacopy').value = '';
	document.getElementById('kodeunithargacopy').value = '';
	document.getElementById('tanggalhargacopy').value = '';
	document.getElementById('tanggalharga2copy').value = '';
	document.getElementById('tanggalhargatujuancopy').value = '';
	document.getElementById('tanggalhargatujuan2copy').value = '';
	document.getElementById('tahuntanamhargacopy').value = '';

	document.getElementById('jamhargacopy').value = '00';
	document.getElementById('menithargacopy').value = '00';
	document.getElementById('jamharga2copy').value = '00';
	document.getElementById('menitharga2copy').value = '00';
	document.getElementById('jamhargatujuancopy').value = '00';
	document.getElementById('menithargatujuancopy').value = '00';
	document.getElementById('jamhargatujuan2copy').value = '00';
	document.getElementById('menithargatujuan2copy').value = '00';
}

function prosescopy() {
	param = "";
	tipe = document.getElementById('tipehargacopy').value;
	kodeunit = document.getElementById('kodeunithargacopy').value;

	tipecopy = document.getElementById('tipeharga2copy').value;
	kodeunitcopy = document.getElementById('kodeunitharga2copy').value;

	tanggal = document.getElementById('tanggalhargacopy').value;
	jam = document.getElementById('jamhargacopy').value;
	menit = document.getElementById('menithargacopy').value;

	tanggal2 = document.getElementById('tanggalharga2copy').value;
	jam2 = document.getElementById('jamharga2copy').value;
	menit2 = document.getElementById('menitharga2copy').value;


	tanggalcopy = document.getElementById('tanggalhargatujuancopy').value;
	jamcopy = document.getElementById('jamhargatujuancopy').value;
	menitcopy = document.getElementById('menithargatujuancopy').value;

	tanggal2copy = document.getElementById('tanggalhargatujuan2copy').value;
	jam2copy = document.getElementById('jamhargatujuan2copy').value;
	menit2copy = document.getElementById('menithargatujuan2copy').value;

	tahuntanam = document.getElementById('tahuntanamhargacopy').value;
	method = 'prosescopy';
	// if(kodeunit==''||tanggal==''||tanggal2==''||tanggalcopy==''||tanggal2copy==''||tipe==''){
	// alert('Lengkapi Pengisian');
	// return false;
	// }
	param += 'kodeunit=' + kodeunit + '&tanggal=' + tanggal + '&tanggal2=' + tanggal2 + '&method=' + method;
	param += '&tanggalcopy=' + tanggalcopy + '&tanggal2copy=' + tanggal2copy + '&tahuntanam=' + tahuntanam;
	param += '&tipe=' + tipe + '&tipecopy=' + tipecopy + '&kodeunitcopy=' + kodeunitcopy;

	param += '&jam=' + jam + '&menit=' + menit + '&jam2=' + jam2 + '&menit2=' + menit2;
	param += '&jamcopy=' + jamcopy + '&menitcopy=' + menitcopy + '&jam2copy=' + jam2copy + '&menit2copy=' + menit2copy;

	tujuan = 'pmn_hargabelitbs_slave.php';
	kalimatthntnm = " tahun tanam / grade seluruhnya ";
	if (tahuntanam != '') {
		kalimatthntnm = ' tahun tanam / grade ' + tahuntanam + ' ';
	}
	if (confirm('Data dicopy untuk ' + kodeunit + ' ' + kalimatthntnm + '  tanggal ' + tanggal + ' ' + jam + ':' + menit + ' s/d ' + tanggal2 + ' ' + jam2 + ':' + menit2 + ' tipe ' + tipe + ' menjadi  ' + kodeunitcopy + ' ' + kalimatthntnm + ' tipecopy ' + tipecopy + '  ' + tanggalcopy + ' ' + jamcopy + ':' + menitcopy + ' s/d ' + tanggal2copy + ' ' + jam2copy + ':' + menit2copy + ' ?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddataharga();
					// alert('Data sudah dicopy untuk unit '+kodeunit+' tanggal '+tanggal1+' s/d '+tanggal2+'  menjadi '+tanggalcopy+' s/d '+tanggal2copy+' ');				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



maxf = 0
sekarang = 1;
function simpanharga(maxRow) {
	maxf = maxRow;
	loopsimpanharga(1, maxRow);
}
function loopsimpanharga(currRow, maxRow) {

	kodeunit = document.getElementById('kodeunitharga').value;
	tanggal = document.getElementById('tanggalharga').value;
	tanggal2 = document.getElementById('tanggalharga2').value;
	tahuntanam = document.getElementById('tahuntanamharga').value;
	tipe = document.getElementById('tipeharga').value;

	jam = document.getElementById('jamharga').value;
	jam2 = document.getElementById('jamharga2').value;
	menit = document.getElementById('menitharga').value;
	menit2 = document.getElementById('menitharga2').value;



	supplier = document.getElementById('supplierharga' + currRow).innerHTML;

	budgetharga = document.getElementById('budgetharga' + currRow).value;
	budgetharga = remove_comma_var(budgetharga);

	disbunharga = document.getElementById('disbunharga' + currRow).value;
	disbunharga = remove_comma_var(disbunharga);

	harga = document.getElementById('harga' + currRow).value;
	harga = remove_comma_var(harga);


	// if (document.getElementById('cekbapp' + currRow).checked == true) {
	// cekbapp = 1;
	// } else {
	// cekbapp = 0;
	// }
	param = 'method=simpanharga' + '&kodeunit=' + kodeunit + '&tanggal=' + tanggal + '&tanggal2=' + tanggal2 + '&tahuntanam=' + tahuntanam + '&supplier=' + supplier;
	param += '&budgetharga=' + budgetharga + '&disbunharga=' + disbunharga + '&harga=' + harga + '&tipe=' + tipe;
	param += '&jam=' + jam + '&menit=' + menit + '&jam2=' + jam2 + '&menit2=' + menit2;
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alert('Done');

						batalharga();
						loaddataharga();
						// listbapp(nopdo, unit, per);
					} else {
						loopsimpanharga(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);

			}
		}
	}
}


function batalharga() {
	document.getElementById('kodeunitharga').value = '';
	document.getElementById('kodeunitharga').disabled = false;
	document.getElementById('tahuntanamharga').disabled = false;
	document.getElementById('tahuntanamharga').value = '';
	document.getElementById('tipeharga').value = '';
	document.getElementById('awalrealisasiharga').value = '';
	document.getElementById('awaldisbunharga').value = '';
	document.getElementById('tanggalharga').disabled = false;
	document.getElementById('tanggalharga2').disabled = false;
	document.getElementById('buttonpreviewharga').disabled = false;
	document.getElementById('awaldisbunharga').disabled = false;
	document.getElementById('awalrealisasiharga').disabled = false;
	document.getElementById('tipeharga').disabled = false;
	document.getElementById('detaildataharga').style.display = 'none';
	document.getElementById('listdataharga').style.display = 'block';
	// document.getElementById('jamharga').value='00';
	// document.getElementById('jamharga2').value='00';
	// document.getElementById('menitharga').value='00';
	// document.getElementById('menitharga2').value='00';

}

function getPageharga() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddataharga(paged);
}


function batalcariharga() {
	document.getElementById('kodeunithargacari').value = '';
	document.getElementById('tipehargacari').value = '';
	document.getElementById('tahuntanamhargacari').value = '';
	document.getElementById('tanggalhargacari').value = '';
	loaddataharga(0);
}


// function posting(notransaksi,kodeunit){
// content= "<div id=formpost  style=\"height:100%;width:325px;\"></div>";
// title='posting';
// height='';
// width='330';
// showDialog1(title,content,width,height,'event');	
// getformPost(notransaksi,kodeunit);
// } 



// function postingunit(notransaksi,kodeunit,tahuntanam,tanggal,tanggal2){
// content= "<div id=formpost  style=\"height:100%;width:325px;\"></div>";
// title='posting';
// height='';
// width='330';
// showDialog1(title,content,width,height,'event');	
// getformPost(notransaksi,kodeunit,tahuntanam,tanggal,tanggal2);
// } 


function posting(notransaksi, kodeunit, tahuntanam, tanggaljam, tanggaljam2, tipe) {
	content = "<div id=formpost  style=\"height:100%;width:325px;\"></div>";
	title = 'posting';
	height = '';
	width = '330';
	showDialog1(title, content, width, height, 'event');
	getformPost(notransaksi, kodeunit, tahuntanam, tanggaljam, tanggaljam2, tipe);
}


function getformPost(notransaksi, kodeunit, tahuntanam, tanggaljam, tanggaljam2, tipe) {
	var param = "notransaksi=" + notransaksi + "&kodeunit=" + kodeunit + "&tahuntanam=" + tahuntanam + "&tanggaljam=" + tanggaljam + "&tanggaljam2=" + tanggaljam2 + "&tipe=" + tipe;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('formpost').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text('pmn_hargabelitbs_slave.php?method=showform', param, respon);
}



// #= diubah menjadi persetujuan
function saveposting(notransaksi, kodeunit, tahuntanam, tanggaljam, tanggaljam2, maxaproval, tipe) {
	strper = '';
	for (i = 1; i <= maxaproval; i++) {

		a = trim(document.getElementById('persetujuan' + i).value);
		if (a == '') {
			alert('Persetujuan masih kosong'); return;
		}

		strper += '&persetujuan[' + i + ']=' + trim(document.getElementById('persetujuan' + i).value);



	}
	param = "notransaksi=" + notransaksi + "&kodeunit=" + kodeunit + "&tahuntanam=" + tahuntanam + "&tanggaljam=" + tanggaljam + "&tanggaljam2=" + tanggaljam2 + "&maxaproval=" + maxaproval + "&tipe=" + tipe + "&method=persetujuan";
	param += strper;

	if (confirm('Posting Persetujuan unit ' + kodeunit + ' tahun tanam ' + tahuntanam + ' tanggal ' + tanggaljam + ' s/d ' + tanggaljam2 + ' tipe ' + tipe + ' ?')) {
		post_response_text('pmn_hargabelitbs_slave.php', param, respon);
	}



	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					getPageharga();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	//}
}



function loaddataharga(num) {
	kodeunitcari = document.getElementById('kodeunithargacari').value;
	tipehargacari = document.getElementById('tipehargacari').value;
	tahuntanamcari = document.getElementById('tahuntanamhargacari').value;
	tanggalcari = document.getElementById('tanggalhargacari').value;
	param = 'method=loaddataharga';
	param += '&page=' + num + '&kodeunitcari=' + kodeunitcari + '&tahuntanamcari=' + tahuntanamcari + '&tanggalcari=' + tanggalcari + '&tipehargacari=' + tipehargacari;
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('containerharga').innerHTML = con.responseText;
					loaddatagrade();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function previewharga() {

	param = "";
	kodeunit = document.getElementById('kodeunitharga').value;
	tanggal = document.getElementById('tanggalharga').value;
	tanggal2 = document.getElementById('tanggalharga2').value;
	jam = document.getElementById('jamharga').value;
	jam2 = document.getElementById('jamharga2').value;
	menit = document.getElementById('menitharga').value;
	menit2 = document.getElementById('menitharga2').value;
	tahuntanam = document.getElementById('tahuntanamharga').value;
	awaldisbun = document.getElementById('awaldisbunharga').value;
	awalrealisasi = document.getElementById('awalrealisasiharga').value;
	tipe = document.getElementById('tipeharga').value;
	method = 'previewharga';

	if (kodeunit == '' || tanggal == '' || tahuntanam == '') {
		alert('Field Was Empty');
		return false;
	}

	param += 'kodeunit=' + kodeunit + '&tanggal=' + tanggal + '&tanggal2=' + tanggal2 + '&tahuntanam=' + tahuntanam + '&method=' + method + '&awaldisbun=' + awaldisbun + '&awalrealisasi=' + awalrealisasi + '&tipe=' + tipe;
	param += '&jam=' + jam + '&menit=' + menit + '&jam2=' + jam2 + '&menit2=' + menit2;
	// console.log(param);
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kodeunitharga').disabled = true;
					document.getElementById('tanggalharga').disabled = true;
					document.getElementById('tanggalharga2').disabled = true;
					document.getElementById('tahuntanamharga').disabled = true;
					document.getElementById('buttonpreviewharga').disabled = true;
					document.getElementById('awaldisbunharga').disabled = true;
					document.getElementById('tipeharga').disabled = true;
					document.getElementById('awalrealisasiharga').disabled = true;
					document.getElementById('detaildataharga').style.display = 'block';
					document.getElementById('listdataharga').style.display = 'none';
					document.getElementById('detailharga').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function formviewhargatbs(titledt) {
	width = '400px';
	// height = 'auto';
	height = '200px';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerview style='width:700px;height:400px;overflow:auto'></div></fieldset>";
	ev = 'event';
	title = titledt;//"Detail HTML";
	showDialog1(title, content, width, height, ev);
}


function viewhargatbs(notransaksi) {
	titl = "Detail data :" + notransaksi;
	// formviewhargatbs(titl);
	param = 'method=viewhargatbs' + '&notransaksi=' + notransaksi;
	// alert(param);
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containerview').innerHTML = con.responseText;
					alertify.popup("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('600px', '600px');
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function uploadcsv() {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	// formdata.append("notransaksi", notransaksi);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_hargabelitbs_slave.php?method=uploadcsv", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					alertify.alert('Informasi', con.responseText);
				} else {
					loaddataharga();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function viewhargatbsperunit(kodeunit, tanggal, tipe) {
	titl = "Detail data :" + kodeunit + " " + tanggal;
	formviewhargatbs(titl);
	param = 'method=viewhargatbsperunit' + '&kodeunit=' + kodeunit + '&tanggal=' + tanggal + '&tipe=' + tipe;
	// alert(param);
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('containerview').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}



/*
############################################################################
############################################################################
############################################################################
*/


function simpangrade() {

	param = "";
	kode = document.getElementById('kodegrade').value;
	kodeunit = document.getElementById('kodeunitgrade').value;
	batasbawah = document.getElementById('batasbawahgrade').value;
	batasatas = document.getElementById('batasatasgrade').value;
	// method=document.getElementById('methodgrade').value;
	method = 'simpangrade';

	if (kode == '' || kodeunit == '' || batasbawah == '' || batasatas == '') {
		alert('Field Was Empty');
		return false;
	}

	param += 'kode=' + kode + '&batasbawah=' + batasbawah + '&batasatas=' + batasatas + '&method=' + method + '&kodeunit=' + kodeunit;
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalgrade();
					loaddatagrade();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function batalgrade() {
	document.getElementById('kodeunitgrade').value = '';
	document.getElementById('kodegrade').value = '';
	document.getElementById('batasbawahgrade').value = '';
	document.getElementById('batasatasgrade').value = '';
	document.getElementById('methodmaster').value = 'insertmaster';
}



function loaddatagrade() {
	param = 'method=loaddatagrade';
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containergrade').innerHTML = con.responseText;

				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function hapusgrade(kodeunit, kode) {
	method = 'hapusgrade';
	param = '';
	param += 'kode=' + kode + '&method=' + method + '&kodeunit=' + kodeunit;
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatagrade();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


/*
**************************************************************************************************
**************************************************************************************************
**************************************************************************************************
*/





function simpanmaster() {

	param = "";
	kodeunit = document.getElementById('kodeunitmaster').value;
	supplier = document.getElementById('suppliermaster').value;
	aktif = document.getElementById('aktifmaster').value;
	method = document.getElementById('methodmaster').value;

	if (kodeunit == '' || supplier == '') {
		alert('Field Was Empty');
		return false;
	}

	param += 'kodeunit=' + kodeunit + '&supplier=' + supplier + '&aktif=' + aktif + '&method=' + method;

	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					batalmaster();
					loaddatamaster(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getsupmaster() {

	param = "";
	kodeunit = document.getElementById('kodeunitmaster').value;
	method = 'getsupmaster';

	if (kodeunit == '') {
		alert('Unit tidak boleh kosong');
		document.getElementById('kodeunitmaster').value = '';
		return false;
	}

	param += 'kodeunit=' + kodeunit + '&method=' + method;

	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('suppliermaster').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




function getPagemaster() {
	pg = document.getElementById('pagesmaster');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddatamaster(paged);
}

function batalmaster() {
	document.getElementById('kodeunitmaster').value = '';
	document.getElementById('kodeunitmaster').disabled = false;
	document.getElementById('suppliermaster').value = '';
	document.getElementById('aktifmaster').value = '';
	document.getElementById('suppliermaster').disabled = false;
	document.getElementById('methodmaster').value = 'insertmaster';
}

function loaddatamaster(num) {
	kodeunitcari = document.getElementById('kodeunitmastercari').value;
	tipecari = document.getElementById('tipemastercari').value;
	param = 'method=loaddatamaster';
	param += '&page=' + num + '&kodeunitcari=' + kodeunitcari + '&tipecari=' + tipecari;
	tujuan = 'pmn_hargabelitbs_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('containermaster').innerHTML = con.responseText;
					loaddataharga(0);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editmaster(kodeunitmaster, suppliermaster, aktifmaster) {
	document.getElementById('kodeunitmaster').disabled = true;
	document.getElementById('suppliermaster').disabled = true;
	document.getElementById('kodeunitmaster').value = kodeunitmaster;
	document.getElementById('suppliermaster').value = suppliermaster;
	document.getElementById('aktifmaster').value = aktifmaster;
	document.getElementById('methodmaster').value = 'updatemaster';
}
