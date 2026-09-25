function cancelkgpks(){
	document.getElementById('brmsk').value=0;
	document.getElementById('brklr').value=0;
	document.getElementById('brnet').value=0;
	document.getElementById('buahdikembalikan').value=0;
	closeDialog5();
}
function savekgpks(){
	kgin = document.getElementById('brmsk').value;
	kgout = document.getElementById('brklr').value;
	buahdikembalikan = document.getElementById('buahdikembalikan').value;
	kgnet = document.getElementById('brnet').value;
	notiket = document.getElementById('notiketkgpks').value;
	spbpabrik = document.getElementById('spbpabrik').value;
	tahuntanam2 = document.getElementById('tahuntanam2').value;
	numrow = document.getElementById('numrow').value;
	tanggalpks = document.getElementById('tglpksx').value;
	potongx = document.getElementById('potongx').value;
	
	kgin = remove_comma_var(kgin);
	kgout = remove_comma_var(kgout);
	kgnet = remove_comma_var(kgnet);
	buahdikembalikan = remove_comma_var(buahdikembalikan);
	
	if(kgin=='' || kgin==0 || kgout=='' || kgout==0 || kgnet=='' || kgnet==0){
		alert("Warning : Berat Masuk, Berat Keluar dan Berat Bersih tidak boleh kosong !");
		return false;
	}
	
	if(spbpabrik == ''){
		alert("Warning : SPB Pabrik Tidak Boleh Kosong");
		return false;	
	}

	if(tahuntanam2 == ''){
		alert("Warning : tahun tanam Tidak Boleh Kosong");
		return false;	
	}

	if (tahuntanam2.length !== 4){
		alert('Tahun Tanam Wajib 4 digit');
		return false;
	}


	
	param = 'proses=saveaddkgpks' + '&notiket=' + notiket+ '&kgin=' + kgin;
	param += '&kgout=' + kgout+ '&kgnet=' + kgnet;
	param += '&tanggalpks=' + tanggalpks;
	param += '&potongx=' + potongx;
	param += '&spbpabrik=' + spbpabrik;
	param += '&tahuntanam2=' + tahuntanam2;
	param += '&buahdikembalikan=' + buahdikembalikan;
    tujuan = 'kebun_slave_timbangke_eksternal.php';
	if (confirm("Pastikan data yang anda input benar, karena setelah di simpan tidak bisa di edit lagi.")) {
		post_response_text(tujuan, param, respog);
	}
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    closeDialog5();
					loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnetkgpks(){
	kgin = document.getElementById('brmsk').value;
	kgout = document.getElementById('brklr').value;
	kgin = remove_comma_var(kgin);
	kgout = remove_comma_var(kgout);
	
	kgnet = parseFloat(kgin)-parseFloat(kgout);
	if(isNaN(kgnet)){
		kgnet = 0;
	}
	if(kgnet < 0){
		document.getElementById('brklr').value=0;
		document.getElementById('brnet').value=0;
	}
		document.getElementById('brnet').value= numberFormat(kgnet);
}

function addkgpks(notiket,numrow,ev){
	width = '';
    height = '';
    content = "<fieldset><legend>Add Kg PKS</legend><div id=contkgpks style=\"width:270px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog5(title, content, width, height, ev);
	
	param = 'proses=addkgpks' + '&notiket=' + notiket+ '&numrow=' + numrow;
    tujuan = 'kebun_slave_timbangke_eksternal.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contkgpks').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefileall(id, nospb) {
	param = "proses=deletefileall";
	param += "&id=" + id;
	param += "&nospb=" + nospb;

	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(id, namafile) {
	nospb = document.getElementById('spbId').value;
	param = "proses=deletefile";
	param += "&id=" + id;
	param += "&namafile=" + namafile;
	param += "&nospb=" + nospb;

	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(nospb) {
	param = 'proses=loadfiles&nospb=' + nospb;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
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
	var nospb = document.getElementById('spbId').value;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("nospb", nospb);

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	if (nospb == "") {
		alert("warning : Silahkan pilih nomor SPB terlebih dahulu !!!");
		return false;
	}
	document.getElementById('btnsubmit').disabled = true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "kebun_slave_timbangke_eksternal.php?proses=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				document.getElementById('btnsubmit').disabled = false;
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getjjg(nospb) {
	var tktkebun = document.getElementById('tktkebun').value;
	param = 'proses=getjjg' + '&nospb=' + nospb + '&tktkebun=' + tktkebun;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					data = con.responseText.split("####");
					document.getElementById('jmlhJjg').value = fmtAngka(data[0]);
					// document.getElementById('jmlhJjg').value = fmtAngka(data[0]);
					document.getElementById('nmSupir').value = data[2];
					document.getElementById('kdKend').value = data[3];
					document.getElementById('datapotongan').innerHTML = data[4];
						document.getElementById('potKg').value = '';
						aturPotKg();
						getBersih(0);
					document.getElementById('pabriktujuan').value = data[5];
					document.getElementById('pabriktujuan').disabled = (document.getElementById('pabriktujuan').value != '');
					refreshSelect2('pabriktujuan');
					loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getNosbp() {
	tgl = document.getElementById('tgl').value;
	param = 'proses=getNosbp' + '&tgl=' + tgl;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('spbId').innerHTML = con.responseText.split("####")[0];
					refreshSelect2('spbId');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getBersih(xyz) {

	if (xyz == 1) {
		brtMskpmks = document.getElementById('brtMskpmks').value;
		brtKlrpmks = document.getElementById('brtKlrpmks').value;
		brtBrshpmks = parseInt(brtMskpmks) - parseInt(brtKlrpmks);
		if (isNaN(brtBrshpmks)) {
			brtBrshpmks = 0;
		}
		if (brtBrshpmks < 0) {
			//alert("Nilai tidak boleh minus");
			brtBrshpmks = 0;
			document.getElementById('brtKlrpmks').value=0;
			//return;
		}
		document.getElementById('brtBrshpmks').value = brtBrshpmks;
	}
	if (xyz == 0) {
		brtBrsh = angka('brtMsk') - angka('brtKlr') - angka('potKg');
		document.getElementById('brtBrsh').value = numberFormat(brtBrsh, 0);
	}

}
function saveData() {
	hitungUlang();
	tgl        = document.getElementById('tgl').value;
	nosbp      = document.getElementById('spbId').value;
	jmmsk      = document.getElementById('jmMasuk').value;
	mntmsk     = document.getElementById('mntMasuk').value;
	jmklr      = document.getElementById('jmKeluar').value;
	mntklr     = document.getElementById('mntKeluar').value;
	kdknd      = document.getElementById('kdKend').value;
	nmspr      = document.getElementById('nmSupir').value;
	jmjjg      = angka('jmlhJjg');
	notiket    = document.getElementById('notiket').value;
	brtms      = angka('brtMsk');
	brtklr     = angka('brtKlr');
	brtbrsh    = angka('brtMsk') - angka('brtKlr') - angka('potKg');
	potDt      = angka('potKg');
	spbpabrik = document.getElementById('spbpabrik').value;
	tahuntanam2 = document.getElementById('tahuntanam2').value;
	buahdikembalikan = document.getElementById('buahdikembalikan').value;
	buahdikembalikan = remove_comma_var(buahdikembalikan);
	
	// nokontrak  = document.getElementById('nokontrak').value;
	// nodo       = document.getElementById('nodo').value;
	// tanggalpks = document.getElementById('tglpks').value;
	// brtmspmks  = document.getElementById('brtMskpmks').value;
	// brtklrpmks = document.getElementById('brtKlrpmks').value;
	// brtbrshpmks= document.getElementById('brtBrshpmks').value;
	// // kgJual     = document.getElementById('kgJual').value;

	// jjgSortsi  = document.getElementById('JjgSortasi').value;
	noTrans    = document.getElementById('notrans').value;
	prs        = document.getElementById('proses').value;
	
	jamMasuk = jmmsk + ":" + mntmsk + ":00";
	jamKeluar = jmklr + ":" + mntklr + ":00";

	// potongan
	var elements = document.querySelectorAll('[id^="dt_potongan_"]');
	var jumlah = elements.length;
	if(jumlah > 0){
		var TotaljumlahPotongan = 0;  // Menginisialisasi variabel TotaljumlahPotongan
		strUrl_potongan = "";
		for (var index = 1; index <= jumlah; index++) {  // Mengubah nilai ke angka
			strUrl_potongan +='&nilai_potongan[]='+angka('dt_potongan_' + index)+'&kode_potongan[]='+trim(document.getElementById('kode_potongan_'+index).value);
		}
	}else{
		var elements = document.querySelectorAll('[id^="dt_edit_potongan_"]');
		var jumlah = elements.length;
		var TotaljumlahPotongan = 0;  // Menginisialisasi variabel TotaljumlahPotongan
		strUrl_potongan = "";
		for (var index = 1; index <= jumlah; index++) {  // Mengubah nilai ke angka
			strUrl_potongan +='&nilai_potongan[]='+angka('dt_edit_potongan_' + index)+'&kode_potongan[]='+trim(document.getElementById('kode_potongan_'+index).value);
		}
	}
	// akhir potongan

	if(spbpabrik == ''){
		alert("Warning : SPB Pabrik Tidak Boleh Kosong");
		return false;	
	}

	if(tahuntanam2 == ''){
		alert("Warning : tahun tanam Tidak Boleh Kosong");
		return false;	
	}

	if (tahuntanam2.length !== 4){
		alert('Tahun Tanam Wajib 4 digit');
		return false;
	}

	if(nosbp == ''){
		alert("Warning : No. SPB Tidak Boleh Kosong");
		return false;
	}

	if(prs != 'updThnTnm' && document.getElementById('pabriktujuan').value == ''){
		alert("Warning : Tujuan Pabrik Tidak Boleh Kosong");
		return false;
	}

	if(brtbrsh < 0){
		alert("Warning : Berat Bersih tidak boleh kurang dari 0. Berat Masuk harus lebih besar dari Berat Keluar ditambah Potongan.");
		return false;
	}

	param = 'proses=' + prs;
	param += '&tgl=' + tgl;
	param += '&kdKend=' + kdknd;
	// param += '&JjgSortasi=' + jjgSortsi;
	param += '&potKg=' + potDt;
	// param +=  '&nokontrak=' + nokontrak+ '&nodo=' + nodo;
	param += '&nmSupir=' + nmspr;
	param += '&jmlhJjg=' + jmjjg;
	param += '&brtMsk=' + brtms;
	// param += '&brtMskpmks=' + brtmspmks;
	param += '&spbId=' + nosbp;
	param += '&notransaksi=' + noTrans;
	// param += '&kgJual=' + kgJual;
	param += '&brtKlr=' + brtklr;
	param += '&brtBrsh=' + brtbrsh;
	// param += '&brtKlrpmks=' + brtklrpmks;
	// param += '&brtBrshpmks=' + brtbrshpmks;
	param += '&jamMasuk=' + jamMasuk;
	param += '&jamKeluar=' + jamKeluar;
	param += '&notiket=' + notiket;
	param += '&spbpabrik=' + spbpabrik;
	param += '&tahuntanam2=' + tahuntanam2;
	param += '&buahdikembalikan=' + buahdikembalikan;
	param += '&pabriktujuan=' + document.getElementById('pabriktujuan').value;
	param += strUrl_potongan;

	// param += '&tanggalpks=' + tanggalpks;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	document.getElementById('dtlAbn').disabled=true;
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					document.getElementById('dtlAbn').disabled=false;
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					displayList();
				}
			} else {
				busy_off();
				document.getElementById('dtlAbn').disabled=false;
				error_catch(con.status);
			}
		}
	}
}
function refreshSelect2(id) {
	if (window.jQuery) {
		jQuery('#' + id).trigger('change.select2');
	}
}
if (window.jQuery) {
	jQuery(document).ready(function () {
		jQuery('#spbId').on('change', function () {
			getjjg(this.value);
		});
	});
}
function angka(id) {
	return parseFloat(remove_comma_var(String(document.getElementById(id).value))) || 0;
}
function fmtAngka(v) {
	v = parseFloat(v);
	return isNaN(v) ? '' : numberFormat(v, 0);
}
function hitungUlang() {
	if (document.querySelectorAll('[id^="dt_potongan_"], [id^="dt_edit_potongan_"]').length > 0) {
		getPotongan();
	} else {
		getBersih(0);
	}
}
function aturPotKg() {
	var adaFraksi = document.querySelectorAll('[id^="dt_potongan_"], [id^="dt_edit_potongan_"]').length > 0;
	document.getElementById('potKg').disabled = adaFraksi;
}
function loadBadgeBelum() {
	post_response_text('kebun_slave_timbangke_eksternal.php', 'proses=countBelumTimbang', respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				var jml = parseInt(con.responseText) || 0;
				var b = document.getElementById('badgeBelum');
				b.innerHTML = jml;
				b.style.display = (jml > 0) ? 'inline-block' : 'none';
			} else {
				busy_off();
			}
		}
	}
}
function spbBelumTimbang() {
	post_response_text('kebun_slave_timbangke_eksternal.php', 'proses=listBelumTimbang', respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup('SPB Belum Ada Hasil Timbang', con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('50%', '70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function paramFilterBaru() {
	var p = '';
	p += '&spbpabriksrc=' + encodeURIComponent(document.getElementById('spbpabriksrc').value);
	p += '&supirsrc=' + encodeURIComponent(document.getElementById('supirsrc').value);
	p += '&pabriktujuansrc=' + encodeURIComponent(document.getElementById('pabriktujuansrc').value);
	p += '&periodesrc=' + encodeURIComponent(document.getElementById('periodesrc').value);
	return p;
}
function dataKePDF(ev) {
	var tgl = document.getElementById('tgl_cari').value;
	var tglSampai = document.getElementById('tgl_cari_sampai').value;
	if (tglSampai != '' && tgl == '') {
		alert('Warning : Jika tanggal sampai terisi maka tanggal dari nya harus terisi!!!');
		return;
	}
	var param = 'nosbpCr=' + encodeURIComponent(document.getElementById('nosbpCr').value);
	param += '&tahuntanamsrc=' + encodeURIComponent(document.getElementById('ttsrc').value);
	param += '&tgl_cari=' + tgl + '&tgl_cari_sampai=' + tglSampai;
	param += paramFilterBaru();
	var tujuan = 'kebun_timbangke_eksternal_pdflist.php';
	alertify.popuppdf('title', "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + '?' + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}
if (window.jQuery) {
	jQuery(document).ready(function () {
		jQuery('#ttsrc, #pabriktujuansrc, #periodesrc').on('change', function () {
			loadData(0);
		});
	});
}
function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('headher').style.display = 'none';
	document.getElementById('nosbpCr').value = '';
	document.getElementById('tgl_cari').value = '';
	document.getElementById('tgl_cari_sampai').value = '';
	document.getElementById('spbpabriksrc').value = '';
	document.getElementById('supirsrc').value = '';
	if (window.jQuery) {
		jQuery('#ttsrc, #pabriktujuansrc').val('').trigger('change.select2');
		jQuery('#periodesrc').val(periodeDefault).trigger('change.select2');
	}
	enabledData();
	cancelData();
	loadData(0);

}
function enabledData() {
	document.getElementById('tgl').disabled = false;
	document.getElementById('kdKend').disabled = false;
	document.getElementById('nmSupir').disabled = false;
	document.getElementById('jmlhJjg').disabled = false;
	document.getElementById('brtMsk').disabled = false;
	document.getElementById('brtKlr').disabled = false;
	document.getElementById('jmMasuk').disabled = false;
	document.getElementById('mntMasuk').disabled = false;
	document.getElementById('jmKeluar').disabled = false;
	document.getElementById('mntKeluar').disabled = false;
	document.getElementById('JjgSortasi').disabled = false;
	document.getElementById('nokontrak').disabled = false;
	document.getElementById('notiket').disabled = false;
	document.getElementById('tglpks').disabled = false;
	document.getElementById('brtMskpmks').disabled = false;
	document.getElementById('brtKlrpmks').disabled = false;
	document.getElementById('brtBrshpmks').disabled = false;
	document.getElementById('tahuntanam2').disabled = false;
	document.getElementById('spbpabrik').disabled = false;
	document.getElementById('spbId').disabled = false;
	refreshSelect2('spbId');
	document.getElementById('buahdikembalikan').disabled = false;
	document.getElementById('pabriktujuan').disabled = false;
	refreshSelect2('pabriktujuan');
	document.getElementById('nodo').disabled = false;

	var elements = document.querySelectorAll('[id^="dt_potongan_"]');
	var jumlah = elements.length;
	
	if(jumlah > 0){
		for (var index = 1; index <= jumlah; index++) {
			document.getElementById('dt_potongan_' + index).disabled = false;
		}
	}else{
		var elements = document.querySelectorAll('[id^="dt_edit_potongan_"]');
		var jumlah = elements.length;
		for (var index = 1; index <= jumlah; index++) {
			document.getElementById('dt_edit_potongan_' + index).disabled = false;
		}
	}
	document.getElementById('uplFileId').style.display = '';
}
function cancelData() {
	document.getElementById('dtlAbn').disabled=false;
	document.getElementById('tgl').value = '';
	document.getElementById('kdKend').value = '';
	document.getElementById('nmSupir').value = '';
	document.getElementById('jmlhJjg').value = '';
	document.getElementById('brtMsk').value = '';
	document.getElementById('brtKlr').value = '';
	document.getElementById('brtBrsh').value = '';
	document.getElementById('jmMasuk').value = '00';
	document.getElementById('mntMasuk').value = '00';
	document.getElementById('jmKeluar').value = '00';
	document.getElementById('mntKeluar').value = '00';
	document.getElementById('JjgSortasi').value = '';
	document.getElementById('potKg').value = '';
	document.getElementById('nokontrak').value = '';
	document.getElementById('notiket').value = '';
	document.getElementById('tglpks').value = '';
	document.getElementById('proses').value = 'insert';
	document.getElementById('brtMskpmks').value = '';
	document.getElementById('brtKlrpmks').value = '';
	document.getElementById('brtBrshpmks').value = '';
	document.getElementById('tahuntanam2').value = '';
	document.getElementById('spbpabrik').value = '';
	document.getElementById('buahdikembalikan').value = '0';
	document.getElementById('pabriktujuan').value = '';
	refreshSelect2('pabriktujuan');
	document.getElementById('spbId').innerHTML = '';
	refreshSelect2('spbId');
	document.getElementById('tktkebun').value = '';
	document.getElementById('notrans').value = '';
	document.getElementById('nodo').innerHTML = '';
	document.getElementById('datapotongan').innerHTML = '';
	document.getElementById('listfiles').innerHTML = '';
	document.getElementById('potKg').disabled = true;

	var elements = document.querySelectorAll('[id^="dt_potongan_"]');
	var jumlah = elements.length;
	if(jumlah > 0){
		for (var index = 1; index <= jumlah; index++) {
			document.getElementById('dt_potongan_' + index).value = '0';
		}
	}else{
		var elements = document.querySelectorAll('[id^="dt_edit_potongan_"]');
		var jumlah = elements.length;
		for (var index = 1; index <= jumlah; index++) {
			document.getElementById('dt_edit_potongan_' + index).value = '0';
		}
	}
	enabledData();
	getNosbp();
}
function loadData(num,tipe='html') {
	nospbcr = document.getElementById('nosbpCr').value;
	ttsrc = document.getElementById('ttsrc').value;
	tgl = document.getElementById('tgl_cari').value;
	tgl_sampai = document.getElementById('tgl_cari_sampai').value;
	param = 'proses=loadNewData' + '&nosbpCr=' + nospbcr;
	param += '&tahuntanamsrc=' + ttsrc;
	param += '&tgl_cari=' + tgl;
	param += '&tgl_cari_sampai=' + tgl_sampai;
	param += paramFilterBaru();
	param += '&page=' + num;
	if(tipe == 'excel'){
		tipe_tampil = 'excel';
		param += '&tipe=' + tipe_tampil;
	}else{
		tipe_tampil = 'html';
		param += '&tipe=' + tipe_tampil;
	}
	tujuan = 'kebun_slave_timbangke_eksternal.php';

	if (tipe != "html") {
   		 printnopopup(tujuan+'?'+param)
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
					document.getElementById('contain').innerHTML = con.responseText;
					loadBadgeBelum();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = "";
    height = "";
    content =
      "<iframe frameborder=0 width=100% height=100% src='" +
      tujuan +
      "'></iframe>";
    showDialog1(title, content, width, height, ev);
  }


function fillField(tahuntanam2,spbpabrik,notransaksi, jammask2, jamklur, nokendaraan, supir, notiket, jumlahtandan1, beratmasuk, beratkeluar, beratbersih, jjgsortasi, kgpotsortas, nospb, tgl, nokontrak,nodo,pabriktujuan,buahdikembalikan, beratmasukpmks,beratkeluarpmks,beratbersihpmks,tanggalpks,kgjual) {
	enabledData();
	
	document.getElementById('headher').style.display = "block";
	document.getElementById('listData').style.display = "none";
	document.getElementById('proses').value = 'updatedata';
	document.getElementById('notrans').value = notransaksi;
	jmmsk = jammask2.split(":");
	jammsk = document.getElementById('jmMasuk');
	for (x = 0; x < jammsk.length; x++) {
		if (jammsk.options[x].value == jmmsk[0]) {
			jammsk.options[x].selected = true;
		}
	}
	mntmsk = document.getElementById('mntMasuk');
	for (x = 0; x < mntmsk.length; x++) {
		if (mntmsk.options[x].value == jmmsk[1]) {
			mntmsk.options[x].selected = true;
		}
	}
	jmklr = jamklur.split(":");
	jamklr = document.getElementById('jmKeluar');
	for (x = 0; x < jamklr.length; x++) {
		if (jamklr.options[x].value == jmklr[0]) {
			jamklr.options[x].selected = true;
		}
	}
	mntklr = document.getElementById('mntKeluar');
	for (x = 0; x < mntklr.length; x++) {
		if (mntklr.options[x].value == jmklr[1]) {
			mntklr.options[x].selected = true;
		}
	}
	document.getElementById('kdKend').value = nokendaraan;
	document.getElementById('nokontrak').value = nokontrak;
	document.getElementById('nodo').innerHTML = "<option value="+nodo+" selected>"+nodo+"</option>";
	document.getElementById('notiket').value = notiket;
	document.getElementById('nmSupir').value = supir;
	document.getElementById('jmlhJjg').value = fmtAngka(jumlahtandan1);
	document.getElementById('brtMsk').value = fmtAngka(beratmasuk);
	document.getElementById('brtKlr').value = fmtAngka(beratkeluar);
	document.getElementById('brtBrsh').value = fmtAngka(beratbersih);
	document.getElementById('JjgSortasi').value = jjgsortasi;
	document.getElementById('potKg').value = fmtAngka(kgpotsortas);
	document.getElementById('tgl').value = tgl;
	document.getElementById('tahuntanam2').value = tahuntanam2;
	document.getElementById('spbpabrik').value = spbpabrik;
	document.getElementById('pabriktujuan').value = pabriktujuan;
	document.getElementById('pabriktujuan').disabled = (document.getElementById('pabriktujuan').value != '');
	refreshSelect2('pabriktujuan');
	document.getElementById('buahdikembalikan').value = fmtAngka(buahdikembalikan);
	if(tanggalpks=='00-00-0000'){
		tanggalpks='';
	}
	// document.getElementById('tglpks').value = tanggalpks;
	// document.getElementById('brtMskpmks').value = beratmasukpmks;
	// document.getElementById('brtKlrpmks').value = beratkeluarpmks;
	// document.getElementById('brtBrshpmks').value = beratbersihpmks;
	// document.getElementById('kgJual').value = kgjual;
	
	document.getElementById('proses').value = 'update';
	param = 'proses=getNosbp' + '&nospb=' + nospb + '&tgl=' + tgl;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					// document.getElementById('spbId').innerHTML = con.responseText;
					data = con.responseText.split("####");
					document.getElementById('spbId').innerHTML = data[0];
					refreshSelect2('spbId');
					document.getElementById('datapotongan').innerHTML = data[1];
						aturPotKg();
					loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function fillFieldTahunTanam(tahuntanam2,spbpabrik,notransaksi, jammask2, jamklur, nokendaraan, supir, notiket, jumlahtandan1, beratmasuk, beratkeluar, beratbersih, jjgsortasi, kgpotsortas, nospb, tgl, nokontrak,nodo, pabriktujuan,buahdikembalikan,beratbersihpmks,tanggalpks,kgjual) {
	document.getElementById('headher').style.display = "block";
	document.getElementById('listData').style.display = "none";
	document.getElementById('notrans').value = notransaksi;
	jmmsk = jammask2.split(":");
	jammsk = document.getElementById('jmMasuk').disabled=true;
	jammsk = document.getElementById('jmMasuk');
	for (x = 0; x < jammsk.length; x++) {
		if (jammsk.options[x].value == jmmsk[0]) {
			jammsk.options[x].selected = true;
		}
	}
	mntmsk = document.getElementById('mntMasuk').disabled=true;
	mntmsk = document.getElementById('mntMasuk');
	for (x = 0; x < mntmsk.length; x++) {
		if (mntmsk.options[x].value == jmmsk[1]) {
			mntmsk.options[x].selected = true;
		}
	}
	jmklr = jamklur.split(":");
	jamklr = document.getElementById('jmKeluar').disabled=true;
	jamklr = document.getElementById('jmKeluar');
	for (x = 0; x < jamklr.length; x++) {
		if (jamklr.options[x].value == jmklr[0]) {
			jamklr.options[x].selected = true;
		}
	}
	mntklr = document.getElementById('mntKeluar').disabled=true;
	mntklr = document.getElementById('mntKeluar');
	for (x = 0; x < mntklr.length; x++) {
		if (mntklr.options[x].value == jmklr[1]) {
			mntklr.options[x].selected = true;
		}
	}
	document.getElementById('kdKend').value = nokendaraan;
	document.getElementById('nokontrak').value = nokontrak;
	document.getElementById('nodo').innerHTML = "<option value="+nodo+" selected>"+nodo+"</option>";
	document.getElementById('notiket').value = notiket;
	document.getElementById('nmSupir').value = supir;
	document.getElementById('jmlhJjg').value = fmtAngka(jumlahtandan1);
	document.getElementById('brtMsk').value = fmtAngka(beratmasuk);
	document.getElementById('brtKlr').value = fmtAngka(beratkeluar);
	document.getElementById('brtBrsh').value = fmtAngka(beratbersih);
	document.getElementById('JjgSortasi').value = jjgsortasi;
	document.getElementById('potKg').value = fmtAngka(kgpotsortas);
	document.getElementById('tgl').value = tgl;
	document.getElementById('tahuntanam2').value = tahuntanam2;
	document.getElementById('spbpabrik').value = spbpabrik;
	document.getElementById('buahdikembalikan').value = fmtAngka(buahdikembalikan);

	document.getElementById('kdKend').disabled = true;
	document.getElementById('pabriktujuan').value = pabriktujuan;
	document.getElementById('pabriktujuan').disabled = true;
	refreshSelect2('pabriktujuan');
	document.getElementById('nokontrak').disabled = true;
	document.getElementById('nodo').disabled = true;
	document.getElementById('notiket').disabled = true;
	document.getElementById('nmSupir').disabled = true;
	document.getElementById('jmlhJjg').disabled = true;
	document.getElementById('brtMsk').disabled = true;
	document.getElementById('brtKlr').disabled = true;
	document.getElementById('brtBrsh').disabled = true;
	document.getElementById('JjgSortasi').disabled = true;
	document.getElementById('potKg').disabled = true;
	document.getElementById('tgl').disabled = true;
	document.getElementById('spbpabrik').disabled = true;
	document.getElementById('buahdikembalikan').disabled = true;
	if(tanggalpks=='00-00-0000'){
		tanggalpks='';
	}
	// document.getElementById('tglpks').value = tanggalpks;
	// document.getElementById('brtMskpmks').value = beratmasukpmks;
	// document.getElementById('brtKlrpmks').value = beratkeluarpmks;
	// document.getElementById('brtBrshpmks').value = beratbersihpmks;
	// document.getElementById('kgJual').value = kgjual;
	
	document.getElementById('proses').value = 'updThnTnm';
	param = 'proses=getNosbp' + '&nospb=' + nospb + '&tgl=' + tgl;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					// document.getElementById('spbId').innerHTML = con.responseText;
					data = con.responseText.split("####");
					document.getElementById('spbId').innerHTML = data[0];
					refreshSelect2('spbId');
					document.getElementById('spbId').disabled = true;
					refreshSelect2('spbId');
					document.getElementById('datapotongan').innerHTML = data[1];
					var elements = document.querySelectorAll('[id^="dt_potongan_"]');
					var jumlah = elements.length;
					if(jumlah > 0){
						for (var index = 1; index <= jumlah; index++) {
							document.getElementById('dt_potongan_' + index).disabled = true;
						}
					}else{
						var elements = document.querySelectorAll('[id^="dt_edit_potongan_"]');
						var jumlah = elements.length;
						for (var index = 1; index <= jumlah; index++) {
							document.getElementById('dt_edit_potongan_' + index).disabled = true;
						}
					}
					document.getElementById('uplFileId').style.display = 'none';
					// loadfiles(nospb);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteData(notrans, nospb) {
	param = 'proses=deleteData' + '&notransaksi=' + notrans;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	if (confirm("Anda Yakin Ingin Menghapus?")) {
		post_response_text(tujuan, param, respog);
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						//alert(con.responseText);
						deletefileall(notrans, nospb);
						displayList();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}

}

function searchNosibp(title, content, ev) {
	width = '';
	height = '';
	getFormNosibp();
	showDialog1(title, content, width, height, ev);
}

function getFormNosibp() {
	param = 'proses=getFormNosipb';
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan + '?' + '', param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('formPencariandata').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function findNosipb() {
	txt = trim(document.getElementById('nosipbcr').value);
	idcust = document.getElementById('custId');
	idcust = idcust.options[idcust.selectedIndex].value;

	param = 'txtfind=' + txt + '&proses=getnosibp' + '&custId=' + idcust;
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('container2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setData (nokontrak,nokontrakx) {
	param = 'nokontrak=' + nokontrakx + '&proses=getnodo';
	tujuan = 'kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('nokontrak').value=nokontrak;
					document.getElementById('nodo').innerHTML=con.responseText;
					closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getberatbersih(){
	bertbersih=document.getElementById('brtBrshpmks').value;
	potKg=document.getElementById('potKg').value;
	hslbersh=parseInt(bertbersih)-parseInt(potKg);
	document.getElementById('kgJual').value=hslbersh;
}
function getPotongan(){
	var TotaljumlahPotongan = 0;
	var prefix = 'dt_potongan_';
	var elements = document.querySelectorAll('[id^="dt_potongan_"]');
	if (elements.length == 0) {
		prefix = 'dt_edit_potongan_';
		elements = document.querySelectorAll('[id^="dt_edit_potongan_"]');
	}
	for (var index = 1; index <= elements.length; index++) {
		TotaljumlahPotongan += angka(prefix + index);
	}
	document.getElementById('potKg').value = numberFormat(TotaljumlahPotongan, 0);
	document.getElementById('brtBrsh').value = numberFormat(angka('brtMsk') - angka('brtKlr') - TotaljumlahPotongan, 0);
}

/*
function printFile(param,tujuan,title,ev){
tujuan=tujuan+"?"+param;
width='600';
height='400';
content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
showDialog2(title,content,width,height,ev);
}
function excel(ev,kodeorg,periodegaji,tipepotongan){
param='method=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
//alert(param);
tujuan='kebun_slave_timbangke_eksternalExcel.php';
judul='Print Excel';
printFile(param,tujuan,judul,ev)
}

function getPrd(){
kdOrg=document.getElementById('kdOrg');
kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
prd=document.getElementById('tglAbsen');
prd=prd.options[prd.selectedIndex].value;
param='periode='+prd+'&proses=getPrd'+'&kdOrg='+kdOrg;
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
//alert(con.responseText);
document.getElementById('tglAbsen').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}

function cariOrg(title,content,ev){
width='500';
height='400';
showDialog1(title,content,width,height,ev);
//alert('asdasd');
}
function findOrg(){
txt=trim(document.getElementById('fnOrg').value);
if(txt==''){
alert('Text is obligatory');
}
else if(txt.length<3){
alert('Text too short');
}
else{
param='txtfind='+txt+'&proses=cariOrg';
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
}
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
//alert(con.responseText);
document.getElementById('container').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}
function setOrg(kdOrg,nmOrg){
document.getElementById('kdOrg').value=kdOrg;
document.getElementById('nmOrg').value=nmOrg;
closeDialog();
}
function findOrg2(){
txt=trim(document.getElementById('crOrg').value);
if(txt==''){
alert('Text is obligatory');
}
else if(txt.length<3){
alert('Text too short');
}
else{
param='txtfind='+txt+'&proses=cariOrg2';
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
}
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
//alert(con.responseText);
document.getElementById('container').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}
function setOrg2(kdOrg,nmOrg){
document.getElementById('kdOrg').value=kdOrg;
document.getElementById('txtsearch').value=nmOrg;
closeDialog();
}
function add_detail(){
kdOrg=document.getElementById('kdOrg');
kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
prd=document.getElementById('tglAbsen');
prd=prd.options[prd.selectedIndex].value;
tpPot=document.getElementById('tpPotongan');
tpPot=tpPot.options[tpPot.selectedIndex].value;
param='kdOrg='+kdOrg+'&proses=createTable';
param+='&periode='+prd+'&tipePot='+tpPot;
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('detailEntry').style.display='block';
document.getElementById('detailIsi').innerHTML=con.responseText;
document.getElementById('tmbLheader').innerHTML='';
lockForm();
}
}
else {
busy_off();
error_catch(con.status);
}
}
}

}

function lockForm(){
document.getElementById('kdOrg').disabled=true;
document.getElementById('tglAbsen').disabled=true;
document.getElementById('tpPotongan').disabled=true;
document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
document.getElementById('kdOrg').disabled=false;
document.getElementById('tglAbsen').disabled=false;
document.getElementById('tpPotongan').disabled=false;
document.getElementById('kdOrg').value='';
document.getElementById('tglAbsen').value='';
document.getElementById('tpPotongan').value='';
document.getElementById('tombolHeader').style.display="block";
}
status_inputan=0;
function addDetail() {
if(status_inputan==0){
if(confirm('Are you sure..?')){
saveData();
}
}
else if(status_inputan!=0){
saveData();
}

}
function saveData(){
kdOrg=document.getElementById('kdOrg');
kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
prd=document.getElementById('tglAbsen');
prd=prd.options[prd.selectedIndex].value;
tpPot=document.getElementById('tpPotongan');
tpPot=tpPot.options[tpPot.selectedIndex].value;
karyId=document.getElementById('krywnId');
karyId=karyId.options[karyId.selectedIndex].value;
rpPot=document.getElementById('rpPot').value;
ketpot=document.getElementById('ketPot').value;


pros=document.getElementById('proses').value;
if(pros!="updateDetail"){
param = "proses=saveData";
}
else{
param = "proses=updateDetail";
}
param+='&kdOrg='+kdOrg;
param+='&periode='+prd+'&tipePot='+tpPot+'&krywnId='+karyId;
param+='&rupPot='+rpPot+'&ketPot='+ketpot
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
status_inputan=1;
lockForm();
showTmbl();
bersihFormDet();
loadDetail();
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}
function editDetail(karyawn,rppot,ketrng) {
document.getElementById('krywnId').value=karyawn;
document.getElementById('krywnId').disabled=true;
document.getElementById('rpPot').value=rppot;
document.getElementById('ketPot').value=ketrng;
document.getElementById('proses').value="updateDetail";
}
statFrm=0;
function showTmbl(){
if(statFrm==0){
document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
}
else if(statFrm==1){
document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
}
}

function bersihFormDet(){
document.getElementById('krywnId').disabled=false;
document.getElementById('ketPot').value='';
document.getElementById('krywnId').value='';
document.getElementById('proses').value="saveData";
}

function delDetail(kdorg,period,krywn,tppot){
param+='&kdOrg='+kdorg;
param+='&periode='+period+'&tipePot='+tppot+'&krywnId='+krywn;
param+='&proses=delDetail';
tujuan='kebun_slave_timbangke_eksternal.php';
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
loadDetail();
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
if(confirm("Deleting, are you sure..?"))
post_response_text(tujuan, param, respog);
}

function loadData(num){
kdorg=document.getElementById('kdOrgCr');
kdorg=kdorg.options[kdorg.selectedIndex].value;
tgl=document.getElementById('tgl_cari').value;
tppot=document.getElementById('tpPotCr');
tppot=tppot.options[tppot.selectedIndex].value;
param='proses=loadNewData'+'&kdOrgCr='+kdorg;
param+='&periodecr='+tgl+'&tipePotCr='+tppot;
param+='&page='+num;
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('contain').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}
function loadDetail(){
kdOrg=document.getElementById('kdOrg');
kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
prd=document.getElementById('tglAbsen');
prd=prd.options[prd.selectedIndex].value;
tpPot=document.getElementById('tpPotongan');
tpPot=tpPot.options[tpPot.selectedIndex].value;
param='kdOrg='+kdOrg+'&periode='+prd+'&tipePot='+tpPot;
param+='&proses=loadDetail';
//alert(param);
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if(con.readyState==4){
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('contentDetail').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}

}
function fillField(kdorg,prder,potong){

kdOrg=document.getElementById('kdOrg');
for(x=0;x<kdOrg.length;x++){
if(kdOrg.options[x].value==kdorg){
kdOrg.options[x].selected=true;
}
}
prd=document.getElementById('tglAbsen');
for(x=0;x<prd.length;x++){
if(prd.options[x].value==prder){
prd.options[x].selected=true;
}
}
tppot=document.getElementById('tpPotongan');
for(x=0;x<tppot.length;x++){
if(tppot.options[x].value==potong){
tppot.options[x].selected=true;
}
}
param='kdOrg='+kdorg+'&periode='+prder+'&tipePotongan='+potong+'&statUpdate=1';
param+="&proses=createTable";
//alert(param);
tujuan='kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respon);
function respon(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
} else {
// Success Response
lockForm();
document.getElementById('listData').style.display='none';
document.getElementById('headher').style.display='block';
document.getElementById('detailEntry').style.display='block';
var detailDiv = document.getElementById('detailIsi');
detailDiv.innerHTML = con.responseText;
status_inputan=1;
statFrm=1;
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

function delData(kdorg,prder,potong){
param+='&kdOrg='+kdorg;
param+='&periode='+prder+'&tipePot='+potong;
param+='&proses=delData';
tujuan='kebun_slave_timbangke_eksternal.php';
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
displayList();
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
if(confirm("Deleteing, are you sure..?"))
post_response_text(tujuan, param, respog);
}
function frm_aju(){

if(statFrm==0){
if(confirm("Done, are you sure..?")){
displayList();
}
}
else if(statFrm==1){
if(confirm("Done, are you sure..?")){
displayList();
}
}
}
function reset_data(){
if(statFrm==0){
if(confirm("Canceling, are you sure..?")){
kdorg=document.getElementById('kdOrg').value;
tgl=document.getElementById('tglAbsen').value;
delDataAll(kdorg,tgl);
}
}

}


function getKary(title,pil,ev){
utkUnit=document.getElementById('kdOrg');
utkUnit=utkUnit.options[utkUnit.selectedIndex].value;
prd=document.getElementById('tglAbsen').value;
tpPot=document.getElementById('tpPotongan');
tpPot=tpPot.options[tpPot.selectedIndex].value;

if(pil==1){
content= "<div style='width:100%;'>";
content+="<fieldset>"+title+"<input type=hidden id=unit value="+utkUnit+" /><input type=hidden id=tppot value="+tpPot+" /><input type=hidden id=periode value="+prd+" /><input type=text id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariKary("+pil+")>Go</button> </fieldset>";
content+="<div id=containercari style='overflow:scroll;height:300px;width:520px'></div></div>";
}

//display window
width='550';
height='350';
showDialog1(title,content,width,height,ev);
}
function goCariKary(pil){
//keu_slave_2globalfungsi
lokTgs=document.getElementById('unit').value;
tppotongan=document.getElementById('tppot').value;
prd=document.getElementById('periode').value;
nmkary=document.getElementById('txtnamabarang').value;
param='unit='+lokTgs+'&tppot='+tppotongan+'&periode='+prd+'&nmkary='+nmkary;

if(pil==1){
param+='&proses=getKary';
}
tujuan = 'kebun_slave_timbangke_eksternal.php';
post_response_text(tujuan, param, respog);
function respog(){
if (con.readyState == 4) {
if (con.status == 200) {
busy_off();
if (!isSaveResponse(con.responseText)) {
alert(con.responseText);
}
else {
document.getElementById('containercari').innerHTML=con.responseText;
}
}
else {
busy_off();
error_catch(con.status);
}
}
}
}
function setKary(karyid){
kar=document.getElementById('krywnId');
for(x=0;x<kar.length;x++){
if(kar.options[x].value==karyid){
kar.options[x].selected=true;
}
}
closeDialog();
} */