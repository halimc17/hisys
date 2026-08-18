function getKegiatan(noakun){
	cekproses = document.getElementById('proses').value;
	kodekegiatan = document.getElementById('kodekegiatan').value;

    param = "cekproses=" + cekproses;
	param += '&proses=getKegiatan';
    param += "&noakun=" + noakun;
    param += "&kodekegiatan=" + kodekegiatan;

    tujuan = 'sdm_slave_absensi.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					split = con.responseText.split("###");
                    document.getElementById('kodekegiatan').innerHTML = split[0];

					if(split[1] == '0'){
						document.getElementById('alokasi').disabled = true;
					}else{
						document.getElementById('alokasi').disabled = false;
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getKehadiran(){
	param  = 'proses=getKehadiran';
    tujuan = 'sdm_slave_absensi.php';
    post_response_text(tujuan, param, respog);

	function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('absniId').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function add_new_data() {

	//alert(con.responseText);
	document.getElementById('headher').style.display = "block";
	document.getElementById('listData').style.display = "none";
	document.getElementById('detailEntry').style.display = "none";
	document.getElementById('tmbLheader').innerHTML = '<button class=mybutton id=dtlAbn onclick=detailAbsn()>' + nmTmblSave + '</button><button class=mybutton id=cancelAbn onclick=cancelAbsn()>' + nmTmblCancel + '</button>';
	document.getElementById('tombol').innerHTML = '';
	document.getElementById('contentDetail').innerHTML = '';
	statFrm = 0;
	status_inputan = 0;
	unlockForm();

}
function cancelAbsn() {
	displayList();
}
function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('detailEntry').style.display = 'none';
	document.getElementById('kdOrgCari').value = '';
	document.getElementById('tgl_cari').value = '';
	loadData();
}
function cariOrg(title, content, ev) {
	width = '500';
	height = '400';
	showDialog1(title, content, width, height, ev);
	//alert('asdasd');
}
function findOrg() {
	txt = trim(document.getElementById('fnOrg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&proses=cariOrg';
		tujuan = 'sdm_slave_absensi.php';
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
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setOrg(kdOrg, nmOrg) {
	document.getElementById('kdOrg').value = kdOrg;
	document.getElementById('nmOrg').value = nmOrg;
	closeDialog();
}
function findOrg2() {
	txt = trim(document.getElementById('crOrg').value);
	if (txt == '') {
		alert('Text is obligatory');
	} else if (txt.length < 3) {
		alert('Text too short');
	} else {
		param = 'txtfind=' + txt + '&proses=cariOrg2';
		tujuan = 'sdm_slave_absensi.php';
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
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setOrg2(kdOrg, nmOrg) {
	document.getElementById('kdOrg').value = kdOrg;
	document.getElementById('txtsearch').value = nmOrg;
	closeDialog();
}
function detailAbsn() {
	kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	period = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	tgl = document.getElementById('tglAbsen').value;
	if ((kdorg == '') && (tgl == '')) {
		alert("Kode organisasi, tanggal dan periode wajib diisi.");
		return;
	}

	id = kdorg + "###" + tgl;
	//alert(hsl);
	//return;
	//alert(notran);
	tujuan = 'sdm_slave_absensi.php';
	param = 'absnId=' + id + '&proses=cekHeader' + '&period=' + period;
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					add_detail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function add_detail() {
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	id = kdorg + "###" + tgl;
	//alert(hsl);
	//return;
	//alert(notran);
	param = 'absnId=' + id;
	param += "&proses=createTable";
	//alert(param);
	tujuan = 'sdm_slave_absen_detail.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					document.getElementById('detailEntry').style.display = 'block';
					document.getElementById('detailIsi').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});					
					document.getElementById('tmbLheader').innerHTML = '';
					lockForm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function lockForm() {
	document.getElementById('kdOrg').disabled = true;
	document.getElementById('tglAbsen').disabled = true;
	document.getElementById('periode').disabled = true;
}
function unlockForm() {
	document.getElementById('kdOrg').disabled = false;
	document.getElementById('tglAbsen').disabled = false;
	document.getElementById('periode').disabled = false;
	document.getElementById('kdOrg').value = '';
	document.getElementById('tglAbsen').value = '';
	document.getElementById('periode').value = '';
}
status_inputan = 0;
function addDetail() {

	crt = document.getElementById('proses');
	//	alert(crt.value);
	kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tgl = document.getElementById('tglAbsen').value;

	var detKode = kdorg + "###" + tgl;
	var period = document.getElementById('periode').value;
	var rkrywn = document.getElementById('krywnId');
	var rshft = document.getElementById('shiftId');
	var rasbnsi = document.getElementById('absniId');
	var rjm = document.getElementById('jmId');
	var rmnt = document.getElementById('mntId');
	var jam = rjm.value + ":" + rmnt.value;
	var rket = document.getElementById('ktrng');
	var catu = document.getElementById('catu').options[document.getElementById('catu').selectedIndex].value;

	var dendakehadiran = document.getElementById('dendakehadiran').value;
	if (dendakehadiran == '')
		dendakehadiran = 0;
	//addSession();
	//var id_user = trim(document.getElementById('user_id').value);
	//alert(rasbnsi.value);
	if (status_inputan == 0) {
		if (confirm('Add detail, are you sure..?')) {
			cek_data();
		}
	} else if (rasbnsi.value == '') {
		alert('Kehadiran is obligatory');
	} else {
		//alert('test');
		cek_data();
	}

}
function editDetail(krywnId, shft, absn, jm, jm2, jm3, jm4, ket, catu, penalty, premi, insentif, premInsentif, prm, hk, insentiflibur,noakun,alokasi, umr, tipekary,noref,kodekegiatan) {
	document.getElementById('krywnId').disabled = true;
	document.getElementById('krywnId').value = krywnId;
	document.getElementById('shiftId').value = shft;
	document.getElementById('absniId').value = absn;
	document.getElementById('dendakehadiran').value = penalty;
	document.getElementById('premiInsentif').value = premInsentif;
	document.getElementById('insentiflibur').value = insentiflibur;
	document.getElementById('premi').value = premi;
	document.getElementById('insentif').value = insentif;
	document.getElementById('noakun').value = noakun;
	document.getElementById('alokasi').value = alokasi;
	document.getElementById('kodekegiatan').value = kodekegiatan;

	ct = document.getElementById('catu');
	for (x = 0; x < ct.length; x++) {
		if (ct.options[x].value == catu) {
			ct.options[x].selected = true;
		}
	}
	ct3 = document.getElementById('premiPil');
	for (x55 = 0; x55 < ct3.length; x55++) {
		if (ct3.options[x55].value == prm) {
			ct3.options[x55].selected = true;
		}
	}
	jam = jm.split(':');
	jam2 = jm2.split(':');
	jam3 = jm3.split(':');
	jam4 = jm4.split(':');
	
	document.getElementById('jmId').value = jam[0];
	document.getElementById('mntId').value = jam[1];
	document.getElementById('jmId2').value = jam2[0];
	document.getElementById('mntId2').value = jam2[1];
	document.getElementById('jmId3').value = jam3[0];
	document.getElementById('mntId3').value = jam3[1];
	document.getElementById('jmId4').value = jam4[0];
	document.getElementById('mntId4').value = jam4[1];
	document.getElementById('ktrng').value = ket;
	document.getElementById('jmlHk').value = hk;
	document.getElementById('rupiahhk').value = umr;
	document.getElementById('proses').value = 'updateData';

	setValue2('krywnId',krywnId);
	setValue2('absniId',absn);
	setValue2('noakun',noakun);
	setValue2('kodekegiatan',kodekegiatan);
	
	setValue2('jmId',jam[0]);
	setValue2('mntId',jam[1]);
	setValue2('jmId2',jam2[0]);
	setValue2('mntId2',jam2[1]);
	setValue2('jmId3',jam3[0]);
	setValue2('mntId3',jam3[1]);
	setValue2('jmId4',jam4[0]);
	setValue2('mntId4',jam4[1]);
	
	if(tipekary=='4'){
		document.getElementById('rupiahhk').style.display = "";
	}else{
		document.getElementById('rupiahhk').style.display = "none";
	}
	
	if(absnId=='H'){
		document.getElementById('jmlHk').disabled = false;
		document.getElementById('rupiahhk').disabled = false;
	}else{
		document.getElementById('jmlHk').disabled = true;
		document.getElementById('rupiahhk').disabled = true;
	}

	if(noref != ''){
		document.getElementById('jmlHk').disabled = true;
		document.getElementById('shiftId').disabled = true;
		document.getElementById('absniId').disabled = true;
		document.getElementById('rupiahhk').disabled = true;

		document.getElementById('jmId').disabled = true;
		document.getElementById('mntId').disabled = true;
		document.getElementById('jmId2').disabled = true;
		document.getElementById('mntId2').disabled = true;
		document.getElementById('jmId3').disabled = true;
		document.getElementById('mntId3').disabled = true;
		document.getElementById('jmId4').disabled = true;
		document.getElementById('mntId4').disabled = true;
	}
}

/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
function deleteDetail(id) {
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;

	var detKode = kdorg + "###" + tgl;
	var rkrywn = document.getElementById('krywnId_' + id);
	param = "proses=detail_delete";
	param += "&absnId=" + detKode;
	param += "&krywnId=" + rkrywn.value;
	//alert(param);
	tujuan = 'sdm_slave_absen_detail.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
	if (confirm('Deleting, are you sure..?')) {
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
		newRow.innerHTML += "<td><select id='krywnId_" + numRow + "' type='text' style='width:150px' />" + optIsi + "</select></td><td>" + "<input id='shiftId_" + numRow + "' type='text' class='myinputtext' value='' onkeypress='return tanpa_kutip(event)' style='width:120px' /></td><td><select id='absniId_" + numRow + "' type='text' style='width:100px' />" + optAbsn + "</select></td>" + "<td><select id='jmId_" + numRow + "' type='text' />" + optJm + "</select>:<select id='mntId_" + numRow + "' type='text' />" + optMnt + "</select>" + "<td>" + "<input id='ktrng_" + numRow + "' type='text' class='myinputtext' style='width:150px' value='' onkeypress='return tanpa_kutip(event)' /></td>" + "<td><img id='detail_add_" + numRow + "' title='Tambah' class=zImgBtn onclick=\"addDetail('" + numRow + "')\" src='images/save.png'/>" + "&nbsp;<img id='detail_delete_" + numRow + "' />" + "&nbsp;<img id='detail_pass_" + numRow + "' />" + "</td>";
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
			document.getElementById('krywnId_' + id).disabled = true;
		}
		delImg.setAttribute('src', 'images/delete_32.png');

	} else {
		alert('DOM Definition Error');
	}
}
function showTmbl() {
	pros = document.getElementById('proses').value;
	if (pros != 'updateData') {
		document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button><button class=mybutton onclick=reset_data()>" + nmTmblCancel + "</button>";
	} else {
		document.getElementById('tombol').innerHTML = "<button class=mybutton onclick=frm_aju()>" + nmTmblDone + "</button>";
	}
}
function cek_data() {
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	var detKode = kdorg + "###" + tgl;
	var period = document.getElementById('periode').value;
	var rkrywn = document.getElementById('krywnId').value;
	var rshft = document.getElementById('shiftId');
	var rasbnsi = document.getElementById('absniId');
	var rjm = document.getElementById('jmId').value;
	var rmnt = document.getElementById('mntId').value;
	var jam = rjm + ":" + rmnt;
	var rjm2 = document.getElementById('jmId2').value;
	var rmnt2 = document.getElementById('mntId2').value;
	var jam2 = rjm2 + ":" + rmnt2;
	var rjm3 = document.getElementById('jmId3').value;
	var rmnt3 = document.getElementById('mntId3').value;
	var jam3 = rjm3 + ":" + rmnt3;
	var rjm4 = document.getElementById('jmId4').value;
	var rmnt4 = document.getElementById('mntId4').value;
	var jam4 = rjm4 + ":" + rmnt4;
	var rket = document.getElementById('ktrng');
	var catu = document.getElementById('catu').value;

	// insentifkehadiran = document.getElementById('insentifkehadiran').value;
	insentiflibur = document.getElementById('insentiflibur').value;
	premidt = document.getElementById('premiInsentif').value;
	var ins = document.getElementById('insentif').value;
	var prm = document.getElementById('premi').value;
	var noakun = document.getElementById('noakun').value;
	var alokasi = document.getElementById('alokasi').value;
	var kodekegiatan = document.getElementById('kodekegiatan').value;
	var period = document.getElementById('periode').value;
	var dendakehadiran = document.getElementById('dendakehadiran').value;
	if (dendakehadiran == '')
		dendakehadiran = 0;
	var jmlHk = document.getElementById('jmlHk').value;
	pros = document.getElementById('proses').value;
	if (pros != 'updateData') {
		param = "proses=cekData";
	} else {
		param = "proses=" + pros;
	}
	
	//alert(param);
	//param = "proses=cekData";
	param += "&absnId=" + detKode;
	param += "&krywnId=" + rkrywn;
	param += "&shifTid=" + rshft.value;
	param += "&asbensiId=" + rasbnsi.value;
	param += "&Jam=" + jam;
	param += "&Jam2=" + jam2;
	param += "&Jam3=" + jam3;
	param += "&Jam4=" + jam4;
	param += "&period=" + period;
	param += "&ket=" + rket.value;
	param += "&catu=" + catu;
	param += "&premi=" + prm + "&insentif=" + ins + '&premidt=' + premidt;
	param += "&dendakehadiran=" + dendakehadiran + '&jmlHk=' + jmlHk;
	param += "&insentiflibur=" + insentiflibur;
	param += "&noakun=" + noakun;
	param += "&alokasi=" + alokasi;
	param += "&kodekegiatan=" + kodekegiatan;
	tujuan = 'sdm_slave_absensi.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					bersihFormDetail();
					showTmbl();
					loadDetail();
					status_inputan = 1;

					tglAbsennya = tgl.split("-")	
					tanggal = tglAbsennya[2] + tglAbsennya[1] + tglAbsennya[0];
					// showuploadV2(tanggal);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function bersihFormDetail() {
	var krywanId = document.getElementById('krywnId').value;
	var param = 'krywnId=' + krywanId + '&proses=checkSecurity';
	var tujuan = 'sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					var res = con.responseText;
					var resarr = res.split('##');
					if (resarr[0] == '1') {
						if (parseInt(resarr[4]) > 0) {
							document.getElementById('shiftId').value = resarr[1];
							if (resarr[1] == 'L') {
								document.getElementById('absniId').value = 'L';
								setValue2('absniId','L');
								//document.getElementById('absniId').disabled=true;
								document.getElementById('jmId').value = '00';
								document.getElementById('mntId').value = '00';
								document.getElementById('jmId2').value = '00';
								document.getElementById('mntId2').value = '00';
							} else {
								document.getElementById('absniId').selectedIndex = 0;
								document.getElementById('absniId').disabled = false;
								setValue2('absniId',null);
								document.getElementById('jmId').value = resarr[2].substr(0, 2);
								document.getElementById('mntId').value = resarr[2].substr(3, 2);
								document.getElementById('jmId2').value = resarr[3].substr(0, 2);
								document.getElementById('mntId2').value = resarr[3].substr(3, 2);
							}
							document.getElementById('ktrng').value = '';
							document.getElementById('proses').value = 'insert';
							document.getElementById('jmId4').value = '00';
							document.getElementById('mntId4').value = '00';
							document.getElementById('jmId3').value = '00';
							document.getElementById('mntId3').value = '00';
							document.getElementById('premiInsentif').value = 0;
							document.getElementById('insentiflibur').value = 0;
							document.getElementById('insentif').value = 0;
							document.getElementById('jmlHk').value = 0;
							document.getElementById('dendakehadiran').value = '0';
						} else {
							/*
							document.getElementById('absniId').disabled=false;
							alert('jadwal security karyawan dengan nama '+resarr[5]+' tidak ada')
							document.getElementById('krywnId').value='';
							document.getElementById('shiftId').value='';
							document.getElementById('absniId').selectedIndex=0;
							document.getElementById('ktrng').value='';
							document.getElementById('proses').value='insert';
							document.getElementById('jmId').value='00';
							document.getElementById('mntId').value='00';
							document.getElementById('jmId2').value='00';
							document.getElementById('mntId2').value='00';
							document.getElementById('jmId3').value='00';
							document.getElementById('mntId3').value='00';
							document.getElementById('jmId4').value='00';
							document.getElementById('mntId4').value='00';
							document.getElementById('premiInsentif').value=0;
							document.getElementById('insentiflibur').value=0;
							document.getElementById('insentif').value=0;
							document.getElementById('jmlHk').value=0;
							document.getElementById('dendakehadiran').value='0';
							 */
						}
					} else {
						document.getElementById('absniId').disabled = false;
						document.getElementById('shiftId').value = '';
						document.getElementById('absniId').selectedIndex = 0;
						setValue2('absniId',null);
						document.getElementById('ktrng').value = '';
						document.getElementById('proses').value = 'insert';
						document.getElementById('jmId').value = '00';
						document.getElementById('mntId').value = '00';
						document.getElementById('jmId2').value = '00';
						document.getElementById('mntId2').value = '00';
						document.getElementById('jmId3').value = '00';
						document.getElementById('mntId3').value = '00';
						document.getElementById('jmId4').value = '00';
						document.getElementById('mntId4').value = '00';
						setValue2('jmId','00');
						setValue2('mntId','00');
						setValue2('jmId2','00');
						setValue2('mntId2','00');
						setValue2('jmId3','00');
						setValue2('mntId3','00');
						setValue2('jmId4','00');
						setValue2('mntId4','00');
						document.getElementById('premiInsentif').value = 0;
						document.getElementById('insentiflibur').value = 0;
						document.getElementById('insentif').value = 0;
						document.getElementById('jmlHk').value = 0;
						document.getElementById('dendakehadiran').value = '0';
						document.getElementById('rupiahhk').value = '0';
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loadDetail() {
	kdOrg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tglAbsn = document.getElementById('tglAbsen').value;
	tujuan = 'sdm_slave_absen_detail.php';
	param = 'kdOrg=' + kdOrg + '&tgAbsn=' + tglAbsn + '&proses=loadDetail';
	//alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contentDetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}
function loadData() {
	kdorg = document.getElementById('kdOrgCari').value;
	tgl = document.getElementById('tgl_cari').value;


	param = 'proses=loadNewData';
	param += '&kdorg=' + kdorg + '&tgl=' + tgl;
	tujuan = 'sdm_slave_absensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					//return;
					document.getElementById('contain').innerHTML = con.responseText;
					leftFixedTable();
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
	tujuan = 'sdm_slave_absensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function fillField(kdorg, tgl, period) {
	tmp = kdorg + "###" + tgl;
	document.getElementById('kdOrg').value = kdorg;
	document.getElementById('tglAbsen').value = tgl;
	document.getElementById('periode').value = period;
	param = 'absnId=' + tmp;
	param += "&proses=createTable";
	param += "&tgAbsn="+tgl;
	tujuan = 'sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					lockForm();

					//alert(con.responseText);
					document.getElementById('listData').style.display = 'none';
					document.getElementById('headher').style.display = 'block';
					document.getElementById('detailEntry').style.display = 'block';
					var detailDiv = document.getElementById('detailIsi');
					detailDiv.innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					
					status_inputan = 1;
					statFrm = 1;
					showTmbl();
					loadDetail();
					document.getElementById('tmbLheader').innerHTML = '';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delDataIn(kdorg, tgl) {
	kdtmp = kdorg;
	tgltmp = tgl;
	absnId = kdtmp + "###" + tgltmp;
	param = 'absnId=' + absnId + '&proses=delData';
	tujuan = 'sdm_slave_absensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
function delDetail(kdorg, tgl, krynid) {
	kdtmp = kdorg;
	tgltmp = tgl;
	absnId = kdtmp + "###" + tgltmp;
	krywnId = krynid;
	param = 'absnId=' + absnId + '&proses=delDetail' + '&krywnId=' + krywnId;
	tujuan = 'sdm_slave_absensi.php';

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
	if (confirm("Deleting, are you sure..?"))
		post_response_text(tujuan, param, respog);
}
function delData(kdorg, tgl) {
	kdtmp = kdorg;
	tgltmp = tgl;
	absnId = kdtmp + "###" + tgltmp;
	param = 'absnId=' + absnId + '&proses=delData';
	tujuan = 'sdm_slave_absensi.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Deleting, are you sure..?"))
		post_response_text(tujuan, param, respog);
}
statFrm = 0;
function frm_aju() {

	if (statFrm == 0) {
		if (confirm("Done, are you sure..?")) {
			displayList();
		}
	} else if (statFrm == 1) {
		if (confirm("Done, are you sure..?")) {
			displayList();
		}
	}
}
function reset_data() {
	if (statFrm == 0) {
		if (confirm("Canceling, are you sure..?")) {
			kdorg = document.getElementById('kdOrg').value;
			tgl = document.getElementById('tglAbsen').value;
			delDataIn(kdorg, tgl);
		}
	} else if (statFrm == 1) {
		displayList();
	}

}
function cariAsbn() {
	
	
	kdorg = document.getElementById('kdOrgCari').value;
	tgl = document.getElementById('tgl_cari').value;

	id = kdorg + "###" + tgl;
	param = 'absnId=' + id + '&proses=cariAbsn';
	tujuan = 'sdm_slave_absensi.php';
	post_response_text(tujuan, param, respog);
	//alert(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listData').style.display = 'block';
					document.getElementById('headher').style.display = 'none';
					document.getElementById('detailEntry').style.display = 'none';
					document.getElementById('contain').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPremiTetap() {
	premiPilidt = document.getElementById('premiPil').options[document.getElementById('premiPil').selectedIndex].value;
	if (premiPilidt == 1) {
		prd = document.getElementById('tglAbsen').value;
		karyId = document.getElementById('krywnId').options[document.getElementById('krywnId').selectedIndex].value;
		var rjm = document.getElementById('jmId').options[document.getElementById('jmId').selectedIndex].value;
		var rmnt = document.getElementById('mntId').options[document.getElementById('mntId').selectedIndex].value;
		var jam = rjm + ":" + rmnt;
		var rjm2 = document.getElementById('jmId2').options[document.getElementById('jmId2').selectedIndex].value;
		var rmnt2 = document.getElementById('mntId2').options[document.getElementById('mntId2').selectedIndex].value;
		var jam2 = rjm2 + ":" + rmnt2;
		kdsb = document.getElementById('absniId');
		kdsb = kdsb.options[kdsb.selectedIndex].value;
		param = 'absnId=' + kdsb + '&proses=getPremi' + '&jmMulai=' + jam;
		zImgBtnH
		param += '&jamPlg=' + jam2 + '&tglDt=' + prd + '&karyId=' + karyId;
		tujuan = 'sdm_slave_absensi.php';
	} else {
		document.getElementById('insentif').value = '';
		document.getElementById('premiInsentif').value = '';
		document.getElementById('premi').value = '';
	}
	post_response_text(tujuan, param, respog);
	//alert(param);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (premiPilidt == 1) {
						dert = con.responseText.split("####");
						document.getElementById('premiInsentif').value = dert[0];
						document.getElementById('insentif').value = dert[1];
						document.getElementById('premi').value = dert[2];
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function saveHariLibur() {
	jnlibur = document.getElementById('jlibur').value;
	tgllibur = document.getElementById('tgllibur').value;
	tipekary = document.getElementById('tipekary').value;
	kodeorg = document.getElementById('kodeorghm').value;
	divisi = document.getElementById('divisihm').value;
	param = "";
	param += '&proses=simpan';
	param += '&jnlibur=' + jnlibur + '&tgllibur=' + tgllibur+'&tipekary='+tipekary+'&kodeorg='+kodeorg;
	param += '&divisi=' + divisi;
	// alert(param);
	// return;
	tujuan = 'sdm_slave_absenLibur.php';
	if (confirm('Are you sure..?')) {
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
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisi() {
	kodeorg = document.getElementById('kodeorghm').value;
	param = "";
	param += '&proses=getdivisi';
	param += '&kodeorg='+kodeorg;
	tujuan = 'sdm_slave_absenLibur.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisihm').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getHk(sumber) {
	absnId    = document.getElementById('absniId').value;
	periode   = document.getElementById('periode').value;
	karyawanid= document.getElementById('krywnId').value;
	param = 'absnId=' + absnId + '&proses=getHk';
	param += '&periode='+periode;
	param += '&karyawanid='+karyawanid;
	tujuan = 'sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(absnId!=''){						
						e = con.responseText.split("####");
						// if (e[1] == '1') {
						// 	document.getElementById('premiInsentif').disabled = false;
						// } else {
						// 	document.getElementById('premiInsentif').disabled = true;
						// }

						// if(absnId!='H' && absnId!='WFH' && absnId!='HL' && absnId!='H/2' && absnId!='NK'){
						// 	document.getElementById('premiInsentif').value = 0;
						// 	document.getElementById('premiInsentif').disabled = true;
						// }else{
							document.getElementById('premiInsentif').disabled = false;
						// }

						if(sumber=='hk'){
							jlhhk = document.getElementById('jmlHk').value;
							upah = parseFloat(e[2])*parseFloat(jlhhk);
							if(isNaN(upah)){upah=0;}
							document.getElementById('rupiahhk').value = upah;
						}else if(sumber=='upah'){
							upah = document.getElementById('rupiahhk').value;
							jlhhk = parseFloat(upah)/parseFloat(e[2]);
							if(isNaN(jlhhk)){jlhhk=0;}
							document.getElementById('jmlHk').value=jlhhk;
						}else{							
							document.getElementById('jmlHk').value = e[0];
							upah = parseFloat(e[2])*parseFloat(e[0]);
							document.getElementById('rupiahhk').value = upah;
						}
						if(absnId=='H'){
							jlhhk = document.getElementById('jmlHk').value;
							// if(jlhhk==0){
							// 	alertify.alert("Jumlah HK tidak boleh kosong.");
							// 	document.getElementById('jmlHk').value = e[0];
							// 	upah = parseFloat(e[2])*parseFloat(e[0]);
							// 	document.getElementById('rupiahhk').value = upah;
							// }
							document.getElementById('jmlHk').disabled = false;
							document.getElementById('rupiahhk').disabled = false;
						}else{
							document.getElementById('jmlHk').disabled = true;
							document.getElementById('rupiahhk').disabled = true;
						}
						
						
						if(e[3]=='4'){
							document.getElementById('rupiahhk').style.display = "";
						}else{
							document.getElementById('rupiahhk').style.display = "none";
							document.getElementById('jmlHk').disabled = true;
							document.getElementById('rupiahhk').disabled = true;
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showuploadV2(tanggal){
	karyawanid = document.getElementById('krywnId').value;
	tglabsen = document.getElementById('tglAbsen').value;
	tglAbsennya = tglabsen.split("-")
	
	if(tanggal == "") {
		tanggal = tglAbsennya[2] + tglAbsennya[1] + tglAbsennya[0];
	}

	if(karyawanid == "") {
		alert("Pastikan memilih Karyawan terlebih dahulu");
		return
	}

	var dendakehadiran = document.getElementById('dendakehadiran').value;
	if (dendakehadiran == '') 
		dendakehadiran = 0;

	var rasbnsi = document.getElementById('absniId');

	if (rasbnsi.value == '') {
		alert('Kehadiran is obligatory');
		return;
	} 

	ev = 'event';
	//showformupload(ev);
	param='proses=showupload&karyawanid='+karyawanid+'&tanggal='+tanggal;
	
	tujuan='sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('600px','300px');
					notransaksi = tanggal+karyawanid
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function showupload(karyawanid,tanggal){
	ev = 'event';
	//showformupload(ev);
	param='proses=showupload&karyawanid='+karyawanid+'&tanggal='+tanggal;
	
	tujuan='sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('600px','300px');
					notransaksi = tanggal+karyawanid
					loadfiles(notransaksi);
				}
			}else {
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

function submitfile(notransaksi) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alertify.alert("Upload file has been empty.");
		return false;
	}
	if(notransaksi==''){
		alertify.alert("Nomor transaksi tidak ditemukan.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').style.display="none";
	//tambahan progress bar
	con.upload.addEventListener("progress", progressHandler, false);
	con.addEventListener("load", completeHandler, false);
	con.addEventListener("error", errorHandler, false);
	con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "sdm_slave_absen_detail.php?proses=submitfile", true);
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
					alertify.alert('Uploaded Success.');
					document.getElementById('btnsubmit').style.display="";
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
	param = 'proses=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'sdm_slave_absen_detail.php';
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
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
					bersihFormDetail();
					showTmbl();
					loadDetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile) {
	param = "proses=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'sdm_slave_absen_detail.php';
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


function formupload() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewupload style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(idfile,sumber) {
	//formupload();
	param = 'proses=viewfile&idfile=' + idfile;
	tujuan = 'sdm_slave_absen_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showuploadperkaryawan(kodeorg,tanggal){
	ev = 'event';
	//showformupload(ev);
	param='proses=showuploadperkaryawan';
	param+='&kodeorgnya='+kodeorg;
	param+='&tanggal='+tanggal;
	
	tujuan='sdm_slave_absensi.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('1200px','600px');
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function addDetailUploadAll() {

	crt = document.getElementById('proses');
	//	alert(crt.value);
	kdorg = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	tgl = document.getElementById('tglAbsen').value;

	var detKode = kdorg + "###" + tgl;
	var period = document.getElementById('periode').value;
	var rkrywn = document.getElementById('krywnId');
	var rshft = document.getElementById('shiftId');
	var rasbnsi = document.getElementById('absniId');
	var rjm = document.getElementById('jmId');
	var rmnt = document.getElementById('mntId');
	var jam = rjm.value + ":" + rmnt.value;
	var rket = document.getElementById('ktrng');
	var catu = document.getElementById('catu').options[document.getElementById('catu').selectedIndex].value;

	var dendakehadiran = document.getElementById('dendakehadiran').value;
	if (dendakehadiran == '')
		dendakehadiran = 0;
	//addSession();
	//var id_user = trim(document.getElementById('user_id').value);
	//alert(rasbnsi.value);
	if (status_inputan == 0) {
		if (confirm('Add detail, are you sure..?')) {
			cek_dataUploadAll();
		}
	} else if (rasbnsi.value == '') {
		alert('Kehadiran is obligatory');
	} else {
		//alert('test');
		cek_dataUploadAll();
	}

}

function cek_dataUploadAll() {
	kdorg = document.getElementById('kdOrg').value;
	tgl = document.getElementById('tglAbsen').value;
	var detKode = kdorg + "###" + tgl;
	var period = document.getElementById('periode').value;
	var rkrywn = document.getElementById('krywnId').value;
	var rshft = document.getElementById('shiftId');
	var rasbnsi = document.getElementById('absniId');
	var rjm = document.getElementById('jmId').value;
	var rmnt = document.getElementById('mntId').value;
	var jam = rjm + ":" + rmnt;
	var rjm2 = document.getElementById('jmId2').value;
	var rmnt2 = document.getElementById('mntId2').value;
	var jam2 = rjm2 + ":" + rmnt2;
	var rjm3 = document.getElementById('jmId3').value;
	var rmnt3 = document.getElementById('mntId3').value;
	var jam3 = rjm3 + ":" + rmnt3;
	var rjm4 = document.getElementById('jmId4').value;
	var rmnt4 = document.getElementById('mntId4').value;
	var jam4 = rjm4 + ":" + rmnt4;
	var rket = document.getElementById('ktrng');
	var catu = document.getElementById('catu').value;

	// insentifkehadiran = document.getElementById('insentifkehadiran').value;
	insentiflibur = document.getElementById('insentiflibur').value;
	premidt = document.getElementById('premiInsentif').value;
	var ins = document.getElementById('insentif').value;
	var prm = document.getElementById('premi').value;
	var noakun = document.getElementById('noakun').value;
	var alokasi = document.getElementById('alokasi').value;
	var kodekegiatan = document.getElementById('kodekegiatan').value;
	var period = document.getElementById('periode').value;
	var dendakehadiran = document.getElementById('dendakehadiran').value;
	if (dendakehadiran == '')
		dendakehadiran = 0;
	var jmlHk = document.getElementById('jmlHk').value;
	pros = document.getElementById('proses').value;
	if (pros != 'updateData') {
		param = "proses=cekDataUploadAll";
	} else {
		param = "proses=" + pros;
	}
	
	//alert(param);
	//param = "proses=cekData";
	param += "&absnId=" + detKode;
	param += "&krywnId=" + rkrywn;
	param += "&shifTid=" + rshft.value;
	param += "&asbensiId=" + rasbnsi.value;
	param += "&Jam=" + jam;
	param += "&Jam2=" + jam2;
	param += "&Jam3=" + jam3;
	param += "&Jam4=" + jam4;
	param += "&period=" + period;
	param += "&ket=" + rket.value;
	param += "&catu=" + catu;
	param += "&premi=" + prm + "&insentif=" + ins + '&premidt=' + premidt;
	param += "&dendakehadiran=" + dendakehadiran + '&jmlHk=' + jmlHk;
	param += "&insentiflibur=" + insentiflibur;
	param += "&noakun=" + noakun;
	param += "&alokasi=" + alokasi;
	param += "&kodekegiatan=" + kodekegiatan;
	tujuan = 'sdm_slave_absensi.php';
	// alert(param);
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					bersihFormDetail();
					showTmbl();
					loadDetail();
					status_inputan = 1;

					tglAbsennya = tgl.split("-")	
					tanggal = tglAbsennya[2] + tglAbsennya[1] + tglAbsennya[0];
					// showuploadV2(tanggal);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}