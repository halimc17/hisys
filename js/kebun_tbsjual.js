 function koreksi(notransaksi) {
    param = 'notransaksi=' + notransaksi;
    tujuan = 'kebun_tbsjual_slave.php';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup("Koreksi",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan + '?' + 'method=koreksiTransaksi', param, respog);
  }

// Data harga (hargaMasterKoreksi) dikirim server lewat <script type="application/json">, bukan
// <script> biasa - karena isi popup ini disuntikkan lewat innerHTML (alertify.popup), dan <script>
// yang disuntik lewat innerHTML tidak pernah dieksekusi browser (variable-nya tidak akan pernah
// terbentuk kalau dikirim sebagai `var hargaMasterKoreksi = [...]` biasa). Dibaca ulang tiap
// dipakai supaya tidak tergantung timing eksekusi script.
function getHargaMasterKoreksi() {
	var el = document.getElementById('hargaMasterKoreksiData');
	if (!el) return [];
	try {
		return JSON.parse(el.textContent || el.innerHTML);
	} catch (e) {
		return [];
	}
}

// Daftar calon approver, dikirim server dengan cara yang sama seperti hargaMasterKoreksiData.
function getApproverList() {
	var el = document.getElementById('approverListData');
	if (!el) return [];
	try {
		return JSON.parse(el.textContent || el.innerHTML);
	} catch (e) {
		return [];
	}
}

// Dipanggil tiap kali dropdown Tahun Tanam (Koreksi) di satu baris diganti.
// Cari harga yang berlaku untuk tahun tanam baru pada tanggal PKS baris itu, lalu hitung ulang
// Harga/Kg & Total kolom "Setelah Koreksi", plus grand total. Kalau tidak ada harga yang cocok
// (belum ada / belum posting untuk tahun tanam & tanggal itu), ditandai merah + keterangan.
function hitungKoreksi(no) {
	var selTT = document.getElementById('kortahuntanam' + no);
	var nettoCell = document.getElementById('kornetto' + no);
	if (!selTT || !nettoCell) return;

	var newTT = selTT.value;
	var netto = parseFloat(nettoCell.getAttribute('data-netto')) || 0;
	var tglPks = nettoCell.getAttribute('data-tglpks');

	var elHargaBaru = document.getElementById('korhargabaru' + no);
	var elTotalBaru = document.getElementById('kortotalbaru' + no);
	var elKet = document.getElementById('korket' + no);

	// Belum pilih tahun tanam koreksi (baris "Campur" defaultnya kosong) -> jangan hitung apa-apa dulu.
	if (newTT === '') {
		elHargaBaru.innerHTML = '-';
		elTotalBaru.innerHTML = elTotalBaru.getAttribute('data-totallama') || elTotalBaru.innerHTML;
		elTotalBaru.style.color = '';
		elTotalBaru.setAttribute('data-valid', '0');
		if (elKet) elKet.innerHTML = '<font color=red>Pilih tahun tanam koreksi</font>';
		hitungGrandTotalKoreksi();
		return;
	}

	var hargaMasterKoreksi = getHargaMasterKoreksi();
	var hargaBaru = 0;
	for (var i = 0; i < hargaMasterKoreksi.length; i++) {
		var h = hargaMasterKoreksi[i];
		if (h.tt == newTT && tglPks >= h.awal && tglPks <= h.akhir) {
			hargaBaru = h.harga;
			break;
		}
	}

	var totalBaru = hargaBaru * netto;
	var valid = hargaBaru > 0;

	elHargaBaru.innerHTML = numberFormat(hargaBaru, 2);
	elTotalBaru.innerHTML = numberFormat(totalBaru, 2);
	elTotalBaru.style.color = valid ? '' : 'red';
	elTotalBaru.setAttribute('data-valid', valid ? '1' : '0');

	if (elKet) {
		elKet.innerHTML = valid ? '' : '<font color=red>Belum ada harga tahun tanam ' + newTT + ' untuk tanggal PKS ini / belum di-posting</font>';
	}

	hitungGrandTotalKoreksi();
}

function hitungGrandTotalKoreksi() {
	var els = document.querySelectorAll('[id^=kortotalbaru]');
	var total = 0;
	els.forEach(function (el) {
		total += parseFloat(el.innerHTML.replace(/,/g, '')) || 0;
	});
	var elGrand = document.getElementById('korgrandtotalbaru');
	if (elGrand) elGrand.innerHTML = numberFormat(total, 2);
}

// TODO: backend pengajuan/approval (case 'ajukanPersetujuanKoreksi' + tabel approval) belum dibuat -
// itu menyusul dari sisi user. Update tahuntanam/harga/total/keterangan ke tabel asli baru terjadi
// saat approval TERAKHIR disetujui, bukan di titik ini. Untuk sekarang, tombol ini cuma mengumpulkan
// & memvalidasi baris yang mau diajukan.
function ajukanPersetujuanKoreksi(notransaksi, maxRow) {
	var perubahan = [];
	var belumPilihCampur = 0;
	var hargaTidakValid = 0;
	var belumAdaKeterangan = 0;

	for (var i = 1; i <= maxRow; i++) {
		var elLama = document.getElementById('kortahuntanamlama' + i);
		var elBaru = document.getElementById('kortahuntanam' + i);
		if (!elLama || !elBaru) continue;

		var row = document.getElementById('rowkoreksi' + i);
		var isMixed = row && row.getAttribute('data-mixed') === '1';
		var baru = elBaru.value.trim();

		if (isMixed && baru === '') {
			belumPilihCampur++;
			continue;
		}

		var lama = elLama.innerHTML.trim();
		if (lama !== baru) {
			var elTotalBaru = document.getElementById('kortotalbaru' + i);
			if (elTotalBaru && elTotalBaru.getAttribute('data-valid') === '0') {
				hargaTidakValid++;
				continue;
			}

			var elKetRevisi = document.getElementById('korketrevisi' + i);
			var keteranganRevisi = elKetRevisi ? elKetRevisi.value.trim() : '';
			if (keteranganRevisi === '') {
				belumAdaKeterangan++;
				if (elKetRevisi) elKetRevisi.style.backgroundColor = '#ffe0e0';
				continue;
			}
			if (elKetRevisi) elKetRevisi.style.backgroundColor = '';

			perubahan.push({
				tanggal: document.getElementById('kortanggal' + i).innerHTML,
				nokendaraan: document.getElementById('kornokendaraan' + i).innerHTML,
				tahuntanamLama: lama,
				tahuntanamBaru: baru,
				hargaLama: document.getElementById('korhargalama' + i).innerHTML.trim(),
				hargaBaru: document.getElementById('korhargabaru' + i).innerHTML.trim(),
				totalLama: document.getElementById('kortotallama' + i).innerHTML.trim(),
				totalBaru: elTotalBaru.innerHTML.trim(),
				keteranganRevisi: keteranganRevisi
			});
		}
	}

	if (belumPilihCampur > 0) {
		alertify.alert('Informasi', 'Ada ' + belumPilihCampur + ' truck/tanggal dengan tahun tanam Campur yang belum dipilih koreksinya. Tolong pilih dulu tahun tanam yang benar untuk baris tersebut.');
		return;
	}

	if (hargaTidakValid > 0) {
		alertify.alert('Informasi', 'Ada ' + hargaTidakValid + ' baris yang tahun tanam koreksinya belum punya harga (belum di-posting) untuk tanggal PKS truck tsb. Lihat kolom Info berwarna merah, tidak bisa diajukan sebelum harganya tersedia.');
		return;
	}

	if (belumAdaKeterangan > 0) {
		alertify.alert('Informasi', 'Ada ' + belumAdaKeterangan + ' baris yang berubah tahun tanamnya tapi belum diisi Keterangan Revisi. Wajib diisi alasan koreksinya.');
		return;
	}

	if (perubahan.length === 0) {
		alertify.alert('Informasi', 'Tidak ada perubahan tahun tanam untuk diajukan.');
		return;
	}

	tampilkanRekapKoreksi(notransaksi, perubahan);
}

// Rekap ringkas HANYA baris yang berubah + total rupiah lama vs baru, ditampilkan sebelum
// benar-benar diajukan - supaya tidak perlu scroll ke tabel detail lagi buat lihat dampaknya.
function tampilkanRekapKoreksi(notransaksi, perubahan) {
	var totalLamaAll = 0, totalBaruAll = 0;
	var rows = '';

	perubahan.forEach(function (p) {
		totalLamaAll += parseFloat(p.totalLama.replace(/,/g, '')) || 0;
		totalBaruAll += parseFloat(p.totalBaru.replace(/,/g, '')) || 0;

		rows += "<tr class=rowcontent>" +
			"<td align=center>" + p.tanggal + "</td>" +
			"<td align=center>" + p.nokendaraan + "</td>" +
			"<td align=center>" + p.tahuntanamLama + " &rarr; <b>" + p.tahuntanamBaru + "</b></td>" +
			"<td align=right>" + p.hargaLama + " &rarr; <b>" + p.hargaBaru + "</b></td>" +
			"<td align=right>" + p.totalLama + " &rarr; <b>" + p.totalBaru + "</b></td>" +
			"<td>" + p.keteranganRevisi + "</td>" +
			"</tr>";
	});

	var selisih = totalBaruAll - totalLamaAll;
	var warnaSelisih = selisih < 0 ? 'red' : (selisih > 0 ? 'green' : '');
	var labelSelisih = (selisih > 0 ? '+' : '') + numberFormat(selisih, 2);

	var html = "<div style='max-height:450px;overflow-y:auto;'>" +
		"<table cellpadding=5 cellspacing=1 border=1 class=sortable width=100%>" +
		"<thead><tr class=rowheader style='text-align:center;font-weight:bold;'>" +
		"<th>Tanggal</th><th>No Polisi</th><th>Tahun Tanam</th><th>Harga/Kg</th><th>Total</th><th>Keterangan Revisi</th>" +
		"</tr></thead><tbody>" + rows + "</tbody>" +
		"<tfoot>" +
		"<tr class=rowheader style='font-weight:bold;'>" +
		"<td align=center colspan=4>Total (" + perubahan.length + " baris berubah)</td>" +
		"<td align=right>" + numberFormat(totalLamaAll, 2) + " &rarr; " + numberFormat(totalBaruAll, 2) + "</td>" +
		"<td></td>" +
		"</tr>" +
		"<tr class=rowheader style='font-weight:bold;'>" +
		"<td align=center colspan=4>Selisih Total Rupiah</td>" +
		"<td align=right style='color:" + warnaSelisih + ";'>" + labelSelisih + "</td>" +
		"<td></td>" +
		"</tr>" +
		"</tfoot>" +
		"</table></div>";

	// Dropdown "Ajukan Kepada" - SEMENTARA daftar semua karyawan di unit ini (belum ada alur
	// approval yang sebenarnya, lihat catatan di getApproverList() & case insertApprovalKoreksi).
	var approverList = getApproverList();
	var optApprover = "<option value=''>-- Pilih Approver --</option>";
	approverList.forEach(function (a) {
		optApprover += "<option value='" + a.id + "'>" + a.nama + "</option>";
	});

	html += "<div style='margin-top:10px;'>" +
		"<label>Ajukan Kepada: </label>" +
		"<select id=korapprover class=myinputtext style='width:250px;'>" + optApprover + "</select>" +
		"</div>" +
		"<div align=center style='margin-top:10px;'>" +
		"<button class=mybutton onclick=konfirmasiAjukanKoreksi()>Konfirmasi &amp; Ajukan</button>&nbsp;" +
		"<button class=mybutton onclick=\"alertify.popup2().destroy()\">Tutup, Edit Lagi</button>" +
		"</div>";

	// Disimpan di window karena tombol di dalam HTML popup2 tidak bisa langsung
	// menangkap closure JS - dibaca lagi oleh konfirmasiAjukanKoreksi() saat diklik.
	window.__koreksiPending = { notransaksi: notransaksi, perubahan: perubahan, totalBaruAll: totalBaruAll };

	alertify.popup2("Ringkasan Perubahan Koreksi", html).set({ 'resizable': true, 'maximizable': true }).resizeTo('60%', '70%');
}

// Kirim pengajuan koreksi ke server (case 'insertApprovalKoreksi' di kebun_tbsjual_slave.php).
// Itu masih skeleton di sisi user (tabel/kolom & alur approval sebenarnya menyusul), tapi jalur
// JS -> server-nya sudah terhubung di sini.
function konfirmasiAjukanKoreksi() {
	var data = window.__koreksiPending;
	if (!data) return;

	var elApprover = document.getElementById('korapprover');
	var approverid = elApprover ? elApprover.value : '';
	if (approverid === '') {
		alertify.alert('Informasi', 'Pilih dulu approver-nya.');
		return;
	}

	var param = 'method=insertApprovalKoreksi';
	param += '&notransaksi=' + encodeURIComponent(data.notransaksi);
	param += '&approverid=' + encodeURIComponent(approverid);
	param += '&dataKoreksi=' + encodeURIComponent(JSON.stringify(data.perubahan));

	var tujuan = 'kebun_tbsjual_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi', con.responseText);
				} else {
					alertify.popup2().destroy();
					alertify.popup().destroy();
					alertify.alert('Informasi', 'Koreksi berhasil diajukan, total rupiah jadi ' + numberFormat(data.totalBaruAll, 2) + '. Menunggu approval.');
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




function excel(notransaksi) {
	param = 'method=excel' + '&notransaksi=' + notransaksi;
	tujuan='kebun_tbsjual_slave.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '820';
	height = '500';
	title = "";
	showDialog5(title, content, width, height, 'event');
}

function posting(notransaksi) {
	param='method=posting'+'&notransaksi='+notransaksi;
	tujuan = 'kebun_tbsjual_slave.php';
	alertify.confirm("Informasi","Posting Transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alertify.alert('Informasi',con.responseText);
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


function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_tbsjual_slave.php';
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
					document.getElementById('tanggal').value = ar[2];
					document.getElementById('kodecustomer').value = ar[3];
					document.getElementById('tanggaltbs1').value = ar[4];
					document.getElementById('tanggaltbs2').value = ar[5];
					document.getElementById('keteranganht').value = ar[6];
					document.getElementById('sortasi').value = ar[7];
					document.getElementById('kodero').value = ar[8];
					document.getElementById('nokontrak').value = ar[9];
					
					setTimeout(() => {
						getcust();
						setTimeout(() => {
							
							document.getElementById('notransaksi').disabled=true;
							document.getElementById('unit').disabled=true;
							document.getElementById('kodecustomer').disabled=true;
							document.getElementById('tanggal').disabled=true;
							document.getElementById('tanggaltbs1').disabled=true;
							document.getElementById('tanggaltbs2').disabled=true;
							document.getElementById('sortasi').disabled=true;
							document.getElementById('saveht').disabled=true;
							document.getElementById('kodero').disabled=true;
							document.getElementById('nokontrak').disabled=true;
							document.getElementById('listdata').style.display='none';
							document.getElementById('header').style.display='block';
							document.getElementById('detail').style.display='block';
							
							setTimeout(() => {
								loaddatadt();
							}, 300);
						}, 300);
					}, 300);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respog);
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function loaddata(num) {
	notransaksisch=document.getElementById('notransaksisch').value;
	tanggalmulaisch=document.getElementById('tanggalmulaisch').value;
	tanggalselesaisch=document.getElementById('tanggalselesaisch').value;
	kodecustomersch=document.getElementById('kodecustomersch').value;
	param = 'method=loaddata&page=' + num;
	param += '&notransaksisch=' + notransaksisch;
	param += '&tanggalmulaisch=' + tanggalmulaisch;
	param += '&tanggalselesaisch=' + tanggalselesaisch;
	param += '&kodecustomersch=' + kodecustomersch;
	tujuan = 'kebun_tbsjual_slave.php';
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

function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display='none';
	loaddata(0);
}

function newdata(){
	cancelht();
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
}


function cancelht(){
	document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('tanggalselesaisch').value='';
    document.getElementById('kodecustomersch').value='';
	document.getElementById('unit').disabled=false;
	document.getElementById('kodecustomer').disabled=false;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('tanggaltbs1').disabled=false;
	document.getElementById('tanggaltbs2').disabled=false;
	document.getElementById('keteranganht').disabled=false;
	document.getElementById('sortasi').disabled=false;
	document.getElementById('saveht').disabled=false;
	document.getElementById('kodero').disabled=false;
	
	document.getElementById('kodero').value='';
	document.getElementById('notransaksi').value='';
	document.getElementById('unit').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggaltbs1').value='';
	document.getElementById('tanggaltbs2').value='';
	document.getElementById('keteranganht').value='';
	document.getElementById('sortasi').value='';
	document.getElementById('detail').style.display='none';
}

function getcust() {
	
	param="";
	nokontrak = document.getElementById('nokontrak').value;
	
	param = 'nokontrak=' + nokontrak + '&method=getcust';
	tujuan = 'kebun_tbsjual_slave.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else  {
					
					isdt = con.responseText;
					document.getElementById('kodecustomer').innerHTML = isdt;
					// document.getElementById('unitinv').innerHTML = isdt[1];

					// isdt = con.responseText.split("####");
					// document.getElementById('divisi').innerHTML = isdt[0];
					// document.getElementById('unitinv').innerHTML = isdt[1];

				}
			} else  {
				busy_off();
                error_catch(con.status);
			}
		} 
	}
}

function loaddatadt() {
	notransaksi= document.getElementById('notransaksi').value;
	unit= document.getElementById('unit').value;
	tanggaltbs1= document.getElementById('tanggaltbs1').value;
	tanggaltbs2= document.getElementById('tanggaltbs2').value;
	kodecustomer= document.getElementById('kodecustomer').value;
	sortasi= document.getElementById('sortasi').value;
	param = 'method=loaddatadt';
	param+='&notransaksi='+notransaksi+'&unit='+unit+'&sortasi='+sortasi;
	param+='&tanggaltbs1='+tanggaltbs1+'&tanggaltbs2='+tanggaltbs2+'&kodecustomer='+kodecustomer;
	// alert(param);
	tujuan = 'kebun_tbsjual_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('listdatadt').innerHTML = con.responseText;
					 leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function deleteht(notransaksi){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'kebun_tbsjual_slave.php';
	alertify.confirm("Informasi","Hapus transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	// post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
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



function saveht() {
	
	param='';
	kodecustomer= document.getElementById('kodecustomer').value;
	notransaksi= document.getElementById('notransaksi').value;
	tanggal= document.getElementById('tanggal').value;
	unit= document.getElementById('unit').value;
	sortasi= document.getElementById('sortasi').value;
	nokontrak= document.getElementById('nokontrak').value;
	
	
	if(tanggal==''){
		alert('Tanggal tidak boleh kosong');return;
	}
	if(kodecustomer==''){
		alert('Customer tidak boleh kosong');return;
	}
	if(unit==''){
		alert('unit tidak boleh kosong');return;
	}
	// if(sortasi==''){
	// 	alert('sortasi tidak boleh kosong');return;
	// }
	
	method = 'saveht';
	param += '&unit=' + unit + '&tanggal=' + tanggal+ '&kodecustomer=' + kodecustomer+'&notransaksi=' + notransaksi;
	param += '&method=' + method;
	param += '&sortasi=' + sortasi;
	param += '&nokontrak=' + nokontrak;
	// alert(param);
	tujuan = 'kebun_tbsjual_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display='block';
					document.getElementById('unit').disabled=true;
					document.getElementById('kodecustomer').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('tanggaltbs1').disabled=true;
					document.getElementById('tanggaltbs2').disabled=true;
					document.getElementById('keteranganht').disabled=true;
					document.getElementById('notransaksi').disabled=true;
					document.getElementById('sortasi').disabled=true;
					document.getElementById('nokontrak').disabled=true;
					loaddatadt();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}




maxf=0
sekarang=1;
function savedt(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}



function loopsave(currRow,maxRow) {
    param = "";
	notransaksi=trim(document.getElementById('notransaksi').value);
	unit=trim(document.getElementById('unit').value);
	kodecustomer=trim(document.getElementById('kodecustomer').value);
	nokontrak=trim(document.getElementById('nokontrak').value);
    tanggal=trim(document.getElementById('tanggal').value);
    tanggaltbs1=trim(document.getElementById('tanggaltbs1').value);
    tanggaltbs2=trim(document.getElementById('tanggaltbs2').value);
	keteranganht=trim(document.getElementById('keteranganht').value);
	sortasi=trim(document.getElementById('sortasi').value);
	kodero=trim(document.getElementById('kodero').value);
	
	notiket=trim(document.getElementById('notiket'+currRow).innerHTML);
	nospb=trim(document.getElementById('nospb'+currRow).innerHTML);
	nokendaraan=trim(document.getElementById('nokendaraan'+currRow).innerHTML);
	tanggalpks=trim(document.getElementById('tanggalpks'+currRow).innerHTML);
	tanggalspb=trim(document.getElementById('tanggalspb'+currRow).innerHTML);
	// kgbruto=trim(document.getElementById('kgbruto'+currRow).innerHTML);
	// kgpotongan=trim(document.getElementById('kgpotongan'+currRow).innerHTML);
	// kgnetto=trim(document.getElementById('kgnetto'+currRow).innerHTML);
	// jjg=trim(document.getElementById('jjg'+currRow).innerHTML);
	blok=trim(document.getElementById('blok'+currRow).innerHTML);
	tahuntanam=trim(document.getElementById('tahuntanam'+currRow).innerHTML);
	rpkg=trim(document.getElementById('rpkg'+currRow).innerHTML);
	totalrp =trim(document.getElementById('totalrp'+currRow).innerHTML);

	// New
	kgbruto=trim(document.getElementById('kgbruto'+currRow).value);
	kgpotongan=trim(document.getElementById('kgpotongan'+currRow).value);
	kgnetto=trim(document.getElementById('kgnetto'+currRow).value);
	jjg=trim(document.getElementById('jjg'+currRow).innerHTML);
	// End New
	
	// OLD
	oldkgbruto=trim(document.getElementById('oldkgbruto'+currRow).value);
	oldkgpotongan=trim(document.getElementById('oldkgpotongan'+currRow).value);
	oldkgnetto=trim(document.getElementById('oldkgnetto'+currRow).value);
	oldjjg=trim(document.getElementById('oldjjg'+currRow).innerHTML);
	// End OLD

	intiplasma =trim(document.getElementById('intiplasma'+currRow).innerHTML);

	param+='&method=savedt';
	param+='&notransaksi='+notransaksi+'&unit='+unit+'&kodecustomer='+kodecustomer+'&nokontrak='+nokontrak+'&tanggal='+tanggal+'&tanggaltbs1='+tanggaltbs1+'&tanggaltbs2='+tanggaltbs2+'&keteranganht='+keteranganht+'&notiket='+notiket+'&nospb='+nospb+'&nokendaraan='+nokendaraan+'&tanggalpks='+tanggalpks+'&tanggalspb='+tanggalspb+'&kgbruto='+kgbruto+'&kgpotongan='+kgpotongan+'&kgnetto='+kgnetto+'&jjg='+jjg+'&blok='+blok+'&tahuntanam='+tahuntanam+'&rpkg='+rpkg+'&totalrp='+totalrp+'&intiplasma='+intiplasma;
	param+='&sortasi='+sortasi;
	param+='&kodero='+kodero;

	// OLD Data
	param+='&oldkgbruto='+oldkgbruto;
	param+='&oldkgpotongan='+oldkgpotongan;
	param+='&oldkgnetto='+oldkgnetto;
	param+='&oldjjg='+oldjjg;
	// Before Update Revision

	param+='&currRow='+currRow;
	
	tujuan = 'kebun_tbsjual_slave.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='red';
					// unlockScreen();
                } else {
					document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						alert('Done');
						loaddatadt();
                    } else {
						loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}


function hitungBruto(idnum,idnummax) {
	var bruto = document.getElementById('kgbruto'+idnum).value;  
	var netto = document.getElementById('kgnetto'+idnum).value;  
	var potongan = document.getElementById('kgpotongan'+idnum).value;  
	var rpkg = remove_comma_var(document.getElementById('rpkg'+idnum).innerHTML);  

	// Ambil Total
	var ttlbruto = document.getElementById('ttlkgbruto').innerHTML;
	var ttlkgpot = document.getElementById('ttlkgpot').innerHTML;
	var ttlkgnetto = document.getElementById('ttlkgnetto').innerHTML;
	var ttlrp = document.getElementById('ttlrp').innerHTML;

	// ============================ //
	// NEW
	// ============================ //

	// Ambil Total Class
	// Pilih semua elemen dengan kelas 'brutox'
	const brutoxElements = document.querySelectorAll('.kgbrutox');
	const nettoxElements = document.querySelectorAll('.kgnettox');
	const totalrpxElements = document.querySelectorAll('.totalrpx');

	// Menghitung total nilai
	let total = 0;
	let totalnetto = 0;
	let totaltotalrp = 0;

	brutoxElements.forEach(element => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const value = parseFloat(element.value.replace(/,/g, '')) || 0;
		total += value;

		// Cek value per masing-masing id
		// console.log('value bruto:', value);
	});
	
	nettoxElements.forEach(elementnetto => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const valuenetto = parseFloat(elementnetto.value.replace(/,/g, '')) || 0;
		totalnetto += valuenetto;

		// Cek value per masing-masing id
		// console.log('value bruto:', value);
	});


	totalrpxElements.forEach(elementtotalrp => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const valuetotalrp = parseFloat(elementtotalrp.textContent.replace(/,/g, '')) || 0;
		totaltotalrp += valuetotalrp;

		// Cek value per masing-masing id
		// console.log('value bruto:', value);
	});

	// Tampilkan totalnya
	// console.log('Total:', total);

	// ============================ //
	// END
	// ============================ //

	// Perhitungan
	// Netto dibulatkan ke kg genap dulu sebelum dikali harga, biar total rupiahnya sama
	// dengan rekap grading manual (Excel) yang juga membulatkan netto per baris ke kg genap.
	var newJumlah = Math.round(bruto-potongan);
	var newTotalRupiah = newJumlah*rpkg;

	// Masih bisa minus
	// if(bruto < potongan) {
	// 	var newJumlah = 0;
	// }

	// Cek Error
	// console.log("rupiah : "+ rpkg + "\n")
	// console.log("total rupiah : "+newTotalRupiah)

	document.getElementById('kgnetto'+idnum).value = newJumlah;
	document.getElementById('totalrp'+idnum).innerHTML = numberFormat(newTotalRupiah,2);
	document.getElementById('ttlkgbruto').innerHTML = numberFormat(total,0); 
	document.getElementById('ttlkgnetto').innerHTML = numberFormat(totalnetto,0); 
	document.getElementById('ttlrp').innerHTML = numberFormat(totaltotalrp,0); 
}
function hitungPotongan(idnum,idnummax) {
	var bruto = document.getElementById('kgbruto'+idnum).value;  
	var netto = document.getElementById('kgnetto'+idnum).value;  
	var potongan = document.getElementById('kgpotongan'+idnum).value;  
	var rpkg = remove_comma_var(document.getElementById('rpkg'+idnum).innerHTML);

	// Ambil Total
	var ttlbruto = document.getElementById('ttlkgbruto').innerHTML;
	var ttlkgpot = document.getElementById('ttlkgpot').innerHTML;
	var ttlkgnetto = document.getElementById('ttlkgnetto').innerHTML;
	var ttlrp = document.getElementById('ttlrp').innerHTML;

	// Ambil Total Class
	// Pilih semua elemen dengan kelas 'brutox'
	const kgpotxElements = document.querySelectorAll('.kgpotx');

	// Menghitung total nilai
	let total = 0;

	kgpotxElements.forEach(element => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const value = parseFloat(element.value.replace(/,/g, '')) || 0;
		total += value;

		// Cek value per masing-masing id
		// console.log('value potongan:', value);
	});

	// Tampilkan totalnya
	// console.log('Total:', total);

	// Perhitungan
	// Netto dibulatkan ke kg genap dulu sebelum dikali harga (samain sama rekap Excel manual).
	var newJumlah = Math.round(bruto-potongan);
	var newTotalRupiah = newJumlah*rpkg;

	document.getElementById('kgnetto'+idnum).value = newJumlah;
	document.getElementById('totalrp'+idnum).innerHTML = numberFormat(newTotalRupiah,2);
	document.getElementById('ttlkgpot').innerHTML = numberFormat(total,0);
}
function hitungNetto(idnum,idnummax) {
	var bruto = document.getElementById('kgbruto'+idnum).value;  
	var netto = document.getElementById('kgnetto'+idnum).value;  
	var potongan = document.getElementById('kgpotongan'+idnum).value;  
	var rpkg = remove_comma_var(document.getElementById('rpkg'+idnum).innerHTML);

	// Ambil Total
	var ttlbruto = document.getElementById('ttlkgbruto').innerHTML;
	var ttlkgpot = document.getElementById('ttlkgpot').innerHTML;
	var ttlkgnetto = document.getElementById('ttlkgnetto').innerHTML;
	var ttlrp = document.getElementById('ttlrp').innerHTML;

	// ============================ //
	// NEW
	// ============================ //
	
	// Ambil Total Class
	// Pilih semua elemen dengan kelas 'brutox'
	const brutoxElements = document.querySelectorAll('.kgbrutox');
	const nettoxElements = document.querySelectorAll('.kgnettox');
	const totalrpxElements = document.querySelectorAll('.totalrpx');

	// Menghitung total nilai
	let total = 0;
	let totalnetto = 0;

	brutoxElements.forEach(element => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const value = parseFloat(element.value.replace(/,/g, '')) || 0;
		total += value;
	});
	
	nettoxElements.forEach(elementnetto => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const valuenetto = parseFloat(elementnetto.value.replace(/,/g, '')) || 0;
		totalnetto += valuenetto;
	});

	totalrpxElements.forEach(elementtotalrp => {
		// Ambil nilai dari setiap elemen, hapus koma, dan tambahkan ke total
		const valuetotalrp = parseFloat(elementtotalrp.textContent.replace(/,/g, '')) || 0;
		totaltotalrp += valuetotalrp;

		// Cek value per masing-masing id
		// console.log('value bruto:', value);
	});

	// ============================ //
	// END
	// ============================ //

	// Perhitungan
	// Netto dibulatkan ke kg genap dulu (samain sama rekap Excel manual). Total rupiah dihitung
	// dari netto (bukan dari newJumlah/bruto hasil turunan) - konsisten sama hitungBruto/hitungPotongan.
	var nettoBulat = Math.round(netto);
	var newJumlah = nettoBulat-potongan;
	var newTotalRupiah = nettoBulat*rpkg;

	document.getElementById('kgnetto'+idnum).value = nettoBulat;
	document.getElementById('kgbruto'+idnum).value = newJumlah;
	document.getElementById('totalrp'+idnum).innerHTML = numberFormat(newTotalRupiah,2);
	document.getElementById('ttlkgbruto').innerHTML = numberFormat(total,0);
	document.getElementById('ttlkgnetto').innerHTML = numberFormat(totalnetto,0);
	document.getElementById('ttlrp').innerHTML = numberFormat(totaltotalrp,0); 
}