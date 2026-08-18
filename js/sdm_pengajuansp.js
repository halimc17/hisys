function add_new_data() {
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('persetujuan').style.display = 'none';
	document.getElementById('listdata').style.display = 'none';
	cancelheader();
}

function displayList() {
	document.getElementById('nopengajuancr').value = '';
	document.getElementById('tglcr').value = '';
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('persetujuan').style.display = 'none';
	loadData(0);
}

function cancelheader() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('kodeorg').value = '';
	document.getElementById('karyawan').disabled = false;
	document.getElementById('jenissurat').value = '';
	document.getElementById('jenissurat').disabled = false;
	document.getElementById('karyawan').value = '';
	document.getElementById('nopengajuan').value = '';
	document.getElementById('sifatpelanggaran').value = '';
	document.getElementById('pembuat').value = '';
	document.getElementById('pembuat').disabled = false;
	document.getElementById('sifatpelanggaran').disabled = false;
	document.getElementById('tanggaldari').value = '';
	document.getElementById('tanggaldari').disabled = false;
	document.getElementById('tanggalsampai').value = '';
	document.getElementById('tanggalsampai').disabled = false;
}

function savepengajuan() {

	nopengajuan = document.getElementById('nopengajuan').value;
	kodeorg = document.getElementById('kodeorg').value;
	karyawan = document.getElementById('karyawan').value;
	tglpengajuan = document.getElementById('tglpengajuan').value;
	pembuat = document.getElementById('pembuat').value;
	sifatpelanggaran = document.getElementById('sifatpelanggaran').value;
	tanggaldari = document.getElementById('tanggaldari').value;
	tanggalsampai = document.getElementById('tanggalsampai').value;
	jenissurat = document.getElementById('jenissurat').value;
	if (kodeorg == '' || karyawan == '' || tglpengajuan == '' || pembuat == '' || jenissurat == '' || sifatpelanggaran == '') {
		alert('Lengkapi Pengisian');
		return;
	}

	if (jenissurat == 'SKR') {
		if (tanggaldari == '' || tanggalsampai == '') {
			alert('Tanggal Skorsing harus diisi.');
			return;
		}
	}

	if (jenissurat == 'PHK') {
		if (tanggaldari == '') {
			alert('Tanggal dari harus diisi.');
			return;
		}
	}

	param = 'method=savepengajuan';
	param += '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg + '&karyawan=' + karyawan + '&tglpengajuan=' + tglpengajuan + '&pembuat=' + pembuat + '&sifatpelanggaran=' + sifatpelanggaran;
	param += '&tanggaldari=' + tanggaldari + '&tanggalsampai=' + tanggalsampai + '&jenissurat=' + jenissurat;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('nopengajuan').value = con.responseText;
					detail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getjenispel(jenissurat, jenispelanggaran) {
	if (jenissurat == 0) {
		jenissurat = document.getElementById('jenissurat').options[document.getElementById('jenissurat').selectedIndex].value;
	}
	param = 'jenissurat=' + jenissurat + '&method=getjenispel';
	if (jenispelanggaran != 0) {
		param += '&jenispelanggaran=' + jenispelanggaran;
	}
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('jenispelanggaran').innerHTML = con.responseText;
					getpelanggaran();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkar(kodeorg, karyawan, pembuat) {
	if (kodeorg == 0) {
		kodeorg = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
		pembuat = document.getElementById('pembuat').value;
	}

	param = 'kodeorg=' + kodeorg + '&method=getkar';
	if (karyawan != 0) {
		param += '&karyawan=' + karyawan;
	}

	param += '&pembuat=' + pembuat;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					$data = con.responseText.split('##');
					document.getElementById('pembuat').innerHTML = $data[1];
					document.getElementById('karyawan').innerHTML = $data[0];

					if (karyawan != 0) {
						detail();
					};

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getpelanggaran() {
	jenispelanggaran = document.getElementById('jenispelanggaran').options[document.getElementById('jenispelanggaran').selectedIndex].value;
	param = 'jenispelanggaran=' + jenispelanggaran + '&method=getpelanggaran';
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('pelanggaran').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detail() {
	kodeorg = document.getElementById('kodeorg').value;
	karyawan = document.getElementById('karyawan').value;
	nopengajuan = document.getElementById('nopengajuan').value;
	jenissurat = document.getElementById('jenissurat').value;
	param = 'method=detail';
	param += '&nopengajuan=' + nopengajuan + '&jenissurat=' + jenissurat + '&kodeorg=' + kodeorg + '&karyawan=' + karyawan;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					loaddatadetail(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetail() {
	kodeorg = document.getElementById('kodeorg').value;
	jenispelanggaran = document.getElementById('jenispelanggaran').value;
	nopengajuan = document.getElementById('nopengajuandt').value;
	method = document.getElementById('method').value;

	if (jenispelanggaran == '') {
		alert('Semua data harus diisi');
		return;
	}

	param = 'jenispelanggaran=' + jenispelanggaran + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	param += '&method=' + method;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					clearpenilaian();
					loaddatadetail(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearpenilaian() {
	document.getElementById('jenispelanggaran').value = '';
	document.getElementById('pelanggaran').innerHTML = '';
}

function loaddatadetail(nopengajuan) {

	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('nopengajuan').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('karyawan').disabled = true;
	document.getElementById('jenissurat').disabled = true;
	document.getElementById('pembuat').disabled = true;
	nopengajuan = document.getElementById('nopengajuan').value;

	param = 'method=loaddatadetail';
	param += '&nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loaddatadetailsp(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formpersetujuan(nopengajuan, kodeorg) {
	width = '';
	height = '';
	content = "<div id=containeraju style=\"width:100%;max-height:300px;overflow:auto;\"></div>";
	ev = 'event';
	title = "";
	showDialog2(title, content, width, height, ev);
	
	param = 'method=formpersetujuan' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	tujuan = 'sdm_slave_pengajuansp.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('persetujuan').style.display = 'block';
					// document.getElementById('detail').style.display = 'none';
					// document.getElementById('header').style.display = 'none';
					document.getElementById('containeraju').innerHTML = con.responseText;
					loaddatadetailtemb(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

// function simpan(){
// 	jenissurat=document.getElementById('jenissurat').options[document.getElementById('jenissurat').selectedIndex].value;
// 	jenispelanggaran=document.getElementById('jenispelanggaran').options[document.getElementById('jenispelanggaran').selectedIndex].value;
// 	kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
// 	karyawan=document.getElementById('karyawan').options[document.getElementById('karyawan').selectedIndex].value;
// 	persetujuan1=document.getElementById('persetujuan1').options[document.getElementById('persetujuan1').selectedIndex].value;
// 	persetujuan2=document.getElementById('persetujuan2').options[document.getElementById('persetujuan2').selectedIndex].value;
// 	nopengajuan=document.getElementById('nopengajuan').value;
// 	tglpengajuan=document.getElementById('tglpengajuan').value;
// 	mendengar=document.getElementById('mendengar').value;
// 	keterangan=document.getElementById('keterangan').value;
// 	//method=document.getElementById('method').value;
// 	param='jenissurat='+jenissurat+'&jenispelanggaran='+jenispelanggaran+'&kodeorg='+kodeorg+'&karyawan='+karyawan+'&persetujuan1='+persetujuan1;
// 	param+='&persetujuan2='+persetujuan2+'&nopengajuan='+nopengajuan+'&tglpengajuan='+tglpengajuan+'&method=insert';
// 	param+='&keterangan='+keterangan+'&mendengar='+mendengar;
// 	tujuan='sdm_slave_pengajuansp.php';

// 	post_response_text(tujuan, param, respog);
// 	function respog(){
// 		if(con.readyState==4){
// 			if (con.status == 200){
// 				busy_off();
//                 if (!isSaveResponse(con.responseText)){
// 					alert(con.responseText);
// 				}else{
//                     //alert(con.responseText);
// 					cancel();
// 					displayList();
// 				}
// 			}else{
// 				busy_off();
//                 error_catch(con.status);
// 			}
// 		}
// 	}
// }

function simpan() {

	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("nopengajuan", getValue('nopengajuanx'));
	formdata.append("persetujuan1", getValue('persetujuan1'));
	formdata.append("persetujuan2", getValue('persetujuan2'));
	formdata.append("method", getValue('methodht'));
	
	busy_on();
	document.getElementById('tombolsimpanhtx').disabled=true;
	
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_pengajuansp.php?", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					busy_off();
					document.getElementById('tombolsimpanhtx').disabled=false;
					cancel();
					cancelheader();
					displayList();
					closeDialog2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	document.getElementById('upload').value = '';
	document.getElementById('upload').disabled = false;
	document.getElementById("persetujuan1").selectedIndex = "0";
	document.getElementById("persetujuan2").selectedIndex = "0";
}

function loadData(num) {
	nopengajuancr = document.getElementById('nopengajuancr').value;
	tglcr = document.getElementById('tglcr').value;

	param = 'method=loadData';
	param += '&page=' + num;

	if (nopengajuancr != '') {
		param += '&nopengajuancr=' + nopengajuancr;
	}
	if (tglcr != '') {
		param += '&tglcr=' + tglcr;
	}
	tujuan = 'sdm_slave_pengajuansp.php';
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

// function fillfield(nopengajuan,kodeorg,karyawan,tglpengajuan,persetujuan1,persetujuan2,jenispelanggaran,jenissurat){
//   ljenissurat=document.getElementById('jenissurat');
//   for(a=0;a<ljenissurat.length;a++){
//     if(ljenissurat.options[a].value==jenissurat){
//       ljenissurat.options[a].selected=true;
//     }
//   }
//   ljenissurat.disabled=true;
//   lkodeorg=document.getElementById('kodeorg');
//   for(a=0;a<lkodeorg.length;a++){
//     if(lkodeorg.options[a].value==kodeorg){
//       lkodeorg.options[a].selected=true;
//     }
//   }
//   lkodeorg.disabled=true;
//   // karyawan=document.getElementById('karyawan');
//   // for(a=0;a<lkaryawan.length;a++){
//   //   if(lkaryawan.options[a].value==karyawan){
//   //     lkaryawan.options[a].selected=true;
//   //   }
//   // }

//   lpengajuan1=document.getElementById('persetujuan1');
//   for(a=0;a<lpengajuan1.length;a++){
//     if(lpengajuan1.options[a].value==persetujuan1){
//       lpengajuan1.options[a].selected=true;
//     }
//   }
//   lpengajuan2=document.getElementById('persetujuan2');
//   for(a=0;a<lpengajuan2.length;a++){
//     if(lpengajuan2.options[a].value==persetujuan2){
//       lpengajuan2.options[a].selected=true;
//     }
//   }
//   document.getElementById('nopengajuan').value=nopengajuan;
//   document.getElementById('tglpengajuan').value=tglpengajuan;
//   document.getElementById('upload').disabled=true;
//   // document.getElementById('mendengar').value=mendengar;
//   // document.getElementById('keterangan').value=keterangan;
//   document.getElementById('method').value='update';
//   document.getElementById('listdata').style.display='none';
//   document.getElementById('adddata').style.display='block';
//   getkar(kodeorg,karyawan);
//   getjenispel(jenissurat,jenispelanggaran);
//   document.getElementById('jenispelanggaran').disabled=true;
// }

function edit(nopengajuan, kodeorg, karyawan, tglpengajuan, jenissurat, pembuat, sifatpelanggaran, tanggaldari, tanggalsampai) {
	document.getElementById('nopengajuan').value = nopengajuan;
	document.getElementById('kodeorg').value = kodeorg;
	document.getElementById('karyawan').value = karyawan;
	document.getElementById('tglpengajuan').value = tglpengajuan;
	document.getElementById('jenissurat').value = jenissurat;
	document.getElementById('pembuat').value = pembuat;
	document.getElementById('sifatpelanggaran').value = sifatpelanggaran;
	document.getElementById('tanggaldari').value = tanggaldari;
	document.getElementById('tanggalsampai').value = tanggalsampai;
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	getkar(kodeorg, karyawan, pembuat);
}

function del(nopengajuan) {
	param = 'method=delete' + '&nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_pengajuansp.php';
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

function deldt(nopengajuan, jenispelanggaran) {
	param = 'method=deldt' + '&nopengajuan=' + nopengajuan + '&jenispelanggaran=' + jenispelanggaran;
	tujuan = 'sdm_slave_pengajuansp.php';
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
					loaddatadetail(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '700';
	height = '';
	content = "<fieldset><div id=containerd align=center style=\"width:680px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog1(title, content, width, height, ev);
}

function viewdetail(nopengajuan) {
	form();
	param = 'method=viewdetail' + '&nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_pengajuansp.php';
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

function previewsp(nopengajuan, ev) {
	param = 'nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_sppdf.php?' + param;
	title = nopengajuan;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function savedetailsp() {
	id = document.getElementById('id').value;
	sanksipelanggaran = document.getElementById('sanksipelanggaran').value;
	nopengajuan = document.getElementById('nopengajuan').value;
	methodsp = document.getElementById('methodsp').value;

	if (sanksipelanggaran == '') {
		alert('Semua data harus diisi');
		return;
	}

	param = 'sanksipelanggaran=' + sanksipelanggaran + '&nopengajuan=' + nopengajuan + '&id=' + id;
	param += '&method=' + methodsp;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('id').value = '';
					document.getElementById('sanksipelanggaran').value = '';
					document.getElementById('methodsp').value = 'insertsp';
					loaddatadetailsp(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailsp(nopengajuan) {

	param = 'method=loaddatadetailsp';
	param += '&nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetailsp').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editsp(id, sanksipelanggaran) {
	document.getElementById('id').value = id;
	document.getElementById('sanksipelanggaran').value = sanksipelanggaran;
	document.getElementById('methodsp').value = 'updatesp';
}

function delsp(nopengajuan, id) {
	param = 'method=delsp' + '&nopengajuan=' + nopengajuan + '&id=' + id;
	tujuan = 'sdm_slave_pengajuansp.php';
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
					loaddatadetailsp(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savedetailtemb() {
	idtemb = document.getElementById('idtemb').value;
	tembusan = document.getElementById('tembusan').value;
	nopengajuan = document.getElementById('nopengajuanx').value;
	methodtemb = document.getElementById('methodtemb').value;

	if (tembusan == '') {
		alert('Semua data harus diisi');
		return;
	}

	param = 'nopengajuan=' + nopengajuan + '&tembusan=' + tembusan + '&id=' + idtemb;
	param += '&method=' + methodtemb;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('idtemb').value = '';
					document.getElementById('tembusan').value = '';
					document.getElementById('methodtemb').value = 'inserttemb';
					loaddatadetailtemb(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddatadetailtemb(nopengajuan) {

	param = 'method=loaddatadetailtemb';
	param += '&nopengajuan=' + nopengajuan;
	tujuan = 'sdm_slave_pengajuansp.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetailtemb').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edittemb(id, tembusan) {
	document.getElementById('idtemb').value = id;
	document.getElementById('tembusan').value = tembusan;
	document.getElementById('methodtemb').value = 'updatetemb';
}

function deltemb(nopengajuan, id) {
	param = 'method=deltemb' + '&nopengajuan=' + nopengajuan + '&id=' + id;
	tujuan = 'sdm_slave_pengajuansp.php';
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
					loaddatadetailtemb(nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tgldis() {
	jenissurat = document.getElementById('jenissurat').value;
	if (jenissurat == 'SKR') {
		document.getElementById('tanggaldari').disabled = false;
		document.getElementById('tanggalsampai').disabled = false;
	} else if (jenissurat == 'PHK') {
		document.getElementById('tanggaldari').disabled = false;
		document.getElementById('tanggalsampai').disabled = true;
	} else {
		document.getElementById('tanggaldari').disabled = true;
		document.getElementById('tanggalsampai').disabled = true;
	}

}