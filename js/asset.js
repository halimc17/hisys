/**
 * @author repindra.ginting
 */
function displayFormInput() {
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formInput').style.display = '';
	// tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);
	cancelAsset();
}
function displayList() {
	// tabAction(document.getElementById('tabFRM1'), 1, 'FRM', 0);
	document.getElementById('listData').style.display = '';
	document.getElementById('formInput').style.display = 'none';
	cancelAsset();
	loadData(0);
}
function getSub(sub,lokasi) {
	tipe = document.getElementById('tipe').value;
	param = 'method=getSub' + '&tipe=' + tipe + '&sub=' + sub
		//alert(param);
		tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					if (tipe == 'BG') //bangunan
					{
						document.getElementById('kodebarang').value = '';
						document.getElementById('kodebarang').style.display = 'none';
						document.getElementById('namaaset').style.width = '336px';
					} else {
						document.getElementById('namaaset').style.width = '233px';
						document.getElementById('kodebarang').style.display = '';
					}
					document.getElementById('sub').innerHTML = con.responseText;
					changetipelokasi(lokasi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getSubsch() {
	tipesch = document.getElementById('tipesch').value;
	param = 'method=getSub' + '&tipe=' + tipesch;
		//alert(param);
		tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('subsch').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function changetipelokasi(lokasi) {
	posisiasset = document.getElementById('posisiasset').value;
	param = 'method=changetipelokasi' + '&posisiasset=' + posisiasset+ '&lokasi=' + lokasi;
		//alert(param);
		tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipelokasiasset').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function changetipelokasisch(lokasi) {
	posisiasset = document.getElementById('posisiasset').value;
	param = 'method=changetipelokasi' + '&posisiasset=' + posisiasset+ '&lokasi=' + lokasi;
		//alert(param);
		tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tipelokasiassetsch').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariNoGudang(title, ev) {
	content = "<div>";
	content += "<fieldset>";
	content += "Document : <select id=tipedoc><option value='po'>PO</option>";
	content += "  <option value='spk'>SPK</option></select>";
	content += "Number : <input placeholder='Min 1 Character'  type=text id=noGudang class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariGudang()>Go</button> </fieldset>";
	content += "<fieldset><div id=containercari style=\"height:250px;overflow:auto;\"></div></fieldset></div>";
	title = title + ' PO:';
	width = '600';
	height = '300';
	showDialog1(title, content, width, height, ev);
}
function goCariGudang() {
	tipedoc = document.getElementById('tipedoc').options[document.getElementById('tipedoc').selectedIndex].value;
	// tipe=document.getElementById('tipe').value;
	noGudang = trim(document.getElementById('noGudang').value);
	//tipedocalert(tipedoc);
	if (noGudang.length < 1)
		alert(notiftextshort);
	else {
		param = 'method=goCariGudang' + '&noGudang=' + noGudang + '&tipedoc=' + tipedoc;
		// alert(param);
		tujuan = 'sdm_slave_save_daftarAsset2.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function goPickGudang(noGudang) {
	document.getElementById('nodokpengadaan').value = noGudang;
	closeDialog();
}
//////////////////////// cari nomor induk
function cariNoInduk(title, ev) {
	content = "<div>";
	content += "<fieldset>";
	content += "Search : <input placeholder='Min 1 Character' type=text id=noInduk class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariInduk()>Go</button>"
	content += "</fieldset>";
	content += "<fieldset><legend>Result</legend><div id=containercari style=\"height:250px;overflow:auto;\"></div></div></fieldset>";
	title = title + ' Parent Number:';
	width = '600';
	height = '300';
	showDialog1(title, content, width, height, ev);
}
function goCariInduk() {
	noInduk = trim(document.getElementById('noInduk').value);
	if (noInduk.length < 1)
		alert(notiftextshort);
	else {
		param = 'method=goCariInduk' + '&noInduk=' + noInduk;
		tujuan = 'sdm_slave_save_daftarAsset2.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function goPickInduk(noInduk) {
	document.getElementById('induk').value = noInduk;
	closeDialog();
}
function cek() {
	kdorg=document.getElementById('kodeorg');
	kdorg=kdorg.options[kdorg.selectedIndex].value;
	kdAset = document.getElementById('tipe').value;
	sub = document.getElementById('sub').value;
	param = 'method=getKodeAkhir' + '&kdAset=' + kdAset + '&sub=' + sub+'&kodeorg='+kdorg;
	//alert(param);
	tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi = con.responseText.split("#####");
					document.getElementById('kodeaset').value = isi[0];
					document.getElementById('jumlahbulan').value = "";
					document.getElementById('persendecline').value = "";
					if (isi[1] == 'double') {
						document.getElementById('jumlahbulan').disabled = false;
						document.getElementById('persendecline').disabled = false;
					} else {
						document.getElementById('jumlahbulan').disabled = false;
						document.getElementById('persendecline').disabled = true;
					}
					getSusut();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getSusut() {
	tipe = document.getElementById('tipe').value;
	sub = document.getElementById('sub').value;
	param = 'method=getSusut' + '&tipe=' + tipe + '&sub=' + sub;
	//alert(param);
	tujuan = 'sdm_slave_save_daftarAsset2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('jumlahbulan').value = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showWindowBarang(title, ev) {
	content = "<div style='width:100%;'>";
	content += "<fieldset>" + title + "<input type=text id=txtnamabarang class=myinputtext size=25 onkeypress=\"return enterEuy(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> </fieldset>";
	content += "<div id=containercari style='overflow:scroll;height:300px;width:520px'></div></div>";
	//display window
	width = '550';
	height = '350';
	showDialog1(title, content, width, height, ev);
}
function enterEuy(evt) {
	key = getKey(evt);
	if (key == 13) {
		goCariBarang();
	} else {
		return tanpa_kutip(evt);
	}
}
function goCariBarang() {
	txtcari = trim(document.getElementById('txtnamabarang').value);
	if (txtcari.length < 3) {
		alert('material name min. 3 char');
	} else {
		param = 'txtcari=' + txtcari;
		tujuan = 'log_slave_cariBarangUmum.php';
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function throwThisRow(kode, nama, satuan) {
	document.getElementById('kodebarang').value = kode;
	document.getElementById('namaaset').value = nama;
	closeDialog();
}
function simpanAssetBaru() {
	kodeorg = trim(document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value);
	jenisbiaya = trim(document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value);
	tipe = trim(document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value);
	kodeasset = trim(document.getElementById('kodeaset').value);
	kodebarang = trim(document.getElementById('kodebarang').value);
	namaaset = trim(document.getElementById('namaaset').value);
	tahunperolehan = trim(document.getElementById('tahunperolehan').value);
	statu = trim(document.getElementById('status').options[document.getElementById('status').selectedIndex].value);
	nilaiperolehan = remove_comma_var(document.getElementById('nilaiperolehan').value);
	jumlahbulan = trim(document.getElementById('jumlahbulan').value);
	bulanawal = trim(document.getElementById('bulanawal').options[document.getElementById('bulanawal').selectedIndex].value);
	// bulanawal = trim(document.getElementById('bulanawal').value);
	keterangan = trim(document.getElementById('keterangan').value);
	penambah = trim(document.getElementById('penambah').value);
	pengurang = trim(document.getElementById('pengurang').value);
	leasing = trim(document.getElementById('leasing').options[document.getElementById('leasing').selectedIndex].value);
	psisasset = trim(document.getElementById('posisiasset').options[document.getElementById('posisiasset').selectedIndex].value);
	refbayar = trim(document.getElementById('refbayar').value);
	nodokpengadaan = trim(document.getElementById('nodokpengadaan').value);
	persendecline = trim(document.getElementById('persendecline').value);
	tanggaldisposal = trim(document.getElementById('tanggaldisposal').value);
	sub = trim(document.getElementById('sub').value);
	induk = trim(document.getElementById('induk').value);
	kodeasetlama = trim(document.getElementById('kodeasetlama').value);
	nomesin = trim(document.getElementById('nomesin').value);
	norangka = trim(document.getElementById('norangka').value);
	tipemodel = trim(document.getElementById('tipemodel').value);
	penyusutantambahan = trim(document.getElementById('penyusutantambahan').value);

	tipelokasiasset = trim(document.getElementById('tipelokasiasset').value);

	met = document.getElementById('method').value;
	if (kodeorg == '' || tipe == '' || kodeasset == '' || namaaset == '' || tahunperolehan == '' || statu == '' || jenisbiaya == '') {
		// alertify.alert(notifdatainconsistent);
		alertify.alert('Lengkapi Tanda Berwarna Merah');
	} else {
		param = 'kodeorg=' + kodeorg + '&tipe=' + tipe + '&kodeasset=' + kodeasset;
		param += '&kodebarang=' + kodebarang + '&namaaset=' + namaaset + '&tahunperolehan=' + tahunperolehan + '&status=' + statu;
		param += '&nilaiperolehan=' + nilaiperolehan + '&jumlahbulan=' + jumlahbulan + '&bulanawal=' + bulanawal;
		param += '&keterangan=' + keterangan + '&method=' + met + '&leasing=' + leasing;
		param += '&penambah=' + penambah + '&pengurang=' + pengurang + '&posisiasset=' + psisasset;
		param += '&refbayar=' + refbayar + '&nodokpengadaan=' + nodokpengadaan + '&persendecline=' + persendecline;
		param += '&sub=' + sub + '&induk=' + induk + '&jenisbiaya=' + jenisbiaya + '&tanggaldisposal=' + tanggaldisposal+ '&tipelokasiasset=' + tipelokasiasset;
		param += '&kodeasetlama=' + kodeasetlama +'&nomesin=' + nomesin +'&norangka=' + norangka;
		param += '&tipemodel=' + tipemodel +'&penyusutantambahan=' + penyusutantambahan;
		tujuan = 'sdm_slave_save_daftarAsset.php';
		if (confirm(notifandayakin))
			post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					// document.getElementById('containeraset').innerHTML = con.responseText;
					loadData(0);
					alert(datatersimpan);
					cancelAsset();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadData(page) {
	// tex  				= trim(document.getElementById('txtsearch').value);
	kodeorgsch 			= trim(document.getElementById('kodeorgsch').value);
	namaasetsch  	= trim(document.getElementById('namaasetsch').value);
	kodeasetlamasch  	= trim(document.getElementById('kodeasetlamasch').value);
	kodeasetsch  	= trim(document.getElementById('kodeasetsch').value);
	tipesch 			= trim(document.getElementById('tipesch').value);
	tipelokasiassetsch 	= trim(document.getElementById('tipelokasiassetsch').value);
	subsch 				= trim(document.getElementById('subsch').value);
	bulanawalsch 		= trim(document.getElementById('bulanawalsch').value);
	posisiassetsch 		= trim(document.getElementById('posisiassetsch').value);
	statussch  			= trim(document.getElementById('statussch').value);
	kodeprojectsch  			= trim(document.getElementById('kodeprojectsch').value);
	
	param = '';
	param = '&page=' + page;
	// param += "&txtcari=" + tex;
	param += "&kodeprojectsch=" + kodeprojectsch;
	param += "&kodeorgsch=" + kodeorgsch;
	param += "&namaasetsch=" + namaasetsch;
	param += "&kodeasetsch=" + kodeasetsch;
	param += "&kodeasetlamasch=" + kodeasetlamasch;
	param += "&tipelokasiassetsch=" + tipelokasiassetsch;
	param += "&subsch=" + subsch;
	param += "&bulanawalsch=" + bulanawalsch;
	param += "&posisiassetsch=" + posisiassetsch;
	param += "&statussch=" + statussch;
	param += "&tipesch=" + tipesch;
	param += '&method=loadData';
	
	tujuan = 'sdm_slave_save_daftarAsset.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containeraset').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function editAsset(kodeorg, tipeasset, kodeasset, namasset, kodebarang, tahunperolehan, stat, hargaperolehan, jlhblnpenyusutan, awalpenyusutan, keterangan, leasing, pena, peng, refbayar, nodok, persen, pssasset, induk, sub, jenisbiaya, tanggaldisposal,kodeasetlama,tipelokasiasset,nomesin,norangka,tipemodel,akumulasiadjust) {
	a = document.getElementById('kodeorg');
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == kodeorg) {
			a.options[x].selected = true;
		}
	}
	a = document.getElementById('jenisbiaya');
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == jenisbiaya) {
			a.options[x].selected = true;
		}
	}
	a = document.getElementById('tipe');
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == tipeasset) {
			a.options[x].selected = true;
		}
	}
	a = document.getElementById('status');
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == stat) {
			a.options[x].selected = true;
		}
	}
	a = document.getElementById('leasing');
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == leasing) {
			a.options[x].selected = true;
		}
	}
	document.getElementById('bulanawal').value = awalpenyusutan;
	document.getElementById('kodeasetlama').value = kodeasetlama;
	document.getElementById('kodeaset').value = kodeasset;
	document.getElementById('kodeaset').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('namaaset').value = namasset;
	document.getElementById('kodebarang').value = kodebarang;
	document.getElementById('tahunperolehan').value = tahunperolehan;
	document.getElementById('nilaiperolehan').value = hargaperolehan;
	document.getElementById('jumlahbulan').value = jlhblnpenyusutan;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('penambah').value = pena;
	document.getElementById('pengurang').value = peng;
	document.getElementById('method').value = 'update';
	document.getElementById('refbayar').value = refbayar;
	document.getElementById('nodokpengadaan').value = nodok;
	document.getElementById('persendecline').value = persen;
	document.getElementById('nomesin').value = nomesin;
	document.getElementById('norangka').value = norangka;
	document.getElementById('tipemodel').value = tipemodel;
	document.getElementById('penyusutantambahan').value = akumulasiadjust;
	if (tanggaldisposal == '00-00-0000') {
		tanggaldisposal = ''
	}
	document.getElementById('tanggaldisposal').value = tanggaldisposal;
	document.getElementById('induk').value = induk;
	document.getElementById('sub').value = sub;
	a = document.getElementById('posisiasset');
	document.getElementById('sub').disabled = true;
	document.getElementById('tipe').disabled = true;
	for (x = 0; x < a.length; x++) {
		if (a.options[x].value == pssasset) {
			a.options[x].selected = true;
		}
	}
	// tabAction(document.getElementById('tabFRM0'), 0, 'FRM', 1);
	document.getElementById('listData').style.display = 'none';
	document.getElementById('formInput').style.display = '';
	getSub(sub,tipelokasiasset);
}
function delAsset(kodeorg, kodeasset) {
	param = "&kodeorg=" + kodeorg + '&kodeasset=' + kodeasset + '&method=delete'; //deleting row
	tujuan = 'sdm_slave_save_daftarAsset.php';
	if (confirm(notifdeleteingdata + ' ' + kodeasset))
		post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('containeraset').innerHTML = con.responseText;
					loadData(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cancelAsset() {
	//document.getElementById('kodeaset').disabled=false;
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('tipe').disabled = false;
	document.getElementById('sub').disabled = false;
	document.getElementById('kodeorg').options[0].selected = true;
	document.getElementById('jenisbiaya').options[0].selected = true;
	document.getElementById('tipe').options[0].selected = true;
	document.getElementById('kodeaset').value = '';
	document.getElementById('kodebarang').value = '';
	document.getElementById('namaaset').value = '';
	document.getElementById('tahunperolehan').value = '';
	document.getElementById('status').options[0].selected = true;
	document.getElementById('nilaiperolehan').value = '0';
	document.getElementById('jumlahbulan').value = '0';
	document.getElementById('penambah').value = '0';
	document.getElementById('pengurang').value = '0';
	//document.getElementById('bulanawal').options[0].selected = true;
	document.getElementById('bulanawal').value='';
	document.getElementById('keterangan').value = '';
	document.getElementById('method').value = 'insert';
	document.getElementById('refbayar').value = '';
	document.getElementById('nodokpengadaan').value = '';
	document.getElementById('tanggaldisposal').value = '';
	document.getElementById('persendecline').value = '0';
	document.getElementById('induk').value = '';
	document.getElementById('sub').value = '';
	document.getElementById('kodeasetlama').value = '';
	document.getElementById('tipelokasiasset').value = '';
	document.getElementById('norangka').value = '';
	document.getElementById('nomesin').value = '';
	// document.getElementById('txtsearch').value = '';
	
	document.getElementById('kodeorgsch').value = '';
	document.getElementById('namaasetsch').value = '';
	document.getElementById('kodeasetlamasch').value = '';
	document.getElementById('kodeasetsch').value = '';
	document.getElementById('tipesch').value = '';
	document.getElementById('tipelokasiassetsch').value = '';
	document.getElementById('subsch').value = '';
	document.getElementById('bulanawalsch').value = '';
	document.getElementById('posisiassetsch').value = '';
	document.getElementById('statussch').value = '';
	document.getElementById('kodeprojectsch').value = '';
}
/**
 * cekJenisDecline
 * Cek Bulan dan Persen Penyusutan, untuk single atau double decline
 */
function cekJenisDecline() {
	var bulan = document.getElementById('jumlahbulan'),
	persen = document.getElementById('persendecline');
	if (bulan.value > 0) {
		persen.disabled = true;
		persen.value = 0;
	} else {
		persen.disabled = false;
	}
	if (persen.value > 0) {
		bulan.disabled = true;
		bulan.value = 0;
	} else {
		bulan.disabled = false;
	}
}
function form() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:90%;\"><div align=center id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Qr Code";
	showDialog5(title, content, width, height, ev);
}
function viewfile(ev, namafile) {
	ext = namafile.split(".");
	if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		form();
		param = 'namafile=' + namafile;
		tujuan = 'sdm_slave_daftarassetviewqrcode.php';
		post_response_text(tujuan, param, respog);
	} else {
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		return;
	}

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPrdAwal(tglIni){
	tgl=tglIni.value;
	cekTglPost=tgl.split("-");
	if(parseInt(cekTglPost[0])<15){
	    prdAwalPenyusutan=cekTglPost[2]+"-"+cekTglPost[1];
	}else{
	    if(parseInt(cekTglPost[1])<10){
	        prdAwalPenyusutan="0"+(parseInt(cekTglPost[1])+1);    
	    }else{
	        if(cekTglPost[1]=='12'){
	            thnPnystan=(parseInt(cekTglPost[2])+1);   
	            prdAwalPenyusutan=thnPnystan+"-01";
	        }else{
	            blnPnystan=(parseInt(cekTglPost[1])+1);        
	            prdAwalPenyusutan=cekTglPost[2]+"-"+blnPnystan;
	        }
	    }
	    
	}
	Prd=document.getElementById('bulanawal');
	for(a=0;a<Prd.length;a++){
        if(Prd.options[a].value==prdAwalPenyusutan){
                Prd.options[a].selected=true;
        }
    }
}