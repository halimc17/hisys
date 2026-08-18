/**
 * @author sendi.bagaskara
 */

function batal() {
	document.getElementById("jenissp").selectedIndex = "";
	document.getElementById("jenissp").disabled = false;
	document.getElementById("lokasitugas").selectedIndex = "";
	document.getElementById("tipekaryawan").selectedIndex = "";
	document.getElementById("karyawanid").selectedIndex = "";
	document.getElementById('tanggalsp').value = '';
	document.getElementById('paragraf1').value = '';
	document.getElementById('paragraf2').value = '';
	document.getElementById('paragraf3').value = '';
	document.getElementById('paragraf4').value = '';
	document.getElementById('penandatangan').value = '';
	document.getElementById('jabatan').value = '';
	document.getElementById('verifikasi').value = '';
	document.getElementById('jabatan1').value = '';
	document.getElementById('dibuat').value = '';
	document.getElementById('jabatan2').value = '';
	document.getElementById('tembusan1').value = '';
	document.getElementById('tembusan2').value = '';
	document.getElementById('tembusan3').value = '';
	document.getElementById('tembusan4').value = '';
	document.getElementById('tembusan4').value = '';
	document.getElementById('paragraf4').style.display = 'block';
	document.getElementById('txt4').style.display = 'block';
	document.getElementById('method').value = 'insert';
}

function saveSP() {
	jenissp = document.getElementById('jenissp').value;
	karyawanid = document.getElementById('karyawanid').value;
	nosp = document.getElementById('nosp').value;
	tanggalsp = document.getElementById('tanggalsp').value;
	paragraf1 = document.getElementById('paragraf1').value;
	paragraf2 = document.getElementById('paragraf2').value;
	paragraf3 = document.getElementById('paragraf3').value;
	paragraf4 = document.getElementById('paragraf4').value;

	penandatangan = trim(document.getElementById('penandatangan').value);
	jabatan = document.getElementById('jabatan').value;
	verifikasi = trim(document.getElementById('verifikasi').value);
	jabatan1 = document.getElementById('jabatan1').value;
	dibuat = trim(document.getElementById('dibuat').value);
	jabatan2 = document.getElementById('jabatan2').value;

	tembusan1 = document.getElementById('tembusan1').value;
	tembusan2 = document.getElementById('tembusan2').value;
	tembusan3 = document.getElementById('tembusan3').value;
	tembusan4 = document.getElementById('tembusan4').value;
	method = document.getElementById('method').value;


	if (jenissp == '') {
		alert("Jenis Surat Wajib diisi");
		return false;
	}

	if (karyawanid == '') {
		alert("Karyawan Wajib diisi");
		return false;
	}

	if (tanggalsp == '') {
		alert("Tanggal SP Wajib diisi");
		return false;
	}

	param = 'jenissp=' + jenissp + '&karyawanid=' + karyawanid;
	param += '&nosp=' + nosp;
	param += '&tanggalsp=' + tanggalsp + '&paragraf1=' + paragraf1 + '&paragraf2=' + paragraf2;
	param += '&paragraf3=' + paragraf3 + '&paragraf4=' + paragraf4;
	param += '&penandatangan=' + penandatangan;
	param += '&jabatan=' + jabatan + '&tembusan1=' + tembusan1;
	param += '&tembusan2=' + tembusan2 + '&tembusan3=' + tembusan3;
	param += '&tembusan4=' + tembusan4 + '&method=' + method;
	param += '&verifikasi=' + verifikasi + '&dibuat=' + dibuat;
	param += '&jabatan1=' + jabatan1 + '&jabatan2=' + jabatan2;

	if (confirm('Saving, are you sure..?')) {
		tujuan = 'sdm_slave_saveSP.php';
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Data berhasil disimpan...');
					batal()
					loadList(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loadList(num) {
	param = '&page=' + num;
	param = 'page=' + num + '&method=loaddata';
	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariSP(num) {
	tex = trim(document.getElementById('txtbabp').value);
	param = 'page=' + num + '&method=loaddata';
	if (tex != '') {
		param += '&tex=' + tex;
	}
	tujuan = 'sdm_slave_getSPList.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delSP(nosp, karid) {
	param = 'nosp=' + nosp + '&method=delete&karyawanid=' + karid;
	tujuan = 'sdm_slave_saveSP.php';
	if (confirm('Deleting Document ' + nosp + ', are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(nosp, karid) {
	param = 'nosp=' + nosp + '&method=posting&karyawanid=' + karid;
	tujuan = 'sdm_slave_saveSP.php';
	if (confirm('Posting Document ' + nosp + ', are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewSP(nosp, ev) {
	param = 'nosp=' + nosp;
	tujuan = 'sdm_slave_printSP_pdf.php?' + param;
	title = nosp;
	width = '800';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function pdfSP(nosp) {
	param = 'method=pdfSP';
	param += '&notrans=' + nosp;
	tujuan = 'sdm_slave_getSPList.php?' + param;
	judul = 'Report PDF ' + nosp;
	ev = 'event';
	closeDialog();
	alertify.popuppdf(judul, "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}

function editSP(nosp, nokaryawan, jenissp) {

	param = 'karid=' + nokaryawan + '&notrans=' + nosp + '&jenisspx=' + jenissp + '&method=editData';
	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					data = con.responseText.split("##");
					document.getElementById('jenissp').value = data[0];
					document.getElementById('lokasitugas').value = data[1];
					document.getElementById('tipekaryawan').value = data[2];
					document.getElementById('karyawanid').value = data[3];
					document.getElementById('tanggalsp').value = data[4];
					document.getElementById('paragraf1').value = data[5];
					document.getElementById('paragraf2').value = data[6];
					document.getElementById('paragraf3').value = data[7];
					document.getElementById('paragraf4').value = data[8];

					document.getElementById('penandatangan').value = data[9];
					document.getElementById('jabatan').value = data[10];

					document.getElementById('verifikasi').value = data[11];
					document.getElementById('jabatan1').value = data[12];

					document.getElementById('dibuat').value = data[13];
					document.getElementById('jabatan2').value = data[14];

					document.getElementById('tembusan1').value = data[15];
					document.getElementById('tembusan2').value = data[16];
					document.getElementById('tembusan3').value = data[17];
					document.getElementById('tembusan4').value = data[18];

					document.getElementById('method').value = 'update';
					document.getElementById('nosp').value = nosp;


					if (data[0] == 'SP1' || data[0] == 'SP2' || data[0] == 'SP3' || data[0] == 'ST') {
						document.getElementById('paragraf1').style.display = 'block';
						document.getElementById('txt1').style.display = 'block';

						document.getElementById('paragraf2').style.display = 'block';
						document.getElementById('txt2').style.display = 'block';

						document.getElementById('paragraf3').style.display = 'block';
						document.getElementById('txt3').style.display = 'block';

						document.getElementById('paragraf4').style.display = 'none';
						document.getElementById('txt4').style.display = 'none';
					} else if (data[0] == 'PHK') {

						document.getElementById('paragraf1').style.display = 'none';
						document.getElementById('txt1').style.display = 'none';

						document.getElementById('paragraf2').style.display = 'none';
						document.getElementById('txt2').style.display = 'none';

						document.getElementById('paragraf3').style.display = 'none';
						document.getElementById('txt3').style.display = 'none';

						document.getElementById('paragraf4').style.display = 'block';
						document.getElementById('txt4').style.display = 'block';
					} else {
						document.getElementById('paragraf1').style.display = 'block';
						document.getElementById('txt1').style.display = 'block';

						document.getElementById('paragraf2').style.display = 'block';
						document.getElementById('txt2').style.display = 'block';

						document.getElementById('paragraf3').style.display = 'block';
						document.getElementById('txt3').style.display = 'block';

						document.getElementById('paragraf4').style.display = 'block';
						document.getElementById('txt4').style.display = 'block';
					}

					tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changeDatakaryawan() {
	lokasitugas = document.getElementById('lokasitugas').value;
	tipekaryawan = document.getElementById('tipekaryawan').value;

	param = 'lokasitugas=' + lokasitugas + '&tipekaryawan=' + tipekaryawan + '&method=changeDatakaryawan';
	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('karyawanid').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function memotypeChange() {
	jenissp = document.getElementById('jenissp').options[document.getElementById('jenissp').selectedIndex].value;

	if (jenissp == 'SP1' || jenissp == 'SP2' || jenissp == 'SP3' || jenissp == 'ST') {
		document.getElementById('paragraf1').style.display = 'block';
		document.getElementById('txt1').style.display = 'block';

		document.getElementById('paragraf2').style.display = 'block';
		document.getElementById('txt2').style.display = 'block';

		document.getElementById('paragraf3').style.display = 'block';
		document.getElementById('txt3').style.display = 'block';

		document.getElementById('paragraf4').style.display = 'none';
		document.getElementById('txt4').style.display = 'none';
	} else if (jenissp == 'PHK') {

		document.getElementById('paragraf1').style.display = 'none';
		document.getElementById('txt1').style.display = 'none';

		document.getElementById('paragraf2').style.display = 'none';
		document.getElementById('txt2').style.display = 'none';

		document.getElementById('paragraf3').style.display = 'none';
		document.getElementById('txt3').style.display = 'none';

		document.getElementById('paragraf4').style.display = 'block';
		document.getElementById('txt4').style.display = 'block';
	} else {
		document.getElementById('paragraf1').style.display = 'block';
		document.getElementById('txt1').style.display = 'block';

		document.getElementById('paragraf2').style.display = 'block';
		document.getElementById('txt2').style.display = 'block';

		document.getElementById('paragraf3').style.display = 'block';
		document.getElementById('txt3').style.display = 'block';

		document.getElementById('paragraf4').style.display = 'block';
		document.getElementById('txt4').style.display = 'block';
	}

	getFormatLetter();
}

function getFormatLetter() {
	jenissp = document.getElementById('jenissp').options[document.getElementById('jenissp').selectedIndex].value;

	param = 'jenissp=' + jenissp + '&method=selectsp';
	tujuan = 'sdm_slave_saveSP.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					isis = con.responseText.split("###");

					// document.getElementById('paragraf1').value=isis[0];
					// document.getElementById('paragraf2').value=isis[1];

					if (jenissp == 'SP1' || jenissp == 'SP2' || jenissp == 'SP3' || jenissp == 'ST') {
						document.getElementById('paragraf1').value = "";
						document.getElementById('paragraf2').value = "";
						document.getElementById('paragraf3').value = isis[2];
						document.getElementById('paragraf4').value = isis[3];
					} else if (jenissp == 'PHK') {
						document.getElementById('paragraf1').value = "";
						document.getElementById('paragraf2').value = "";
						document.getElementById('paragraf3').value = "";
						document.getElementById('paragraf4').value = isis[3];
					} else {
						document.getElementById('paragraf1').value = "";
						document.getElementById('paragraf2').value = "";
						document.getElementById('paragraf3').value = "";
						document.getElementById('paragraf4').value = "";
					}
				}
			}
			else {
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
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, notrans, karid, jenisspx) {
	showformupload(ev);

	param = "";
	param += "notrans=" + notrans;
	param += "&karid=" + karid;
	param += "&jenisspx=" + jenisspx;
	param += '&method=showupload';
	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(notrans, karid, jenisspx);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notrans, karid, jenisspx) {
	param = "";
	param += "notrans=" + notrans;
	param += "&karid=" + karid;
	param += "&jenisspx=" + jenisspx;
	param += '&method=loadfiles';
	tujuan = 'sdm_slave_getSPList.php';
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notrans = document.getElementById('notrans').innerHTML;
	var karid = document.getElementById('karid').innerHTML;
	var jenisspx = document.getElementById('jenisspx').innerHTML;
	var formdata = new FormData();

	formdata.append("notrans", notrans);
	formdata.append("karid", karid);
	formdata.append("jenisspx", jenisspx);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_getSPList.php?method=submitfile", true);
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
					loadfiles(notrans, karid, jenisspx);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notrans, karid, jenisspx, namafile) {
	param = "method=deletefile";
	param += "&notrans=" + notrans;
	param += "&karid=" + karid;
	param += "&jenisspx=" + jenisspx;
	param += "&namafile=" + namafile;

	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notrans, karid, jenisspx);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewfile(namafile, sumber) {
	//formupload();
	param = 'method=viewfile&namafile=' + namafile;
	tujuan = 'sdm_slave_getSPList.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('80%', '70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}