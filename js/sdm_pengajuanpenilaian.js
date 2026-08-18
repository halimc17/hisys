function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}

function displayList() {
	document.getElementById('tglevaluasicr').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loadData(0);
}

function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('tglevaluasi').disabled = false;
	document.getElementById('unit').disabled = false;
	document.getElementById('unit').value = '';
	document.getElementById('karyawan').disabled = false;
	document.getElementById('karyawan').value = '';
}

function detail(tipe) {
	unit = document.getElementById('unit').value;
	tglevaluasi = document.getElementById('tglevaluasi').value;
	karyawan = document.getElementById('karyawan').value;
	if (unit == '' || tglevaluasi == '' || karyawan == '') {
		alert('Lengkapi Pengisian');
		return;
	}

	document.getElementById('unit').disabled = true;
	document.getElementById('karyawan').disabled = true;
	param = 'method=detail';
	param += '&tglevaluasi=' + tglevaluasi + '&unit=' + unit + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					if(tipe=='edit'){
						editdtnilai(totRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkar(unit, karyawan) {
	if (typeof unit == 'undefined' || unit == 0) {
		unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	}
	param = 'unit=' + unit + '&method=getkar';
	if (typeof karyawan == 'undefined' || karyawan != 0) {
		param += '&karyawan=' + karyawan;
	}
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('karyawan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetail(unit, tglevaluasi, karyawan) {

	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('unit').disabled = true;
	document.getElementById('tglevaluasi').disabled = true;
	document.getElementById('karyawan').disabled = true;

	param = 'method=loaddatadetail';
	param += '&tglevaluasi=' + tglevaluasi + '&unit=' + unit + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail() {
	kriteria = document.getElementById('kriteria').value;
	karyawan = document.getElementById('karyawanid').value;
	tgleva = document.getElementById('tgleva').value;
	unit = document.getElementById('unitid').value;
	penilaian = document.getElementById('penilaian').value;
	nilai = document.getElementById('nilai').value;
	method = document.getElementById('method').value;

	if (kriteria == '' || penilaian == '' || nilai == '') {
		alert('Semua data harus diisi');
		return;
	}

	param = 'kriteria=' + kriteria + '&penilaian=' + penilaian + '&nilai=' + nilai + '&karyawan=' + karyawan + '&tgleva=' + tgleva + '&unit=' + unit;
	param += '&method=' + method;

	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//clearpenilaian();
					//loaddatadetail(unit,tglevaluasi,karyawan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearpenilaian() {
	document.getElementById('kriteria').value = '';
	document.getElementById('penilaian').value = '';
	document.getElementById('nilai').selectedIndex = '0';
}

function loadData(num) {
	tglevaluasicr = document.getElementById('tglevaluasicr').value;

	param = 'method=loadData';
	param += '&page=' + num;

	if (tglevaluasicr != '') {
		param += '&tglevaluasicr=' + tglevaluasicr;
	}
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('container').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
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

function saveht() {
	kekuatan = document.getElementById('kekuatan').value;
	perbaikan = document.getElementById('perbaikan').value;
	catatan = document.getElementById('catatan').value;
	rekomendasi = document.getElementById('rekomendasi').value;
	ttd1 = document.getElementById('ttd1').value;
	ttd2 = document.getElementById('ttd2').value;
	ttd3 = document.getElementById('ttd3').value;
	tgleva = document.getElementById('tgleva').value;
	unit = document.getElementById('unit').value;
	karyawan = document.getElementById('karyawanid').value;
	method = document.getElementById('methodht').value;
	totRow = document.getElementById('totrows').value;
	var allData = '';
	for (dwc = 0; dwc < totRow; dwc++) {
		allData += "&arrNilai[" + dwc + "]=" + document.getElementById('nilai_' + dwc).value;
		allData += "&kdNilai[" + dwc + "]=" + document.getElementById('kdnil_' + dwc).value;
		allData += "&kom[" + dwc + "]=" + document.getElementById('kom_' + dwc).value;
	}
	if (rekomendasi == '') {
		alert('Warning : Rekomendasi harus dipilih.');
		return;
	}
	if (ttd1 == '') {
		alert('Warning : Penyetuju harus dipilih.');
		return;
	}
	if (ttd2 == '') {
		alert('Warning : HC dan GA Head harus dipilih.');
		return;
	}
	if (ttd3 == '') {
		alert('Gagal : HR Officer harus dipilih.');
		return;
	}

	param = 'kekuatan=' + kekuatan + '&perbaikan=' + perbaikan + '&catatan=' + catatan + '&rekomendasi=' + rekomendasi + '&tgleva=' + tgleva;
	param += '&ttd1=' + ttd1 + '&ttd2=' + ttd2 + '&ttd3=' + ttd3 + '&karyawan=' + karyawan + '&method=' + method + '&totRow=' + totRow + '&unit=' + unit;
	param += allData;
	//alert(param);
	// return;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetail();
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cleardetail() {
	document.getElementById('kekuatan').value = '';
	document.getElementById('perbaikan').value = '';
	document.getElementById('catatan').value = '';
	document.getElementById('rekomendasi').value = '';
	document.getElementById('ttd1').value = '';
	document.getElementById('ttd2').value = '';
	document.getElementById('ttd3').value = '';
}

function deldata(tglevaluasi, karyawan) {
	param = 'method=delete' + '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	if (confirm(' Anda yakin ingin menghapus pengajuan ini?')) {
		post_response_text(tujuan, param, respog);
	}
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

function form() {
	width = '';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}

function viewdetail(tglevaluasi, karyawan) {
	form();
	param = 'method=viewdetail' + '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function deldt(tglevaluasi, karyawan, penilaian) {
	param = 'method=deldt' + '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan + '&penilaian=' + penilaian;

	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(unit, tglevaluasi, karyawan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(tglevaluasi, karyawan) {
	param = 'method=ajukan' + '&tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	if (confirm('Anda yakin ingin mengajukan ini ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
					//loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form_ajukan(tglevaluasi,karyawan,unit,noid,numrow){
	width = '';
    height = '';
    content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
	
	param = 'method=form_ajukan' + '&tglevaluasi=' + tglevaluasi+ '&karyawan=' + karyawan+ '&numrow=' + numrow+ '&unit=' + unit+'&noid='+noid;
    tujuan = 'sdm_slave_pengajuanpenilaian.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containeraju').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan(){
	noid=document.getElementById('noid').innerHTML;
	karyawan=document.getElementById('form_karyawan').innerHTML;
	tglevaluasi=document.getElementById('form_tglevaluasi').innerHTML;
	kepada=document.getElementById('kepada').value;
    numrow=document.getElementById('numrow').value;
	param = 'method=ajukan' + '&karyawan=' + karyawan+ '&tglevaluasi=' + tglevaluasi+ '&kepada=' + kepada+ '&numrow=' + numrow+'&noid='+noid;
    
	if(kepada==''){
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					x = document.getElementById('tr_' + numrow);
					x.cells[7].innerHTML = '';
					// x.cells[7].innerHTML = '';
					// x.cells[8].innerHTML = '';
					alert('Success');
					loadData(0);
					closeDialog();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function editdt(unit,tglevaluasi,karyawan,kekuatan)
// {
//     //getkar(unit,karyawan);
//     document.getElementById('unit').value=unit;
//     document.getElementById('unit').disabled=true;
//     document.getElementById('tglevaluasi').value=tglevaluasi;
//     document.getElementById('karyawan').value=karyawan;
//     document.getElementById('karyawan').disabled=true;
//     document.getElementById('header').style.display='block';
//     document.getElementById('listData').style.display='none';
//     //document.getElementById('method').value='update';
//     unit=document.getElementById('unit').value;
//     karyawan=document.getElementById('karyawan').value;
//     tglevaluasi=document.getElementById('tglevaluasi').value;
//     param = 'method=detail';
//     param += '&tglevaluasi=' + tglevaluasi+'&unit=' + unit+'&karyawan=' + karyawan;
//     tujuan = 'sdm_slave_pengajuanpenilaian.php';
//     post_response_text(tujuan, param, respog);
//     function respog()
//     {
//         if (con.readyState == 4)
//         {
//             if (con.status == 200)
//             {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText))
//                 {
//                     alert(con.responseText);
//                 }
//                 else {
//                     document.getElementById('detail').style.display = 'block';
//                     document.getElementById('detail').innerHTML = con.responseText;
//                 }
//             }
//             else
//             {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }


function previewep(tglevaluasi, karyawan, ev) {
	param = 'tglevaluasi=' + tglevaluasi + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_eppdf.php?' + param;
	title = 'KPI';
	width = '1000';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);

}

function editdt(unit, tglevaluasi, karyawan) {
	document.getElementById('unit').value = unit;
	document.getElementById('tglevaluasi').value = tglevaluasi;
	document.getElementById('karyawan').value = karyawan;
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//editdtnilai();
	detail('edit');
}


function editdtnilai(totRow) {
	tglevaluasi = document.getElementById('tglevaluasi').value;
	karyawan = document.getElementById('karyawan').value;

	param = 'tglevaluasi=' + tglevaluasi;
	param += '&karyawan=' + karyawan;
	param += '&totRow=' + totRow;
	param += '&method=editdtnilai';
	tujuan = 'sdm_slave_pengajuanpenilaian.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = JSON.parse(con.responseText);
					document.getElementById('methodht').value='updateht';
					
					for(i=0; i<data.idjenispenilaian.length; i++){
						var tr = document.getElementById('row_'+data.idjenispenilaian[i]);//row_A1
						var select_ = tr.getElementsByTagName('select');
						var input_ = tr.getElementsByTagName('input');
						for(x=0; x<select_.length; x++){
							if(select_[x].getAttribute('for')=="nilai"){
								select_[x].value = data.nilai[i];
							}
						}
						for(x=0; x<input_.length; x++){
							if(input_[x].getAttribute('for')=="kom"){
								input_[x].value = data.kom[i];
							}
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