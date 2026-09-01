function gettanggalangkat(){
	statuskaryawan= document.getElementById('statuskaryawan').value;
	tipekaryawan= document.getElementById('tipekaryawan').value;
	if(statuskaryawan=='Tetap'){
		document.getElementById('tanggalpengangkatan').disabled=false;
	}else{
		document.getElementById('tanggalpengangkatan').disabled=true;
		document.getElementById('tanggalpengangkatan').value="";
	}
	
	if(tipekaryawan=='4'){
		const today = new Date();
		const yyyy = today.getFullYear();
		let mm = today.getMonth() + 1;
		let md = today.getMonth() + 2;
		let dd = today.getDate();
		if (dd < 10) dd = '0' + dd;
		if (mm < 10) mm = '0' + mm;
		if (md < 10) md = '0' + md;
		dd = '01';
		const formattedToday = dd + '-' + mm + '-' + yyyy;
		const formatteddepan = dd + '-' + md + '-' + yyyy;

		
		if(statuskaryawan=='Aktif'){
			document.getElementById('tanggalmasuk').value=formattedToday;
			document.getElementById('tanggalkeluar').value="";
		}else if(statuskaryawan=='Keluar'){
			document.getElementById('tanggalkeluar').value=formatteddepan;
		}
	}
}

// function savePhoto() {
// 	const nik = document.querySelector("#nik").value;
// 	const photo = document.querySelector('#displayphoto').getAttribute("src");
// 	if (photo === "") {
// 		alertify.alert("Photo tidak boleh kosong");
// 	} else {
// 		param = `method=savePhoto&nik=${nik}&photo=${photo}`;
// 		tujuan = 'sdm_slave_save_datakaryawan.php';
// 		console.log(param + ' - ' + tujuan)
// 		post_response_text(tujuan, param, respog);
// 		function respog() {
// 			if (con.readyState == 4) {
// 				if (con.status == 200) {
// 					busy_off();
// 					if (!isSaveResponse(con.responseText)) {
// 						alertify.alert("Upload Failed");
// 					} else {
// 						alertify.alert("Upload Success");
// 					}
// 				} else {
// 					busy_off();
// 					error_catch(con.status);
// 				}
// 			}
// 		}
// 	}
// }


// Sama seperti post_response_text() di generic.js, tapi tanpa pengecekan
// isSaveResponse() pada seluruh body request. Dipakai khusus saat request
// membawa foto (base64), karena isi foto yang panjang & acak bisa kebetulan
// mengandung kata "error"/"warning"/"gagal" dan salah terdeteksi sebagai
// kata terlarang. Pemanggil wajib sudah mengecek field non-foto sendiri.
function post_response_text_photo(tujuan, param, functiontoexecute) {
	busy_on();
	zz = verify();
	if (zz) {
		par = parent.location.href.replace("http://", "");
		par = par.replace("https://", "");
		param += "&par=" + par;
		con.open("POST", tujuan, true);
		con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		con.onreadystatechange = eval(functiontoexecute);
		con.send(param);
	} else window.location = "logout.php";
}

//==========================================================
// Abdul
function savePhoto() {
	var nik = document.querySelector("#nik").value;
	var photo = document.querySelector('#displayphoto').getAttribute("src");
	var formdata = new FormData();
	formdata.append("nik", nik);
	formdata.append("photo", photo);
	if (photo == "") {
		alertify.alert("Upload file has been empty.");
		return false;
	}

	var con = createXMLHttpRequest();
	// document.getElementById('savePhoto').style.display = "none";
	//tambahan progress bar
	// con.upload.addEventListener("progress", progressHandler, false);
	// con.addEventListener("load", completeHandler, false);
	// con.addEventListener("error", errorHandler, false);
	// con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "sdm_slave_save_datakaryawan.php?method=savePhoto", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	// console.log(con)
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementById('savePhoto').hidden = true;
					alertify.alert('Uploaded Success.');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// End Abdul
//======================================================================

function deletePhoto() {
	// Foto wajib ada untuk setiap karyawan, jadi tidak boleh dihapus sampai kosong.
	// Untuk mengganti foto, pilih foto baru lalu klik "Save Photo" (otomatis menimpa yang lama).
	alertify.alert('Foto tidak dapat dihapus karena wajib diisi.\nUntuk mengganti foto, pilih foto baru lalu klik "Save Photo".');
}

function getDivisi(){
	lokasitugas= document.getElementById('lokasitugas').value;
	subbagian  = document.getElementById('subbagian').value;
	n = document.getElementById('subbagian');
	jlhawal = n.length;
	
	
	param ="";
	param += '&lokasitugas=' + lokasitugas;
	param += '&subbagian=' + subbagian;
	param += '&method=getDivisi';
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('subbagian').innerHTML = con.responseText;
					i = document.getElementById('subbagian');
					if(jlhawal!=i.length){						
						$('#subbagian').select2();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdetailtipekary(){
	tipekaryawan   = document.getElementById('tipekaryawan').value;
	
	param ="";
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&method=getdetailtipekary';
	
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("####");
					document.getElementById('statuskaryawan').innerHTML = data[0];
					document.getElementById('sistemgaji').innerHTML = data[1];
					document.getElementById('kodegolongan').innerHTML = data[2];
					
					if(tipekaryawan=='0'){
						document.getElementById('alokasi').value = '1';
					}else{
						document.getElementById('alokasi').value = '0';
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setalamat(){
	provinsi = document.getElementById('prov2').value;
	kabupaten= document.getElementById('kab2').value;
	kecamatan= document.getElementById('kec2').value;
	desa     = document.getElementById('des2').value;
	almt     = document.getElementById('alamat2').value;
	kopos2   = document.getElementById('kopos2').value;
	
	nmprov= $('#prov2 option:selected').text();
	nmkab = $('#kab2 option:selected').text();
	nmkec = $('#kec2 option:selected').text();
	nmdesa= $('#des2 option:selected').text();

	setValue('alamataktif',almt);
	setValue('desa',desa);
	setValue('kecamatan',kecamatan);
	setValue('kabupaten',kabupaten);
	setValue('provinsi',provinsi);
	setValue('kodepos',kopos2);
	
	setValue('namadesa',nmdesa);
	setValue('namakecamatan',nmkec);
	setValue('namakabupaten',nmkab);
	setValue('namaprovinsi',nmprov);
	
	alertify.popup().destroy();
}
function getkab(jenis,value){
	provinsi   = document.getElementById('prov2').value;
	kabupaten  = document.getElementById('kab2').value;
	kecamatan  = document.getElementById('kec2').value;
	desa       = document.getElementById('des2').value;
	
	param ="";
	param += '&provinsi=' + provinsi;
	param += '&kabupaten=' + kabupaten;
	param += '&kecamatan=' + kecamatan;
	param += '&desa=' + desa;
	param += '&value=' + value;
	param += '&jenis=' + jenis;
	param += '&method=getkab';
	
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(jenis=='kab'){
						document.getElementById('kab2').innerHTML = con.responseText;
					}
					if(jenis=='kec'){
						document.getElementById('kec2').innerHTML = con.responseText;
					}
					if(jenis=='des'){
						document.getElementById('des2').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getpopupalamat(){
	alamataktif= document.getElementById('alamataktif').value;
	provinsi   = document.getElementById('provinsi').value;
	kabupaten  = document.getElementById('kabupaten').value;
	kecamatan  = document.getElementById('kecamatan').value;
	desa       = document.getElementById('desa').value;
	kodepos    = document.getElementById('kodepos').value;
	
	param ="";
	param += '&alamataktif=' + alamataktif;
	param += '&provinsi=' + provinsi;
	param += '&kabupaten=' + kabupaten;
	param += '&kecamatan=' + kecamatan;
	param += '&desa=' + desa;
	param += '&kodepos=' + kodepos;
	param += '&value=' + provinsi;
	param += '&method=getpopupalamat';
	
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail Alamat","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('40%','60%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
					});
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					
					setValue('alamat2',alamataktif);
					setValue('kopos2',kodepos);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function editPengalaman(namaperusahaan,bidangusaha,bulanmasuk,bulankeluar,bagian,jabatan,alamatperusahaan,gajipokok,alasanberhenti,nomor){
	document.getElementById('namaperusahaan').value=namaperusahaan;
	document.getElementById('bidangusaha').value=bidangusaha;
	masuk=bulanmasuk.split("-");
	document.getElementById('blnmasuk').value=masuk[0];
	document.getElementById('thnmasuk').value=masuk[1];
	keluar=bulankeluar.split("-");
	document.getElementById('blnkeluar').value=keluar[0];
	document.getElementById('thnkeluar').value=keluar[1];
	document.getElementById('pengalamanbagian').value=bagian;
	document.getElementById('pengalamanjabatan').value=jabatan;
	document.getElementById('pengalamanalamat').value=alamatperusahaan;
	document.getElementById('gajipokok').value=gajipokok;
	document.getElementById('alasanberhenti').value=alasanberhenti;
	document.getElementById('methodcv').value='edit';
	document.getElementById('nomor').value=nomor;
}

function editPendidikan(levelpendidikan,spesialisasi,gelar,tahunlulus,namasekolah,nilai,kota,keterangan,kode){
	document.getElementById('levelpendidikan2').value=levelpendidikan;
	document.getElementById('spesialisasi').value=spesialisasi;
	document.getElementById('gelar').value=gelar;
	document.getElementById('tahunlulus').value=tahunlulus;
	document.getElementById('namasekolah').value=namasekolah;
	document.getElementById('nilai').value=nilai;
	document.getElementById('pendidikankota').value=kota;
	document.getElementById('pendidikanketerangan').value=keterangan;
	document.getElementById('kode').value=kode;
	document.getElementById('methodpddkn').value='edit';
}
function editTraining(jnstraining,judultraining,tanggalmulai,tanggalselesai,penyelenggara,bersertifikat,biaya,nomortrain){
	document.getElementById('jenistraining').value=jnstraining;
	document.getElementById('judultraining').value=judultraining;
	document.getElementById('tanggalmulai').value=tanggalmulai;
	document.getElementById('tanggalselesai').value=tanggalselesai;
	document.getElementById('penyelenggara').value=penyelenggara;
	document.getElementById('sertifikat').value=bersertifikat;
	document.getElementById('biaya').value=biaya;
	document.getElementById('nomortrain').value=nomortrain;
	document.getElementById('methodtrain').value='edit';
	// alertify.alert(nomor);
	// return;
}

function periodeakhirpilih(){
	tanggalkeluar=document.getElementById('tanggalkeluar').value;
	periode="<option value=''>Pilih Data</option>";
	if(tanggalkeluar!='')
	{
		arrtanggal=tanggalkeluar.split('-');
		bulan=arrtanggal[1];
		tahun=arrtanggal[2];
		bulandepan=parseInt(bulan)+1;
		periode='';
		if(bulandepan==13)
		{
			bulandepan=1;
			tahundepan=parseInt(tahun)+1;
			periode="<option value='"+tahun+"-"+bulan+"'>"+tahun+"-"+bulan+"</option>";
			periode+="<option value='"+tahundepan+"-0"+bulandepan+"'>"+tahundepan+"-0"+bulandepan+"</option>";
		}
		else
		{	
			if(bulandepan<=9)
			{
			periode="<option value='"+tahun+"-"+bulan+"'>"+tahun+"-"+bulan+"</option>";
			periode+="<option value='"+tahun+"-0"+bulandepan+"'>"+tahun+"-0"+bulandepan+"</option>";
			}
			else
			{
			periode="<option value='"+tahun+"-"+bulan+"'>"+tahun+"-"+bulan+"</option>";
			periode+="<option value='"+tahun+"-"+bulandepan+"'>"+tahun+"-"+bulandepan+"</option>";	
			}
		}

	}


	document.getElementById('periodeakhir').innerHTML=periode;

}

function editAlamat(alamat,kota,kodepos,provinsi,telepon,emplasemen,aktif,nomor){
	document.getElementById('alamatalamat').value=alamat;
	document.getElementById('alamatkota').value=kota;
	document.getElementById('alamatkodepos').value=kodepos;
	document.getElementById('alamatprovinsi').value=provinsi;
	document.getElementById('alamattelepon').value=telepon;
	document.getElementById('alamatemplasement').value=emplasemen;
	document.getElementById('alamatstatus').value=aktif;
	document.getElementById('nomoralamat').value=nomor;
	document.getElementById('methodalamat').value='edit';
}
function clearpengalaman(){
	document.getElementById('namaperusahaan').value='';
	document.getElementById('bidangusaha').value='';
	document.getElementById('blnmasuk').value='';
	document.getElementById('thnmasuk').value='';
	document.getElementById('blnkeluar').value='';
	document.getElementById('thnkeluar').value='';
	document.getElementById('pengalamanbagian').value='';
	document.getElementById('pengalamanjabatan').value='';
	document.getElementById('pengalamanalamat').value='';
	document.getElementById('gajipokok').value='';
	document.getElementById('alasanberhenti').value='';
	document.getElementById('methodcv').value='insert';
	document.getElementById('nomor').value='';
}
function clearpendidikan(){
	document.getElementById('levelpendidikan2').value='';
	document.getElementById('spesialisasi').value='';
	document.getElementById('gelar').value='';
	document.getElementById('tahunlulus').value='';
	document.getElementById('namasekolah').value='';
	document.getElementById('nilai').value='';
	document.getElementById('pendidikankota').value='';
	document.getElementById('pendidikanketerangan').value='';
	document.getElementById('methodpddkn').value='insert';
	document.getElementById('kode').value='';
}
function cleartraining(){
	document.getElementById('jenistraining').value='0007';
	document.getElementById('judultraining').value='';
	document.getElementById('tanggalmulai').value='';
	document.getElementById('tanggalselesai').value='';
	document.getElementById('penyelenggara').value='';
	document.getElementById('sertifikat').value='0';
	document.getElementById('biaya').value='';
	document.getElementById('nomortrain').value='';
	document.getElementById('methodtrain').value='insert';
}

function getImageSizeInBytes(imgURL) {
	var request = new XMLHttpRequest();
	request.open("HEAD", imgURL, false);
	request.send(null);
	var headerText = request.getAllResponseHeaders();
	var re = /Content\-Length\s*:\s*(\d+)/i;
	re.exec(headerText);
	return parseInt(RegExp.$1);
}
function readURL(data) {
	if (data.files && data.files[0]) {
		var typeimg = ["image/jpeg"];
		var a = typeimg.indexOf(data.files[0].type);
		if (data.files[0].size > 2 * 1024 * 1024) {
			alertify.alert("Ukuran foto maksimal 2 MB");
		} else if (a == -1) {
			alertify.alert("Format foto harus JPEG !!");
		} else {
			var reader = new FileReader();
			reader.onload = function (e) {
				var img = new Image();
				img.src = e.target.result;
				//console.log(getImageSizeInBytes(e.target.result));
				img.onload = function () {
					//max 257000;
					var displayphoto = document.getElementById('displayphoto');
					var photoboth = document.getElementById('photoboth');
					displayphoto.setAttribute('src', e.target.result);
					var height = (150 / this.width) * this.height;
					displayphoto.style.width = "150px";
					displayphoto.style.height = height + "px";
					photoboth.style.backgroundImage = "";
					photoboth.style.width = "150px";
					photoboth.style.height = height + "px";
					photoboth.style.border = "solid 2px #FFF";

					// Untuk karyawan existing (mode update), foto disimpan lewat
					// tombol "Save Photo" (multipart), bukan ikut tombol Simpan.
					if (document.getElementById('method').value == 'update') {
						document.getElementById('savePhoto').hidden = false;
					}
				}
			};
			reader.readAsDataURL(data.files[0]);
		}
	}
}
function chooseFile(idname) {
	var fupload = document.getElementById(idname);
	fupload.click();
}
function pendi() {
	document.getElementById('levelpendidikan').value;
	//document.getElementById('levelpendidikan2').value;

	param = 'levelpendidikan=' + levelpendidikan + '&method=cek';
	alertify.alert(param);
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingdata(nourut,karyawanid,namakaryawan) {

	param = 'nourut=' + nourut + '&method=postingdata';
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// closeDialog();
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unpostingdata(nourut,karyawanid,namakaryawan) {

	param = 'nourut=' + nourut + '&method=unpostingdata';
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//closeDialog();
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanKaryawan() {
	//get input text and textarea value
	noerf              = trim(document.getElementById('noerf').value);
	nik                = trim(document.getElementById('nik').value);
	photo              = document.getElementById('displayphoto').getAttribute("src");
	namakaryawan       = trim(document.getElementById('namakaryawan').value);
	tempatlahir        = trim(document.getElementById('tempatlahir').value);
	tanggallahir       = trim(document.getElementById('tanggallahir').value);
	noktp              = trim(document.getElementById('noktp').value);
	nopassport         = trim(document.getElementById('nopassport').value);
	npwp               = trim(document.getElementById('npwp').value);
	bpjs               = trim(document.getElementById('bpjs').value);
	pensiun            = trim(document.getElementById('pensiun').value);
	kodepos            = trim(document.getElementById('kodepos').value);
	alamataktif        = trim(document.getElementById('alamataktif').value);
	kota               = trim(document.getElementById('kota').value);
	noteleponrumah     = trim(document.getElementById('noteleponrumah').value);
	nohp               = trim(document.getElementById('nohp').value);
	nohp2              = trim(document.getElementById('nohp2').value);
	norekeningbank     = trim(document.getElementById('norekeningbank').value);
	namabank           = trim(document.getElementById('namabank').value);
	anrekening         = trim(document.getElementById('anrekening').value);
	tanggalmasuk       = trim(document.getElementById('tanggalmasuk').value);
	tanggalpengangkatan= trim(document.getElementById('tanggalpengangkatan').value);
	tanggalpengangkatannonstaff= trim(document.getElementById('tanggalpengangkatannonstaff').value);
	tanggalkeluar      = trim(document.getElementById('tanggalkeluar').value);
	tanggalmenikah     = trim(document.getElementById('tanggalmenikah').value);
	jumlahanak         = trim(document.getElementById('jumlahanak').value);
	jumlahtanggungan   = trim(document.getElementById('jumlahtanggungan').value);
	tanggalmenikah     = trim(document.getElementById('tanggalmenikah').value);
	periodeakhirgaji   = trim(document.getElementById('periodeakhirgaji').value);
	notelepondarurat   = trim(document.getElementById('notelepondarurat').value);
	email              = trim(document.getElementById('email').value);
	emailkantor        = trim(document.getElementById('emailkantor').value);
	subbpjs            = trim(document.getElementById('supbpjs').value);
	kppnpwp            = trim(document.getElementById('kppnpwp').value);
	anrekening         = trim(document.getElementById('anrekening').value);
	method             = trim(document.getElementById('method').value);
	nourut             = trim(document.getElementById('nourut').value);
	karyawanid         = trim(document.getElementById('karyawanid').value);
	jms                = trim(document.getElementById('jms').value);
	statuskaryawan     = document.getElementById('statuskaryawan').value;
	jeniskelamin       = document.getElementById('jeniskelamin').value;
	agama              = document.getElementById('agama').value;
	bagian             = document.getElementById('bagian').value;
	subdept            = document.getElementById('subdept').value;
	kodejabatan        = document.getElementById('kodejabatan').value;
	kodegolongan       = document.getElementById('kodegolongan').value;
	lokasitugas        = document.getElementById('lokasitugas').value;
	kodeorganisasi     = document.getElementById('kodeorganisasi').value;
	tipekaryawan       = document.getElementById('tipekaryawan').value;
	warganegara        = document.getElementById('warganegara').value;
	suku               = document.getElementById('suku').value;
	lokasipenerimaan   = document.getElementById('lokasipenerimaan').value;
	insstatuspajak     = document.getElementById('vstatuspajak').value;
	statuspajak        = trim(document.getElementById('vstatuspajak').value);
	provinsi           = document.getElementById('provinsi').value;
	
	if(email != ''){
		if(emailCheck(email)==false)
		{
			return false;
		}
	}

	if(emailkantor != ''){
		if(emailCheck(emailkantor)==false)
		{
			return false;
		}
	}

	
	// try {
		// provinsi = trim(provinsi.options[provinsi.selectedIndex].value);
	// } catch (e) {}
	sistemgaji   = document.getElementById('sistemgaji').value;
	golongandarah= document.getElementById('golongandarah').value;
	alokasi      = document.getElementById('alokasi').value;
	subbagian    = document.getElementById('subbagian').value;
	catu         = document.getElementById('catu').value;
	while (golongandarah.indexOf("+") > -1) {
		golongandarah = golongandarah.replace("+", "%2B");
	}
	statusperkawinan= document.getElementById('statusperkawinan').value;
	levelpendidikan = document.getElementById('levelpendidikan').value;
	dert            = document.getElementById('dptPremi');
	statPremi       = 0;
	statusakad      = document.getElementById('statusakad').value;
	sim             = trim(document.getElementById('sim').value);
	kabupaten       = trim(document.getElementById('kabupaten').value);
	kecamatan       = trim(document.getElementById('kecamatan').value);
	desa            = trim(document.getElementById('desa').value);
	bulandaftarbpjs = trim(document.getElementById('bulandaftarbpjs').value);
	levelkaryawan   = document.getElementById('levelkaryawan').value;

	if (dert.checked == true) {
		statPremi = 1;
	}
	
	if(kota==''){
		kota='-';
	}
	
	if (noktp == '' || alamataktif == '' || kota == '' || tempatlahir == '' || tanggallahir.length != 10 || tanggalmasuk.length != 10 || statuskaryawan == '') {
		alertify.alert('ID.Num/KTP,Address/Alamat, City/Kota,\nPlace Of Birth/Tempat lahir, Birth.Date/Tgl.lahir,\nJoin.date/Tgl.Masuk,\nStatus Karyawan\n are Obligatory');
	} else if ((tipekaryawan == '6') && (tanggalkeluar == '' || tanggalkeluar == '00-00-0000')) {
		alertify.alert('ID: Karyawan kontrak harus diisi tanggal keluarnya sebagai tanggal akhir kontrak\nEN:Employee with Contract agreement must be filled discharge date as the end date of the contract');
	} else if ((tipekaryawan == '4') && (statuskaryawan != 'Aktif' && statuskaryawan != 'Keluar' && statuskaryawan != 'Percobaan' )) {
		alertify.alert('ID: Karyawan kontrak/KHL harus diisi dengan status karyawan Aktif / Keluar / Percobaan');
	} else if ((tipekaryawan == '1' || tipekaryawan == '0') && (nopassport == '')) {
		alertify.alert('ID: No kartu keluarga wajib diisi');
	}else if ((tanggalkeluar== '' || tanggalkeluar=='00-00-0000') && (periodeakhirgaji != '' )) {
		alertify.alert('ID: Jika periode gaji terakhir di input maka tanggal keluar harus di input');
	} else {

		param = 'nik=' + nik + '&namakaryawan=' + namakaryawan + '&tempatlahir=' + tempatlahir;
		param += '&tanggallahir=' + tanggallahir + '&noktp=' + noktp;
		param += '&nopassport=' + nopassport + '&npwp=' + npwp + '&bpjs=' + bpjs + '&kodepos=' + kodepos + '&pensiun=' + pensiun;
		param += '&alamataktif=' + alamataktif + '&kota=' + kota + '&noteleponrumah=' + noteleponrumah
		param += '&nohp=' + nohp + '&nohp2=' + nohp2 + '&norekeningbank=' + norekeningbank + '&namabank=' + namabank + '&anrekening=' + anrekening + '&tanggalmasuk=' + tanggalmasuk;
		param += '&tanggalpengangkatan=' + tanggalpengangkatan + '&tanggalpengangkatannonstaff=' + tanggalpengangkatannonstaff + '&tanggalkeluar=' + tanggalkeluar + '&jumlahanak=' + jumlahanak;
		param += '&jumlahtanggungan=' + jumlahtanggungan + '&tanggalmenikah=' + tanggalmenikah;
		param += '&notelepondarurat=' + notelepondarurat + '&email=' + email + '&emailkantor=' + emailkantor;
		param += '&jeniskelamin=' + jeniskelamin + '&agama=' + agama;
		param += '&bagian=' + bagian + '&subdept=' + subdept + '&kodejabatan=' + kodejabatan;
		param += '&kodegolongan=' + kodegolongan + '&lokasitugas=' + lokasitugas;
		param += '&kodeorganisasi=' + kodeorganisasi + '&tipekaryawan=' + tipekaryawan;
		param += '&warganegara=' + warganegara + '&lokasipenerimaan=' + lokasipenerimaan;
		param += '&suku=' + suku + '&statuskaryawan=' + statuskaryawan + '&subbpjs=' + subbpjs;

		param += '&statuspajak=' + statuspajak + '&insstatuspajak=' + insstatuspajak + '&provinsi=' + provinsi;
		param += '&sistemgaji=' + sistemgaji + '&golongandarah=' + golongandarah;
		param += '&statusperkawinan=' + statusperkawinan + '&levelpendidikan=' + levelpendidikan;
		param += '&method=' + method + '&karyawanid=' + karyawanid + '&alokasi=' + alokasi;
		param += '&subbagian=' + subbagian + '&jms=' + jms;
		param += '&catu=' + catu + '&statPremi=' + statPremi + '&statusakad=' + statusakad;
		param += '&sim=' + sim;
		param += '&noerf=' + noerf;
		param += '&periodeakhirgaji=' + periodeakhirgaji;
		param += '&anrekening=' + anrekening + '&kppnpwp=' + kppnpwp+ '&nourut=' + nourut;
		param += '&kabupaten=' + kabupaten;
		param += '&kecamatan=' + kecamatan;
		param += '&desa=' + desa;
		param += '&bulandaftarbpjs=' + bulandaftarbpjs;
		param += '&levelkaryawan=' + levelkaryawan;

		// Cek kata terlarang hanya pada field yang diisi manual oleh user.
		// Foto (base64) sengaja tidak ikut dicek karena isinya data biner acak
		// yang kebetulan bisa saja mengandung teks "error"/"warning"/"gagal".
		if (!isSaveResponse(param)) {
			alertify.alert('errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL');
			return false;
		}

		if (method == 'insert') {
			// Karyawan baru: foto wajib & langsung ikut tersimpan dalam satu langkah.
			param += '&photo=' + encodeURIComponent(photo);
		} else if (!document.getElementById('savePhoto').hidden) {
			// Karyawan existing: ada foto baru yang dipilih tapi belum di-"Save Photo".
			if (!confirm('Anda memilih foto baru tapi belum klik "Save Photo".\nFoto baru TIDAK akan ikut tersimpan kalau lanjut.\nLanjutkan menyimpan data lainnya?')) {
				return false;
			}
		}

		tujuan = 'sdm_slave_save_datakaryawan.php';
		if (confirm('Menyimpan riwayat data untuk ' + namakaryawan + '.  Apakah Anda yakin?'))

			post_response_text_photo(tujuan, param, respog);
		}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert('Data berhasil disimpan....');
					controlThisForm(con.responseText);
					enableOtherButton();
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function form_ajukan_dtk(nourut, tipekaryawan,lokasitugas,namakaryawan,karyawanid,periodegaji,version,numrow) {
	// width = '300';
	// height = '';
	// content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	
	param = 'method=form_ajukan' + '&nourut=' + nourut + '&tipekaryawan=' + tipekaryawan + '&lokasitugas=' + lokasitugas + '&namakaryawan=' + namakaryawan +'&numrow=' + numrow;
	param += '&karyawanid=' + karyawanid +'&periodegaji=' + periodegaji+'&version=' + version;
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containeraju').innerHTML = con.responseText;
					alertify.popup2("Approval",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('350px','300px');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function ajukan(karyawanid) {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	nourut = document.getElementById('numrow').value;
	jenispersetujuanx = document.getElementById('jenispersetujuanx').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada + '&nourut=' + nourut + '&jenispersetujuanx=' + jenispersetujuanx+ '&karyawanid=' + karyawanid;
	if (kepada == '') {
		alertify.alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'sdm_slave_save_datakaryawan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					displayList();
					alertify.popup2().destroy();
					alertify.popup().destroy();
					//closeDialog();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function controlThisForm(tex) {
	xml = tex.toString();
	xmlobject = (new DOMParser()).parseFromString(xml, "text/xml");
	getId = xmlobject.getElementsByTagName('karyawanid')[0].firstChild.nodeValue;
	getNama = xmlobject.getElementsByTagName('namakaryawan')[0].firstChild.nodeValue;
	getNik = xmlobject.getElementsByTagName('nik')[0].firstChild.nodeValue;
	if (trim(getId) != '') {
		//Change first Tab Caption
		document.getElementById('tabFRM0').innerHTML = getNama;
		//change to update method
		document.getElementById('method').value = 'update';
		document.getElementById('karyawanid').value = getId;
		document.getElementById('nik').value = getNik;
	} else {
		alertify.alert('Last transaction has nothing affected');
	}
}
function cancelDataKaryawan() {
	document.getElementById('nik').value = '';
	document.getElementById('namakaryawan').value = '';
	document.getElementById('tempatlahir').value = '';
	document.getElementById('tanggallahir').value = '';
	document.getElementById('noktp').value = '';
	document.getElementById('nopassport').value = '';
	document.getElementById('suku').options[0].selected = true;
	document.getElementById('npwp').value = '';
	document.getElementById('bpjs').value = '';
	document.getElementById('pensiun').value = '';
	document.getElementById('alamataktif').value = '';
	document.getElementById('kota').value = '';
	document.getElementById('provinsi').value='';
	document.getElementById('kodepos').value = '';
	document.getElementById('noteleponrumah').value = '';
	document.getElementById('nohp').value = '';
	document.getElementById('norekeningbank').value = '';
	document.getElementById('namabank').value = '';
	document.getElementById('anrekening').value = '';
	document.getElementById('sistemgaji').options[0].selected = true;
	document.getElementById('tanggalmasuk').value = '';
	document.getElementById('tanggalpengangkatan').value = '';
	document.getElementById('tanggalpengangkatannonstaff').value = '';
	document.getElementById('tanggalkeluar').value = '';
	document.getElementById('statusperkawinan').options[0].selected = true;
	document.getElementById('tanggalmenikah').value = '';
	document.getElementById('jumlahanak').value = '';
	document.getElementById('jumlahtanggungan').value = '';
	document.getElementById('tanggalmenikah').value = '';
	document.getElementById('notelepondarurat').value = '';
	document.getElementById('karyawanid').value = '';
	document.getElementById('email').value = '';
	document.getElementById('sim').value = '';
	document.getElementById('dptPremi').checked = false;
	document.getElementById('method').value = 'insert';
	document.getElementById('savePhoto').hidden = true;
	document.getElementById('tabFRM0').innerHTML = 'Karyawan Baru';
	document.getElementById('container').innerHTML = '';
	document.getElementById('containerpendidikan').innerHTML = '';
	document.getElementById('containertraining').innerHTML = '';
	document.getElementById('containerkeluarga').innerHTML = '';
	document.getElementById('containeralamat').innerHTML = '';
	document.getElementById('displayphoto').removeAttribute('src');
	document.getElementById('displayphoto').setAttribute('src', '');
	document.getElementById('jms').value = '';
	document.getElementById('sim').value = '';
	document.getElementById('statuskaryawan').options[0].selected = true;
	document.getElementById('subbagian').options[0].selected = true;
	document.getElementById('statusakad').options[0].selected = true;

	document.getElementById('kodeorganisasi').disabled = false;
	document.getElementById('lokasitugas').disabled = false;
	document.getElementById('tanggalkeluar').disabled = false;
	document.getElementById('tanggalmasuk').disabled = false;
	//document.getElementById('alokasi').disabled = false;

	document.getElementById('statuspajak').disabled=false;

	disableOtherButton();
	cancelPhoto();
}

function enableOtherButton() {
	//after success saving then activate sumbit button on each tab
	document.getElementById('btncv').disabled = false;
	document.getElementById('btnpendidikan').disabled = false;
	document.getElementById('btntraining').disabled = false;
	//document.getElementById('btnphoto').disabled=false;
	document.getElementById('btnalamat').disabled = false;
	document.getElementById('btnkeluarga').disabled = false;
}

function disableOtherButton() {
	//after success saving then activate sumbit button on each tab
	document.getElementById('btncv').disabled = true;
	document.getElementById('btnpendidikan').disabled = true;
	document.getElementById('btntraining').disabled = true;
	//document.getElementById('btnphoto').disabled=true;
	document.getElementById('btnalamat').disabled = true;
	document.getElementById('btnkeluarga').disabled = true;
}
//========================tab pengalaman
function simpanPengalaman() {
	method=document.getElementById('methodcv').value;
	nomor=document.getElementById('nomor').value;
	namaperusahaan = trim(document.getElementById('namaperusahaan').value);
	bidangusaha = (document.getElementById('bidangusaha').value);

	blnmasuk = document.getElementById('blnmasuk');
	blnmasuk = blnmasuk.options[blnmasuk.selectedIndex].value;
	thnmasuk = document.getElementById('thnmasuk');
	thnmasuk = thnmasuk.options[thnmasuk.selectedIndex].value;
	blnkeluar = document.getElementById('blnkeluar');
	blnkeluar = blnkeluar.options[blnkeluar.selectedIndex].value;
	thnkeluar = document.getElementById('thnkeluar');
	thnkeluar = thnkeluar.options[thnkeluar.selectedIndex].value;

	jabatan = trim(document.getElementById('pengalamanjabatan').value);
	bagian = document.getElementById('pengalamanbagian').value;
	alamat = document.getElementById('pengalamanalamat').value;
	karyawanid = document.getElementById('karyawanid').value;

	gajipokok = document.getElementById('gajipokok').value;
	alasanberhenti = document.getElementById('alasanberhenti').value;
	tunjangan = document.getElementById('tunjangan').value;
	lokasicuti = document.getElementById('lokasicuti').value;

	if (blnmasuk == '' || thnmasuk == '' || blnkeluar == '' || thnkeluar == '') {
		alertify.alert('Incorrect period');
	} else if (namaperusahaan == '' || bidangusaha == '' || jabatan == '') {
		alertify.alert('Data Incomplete');
	} else {
		param = 'namaperusahaan=' + namaperusahaan + '&bidangusaha=' + bidangusaha;
		param += '&blnmasuk=' + blnmasuk + '&thnmasuk=' + thnmasuk;
		param += '&blnkeluar=' + blnkeluar + '&thnkeluar=' + thnkeluar;
		param += '&jabatan=' + jabatan + '&bagian=' + bagian;
		param += '&alamat=' + alamat + '&karyawanid=' + karyawanid;
		param += '&gajipokok=' + gajipokok + '&alasanberhenti=' + alasanberhenti;
		param += '&tunjangan=' + tunjangan + '&lokasicuti=' + lokasicuti;
		param += '&method=' + method;
		param += '&nomor=' + nomor;

		tujuan = 'sdm_slave_save_riwayat_pekerjaan.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					clearpengalaman();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function delPengalaman(karyawanid, nomor) {
	param = 'nomor=' + nomor + '&karyawanid=' + karyawanid + '&del=true';
	tujuan = 'sdm_slave_save_riwayat_pekerjaan.php';
	if (confirm('Deleting, are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//=================tab pendidikan

function updatelv() {
	document.getElementById('levelpendidikan').value;
	document.getElementById('levelpendidikan2').value;
	if (levelpendidikan2 > levelpendidikan) {
		alertify.alert('Education level greater than listed in main data');
		return;
	}
}

function simpanPendidikan() {
	method=document.getElementById('methodpddkn').value;
	kode=document.getElementById('kode').value;
	document.getElementById('levelpendidikan').value;
	document.getElementById('levelpendidikan2').value;
	levelpendidikan2 = document.getElementById('levelpendidikan2');
	levelpendidikan2 = trim(levelpendidikan2.options[levelpendidikan2.selectedIndex].value);
	tahunlulus = document.getElementById('tahunlulus');
	tahunlulus = trim(tahunlulus.options[tahunlulus.selectedIndex].value);
	spesialisasi = trim(document.getElementById('spesialisasi').value);
	gelar = trim(document.getElementById('gelar').value);
	namasekolah = trim(document.getElementById('namasekolah').value);
	nilai = document.getElementById('nilai').value;
	pendidikankota = trim(document.getElementById('pendidikankota').value);
	pendidikanketerangan = trim(document.getElementById('pendidikanketerangan').value);
	karyawanid = document.getElementById('karyawanid').value;

	if (tahunlulus == '' || namasekolah == '') {
		alertify.alert('Data incomplete');
	} else if (levelpendidikan2 > levelpendidikan) {
		alertify.alert('Education level greater than listed in main data');
		return;
	} else {
		param = 'levelpendidikan=' + levelpendidikan2 + '&tahunlulus=' + tahunlulus;
		param += '&spesialisasi=' + spesialisasi + '&gelar=' + gelar;
		param += '&namasekolah=' + namasekolah + '&nilai=' + nilai;
		param += '&pendidikankota=' + pendidikankota + '&pendidikanketerangan=' + pendidikanketerangan;
		param += '&method=' + method;
		param += '&kode=' + kode;
		param += '&karyawanid=' + karyawanid;
		// alertify.alert(param);
		// return;
		tujuan = 'sdm_slave_save_riwayat_pendidikan.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerpendidikan').innerHTML = con.responseText;
					clearpendidikan();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delPendidikan(karyawanid, kode) {
	param = 'kode=' + kode + '&karyawanid=' + karyawanid + '&del=true';
	tujuan = 'sdm_slave_save_riwayat_pendidikan.php';
	if (confirm('Deleting, are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerpendidikan').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
//=================training
function simpanTraining() {
	method=document.getElementById('methodtrain').value;
	nomor=document.getElementById('nomortrain').value;
	jenistraining = trim(document.getElementById('jenistraining').value);
	judultraining = trim(document.getElementById('judultraining').value);
	penyelenggara = trim(document.getElementById('penyelenggara').value);
	tanggalmulai = trim(document.getElementById('tanggalmulai').value);
	tanggalselesai = trim(document.getElementById('tanggalselesai').value);
	// trainingblnmulai	=document.getElementById('trainingblnmulai');
	// trainingblnmulai=trim(trainingblnmulai.options[trainingblnmulai.selectedIndex].value);
	// trainingthnmulai	=document.getElementById('trainingthnmulai');
	// trainingthnmulai=trim(trainingthnmulai.options[trainingthnmulai.selectedIndex].value);
	// trainingblnselesai	=document.getElementById('trainingblnselesai');
	// trainingblnselesai=trim(trainingblnselesai.options[trainingblnselesai.selectedIndex].value);
	// trainingthnselesai	=document.getElementById('trainingthnselesai');
	// trainingthnselesai=trim(trainingthnselesai.options[trainingthnselesai.selectedIndex].value);
	sertifikat = document.getElementById('sertifikat');
	sertifikat = trim(sertifikat.options[sertifikat.selectedIndex].value);
	karyawanid = document.getElementById('karyawanid').value;
	biaya = document.getElementById('biaya').value;
	if (jenistraining == '' || judultraining == '' || penyelenggara == '') {
		alertify.alert('Data incomplete');
	} else {
		param = 'jenistraining=' + jenistraining + '&judultraining=' + judultraining;
		param += '&penyelenggara=' + penyelenggara;
		// param+='&trainingblnmulai='+trainingblnmulai+'&trainingthnmulai='+trainingthnmulai+'&trainingblnselesai='+trainingblnselesai+'&trainingthnselesai='+trainingthnselesai;
		param += '&tanggalmulai=' + tanggalmulai + '&tanggalselesai=' + tanggalselesai;
		param += '&sertifikat=' + sertifikat;
		param += '&karyawanid=' + karyawanid + '&biaya=' + biaya;
		param += '&method=' + method + '&nomor=' + nomor;
		tujuan = 'sdm_slave_save_riwayat_training.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containertraining').innerHTML = con.responseText;
					cleartraining();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delTraining(karyawanid, nomor) {
	param = 'nomor=' + nomor + '&karyawanid=' + karyawanid + '&del=true';
	tujuan = 'sdm_slave_save_riwayat_training.php';
	if (confirm('Deleting, are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containertraining').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//==================keluarga
function simpanKeluarga() {
	keluarganama = trim(document.getElementById('keluarganama').value);
	keluargatmplahir = document.getElementById('keluargatmplahir').value;
	keluargatgllahir = document.getElementById('keluargatgllahir').value;
	keluargapekerjaan = document.getElementById('keluargapekerjaan').value;
	keluargatelp = document.getElementById('keluargatelp').value;
	keluargaemail = document.getElementById('keluargaemail').value;
	karyawanid = document.getElementById('karyawanid').value;
	method = document.getElementById('keluargamethod').value;
	nomor = document.getElementById('keluarganomor').value;

	keluargajk = document.getElementById('keluargajk');
	keluargajk = keluargajk.options[keluargajk.selectedIndex].value;
	hubungankeluarga = document.getElementById('hubungankeluarga');
	hubungankeluarga = hubungankeluarga.options[hubungankeluarga.selectedIndex].value;
	keluargastatus = document.getElementById('keluargastatus');
	keluargastatus = keluargastatus.options[keluargastatus.selectedIndex].value;
	keluargapendidikan = document.getElementById('keluargapendidikan');
	keluargapendidikan = keluargapendidikan.options[keluargapendidikan.selectedIndex].value;
	keluargatanggungan = document.getElementById('keluargatanggungan');
	keluargatanggungan = keluargatanggungan.options[keluargatanggungan.selectedIndex].value;
	
	keluargabpjstanggungan = document.getElementById('keluargabpjstanggungan').value;
	
	keluargaemplasment = document.getElementById('keluargaemplasment');
	keluargaemplasment = keluargaemplasment.options[keluargaemplasment.selectedIndex].value;

	if (keluarganama == '') {
		alertify.alert('Data incomplete');
	} else {
		param = 'keluarganama=' + keluarganama + '&keluargajk=' + keluargajk;
		param += '&keluargatmplahir=' + keluargatmplahir + '&keluargatgllahir=' + keluargatgllahir;
		param += '&keluargapekerjaan=' + keluargapekerjaan + '&keluargatelp=' + keluargatelp;
		param += '&keluargaemail=' + keluargaemail + '&karyawanid=' + karyawanid;
		param += '&hubungankeluarga=' + hubungankeluarga + '&keluargastatus=' + keluargastatus;
		param += '&keluargapendidikan=' + keluargapendidikan + '&keluargatanggungan=' + keluargatanggungan + '&keluargaemplasment=' + keluargaemplasment + '&keluargabpjstanggungan=' + keluargabpjstanggungan;
		param += '&method=' + method + '&nomor=' + nomor;
		//alertify.alert(param);
		tujuan = 'sdm_slave_save_keluarga.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerkeluarga').innerHTML = con.responseText;
					clearKeluarga();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function delKeluarga(karyawanid, nomor) {
	param = 'nomor=' + nomor + '&karyawanid=' + karyawanid + '&del=true';
	tujuan = 'sdm_slave_save_keluarga.php';
	if (confirm('Deleting, are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerkeluarga').innerHTML = con.responseText;
					clearKeluarga();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearKeluarga() {
	document.getElementById('keluarganama').value = '';
	document.getElementById('keluargatmplahir').value = '';
	document.getElementById('keluargatgllahir').value = '';
	document.getElementById('keluargapekerjaan').value = '';
	document.getElementById('keluargatelp').value = '';
	document.getElementById('keluargaemail').value = '';
	document.getElementById('keluargabpjstanggungan').value = '';
	document.getElementById('keluargamethod').value = 'insert';

}

function fillField(nama, jeniskelamin, tempatlahir, tanggallahir, hubungankeluarga, status, levelpendidikan, pekerjaan, telp, email, tanggungan, nobpjstanggungan, emplasment, nomor) {
	document.getElementById('keluargamethod').value = 'update';
	document.getElementById('keluarganomor').value = nomor;
	document.getElementById('keluarganama').value = nama;
	document.getElementById('keluargatmplahir').value = tempatlahir;
	document.getElementById('keluargatgllahir').value = tanggallahir;
	document.getElementById('keluargapekerjaan').value = pekerjaan;
	document.getElementById('keluargatelp').value = telp;
	document.getElementById('keluargaemail').value = email;
	document.getElementById('keluargabpjstanggungan').value = nobpjstanggungan;

	// alertify.alert(nobpjstanggungan);
	// return;

	jk = document.getElementById('keluargajk');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == jeniskelamin) {
			jk.options[x].selected = true;
		}
	}
	hk = document.getElementById('hubungankeluarga');
	for (x = 0; x < hk.length; x++) {
		if (hk.options[x].value == hubungankeluarga) {
			hk.options[x].selected = true;
		}
	}
	st = document.getElementById('keluargastatus');
	for (x = 0; x < st.length; x++) {
		if (st.options[x].value == status) {
			st.options[x].selected = true;
		}
	}
	lp = document.getElementById('keluargapendidikan');
	for (x = 0; x < lp.length; x++) {
		if (lp.options[x].value == levelpendidikan) {
			lp.options[x].selected = true;
		}
	}
	tgx = document.getElementById('keluargatanggungan');
	for (x = 0; x < tgx.length; x++) {
		if (tgx.options[x].value == tanggungan) {
			tgx.options[x].selected = true;
		}
	}

	empls = document.getElementById('keluargaemplasment');
	for (x = 0; x < empls.length; x++) {
		if (empls.options[x].value == emplasment) {
			empls.options[x].selected = true;
		}
	}
}
//=================tab photo
function cancelPhoto() {
	//winForm.document.getElementById('frmUpload').reset();
}
function simpanPhoto() {
	winForm.document.getElementById('karyawanid').value = document.getElementById('karyawanid').value;
	//winForm.document.getElementById('frmUpload').submit();
}

//==============tab alamat
function simpanAlamat() {
	method=document.getElementById('methodalamat').value;
	nomor=document.getElementById('nomoralamat').value;
	karyawanid = document.getElementById('karyawanid').value;
	alamatalamat = trim(document.getElementById('alamatalamat').value);
	alamatkota = document.getElementById('alamatkota').value;
	alamatkodepos = document.getElementById('alamatkodepos').value;
	alamattelepon = document.getElementById('alamattelepon').value;
	alamatemplasement = document.getElementById('alamatemplasement').value;
	alamatstatus = document.getElementById('alamatstatus');
	alamatstatus = alamatstatus.options[alamatstatus.selectedIndex].value;
	alamatprovinsi = document.getElementById('alamatprovinsi');
	alamatprovinsi = alamatprovinsi.options[alamatprovinsi.selectedIndex].value;
	if (alamatalamat == '') {
		alertify.alert('Data incomplete');
	} else {
		param = 'alamatalamat=' + alamatalamat + '&karyawanid=' + karyawanid;
		param += '&alamatkota=' + alamatkota + '&alamatkodepos=' + alamatkodepos;
		param += '&alamattelepon=' + alamattelepon + '&alamatemplasement=' + alamatemplasement;
		param += '&alamatstatus=' + alamatstatus + '&alamatprovinsi=' + alamatprovinsi+'&method='+method + '&nomor='+nomor;
		// return;
		tujuan = 'sdm_slave_save_alamat_karyawan.php';
		if (confirm('Saving, are you sure..?')) {
			post_response_text(tujuan, param, respog);
		}
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containeralamat').innerHTML = con.responseText;
					clearAlamat();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function clearAlamat() {
	document.getElementById('alamatalamat').value = '';
	document.getElementById('alamatkota').value = '';
	document.getElementById('alamatkodepos').value = '';
	document.getElementById('alamatprovinsi').value = 'LOKAL';
	document.getElementById('alamattelepon').value = '';
	document.getElementById('alamatemplasement').value = '';
	document.getElementById('nomoralamat').value = '';
	document.getElementById('alamatstatus').value = '0';
	document.getElementById('methodalamat').value='insert';

}

function delAlamat(karyawanid, nomor) {
	param = 'nomor=' + nomor + '&karyawanid=' + karyawanid + '&del=true';
	tujuan = 'sdm_slave_save_alamat_karyawan.php';
	if (confirm('Deleting, are you sure.?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containeralamat').innerHTML = con.responseText;
					clearAlamat();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function delKeanggotaan(id) {
	karyawanid = document.getElementById('karyawanid').value;
	param = 'id=' + id + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_keanggotaan_karyawan.php?method=delete';
	if (confirm('Delete, are you sure..?')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerRAK').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanRAK() {
	karyawanid = document.getElementById('karyawanid').value;
	jumlahpotongan = document.getElementById('jumlahpotongan').value;
	listkoperasi = document.getElementById('listkoperasi');
	jenispotongan = document.getElementById('jenispotongan');
	tahunpotongandata = document.getElementById('tahunpotongan').value;
	fileupload = document.getElementById('fileupload');
	listkoperasidata = listkoperasi.options[listkoperasi.selectedIndex].value;
	jenispotongandata = jenispotongan.options[jenispotongan.selectedIndex].value;
	var file = "";
	sendUpload = "";
	if (fileupload.files && fileupload.files[0]) {
		var typeimg = ["image/jpeg", "image/png", "application/pdf"];
		var a = typeimg.indexOf(fileupload.files[0].type);
		if (fileupload.files[0].size > 257000000) {
			alertify.alert("File Max. 256MB");
		} else if (a == -1) {
			alertify.alert("File '" + fileupload.files[0].name + "' Harus dalam bentuk [ jpeg,png,pdf ] !!");
		} else {
			var fileRAKreader = new FileReader();
			fileRAKreader.onload = function (e) {
				var result = "";
				var data = "";
				if (e.target.readyState == 2) {
					data = e.target.result;
				}
				if (jumlahpotongan == '' || karyawanid == '' || tahunpotongandata == '') {
					alertify.alert('Data Incomplete');
				} else if (data == "") {
					alertify.alert('Data Upload kosong !');
				} else {
					param = 'jumlahpotongan=' + jumlahpotongan + '&karyawanid=' + karyawanid;
					param += '&listkoperasi=' + listkoperasidata + '&jenispotongan=' + jenispotongandata;
					param += '&tahunpotongan=' + tahunpotongandata + '&fileupload=' + data;
					tujuan = 'sdm_slave_save_keanggotaan_karyawan.php?method=insert';
					if (confirm('Saving, are you sure..?')) {
						post_response_text(tujuan, param, respog);
					}
				}

			}
			fileRAKreader.readAsDataURL(fileupload.files[0]);
		}
	} else {
		alertify.alert('Data Upload kosong !');
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerRAK').innerHTML = con.responseText;
					clearRAK();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function load_documentfile(karyawanid) {
	param = 'karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_uploaddocument_karyawan.php?method=loaddata';
	post_response_text(tujuan, param, callback);
	function callback() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					arrJ = JSON.parse(con.responseText);
					// console.log(arrJ);
					for (i = 0; i < arrJ.listtipe.length; i++) {
						if (document.getElementById("document_" + arrJ.listtipe[i].id)) {
							objct = document.getElementById("document_" + arrJ.listtipe[i].id);
							objct.style.display = "block";
							wrapobjct = objct.parentNode;
							rescurent = wrapobjct.getElementsByClassName('result-upload-document');
							if (rescurent.length > 0) {
								for (ii = 0; ii < rescurent.length; ii++) {
									if (rescurent[ii]) {
										wrapobjct.removeChild(rescurent[ii]);
									}
								}
							}
							wrapobjct.style.background = null;
							wrapobjct.style.cursor = null;
							wrapobjct.style.color = null;
							wrapobjct.setAttribute('onclick', "");
							wrapobjctdownload = document.getElementById("download_" + arrJ.listtipe[i].id);
							wrapobjctdownload.innerHTML = '<img src="images/download-file-die.png" width="20">';
						}

					}
					if (arrJ.listdoc.length > 0) {
						for (i = 0; i < arrJ.listdoc.length; i++) {
							fileupload = "";
							fileupload = document.getElementById("document_" + arrJ.listdoc[i].tipedokumen);
							fileupload.style.display = "none";
							wrap = fileupload.parentNode;

							div = document.createElement('div');
							div.setAttribute('class', 'result-upload-document');
							wrap.style.background = "#a0f91d";
							wrap.style.cursor = "pointer";
							wrap.style.color = "#646661";
							wrap.setAttribute('onclick', "chooseFile('document_" + arrJ.listdoc[i].tipedokumen + "')");
							div.innerHTML = arrJ.listdoc[i].namafile;
							wrap.appendChild(div);
							wrapdownload = document.getElementById("download_" + arrJ.listdoc[i].tipedokumen);
							wrapdownload.innerHTML = '<a href="fileupload/karyawan/' + karyawanid + '/' + arrJ.listdoc[i].namafile + '" download><img src="images/download-file.png" width="20"></a>';
						}

					}

					loadfiles(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function simpanUploadDoc(eleform) {
	function checkdoc(fileupload) {
		var message = "CHECKED";
		if (fileupload.files && fileupload.files[0]) {
			var typeimg = ["image/jpeg", "image/png", "application/pdf"];
			var a = typeimg.indexOf(fileupload.files[0].type);
			if (fileupload.files[0].size > 257000000) {
				message = "File Max. 256MB";
			} else if (a == -1) {
				message = "File '" + fileupload.files[0].name + "' Harus dalam bentuk [ jpeg,png,pdf ] !!";
			}
		} else {
			message = "NULL";
		}
		return message;
	}

	function upload(fileupload, karyawanid) {
		tpdoc = fileupload.getAttribute("for");
		var fileRAKreader = new FileReader();
		fileRAKreader.onload = function (e) {
			var data = "";
			if (e.target.readyState == 2) {
				data = e.target.result;
			}
			param = 'karyawanid=' + karyawanid + '&tipedoc=' + tpdoc + '&fileupload=' + data;
			tujuan = 'sdm_slave_save_uploaddocument_karyawan.php?method=insert';
			post_response_text(tujuan, param, callback);
		}
		fileRAKreader.readAsDataURL(fileupload.files[0]);
		function callback() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
					} else {
						//console.log("success="+tpdoc);
						arrJ = JSON.parse(con.responseText);
						if (arrJ.wrong && arrJ.wrong == "false") {
							fileupload.style.display = "none";
							wrap = fileupload.parentNode;
							resultcurent = wrap.getElementsByClassName('result-upload-document');
							if (resultcurent.length > 0) {
								for (ii = 0; ii < resultcurent.length; ii++) {
									if (resultcurent[ii]) {
										wrap.removeChild(resultcurent[ii]);
									}
								}
							}
							div = document.createElement('div');
							div.setAttribute('class', 'result-upload-document');
							wrap.style.background = "#a0f91d";
							wrap.style.cursor = "pointer";
							wrap.style.color = "#646661";
							wrap.setAttribute('onclick', "chooseFile('document_" + tpdoc + "')");
							div.innerHTML = arrJ.namafile;
							wrap.appendChild(div);
							download_wrap = document.getElementById('download_' + tpdoc);
							uploadfile = document.getElementById('document_' + tpdoc);
							uploadfile.value = "";
							a = '<a href="fileupload/karyawan/' + arrJ.karyawanid + '/' + arrJ.namafile + '" download=""><img src="images/download-file.png" width="20"></a>';
							download_wrap.innerHTML = a;
							return simpanUploadDoc(eleform);
						} else if (arrJ.wrong && arrJ.wrong == "true") {
							alertify.alert(arrJ.message);
						}
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	var karyawanid = document.getElementById('karyawanid').value;
	var fldoc = eleform.getElementsByTagName('input');
	//Validation data Karyawan
	if (karyawanid == '') {
		alertify.alert('Data Incomplete');
		return false;
	}
	for (i = 0; i < fldoc.length; i++) {
		var check = checkdoc(fldoc[i]);
		if (check == "CHECKED") {
			console.log(check);
			upload(fldoc[i], karyawanid);
			break;
		} else if (check == "NULL") {}
		else {
			//console.log(check);
			tpdoc = fldoc[i].getAttribute("for");
			parent = fldoc[i].parentNode;
			fldoc[i].setAttribute('onchange', 'ClearNotif(this)');
			notif = "<font color='white'>" + check + "</font>";
			elenotif = document.getElementsByClassName('cekfor');
			for (ii = 0; ii < elenotif.length; ii++) {
				if (elenotif[ii]) {
					parent.removeChild(elenotif[ii]);
				}
			}
			ele = document.createElement("div");
			ele.setAttribute('class', 'cekfor');
			ele.style = "float:right;";
			ele.innerHTML = notif;
			parent.appendChild(ele);
			parent.style.background = "red";
			parent.style.color = "white";
			fldoc[i].value = "";
			break;
			return simpanUploadDoc(eleform);
		}
	}
}
function ClearNotif(ele) {
	parent = ele.parentNode;
	parent.style.background = "none";
	parent.style.color = "black";
	elenotif = document.getElementsByClassName('cekfor');
	for (ii = 0; ii < elenotif.length; ii++) {
		if (elenotif[ii]) {
			parent.removeChild(elenotif[ii]);
		}
	}
	ele.setAttribute('onchange', '');
}
function load_keanggotaan(karyawanid) {
	param = 'karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_keanggotaan_karyawan.php';
	post_response_text(tujuan, param, containerRAK);
	function containerRAK() {
		if (con.readyState == 4) {
			//console.log('masuk3');
			if (con.status == 200) {
				//console.log(con.responseText);
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerRAK').innerHTML = con.responseText;
					load_documentfile(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearRAK() {
	document.getElementById('jumlahpotongan').value = '';
	document.getElementById('listkoperasi').value = '';
	document.getElementById('jenispotongan').value = '';
	document.getElementById('tahunpotongan').value = '';
}
//=====================list click
function displayList() {
	document.getElementById('frminput').style.display = 'none';
	document.getElementById('searchplace').style.display = '';
	document.getElementById('statuspajak').disabled=false;
	loadEmployeeList();
	document.getElementById('postingdata').style.display = 'none';
}
function add_posting() {
	document.getElementById('frminput').style.display = 'none';
	document.getElementById('searchplace').style.display = 'none';
	document.getElementById('postingdata').style.display = '';
	listpostingdata();
}

function displayFormInput() {
	document.getElementById('postingdata').style.display = 'none';
	document.getElementById('frminput').style.display = '';
	document.getElementById('searchplace').style.display = 'none';
	cancelDataKaryawan();
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    listpostingdata(paged);	
}

function listpostingdata(page) {
	param = '';
	param += '&method=listpostingdata&page=' + page;
	tujuan = 'sdm_slave_load_employee_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('listpostingdata').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function closedatakary(kodeorg, periode) {
	param = '';
	param += '&kodeorg='+kodeorg;
	param += '&periode='+periode;
	param += '&method=closedatakary';
	tujuan = 'sdm_slave_load_employee_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					listpostingdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function unclosedatakary(kodeorg, periode) {
	param = '';
	param += '&kodeorg='+kodeorg;
	param += '&periode='+periode;
	param += '&method=unclosedatakary';
	tujuan = 'sdm_slave_load_employee_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					listpostingdata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadEmployeeList() {
	param = '';
	param += '&method=loaddata';
	tujuan = 'sdm_slave_load_employee_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('searchplaceresult').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function prefDatakaryawan(btn, curval) {
	if (curval == 0) {
		document.getElementById('prefbtn').disabled = true;
		return false;
	} else {
		cariKaryawan(curval);
		btn.value = parseInt(curval) - 1;
		document.getElementById('nextbtn').value = parseInt(btn.value) + 2;
	}
}
function nextDatakaryawan(btn, curval) {
	cariKaryawan(curval);
	document.getElementById('prefbtn').disabled = false;
}

function cariKaryawan(page) {
	displayList();
	txtsearch = trim(document.getElementById('txtsearch').value);
	schorg = document.getElementById('schorg');
	schtipe = document.getElementById('schtipe');
	schstatus = document.getElementById('schstatus');
	schjk = document.getElementById('schjk');
	noktp = document.getElementById('noktpsch').value;
	
	
	//schjk=schjk.options[schjk.selectedIndex].value;
	schorg = schorg.options[schorg.selectedIndex].value;
	schtipe = schtipe.options[schtipe.selectedIndex].value;
	schstatus = schstatus.options[schstatus.selectedIndex].value;

	param = 'txtsearch=' + txtsearch;
	param += '&method=loaddata';
	param += '&orgsearch=' + schorg;
	param += '&tipesearch=' + schtipe;
	param += '&statussearch=' + schstatus;
	//patam+='&schjk='+schjk;
	param += '&page=' + page;
	param += '&noktp=' + noktp;

	//alertify.alert(param);

	tujuan = 'sdm_slave_load_employee_list.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (con.responseText == 'notfound') {
						document.getElementById('nextbtn').disabled = true;
					} else {
						nextPage = parseInt(page) + 1;
						document.getElementById('nextbtn').value = nextPage;
						document.getElementById('prefbtn').value = parseInt(nextPage) - 2;
						document.getElementById('nextbtn').disabled = false;
						document.getElementById('searchplaceresult').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changeCaption(text) {
	document.getElementById('cap1').innerHTML = text;
}
function changeCaption1(text) {
	document.getElementById('cap2').innerHTML = text;
}

function editKaryawan(karyawanid, namakaryawan) {
	document.getElementById('periodeakhirgaji').value = '';
	param = 'karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_get_employee_for_edit.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadFormKaryawan(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editKaryawanhist(nourut, karyawanid,namakaryawan) {
	param = 'karyawanid=' + karyawanid;
	param += '&nourut=' + nourut;
	param += '&namakaryawan=' + namakaryawan;
	tujuan = 'sdm_slave_get_employee_hist_for_edit.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadFormKaryawanhist(con.responseText);
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadFormKaryawan(tex) {
	
	//display input form
	displayFormInput();
	//parse XML
	xml = tex.toString();
	xmlobject = (new DOMParser()).parseFromString(xml, "text/xml");
	// console.log(xmlobject);
	
	//Extract XML
	karyawanid         = xmlobject.getElementsByTagName('karyawanid')[0].firstChild.nodeValue;
	displayphoto       = xmlobject.getElementsByTagName('displayphoto')[0].firstChild.nodeValue;
	nik                = xmlobject.getElementsByTagName('nik')[0].firstChild.nodeValue;
	nik                = nik.replace("*", "");

	namakaryawan       = xmlobject.getElementsByTagName('namakaryawan')[0].firstChild.nodeValue;
	namakaryawan       = namakaryawan.replace("*", "");
	/*namareward       =xmlobject.getElementsByTagName('namareward')[0].firstChild.nodeValue;
	namareward         =namareward.replace("*","");*/
	tempatlahir        = xmlobject.getElementsByTagName('tempatlahir')[0].firstChild.nodeValue;
	tempatlahir        = tempatlahir.replace("*", "");
	tanggallahir       = xmlobject.getElementsByTagName('tanggallahir')[0].firstChild.nodeValue;
	warganegara        = xmlobject.getElementsByTagName('warganegara')[0].firstChild.nodeValue;
	warganegara        = warganegara.replace("*", "");
	suku               = xmlobject.getElementsByTagName('suku')[0].firstChild.nodeValue;
	suku               = suku.replace("*", "");
	statuskaryawan     = xmlobject.getElementsByTagName('statuskaryawan')[0].firstChild.nodeValue;
	statuskaryawan     = statuskaryawan.replace("*", "");
	jeniskelamin       = xmlobject.getElementsByTagName('jeniskelamin')[0].firstChild.nodeValue;
	jeniskelamin       = jeniskelamin.replace("*", "");
	statusperkawinan   = xmlobject.getElementsByTagName('statusperkawinan')[0].firstChild.nodeValue;
	statusperkawinan   = statusperkawinan.replace("*", "");
	tanggalmenikah     = xmlobject.getElementsByTagName('tanggalmenikah')[0].firstChild.nodeValue;
	agama              = xmlobject.getElementsByTagName('agama')[0].firstChild.nodeValue;
	agama              = agama.replace("*", "");
	golongandarah      = xmlobject.getElementsByTagName('golongandarah')[0].firstChild.nodeValue;
	golongandarah      = golongandarah.replace("*", "");
	levelpendidikan    = xmlobject.getElementsByTagName('levelpendidikan')[0].firstChild.nodeValue;
	levelpendidikan    = levelpendidikan.replace("*", "");
	alamataktif        = xmlobject.getElementsByTagName('alamataktif')[0].firstChild.nodeValue;
	alamataktif        = alamataktif.replace("*", "");
	provinsi           = xmlobject.getElementsByTagName('provinsi')[0].firstChild.nodeValue;
	provinsi           = provinsi.replace("*", "");
	kota               = xmlobject.getElementsByTagName('kota')[0].firstChild.nodeValue;
	kota               = kota.replace("*", "");
	kodepos            = xmlobject.getElementsByTagName('kodepos')[0].firstChild.nodeValue;
	kodepos            = kodepos.replace("*", "");
	noteleponrumah     = xmlobject.getElementsByTagName('noteleponrumah')[0].firstChild.nodeValue;
	noteleponrumah     = noteleponrumah.replace("*", "");
	nohp               = xmlobject.getElementsByTagName('nohp')[0].firstChild.nodeValue;
	nohp               = nohp.replace("*", "");
	nohp2              = xmlobject.getElementsByTagName('nohp2')[0].firstChild.nodeValue;
	nohp2              = nohp2.replace("*", "");

	norekeningbank     = xmlobject.getElementsByTagName('norekeningbank')[0].firstChild.nodeValue;
	norekeningbank     = norekeningbank.replace("*", "");
	namabank           = xmlobject.getElementsByTagName('namabank')[0].firstChild.nodeValue;
	namabank           = namabank.replace("*", "");
	pemilikrekening    = xmlobject.getElementsByTagName('pemilikrekening')[0].firstChild.nodeValue;
	sistemgaji         = xmlobject.getElementsByTagName('sistemgaji')[0].firstChild.nodeValue;
	sistemgaji         = sistemgaji.replace("*", "");
	nopaspor           = xmlobject.getElementsByTagName('nopaspor')[0].firstChild.nodeValue;
	nopaspor           = nopaspor.replace("*", "");
	noktp              = xmlobject.getElementsByTagName('noktp')[0].firstChild.nodeValue;
	noktp              = noktp.replace("*", "");
	notelepondarurat   = xmlobject.getElementsByTagName('notelepondarurat')[0].firstChild.nodeValue;
	notelepondarurat   = notelepondarurat.replace("*", "");
	tanggalmasuk       = xmlobject.getElementsByTagName('tanggalmasuk')[0].firstChild.nodeValue;
	tanggalpengangkatan= xmlobject.getElementsByTagName('tanggalpengangkatan')[0].firstChild.nodeValue;
	tanggalpengangkatannonstaff= xmlobject.getElementsByTagName('tanggalpengangkatannonstaff')[0].firstChild.nodeValue;
	tanggalkeluar      = xmlobject.getElementsByTagName('tanggalkeluar')[0].firstChild.nodeValue;
	tipekaryawan       = xmlobject.getElementsByTagName('tipekaryawan')[0].firstChild.nodeValue;
	tipekaryawan       = tipekaryawan.replace("*", "");
	jumlahanak         = xmlobject.getElementsByTagName('jumlahanak')[0].firstChild.nodeValue;
	jumlahanak         = jumlahanak.replace("*", "");
	jumlahtanggungan   = xmlobject.getElementsByTagName('jumlahtanggungan')[0].firstChild.nodeValue;
	jumlahtanggungan   = jumlahtanggungan.replace("*", "");
	statuspajak        = xmlobject.getElementsByTagName('statuspajak')[0].firstChild.nodeValue;
	statuspajak        = statuspajak.replace("*", "");
	npwp               = xmlobject.getElementsByTagName('npwp')[0].firstChild.nodeValue;
	kppnpwp            = xmlobject.getElementsByTagName('kppnpwp')[0].firstChild.nodeValue;
	bpjs               = xmlobject.getElementsByTagName('bpjs')[0].firstChild.nodeValue;
	pensiun            = xmlobject.getElementsByTagName('pensiun')[0].firstChild.nodeValue;

	npwp               = npwp.replace("*", "");
	bpjs               = bpjs.replace("*", "");
	pensiun            = pensiun.replace("*", "");
	lokasipenerimaan   = xmlobject.getElementsByTagName('lokasipenerimaan')[0].firstChild.nodeValue;
	lokasipenerimaan   = lokasipenerimaan.replace("*", "");
	kodeorganisasi     = xmlobject.getElementsByTagName('kodeorganisasi')[0].firstChild.nodeValue;
	kodeorganisasi     = kodeorganisasi.replace("*", "");
	bagian             = xmlobject.getElementsByTagName('bagian')[0].firstChild.nodeValue;
	bagian             = bagian.replace("*", "");
	levelkaryawan      = xmlobject.getElementsByTagName('levelkaryawan')[0].firstChild.nodeValue;
	levelkaryawan      = levelkaryawan.replace("*", "");
	subdept            = xmlobject.getElementsByTagName('subdept')[0].firstChild.nodeValue;
	subdept            = subdept.replace("*", "");
	kodejabatan        = xmlobject.getElementsByTagName('kodejabatan')[0].firstChild.nodeValue;
	kodejabatan        = kodejabatan.replace("*", "");
	kodegolongan       = xmlobject.getElementsByTagName('kodegolongan')[0].firstChild.nodeValue;
	kodegolongan       = kodegolongan.replace("*", "");
	lokasitugas        = xmlobject.getElementsByTagName('lokasitugas')[0].firstChild.nodeValue;
	lokasitugas        = lokasitugas.replace("*", "");

	periodeakhirgaji   = xmlobject.getElementsByTagName('periodeakhirgaji')[0].firstChild.nodeValue;
	periodeakhirgaji   = periodeakhirgaji.replace("*", "");

	photo              = xmlobject.getElementsByTagName('photo')[0].firstChild.nodeValue;
	photo              = photo.replace("*", "");
	email              = xmlobject.getElementsByTagName('email')[0].firstChild.nodeValue;
	email              = email.replace("*", "");

	emailkantor        = xmlobject.getElementsByTagName('emailkantor')[0].firstChild.nodeValue;
	emailkantor        = emailkantor.replace("*", "");

	alokasi            = xmlobject.getElementsByTagName('alokasi')[0].firstChild.nodeValue;
	alokasi            = alokasi.replace("*", "");
	subbagian          = xmlobject.getElementsByTagName('subbagian')[0].firstChild.nodeValue;
	subbagian          = subbagian.replace("*", "");
	jms                = xmlobject.getElementsByTagName('jms')[0].firstChild.nodeValue;
	jms                = jms.replace("*", "");
	catu               = xmlobject.getElementsByTagName('catu')[0].firstChild.nodeValue;
	catu               = catu.replace("*", "");
	dptPremi           = xmlobject.getElementsByTagName('dptPremi')[0].firstChild.nodeValue;

	
	/*
	statusakad         =xmlobject.getElementsByTagName('statusakad')[0].firstChild.nodeValue;
	statusakad         =statusakad.replace("*","");*/

	//insstatuspajak   =xmlobject.getElementsByTagName('insstatuspajak')[0].firstChild.nodeValue;
	//insstatuspajak   =insstatuspajak.replace("*","");

	sim      = xmlobject.getElementsByTagName('sim')[0].firstChild.nodeValue;
	sim      = sim.replace("*", "");

	supbpjs  = xmlobject.getElementsByTagName('supbpjs')[0].firstChild.nodeValue;
	supbpjs  = supbpjs.replace("*", "");
	
	
	kabupaten= xmlobject.getElementsByTagName('kabupaten')[0].firstChild.nodeValue;
	kabupaten= kabupaten.replace("*", "");
	
	kecamatan= xmlobject.getElementsByTagName('kecamatan')[0].firstChild.nodeValue;
	kecamatan= kecamatan.replace("*", "");
	
	desa     = xmlobject.getElementsByTagName('desa')[0].firstChild.nodeValue;
	desa     = desa.replace("*", "");
	
	namaprovinsi = xmlobject.getElementsByTagName('namaprovinsi')[0].firstChild.nodeValue;
	namaprovinsi = namaprovinsi.replace("*", "");
	namakabupaten= xmlobject.getElementsByTagName('namakabupaten')[0].firstChild.nodeValue;
	namakabupaten= namakabupaten.replace("*", "");
	namakecamatan= xmlobject.getElementsByTagName('namakecamatan')[0].firstChild.nodeValue;
	namakecamatan= namakecamatan.replace("*", "");
	namadesa     = xmlobject.getElementsByTagName('namadesa')[0].firstChild.nodeValue;
	namadesa     = namadesa.replace("*", "");
	
	bulandaftarbpjs     = xmlobject.getElementsByTagName('bulandaftarbpjs')[0].firstChild.nodeValue;
	document.getElementById('bulandaftarbpjs').value = bulandaftarbpjs;

	// enableedittglmasuk     = xmlobject.getElementsByTagName('enableedittglmasuk')[0].firstChild.nodeValue;


	// if(enableedittglmasuk=='0'){
	// 	document.getElementById('tanggalkeluar').disabled = false;
	// 	document.getElementById('tanggalmasuk').disabled = true;
	// }else{
		
	// 	document.getElementById('tanggalkeluar').disabled = false;
	// 	document.getElementById('tanggalmasuk').disabled = false;

	// }
	
	document.getElementById('bulandaftarbpjs').value = bulandaftarbpjs;
	
	document.getElementById('namakabupaten').value = namakabupaten;
	document.getElementById('namakecamatan').value = namakecamatan;
	document.getElementById('namadesa').value = namadesa;
	document.getElementById('namaprovinsi').value = namaprovinsi;
	
	document.getElementById('kabupaten').value = kabupaten;
	document.getElementById('kecamatan').value = kecamatan;
	document.getElementById('desa').value = desa;
	
	//load form from extracted valiable
	document.getElementById('vstatuspajak').value = statuspajak;
	if(statuspajak=='TK'){
		document.getElementById('statuspajak').value='TK0';
	}else{
		document.getElementById('statuspajak').value=statuspajak;
	}
	document.getElementById('statuspajak').disabled=true;
	document.getElementById('nik').value = nik;
	document.getElementById('namakaryawan').value = namakaryawan;
	// document.getElementById('namareward').value=namareward;
	document.getElementById('tempatlahir').value = tempatlahir;
	document.getElementById('tanggallahir').value = tanggallahir;
	//console.log(displayphoto);
	if (displayphoto !== "*") {
		//console.log("photokaryawan/"+displayphoto);
		var photoimg = document.getElementById('displayphoto');
		// Nama file foto tetap sama walau isinya diganti (photo_<karyawanid>.jpeg),
		// jadi perlu cache-buster supaya browser tidak menampilkan foto lama dari cache.
		photoimg.setAttribute("src", "photokaryawan/" + displayphoto + "?v=" + new Date().getTime());
		var photoboth = document.getElementById('photoboth');
		photoimg.style.width = 150 + "px";
		photoboth.style.backgroundImage = "";
		photoboth.style.width = "150px";
		photoimg = document.getElementById('displayphoto');
		photoboth.style.height = "auto";
		photoboth.style.border = "solid 2px #FFF";

	}
	jk = document.getElementById('jeniskelamin');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == jeniskelamin) {
			jk.options[x].selected = true;
		}
	}
	ag = document.getElementById('agama');
	for (x = 0; x < ag.length; x++) {
		if (ag.options[x].value == agama) {
			ag.options[x].selected = true;
		}
	}
	bg = document.getElementById('bagian');
	for (x = 0; x < bg.length; x++) {
		if (bg.options[x].value == bagian) {
			bg.options[x].selected = true;
		}
	}
	setValue2('bagian',bagian);
	
	sd = document.getElementById('subdept');
	for (x = 0; x < sd.length; x++) {
		if (sd.options[x].value == subdept) {
			sd.options[x].selected = true;
		}
	}
	setValue2('subdept',subdept);
	supp = document.getElementById('supbpjs');
	for (x = 0; x < supp.length; x++) {
		if (supp.options[x].value == supbpjs) {
			supp.options[x].selected = true;
		}
	}

	lk = document.getElementById('levelkaryawan');
	for (x = 0; x < lk.length; x++) {
		if (lk.options[x].value == levelkaryawan) {
			lk.options[x].selected = true;
		}
	}

	setValue2('levelkaryawan',levelkaryawan);
	lvlkaryawan = document.getElementById('levelkaryawan');
	for (x = 0; x < lvlkaryawan.length; x++) {
		if (lvlkaryawan.options[x].value == levelkaryawan) {
			lvlkaryawan.options[x].selected = true;
		}
	}

	jbt = document.getElementById('kodejabatan');
	for (x = 0; x < jbt.length; x++) {
		if (jbt.options[x].value == kodejabatan) {
			jbt.options[x].selected = true;
		}
	}
	setValue2('kodejabatan',kodejabatan);
	gol = document.getElementById('kodegolongan');
	for (x = 0; x < gol.length; x++) {
		if (gol.options[x].value == kodegolongan) {
			gol.options[x].selected = true;
		}
	}
	setValue2('kodegolongan',kodegolongan);
	tgs = document.getElementById('lokasitugas');
	for (x = 0; x < tgs.length; x++) {
		if (tgs.options[x].value == lokasitugas) {
			tgs.options[x].selected = true;
		}
	}
	setValue2('lokasitugas',lokasitugas);
	
	pag = document.getElementById('periodeakhirgaji');
	for (x = 0; x < pag.length; x++) {
		if (pag.options[x].value == periodeakhirgaji) {
			pag.options[x].selected = true;
		}
	}

	org = document.getElementById('kodeorganisasi');
	for (x = 0; x < org.length; x++) {
		if (org.options[x].value == kodeorganisasi) {
			org.options[x].selected = true;
		}
	}


	tik = document.getElementById('tipekaryawan');
	for (x = 0; x < tik.length; x++) {
		if (tik.options[x].value == tipekaryawan) {
			tik.options[x].selected = true;
		}
	}
	setValue2('tipekaryawan',tipekaryawan);

	tok = document.getElementById('subbagian');
	for (x = 0; x < tok.length; x++) {
		if (tok.options[x].value == subbagian) {
			tok.options[x].selected = true;
		}
	}
	setValue2('subbagian',subbagian);
	
	cat = document.getElementById('catu');
	for (x = 0; x < cat.length; x++) {
		if (cat.options[x].value == catu) {
			cat.options[x].selected = true;
		}
	}
	setValue2('catu',catu);

	document.getElementById('noktp').value = noktp;
	document.getElementById('nopassport').value = nopaspor;
	wni = document.getElementById('warganegara');
	for (x = 0; x < wni.length; x++) {
		if (wni.options[x].value == warganegara) {
			wni.options[x].selected = true;
		}
	}
	suk = document.getElementById('suku');
	for (x = 0; x < suk.length; x++) {
		if (suk.options[x].value == suku) {
			suk.options[x].selected = true;
		}
	}
	setValue2('suku',suku);
	
	statkarya = document.getElementById('statuskaryawan');
	for (x = 0; x < statkarya.length; x++) {
		if (statkarya.options[x].value == statuskaryawan) {
			statkarya.options[x].selected = true;
		}
	}
	poh = document.getElementById('lokasipenerimaan');
	for (x = 0; x < poh.length; x++) {
		if (poh.options[x].value == lokasipenerimaan) {
			poh.options[x].selected = true;
		}
	}
	setValue2('lokasipenerimaan',lokasipenerimaan);
	
	kppn = document.getElementById('kppnpwp');
	for (x = 0; x < kppn.length; x++) {
		if (kppn.options[x].value == kppnpwp) {
			kppn.options[x].selected = true;
		}
	}

	/*stpj=document.getElementById('statuspajak');
	for(x=0;x<stpj.length;x++){
	if(stpj.options[x].value==insstatuspajak){
	stpj.options[x].selected=true;
	}
	}*/
	document.getElementById('npwp').value = npwp;
	document.getElementById('bpjs').value = bpjs;
	document.getElementById('pensiun').value = pensiun;
	document.getElementById('alamataktif').value = alamataktif;
	document.getElementById('kota').value = kota;
	document.getElementById('provinsi').value=provinsi;
	
	/* prov = document.getElementById('provinsi');
	for (x = 0; x < prov.length; x++) {
		if (prov.options[x].value == provinsi) {
			prov.options[x].selected = true;
		}
	} */
	document.getElementById('kodepos').value = kodepos;
	document.getElementById('noteleponrumah').value = noteleponrumah;
	document.getElementById('nohp').value = nohp;
	document.getElementById('nohp2').value = nohp2;
	document.getElementById('norekeningbank').value = norekeningbank;
	document.getElementById('namabank').value = namabank;
	document.getElementById('anrekening').value = pemilikrekening;

	stmgj = document.getElementById('sistemgaji');
	for (x = 0; x < stmgj.length; x++) {
		if (stmgj.options[x].value == sistemgaji) {
			stmgj.options[x].selected = true;
		}
	}

	goldar = document.getElementById('golongandarah');
	for (x = 0; x < goldar.length; x++) {
		if (goldar.options[x].value == golongandarah) {
			goldar.options[x].selected = true;
		}
	}
	document.getElementById('tanggalmasuk').value = tanggalmasuk;
	document.getElementById('tanggalpengangkatan').value = tanggalpengangkatan;
	document.getElementById('tanggalpengangkatannonstaff').value = tanggalpengangkatannonstaff;
	
	document.getElementById('tanggalkeluar').value = tanggalkeluar;
	stk = document.getElementById('statusperkawinan');
	for (x = 0; x < stk.length; x++) {
		if (stk.options[x].value == statusperkawinan) {
			stk.options[x].selected = true;
		}
	}
	document.getElementById('tanggalmenikah').value = tanggalmenikah;
	document.getElementById('jumlahanak').value = jumlahanak;
	document.getElementById('jumlahtanggungan').value = jumlahtanggungan;
	document.getElementById('jms').value = jms;

	lvlpndk = document.getElementById('levelpendidikan');
	for (x = 0; x < lvlpndk.length; x++) {
		if (lvlpndk.options[x].value == levelpendidikan) {
			lvlpndk.options[x].selected = true;
		}
	}
	
	/*sttAkad=document.getElementById('statusakad');
	for(x=0;x<sttAkad.length;x++){
	if(sttAkad.options[x].value==statusakad){
	sttAkad.options[x].selected=true;
	}
	}*/

	document.getElementById('sim').value = sim;

	document.getElementById('notelepondarurat').value = notelepondarurat;
	document.getElementById('email').value = email;
	document.getElementById('emailkantor').value = emailkantor;
	document.getElementById('alokasi').value = alokasi;
	//change the method to update===========================
	document.getElementById('method').value = 'update';
	document.getElementById('savePhoto').hidden = true;
	document.getElementById('karyawanid').value = karyawanid;
	document.getElementById('karyawanidreward').value = karyawanid;

	document.getElementById('tabFRM0').innerHTML = namakaryawan;
	//document.getElementById('tabFRM8').innerHTML=namakaryawan;
	document.getElementById('dptPremi').checked = false;
	if (dptPremi == 1) {
		document.getElementById('dptPremi').checked = true;
	}
	//load lis keanggotaan


	//=========================
	//enable save button each tab
	disabledonpayroll(tipekaryawan);
	enableOtherButton();
	loadExperience('queryonly', karyawanid);
	//loac photo
	// document.getElementById('displayphoto2').removeAttribute('src');
	// document.getElementById('displayphoto2').setAttribute('src',photo);
}

function loadFormKaryawanhist(tex) {
	//display input form
	displayFormInput();
	//parse XML
	xml = tex.toString();
	xmlobject = (new DOMParser()).parseFromString(xml, "text/xml");
	
	//Extract XML
	nourut = xmlobject.getElementsByTagName('nourutx')[0].firstChild.nodeValue;
	nourut = nourut.replace("*", "");
	karyawanid = xmlobject.getElementsByTagName('karyawanid')[0].firstChild.nodeValue;
	displayphoto = xmlobject.getElementsByTagName('displayphoto')[0].firstChild.nodeValue;
	nik = xmlobject.getElementsByTagName('nik')[0].firstChild.nodeValue;
	nik = nik.replace("*", "");

	namakaryawan = xmlobject.getElementsByTagName('namakaryawan')[0].firstChild.nodeValue;
	namakaryawan = namakaryawan.replace("*", "");
	/*namareward   =xmlobject.getElementsByTagName('namareward')[0].firstChild.nodeValue;
	namareward  =namareward.replace("*","");*/
	tempatlahir = xmlobject.getElementsByTagName('tempatlahir')[0].firstChild.nodeValue;
	tempatlahir = tempatlahir.replace("*", "");
	tanggallahir = xmlobject.getElementsByTagName('tanggallahir')[0].firstChild.nodeValue;
	warganegara = xmlobject.getElementsByTagName('warganegara')[0].firstChild.nodeValue;
	warganegara = warganegara.replace("*", "");
	suku = xmlobject.getElementsByTagName('suku')[0].firstChild.nodeValue;
	suku = suku.replace("*", "");
	statuskaryawan = xmlobject.getElementsByTagName('statuskaryawan')[0].firstChild.nodeValue;
	statuskaryawan = statuskaryawan.replace("*", "");
	jeniskelamin = xmlobject.getElementsByTagName('jeniskelamin')[0].firstChild.nodeValue;
	jeniskelamin = jeniskelamin.replace("*", "");
	statusperkawinan = xmlobject.getElementsByTagName('statusperkawinan')[0].firstChild.nodeValue;
	statusperkawinan = statusperkawinan.replace("*", "");
	tanggalmenikah = xmlobject.getElementsByTagName('tanggalmenikah')[0].firstChild.nodeValue;
	agama = xmlobject.getElementsByTagName('agama')[0].firstChild.nodeValue;
	agama = agama.replace("*", "");
	golongandarah = xmlobject.getElementsByTagName('golongandarah')[0].firstChild.nodeValue;
	golongandarah = golongandarah.replace("*", "");
	levelpendidikan = xmlobject.getElementsByTagName('levelpendidikan')[0].firstChild.nodeValue;
	levelpendidikan = levelpendidikan.replace("*", "");
	alamataktif = xmlobject.getElementsByTagName('alamataktif')[0].firstChild.nodeValue;
	alamataktif = alamataktif.replace("*", "");
	provinsi = xmlobject.getElementsByTagName('provinsi')[0].firstChild.nodeValue;
	provinsi = provinsi.replace("*", "");
	kota = xmlobject.getElementsByTagName('kota')[0].firstChild.nodeValue;
	kota = kota.replace("*", "");
	kodepos = xmlobject.getElementsByTagName('kodepos')[0].firstChild.nodeValue;
	kodepos = kodepos.replace("*", "");
	noteleponrumah = xmlobject.getElementsByTagName('noteleponrumah')[0].firstChild.nodeValue;
	noteleponrumah = noteleponrumah.replace("*", "");
	nohp = xmlobject.getElementsByTagName('nohp')[0].firstChild.nodeValue;
	nohp = nohp.replace("*", "");
	nohp2 = xmlobject.getElementsByTagName('nohp2')[0].firstChild.nodeValue;
	nohp2 = nohp2.replace("*", "");

	norekeningbank = xmlobject.getElementsByTagName('norekeningbank')[0].firstChild.nodeValue;
	norekeningbank = norekeningbank.replace("*", "");
	namabank = xmlobject.getElementsByTagName('namabank')[0].firstChild.nodeValue;
	namabank = namabank.replace("*", "");
	pemilikrekening = xmlobject.getElementsByTagName('pemilikrekening')[0].firstChild.nodeValue;
	sistemgaji = xmlobject.getElementsByTagName('sistemgaji')[0].firstChild.nodeValue;
	sistemgaji = sistemgaji.replace("*", "");
	nopaspor = xmlobject.getElementsByTagName('nopaspor')[0].firstChild.nodeValue;
	nopaspor = nopaspor.replace("*", "");
	noktp = xmlobject.getElementsByTagName('noktp')[0].firstChild.nodeValue;
	noktp = noktp.replace("*", "");
	notelepondarurat = xmlobject.getElementsByTagName('notelepondarurat')[0].firstChild.nodeValue;
	notelepondarurat = notelepondarurat.replace("*", "");
	tanggalmasuk = xmlobject.getElementsByTagName('tanggalmasuk')[0].firstChild.nodeValue;
	tanggalpengangkatan = xmlobject.getElementsByTagName('tanggalpengangkatan')[0].firstChild.nodeValue;
	tanggalpengangkatannonstaff = xmlobject.getElementsByTagName('tanggalpengangkatannonstaff')[0].firstChild.nodeValue;
	tanggalkeluar = xmlobject.getElementsByTagName('tanggalkeluar')[0].firstChild.nodeValue;
	tipekaryawan = xmlobject.getElementsByTagName('tipekaryawan')[0].firstChild.nodeValue;
	tipekaryawan = tipekaryawan.replace("*", "");
	jumlahanak = xmlobject.getElementsByTagName('jumlahanak')[0].firstChild.nodeValue;
	jumlahanak = jumlahanak.replace("*", "");
	jumlahtanggungan = xmlobject.getElementsByTagName('jumlahtanggungan')[0].firstChild.nodeValue;
	jumlahtanggungan = jumlahtanggungan.replace("*", "");
	statuspajak = xmlobject.getElementsByTagName('statuspajak')[0].firstChild.nodeValue;
	statuspajak = statuspajak.replace("*", "");
	npwp = xmlobject.getElementsByTagName('npwp')[0].firstChild.nodeValue;
	kppnpwp = xmlobject.getElementsByTagName('kppnpwp')[0].firstChild.nodeValue;
	bpjs = xmlobject.getElementsByTagName('bpjs')[0].firstChild.nodeValue;
	pensiun = xmlobject.getElementsByTagName('pensiun')[0].firstChild.nodeValue;

	npwp = npwp.replace("*", "");
	bpjs = bpjs.replace("*", "");
	pensiun = pensiun.replace("*", "");
	lokasipenerimaan = xmlobject.getElementsByTagName('lokasipenerimaan')[0].firstChild.nodeValue;
	lokasipenerimaan = lokasipenerimaan.replace("*", "");
	kodeorganisasi = xmlobject.getElementsByTagName('kodeorganisasi')[0].firstChild.nodeValue;
	kodeorganisasi = kodeorganisasi.replace("*", "");
	bagian = xmlobject.getElementsByTagName('bagian')[0].firstChild.nodeValue;
	bagian = bagian.replace("*", "");
	levelkaryawan = xmlobject.getElementsByTagName('levelkaryawan')[0].firstChild.nodeValue;
	levelkaryawan = levelkaryawan.replace("*", "");
	subdept = xmlobject.getElementsByTagName('subdept')[0].firstChild.nodeValue;
	subdept = subdept.replace("*", "");
	kodejabatan = xmlobject.getElementsByTagName('kodejabatan')[0].firstChild.nodeValue;
	kodejabatan = kodejabatan.replace("*", "");
	kodegolongan = xmlobject.getElementsByTagName('kodegolongan')[0].firstChild.nodeValue;
	kodegolongan = kodegolongan.replace("*", "");
	lokasitugas = xmlobject.getElementsByTagName('lokasitugas')[0].firstChild.nodeValue;
	lokasitugas = lokasitugas.replace("*", "");

	periodeakhirgaji = xmlobject.getElementsByTagName('periodeakhirgaji')[0].firstChild.nodeValue;
	periodeakhirgaji = periodeakhirgaji.replace("*", "");

	photo = xmlobject.getElementsByTagName('photo')[0].firstChild.nodeValue;
	photo = photo.replace("*", "");
	email = xmlobject.getElementsByTagName('email')[0].firstChild.nodeValue;
	email = email.replace("*", "");

	emailkantor = xmlobject.getElementsByTagName('emailkantor')[0].firstChild.nodeValue;
	emailkantor = emailkantor.replace("*", "");

	alokasi = xmlobject.getElementsByTagName('alokasi')[0].firstChild.nodeValue;
	alokasi = alokasi.replace("*", "");
	subbagian = xmlobject.getElementsByTagName('subbagian')[0].firstChild.nodeValue;
	subbagian = subbagian.replace("*", "");
	jms = xmlobject.getElementsByTagName('jms')[0].firstChild.nodeValue;
	jms = jms.replace("*", "");
	catu = xmlobject.getElementsByTagName('catu')[0].firstChild.nodeValue;
	catu = catu.replace("*", "");
	dptPremi = xmlobject.getElementsByTagName('dptPremi')[0].firstChild.nodeValue;
	/*
	statusakad	=xmlobject.getElementsByTagName('statusakad')[0].firstChild.nodeValue;
	statusakad=statusakad.replace("*","");*/

	//insstatuspajak	=xmlobject.getElementsByTagName('insstatuspajak')[0].firstChild.nodeValue;
	//insstatuspajak	=insstatuspajak.replace("*","");


	sim = xmlobject.getElementsByTagName('sim')[0].firstChild.nodeValue;
	sim = sim.replace("*", "");

	supbpjs = xmlobject.getElementsByTagName('supbpjs')[0].firstChild.nodeValue;
	supbpjs = supbpjs.replace("*", "");
	
	kabupaten= xmlobject.getElementsByTagName('kabupaten')[0].firstChild.nodeValue;
	kabupaten= kabupaten.replace("*", "");
	
	kecamatan= xmlobject.getElementsByTagName('kecamatan')[0].firstChild.nodeValue;
	kecamatan= kecamatan.replace("*", "");
	
	desa     = xmlobject.getElementsByTagName('desa')[0].firstChild.nodeValue;
	desa     = desa.replace("*", "");
	
	namaprovinsi = xmlobject.getElementsByTagName('namaprovinsi')[0].firstChild.nodeValue;
	namaprovinsi = namaprovinsi.replace("*", "");
	namakabupaten= xmlobject.getElementsByTagName('namakabupaten')[0].firstChild.nodeValue;
	namakabupaten= namakabupaten.replace("*", "");
	namakecamatan= xmlobject.getElementsByTagName('namakecamatan')[0].firstChild.nodeValue;
	namakecamatan= namakecamatan.replace("*", "");
	namadesa     = xmlobject.getElementsByTagName('namadesa')[0].firstChild.nodeValue;
	namadesa     = namadesa.replace("*", "");
	
	bulandaftarbpjs     = xmlobject.getElementsByTagName('bulandaftarbpjs')[0].firstChild.nodeValue;
	document.getElementById('bulandaftarbpjs').value = bulandaftarbpjs;
	
	document.getElementById('namakabupaten').value = namakabupaten;
	document.getElementById('namakecamatan').value = namakecamatan;
	document.getElementById('namadesa').value = namadesa;
	document.getElementById('namaprovinsi').value = namaprovinsi;
	
	document.getElementById('kabupaten').value = kabupaten;
	document.getElementById('kecamatan').value = kecamatan;
	document.getElementById('desa').value = desa;
	
	
	//load form from extracted valiable
	document.getElementById('vstatuspajak').value = statuspajak;
	if(statuspajak=='TK')
	{
		document.getElementById('statuspajak').value='TK0';
	}
	else
	{
		document.getElementById('statuspajak').value=statuspajak;
	}
	document.getElementById('statuspajak').disabled=true;
	document.getElementById('nik').value = nik;
	document.getElementById('namakaryawan').value = namakaryawan;
	// document.getElementById('namareward').value=namareward;
	document.getElementById('tempatlahir').value = tempatlahir;
	document.getElementById('tanggallahir').value = tanggallahir;
	//console.log(displayphoto);
	if (displayphoto !== "*") {
		//console.log("photokaryawan/"+displayphoto);
		var photoimg = document.getElementById('displayphoto');
		// Nama file foto tetap sama walau isinya diganti (photo_<karyawanid>.jpeg),
		// jadi perlu cache-buster supaya browser tidak menampilkan foto lama dari cache.
		photoimg.setAttribute("src", "photokaryawan/" + displayphoto + "?v=" + new Date().getTime());
		var photoboth = document.getElementById('photoboth');
		photoimg.style.width = 150 + "px";
		photoboth.style.backgroundImage = "";
		photoboth.style.width = "150px";
		photoimg = document.getElementById('displayphoto');
		photoboth.style.height = "auto";
		photoboth.style.border = "solid 2px #FFF";

	}
	jk = document.getElementById('jeniskelamin');
	for (x = 0; x < jk.length; x++) {
		if (jk.options[x].value == jeniskelamin) {
			jk.options[x].selected = true;
		}
	}
	ag = document.getElementById('agama');
	for (x = 0; x < ag.length; x++) {
		if (ag.options[x].value == agama) {
			ag.options[x].selected = true;
		}
	}
	bg = document.getElementById('bagian');
	for (x = 0; x < bg.length; x++) {
		if (bg.options[x].value == bagian) {
			bg.options[x].selected = true;
		}
	}
	sd = document.getElementById('subdept');
	for (x = 0; x < sd.length; x++) {
		if (sd.options[x].value == subdept) {
			sd.options[x].selected = true;
		}
	}
	lk = document.getElementById('levelkaryawan');
	for (x = 0; x < lk.length; x++) {
		if (lk.options[x].value == levelkaryawan) {
			lk.options[x].selected = true;
		}
	}
	supp = document.getElementById('supbpjs');
	for (x = 0; x < supp.length; x++) {
		if (supp.options[x].value == supbpjs) {
			supp.options[x].selected = true;
		}
	}
	jbt = document.getElementById('kodejabatan');
	for (x = 0; x < jbt.length; x++) {
		if (jbt.options[x].value == kodejabatan) {
			jbt.options[x].selected = true;
		}
	}
	gol = document.getElementById('kodegolongan');
	for (x = 0; x < gol.length; x++) {
		if (gol.options[x].value == kodegolongan) {
			gol.options[x].selected = true;
		}
	}
	tgs = document.getElementById('lokasitugas');
	for (x = 0; x < tgs.length; x++) {
		if (tgs.options[x].value == lokasitugas) {
			tgs.options[x].selected = true;
		}
	}


	pag = document.getElementById('periodeakhirgaji');
	for (x = 0; x < pag.length; x++) {
		if (pag.options[x].value == periodeakhirgaji) {
			pag.options[x].selected = true;
		}
	}

	org = document.getElementById('kodeorganisasi');
	for (x = 0; x < org.length; x++) {
		if (org.options[x].value == kodeorganisasi) {
			org.options[x].selected = true;
		}
	}
	tik = document.getElementById('tipekaryawan');
	for (x = 0; x < tik.length; x++) {
		if (tik.options[x].value == tipekaryawan) {
			tik.options[x].selected = true;
		}
	}
	tok = document.getElementById('subbagian');
	for (x = 0; x < tok.length; x++) {
		if (tok.options[x].value == subbagian) {
			tok.options[x].selected = true;
		}
	}
	cat = document.getElementById('catu');
	for (x = 0; x < cat.length; x++) {
		if (cat.options[x].value == catu) {
			cat.options[x].selected = true;
		}
	}

	document.getElementById('noktp').value = noktp;
	document.getElementById('nopassport').value = nopaspor;
	wni = document.getElementById('warganegara');
	for (x = 0; x < wni.length; x++) {
		if (wni.options[x].value == warganegara) {
			wni.options[x].selected = true;
		}
	}
	suk = document.getElementById('suku');
	for (x = 0; x < suk.length; x++) {
		if (suk.options[x].value == suku) {
			suk.options[x].selected = true;
		}
	}
	statkarya = document.getElementById('statuskaryawan');
	for (x = 0; x < statkarya.length; x++) {
		if (statkarya.options[x].value == statuskaryawan) {
			statkarya.options[x].selected = true;
		}
	}
	poh = document.getElementById('lokasipenerimaan');
	for (x = 0; x < poh.length; x++) {
		if (poh.options[x].value == lokasipenerimaan) {
			poh.options[x].selected = true;
		}
	}

	kppn = document.getElementById('kppnpwp');
	for (x = 0; x < kppn.length; x++) {
		if (kppn.options[x].value == kppnpwp) {
			kppn.options[x].selected = true;
		}
	}

	/*stpj=document.getElementById('statuspajak');
	for(x=0;x<stpj.length;x++){
	if(stpj.options[x].value==insstatuspajak){
	stpj.options[x].selected=true;
	}
	}*/
	document.getElementById('npwp').value = npwp;
	document.getElementById('bpjs').value = bpjs;
	document.getElementById('pensiun').value = pensiun;
	document.getElementById('alamataktif').value = alamataktif;
	document.getElementById('kota').value = kota;
	document.getElementById('provinsi').value=provinsi;
	// prov = document.getElementById('provinsi');
	// for (x = 0; x < prov.length; x++) {
		// if (prov.options[x].value == provinsi) {
			// prov.options[x].selected = true;
		// }
	// }
	document.getElementById('kodepos').value = kodepos;
	document.getElementById('noteleponrumah').value = noteleponrumah;
	document.getElementById('nohp').value = nohp;
	document.getElementById('nohp2').value = nohp2;
	document.getElementById('norekeningbank').value = norekeningbank;
	document.getElementById('namabank').value = namabank;
	document.getElementById('anrekening').value = pemilikrekening;

	stmgj = document.getElementById('sistemgaji');
	for (x = 0; x < stmgj.length; x++) {
		if (stmgj.options[x].value == sistemgaji) {
			stmgj.options[x].selected = true;
		}
	}

	goldar = document.getElementById('golongandarah');
	for (x = 0; x < goldar.length; x++) {
		if (goldar.options[x].value == golongandarah) {
			goldar.options[x].selected = true;
		}
	}
	document.getElementById('tanggalmasuk').value = tanggalmasuk;
	document.getElementById('tanggalpengangkatan').value = tanggalpengangkatan;
	document.getElementById('tanggalpengangkatannonstaff').value = tanggalpengangkatannonstaff;
	document.getElementById('tanggalkeluar').value = tanggalkeluar;
	stk = document.getElementById('statusperkawinan');
	for (x = 0; x < stk.length; x++) {
		if (stk.options[x].value == statusperkawinan) {
			stk.options[x].selected = true;
		}
	}
	document.getElementById('tanggalmenikah').value = tanggalmenikah;
	document.getElementById('jumlahanak').value = jumlahanak;
	document.getElementById('jumlahtanggungan').value = jumlahtanggungan;
	document.getElementById('jms').value = jms;

	lvlpndk = document.getElementById('levelpendidikan');
	for (x = 0; x < lvlpndk.length; x++) {
		if (lvlpndk.options[x].value == levelpendidikan) {
			lvlpndk.options[x].selected = true;
		}
	}
	
	
	setValue2('bagian',bagian);
	setValue2('subdept',subdept);
	setValue2('levelkaryawan',levelkaryawan);
	setValue2('kodegolongan',kodegolongan);
	setValue2('lokasitugas',lokasitugas);
	setValue2('subbagian',subbagian);
	setValue2('suku',suk);
	setValue2('lokasipenerimaan',lokasipenerimaan);	
	setValue2('kodejabatan',kodejabatan);
	
	/*sttAkad=document.getElementById('statusakad');
	for(x=0;x<sttAkad.length;x++){
	if(sttAkad.options[x].value==statusakad){
	sttAkad.options[x].selected=true;
	}
	}*/

	document.getElementById('sim').value = sim;

	document.getElementById('notelepondarurat').value = notelepondarurat;
	document.getElementById('email').value = email;
	document.getElementById('emailkantor').value = emailkantor;
	document.getElementById('alokasi').value = alokasi;
	//change the method to update===========================
	document.getElementById('method').value = 'update';
	document.getElementById('savePhoto').hidden = true;
	document.getElementById('nourut').value = nourut;
	document.getElementById('karyawanid').value = karyawanid;
	document.getElementById('karyawanidreward').value = karyawanid;

	document.getElementById('tabFRM0').innerHTML = namakaryawan;
	//document.getElementById('tabFRM8').innerHTML=namakaryawan;
	document.getElementById('dptPremi').checked = false;
	if (dptPremi == 1) {
		document.getElementById('dptPremi').checked = true;
	}
	//load lis keanggotaan


	//=========================
	//enable save button each tab
	disabledonpayroll(tipekaryawan);
	enableOtherButton();
	loadExperience('queryonly', karyawanid);
	//loac photo
	// document.getElementById('displayphoto2').removeAttribute('src');
	// document.getElementById('displayphoto2').setAttribute('src',photo);
}

function disabledonpayroll(tipekaryawan) {
	if (tipekaryawan == '4') {
		document.getElementById('kodeorganisasi').disabled = true;
		document.getElementById('lokasitugas').disabled = true;
		//document.getElementById('tanggalkeluar').disabled = false;
		//document.getElementById('tanggalmasuk').disabled = false;
		document.getElementById('tanggalpengangkatan').disabled = false;
		//document.getElementById('alokasi').disabled = false;
	} else {
		document.getElementById('kodeorganisasi').disabled = true;
		document.getElementById('lokasitugas').disabled = true;
		//document.getElementById('tanggalkeluar').disabled = true;
		//document.getElementById('tanggalmasuk').disabled = true;
		// document.getElementById('alokasi').disabled = true;
	}
	
	
	document.getElementById('alokasi').disabled = true;
	tanggalkeluar = document.getElementById('tanggalkeluar').value;
	// if(tanggalkeluar=='00-00-0000' || tanggalkeluar==""){
		// document.getElementById('tanggalmasuk').disabled=true;
	// }else{
		// document.getElementById('tanggalmasuk').disabled=false;
	// }
	
	statuskaryawan= document.getElementById('statuskaryawan').value;
	tanggalpengangkatan= document.getElementById('tanggalpengangkatan').value;
	if(statuskaryawan=='Tetap' && (tanggalpengangkatan!='00-00-0000' && tanggalpengangkatan=='')){
		document.getElementById('tanggalpengangkatan').disabled=false;
	}else{
		document.getElementById('tanggalpengangkatan').disabled=false;
	}
}

function loadExperience(x, karyawanid) {
	param = 'queryonly=' + x + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_riwayat_pekerjaan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
					loadPendidikan('queryonly', karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadPendidikan(x, karyawanid) {
	param = 'queryonly=' + x + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_riwayat_pendidikan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerpendidikan').innerHTML = con.responseText;
					loadKursus(x, karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadKursus(x, karyawanid) {
	param = 'queryonly=' + x + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_riwayat_training.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containertraining').innerHTML = con.responseText;
					loadKeluarga(x, karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadKeluarga(x, karyawanid) {
	param = 'queryonly=' + x + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_keluarga.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containerkeluarga').innerHTML = con.responseText;
					loadAlamat(x, karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadAlamat(x, karyawanid) {
	param = 'queryonly=' + x + '&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_alamat_karyawan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('containeralamat').innerHTML = con.responseText;
					load_keanggotaan(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewKaryawan(karid, nama, ev) {
	param = 'karyawanid=' + karid + '&namakaryawan=' + nama+ "&method=''";
	tujuan = 'sdm_slave_get_karyawan_preview.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// content = con.responseText;
					// //display window
					// title = nama;
					// width = '100%';
					// height = '';
					// showDialog1(title, content, width, height, ev);
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewKaryawanhist(nourut,karid, nama, ev) {
	param = 'nourut=' + nourut + '&karyawanid=' + karid + '&namakaryawan=' + nama+ '&method=history';
	tujuan = 'sdm_slave_get_karyawan_preview.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// content = con.responseText;
					// //display window
					// title = nama;
					// width = '100%';
					// height = '400';
					// showDialog2(title, content, width, height, ev);
					alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function previewKaryawanPDF2(karid, nama, ev) {
	param = 'karyawanid=' + karid + '&namakaryawan=' + nama;
	param += "&tampilan=PDF&method=''";
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_get_karyawan_preview.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');		
}

function previewKaryawanPDF(karid, nama, ev) {
	param = 'karyawanid=' + karid + '&namakaryawan=' + nama;
	param += "&tampilan=PDF&method=''";
	// tujuan = 'sdm_slave_get_karyawan_preview_pdf.php?' + param;
	// //display window
	// title = nama;
	// width = '700';
	// height = '400';
	// content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	// showDialog1(title, content, width, height, ev);
	
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_get_karyawan_preview.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');		
}

function previewKaryawanjobdescPDF(karid, nama, ev) {
	param = 'karyawanid=' + karid + '&namakaryawan=' + nama;
	tujuan = 'sdm_slave_save_datakaryawan1.php?' + param;
	//display window
	title = nama;
	width = '700';
	height = '400';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

//---------------------------------------------------------------------------------------------------------------------------------


function ubah_listmsk(page) {

	txtsearch = document.getElementById('txtsearch').value; //options[document.getElementById('schorg').selectedIndex].value;
	schorg = document.getElementById('schorg').options[document.getElementById('schorg').selectedIndex].value;
	schtipe = document.getElementById('schtipe').options[document.getElementById('schtipe').selectedIndex].value;
	schstatus = document.getElementById('schstatus').options[document.getElementById('schstatus').selectedIndex].value;
	schjk = document.getElementById('schjk').options[document.getElementById('schjk').selectedIndex].value;

	thnmsk = document.getElementById('thnmsk').options[document.getElementById('thnmsk').selectedIndex].value;
	blnmsk = document.getElementById('blnmsk').options[document.getElementById('blnmsk').selectedIndex].value;
	thnkel = document.getElementById('thnkel').options[document.getElementById('thnkel').selectedIndex].value;
	blnkel = document.getElementById('blnkel').options[document.getElementById('blnkel').selectedIndex].value;

	param = 'thnmsk=' + thnmsk + '&blnmsk=' + blnmsk + '&thnkel=' + thnkel + '&blnkel=' + blnkel + '&schorg=' + schorg + '&schtipe=' + schtipe + '&schstatus=' + schstatus + '&txtsearch=' + txtsearch + '&schjk=' + schjk;

	//alert (param);
	tujuan = 'sdm_slave_load_employeeLaporan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					document.getElementById('searchplaceresult').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

//INIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
function cariKaryawanLaporan1(page) {

	document.getElementById('thnmsk').value = '';
	document.getElementById('blnmsk').value = '';
	document.getElementById('thnkel').value = '';
	document.getElementById('blnkel').value = '';

	schjk = document.getElementById('schjk');
	txtsearch = trim(document.getElementById('txtsearch').value);
	schorg = document.getElementById('schorg');
	schtipe = document.getElementById('schtipe');
	schstatus = document.getElementById('schstatus');
	schorg = schorg.options[schorg.selectedIndex].value;
	schtipe = schtipe.options[schtipe.selectedIndex].value;
	schstatus = schstatus.options[schstatus.selectedIndex].value;

	param = 'txtsearch=' + txtsearch;

	param += '&orgsearch=' + schorg;
	param += '&tipesearch=' + schtipe;
	param += '&statussearch=' + schstatus;
	param += '&schjk=' + schjk;
	param += '&page=' + page;

	//alertify.alert(param);

	tujuan = 'sdm_slave_load_employeeLaporan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('searchplaceresult').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariKaryawanLaporan(page) {
	//alertify.alert('MASUK');
	thnmsk = trim(document.getElementById('thnmsk').value);
	blnmsk = trim(document.getElementById('blnmsk').value);
	thnkel = trim(document.getElementById('thnkel').value);
	blnkel = trim(document.getElementById('blnkel').value);

	schjk = document.getElementById('schjk');
	txtsearch = trim(document.getElementById('txtsearch').value);
	schpt = document.getElementById('schpt');
	schorg = document.getElementById('schorg');
	schtipe = document.getElementById('schtipe');
	schstatus = document.getElementById('schstatus');
	schjk = schjk.options[schjk.selectedIndex].value;
	schpt = schpt.options[schpt.selectedIndex].value;
	schorg = schorg.options[schorg.selectedIndex].value;
	schtipe = schtipe.options[schtipe.selectedIndex].value;
	schstatus = schstatus.options[schstatus.selectedIndex].value;

	param = 'txtsearch=' + txtsearch;
	param += '&schjk=' + schjk;
	param += '&thnmsk=' + thnmsk;
	param += '&blnmsk=' + blnmsk;
	param += '&thnkel=' + thnkel;
	param += '&blnkel=' + blnkel;

	param += '&ptsearch=' + schpt;
	param += '&orgsearch=' + schorg;
	param += '&tipesearch=' + schtipe;
	param += '&statussearch=' + schstatus;
	param += '&page=' + page;

	//	alertify.alert(param);

	tujuan = 'sdm_slave_load_employeeLaporan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('searchplaceresult').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prefDatakaryawan1(btn, curval) {
	cariKaryawanLaporan(curval);
	if (curval == 0) {}
	else
		btn.value = parseInt(curval) - 1;
	document.getElementById('nextbtn').value = parseInt(btn.value) + 2;

}
function nextDatakaryawan1(btn, curval) {
	cariKaryawanLaporan(curval);
	btn.value = parseInt(curval) + 1;
	document.getElementById('prefbtn').value = parseInt(btn.value) - 2;
}

function datakaryawanExcel(ev, thnmsk, blnmsk, thnkel, blnkel, tujuan) {
	txtsearch = trim(document.getElementById('txtsearch').value);
	schpt = document.getElementById('schpt');
	schorg = document.getElementById('schorg');
	schtipe = document.getElementById('schtipe');
	schstatus = document.getElementById('schstatus');
	schpt = schpt.options[schpt.selectedIndex].value;
	schorg = schorg.options[schorg.selectedIndex].value;

	schjk = document.getElementById('schjk');
	schjk = schjk.options[schjk.selectedIndex].value;

	schtipe = schtipe.options[schtipe.selectedIndex].value;
	schstatus = schstatus.options[schstatus.selectedIndex].value;

	thnmsk = document.getElementById('thnmsk').value;
	blnmsk = document.getElementById('blnmsk').value;
	thnkel = document.getElementById('thnkel').value;
	blnkel = document.getElementById('blnkel').value;

	thnm = thnmsk;
	blnm = blnmsk;
	thnk = thnkel;
	blnk = blnkel;

	param = 'txtsearch=' + txtsearch;
	param += '&ptsearch=' + schpt;
	param += '&orgsearch=' + schorg;
	param += '&tipesearch=' + schtipe;
	param += '&statussearch=' + schstatus;
	param += '&schjk=' + schjk;
	param += '&thnmsk=' + thnm;
	param += '&blnmsk=' + blnm;

	param += '&thnkel=' + thnk;
	param += '&blnkel=' + blnk;

	tujuan = 'sdm_slave_datakaryawan_Excel.php?' + param;
	title = 'Download';
	width = 'auto';
	height = 'auto';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function delKaryawan(ki, nm) {
	param = 'method=delete' + '&ki=' + ki;
	//alertify.alert(param);
	tujuan = 'sdm_slave_get_employee_for_delete.php';
	if (confirm("Are you sure delete all data for employee: " + nm + " ?? ")) {
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadEmployeeList();

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function filterlokasitugas() {
	schpt = document.getElementById('schpt');
	schpt = schpt.options[schpt.selectedIndex].value;
	param = 'method=filterlokasitugas' + '&ptsearch=' + schpt;
	tujuan = 'sdm_slave_load_employeeLaporan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('schorg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getstatuspajak() {
	statuspajak = document.getElementById('statuspajak');
	statuspajak = statuspajak.options[statuspajak.selectedIndex].value;

	param = 'method=getstatuspajak' + '&statuspajak=' + statuspajak;
	tujuan = 'sdm_slave_statuspajak.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('vstatuspajak').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var karyawanid = document.getElementById("karyawanidreward").value;
	var namareward = document.getElementById("namareward").value;
	var tanggalreward = document.getElementById("tanggalreward").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("namareward", namareward);
	formdata.append("tanggalreward", tanggalreward);
	formdata.append("karyawanid", karyawanid);
	if (getValue('upload') == "") {
		alertify.alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled = true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_rewardkaryawan.php?proses=submitfile", true);
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
					document.getElementsByClassName("mybutton").disabled = false;
					alertify.alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfilekar() {
	var karyawanid = document.getElementById("karyawanid").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("uploadkar").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('uploadkar'));
	formdata.append("karyawanid", karyawanid);
	formdata.append("kriteriaefil", kriteriaefil);
	//alertify.alert(getValue('uploadkar'));
	if (getValue('uploadkar') == "") {
		alertify.alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled = true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_save_uploaddocument_karyawan.php?method=submitfile", true);
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
					document.getElementsByClassName("mybutton").disabled = false;
					alertify.alert('Uploaded Success.');
					document.getElementById("uploadkar").value = "";
					loadfileskar(karyawanid);

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfileskar(karyawanid) {
	param = 'karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_save_uploaddocument_karyawan.php?method=loadfileskar';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(karyawanid) {
	param = 'proses=loadfiles&karyawanid=' + karyawanid;
	tujuan = 'sdm_slave_rewardkaryawan.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {

					document.getElementById('containerreward').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(karyawanid, namafile, namareward) {
	param = 'proses=deletefile&karyawanid=' + karyawanid + '&namafile=' + namafile + '&namareward=' + namareward;
	tujuan = 'sdm_slave_rewardkaryawan.php';

	if (confirm("Apakah anda Yakin Mau Menghapus data ? ")) {
		post_response_text(tujuan, param, respog);
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function clearreward() {

	document.getElementById('namareward').value = '';
	document.getElementById('tanggalreward').value = '';

}

function showupload(ev,jenis) {
	//showformupload(ev);
	karyawanid = document.getElementById('karyawanid').value;
	param = 'karyawanid=' + karyawanid;
	param += '&jenis=' + jenis;
	tujuan = 'sdm_slave_save_uploaddocument_karyawan.php?method=showupload';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					alertify.popup("Upload",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%');
					//document.getElementById('contUpload').innerHTML = con.responseText;
					loadfileskar(karyawanid);
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

function deletefile(karyawanid, tipefile, namafile) {
	param = 'karyawanid=' + karyawanid + '&tipefile=' + tipefile + '&namafile=' + namafile;
	tujuan = 'sdm_slave_save_uploaddocument_karyawan.php?method=deletefile';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfileskar(karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

// Fungsi Baru Rizky
function setGajiTerakhir(tgl_klr) {
	let tgl = tgl_klr.substr(0,2);
	let bln = tgl_klr.substr(3,2);
	let thn = tgl_klr.substr(6,4);
	let tipek = document.getElementById('tipekaryawan').value;
	let statk = document.getElementById('statuskaryawan').value;
	document.getElementById('periodeakhirgaji').value = '';

	if (statk == 'Aktif' || statk == 'Keluar') {
		statk = 'KHL';
	} else {
		statk = 'Umum';
	}

	if (tgl_klr == '' || tgl_klr == '00-00-0000') {
		document.getElementById('periodeakhirgaji').value = '';
	} else {
		if (tipek == 4 && statk == 'KHL') {
			// alertify.alert(thn + '-' + bln);
			document.getElementById('periodeakhirgaji').value = thn + '-' + bln;
		} else if (tipek == 4 && statk == 'Umum') {
			// alertify.alert(thn + '-' + bln);
			document.getElementById('periodeakhirgaji').value = thn + '-' + bln;
		} else if (tipek != 4 && statk == 'KHL') {
			// alertify.alert(thn + '-' + bln);
			document.getElementById('periodeakhirgaji').value = thn + '-' + bln;
		} else {
			let blnplus = parseInt(bln) + 1;
			if (blnplus < 10) {
				// alertify.alert(thn + '-0' + blnplus);
				document.getElementById('periodeakhirgaji').value = thn + '-0' + blnplus;
			} else if (blnplus > 12) {
				let thnplus = parseInt(thn) + 1;
				blnplus = '01';
				// alertify.alert(thnplus + '-' + blnplus);
				document.getElementById('periodeakhirgaji').value = thnplus + '-' + blnplus;
			} else {
				// alertify.alert(thn + '-' + blnplus);
				document.getElementById('periodeakhirgaji').value = thn + '-' + blnplus;
			}
		}
	}
}