function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function openlistperpt(e) {
	param = 'proses=getdivisi&perpt=perpt';
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	param = '&proses=getws';
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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

function loadDetailBarang(firstload) {

	if (typeof firstload == 'undefined') {
		firstload = false;
	}
	trans_no = document.getElementById('trans_no').value;
	param = 'trans_no=' + trans_no;
	param += '&proses=loadDetailBarang';
	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containListBarang').innerHTML = con.responseText;
					loadDetailKaryawan();
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
	codeOrg 		= document.getElementById('codeOrg').value;
	trans_no 		= document.getElementById('trans_no').value;
	nmpemohon 		= document.getElementById('nmpemohon').value;
	vhc_code 		= document.getElementById('vhc_code').value;
	kdTraksi 		= document.getElementById('kdTraksi').value;
	tgl_ganti 		= document.getElementById('tgl_ganti').value;
	tgl_pengajuan 	= document.getElementById('tgl_pengajuan').value;
	tgl_keluar 		= document.getElementById('tgl_keluar').value;
	dwnTime 		= document.getElementById('dwnTime').value;
	kmmasuk 		= remove_comma_var(document.getElementById('kmmasuk').value);
	kmkeluar 		= remove_comma_var(document.getElementById('kmkeluar').value);
	tipeperbaikan 	= document.getElementById('tipeperbaikan').value;
	descDmg 		= document.getElementById('descDmg').value;
	terlambat 		= document.getElementById('terlambat').value;
	nodok 			= document.getElementById('nodok').value;
	proses 			= 'saveHeader';
	if(kmmasuk==''){kmmasuk=0;}
	if(kmkeluar==''){kmkeluar=0;}
	
	validate([
        ["nmpemohon","Nama Pemohon harus dipilih."],
        ["codeOrg","Bengkel harus dipilih."],
        ["kdTraksi","Kode traksi harus dipilih."],
        ["vhc_code","Kode kendaraan harus dipilih."],
        ["tgl_pengajuan","Tanggal Pengajuan harus diisi."],
        ["tgl_ganti","Tanggal masuk harus diisi."],
        ["tgl_keluar","Tanggal keluar harus diisi."],
        ["dwnTime","Waktu perbaikan (Jam) harus diisi."],
        ["tipeperbaikan","Tipe perbaikan harus dipilih."],
        ["descDmg","Deskripsi kerusakan harus diisi."],
        ["terlambat","Alasan harus diisi."],
	]);
	
	if(parseFloat(kmmasuk) > parseFloat(kmkeluar)){
		validate([
			["kmmasuk","KM/HM keluar harus lebih besar atau sama dengan KM/HM masuk."]
		]);
	}

	param = 'codeOrg=' + codeOrg + '&trans_no=' + trans_no + '&vhc_code=' + vhc_code + '&kdTraksi=' + kdTraksi + '&nmpemohon=' + nmpemohon;
	param += '&tgl_pengajuan=' + tgl_pengajuan + '&tgl_ganti=' + tgl_ganti + '&tgl_keluar=' + tgl_keluar + '&dwnTime=' + dwnTime  + '&nodok=' + nodok;
	param += '&kmmasuk=' + kmmasuk + '&kmkeluar=' + kmkeluar + '&tipeperbaikan=' + tipeperbaikan + '&descDmg=' + descDmg + '&terlambat=' + terlambat;
	param += '&proses=' + proses;

	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
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

// function loaddetail(notran) {
// 	codeOrg	= document.getElementById('codeOrg').value;
// 	param 	= 'proses=loaddetail' + '&trans_no=' + notran + '&codeOrg=' + codeOrg;
// 	tujuan 	= 'vhc_slave_pengajuanservice.php';
// 	post_response_text(tujuan, param, respon);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					document.getElementById('detailEntry').style.display = 'block';
// 					document.getElementById('detailisi').innerHTML = con.responseText;
// 					// getSelect2();
// 					setTimeout(() => {
// 						loadDetailBarang();
// 					}, 400);
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}

// }


function loaddetail(notran) {
	codeOrg = document.getElementById('codeOrg').value;
	param = 'proses=loaddetail' + '&trans_no=' + notran + '&codeOrg=' + codeOrg;
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response

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
	content = "<div id=formBarang style=\"max-height:250px;max-width:450px;overflow:auto;\"></div>";
	alertify.popup2().destroy();
	alertify.popup2("Add Material",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','45%');
	getListBarang();
}

function getListBarang() {

	param = 'proses=getListBarang';
	tujuan = 'vhc_slave_pengajuanservice.php';
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

	tujuan = 'vhc_slave_pengajuanservice.php';
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
	content = "<div id=formBarangback style=\"max-height:250px;max-width:450px;overflow:auto;\"></div>";
	alertify.popup2().destroy();
	alertify.popup2("Add Material",content).set({'resizable':true,'maximizable':true}).resizeTo('50%','45%');
	getListBarangback();
}

function getListBarangback() {

	param = 'proses=getListBarangback';
	tujuan = 'vhc_slave_pengajuanservice.php';
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

	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	schTran 		= trim(document.getElementById('schTran').value);
	schTgl 			= trim(document.getElementById('schTgl').value);
	schTglPengajuan = trim(document.getElementById('schTglPengajuan').value);
	param 			= 'schTran=' + schTran + '&schTgl=' + schTgl + '&schTglPengajuan=' + schTglPengajuan;
	param 			+= '&proses=loadData'; //loadSch
	
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	document.getElementById('headher').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	document.getElementById('detailEntry').style.display = 'none';
	document.getElementById('detailisi').innerHTML='';
	// document.getElementById('containListKaryawan').innerHTML='';
	// document.getElementById('containListBarang').innerHTML='';
	// document.getElementById('backcontainListBarang').innerHTML='';
	cancelHead();
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('schTgl').value = '';
	document.getElementById('schTran').value = '';
	document.getElementById('detailEntry').style.display = 'none';
	document.getElementById('detailisi').innerHTML='';
	document.getElementById('schTglPengajuan').value = '';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	
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

	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	
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

function loadDetailKaryawan() {
	trans_no = document.getElementById('trans_no').value;
	param = 'trans_no=' + trans_no;
	param += '&proses=loadDetailKaryawan';
	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containListKaryawan').innerHTML = con.responseText;
					backloadDetailBarang(true);
					//backloadDetailBarang();

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

function fillField(codeOrg, trans_no, kdTraksi, vhc_code, tgl_ganti, tgl_keluar, dwnTime, kmmasuk, kmkeluar, tipeperbaikan, descDmg, terlambat, tgl_pengajuan, nmpemohon) {

	document.getElementById('listData').style.display = 'none';
	document.getElementById('headher').style.display = 'block';
	document.getElementById('detailEntry').style.display = 'block';
	
	setValue2('trans_no',trans_no);
	setValue2('codeOrg',codeOrg);
	setValue2('kdTraksi',kdTraksi);
	setValue2('nmpemohon',nmpemohon);
	
	setValue2('tgl_pengajuan',tgl_pengajuan);
	setValue2('tgl_ganti',tgl_ganti);
	setValue2('tgl_keluar',tgl_keluar);
	setValue2('dwnTime',dwnTime);
	setValue2('kmmasuk',kmmasuk);
	setValue2('kmkeluar',kmkeluar);
	setValue2('tipeperbaikan',tipeperbaikan);
	setValue2('descDmg',descDmg);
	setValue2('terlambat',terlambat);
	setValue2('proses','update');
	
	getKdVhc(kdTraksi,vhc_code);
	setTimeout(function(){
		loaddetail(trans_no);
	}, 800);
	
	document.getElementById('codeOrg').disabled = true;
	document.getElementById('trans_no').disabled = true;
	document.getElementById('kdTraksi').disabled = true;
	document.getElementById('vhc_code').disabled = true;
	document.getElementById('nmpemohon').disabled = true;
	document.getElementById('tgl_pengajuan').disabled = true;
}

function cancelHead() {
	document.getElementById('codeOrg').disabled = false;
	document.getElementById('kdTraksi').disabled = false;
	document.getElementById('vhc_code').disabled = false;
	document.getElementById('nmpemohon').disabled = false;
	document.getElementById('tgl_pengajuan').disabled = false;
	
	setValue2('proses','insert');
	setValue2('trans_no','');
	setValue2('nmpemohon','');
	setValue2('codeOrg','');
	setValue2('kdTraksi','');
	setValue2('vhc_code','');
	setValue2('nodok','');
	setValue2('tgl_pengajuan','');
	setValue2('tgl_ganti','');
	setValue2('tgl_keluar','');
	setValue2('dwnTime','');
	setValue2('kmmasuk','0');
	setValue2('kmkeluar','');
	setValue2('tipeperbaikan','');
	setValue2('descDmg','');
	setValue2('terlambat','');
}

function getKm() {
	vhc_code = document.getElementById('vhc_code').value;
	param = 'vhc_code=' + vhc_code;
	param += '&proses=getKm';
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('',con.responseText);
				} else {
					backbersihFormBarang();
					setTimeout(() => {
						backloadDetailBarang();
					}, 300);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function backloadDetailBarang(firstload) {
	if (typeof firstload == 'undefined') {
		firstload = false;
	}
	trans_no = document.getElementById('trans_no').value;
	param = 'trans_no=' + trans_no;
	param += '&proses=backloadDetailBarang';
	//alert(param);
	tujuan = 'vhc_slave_pengajuanservice.php';
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

function backdeleteBarang(trans_no, kodebarang) {
	param = 'proses=backdeleteBarang' + '&trans_no=' + trans_no + '&kodeBarangback=' + kodebarang;
	tujuan = 'vhc_slave_pengajuanservice.php';
	
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	con.open("POST", "vhc_slave_pengajuanservice.php?proses=submitfile", true);
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	
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
	tujuan = 'vhc_slave_pengajuanservice.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					alertify.popup().set({'title':'Detail Work Order Service','resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('55%','80%').show();
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
	tujuan = 'vhc_slave_pengajuanservice.php';
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

function posting_data(notrans,kdvhc,tanggal){
	param='trans_no='+notrans+'&proses=postingData'+'&vhc_code='+kdvhc+'&tanggal='+tanggal;
	tujuan='vhc_slave_pengajuanservice.php';
	
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

function printpdf(trans_no,kodevhc){
	param = 'proses=pdf&column='+trans_no+'&kodevhc='+kodevhc+'&table=vhc_pengajuanservice';
	tujuan = tujuan+'?' + param;
	alertify.popuppdf().destroy();
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_pengajuanservice_pdf.php?"+param+"'></iframe>").set({'resizable':true,'maximizable':true,'startMaximized':true,'overflow':false}).resizeTo('80%','70%');
}

function janganlebihdarikmawal() {
	if(document.getElementById('kmkeluar').value < document.getElementById('kmmasuk').value){
		alertify.alert('Informasi','KM / HM Masuk tidak boleh lebih besar dari Est KM / HM Keluar.');
	}
}

function tutuppengajuan(notrans){
	param='trans_no='+notrans+'&proses=tutuppengajuan';
	tujuan='vhc_slave_pengajuanservice.php';
	
	alertify.confirm("Informasi","Apakah anda yakin ingin menutup transaksi work order service dengan notransaksi = "+notrans+"<br> Apabila ditutup maka notransaksi tersebut tidak bisa ditarik di menu Service dan tidak dapat di ubah atau dihapus kembali ?",
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
