function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function openlistperpt(e) {
	param = 'proses=getdivisi&perpt=perpt';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					
					if(e.checked == true){
						listpt = document.getElementById('listpt');
						listpt.style = "";
						listdivisi = document.getElementById('listdivisi');
						listdivisi.innerHTML = con.responseText;
						selectkaryawan('','perpt');
					}else{
						listpt = document.getElementById('listpt');
						listpt.style="display:none;";
						listdivisi = document.getElementById('listdivisi');
						listdivisi.innerHTML = "";
						selectkaryawan('','');
					}
					permandor = document.getElementById('permandor');
					filtername = document.getElementById('filtername');
					filtername.innerHTML = bahasa.divisi;
					if(permandor.checked == true){
						permandor.checked = false;
					}
					getSelect2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function openlistpermandor(e) {
	param = 'proses=getdivisi&perpt=permandor';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(e.checked == true){
						listpt = document.getElementById('listpt');
						listpt.style = "";
						listdivisi = document.getElementById('listdivisi');
						listdivisi.innerHTML = con.responseText;
						selectkaryawan('','permandor');
					}else{
						listpt = document.getElementById('listpt');
						listpt.style="display:none;";
						listdivisi = document.getElementById('listdivisi');
						listdivisi.innerHTML = "";
						selectkaryawan('','');
					}
					perpt = document.getElementById('perpt');
					filtername.innerHTML = bahasa.mandor;
					if(perpt.checked == true){
						perpt.checked = false;
					}
					getSelect2();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function selectkaryawan(lokasitugas,perpt) {
	param = 'proses=getkaryawan&perpt='+perpt+'&lokasitugas='+lokasitugas;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					karyawan = document.getElementById('karyawan');
					karyawan.innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getws() {
	external = document.getElementById('external').value;
	// if (external == 'external') {
	// 	document.getElementById('nodok').disabled = true;
	// } else {
	// 	document.getElementById('nodok').disabled = false;
	// }
	param = 'external=' + external + '&proses=getws';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					//document.getElementById('codeOrg').innerHTML=con.responseText;
					document.getElementById('kdTraksi').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveBarang() {
	trans_no = document.getElementById('trans_no').value;
	kodeBarang = document.getElementById('kodeBarang').value;
	jumlahBarang = document.getElementById('jumlahBarang').value;
	satuanBarang = document.getElementById('satuanBarang').value;
	keteranganBarang = document.getElementById('keteranganBarang').value;
	
	validate([
		["kodeBarang","Kode barang harus diisi."],
		["jumlahBarang","Jumlah barang harus diisi."],
	]);
	
	if(jumlahBarang <= 0){
		alertify.alert("Jumlah barang harus lebih besar dari 0 (nol).");
		return false;
	}
	
	param = 'trans_no=' + trans_no + '&kodeBarang=' + kodeBarang + '&jumlahBarang=' + jumlahBarang;
	param += '&satuanBarang=' + satuanBarang + '&keteranganBarang=' + keteranganBarang;
	param += "&proses=saveBarang";
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					//bersihdetail();
					bersihFormBarang();
					loadDetailBarang();
					//document.getElementById('containListBarang').style.display='block';
					//document.getElementById('contentDetail').innerHTML=con.responseText;
					// Success Response
					//alert(con.responseText);
					//document.getElementById('detailEntry').style.display='block';
					//document.getElementById('detailIsi').innerHTML=con.responseText;
					//document.getElementById('tmbLheader').innerHTML='';
					//lockForm();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bersihFormBarang() {
	document.getElementById('kodeBarang').value = '';
	document.getElementById('jumlahBarang').value = '';
	document.getElementById('satuanBarang').value = '';
	document.getElementById('keteranganBarang').value = '';
	document.getElementById('namaBarang').value = '';
}

function deleteBarang(trans_no, kodeBarang) {
	param = 'proses=deleteBarang' + '&trans_no=' + trans_no + '&kodeBarang=' + kodeBarang;
	tujuan = 'vhc_slave_service.php';
	alertify.confirm("Informasi","Anda yakin hapus material???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					loadDetailBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loadDetailBarang(firstload,refer) {

	if (typeof firstload == 'undefined') {
		firstload = false;
	}
	if(refer == 1){
		trans_no = document.getElementById('nopengajuan').value;
	}else{
		trans_no = document.getElementById('trans_no').value;
	}
	param = 'trans_no=' + trans_no;
	param += '&refer=' + refer;
	param += '&proses=loadDetailBarang';
	//alert(param);
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containListBarang').innerHTML = con.responseText;
					loadDetailKaryawan(refer);
					//  if(firstload)loadDetailPekerjaan(true);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveHeader() {
	//save header + buka input detail di sini
	codeOrg 		= document.getElementById('codeOrg').value;
	trans_no 		= document.getElementById('trans_no').value;
	nopengajuan 	= document.getElementById('nopengajuan').value;
	vhc_code 		= document.getElementById('vhc_code').value;
	kdTraksi 		= document.getElementById('kdTraksi').value;
	tgl_ganti 		= document.getElementById('tgl_ganti').value;
	tgl_keluar 		= document.getElementById('tgl_keluar').value;
	dwnTime 		= document.getElementById('dwnTime').value;
	kmmasuk 		= remove_comma_var(document.getElementById('kmmasuk').value);
	kmkeluar 		= remove_comma_var(document.getElementById('kmkeluar').value);
	tipeperbaikan 	= document.getElementById('tipeperbaikan').value;
	descDmg 		= document.getElementById('descDmg').value;
	terlambat 		= document.getElementById('terlambat').value;
	nmpemohon 		= document.getElementById('nmpemohon').value;
	ext 			= document.getElementById('external').value;
	proses 			= 'saveHeader';
	if(kmmasuk==''){kmmasuk=0;}
	if(kmkeluar==''){kmkeluar=0;}
	
	validate([
        ["codeOrg","Bengkel harus dipilih."],
        ["kdTraksi","Kode traksi harus dipilih."],
        ["vhc_code","Kode kendaraan harus dipilih."],
        ["tgl_ganti","Tanggal masuk harus diisi."],
        ["tgl_keluar","Tanggal keluar harus diisi."],
        ["dwnTime","Waktu perbaikan harus diisi."],
        ["tipeperbaikan","Tipe perbaikan harus dipilih."],
        ["descDmg","Deskripsi kerusakan harus diisi."],
        ["terlambat","Alasan harus diisi."],
	]);
	
	if(parseFloat(kmmasuk) > parseFloat(kmkeluar)){
		validate([
			["kmmasuk","KM/HM keluar harus lebih besar atau sama dengan KM/HM masuk."]
		]);
	}

	param = 'codeOrg=' + codeOrg + '&trans_no=' + trans_no + '&vhc_code=' + vhc_code + '&kdTraksi=' + kdTraksi;
	param += '&tgl_ganti=' + tgl_ganti + '&tgl_keluar=' + tgl_keluar + '&dwnTime=' + dwnTime  + '&nmpemohon=' + nmpemohon;
	param += '&kmmasuk=' + kmmasuk + '&kmkeluar=' + kmkeluar + '&tipeperbaikan=' + tipeperbaikan + '&descDmg=' + descDmg + '&terlambat=' + terlambat;
	param += '&external=' + ext + '&proses=' + proses + '&nopengajuan=' + nopengajuan;

	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					document.getElementById('detailEntry').style.display = 'block';
					setValue2('trans_no',con.responseText);
					loaddetail(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddetail(notran) {
	codeOrg 		= document.getElementById('codeOrg').value;
	param = 'proses=loaddetail' + '&trans_no=' + notran + '&codeOrg=' + codeOrg;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detailEntry').style.display = 'block';
					document.getElementById('detailisi').innerHTML = con.responseText;
					$(document).ready(function() {
					$('.select2').select2({
						dropdownAutoWidth:false
					});
					$('.select2-selection--single').height(20).css({
						cursor: "auto"
					});
					$('.select2-selection__arrow b').css({
						top: "40%"
					});
					$('.select2-selection__rendered').css({
						'line-height': '21px'
					});
				});
					loadDetailBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getKdVhc(kdtrak, kdvhc) {
	if ((kdvhc == 0) || (kdtrak == 0)) {
		kdtraks = document.getElementById('kdTraksi');
		kdtraks = kdtraks.options[kdtraks.selectedIndex].value;
		param = 'kdTraksi=' + kdtraks;
	} else {
		param = 'kdTraksi=' + kdtrak + '&kdVhc=' + kdvhc;
	}
	param += '&proses=getVhc';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					// console.log(con.responseText);
					document.getElementById('vhc_code').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//cari barang---------------------------

function tambahBarang(title, ev) {
	content = "<div id=formBarang style=\"height:300px;max-width:450px;\"></div>";
	alertify.popup2().destroy();
	alertify.popup2("Add Material",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','50%');
	getListBarang();
}

function getListBarang() {

	param = 'proses=getListBarang';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function cariListBarang() {
	namaBarangCari = document.getElementById('namaBarangCari').value;
	param = 'proses=getListBarang' + '&namaBarangCari=' + namaBarangCari;

	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function moveDataBarang(kodebarang, namabarang, satuanbarang) {
	document.getElementById('kodeBarang').value = kodebarang;
	document.getElementById('namaBarang').value = namabarang;
	document.getElementById('satuanBarang').value = satuanbarang;
	document.getElementById('listCariBarang').style.display = 'none';
	alertify.popup2().destroy();
}

//cari barang back---------------------------

function tambahBarangback(title, ev) {
	content = "<div id=formBarangback style=\"height:500px;max-width:450px;overflow:auto;\"></div>";
	alertify.popup2().destroy();
	alertify.popup2("Add Material",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
	getListBarangback();
}

function getListBarangback() {

	param = 'proses=getListBarangback';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarangback').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function cariListBarangback() {
	namaBarangCariback = document.getElementById('namaBarangCariback').value;
	param = 'proses=getListBarangback' + '&namaBarangCariback=' + namaBarangCariback;

	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarangback').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function moveDataBarangback(kodebarang, namabarang, satuanbarang) {
	document.getElementById('kodeBarangback').value = kodebarang;
	document.getElementById('backnamaBarang').value = namabarang;
	document.getElementById('backsatuanBarang').value = satuanbarang;
	document.getElementById('listCariBarangback').style.display = 'none';
	alertify.popup2().destroy();
}

//-------------------------


function cariBast(num) {
	param = 'proses=loadData';
	param += '&page=' + num;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//displayList();

					document.getElementById('contain').innerHTML = con.responseText;
					//loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cari() {
	schTran = trim(document.getElementById('schTran').value);
	schTgl = trim(document.getElementById('schTgl').value);
	// schRef = trim(document.getElementById('schRef').value);
	param = 'schTran=' + schTran + '&schTgl=' + schTgl;// + '&schRef=' + schRef
	param += '&proses=loadData'; //loadSch
	//alert(param);
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
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

function loadData(page=0) {
	param = 'proses=loadData&page='+page;
	tujuan = 'vhc_slave_service.php';
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

function add_new_data() {
	//alert('MASUK COI');
	//alert(con.responseText);
	// document.getElementById('headhernew').style.display = 'block';
	document.getElementById('headher').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	// document.getElementById('detailEntry').style.display = 'none';
	// document.getElementById('contentDetail').innerHTML='';
	// document.getElementById('containListKaryawan').innerHTML='';
	// document.getElementById('containListBarang').innerHTML='';
	// document.getElementById('backcontainListBarang').innerHTML='';
	//bukaform();
	cancelHead();
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	// document.getElementById('headhernew').style.display = 'none';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('detailEntry').style.display = 'none';
	document.getElementById('schTgl').value = '';
	document.getElementById('schTran').value = '';
	// document.getElementById('schRef').value = '';
	// document.getElementById('contentDetail').innerHTML='';
	// document.getElementById('containListKaryawan').innerHTML='';
	// document.getElementById('containListBarang').innerHTML='';
	// document.getElementById('backcontainListBarang').innerHTML='';
	cancelHead();
	setTimeout(function() {
		loadData();
	}, 300);
}

function deleteHead(trans_no) {
	param = 'proses=delete' + '&trans_no=' + trans_no;
	tujuan = 'vhc_slave_service.php';
	
	alertify.confirm('Apakah anda yakin hapus transaksi service '+trans_no+'?',
	function(){
		post_response_text(tujuan, param, respog);
	},
	function(){
		return;
	}
);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//karyawan
function saveKaryawan() {
	trans_no = document.getElementById('trans_no').value;
	karyawan = document.getElementById('karyawan').value;
	param = 'trans_no=' + trans_no + '&karyawan=' + karyawan;
	param += "&proses=saveKaryawan";
	
	validate([
		["karyawan","Nama karyawan harus dipilih."]
	]);

	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('info',con.responseText);
				} else {
					bersihFormKaryawan();
					loadDetailKaryawan();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bersihFormKaryawan() {
	document.getElementById('karyawan').value = '';
}

function deleteKaryawan(trans_no, karyawan) {
	param = 'proses=deleteKaryawan' + '&trans_no=' + trans_no + '&karyawan=' + karyawan;
	tujuan = 'vhc_slave_service.php';
	
	alertify.confirm("Informasi","Anda yakin hapus karyawan???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					loadDetailKaryawan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function loadDetailKaryawan(refer) {
	if(refer == 1){
		trans_no = document.getElementById('nopengajuan').value;
	}else{
		trans_no = document.getElementById('trans_no').value;
	}
	param = 'trans_no=' + trans_no;
	param += '&refer=' + refer;
	param += '&proses=loadDetailKaryawan';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containListKaryawan').innerHTML = con.responseText;
					backloadDetailBarang(true,refer);
					//backloadDetailBarang();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnopengajuan(tgl, trans_no, nopengajuan) {
	// tgl_pengajuan = document.getElementById('tgl_pengajuan').value;
	// if(tgl == undefined || tgl == ''){
	// 	tgl_pengajuan = document.getElementById('tgl_pengajuan').value;
	// }else{
	// 	tgl_pengajuan = tgl;
	// }
	param = 'tgl_pengajuan=' + tgl_pengajuan;
	param += '&proses=getnopengajuan';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tabelnopengajuan').style.display = '';
					loadnopengajuan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewspk() {
	tgl_pengajuan 	= document.getElementById('tgl_pengajuan').value;
	nopengajuan 	= document.getElementById('nopengajuan').value;
	validate([
		["tgl_pengajuan","Tanggal Pengajuan harus diisi."],
		["nopengajuan","No Pengajuan harus dipilih."],
	]);
	param = 'tgl_pengajuan=' + tgl_pengajuan;
	param += '&nopengajuan=' + nopengajuan;
	param += '&proses=previewspk';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split('##');
					if(data[1] == undefined){
						alertify.alert(con.responseText)
					}else{
						if(tgl_pengajuan != '' && nopengajuan != ''){
							// var ganti = document.getElementById('nmganti').value;
							// document.getElementById('saveheadspk').style.visibility = 'hidden';
							// document.getElementById('batalspk').innerHTML = ganti;
						}
						document.getElementById('headher').style.display = 'block';
						// document.getElementById('tgl_pengajuan').disabled = true;
						document.getElementById('kdTraksi').disabled = true;
						document.getElementById('codeOrg').disabled = true;
						document.getElementById('vhc_code').disabled = true;
						document.getElementById('nmpemohon').disabled = true;
						
						var res = JSON.parse(data[0]);
						setValue2('kdTraksi',data[1]);
						setValue2('nmpemohon',data[2]);
						setValue2('tgl_ganti',data[3]);
						setValue2('tgl_keluar',data[4]);
						setValue2('codeOrg',res.kodeorg);
						setValue2('dwnTime',res.downtimejam);
						setValue2('kmkeluar',res.kmkeluar);
						getKdVhc(0,0);
						setValue2('tipeperbaikan',res.tipeperbaikan);
						setValue2('descDmg',res.kerusakan);
						setValue2('terlambat',res.alasan);
						setValue2('vhc_code',res.kodevhc);
						setTimeout(() => {
							document.getElementById('kmmasuk').value=res.kmmasuk;
						}, 1500);
						document.getElementById('external').value = res.external;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//JS untuk delete detailnya

function update() {}


function fillField(external, codeOrg, trans_no, kdTraksi, vhc_code, nmpemohon, tgl_ganti, tgl_keluar, dwnTime, kmmasuk, kmkeluar, tipeperbaikan, descDmg, terlambat, nopengajuan, tgl_pengajuan, nmpemohon,nmpemohonsvc) {
	document.getElementById('listData').style.display = 'none';
	document.getElementById('headher').style.display = 'block';
	// document.getElementById('detailEntry').style.display = 'block';
	
	setValue2('trans_no',trans_no);
	setValue2('codeOrg',codeOrg);
	setValue2('external',external);
	setValue2('kdTraksi',kdTraksi);
	if(nmpemohon == ''){
		setValue2('nmpemohon',nmpemohonsvc);
	}else{
		setValue2('nmpemohon',nmpemohon);
	}
	setValue2('tgl_ganti',tgl_ganti);
	setValue2('tgl_keluar',tgl_keluar);
	setValue2('dwnTime',dwnTime);
	setValue2('kmkeluar',kmkeluar);
	setValue2('tipeperbaikan',tipeperbaikan);
	setValue2('descDmg',descDmg);
	setValue2('terlambat',terlambat);
	// setValue2('tgl_pengajuan',tgl_pengajuan);
	setValue2('proses','update');
	setValue2('vhc_code',vhc_code);
	setValue2('nopengajuan',nopengajuan);
	setTimeout(() => {
		document.getElementById('kmmasuk').value = kmmasuk;
	}, 1000);
	
	document.getElementById('codeOrg').disabled = true;
	document.getElementById('trans_no').disabled = true;
	document.getElementById('external').disabled = true;
	document.getElementById('kdTraksi').disabled = true;
	document.getElementById('vhc_code').disabled = true;
	document.getElementById('nopengajuan').disabled = true;
	document.getElementById('nmpemohon').disabled = true;
}

function cancelHead() {
	document.getElementById('codeOrg').disabled = false;
	document.getElementById('external').disabled = false;
	document.getElementById('kdTraksi').disabled = false;
	document.getElementById('vhc_code').disabled = false;
	document.getElementById('nmpemohon').disabled = false;
	document.getElementById('nopengajuan').disabled = false;
	
	setValue2('proses','insert');
	setValue2('trans_no','');
	setValue2('codeOrg','');
	setValue2('kdTraksi','');
	setValue2('vhc_code','');
	setValue2('nmpemohon',null);
	setValue2('tgl_ganti','');
	setValue2('tgl_keluar','');
	setValue2('dwnTime','0');
	setValue2('kmmasuk','0');
	setValue2('kmkeluar','0');
	setValue2('tipeperbaikan','');
	setValue2('descDmg','');
	setValue2('terlambat','');
	setValue2('external','');
	setValue2('detailEntry','');
	setValue2('nopengajuan','');
	
	document.getElementById('detailEntry').style.display = 'none';
}

function cancelcarispk(){
	// var cencel = document.getElementById('nmcancel').value;
	document.getElementById('tgl_pengajuan').disabled = false;
	document.getElementById('nopengajuan').disabled = false;
	document.getElementById('tgl_pengajuan').value = '';
	document.getElementById('nopengajuan').innerHTML = '';
	// document.getElementById('saveheadspk').style.visibility = 'visible';
	// document.getElementById('batalspk').style.visibility = 'visible';
	// document.getElementById('batalspk').innerHTML = cencel;
	document.getElementById('headher').style.display = 'block';
	cancelHead();
}
//getKm

function getKm() {
	vhc_code = document.getElementById('vhc_code').value;
	param = 'vhc_code=' + vhc_code;
	param += '&proses=getKm';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					ar = con.responseText.split("###");

					if (ar[1] == 1) {
						document.getElementById('kmmasuk').value = ar[0];
						//document.getElementById('kmmasuk').disabled=true;
					} else {
						document.getElementById('kmmasuk').disabled = false;
						document.getElementById('kmmasuk').value = 0;
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailBarang(nodok, ev) {
	content = "<div id=formBarang style=\"height:200px;width:500px;overflow:scroll;\"></div>";
	title = 'No. Job Order : ' + nodok;
	height = '200';
	width = '500';
	showDialog1(title, content, width, height, ev);
	getListBarangLaporan(nodok);
}

function getListBarangLaporan(nodok) {
	param = 'proses=getListBarangLaporan' + '&nodok=' + nodok;
	//alert(param);
	tujuan = 'pabrik_slave_2perbaikan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formBarang').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function batalLaporan() {
	document.getElementById('pabrik').value = '';
	document.getElementById('station').value = '';
	document.getElementById('tgl2').value = '';
	document.getElementById('tgl1').value = '';
	document.getElementById('printContainer').innerHTML = '';
}

function backbersihFormBarang() {
	document.getElementById('backnamaBarang').value = '';
	document.getElementById('backsatuanBarang').value = '';
	document.getElementById('backjumlahBarang').value = '';
	document.getElementById('backketeranganBarang').value = '';
	document.getElementById('kodeBarangback').value = '';
}

function backsaveBarang() {
	trans_no = document.getElementById('trans_no').value;
	kodeBarangback = document.getElementById('kodeBarangback').value;
	namaBarang = document.getElementById('backnamaBarang').value;
	jumlahBarang = document.getElementById('backjumlahBarang').value;
	satuanBarang = document.getElementById('backsatuanBarang').value;
	keteranganBarang = document.getElementById('backketeranganBarang').value;
	
	validate([
		["kodeBarangback","Kode barang harus diisi."],
		["backjumlahBarang","Jumlah barang harus diisi."],
	]);
	
	if(jumlahBarang <= 0){
		alertify.alert("Jumlah barang harus lebih besar dari 0 (nol).");
		return false;
	}

	param = 'trans_no=' + trans_no + '&kodeBarangback=' + kodeBarangback + '&namaBarang=' + namaBarang + '&jumlahBarang=' + jumlahBarang;
	param += '&satuanBarang=' + satuanBarang + '&keteranganBarang=' + keteranganBarang;
	param += "&proses=backsaveBarang";
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('',con.responseText);
				} else {
					backbersihFormBarang();
					backloadDetailBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function backloadDetailBarang(firstload,refer) {
	if (typeof firstload == 'undefined') {
		firstload = false;
	}
	if(refer == 1){
		trans_no = document.getElementById('nopengajuan').value;
	}else{
		trans_no = document.getElementById('trans_no').value;
	}
	param = 'trans_no=' + trans_no;
	param += '&refer=' + refer;
	param += '&proses=backloadDetailBarang';
	//alert(param);
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("####");
					document.getElementById('backcontainListBarang').innerHTML = isi[0];
					document.getElementById('loadfilesht').innerHTML = isi[1];
					loadfiles(trans_no);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function backdeleteBarang(trans_no, namaBarang, satuanBarang) {
	param = 'proses=backdeleteBarang' + '&trans_no=' + trans_no + '&namaBarang=' + namaBarang + '&satuanBarang=' + satuanBarang;
	tujuan = 'vhc_slave_service.php';
	
	alertify.confirm("Informasi","Anda yakin hapus material???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					backloadDetailBarang();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unposting(trans_no, numrow, tgl) {
	param = 'proses=unposting' + '&trans_no=' + trans_no + '&tgl=' + tgl;
	tujuan = 'vhc_slave_service.php';
	if (confirm('Anda yakin ingin unposting transaksi nomor ' + trans_no + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[8].innerHTML = '';
					//x.cells[11].innerHTML = '';
					//x.cells[12].innerHTML = '';
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
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev) {
	trans_no = document.getElementById('trans_no').value;
	param = 'proses=showupload&trans_no=' + trans_no;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().set({'Upload Files':'Detail','resizable':true,'maximizable':true,'message':con.responseText}).resizeTo('80%','70%').show();
					loadfiles(trans_no);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var trans_no = document.getElementById("notransaksiupload").innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("trans_no", trans_no);

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}

	var con = createXMLHttpRequest();
	con.open("POST", "vhc_slave_service.php?proses=submitfile", true);
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
					loadfiles(trans_no);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(trans_no) {
	param = 'proses=loadfiles&trans_no=' + trans_no;
	tujuan = 'vhc_slave_service.php';
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
					if (document.getElementById('loadfilesview') !== null) {
						document.getElementById('loadfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile) {
	param = 'proses=deletefile&trans_no=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'vhc_slave_service.php';
	
	alertify.confirm("Informasi","Anda yakin hapus material???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function form() {
	width = '720';
	height = '';
	content = "<fieldset><div id=containerd style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}

function html(notransaksi) {
	param = 'proses=html' + '&trans_no=' + notransaksi;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup().set({'title':'Detail','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('50%','70%').show();
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefileall(trans_no) {
	param = 'proses=deletefileall&trans_no=' + trans_no;
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('File sudah di hapus');
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting_data(notrans,kdvhc){
	param='trans_no='+notrans+'&proses=postingData'+'&vhc_code='+kdvhc;
	tujuan='vhc_slave_service.php';
	
	alertify.confirm("Informasi","Apakah anda yakin ingin posting transaksi service dengan notransaksi = "+notrans+" ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog(){
		if(con.readyState==4){
			if(con.status==200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert('Info',con.responseText);
				}else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil');
					getPage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printpdf(trans_no){
	param = 'proses=pdf&column='+trans_no+'&table=vhc_penggantianht';
	tujuan = tujuan+'?' + param;
	alertify.popuppdf().destroy();
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_penggunaanKomponen.php?"+param+"'></iframe>").set({'resizable':true,'maximizable':true,'startMaximized':true,'overflow':false}).resizeTo('80%','70%');
}

function form_ajukan(nopengajuan) {
    param = 'proses=form_ajukan' + '&trans_no=' + nopengajuan;
    tujuan = 'vhc_slave_service.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popup().set({'title':'Approval','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('30%','30%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function ajukan() {
    jumlahlevel = document.getElementById('numrow').value;
    kepada = '';
    for (var i = 1; i <= jumlahlevel; i++) {
        if (kepada == '') {
            kepada = document.getElementById('kepada' + i).value;
        } else {
            kepada += '###' + document.getElementById('kepada' + i).value;
        }
    }
    notransaksi 		= document.getElementById('notran_aju').innerHTML;
    jenispersetujuanx 	= document.getElementById('jenispersetujuanx').value;
    param 				= 'proses=ajukan' + '&trans_no=' + notransaksi + '&kepada=' + kepada + '&jenispersetujuanx=' + jenispersetujuanx;
    if (kepada == '') {
        alert('Isikan nama penyetuju.');
        return;
    }
    tujuan = 'vhc_slave_service.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    displayList();
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil Reconfirm');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function reconfirm(notrans){
	param='trans_no='+notrans+'&proses=reconfirm';
	tujuan='vhc_slave_service.php';
	
	alertify.confirm("Informasi","Apakah anda yakin ingin melakukan Reconfirm transaksi service dengan notransaksi = "+notrans+". ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
	function respog(){
		if(con.readyState==4){
			if(con.status==200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert('Info',con.responseText);
				}else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-right');
					alertify.set('notifier','delay', 2);
					alertify.success('Berhasil Reconfirm');
					getPage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function popupnopengajuan() {
	param = 'proses=popupnopengajuan';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup().set({'title':'Pilih No Pengajuan','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('30%','30%').show();
					// cancelcarispk();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadnopengajuan() {
	tgl_pengajuan = document.getElementById('tgl_pengajuan').value;
	param = 'tgl_pengajuan=' + tgl_pengajuan;
	param += '&proses=loadnopengajuan';
	tujuan = 'vhc_slave_service.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddetailnopengajuan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function pilihnopengajuan(nopengajuan) {
	document.getElementById('nopengajuan').value = nopengajuan;
	previewspk();
	alertify.popup().destroy();
}